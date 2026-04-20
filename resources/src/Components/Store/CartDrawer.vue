<script setup>
import { computed } from 'vue';
import { usePage, Link } from '@inertiajs/vue3';
import { useCart } from '@/Composables/useCart';
import { 
  ShoppingBag, 
  X, 
  Trash2, 
  Plus, 
  Minus, 
  ArrowLeft, 
  ShieldCheck,
  CreditCard,
  Gift
} from 'lucide-vue-next';

const props = defineProps({ isOpen: Boolean });
const emit = defineEmits(['close']);

const { cart, removeFromCart, updateQuantity } = useCart();
const page = usePage();
const s = computed(() => page.props.settings || page.props.s || {});

const formatPrice = (price) => {
  return Number(price).toLocaleString('ar-EG', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });
};

const updateQty = (item, delta) => {
  const newQty = item.quantity + delta;
  if (newQty > 0) {
    updateQuantity(item.id, newQty);
  } else {
    removeFromCart(item.id);
  }
};
</script>

<template>
  <div v-show="isOpen" class="relative z-50 font-['Cairo']" dir="rtl">
    <!-- Overlay -->
    <transition
      enter-active-class="transition-opacity ease-out duration-500"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition-opacity ease-in duration-400"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div v-if="isOpen" class="fixed inset-0 bg-gray-900/60 dark:bg-black/80 backdrop-blur-md" @click="$emit('close')"></div>
    </transition>

    <!-- Slide-over panel (RTL: slides from left) -->
    <transition
      enter-active-class="transform transition ease-in-out duration-700"
      enter-from-class="-translate-x-full"
      enter-to-class="translate-x-0"
      leave-active-class="transform transition ease-in-out duration-500"
      leave-from-class="translate-x-0"
      leave-to-class="-translate-x-full"
    >
      <!-- Widened significantly to 650px for spacious B2B premium design -->
      <div v-if="isOpen" class="fixed inset-y-0 left-0 max-w-full flex w-full md:w-[650px]">
        <div class="h-full w-full bg-white dark:bg-night-900 shadow-3xl flex flex-col pt-10 pb-12 transition-colors duration-500 relative overflow-hidden">
          
          <!-- Decorative Background Elements -->
          <div class="absolute top-0 right-0 w-64 h-64 bg-brand/5 dark:bg-accent/5 rounded-full blur-[80px] -translate-y-1/2 translate-x-1/2 pointer-events-none"></div>

          <!-- Header -->
          <div class="px-10 flex items-center justify-between mb-10 pb-6 border-b border-gray-50 dark:border-night-800">
            <div class="flex items-center gap-5">
              <div class="w-16 h-16 rounded-[1.5rem] bg-gray-900 dark:bg-brand text-white dark:text-night-900 flex items-center justify-center shadow-2xl shadow-brand/20">
                <ShoppingBag class="w-8 h-8" />
              </div>
              <div>
                 <h2 class="text-3xl font-black text-gray-900 dark:text-white tracking-tighter italic">سلة المشتريات</h2>
                 <p class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mt-1">لديك {{ cart.items.length }} منتجات مختارة</p>
              </div>
            </div>
            <button @click="$emit('close')" class="w-12 h-12 flex items-center justify-center text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 rounded-2xl transition-all group">
              <X class="w-6 h-6 group-hover:rotate-90 transition-transform" />
            </button>
          </div>

          <!-- Items List with SmartScroll -->
          <div class="flex-1 px-10 overflow-y-auto custom-scrollbar overflow-x-hidden">
            <!-- Empty State -->
            <div v-if="cart.items.length === 0" class="flex flex-col items-center justify-center h-full text-center py-20 px-8">
              <div class="w-32 h-32 bg-gray-50 dark:bg-night-800 rounded-[3rem] flex items-center justify-center mb-10 shadow-inner border border-gray-100 dark:border-night-700">
                <ShoppingBag class="w-14 h-14 text-gray-200 dark:text-gray-700" />
              </div>
              <h3 class="text-2xl font-black text-gray-900 dark:text-white mb-4 italic">السلة فارغة حالياً</h3>
              <p class="text-gray-500 dark:text-gray-400 font-medium mb-12 max-w-xs mx-auto leading-relaxed">اكتشف أحدث منتجاتنا وعروض الجملة وابدأ في بناء طلبك الأول معنا اليوم.</p>
              <button @click="$emit('close')"
                class="px-12 py-5 bg-gray-900 dark:bg-brand text-white dark:text-night-950 font-black rounded-[2rem] transition-all shadow-2xl shadow-brand/20 hover:scale-105 active:scale-95 text-sm uppercase tracking-widest">
                استعراض المنتجات
              </button>
            </div>

            <!-- Cart Items Grid (Spacious) -->
            <div v-else class="space-y-8 pb-10">
              <div v-for="item in cart.items" :key="item.id" class="group relative bg-gray-50/50 dark:bg-night-800/20 p-6 rounded-[2.5rem] border border-transparent hover:border-brand/10 dark:hover:border-accent/10 hover:bg-white dark:hover:bg-night-800 transition-all duration-500 flex flex-col sm:flex-row gap-8">
                
                <!-- Product Image -->
                <div class="h-32 w-32 sm:h-40 sm:w-40 flex-shrink-0 overflow-hidden rounded-[2rem] bg-white dark:bg-night-900 p-3 shadow-lg border border-gray-100 dark:border-night-800">
                  <img :src="item.image ? `/images/products/${item.image}` : '/images/products/no-image.png'"
                    class="h-full w-full object-contain group-hover:scale-110 transition-transform duration-1000" />
                </div>

                <div class="flex-1 flex flex-col py-2">
                  <div class="flex justify-between items-start gap-4 mb-4">
                    <div class="flex-1 min-w-0">
                      <h3 class="text-xl font-black text-gray-900 dark:text-white leading-tight group-hover:text-brand dark:group-hover:text-accent transition-colors">
                        <Link :href="`/product/${item.product_id || item.id}`" @click="$emit('close')">{{ item.name }}</Link>
                      </h3>
                      <div class="flex items-center gap-3 mt-2">
                         <span class="text-[10px] font-black text-brand dark:text-accent bg-brand/5 dark:bg-accent/5 px-3 py-1 rounded-full border border-brand/10 dark:border-accent/10 uppercase tracking-widest">{{ item.variant_name || 'النموذج الأساسي' }}</span>
                         <span v-if="item.sku" class="text-[10px] font-bold text-gray-400">SKU: {{ item.sku }}</span>
                      </div>
                    </div>
                    
                    <button @click="removeFromCart(item.id)" class="text-gray-300 dark:text-gray-600 hover:text-red-500 transition-colors p-2 rounded-xl">
                       <Trash2 class="w-5 h-5" />
                    </button>
                  </div>
                  
                  <div class="mt-auto flex items-end justify-between gap-6">
                    <!-- Qty Control -->
                    <div class="flex items-center gap-4 bg-white dark:bg-night-900 p-1.5 rounded-2xl border border-gray-100 dark:border-night-800 shadow-sm">
                      <button @click="updateQty(item, -1)" class="w-10 h-10 flex items-center justify-center text-gray-400 dark:text-gray-500 hover:text-brand dark:hover:text-accent hover:bg-gray-50 dark:hover:bg-night-800 rounded-xl transition-all">
                        <Minus class="w-4 h-4" />
                      </button>
                      <span class="font-black text-gray-900 dark:text-white min-w-[2.5rem] text-center text-lg tabular-nums">{{ item.quantity }}</span>
                      <button @click="updateQty(item, 1)" class="w-10 h-10 flex items-center justify-center text-gray-400 dark:text-gray-500 hover:text-brand dark:hover:text-accent hover:bg-gray-50 dark:hover:bg-night-800 rounded-xl transition-all">
                        <Plus class="w-4 h-4" />
                      </button>
                    </div>

                    <div class="text-left">
                       <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1 ml-1 opacity-60">إجمالي المنتج</p>
                       <p class="text-2xl font-black text-gray-900 dark:text-white tabular-nums tracking-tighter italic">
                          {{ formatPrice((item.display_price ?? item.price) * item.quantity) }}
                          <span class="text-xs opacity-40 ml-1 font-bold">{{ s?.currency_code || 'ج.م' }}</span>
                       </p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Footer Summary -->
          <div v-if="cart.items.length > 0" class="px-10 pt-10 border-t border-gray-100 dark:border-night-800 bg-gray-50/30 dark:bg-night-900/50">
            
            <div class="space-y-4 mb-10">
               <div class="flex justify-between items-center text-sm">
                  <p class="font-bold text-gray-500 dark:text-gray-400 flex items-center gap-2 italic"><CreditCard class="w-4 h-4 opacity-50" /> المجموع الفرعي</p>
                  <p class="font-black text-gray-900 dark:text-white tabular-nums">{{ formatPrice(cart.total) }} <span class="text-[10px] opacity-40 mr-1">{{ s?.currency_code }}</span></p>
               </div>
               <div class="flex justify-between items-center text-sm">
                  <p class="font-bold text-gray-500 dark:text-gray-400 flex items-center gap-2 italic"><Gift class="w-4 h-4 opacity-50" /> الشحن والرسوم</p>
                  <p class="font-black text-brand dark:text-accent uppercase text-[10px] tracking-widest">يتم الحساب عند الدفع</p>
               </div>
               <div class="pt-6 mt-6 border-t border-gray-100 dark:border-night-800 flex justify-between items-end">
                  <div>
                     <h4 class="text-3xl md:text-5xl font-black text-gray-900 dark:text-white tracking-tighter italic tabular-nums leading-none mb-1">
                        {{ formatPrice(cart.total) }}
                         <span class="text-sm opacity-40 font-bold ml-1 mr-2">{{ s?.currency_code || 'ج.م' }}</span>
                     </h4>
                     <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest flex items-center gap-2">
                        <ShieldCheck class="w-3.5 h-3.5" /> شامل ضريبة القيمة المضافة
                     </p>
                  </div>
                  <div class="text-left hidden sm:block">
                     <p class="text-[10px] text-gray-400 font-bold italic">عملية دفع آمنة 100%</p>
                  </div>
               </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-4">
               <button @click="$emit('close')" type="button" class="flex-1 h-20 rounded-[2rem] border-2 border-gray-100 dark:border-night-800 text-gray-400 dark:text-gray-500 font-black text-sm uppercase tracking-widest hover:border-brand dark:hover:border-accent hover:text-brand dark:hover:text-accent transition-all">واصل التسوق</button>
               
               <Link href="/checkout" @click="$emit('close')" class="flex-[1.5] h-20 rounded-[2rem] bg-gray-900 dark:bg-brand text-white dark:text-night-950 flex items-center justify-center gap-4 group shadow-2xl shadow-brand/20 hover:scale-[1.02] active:scale-[0.98] transition-all overflow-hidden relative">
                  <div class="absolute inset-0 bg-white/10 -translate-x-full group-hover:translate-x-full transition-transform duration-1000"></div>
                  <span class="text-xl font-black italic">إتمام عملية الشراء</span>
                  <ArrowLeft class="w-6 h-6 group-hover:-translate-x-2 transition-transform" />
               </Link>
            </div>
            
            <p class="text-center mt-8 text-[10px] font-bold text-gray-400 dark:text-gray-600 uppercase tracking-[0.2em] italic">ضمان الجودة والتوريد الأصلي من DawPOS B2B</p>
          </div>

        </div>
      </div>
    </transition>
  </div>
</template>

<style scoped>
/* SmartScroll for the items list */
.custom-scrollbar {
  scrollbar-width: thin;
  scrollbar-color: rgba(4, 114, 77, 0.1) transparent;
}

.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}

.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(4, 114, 77, 0.15);
  border-radius: 10px;
  transition: all 0.3s;
}

.custom-scrollbar:hover::-webkit-scrollbar-thumb {
  background: rgba(4, 114, 77, 0.4);
}

:global(.dark) .custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(62, 255, 139, 0.1);
}

:global(.dark) .custom-scrollbar:hover::-webkit-scrollbar-thumb {
  background: rgba(62, 255, 139, 0.3);
}
</style>
