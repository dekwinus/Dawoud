<script setup>
import { ref, computed } from 'vue';
import { 
  X, 
  CreditCard, 
  Banknote, 
  Wallet, 
  ArrowRight, 
  CheckCircle2, 
  AlertCircle 
} from 'lucide-vue-next';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
  show: Boolean,
  total: Number,
  paymentMethods: Array,
  accounts: Array,
  client: Object,
  cart: Array,
  warehouseId: Number
});

const emit = defineEmits(['close', 'success']);

const selectedMethod = ref(props.paymentMethods[0]?.id || null);
const selectedAccount = ref(props.accounts[0]?.id || null);
const amountPaid = ref(props.total);
const processing = ref(false);

const changeDue = computed(() => {
  return Math.max(0, amountPaid.value - props.total);
});

const submitPayment = async () => {
    processing.value = true;
    
    // Assemble the POS Sale Data
    const saleData = {
        client_id: props.client?.id,
        warehouse_id: props.warehouseId,
        date: new Date().toISOString().slice(0, 10),
        tax_rate: 0,
        TaxNet: 0,
        discount: 0,
        shipping: 0,
        GrandTotal: props.total,
        notes: "",
        payment: {
            amount: amountPaid.value,
            payment_method_id: selectedMethod.value,
            account_id: selectedAccount.value
        },
        details: props.cart.map(item => ({
            product_id: item.id,
            quantity: item.quantity,
            price: item.price,
            Net_price: item.price,
            total: item.price * item.quantity,
            unit_id: item.unit_id,
            product_variant_id: item.variant_id
        }))
    };

    try {
        // In a real scenario, this would be an Inertia post or Axios call
        // For the demo, we simulate success
        await new Promise(resolve => setTimeout(resolve, 1500));
        emit('success', { change: changeDue.value });
    } catch (e) {
        console.error("Payment failed", e);
    } finally {
        processing.value = false;
    }
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(value);
};
</script>

<template>
  <div v-if="show" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm transition-opacity" @click="emit('close')"></div>
    
    <!-- Modal Content -->
    <div class="relative bg-white dark:bg-[#141414] w-full max-w-2xl rounded-[32px] shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
      <!-- Header -->
      <div class="px-8 py-6 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
        <div>
          <h2 class="text-xl font-black text-gray-800 dark:text-white uppercase tracking-tight">Complete Transaction</h2>
          <p class="text-xs text-gray-400 font-medium">Select payment method and confirm amount</p>
        </div>
        <button @click="emit('close')" class="p-2 hover:bg-gray-100 dark:hover:bg-white/5 rounded-full transition-colors">
          <X class="h-5 w-5 text-gray-400" />
        </button>
      </div>

      <div class="flex-1 overflow-y-auto p-8 custom-scrollbar">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
          <!-- Left: Summary -->
          <div class="space-y-6">
            <div class="bg-gray-50 dark:bg-white/5 p-6 rounded-3xl border border-gray-100 dark:border-gray-800">
               <p class="text-[10px] text-gray-400 font-bold uppercase tracking-[0.2em] mb-4">Final Amount Due</p>
               <div class="text-4xl font-black text-[#04724D] dark:text-[#3EFF8B] tracking-tighter">
                  {{ formatCurrency(total) }}
               </div>
            </div>

            <div class="space-y-4">
              <label class="text-[10px] text-gray-400 font-bold uppercase tracking-[0.2em]">Select Payment Method</label>
              <div class="grid grid-cols-2 gap-3">
                <button 
                  v-for="method in paymentMethods" 
                  :key="method.id"
                  @click="selectedMethod = method.id"
                  class="flex flex-col items-center gap-3 p-4 rounded-2xl border-2 transition-all duration-300"
                  :class="selectedMethod === method.id 
                    ? 'border-[#04724D] bg-[#04724D]/5 text-[#04724D] dark:text-[#3EFF8B]' 
                    : 'border-gray-100 dark:border-gray-800 hover:border-gray-200 dark:hover:border-gray-700'"
                >
                  <div class="w-10 h-10 rounded-xl bg-gray-50 dark:bg-white/5 flex items-center justify-center">
                    <Banknote v-if="method.name.toLowerCase().includes('cash')" class="h-5 w-5" />
                    <CreditCard v-else-if="method.name.toLowerCase().includes('card')" class="h-5 w-5" />
                    <Wallet v-else class="h-5 w-5" />
                  </div>
                  <span class="text-xs font-bold uppercase tracking-widest">{{ method.name }}</span>
                </button>
              </div>
            </div>
          </div>

          <!-- Right: Inputs -->
          <div class="space-y-6">
            <div class="space-y-4">
               <div>
                  <label class="text-[10px] text-gray-400 font-bold uppercase tracking-[0.2em] block mb-2">Amount Paid</label>
                  <div class="relative">
                    <input 
                      type="number" 
                      v-model="amountPaid"
                      class="w-full bg-gray-50 dark:bg-white/5 border-none rounded-2xl py-4 px-5 text-xl font-black focus:ring-2 focus:ring-[#04724D]/20"
                    />
                    <div class="absolute right-5 top-1/2 -translate-y-1/2 text-gray-400 font-bold">USD</div>
                  </div>
               </div>

               <div>
                  <label class="text-[10px] text-gray-400 font-bold uppercase tracking-[0.2em] block mb-2">Deposit Account</label>
                  <select 
                    v-model="selectedAccount"
                    class="w-full bg-gray-50 dark:bg-white/5 border-none rounded-2xl py-4 px-5 text-sm font-bold focus:ring-2 focus:ring-[#04724D]/20 cursor-pointer"
                  >
                    <option v-for="account in accounts" :key="account.id" :value="account.id">{{ account.account_name }}</option>
                  </select>
               </div>

               <!-- Change Indicator -->
               <div v-if="changeDue > 0" class="flex items-center justify-between p-4 bg-yellow-50 dark:bg-yellow-900/10 rounded-2xl border border-yellow-101 dark:border-yellow-900/20">
                  <span class="text-xs font-bold text-yellow-700 dark:text-yellow-400 uppercase tracking-wider">Change to Return</span>
                  <span class="text-lg font-black text-yellow-800 dark:text-yellow-200">{{ formatCurrency(changeDue) }}</span>
               </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Footer Actions -->
      <div class="p-8 bg-gray-50 dark:bg-[#1A1A1A] border-t border-gray-100 dark:border-gray-800 flex gap-4">
        <button 
          @click="emit('close')"
          class="flex-1 py-4 bg-white dark:bg-white/5 text-gray-600 dark:text-gray-300 rounded-2xl font-bold text-xs uppercase tracking-widest hover:bg-gray-100 transition-all border border-gray-200 dark:border-gray-800"
        >
          Cancel
        </button>
        <button 
          @click="submitPayment"
          :disabled="processing || amountPaid < total"
          class="flex-[2] py-4 bg-[#04724D] hover:bg-[#058a5e] disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-2xl font-black text-xs uppercase tracking-[0.2em] shadow-xl shadow-[#04724D]/25 transition-all flex items-center justify-center gap-2"
        >
          <template v-if="processing">
            <div class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></div>
            Processing...
          </template>
          <template v-else>
            Confirm & Pay
            <ArrowRight class="h-4 w-4" />
          </template>
        </button>
      </div>
    </div>
  </div>
</template>
