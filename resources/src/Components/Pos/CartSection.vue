<script setup>
import { ShoppingBag, User as UserIcon, Trash2, Plus, Minus, CreditCard, Save, RotateCcw } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps({
  cart: Array,
  clients: Array,
  selectedClient: Number,
  subtotal: Number,
  currency: String,
});

const emit = defineEmits(['update:selectedClient', 'update-quantity', 'remove', 'checkout', 'save-draft', 'reset']);

const tax = 0; // Simplified for now
const discount = 0;
const shipping = 0;

const total = computed(() => {
  return props.subtotal + tax - discount + shipping;
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(value);
};
</script>

<template>
  <div class="flex flex-col h-full bg-white dark:bg-[#121212]">
    <!-- Client Selection -->
    <div class="p-4 border-b border-gray-100 dark:border-gray-800">
      <div class="flex items-center gap-3 bg-gray-50 dark:bg-white/5 p-3 rounded-2xl border border-transparent focus-within:border-[#04724D]/30 transition-all">
        <div class="w-8 h-8 rounded-full bg-white dark:bg-[#1A1A1A] flex items-center justify-center text-gray-400">
          <UserIcon class="h-4 w-4" />
        </div>
        <div class="flex-1">
          <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mb-0.5">Select Client</p>
          <select
            :value="selectedClient"
            @change="emit('update:selectedClient', parseInt($event.target.value))"
            class="bg-transparent border-none p-0 text-sm font-bold focus:ring-0 w-full cursor-pointer text-gray-700 dark:text-gray-200"
          >
            <option v-for="client in clients" :key="client.id" :value="client.id">{{ client.name }}</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Cart Items -->
    <div class="flex-1 overflow-y-auto p-4 space-y-3 pos-scrollbar">
      <div v-if="cart.length === 0" class="h-full flex flex-col items-center justify-center text-gray-400 opacity-50 space-y-4">
          <div class="w-20 h-20 rounded-full border-2 border-dashed border-gray-200 dark:border-gray-800 flex items-center justify-center">
            <ShoppingBag class="h-8 w-8" />
          </div>
          <p class="text-sm font-bold uppercase tracking-widest">Cart is empty</p>
      </div>

      <div
        v-for="(item, index) in cart"
        :key="index"
        class="group bg-gray-50 dark:bg-white/5 rounded-2xl p-3 border border-transparent hover:border-gray-200 dark:hover:border-gray-700 transition-all"
      >
        <div class="flex gap-3">
          <div class="w-12 h-12 rounded-xl bg-white dark:bg-[#1A1A1A] flex items-center justify-center overflow-hidden border border-gray-100 dark:border-gray-800">
             <img v-if="item.image" :src="'/images/products/' + item.image" class="w-full h-full object-cover" />
             <ShoppingBag v-else class="h-5 w-5 text-gray-300" />
          </div>
          <div class="flex-1 min-w-0">
            <div class="flex justify-between items-start">
               <h4 class="text-xs font-bold text-gray-800 dark:text-gray-200 truncate pr-2">{{ item.name }}</h4>
               <button @click="emit('remove', index)" class="text-gray-400 hover:text-red-500 transition-colors">
                  <Trash2 class="h-3.5 w-3.5" />
               </button>
            </div>
            <p class="text-[10px] text-gray-400 font-medium mb-2">{{ formatCurrency(item.price) }}</p>
            
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2 bg-white dark:bg-[#1A1A1A] rounded-lg p-1 border border-gray-100 dark:border-gray-800">
                <button @click="emit('update-quantity', index, -1)" class="w-6 h-6 rounded flex items-center justify-center hover:bg-gray-100 dark:hover:bg-white/5 text-gray-500 transition-colors">
                  <Minus class="h-3 w-3" />
                </button>
                <span class="text-xs font-bold w-6 text-center text-gray-700 dark:text-gray-200">{{ item.quantity }}</span>
                <button @click="emit('update-quantity', index, 1)" class="w-6 h-6 rounded flex items-center justify-center hover:bg-gray-100 dark:hover:bg-white/5 text-gray-500 transition-colors">
                  <Plus class="h-3 w-3" />
                </button>
              </div>
              <span class="text-sm font-black text-gray-800 dark:text-gray-100">{{ formatCurrency(item.price * item.quantity) }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Summary & Actions -->
    <div class="p-6 bg-gray-50 dark:bg-[#181818] rounded-t-[32px] shadow-[0_-12px_40px_-12px_rgba(0,0,0,0.1)] border-t border-gray-100 dark:border-transparent">
        <div class="space-y-3 mb-6">
            <div class="flex justify-between text-xs font-medium text-gray-500 dark:text-gray-400">
                <span>Subtotal</span>
                <span>{{ formatCurrency(subtotal) }}</span>
            </div>
            <div class="flex justify-between text-xs font-medium text-gray-500 dark:text-gray-400">
                <span>Tax (0%)</span>
                <span>$0.00</span>
            </div>
            <div class="h-px bg-gray-200 dark:bg-gray-800 my-2"></div>
            <div class="flex justify-between items-center">
                <span class="text-sm font-bold text-gray-800 dark:text-white uppercase tracking-wider">Total Amount</span>
                <span class="text-2xl font-black text-[#04724D] dark:text-[#3EFF8B]">{{ formatCurrency(total) }}</span>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-3 mb-4">
            <button @click="emit('save-draft')" class="flex items-center justify-center gap-2 py-3.5 px-4 bg-white dark:bg-white/5 text-gray-600 dark:text-gray-300 rounded-2xl font-bold text-xs uppercase tracking-widest hover:bg-gray-100 transition-all border border-gray-200 dark:border-gray-800">
                <Save class="h-4 w-4" />
                Draft
            </button>
            <button @click="emit('reset')" class="flex items-center justify-center gap-2 py-3.5 px-4 bg-white dark:bg-white/5 text-gray-600 dark:text-gray-300 rounded-2xl font-bold text-xs uppercase tracking-widest hover:bg-gray-100 transition-all border border-gray-200 dark:border-gray-800">
                <RotateCcw class="h-4 w-4" />
                Reset
            </button>
        </div>

        <button 
          @click="emit('checkout')"
          :disabled="cart.length === 0"
          class="w-full flex items-center justify-center gap-3 py-5 bg-[#04724D] hover:bg-[#058a5e] disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-[20px] font-black text-sm uppercase tracking-[0.2em] shadow-xl shadow-[#04724D]/25 transition-all active:scale-[0.98]"
        >
            <CreditCard class="h-5 w-5" />
            Process Payment
        </button>
    </div>
  </div>
</template>

<style scoped>
.pos-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.pos-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.pos-scrollbar::-webkit-scrollbar-thumb {
  background: #04724D22;
  border-radius: 10px;
}
</style>
