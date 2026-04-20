<script setup>
import { Link, Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import axios from 'axios';
import StoreLayout from '@/Layouts/StoreLayout.vue';
import { 
  User, 
  ShoppingBag, 
  ChevronLeft, 
  Search, 
  Calendar, 
  Hash, 
  ArrowRight,
  PackageCheck,
  Clock,
  XCircle,
  AlertCircle
} from 'lucide-vue-next';

const props = defineProps({
  s: Object,
});

const orders = ref([]);
const loading = ref(true);
const meta = ref({ total: 0, page: 1, pages: 1 });

const fetchOrders = async (page = 1) => {
  loading.value = true;
  try {
    const res = await axios.get(`/my/orders?page=${page}`);
    orders.value = res.data.data;
    meta.value = res.data.meta;
  } catch (err) {
    console.error(err);
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchOrders();
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
  <Head :title="`طلباتي | ${s?.store_name || 'المتجر'}`" />

  <StoreLayout>
    <div class="bg-gray-50 dark:bg-night-950 min-h-screen py-12 md:py-24 transition-colors duration-500">
      <div class="container mx-auto px-4 max-w-7xl">
        
        <div class="flex flex-col lg:flex-row gap-12 items-start">
          
          <!-- Sidebar Navigation -->
          <aside class="w-full lg:w-80 flex-shrink-0 sticky top-24">
            <div class="bg-white dark:bg-night-900 rounded-[3rem] p-8 shadow-xl shadow-gray-200/50 dark:shadow-black/20 border border-gray-100 dark:border-night-800">
               <nav class="space-y-3">
                 <Link href="/account" class="flex items-center justify-between px-6 py-4 text-gray-400 dark:text-gray-500 hover:bg-gray-50 dark:hover:bg-night-800 hover:text-gray-900 dark:hover:text-white rounded-[1.5rem] font-bold transition-all group">
                   <div class="flex items-center gap-4">
                     <User class="w-5 h-5" />
                     ملفي الشخصي
                   </div>
                   <ChevronLeft class="w-4 h-4 opacity-0 group-hover:opacity-100 transition-opacity" />
                 </Link>
                 
                 <Link href="/account/orders" class="flex items-center justify-between px-6 py-4 bg-brand/10 dark:bg-accent/10 text-brand dark:text-accent rounded-[1.5rem] font-black transition-all group">
                   <div class="flex items-center gap-4">
                     <ShoppingBag class="w-5 h-5" />
                     طلباتي السابقة
                   </div>
                   <ChevronLeft class="w-4 h-4" />
                 </Link>
               </nav>
            </div>
          </aside>

          <!-- Orders Content Area -->
          <main class="flex-grow">
            <div class="bg-white dark:bg-night-900 rounded-[3.5rem] shadow-2xl shadow-gray-200/50 dark:shadow-black/40 border border-gray-100 dark:border-night-800 overflow-hidden">
              
              <div class="p-8 md:p-14 border-b border-gray-100 dark:border-night-800 bg-gray-50/50 dark:bg-night-900/50 flex flex-col md:flex-row md:items-end justify-between gap-8">
                <div>
                   <h1 class="text-3xl md:text-4xl font-black text-gray-900 dark:text-white tracking-tighter mb-4 italic">تاريخ الطلبات</h1>
                   <p class="text-gray-400 dark:text-gray-500 font-medium">تتبع وراجع جميع مشترياتك السابقة وتفاصيل شحنها.</p>
                </div>
                
                <!-- Search Mockup -->
                <div class="relative w-full md:w-72 group">
                   <input type="text" placeholder="ابحث برقم الطلب..." class="w-full bg-white dark:bg-night-800 border-2 border-gray-100 dark:border-night-700 focus:border-brand dark:focus:border-accent rounded-[1.5rem] py-4 pr-12 pl-6 font-bold text-sm transition-all focus:ring-0 shadow-sm" />
                   <Search class="absolute right-5 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-300 dark:text-gray-600 group-focus-within:text-brand dark:group-focus-within:text-accent transition-colors" />
                </div>
              </div>

              <div class="p-0">
                <!-- Loading State -->
                <div v-if="loading" class="p-32 flex flex-col items-center justify-center text-gray-400 text-center">
                  <div class="w-20 h-20 border-4 border-gray-100 dark:border-night-800 border-t-brand dark:border-t-accent rounded-full animate-spin mb-8"></div>
                  <p class="font-black uppercase tracking-[0.3em] text-xs">جاري جلب بياناتك...</p>
                </div>

                <!-- Empty State -->
                <div v-else-if="orders.length === 0" class="p-32 text-center">
                  <div class="inline-flex items-center justify-center w-32 h-32 bg-gray-50 dark:bg-night-800 rounded-[2.5rem] mb-8 relative">
                    <ShoppingBag class="w-14 h-14 text-gray-300 dark:text-gray-700" />
                    <div class="absolute -bottom-2 -right-2 w-10 h-10 bg-white dark:bg-night-900 rounded-full border-4 border-gray-50 dark:border-night-800 flex items-center justify-center text-gray-400">?</div>
                  </div>
                  <h3 class="text-3xl font-black text-gray-900 dark:text-white mb-4">لا توجد طلبات بعد</h3>
                  <p class="text-gray-500 dark:text-gray-400 mb-12 max-w-sm mx-auto font-medium">ابدأ رحلة تسوقك الآن واكتشف أفضل المنتجات B2B في السوق المصري.</p>
                  <Link href="/shop" class="bg-gray-900 dark:bg-brand text-white dark:text-night-950 px-14 py-5 rounded-[1.5rem] font-black text-xl shadow-2xl hover:scale-105 transition-all inline-flex items-center gap-3">
                    ابحث عن منتجات
                    <ArrowRight class="w-6 h-6 rotate-180" />
                  </Link>
                </div>

                <!-- Orders List -->
                <div v-else>
                  <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-right border-collapse">
                      <thead>
                        <tr class="bg-gray-50/30 dark:bg-night-800/20">
                          <th class="px-10 py-8 text-xs font-black text-gray-400 dark:text-gray-600 uppercase tracking-[0.3em]">الطلب</th>
                          <th class="px-10 py-8 text-xs font-black text-gray-400 dark:text-gray-600 uppercase tracking-[0.3em]">التاريخ</th>
                          <th class="px-10 py-8 text-xs font-black text-gray-400 dark:text-gray-600 uppercase tracking-[0.3em]">الحالة</th>
                          <th class="px-10 py-8 text-xs font-black text-gray-400 dark:text-gray-600 uppercase tracking-[0.3em]">المجموع</th>
                          <th class="px-10 py-8"></th>
                        </tr>
                      </thead>
                      <tbody class="divide-y divide-gray-100 dark:divide-night-800">
                        <tr v-for="order in orders" :key="order.id" class="group hover:bg-brand/[0.02] dark:hover:bg-accent/[0.02] transition-colors">
                          <td class="px-10 py-10">
                            <div class="flex items-center gap-4">
                               <div class="w-10 h-10 rounded-xl bg-gray-50 dark:bg-night-800 flex items-center justify-center text-gray-400 dark:text-gray-600">
                                  <Hash class="w-4 h-4" />
                               </div>
                               <span class="font-black text-gray-900 dark:text-white tracking-tighter text-xl">{{ order.code }}</span>
                            </div>
                          </td>
                          <td class="px-10 py-10">
                            <div class="flex items-center gap-3 text-gray-500 dark:text-gray-400 font-bold">
                               <Calendar class="w-4 h-4 opacity-50" />
                               {{ order.created_at }}
                            </div>
                          </td>
                          <td class="px-10 py-10">
                            <div :class="[getStatusBadge(order.status).class, 'inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl border text-xs font-black uppercase tracking-widest']">
                               <component :is="getStatusBadge(order.status).icon" class="w-3.5 h-3.5" />
                               {{ getStatusBadge(order.status).text }}
                            </div>
                          </td>
                          <td class="px-10 py-10">
                            <span class="font-black text-brand dark:text-accent text-2xl tracking-tighter tabular-nums">
                              {{ order.total.toFixed(2) }} 
                              <span class="text-sm opacity-50 ml-1">{{ s.currency_code || 'ج.م' }}</span>
                            </span>
                          </td>
                          <td class="px-10 py-10 text-left">
                             <Link :href="`/account/orders/${order.id}`" class="px-6 py-3 rounded-xl bg-white dark:bg-night-800 border border-gray-100 dark:border-night-700 text-sm font-black text-gray-400 dark:text-gray-600 hover:border-brand dark:hover:border-accent hover:text-brand dark:hover:text-accent transition-all inline-flex items-center gap-3">
                               التفاصيل
                               <ChevronLeft class="w-4 h-4" />
                             </Link>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>

                  <!-- Pagination -->
                  <div v-if="meta.pages > 1" class="p-10 border-t border-gray-100 dark:border-night-800 flex justify-center gap-4">
                     <button @click="fetchOrders(meta.page - 1)" :disabled="meta.page === 1" class="w-12 h-12 flex items-center justify-center rounded-2xl bg-gray-50 dark:bg-night-800 text-gray-400 disabled:opacity-30 border border-gray-100 dark:border-night-700"><ChevronLeft class="w-5 h-5 rotate-180" /></button>
                     <div class="flex items-center px-4 font-black text-gray-400">{{ meta.page }} / {{ meta.pages }}</div>
                     <button @click="fetchOrders(meta.page + 1)" :disabled="meta.page === meta.pages" class="w-12 h-12 flex items-center justify-center rounded-2xl bg-gray-50 dark:bg-night-800 text-gray-400 disabled:opacity-30 border border-gray-100 dark:border-night-700"><ChevronLeft class="w-5 h-5" /></button>
                  </div>
                </div>
              </div>
            </div>
          </main>
        </div>
      </div>
    </div>
  </StoreLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { height: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.1); border-radius: 10px; }
:global(.dark) .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); }
</style>
