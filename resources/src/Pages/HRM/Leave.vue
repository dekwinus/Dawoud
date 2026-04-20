<script setup>
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import axios from 'axios';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { CalendarOff, Plus, Trash2, Edit, CheckCircle, XCircle } from 'lucide-vue-next';

const props = defineProps({ employees: Array });

const leaves = ref([]);
const totalRows = ref(0);
const loading = ref(false);
const leaveTypes = ref([]);
const filters = ref({ employee_id: '', status: '', page: 1, limit: 15, SortField: 'id', SortType: 'desc' });

const showForm = ref(false);
const saving = ref(false);
const editingId = ref(null);
const form = ref({ employee_id: '', leave_type_id: '', date_from: '', date_to: '', reason: '', status: 'pending' });

const fetchLeaves = async () => {
  loading.value = true;
  try {
    const { data } = await axios.get('/api/leave', { params: filters.value });
    leaves.value = data.leaves || data.leave || [];
    totalRows.value = data.totalRows || 0;
  } finally { loading.value = false; }
};

const fetchLeaveTypes = async () => {
  try {
    const { data } = await axios.get('/api/leave_type');
    leaveTypes.value = data.leave_types || data.leaveTypes || [];
  } catch {}
};

const openCreate = () => { editingId.value = null; form.value = { employee_id: '', leave_type_id: '', date_from: '', date_to: '', reason: '', status: 'pending' }; showForm.value = true; };
const openEdit = (l) => { editingId.value = l.id; form.value = { employee_id: l.employee_id, leave_type_id: l.leave_type_id, date_from: l.date_from||l.start_date||'', date_to: l.date_to||l.end_date||'', reason: l.reason||'', status: l.status||'pending' }; showForm.value = true; };

const submit = async () => {
  saving.value = true;
  try {
    if (editingId.value) await axios.put(`/api/leave/${editingId.value}`, form.value);
    else await axios.post('/api/leave', form.value);
    showForm.value = false; fetchLeaves();
  } catch (e) { alert(e.response?.data?.message || 'فشل الحفظ'); }
  finally { saving.value = false; }
};

const deleteLeave = async (id) => {
  if (!confirm('حذف طلب الإجازة؟')) return;
  await axios.delete(`/api/leave/${id}`);
  fetchLeaves();
};

const statusClass = (s) => ({ approved: 'bg-green-100 text-green-700', pending: 'bg-orange-100 text-orange-700', rejected: 'bg-red-100 text-red-600' }[s] || 'bg-gray-100 text-gray-600');
const statusLabel = (s) => ({ approved: 'معتمد', pending: 'معلق', rejected: 'مرفوض' }[s] || s);

onMounted(async () => { await Promise.all([fetchLeaves(), fetchLeaveTypes()]); });
</script>

