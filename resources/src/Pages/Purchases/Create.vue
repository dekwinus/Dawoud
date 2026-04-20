<script setup>
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import axios from 'axios';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import {
  Plus, Trash2, Search, ShoppingBag, ChevronRight
} from 'lucide-vue-next';

const props = defineProps({
  suppliers:       { type: Array, default: () => [] },
  warehouses:      { type: Array, default: () => [] },
  accounts:        { type: Array, default: () => [] },
  payment_methods: { type: Array, default: () => [] },
  products:        { type: Array, default: () => [] },
});

const saving = ref(false);
const saveError = ref(null);
const productSearch = ref('');

const form = ref({
  date:             new Date().toISOString().split('T')[0],
  provider_id:      props.suppliers[0]?.id || '',
  warehouse_id:     props.warehouses[0]?.id || '',
  account_id:       props.accounts[0]?.id || '',
  payment_method:   props.payment_methods[0]?.name || 'cash',
  statut:           'received',
  payment_statut:   'paid',
  shipping:         0,
  discount:         0,
  note:             '',
  details:          [],
});

const filteredProducts = computed(() => {
  const q = productSearch.value.toLowerCase();
  if (!q) return props.products.slice(0, 30);
  return props.products.filter(p =>
    p.name?.toLowerCase().includes(q) || p.code?.toLowerCase().includes(q)
  ).slice(0, 30);
});

const addProduct = (product) => {
  const existing = form.value.details.find(d => d.product_id === product.id);
  if (existing) { existing.quantity++; recalcLine(existing); return; }
  form.value.details.push({
    product_id: product.id,
    name:       product.name,
    code:       product.code,
    unit_cost:  Number(product.cost || 0),
    quantity:   1,
    discount:   0,
    tax_percent: 0,
    total:      Number(product.cost || 0),
  });
};

const recalcLine = (line) => {
  line.total = (line.unit_cost * line.quantity) - Number(line.discount || 0);
};

const removeLine = (index) => form.value.details.splice(index, 1);

const subtotal   = computed(() => form.value.details.reduce((s, d) => s + Number(d.total || 0), 0));
const grandTotal = computed(() => subtotal.value + Number(form.value.shipping || 0) - Number(form.value.discount || 0));

const formatCurrency = (v) =>
  new Intl.NumberFormat('ar-EG', { style: 'currency', currency: 'EGP' }).format(v);

const submit = async () => {
  if (!form.value.provider_id || !form.value.warehouse_id) {
    saveError.value = 'يرجى اختيار المورد والمخزن';
    return;
  }
  if (form.value.details.length === 0) {
    saveError.value = 'أضف منتجاً واحداً على الأقل';
    return;
  }
  saving.value = true;
  saveError.value = null;
  try {
    await axios.post('/api/purchases', {
      Ref:            'PUR-' + Date.now(),
      date:           form.value.date,
      provider_id:    form.value.provider_id,
      warehouse_id:   form.value.warehouse_id,
      account_id:     form.value.account_id,
      statut:         form.value.statut,
      payment_statut: form.value.payment_statut,
      shipping:       Number(form.value.shipping || 0),
      discount:       Number(form.value.discount || 0),
      discount_type:  'fixed',
      TaxNet:         0,
      tax_rate:       0,
      GrandTotal:     grandTotal.value,
      note:           form.value.note,
      details: form.value.details.map(d => ({
        product_id:          d.product_id,
        product_variant_id:  null,
        Unit_cost:           d.unit_cost,
        TotalPrice:          d.total,
        quantity:            d.quantity,
        discount:            Number(d.discount || 0),
        tax_percent:         Number(d.tax_percent || 0),
        tax_method:          'exclusive',
        purchase_unit_id:    null,
      })),
    });
    router.visit('/admin/purchases');
  } catch (err) {
    saveError.value = err.response?.data?.message
      || Object.values(err.response?.data?.errors || {}).flat().join(' | ')
      || 'تعذر الحفظ';
  } finally {
    saving.value = false;
  }
};
</script>

