<script setup>
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import axios from 'axios';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Clock, Search, Plus, Trash2, Edit } from 'lucide-vue-next';

const props = defineProps({ employees: Array });

const records = ref([]);
const totalRows = ref(0);
const loading = ref(false);
const filters = ref({ employee_id: '', date: '', page: 1, limit: 15, SortField: 'id', SortType: 'desc' });

const showForm = ref(false);
const saving = ref(false);
const editingId = ref(null);
const form = ref({ employee_id: '', date: new Date().toISOString().split('T')[0], clock_in: '', clock_out: '', status: 'present' });

const fetchAttendance = async () => {
  loading.value = true;
  try {
    const { data } = await axios.get('/api/attendances', { params: filters.value });
    records.value = data.attendances || [];
    totalRows.value = data.totalRows || 0;
  } finally { loading.value = false; }
};

const openCreate = () => { editingId.value = null; form.value = { employee_id: '', date: new Date().toISOString().split('T')[0], clock_in: '', clock_out: '', status: 'present' }; showForm.value = true; };
const openEdit = (r) => { editingId.value = r.id; form.value = { employee_id: r.employee_id, date: r.date, clock_in: r.clock_in||'', clock_out: r.clock_out||'', status: r.status||'present' }; showForm.value = true; };

const submit = async () => {
  saving.value = true;
  try {
    if (editingId.value) await axios.put(`/api/attendances/${editingId.value}`, form.value);
    else await axios.post('/api/attendances', form.value);
    showForm.value = false; fetchAttendance();
  } catch (e) { alert(e.response?.data?.message || 'فشل الحفظ'); }
  finally { saving.value = false; }
};

const deleteRecord = async (id) => {
  if (!confirm('حذف هذا السجل؟')) return;
  await axios.delete(`/api/attendances/${id}`);
  fetchAttendance();
};

const statusClass = (s) => ({ present: 'bg-green-100 text-green-700', absent: 'bg-red-100 text-red-600', late: 'bg-orange-100 text-orange-700' }[s] || 'bg-gray-100 text-gray-600');
const statusLabel = (s) => ({ present: 'حاضر', absent: 'غائب', late: 'متأخر' }[s] || s);

onMounted(fetchAttendance);
</script>

