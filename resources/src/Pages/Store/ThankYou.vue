<script setup>
import { Link } from '@inertiajs/vue3';
import { onMounted, ref, computed } from 'vue';
import StoreLayout from '@/Layouts/StoreLayout.vue';
import { 
  CheckCircle2, 
  Package, 
  ArrowLeft, 
  ShoppingBag,
  ExternalLink,
  Calendar,
  CreditCard
} from 'lucide-vue-next';

const props = defineProps({ s: Object });

const lastOrder = ref(null);

onMounted(() => {
  try {
    const raw = localStorage.getItem('shop.last_order');
    if (raw) lastOrder.value = JSON.parse(raw);
  } catch (e) {
    console.error(e);
  }
});

const formatDate = (dateStr) => {
  return new Date(dateStr).toLocaleDateString('ar-EG', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
};
</script>

<template>
  <StoreLayout>
    <div class="bg-gray-50 dark:bg-night-950 min-h-screen transition-colors duration-500 py-12 md:py-24 flex items-center">
      <div class="container mx-auto px-4 max-w-4xl">
        
        <!-- Main Success Card -->
        <div class="bg-white dark:bg-night-900 rounded-[3.5rem] p-8 md:p-16 shadow-2xl shadow-gray-200/50 dark:shadow-black/40 border border-gray-100 dark:border-night-800 relative overflow-hidden text-center">
          
          <!-- Decoration -->
          <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-brand to-accent"></div>
          <div class="absolute -top-24 -right-24 w-64 h-64 bg-brand/5 dark:bg-accent/5 rounded-full blur-3xl"></div>

          <!-- Icon Header -->
          <div class="mb-10 relative inline-block">
             <div class="w-28 h-28 bg-brand/10 dark:bg-accent/10 border-4 border-white dark:border-night-900 text-brand dark:text-accent rounded-[2.5rem] flex items-center justify-center shadow-2xl shadow-brand/20 relative z-10 animate-in zoom-in duration-700">
               <CheckCircle2 class="w-14 h-14" />
             </div>
             <!-- Animated Rings -->
             <div class="absolute inset-0 bg-brand/20 dark:bg-accent/20 rounded-[2.5rem] animate-ping opacity-20"></div>
          </div>

          <h1 class="text-3xl md:text-4xl font-black text-gray-900 dark:text-white tracking-tighter mb-6">شكراً لثقتك بنا!</h1>
          <p class="text-xl text-gray-500 dark:text-gray-400 font-medium mb-12 max-w-xl mx-auto leading-relaxed">
            تم استلام طلبك بنجاح. سيقوم فريق العمل بمراجعة الطلب وتجهيزه للشحن فوراً.
          </p>

          <!-- Order Summary Dashboard -->
          <div v-if="lastOrder" class="grid md:grid-cols-12 gap-8 mb-12 text-right">
             
             <!-- Left: Detail List -->
             <div class="md:col-span-12 lg:col-span-7 space-y-4">
                <div class="bg-gray-50 dark:bg-night-800 rounded-3xl p-8 border border-gray-100 dark:border-night-700">
                   <div class="flex items-center justify-between mb-8">
                      <h3 class="text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-[0.2em]">تفاصيل المنتجات</h3>
                      <span class="text-xs font-black text-brand dark:text-accent bg-brand/10 dark:bg-accent/10 px-3 py-1 rounded-full">{{ lastOrder.items.length }} منتجات</span>
                   </div>
                   
                   <div class="space-y-4 max-h-[300px] overflow-y-auto custom-scrollbar px-2">
                      <div v-for="item in lastOrder.items" :key="item.id" class="flex justify-between items-center group">
                         <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-white dark:bg-night-900 rounded-xl flex items-center justify-center border border-gray-100 dark:border-night-700 p-2">
                               <img v-if="item.image" :src="`/images/products/${item.image}`" class="w-full h-full object-contain" />
                               <Package v-else class="w-6 h-6 text-gray-300" />
                            </div>
                            <div>
                               <p class="font-black text-gray-900 dark:text-white text-sm line-clamp-1">{{ item.name }}</p>
                               <p class="text-[10px] font-bold text-gray-400">الكمية: {{ item.qty }}</p>
                            </div>
                         </div>
                         <span class="font-black text-gray-900 dark:text-white tabular-nums">{{ (item.price * item.qty).toFixed(2) }}</span>
                      </div>
                   </div>

                   <div class="mt-8 pt-6 border-t border-gray-200 dark:border-night-700 flex justify-between items-center">
                      <span class="font-black text-gray-400 uppercase tracking-widest text-xs">إجمالي الطلب</span>
                      <span class="text-3xl font-black text-brand dark:text-accent tracking-tighter tabular-nums">
                        {{ lastOrder.total.toFixed(2) }} 
                        <span class="text-sm opacity-50">{{ s?.currency_code || 'ج.م' }}</span>
                      </span>
                   </div>
                </div>
             </div>

             <!-- Right: Status/Meta -->
             <div class="md:col-span-12 lg:col-span-5 space-y-4">
                <div class="bg-gray-900 dark:bg-brand text-white dark:text-night-950 rounded-3xl p-8 shadow-xl">
                   <div class="flex items-center gap-3 mb-6 opacity-80">
                      <Calendar class="w-5 h-5" />
                      <span class="text-xs font-black uppercase tracking-widest">تاريخ الطلب</span>
                   </div>
                   <p class="text-xl font-black mb-10">{{ formatDate(lastOrder.date) }}</p>
                   
                   <div class="flex items-center gap-3 mb-6 opacity-80">
                      <CreditCard class="w-5 h-5" />
                      <span class="text-xs font-black uppercase tracking-widest">طريقة الدفع</span>
                   </div>
                   <p class="text-xl font-black mb-2">الدفع عند الاستلام</p>
                   <p class="text-[10px] font-bold opacity-60">متاح لعملاء B2B المؤكدين</p>
                </div>

                <Link href="/account/orders" class="w-full bg-gray-50 dark:bg-night-800 hover:bg-gray-100 dark:hover:bg-night-700 py-6 rounded-3xl flex items-center justify-center gap-3 transition-all group">
                   <span class="font-black text-gray-900 dark:text-white">تتبع طلبك الآن</span>
                   <ExternalLink class="w-5 h-5 text-gray-400 group-hover:text-brand dark:group-hover:text-accent transition-colors" />
                </Link>
             </div>
          </div>

          <!-- CTAs -->
          <div class="flex flex-col sm:flex-row gap-6 justify-center items-center">
             <Link href="/shop" class="w-full sm:w-auto bg-gray-900 dark:bg-brand text-white dark:text-night-900 px-14 py-5 rounded-2xl font-black text-lg shadow-2xl hover:scale-105 active:scale-95 transition-all flex items-center justify-center gap-3">
               <ShoppingBag class="w-6 h-6" />
               مواصلة التسوق
             </Link>
             <Link href="/" class="w-full sm:w-auto bg-white dark:bg-night-800 text-gray-900 dark:text-white border-2 border-gray-100 dark:border-night-700 px-14 py-5 rounded-2xl font-black text-lg hover:border-brand dark:hover:border-accent transition-all flex items-center justify-center gap-3">
               <ArrowLeft class="w-6 h-6" />
               العودة للرئيسية
             </Link>
          </div>

          <!-- Footer Note -->
          <p class="mt-16 text-xs text-gray-400 dark:text-gray-600 font-bold flex items-center justify-center gap-3 uppercase tracking-widest">
            <Mail class="w-4 h-4" />
            تم إرسال نسخة من الفاتورة إلى بريدك الإلكتروني المسجل
          </p>

        </div>
      </div>
    </div>
  </StoreLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.1); border-radius: 10px; }
:global(.dark) .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); }
</style>
