<script setup>
import { Head, router } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import axios from 'axios';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Receipt, Plus, Search, Trash2, Eye, Filter, Warehouse, Calendar } from 'lucide-vue-next';

const props = defineProps({ warehouses: Array, categories: Array, accounts: Array, payment_methods: Array });

const expenses = ref([]);
const totalRows = ref(0);
const loading = ref(false);
const filters = ref({ search: '', warehouse_id: '', expense_category_id: '', page: 1, limit: 10, SortField: 'id', SortType: 'desc' });

const showForm = ref(false);
const saving = ref(false);
const editingId = ref(null);
const form = ref({ date: new Date().toISOString().split('T')[0], Ref: '', expense_category_id: '', warehouse_id: '', account_id: '', payment_method_id: '', amount: 0, details: '' });

const fetchExpenses = async () => {
  loading.value = true;
  try {
    const { data } = await axios.get('/api/expenses', { params: filters.value });
    expenses.value = data.expenses || [];
    totalRows.value = data.totalRows || 0;
  } finally { loading.value = false; }
};

const openCreate = () => {
  editingId.value = null;
  form.value = { date: new Date().toISOString().split('T')[0], Ref: '', expense_category_id: '', warehouse_id: '', account_id: '', payment_method_id: '', amount: 0, details: '' };
  showForm.value = true;
};

const openEdit = (e) => {
  editingId.value = e.id;
  form.value = { date: e.date, Ref: e.Ref, expense_category_id: e.expense_category_id, warehouse_id: e.warehouse_id, account_id: e.account_id, payment_method_id: e.payment_method_id, amount: e.amount, details: e.details || '' };
  showForm.value = true;
};

const submit = async () => {
  saving.value = true;
  try {
    // Wrap fields in 'expense' object as backend expects
    const payload = {
      expense: {
        date: form.value.date,
        warehouse_id: form.value.warehouse_id,
        category_id: form.value.expense_category_id,
        details: form.value.details,
        amount: form.value.amount,
        payment_method_id: form.value.payment_method_id,
      }
    };
    if (editingId.value) await axios.put(`/api/expenses/${editingId.value}`, payload);
    else await axios.post('/api/expenses', payload);
    showForm.value = false;
    fetchExpenses();
  } catch (e) { alert(e.response?.data?.message || 'فشل الحفظ'); }
  finally { saving.value = false; }
};

const deleteExpense = async (id) => {
  if (!confirm('حذف هذا المصروف؟')) return;
  await axios.delete(`/api/expenses/${id}`);
  fetchExpenses();
};

onMounted(fetchExpenses);
</script>

