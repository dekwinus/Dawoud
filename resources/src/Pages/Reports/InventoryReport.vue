<script setup>
import { Head } from '@inertiajs/vue3';
import { onMounted, ref, computed, watch } from 'vue';
import axios from 'axios';
import VueApexCharts from 'vue3-apexcharts';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { 
  Warehouse as BuildingStorefrontIcon,
  Database as CircleStackIcon,
  BarChart3 as ChartBarIcon,
  RefreshCcw as ArrowPathRoundedSquareIcon,
  Box as CubeIcon,
  AlertTriangle as ExclamationTriangleIcon,
  DollarSign as CurrencyDollarIcon,
  Scale as ScaleIcon
} from 'lucide-vue-next';

const apexchart = VueApexCharts;

const props = defineProps({
  warehouses: Array,
});

const loading = ref(true);
const warehouse_id = ref('');
const stockData = ref({
  stock_count: [],
  stock_value: [],
  count_labels: [],
  count_items: [],
  count_qty: [],
  value_labels: [],
  value_price: [],
  value_cost: []
});

const loadReport = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/admin/reports/inventory-data', {
      params: { warehouse_id: warehouse_id.value }
    });
    stockData.value = response.data;
    updateCharts();
  } catch (error) {
    console.error('Failed to load inventory report:', error);
  } finally {
    loading.value = false;
  }
};

// Chart Options
const distributionChartOptions = ref({
  chart: { type: 'bar', toolbar: { show: false }, stacked: true },
  colors: ['#04724D', '#10B981'],
  plotOptions: { 
    bar: { 
        borderRadius: 12, 
        columnWidth: '35%',
        dataLabels: { position: 'top' }
    } 
  },
  xaxis: { 
    categories: [], 
    labels: { 
        style: { 
            colors: '#94a3b8', 
            fontWeight: 800,
            fontFamily: 'Cairo'
        } 
    } 
  },
  legend: { 
    show: true, 
    position: 'top',
    fontFamily: 'Cairo',
    fontWeight: 800
  },
  grid: { borderColor: '#f1f1f1', strokeDashArray: 4 },
  dataLabels: { enabled: false }
});

const distributionChartSeries = ref([]);

const updateCharts = () => {
  distributionChartSeries.value = [
    { name: 'منتجات فريدة', data: stockData.value.count_items },
    { name: 'إجمالي الكمية', data: stockData.value.count_qty }
  ];
  distributionChartOptions.value = {
    ...distributionChartOptions.value,
    xaxis: { ...distributionChartOptions.value.xaxis, categories: stockData.value.count_labels }
  };
};

onMounted(() => {
  loadReport();
});

const formatCurrency = (value) => {
  return new Intl.NumberFormat('ar-EG', { 
    style: 'currency', 
    currency: 'EGP',
    maximumFractionDigits: 0
  }).format(value);
};

const totalStockValue = computed(() => {
  return stockData.value.value_price ? stockData.value.value_price.reduce((a, b) => a + b, 0) : 0;
});

const totalStockCost = computed(() => {
  return stockData.value.value_cost ? stockData.value.value_cost.reduce((a, b) => a + b, 0) : 0;
});
</script>

