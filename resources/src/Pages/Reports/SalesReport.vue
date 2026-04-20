<script setup>
import { Head } from '@inertiajs/vue3';
import { onMounted, ref, computed, watch } from 'vue';
import axios from 'axios';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { 
  ShoppingCart as ShoppingCartIcon,
  Search as MagnifyingGlassIcon,
  ChevronLeft as ChevronLeftIcon,
  ChevronRight as ChevronRightIcon,
  Filter as FunnelIcon,
  Calendar as CalendarIcon,
  Warehouse as BuildingStorefrontIcon,
  User as UserIcon,
  FileDown as ArrowDownTrayIcon,
  BarChart3 as ChartBarIcon,
  ArrowUpRight as ArrowUpRightIcon,
  DollarSign as CurrencyDollarIcon
} from 'lucide-vue-next';

const props = defineProps({
  warehouses: Array,
  clients: Array,
});

const loading = ref(true);
const sales = ref([]);
const totalRows = ref(0);
const perPage = ref(10);
const page = ref(1);
const search = ref('');

const filters = ref({
  warehouse_id: '',
  client_id: '',
  statut: '',
  payment_statut: '',
  from: new Date(new Date().setDate(new Date().getDate() - 30)).toISOString().split('T')[0],
  to: new Date().toISOString().split('T')[0],
  filter: '' // for special filters like 'due_soon'
});

const loadSales = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/admin/reports/sales-data', {
      params: {
        page: page.value,
        limit: perPage.value,
        search: search.value,
        SortField: 'id',
        SortType: 'desc',
        ...filters.value
      }
    });
    sales.value = response.data.sales;
    totalRows.value = response.data.totalRows;
  } catch (error) {
    console.error('Failed to load sales report:', error);
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  loadSales();
});

// Watchers for filtering
watch([page, perPage, filters], () => {
  loadSales();
}, { deep: true });

watch(search, (newVal) => {
  if (newVal.length > 2 || newVal.length === 0) {
    page.value = 1;
    loadSales();
  }
});

const formatCurrency = (value) => {
  return new Intl.NumberFormat('ar-EG', { 
    style: 'currency', 
    currency: 'EGP',
    maximumFractionDigits: 0
  }).format(value);
};

const getStatusClass = (status) => {
  const base = 'px-4 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-widest border font-["Cairo"]';
  switch (status?.toLowerCase()) {
    case 'completed':
    case 'paid':
      return `${base} bg-green-50 text-green-700 border-green-200 dark:bg-green-500/10 dark:text-green-400 dark:border-green-500/20`;
    case 'pending':
    case 'partial':
      return `${base} bg-orange-50 text-orange-700 border-orange-200 dark:bg-orange-500/10 dark:text-orange-400 dark:border-orange-500/20`;
    case 'ordered':
    case 'unpaid':
      return `${base} bg-red-50 text-red-700 border-red-200 dark:bg-red-500/10 dark:text-red-400 dark:border-red-500/20`;
    default:
      return `${base} bg-gray-50 text-gray-700 border-gray-200 dark:bg-white/5 dark:text-gray-400 dark:border-white/10`;
  }
};

const translateStatus = (status) => {
    const table = {
        'completed': 'مكتمل',
        'paid': 'مدفوع',
        'pending': 'معلق',
        'partial': 'جزئي',
        'ordered': 'مطلوب',
        'unpaid': 'غير مدفوع',
        'received': 'تم الاستلام'
    };
    return table[status?.toLowerCase()] || status;
};

const totalPages = computed(() => Math.ceil(totalRows.value / perPage.value));

