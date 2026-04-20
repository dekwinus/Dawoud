<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { 
  ShoppingCart as ShoppingCartIcon, 
  Search as MagnifyingGlassIcon, 
  UserPlus as UserPlusIcon, 
  Tag as TagIcon, 
  Trash2 as TrashIcon, 
  CreditCard as CreditCardIcon, 
  RotateCcw as ArrowPathIcon,
  LayoutGrid as Squares2X2Icon,
  List as ListBulletIcon,
  ChevronRight,
  Minus as MinusIcon,
  Plus as PlusIcon,
  Layers as RectangleStackIcon,
  Package as BoxIcon,
  User,
  Store,
  Wallet
} from 'lucide-vue-next';

const props = defineProps({
  warehouses: Array,
  clients: Array,
  categories: Array,
  brands: Array,
  pos_settings: Object,
  settings: Object,
  payment_methods: Array,
  accounts: Array,
});

// State
const cart = ref([]);
const searchQuery = ref('');
const selectedCategory = ref(null);
const selectedWarehouse = ref(props.warehouses[0]?.id || null);
const selectedClient = ref(props.clients[0]?.id || null);
const isLoadingProducts = ref(false);
const products = ref([]);
const checkoutLoading = ref(false);
const checkoutError = ref(null);

const selectedPaymentMethod = ref(props.payment_methods[0]?.id || null);
const selectedAccount = ref(props.accounts[0]?.id || null);
const paymentNote = ref('');

const clientData = computed(() => props.clients.find(c => c.id === selectedClient.value) || {});
const isOverCreditLimit = computed(() => {
  if (!clientData.value.credit_limit || clientData.value.credit_limit <= 0) return false;
  return (Number(clientData.value.net_balance || 0) + grandTotal.value) > clientData.value.credit_limit;
});

// Keyboard Shortcuts
onMounted(() => {
  window.addEventListener('keydown', (e) => {
    if (e.key === 'F1') {
      e.preventDefault();
      document.querySelector('input[type="text"]')?.focus();
    }
    if (e.key === 'F2') {
      e.preventDefault();
      if (cart.value.length > 0) checkout('cash');
    }
    if (e.key === 'F3') {
      e.preventDefault();
      if (cart.value.length > 0 && !isOverCreditLimit.value) checkout('credit');
    }
    if (e.key === 'F4') {
      e.preventDefault();
      cart.value = [];
    }
  });
});

// Helper for category colors
const getCategoryColor = (catId) => {
  const colors = [
    'bg-emerald-500/10 text-emerald-600',
    'bg-blue-500/10 text-blue-600',
    'bg-amber-500/10 text-amber-600',
    'bg-purple-500/10 text-purple-600',
    'bg-pink-500/10 text-pink-600',
    'bg-cyan-500/10 text-cyan-600'
  ];
  return colors[catId % colors.length];
};

// Totals
const subtotal = computed(() => cart.value.reduce((acc, item) => {
  const lineSubtotal = item.price * item.quantity;
  const lineDiscount = item.discount_type === 'fixed' ? item.discount : (lineSubtotal * item.discount / 100);
  return acc + (lineSubtotal - lineDiscount);
}, 0));
const taxAmount = computed(() => subtotal.value * (props.settings?.tax_rate || 0) / 100);
const grandTotal = computed(() => subtotal.value + taxAmount.value);

// Fetch products from API
const fetchProducts = async () => {
  isLoadingProducts.value = true;
  try {
    const { data } = await axios.get('/api/products', {
      params: {
        search: searchQuery.value,
        category_id: selectedCategory.value || '',
        warehouse_id: selectedWarehouse.value || '',
        limit: 60, page: 1
      }
    });
    products.value = data.products || [];
  } catch (e) { console.error(e); }
  finally { isLoadingProducts.value = false; }
};

watch([searchQuery, selectedCategory, selectedWarehouse], fetchProducts);

// Cart Management
const addToCart = (product) => {
  const existing = cart.value.find(item => item.id === product.id);
  if (existing) { existing.quantity++; }
  else { 
    cart.value.push({ 
      ...product, 
      quantity: 1, 
      discount: 0, 
      discount_type: 'fixed',
      price_type: 'retail' 
    }); 
  }
};

