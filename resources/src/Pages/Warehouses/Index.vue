<script setup>
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import axios from 'axios';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Warehouse, Plus, Search, Trash2, Edit } from 'lucide-vue-next';

const warehouses = ref([]);
const totalRows = ref(0);
const loading = ref(false);
const filters = ref({ search: '', page: 1, limit: 10, SortField: 'id', SortType: 'desc' });
const showForm = ref(false);
const saving = ref(false);
const editingId = ref(null);
const form = ref({ name: '', Mobile: '', country: '', city: '', email: '', zip: '' });

const fetchWarehouses = async () => {
  loading.value = true;
  try {
    const { data } = await axios.get('/api/warehouses', { params: filters.value });
    warehouses.value = data.warehouses || [];
    totalRows.value = data.totalRows || 0;
  } finally { loading.value = false; }
};

const openCreate = () => { editingId.value = null; form.value = { name: '', Mobile: '', country: '', city: '', email: '', zip: '' }; showForm.value = true; };
const openEdit = (w) => { editingId.value = w.id; form.value = { name: w.name||'', Mobile: w.Mobile||'', country: w.country||'', city: w.city||'', email: w.email||'', zip: w.zip||'' }; showForm.value = true; };

const submit = async () => {
  saving.value = true;
  try {
    if (editingId.value) await axios.put(`/api/warehouses/${editingId.value}`, form.value);
    else await axios.post('/api/warehouses', form.value);
    showForm.value = false; fetchWarehouses();
  } catch (e) { alert(e.response?.data?.message || 'فشل الحفظ'); }
  finally { saving.value = false; }
};

const deleteWarehouse = async (id) => {
  if (!confirm('حذف هذا المستودع؟')) return;
  await axios.delete(`/api/warehouses/${id}`);
  fetchWarehouses();
};

onMounted(fetchWarehouses);
</script>

