<script setup>
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import axios from 'axios';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { DollarSign, Plus, Trash2, Edit } from 'lucide-vue-next';

const props = defineProps({ employees: Array });

const payrolls = ref([]);
const totalRows = ref(0);
const loading = ref(false);
const filters = ref({ employee_id: '', month: '', year: new Date().getFullYear(), page: 1, limit: 15, SortField: 'id', SortType: 'desc' });

const showForm = ref(false);
const saving = ref(false);
const editingId = ref(null);
const form = ref({ employee_id: '', month: new Date().getMonth() + 1, year: new Date().getFullYear(), basic_salary: 0, allowances: 0, deductions: 0, net_salary: 0, status: 'pending' });

const fetchPayrolls = async () => {
  loading.value = true;
  try {
    const { data } = await axios.get('/api/payroll', { params: filters.value });
    payrolls.value = data.payrolls || data.payroll || [];
    totalRows.value = data.totalRows || 0;
  } finally { loading.value = false; }
};

const openCreate = () => {
  editingId.value = null;
  form.value = { employee_id: '', month: new Date().getMonth() + 1, year: new Date().getFullYear(), basic_salary: 0, allowances: 0, deductions: 0, net_salary: 0, status: 'pending' };
  showForm.value = true;
};

const openEdit = (p) => {
  editingId.value = p.id;
  form.value = { employee_id: p.employee_id, month: p.month, year: p.year, basic_salary: p.basic_salary||0, allowances: p.allowances||0, deductions: p.deductions||0, net_salary: p.net_salary||0, status: p.status||'pending' };
  showForm.value = true;
};

const calcNet = () => { form.value.net_salary = (Number(form.value.basic_salary) + Number(form.value.allowances)) - Number(form.value.deductions); };

const submit = async () => {
  saving.value = true;
  try {
    if (editingId.value) await axios.put(`/api/payroll/${editingId.value}`, form.value);
    else await axios.post('/api/payroll', form.value);
    showForm.value = false; fetchPayrolls();
  } catch (e) { alert(e.response?.data?.message || 'فشل الحفظ'); }
  finally { saving.value = false; }
};

const deletePayroll = async (id) => {
  if (!confirm('حذف سجل الراتب؟')) return;
  await axios.delete(`/api/payroll/${id}`);
  fetchPayrolls();
};

const months = ['يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'];

onMounted(fetchPayrolls);
</script>

