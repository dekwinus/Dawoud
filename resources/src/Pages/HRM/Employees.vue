<script setup>
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import axios from 'axios';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Users, Plus, Search, Trash2, Edit, Mail, Phone } from 'lucide-vue-next';

const employees = ref([]);
const totalRows = ref(0);
const loading = ref(false);
const filters = ref({ search: '', department_id: '', page: 1, limit: 10, SortField: 'id', SortType: 'desc' });
const departments = ref([]);
const companies = ref([]);
const officeShifts = ref([]);

const showForm = ref(false);
const saving = ref(false);
const editingId = ref(null);
const form = ref({ firstname: '', lastname: '', email: '', phone: '', department_id: '', company_id: '', designation_id: '', office_shift_id: '', gender: '', salary: 0, hiring_date: new Date().toISOString().split('T')[0] });

const fetchEmployees = async () => {
  loading.value = true;
  try {
    const { data } = await axios.get('/api/employees', { params: filters.value });
    employees.value = data.employees || [];
    totalRows.value = data.totalRows || 0;
  } finally { loading.value = false; }
};

const fetchLookups = async () => {
  try {
    const [depRes, compRes] = await Promise.all([axios.get('/api/departments'), axios.get('/api/company')]);
    departments.value = depRes.data.departments || [];
    companies.value = compRes.data.companies || [];
  } catch {}
};

const openCreate = () => { editingId.value = null; form.value = { firstname: '', lastname: '', email: '', phone: '', department_id: '', company_id: '', designation_id: '', office_shift_id: '', gender: '', salary: 0, hiring_date: new Date().toISOString().split('T')[0] }; showForm.value = true; };
const openEdit = (e) => { editingId.value = e.id; form.value = { firstname: e.firstname||'', lastname: e.lastname||'', email: e.email||'', phone: e.phone||'', department_id: e.department_id||'', company_id: e.company_id||'', designation_id: e.designation_id||'', office_shift_id: e.office_shift_id||'', gender: e.gender||'', salary: e.salary||0, hiring_date: e.hiring_date||'' }; showForm.value = true; };

const submit = async () => {
  saving.value = true;
  try {
    if (editingId.value) await axios.put(`/api/employees/${editingId.value}`, form.value);
    else await axios.post('/api/employees', form.value);
    showForm.value = false; fetchEmployees();
  } catch (e) { alert(e.response?.data?.message || 'فشل الحفظ'); }
  finally { saving.value = false; }
};

const deleteEmployee = async (id) => {
  if (!confirm('حذف هذا الموظف؟')) return;
  await axios.delete(`/api/employees/${id}`);
  fetchEmployees();
};

onMounted(async () => { await Promise.all([fetchEmployees(), fetchLookups()]); });
</script>

