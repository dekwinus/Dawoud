<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import {
  Search as MagnifyingGlassIcon,
  UserPlus as UserPlusIcon,
  Tag as TagIcon,
  Trash2 as TrashIcon,
  CreditCard as CreditCardIcon,
  RotateCcw as ArrowPathIcon,
  Minus as MinusIcon,
  Plus as PlusIcon,
  Layers as RectangleStackIcon,
  Package as BoxIcon,
  User,
  Store,
  Wallet,
  ShoppingCart as ShoppingCartIcon,
  Check,
  X,
  Banknote,
  ArrowRight,
  ListOrdered,
} from 'lucide-vue-next';

// ── Props ────────────────────────────────────────────────────────────
const props = defineProps({
  warehouses:      Array,
  clients:         Array,
  categories:      Array,
  brands:          Array,
  pos_settings:    Object,
  settings:        Object,
  payment_methods: Array,
  accounts:        Array,
});

// ── Product State ─────────────────────────────────────────────────
const cart             = ref([]);
const searchQuery      = ref('');
const selectedCategory = ref(null);
const selectedWarehouse = ref(props.warehouses[0]?.id || null);
const isLoadingProducts = ref(false);
const products         = ref([]);

// ── Client State ──────────────────────────────────────────────────
const selectedClient = ref(props.clients[0]?.id || null);
const clientData     = computed(() => props.clients.find(c => c.id === selectedClient.value) || {});

const isOverCreditLimit = computed(() => {
  const limit = Number(clientData.value.credit_limit || 0);
  if (limit <= 0) return false;
  return (Number(clientData.value.net_balance || 0) + grandTotal.value) > limit;
});

// ── Payment Modal State ───────────────────────────────────────────
const showPaymentModal    = ref(false);
const selectedPaymentMethod = ref(props.payment_methods[0]?.id || null);
const selectedAccount     = ref(props.accounts[0]?.id || null);
const amountPaid          = ref(0);
const paymentNote         = ref('');
const checkoutLoading     = ref(false);
const checkoutError       = ref(null);
const successMessage      = ref(null);

// ── Toast ─────────────────────────────────────────────────────────
let toastTimer = null;
const showToast = (msg, type = 'success') => {
  successMessage.value = { msg, type };
  clearTimeout(toastTimer);
  toastTimer = setTimeout(() => { successMessage.value = null; }, 3500);
};

// ── Totals ────────────────────────────────────────────────────────
const taxRate = computed(() => Number(props.settings?.tax_rate || 0));

const subtotal = computed(() =>
  cart.value.reduce((acc, item) => {
    const lineBase     = item.price * item.quantity;
    const lineDiscount = item.discount_type === 'fixed'
      ? item.discount
      : lineBase * (item.discount / 100);
    return acc + Math.max(0, lineBase - lineDiscount);
  }, 0)
);
const taxAmount  = computed(() => subtotal.value * taxRate.value / 100);
const grandTotal = computed(() => subtotal.value + taxAmount.value);
const changeDue  = computed(() => Math.max(0, amountPaid.value - grandTotal.value));

// ── Category Colors ───────────────────────────────────────────────
const getCategoryColor = (catId) => {
  const colors = [
    'bg-emerald-500/10 text-emerald-600',
    'bg-blue-500/10    text-blue-600',
    'bg-amber-500/10   text-amber-600',
    'bg-purple-500/10  text-purple-600',
    'bg-pink-500/10    text-pink-600',
    'bg-cyan-500/10    text-cyan-600',
  ];
  return colors[(catId || 0) % colors.length];
};

const currency = computed(() => props.settings?.currency_code || 'ج.م');
const toSafeNumber = (value, fallback = 0) => {
  const n = Number(value);
  return Number.isFinite(n) ? n : fallback;
};

const extractPriceNumber = (value) => {
  if (typeof value === 'number') {
    return Number.isFinite(value) ? value : 0;
  }

  if (typeof value === 'string') {
    const firstLine = value.split('\n')[0]?.trim() || '';
    if (!firstLine) return 0;

    const normalized = firstLine
      .replace(/,/g, '')
      .replace(/[^0-9.-]/g, '');

    return toSafeNumber(normalized, 0);
  }

  return toSafeNumber(value, 0);
};

const fmt = (val) => toSafeNumber(val).toLocaleString('ar-EG');
const isOutOfStock = (product) => toSafeNumber(product?.qte, 0) <= 0;

// ── Fetch Products ────────────────────────────────────────────────
const fetchProducts = async () => {
  isLoadingProducts.value = true;
  try {
    const { data } = await axios.get('/api/products', {
      params: {
        search:       searchQuery.value,
        category_id:  selectedCategory.value || '',
        warehouse_id: selectedWarehouse.value || '',
        limit: 60,
        page: 1,
      },
    });
    products.value = (data.products || []).map((product) => ({
      ...product,
      price: extractPriceNumber(product.price ?? product.Unit_price ?? product.Total_price ?? product.Net_price),
      qte: toSafeNumber(product.qte, 0),
      unit: product.unit || product.unitSale || 'قطعة',
      variant_id: product.variant_id ?? product.product_variant_id ?? null,
    }));
  } catch (e) {
    console.error(e);
  } finally {
    isLoadingProducts.value = false;
  }
};

