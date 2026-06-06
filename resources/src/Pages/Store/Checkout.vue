<script setup>
import { Link, router } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
import axios from 'axios';
import StoreLayout from '@/Layouts/StoreLayout.vue';
import { useCart } from '@/Composables/useCart';
import { 
  ShieldCheck, 
  Truck, 
  CreditCard, 
  MapPin, 
  User, 
  Phone, 
  Mail,
  ArrowRight,
  CheckCircle2,
  Trash2,
  ChevronLeft,
  X,
  Plus,
  Minus,
  ShoppingBag
} from 'lucide-vue-next';

const props = defineProps({
  s: Object,
  client: Object,
});

const { cart, loadCart, updateQty, removeItem, clearCart } = useCart();
const processing = ref(false);
const checkoutError = ref('');

onMounted(() => { loadCart(); });

const formatPrice = (price) => {
  return Number(price).toLocaleString('ar-EG', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });
};

const placeOrder = async () => {
  if (cart.value.items.length === 0) return;
  processing.value = true;
  checkoutError.value = '';

  const payload = {
    items: cart.value.items.map(i => ({
      product_id: i.product_id,
      product_variant_id: i.product_variant_id,
      qty: i.qty
    }))
  };

  try {
    const { data } = await axios.post('/store/orders', payload);
    localStorage.setItem('shop.last_order', JSON.stringify({
      ...data,
      items: cart.value.items,
      date: data.date || new Date().toISOString()
    }));
    clearCart();
    router.visit(`/thank-you?order=${data.id}`);
  } catch (error) {
    const response = error.response?.data;
    checkoutError.value = response?.error
      || response?.message
      || Object.values(response?.errors || {}).flat().join(' ')
      || 'تعذر إنشاء الطلب. راجع البيانات وحاول مرة أخرى.';
  } finally {
    processing.value = false;
  }
};
</script>

