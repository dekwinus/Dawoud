<script setup>
import { Head, usePage, router } from '@inertiajs/vue3';
import { onMounted, ref, computed } from 'vue';
import axios from 'axios';
import VueApexCharts from 'vue3-apexcharts';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { 
  ShoppingBag, 
  ShoppingCart, 
  ArrowLeftRight, 
  Banknote,
  AlertTriangle,
  TrendingUp,
  Users,
  Box,
  LayoutDashboard,
  Calendar,
  Warehouse as WarehouseIcon,
  ChevronLeft,
  Plus,
  ArrowUpRight,
  ArrowDownRight
} from 'lucide-vue-next';

const apexchart = VueApexCharts;

const props = defineProps({
  warehouses: Array,
});

const loading = ref(true);
const stats = ref({
  today_sales: 0,
  today_purchases: 0,
  return_sales: 0,
  return_purchases: 0,
  sales_due: 0,
  purchase_due: 0,
  today_invoices: 0,
  today_profit: 0,
  sales_growth: 0,
  new_clients_today: 0
});

const warehouse_id = ref('');
const dateRange = ref({
  from: new Date(new Date().setDate(new Date().getDate() - 30)).toISOString().split('T')[0],
  to: new Date().toISOString().split('T')[0]
});

const recentSales = ref([]);
const stockAlerts = ref([]);
const salesTarget = 250000;
const targetProgress = computed(() => {
  const sales = Number(stats.value.today_sales || 0);
  return Math.min(100, (sales / salesTarget) * 100).toFixed(1);
});
const topProducts = ref([]);
const newCustomersToday = computed(() => stats.value.new_clients_today || 0);
const chartMode = ref('combined'); // 'combined' | 'revenue' | 'purchases' | 'net'

const fetchDashboardData = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/api/dashboard_data', {
      params: {
        warehouse_id: warehouse_id.value,
        from: dateRange.value.from,
        to: dateRange.value.to
      }
    });

    const data = response.data;
    const report = data.report_dashboard?.original ?? data.report_dashboard ?? {};
    
    stats.value = report.report ?? {
      today_sales: 0,
      today_purchases: 0,
      return_sales: 0,
      return_purchases: 0,
      sales_due: 0,
      purchase_due: 0,
      today_invoices: 0,
      today_profit: 0,
      sales_growth: 0,
      new_clients_today: 0
    };
    recentSales.value = report.last_sales ?? [];
    stockAlerts.value = report.stock_alert ?? [];
    topProducts.value = report.products ?? [];
    
    updateCharts(data);

  } catch (error) {
    console.error('Failed to fetch dashboard data:', error);
  } finally {
    loading.value = false;
  }
};

// Chart Data & Options
const salesChartSeries = ref([]);
const salesChartOptions = ref({
  chart: { 
    type: 'area', 
    toolbar: { show: false }, 
    zoom: { enabled: false }, 
    fontFamily: 'Cairo, sans-serif',
    sparkline: { enabled: false },
    background: 'transparent'
  },
  colors: ['#F59E0B', '#10B981', '#04724D'], // Orange (Sales), Teal (Purchases), Green (Profit)
  dataLabels: { enabled: false },
  stroke: { curve: 'smooth', width: 4, lineCap: 'round' },
  grid: { 
    borderColor: '#f0f0f0',
    strokeDashArray: 4,
    xaxis: { lines: { show: true } },
    yaxis: { lines: { show: false } }
  },
  xaxis: { 
    categories: [],
    axisBorder: { show: false },
    axisTicks: { show: false },
    labels: { style: { colors: '#94a3b8', fontWeight: 600, fontSize: '11px' } }
  },
  yaxis: {
    labels: { show: false }
  },
  fill: {
    type: 'gradient',
    gradient: { 
      shadeIntensity: 1, 
      opacityFrom: 0.2, 
      opacityTo: 0.05, 
      stops: [0, 90, 100],
      colorStops: [
        { offset: 0, color: '#04724D', opacity: 0.2 },
        { offset: 100, color: '#04724D', opacity: 0 }
      ]
    }
  },
  tooltip: {
    theme: 'dark',
    x: { show: true },
    y: { formatter: (val) => `${Number(val || 0).toLocaleString()} ج.م` }
  }
});

