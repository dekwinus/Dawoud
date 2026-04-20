<script setup>
import { Head } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ArrowLeftRight, Plus, Search, Trash2, Eye, CheckCircle, XCircle } from 'lucide-vue-next';

const props = defineProps({ warehouses: Array });

const transfers = ref([]);
const totalRows = ref(0);
const loading = ref(false);
const filters = ref({ search: '', from_warehouse_id: '', to_warehouse_id: '', page: 1, limit: 10, SortField: 'id', SortType: 'desc' });

const showForm = ref(false);
const saving = ref(false);
const editingId = ref(null);
const form = ref({ date: new Date().toISOString().split('T')[0], Ref: '', from_warehouse_id: '', to_warehouse_id: '', items: [{ product_id: '', quantity: 1 }], notes: '' });

const showDetails = ref(false);
const selectedTransfer = ref(null);

const fetchTransfers = async () => {
  loading.value = true;
  try {
    const { data } = await axios.get('/api/transfers', { params: filters.value });
    transfers.value = data.transfers || [];
    totalRows.value = data.totalRows || 0;
  } finally { loading.value = false; }
};

const openCreate = () => {
  editingId.value = null;
  form.value = { date: new Date().toISOString().split('T')[0], Ref: '', from_warehouse_id: '', to_warehouse_id: '', items: [{ product_id: '', quantity: 1 }], notes: '' };
  showForm.value = true;
};

const addItem = () => form.value.items.push({ product_id: '', quantity: 1 });
const removeItem = (i) => { if (form.value.items.length > 1) form.value.items.splice(i, 1); };

const submit = async () => {
  saving.value = true;
  try {
    const payload = {
      transfer: {
        date: form.value.date,
        Ref: form.value.Ref,
        from_warehouse_id: form.value.from_warehouse_id,
        to_warehouse_id: form.value.to_warehouse_id,
        items: form.value.items,
        notes: form.value.notes,
      }
    };
    if (editingId.value) await axios.put(`/api/transfers/${editingId.value}`, payload);
    else await axios.post('/api/transfers', payload);
    showForm.value = false;
    fetchTransfers();
  } catch (e) { alert(e.response?.data?.message || 'فشل الحفظ'); }
  finally { saving.value = false; }
};

const deleteTransfer = async (id) => {
  if (!confirm('حذف هذا التحويل؟')) return;
  await axios.delete(`/api/transfers/${id}`);
  fetchTransfers();
};

const approveTransfer = async (id) => {
  await axios.post(`/api/transfers/${id}/approve`);
  fetchTransfers();
};

const openDetails = (t) => { selectedTransfer.value = t; showDetails.value = true; };

const statusLabel = (s) => ({ completed: 'مكتمل', pending: 'معلق', approved: 'معتمد' }[s] || s);
const statusClass = (s) => ({ completed: 'bg-green-100 text-green-700', pending: 'bg-orange-100 text-orange-700', approved: 'bg-blue-100 text-blue-700' }[s] || 'bg-gray-100 text-gray-600');

onMounted(fetchTransfers);
</script>

