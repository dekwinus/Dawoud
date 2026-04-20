<script setup>
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import axios from 'axios';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Wrench, Plus, Search, Trash2, Eye, Edit } from 'lucide-vue-next';

const jobs = ref([]);
const totalRows = ref(0);
const loading = ref(false);
const technicians = ref([]);
const filters = ref({ search: '', status: '', technician_id: '', page: 1, limit: 15, SortField: 'id', SortType: 'desc' });

const showForm = ref(false);
const saving = ref(false);
const editingId = ref(null);
const form = ref({ title: '', client_name: '', client_phone: '', technician_id: '', scheduled_date: '', description: '', status: 'pending', priority: 'normal' });

const showDetails = ref(false);
const selectedJob = ref(null);

const fetchJobs = async () => {
  loading.value = true;
  try {
    const { data } = await axios.get('/api/service_jobs', { params: filters.value });
    jobs.value = data.service_jobs || data.jobs || [];
    totalRows.value = data.totalRows || 0;
  } finally { loading.value = false; }
};

const fetchTechnicians = async () => {
  try {
    const { data } = await axios.get('/api/service_technicians');
    technicians.value = data.technicians || data.service_technicians || [];
  } catch {}
};

const openCreate = () => {
  editingId.value = null;
  form.value = { title: '', client_name: '', client_phone: '', technician_id: '', scheduled_date: '', description: '', status: 'pending', priority: 'normal' };
  showForm.value = true;
};

const openEdit = (j) => {
  editingId.value = j.id;
  form.value = { title: j.title||'', client_name: j.client_name||'', client_phone: j.client_phone||'', technician_id: j.technician_id||'', scheduled_date: j.scheduled_date||'', description: j.description||'', status: j.status||'pending', priority: j.priority||'normal' };
  showForm.value = true;
};

const submit = async () => {
  saving.value = true;
  try {
    if (editingId.value) await axios.put(`/api/service_jobs/${editingId.value}`, form.value);
    else await axios.post('/api/service_jobs', form.value);
    showForm.value = false; fetchJobs();
  } catch (e) { alert(e.response?.data?.message || 'فشل الحفظ'); }
  finally { saving.value = false; }
};

const deleteJob = async (id) => {
  if (!confirm('حذف طلب الخدمة؟')) return;
  await axios.delete(`/api/service_jobs/${id}`);
  fetchJobs();
};

const statusClass = (s) => ({ completed: 'bg-green-100 text-green-700', pending: 'bg-orange-100 text-orange-700', in_progress: 'bg-blue-100 text-blue-700', cancelled: 'bg-red-100 text-red-600' }[s] || 'bg-gray-100 text-gray-600');
const statusLabel = (s) => ({ completed: 'مكتمل', pending: 'معلق', in_progress: 'جاري', cancelled: 'ملغي' }[s] || s);
const priorityClass = (p) => ({ high: 'text-red-500', normal: 'text-gray-500', low: 'text-green-600' }[p] || 'text-gray-500');
const priorityLabel = (p) => ({ high: 'عالية', normal: 'عادي', low: 'منخفضة' }[p] || p);

onMounted(async () => { await Promise.all([fetchJobs(), fetchTechnicians()]); });
</script>

