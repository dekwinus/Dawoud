<script setup>
import { Head } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';
import axios from 'axios';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const rows = ref([]);
const loading = ref(false);
const totalRows = ref(0);
const saving = ref(false);
const deletingId = ref(null);
const showForm = ref(false);
const editingId = ref(null);
const formMode = ref('edit');
const filters = ref({
  search: '',
  status: '',
  page: 1,
  limit: 10,
  SortField: 'id',
  SortType: 'desc'
});
const form = ref({
  Ref: '',
  sale_id: '',
  delivered_to: '',
  shipping_address: '',
  shipping_details: '',
  status: ''
});

const loadRows = async () => {
  loading.value = true;
  try {
    const { data } = await axios.get('/api/shipments', { params: filters.value });
    rows.value = (data.shipments || []).filter((s) => !filters.value.status || s.status === filters.value.status);
    totalRows.value = filters.value.status ? rows.value.length : (data.totalRows || 0);
  } finally {
    loading.value = false;
  }
};

const deliveredCount = computed(() => rows.value.filter((r) => r.status === 'delivered').length);
const pendingCount = computed(() => rows.value.filter((r) => r.status === 'pending').length);

const openEdit = (row) => {
  formMode.value = 'edit';
  editingId.value = row.id;
  form.value = {
    Ref: row.shipment_ref || '',
    sale_id: row.sale_id || '',
    delivered_to: row.delivered_to || '',
    shipping_address: row.shipping_address || '',
    shipping_details: row.shipping_details || '',
    status: row.status || ''
  };
  showForm.value = true;
};

const openCreate = () => {
  formMode.value = 'create';
  editingId.value = null;
  form.value = {
    Ref: '',
    sale_id: '',
    delivered_to: '',
    shipping_address: '',
    shipping_details: '',
    status: 'pending'
  };
  showForm.value = true;
};

const preloadRefBySale = async () => {
  if (!form.value.sale_id) return;
  try {
    const { data } = await axios.get(`/api/shipments/${form.value.sale_id}`);
    if (data?.shipment?.Ref) {
      form.value.Ref = data.shipment.Ref;
    }
  } catch (e) {
    console.error('Failed to preload shipment ref', e);
  }
};

const submit = async () => {
  if (!form.value.sale_id || !form.value.status) return;
  saving.value = true;
  try {
    if (formMode.value === 'create') {
      await axios.post('/api/shipments', form.value);
    } else {
      await axios.put(`/api/shipments/${editingId.value}`, form.value);
    }
    showForm.value = false;
    await loadRows();
  } finally {
    saving.value = false;
  }
};

const removeItem = async (row) => {
  if (!confirm(`حذف الشحنة ${row.shipment_ref}؟`)) return;
  deletingId.value = row.id;
  try {
    await axios.delete(`/api/shipments/${row.id}`, { data: { status: 'pending' } });
    await loadRows();
  } finally {
    deletingId.value = null;
  }
};

onMounted(loadRows);
</script>

