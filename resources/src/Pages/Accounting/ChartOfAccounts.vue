<script setup>
import { Head } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
import axios from 'axios';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const rows = ref([]);
const totalRows = ref(0);
const loading = ref(false);
const filters = ref({ search: '', type: '', active: '', page: 1, limit: 10, SortField: 'code', SortType: 'asc' });
const saving = ref(false);
const showForm = ref(false);
const editingId = ref(null);
const form = ref({
  code: '',
  name: '',
  type: 'asset',
  account_id: null,
  parent_id: null,
  level: 0,
  is_active: true
});

const loadRows = async () => {
  loading.value = true;
  try {
    const { data } = await axios.get('/api/accounting/v2/coa', { params: filters.value });
    rows.value = data.data || [];
    totalRows.value = data.totalRows || 0;
  } finally {
    loading.value = false;
  }
};

const openCreate = () => {
  editingId.value = null;
  form.value = {
    code: '',
    name: '',
    type: 'asset',
    account_id: null,
    parent_id: null,
    level: 0,
    is_active: true
  };
  showForm.value = true;
};

const openEdit = (row) => {
  editingId.value = row.id;
  form.value = {
    code: row.code || '',
    name: row.name || '',
    type: row.type || 'asset',
    account_id: row.account_id || null,
    parent_id: row.parent_id || null,
    level: Number(row.level || 0),
    is_active: Boolean(row.is_active)
  };
  showForm.value = true;
};

const submit = async () => {
  if (!form.value.code?.trim() || !form.value.name?.trim()) return;
  saving.value = true;
  try {
    if (editingId.value) {
      await axios.put(`/api/accounting/v2/coa/${editingId.value}`, form.value);
    } else {
      await axios.post('/api/accounting/v2/coa', form.value);
    }
    showForm.value = false;
    await loadRows();
  } finally {
    saving.value = false;
  }
};

const removeItem = async (id) => {
  if (!confirm('هل أنت متأكد من حذف الحساب؟')) return;
  await axios.delete(`/api/accounting/v2/coa/${id}`);
  await loadRows();
};

onMounted(loadRows);
</script>

<template>
  <AdminLayout>
    <Head title="شجرة الحسابات" />
    <div class="p-6 space-y-4" dir="rtl">
      <div class="flex items-center justify-between gap-3">
        <h1 class="text-2xl font-black">شجرة الحسابات</h1>
        <button @click="openCreate" class="px-4 py-2 rounded-xl bg-[#04724D] text-white text-sm font-black">إضافة حساب</button>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
        <input v-model="filters.search" @input="loadRows" class="rounded-xl bg-gray-50 border-0" placeholder="بحث" />
        <select v-model="filters.type" @change="loadRows" class="rounded-xl bg-gray-50 border-0">
          <option value="">كل الأنواع</option>
          <option value="asset">أصول</option><option value="liability">خصوم</option><option value="equity">حقوق ملكية</option><option value="income">إيرادات</option><option value="expense">مصروفات</option>
        </select>
      </div>
      <div class="text-sm text-gray-500">الإجمالي: {{ totalRows }}</div>
      <div class="overflow-x-auto bg-white rounded-2xl border border-gray-100">
        <table class="w-full text-sm">
          <thead class="bg-gray-50"><tr><th class="p-3 text-right">الكود</th><th class="p-3 text-right">الاسم</th><th class="p-3 text-right">النوع</th><th class="p-3 text-right">فعال</th><th class="p-3 text-right">إجراءات</th></tr></thead>
          <tbody>
            <tr v-if="loading"><td class="p-4" colspan="5">جاري التحميل...</td></tr>
            <tr v-else-if="rows.length === 0"><td class="p-4" colspan="5">لا توجد بيانات</td></tr>
            <tr v-for="r in rows" :key="r.id" class="border-t">
              <td class="p-3">{{ r.code }}</td>
              <td class="p-3">{{ r.name }}</td>
              <td class="p-3">{{ r.type }}</td>
              <td class="p-3">{{ r.is_active ? 'نعم' : 'لا' }}</td>
              <td class="p-3">
                <div class="flex items-center gap-2">
                  <button @click="openEdit(r)" class="px-3 py-1 rounded-lg bg-gray-100 text-xs font-bold">تعديل</button>
                  <button @click="removeItem(r.id)" class="px-3 py-1 rounded-lg bg-red-100 text-red-700 text-xs font-bold">حذف</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-if="showForm" class="fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl w-full max-w-xl p-6 space-y-4">
          <h2 class="text-lg font-black">{{ editingId ? 'تعديل حساب' : 'إضافة حساب' }}</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div class="space-y-2">
              <label class="text-xs text-gray-500">الكود</label>
              <input v-model="form.code" class="w-full rounded-xl bg-gray-50 border-0" />
            </div>
            <div class="space-y-2">
              <label class="text-xs text-gray-500">الاسم</label>
              <input v-model="form.name" class="w-full rounded-xl bg-gray-50 border-0" />
            </div>
            <div class="space-y-2">
              <label class="text-xs text-gray-500">النوع</label>
              <select v-model="form.type" class="w-full rounded-xl bg-gray-50 border-0">
                <option value="asset">أصول</option>
                <option value="liability">خصوم</option>
                <option value="equity">حقوق ملكية</option>
                <option value="income">إيرادات</option>
                <option value="expense">مصروفات</option>
              </select>
            </div>
            <div class="space-y-2">
              <label class="text-xs text-gray-500">المستوى</label>
              <input v-model.number="form.level" type="number" min="0" class="w-full rounded-xl bg-gray-50 border-0" />
            </div>
          </div>
          <label class="inline-flex items-center gap-2 text-sm"><input v-model="form.is_active" type="checkbox" /> فعال</label>
          <div class="flex items-center justify-end gap-2">
            <button @click="showForm = false" class="px-4 py-2 rounded-xl bg-gray-100 text-sm font-bold">إلغاء</button>
            <button :disabled="saving" @click="submit" class="px-4 py-2 rounded-xl bg-[#04724D] text-white text-sm font-black disabled:opacity-60">{{ saving ? 'جاري الحفظ...' : 'حفظ' }}</button>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
