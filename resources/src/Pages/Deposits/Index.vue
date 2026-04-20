<script setup>
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import axios from 'axios';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { PiggyBank, Plus, Search, Trash2, Edit } from 'lucide-vue-next';

const props = defineProps({ accounts: Array, payment_methods: Array });

const deposits = ref([]);
const totalRows = ref(0);
const loading = ref(false);
const filters = ref({ search: '', page: 1, limit: 10, SortField: 'id', SortType: 'desc' });
const categories = ref([]);

const showForm = ref(false);
const saving = ref(false);
const editingId = ref(null);
const form = ref({ date: new Date().toISOString().split('T')[0], Ref: '', deposit_category_id: '', account_id: '', payment_method_id: '', amount: 0, details: '' });

const fetchDeposits = async () => {
  loading.value = true;
  try {
    const { data } = await axios.get('/api/deposits', { params: filters.value });
    deposits.value = data.deposits || [];
    totalRows.value = data.totalRows || 0;
  } finally { loading.value = false; }
};

const fetchCategories = async () => {
  try {
    const { data } = await axios.get('/api/deposits_category');
    categories.value = data.deposit_categories || [];
  } catch {}
};

const openCreate = () => { editingId.value = null; form.value = { date: new Date().toISOString().split('T')[0], Ref: '', deposit_category_id: '', account_id: '', payment_method_id: '', amount: 0, details: '' }; showForm.value = true; };
const openEdit = (d) => { editingId.value = d.id; form.value = { date: d.date, Ref: d.Ref, deposit_category_id: d.deposit_category_id||'', account_id: d.account_id||'', payment_method_id: d.payment_method_id||'', amount: d.amount||0, details: d.details||'' }; showForm.value = true; };

const submit = async () => {
  saving.value = true;
  try {
    const payload = {
      deposit: {
        date: form.value.date,
        deposit_category_id: form.value.deposit_category_id,
        account_id: form.value.account_id,
        payment_method_id: form.value.payment_method_id,
        amount: form.value.amount,
        details: form.value.details,
      }
    };
    if (editingId.value) await axios.put(`/api/deposits/${editingId.value}`, payload);
    else await axios.post('/api/deposits', payload);
    showForm.value = false; fetchDeposits();
  } catch (e) { alert(e.response?.data?.message || 'فشل الحفظ'); }
  finally { saving.value = false; }
};

const deleteDeposit = async (id) => {
  if (!confirm('حذف هذا الإيداع؟')) return;
  await axios.delete(`/api/deposits/${id}`);
  fetchDeposits();
};

onMounted(async () => { await Promise.all([fetchDeposits(), fetchCategories()]); });
</script>