<template>
  <AdminLayout>
    <Head title="الشحنات" />
    <div class="p-6 space-y-4" dir="rtl">
      <div class="flex items-center justify-between gap-3">
        <h1 class="text-2xl font-black">الشحنات</h1>
        <button @click="openCreate" class="px-4 py-2 rounded-xl bg-[#04724D] text-white text-sm font-black">إضافة شحنة</button>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        <div class="bg-white rounded-2xl border border-gray-100 p-4">
          <p class="text-xs text-gray-400">شحنات قيد الانتظار</p>
          <p class="text-xl font-black">{{ pendingCount }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 p-4">
          <p class="text-xs text-gray-400">شحنات تم تسليمها</p>
          <p class="text-xl font-black text-[#04724D]">{{ deliveredCount }}</p>
        </div>
      </div>

      <div class="flex flex-col md:flex-row gap-3">
        <input v-model="filters.search" @input="loadRows" class="rounded-xl bg-gray-50 border-0 w-full md:w-96" placeholder="بحث" />
        <select v-model="filters.status" @change="loadRows" class="rounded-xl bg-gray-50 border-0 w-full md:w-56">
          <option value="">كل الحالات</option>
          <option value="pending">قيد الانتظار</option>
          <option value="shipped">تم الشحن</option>
          <option value="delivered">تم التسليم</option>
        </select>
      </div>

      <div class="text-sm text-gray-500">الإجمالي: {{ totalRows }}</div>
      <div class="overflow-x-auto bg-white rounded-2xl border border-gray-100">
        <table class="w-full text-sm">
          <thead class="bg-gray-50">
            <tr><th class="p-3 text-right">المرجع</th><th class="p-3 text-right">مرجع البيع</th><th class="p-3 text-right">العميل</th><th class="p-3 text-right">الحالة</th><th class="p-3 text-right">تاريخ</th><th class="p-3 text-right">إجراءات</th></tr>
          </thead>
          <tbody>
            <tr v-if="loading"><td class="p-4" colspan="6">جاري التحميل...</td></tr>
            <tr v-else-if="rows.length === 0"><td class="p-4" colspan="6">لا توجد بيانات</td></tr>
            <tr v-for="r in rows" :key="r.id" class="border-t">
              <td class="p-3">{{ r.shipment_ref }}</td>
              <td class="p-3">{{ r.sale_ref }}</td>
              <td class="p-3">{{ r.customer_name }}</td>
              <td class="p-3">{{ r.status }}</td>
              <td class="p-3">{{ r.date }}</td>
              <td class="p-3">
                <div class="flex items-center gap-2">
                  <button @click="openEdit(r)" class="px-3 py-1 rounded-lg bg-gray-100 text-xs font-bold">تعديل</button>
                  <button :disabled="deletingId === r.id" @click="removeItem(r)" class="px-3 py-1 rounded-lg bg-red-100 text-red-700 text-xs font-bold disabled:opacity-50">حذف</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-if="showForm" class="fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl w-full max-w-2xl p-6 space-y-4">
          <h2 class="text-lg font-black">{{ formMode === 'create' ? 'إضافة شحنة' : `تعديل الشحنة ${form.Ref}` }}</h2>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div class="space-y-2">
              <label class="text-xs text-gray-500">رقم مرجع البيع</label>
              <input v-model.number="form.sale_id" @blur="preloadRefBySale" type="number" min="1" class="w-full rounded-xl bg-gray-50 border-0" :disabled="formMode === 'edit'" />
            </div>
            <div class="space-y-2">
              <label class="text-xs text-gray-500">مرجع الشحنة</label>
              <input v-model="form.Ref" class="w-full rounded-xl bg-gray-50 border-0" />
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div class="space-y-2">
              <label class="text-xs text-gray-500">تم التسليم إلى</label>
              <input v-model="form.delivered_to" class="w-full rounded-xl bg-gray-50 border-0" />
            </div>
            <div class="space-y-2">
              <label class="text-xs text-gray-500">الحالة</label>
              <select v-model="form.status" class="w-full rounded-xl bg-gray-50 border-0">
                <option value="pending">قيد الانتظار</option>
                <option value="shipped">تم الشحن</option>
                <option value="delivered">تم التسليم</option>
              </select>
            </div>
          </div>

          <div class="space-y-2">
            <label class="text-xs text-gray-500">عنوان الشحن</label>
            <input v-model="form.shipping_address" class="w-full rounded-xl bg-gray-50 border-0" />
          </div>

          <div class="space-y-2">
            <label class="text-xs text-gray-500">تفاصيل الشحن</label>
            <textarea v-model="form.shipping_details" rows="3" class="w-full rounded-xl bg-gray-50 border-0"></textarea>
          </div>

          <div class="flex items-center justify-end gap-2">
            <button @click="showForm = false" class="px-4 py-2 rounded-xl bg-gray-100 text-sm font-bold">إلغاء</button>
            <button :disabled="saving" @click="submit" class="px-4 py-2 rounded-xl bg-[#04724D] text-white text-sm font-black disabled:opacity-60">{{ saving ? 'جاري الحفظ...' : (formMode === 'create' ? 'إنشاء' : 'حفظ') }}</button>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