<template>
  <AdminLayout>
    <Head title="طلبات الخدمة" />
    <div class="space-y-8 p-6 md:p-10 font-['Cairo']" dir="rtl">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
          <div class="flex items-center gap-2 text-[#04724D] text-xs font-black uppercase tracking-widest mb-1"><Wrench class="w-4 h-4" />الخدمة والصيانة</div>
          <h1 class="text-4xl font-black text-gray-900 dark:text-white">طلبات الخدمة</h1>
        </div>
        <button @click="openCreate" class="bg-[#04724D] hover:bg-[#035a3d] text-[#3EFF8B] px-8 py-4 rounded-[20px] font-black text-xs uppercase tracking-widest flex items-center gap-2 transition-all">
          <Plus class="h-5 w-5" />طلب جديد
        </button>
      </div>

      <div class="bg-white dark:bg-[#0A0A0A] rounded-[40px] border border-gray-100 dark:border-gray-800 overflow-hidden">
        <div class="p-8 border-b border-gray-100 dark:border-gray-800 flex flex-wrap gap-4">
          <div class="relative flex-1 min-w-[260px]">
            <Search class="absolute right-4 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" />
            <input v-model="filters.search" @input="filters.page=1; fetchJobs()" placeholder="بحث..." class="w-full pr-12 pl-4 py-4 bg-gray-50 dark:bg-white/5 border-none rounded-2xl text-sm font-black" />
          </div>
          <select v-model="filters.status" @change="filters.page=1; fetchJobs()" class="bg-gray-50 dark:bg-white/5 border-none rounded-2xl px-4 py-4 text-sm font-black text-gray-700 dark:text-gray-300">
            <option value="">كل الحالات</option>
            <option value="pending">معلق</option><option value="in_progress">جاري</option><option value="completed">مكتمل</option><option value="cancelled">ملغي</option>
          </select>
          <select v-model="filters.technician_id" @change="filters.page=1; fetchJobs()" class="bg-gray-50 dark:bg-white/5 border-none rounded-2xl px-4 py-4 text-sm font-black text-gray-700 dark:text-gray-300 min-w-[180px]">
            <option value="">كل الفنيين</option>
            <option v-for="t in technicians" :key="t.id" :value="t.id">{{ t.name }}</option>
          </select>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-right">
            <thead><tr class="text-[10px] font-black text-gray-400 uppercase tracking-widest bg-gray-50/50 dark:bg-white/[0.01]">
              <th class="px-8 py-6">العنوان</th><th class="px-6 py-6">العميل</th><th class="px-6 py-6">الفني</th><th class="px-6 py-6">الموعد</th><th class="px-6 py-6">الأولوية</th><th class="px-6 py-6">الحالة</th><th class="px-6 py-6">إجراءات</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
              <tr v-if="loading" v-for="i in 5" :key="i" class="animate-pulse"><td v-for="j in 7" :key="j" class="px-6 py-5"><div class="h-5 bg-gray-100 dark:bg-white/5 rounded-xl"></div></td></tr>
              <tr v-else-if="jobs.length===0"><td colspan="7" class="py-20 text-center text-gray-400 font-black text-sm">لا توجد طلبات خدمة</td></tr>
              <tr v-for="j in jobs" :key="j.id" class="group hover:bg-gray-50/50 dark:hover:bg-white/[0.01] transition-all">
                <td class="px-8 py-5 font-black text-gray-900 dark:text-white text-sm">{{ j.title }}</td>
                <td class="px-6 py-5 text-sm text-gray-600 dark:text-gray-400">{{ j.client_name || '—' }}</td>
                <td class="px-6 py-5 text-sm text-gray-600 dark:text-gray-400">{{ j.technician_name || '—' }}</td>
                <td class="px-6 py-5 text-xs font-black text-gray-500 dark:text-gray-400">{{ j.scheduled_date || '—' }}</td>
                <td class="px-6 py-5 text-xs font-black" :class="priorityClass(j.priority)">{{ priorityLabel(j.priority) }}</td>
                <td class="px-6 py-5"><span :class="statusClass(j.status)" class="px-3 py-1 rounded-full text-[10px] font-black">{{ statusLabel(j.status) }}</span></td>
                <td class="px-6 py-5">
                  <div class="flex gap-2 opacity-0 group-hover:opacity-100 transition-all">
                    <button @click="openEdit(j)" class="p-3 bg-white dark:bg-[#1A1A1A] text-gray-400 hover:text-[#04724D] rounded-2xl border border-gray-100 dark:border-gray-800"><Edit class="h-4 w-4" /></button>
                    <button @click="deleteJob(j.id)" class="p-3 bg-white dark:bg-[#1A1A1A] text-gray-400 hover:text-red-500 rounded-2xl border border-gray-100 dark:border-gray-800"><Trash2 class="h-4 w-4" /></button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="p-8 bg-gray-50/50 dark:bg-white/[0.02] border-t border-gray-100 dark:border-gray-800 flex items-center justify-between">
          <p class="text-xs font-black text-gray-400">الإجمالي: <span class="text-[#04724D] dark:text-[#3EFF8B]">{{ totalRows }}</span></p>
          <div class="flex gap-4">
            <button @click="filters.page--; fetchJobs()" :disabled="filters.page===1" class="text-xs font-black text-[#04724D] dark:text-[#3EFF8B] disabled:opacity-20">السابق</button>
            <button @click="filters.page++; fetchJobs()" :disabled="jobs.length < filters.limit" class="text-xs font-black text-[#04724D] dark:text-[#3EFF8B] disabled:opacity-20">التالي</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Form Modal -->
    <div v-if="showForm" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
      <div class="bg-white dark:bg-[#121212] rounded-[32px] w-full max-w-lg p-8 space-y-5 shadow-2xl max-h-[90vh] overflow-y-auto">
        <h2 class="text-xl font-black text-gray-900 dark:text-white">{{ editingId ? 'تعديل طلب' : 'طلب خدمة جديد' }}</h2>
        <div class="grid grid-cols-2 gap-4">
          <div class="space-y-1 col-span-2"><label class="text-xs text-gray-400 font-black">العنوان *</label><input v-model="form.title" class="w-full rounded-2xl bg-gray-50 dark:bg-white/5 border-none py-3 px-4 text-sm font-black dark:text-white" /></div>
          <div class="space-y-1"><label class="text-xs text-gray-400 font-black">اسم العميل</label><input v-model="form.client_name" class="w-full rounded-2xl bg-gray-50 dark:bg-white/5 border-none py-3 px-4 text-sm font-black dark:text-white" /></div>
          <div class="space-y-1"><label class="text-xs text-gray-400 font-black">هاتف العميل</label><input v-model="form.client_phone" class="w-full rounded-2xl bg-gray-50 dark:bg-white/5 border-none py-3 px-4 text-sm font-black dark:text-white" /></div>
          <div class="space-y-1"><label class="text-xs text-gray-400 font-black">الفني</label>
            <select v-model="form.technician_id" class="w-full rounded-2xl bg-gray-50 dark:bg-white/5 border-none py-3 px-4 text-sm font-black dark:text-white">
              <option value="">اختر فني</option>
              <option v-for="t in technicians" :key="t.id" :value="t.id">{{ t.name }}</option>
            </select>
          </div>
          <div class="space-y-1"><label class="text-xs text-gray-400 font-black">الموعد</label><input v-model="form.scheduled_date" type="date" class="w-full rounded-2xl bg-gray-50 dark:bg-white/5 border-none py-3 px-4 text-sm font-black dark:text-white" /></div>
          <div class="space-y-1"><label class="text-xs text-gray-400 font-black">الأولوية</label>
            <select v-model="form.priority" class="w-full rounded-2xl bg-gray-50 dark:bg-white/5 border-none py-3 px-4 text-sm font-black dark:text-white">
              <option value="low">منخفضة</option><option value="normal">عادي</option><option value="high">عالية</option>
            </select>
          </div>
          <div class="space-y-1"><label class="text-xs text-gray-400 font-black">الحالة</label>
            <select v-model="form.status" class="w-full rounded-2xl bg-gray-50 dark:bg-white/5 border-none py-3 px-4 text-sm font-black dark:text-white">
              <option value="pending">معلق</option><option value="in_progress">جاري</option><option value="completed">مكتمل</option><option value="cancelled">ملغي</option>
            </select>
          </div>
          <div class="space-y-1 col-span-2"><label class="text-xs text-gray-400 font-black">الوصف</label><textarea v-model="form.description" rows="3" class="w-full rounded-2xl bg-gray-50 dark:bg-white/5 border-none py-3 px-4 text-sm font-black dark:text-white"></textarea></div>
        </div>
        <div class="flex justify-end gap-3">
          <button @click="showForm=false" class="px-6 py-3 rounded-2xl bg-gray-100 dark:bg-white/10 text-sm font-black text-gray-700 dark:text-gray-300">إلغاء</button>
          <button @click="submit" :disabled="saving" class="px-6 py-3 rounded-2xl bg-[#04724D] text-white text-sm font-black disabled:opacity-60">{{ saving ? 'جاري...' : 'حفظ' }}</button>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