const donutChartSeries = ref([44, 55, 13, 43]);
const donutChartOptions = ref({
  chart: { type: 'donut', fontFamily: 'Cairo, sans-serif' },
  labels: ['مباشر', 'أونلاين', 'جملة', 'أخرى'],
  colors: ['#04724D', '#3EFF8B', '#10B981', '#064E3B'],
  stroke: { width: 0 },
  plotOptions: {
    pie: {
      donut: {
        size: '80%',
        labels: {
          show: true,
          total: { show: true, label: 'المبيعات', formatter: () => 'إجمالي' }
        }
      }
    }
  },
  legend: { show: false },
  dataLabels: { enabled: false }
});

const _chartData = ref(null);

const updateCharts = (data) => {
  _chartData.value = data;
  applyChartMode();
};

const applyChartMode = () => {
  if (!_chartData.value) return;
  const data = _chartData.value;
  const salesData = data.sales?.original ?? data.sales ?? { data: [], days: [] };
  const purchasesData = data.purchases?.original ?? data.purchases ?? { data: [], days: [] };
  const categories = salesData.days?.length ? salesData.days : (purchasesData.days ?? []);

  const salesSeriesData = categories.map((_, idx) => Number(salesData.data?.[idx] ?? 0));
  const purchasesSeriesData = categories.map((_, idx) => Number(purchasesData.data?.[idx] ?? 0));
  const netSeriesData = categories.map((_, idx) => salesSeriesData[idx] - purchasesSeriesData[idx]);

  if (chartMode.value === 'combined') {
    salesChartSeries.value = [
      { name: 'المبيعات', data: salesSeriesData },
      { name: 'المشتريات', data: purchasesSeriesData },
      { name: 'صافي الربح (تقديري)', data: netSeriesData },
    ];
  } else if (chartMode.value === 'revenue') {
    salesChartSeries.value = [
      { name: 'المبيعات', data: salesSeriesData },
    ];
  } else if (chartMode.value === 'purchases') {
    salesChartSeries.value = [
      { name: 'المشتريات', data: purchasesSeriesData },
    ];
  } else {
    salesChartSeries.value = [
      { name: 'صافي الربح (تقديري)', data: netSeriesData },
    ];
  }

  salesChartOptions.value = {
    ...salesChartOptions.value,
    xaxis: { ...salesChartOptions.value.xaxis, categories }
  };
};

const setChartMode = (mode) => {
  chartMode.value = mode;
  applyChartMode();
};

onMounted(() => {
  fetchDashboardData();
});

const formatCurrency = (value) => {
  return new Intl.NumberFormat('ar-EG', { 
    style: 'currency', 
    currency: 'EGP',
    maximumFractionDigits: 0
  }).format(value);
};
</script>