const removeFromCart = (index) => cart.value.splice(index, 1);

const updateQuantity = (index, delta) => {
  if (cart.value[index].quantity + delta > 0) cart.value[index].quantity += delta;
};

const updateItemDiscount = (index, val) => {
  cart.value[index].discount = parseFloat(val) || 0;
};

const togglePriceType = (index) => {
  const item = cart.value[index];
  item.price_type = item.price_type === 'retail' ? 'wholesale' : 'retail';
  // If wholesale exists in product data, use it (need to ensure products API returns it)
};

const checkout = async (paymentType) => {
  if (cart.value.length === 0) return;
  checkoutLoading.value = true;
  checkoutError.value = null;
  try {
    const details = cart.value.map(item => {
      const lineSubtotal = item.price * item.quantity;
      const lineDiscount = item.discount_type === 'fixed' ? item.discount : (lineSubtotal * item.discount / 100);
      
      return {
        product_id: item.id,
        product_variant_id: item.variant_id || null,
        Unit_price: item.price, // FIXED CASE
        subtotal: lineSubtotal - lineDiscount,
        quantity: item.quantity,
        discount: item.discount, 
        discount_Method: item.discount_type === 'fixed' ? '2' : '1',
        tax_percent: props.settings?.tax_rate || 0,
        tax_method: 'exclusive', 
        sale_unit_id: item.unit_sale_id || item.unit_id || null,
      };
    });

    await axios.post('/api/sales', {
      Ref: 'POS-' + Date.now(),
      warehouse_id: selectedWarehouse.value,
      client_id: selectedClient.value,
      statut: 'completed',
      payment_statut: paymentType === 'cash' ? 'paid' : 'unpaid',
      payments: [{
        amount: grandTotal.value,
        payment_method_id: selectedPaymentMethod.value,
        account_id: selectedAccount.value
      }],
      discount: 0, 
      shipping: 0, 
      tax_rate: props.settings?.tax_rate || 0,
      TaxNet: taxAmount.value,
      discount_type: 'fixed',
      GrandTotal: grandTotal.value,
      date: new Date().toISOString().split('T')[0],
      note: '',
      payment_note: paymentNote.value,
      details,
    });
    cart.value = [];
    paymentNote.value = '';
    alert('تم تسجيل البيع بنجاح');
  } catch (e) {
    checkoutError.value = e.response?.data?.message || 'تعذر تسجيل البيع';
  } finally { checkoutLoading.value = false; }
};

// Layout
const viewType = ref('grid');

onMounted(fetchProducts);

</script>