<template>
  <AdminLayout>
    <Head title="إضافة مشتريات - DawPOS" />
    <div class="p-6 md:p-10 space-y-8 font-['Cairo']" dir="rtl">

      <!-- Header -->
      <div class="flex items-center gap-4">
        <button @click="router.visit('/admin/purchases')" class="p-2 rounded-xl bg-gray-100 dark:bg-white/5 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 transition-all">
          <ChevronRight class="w-5 h-5" />
        </button>
        <div>
          <h1 class="text-2xl font-black text-gray-900 dark:text-white">إضافة مشتريات جديدة</h1>
          <p class="text-sm text-gray-400 font-medium">تسجيل فاتورة شراء جديدة</p>
        </div>
      </div>

      <div v-if="saveError" class="text-red-600 text-sm font-bold bg-red-50 dark:bg-red-500/10 px-5 py-3 rounded-2xl">{{ saveError }}</div>

      <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">

        <!-- Left: Product search + items table -->
        <div class="xl:col-span-2 space-y-6">

          <!-- Product Search -->
          <div class="bg-white dark:bg-[#121212] rounded-[28px] border border-gray-100 dark:border-gray-800 p-6 space-y-4">
            <h2 class="text-sm font-black text-gray-700 dark:text-gray-200 uppercase tracking-widest">البحث وإضافة المنتجات</h2>
            <div class="relative">
              <Search class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
              <input v-model="productSearch" type="text" placeholder="ابحث بالاسم أو الكود..." class="w-full pr-11 pl-4 py-3 bg-gray-50 dark:bg-white/5 border-none rounded-xl text-sm dark:text-gray-200" />
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 max-h-64 overflow-y-auto">
              <button
                v-for="p in filteredProducts" :key="p.id"
                @click="addProduct(p)"
                class="flex items-start gap-3 p-3 rounded-xl bg-gray-50 dark:bg-white/5 hover:bg-[#04724D]/5 dark:hover:bg-[#04724D]/10 hover:border-[#04724D]/20 border border-transparent transition-all text-right"
              >
                <div class="flex-1 min-w-0">
                  <div class="text-xs font-black text-gray-800 dark:text-gray-100 truncate">{{ p.name }}</div>
                  <div class="text-[10px] text-gray-400 font-mono">{{ p.code }}</div>
                  <div class="text-xs font-black text-[#04724D] dark:text-[#3EFF8B]">{{ formatCurrency(p.cost) }}</div>
                </div>
                <Plus class="w-4 h-4 text-[#04724D] flex-none mt-0.5" />
              </button>
            </div>
          </div>

          <!-- Items Table -->
          <div class="bg-white dark:bg-[#121212] rounded-[28px] border border-gray-100 dark:border-gray-800 overflow-hidden">
            <div class="p-6 border-b border-gray-50 dark:border-gray-800">
              <h2 class="text-sm font-black text-gray-700 dark:text-gray-200 uppercase tracking-widest">أصناف الفاتورة</h2>
            </div>
            <div class="overflow-x-auto">
              <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-white/[0.02]">
                  <tr class="text-[10px] font-black text-gray-400 uppercase tracking-widest">
                    <th class="px-5 py-3 text-right">المنتج</th>
                    <th class="px-5 py-3 text-right">التكلفة</th>
                    <th class="px-5 py-3 text-right">الكمية</th>
                    <th class="px-5 py-3 text-right">خصم</th>
                    <th class="px-5 py-3 text-right">الإجمالي</th>
                    <th class="px-5 py-3"></th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                  <tr v-if="form.details.length === 0">
                    <td colspan="6" class="py-12 text-center text-gray-400 text-sm">لم تُضف أي منتجات بعد</td>
                  </tr>
                  <tr v-for="(line, i) in form.details" :key="i" class="group">
                    <td class="px-5 py-3">
                      <div class="font-black text-gray-800 dark:text-gray-100 text-xs">{{ line.name }}</div>
                      <div class="text-[10px] text-gray-400 font-mono">{{ line.code }}</div>
                    </td>
                    <td class="px-5 py-3">
                      <input v-model.number="line.unit_cost" @input="recalcLine(line)" type="number" min="0" step="0.01"
                        class="w-24 rounded-lg bg-gray-50 dark:bg-white/5 border-none text-xs font-black dark:text-gray-200 p-2" />
                    </td>
                    <td class="px-5 py-3">
                      <input v-model.number="line.quantity" @input="recalcLine(line)" type="number" min="1"
                        class="w-20 rounded-lg bg-gray-50 dark:bg-white/5 border-none text-xs font-black dark:text-gray-200 p-2" />
                    </td>
                    <td class="px-5 py-3">
                      <input v-model.number="line.discount" @input="recalcLine(line)" type="number" min="0" step="0.01"
                        class="w-20 rounded-lg bg-gray-50 dark:bg-white/5 border-none text-xs font-black dark:text-gray-200 p-2" />
                    </td>
                    <td class="px-5 py-3 font-black text-[#04724D] dark:text-[#3EFF8B] text-xs font-mono">{{ formatCurrency(line.total) }}</td>
                    <td class="px-5 py-3">
                      <button @click="removeLine(i)" class="opacity-0 group-hover:opacity-100 p-1.5 text-gray-300 hover:text-red-500 transition-all rounded-lg hover:bg-red-50 dark:hover:bg-red-500/10">
                        <Trash2 class="w-4 h-4" />
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Right: Form + totals -->
        <div class="space-y-6">

          <!-- Purchase Info -->
          <div class="bg-white dark:bg-[#121212] rounded-[28px] border border-gray-100 dark:border-gray-800 p-6 space-y-5">
            <h2 class="text-sm font-black text-gray-700 dark:text-gray-200 uppercase tracking-widest">بيانات الفاتورة</h2>

            <div class="space-y-2">
              <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">التاريخ</label>
              <input v-model="form.date" type="date" class="w-full rounded-xl bg-gray-50 dark:bg-white/5 border-none p-3 text-sm font-black dark:text-gray-200" />
            </div>
            <div class="space-y-2">
              <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">المورد *</label>
              <select v-model="form.provider_id" class="w-full rounded-xl bg-gray-50 dark:bg-white/5 border-none p-3 text-sm font-black dark:text-gray-200">
                <option v-for="s in suppliers" :key="s.id" :value="s.id">{{ s.name }}</option>
              </select>
            </div>
            <div class="space-y-2">
              <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">المخزن *</label>
              <select v-model="form.warehouse_id" class="w-full rounded-xl bg-gray-50 dark:bg-white/5 border-none p-3 text-sm font-black dark:text-gray-200">
                <option v-for="w in warehouses" :key="w.id" :value="w.id">{{ w.name }}</option>
              </select>
            </div>
            <div class="space-y-2">
              <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">حالة الطلب</label>
              <select v-model="form.statut" class="w-full rounded-xl bg-gray-50 dark:bg-white/5 border-none p-3 text-sm font-black dark:text-gray-200">
                <option value="ordered">مطلوب</option>
                <option value="received">تم الاستلام</option>
                <option value="pending">معلق</option>
              </select>
            </div>
            <div class="space-y-2">
              <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">حالة الدفع</label>
              <select v-model="form.payment_statut" class="w-full rounded-xl bg-gray-50 dark:bg-white/5 border-none p-3 text-sm font-black dark:text-gray-200">
                <option value="paid">مدفوع</option>
                <option value="partial">جزئي</option>
                <option value="unpaid">غير مدفوع</option>
              </select>
            </div>
            <div class="space-y-2">
              <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">ملاحظات</label>
              <textarea v-model="form.note" rows="2" class="w-full rounded-xl bg-gray-50 dark:bg-white/5 border-none p-3 text-sm font-black dark:text-gray-200 resize-none"></textarea>
            </div>
          </div>

          <!-- Totals -->
          <div class="bg-white dark:bg-[#121212] rounded-[28px] border border-gray-100 dark:border-gray-800 p-6 space-y-4">
            <h2 class="text-sm font-black text-gray-700 dark:text-gray-200 uppercase tracking-widest">الإجماليات</h2>
            <div class="flex justify-between text-sm text-gray-500 dark:text-gray-400">
              <span>المجموع الفرعي</span>
              <span class="font-black text-gray-800 dark:text-gray-100 font-mono">{{ formatCurrency(subtotal) }}</span>
            </div>
            <div class="flex items-center justify-between gap-3">
              <label class="text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap">شحن</label>
              <input v-model.number="form.shipping" type="number" min="0" step="0.01"
                class="w-28 rounded-xl bg-gray-50 dark:bg-white/5 border-none p-2 text-sm font-black dark:text-gray-200 text-left font-mono" />
            </div>
            <div class="flex items-center justify-between gap-3">
              <label class="text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap">خصم</label>
              <input v-model.number="form.discount" type="number" min="0" step="0.01"
                class="w-28 rounded-xl bg-gray-50 dark:bg-white/5 border-none p-2 text-sm font-black dark:text-gray-200 text-left font-mono" />
            </div>
            <div class="pt-4 border-t border-gray-100 dark:border-gray-800 flex justify-between items-center">
              <span class="font-black text-gray-900 dark:text-white">الإجمالي الكلي</span>
              <span class="text-xl font-black text-[#04724D] dark:text-[#3EFF8B] font-mono">{{ formatCurrency(grandTotal) }}</span>
            </div>
          </div>

          <!-- Submit -->
          <button @click="submit" :disabled="saving" class="w-full py-4 rounded-2xl bg-[#04724D] hover:bg-[#058a5e] text-white font-black text-sm shadow-xl shadow-[#04724D]/25 transition-all disabled:opacity-60 flex items-center justify-center gap-2">
            <span v-if="saving" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
            <ShoppingBag v-else class="w-4 h-4" />
            {{ saving ? 'جاري الحفظ...' : 'حفظ الفاتورة' }}
          </button>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
