<script setup>
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref, onMounted } from 'vue';
import { 
  Plus, 
  Search, 
  Filter, 
  Download, 
  Archive, 
  MoreHorizontal, 
  Eye, 
  Edit, 
  Trash2,
  Calendar,
  Warehouse,
  Package,
  ArrowUpDown,
  History
} from 'lucide-vue-next';
import axios from 'axios';
import { router } from '@inertiajs/vue3';

const props = defineProps({
  warehouses: Array
});

const adjustments = ref([]);
const totalRows = ref(0);
const loading = ref(false);
const filters = ref({
  search: '',
  warehouse_id: '',
  date: '',
  page: 1,
  limit: 10,
  SortField: 'id',
  SortType: 'desc'
});

const fetchAdjustments = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/adjustments', { params: filters.value });
        adjustments.value = response.data.adjustments || [];
        totalRows.value = response.data.totalRows || 0;
    } catch (e) {
        console.error("Failed to fetch adjustments", e);
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchAdjustments();
});

const handleFilter = () => {
    filters.value.page = 1;
    fetchAdjustments();
};

const deleteAdjustment = async (id) => {
    if (!confirm('هل أنت متأكد من حذف هذا التعديل؟')) return;
    try {
        await axios.delete(`/api/adjustments/${id}`);
        fetchAdjustments();
    } catch (e) {
        alert(e.response?.data?.message || 'تعذر الحذف');
    }
};

// Details modal
const showDetails = ref(false);
const selectedAdj = ref(null);
const adjDetails = ref([]);
const loadingDetails = ref(false);

const openDetails = async (adj) => {
    selectedAdj.value = adj;
    adjDetails.value = [];
    loadingDetails.value = true;
    showDetails.value = true;
    try {
        const { data } = await axios.get(`/api/adjustments/${adj.id}`);
        adjDetails.value = data.details || [];
    } catch (e) { console.error(e); }
    finally { loadingDetails.value = false; }
};
</script>

