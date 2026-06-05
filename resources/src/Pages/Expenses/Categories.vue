<script setup>
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import axios from 'axios';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const rows = ref([]);
const totalRows = ref(0);
const loading = ref(false);
const saving = ref(false);
const filters = ref({ search: '', page: 1, limit: 10, SortField: 'id', SortType: 'desc' });
const showForm = ref(false);
const editingId = ref(null);
const form = ref({ name: '', description: '' });

const loadRows = async () => {
  loading.value = true;
  try {
    const { data } = await axios.get('/api/expenses_category', { params: filters.value });
    rows.value = data.Expenses_category || [];
    totalRows.value = data.totalRows || 0;
  } finally {
    loading.value = false;
  }
};

const openCreate = () => {
  editingId.value = null;
  form.value = { name: '', description: '' };
  showForm.value = true;
};

const openEdit = (row) => {
  editingId.value = row.id;
  form.value = { name: row.name || '', description: row.description || '' };
  showForm.value = true;
};

const submit = async () => {
  if (!form.value.name?.trim()) return;
  saving.value = true;
  try {
    if (editingId.value) {
      await axios.put(`/api/expenses_category/${editingId.value}`, form.value);
    } else {
      await axios.post('/api/expenses_category', form.value);
    }
    showForm.value = false;
    await loadRows();
  } finally {
    saving.value = false;
  }
};

const removeItem = async (id) => {
  if (!confirm('هل أنت متأكد من حذف الفئة؟')) return;
  await axios.delete(`/api/expenses_category/${id}`);
  await loadRows();
};

onMounted(loadRows);
</script>

<template>
  <AdminLayout>
    <Head title="فئات المصروفات" />
    <div class="p-6 space-y-4" dir="rtl">
      <div class="flex items-center justify-between gap-3">
        <h1 class="text-2xl font-black">فئات المصروفات</h1>
        <button @click="openCreate" class="px-4 py-2 rounded-xl bg-[#04724D] text-white text-sm font-black">إضافة فئة</button>
      </div>

      <input v-model="filters.search" @input="loadRows" class="rounded-xl bg-gray-50 border-0 w-full md:w-96" placeholder="بحث" />
      <div class="text-sm text-gray-500">الإجمالي: {{ totalRows }}</div>

      <div class="overflow-x-auto bg-white rounded-2xl border border-gray-100">
        <table class="w-full text-sm">
          <thead class="bg-gray-50"><tr><th class="p-3 text-right">الاسم</th><th class="p-3 text-right">الوصف</th><th class="p-3 text-right">إجراءات</th></tr></thead>
          <tbody>
            <tr v-if="loading"><td class="p-4" colspan="3">جاري التحميل...</td></tr>
            <tr v-else-if="rows.length === 0"><td class="p-4" colspan="3">لا توجد بيانات</td></tr>
            <tr v-for="r in rows" :key="r.id" class="border-t">
              <td class="p-3">{{ r.name }}</td>
              <td class="p-3">{{ r.description || '—' }}</td>
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
        <div class="bg-white rounded-2xl w-full max-w-lg p-6 space-y-4">
          <h2 class="text-lg font-black">{{ editingId ? 'تعديل فئة مصروف' : 'إضافة فئة مصروف' }}</h2>
          <div class="space-y-2">
            <label class="text-xs text-gray-500">الاسم</label>
            <input v-model="form.name" class="w-full rounded-xl bg-gray-50 border-0" />
          </div>
          <div class="space-y-2">
            <label class="text-xs text-gray-500">الوصف</label>
            <textarea v-model="form.description" rows="3" class="w-full rounded-xl bg-gray-50 border-0"></textarea>
          </div>
          <div class="flex items-center justify-end gap-2">
            <button @click="showForm = false" class="px-4 py-2 rounded-xl bg-gray-100 text-sm font-bold">إلغاء</button>
            <button :disabled="saving" @click="submit" class="px-4 py-2 rounded-xl bg-[#04724D] text-white text-sm font-black disabled:opacity-60">{{ saving ? 'جاري الحفظ...' : 'حفظ' }}</button>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