<template>
  <AdminLayout>
    <Head title="سجل الحضور" />
    <div class="space-y-8 p-6 md:p-10 font-['Cairo']" dir="rtl">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
          <div class="flex items-center gap-2 text-[#04724D] text-xs font-black uppercase tracking-widest mb-1"><Clock class="w-4 h-4" />الموارد البشرية</div>
          <h1 class="text-4xl font-black text-gray-900 dark:text-white">سجل الحضور والانصراف</h1>
        </div>
        <button @click="openCreate" class="bg-[#04724D] hover:bg-[#035a3d] text-[#3EFF8B] px-8 py-4 rounded-[20px] font-black text-xs uppercase tracking-widest flex items-center gap-2 transition-all">
          <Plus class="h-5 w-5" />تسجيل حضور
        </button>
      </div>

      <div class="bg-white dark:bg-[#0A0A0A] rounded-[40px] border border-gray-100 dark:border-gray-800 overflow-hidden">
        <div class="p-8 border-b border-gray-100 dark:border-gray-800 flex flex-wrap gap-4">
          <select v-model="filters.employee_id" @change="filters.page=1; fetchAttendance()" class="bg-gray-50 dark:bg-white/5 border-none rounded-2xl px-4 py-4 text-sm font-black text-gray-700 dark:text-gray-300 min-w-[200px]">
            <option value="">كل الموظفين</option>
            <option v-for="e in employees" :key="e.id" :value="e.id">{{ e.first_name }} {{ e.last_name }}</option>
          </select>
          <input v-model="filters.date" @change="filters.page=1; fetchAttendance()" type="date" class="bg-gray-50 dark:bg-white/5 border-none rounded-2xl px-4 py-4 text-sm font-black text-gray-700 dark:text-gray-300" />
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-right">
            <thead><tr class="text-[10px] font-black text-gray-400 uppercase tracking-widest bg-gray-50/50 dark:bg-white/[0.01]">
              <th class="px-8 py-6">الموظف</th><th class="px-6 py-6">التاريخ</th><th class="px-6 py-6">دخول</th><th class="px-6 py-6">خروج</th><th class="px-6 py-6">الحالة</th><th class="px-6 py-6">إجراءات</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
              <tr v-if="loading" v-for="i in 5" :key="i" class="animate-pulse"><td v-for="j in 6" :key="j" class="px-6 py-5"><div class="h-5 bg-gray-100 dark:bg-white/5 rounded-xl"></div></td></tr>
              <tr v-else-if="records.length===0"><td colspan="6" class="py-20 text-center text-gray-400 font-black text-sm">لا توجد سجلات حضور</td></tr>
              <tr v-for="r in records" :key="r.id" class="group hover:bg-gray-50/50 dark:hover:bg-white/[0.01] transition-all">
                <td class="px-8 py-5 font-black text-gray-900 dark:text-white text-sm">{{ r.employee_name || r.first_name+' '+r.last_name || '—' }}</td>
                <td class="px-6 py-5 text-xs font-black text-gray-500 dark:text-gray-400">{{ r.date }}</td>
                <td class="px-6 py-5 font-mono text-sm text-gray-700 dark:text-gray-300">{{ r.clock_in || '—' }}</td>
                <td class="px-6 py-5 font-mono text-sm text-gray-700 dark:text-gray-300">{{ r.clock_out || '—' }}</td>
                <td class="px-6 py-5"><span :class="statusClass(r.status)" class="px-3 py-1 rounded-full text-[10px] font-black">{{ statusLabel(r.status) }}</span></td>
                <td class="px-6 py-5">
                  <div class="flex gap-2 opacity-0 group-hover:opacity-100 transition-all">
                    <button @click="openEdit(r)" class="p-3 bg-white dark:bg-[#1A1A1A] text-gray-400 hover:text-[#04724D] rounded-2xl border border-gray-100 dark:border-gray-800"><Edit class="h-4 w-4" /></button>
                    <button @click="deleteRecord(r.id)" class="p-3 bg-white dark:bg-[#1A1A1A] text-gray-400 hover:text-red-500 rounded-2xl border border-gray-100 dark:border-gray-800"><Trash2 class="h-4 w-4" /></button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="p-8 bg-gray-50/50 dark:bg-white/[0.02] border-t border-gray-100 dark:border-gray-800 flex items-center justify-between">
          <p class="text-xs font-black text-gray-400">الإجمالي: <span class="text-[#04724D] dark:text-[#3EFF8B]">{{ totalRows }}</span></p>
          <div class="flex gap-4">
            <button @click="filters.page--; fetchAttendance()" :disabled="filters.page===1" class="text-xs font-black text-[#04724D] dark:text-[#3EFF8B] disabled:opacity-20">السابق</button>
            <button @click="filters.page++; fetchAttendance()" :disabled="records.length < filters.limit" class="text-xs font-black text-[#04724D] dark:text-[#3EFF8B] disabled:opacity-20">التالي</button>
          </div>
        </div>
      </div>
    </div>

    <div v-if="showForm" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
      <div class="bg-white dark:bg-[#121212] rounded-[32px] w-full max-w-md p-8 space-y-5 shadow-2xl">
        <h2 class="text-xl font-black text-gray-900 dark:text-white">{{ editingId ? 'تعديل سجل' : 'تسجيل حضور' }}</h2>
        <div class="space-y-4">
          <div class="space-y-1"><label class="text-xs text-gray-400 font-black">الموظف</label>
            <select v-model="form.employee_id" class="w-full rounded-2xl bg-gray-50 dark:bg-white/5 border-none py-3 px-4 text-sm font-black dark:text-white">
              <option value="">اختر موظف</option>
              <option v-for="e in employees" :key="e.id" :value="e.id">{{ e.first_name }} {{ e.last_name }}</option>
            </select>
          </div>
          <div class="space-y-1"><label class="text-xs text-gray-400 font-black">التاريخ</label><input v-model="form.date" type="date" class="w-full rounded-2xl bg-gray-50 dark:bg-white/5 border-none py-3 px-4 text-sm font-black dark:text-white" /></div>
          <div class="grid grid-cols-2 gap-3">
            <div class="space-y-1"><label class="text-xs text-gray-400 font-black">وقت الدخول</label><input v-model="form.clock_in" type="time" class="w-full rounded-2xl bg-gray-50 dark:bg-white/5 border-none py-3 px-4 text-sm font-black dark:text-white" /></div>
            <div class="space-y-1"><label class="text-xs text-gray-400 font-black">وقت الخروج</label><input v-model="form.clock_out" type="time" class="w-full rounded-2xl bg-gray-50 dark:bg-white/5 border-none py-3 px-4 text-sm font-black dark:text-white" /></div>
          </div>
          <div class="space-y-1"><label class="text-xs text-gray-400 font-black">الحالة</label>
            <select v-model="form.status" class="w-full rounded-2xl bg-gray-50 dark:bg-white/5 border-none py-3 px-4 text-sm font-black dark:text-white">
              <option value="present">حاضر</option><option value="absent">غائب</option><option value="late">متأخر</option>
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
