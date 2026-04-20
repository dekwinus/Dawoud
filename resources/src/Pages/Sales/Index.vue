<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref, onMounted, computed } from 'vue';
import { 
  ShoppingCart, 
  Search, 
  Filter, 
  Download, 
  Eye, 
  Printer,
  Trash2, 
  Calendar,
  User,
  MapPin,
  Banknote as Banknotes,
  Clock,
  CheckCircle,
  AlertCircle
} from 'lucide-vue-next';
import axios from 'axios';
import { router, Head } from '@inertiajs/vue3';

const props = defineProps({
  warehouses: Array,
  clients: Array,
  accounts: Array,
  payment_methods: Array,
});

const sales = ref([]);
const totalRows = ref(0);
const loading = ref(false);
const filters = ref({
  search: '',
  client_id: '',
  warehouse_id: '',
  page: 1,
  limit: 10,
  SortField: 'id',
  SortType: 'desc'
});
const showDetails = ref(false);
const selectedSale = ref(null);
const saleLines = ref([]);
const deletingId = ref(null);

const fetchSales = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/sales', { params: filters.value });
        sales.value = response.data.sales;
        totalRows.value = response.data.totalRows;
    } catch (e) {
        console.error("Failed to fetch sales", e);
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchSales();
});

const handleFilter = () => {
    filters.value.page = 1;
    fetchSales();
};

const formatCurrency = (value) => {
    const numeric = Number(value || 0);
    return new Intl.NumberFormat('ar-EG', { style: 'currency', currency: 'EGP' }).format(numeric);
};

const totalSalesAmount = computed(() => sales.value.reduce((sum, s) => sum + Number(s.GrandTotal || 0), 0));
const paidInvoicesCount = computed(() => sales.value.filter((s) => s.payment_status === 'paid').length);
const partialInvoicesCount = computed(() => sales.value.filter((s) => s.payment_status === 'partial').length);
const totalDueAmount = computed(() => sales.value.reduce((sum, s) => sum + Number(s.due || 0), 0));

const getStatusColor = (status) => {
  switch (status) {
    case 'completed': return 'bg-green-100 text-green-700';
    case 'pending': return 'bg-yellow-100 text-yellow-700';
    case 'ordered': return 'bg-blue-100 text-blue-700';
    default: return 'bg-gray-100 text-gray-700';
  }
};

const getStatusLabel = (status) => {
  switch (status) {
    case 'completed': return 'مكتمل';
    case 'pending': return 'قيد الانتظار';
    case 'ordered': return 'مطلوب';
    default: return status;
  }
};

const getPaymentStatusColor = (status) => {
  switch (status) {
    case 'paid': return 'bg-[#3EFF8B]/10 text-[#04724D]';
    case 'partial': return 'bg-orange-50 text-orange-600';
    case 'unpaid': return 'bg-red-50 text-red-600';
    default: return 'bg-gray-100 text-gray-700';
  }
};

const getPaymentStatusLabel = (status) => {
  switch (status) {
    case 'paid': return 'مدفوع';
    case 'partial': return 'مدفوع جزئياً';
    case 'unpaid': return 'غير مدفوع';
    default: return status;
  }
};

const openDetails = async (sale) => {
  selectedSale.value = sale;
  showDetails.value = true;
  saleLines.value = [];
  try {
    const { data } = await axios.get(`/api/get_Products_by_sale/${sale.id}`);
    saleLines.value = data?.details || [];
  } catch (e) {
    console.error('Failed to load sale details', e);
  }
};

const openPrint = (sale) => {
  window.open(`/api/sale_print_html/${sale.id}`, '_blank');
};

const removeSale = async (sale) => {
  if (!confirm(`حذف الفاتورة ${sale.Ref}؟`)) return;
  deletingId.value = sale.id;
  try {
    await axios.delete(`/api/sales/${sale.id}`);
    await fetchSales();
  } finally {
    deletingId.value = null;
  }
};

const exportCSV = async () => {
  try {
    const { data } = await axios.get('/api/sales', { params: { ...filters.value, limit: 9999, page: 1 } });
    const rows = data.sales || [];
    const headers = ['Ref', 'date', 'client_name', 'warehouse_name', 'GrandTotal', 'paid_amount', 'due', 'payment_status', 'statut'];
    const csv = [headers.join(','), ...rows.map(r => headers.map(h => JSON.stringify(r[h] ?? '')).join(','))].join('\n');
    const blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' });
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = `sales-${filters.value.SortField}-${Date.now()}.csv`;
    a.click();
    URL.revokeObjectURL(a.href);
  } catch (e) { console.error(e); }
};