<template>
  <AdminLayout>
    <Head title="لوحة القيادة - DawPOS" />
    <div class="space-y-6 pb-8" dir="rtl">
      <!-- Welcome Header -->
      <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-4">
        <div class="space-y-1 text-right">
            <div class="flex items-center gap-2 text-[#04724D] font-black text-[10px] uppercase tracking-[0.2em] font-['Cairo']">
                <LayoutDashboard class="w-3.5 h-3.5" />
                وحدة التحكم الإدارية
            </div>
            <h1 class="text-xl font-black text-gray-900 dark:text-white tracking-tight font-['Cairo']">نظرة عامة على النظام</h1>
            <p class="text-xs text-gray-500 font-medium font-['Cairo']">مقاييس الأداء في الوقت الفعلي لنظام DawPOS.</p>
        </div>

        <div class="flex items-center gap-3 bg-white dark:bg-[#1A1A1A] p-2 rounded-[24px] shadow-sm border border-gray-100 dark:border-gray-800">
           <div class="flex items-center gap-2 px-4 py-2 bg-gray-50 dark:bg-white/5 rounded-2xl border border-gray-100 dark:border-gray-800">
                <WarehouseIcon class="h-4 w-4 text-gray-400" />
                <select 
                    v-model="warehouse_id" 
                    @change="fetchDashboardData"
                    class="bg-transparent border-none text-sm font-black text-gray-700 dark:text-gray-200 focus:ring-0 p-0 pl-10 font-['Cairo']"
                >
                    <option value="">جميع الفروع (شبكة عالمية)</option>
                    <option v-for="w in warehouses" :key="w.id" :value="w.id">{{ w.name }}</option>
                </select>
           </div>
           
           <div class="h-6 w-px bg-gray-200 dark:bg-gray-800"></div>

           <div class="flex items-center gap-2 px-4">
                <Calendar class="h-4 w-4 text-gray-400" />
                <input 
                    type="date" 
                    v-model="dateRange.from" 
                    @change="fetchDashboardData"
                    class="bg-transparent border-none text-xs font-black text-gray-700 dark:text-gray-200 focus:ring-0 p-0 font-mono"
                />
                <span class="text-gray-300 mx-1">←</span>
                <input 
                    type="date" 
                    v-model="dateRange.to" 
                    @change="fetchDashboardData"
                    class="bg-transparent border-none text-xs font-black text-gray-700 dark:text-gray-200 focus:ring-0 p-0 font-mono"
                />
           </div>
        </div>
      </div>

      <!-- Hero Stats Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Sales Card -->
        <div class="relative group">
            <div class="absolute inset-0 bg-gradient-to-br from-[#04724D] to-[#10B981] rounded-[24px] blur-2xl opacity-0 group-hover:opacity-20 transition-opacity duration-500"></div>
            <div class="relative bg-white dark:bg-[#121212] p-5 rounded-[24px] border border-gray-100 dark:border-gray-800 shadow-sm transition-all duration-300 group-hover:-translate-y-1 text-right">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-2.5 bg-[#04724D]/10 rounded-xl">
                        <ShoppingBag class="w-5 h-5 text-[#04724D]" />
                    </div>
                    <div :class="stats.sales_growth >= 0 ? 'text-green-600' : 'text-red-600'" class="flex items-center gap-1 font-black text-[10px] font-['Cairo']">
                        <ArrowUpRight v-if="stats.sales_growth >= 0" class="w-3 h-3" />
                        <ArrowDownRight v-else class="w-3 h-3" />
                        {{ stats.sales_growth >= 0 ? '+' : '' }}{{ stats.sales_growth }}%
                    </div>
                </div>
                <div class="space-y-0.5">
                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest font-['Cairo']">إجمالي الإيرادات</span>
                    <h3 class="text-xl font-black text-gray-900 dark:text-white tracking-tighter">{{ formatCurrency(stats.today_sales) }}</h3>
                </div>
                <div class="mt-3 pt-3 border-t border-gray-50 dark:border-gray-800 flex items-center justify-between text-[10px] font-black text-gray-500 font-['Cairo']">
                    <span>الهدف: {{ (salesTarget / 1000) }} ألف</span>
                    <div class="w-16 bg-gray-100 dark:bg-gray-800 h-1 rounded-full overflow-hidden">
                        <div class="bg-[#04724D] h-full transition-all duration-1000" :style="{ width: targetProgress + '%' }"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Orders Card -->
        <div class="relative group">
            <div class="relative bg-white dark:bg-[#121212] p-5 rounded-[24px] border border-gray-100 dark:border-gray-800 shadow-sm transition-all duration-300 group-hover:-translate-y-1 text-right">
                <div class="flex items-center justify-between mb-4 text-[#3EFF8B]">
                    <div class="p-2.5 bg-[#3EFF8B]/10 rounded-xl">
                        <ShoppingCart class="w-5 h-5 text-[#04724D]" />
                    </div>
                     <div class="flex items-center gap-1 font-black text-[10px] opacity-60 font-['Cairo'] text-gray-400">
                        الطلبات
                    </div>
                </div>
                <div class="space-y-0.5">
                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest font-['Cairo']">حجم المبيعات</span>
                    <h3 class="text-xl font-black text-gray-900 dark:text-white tracking-tighter">{{ stats.today_invoices }} فاتورة</h3>
                </div>
                 <div class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-800 flex items-center gap-2">
                    <div class="flex -space-x-1.5">
                        <div v-for="i in 3" :key="i" class="w-6 h-6 rounded-full border border-white dark:border-gray-900 bg-gray-200"></div>
                    </div>
                    <span class="text-[9px] text-gray-400 font-black uppercase tracking-tight font-['Cairo']">+{{ newCustomersToday }} عميل جديد اليوم</span>
                </div>
            </div>
        </div>

        <!-- Returns Card -->
        <div class="relative group">
            <div class="relative bg-white dark:bg-[#121212] p-5 rounded-[24px] border border-gray-100 dark:border-gray-800 shadow-sm transition-all duration-300 group-hover:-translate-y-1 text-right">
                <div class="flex items-center justify-between mb-4 text-orange-500">
                    <div class="p-2.5 bg-orange-500/10 rounded-xl">
                        <ArrowLeftRight class="w-5 h-5" />
                    </div>
                    <div class="flex items-center gap-1 text-red-500 font-black text-[10px] font-['Cairo']">
                        <ArrowUpRight class="w-3 h-3" />
                        مرتفع
                    </div>
                </div>
                <div class="space-y-0.5">
                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest font-['Cairo']">مرتجات المبيعات</span>
                    <h3 class="text-xl font-black text-gray-900 dark:text-white tracking-tighter">{{ formatCurrency(stats.return_sales) }}</h3>
                </div>
                <div class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-800 text-[9px] font-black text-gray-400 font-['Cairo']">
                    يتطلب تدقيقاً نشطاً للفواتير
                </div>
            </div>
        </div>

        <!-- Profit Card (PREMIUM) -->
        <div class="relative group">
            <div class="absolute inset-0 bg-[#04724D] shadow-xl shadow-[#04724D]/20 rounded-[24px] group-hover:scale-[1.02] transition-transform duration-500"></div>
            <div class="relative bg-transparent p-5 rounded-[24px] text-white text-right">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-2.5 bg-white/20 rounded-xl backdrop-blur-md">
                        <Banknote class="w-5 h-5 text-white" />
                    </div>
                    <TrendingUp class="w-5 h-5 opacity-40 shrink-0" />
                </div>
                <div class="space-y-0.5">
                    <span class="text-[10px] font-black text-white/60 uppercase tracking-widest font-['Cairo']">صافي الربحية</span>
                    <h3 class="text-xl font-black text-white tracking-tighter">{{ formatCurrency(stats.today_profit) }}</h3>
                </div>
                 <div class="mt-3 pt-3 border-t border-white/10 flex items-center justify-between">
                    <span class="text-[9px] font-black text-white/50 tracking-[0.1em] font-['Cairo'] uppercase">فئة بريميوم</span>
                    <ChevronLeft class="w-4 h-4 opacity-40" />
                </div>
            </div>
        </div>
      </div>

      <!-- Secondary Layout -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <!-- Main Chart Section -->
        <div class="lg:col-span-8 space-y-6">
            <div class="bg-white dark:bg-[#121212] p-5 rounded-[24px] border border-gray-100 dark:border-gray-800 shadow-sm relative overflow-hidden">
                <!-- Visual Edge -->
                <div class="absolute top-0 left-0 w-48 h-48 bg-[#04724D]/5 blur-3xl -ml-24 -mt-24"></div>

                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
                    <div class="text-right">
                        <h3 class="text-base font-black text-gray-900 dark:text-white tracking-tight font-['Cairo']">إحصائيات النمو</h3>
                        <p class="text-xs text-gray-500 font-medium font-['Cairo']">المبيعات والمشتريات وصافي الربح حسب الفترة المختارة.</p>
                    </div>
                    <div class="flex items-center gap-2 bg-gray-50 dark:bg-white/5 p-1 rounded-xl border border-gray-100 dark:border-gray-800 font-['Cairo']">
                        <button @click="setChartMode('combined')" :class="chartMode === 'combined' ? 'bg-white dark:bg-[#1A1A1A] text-[#04724D] shadow-sm border border-gray-100 dark:border-gray-800' : 'text-gray-400 hover:text-gray-600'" class="px-4 py-2 text-[10px] font-black uppercase rounded-lg transition-all">الكل</button>
                        <button @click="setChartMode('revenue')" :class="chartMode === 'revenue' ? 'bg-white dark:bg-[#1A1A1A] text-[#04724D] shadow-sm border border-gray-100 dark:border-gray-800' : 'text-gray-400 hover:text-gray-600'" class="px-4 py-2 text-[10px] font-black uppercase rounded-lg transition-all">المبيعات</button>
                        <button @click="setChartMode('purchases')" :class="chartMode === 'purchases' ? 'bg-white dark:bg-[#1A1A1A] text-[#04724D] shadow-sm border border-gray-100 dark:border-gray-800' : 'text-gray-400 hover:text-gray-600'" class="px-4 py-2 text-[10px] font-black uppercase rounded-lg transition-all">المشتريات</button>
                        <button @click="setChartMode('net')" :class="chartMode === 'net' ? 'bg-white dark:bg-[#1A1A1A] text-[#04724D] shadow-sm border border-gray-100 dark:border-gray-800' : 'text-gray-400 hover:text-gray-600'" class="px-4 py-2 text-[10px] font-black uppercase rounded-lg transition-all">صافي الربح</button>
                    </div>
                </div>

                <div class="h-80">
                    <apexchart 
                        v-if="!loading && salesChartSeries.length"
                        type="area" 
                        height="100%" 
                        :options="salesChartOptions" 
                        :series="salesChartSeries" 
                    />
                    <div v-else class="h-full flex flex-col items-center justify-center gap-3 opacity-30">
                        <div class="flex gap-1.5 items-end">
                            <div class="w-4 h-10 bg-gray-200 rounded-t-lg animate-pulse"></div>
                            <div class="w-4 h-20 bg-gray-200 rounded-t-lg animate-pulse" style="animation-delay: 0.1s"></div>
                            <div class="w-4 h-15 bg-gray-200 rounded-t-lg animate-pulse" style="animation-delay: 0.2s"></div>
                        </div>
                        <span class="text-[10px] font-black uppercase tracking-widest font-['Cairo']">تجميع البيانات...</span>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="bg-white dark:bg-[#121212] rounded-[24px] border border-gray-100 dark:border-gray-800 shadow-sm overflow-hidden text-right">
                <div class="p-6 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-black text-gray-900 dark:text-white tracking-tight font-['Cairo']">بث المعاملات المباشر</h3>
                        <p class="text-[10px] text-gray-400 font-medium mt-1 font-['Cairo']">مراقبة ومعالجة المبيعات في الوقت الحقيقي</p>
                    </div>
                    <button @click="router.visit('/admin/sales')" class="text-[10px] font-black text-[#04724D] uppercase tracking-widest hover:underline px-4 py-2 hover:bg-[#04724D]/5 rounded-xl transition-all font-['Cairo']">عرض الكل</button>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-right">
                        <thead>
                            <tr class="text-[10px] uppercase font-black text-gray-400 tracking-[0.2em] bg-gray-50/30 dark:bg-white/[0.01] font-['Cairo']">
                                <th class="px-8 py-5">المرجع</th>
                                <th class="px-6 py-5">العميل / المندوب</th>
                                <th class="px-6 py-5">القيمة</th>
                                <th class="px-8 py-5 text-center">حالة البروتوكول</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-gray-800 font-['Cairo']">
                            <tr v-for="sale in recentSales" :key="sale.Ref" class="group hover:bg-gray-50/50 dark:hover:bg-white/[0.01] transition-all">
                                <td class="px-8 py-6">
                                    <div class="text-xs font-black text-[#04724D] dark:text-[#3EFF8B] font-mono">{{ sale.Ref }}</div>
                                    <div class="text-[10px] font-black text-gray-400 uppercase mt-0.5 tracking-tighter uppercase">سجل مبيعات</div>
                                </td>
                                <td class="px-6 py-6 font-black">
                                    <div class="text-xs font-black text-gray-800 dark:text-gray-200">{{ sale.client_name }}</div>
                                    <div class="flex items-center gap-1.5 mt-0.5 opacity-50">
                                        <WarehouseIcon class="h-3 w-3" />
                                        <span class="text-[9px] font-black uppercase">{{ sale.warehouse_name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-6">
                                    <div class="text-sm font-black text-gray-900 dark:text-white">{{ formatCurrency(sale.GrandTotal) }}</div>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex justify-center">
                                        <span :class="{
                                            'bg-green-100 dark:bg-green-500/10 text-green-700 dark:text-green-400 border-green-200': sale.statut === 'completed',
                                            'bg-orange-100 dark:bg-orange-500/10 text-orange-700 dark:text-orange-400 border-orange-200': sale.statut === 'pending',
                                            'bg-blue-100 dark:bg-blue-500/10 text-blue-700 dark:text-blue-400 border-blue-200': sale.statut === 'ordered'
                                        }" class="px-4 py-1.5 rounded-full text-[9px] font-black uppercase tracking-widest border opacity-80">
                                            {{ sale.statut === 'completed' ? 'مكتمل' : (sale.statut === 'pending' ? 'معلق' : 'مطلوب') }}
                                        </span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Sidebar Insights -->
        <div class="lg:col-span-4 space-y-6 text-right">
            <!-- Stock Alerts -->
            <div class="bg-white dark:bg-[#121212] p-6 rounded-[24px] border border-gray-100 dark:border-gray-800 shadow-sm overflow-hidden relative group">
                <div class="absolute inset-0 bg-red-500/[0.02] opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <div class="flex items-center justify-between mb-6 relative z-10">
                    <h3 class="text-sm font-black text-gray-900 dark:text-white tracking-tight flex items-center gap-2.5 font-['Cairo']">
                        <AlertTriangle class="w-5 h-5 text-red-500" />
                        تنبيهات حرجة
                    </h3>
                    <span class="bg-red-500 text-white px-2 py-0.5 rounded-lg text-[9px] font-black uppercase tracking-widest animate-pulse font-mono">{{ stockAlerts.length }}</span>
                </div>
                
                <div class="space-y-3 relative z-10 h-[350px] overflow-y-auto custom-scrollbar pl-2 font-['Cairo']">
                    <div v-for="alert in stockAlerts" :key="alert.code" class="flex items-center gap-4 p-4 rounded-[20px] bg-gray-50/50 dark:bg-white/[0.02] border border-transparent hover:border-red-500/20 transition-all group shadow-inner">
                        <div class="w-12 h-12 bg-white dark:bg-[#1A1A1A] rounded-xl flex items-center justify-center flex-shrink-0 shadow-sm border border-gray-100 dark:border-gray-800">
                             <Box class="w-5 h-5 text-gray-400 group-hover:scale-110 group-hover:text-red-500 transition-all" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-black text-gray-900 dark:text-white truncate">{{ alert.name }}</p>
                            <p class="text-[9px] text-gray-400 font-black uppercase mt-0.5 tracking-tighter">{{ alert.warehouse }}</p>
                        </div>
                         <div class="text-left">
                            <p class="text-sm font-black text-red-500 font-mono">{{ alert.quantity }}</p>
                            <p class="text-[8px] text-gray-400 font-black uppercase tracking-tight">وحدة</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Performance -->
            <div class="bg-[#04724D] p-6 rounded-[32px] border border-[#04724D] shadow-[0_20px_40px_-10px_rgba(4,114,77,0.2)] relative overflow-hidden group">
                <!-- Glowing Decoration -->
                <div class="absolute -top-16 -left-16 w-48 h-48 bg-white/20 blur-3xl rounded-full opacity-20 group-hover:scale-110 transition-transform duration-700"></div>

                <div class="flex items-center justify-between mb-6 relative z-10">
                    <h3 class="text-sm font-black text-white tracking-tight flex items-center gap-2.5 font-['Cairo']">
                        <TrendingUp class="w-5 h-5" />
                        الأعلى أداءً
                    </h3>
                    <Plus class="w-5 h-5 text-white p-1 rounded-lg bg-white/10 cursor-pointer hover:bg-white/20 transition-all" />
                </div>

                <div class="space-y-8 relative z-10 font-['Cairo']">
                    <div v-for="product in topProducts.slice(0, 3)" :key="product.name" class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-black text-white tracking-wide uppercase">{{ product.name }}</span>
                            <span class="text-[10px] font-black text-[#3EFF8B] bg-white/10 px-2.5 py-1 rounded-md tracking-[0.1em] font-mono">{{ product.total_sales }} وحدة</span>
                        </div>
                        <div class="w-full bg-black/20 h-2.5 rounded-full overflow-hidden p-0.5 ring-1 ring-white/10">
                            <div 
                                class="bg-gradient-to-l from-[#3EFF8B] to-[#10B981] h-full rounded-full transition-all duration-1000 shadow-[0_0_15px_rgba(62,255,139,0.4)]"
                                :style="{ width: (product.total_sales / topProducts[0].total_sales * 100) + '%' }"
                            ></div>
                        </div>
                    </div>
                </div>

                <div class="mt-8 pt-6 border-t border-white/10 relative z-10">
                    <button @click="router.visit('/admin/reports/profit-loss')" class="w-full py-4 bg-white/10 hover:bg-white/20 text-white rounded-[18px] font-black text-[9px] uppercase tracking-[0.2em] transition-all border border-white/5 backdrop-blur-sm font-['Cairo']">تحليل المقاييس العميقة</button>
                </div>
            </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #04724D22;
  border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #04724D44;
}

/* Animations */
@keyframes slideDown {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

.animate-fade-in {
    animation: slideDown 0.5s ease-out forwards;
}

input[type="date"]::-webkit-calendar-picker-indicator {
  cursor: pointer;
  filter: invert(44%) sepia(87%) saturate(302%) hue-rotate(113deg) brightness(92%) contrast(89%);
}
</style>
