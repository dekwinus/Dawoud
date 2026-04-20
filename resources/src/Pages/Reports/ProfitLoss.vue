<script setup>
import { Head } from '@inertiajs/vue3';
import { onMounted, ref, computed, watch } from 'vue';
import axios from 'axios';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { 
  Banknote as BanknotesIcon,
  ShoppingBag as ShoppingBagIcon,
  ShoppingCart as ShoppingCartIcon,
  RefreshCcw as ArrowPathRoundedSquareIcon,
  Calculator as CalculatorIcon,
  TrendingUp as ArrowTrendingUpIcon,
  TrendingDown as ArrowTrendingDownIcon,
  Filter as FunnelIcon,
  Calendar as CalendarIcon,
  Warehouse as BuildingStorefrontIcon,
  FileDown as DocumentArrowDownIcon
} from 'lucide-vue-next';

const props = defineProps({
  warehouses: Array,
});

const loading = ref(true);
const stats = ref({
  sales_sum: 0,
  sales_count: 0,
  purchases_sum: 0,
  purchases_count: 0,
  returns_sales_sum: 0,
  returns_sales_count: 0,
  returns_purchases_sum: 0,
  returns_purchases_count: 0,
  paiement_sales: 0,
  PaymentSaleReturns: 0,
  PaymentPurchaseReturns: 0,
  paiement_purchases: 0,
  expenses_sum: 0,
  product_cost_fifo: 0,
  averagecost: 0,
  profit_fifo: 0,
  profit_average_cost: 0,
  payment_received: 0,
  payment_sent: 0,
  paiement_net: 0,
  total_revenue: 0
});

const warehouse_id = ref('');
const dateRange = ref({
  from: new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().split('T')[0],
  to: new Date().toISOString().split('T')[0]
});

const loadReport = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/admin/reports/profit-loss-data', {
      params: {
        warehouse_id: warehouse_id.value,
        from: dateRange.value.from,
        to: dateRange.value.to
      }
    });
    stats.value = response.data.data;
  } catch (error) {
    console.error('Failed to load P&L report:', error);
  } finally {
    loading.value = false;
  }
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

// Computed for percentage indicators
const profitMargin = computed(() => {
  if (!stats.value.sales_sum) return 0;
  return ((stats.value.profit_fifo / stats.value.sales_sum) * 100).toFixed(1);
});

const expenseRatio = computed(() => {
  if (!stats.value.sales_sum) return 0;
  return ((stats.value.expenses_sum / stats.value.sales_sum) * 100).toFixed(1);
});

const printReport = () => window.print();

const exportInventoryCSV = () => {
  const rows = [
    ['\u0627\u0644\u0628\u064a\u0627\u0646', '\u0627\u0644\u0642\u064a\u0645\u0629'],
    ['\u0625\u062c\u0645\u0627\u0644\u064a \u0627\u0644\u0645\u0628\u064a\u0639\u0627\u062a', stats.value.sales_sum],
    ['\u0645\u0631\u062a\u062c\u0639\u0627\u062a \u0627\u0644\u0645\u0628\u064a\u0639\u0627\u062a', stats.value.returns_sales_sum],
    ['\u0625\u062c\u0645\u0627\u0644\u064a \u0627\u0644\u0645\u0634\u062a\u0631\u064a\u0627\u062a', stats.value.purchases_sum],
    ['\u062a\u0643\u0644\u0641\u0629 \u0627\u0644\u0628\u0636\u0627\u0639\u0629 \u0627\u0644\u0645\u0628\u0627\u0639\u0629 (FIFO)', stats.value.product_cost_fifo],
    ['\u0645\u0635\u0627\u0631\u064a\u0641 \u062a\u0634\u063a\u064a\u0644\u064a\u0629', stats.value.expenses_sum],
    ['\u0635\u0627\u0641\u064a \u0627\u0644\u0631\u0628\u062d (FIFO)', stats.value.profit_fifo],
    ['\u0635\u0627\u0641\u064a \u0627\u0644\u0631\u0628\u062d (\u0645\u062a\u0648\u0633\u0637)', stats.value.profit_average_cost],
    ['\u0625\u062c\u0645\u0627\u0644\u064a \u0627\u0644\u0645\u0642\u0628\u0648\u0636\u0627\u062a', stats.value.payment_received],
    ['\u0625\u062c\u0645\u0627\u0644\u064a \u0627\u0644\u0645\u062f\u0641\u0648\u0639\u0627\u062a', stats.value.payment_sent],
    ['\u0635\u0627\u0641\u064a \u0627\u0644\u062d\u0631\u0643\u0629 \u0627\u0644\u0646\u0642\u062f\u064a\u0629', stats.value.paiement_net],
  ];
  const csv = rows.map(r => r.map(v => JSON.stringify(v ?? '')).join(',')).join('\n');
  const blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' });
  const a = document.createElement('a');
  a.href = URL.createObjectURL(blob);
  a.download = `profit-loss-${dateRange.value.from}-${dateRange.value.to}.csv`;
  a.click();
  URL.revokeObjectURL(a.href);
};
</script>