<template>
  <AdminLayout>
    <Head title="المستودعات" />
    <div class="space-y-8 p-6 md:p-10 font-['Cairo']" dir="rtl">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
          <div class="flex items-center gap-2 text-[#04724D] text-xs font-black uppercase tracking-widest mb-1"><Warehouse class="w-4 h-4" />إدارة المستودعات</div>
          <h1 class="text-4xl font-black text-gray-900 dark:text-white">المستودعات</h1>
        </div>
        <button @click="openCreate" class="bg-[#04724D] hover:bg-[#035a3d] text-[#3EFF8B] px-8 py-4 rounded-[20px] font-black text-xs uppercase tracking-widest flex items-center gap-2 transition-all">
          <Plus class="h-5 w-5" />إضافة مستودع
        </button>
      </div>

      <div class="bg-white dark:bg-[#0A0A0A] rounded-[40px] border border-gray-100 dark:border-gray-800 overflow-hidden">
        <div class="p-8 border-b border-gray-100 dark:border-gray-800">
          <div class="relative max-w-md">
            <Search class="absolute right-4 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" />
            <input v-model="filters.search" @input="filters.page=1; fetchWarehouses()" placeholder="بحث..." class="w-full pr-12 pl-4 py-4 bg-gray-50 dark:bg-white/5 border-none rounded-2xl text-sm font-black" />
          </div>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-right">
            <thead><tr class="text-[10px] font-black text-gray-400 uppercase tracking-widest bg-gray-50/50 dark:bg-white/[0.01]">
              <th class="px-8 py-6">الاسم</th><th class="px-6 py-6">الهاتف</th><th class="px-6 py-6">الدولة</th><th class="px-6 py-6">المدينة</th><th class="px-6 py-6">البريد</th><th class="px-6 py-6">إجراءات</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
              <tr v-if="loading" v-for="i in 4" :key="i" class="animate-pulse"><td v-for="j in 6" :key="j" class="px-6 py-5"><div class="h-5 bg-gray-100 dark:bg-white/5 rounded-xl"></div></td></tr>
              <tr v-else-if="warehouses.length===0"><td colspan="6" class="py-20 text-center text-gray-400 font-black text-sm">لا توجد مستودعات</td></tr>
              <tr v-for="w in warehouses" :key="w.id" class="group hover:bg-gray-50/50 dark:hover:bg-white/[0.01] transition-all">
                <td class="px-8 py-5 font-black text-gray-900 dark:text-white">{{ w.name }}</td>
                <td class="px-6 py-5 text-sm text-gray-600 dark:text-gray-400">{{ w.Mobile || '—' }}</td>
                <td class="px-6 py-5 text-sm text-gray-600 dark:text-gray-400">{{ w.country || '—' }}</td>
                <td class="px-6 py-5 text-sm text-gray-600 dark:text-gray-400">{{ w.city || '—' }}</td>
                <td class="px-6 py-5 text-sm text-gray-600 dark:text-gray-400">{{ w.email || '—' }}</td>
                <td class="px-6 py-5">
                  <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-all">
                    <button @click="openEdit(w)" class="p-3 bg-white dark:bg-[#1A1A1A] text-gray-400 hover:text-[#04724D] rounded-2xl border border-gray-100 dark:border-gray-800 transition-all"><Edit class="h-4 w-4" /></button>
                    <button @click="deleteWarehouse(w.id)" class="p-3 bg-white dark:bg-[#1A1A1A] text-gray-400 hover:text-red-500 rounded-2xl border border-gray-100 dark:border-gray-800 transition-all"><Trash2 class="h-4 w-4" /></button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="p-8 bg-gray-50/50 dark:bg-white/[0.02] border-t border-gray-100 dark:border-gray-800 flex items-center justify-between">
          <p class="text-xs font-black text-gray-400">الإجمالي: <span class="text-[#04724D] dark:text-[#3EFF8B]">{{ totalRows }}</span></p>
          <div class="flex gap-4">
            <button @click="filters.page--; fetchWarehouses()" :disabled="filters.page===1" class="text-xs font-black text-[#04724D] dark:text-[#3EFF8B] disabled:opacity-20">السابق</button>
            <button @click="filters.page++; fetchWarehouses()" :disabled="warehouses.length < filters.limit" class="text-xs font-black text-[#04724D] dark:text-[#3EFF8B] disabled:opacity-20">التالي</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Form Modal -->
    <div v-if="showForm" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
      <div class="bg-white dark:bg-[#121212] rounded-[32px] w-full max-w-lg p-8 space-y-5 shadow-2xl">
        <h2 class="text-xl font-black text-gray-900 dark:text-white">{{ editingId ? 'تعديل مستودع' : 'إضافة مستودع' }}</h2>
        <div class="grid grid-cols-2 gap-4">
          <div class="space-y-1 col-span-2"><label class="text-xs text-gray-400 font-black">الاسم *</label><input v-model="form.name" class="w-full rounded-2xl bg-gray-50 dark:bg-white/5 border-none py-3 px-4 text-sm font-black dark:text-white" /></div>
          <div class="space-y-1"><label class="text-xs text-gray-400 font-black">الهاتف</label><input v-model="form.Mobile" class="w-full rounded-2xl bg-gray-50 dark:bg-white/5 border-none py-3 px-4 text-sm font-black dark:text-white" /></div>
          <div class="space-y-1"><label class="text-xs text-gray-400 font-black">البريد</label><input v-model="form.email" type="email" class="w-full rounded-2xl bg-gray-50 dark:bg-white/5 border-none py-3 px-4 text-sm font-black dark:text-white" /></div>
          <div class="space-y-1"><label class="text-xs text-gray-400 font-black">الدولة</label><input v-model="form.country" class="w-full rounded-2xl bg-gray-50 dark:bg-white/5 border-none py-3 px-4 text-sm font-black dark:text-white" /></div>
          <div class="space-y-1"><label class="text-xs text-gray-400 font-black">المدينة</label><input v-model="form.city" class="w-full rounded-2xl bg-gray-50 dark:bg-white/5 border-none py-3 px-4 text-sm font-black dark:text-white" /></div>
        </div>
        <div class="flex justify-end gap-3 pt-2">
          <button @click="showForm=false" class="px-6 py-3 rounded-2xl bg-gray-100 dark:bg-white/10 text-sm font-black text-gray-700 dark:text-gray-300">إلغاء</button>
          <button @click="submit" :disabled="saving" class="px-6 py-3 rounded-2xl bg-[#04724D] text-white text-sm font-black disabled:opacity-60">{{ saving ? 'جاري الحفظ...' : 'حفظ' }}</button>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
