<script setup>
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import axios from 'axios';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { AlertTriangle, Plus, Search, Trash2, Eye } from 'lucide-vue-next';

const props = defineProps({ warehouses: Array });

const damages = ref([]);
const totalRows = ref(0);
const loading = ref(false);
const filters = ref({ search: '', warehouse_id: '', page: 1, limit: 10, SortField: 'id', SortType: 'desc' });

const showDetails = ref(false);
const selectedDamage = ref(null);
const detailItems = ref([]);
const loadingDetails = ref(false);

const fetchDamages = async () => {
  loading.value = true;
  try {
    const { data } = await axios.get('/api/damages', { params: filters.value });
    damages.value = data.damages || [];
    totalRows.value = data.totalRows || 0;
  } finally { loading.value = false; }
};

const openDetails = async (d) => {
  selectedDamage.value = d;
  detailItems.value = [];
  loadingDetails.value = true;
  showDetails.value = true;
  try {
    const { data } = await axios.get(`/api/damages/detail/${d.id}`);
    detailItems.value = data.details || [];
  } finally { loadingDetails.value = false; }
};

const deleteDamage = async (id) => {
  if (!confirm('حذف سجل التلف؟')) return;
  await axios.delete(`/api/damages/${id}`);
  fetchDamages();
};

onMounted(fetchDamages);
</script>

