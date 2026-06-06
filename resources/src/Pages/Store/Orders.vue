<script setup>
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import axios from 'axios';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { CheckCircle2, Eye, Search, ShoppingCart, XCircle } from 'lucide-vue-next';

const orders = ref([]);
const meta = ref({ total: 0, page: 1, pages: 1 });
const loading = ref(false);
const filters = ref({ q: '', status: '', page: 1, per_page: 15 });
const updatingId = ref(null);
const errorMessage = ref('');
const successMessage = ref('');
let searchTimer = null;

const showDetails = ref(false);
const selectedOrder = ref(null);

const fetchOrders = async () => {
  loading.value = true;
  errorMessage.value = '';
  try {
    const { data } = await axios.get('/api/store/orders', { params: filters.value });
    orders.value = data.data || [];
    meta.value = data.meta || { total: 0, page: 1, pages: 1 };
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'تعذر تحميل طلبات المتجر.';
  } finally {
    loading.value = false;
  }
};

const viewOrder = async (id) => {
  errorMessage.value = '';
  try {
    const { data } = await axios.get(`/api/store/orders/${id}`);
    selectedOrder.value = data;
    showDetails.value = true;
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'تعذر تحميل تفاصيل الطلب.';
  }
};

const updateStatus = async (id, status) => {
  const action = status === 'confirmed' ? 'تأكيد الطلب وتحويله إلى فاتورة بيع' : 'إلغاء الطلب';
  if (!window.confirm(`${action}؟`)) return;

  updatingId.value = id;
  errorMessage.value = '';
  successMessage.value = '';
  try {
    const { data } = await axios.patch(`/api/store/orders/${id}`, { status });
    successMessage.value = status === 'confirmed'
      ? `تم إنشاء فاتورة البيع ${data.sale_ref || ''} بنجاح.`
      : 'تم إلغاء الطلب.';
    await fetchOrders();
    if (selectedOrder.value?.id === id) {
      await viewOrder(id);
    }
  } catch (error) {
    const response = error.response?.data;
    const stockItems = response?.items?.map(item => `${item.name}: المتاح ${item.available}`).join('، ');
    errorMessage.value = stockItems
      || response?.error
      || Object.values(response?.errors || {}).flat().join(' ')
      || response?.message
      || 'تعذر تحديث حالة الطلب.';
  } finally {
    updatingId.value = null;
  }
};

const scheduleSearch = () => {
  window.clearTimeout(searchTimer);
  searchTimer = window.setTimeout(() => {
    filters.value.page = 1;
    fetchOrders();
  }, 300);
};

const changePage = (page) => {
  if (page < 1 || page > meta.value.pages) return;
  filters.value.page = page;
  fetchOrders();
};

const statusClass = (status) => ({
  pending: 'bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300',
  confirmed: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300',
  cancelled: 'bg-red-100 text-red-700 dark:bg-red-500/10 dark:text-red-300'
}[status] || 'bg-gray-100 text-gray-600');
const statusLabel = (status) => ({
  pending: 'قيد المراجعة',
  confirmed: 'تم التحويل إلى بيع',
  cancelled: 'ملغي'
}[status] || status);

onMounted(fetchOrders);
</script>

