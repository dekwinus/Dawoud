<script setup>
import { Head } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';
import axios from 'axios';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const rows = ref([]);
const loading = ref(false);
const saving = ref(false);
const filters = ref({ status: '', page: 1, limit: 20 });
const showForm = ref(false);
const editingId = ref(null);
const form = ref({
  date: new Date().toISOString().slice(0, 10),
  description: '',
  reference_type: 'manual',
  reference_id: '',
  lines: [
    { coa_id: '', account_id: '', debit: 0, credit: 0, memo: '' }
  ]
});

const totalDebit = computed(() => form.value.lines.reduce((sum, l) => sum + (Number(l.debit) || 0), 0));
const totalCredit = computed(() => form.value.lines.reduce((sum, l) => sum + (Number(l.credit) || 0), 0));

const loadRows = async () => {
  loading.value = true;
  try {
    const { data } = await axios.get('/api/accounting/v2/journal-entries', { params: filters.value });
    rows.value = data.data || [];
  } finally {
    loading.value = false;
  }
};

const emptyLine = () => ({ coa_id: '', account_id: '', debit: 0, credit: 0, memo: '' });

const openCreate = () => {
  editingId.value = null;
  form.value = {
    date: new Date().toISOString().slice(0, 10),
    description: '',
    reference_type: 'manual',
    reference_id: '',
    lines: [emptyLine()]
  };
  showForm.value = true;
};

const openEdit = async (row) => {
  const { data } = await axios.get(`/api/accounting/v2/journal-entries/${row.id}`);
  editingId.value = row.id;
  form.value = {
    date: data.date || new Date().toISOString().slice(0, 10),
    description: data.description || '',
    reference_type: data.reference_type || 'manual',
    reference_id: data.reference_id || '',
    lines: (data.lines || []).map((l) => ({
      coa_id: l.coa_id || '',
      account_id: l.account_id || '',
      debit: Number(l.debit || 0),
      credit: Number(l.credit || 0),
      memo: l.memo || ''
    }))
  };
  if (form.value.lines.length === 0) {
    form.value.lines = [emptyLine()];
  }
  showForm.value = true;
};

const addLine = () => {
  form.value.lines.push(emptyLine());
};

const removeLine = (index) => {
  if (form.value.lines.length <= 1) return;
  form.value.lines.splice(index, 1);
};

const submit = async () => {
  saving.value = true;
  try {
    const payload = {
      date: form.value.date,
      description: form.value.description,
      reference_type: form.value.reference_type,
    };
    if (editingId.value) await axios.put(`/api/accounting/v2/journal-entries/${editingId.value}`, payload);
    else await axios.post('/api/accounting/v2/journal-entries', payload);
    showForm.value = false;
    loadRows();
  } catch (e) { alert(e.response?.data?.message || 'فشل الحفظ'); }
  finally { saving.value = false; }
};

const removeItem = async (id) => {
  if (!confirm('هل أنت متأكد من حذف القيد؟')) return;
  await axios.delete(`/api/accounting/v2/journal-entries/${id}`);
  await loadRows();
};

const postEntry = async (id) => {
  if (!confirm('ترحيل هذا القيد الآن؟')) return;
  await axios.post(`/api/accounting/v2/journal-entries/${id}/post`);
  await loadRows();
};

onMounted(loadRows);
</script>

