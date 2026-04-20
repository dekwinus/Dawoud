<script setup>
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import axios from 'axios';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { RotateCcw, Search, Trash2, Eye, Printer } from 'lucide-vue-next';

const props = defineProps({ warehouses: Array });

const returns = ref([]);
const totalRows = ref(0);
const loading = ref(false);
const filters = ref({ search: '', warehouse_id: '', page: 1, limit: 10, SortField: 'id', SortType: 'desc' });

const showDetails = ref(false);
const selectedReturn = ref(null);
const returnDetails = ref([]);
const loadingDetails = ref(false);

const fetchReturns = async () => {
  loading.value = true;
  try {
    const { data } = await axios.get('/api/returns/sale', { params: filters.value });
    returns.value = data.sale_returns || [];
    totalRows.value = data.totalRows || 0;
  } finally { loading.value = false; }
};

const openDetails = async (r) => {
  selectedReturn.value = r;
  returnDetails.value = [];
  loadingDetails.value = true;
  showDetails.value = true;
  try {
    const { data } = await axios.get(`/api/returns/sale/${r.id}`);
    returnDetails.value = data.details || data.return_details || [];
  } catch {} finally { loadingDetails.value = false; }
};

const deleteReturn = async (id) => {
  if (!confirm('حذف هذا المرتجع؟')) return;
  await axios.delete(`/api/returns/sale/${id}`);
  fetchReturns();
};

const statusClass = (s) => ({ completed: 'bg-green-100 text-green-700', pending: 'bg-orange-100 text-orange-700' }[s] || 'bg-gray-100 text-gray-600');

onMounted(fetchReturns);
</script>

