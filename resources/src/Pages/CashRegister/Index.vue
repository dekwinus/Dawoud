<script setup>
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import axios from 'axios';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { DollarSign, Plus, Minus, Play, Square, FileText } from 'lucide-vue-next';

const currentRegister = ref(null);
const loading = ref(false);
const openingBalance = ref(0);
const closingBalance = ref(0);
const transactions = ref([]);
const cashInAmount = ref('');
const cashOutAmount = ref('');
const cashInReason = ref('');
const cashOutReason = ref('');
const showCashIn = ref(false);
const showCashOut = ref(false);

const fetchCurrentRegister = async () => {
  loading.value = true;
  try {
    const { data } = await axios.get(`/api/cash-registers/current/${window.auth?.user?.id || 1}`);
    currentRegister.value = data.register || data;
    transactions.value = data.transactions || [];
  } catch (e) { currentRegister.value = null; }
  finally { loading.value = false; }
};

const openRegister = async () => {
  if (!openingBalance.value) return alert('أدخل الرصيد الافتتاحي');
  try {
    await axios.post('/api/cash-registers/open', { opening_balance: openingBalance.value });
    openingBalance.value = 0;
    fetchCurrentRegister();
  } catch (e) { alert(e.response?.data?.message || 'فشل فتح الصندوق'); }
};

const closeRegister = async () => {
  if (!confirm('إغلاق الصندوق النقدي؟')) return;
  try {
    await axios.post('/api/cash-registers/close', { closing_balance: closingBalance.value });
    currentRegister.value = null;
    transactions.value = [];
    closingBalance.value = 0;
  } catch (e) { alert(e.response?.data?.message || 'فشل إغلاق الصندوق'); }
};

const cashInOut = async (type) => {
  const amount = type === 'in' ? cashInAmount.value : cashOutAmount.value;
  const reason = type === 'in' ? cashInReason.value : cashOutReason.value;
  if (!amount) return alert('أدخل المبلغ');
  try {
    await axios.post('/api/cash-registers/cash-move', { amount, reason, type });
    showCashIn.value = false;
    showCashOut.value = false;
    cashInAmount.value = '';
    cashOutAmount.value = '';
    cashInReason.value = '';
    cashOutReason.value = '';
    fetchCurrentRegister();
  } catch (e) { alert(e.response?.data?.message || 'فشل العملية'); }
};

onMounted(fetchCurrentRegister);
</script>