</script>

<template>
  <AdminLayout>
    <Head title="قائمة المبيعات" />
    <div class="p-8 space-y-8" dir="rtl">
      <!-- Header -->
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
          <h1 class="text-xl font-black text-gray-800 dark:text-white font-['Cairo'] tracking-tight">إدارة المبيعات</h1>
          <p class="text-gray-500 dark:text-gray-400 font-medium">تتبع جميع المعاملات وفواتير المبيعات</p>
        </div>
        <div class="flex items-center gap-3">
            <button @click="exportCSV" class="bg-white dark:bg-white/5 text-gray-600 dark:text-gray-300 px-5 py-3 rounded-2xl font-bold text-sm border border-gray-200 dark:border-gray-800 hover:bg-gray-50 transition-all flex items-center gap-2 font-['Cairo']">
                <Download class="h-4 w-4" />
                تصدير CSV
            </button>
            <button @click="router.visit('/admin/pos')" class="bg-[#04724D] hover:bg-[#058a5e] text-white px-6 py-3 rounded-2xl font-black text-sm shadow-xl shadow-[#04724D]/25 transition-all flex items-center gap-2 font-['Cairo']">
                <ShoppingCart class="h-4 w-4" />
                نقطة البيع (POS)
            </button>
        </div>
      </div>

      <!-- Quick Summary Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
          <div class="bg-white dark:bg-[#121212] p-6 rounded-[32px] border border-gray-100 dark:border-gray-800 shadow-sm relative overflow-hidden group">
              <div class="relative z-10">
                <div class="w-10 h-10 rounded-xl bg-gray-50 dark:bg-white/5 flex items-center justify-center mb-4">
                  <Banknotes class="h-5 w-5 text-[#04724D]" />
                </div>
                <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mb-1 font-['Cairo']">إجمالي المبيعات</p>
                <h3 class="text-2xl font-black text-gray-800 dark:text-white">{{ formatCurrency(totalSalesAmount) }}</h3>
              </div>
          </div>
          <div class="bg-white dark:bg-[#121212] p-6 rounded-[32px] border border-gray-100 dark:border-gray-800 shadow-sm">
                <div class="w-10 h-10 rounded-xl bg-gray-50 dark:bg-white/5 flex items-center justify-center mb-4">
                  <CheckCircle class="h-5 w-5 text-green-500" />
                </div>
                <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mb-1 font-['Cairo']">المدفوعة بالكامل</p>
                <h3 class="text-2xl font-black text-gray-800 dark:text-white">{{ paidInvoicesCount }} فاتورة</h3>
          </div>
          <div class="bg-white dark:bg-[#121212] p-6 rounded-[32px] border border-gray-100 dark:border-gray-800 shadow-sm">
                <div class="w-10 h-10 rounded-xl bg-gray-50 dark:bg-white/5 flex items-center justify-center mb-4">
                  <Clock class="h-5 w-5 text-orange-500" />
                </div>
                <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mb-1 font-['Cairo']">مدفوعات جزئية</p>
                <h3 class="text-2xl font-black text-gray-800 dark:text-white">{{ partialInvoicesCount }} فاتورة</h3>
          </div>
          <div class="bg-white dark:bg-[#121212] p-6 rounded-[32px] border border-gray-100 dark:border-gray-800 shadow-sm">
                <div class="w-10 h-10 rounded-xl bg-gray-50 dark:bg-white/5 flex items-center justify-center mb-4">
                  <AlertCircle class="h-5 w-5 text-red-500" />
                </div>
                <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mb-1 font-['Cairo']">مبالغ مستحقة</p>
                <h3 class="text-2xl font-black text-gray-800 dark:text-white">{{ formatCurrency(totalDueAmount) }}</h3>
          </div>
      </div>

      <!-- Sales Table Container -->
      <div class="bg-white dark:bg-[#121212] rounded-[32px] border border-gray-100 dark:border-gray-800 shadow-sm overflow-hidden">
        <!-- Filter Bar -->
        <div class="p-6 border-b border-gray-100 dark:border-gray-800 flex flex-wrap items-center gap-4">
           <div class="flex-1 min-w-[300px] relative">
              <Search class="absolute right-4 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
              <input 
                v-model="filters.search" 
                @input="handleFilter"
                type="text" 
                placeholder="ابحث برقم المرجع، العميل، أو الحالة..." 
                class="w-full pr-12 pl-4 py-3 bg-gray-50 dark:bg-white/5 border-none rounded-xl text-sm focus:ring-2 focus:ring-[#04724D]/20 font-medium font-['Cairo']"
              />
           </div>

           <div class="flex items-center gap-3">
              <select v-model="filters.client_id" @change="handleFilter" class="bg-gray-50 dark:bg-white/5 border-none rounded-xl px-4 py-3 text-sm font-bold text-gray-600 dark:text-gray-300 focus:ring-2 focus:ring-[#04724D]/20 font-['Cairo']">
                  <option value="">جميع العملاء</option>
                  <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.name }}</option>
              </select>
              
              <select v-model="filters.warehouse_id" @change="handleFilter" class="bg-gray-50 dark:bg-white/5 border-none rounded-xl px-4 py-3 text-sm font-bold text-gray-600 dark:text-gray-300 focus:ring-2 focus:ring-[#04724D]/20 font-['Cairo']">
                  <option value="">جميع المخازن</option>
                  <option v-for="w in warehouses" :key="w.id" :value="w.id">{{ w.name }}</option>
              </select>

              <button class="p-3 bg-gray-50 dark:bg-white/5 text-gray-400 rounded-xl hover:text-gray-600 transition-colors">
                  <Filter class="h-4 w-4" />
              </button>
           </div>
        </div>

        <!-- Table Content -->
        <div class="overflow-x-auto">
          <table class="w-full text-right border-collapse">
             <thead>
               <tr class="text-[11px] font-bold text-gray-400 uppercase tracking-wider bg-gray-50/50 dark:bg-white/[0.02] font-['Cairo']">
                  <th class="px-8 py-5">الموعد والمرجع</th>
                  <th class="px-6 py-5">العميل</th>
                  <th class="px-6 py-5">المستودع</th>
                  <th class="px-6 py-5">الإجمالي</th>
                  <th class="px-6 py-5">المدفوع</th>
                  <th class="px-6 py-5">المتبقي</th>
                  <th class="px-6 py-5">حالة الدفع</th>
                  <th class="px-8 py-5 text-left">الإجراءات</th>
               </tr>
             </thead>
             <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                <tr v-if="loading" v-for="i in 5" :key="i" class="animate-pulse">
                   <td v-for="j in 8" :key="j" class="px-6 py-6"><div class="h-4 bg-gray-100 dark:bg-white/5 rounded w-full"></div></td>
                </tr>

                <tr v-else-if="sales.length === 0" class="text-center">
                    <td colspan="8" class="py-20 text-gray-400 font-['Cairo'] text-lg">لم يتم العثور على مبيعات</td>
                </tr>

                <tr v-for="sale in sales" :key="sale.id" class="group hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition-colors font-['Cairo']">
                   <td class="px-8 py-5">
                      <div class="flex flex-col">
                        <span class="text-xs font-black text-gray-800 dark:text-white mb-0.5 line-clamp-1 truncate font-mono uppercase">{{ sale.Ref }}</span>
                        <div class="flex items-center gap-1.5 text-[10px] text-gray-400 font-bold">
                          <Calendar class="h-3 w-3" />
                          {{ sale.date }}
                        </div>
                      </div>
                   </td>
                   <td class="px-6 py-5">
                      <div class="flex items-center gap-3">
                         <div class="w-8 h-8 rounded-full bg-gray-50 dark:bg-white/5 flex items-center justify-center text-[#04724D]">
                            <User class="h-4 w-4" />
                         </div>
                         <span class="text-sm font-black text-gray-700 dark:text-gray-300">{{ sale.client_name }}</span>
                      </div>
                   </td>
                   <td class="px-6 py-5 font-['Cairo'] text-xs text-gray-500 flex items-center gap-1.5">
                      <MapPin class="h-3 w-3" />
                      {{ sale.warehouse_name }}
                   </td>
                   <td class="px-6 py-5 text-sm font-black text-gray-800 dark:text-white">{{ formatCurrency(sale.GrandTotal) }}</td>
                   <td class="px-6 py-5 text-sm font-bold text-[#04724D]">{{ formatCurrency(sale.paid_amount) }}</td>
                   <td class="px-6 py-5 text-sm font-bold text-red-500">{{ formatCurrency(sale.due) }}</td>
                   <td class="px-6 py-5">
                      <span class="px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest inline-flex items-center" :class="getPaymentStatusColor(sale.payment_status)">
                        {{ getPaymentStatusLabel(sale.payment_status) }}
                      </span>
                   </td>
                   <td class="px-8 py-5 text-left">
                      <div class="flex items-center justify-start gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                         <button title="عرض التفاصيل" @click="openDetails(sale)" class="p-2.5 bg-white dark:bg-[#1A1A1A] text-gray-400 hover:text-[#04724D] rounded-xl border border-gray-100 dark:border-gray-800 transition-all shadow-sm">
                            <Eye class="h-4 w-4" />
                         </button>
                         <button title="طباعة الفاتورة" @click="openPrint(sale)" class="p-2.5 bg-white dark:bg-[#1A1A1A] text-gray-400 hover:text-[#04724D] rounded-xl border border-gray-100 dark:border-gray-800 transition-all shadow-sm">
                            <Printer class="h-4 w-4" />
                         </button>
                         <button title="حذف الفاتورة" :disabled="deletingId === sale.id" @click="removeSale(sale)" class="p-2.5 bg-white dark:bg-[#1A1A1A] text-gray-400 hover:text-red-500 rounded-xl border border-gray-100 dark:border-gray-800 transition-all shadow-sm disabled:opacity-50">
                            <Trash2 class="h-4 w-4" />
                         </button>
                      </div>
                   </td>
                </tr>
             </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="p-8 border-t border-gray-50 dark:border-gray-800 flex items-center justify-between font-['Cairo']">
           <div class="text-sm text-gray-400 font-bold">
              إجمالي النتائج: <span class="text-[#04724D] font-black px-1">{{ totalRows }}</span> صف
           </div>
           <div class="flex items-center gap-2">
               <button @click="filters.page--; fetchSales()" :disabled="filters.page === 1" class="px-6 py-2.5 rounded-xl bg-gray-50 dark:bg-white/5 text-gray-400 font-black text-sm hover:text-gray-800 transition-all disabled:opacity-30 border border-gray-100 dark:border-gray-800">السابق</button>
               <button @click="filters.page++; fetchSales()" class="px-8 py-2.5 rounded-xl bg-[#04724D] text-white font-black text-sm hover:bg-[#058a5e] transition-all shadow-lg shadow-[#04724D]/20">التالي</button>
           </div>
        </div>
      </div>

      <div v-if="showDetails" class="fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-[#121212] rounded-2xl w-full max-w-5xl p-6 space-y-4 max-h-[90vh] overflow-y-auto">
          <div class="flex items-center justify-between">
            <h2 class="text-xl font-black">تفاصيل الفاتورة {{ selectedSale?.Ref }}</h2>
            <button @click="showDetails = false" class="px-3 py-1 rounded-lg bg-gray-100 text-sm font-bold">إغلاق</button>
          </div>

          <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
            <div class="bg-gray-50 rounded-xl p-3"><span class="text-gray-500">العميل:</span> {{ selectedSale?.client_name }}</div>
            <div class="bg-gray-50 rounded-xl p-3"><span class="text-gray-500">المستودع:</span> {{ selectedSale?.warehouse_name }}</div>
            <div class="bg-gray-50 rounded-xl p-3"><span class="text-gray-500">الإجمالي:</span> {{ formatCurrency(selectedSale?.GrandTotal) }}</div>
            <div class="bg-gray-50 rounded-xl p-3"><span class="text-gray-500">المتبقي:</span> {{ formatCurrency(selectedSale?.due) }}</div>
          </div>

          <div class="overflow-x-auto border rounded-xl">
            <table class="w-full text-sm">
              <thead class="bg-gray-50">
                <tr>
                  <th class="p-3 text-right">المنتج</th>
                  <th class="p-3 text-right">الكمية</th>
                  <th class="p-3 text-right">السعر</th>
                  <th class="p-3 text-right">الإجمالي</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="saleLines.length === 0">
                  <td colspan="4" class="p-4 text-center text-gray-400">لا توجد تفاصيل متاحة</td>
                </tr>
                <tr v-for="(line, idx) in saleLines" :key="idx" class="border-t">
                  <td class="p-3">{{ line.name || line.product_name || line.code || '—' }}</td>
                  <td class="p-3">{{ line.quantity || line.qty || 0 }}</td>
                  <td class="p-3">{{ formatCurrency(line.Net_price || line.price || 0) }}</td>
                  <td class="p-3">{{ formatCurrency(line.total || line.Total || (Number(line.quantity || 0) * Number(line.Net_price || line.price || 0))) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<style scoped>
/* Page-specific styling if needed */
</style>