<template>
  <AdminLayout>
    <Head title="مرتجعات المبيعات" />
    <div class="space-y-8 p-6 md:p-10 font-['Cairo']" dir="rtl">
      <div>
        <div class="flex items-center gap-2 text-orange-500 text-xs font-black uppercase tracking-widest mb-1"><RotateCcw class="w-4 h-4" />مرتجعات المبيعات</div>
        <h1 class="text-4xl font-black text-gray-900 dark:text-white">مرتجعات المبيعات</h1>
      </div>

      <div class="bg-white dark:bg-[#0A0A0A] rounded-[40px] border border-gray-100 dark:border-gray-800 overflow-hidden">
        <div class="p-8 border-b border-gray-100 dark:border-gray-800 flex flex-wrap gap-4">
          <div class="relative flex-1 min-w-[260px]">
            <Search class="absolute right-4 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" />
            <input v-model="filters.search" @input="filters.page=1; fetchReturns()" placeholder="بحث..." class="w-full pr-12 pl-4 py-4 bg-gray-50 dark:bg-white/5 border-none rounded-2xl text-sm font-black" />
          </div>
          <select v-model="filters.warehouse_id" @change="filters.page=1; fetchReturns()" class="bg-gray-50 dark:bg-white/5 border-none rounded-2xl px-4 py-4 text-sm font-black text-gray-700 dark:text-gray-300 min-w-[200px]">
            <option value="">كل المستودعات</option>
            <option v-for="w in warehouses" :key="w.id" :value="w.id">{{ w.name }}</option>
          </select>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-right">
            <thead><tr class="text-[10px] font-black text-gray-400 uppercase tracking-widest bg-gray-50/50 dark:bg-white/[0.01]">
              <th class="px-8 py-6">التاريخ</th><th class="px-6 py-6">المرجع</th><th class="px-6 py-6">العميل</th><th class="px-6 py-6">الإجمالي</th><th class="px-6 py-6">الحالة</th><th class="px-6 py-6">إجراءات</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
              <tr v-if="loading" v-for="i in 5" :key="i" class="animate-pulse"><td v-for="j in 6" :key="j" class="px-6 py-5"><div class="h-5 bg-gray-100 dark:bg-white/5 rounded-xl"></div></td></tr>
              <tr v-else-if="returns.length===0"><td colspan="6" class="py-20 text-center text-gray-400 font-black text-sm">لا توجد مرتجعات</td></tr>
              <tr v-for="r in returns" :key="r.id" class="group hover:bg-gray-50/50 dark:hover:bg-white/[0.01] transition-all">
                <td class="px-8 py-5 text-xs font-black text-gray-500 dark:text-gray-400">{{ r.date }}</td>
                <td class="px-6 py-5 font-mono font-black text-orange-500">{{ r.Ref }}</td>
                <td class="px-6 py-5 text-sm font-black text-gray-700 dark:text-gray-300">{{ r.client_name || '—' }}</td>
                <td class="px-6 py-5 text-sm font-black text-gray-900 dark:text-white">{{ Number(r.GrandTotal||0).toLocaleString('ar-EG') }}</td>
                <td class="px-6 py-5"><span :class="statusClass(r.statut)" class="px-3 py-1 rounded-full text-[10px] font-black">{{ r.statut }}</span></td>
                <td class="px-6 py-5">
                  <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-all">
                    <button @click="openDetails(r)" class="p-3 bg-white dark:bg-[#1A1A1A] text-gray-400 hover:text-[#04724D] rounded-2xl border border-gray-100 dark:border-gray-800 transition-all"><Eye class="h-4 w-4" /></button>
                    <a :href="`/sale_return_pdf/${r.id}`" target="_blank" class="p-3 bg-white dark:bg-[#1A1A1A] text-gray-400 hover:text-blue-500 rounded-2xl border border-gray-100 dark:border-gray-800 transition-all flex"><Printer class="h-4 w-4" /></a>
                    <button @click="deleteReturn(r.id)" class="p-3 bg-white dark:bg-[#1A1A1A] text-gray-400 hover:text-red-500 rounded-2xl border border-gray-100 dark:border-gray-800 transition-all"><Trash2 class="h-4 w-4" /></button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="p-8 bg-gray-50/50 dark:bg-white/[0.02] border-t border-gray-100 dark:border-gray-800 flex items-center justify-between">
          <p class="text-xs font-black text-gray-400">الإجمالي: <span class="text-orange-500">{{ totalRows }}</span></p>
          <div class="flex gap-4">
            <button @click="filters.page--; fetchReturns()" :disabled="filters.page===1" class="text-xs font-black text-[#04724D] dark:text-[#3EFF8B] disabled:opacity-20">السابق</button>
            <button @click="filters.page++; fetchReturns()" :disabled="returns.length < filters.limit" class="text-xs font-black text-[#04724D] dark:text-[#3EFF8B] disabled:opacity-20">التالي</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Details Modal -->
    <div v-if="showDetails" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
      <div class="bg-white dark:bg-[#121212] rounded-[32px] w-full max-w-2xl p-8 shadow-2xl max-h-[90vh] overflow-y-auto space-y-4">
        <div class="flex items-center justify-between">
          <div><h2 class="text-xl font-black text-gray-900 dark:text-white">تفاصيل المرتجع</h2><p class="text-xs font-mono text-orange-500 mt-1">{{ selectedReturn?.Ref }}</p></div>
          <button @click="showDetails=false" class="px-4 py-2 rounded-xl bg-gray-100 dark:bg-white/10 text-sm font-black">إغلاق</button>
        </div>
        <div v-if="loadingDetails" class="text-center py-6 text-gray-400 text-sm">جاري التحميل...</div>
        <div v-else class="border border-gray-100 dark:border-gray-800 rounded-2xl overflow-hidden">
          <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-white/5"><tr class="text-[10px] font-black text-gray-400 uppercase tracking-widest"><th class="px-4 py-3 text-right">المنتج</th><th class="px-4 py-3 text-right">الكمية</th><th class="px-4 py-3 text-right">السعر</th></tr></thead>
            <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
              <tr v-if="returnDetails.length===0"><td colspan="3" class="px-4 py-6 text-center text-gray-400 text-sm">لا تفاصيل</td></tr>
              <tr v-for="(d,i) in returnDetails" :key="i">
                <td class="px-4 py-3 font-black dark:text-gray-200">{{ d.product_name||d.name||'—' }}</td>
                <td class="px-4 py-3 font-mono font-black dark:text-gray-200">{{ d.quantity }}</td>
                <td class="px-4 py-3 font-mono font-black dark:text-gray-200">{{ Number(d.price||d.Unit_price||0).toLocaleString('ar-EG') }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
