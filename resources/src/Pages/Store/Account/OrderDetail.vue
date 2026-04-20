<script setup>
import { router, Link, Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import axios from 'axios';
import StoreLayout from '@/Layouts/StoreLayout.vue';
import { 
  ChevronRight, 
  Printer, 
  Package, 
  Truck, 
  CreditCard, 
  User, 
  Mail, 
  Phone, 
  MapPin,
  Clock,
  PackageCheck,
  XCircle,
  AlertCircle
} from 'lucide-vue-next';

const props = defineProps({
  s: Object,
  orderId: String,
});

const order = ref(null);
const loading = ref(true);

const fetchOrder = async () => {
  loading.value = true;
  try {
    const res = await axios.get(`/my/orders/${props.orderId}`);
    order.value = res.data;
  } catch (err) {
    console.error(err);
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchOrder();
});

const getStatusBadge = (status) => {
  switch (status) {
    case 'pending': return { text: 'قيد المراجعة', class: 'bg-amber-500/10 text-amber-500 border-amber-500/20', icon: Clock };
    case 'confirmed': return { text: 'تم التأكيد', class: 'bg-brand/10 text-brand border-brand/20', icon: PackageCheck };
    case 'cancelled': return { text: 'ملغي', class: 'bg-red-500/10 text-red-500 border-red-500/20', icon: XCircle };
    default: return { text: status, class: 'bg-gray-500/10 text-gray-500 border-gray-500/20', icon: AlertCircle };
  }
};
</script>

<template>
  <Head :title="`تفاصيل الطلب #${orderId} | ${s?.store_name || 'المتجر'}`" />

  <StoreLayout>
    <div class="bg-gray-50 dark:bg-night-950 min-h-screen py-10 md:py-20 transition-colors duration-500">
      <div class="container mx-auto px-4 max-w-5xl">
        
        <!-- Header Actions -->
        <div class="mb-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
           <Link href="/account/orders" class="inline-flex items-center gap-2 text-sm font-black text-gray-400 hover:text-brand dark:hover:text-accent transition-all group">
              <ChevronRight class="w-4 h-4" />
              العودة إلى قائمة الطلبات
           </Link>
           
           <div class="flex items-center gap-4">
              <button v-if="order" @click="window.print()" class="bg-white dark:bg-night-900 border border-gray-100 dark:border-night-800 px-6 py-3 rounded-2xl text-xs font-black uppercase tracking-widest text-gray-600 dark:text-gray-400 hover:border-brand dark:hover:border-accent hover:text-brand dark:hover:text-accent transition-all flex items-center gap-3 shadow-sm">
                <Printer class="w-4 h-4" />
                طباعة الفاتورة
              </button>
           </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="bg-white dark:bg-night-900 rounded-[3rem] p-32 flex flex-col items-center justify-center text-gray-400 shadow-2xl border border-gray-100 dark:border-night-800">
           <div class="w-20 h-20 border-4 border-gray-100 dark:border-night-800 border-t-brand dark:border-t-accent rounded-full animate-spin mb-8"></div>
           <p class="font-black uppercase tracking-[0.3em] text-[10px]">جاري تحميل البيانات...</p>
        </div>

        <div v-else-if="order" class="space-y-8">
           
           <!-- Main Info Card -->
           <div class="bg-white dark:bg-night-900 rounded-[3rem] shadow-2xl shadow-gray-200/50 dark:shadow-black/40 border border-gray-100 dark:border-night-800 overflow-hidden">
              <div class="p-8 md:p-14 border-b border-gray-100 dark:border-night-800 bg-gray-50/50 dark:bg-night-900/50">
                 <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-8">
                    <div class="text-right">
                       <div class="flex flex-wrap items-center gap-4 mb-4">
                          <h1 class="text-2xl md:text-3xl font-black text-gray-900 dark:text-white tracking-tighter uppercase">تفاصيل الطلب #{{ order.code }}</h1>
                          <div :class="[getStatusBadge(order.status).class, 'px-5 py-2.5 rounded-2xl border text-xs font-black uppercase tracking-widest flex items-center gap-2']">
                             <component :is="getStatusBadge(order.status).icon" class="w-4 h-4" />
                             {{ getStatusBadge(order.status).text }}
                          </div>
                       </div>
                       <div class="flex items-center gap-3 text-gray-500 dark:text-gray-400 font-bold">
                          <Clock class="w-4 h-4 opacity-50" />
                          <span>تم الطلب في {{ order.date }} الساعة {{ order.time }}</span>
                       </div>
                    </div>
                    <div class="text-right lg:text-left bg-white dark:bg-night-800 p-6 rounded-3xl border border-gray-100 dark:border-night-700 shadow-sm">
                       <p class="text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-2">إجمالي الفاتورة</p>
                       <p class="text-3xl font-black text-brand dark:text-accent tracking-tighter tabular-nums">
                          {{ order.total.toFixed(2) }} 
                          <span class="text-lg opacity-60 ml-1">{{ s.currency_code || 'ج.م' }}</span>
                       </p>
                    </div>
                 </div>
              </div>

              <!-- Items Table -->
              <div class="overflow-x-auto">
                 <table class="w-full text-right border-collapse">
                    <thead>
                       <tr class="bg-white dark:bg-night-900">
                          <th class="px-12 py-10 text-xs font-black text-gray-400 dark:text-gray-600 uppercase tracking-[0.3em]">المنتج</th>
                          <th class="px-8 py-10 text-xs font-black text-gray-400 dark:text-gray-600 uppercase tracking-[0.3em] text-center">الكمية</th>
                          <th class="px-8 py-10 text-xs font-black text-gray-400 dark:text-gray-600 uppercase tracking-[0.3em] text-left">سعر الوحدة</th>
                          <th class="px-12 py-10 text-xs font-black text-gray-400 dark:text-gray-600 uppercase tracking-[0.3em] text-left">الإجمالي</th>
                       </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-night-800">
                       <tr v-for="item in order.items" :key="item.id" class="group transition-colors hover:bg-gray-50/50 dark:hover:bg-night-800/20">
                          <td class="px-12 py-8">
                             <div class="flex items-center gap-5">
                                <div class="w-16 h-16 rounded-2xl bg-gray-50 dark:bg-night-800 p-2 flex items-center justify-center border border-gray-100 dark:border-night-700 group-hover:scale-110 transition-transform">
                                   <Package class="w-8 h-8 text-gray-300 dark:text-gray-600" />
                                </div>
                                <div>
                                   <p class="font-black text-gray-900 dark:text-white text-lg leading-tight">{{ item.name }}</p>
                                   <p class="text-[10px] text-brand dark:text-accent font-black uppercase tracking-widest mt-1">كود المنتج: {{ item.product_id }}</p>
                                </div>
                             </div>
                          </td>
                          <td class="px-8 py-8 text-center tabular-nums">
                             <span class="bg-gray-100 dark:bg-night-800 text-gray-900 dark:text-white px-5 py-2.5 rounded-2xl font-black text-base">{{ item.qty }}</span>
                          </td>
                          <td class="px-8 py-8 text-left font-bold text-gray-500 dark:text-gray-400 tabular-nums">
                             {{ item.price.toFixed(2) }}
                          </td>
                          <td class="px-12 py-8 text-left font-black text-gray-900 dark:text-white tabular-nums text-xl">
                             {{ item.line_total.toFixed(2) }}
                          </td>
                       </tr>
                    </tbody>
                    <tfoot>
                       <tr class="bg-gray-50/20 dark:bg-night-800/10">
                          <td colspan="3" class="px-12 py-10 text-left font-black text-gray-400 dark:text-gray-600 uppercase tracking-[0.4em] text-sm">المجموع الفرعي</td>
                          <td class="px-12 py-10 text-left font-black text-gray-900 dark:text-white text-3xl tabular-nums tracking-tighter">{{ order.subtotal.toFixed(2) }}</td>
                       </tr>
                    </tfoot>
                 </table>
              </div>
           </div>

           <!-- Details Grid -->
           <div class="grid md:grid-cols-2 gap-10">
              
              <!-- Customer Info -->
              <div class="bg-white dark:bg-night-900 rounded-[2.5rem] p-10 shadow-xl border border-gray-100 dark:border-night-800">
                 <div class="flex items-center gap-4 mb-8 text-brand dark:text-accent">
                    <User class="w-6 h-6" />
                    <h3 class="text-sm font-black uppercase tracking-[0.3em]">بيانات العميل</h3>
                 </div>
                 <div class="space-y-6">
                    <div>
                       <p class="text-[10px] font-black text-gray-400 dark:text-gray-600 uppercase tracking-widest mb-1.5 ml-2">الاسم بالكامل</p>
                       <p class="font-black text-gray-900 dark:text-white text-lg">{{ order.customer_name }}</p>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                       <div class="flex items-center gap-4">
                          <div class="w-10 h-10 bg-gray-50 dark:bg-night-800 rounded-xl flex items-center justify-center text-gray-400"><Phone class="w-4 h-4" /></div>
                          <p class="font-black text-gray-600 dark:text-gray-400 tabular-nums">{{ order.customer_phone }}</p>
                       </div>
                       <div class="flex items-center gap-4">
                          <div class="w-10 h-10 bg-gray-50 dark:bg-night-800 rounded-xl flex items-center justify-center text-gray-400"><Mail class="w-4 h-4" /></div>
                          <p class="font-bold text-gray-600 dark:text-gray-400 truncate">{{ order.customer_email }}</p>
                       </div>
                    </div>
                 </div>
              </div>

              <!-- Shipping Info -->
              <div class="bg-white dark:bg-night-900 rounded-[2.5rem] p-10 shadow-xl border border-gray-100 dark:border-night-800">
                 <div class="flex items-center gap-4 mb-8 text-brand dark:text-accent">
                    <Truck class="w-6 h-6" />
                    <h3 class="text-sm font-black uppercase tracking-[0.3em]">الشحن والتسليم</h3>
                 </div>
                 <div class="space-y-6">
                    <div class="flex gap-4">
                       <div class="w-10 h-10 bg-gray-50 dark:bg-night-800 rounded-xl flex items-center justify-center text-gray-400 flex-shrink-0"><MapPin class="w-4 h-4" /></div>
                       <div>
                          <p class="text-[10px] font-black text-gray-400 dark:text-gray-600 uppercase tracking-widest mb-1.5 ml-2">المخزن المسؤول</p>
                          <p class="font-black text-gray-900 dark:text-white text-lg">{{ order.warehouse_name }}</p>
                       </div>
                    </div>
                    <div class="flex items-center gap-4">
                       <div class="w-10 h-10 bg-gray-50 dark:bg-night-800 rounded-xl flex items-center justify-center text-gray-400"><CreditCard class="w-4 h-4" /></div>
                       <div>
                          <p class="text-[10px] font-black text-gray-400 dark:text-gray-600 uppercase tracking-widest mb-1.5 ml-2">طريقة الدفع</p>
                          <p class="font-black text-gray-900 dark:text-white">الدفع عند الاستلام (COD)</p>
                       </div>
                    </div>
                 </div>
              </div>
              
           </div>
        </div>
      </div>
    </div>
  </StoreLayout>
</template>

<style scoped>
@media print {
  body { background: white; }
  .no-print { display: none; }
  aside { display: none; }
  button { display: none; }
}
</style>