<template>
  <AdminLayout :hideSidebar="true">
    <Head title="نقطة البيع - DawPOS" />

    <div class="flex flex-col lg:flex-row h-[calc(100vh-140px)] gap-6 p-4 md:p-6" dir="rtl">
      <!-- Left side: Products (Responsive Grid) -->
      <div class="flex-1 flex flex-col min-w-0">
        <!-- POS Toolbar -->
        <div class="bg-white dark:bg-[#121212] rounded-[32px] p-5 shadow-sm border border-gray-100 dark:border-gray-800 mb-6 flex flex-wrap items-center gap-6">
          <!-- Search -->
          <div class="relative flex-1 min-w-[280px]">
            <span class="absolute inset-y-0 right-4 flex items-center text-gray-400">
              <MagnifyingGlassIcon class="h-5 w-5" />
            </span>
            <input 
              v-model="searchQuery"
              type="text" 
              placeholder="البحث عن المنتجات بالاسم أو الكود..."
              class="w-full bg-gray-50 dark:bg-white/5 border-none rounded-2xl py-4 pr-12 focus:ring-2 focus:ring-[#04724D]/20 transition-all font-black text-gray-800 dark:text-gray-200 font-['Cairo'] text-sm"
            />
          </div>

          <!-- Category Filter -->
          <div class="flex items-center gap-3 overflow-x-auto no-scrollbar py-2">
            <button 
              @click="selectedCategory = null"
              :class="!selectedCategory ? 'bg-[#04724D] text-white shadow-xl shadow-[#04724D]/20' : 'bg-gray-50 dark:bg-white/5 text-gray-500 hover:bg-gray-100 dark:hover:bg-white/10'"
              class="px-8 py-3.5 rounded-2xl text-xs font-black whitespace-nowrap transition-all font-['Cairo']"
            >
              الكل
            </button>
            <button 
              v-for="cat in categories" 
              :key="cat.id"
              @click="selectedCategory = cat.id"
              :class="selectedCategory === cat.id ? 'bg-[#04724D] text-white shadow-xl shadow-[#04724D]/20' : 'bg-gray-50 dark:bg-white/5 text-gray-500 hover:bg-gray-100 dark:hover:bg-white/10'"
              class="px-8 py-3.5 rounded-2xl text-xs font-black whitespace-nowrap transition-all font-['Cairo']"
            >
              {{ cat.name }}
            </button>
          </div>
        </div>

        <!-- Products Grid -->
        <div class="flex-1 overflow-y-auto pr-2 custom-scrollbar">
          <!-- Loading skeleton -->
          <div v-if="isLoadingProducts" class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-6">
            <div v-for="i in 10" :key="i" class="animate-pulse bg-white dark:bg-[#121212] rounded-[32px] p-5 border border-gray-100 dark:border-gray-800 h-52"></div>
          </div>
          <!-- Empty state -->
          <div v-else-if="products.length === 0" class="flex flex-col items-center justify-center h-64 text-gray-300 dark:text-gray-700 gap-4">
            <BoxIcon class="w-16 h-16 opacity-30" />
            <p class="font-black font-['Cairo']">لا توجد منتجات</p>
          </div>
          <!-- Real products -->
          <div v-else class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-6">
            <div 
              v-for="product in products" :key="product.id"
              @click="addToCart(product)"
              class="bg-white dark:bg-[#121212] rounded-[32px] p-5 border border-gray-100 dark:border-gray-800 hover:border-[#04724D] hover:shadow-2xl hover:shadow-[#04724D]/10 transition-all cursor-pointer group flex flex-col relative overflow-hidden"
              :class="{'opacity-60': product.qte <= 0}"
            >
              <!-- Stock Alert Badge -->
              <div 
                class="absolute top-4 right-4 z-10 px-3 py-1 rounded-full text-[9px] font-black font-['Cairo'] shadow-sm"
                :class="product.qte <= product.stock_alert ? 'bg-red-500 text-white animate-pulse' : 'bg-[#3EFF8B] text-[#04724D]'"
              >
                {{ product.qte <= product.stock_alert ? 'تنبيه المخزون' : 'متوفر' }}
              </div>

              <div class="aspect-square bg-gray-50 dark:bg-white/5 rounded-2xl mb-4 flex items-center justify-center overflow-hidden relative">
                <img v-if="product.image && product.image !== 'no-image.png'" :src="'/images/products/' + product.image" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700" />
                <div v-else :class="getCategoryColor(product.category_id)" class="w-full h-full flex flex-col items-center justify-center gap-2 transition-opacity">
                  <BoxIcon class="h-12 w-12 opacity-40" />
                  <span class="text-[8px] font-black uppercase tracking-tighter opacity-60">DawPOS AGRI</span>
                </div>
              </div>

              <h3 class="font-black text-gray-800 dark:text-white text-sm mb-1 truncate font-['Cairo'] tracking-tight">{{ product.name }}</h3>
              <div class="flex items-center justify-between gap-2 mb-3">
                 <p class="text-[10px] text-gray-400 font-mono font-black opacity-60">{{ product.code }}</p>
                 <span class="text-[9px] bg-gray-100 dark:bg-white/5 px-2 py-0.5 rounded-md font-black text-gray-500">{{ product.unit || 'قطعة' }}</span>
              </div>
              
              <div class="mt-auto flex items-center justify-between pt-2">
                <span class="text-[#04724D] dark:text-[#3EFF8B] font-black text-lg font-['Cairo'] tracking-tighter">
                  {{ Number(product.price).toLocaleString() }}
                  <span class="text-[9px] text-gray-400 mr-1 font-bold">ج.م</span>
                </span>
                <div class="bg-[#3EFF8B]/10 p-2 rounded-xl group-hover:bg-[#3EFF8B] transition-colors duration-300">
                  <PlusIcon class="h-4 w-4 text-[#04724D]" />
                </div>
              </div>
              <div class="absolute bottom-16 left-4 bg-black/60 backdrop-blur-md px-2.5 py-1.5 rounded-xl text-[10px] font-black text-white font-['Cairo'] border border-white/10">
                {{ product.qte }} <span class="text-[7px] opacity-70">{{ product.unit }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Right side: Cart & Checkout (Widened to 560px and optimized padding) -->
      <div class="w-full lg:w-[560px] flex flex-col min-w-0 transition-all duration-500">
        <div class="bg-white dark:bg-[#121212] rounded-[48px] shadow-2xl shadow-[#04724D]/10 border border-gray-100 dark:border-gray-800 flex flex-col h-full overflow-hidden relative">
          <!-- Cart Header -->
          <div class="p-8 pb-6 border-b border-gray-50 dark:border-gray-800/50">
            <div class="flex items-center justify-between mb-6">
              <h2 class="text-xl font-black text-gray-900 dark:text-white flex items-center gap-3 font-['Cairo'] tracking-tight">
                <div class="w-12 h-12 bg-[#04724D]/10 rounded-2xl flex items-center justify-center">
                   <ShoppingCartIcon class="h-7 w-7 text-[#04724D] dark:text-[#3EFF8B]" />
                </div>
                سلة المبيعات
                <span v-if="cart.length > 0" class="bg-brand/10 text-brand text-sm font-black px-3 py-1 rounded-full">{{ cart.length }}</span>
              </h2>
              <div class="flex gap-2">
                <div 
                  class="px-4 py-2 rounded-2xl border flex flex-col items-end transition-all duration-500"
                  :class="isOverCreditLimit ? 'bg-red-50 dark:bg-red-500/10 border-red-200 dark:border-red-500/30' : 'bg-amber-50 dark:bg-amber-500/10 border-amber-100 dark:border-amber-500/20'"
                >
                   <span class="text-[8px] font-black uppercase" :class="isOverCreditLimit ? 'text-red-600' : 'text-amber-600'">المتبقي</span>
                   <span class="text-xs font-black font-mono tabular-nums" :class="isOverCreditLimit ? 'text-red-700' : 'text-amber-700'">{{ Number(clientData.net_balance || 0).toLocaleString() }}</span>
                </div>
                <div 
                   v-if="clientData.credit_limit > 0"
                   class="bg-blue-50 dark:bg-blue-500/10 px-4 py-2 rounded-2xl border border-blue-100 dark:border-blue-500/20 flex flex-col items-end"
                >
                   <span class="text-[8px] font-black text-blue-600 dark:text-blue-400 uppercase">الحد</span>
                   <span class="text-xs font-black text-blue-700 dark:text-blue-300 font-mono tabular-nums">{{ Number(clientData.credit_limit).toLocaleString() }}</span>
                </div>
                <div class="bg-purple-50 dark:bg-purple-500/10 px-4 py-2 rounded-2xl border border-purple-100 dark:border-purple-500/20 flex flex-col items-end">
                   <span class="text-[8px] font-black text-purple-600 dark:text-purple-400 uppercase">النقاط</span>
                   <span class="text-xs font-black text-purple-700 dark:text-purple-300 font-mono tabular-nums">{{ Number(clientData.points || 0).toLocaleString() }}</span>
                </div>
              </div>
            </div>

            <!-- Client & Warehouse Select -->
            <div class="grid grid-cols-12 gap-3">
              <div class="col-span-11 relative group cursor-pointer">
                 <div class="absolute inset-y-0 right-4 flex items-center pointer-events-none">
                   <User class="h-5 w-5 text-gray-400 group-focus-within:text-[#04724D] transition-colors" />
                 </div>
                 <select v-model="selectedClient" class="w-full bg-gray-50 dark:bg-white/5 border-none rounded-2xl py-4 pr-12 focus:ring-2 focus:ring-[#04724D]/20 appearance-none font-black text-gray-700 dark:text-gray-200 font-['Cairo'] text-sm transition-all duration-300">
                   <option v-for="client in clients" :key="client.id" :value="client.id">{{ client.name }}</option>
                 </select>
                 <div class="absolute inset-y-0 left-4 flex items-center text-[10px] text-gray-400 font-black">
                   {{ clientData.phone }}
                 </div>
              </div>
              <div class="col-span-1 flex justify-end">
                <button class="bg-[#3EFF8B] p-3.5 rounded-2xl hover:scale-105 transition-all shadow-lg shadow-[#3EFF8B]/20 h-full flex items-center justify-center">
                  <UserPlusIcon class="h-6 w-6 text-[#04724D]" />
                </button>
              </div>

              <div class="col-span-12 relative group cursor-pointer">
                 <div class="absolute inset-y-0 right-4 flex items-center pointer-events-none">
                   <Store class="h-5 w-5 text-gray-400 group-focus-within:text-[#04724D] transition-colors" />
                 </div>
                 <select v-model="selectedWarehouse" class="w-full bg-gray-50 dark:bg-white/5 border-none rounded-2xl py-3 pr-12 focus:ring-2 focus:ring-[#04724D]/20 appearance-none font-black text-gray-700 dark:text-gray-200 font-['Cairo'] text-sm">
                   <option v-for="wh in warehouses" :key="wh.id" :value="wh.id">{{ wh.name }}</option>
                 </select>
              </div>
            </div>
          </div>

          <!-- Cart Items -->
          <div class="flex-1 overflow-y-auto p-6 md:p-8 space-y-4 custom-scrollbar">
            <div v-if="cart.length === 0" class="h-full flex flex-col items-center justify-center text-gray-300 dark:text-gray-700 opacity-40 space-y-6 py-12">
               <div class="bg-gray-50 dark:bg-white/5 p-10 rounded-[40px] relative">
                 <div class="absolute inset-0 bg-[#04724D] blur-[80px] opacity-10"></div>
                 <ShoppingCartIcon class="h-20 w-20 relative z-10" />
               </div>
               <p class="font-black text-xl font-['Cairo']">السلة فارغة حالياً</p>
            </div>

            <!-- Item Row - More horizontal and spacious -->
            <div v-for="(item, index) in cart" :key="item.id" class="flex flex-col gap-3 p-4 rounded-[32px] bg-white dark:bg-white/[0.03] hover:bg-gray-50 dark:hover:bg-white/5 transition-all group border border-gray-50 dark:border-white/5 hover:border-[#04724D]/20">
              <div class="flex gap-4">
                <div class="h-14 w-14 bg-gray-50 dark:bg-[#1A1A1A] shadow-inner border border-gray-100 dark:border-gray-800 rounded-2xl flex items-center justify-center flex-none relative">
                  <BoxIcon class="h-7 w-7 text-gray-300 dark:text-gray-600" />
                  <div class="absolute -top-1 -right-1 bg-[#04724D] text-white text-[8px] font-black w-5 h-5 rounded-full flex items-center justify-center border-2 border-white dark:border-[#121212]">
                    {{ item.quantity }}
                  </div>
                </div>
                <div class="flex-1 min-w-0 flex flex-col justify-between py-0.5">
                  <div class="flex justify-between items-start gap-3">
                     <div class="flex flex-col">
                        <h4 class="font-black text-gray-800 dark:text-white text-sm truncate font-['Cairo'] leading-tight">{{ item.name }}</h4>
                        <div class="flex items-center gap-2 mt-1">
                          <span class="text-[9px] font-black text-gray-400 font-mono tracking-tighter">{{ item.code }}</span>
                          <span class="w-1 h-1 rounded-full bg-gray-300"></span>
                          <span class="text-[9px] font-black text-[#04724D]">{{ item.unit }}</span>
                        </div>
                     </div>
                     <div class="flex flex-col items-end">
                        <span class="font-black text-gray-900 dark:text-white text-base font-mono tabular-nums whitespace-nowrap">
                          {{ ((item.price * item.quantity) - (item.discount_type === 'fixed' ? item.discount : (item.price * item.quantity * item.discount / 100))).toLocaleString() }}
                        </span>
                        <span v-if="item.discount > 0" class="text-[8px] line-through text-red-400 font-black">{{ (item.price * item.quantity).toLocaleString() }}</span>
                     </div>
                  </div>
                </div>
              </div>

              <!-- Item Controls Layer -->
              <div class="flex items-center justify-between pt-2 border-t border-gray-100 dark:border-white/5 mt-1">
                <div class="flex items-center bg-gray-100 dark:bg-white/10 rounded-xl p-0.5 gap-1">
                  <button @click="updateQuantity(index, -1)" class="w-8 h-8 flex items-center justify-center hover:text-red-500 transition-colors text-gray-400 bg-white dark:bg-night-900 rounded-lg shadow-sm"><MinusIcon class="h-3.5 w-3.5" /></button>
                  <span class="w-8 text-center font-black text-sm text-[#04724D] dark:text-[#3EFF8B] font-mono tabular-nums">{{ item.quantity }}</span>
                  <button @click="updateQuantity(index, 1)" class="w-8 h-8 flex items-center justify-center hover:text-[#04724D] transition-colors text-gray-400 bg-white dark:bg-night-900 rounded-lg shadow-sm"><PlusIcon class="h-3.5 w-3.5" /></button>
                </div>

                <div class="flex items-center gap-3">
                  <!-- Per-item Discount Input -->
                  <div class="flex items-center gap-1.5 bg-gray-50 dark:bg-white/5 px-2 py-1.5 rounded-xl border border-gray-100 dark:border-white/10">
                    <TagIcon class="w-3 h-3 text-gray-400" />
                    <input 
                      :value="item.discount"
                      @input="e => updateItemDiscount(index, e.target.value)"
                      type="number" 
                      placeholder="خصم"
                      class="w-12 bg-transparent border-none p-0 text-[10px] font-black focus:ring-0 text-[#04724D] placeholder-gray-300"
                    />
                    <button 
                      @click="item.discount_type = (item.discount_type === 'fixed' ? 'percent' : 'fixed')"
                      class="text-[9px] font-black text-gray-400 hover:text-[#04724D] transition-colors"
                    >
                      {{ item.discount_type === 'fixed' ? 'ج.م' : '%' }}
                    </button>
                  </div>

                  <button @click="removeFromCart(index)" class="p-2 text-gray-300 hover:text-red-500 transition-all">
                    <TrashIcon class="h-4 w-4" />
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Totals & Checkout -->
          <div class="p-8 bg-gray-50/50 dark:bg-white/[0.01] border-t border-gray-100 dark:border-gray-800 rounded-t-[56px] space-y-6">
            <div class="space-y-3 font-['Cairo']">
              <div class="flex justify-between items-center text-xs">
                <span class="text-gray-400 font-bold uppercase tracking-widest">المجموع الفرعي</span>
                <span class="text-gray-900 dark:text-white font-black text-sm font-mono tabular-nums">{{ subtotal.toLocaleString() }} ج.م</span>
              </div>
              <div class="flex justify-between items-center text-xs">
                <span class="text-gray-400 font-bold uppercase tracking-widest">الضريبة ({{ settings?.tax_rate || 0 }}%)</span>
                <span class="text-gray-900 dark:text-white font-black text-sm font-mono tabular-nums">{{ taxAmount.toLocaleString() }} ج.م</span>
              </div>
              <div class="pt-5 mt-2 border-t border-gray-200/50 dark:border-gray-800 flex justify-between items-center">
                <span class="text-gray-900 dark:text-white font-black text-lg">الإجمالي</span>
                <span class="text-[#04724D] dark:text-[#3EFF8B] font-black text-2xl tracking-tighter tabular-nums">{{ grandTotal.toLocaleString() }} <span class="text-[9px] mr-1 opacity-50 font-bold uppercase">EGP</span></span>
              </div>
            </div>

            <!-- Payment & Account Selection -->
            <div class="space-y-4 pt-4 border-t border-gray-100 dark:border-gray-800">
               <div class="grid grid-cols-2 gap-3">
                 <div class="relative group cursor-pointer">
                    <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none">
                      <Wallet class="h-4 w-4 text-gray-400 group-focus-within:text-[#04724D] transition-colors" />
                    </div>
                    <select v-model="selectedPaymentMethod" class="w-full bg-gray-50 dark:bg-white/5 border-none rounded-xl py-3 pr-10 focus:ring-2 focus:ring-[#04724D]/20 appearance-none font-black text-gray-700 dark:text-gray-200 font-['Cairo'] text-[11px]">
                      <option v-for="pm in payment_methods" :key="pm.id" :value="pm.id">{{ pm.name }}</option>
                    </select>
                 </div>
                 <div class="relative group cursor-pointer">
                    <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none">
                      <ArrowPathIcon class="h-4 w-4 text-gray-400 group-focus-within:text-[#04724D] transition-colors" />
                    </div>
                    <select v-model="selectedAccount" class="w-full bg-gray-50 dark:bg-white/5 border-none rounded-xl py-3 pr-10 focus:ring-2 focus:ring-[#04724D]/20 appearance-none font-black text-gray-700 dark:text-gray-200 font-['Cairo'] text-[11px]">
                      <option v-for="acc in accounts" :key="acc.id" :value="acc.id">{{ acc.account_name }}</option>
                    </select>
                 </div>
                  <div class="relative group cursor-pointer mt-3">
                  <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none">
                    <ListBulletIcon class="h-4 w-4 text-gray-400 group-focus-within:text-[#04724D] transition-colors" />
                  </div>
                  <input 
                    v-model="paymentNote"
                    type="text" 
                    placeholder="ملاحظات الدفع (اختياري)..."
                    class="w-full bg-gray-50 dark:bg-white/5 border-none rounded-xl py-3 pr-10 focus:ring-2 focus:ring-[#04724D]/20 transition-all font-black text-gray-700 dark:text-gray-200 font-['Cairo'] text-[11px]"
                  />
               </div>
            </div>
            </div>

            <div v-if="checkoutError" class="text-red-500 text-xs font-bold bg-red-50 dark:bg-red-500/10 px-4 py-2 rounded-xl text-center font-['Cairo']">{{ checkoutError }}</div>
            <div class="grid grid-cols-2 gap-4">
              <button @click="checkout('cash')" :disabled="checkoutLoading || cart.length === 0" class="bg-[#04724D] text-white py-5 rounded-[24px] font-black text-lg shadow-2xl shadow-[#04724D]/20 hover:scale-[1.02] active:scale-[0.98] transition-all flex flex-col items-center justify-center gap-1 font-['Cairo'] disabled:opacity-50">
                <div class="flex items-center gap-3">
                  <span v-if="checkoutLoading" class="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                  <Wallet v-else class="h-6 w-6 text-[#3EFF8B]" />
                  دفع نقدي
                </div>
                <span class="text-[8px] opacity-40 font-black">F2 Shortcut</span>
              </button>
              <button @click="checkout('credit')" :disabled="checkoutLoading || cart.length === 0" class="border-2 border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 py-5 rounded-[24px] font-black text-lg hover:bg-gray-50 dark:hover:bg-white/5 transition-all flex flex-col items-center justify-center gap-1 font-['Cairo'] disabled:opacity-50">
                <div class="flex items-center gap-3">
                  <RectangleStackIcon class="h-6 w-6 opacity-60" />
                  آجل (Ajel)
                </div>
                <span class="text-[8px] opacity-40 font-black">F3 Shortcut</span>
              </button>
            </div>

            <!-- Global Shortcuts Guide -->
            <div class="flex justify-center gap-6 pt-2 opacity-30 group hover:opacity-100 transition-opacity duration-500">
               <div class="flex items-center gap-2 text-[9px] font-black font-['Cairo']">
                 <span class="bg-gray-200 dark:bg-white/10 px-1.5 py-0.5 rounded text-[8px] font-mono">F1</span> بحث
               </div>
               <div class="flex items-center gap-2 text-[9px] font-black font-['Cairo']">
                 <span class="bg-gray-200 dark:bg-white/10 px-1.5 py-0.5 rounded text-[8px] font-mono">F2</span> كاش
               </div>
               <div class="flex items-center gap-2 text-[9px] font-black font-['Cairo']">
                 <span class="bg-gray-200 dark:bg-white/10 px-1.5 py-0.5 rounded text-[8px] font-mono">F3</span> آجل
               </div>
               <div class="flex items-center gap-2 text-[9px] font-black font-['Cairo']">
                 <span class="bg-gray-200 dark:bg-white/10 px-1.5 py-0.5 rounded text-[8px] font-mono">F4</span> تفريغ
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
.custom-scrollbar::-webkit-scrollbar-thumb { background: #04724D11; border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #04724D22; }
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