<template>
  <AdminLayout>
    <Head title="تحليل توزيع المخزون - DawPOS" />
    <div class="space-y-12 pb-24 p-6 md:p-12 font-['Cairo']" dir="rtl">
      <!-- Header -->
      <div class="flex flex-col md:flex-row md:items-end justify-between gap-10">
        <div class="space-y-3">
          <div class="flex items-center gap-3 text-[#04724D] font-black text-xs uppercase tracking-[0.4em]">
            <CircleStackIcon class="w-5 h-5" />
            ذكاء أصول المخزون
          </div>
          <h1 class="text-4xl md:text-6xl font-black text-gray-900 dark:text-white tracking-tight">بروتوكول توزيع المخزون</h1>
          <p class="text-gray-500 font-bold text-lg">مستويات المخزون الموحدة وبروتوكولات التقييم المالي.</p>
        </div>

        <div class="flex items-center gap-4 bg-white dark:bg-[#1A1A1A] p-3 rounded-[32px] shadow-2xl shadow-[#04724D]/5 border border-gray-100 dark:border-gray-800">
           <div class="flex items-center gap-3 px-6 py-4 bg-gray-50 dark:bg-white/5 rounded-2xl border border-gray-100 dark:border-gray-800 group">
                <BuildingStorefrontIcon class="h-5 w-5 text-gray-400 group-hover:text-[#04724D] transition-colors" />
                <select 
                    v-model="warehouse_id" 
                    @change="loadReport"
                    class="bg-transparent border-none text-xs font-black text-gray-700 dark:text-gray-200 focus:ring-0 p-0 pr-10 appearance-none"
                >
                    <option value="">شبكة المستودعات العالمية</option>
                    <option v-for="w in warehouses" :key="w.id" :value="w.id">{{ w.name }}</option>
                </select>
           </div>
           
           <button @click="loadReport" class="p-4 bg-[#04724D] text-[#3EFF8B] rounded-2xl hover:shadow-xl hover:shadow-[#04724D]/20 transition-all active:scale-95 group">
             <ArrowPathRoundedSquareIcon class="w-6 h-6 group-hover:rotate-180 transition-transform duration-700" :class="{'animate-spin': loading}" />
           </button>
        </div>
      </div>

      <!-- Quick Stats -->
      <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-8">
        <div class="bg-white dark:bg-[#0A0A0A] p-10 rounded-[48px] border border-gray-100 dark:border-gray-800 shadow-3xl shadow-[#04724D]/5 overflow-hidden relative group">
          <div class="relative z-10">
            <span class="text-[10px] font-black text-gray-400 dark:text-gray-600 uppercase tracking-[0.3em] block mb-5">تقييم السوق</span>
            <div class="text-3xl xl:text-4xl font-black text-gray-900 dark:text-white mb-3 tabular-nums font-mono">{{ formatCurrency(totalStockValue) }}</div>
            <div class="flex items-center gap-2 text-xs text-green-600 font-black">
               <ScaleIcon class="w-4 h-4" />
               إمكانات البيع
            </div>
          </div>
          <CurrencyDollarIcon class="absolute -left-6 -bottom-6 w-32 h-32 text-[#04724D]/[0.03] group-hover:scale-125 group-hover:-rotate-12 transition-all duration-700" />
        </div>

        <div class="bg-white dark:bg-[#0A0A0A] p-10 rounded-[48px] border border-gray-100 dark:border-gray-800 shadow-3xl shadow-[#04724D]/5 overflow-hidden relative group">
          <div class="relative z-10">
            <span class="text-[10px] font-black text-gray-400 dark:text-gray-600 uppercase tracking-[0.3em] block mb-5">تكلفة الاستحواذ</span>
            <div class="text-3xl xl:text-4xl font-black text-gray-900 dark:text-white mb-3 tabular-nums font-mono">{{ formatCurrency(totalStockCost) }}</div>
             <div class="flex items-center gap-2 text-xs text-gray-400 dark:text-gray-600 font-black italic">
               القيمة الدفترية
            </div>
          </div>
          <div class="absolute -left-6 -bottom-6 w-32 h-32 bg-[#04724D]/[0.01] rounded-full group-hover:scale-150 transition-transform duration-1000"></div>
        </div>

        <div class="bg-gradient-to-br from-[#04724D] to-[#035a3d] p-10 rounded-[48px] text-white overflow-hidden relative group shadow-3xl shadow-[#04724D]/30 border border-white/10">
          <div class="relative z-10">
            <span class="text-[10px] font-black text-white/50 uppercase tracking-[0.3em] block mb-5">أداء الأصول</span>
            <div class="text-4xl xl:text-5xl font-black mb-3 tabular-nums font-mono">+{{ ((totalStockValue - totalStockCost) / (totalStockCost || 1) * 100).toFixed(1) }}%</div>
            <div class="text-[10px] font-black uppercase tracking-widest text-[#3EFF8B]">عامل النمو المتوقع</div>
          </div>
          <ChartBarIcon class="absolute -left-6 -bottom-6 w-32 h-32 text-white/10 group-hover:scale-125 transition-transform duration-700" />
        </div>

        <div class="bg-white dark:bg-[#0A0A0A] p-10 rounded-[48px] border border-gray-100 dark:border-gray-800 shadow-3xl shadow-[#04724D]/5 relative group">
          <span class="text-[10px] font-black text-gray-400 dark:text-gray-600 uppercase tracking-[0.3em] block mb-5">التوزيع النشط</span>
          <div class="flex items-center justify-between">
             <div class="text-4xl xl:text-5xl font-black text-gray-900 dark:text-white tabular-nums font-mono">{{ warehouses.length }}</div>
             <div class="p-5 bg-[#04724D]/10 rounded-3xl text-[#04724D] dark:text-[#3EFF8B] group-hover:rotate-12 transition-transform duration-500">
                <BuildingStorefrontIcon class="w-8 h-8" />
             </div>
          </div>
          <div class="text-[10px] font-black text-gray-400 dark:text-gray-600 mt-4 uppercase tracking-[0.2em]">عُقد محققة</div>
        </div>
      </div>

      <!-- Detail Visuals -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
        <!-- Distribution Chart -->
        <div class="lg:col-span-8 bg-white dark:bg-[#0A0A0A] p-12 rounded-[56px] border border-gray-100 dark:border-gray-800 shadow-3xl shadow-[#04724D]/5 relative group overflow-hidden">
          <div class="absolute -top-32 -left-32 w-96 h-96 bg-[#04724D]/[0.02] blur-[120px] rounded-full pointer-events-none"></div>
          
          <div class="flex items-center justify-between mb-16 relative z-10">
            <div>
              <h3 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight">عامل حمولة المستودعات</h3>
              <p class="text-sm text-gray-500 font-bold mt-2">مقارنة بين عدد الأصناف الفريدة وإجمالي الكميات لكل محطة.</p>
            </div>
          </div>

          <div class="h-[450px] relative z-10 transition-all duration-1000" :class="{'opacity-20 grayscale': loading}">
            <apexchart 
              v-if="!loading && distributionChartSeries.length"
              type="bar" 
              height="100%" 
              :options="distributionChartOptions" 
              :series="distributionChartSeries" 
            />
            <div v-else class="h-full flex items-center justify-center select-none">
               <div class="text-center space-y-6">
                  <div class="relative w-20 h-20 mx-auto">
                    <CubeIcon class="w-20 h-20 text-[#04724D] animate-bounce opacity-20" />
                    <div class="absolute inset-0 border-4 border-[#04724D]/10 rounded-full animate-ping"></div>
                  </div>
                  <span class="text-xs font-black uppercase tracking-[0.4em] text-[#04724D] animate-pulse">تجميع عُقد المخزون...</span>
               </div>
            </div>
          </div>
        </div>

        <!-- Value Distribution -->
        <div class="lg:col-span-4 bg-white dark:bg-[#0A0A0A] p-12 rounded-[56px] border border-gray-100 dark:border-gray-800 shadow-3xl shadow-[#04724D]/5 flex flex-col relative overflow-hidden group">
           <div class="absolute -bottom-24 -right-24 w-64 h-64 bg-[#04724D]/[0.02] blur-[80px] rounded-full"></div>
           
           <div class="space-y-2 mb-12 relative z-10">
             <h3 class="text-xl font-black text-gray-900 dark:text-white tracking-tight">تركيز الأصول المالية</h3>
             <p class="text-sm text-gray-500 font-bold">الوزن المالي لكل عقدة توزيع في الشبكة.</p>
           </div>
           
           <div class="flex-1 space-y-8 overflow-y-auto pl-4 custom-scrollbar relative z-10">
              <div v-for="item in stockData.stock_value" :key="item.name" class="space-y-4 group/item">
                 <div class="flex items-center justify-between">
                    <span class="text-xs font-black text-gray-900 dark:text-white uppercase tracking-tight group-hover/item:text-[#04724D] transition-colors">{{ item.name }}</span>
                    <span class="text-sm font-black text-[#04724D] dark:text-[#3EFF8B] tabular-nums font-mono">{{ formatCurrency(item.value) }}</span>
                 </div>
                 <div class="w-full bg-gray-100 dark:bg-white/[0.03] h-2.5 rounded-full overflow-hidden p-0.5 border border-transparent group-hover/item:border-[#04724D]/10 transition-all">
                    <div 
                      class="bg-gradient-to-r from-[#04724D] to-[#10B981] h-full rounded-full transition-all duration-1000 shadow-lg shadow-[#04724D]/10"
                      :style="{ width: (totalStockValue > 0 ? (item.value / totalStockValue * 100) : 0) + '%' }"
                    ></div>
                 </div>
              </div>
           </div>

           <div class="mt-10 pt-10 border-t border-gray-100 dark:border-gray-800 relative z-10">
              <div class="flex items-center justify-between text-xs font-black uppercase tracking-[0.3em] text-[#04724D] dark:text-[#3EFF8B]">
                 <span>إجمالي الأصول الصافية</span>
                 <span class="text-lg tabular-nums font-mono">{{ formatCurrency(totalStockValue) }}</span>
              </div>
           </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 5px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #04724D11;
  border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #04724D33;
}
</style>