<template>
  <AdminLayout>
    <Head title="سجل التلف" />
    <div class="space-y-8 p-6 md:p-10 font-['Cairo']" dir="rtl">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
          <div class="flex items-center gap-2 text-red-500 text-xs font-black uppercase tracking-widest mb-1"><AlertTriangle class="w-4 h-4" />إدارة التلف</div>
          <h1 class="text-4xl font-black text-gray-900 dark:text-white">سجل التلف والخسائر</h1>
        </div>
      </div>

      <div class="bg-white dark:bg-[#0A0A0A] rounded-[40px] border border-gray-100 dark:border-gray-800 overflow-hidden">
        <div class="p-8 border-b border-gray-100 dark:border-gray-800 flex flex-wrap gap-4">
          <div class="relative flex-1 min-w-[260px]">
            <Search class="absolute right-4 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" />
            <input v-model="filters.search" @input="filters.page=1; fetchDamages()" placeholder="بحث..." class="w-full pr-12 pl-4 py-4 bg-gray-50 dark:bg-white/5 border-none rounded-2xl text-sm font-black focus:ring-2 focus:ring-[#04724D]/20" />
          </div>
          <select v-model="filters.warehouse_id" @change="filters.page=1; fetchDamages()" class="bg-gray-50 dark:bg-white/5 border-none rounded-2xl px-4 py-4 text-sm font-black text-gray-700 dark:text-gray-300 min-w-[200px]">
            <option value="">كل المستودعات</option>
            <option v-for="w in warehouses" :key="w.id" :value="w.id">{{ w.name }}</option>
          </select>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-right">
            <thead><tr class="text-[10px] font-black text-gray-400 uppercase tracking-widest bg-gray-50/50 dark:bg-white/[0.01]">
              <th class="px-8 py-6">التاريخ</th><th class="px-6 py-6">المرجع</th><th class="px-6 py-6">المستودع</th><th class="px-6 py-6">الأصناف</th><th class="px-6 py-6">إجراءات</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
              <tr v-if="loading" v-for="i in 5" :key="i" class="animate-pulse"><td v-for="j in 5" :key="j" class="px-6 py-5"><div class="h-5 bg-gray-100 dark:bg-white/5 rounded-xl"></div></td></tr>
              <tr v-else-if="damages.length===0"><td colspan="5" class="py-20 text-center text-gray-400 font-black text-sm">لا توجد سجلات تلف</td></tr>
              <tr v-for="d in damages" :key="d.id" class="group hover:bg-gray-50/50 dark:hover:bg-white/[0.01] transition-all">
                <td class="px-8 py-5 text-xs font-black text-gray-500 dark:text-gray-400">{{ d.date }}</td>
                <td class="px-6 py-5 font-mono font-black text-red-500">{{ d.Ref }}</td>
                <td class="px-6 py-5 text-sm font-black text-gray-700 dark:text-gray-300">{{ d.warehouse_name || '—' }}</td>
                <td class="px-6 py-5 text-sm font-black text-gray-900 dark:text-white">{{ d.items }} صنف</td>
                <td class="px-6 py-5">
                  <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-all">
                    <button @click="openDetails(d)" class="p-3 bg-white dark:bg-[#1A1A1A] text-gray-400 hover:text-[#04724D] rounded-2xl border border-gray-100 dark:border-gray-800 transition-all"><Eye class="h-4 w-4" /></button>
                    <a :href="`/damage_pdf/${d.id}`" target="_blank" class="p-3 bg-white dark:bg-[#1A1A1A] text-gray-400 hover:text-blue-500 rounded-2xl border border-gray-100 dark:border-gray-800 transition-all flex items-center text-xs font-black">PDF</a>
                    <button @click="deleteDamage(d.id)" class="p-3 bg-white dark:bg-[#1A1A1A] text-gray-400 hover:text-red-500 rounded-2xl border border-gray-100 dark:border-gray-800 transition-all"><Trash2 class="h-4 w-4" /></button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="p-8 bg-gray-50/50 dark:bg-white/[0.02] border-t border-gray-100 dark:border-gray-800 flex items-center justify-between">
          <p class="text-xs font-black text-gray-400">الإجمالي: <span class="text-red-500">{{ totalRows }}</span></p>
          <div class="flex gap-4">
            <button @click="filters.page--; fetchDamages()" :disabled="filters.page===1" class="text-xs font-black text-[#04724D] dark:text-[#3EFF8B] disabled:opacity-20">السابق</button>
            <button @click="filters.page++; fetchDamages()" :disabled="damages.length < filters.limit" class="text-xs font-black text-[#04724D] dark:text-[#3EFF8B] disabled:opacity-20">التالي</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Details Modal -->
    <div v-if="showDetails" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
      <div class="bg-white dark:bg-[#121212] rounded-[32px] w-full max-w-2xl p-8 shadow-2xl max-h-[90vh] overflow-y-auto space-y-4">
        <div class="flex items-center justify-between">
          <div><h2 class="text-xl font-black text-gray-900 dark:text-white">تفاصيل سجل التلف</h2><p class="text-xs font-mono text-gray-400 mt-1">{{ selectedDamage?.Ref }}</p></div>
          <button @click="showDetails=false" class="px-4 py-2 rounded-xl bg-gray-100 dark:bg-white/10 text-sm font-black">إغلاق</button>
        </div>
        <div class="grid grid-cols-2 gap-3 text-sm">
          <div class="bg-gray-50 dark:bg-white/5 rounded-2xl p-4"><p class="text-xs text-gray-400 mb-1">المستودع</p><p class="font-black dark:text-white">{{ selectedDamage?.warehouse_name }}</p></div>
          <div class="bg-gray-50 dark:bg-white/5 rounded-2xl p-4"><p class="text-xs text-gray-400 mb-1">التاريخ</p><p class="font-black dark:text-white">{{ selectedDamage?.date }}</p></div>
        </div>
        <div v-if="loadingDetails" class="text-center py-6 text-gray-400 text-sm">جاري التحميل...</div>
        <div v-else class="border border-gray-100 dark:border-gray-800 rounded-2xl overflow-hidden">
          <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-white/5"><tr class="text-[10px] font-black text-gray-400 uppercase tracking-widest"><th class="px-4 py-3 text-right">المنتج</th><th class="px-4 py-3 text-right">الكمية</th></tr></thead>
            <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
              <tr v-if="detailItems.length===0"><td colspan="2" class="px-4 py-6 text-center text-gray-400 text-sm">لا تفاصيل</td></tr>
              <tr v-for="(item, i) in detailItems" :key="i">
                <td class="px-4 py-3 font-black dark:text-gray-200">{{ item.product_name || item.code || '—' }}</td>
                <td class="px-4 py-3 font-mono font-black text-red-500">{{ item.quantity }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