<template>
  <AdminLayout>
    <Head title="تسويات المخزون - DawPOS" />
    <div class="space-y-12 pb-24 p-6 md:p-12 font-['Cairo']" dir="rtl">
      <!-- Header -->
      <div class="flex flex-col md:flex-row md:items-end justify-between gap-10">
        <div class="space-y-3">
          <div class="flex items-center gap-3 text-[#04724D] font-black text-xs uppercase tracking-[0.4em]">
            <History class="w-5 h-5" />
            إدارة المخزون
          </div>
          <h1 class="text-4xl md:text-6xl font-black text-gray-900 dark:text-white tracking-tight">بروتوكول تسوية المخزون</h1>
          <p class="text-gray-500 font-bold text-lg">تحرير وتتبع تصحيحات المخزون اليدوية وبروتوكولات الجرد.</p>
        </div>
        <div class="flex items-center gap-5">
            <button @click="router.visit('/admin/adjustments/create')" class="bg-[#04724D] hover:bg-[#035a3d] text-[#3EFF8B] px-10 py-5 rounded-[24px] font-black text-xs uppercase tracking-[0.2em] shadow-2xl shadow-[#04724D]/30 transition-all flex items-center gap-3 active:scale-95 group">
                <Plus class="h-5 w-5 group-hover:rotate-90 transition-transform" />
                تسوية جديدة
            </button>
        </div>
      </div>

      <!-- Main Board -->
      <div class="bg-white dark:bg-[#0A0A0A] rounded-[56px] border border-gray-100 dark:border-gray-800 shadow-3xl shadow-[#04724D]/5 overflow-hidden relative">
        <!-- Abstract Decoration -->
        <div class="absolute -top-24 -left-24 w-96 h-96 bg-[#04724D]/[0.02] blur-[120px] rounded-full pointer-events-none"></div>

        <!-- Filter Bar -->
        <div class="p-10 border-b border-gray-100 dark:border-gray-800 flex flex-wrap items-center gap-6 relative z-10">
           <div class="flex-1 min-w-[400px] relative group">
              <Search class="absolute right-6 top-1/2 -translate-y-1/2 h-6 w-6 text-gray-400 group-focus-within:text-[#04724D] transition-all" />
              <input 
                v-model="filters.search" 
                @input="handleFilter"
                type="text" 
                placeholder="البحث برقم المرجع الداخلي..." 
                class="w-full pr-16 pl-6 py-5 bg-gray-50 dark:bg-white/[0.03] border-none rounded-[24px] text-sm font-black focus:ring-4 focus:ring-[#04724D]/10 transition-all placeholder:text-gray-400 dark:placeholder:text-gray-600"
              />
           </div>

           <div class="flex items-center gap-4">
              <div class="relative group">
                  <Warehouse class="absolute right-5 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400 group-hover:text-[#04724D] transition-colors" />
                  <select v-model="filters.warehouse_id" @change="handleFilter" class="bg-gray-50 dark:bg-white/[0.03] border-none rounded-[24px] pr-14 pl-10 py-5 text-xs font-black text-gray-700 dark:text-gray-300 focus:ring-4 focus:ring-[#04724D]/10 min-w-[240px] appearance-none">
                      <option value="">جميع المستودعات</option>
                      <option v-for="w in warehouses" :key="w.id" :value="w.id">{{ w.name }}</option>
                  </select>
              </div>

               <button class="p-5 bg-gray-50 dark:bg-white/[0.03] text-gray-400 rounded-[22px] hover:text-[#04724D] hover:bg-white dark:hover:bg-white/5 transition-all border border-transparent hover:border-gray-100 dark:hover:border-white/10 group">
                  <Filter class="h-6 w-6 group-hover:rotate-12 transition-transform" />
              </button>
           </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto relative z-10">
          <table class="w-full text-right">
            <thead>
              <tr class="text-[10px] font-black text-gray-400 uppercase tracking-[0.4em] bg-gray-50/50 dark:bg-white/[0.01]">
                <th class="px-12 py-8">تاريخ التسوية</th>
                <th class="px-8 py-8">المرجع</th>
                <th class="px-8 py-8">المستودع</th>
                <th class="px-8 py-8">إجمالي الأصناف</th>
                <th class="px-12 py-8 text-left">إجراءات البروتوكول</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
              <tr v-if="loading" v-for="i in 5" :key="i" class="animate-pulse">
                  <td v-for="j in 5" :key="j" class="px-8 py-8"><div class="h-6 bg-gray-100 dark:bg-white/5 rounded-xl w-full"></div></td>
              </tr>
              
              <tr v-else-if="adjustments.length === 0" class="text-center">
                  <td colspan="5" class="py-32">
                     <div class="flex flex-col items-center gap-6 opacity-20">
                        <History class="w-20 h-20" />
                        <span class="text-xs font-black uppercase tracking-[0.4em]">لا يوجد سجلات تسوية مدققة حالياً</span>
                     </div>
                  </td>
              </tr>

              <tr v-for="adjustment in adjustments" :key="adjustment.id" class="group hover:bg-gray-50/50 dark:hover:bg-white/[0.01] transition-all duration-500">
                <td class="px-12 py-8 text-xs font-black text-gray-500 dark:text-gray-400 whitespace-nowrap tabular-nums">
                   <div class="flex items-center gap-3">
                       <Calendar class="h-4 w-4 opacity-40 group-hover:text-[#04724D] transition-colors" />
                       {{ adjustment.date }}
                   </div>
                </td>
                <td class="px-8 py-8 text-sm font-black text-[#04724D] dark:text-[#3EFF8B] tracking-tighter uppercase font-mono">{{ adjustment.Ref }}</td>
                <td class="px-8 py-8">
                   <div class="flex items-center gap-3">
                       <Warehouse class="h-4 w-4 opacity-40" />
                       <span class="text-xs font-black text-gray-800 dark:text-gray-200">{{ adjustment.warehouse_name }}</span>
                   </div>
                </td>
                <td class="px-8 py-8">
                   <div class="flex items-center gap-3">
                       <div class="p-2.5 bg-[#04724D]/5 rounded-xl">
                          <Package class="h-4 w-4 text-[#04724D] dark:text-[#3EFF8B]" />
                       </div>
                       <span class="text-sm font-black text-gray-800 dark:text-white tabular-nums font-mono">{{ adjustment.items }} <span class="text-[10px] font-black text-gray-400 mr-1 uppercase">صنف</span></span>
                   </div>
                </td>
                <td class="px-12 py-8">
                   <div class="flex items-center justify-end gap-3 opacity-0 group-hover:opacity-100 translate-x-4 group-hover:translate-x-0 transition-all duration-500">
                      <button @click="openDetails(adjustment)" class="p-4 bg-white dark:bg-[#1A1A1A] text-gray-400 hover:text-[#04724D] rounded-[20px] border border-gray-100 dark:border-gray-800 transition-all shadow-sm active:scale-90" title="عرض التفاصيل">
                         <Eye class="h-5 w-5" />
                      </button>
                      <a :href="`/api/adjustment_pdf/${adjustment.id}`" target="_blank" class="p-4 bg-white dark:bg-[#1A1A1A] text-gray-400 hover:text-blue-500 rounded-[20px] border border-gray-100 dark:border-gray-800 transition-all shadow-sm active:scale-90 flex items-center" title="طباعة">
                         <Edit class="h-5 w-5" />
                      </a>
                      <button @click="deleteAdjustment(adjustment.id)" class="p-4 bg-white dark:bg-[#1A1A1A] text-gray-400 hover:text-red-500 rounded-[20px] border border-gray-100 dark:border-gray-800 transition-all shadow-sm active:scale-90">
                         <Trash2 class="h-5 w-5" />
                      </button>
                   </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="p-12 bg-gray-50/50 dark:bg-white/[0.04] border-t border-gray-100 dark:border-gray-800 flex flex-col md:flex-row md:items-center justify-between gap-10 relative z-10">
            <p class="text-xs font-black text-gray-400 uppercase tracking-[0.3em]">تتبع <span class="text-[#04724D] dark:text-[#3EFF8B] mx-2">{{ totalRows }}</span> سجل تصحيح إجمالي</p>
            <div class="flex items-center gap-8">
                <button @click="filters.page--; fetchAdjustments()" :disabled="filters.page === 1" class="text-[10px] font-black uppercase tracking-[0.3em] text-[#04724D] dark:text-[#3EFF8B] disabled:opacity-20 hover:translate-x-1 transition-transform">الصفحة السابقة</button>
                <div class="w-px h-6 bg-gray-200 dark:bg-gray-800"></div>
                <button @click="filters.page++; fetchAdjustments()" :disabled="adjustments.length < filters.limit" class="text-[10px] font-black uppercase tracking-[0.3em] text-[#04724D] dark:text-[#3EFF8B] disabled:opacity-20 hover:-translate-x-1 transition-transform">الصفحة التالية</button>
            </div>
        </div>
      </div>
    </div>

    <!-- Details Modal -->
    <div v-if="showDetails" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
      <div class="bg-white dark:bg-[#121212] rounded-[32px] w-full max-w-3xl max-h-[90vh] overflow-y-auto shadow-2xl">
        <div class="p-8 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
          <div>
            <h2 class="text-xl font-black text-gray-900 dark:text-white font-['Cairo']">تفاصيل التسوية</h2>
            <p class="text-xs text-gray-400 font-mono mt-1">{{ selectedAdj?.Ref }} — {{ selectedAdj?.date }}</p>
          </div>
          <button @click="showDetails = false" class="p-2 rounded-xl bg-gray-100 dark:bg-white/5 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 transition-all text-sm font-black px-4">إغلاق</button>
        </div>
        <div class="p-8">
          <div class="grid grid-cols-2 gap-4 mb-6 text-sm">
            <div class="bg-gray-50 dark:bg-white/5 rounded-2xl p-4">
              <span class="text-xs text-gray-400 block mb-1">المستودع</span>
              <span class="font-black text-gray-800 dark:text-white">{{ selectedAdj?.warehouse_name }}</span>
            </div>
            <div class="bg-gray-50 dark:bg-white/5 rounded-2xl p-4">
              <span class="text-xs text-gray-400 block mb-1">إجمالي الأصناف</span>
              <span class="font-black text-gray-800 dark:text-white">{{ selectedAdj?.items }} صنف</span>
            </div>
          </div>
          <div v-if="loadingDetails" class="text-center py-8 text-gray-400 text-sm">جاري التحميل...</div>
          <div v-else-if="adjDetails.length === 0" class="text-center py-8 text-gray-400 text-sm">لا توجد تفاصيل</div>
          <div v-else class="overflow-x-auto border border-gray-100 dark:border-gray-800 rounded-2xl">
            <table class="w-full text-sm">
              <thead class="bg-gray-50 dark:bg-white/5">
                <tr class="text-[10px] font-black text-gray-400 uppercase tracking-widest">
                  <th class="px-5 py-3 text-right">المنتج</th>
                  <th class="px-5 py-3 text-right">الكمية</th>
                  <th class="px-5 py-3 text-right">النوع</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                <tr v-for="(d, i) in adjDetails" :key="i">
                  <td class="px-5 py-3 font-black text-gray-800 dark:text-gray-200">{{ d.product_name || d.code || '—' }}</td>
                  <td class="px-5 py-3 font-mono font-black" :class="d.type === 'add' ? 'text-green-600' : 'text-red-500'">
                    {{ d.type === 'add' ? '+' : '-' }}{{ d.quantity }}
                  </td>
                  <td class="px-5 py-3 text-xs">
                    <span class="px-3 py-1 rounded-full font-black" :class="d.type === 'add' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'">
                      {{ d.type === 'add' ? 'إضافة' : 'خصم' }}
                    </span>
                  </td>
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
/* Specific Select overrides for Arabic alignment if needed, otherwise parent layout handles it */
</style>