<template>
  <AdminLayout>
    <Head title="تقرير الأرباح والخسائر - DawPOS" />
    <div class="space-y-12 pb-24 p-6 md:p-12 font-['Cairo']" dir="rtl">
      <!-- Header Section -->
      <div class="flex flex-col xl:flex-row xl:items-end justify-between gap-10">
        <div class="space-y-3">
          <div class="flex items-center gap-3 text-[#04724D] font-black text-xs uppercase tracking-[0.4em]">
            <CalculatorIcon class="w-5 h-5" />
            الذكاء المالي المتقدم
          </div>
          <h1 class="text-4xl md:text-6xl font-black text-gray-900 dark:text-white tracking-tight">تقرير الأرباح والخسائر</h1>
          <p class="text-gray-500 font-bold text-lg">تحليل شامل للربحية، التدفقات النقدية، والأصول المتداولة.</p>
        </div>

        <div class="flex flex-wrap items-center gap-4 bg-white dark:bg-[#1A1A1A] p-3 rounded-[32px] shadow-2xl shadow-[#04724D]/5 border border-gray-100 dark:border-gray-800">
           <div class="flex items-center gap-3 px-6 py-4 bg-gray-50 dark:bg-white/5 rounded-2xl border border-gray-100 dark:border-gray-800 group">
                <BuildingStorefrontIcon class="h-5 w-5 text-gray-400 group-hover:text-[#04724D] transition-colors" />
                <select 
                    v-model="warehouse_id" 
                    @change="loadReport"
                    class="bg-transparent border-none text-sm font-black text-gray-700 dark:text-gray-200 focus:ring-0 p-0 pr-10 appearance-none"
                >
                    <option value="">جميع الفروع والشبكات</option>
                    <option v-for="w in warehouses" :key="w.id" :value="w.id">{{ w.name }}</option>
                </select>
           </div>
           
           <div class="h-10 w-px bg-gray-200 dark:bg-gray-800 hidden xl:block"></div>

           <div class="flex items-center gap-6 px-6 py-4 bg-gray-50 dark:bg-white/5 rounded-2xl border border-gray-100 dark:border-gray-800">
                <CalendarIcon class="h-5 w-5 text-gray-400" />
                <div class="flex items-center gap-3 font-black text-xs text-gray-700 dark:text-gray-300 font-mono tabular-nums">
                    <input type="date" v-model="dateRange.from" @change="loadReport" class="bg-transparent border-none focus:ring-0 p-0 cursor-pointer" />
                    <span class="text-gray-300">→</span>
                    <input type="date" v-model="dateRange.to" @change="loadReport" class="bg-transparent border-none focus:ring-0 p-0 cursor-pointer" />
                </div>
           </div>
           
           <button @click="loadReport" class="p-4 bg-[#04724D] text-[#3EFF8B] rounded-2xl hover:bg-opacity-90 transition-all shadow-xl shadow-[#04724D]/20 active:scale-95 group">
             <ArrowPathRoundedSquareIcon class="w-6 h-6" :class="{'animate-spin': loading}" />
           </button>
        </div>
      </div>

      <!-- Hero Analytics Board -->
      <div class="bg-white dark:bg-[#0A0A0A] rounded-[56px] border border-gray-100 dark:border-gray-800 shadow-3xl shadow-[#04724D]/5 overflow-hidden relative">
        <!-- Luxury Overlays -->
        <div class="absolute top-0 right-0 w-1/2 h-full bg-gradient-to-l from-[#04724D]/[0.02] to-transparent pointer-events-none"></div>
        <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-[#3EFF8B]/[0.03] blur-[120px] rounded-full"></div>
        
        <div class="p-16 relative z-10 grid grid-cols-1 lg:grid-cols-12 gap-20">
          <!-- Main Profit Gauge -->
          <div class="lg:col-span-12 xl:col-span-5 flex flex-col justify-center">
            <div class="space-y-8">
              <div class="inline-flex items-center gap-3 px-5 py-2.5 bg-[#04724D]/10 rounded-2xl border border-[#04724D]/10">
                <div class="w-2.5 h-2.5 rounded-full bg-[#04724D] animate-pulse"></div>
                <span class="text-[11px] font-black text-[#04724D] uppercase tracking-widest">صافي الربح الفعلي (FIFO)</span>
              </div>
              
              <div class="space-y-2">
                <h2 class="text-8xl font-black text-[#04724D] dark:text-[#3EFF8B] tracking-tighter font-mono tabular-nums leading-none">
                  {{ formatCurrency(stats.profit_fifo) }}
                </h2>
                <div class="flex items-center gap-3 text-2xl font-black text-gray-400 opacity-60">
                   <ArrowTrendingUpIcon class="w-8 h-8 text-[#04724D]" />
                   <span>{{ profitMargin }}% <span class="text-sm uppercase tracking-widest mr-2">هامش الربحية</span></span>
                </div>
              </div>

              <div class="grid grid-cols-2 gap-8 pt-8">
                <div class="space-y-2">
                   <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">إجمالي الإيرادات</p>
                   <p class="text-2xl font-black text-gray-900 dark:text-white tabular-nums font-mono">{{ formatCurrency(stats.sales_sum) }}</p>
                </div>
                <div class="space-y-2">
                   <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">إجمالي المرتجعات</p>
                   <p class="text-2xl font-black text-orange-500 tabular-nums font-mono">{{ formatCurrency(stats.returns_sales_sum) }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Divider -->
          <div class="hidden xl:block lg:col-span-1 flex items-center justify-center">
             <div class="w-px h-64 bg-gradient-to-b from-transparent via-gray-200 dark:via-gray-800 to-transparent"></div>
          </div>

          <!-- Ops/Cash Breakdown -->
          <div class="lg:col-span-12 xl:col-span-6 grid grid-cols-1 md:grid-cols-2 gap-12">
             <!-- Operational Expenses -->
             <div class="space-y-8">
                <h4 class="text-xs font-black text-gray-400 uppercase tracking-[0.3em] flex items-center gap-3">
                   <div class="p-2 bg-red-500/10 rounded-xl">
                    <TrendingDownIcon class="w-4 h-4 text-red-500" />
                   </div>
                   المصروفات التشغيلية
                </h4>
                <div class="space-y-4">
                   <div class="p-6 bg-gray-50 dark:bg-white/[0.02] rounded-[32px] border border-gray-100 dark:border-gray-800 flex justify-between items-center group hover:bg-red-500/5 transition-all">
                      <div>
                        <p class="text-[9px] font-black text-gray-400 uppercase mb-1">المصاريف النثرية</p>
                        <p class="text-xl font-black text-gray-900 dark:text-white tabular-nums">{{ formatCurrency(stats.expenses_sum) }}</p>
                      </div>
                      <span class="text-[10px] font-black text-red-500">{{ expenseRatio }}% نسبة</span>
                   </div>
                   <div class="p-6 bg-gray-50 dark:bg-white/[0.02] rounded-[32px] border border-gray-100 dark:border-gray-800 flex justify-between items-center group hover:bg-[#04724D]/5 transition-all">
                      <div>
                        <p class="text-[9px] font-black text-gray-400 uppercase mb-1">تكلفة البضاعة المباعة</p>
                        <p class="text-xl font-black text-gray-900 dark:text-white tabular-nums">{{ formatCurrency(stats.product_cost_fifo) }}</p>
                      </div>
                      <span class="text-[10px] font-black text-gray-500">COGS</span>
                   </div>
                </div>
             </div>

             <!-- Inflow/Outflow -->
             <div class="space-y-8">
                <h4 class="text-xs font-black text-gray-400 uppercase tracking-[0.3em] flex items-center gap-3">
                   <div class="p-2 bg-blue-500/10 rounded-xl">
                    <BanknotesIcon class="w-4 h-4 text-blue-500" />
                   </div>
                   التدفقات النقدية
                </h4>
                <div class="space-y-4">
                   <div class="p-6 bg-[#3EFF8B]/[0.02] dark:bg-[#3EFF8B]/[0.05] rounded-[32px] border border-[#3EFF8B]/20 flex justify-between items-center group hover:bg-[#3EFF8B]/10 transition-all">
                      <div>
                        <p class="text-[9px] font-black text-[#04724D] dark:text-[#3EFF8B] uppercase mb-1">إجمالي المقبوضات</p>
                        <p class="text-xl font-black text-[#04724D] dark:text-[#3EFF8B] tabular-nums">{{ formatCurrency(stats.payment_received) }}</p>
                      </div>
                      <ArrowTrendingUpIcon class="w-5 h-5 text-[#04724D] dark:text-[#3EFF8B]" />
                   </div>
                   <div class="p-6 bg-red-50 dark:bg-red-500/[0.05] rounded-[32px] border border-red-100 dark:border-red-500/20 flex justify-between items-center group hover:bg-red-500/10 transition-all">
                      <div>
                        <p class="text-[9px] font-black text-red-700 dark:text-red-400 uppercase mb-1">إجمالي المدفوعات</p>
                        <p class="text-xl font-black text-red-600 tabular-nums">{{ formatCurrency(stats.payment_sent) }}</p>
                      </div>
                      <ArrowTrendingDownIcon class="w-5 h-5 text-red-500" />
                   </div>
                </div>
             </div>
          </div>
        </div>

        <!-- Global Summary Bar -->
        <div class="px-16 py-12 bg-gray-50 dark:bg-white/[0.03] border-t border-gray-100 dark:border-gray-800 flex flex-col md:flex-row items-center justify-between gap-12">
            <div class="flex items-center flex-wrap gap-16">
               <div class="space-y-1">
                 <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">صافي الحركة النقدية</p>
                 <p class="text-3xl font-black tabular-nums tracking-tighter" :class="stats.paiement_net >=0 ? 'text-[#04724D] dark:text-[#3EFF8B]' : 'text-red-600'">
                   {{ stats.paiement_net >= 0 ? '+' : '' }}{{ formatCurrency(stats.paiement_net) }}
                 </p>
               </div>
               <div class="w-px h-12 bg-gray-200 dark:bg-gray-800 hidden md:block"></div>
               <div class="space-y-1">
                 <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">صافي الإيرادات</p>
                 <p class="text-3xl font-black text-gray-900 dark:text-white tabular-nums tracking-tighter">{{ formatCurrency(stats.total_revenue) }}</p>
               </div>
            </div>
            
            <button @click="printReport" class="px-10 py-5 bg-white dark:bg-[#121212] border border-gray-200 dark:border-gray-800 rounded-3xl text-sm font-black uppercase tracking-widest shadow-xl shadow-gray-200/50 dark:shadow-none hover:shadow-2xl transition-all border-b-4 border-b-[#04724D] active:border-b-0 active:translate-y-1 flex items-center gap-4">
               <DocumentArrowDownIcon class="w-6 h-6 text-[#04724D]" />
               طباعة / PDF
            </button>
        </div>
      </div>

      <!-- Logic Compare & Inventory Value -->
      <div class="grid grid-cols-1 xl:grid-cols-3 gap-12">
         <!-- Valuation Logic -->
         <div class="xl:col-span-2 bg-white dark:bg-[#0A0A0A] p-12 rounded-[56px] border border-gray-100 dark:border-gray-800 shadow-sm flex flex-col justify-between">
            <div class="space-y-3 mb-12">
               <h3 class="text-3xl font-black text-gray-900 dark:text-white tracking-tight">مقارنة منطق التقييم المالي</h3>
               <p class="text-gray-400 font-bold">المقارنة بين طريقتي الجرد المستمر (FIFO vs Average Cost) في احتساب الربحية.</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-16">
               <div class="space-y-5">
                  <span class="text-[11px] font-black text-gray-400 uppercase tracking-widest">الربح (متوسط التكلفة)</span>
                  <div class="text-5xl font-black text-gray-900 dark:text-white tabular-nums tracking-tighter">{{ formatCurrency(stats.profit_average_cost) }}</div>
                  <div class="w-full bg-gray-100 dark:bg-gray-800 h-3.5 rounded-full overflow-hidden">
                    <div class="bg-gray-400 dark:bg-gray-600 h-full rounded-full transition-all duration-1000" :style="{ width: (stats.profit_average_cost / stats.profit_fifo * 100) + '%' }"></div>
                  </div>
               </div>
               <div class="space-y-5">
                  <span class="text-[11px] font-black text-[#04724D] uppercase tracking-widest">الربح (FIFO) - الأداء الأمثل</span>
                  <div class="text-5xl font-black text-[#04724D] dark:text-[#3EFF8B] tabular-nums tracking-tighter">{{ formatCurrency(stats.profit_fifo) }}</div>
                  <div class="w-full bg-gray-100 dark:bg-gray-800 h-3.5 rounded-full overflow-hidden p-0.5">
                    <div class="bg-gradient-to-r from-[#04724D] to-[#3EFF8B] h-full rounded-full transition-all duration-1000 shadow-lg shadow-[#04724D]/40" style="width: 100%"></div>
                  </div>
               </div>
            </div>
         </div>

         <!-- Inventory Valuation -->
         <div class="bg-[#04724D] p-12 rounded-[56px] text-white flex flex-col justify-between relative overflow-hidden group border border-white/5 shadow-3xl shadow-[#04724D]/40">
            <BanknotesIcon class="absolute -right-20 -bottom-20 w-80 h-80 text-white/5 group-hover:scale-125 group-hover:rotate-12 transition-transform duration-1000" />
            
            <div class="relative z-10 space-y-10">
               <div class="p-6 bg-white/10 rounded-[32px] w-fit backdrop-blur-3xl border border-white/10">
                  <ArrowTrendingUpIcon class="w-10 h-10 text-[#3EFF8B]" />
               </div>
               <div class="space-y-4">
                  <p class="text-[11px] font-black text-white/60 uppercase tracking-[0.4em]">إجمالي قيمة الأصول (المخزون)</p>
                  <h3 class="text-6xl font-black text-white tracking-tighter tabular-nums leading-none">{{ formatCurrency(stats.product_cost_fifo) }}</h3>
                  <p class="text-xs font-bold text-white/40 italic">القيمة السوقية الحالية بناءً على سياسة FIFO</p>
               </div>
            </div>

            <button @click="exportInventoryCSV" class="relative z-10 w-full py-6 bg-white text-[#04724D] rounded-[24px] text-xs font-black uppercase tracking-[0.3em] shadow-2xl hover:bg-[#3EFF8B] transition-all transform hover:scale-105 active:scale-95 mt-10">
               تصدير CSV
            </button>
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