<template>
  <AdminLayout>
    <Head title="الموظفون" />
    <div class="space-y-8 p-6 md:p-10 font-['Cairo']" dir="rtl">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
          <div class="flex items-center gap-2 text-[#04724D] text-xs font-black uppercase tracking-widest mb-1"><Users class="w-4 h-4" />الموارد البشرية</div>
          <h1 class="text-4xl font-black text-gray-900 dark:text-white">إدارة الموظفين</h1>
        </div>
        <button @click="openCreate" class="bg-[#04724D] hover:bg-[#035a3d] text-[#3EFF8B] px-8 py-4 rounded-[20px] font-black text-xs uppercase tracking-widest flex items-center gap-2 transition-all">
          <Plus class="h-5 w-5" />إضافة موظف
        </button>
      </div>

      <div class="bg-white dark:bg-[#0A0A0A] rounded-[40px] border border-gray-100 dark:border-gray-800 overflow-hidden">
        <div class="p-8 border-b border-gray-100 dark:border-gray-800 flex flex-wrap gap-4">
          <div class="relative flex-1 min-w-[260px]">
            <Search class="absolute right-4 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" />
            <input v-model="filters.search" @input="filters.page=1; fetchEmployees()" placeholder="بحث بالاسم أو البريد..." class="w-full pr-12 pl-4 py-4 bg-gray-50 dark:bg-white/5 border-none rounded-2xl text-sm font-black" />
          </div>
          <select v-model="filters.department_id" @change="filters.page=1; fetchEmployees()" class="bg-gray-50 dark:bg-white/5 border-none rounded-2xl px-4 py-4 text-sm font-black text-gray-700 dark:text-gray-300 min-w-[200px]">
            <option value="">كل الأقسام</option>
            <option v-for="d in departments" :key="d.id" :value="d.id">{{ d.DepartmentName || d.name }}</option>
          </select>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-right">
            <thead><tr class="text-[10px] font-black text-gray-400 uppercase tracking-widest bg-gray-50/50 dark:bg-white/[0.01]">
              <th class="px-8 py-6">الموظف</th><th class="px-6 py-6">القسم</th><th class="px-6 py-6">الراتب</th><th class="px-6 py-6">تاريخ التعيين</th><th class="px-6 py-6">إجراءات</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
              <tr v-if="loading" v-for="i in 5" :key="i" class="animate-pulse"><td v-for="j in 5" :key="j" class="px-6 py-5"><div class="h-5 bg-gray-100 dark:bg-white/5 rounded-xl"></div></td></tr>
              <tr v-else-if="employees.length===0"><td colspan="5" class="py-20 text-center text-gray-400 font-black text-sm">لا يوجد موظفون</td></tr>
              <tr v-for="e in employees" :key="e.id" class="group hover:bg-gray-50/50 dark:hover:bg-white/[0.01] transition-all">
                <td class="px-8 py-5">
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-[#04724D]/10 flex items-center justify-center font-black text-[#04724D] text-sm">{{ (e.firstname||'?')[0] }}</div>
                    <div>
                      <p class="font-black text-gray-900 dark:text-white text-sm">{{ e.firstname }} {{ e.lastname }}</p>
                      <p class="text-xs text-gray-400">{{ e.email }}</p>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-5 text-sm font-black text-gray-600 dark:text-gray-400">{{ e.department_name || '—' }}</td>
                <td class="px-6 py-5 font-mono font-black text-gray-900 dark:text-white text-sm">{{ Number(e.salary||0).toLocaleString('ar-EG') }}</td>
                <td class="px-6 py-5 text-xs font-black text-gray-500 dark:text-gray-400">{{ e.hiring_date }}</td>
                <td class="px-6 py-5">
                  <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-all">
                    <button @click="openEdit(e)" class="p-3 bg-white dark:bg-[#1A1A1A] text-gray-400 hover:text-[#04724D] rounded-2xl border border-gray-100 dark:border-gray-800 transition-all"><Edit class="h-4 w-4" /></button>
                    <button @click="deleteEmployee(e.id)" class="p-3 bg-white dark:bg-[#1A1A1A] text-gray-400 hover:text-red-500 rounded-2xl border border-gray-100 dark:border-gray-800 transition-all"><Trash2 class="h-4 w-4" /></button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="p-8 bg-gray-50/50 dark:bg-white/[0.02] border-t border-gray-100 dark:border-gray-800 flex items-center justify-between">
          <p class="text-xs font-black text-gray-400">الإجمالي: <span class="text-[#04724D] dark:text-[#3EFF8B]">{{ totalRows }}</span></p>
          <div class="flex gap-4">
            <button @click="filters.page--; fetchEmployees()" :disabled="filters.page===1" class="text-xs font-black text-[#04724D] dark:text-[#3EFF8B] disabled:opacity-20">السابق</button>
            <button @click="filters.page++; fetchEmployees()" :disabled="employees.length < filters.limit" class="text-xs font-black text-[#04724D] dark:text-[#3EFF8B] disabled:opacity-20">التالي</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Form Modal -->
    <div v-if="showForm" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
      <div class="bg-white dark:bg-[#121212] rounded-[32px] w-full max-w-xl p-8 space-y-5 shadow-2xl max-h-[90vh] overflow-y-auto">
        <h2 class="text-xl font-black text-gray-900 dark:text-white">{{ editingId ? 'تعديل موظف' : 'إضافة موظف' }}</h2>
        <div class="grid grid-cols-2 gap-4">
          <div class="space-y-1"><label class="text-xs text-gray-400 font-black">الاسم الأول *</label><input v-model="form.firstname" class="w-full rounded-2xl bg-gray-50 dark:bg-white/5 border-none py-3 px-4 text-sm font-black dark:text-white" /></div>
          <div class="space-y-1"><label class="text-xs text-gray-400 font-black">اسم العائلة *</label><input v-model="form.lastname" class="w-full rounded-2xl bg-gray-50 dark:bg-white/5 border-none py-3 px-4 text-sm font-black dark:text-white" /></div>
          <div class="space-y-1"><label class="text-xs text-gray-400 font-black">البريد</label><input v-model="form.email" type="email" class="w-full rounded-2xl bg-gray-50 dark:bg-white/5 border-none py-3 px-4 text-sm font-black dark:text-white" /></div>
          <div class="space-y-1"><label class="text-xs text-gray-400 font-black">الهاتف</label><input v-model="form.phone" class="w-full rounded-2xl bg-gray-50 dark:bg-white/5 border-none py-3 px-4 text-sm font-black dark:text-white" /></div>
          <div class="space-y-1"><label class="text-xs text-gray-400 font-black">القسم</label>
            <select v-model="form.department_id" class="w-full rounded-2xl bg-gray-50 dark:bg-white/5 border-none py-3 px-4 text-sm font-black dark:text-white">
              <option value="">اختر قسم</option><option v-for="d in departments" :key="d.id" :value="d.id">{{ d.DepartmentName||d.name }}</option>
            </select></div>
          <div class="space-y-1"><label class="text-xs text-gray-400 font-black">الراتب</label><input v-model.number="form.salary" type="number" min="0" class="w-full rounded-2xl bg-gray-50 dark:bg-white/5 border-none py-3 px-4 text-sm font-black dark:text-white" /></div>
          <div class="space-y-1"><label class="text-xs text-gray-400 font-black">الجنس *</label>
            <select v-model="form.gender" class="w-full rounded-2xl bg-gray-50 dark:bg-white/5 border-none py-3 px-4 text-sm font-black dark:text-white">
              <option value="">اختر الجنس</option>
              <option value="male">ذكر</option>
              <option value="female">أنثى</option>
            </select>
          </div>
          <div class="space-y-1"><label class="text-xs text-gray-400 font-black">نوبة العمل *</label>
            <select v-model="form.office_shift_id" class="w-full rounded-2xl bg-gray-50 dark:bg-white/5 border-none py-3 px-4 text-sm font-black dark:text-white">
              <option value="">اختر نوبة</option>
              <option v-for="shift in officeShifts" :key="shift.id" :value="shift.id">{{ shift.name }}</option>
            </select>
          </div>
          <div class="space-y-1 col-span-2"><label class="text-xs text-gray-400 font-black">تاريخ التعيين</label><input v-model="form.hiring_date" type="date" class="w-full rounded-2xl bg-gray-50 dark:bg-white/5 border-none py-3 px-4 text-sm font-black dark:text-white" /></div>
        </div>
        <div class="flex justify-end gap-3 pt-2">
          <button @click="showForm=false" class="px-6 py-3 rounded-2xl bg-gray-100 dark:bg-white/10 text-sm font-black text-gray-700 dark:text-gray-300">إلغاء</button>
          <button @click="submit" :disabled="saving" class="px-6 py-3 rounded-2xl bg-[#04724D] text-white text-sm font-black disabled:opacity-60">{{ saving ? 'جاري الحفظ...' : 'حفظ' }}</button>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