<template>
  <AdminLayout>
    <Head title="طلبات المتجر" />
    <div class="space-y-8 p-6 md:p-10 font-['Cairo']" dir="rtl">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
          <div class="flex items-center gap-2 text-[#04724D] text-xs font-black uppercase tracking-widest mb-1"><ShoppingCart class="w-4 h-4" />المتجر الإلكتروني</div>
          <h1 class="text-4xl font-black text-gray-900 dark:text-white">طلبات المتجر</h1>
        </div>
      </div>

      <div v-if="successMessage" aria-live="polite" class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-black text-emerald-700 dark:border-emerald-900/50 dark:bg-emerald-950/30 dark:text-emerald-300">
        {{ successMessage }}
      </div>
      <div v-if="errorMessage" role="alert" class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm font-black text-red-700 dark:border-red-900/50 dark:bg-red-950/30 dark:text-red-300">
        {{ errorMessage }}
      </div>

      <div class="bg-white dark:bg-[#0A0A0A] rounded-[40px] border border-gray-100 dark:border-gray-800 overflow-hidden">
        <div class="p-8 border-b border-gray-100 dark:border-gray-800 flex flex-wrap gap-4">
          <div class="relative flex-1 min-w-[260px]">
            <Search class="absolute right-4 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" />
            <input v-model="filters.q" @input="scheduleSearch" placeholder="ابحث برقم الطلب أو اسم العميل..." class="w-full pr-12 pl-4 py-4 bg-gray-50 dark:bg-white/5 border-none rounded-2xl text-sm font-black" />
          </div>
          <select v-model="filters.status" @change="filters.page=1; fetchOrders()" class="bg-gray-50 dark:bg-white/5 border-none rounded-2xl px-4 py-4 text-sm font-black text-gray-700 dark:text-gray-300">
            <option value="">كل الحالات</option>
            <option value="pending">قيد المراجعة</option>
            <option value="confirmed">تم التحويل إلى بيع</option>
            <option value="cancelled">ملغي</option>
          </select>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-right">
            <thead><tr class="text-[10px] font-black text-gray-400 uppercase tracking-widest bg-gray-50/50 dark:bg-white/[0.01]">
              <th class="px-8 py-6">رقم الطلب</th><th class="px-6 py-6">العميل</th><th class="px-6 py-6">المبلغ</th><th class="px-6 py-6">التاريخ</th><th class="px-6 py-6">الحالة</th><th class="px-6 py-6">إجراءات</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
              <tr v-if="loading" v-for="i in 5" :key="i" class="animate-pulse"><td v-for="j in 6" :key="j" class="px-6 py-5"><div class="h-5 bg-gray-100 dark:bg-white/5 rounded-xl"></div></td></tr>
              <tr v-else-if="orders.length===0"><td colspan="6" class="py-20 text-center text-gray-400 font-black text-sm">لا توجد طلبات</td></tr>
              <tr v-for="o in orders" :key="o.id" class="group hover:bg-gray-50/50 dark:hover:bg-white/[0.01] transition-all">
                <td class="px-8 py-5">
                  <p class="font-black text-gray-900 dark:text-white text-sm">{{ o.code }}</p>
                  <p v-if="o.sale_ref" class="mt-1 text-[10px] font-black text-[#04724D] dark:text-[#3EFF8B]">فاتورة: {{ o.sale_ref }}</p>
                </td>
                <td class="px-6 py-5 text-sm text-gray-600 dark:text-gray-400">{{ o.customer_name || o.client_name || '—' }}</td>
                <td class="px-6 py-5 font-mono font-black text-[#04724D] dark:text-[#3EFF8B] text-sm">{{ Number(o.total||o.amount||0).toLocaleString('ar-EG') }}</td>
                <td class="px-6 py-5 text-xs font-black text-gray-500 dark:text-gray-400">{{ o.created_at }}</td>
                <td class="px-6 py-5"><span :class="statusClass(o.status)" class="px-3 py-1 rounded-full text-[10px] font-black">{{ statusLabel(o.status) }}</span></td>
                <td class="px-6 py-5">
                  <div class="flex flex-wrap gap-2">
                    <button aria-label="عرض تفاصيل الطلب" @click="viewOrder(o.id)" class="min-h-11 min-w-11 p-3 bg-white dark:bg-[#1A1A1A] text-gray-500 hover:text-[#04724D] rounded-2xl border border-gray-100 dark:border-gray-800"><Eye class="h-4 w-4" /></button>
                    <button
                      v-if="o.status === 'pending'"
                      :disabled="updatingId === o.id"
                      @click="updateStatus(o.id, 'confirmed')"
                      class="inline-flex min-h-11 items-center gap-2 rounded-2xl bg-[#04724D] px-4 py-2 text-xs font-black text-white disabled:opacity-50"
                    >
                      <CheckCircle2 class="h-4 w-4" />
                      تأكيد البيع
                    </button>
                    <button
                      v-if="o.status === 'pending'"
                      :disabled="updatingId === o.id"
                      @click="updateStatus(o.id, 'cancelled')"
                      class="inline-flex min-h-11 items-center gap-2 rounded-2xl bg-red-50 px-4 py-2 text-xs font-black text-red-600 dark:bg-red-500/10 dark:text-red-300 disabled:opacity-50"
                    >
                      <XCircle class="h-4 w-4" />
                      إلغاء
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="p-8 bg-gray-50/50 dark:bg-white/[0.02] border-t border-gray-100 dark:border-gray-800 flex items-center justify-between">
          <p class="text-xs font-black text-gray-400">الإجمالي: <span class="text-[#04724D] dark:text-[#3EFF8B]">{{ meta.total }}</span></p>
          <div class="flex gap-4">
            <button @click="changePage(meta.page - 1)" :disabled="meta.page === 1" class="min-h-11 px-3 text-xs font-black text-[#04724D] dark:text-[#3EFF8B] disabled:opacity-20">السابق</button>
            <span class="flex items-center text-xs font-black text-gray-400">{{ meta.page }} / {{ meta.pages }}</span>
            <button @click="changePage(meta.page + 1)" :disabled="meta.page >= meta.pages" class="min-h-11 px-3 text-xs font-black text-[#04724D] dark:text-[#3EFF8B] disabled:opacity-20">التالي</button>
          </div>
        </div>
      </div>
    </div>

    <div v-if="showDetails" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
      <div class="bg-white dark:bg-[#121212] rounded-[32px] w-full max-w-lg p-8 space-y-5 shadow-2xl max-h-[90vh] overflow-y-auto">
        <h2 class="text-xl font-black text-gray-900 dark:text-white">تفاصيل الطلب {{ selectedOrder?.code }}</h2>
        <div v-if="selectedOrder" class="space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div class="bg-gray-50 dark:bg-white/5 rounded-2xl p-4"><p class="text-xs text-gray-400 font-black">العميل</p><p class="font-black text-gray-900 dark:text-white">{{ selectedOrder.customer_name || selectedOrder.client_name || '—' }}</p></div>
            <div class="bg-gray-50 dark:bg-white/5 rounded-2xl p-4"><p class="text-xs text-gray-400 font-black">المبلغ</p><p class="font-black text-[#04724D] dark:text-[#3EFF8B]">{{ Number(selectedOrder.total||selectedOrder.amount||0).toLocaleString('ar-EG') }}</p></div>
            <div class="bg-gray-50 dark:bg-white/5 rounded-2xl p-4"><p class="text-xs text-gray-400 font-black">الحالة</p><p class="font-black">{{ statusLabel(selectedOrder.status) }}</p></div>
            <div class="bg-gray-50 dark:bg-white/5 rounded-2xl p-4"><p class="text-xs text-gray-400 font-black">التاريخ</p><p class="font-black text-gray-700 dark:text-gray-300">{{ selectedOrder.date }} {{ selectedOrder.time }}</p></div>
          </div>
          <div v-if="selectedOrder.sale_ref" class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-black text-emerald-700 dark:border-emerald-900/50 dark:bg-emerald-950/30 dark:text-emerald-300">
            تم إنشاء فاتورة البيع {{ selectedOrder.sale_ref }}
          </div>
          <div class="bg-gray-50 dark:bg-white/5 rounded-2xl p-4">
            <p class="text-xs text-gray-400 font-black mb-2">العناصر</p>
            <div v-if="selectedOrder.items && selectedOrder.items.length" class="space-y-2">
              <div v-for="item in selectedOrder.items" :key="item.id" class="flex justify-between text-sm">
                <span class="text-gray-700 dark:text-gray-300">{{ item.name }} × {{ item.qty }}</span>
                <span class="font-black text-gray-900 dark:text-white">{{ Number(item.line_total).toLocaleString('ar-EG') }}</span>
              </div>
            </div>
            <p v-else class="text-xs text-gray-400">لا توجد عناصر</p>
          </div>
        </div>
        <div class="flex justify-end">
          <button @click="showDetails=false" class="px-6 py-3 rounded-2xl bg-gray-100 dark:bg-white/10 text-sm font-black text-gray-700 dark:text-gray-300">إغلاق</button>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
