<script setup>
import { Head } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';
import axios from 'axios';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  suppliers: { type: Array, default: () => [] },
  warehouses: { type: Array, default: () => [] }
});

const rows = ref([]);
const loading = ref(false);
const totalRows = ref(0);
const showDetails = ref(false);
const selectedPurchase = ref(null);
const deletingId = ref(null);
const filters = ref({
  search: '',
  provider_id: '',
  warehouse_id: '',
  page: 1,
  limit: 10,
  SortField: 'id',
  SortType: 'desc'
});

const loadRows = async () => {
  loading.value = true;
  try {
    const { data } = await axios.get('/api/purchases', { params: filters.value });
    rows.value = data.purchases || [];
    totalRows.value = data.totalRows || 0;
  } finally {
    loading.value = false;
  }
};

const totalAmount = computed(() => rows.value.reduce((sum, r) => sum + Number(r.GrandTotal || 0), 0));
const totalDue = computed(() => rows.value.reduce((sum, r) => sum + Number(r.due || 0), 0));
const unpaidCount = computed(() => rows.value.filter((r) => r.payment_status === 'unpaid').length);

const formatCurrency = (value) => {
  const numeric = Number(value || 0);
  return new Intl.NumberFormat('ar-EG', { style: 'currency', currency: 'EGP' }).format(numeric);
};

const openDetails = async (row) => {
  const { data } = await axios.get(`/api/purchases/${row.id}`);
  selectedPurchase.value = data;
  showDetails.value = true;
};

const openPrint = (row) => {
  window.open(`/api/purchase_print_html/${row.id}`, '_blank');
};

const removeItem = async (row) => {
  if (!confirm(`حذف المشتريات ${row.Ref}؟`)) return;
  deletingId.value = row.id;
  try {
    await axios.delete(`/api/purchases/${row.id}`);
    await loadRows();
  } finally {
    deletingId.value = null;
  }
};

onMounted(loadRows);
</script>

<template>
  <AdminLayout>
    <Head title="المشتريات" />
    <div class="p-6 space-y-4" dir="rtl">
      <h1 class="text-2xl font-black">المشتريات</h1>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
        <div class="bg-white rounded-2xl border border-gray-100 p-4">
          <p class="text-xs text-gray-400">إجمالي قيمة المشتريات</p>
          <p class="text-xl font-black">{{ formatCurrency(totalAmount) }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 p-4">
          <p class="text-xs text-gray-400">إجمالي المستحق</p>
          <p class="text-xl font-black text-red-600">{{ formatCurrency(totalDue) }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 p-4">
          <p class="text-xs text-gray-400">فواتير غير مدفوعة</p>
          <p class="text-xl font-black">{{ unpaidCount }}</p>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
        <input v-model="filters.search" @input="loadRows" class="rounded-xl bg-gray-50 border-0" placeholder="بحث" />
        <select v-model="filters.provider_id" @change="loadRows" class="rounded-xl bg-gray-50 border-0">
          <option value="">كل الموردين</option>
          <option v-for="s in props.suppliers" :key="s.id" :value="s.id">{{ s.name }}</option>
        </select>
        <select v-model="filters.warehouse_id" @change="loadRows" class="rounded-xl bg-gray-50 border-0">
          <option value="">كل المخازن</option>
          <option v-for="w in props.warehouses" :key="w.id" :value="w.id">{{ w.name }}</option>
        </select>
      </div>
      <div class="text-sm text-gray-500">الإجمالي: {{ totalRows }}</div>
      <div class="overflow-x-auto bg-white rounded-2xl border border-gray-100">
        <table class="w-full text-sm">
          <thead class="bg-gray-50">
            <tr><th class="p-3 text-right">المرجع</th><th class="p-3 text-right">المورد</th><th class="p-3 text-right">المخزن</th><th class="p-3 text-right">الحالة</th><th class="p-3 text-right">الإجمالي</th><th class="p-3 text-right">إجراءات</th></tr>
          </thead>
          <tbody>
            <tr v-if="loading"><td class="p-4" colspan="6">جاري التحميل...</td></tr>
            <tr v-else-if="rows.length === 0"><td class="p-4" colspan="6">لا توجد بيانات</td></tr>
            <tr v-for="r in rows" :key="r.id" class="border-t">
              <td class="p-3">{{ r.Ref }}</td>
              <td class="p-3">{{ r.provider_name }}</td>
              <td class="p-3">{{ r.warehouse_name }}</td>
              <td class="p-3">{{ r.statut }}</td>
              <td class="p-3">{{ formatCurrency(r.GrandTotal) }}</td>
              <td class="p-3">
                <div class="flex items-center gap-2">
                  <button @click="openDetails(r)" class="px-3 py-1 rounded-lg bg-gray-100 text-xs font-bold">عرض</button>
                  <button @click="openPrint(r)" class="px-3 py-1 rounded-lg bg-blue-100 text-blue-700 text-xs font-bold">طباعة</button>
                  <button :disabled="deletingId === r.id" @click="removeItem(r)" class="px-3 py-1 rounded-lg bg-red-100 text-red-700 text-xs font-bold disabled:opacity-50">حذف</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-if="showDetails" class="fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl w-full max-w-5xl p-6 space-y-4 max-h-[90vh] overflow-y-auto">
          <div class="flex items-center justify-between">
            <h2 class="text-lg font-black">تفاصيل {{ selectedPurchase?.purchase?.Ref }}</h2>
            <button @click="showDetails = false" class="px-3 py-1 rounded-lg bg-gray-100 text-sm font-bold">إغلاق</button>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-4 gap-3 text-sm">
            <div class="bg-gray-50 rounded-xl p-3">المورد: {{ selectedPurchase?.purchase?.supplier_name }}</div>
            <div class="bg-gray-50 rounded-xl p-3">المخزن: {{ selectedPurchase?.purchase?.warehouse }}</div>
            <div class="bg-gray-50 rounded-xl p-3">الإجمالي: {{ formatCurrency(selectedPurchase?.purchase?.GrandTotal) }}</div>
            <div class="bg-gray-50 rounded-xl p-3">المتبقي: {{ formatCurrency(selectedPurchase?.purchase?.due) }}</div>
          </div>

          <div class="overflow-x-auto border rounded-xl">
            <table class="w-full text-sm">
              <thead class="bg-gray-50">
                <tr>
                  <th class="p-3 text-right">المنتج</th>
                  <th class="p-3 text-right">الكمية</th>
                  <th class="p-3 text-right">الوحدة</th>
                  <th class="p-3 text-right">التكلفة</th>
                  <th class="p-3 text-right">الإجمالي</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="!(selectedPurchase?.details || []).length">
                  <td colspan="5" class="p-4 text-center text-gray-400">لا توجد تفاصيل</td>
                </tr>
                <tr v-for="(d, i) in (selectedPurchase?.details || [])" :key="i" class="border-t">
                  <td class="p-3">{{ d.name }}</td>
                  <td class="p-3">{{ d.quantity }}</td>
                  <td class="p-3">{{ d.unit_purchase }}</td>
                  <td class="p-3">{{ formatCurrency(d.Unit_cost) }}</td>
                  <td class="p-3">{{ formatCurrency(d.total) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