<template>
  <AdminLayout>
    <Head title="تحويلات المخزون" />
    <div class="space-y-8 p-6 md:p-10 font-['Cairo']" dir="rtl">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
          <div class="flex items-center gap-2 text-[#04724D] text-xs font-black uppercase tracking-widest mb-1"><ArrowLeftRight class="w-4 h-4" />نقل المخزون</div>
          <h1 class="text-4xl font-black text-gray-900 dark:text-white">تحويلات المخزون</h1>
        </div>
        <button @click="openCreate" class="bg-[#04724D] hover:bg-[#035a3d] text-[#3EFF8B] px-8 py-4 rounded-[20px] font-black text-xs uppercase tracking-widest flex items-center gap-2 transition-all">
          <Plus class="h-5 w-5" />تحويل جديد
        </button>
      </div>

      <div class="bg-white dark:bg-[#0A0A0A] rounded-[40px] border border-gray-100 dark:border-gray-800 overflow-hidden">
        <div class="p-8 border-b border-gray-100 dark:border-gray-800 flex flex-wrap gap-4">
          <div class="relative flex-1 min-w-[260px]">
            <Search class="absolute right-4 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" />
            <input v-model="filters.search" @input="filters.page=1; fetchTransfers()" type="text" placeholder="بحث برقم المرجع..." class="w-full pr-12 pl-4 py-4 bg-gray-50 dark:bg-white/5 border-none rounded-2xl text-sm font-black focus:ring-2 focus:ring-[#04724D]/20" />
          </div>
          <select v-model="filters.from_warehouse_id" @change="filters.page=1; fetchTransfers()" class="bg-gray-50 dark:bg-white/5 border-none rounded-2xl px-4 py-4 text-sm font-black text-gray-700 dark:text-gray-300 min-w-[180px]">
            <option value="">من: كل المستودعات</option>
            <option v-for="w in warehouses" :key="w.id" :value="w.id">{{ w.name }}</option>
          </select>
          <select v-model="filters.to_warehouse_id" @change="filters.page=1; fetchTransfers()" class="bg-gray-50 dark:bg-white/5 border-none rounded-2xl px-4 py-4 text-sm font-black text-gray-700 dark:text-gray-300 min-w-[180px]">
            <option value="">إلى: كل المستودعات</option>
            <option v-for="w in warehouses" :key="w.id" :value="w.id">{{ w.name }}</option>
          </select>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-right">
            <thead><tr class="text-[10px] font-black text-gray-400 uppercase tracking-widest bg-gray-50/50 dark:bg-white/[0.01]">
              <th class="px-8 py-6">التاريخ</th>
              <th class="px-6 py-6">المرجع</th>
              <th class="px-6 py-6">من</th>
              <th class="px-6 py-6">إلى</th>
              <th class="px-6 py-6">الحالة</th>
              <th class="px-6 py-6">إجراءات</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
              <tr v-if="loading" v-for="i in 5" :key="i" class="animate-pulse"><td v-for="j in 6" :key="j" class="px-6 py-5"><div class="h-5 bg-gray-100 dark:bg-white/5 rounded-xl"></div></td></tr>
              <tr v-else-if="transfers.length===0"><td colspan="6" class="py-20 text-center text-gray-400 font-black text-sm">لا توجد تحويلات</td></tr>
              <tr v-for="t in transfers" :key="t.id" class="group hover:bg-gray-50/50 dark:hover:bg-white/[0.01] transition-all">
                <td class="px-8 py-5 text-xs font-black text-gray-500 dark:text-gray-400">{{ t.date }}</td>
                <td class="px-6 py-5 font-mono font-black text-[#04724D] dark:text-[#3EFF8B]">{{ t.Ref }}</td>
                <td class="px-6 py-5 text-sm font-black text-gray-700 dark:text-gray-300">{{ t.from_warehouse_name || '—' }}</td>
                <td class="px-6 py-5 text-sm font-black text-gray-700 dark:text-gray-300">{{ t.to_warehouse_name || '—' }}</td>
                <td class="px-6 py-5"><span :class="statusClass(t.statut)" class="px-3 py-1 rounded-full text-[10px] font-black">{{ statusLabel(t.statut) }}</span></td>
                <td class="px-6 py-5">
                  <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-all">
                    <button @click="openDetails(t)" class="p-3 bg-white dark:bg-[#1A1A1A] text-gray-400 hover:text-[#04724D] rounded-2xl border border-gray-100 dark:border-gray-800 transition-all"><Eye class="h-4 w-4" /></button>
                    <button v-if="t.statut === 'pending'" @click="approveTransfer(t.id)" class="p-3 bg-white dark:bg-[#1A1A1A] text-gray-400 hover:text-green-600 rounded-2xl border border-gray-100 dark:border-gray-800 transition-all" title="اعتماد"><CheckCircle class="h-4 w-4" /></button>
                    <button @click="deleteTransfer(t.id)" class="p-3 bg-white dark:bg-[#1A1A1A] text-gray-400 hover:text-red-500 rounded-2xl border border-gray-100 dark:border-gray-800 transition-all"><Trash2 class="h-4 w-4" /></button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="p-8 bg-gray-50/50 dark:bg-white/[0.02] border-t border-gray-100 dark:border-gray-800 flex items-center justify-between">
          <p class="text-xs font-black text-gray-400">الإجمالي: <span class="text-[#04724D] dark:text-[#3EFF8B]">{{ totalRows }}</span></p>
          <div class="flex gap-4">
            <button @click="filters.page--; fetchTransfers()" :disabled="filters.page===1" class="text-xs font-black text-[#04724D] dark:text-[#3EFF8B] disabled:opacity-20">السابق</button>
            <button @click="filters.page++; fetchTransfers()" :disabled="transfers.length < filters.limit" class="text-xs font-black text-[#04724D] dark:text-[#3EFF8B] disabled:opacity-20">التالي</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Details Modal -->
    <div v-if="showDetails && selectedTransfer" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
      <div class="bg-white dark:bg-[#121212] rounded-[32px] w-full max-w-lg p-8 shadow-2xl space-y-4">
        <div class="flex items-center justify-between">
          <h2 class="text-xl font-black text-gray-900 dark:text-white">تفاصيل التحويل</h2>
          <button @click="showDetails=false" class="px-4 py-2 rounded-xl bg-gray-100 dark:bg-white/10 text-sm font-black">إغلاق</button>
        </div>
        <div class="grid grid-cols-2 gap-3 text-sm">
          <div class="bg-gray-50 dark:bg-white/5 rounded-2xl p-4"><p class="text-xs text-gray-400 mb-1">المرجع</p><p class="font-black font-mono text-[#04724D] dark:text-[#3EFF8B]">{{ selectedTransfer.Ref }}</p></div>
          <div class="bg-gray-50 dark:bg-white/5 rounded-2xl p-4"><p class="text-xs text-gray-400 mb-1">التاريخ</p><p class="font-black">{{ selectedTransfer.date }}</p></div>
          <div class="bg-gray-50 dark:bg-white/5 rounded-2xl p-4"><p class="text-xs text-gray-400 mb-1">من</p><p class="font-black">{{ selectedTransfer.from_warehouse_name }}</p></div>
          <div class="bg-gray-50 dark:bg-white/5 rounded-2xl p-4"><p class="text-xs text-gray-400 mb-1">إلى</p><p class="font-black">{{ selectedTransfer.to_warehouse_name }}</p></div>
        </div>
        <div class="bg-gray-50 dark:bg-white/5 rounded-2xl p-4"><p class="text-xs text-gray-400 mb-1">الحالة</p><span :class="statusClass(selectedTransfer.statut)" class="px-3 py-1 rounded-full text-[10px] font-black">{{ statusLabel(selectedTransfer.statut) }}</span></div>
        <a :href="`/transfer_pdf/${selectedTransfer.id}`" target="_blank" class="block text-center px-4 py-3 rounded-2xl bg-[#04724D] text-white text-sm font-black">طباعة PDF</a>
      </div>
    </div>

    <!-- Create Modal -->
    <div v-if="showForm" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
      <div class="bg-white dark:bg-[#121212] rounded-[32px] w-full max-w-2xl p-8 space-y-5 shadow-2xl max-h-[90vh] overflow-y-auto">
        <h2 class="text-xl font-black text-gray-900 dark:text-white">تحويل مخزون جديد</h2>
        <div class="grid grid-cols-2 gap-4">
          <div class="space-y-1"><label class="text-xs text-gray-400 font-black">التاريخ</label><input v-model="form.date" type="date" class="w-full rounded-2xl bg-gray-50 dark:bg-white/5 border-none py-3 px-4 text-sm font-black dark:text-white" /></div>
          <div class="space-y-1"><label class="text-xs text-gray-400 font-black">المرجع</label><input v-model="form.Ref" class="w-full rounded-2xl bg-gray-50 dark:bg-white/5 border-none py-3 px-4 text-sm font-black dark:text-white" /></div>
          <div class="space-y-1"><label class="text-xs text-gray-400 font-black">من مستودع</label>
            <select v-model="form.from_warehouse_id" class="w-full rounded-2xl bg-gray-50 dark:bg-white/5 border-none py-3 px-4 text-sm font-black dark:text-white">
              <option value="">اختر</option><option v-for="w in warehouses" :key="w.id" :value="w.id">{{ w.name }}</option>
            </select></div>
          <div class="space-y-1"><label class="text-xs text-gray-400 font-black">إلى مستودع</label>
            <select v-model="form.to_warehouse_id" class="w-full rounded-2xl bg-gray-50 dark:bg-white/5 border-none py-3 px-4 text-sm font-black dark:text-white">
              <option value="">اختر</option><option v-for="w in warehouses" :key="w.id" :value="w.id">{{ w.name }}</option>
            </select></div>
        </div>
        <div class="space-y-3">
          <div class="flex items-center justify-between"><label class="text-xs text-gray-400 font-black">الأصناف</label><button @click="addItem" class="text-xs text-[#04724D] font-black">+ إضافة صنف</button></div>
          <div v-for="(item, i) in form.items" :key="i" class="flex gap-3">
            <input v-model="item.product_id" type="number" placeholder="ID المنتج" class="flex-1 rounded-2xl bg-gray-50 dark:bg-white/5 border-none py-3 px-4 text-sm font-black dark:text-white" />
            <input v-model.number="item.quantity" type="number" min="1" placeholder="الكمية" class="w-32 rounded-2xl bg-gray-50 dark:bg-white/5 border-none py-3 px-4 text-sm font-black dark:text-white" />
            <button @click="removeItem(i)" class="text-red-400 text-lg font-black px-2">×</button>
          </div>
        </div>
        <div class="flex justify-end gap-3 pt-2">
          <button @click="showForm=false" class="px-6 py-3 rounded-2xl bg-gray-100 dark:bg-white/10 text-sm font-black text-gray-700 dark:text-gray-300">إلغاء</button>
          <button @click="submit" :disabled="saving" class="px-6 py-3 rounded-2xl bg-[#04724D] text-white text-sm font-black disabled:opacity-60">{{ saving ? 'جاري الحفظ...' : 'حفظ' }}</button>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