<template>
  <AdminLayout>
    <Head title="الإيداعات" />
    <div class="space-y-8 p-6 md:p-10 font-['Cairo']" dir="rtl">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
          <div class="flex items-center gap-2 text-[#04724D] text-xs font-black uppercase tracking-widest mb-1"><PiggyBank class="w-4 h-4" />إدارة الإيداعات</div>
          <h1 class="text-xl font-black text-gray-900 dark:text-white">الإيداعات</h1>
        </div>
        <button @click="openCreate" class="bg-[#04724D] hover:bg-[#035a3d] text-[#3EFF8B] px-8 py-4 rounded-[20px] font-black text-xs uppercase tracking-widest flex items-center gap-2 transition-all">
          <Plus class="h-5 w-5" />إضافة إيداع
        </button>
      </div>

      <div class="bg-white dark:bg-[#0A0A0A] rounded-[40px] border border-gray-100 dark:border-gray-800 overflow-hidden">
        <div class="p-8 border-b border-gray-100 dark:border-gray-800">
          <div class="relative max-w-md">
            <Search class="absolute right-4 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" />
            <input v-model="filters.search" @input="filters.page=1; fetchDeposits()" placeholder="بحث..." class="w-full pr-12 pl-4 py-4 bg-gray-50 dark:bg-white/5 border-none rounded-2xl text-sm font-black" />
          </div>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-right">
            <thead><tr class="text-[10px] font-black text-gray-400 uppercase tracking-widest bg-gray-50/50 dark:bg-white/[0.01]">
              <th class="px-8 py-6">التاريخ</th><th class="px-6 py-6">المرجع</th><th class="px-6 py-6">الفئة</th><th class="px-6 py-6">المبلغ</th><th class="px-6 py-6">إجراءات</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
              <tr v-if="loading" v-for="i in 5" :key="i" class="animate-pulse"><td v-for="j in 5" :key="j" class="px-6 py-5"><div class="h-5 bg-gray-100 dark:bg-white/5 rounded-xl"></div></td></tr>
              <tr v-else-if="deposits.length===0"><td colspan="5" class="py-20 text-center text-gray-400 font-black text-sm">لا توجد إيداعات</td></tr>
              <tr v-for="d in deposits" :key="d.id" class="group hover:bg-gray-50/50 dark:hover:bg-white/[0.01] transition-all">
                <td class="px-8 py-5 text-xs font-black text-gray-500 dark:text-gray-400">{{ d.date }}</td>
                <td class="px-6 py-5 font-mono font-black text-[#04724D] dark:text-[#3EFF8B]">{{ d.Ref }}</td>
                <td class="px-6 py-5 text-sm font-black text-gray-600 dark:text-gray-400">{{ d.deposit_category_name || '—' }}</td>
                <td class="px-6 py-5 font-mono font-black text-gray-900 dark:text-white">{{ Number(d.amount||0).toLocaleString('ar-EG') }}</td>
                <td class="px-6 py-5">
                  <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-all">
                    <button @click="openEdit(d)" class="p-3 bg-white dark:bg-[#1A1A1A] text-gray-400 hover:text-[#04724D] rounded-2xl border border-gray-100 dark:border-gray-800 transition-all"><Edit class="h-4 w-4" /></button>
                    <button @click="deleteDeposit(d.id)" class="p-3 bg-white dark:bg-[#1A1A1A] text-gray-400 hover:text-red-500 rounded-2xl border border-gray-100 dark:border-gray-800 transition-all"><Trash2 class="h-4 w-4" /></button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="p-8 bg-gray-50/50 dark:bg-white/[0.02] border-t border-gray-100 dark:border-gray-800 flex items-center justify-between">
          <p class="text-xs font-black text-gray-400">الإجمالي: <span class="text-[#04724D] dark:text-[#3EFF8B]">{{ totalRows }}</span></p>
          <div class="flex gap-4">
            <button @click="filters.page--; fetchDeposits()" :disabled="filters.page===1" class="text-xs font-black text-[#04724D] dark:text-[#3EFF8B] disabled:opacity-20">السابق</button>
            <button @click="filters.page++; fetchDeposits()" :disabled="deposits.length < filters.limit" class="text-xs font-black text-[#04724D] dark:text-[#3EFF8B] disabled:opacity-20">التالي</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Form Modal -->
    <div v-if="showForm" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
      <div class="bg-white dark:bg-[#121212] rounded-[32px] w-full max-w-lg p-8 space-y-5 shadow-2xl">
        <h2 class="text-xl font-black text-gray-900 dark:text-white">{{ editingId ? 'تعديل إيداع' : 'إضافة إيداع' }}</h2>
        <div class="grid grid-cols-2 gap-4">
          <div class="space-y-1"><label class="text-xs text-gray-400 font-black">التاريخ</label><input v-model="form.date" type="date" class="w-full rounded-2xl bg-gray-50 dark:bg-white/5 border-none py-3 px-4 text-sm font-black dark:text-white" /></div>
          <div class="space-y-1"><label class="text-xs text-gray-400 font-black">المرجع</label><input v-model="form.Ref" class="w-full rounded-2xl bg-gray-50 dark:bg-white/5 border-none py-3 px-4 text-sm font-black dark:text-white" /></div>
          <div class="space-y-1"><label class="text-xs text-gray-400 font-black">الفئة</label>
            <select v-model="form.deposit_category_id" class="w-full rounded-2xl bg-gray-50 dark:bg-white/5 border-none py-3 px-4 text-sm font-black dark:text-white">
              <option value="">اختر فئة</option><option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select></div>
          <div class="space-y-1"><label class="text-xs text-gray-400 font-black">الحساب</label>
            <select v-model="form.account_id" class="w-full rounded-2xl bg-gray-50 dark:bg-white/5 border-none py-3 px-4 text-sm font-black dark:text-white">
              <option value="">اختر حساب</option><option v-for="a in accounts" :key="a.id" :value="a.id">{{ a.account_name }}</option>
            </select></div>
          <div class="space-y-1 col-span-2"><label class="text-xs text-gray-400 font-black">المبلغ</label><input v-model.number="form.amount" type="number" min="0" step="0.01" class="w-full rounded-2xl bg-gray-50 dark:bg-white/5 border-none py-3 px-4 text-sm font-black dark:text-white" /></div>
          <div class="space-y-1 col-span-2"><label class="text-xs text-gray-400 font-black">التفاصيل</label><textarea v-model="form.details" rows="2" class="w-full rounded-2xl bg-gray-50 dark:bg-white/5 border-none py-3 px-4 text-sm font-black dark:text-white"></textarea></div>
        </div>
        <div class="flex justify-end gap-3 pt-2">
          <button @click="showForm=false" class="px-6 py-3 rounded-2xl bg-gray-100 dark:bg-white/10 text-sm font-black text-gray-700 dark:text-gray-300">إلغاء</button>
          <button @click="submit" :disabled="saving" class="px-6 py-3 rounded-2xl bg-[#04724D] text-white text-sm font-black disabled:opacity-60">{{ saving ? 'جاري الحفظ...' : 'حفظ' }}</button>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