<template>
  <AdminLayout>
    <Head title="المصروفات" />
    <div class="space-y-8 p-6 md:p-10 font-['Cairo']" dir="rtl">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
          <div class="flex items-center gap-2 text-[#04724D] text-xs font-black uppercase tracking-widest mb-1"><Receipt class="w-4 h-4" />إدارة المصروفات</div>
          <h1 class="text-4xl font-black text-gray-900 dark:text-white">المصروفات</h1>
        </div>
        <button @click="openCreate" class="bg-[#04724D] hover:bg-[#035a3d] text-[#3EFF8B] px-8 py-4 rounded-[20px] font-black text-xs uppercase tracking-widest flex items-center gap-2 transition-all">
          <Plus class="h-5 w-5" />إضافة مصروف
        </button>
      </div>

      <div class="bg-white dark:bg-[#0A0A0A] rounded-[40px] border border-gray-100 dark:border-gray-800 overflow-hidden">
        <!-- Filters -->
        <div class="p-8 border-b border-gray-100 dark:border-gray-800 flex flex-wrap gap-4">
          <div class="relative flex-1 min-w-[260px]">
            <Search class="absolute right-4 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" />
            <input v-model="filters.search" @input="filters.page=1; fetchExpenses()" type="text" placeholder="بحث..." class="w-full pr-12 pl-4 py-4 bg-gray-50 dark:bg-white/5 border-none rounded-2xl text-sm font-black focus:ring-2 focus:ring-[#04724D]/20" />
          </div>
          <select v-model="filters.expense_category_id" @change="filters.page=1; fetchExpenses()" class="bg-gray-50 dark:bg-white/5 border-none rounded-2xl px-4 py-4 text-sm font-black text-gray-700 dark:text-gray-300 min-w-[200px]">
            <option value="">كل الفئات</option>
            <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
          </select>
          <select v-model="filters.warehouse_id" @change="filters.page=1; fetchExpenses()" class="bg-gray-50 dark:bg-white/5 border-none rounded-2xl px-4 py-4 text-sm font-black text-gray-700 dark:text-gray-300 min-w-[200px]">
            <option value="">كل المستودعات</option>
            <option v-for="w in warehouses" :key="w.id" :value="w.id">{{ w.name }}</option>
          </select>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
          <table class="w-full text-right">
            <thead><tr class="text-[10px] font-black text-gray-400 uppercase tracking-widest bg-gray-50/50 dark:bg-white/[0.01]">
              <th class="px-8 py-6">التاريخ</th>
              <th class="px-6 py-6">المرجع</th>
              <th class="px-6 py-6">الفئة</th>
              <th class="px-6 py-6">المستودع</th>
              <th class="px-6 py-6">المبلغ</th>
              <th class="px-6 py-6">إجراءات</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
              <tr v-if="loading" v-for="i in 5" :key="i" class="animate-pulse">
                <td v-for="j in 6" :key="j" class="px-6 py-5"><div class="h-5 bg-gray-100 dark:bg-white/5 rounded-xl"></div></td>
              </tr>
              <tr v-else-if="expenses.length === 0"><td colspan="6" class="py-20 text-center text-gray-400 font-black text-sm">لا توجد مصروفات</td></tr>
              <tr v-for="e in expenses" :key="e.id" class="group hover:bg-gray-50/50 dark:hover:bg-white/[0.01] transition-all">
                <td class="px-8 py-5 text-xs font-black text-gray-500 dark:text-gray-400">{{ e.date }}</td>
                <td class="px-6 py-5 font-mono font-black text-[#04724D] dark:text-[#3EFF8B] text-sm">{{ e.Ref }}</td>
                <td class="px-6 py-5 text-sm font-black text-gray-700 dark:text-gray-300">{{ e.expense_category_name || '—' }}</td>
                <td class="px-6 py-5 text-sm font-black text-gray-700 dark:text-gray-300">{{ e.warehouse_name || '—' }}</td>
                <td class="px-6 py-5 text-sm font-black text-gray-900 dark:text-white">{{ Number(e.amount).toLocaleString('ar-EG') }}</td>
                <td class="px-6 py-5">
                  <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-all">
                    <button @click="openEdit(e)" class="p-3 bg-white dark:bg-[#1A1A1A] text-gray-400 hover:text-[#04724D] rounded-2xl border border-gray-100 dark:border-gray-800 transition-all"><Eye class="h-4 w-4" /></button>
                    <button @click="deleteExpense(e.id)" class="p-3 bg-white dark:bg-[#1A1A1A] text-gray-400 hover:text-red-500 rounded-2xl border border-gray-100 dark:border-gray-800 transition-all"><Trash2 class="h-4 w-4" /></button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="p-8 bg-gray-50/50 dark:bg-white/[0.02] border-t border-gray-100 dark:border-gray-800 flex items-center justify-between">
          <p class="text-xs font-black text-gray-400">الإجمالي: <span class="text-[#04724D] dark:text-[#3EFF8B]">{{ totalRows }}</span></p>
          <div class="flex gap-4">
            <button @click="filters.page--; fetchExpenses()" :disabled="filters.page===1" class="text-xs font-black text-[#04724D] dark:text-[#3EFF8B] disabled:opacity-20">السابق</button>
            <button @click="filters.page++; fetchExpenses()" :disabled="expenses.length < filters.limit" class="text-xs font-black text-[#04724D] dark:text-[#3EFF8B] disabled:opacity-20">التالي</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Form Modal -->
    <div v-if="showForm" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
      <div class="bg-white dark:bg-[#121212] rounded-[32px] w-full max-w-xl p-8 space-y-5 shadow-2xl">
        <h2 class="text-xl font-black text-gray-900 dark:text-white">{{ editingId ? 'تعديل مصروف' : 'إضافة مصروف' }}</h2>
        <div class="grid grid-cols-2 gap-4">
          <div class="space-y-1"><label class="text-xs text-gray-400 font-black">التاريخ</label><input v-model="form.date" type="date" class="w-full rounded-2xl bg-gray-50 dark:bg-white/5 border-none py-3 px-4 text-sm font-black dark:text-white" /></div>
          <div class="space-y-1"><label class="text-xs text-gray-400 font-black">المرجع</label><input v-model="form.Ref" class="w-full rounded-2xl bg-gray-50 dark:bg-white/5 border-none py-3 px-4 text-sm font-black dark:text-white" /></div>
          <div class="space-y-1"><label class="text-xs text-gray-400 font-black">الفئة</label>
            <select v-model="form.expense_category_id" class="w-full rounded-2xl bg-gray-50 dark:bg-white/5 border-none py-3 px-4 text-sm font-black dark:text-white">
              <option value="">اختر فئة</option>
              <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
          </div>
          <div class="space-y-1"><label class="text-xs text-gray-400 font-black">المستودع</label>
            <select v-model="form.warehouse_id" class="w-full rounded-2xl bg-gray-50 dark:bg-white/5 border-none py-3 px-4 text-sm font-black dark:text-white">
              <option value="">اختر مستودع</option>
              <option v-for="w in warehouses" :key="w.id" :value="w.id">{{ w.name }}</option>
            </select>
          </div>
          <div class="space-y-1"><label class="text-xs text-gray-400 font-black">الحساب</label>
            <select v-model="form.account_id" class="w-full rounded-2xl bg-gray-50 dark:bg-white/5 border-none py-3 px-4 text-sm font-black dark:text-white">
              <option value="">اختر حساب</option>
              <option v-for="a in accounts" :key="a.id" :value="a.id">{{ a.account_name }}</option>
            </select>
          </div>
          <div class="space-y-1"><label class="text-xs text-gray-400 font-black">طريقة الدفع</label>
            <select v-model="form.payment_method_id" class="w-full rounded-2xl bg-gray-50 dark:bg-white/5 border-none py-3 px-4 text-sm font-black dark:text-white">
              <option value="">اختر طريقة</option>
              <option v-for="m in payment_methods" :key="m.id" :value="m.id">{{ m.name }}</option>
            </select>
          </div>
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