<template>
  <AdminLayout>
    <Head title="قيود اليومية" />
    <div class="p-6 space-y-4" dir="rtl">
      <div class="flex items-center justify-between gap-3">
        <h1 class="text-2xl font-black">قيود اليومية</h1>
        <button @click="openCreate" class="px-4 py-2 rounded-xl bg-[#04724D] text-white text-sm font-black">إضافة قيد</button>
      </div>
      <select v-model="filters.status" @change="loadRows" class="rounded-xl bg-gray-50 border-0 w-full md:w-72">
        <option value="">كل الحالات</option>
        <option value="draft">مسودة</option>
        <option value="posted">مُرحّل</option>
      </select>
      <div class="overflow-x-auto bg-white rounded-2xl border border-gray-100">
        <table class="w-full text-sm">
          <thead class="bg-gray-50"><tr><th class="p-3 text-right">#</th><th class="p-3 text-right">التاريخ</th><th class="p-3 text-right">الوصف</th><th class="p-3 text-right">الحالة</th><th class="p-3 text-right">إجراءات</th></tr></thead>
          <tbody>
            <tr v-if="loading"><td class="p-4" colspan="5">جاري التحميل...</td></tr>
            <tr v-else-if="rows.length === 0"><td class="p-4" colspan="5">لا توجد بيانات</td></tr>
            <tr v-for="r in rows" :key="r.id" class="border-t">
              <td class="p-3">{{ r.id }}</td>
              <td class="p-3">{{ r.date }}</td>
              <td class="p-3">{{ r.description || '—' }}</td>
              <td class="p-3">{{ r.status }}</td>
              <td class="p-3">
                <div class="flex items-center gap-2">
                  <button @click="openEdit(r)" class="px-3 py-1 rounded-lg bg-gray-100 text-xs font-bold">تعديل</button>
                  <button @click="removeItem(r.id)" class="px-3 py-1 rounded-lg bg-red-100 text-red-700 text-xs font-bold">حذف</button>
                  <button v-if="r.status === 'draft'" @click="postEntry(r.id)" class="px-3 py-1 rounded-lg bg-emerald-100 text-emerald-700 text-xs font-bold">ترحيل</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-if="showForm" class="fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl w-full max-w-5xl p-6 space-y-4 max-h-[90vh] overflow-y-auto">
          <h2 class="text-lg font-black">{{ editingId ? 'تعديل قيد' : 'إضافة قيد' }}</h2>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <div class="space-y-2">
              <label class="text-xs text-gray-500">التاريخ</label>
              <input v-model="form.date" type="date" class="w-full rounded-xl bg-gray-50 border-0" />
            </div>
            <div class="space-y-2">
              <label class="text-xs text-gray-500">نوع المرجع</label>
              <input v-model="form.reference_type" class="w-full rounded-xl bg-gray-50 border-0" />
            </div>
            <div class="space-y-2">
              <label class="text-xs text-gray-500">رقم المرجع</label>
              <input v-model="form.reference_id" class="w-full rounded-xl bg-gray-50 border-0" />
            </div>
          </div>

          <div class="space-y-2">
            <label class="text-xs text-gray-500">الوصف</label>
            <textarea v-model="form.description" rows="2" class="w-full rounded-xl bg-gray-50 border-0"></textarea>
          </div>

          <div class="space-y-3">
            <div class="flex items-center justify-between">
              <h3 class="font-black text-sm">خطوط القيد</h3>
              <button @click="addLine" class="px-3 py-1 rounded-lg bg-gray-100 text-xs font-bold">إضافة سطر</button>
            </div>

            <div class="overflow-x-auto border rounded-xl">
              <table class="w-full text-xs">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="p-2 text-right">COA ID</th>
                    <th class="p-2 text-right">Account ID</th>
                    <th class="p-2 text-right">مدين</th>
                    <th class="p-2 text-right">دائن</th>
                    <th class="p-2 text-right">ملاحظة</th>
                    <th class="p-2 text-right">حذف</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(line, index) in form.lines" :key="index" class="border-t">
                    <td class="p-2"><input v-model="line.coa_id" type="number" min="1" class="w-full rounded-lg bg-gray-50 border-0" /></td>
                    <td class="p-2"><input v-model="line.account_id" type="number" min="1" class="w-full rounded-lg bg-gray-50 border-0" /></td>
                    <td class="p-2"><input v-model.number="line.debit" type="number" min="0" step="0.01" class="w-full rounded-lg bg-gray-50 border-0" /></td>
                    <td class="p-2"><input v-model.number="line.credit" type="number" min="0" step="0.01" class="w-full rounded-lg bg-gray-50 border-0" /></td>
                    <td class="p-2"><input v-model="line.memo" class="w-full rounded-lg bg-gray-50 border-0" /></td>
                    <td class="p-2"><button @click="removeLine(index)" class="px-2 py-1 rounded bg-red-100 text-red-700">×</button></td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div class="text-xs text-gray-600">
              إجمالي المدين: <span class="font-black">{{ totalDebit.toFixed(2) }}</span>
              <span class="mx-2">|</span>
              إجمالي الدائن: <span class="font-black">{{ totalCredit.toFixed(2) }}</span>
            </div>
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