<template>
  <AdminLayout>
    <Head title="الرواتب" />
    <div class="space-y-8 p-6 md:p-10 font-['Cairo']" dir="rtl">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
          <div class="flex items-center gap-2 text-[#04724D] text-xs font-black uppercase tracking-widest mb-1"><DollarSign class="w-4 h-4" />الموارد البشرية</div>
          <h1 class="text-4xl font-black text-gray-900 dark:text-white">كشف الرواتب</h1>
        </div>
        <button @click="openCreate" class="bg-[#04724D] hover:bg-[#035a3d] text-[#3EFF8B] px-8 py-4 rounded-[20px] font-black text-xs uppercase tracking-widest flex items-center gap-2 transition-all">
          <Plus class="h-5 w-5" />إضافة راتب
        </button>
      </div>

      <div class="bg-white dark:bg-[#0A0A0A] rounded-[40px] border border-gray-100 dark:border-gray-800 overflow-hidden">
        <div class="p-8 border-b border-gray-100 dark:border-gray-800 flex flex-wrap gap-4">
          <select v-model="filters.employee_id" @change="filters.page=1; fetchPayrolls()" class="bg-gray-50 dark:bg-white/5 border-none rounded-2xl px-4 py-4 text-sm font-black text-gray-700 dark:text-gray-300 min-w-[200px]">
            <option value="">كل الموظفين</option>
            <option v-for="e in employees" :key="e.id" :value="e.id">{{ e.first_name }} {{ e.last_name }}</option>
          </select>
          <select v-model="filters.year" @change="filters.page=1; fetchPayrolls()" class="bg-gray-50 dark:bg-white/5 border-none rounded-2xl px-4 py-4 text-sm font-black text-gray-700 dark:text-gray-300">
            <option v-for="y in [2023,2024,2025,2026]" :key="y" :value="y">{{ y }}</option>
          </select>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-right">
            <thead><tr class="text-[10px] font-black text-gray-400 uppercase tracking-widest bg-gray-50/50 dark:bg-white/[0.01]">
              <th class="px-8 py-6">الموظف</th><th class="px-6 py-6">الشهر/السنة</th><th class="px-6 py-6">الراتب الأساسي</th><th class="px-6 py-6">البدلات</th><th class="px-6 py-6">الخصومات</th><th class="px-6 py-6">الصافي</th><th class="px-6 py-6">إجراءات</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
              <tr v-if="loading" v-for="i in 5" :key="i" class="animate-pulse"><td v-for="j in 7" :key="j" class="px-6 py-5"><div class="h-5 bg-gray-100 dark:bg-white/5 rounded-xl"></div></td></tr>
              <tr v-else-if="payrolls.length===0"><td colspan="7" class="py-20 text-center text-gray-400 font-black text-sm">لا توجد سجلات رواتب</td></tr>
              <tr v-for="p in payrolls" :key="p.id" class="group hover:bg-gray-50/50 dark:hover:bg-white/[0.01] transition-all">
                <td class="px-8 py-5 font-black text-gray-900 dark:text-white text-sm">{{ p.employee_name || '—' }}</td>
                <td class="px-6 py-5 text-sm text-gray-600 dark:text-gray-400">{{ months[(p.month||1)-1] }} {{ p.year }}</td>
                <td class="px-6 py-5 font-mono font-black text-gray-900 dark:text-white text-sm">{{ Number(p.basic_salary||0).toLocaleString('ar-EG') }}</td>
                <td class="px-6 py-5 font-mono text-sm text-green-600">+{{ Number(p.allowances||0).toLocaleString('ar-EG') }}</td>
                <td class="px-6 py-5 font-mono text-sm text-red-500">-{{ Number(p.deductions||0).toLocaleString('ar-EG') }}</td>
                <td class="px-6 py-5 font-mono font-black text-[#04724D] dark:text-[#3EFF8B] text-sm">{{ Number(p.net_salary||0).toLocaleString('ar-EG') }}</td>
                <td class="px-6 py-5">
                  <div class="flex gap-2 opacity-0 group-hover:opacity-100 transition-all">
                    <button @click="openEdit(p)" class="p-3 bg-white dark:bg-[#1A1A1A] text-gray-400 hover:text-[#04724D] rounded-2xl border border-gray-100 dark:border-gray-800"><Edit class="h-4 w-4" /></button>
                    <button @click="deletePayroll(p.id)" class="p-3 bg-white dark:bg-[#1A1A1A] text-gray-400 hover:text-red-500 rounded-2xl border border-gray-100 dark:border-gray-800"><Trash2 class="h-4 w-4" /></button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="p-8 bg-gray-50/50 dark:bg-white/[0.02] border-t border-gray-100 dark:border-gray-800 flex items-center justify-between">
          <p class="text-xs font-black text-gray-400">الإجمالي: <span class="text-[#04724D] dark:text-[#3EFF8B]">{{ totalRows }}</span></p>
          <div class="flex gap-4">
            <button @click="filters.page--; fetchPayrolls()" :disabled="filters.page===1" class="text-xs font-black text-[#04724D] dark:text-[#3EFF8B] disabled:opacity-20">السابق</button>
            <button @click="filters.page++; fetchPayrolls()" :disabled="payrolls.length < filters.limit" class="text-xs font-black text-[#04724D] dark:text-[#3EFF8B] disabled:opacity-20">التالي</button>
          </div>
        </div>
      </div>
    </div>

    <div v-if="showForm" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
      <div class="bg-white dark:bg-[#121212] rounded-[32px] w-full max-w-md p-8 space-y-5 shadow-2xl">
        <h2 class="text-xl font-black text-gray-900 dark:text-white">{{ editingId ? 'تعديل راتب' : 'إضافة راتب' }}</h2>
        <div class="space-y-4">
          <div class="space-y-1"><label class="text-xs text-gray-400 font-black">الموظف</label>
            <select v-model="form.employee_id" class="w-full rounded-2xl bg-gray-50 dark:bg-white/5 border-none py-3 px-4 text-sm font-black dark:text-white">
              <option value="">اختر موظف</option>
              <option v-for="e in employees" :key="e.id" :value="e.id">{{ e.first_name }} {{ e.last_name }}</option>
            </select>
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div class="space-y-1"><label class="text-xs text-gray-400 font-black">الشهر</label>
              <select v-model.number="form.month" class="w-full rounded-2xl bg-gray-50 dark:bg-white/5 border-none py-3 px-4 text-sm font-black dark:text-white">
                <option v-for="(m,i) in months" :key="i" :value="i+1">{{ m }}</option>
              </select>
            </div>
            <div class="space-y-1"><label class="text-xs text-gray-400 font-black">السنة</label>
              <input v-model.number="form.year" type="number" class="w-full rounded-2xl bg-gray-50 dark:bg-white/5 border-none py-3 px-4 text-sm font-black dark:text-white" />
            </div>
          </div>
          <div class="space-y-1"><label class="text-xs text-gray-400 font-black">الراتب الأساسي</label><input v-model.number="form.basic_salary" @input="calcNet" type="number" min="0" class="w-full rounded-2xl bg-gray-50 dark:bg-white/5 border-none py-3 px-4 text-sm font-black dark:text-white" /></div>
          <div class="grid grid-cols-2 gap-3">
            <div class="space-y-1"><label class="text-xs text-gray-400 font-black">البدلات</label><input v-model.number="form.allowances" @input="calcNet" type="number" min="0" class="w-full rounded-2xl bg-gray-50 dark:bg-white/5 border-none py-3 px-4 text-sm font-black dark:text-white" /></div>
            <div class="space-y-1"><label class="text-xs text-gray-400 font-black">الخصومات</label><input v-model.number="form.deductions" @input="calcNet" type="number" min="0" class="w-full rounded-2xl bg-gray-50 dark:bg-white/5 border-none py-3 px-4 text-sm font-black dark:text-white" /></div>
          </div>
          <div class="bg-[#04724D]/10 dark:bg-[#3EFF8B]/10 rounded-2xl p-4 text-center">
            <p class="text-xs text-gray-500 font-black mb-1">الراتب الصافي</p>
            <p class="text-2xl font-black text-[#04724D] dark:text-[#3EFF8B]">{{ Number(form.net_salary).toLocaleString('ar-EG') }}</p>
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