<template>
  <AdminLayout>
    <Head title="الإجازات" />
    <div class="space-y-8 p-6 md:p-10 font-['Cairo']" dir="rtl">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
          <div class="flex items-center gap-2 text-[#04724D] text-xs font-black uppercase tracking-widest mb-1"><CalendarOff class="w-4 h-4" />الموارد البشرية</div>
          <h1 class="text-4xl font-black text-gray-900 dark:text-white">إدارة الإجازات</h1>
        </div>
        <button @click="openCreate" class="bg-[#04724D] hover:bg-[#035a3d] text-[#3EFF8B] px-8 py-4 rounded-[20px] font-black text-xs uppercase tracking-widest flex items-center gap-2 transition-all">
          <Plus class="h-5 w-5" />طلب إجازة
        </button>
      </div>

      <div class="bg-white dark:bg-[#0A0A0A] rounded-[40px] border border-gray-100 dark:border-gray-800 overflow-hidden">
        <div class="p-8 border-b border-gray-100 dark:border-gray-800 flex flex-wrap gap-4">
          <select v-model="filters.employee_id" @change="filters.page=1; fetchLeaves()" class="bg-gray-50 dark:bg-white/5 border-none rounded-2xl px-4 py-4 text-sm font-black text-gray-700 dark:text-gray-300 min-w-[200px]">
            <option value="">كل الموظفين</option>
            <option v-for="e in employees" :key="e.id" :value="e.id">{{ e.first_name }} {{ e.last_name }}</option>
          </select>
          <select v-model="filters.status" @change="filters.page=1; fetchLeaves()" class="bg-gray-50 dark:bg-white/5 border-none rounded-2xl px-4 py-4 text-sm font-black text-gray-700 dark:text-gray-300">
            <option value="">كل الحالات</option>
            <option value="pending">معلق</option><option value="approved">معتمد</option><option value="rejected">مرفوض</option>
          </select>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-right">
            <thead><tr class="text-[10px] font-black text-gray-400 uppercase tracking-widest bg-gray-50/50 dark:bg-white/[0.01]">
              <th class="px-8 py-6">الموظف</th><th class="px-6 py-6">نوع الإجازة</th><th class="px-6 py-6">من</th><th class="px-6 py-6">إلى</th><th class="px-6 py-6">الحالة</th><th class="px-6 py-6">إجراءات</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
              <tr v-if="loading" v-for="i in 5" :key="i" class="animate-pulse"><td v-for="j in 6" :key="j" class="px-6 py-5"><div class="h-5 bg-gray-100 dark:bg-white/5 rounded-xl"></div></td></tr>
              <tr v-else-if="leaves.length===0"><td colspan="6" class="py-20 text-center text-gray-400 font-black text-sm">لا توجد طلبات إجازة</td></tr>
              <tr v-for="l in leaves" :key="l.id" class="group hover:bg-gray-50/50 dark:hover:bg-white/[0.01] transition-all">
                <td class="px-8 py-5 font-black text-gray-900 dark:text-white text-sm">{{ l.employee_name || '—' }}</td>
                <td class="px-6 py-5 text-sm text-gray-600 dark:text-gray-400">{{ l.leave_type_name || '—' }}</td>
                <td class="px-6 py-5 text-xs font-black text-gray-500 dark:text-gray-400">{{ l.date_from || l.start_date }}</td>
                <td class="px-6 py-5 text-xs font-black text-gray-500 dark:text-gray-400">{{ l.date_to || l.end_date }}</td>
                <td class="px-6 py-5"><span :class="statusClass(l.status)" class="px-3 py-1 rounded-full text-[10px] font-black">{{ statusLabel(l.status) }}</span></td>
                <td class="px-6 py-5">
                  <div class="flex gap-2 opacity-0 group-hover:opacity-100 transition-all">
                    <button @click="openEdit(l)" class="p-3 bg-white dark:bg-[#1A1A1A] text-gray-400 hover:text-[#04724D] rounded-2xl border border-gray-100 dark:border-gray-800"><Edit class="h-4 w-4" /></button>
                    <button @click="deleteLeave(l.id)" class="p-3 bg-white dark:bg-[#1A1A1A] text-gray-400 hover:text-red-500 rounded-2xl border border-gray-100 dark:border-gray-800"><Trash2 class="h-4 w-4" /></button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="p-8 bg-gray-50/50 dark:bg-white/[0.02] border-t border-gray-100 dark:border-gray-800 flex items-center justify-between">
          <p class="text-xs font-black text-gray-400">الإجمالي: <span class="text-[#04724D] dark:text-[#3EFF8B]">{{ totalRows }}</span></p>
          <div class="flex gap-4">
            <button @click="filters.page--; fetchLeaves()" :disabled="filters.page===1" class="text-xs font-black text-[#04724D] dark:text-[#3EFF8B] disabled:opacity-20">السابق</button>
            <button @click="filters.page++; fetchLeaves()" :disabled="leaves.length < filters.limit" class="text-xs font-black text-[#04724D] dark:text-[#3EFF8B] disabled:opacity-20">التالي</button>
          </div>
        </div>
      </div>
    </div>

    <div v-if="showForm" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
      <div class="bg-white dark:bg-[#121212] rounded-[32px] w-full max-w-md p-8 space-y-5 shadow-2xl">
        <h2 class="text-xl font-black text-gray-900 dark:text-white">{{ editingId ? 'تعديل طلب' : 'طلب إجازة جديد' }}</h2>
        <div class="space-y-4">
          <div class="space-y-1"><label class="text-xs text-gray-400 font-black">الموظف</label>
            <select v-model="form.employee_id" class="w-full rounded-2xl bg-gray-50 dark:bg-white/5 border-none py-3 px-4 text-sm font-black dark:text-white">
              <option value="">اختر موظف</option>
              <option v-for="e in employees" :key="e.id" :value="e.id">{{ e.first_name }} {{ e.last_name }}</option>
            </select>
          </div>
          <div class="space-y-1"><label class="text-xs text-gray-400 font-black">نوع الإجازة</label>
            <select v-model="form.leave_type_id" class="w-full rounded-2xl bg-gray-50 dark:bg-white/5 border-none py-3 px-4 text-sm font-black dark:text-white">
              <option value="">اختر نوع</option>
              <option v-for="t in leaveTypes" :key="t.id" :value="t.id">{{ t.name }}</option>
            </select>
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div class="space-y-1"><label class="text-xs text-gray-400 font-black">من</label><input v-model="form.date_from" type="date" class="w-full rounded-2xl bg-gray-50 dark:bg-white/5 border-none py-3 px-4 text-sm font-black dark:text-white" /></div>
            <div class="space-y-1"><label class="text-xs text-gray-400 font-black">إلى</label><input v-model="form.date_to" type="date" class="w-full rounded-2xl bg-gray-50 dark:bg-white/5 border-none py-3 px-4 text-sm font-black dark:text-white" /></div>
          </div>
          <div class="space-y-1"><label class="text-xs text-gray-400 font-black">السبب</label><textarea v-model="form.reason" rows="2" class="w-full rounded-2xl bg-gray-50 dark:bg-white/5 border-none py-3 px-4 text-sm font-black dark:text-white"></textarea></div>
          <div class="space-y-1"><label class="text-xs text-gray-400 font-black">الحالة</label>
            <select v-model="form.status" class="w-full rounded-2xl bg-gray-50 dark:bg-white/5 border-none py-3 px-4 text-sm font-black dark:text-white">
              <option value="pending">معلق</option><option value="approved">معتمد</option><option value="rejected">مرفوض</option>
            </select>
          </div>
        </div>
        <div class="flex justify-end gap-3">
          <button @click="showForm=false" class="px-6 py-3 rounded-2xl bg-gray-100 dark:bg-white/10 text-sm font-black text-gray-700 dark:text-gray-300">إلغاء</button>
          <button @click="submit" :disabled="saving" class="px-6 py-3 rounded-2xl bg-[#04724D] text-white text-sm font-black disabled:opacity-60">{{ saving ? 'جاري...' : 'حفظ' }}</button>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