watch([searchQuery, selectedCategory, selectedWarehouse], fetchProducts);

// ── Cart Management ───────────────────────────────────────────────
const addToCart = (product) => {
  if (isOutOfStock(product)) return;

  const existing = cart.value.find(
    i => i.id === product.id && (i.variant_id || null) === (product.variant_id || null)
  );
  if (existing) {
    existing.quantity++;
  } else {
    cart.value.push({
      ...product,
      price:          toSafeNumber(product.price),
      quantity:       1,
      discount:       0,
      discount_type:  'fixed',
      price_type:     'retail',
    });
  }
};

const removeFromCart = (index) => cart.value.splice(index, 1);

const updateQuantity = (index, delta) => {
  const item = cart.value[index];
  if (!item) return;
  if (item.quantity + delta > 0) item.quantity += delta;
};

const updateItemDiscount = (index, val) => {
  cart.value[index].discount = toSafeNumber(parseFloat(val));
};

const clearCart = () => {
  cart.value = [];
  checkoutError.value = null;
};

// ── Open Payment Modal ────────────────────────────────────────────
const openPaymentModal = () => {
  if (cart.value.length === 0) return;
  amountPaid.value = grandTotal.value;
  checkoutError.value = null;
  showPaymentModal.value = true;
};

// ── Checkout ──────────────────────────────────────────────────────
// BUG FIX #1: route was /api/sales — correct endpoint is /api/pos/create_pos
// BUG FIX #2: no idempotency key was sent — backend deduplicates on sale_uuid
const buildDetails = () => cart.value.map(item => {
  const lineSubtotal = item.price * item.quantity;
  const lineDiscount = item.discount_type === 'fixed'
    ? item.discount
    : lineSubtotal * (item.discount / 100);
  return {
    product_id:         item.id,
    product_variant_id: item.variant_id || null,
    Unit_price:         item.price,
    subtotal:           Math.max(0, lineSubtotal - lineDiscount),
    quantity:           item.quantity,
    discount:           item.discount,
    discount_Method:    item.discount_type === 'fixed' ? '2' : '1',
    tax_percent:        taxRate.value,
    tax_method:         'exclusive',
    sale_unit_id:       item.unit_sale_id || item.unit_id || null,
    price_type:         item.price_type || 'retail',
  };
});

const submitSale = async (paidAmount, paymentStatut) => {
  checkoutLoading.value = true;
  checkoutError.value   = null;
  try {
    await axios.post('/api/pos/create_pos', {
      sale_uuid:       crypto.randomUUID(),
      warehouse_id:    selectedWarehouse.value,
      client_id:       selectedClient.value,
      statut:          'completed',
      payment_statut:  paymentStatut,
      payments: [{
        amount:            paidAmount,
        payment_method_id: selectedPaymentMethod.value,
        account_id:        selectedAccount.value,
      }],
      discount:        0,
      shipping:        0,
      tax_rate:        taxRate.value,
      TaxNet:          taxAmount.value,
      discount_type:   'fixed',
      GrandTotal:      grandTotal.value,
      date:            new Date().toISOString().split('T')[0],
      notes:           '',
      payment_note:    paymentNote.value,
      details:         buildDetails(),
    });
    showPaymentModal.value = false;
    const change = Math.max(0, paidAmount - grandTotal.value);
    clearCart();
    paymentNote.value = '';
    showToast(
      change > 0
        ? `✔ تم البيع · الفكة: ${fmt(change)} ${currency.value}`
        : '✔ تم تسجيل البيع بنجاح',
      'success'
    );
  } catch (e) {
    checkoutError.value = e.response?.data?.message || 'تعذر تسجيل البيع، حاول مجدداً';
  } finally {
    checkoutLoading.value = false;
  }
};

// Cash checkout: called from inside the payment modal (F2)
const checkout = async () => {
  if (cart.value.length === 0 || checkoutLoading.value) return;
  const paidAmount = toSafeNumber(amountPaid.value);
  const paid   = Math.min(paidAmount, grandTotal.value);
  const status = paidAmount >= grandTotal.value ? 'paid' : 'partial';
  await submitSale(paid, status);
};

// Credit (deferred) checkout: 0 paid upfront (F3 / بيع آجل)
const checkoutCredit = async () => {
  if (cart.value.length === 0 || checkoutLoading.value) return;
  await submitSale(0, 'unpaid');
};

// ── Keyboard Shortcuts ────────────────────────────────────────────
const handleKeydown = (e) => {
  if (e.key === 'F1') { e.preventDefault(); document.querySelector('#pos-search')?.focus(); }
  if (e.key === 'F2') { e.preventDefault(); if (!showPaymentModal.value) openPaymentModal(); else checkout(); }
  if (e.key === 'F4') { e.preventDefault(); clearCart(); }
  if (e.key === 'Escape') { showPaymentModal.value = false; }
};

onMounted(() => {
  fetchProducts();
  window.addEventListener('keydown', handleKeydown);
});
onBeforeUnmount(() => window.removeEventListener('keydown', handleKeydown));
</script>