<template>
  <AdminLayout>
    <Head title="الصندوق النقدي" />
    <div class="space-y-8 p-6 md:p-10 font-['Cairo']" dir="rtl">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
          <div class="flex items-center gap-2 text-[#04724D] text-xs font-black uppercase tracking-widest mb-1"><DollarSign class="w-4 h-4" />المالية</div>
          <h1 class="text-4xl font-black text-gray-900 dark:text-white">الصندوق النقدي</h1>
        </div>
      </div>

      <!-- No Register Open -->
      <div v-if="!currentRegister" class="bg-white dark:bg-[#0A0A0A] rounded-[40px] border border-gray-100 dark:border-gray-800 p-12 text-center">
        <div class="w-20 h-20 mx-auto mb-6 rounded-full bg-[#04724D]/10 flex items-center justify-center"><DollarSign class="w-10 h-10 text-[#04724D]" /></div>
        <h2 class="text-2xl font-black text-gray-900 dark:text-white mb-4">الصندوق مغلق</h2>
        <p class="text-gray-400 font-black text-sm mb-6">أدخل الرصيد الافتتاحي لفتح الصندوق</p>
        <div class="flex justify-center gap-4">
          <input v-model.number="openingBalance" type="number" placeholder="الرصيد الافتتاحي" class="w-64 rounded-2xl bg-gray-50 dark:bg-white/5 border-none py-3 px-4 text-sm font-black text-center" />
          <button @click="openRegister" class="bg-[#04724D] hover:bg-[#035a3d] text-[#3EFF8B] px-8 py-3 rounded-2xl font-black text-xs uppercase tracking-widest flex items-center gap-2 transition-all">
            <Play class="h-4 w-4" />فتح الصندوق
          </button>
        </div>
      </div>

      <!-- Register Open -->
      <div v-else class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div class="bg-white dark:bg-[#0A0A0A] rounded-[32px] border border-gray-100 dark:border-gray-800 p-8">
            <p class="text-xs font-black text-gray-400 mb-2">الرصيد الافتتاحي</p>
            <p class="text-3xl font-black text-gray-900 dark:text-white">{{ Number(currentRegister.opening_balance||0).toLocaleString('ar-EG') }}</p>
          </div>
          <div class="bg-white dark:bg-[#0A0A0A] rounded-[32px] border border-gray-100 dark:border-gray-800 p-8">
            <p class="text-xs font-black text-gray-400 mb-2">الرصيد الحالي</p>
            <p class="text-3xl font-black text-[#04724D] dark:text-[#3EFF8B]">{{ Number(currentRegister.current_balance||0).toLocaleString('ar-EG') }}</p>
          </div>
          <div class="bg-white dark:bg-[#0A0A0A] rounded-[32px] border border-gray-100 dark:border-gray-800 p-8">
            <p class="text-xs font-black text-gray-400 mb-2">المعاملات</p>
            <p class="text-3xl font-black text-gray-900 dark:text-white">{{ transactions.length }}</p>
          </div>
        </div>

        <div class="flex gap-4">
          <button @click="showCashIn=true" class="flex-1 bg-green-50 dark:bg-green-900/20 border border-green-100 dark:border-green-800 text-green-700 dark:text-green-400 px-6 py-4 rounded-2xl font-black text-sm flex items-center justify-center gap-2 hover:bg-green-100 dark:hover:bg-green-900/30 transition-all">
            <Plus class="h-5 w-5" />إيداع نقدي
          </button>
          <button @click="showCashOut=true" class="flex-1 bg-red-50 dark:bg-red-900/20 border border-red-100 dark:border-red-800 text-red-700 dark:text-red-400 px-6 py-4 rounded-2xl font-black text-sm flex items-center justify-center gap-2 hover:bg-red-100 dark:hover:bg-red-900/30 transition-all">
            <Minus class="h-5 w-5" />سحب نقدي
          </button>
          <button @click="closeRegister" class="flex-1 bg-gray-50 dark:bg-white/5 border border-gray-100 dark:border-gray-800 text-gray-700 dark:text-gray-400 px-6 py-4 rounded-2xl font-black text-sm flex items-center justify-center gap-2 hover:bg-gray-100 dark:hover:bg-white/10 transition-all">
            <Square class="h-5 w-5" />إغلاق الصندوق
          </button>
        </div>

        <div class="bg-white dark:bg-[#0A0A0A] rounded-[40px] border border-gray-100 dark:border-gray-800 overflow-hidden">
          <div class="p-6 border-b border-gray-100 dark:border-gray-800">
            <h3 class="text-sm font-black text-gray-900 dark:text-white">سجل المعاملات</h3>
          </div>
          <div v-if="transactions.length===0" class="py-20 text-center text-gray-400 font-black text-sm">لا توجد معاملات</div>
          <div v-else class="divide-y divide-gray-50 dark:divide-gray-800">
            <div v-for="t in transactions" :key="t.id" class="flex items-center justify-between px-8 py-4">
              <div class="flex items-center gap-4">
                <div :class="t.type === 'in' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600'" class="w-10 h-10 rounded-full flex items-center justify-center">
                  <span>{{ t.type === 'in' ? '+' : '-' }}</span>
                </div>
                <div>
                  <p class="font-black text-gray-900 dark:text-white text-sm">{{ t.reason }}</p>
                  <p class="text-xs text-gray-400">{{ t.created_at }}</p>
                </div>
              </div>
              <p :class="t.type==='in'?'text-green-600':'text-red-500'" class="font-mono font-black text-sm">{{ Number(t.amount).toLocaleString('ar-EG') }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Cash In Modal -->
    <div v-if="showCashIn" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
      <div class="bg-white dark:bg-[#121212] rounded-[32px] w-full max-w-sm p-8 space-y-5 shadow-2xl">
        <h2 class="text-xl font-black text-gray-900 dark:text-white">إيداع نقدي</h2>
        <div class="space-y-4">
          <div class="space-y-1"><label class="text-xs text-gray-400 font-black">المبلغ *</label><input v-model.number="cashInAmount" type="number" min="0" class="w-full rounded-2xl bg-gray-50 dark:bg-white/5 border-none py-3 px-4 text-sm font-black dark:text-white" /></div>
          <div class="space-y-1"><label class="text-xs text-gray-400 font-black">السبب</label><input v-model="cashInReason" class="w-full rounded-2xl bg-gray-50 dark:bg-white/5 border-none py-3 px-4 text-sm font-black dark:text-white" /></div>
        </div>
        <div class="flex justify-end gap-3">
          <button @click="showCashIn=false" class="px-6 py-3 rounded-2xl bg-gray-100 dark:bg-white/10 text-sm font-black text-gray-700 dark:text-gray-300">إلغاء</button>
          <button @click="cashInOut('in')" class="px-6 py-3 rounded-2xl bg-green-600 text-white text-sm font-black">تأكيد</button>
        </div>
      </div>
    </div>

    <!-- Cash Out Modal -->
    <div v-if="showCashOut" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
      <div class="bg-white dark:bg-[#121212] rounded-[32px] w-full max-w-sm p-8 space-y-5 shadow-2xl">
        <h2 class="text-xl font-black text-gray-900 dark:text-white">سحب نقدي</h2>
        <div class="space-y-4">
          <div class="space-y-1"><label class="text-xs text-gray-400 font-black">المبلغ *</label><input v-model.number="cashOutAmount" type="number" min="0" class="w-full rounded-2xl bg-gray-50 dark:bg-white/5 border-none py-3 px-4 text-sm font-black dark:text-white" /></div>
          <div class="space-y-1"><label class="text-xs text-gray-400 font-black">السبب</label><input v-model="cashOutReason" class="w-full rounded-2xl bg-gray-50 dark:bg-white/5 border-none py-3 px-4 text-sm font-black dark:text-white" /></div>
        </div>
        <div class="flex justify-end gap-3">
          <button @click="showCashOut=false" class="px-6 py-3 rounded-2xl bg-gray-100 dark:bg-white/10 text-sm font-black text-gray-700 dark:text-gray-300">إلغاء</button>
          <button @click="cashInOut('out')" class="px-6 py-3 rounded-2xl bg-red-600 text-white text-sm font-black">تأكيد</button>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
