<script setup>
import { ref, watch, reactive } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import StoreLayout from '@/Layouts/StoreLayout.vue';
import ProductCard from '@/Components/ProductCard.vue';
import Pagination from '@/Components/Pagination.vue';
import QuickViewModal from '@/Components/Store/QuickViewModal.vue';
import { useCart } from '@/Composables/useCart';
import debounce from 'lodash/debounce';
import {
  Search,
  SlidersHorizontal,
  RotateCcw,
  X,
  ChevronDown,
  Package,
  Layers,
  Filter,
  GridIcon,
  Tag
} from 'lucide-vue-next';

const props = defineProps({
  s: Object,
  products: Object,
  categories: Array,
  collections: Array,
  filters: Object,
});

const filterForm = reactive({
  q: props.filters.q || '',
  category: props.filters.category || '',
  collection: props.filters.collection || '',
  min: props.filters.min || '',
  max: props.filters.max || '',
  sort: props.filters.sort || 'latest',
});

const applyFilters = () => {
  router.get('/shop',
    { ...filterForm },
    { preserveState: true, replace: true }
  );
};

watch(
  () => filterForm.q,
  debounce(() => { applyFilters(); }, 500)
);

const resetFilters = () => {
  Object.keys(filterForm).forEach(key => filterForm[key] = key === 'sort' ? 'latest' : '');
  applyFilters();
};

const removeFilter = (key) => {
  filterForm[key] = '';
  applyFilters();
};

const hasFilters = () => {
  return filterForm.q || filterForm.category || filterForm.collection || filterForm.min || filterForm.max;
};

const { addToCart: addItemToCart } = useCart();
const addToCart = (product) => { addItemToCart(product); };

const selectedQuickViewProduct = ref(null);
const isQuickViewOpen = ref(false);
const quickView = (product) => {
  selectedQuickViewProduct.value = product;
  isQuickViewOpen.value = true;
};
</script>