const exportCSV = async () => {
  try {
    const response = await axios.get('/admin/reports/sales-data', {
      params: { page: 1, limit: 9999, search: search.value, SortField: 'id', SortType: 'desc', ...filters.value },
      responseType: 'blob'
    });
    // If server returns JSON (not a real CSV endpoint), build CSV client-side
    if (response.headers['content-type']?.includes('application/json')) {
      const text = await response.data.text();
      const json = JSON.parse(text);
      const rows = json.sales || [];
      const headers = ['Ref', 'date', 'warehouse_name', 'client_name', 'seller', 'statut', 'payment_status', 'GrandTotal', 'paid_amount'];
      const csv = [headers.join(','), ...rows.map(r => headers.map(h => JSON.stringify(r[h] ?? '')).join(','))].join('\n');
      const blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' });
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url; a.download = `sales-report-${filters.value.from}-${filters.value.to}.csv`; a.click();
      URL.revokeObjectURL(url);
    } else {
      const url = URL.createObjectURL(response.data);
      const a = document.createElement('a');
      a.href = url; a.download = `sales-report.csv`; a.click();
      URL.revokeObjectURL(url);
    }
  } catch (e) {
    console.error('Export failed', e);
  }
};
</script>

<template>
  <AdminLayout>
    <Head title="تقرير مبيعات النظام - DawPOS" />
    <div class="space-y-12 pb-24 p-6 md:p-12 font-['Cairo']" dir="rtl">
      <!-- Header & Quick Filters -->
      <div class="flex flex-col xl:flex-row xl:items-end justify-between gap-10">
        <div class="space-y-3">
          <div class="flex items-center gap-3 text-[#04724D] font-black text-xs uppercase tracking-[0.4em]">
            <ChartBarIcon class="w-5 h-5" />
            التحليلات التشغيلية
          </div>
          <h1 class="text-4xl md:text-6xl font-black text-gray-900 dark:text-white tracking-tight">سجل بروتوكول المبيعات</h1>
          <p class="text-gray-500 font-bold text-lg">تدقيق المعاملات العالمية وتحليل توزيع المخزون.</p>
        </div>

        <div class="flex flex-wrap items-center gap-4 bg-white dark:bg-[#1A1A1A] p-3 rounded-[32px] shadow-2xl shadow-[#04724D]/5 border border-gray-100 dark:border-gray-800">
           <!-- Warehouse Filter -->
           <div class="flex items-center gap-3 px-6 py-4 bg-gray-50 dark:bg-white/5 rounded-2xl border border-gray-100 dark:border-gray-800 group">
                <BuildingStorefrontIcon class="h-5 w-5 text-gray-400 group-hover:text-[#04724D] transition-colors" />
                <select v-model="filters.warehouse_id" class="bg-transparent border-none text-xs font-black text-gray-700 dark:text-gray-200 focus:ring-0 p-0 pr-10 appearance-none">
                    <option value="">جميع المستودعات</option>
                    <option v-for="w in warehouses" :key="w.id" :value="w.id">{{ w.name }}</option>
                </select>
           </div>

           <!-- Client Filter -->
           <div class="flex items-center gap-3 px-6 py-4 bg-gray-50 dark:bg-white/5 rounded-2xl border border-gray-100 dark:border-gray-800 group">
                <UserIcon class="h-5 w-5 text-gray-400 group-hover:text-[#04724D] transition-colors" />
                <select v-model="filters.client_id" class="bg-transparent border-none text-xs font-black text-gray-700 dark:text-gray-200 focus:ring-0 p-0 pr-10 appearance-none">
                    <option value="">جميع العملاء</option>
                    <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.name }}</option>
                </select>
           </div>

           <div class="h-10 w-px bg-gray-200 dark:bg-gray-800 hidden xl:block mx-2"></div>

           <!-- Date Range -->
           <div class="flex items-center gap-6 px-6 py-4 bg-gray-50 dark:bg-white/5 rounded-2xl border border-gray-100 dark:border-gray-800">
                <CalendarIcon class="h-5 w-5 text-gray-400" />
                <div class="flex items-center gap-3 font-black text-xs text-gray-700 dark:text-gray-300 font-mono tabular-nums">
                  <input type="date" v-model="filters.from" class="bg-transparent border-none focus:ring-0 p-0 cursor-pointer" />
                  <span class="text-gray-300">←</span>
                  <input type="date" v-model="filters.to" class="bg-transparent border-none focus:ring-0 p-0 cursor-pointer" />
                </div>
           </div>
        </div>
      </div>

      <!-- Main Audit Table -->
      <div class="bg-white dark:bg-[#0A0A0A] rounded-[56px] border border-gray-100 dark:border-gray-800 shadow-3xl shadow-[#04724D]/5 overflow-hidden relative">
        <!-- Abstract Decoration -->
        <div class="absolute -top-24 -right-24 w-96 h-96 bg-[#04724D]/[0.02] blur-[120px] rounded-full pointer-events-none"></div>

        <!-- Table Header & Search -->
        <div class="p-12 border-b border-gray-100 dark:border-gray-800 flex flex-col md:flex-row md:items-center justify-between gap-10 relative z-10">
          <div class="relative flex-1 max-w-xl group">
            <MagnifyingGlassIcon class="absolute right-6 top-1/2 -translate-y-1/2 w-6 h-6 text-gray-400 group-focus-within:text-[#04724D] transition-all" />
            <input 
              v-model="search"
              type="text" 
              placeholder="البحث برقم المرجع، العميل، أو الحالة..." 
              class="w-full pr-16 pl-8 py-5 bg-gray-50 dark:bg-white/[0.03] border-none rounded-[24px] text-sm font-black focus:ring-4 focus:ring-[#04724D]/10 transition-all placeholder:text-gray-400 dark:placeholder:text-gray-600"
            />
          </div>

          <div class="flex items-center gap-5">
            <button @click="exportCSV" class="flex items-center gap-3 px-10 py-5 bg-[#04724D] text-[#3EFF8B] rounded-[24px] text-xs font-black uppercase tracking-widest hover:shadow-2xl hover:shadow-[#04724D]/30 transition-all active:scale-95 group">
              <ArrowDownTrayIcon class="w-5 h-5 group-hover:translate-y-0.5 transition-transform" />
              تصدير البروتوكول
            </button>
          </div>
        </div>

        <div class="overflow-x-auto relative z-10">
          <table class="w-full border-collapse">
            <thead>
              <tr class="bg-gray-50/50 dark:bg-white/[0.01] text-[10px] font-black text-gray-400 uppercase tracking-[0.4em] font-['Cairo']">
                <th class="px-12 py-10 text-right">المرجع الداخلي</th>
                <th class="px-8 py-10 text-right">نقطة التوزيع</th>
                <th class="px-8 py-10 text-right">العميل / المحاسب</th>
                <th class="px-8 py-10 text-right">حالة البروتوكول</th>
                <th class="px-8 py-10 text-left">القيمة المحاسبية</th>
                <th class="px-12 py-10 text-center">حالة الدفع</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
              <tr v-for="sale in sales" :key="sale.id" class="group hover:bg-gray-50/50 dark:hover:bg-white/[0.01] transition-all duration-500">
                <td class="px-12 py-8">
                  <div class="flex items-center gap-5">
                    <div class="p-4 bg-[#04724D]/10 rounded-2xl group-hover:scale-110 transition-transform duration-500">
                      <ShoppingCartIcon class="w-5 h-5 text-[#04724D] dark:text-[#3EFF8B]" />
                    </div>
                    <div>
                      <div class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-tighter font-mono">{{ sale.Ref }}</div>
                      <div class="text-[10px] font-bold text-gray-400 mt-1 tabular-nums font-mono">{{ sale.date }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-8 py-8">
                  <div class="flex items-center gap-3">
                    <BuildingStorefrontIcon class="w-4 h-4 text-gray-400 opacity-60" />
                    <span class="text-xs font-black text-gray-700 dark:text-gray-300">{{ sale.warehouse_name }}</span>
                  </div>
                </td>
                <td class="px-8 py-8">
                  <div class="text-xs font-black text-gray-900 dark:text-white">{{ sale.client_name }}</div>
                  <div class="text-[10px] font-bold text-gray-400 mt-1.5 flex items-center gap-2">
                    <UserIcon class="w-3 h-3 opacity-40" />
                    بواسطة: {{ sale.seller }}
                  </div>
                </td>
                <td class="px-8 py-8">
                  <span :class="getStatusClass(sale.statut)">
                    {{ translateStatus(sale.statut) }}
                  </span>
                </td>
                <td class="px-8 py-8 text-left">
                  <div class="text-lg font-black text-[#04724D] dark:text-[#3EFF8B] tabular-nums font-mono">{{ formatCurrency(sale.GrandTotal) }}</div>
                  <div class="text-[9px] font-black text-gray-400 mt-1 uppercase tracking-widest leading-none">إجمالي الصافي</div>
                </td>
                <td class="px-12 py-8">
                  <div class="flex flex-col items-center gap-3">
                    <span :class="getStatusClass(sale.payment_status)">
                      {{ translateStatus(sale.payment_status) }}
                    </span>
                    <div class="w-32 bg-gray-100 dark:bg-white/5 h-1.5 rounded-full overflow-hidden p-0.5">
                      <div 
                        class="bg-gradient-to-r from-[#04724D] to-[#3EFF8B] h-full rounded-full transition-all duration-1000 shadow-lg shadow-[#04724D]/20"
                        :style="{ width: (sale.paid_amount / sale.GrandTotal * 100) + '%' }"
                      ></div>
                    </div>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination Protocol -->
        <div class="p-12 bg-gray-50/50 dark:bg-white/[0.04] border-t border-gray-100 dark:border-gray-800 flex flex-col md:flex-row md:items-center justify-between gap-10 relative z-10">
          <div class="text-sm font-bold text-gray-500 flex items-center gap-6">
            <span class="px-5 py-2.5 bg-white dark:bg-[#1A1A1A] rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800 font-black text-xs">
               عرض {{ sales.length }} من أصل {{ totalRows }} بروتوكول مدقق
            </span>
            
            <div class="flex items-center gap-3">
               <span class="text-xs font-black opacity-60">حجم الصفحة:</span>
               <select v-model="perPage" class="bg-white dark:bg-[#1A1A1A] border border-gray-100 dark:border-gray-800 rounded-xl text-xs font-black text-[#04724D] focus:ring-0 px-4 py-2 appearance-none">
                  <option :value="10">10 سجلات</option>
                  <option :value="25">25 سجل</option>
                  <option :value="50">50 سجل</option>
                  <option :value="100">100 سجل</option>
               </select>
            </div>
          </div>

          <div class="flex items-center gap-3">
            <button 
              @click="page--" 
              :disabled="page === 1"
              class="p-4 bg-white dark:bg-[#1A1A1A] rounded-2xl border border-gray-100 dark:border-gray-800 disabled:opacity-30 hover:border-[#04724D] transition-all shadow-sm active:scale-90 group"
            >
              <ChevronRightIcon class="w-6 h-6 text-gray-600 dark:text-gray-400 group-hover:text-[#04724D] transition-colors" />
            </button>
            
            <div class="flex items-center gap-2">
              <template v-for="p in totalPages" :key="p">
                <button 
                  v-if="p === 1 || p === totalPages || (p >= page - 1 && p <= page + 1)"
                  @click="page = p"
                  :class="p === page ? 'bg-[#04724D] text-white border-[#04724D] shadow-2xl shadow-[#04724D]/30' : 'bg-white dark:bg-[#1A1A1A] text-gray-500 border-gray-100 dark:border-gray-800 hover:border-[#04724D]'"
                  class="w-12 h-12 rounded-2xl border text-xs font-black transition-all font-mono tabular-nums active:scale-95"
                >
                   {{ p }}
                </button>
                <span v-else-if="p === page - 2 || p === page + 2" class="px-2 text-gray-400 font-black">...</span>
              </template>
            </div>

            <button 
              @click="page++" 
              :disabled="page === totalPages"
              class="p-4 bg-white dark:bg-[#1A1A1A] rounded-2xl border border-gray-100 dark:border-gray-800 disabled:opacity-30 hover:border-[#04724D] transition-all shadow-sm active:scale-90 group"
            >
              <ChevronLeftIcon class="w-6 h-6 text-gray-600 dark:text-gray-400 group-hover:text-[#04724D] transition-colors" />
            </button>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<style scoped>
input[type="date"]::-webkit-calendar-picker-indicator {
  cursor: pointer;
  filter: invert(44%) sepia(87%) saturate(302%) hue-rotate(113deg) brightness(92%) contrast(89%);
}
</style>