<template>
  <StoreLayout>
    <div class="bg-gray-50 dark:bg-night-950 min-h-screen py-16 md:py-24 transition-colors duration-500 font-['Cairo']" dir="rtl">
      <div class="container mx-auto px-4 max-w-7xl">

        <!-- Premium Breadcrumb -->
        <nav class="flex items-center text-[10px] font-black uppercase tracking-widest text-gray-400 mb-12 gap-3">
          <Link href="/" class="hover:text-brand transition-colors">الرئيسية</Link>
          <ChevronLeft class="w-3 h-3 opacity-30" />
          <Link href="/shop" class="hover:text-brand transition-colors">المتجر</Link>
          <ChevronLeft class="w-3 h-3 opacity-30" />
          <span class="text-gray-900 dark:text-white">إتمام عملية الشراء</span>
        </nav>

        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-12">
          <div>
            <h1 class="text-2xl md:text-3xl font-black text-gray-900 dark:text-white tracking-tighter leading-none">مراجعة <span class="text-brand dark:text-accent">وإتمام الطلب</span></h1>
            <p class="text-sm text-gray-400 dark:text-gray-500 font-medium mt-2">تأكد من بيانات التوصيل ومحتويات السلة قبل التأكيد.</p>
          </div>

          <div v-if="cart.items.length > 0" class="flex items-center gap-3 bg-white dark:bg-night-900 px-5 py-3.5 rounded-2xl border border-gray-100 dark:border-night-800 shadow-sm self-start md:self-auto">
             <div class="w-10 h-10 rounded-xl bg-brand/10 dark:bg-accent/10 flex items-center justify-center text-brand dark:text-accent">
                <CheckCircle2 class="w-5 h-5" />
             </div>
             <div>
                <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest leading-none mb-0.5">حالة الطلب</p>
                <p class="text-sm font-black text-gray-900 dark:text-white leading-none">جاهز للتأكيد</p>
             </div>
          </div>
        </div>

        <!-- Empty Cart State -->
        <div v-if="cart.items.length === 0" class="bg-white dark:bg-night-900 rounded-[4rem] p-24 md:p-40 text-center shadow-3xl shadow-gray-200/50 dark:shadow-none border border-gray-50 dark:border-night-800 transition-all">
          <div class="inline-flex items-center justify-center w-32 h-32 bg-gray-50 dark:bg-night-800 rounded-[2.5rem] mb-10 shadow-inner group">
             <ShoppingBag class="w-14 h-14 text-gray-200 dark:text-gray-700 group-hover:scale-110 transition-transform duration-700" />
          </div>
          <h2 class="text-4xl font-black text-gray-900 dark:text-white mb-4 italic">حقيبة التسوق فارغة</h2>
          <p class="text-gray-500 dark:text-gray-400 mb-12 max-w-md mx-auto font-medium text-lg leading-relaxed">يبدو أنك لم تقم بإضافة أي منتجات إلى سلتك بعد. ابدأ بالتسوق الآن لاختيار أفضل الحلول التقنية.</p>
          <Link href="/shop" class="bg-gray-900 dark:bg-brand text-white dark:text-night-950 px-16 py-6 rounded-[2rem] font-black shadow-3xl shadow-brand/20 hover:scale-105 active:scale-95 transition-all inline-flex items-center gap-4 text-xl overflow-hidden relative group">
            <div class="absolute inset-0 bg-white/10 -translate-x-full group-hover:translate-x-full transition-transform duration-1000"></div>
            العودة للمتجر
            <ArrowRight class="w-6 h-6" />
          </Link>
        </div>

        <div v-else class="grid lg:grid-cols-12 gap-12 items-start">

          <!-- Left Column: Checkout Journey -->
          <div class="lg:col-span-8 space-y-8 animate-in fade-in slide-in-from-right-10 duration-700">

            <!-- Step 1: Items Overview -->
            <div class="bg-white dark:bg-night-900 rounded-[3rem] shadow-xl shadow-gray-200/20 dark:shadow-none border border-gray-50 dark:border-night-800 p-10 md:p-14 overflow-hidden relative">
              <div class="absolute top-0 right-0 w-32 h-32 bg-brand/5 dark:bg-accent/5 rounded-full blur-[60px] -translate-y-1/2 translate-x-1/2"></div>
              
              <div class="flex items-center justify-between mb-8 pb-5 border-b border-gray-50 dark:border-night-800">
                <div class="flex items-center gap-4">
                  <div class="w-11 h-11 rounded-xl bg-gray-50 dark:bg-night-800 flex items-center justify-center text-brand dark:text-accent shadow-inner">
                    <ShoppingBag class="w-5 h-5" />
                  </div>
                  <div>
                    <h3 class="text-lg font-black text-gray-900 dark:text-white">المنتجات المختارة</h3>
                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mt-0.5">محتويات سلة الشراء ({{ cart.items.length }})</p>
                  </div>
                </div>
              </div>

              <div class="space-y-8">
                <div v-for="item in cart.items" :key="item.id" class="flex items-center gap-5 p-4 rounded-2xl hover:bg-gray-50 dark:hover:bg-night-800/50 transition-colors border border-transparent hover:border-gray-100 dark:hover:border-night-800 group">
                  <div class="w-20 h-20 bg-white dark:bg-night-800 rounded-2xl flex-shrink-0 overflow-hidden border border-gray-100 dark:border-night-700 p-3 group-hover:scale-105 transition-transform duration-500">
                    <img :src="item.image ? `/images/products/${item.image}` : '/images/products/no-image.png'" class="w-full h-full object-contain" />
                  </div>

                  <div class="flex-grow min-w-0">
                    <div class="flex items-start justify-between gap-3">
                       <div class="flex-1 min-w-0">
                          <h4 class="text-sm font-black text-gray-900 dark:text-white truncate group-hover:text-brand transition-colors">{{ item.name }}</h4>
                          <span class="text-[9px] font-black text-brand dark:text-accent bg-brand/5 dark:bg-accent/5 px-2 py-0.5 rounded-full border border-brand/10 dark:border-accent/10 whitespace-nowrap mt-1 inline-block">{{ item.variant_name || 'قياسي' }}</span>
                       </div>
                       <p class="text-base font-black text-gray-900 dark:text-white tabular-nums whitespace-nowrap">{{ formatPrice(item.price * item.qty) }} <span class="text-[9px] opacity-40">{{ s.currency_code }}</span></p>
                    </div>

                    <div class="flex items-center justify-between mt-3">
                      <div class="flex items-center bg-gray-100 dark:bg-night-800 rounded-xl p-0.5 gap-0.5 shadow-inner">
                        <button @click="updateQty(item.id, item.qty - 1)" class="w-7 h-7 flex items-center justify-center text-gray-400 hover:bg-white dark:hover:bg-night-700 hover:text-brand rounded-lg transition-all"><Minus class="w-3 h-3" /></button>
                        <span class="w-8 text-center font-black text-xs text-gray-900 dark:text-white tabular-nums">{{ item.qty }}</span>
                        <button @click="updateQty(item.id, item.qty + 1)" class="w-7 h-7 flex items-center justify-center text-gray-400 hover:bg-white dark:hover:bg-night-700 hover:text-brand rounded-lg transition-all"><Plus class="w-3 h-3" /></button>
                      </div>
                      <button @click="removeItem(item.id)" class="text-red-300 dark:text-red-500 hover:text-red-500 p-1.5 transition rounded-xl hover:bg-red-50 dark:hover:bg-red-500/10">
                        <Trash2 class="w-4 h-4" />
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Step 2: Shipping Address -->
            <div class="bg-white dark:bg-night-900 rounded-[3rem] shadow-xl shadow-gray-200/20 dark:shadow-none border border-gray-50 dark:border-night-800 p-10 md:p-14 relative overflow-hidden transition-colors duration-500">
              <div class="absolute top-0 right-0 w-32 h-32 bg-accent/5 dark:bg-accent/5 rounded-full blur-[60px] -translate-y-1/2 translate-x-1/2"></div>
              
              <h3 class="text-2xl font-black text-gray-900 dark:text-white mb-12 pb-6 border-b border-gray-50 dark:border-night-800 flex items-center gap-5">
                <div class="w-14 h-14 rounded-[1.25rem] bg-gray-50 dark:bg-night-800 flex items-center justify-center text-brand dark:text-accent shadow-inner">
                  <MapPin class="w-7 h-7" />
                </div>
                <div>
                   <span class="italic">بيانات التوصيل والعميل</span>
                   <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mt-1">تأكد من دقة العنوان ورقم الهاتف</p>
                </div>
              </h3>

              <div v-if="client" class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="p-8 bg-brand/5 dark:bg-brand/10 rounded-[2.5rem] border border-brand/20 dark:border-brand/30 shadow-xl shadow-brand/5 relative group hover:scale-[1.02] transition-transform duration-500">
                  <div class="flex items-center gap-3 mb-6">
                     <User class="w-5 h-5 text-brand" />
                     <p class="text-[10px] font-black text-brand dark:text-accent uppercase tracking-widest">المستلم</p>
                  </div>
                  <p class="text-2xl font-black text-gray-900 dark:text-white truncate italic">{{ client.name }}</p>
                  <p class="text-gray-500 dark:text-gray-400 mt-3 font-medium text-lg leading-relaxed flex items-start gap-2">
                     <MapPin class="w-5 h-5 mt-1 shrink-0 opacity-40" />
                     {{ client.adresse || 'لا يوجد عنوان مسجل' }}
                  </p>
                  <Link href="/account" class="absolute top-6 left-6 w-10 h-10 rounded-xl bg-white dark:bg-night-900 border border-gray-100 dark:border-night-800 shadow-sm flex items-center justify-center text-gray-400 hover:text-brand transition-all opacity-0 group-hover:opacity-100">
                     <ArrowRight class="w-4 h-4 rotate-180" />
                  </Link>
                </div>

                <div class="p-8 bg-gray-50 dark:bg-night-800 rounded-[2.5rem] border border-gray-100 dark:border-night-700 shadow-lg shadow-gray-200/50 dark:shadow-none group hover:scale-[1.02] transition-transform duration-500">
                  <div class="flex items-center gap-3 mb-6">
                     <Phone class="w-5 h-5 text-gray-400" />
                     <p class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest">معلومات التواصل</p>
                  </div>
                  <div class="space-y-4">
                     <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-white dark:bg-night-900 rounded-xl flex items-center justify-center text-gray-400 border border-gray-100 dark:border-night-700"><Phone class="w-5 h-5" /></div>
                        <p class="text-xl font-black text-gray-900 dark:text-white tabular-nums tracking-wider">{{ client.phone }}</p>
                     </div>
                     <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-white dark:bg-night-900 rounded-xl flex items-center justify-center text-gray-400 border border-gray-100 dark:border-night-700"><Mail class="w-5 h-5" /></div>
                        <p class="text-lg font-bold text-gray-500 dark:text-gray-400 truncate">{{ $page.props.auth.store_user?.email }}</p>
                     </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Right Column: Summary & Confirmation -->
          <aside class="lg:col-span-4 space-y-6 lg:sticky lg:top-32 animate-in fade-in slide-in-from-left-10 duration-700">
            
            <div class="bg-gray-900 dark:bg-night-900 text-white rounded-[3rem] p-10 md:p-12 shadow-[0_50px_100px_-20px_rgba(4,114,77,0.3)] dark:shadow-none border border-transparent dark:border-night-800 relative overflow-hidden transition-all duration-500">
              <!-- Decorative Ornaments -->
              <div class="absolute -top-20 -left-20 w-48 h-48 bg-brand/30 rounded-full blur-[80px] pointer-events-none"></div>
              <div class="absolute -bottom-20 -right-20 w-40 h-40 bg-accent/10 rounded-full blur-[60px] pointer-events-none"></div>

              <div class="flex items-center justify-between mb-12">
                 <h3 class="text-2xl font-black italic flex items-center gap-3">
                   <CreditCard class="w-6 h-6 text-brand dark:text-accent" />
                   ملخص الفاتورة
                 </h3>
                 <div class="text-[10px] font-black uppercase tracking-widest text-white/40 border border-white/10 px-3 py-1 rounded-full">B2B المعتمد</div>
              </div>

              <div class="space-y-6 relative z-10 font-bold">
                <div class="flex justify-between items-center text-white/50">
                  <span class="text-sm">المجموع الفرعي</span>
                  <p class="text-lg tabular-nums text-white">{{ formatPrice(cart.subtotal) }} <span class="text-xs opacity-50 ml-1">{{ s.currency_code }}</span></p>
                </div>
                <div class="flex justify-between items-center text-white/50 pb-6 border-b border-white/5">
                  <span class="text-sm">مصاريف الشحن</span>
                  <span class="text-[10px] font-black uppercase tracking-widest bg-brand/20 text-brand py-2 px-5 rounded-full border border-brand/20">شحن مجاني</span>
                </div>
                
                <div class="pt-5 mt-2 border-t border-white/5 flex flex-col gap-1.5">
                  <span class="text-brand dark:text-accent font-black text-[9px] uppercase tracking-[0.4em]">القيمة الإجمالية للطلب</span>
                  <p class="text-3xl md:text-4xl font-black tracking-tighter text-white tabular-nums">
                    {{ formatPrice(cart.grand) }}
                    <span class="text-sm font-bold opacity-40 mr-1 leading-none uppercase">{{ s.currency_code || 'ج.م' }}</span>
                  </p>
                </div>
              </div>

              <button
                @click="placeOrder"
                :disabled="processing"
                class="w-full bg-brand hover:bg-accent text-gray-900 group h-16 rounded-2xl font-black uppercase tracking-widest text-base mt-10 transition-all duration-400 shadow-2xl shadow-brand/30 disabled:opacity-50 overflow-hidden relative flex items-center justify-center gap-3 hover:-translate-y-0.5 active:scale-95">
                <div class="absolute inset-0 bg-white/10 -translate-x-full group-hover:translate-x-full transition-transform duration-700"></div>
                <div v-if="processing" class="flex items-center gap-2">
                   <svg class="animate-spin h-5 w-5" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                   <span>جارٍ المعالجة...</span>
                </div>
                <div v-else class="flex items-center gap-3">
                   <span>تأكيد وشراء الآن</span>
                   <ArrowRight class="w-5 h-5 rotate-180 group-hover:-translate-x-1 transition-transform" />
                </div>
              </button>

              <p
                v-if="checkoutError"
                role="alert"
                class="mt-5 rounded-2xl border border-red-400/20 bg-red-500/10 px-5 py-4 text-sm font-bold leading-relaxed text-red-200"
              >
                {{ checkoutError }}
              </p>

              <div class="mt-10 flex flex-col gap-4">
                 <div class="flex items-center gap-3 text-[10px] font-black text-white/30 uppercase tracking-widest">
                    <ShieldCheck class="w-4 h-4 text-brand" /> ضمان DawPOS للتوريد الآمن
                 </div>
                 <div class="flex items-center gap-3 text-[10px] font-black text-white/30 uppercase tracking-widest">
                    <Truck class="w-4 h-4 text-brand" /> تسليم خلال 24 - 48 ساعة عمل
                 </div>
              </div>
            </div>

            <!-- Additional Security Marker -->
            <div class="bg-gray-100 dark:bg-night-900/50 rounded-[2rem] p-6 text-center border border-transparent dark:border-night-800">
              <p class="text-[10px] text-gray-400 dark:text-gray-500 font-extrabold uppercase tracking-widest flex items-center justify-center gap-3 italic">
                <ShieldCheck class="w-4 h-4 opacity-50" />
                بياناتك مشفرة ومحمية بالكامل بواسطة DawPOS
              </p>
            </div>
          </aside>
        </div>
      </div>
    </div>
  </StoreLayout>
</template>

<style scoped>
/* Page transition safety */
.animate-in {
  animation-duration: 0.8s;
  animation-timing-function: cubic-bezier(0.2, 0, 0, 1);
  animation-fill-mode: both;
}
</style>