<template>
  <StoreLayout>
    <div class="bg-gray-50 dark:bg-night-950 min-h-screen transition-colors duration-500 font-['Cairo']" dir="rtl">

      <!-- Page Header -->
      <div class="relative overflow-hidden bg-white dark:bg-night-900 border-b border-gray-100 dark:border-night-800 py-10 md:py-14">
        <div class="absolute inset-0 bg-gradient-to-l from-brand/4 to-transparent dark:from-accent/4 pointer-events-none"></div>
        <div class="container mx-auto px-4 relative z-10">
          <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="text-right">
              <div class="flex items-center gap-2 text-brand dark:text-accent text-[10px] font-black uppercase tracking-[0.3em] mb-3">
                 <div class="w-8 h-[2px] bg-brand dark:bg-accent"></div>
                 <span>كتالوج المنتجات</span>
              </div>
              <h1 class="text-xl md:text-3xl font-black text-gray-900 dark:text-white tracking-tighter leading-none">تصفح <span class="text-brand dark:text-accent">الكتالوج الزراعي</span></h1>
              <p class="text-xs text-gray-400 dark:text-gray-500 font-medium mt-2">استكشف أجود أنواع البذور والأعلاف والأسمدة المتاحة لمزرعتك.</p>
            </div>

            <div class="bg-gray-50 dark:bg-night-800 px-4 py-3 rounded-xl border border-gray-100 dark:border-night-700 flex items-center gap-3 self-start md:self-auto">
               <Package class="w-4 h-4 text-brand dark:text-accent flex-shrink-0" />
               <div>
                  <p class="text-[8px] font-black text-gray-400 uppercase tracking-widest leading-none mb-0.5">إجمالي الأصناف</p>
                  <p class="text-lg font-black text-gray-900 dark:text-white tabular-nums leading-none">{{ products.total }} <span class="text-[10px] opacity-40 font-bold">صنف</span></p>
               </div>
            </div>
          </div>
        </div>
      </div>

      <div class="container mx-auto px-4 py-10">
        <div class="flex flex-col lg:flex-row gap-8">

          <!-- Sidebar Filter -->
          <aside class="w-full lg:w-64 flex-shrink-0">
            <div class="sticky top-24 space-y-4">

              <div class="bg-white dark:bg-night-900 rounded-2xl p-6 border border-gray-100 dark:border-night-800 shadow-sm">

                <div class="flex items-center justify-between mb-4">
                  <div class="flex items-center gap-2 text-gray-900 dark:text-white">
                    <Filter class="w-3.5 h-3.5 text-brand dark:text-accent" />
                    <h2 class="text-xs font-black uppercase tracking-tight">تصفية النتائج</h2>
                  </div>
                  <button v-if="hasFilters()" @click="resetFilters" class="text-[8px] font-black text-red-500 dark:text-red-400 uppercase tracking-widest flex items-center gap-1 hover:scale-105 transition-transform group">
                    <RotateCcw class="w-2 h-2 group-hover:rotate-[-45deg] transition-transform" />
                    مسح الكل
                  </button>
                </div>

                <!-- Search -->
                <div class="mb-6 space-y-2">
                  <label class="text-[9px] font-black text-gray-400 dark:text-gray-600 uppercase tracking-widest block">بحث سريع</label>
                  <div class="relative">
                    <input
                      v-model="filterForm.q"
                      type="text"
                      placeholder="عن ماذا تبحث؟"
                      class="w-full bg-gray-50 dark:bg-night-800 border-none rounded-xl py-3 pr-10 pl-3 text-sm focus:ring-2 focus:ring-brand/15 transition-all font-bold text-gray-900 dark:text-white placeholder-gray-300 dark:placeholder-gray-700"
                    />
                    <Search class="w-4 h-4 absolute right-3 top-1/2 -translate-y-1/2 text-gray-300 dark:text-gray-600" />
                  </div>
                </div>

                <!-- Categories -->
                <div class="mb-6 space-y-2">
                  <h3 class="text-[9px] font-black text-gray-400 dark:text-gray-600 uppercase tracking-widest flex items-center gap-1.5">
                     <Layers class="w-3 h-3 opacity-50" /> الفئات
                  </h3>
                  <div class="space-y-0.5 max-h-56 overflow-y-auto pr-1 custom-scrollbar">
                    <label class="flex items-center gap-3 cursor-pointer group py-2 px-3 rounded-xl transition-all" :class="filterForm.category === '' ? 'bg-brand/8 dark:bg-accent/8' : 'hover:bg-gray-50 dark:hover:bg-night-800'">
                      <div class="relative w-4 h-4 flex-shrink-0">
                        <input type="radio" v-model="filterForm.category" value="" @change="applyFilters" class="peer hidden" />
                        <div class="w-full h-full bg-gray-100 dark:bg-night-800 rounded border border-gray-200 dark:border-night-700 peer-checked:bg-brand dark:peer-checked:bg-accent peer-checked:border-transparent transition-all"></div>
                      </div>
                      <span class="text-xs font-bold text-gray-700 dark:text-gray-300 group-hover:text-brand dark:group-hover:text-accent transition-colors">عرض الجميع</span>
                    </label>

                    <label v-for="cat in categories" :key="cat.id" class="flex items-center gap-3 cursor-pointer group py-2 px-3 rounded-xl transition-all" :class="filterForm.category == cat.id ? 'bg-brand/8 dark:bg-accent/8' : 'hover:bg-gray-50 dark:hover:bg-night-800'">
                      <div class="relative w-4 h-4 flex-shrink-0">
                        <input type="radio" v-model="filterForm.category" :value="cat.id" @change="applyFilters" class="peer hidden" />
                        <div class="w-full h-full bg-gray-100 dark:bg-night-800 rounded border border-gray-200 dark:border-night-700 peer-checked:bg-brand dark:peer-checked:bg-accent peer-checked:border-transparent transition-all"></div>
                      </div>
                      <span class="text-xs font-bold text-gray-600 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white transition-colors truncate">{{ cat.name }}</span>
                    </label>
                  </div>
                </div>

                <!-- Price Range -->
                <div class="space-y-3">
                  <h3 class="text-[9px] font-black text-gray-400 dark:text-gray-600 uppercase tracking-widest">نطاق السعر</h3>
                  <div class="flex items-center gap-2">
                    <div class="flex-1">
                       <p class="text-[9px] font-bold text-gray-400 mb-1">من</p>
                       <input v-model="filterForm.min" type="number" @change="applyFilters" class="w-full bg-gray-50 dark:bg-night-800 border-none rounded-xl py-2.5 px-3 text-xs font-black text-gray-900 dark:text-white focus:ring-2 focus:ring-brand/15" placeholder="0" />
                    </div>
                    <div class="h-px w-3 bg-gray-200 dark:bg-night-700 mt-5 flex-shrink-0"></div>
                    <div class="flex-1">
                       <p class="text-[9px] font-bold text-gray-400 mb-1">إلى</p>
                       <input v-model="filterForm.max" type="number" @change="applyFilters" class="w-full bg-gray-50 dark:bg-night-800 border-none rounded-xl py-2.5 px-3 text-xs font-black text-gray-900 dark:text-white focus:ring-2 focus:ring-brand/15" placeholder="∞" />
                    </div>
                  </div>
                </div>
              </div>

              <!-- B2B Promo Card -->
              <div class="bg-gray-900 dark:bg-brand rounded-2xl p-6 text-white dark:text-night-950 relative overflow-hidden shadow-lg">
                 <div class="relative z-10">
                    <h4 class="text-base font-black mb-2">طلبات الجملة B2B</h4>
                    <p class="text-xs font-medium opacity-70 mb-5 leading-relaxed">كميات كبيرة لمؤسستك؟ انضم لشبكة موزعينا.</p>
                    <Link href="/wholesale-request" class="bg-white/15 hover:bg-white/25 px-4 py-2.5 rounded-xl text-[9px] font-black uppercase tracking-widest transition-all block text-center">تقديم طلب</Link>
                 </div>
              </div>
            </div>
          </aside>

          <!-- Main Product Grid -->
          <main class="flex-grow min-w-0">

            <!-- Grid Header -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
               <div class="flex items-center gap-2 flex-wrap">
                  <div v-if="hasFilters()" class="flex flex-wrap gap-2">
                     <button v-if="filterForm.q" @click="removeFilter('q')" class="bg-brand/8 border border-brand/15 px-3 py-1.5 rounded-full text-xs font-black text-brand dark:text-accent flex items-center gap-1.5 hover:bg-red-50 dark:hover:bg-red-500/10 hover:text-red-500 hover:border-red-200 transition-all">
                        "{{ filterForm.q }}" <X class="w-3 h-3" />
                     </button>
                  </div>
                  <p v-if="!hasFilters()" class="text-xs font-bold text-gray-400 dark:text-gray-500">عرض جميع المنتجات</p>
               </div>

               <div class="relative group w-full md:w-auto">
                 <div class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"><SlidersHorizontal class="w-3.5 h-3.5" /></div>
                 <select
                   v-model="filterForm.sort"
                   @change="applyFilters"
                   class="w-full md:w-40 bg-white dark:bg-night-900 border border-gray-100 dark:border-night-800 rounded-xl py-2.5 pr-9 pl-8 text-[10px] font-black text-gray-900 dark:text-white focus:ring-2 focus:ring-brand/15 transition-all cursor-pointer appearance-none shadow-sm"
                 >
                   <option value="latest">الأحدث</option>
                   <option value="price_asc">السعر: الأقل</option>
                   <option value="price_desc">السعر: الأعلى</option>
                 </select>
                 <ChevronDown class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-300 pointer-events-none" />
               </div>
            </div>

            <!-- Product Grid -->
            <div v-if="products.data.length" class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4 md:gap-5 mb-12 animate-in fade-in duration-500">
               <ProductCard
                 v-for="product in products.data"
                 :key="product.id"
                 :product="product"
                 :settings="s"
                 @addToCart="addToCart"
                 @quickView="quickView"
               />
            </div>

            <!-- Empty State -->
            <div v-else class="bg-white dark:bg-night-900 rounded-[2rem] p-16 md:p-24 text-center border border-gray-100 dark:border-night-800">
              <div class="w-16 h-16 mx-auto mb-6 bg-gray-50 dark:bg-night-800 rounded-2xl flex items-center justify-center text-gray-200 dark:text-gray-700">
                <Search class="w-8 h-8" />
              </div>
              <h3 class="text-xl font-black text-gray-900 dark:text-white mb-2">لا توجد نتائج مطابقة</h3>
              <p class="text-sm text-gray-500 dark:text-gray-400 font-medium mb-8 max-w-xs mx-auto leading-relaxed">جرب تغيير الفلاتر أو الكلمات البحثية.</p>
              <button @click="resetFilters"
                class="px-8 py-3 bg-gray-900 dark:bg-brand text-white dark:text-night-950 rounded-2xl font-black text-xs uppercase tracking-widest hover:scale-105 active:scale-95 transition-all shadow-lg">
                عرض جميع المنتجات
              </button>
            </div>

            <!-- Pagination -->
            <div class="flex justify-center mt-10 pb-10">
               <Pagination :links="products.links" />
            </div>
          </main>
        </div>
      </div>
    </div>

    <!-- Quick View Modal -->
    <QuickViewModal
      :isOpen="isQuickViewOpen"
      :product="selectedQuickViewProduct"
      @close="isQuickViewOpen = false"
    />
  </StoreLayout>
</template>

<style scoped>
select { -webkit-appearance: none; -moz-appearance: none; appearance: none; }
.custom-scrollbar::-webkit-scrollbar { width: 3px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #04724D22; border-radius: 10px; }
</style>