<template>
  <AdminLayout :hideSidebar="true">
    <Head title="نقطة البيع - DawPOS" />

    <!-- Toast Notification -->
    <transition name="slide-up">
      <div
        v-if="successMessage"
        :class="[
          'fixed bottom-8 left-1/2 -translate-x-1/2 z-[200] px-8 py-4 rounded-[24px] shadow-2xl font-black text-sm font-[\'Cairo\'] flex items-center gap-3 border whitespace-nowrap',
          successMessage.type === 'success'
            ? 'bg-[#04724D] text-white border-[#056b47] shadow-[#04724D]/30'
            : 'bg-red-500 text-white border-red-600 shadow-red-500/30'
        ]"
      >
        <Check v-if="successMessage.type === 'success'" class="w-5 h-5 shrink-0" />
        <X v-else class="w-5 h-5 shrink-0" />
        {{ successMessage.msg }}
      </div>
    </transition>

    <!-- Payment Modal -->
    <transition name="modal-fade">
      <div v-if="showPaymentModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4" dir="rtl">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="showPaymentModal = false" />

        <div class="relative bg-white dark:bg-[#141414] w-full max-w-2xl rounded-[40px] shadow-2xl overflow-hidden flex flex-col max-h-[92vh]">
          <!-- Modal Header -->
          <div class="px-8 py-6 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
            <div>
              <h2 class="text-xl font-black text-gray-900 dark:text-white font-['Cairo'] tracking-tight">إتمام عملية البيع</h2>
              <p class="text-xs text-gray-400 font-black uppercase tracking-widest mt-1">اختر طريقة الدفع وأكد المبلغ</p>
            </div>
            <button @click="showPaymentModal = false" class="p-3 hover:bg-gray-100 dark:hover:bg-white/5 rounded-2xl transition-colors text-gray-400 hover:text-gray-700">
              <X class="h-5 w-5" />
            </button>
          </div>

          <!-- Modal Body -->
          <div class="flex-1 overflow-y-auto p-8 custom-scrollbar">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
              <!-- Left: Summary -->
              <div class="space-y-6">
                <div class="bg-gray-50 dark:bg-white/5 p-7 rounded-[28px] border border-gray-100 dark:border-gray-800 text-center">
                  <p class="text-[10px] text-gray-400 font-black uppercase tracking-[0.25em] mb-3">الإجمالي المستحق</p>
                  <div class="text-5xl font-black text-[#04724D] dark:text-[#3EFF8B] tracking-tighter tabular-nums">
                    {{ fmt(grandTotal) }}
                    <span class="text-lg opacity-50 font-['Cairo']">{{ currency }}</span>
                  </div>
                  <div v-if="taxAmount > 0" class="mt-2 text-xs text-gray-400 font-black">
                    شامل ضريبة {{ fmt(taxAmount) }} {{ currency }}
                  </div>
                </div>

                <!-- Payment Method Cards -->
                <div>
                  <p class="text-[10px] text-gray-400 font-black uppercase tracking-[0.25em] mb-3">طريقة الدفع</p>
                  <div class="grid grid-cols-2 gap-3">
                    <button
                      v-for="pm in payment_methods"
                      :key="pm.id"
                      @click="selectedPaymentMethod = pm.id"
                      :class="[
                        'flex flex-col items-center gap-2 p-4 rounded-2xl border-2 transition-all duration-300 font-black font-[\'Cairo\'] text-[11px]',
                        selectedPaymentMethod === pm.id
                          ? 'border-[#04724D] bg-[#04724D]/5 text-[#04724D] dark:text-[#3EFF8B] shadow-xl shadow-[#04724D]/10'
                          : 'border-gray-100 dark:border-gray-800 text-gray-500 hover:border-gray-200'
                      ]"
                    >
                      <Banknote v-if="pm.name.toLowerCase().includes('cash') || pm.name.includes('نقد')" class="h-5 w-5" />
                      <CreditCardIcon v-else-if="pm.name.toLowerCase().includes('card') || pm.name.includes('بطاقة')" class="h-5 w-5" />
                      <Wallet v-else class="h-5 w-5" />
                      <span>{{ pm.name }}</span>
                    </button>
                  </div>
                </div>
              </div>

              <!-- Right: Inputs -->
              <div class="space-y-5">
                <!-- Amount Input -->
                <div>
                  <label class="text-[10px] text-gray-400 font-black uppercase tracking-[0.25em] block mb-2">المبلغ المدفوع</label>
                  <div class="relative">
                    <input
                      v-model.number="amountPaid"
                      type="number"
                      :min="grandTotal"
                      class="w-full bg-gray-50 dark:bg-white/5 border-none rounded-2xl py-5 px-5 text-2xl font-black focus:ring-2 focus:ring-[#04724D]/20 text-gray-900 dark:text-white tabular-nums"
                    />
                    <div class="absolute left-5 top-1/2 -translate-y-1/2 text-gray-400 font-black text-sm">{{ currency }}</div>
                  </div>
                </div>

                <!-- Change -->
                <transition name="fade">
                  <div v-if="changeDue > 0" class="flex items-center justify-between p-5 bg-yellow-50 dark:bg-yellow-900/10 rounded-2xl border border-yellow-200 dark:border-yellow-900/30">
                    <span class="text-xs font-black text-yellow-700 dark:text-yellow-400 uppercase tracking-wider font-['Cairo']">الفكة للعميل</span>
                    <span class="text-2xl font-black text-yellow-800 dark:text-yellow-200 tabular-nums">{{ fmt(changeDue) }} {{ currency }}</span>
                  </div>
                </transition>

                <!-- Account Select -->
                <div>
                  <label class="text-[10px] text-gray-400 font-black uppercase tracking-[0.25em] block mb-2">حساب الإيداع</label>
                  <select
                    v-model="selectedAccount"
                    class="w-full bg-gray-50 dark:bg-white/5 border-none rounded-2xl py-4 px-5 text-sm font-black focus:ring-2 focus:ring-[#04724D]/20 text-gray-700 dark:text-gray-200 font-['Cairo']"
                  >
                    <option v-for="acc in accounts" :key="acc.id" :value="acc.id">{{ acc.account_name }}</option>
                  </select>
                </div>

                <!-- Note -->
                <div>
                  <label class="text-[10px] text-gray-400 font-black uppercase tracking-[0.25em] block mb-2">ملاحظة العملية</label>
                  <input
                    v-model="paymentNote"
                    type="text"
                    placeholder="اختياري..."
                    class="w-full bg-gray-50 dark:bg-white/5 border-none rounded-2xl py-4 px-5 text-sm font-black focus:ring-2 focus:ring-[#04724D]/20 text-gray-700 dark:text-gray-200 font-['Cairo'] placeholder-gray-300"
                  />
                </div>

                <!-- Error -->
                <div v-if="checkoutError" class="bg-red-500/10 text-red-500 text-xs font-black p-4 rounded-2xl border border-red-500/20 font-['Cairo']">
                  {{ checkoutError }}
                </div>
              </div>
            </div>
          </div>

          <!-- Modal Footer -->
          <div class="p-6 bg-gray-50 dark:bg-[#1A1A1A] border-t border-gray-100 dark:border-gray-800 flex gap-4">
            <button @click="showPaymentModal = false" class="flex-1 py-4 bg-white dark:bg-white/5 text-gray-600 dark:text-gray-300 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-gray-100 transition-all border border-gray-200 dark:border-gray-800 font-['Cairo']">
              إلغاء
            </button>
            <button
              @click="checkout"
              :disabled="checkoutLoading || amountPaid < grandTotal"
              class="flex-[2] py-4 bg-[#04724D] hover:bg-[#058a5e] disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-2xl font-black text-sm uppercase tracking-[0.2em] shadow-xl shadow-[#04724D]/25 transition-all flex items-center justify-center gap-2 font-['Cairo']"
            >
              <span v-if="checkoutLoading" class="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin" />
              <template v-else>
                تأكيد الدفع
                <ArrowRight class="h-4 w-4" />
              </template>
            </button>
          </div>
        </div>
      </div>
    </transition>

    <!-- ── Main POS Layout ───────────────────────────────────────── -->
    <div class="flex flex-col lg:flex-row h-[calc(100vh-140px)] gap-6 p-4 md:p-6" dir="rtl">

      <!-- ══ LEFT: Product Panel ══════════════════════════════════ -->
      <div class="flex-1 flex flex-col min-w-0">

        <!-- Toolbar -->
        <div class="bg-white dark:bg-[#121212] rounded-[32px] p-5 shadow-sm border border-gray-100 dark:border-gray-800 mb-6 flex flex-wrap items-center gap-6">
          <!-- Search -->
          <div class="relative flex-1 min-w-[260px]">
            <span class="absolute inset-y-0 right-4 flex items-center text-gray-400">
              <MagnifyingGlassIcon class="h-5 w-5" />
            </span>
            <input
              id="pos-search"
              v-model="searchQuery"
              type="text"
              placeholder="البحث بالاسم أو الكود — F1"
              class="w-full bg-gray-50 dark:bg-white/5 border-none rounded-2xl py-4 pr-12 focus:ring-2 focus:ring-[#04724D]/20 transition-all font-black text-gray-800 dark:text-gray-200 font-['Cairo'] text-sm"
            />
          </div>

          <!-- Category Chips -->
          <div class="flex items-center gap-3 overflow-x-auto no-scrollbar py-1">
            <button
              @click="selectedCategory = null"
              :class="!selectedCategory ? 'bg-[#04724D] text-white shadow-xl shadow-[#04724D]/20' : 'bg-gray-50 dark:bg-white/5 text-gray-500 hover:bg-gray-100'"
              class="px-6 py-3 rounded-2xl text-xs font-black whitespace-nowrap transition-all font-['Cairo']"
            >
              الكل
            </button>
            <button
              v-for="cat in categories"
              :key="cat.id"
              @click="selectedCategory = cat.id"
              :class="selectedCategory === cat.id ? 'bg-[#04724D] text-white shadow-xl shadow-[#04724D]/20' : 'bg-gray-50 dark:bg-white/5 text-gray-500 hover:bg-gray-100'"
              class="px-6 py-3 rounded-2xl text-xs font-black whitespace-nowrap transition-all font-['Cairo']"
            >
              {{ cat.name }}
            </button>
          </div>
        </div>

        <!-- Product Grid -->
        <div class="flex-1 overflow-y-auto pr-2 custom-scrollbar">
          <!-- Loading skeleton -->
          <div v-if="isLoadingProducts" class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-6">
            <div v-for="i in 10" :key="i" class="animate-pulse bg-white dark:bg-[#121212] rounded-[32px] p-5 border border-gray-100 dark:border-gray-800 h-52" />
          </div>
          <!-- Empty -->
          <div v-else-if="products.length === 0" class="flex flex-col items-center justify-center h-64 text-gray-300 dark:text-gray-700 gap-4">
            <BoxIcon class="w-16 h-16 opacity-30" />
            <p class="font-black font-['Cairo']">لا توجد منتجات</p>
          </div>
          <!-- Grid -->
          <div v-else class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-6">
            <div
              v-for="product in products"
              :key="product.id"
              @click="addToCart(product)"
              class="relative bg-white dark:bg-[#121212] rounded-[40px] p-6 border border-gray-100/80 dark:border-white/5 hover:border-[#04724D]/40 hover:shadow-2xl hover:shadow-[#04724D]/10 transition-all duration-500 cursor-pointer group flex flex-col overflow-hidden"
              :class="{ 'opacity-60 grayscale-[0.5] pointer-events-none': isOutOfStock(product) }"
            >
              <!-- Stock badges -->
              <div
                v-if="isOutOfStock(product)"
                class="absolute top-6 left-6 z-10 px-3 py-1.5 rounded-xl text-[9px] font-black font-['Cairo'] shadow-sm bg-gray-900 text-white flex items-center gap-2"
              >
                <span class="w-1.5 h-1.5 bg-red-400 rounded-full" />
                نفد المخزون
              </div>
              <div
                v-else-if="product.qte <= (product.stock_alert || 5)"
                class="absolute top-6 left-6 z-10 px-3 py-1.5 rounded-xl text-[9px] font-black font-['Cairo'] shadow-sm bg-red-500 text-white flex items-center gap-2"
              >
                <span class="w-1.5 h-1.5 bg-white rounded-full animate-ping" />
                مخزون منخفض
              </div>

              <!-- Image -->
              <div class="aspect-[4/3] bg-gray-50/50 dark:bg-white/[0.03] rounded-[32px] mb-5 flex items-center justify-center overflow-hidden relative border border-gray-50 dark:border-white/5 shadow-inner">
                <img v-if="product.image && product.image !== 'no-image.png'" :src="'/images/products/' + product.image" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-1000" />
                <div v-else :class="getCategoryColor(product.category_id)" class="w-full h-full flex items-center justify-center">
                  <BoxIcon class="h-14 w-14 opacity-20 group-hover:scale-110 transition-transform duration-500" />
                </div>
                <!-- Stock overlay -->
                <div class="absolute bottom-3 right-3 bg-white/90 dark:bg-black/40 backdrop-blur-md px-3 py-1.5 rounded-xl text-[10px] font-black text-[#04724D] dark:text-[#3EFF8B] font-['Cairo'] border border-white/20 shadow-sm">
                  {{ product.qte }} <span class="text-[7px] opacity-70">{{ product.unit || 'قطعة' }}</span>
                </div>
              </div>

              <!-- Info -->
              <div class="flex flex-col flex-1">
                <div class="mb-3">
                  <h3 class="font-black text-gray-900 dark:text-white text-sm mb-1.5 font-['Cairo'] tracking-tight group-hover:text-[#04724D] transition-colors line-clamp-2 min-h-[2.5rem]">
                    {{ product.name }}
                  </h3>
                  <span class="text-[9px] font-black text-gray-400 font-mono bg-gray-50 dark:bg-white/5 px-2 py-0.5 rounded-md border border-gray-100 dark:border-white/5">
                    {{ product.code }}
                  </span>
                </div>
                <div class="mt-auto pt-4 border-t border-gray-50 dark:border-white/5 flex items-center justify-between">
                  <div class="flex flex-col">
                    <span class="text-[8px] text-gray-400 font-black uppercase mb-0.5">السعر</span>
                    <span class="text-[#04724D] dark:text-[#3EFF8B] font-black text-xl font-mono tabular-nums leading-none">
                      {{ fmt(product.price) }}
                      <span class="text-[10px] mr-0.5 font-['Cairo']">{{ currency }}</span>
                    </span>
                  </div>
                  <div class="bg-gray-50 dark:bg-white/10 p-3 rounded-2xl group-hover:bg-[#3EFF8B] group-hover:rotate-90 transition-all duration-500 shadow-sm">
                    <Plus class="h-5 w-5 text-gray-400 group-hover:text-[#04724D]" />
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ══ RIGHT: Cart Panel ════════════════════════════════════ -->
      <div class="w-full lg:w-[560px] flex flex-col min-w-0">
        <div class="bg-white dark:bg-[#121212] rounded-[48px] shadow-2xl shadow-[#04724D]/10 border border-gray-100 dark:border-gray-800 flex flex-col h-full overflow-hidden">

          <!-- Cart Header -->
          <div class="p-8 pb-6 border-b border-gray-100/50 dark:border-white/5 bg-gray-50/30 dark:bg-white/[0.01]">
            <div class="flex items-center justify-between mb-6">
              <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-[#04724D] rounded-[22px] flex items-center justify-center shadow-lg shadow-[#04724D]/20">
                  <ShoppingCartIcon class="h-7 w-7 text-white" />
                </div>
                <div>
                  <h2 class="text-xl font-black text-gray-900 dark:text-white font-['Cairo'] tracking-tight">سلة المبيعات</h2>
                  <span v-if="cart.length > 0" class="text-[10px] font-black text-[#04724D] uppercase tracking-widest opacity-60">{{ cart.length }} أصناف</span>
                  <span v-else class="text-[10px] font-black text-gray-400 uppercase tracking-widest">فارغة</span>
                </div>
              </div>
              <!-- Client Quick Stats -->
              <div class="flex gap-2">
                <div
                  class="px-4 py-2 rounded-2xl border flex flex-col items-end shadow-sm transition-all"
                  :class="isOverCreditLimit ? 'bg-red-50 dark:bg-red-500/10 border-red-200 dark:border-red-500/30' : 'bg-[#3EFF8B]/10 border-[#3EFF8B]/20'"
                >
                  <span class="text-[8px] font-black uppercase mb-0.5" :class="isOverCreditLimit ? 'text-red-600' : 'text-[#04724D]'">الرصيد</span>
                  <span class="text-xs font-black font-mono tabular-nums" :class="isOverCreditLimit ? 'text-red-700' : 'text-[#04724D]'">{{ fmt(clientData.net_balance || 0) }}</span>
                </div>
                <div class="bg-gray-50 dark:bg-white/5 border border-gray-100 dark:border-white/10 px-4 py-2 rounded-2xl flex flex-col items-end shadow-sm">
                  <span class="text-[8px] font-black text-gray-400 uppercase mb-0.5">النقاط</span>
                  <span class="text-xs font-black text-gray-700 dark:text-gray-300 font-mono tabular-nums">{{ fmt(clientData.points || 0) }}</span>
                </div>
              </div>
            </div>

            <!-- Client & Warehouse -->
            <div class="space-y-4">
              <div class="grid grid-cols-12 gap-3">
                <div class="col-span-11 relative group">
                  <div class="absolute inset-y-0 right-4 flex items-center pointer-events-none">
                    <User class="h-5 w-5 text-gray-400 group-focus-within:text-[#04724D]" />
                  </div>
                  <select
                    v-model="selectedClient"
                    class="w-full bg-white dark:bg-[#1A1A1A] border border-gray-100 dark:border-white/5 rounded-2xl py-4 pr-12 focus:ring-4 focus:ring-[#04724D]/10 focus:border-[#04724D]/40 appearance-none font-black text-gray-700 dark:text-gray-200 font-['Cairo'] text-sm transition-all shadow-sm"
                  >
                    <option v-for="client in clients" :key="client.id" :value="client.id">{{ client.name }}</option>
                  </select>
                  <div class="absolute inset-y-0 left-4 flex items-center text-[10px] text-gray-400 font-black font-mono tracking-wider">
                    {{ clientData.phone }}
                  </div>
                </div>
                <div class="col-span-1">
                  <button class="w-full h-full bg-[#3EFF8B] text-[#04724D] rounded-2xl hover:scale-105 active:scale-95 transition-all shadow-lg flex items-center justify-center">
                    <UserPlusIcon class="h-6 w-6" />
                  </button>
                </div>
              </div>
              <div class="relative group">
                <div class="absolute inset-y-0 right-4 flex items-center pointer-events-none">
                  <Store class="h-5 w-5 text-gray-400 group-focus-within:text-[#04724D]" />
                </div>
                <select
                  v-model="selectedWarehouse"
                  class="w-full bg-white dark:bg-[#1A1A1A] border border-gray-100 dark:border-white/5 rounded-2xl py-3.5 pr-12 focus:ring-4 focus:ring-[#04724D]/10 focus:border-[#04724D]/40 appearance-none font-black text-gray-700 dark:text-gray-200 font-['Cairo'] text-sm transition-all shadow-sm"
                >
                  <option v-for="wh in warehouses" :key="wh.id" :value="wh.id">{{ wh.name }}</option>
                </select>
              </div>
            </div>
          </div>

          <!-- Cart Items -->
          <div class="flex-1 overflow-y-auto p-8 space-y-5 custom-scrollbar">
            <!-- Empty State -->
            <div v-if="cart.length === 0" class="h-full flex flex-col items-center justify-center text-gray-300 dark:text-gray-700 opacity-40 space-y-8 py-20">
              <div class="bg-gray-50/50 dark:bg-white/5 p-12 rounded-[50px] relative">
                <div class="absolute inset-0 bg-[#04724D] blur-[100px] opacity-10 animate-pulse" />
                <ShoppingCartIcon class="h-24 w-24 relative z-10" />
              </div>
              <p class="font-black text-2xl font-['Cairo'] tracking-tight">ابدأ بإضافة المنتجات للسلة</p>
            </div>

            <!-- Item Rows -->
            <div
              v-for="(item, index) in cart"
              :key="item.id"
              class="relative overflow-hidden group p-5 rounded-[28px] bg-white dark:bg-white/[0.02] border border-gray-100 dark:border-white/5 hover:border-[#04724D]/30 transition-all duration-300"
            >
              <div class="flex items-start gap-5">
                <!-- Icon + Qty Badge -->
                <div class="relative flex-none">
                  <div class="h-16 w-16 bg-gray-50 dark:bg-[#1A1A1A] rounded-2xl flex items-center justify-center border border-gray-100 dark:border-gray-800 shadow-sm group-hover:bg-[#04724D]/5 transition-colors">
                    <BoxIcon class="h-8 w-8 text-gray-400 group-hover:text-[#04724D] transition-colors" />
                  </div>
                  <div class="absolute -top-2 -right-2 bg-[#04724D] text-white text-[10px] font-black w-6 h-6 rounded-lg flex items-center justify-center border-2 border-white dark:border-[#121212] shadow-lg">
                    {{ item.quantity }}
                  </div>
                </div>

                <!-- Info -->
                <div class="flex-1 min-w-0 flex flex-col justify-between self-stretch">
                  <div class="flex justify-between items-start gap-4 mb-2">
                    <div class="flex-1 min-w-0">
                      <h4 class="font-black text-gray-900 dark:text-white text-sm truncate font-['Cairo'] leading-none mb-1.5">{{ item.name }}</h4>
                      <div class="flex items-center gap-2">
                        <span class="text-[9px] font-black text-gray-400 font-mono tracking-tighter">{{ item.code }}</span>
                        <span class="w-1 h-1 rounded-full bg-gray-200 dark:bg-gray-700" />
                        <span class="text-[10px] font-black text-[#04724D] dark:text-[#3EFF8B]">{{ item.unit }}</span>
                      </div>
                    </div>
                    <div class="flex flex-col items-end flex-none">
                      <div class="text-gray-900 dark:text-white font-black text-base font-mono tabular-nums">
                        {{ fmt((item.price * item.quantity) - (item.discount_type === 'fixed' ? item.discount : item.price * item.quantity * item.discount / 100)) }}
                      </div>
                      <div v-if="item.discount > 0" class="text-[9px] line-through text-red-400 font-black">
                        {{ fmt(item.price * item.quantity) }}
                      </div>
                    </div>
                  </div>

                  <!-- Controls -->
                  <div class="flex items-center justify-between pt-3 border-t border-gray-50 dark:border-white/5">
                    <div class="flex items-center bg-gray-50 dark:bg-white/5 rounded-xl p-0.5 gap-1 border border-gray-100/50 dark:border-white/5">
                      <button @click="updateQuantity(index, -1)" class="w-8 h-8 flex items-center justify-center hover:bg-white hover:text-red-500 transition-all text-gray-400 rounded-lg">
                        <MinusIcon class="h-4 w-4" />
                      </button>
                      <div class="w-10 text-center font-black text-sm text-[#04724D] dark:text-[#3EFF8B] font-mono tabular-nums">{{ item.quantity }}</div>
                      <button @click="updateQuantity(index, 1)" class="w-8 h-8 flex items-center justify-center hover:bg-white hover:text-[#04724D] transition-all text-gray-400 rounded-lg">
                        <PlusIcon class="h-4 w-4" />
                      </button>
                    </div>
                    <div class="flex items-center gap-3">
                      <!-- Discount input -->
                      <div class="flex items-center bg-gray-50 dark:bg-white/5 px-2.5 py-1.5 rounded-xl border border-gray-100 dark:border-white/5 transition-colors">
                        <TagIcon class="w-3.5 h-3.5 text-gray-300 mr-1 shrink-0" />
                        <input
                          :value="item.discount"
                          @input="e => updateItemDiscount(index, e.target.value)"
                          type="number"
                          min="0"
                          class="w-14 bg-transparent border-none p-0 text-xs font-black focus:ring-0 text-[#04724D] dark:text-[#3EFF8B] placeholder-gray-300"
                          placeholder="0"
                        />
                        <button
                          @click="item.discount_type = item.discount_type === 'fixed' ? 'percent' : 'fixed'"
                          class="mr-1 h-5 px-1.5 rounded-md bg-white dark:bg-white/10 text-[9px] font-black text-gray-500 hover:text-[#04724D] transition-all border border-gray-100 dark:border-white/10"
                        >
                          {{ item.discount_type === 'fixed' ? 'ج.م' : '%' }}
                        </button>
                      </div>
                      <button @click="removeFromCart(index)" class="p-2 text-gray-300 hover:text-red-500 transition-all hover:scale-110 active:scale-95">
                        <TrashIcon class="h-5 w-5" />
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Totals & Checkout -->
          <div class="p-8 bg-white dark:bg-[#0A0A0A] border-t border-gray-100 dark:border-white/5 rounded-t-[56px] shadow-[0_-20px_40px_rgba(0,0,0,0.02)] relative z-20 space-y-6">
            <!-- Totals -->
            <div class="grid grid-cols-2 gap-x-10 gap-y-3 font-['Cairo'] pb-2">
              <div class="flex flex-col">
                <span class="text-[10px] text-gray-400 font-black uppercase tracking-widest mb-1">المجموع الفرعي</span>
                <span class="text-gray-900 dark:text-white font-black text-lg font-mono tabular-nums">{{ fmt(subtotal) }} <span class="text-[10px] opacity-30">{{ currency }}</span></span>
              </div>
              <div class="flex flex-col items-end">
                <span class="text-[10px] text-gray-400 font-black uppercase tracking-widest mb-1">الضريبة ({{ taxRate }}%)</span>
                <span class="text-gray-900 dark:text-white font-black text-lg font-mono tabular-nums">{{ fmt(taxAmount) }} <span class="text-[10px] opacity-30">{{ currency }}</span></span>
              </div>
              <div class="col-span-2 pt-5 mt-1 border-t border-gray-100 dark:border-white/5 flex justify-between items-end">
                <div>
                  <span class="text-[11px] text-[#04724D] dark:text-[#3EFF8B] font-black uppercase tracking-[0.2em] block mb-1">الإجمالي النهائي</span>
                  <span class="text-4xl font-black text-gray-900 dark:text-white tabular-nums tracking-tighter">
                    {{ fmt(grandTotal) }}
                    <span class="text-sm font-['Cairo'] ml-1 opacity-20">{{ currency }}</span>
                  </span>
                </div>
                <div v-if="isOverCreditLimit" class="bg-red-500/10 text-red-500 text-[9px] font-black px-3 py-1.5 rounded-xl border border-red-500/20 animate-bounce font-['Cairo']">
                  تجاوز الحد الائتماني!
                </div>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="grid grid-cols-2 gap-4">
              <!-- Cash: open modal -->
              <button
                @click="openPaymentModal"
                :disabled="cart.length === 0 || isOverCreditLimit"
                class="group relative h-24 bg-[#04724D] text-white rounded-[32px] font-black text-xl shadow-2xl shadow-[#04724D]/30 hover:scale-[1.02] active:scale-[0.98] transition-all flex flex-col items-center justify-center gap-1 font-['Cairo'] disabled:opacity-40 overflow-hidden"
              >
                <div class="absolute inset-0 bg-white/10 translate-y-full group-hover:translate-y-0 transition-transform duration-500" />
                <div class="relative flex items-center gap-3">
                  <Wallet class="h-7 w-7 text-[#3EFF8B]" />
                  دفع نقدي
                </div>
                <span class="relative text-[9px] opacity-50 font-black tracking-[0.2em]">SHORTCUT F2</span>
              </button>

              <!-- Credit: checkout directly (deferred) -->
              <button
                @click="checkoutCredit"
                :disabled="checkoutLoading || cart.length === 0 || isOverCreditLimit"
                class="group relative h-24 bg-white dark:bg-white/5 text-gray-900 dark:text-white border-2 border-gray-100 dark:border-white/10 rounded-[32px] font-black text-xl hover:bg-gray-50 hover:border-[#04724D]/30 transition-all flex flex-col items-center justify-center gap-1 font-['Cairo'] disabled:opacity-40 overflow-hidden"
              >
                <div class="relative flex items-center gap-3">
                  <span v-if="checkoutLoading" class="w-6 h-6 border-2 border-gray-400/30 border-t-[#04724D] rounded-full animate-spin" />
                  <RectangleStackIcon v-else class="h-7 w-7 text-[#04724D] dark:text-[#3EFF8B] opacity-60" />
                  بيع آجل
                </div>
                <span class="relative text-[9px] opacity-30 font-black tracking-[0.2em]">SHORTCUT F3</span>
              </button>
            </div>

            <!-- Shortcuts guide -->
            <div class="flex justify-center gap-8 pt-2 opacity-20 hover:opacity-100 transition-opacity duration-700">
              <div v-for="(sc, i) in [['F1','بحث'],['F2','كاش'],['F4','مسح']]" :key="i" class="flex items-center gap-2 text-[10px] font-black font-['Cairo'] group cursor-default" :class="i === 2 ? 'text-red-500' : ''">
                <span class="bg-gray-100 dark:bg-white/10 px-2 py-1 rounded-lg text-[9px] font-mono group-hover:bg-[#04724D] group-hover:text-white transition-colors">{{ sc[0] }}</span>
                {{ sc[1] }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #04724D18; border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #04724D33; }
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

/* Toast slide-up */
.slide-up-enter-active, .slide-up-leave-active { transition: all 0.4s cubic-bezier(0.16,1,0.3,1); }
.slide-up-enter-from, .slide-up-leave-to { opacity: 0; transform: translate(-50%, 2rem); }

/* Modal fade */
.modal-fade-enter-active, .modal-fade-leave-active { transition: opacity 0.25s ease; }
.modal-fade-enter-from, .modal-fade-leave-to { opacity: 0; }
.modal-fade-enter-active .relative, .modal-fade-leave-active .relative { transition: transform 0.3s cubic-bezier(0.16,1,0.3,1); }
.modal-fade-enter-from .relative, .modal-fade-leave-to .relative { transform: scale(0.95); }

/* Fade for change indicator */
.fade-enter-active, .fade-leave-active { transition: all 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; transform: translateY(-5px); }
</style>
