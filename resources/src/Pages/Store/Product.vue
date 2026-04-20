<script setup>
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import StoreLayout from '@/Layouts/StoreLayout.vue';
import { useCart } from '@/Composables/useCart';
import {
  ChevronRight,
  ShoppingCart,
  Check,
  Minus,
  Plus,
  ArrowRight,
  ShieldCheck,
  Truck,
  RotateCcw,
  Tag
} from 'lucide-vue-next';

const props = defineProps({
  s: Object,
  product: Object,
});

const { addToCart } = useCart();

const formatPrice = (price) => {
  return Number(price).toLocaleString('ar-EG', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });
};

const selectedQuantity = ref(1);
const variants = computed(() => props.product.variants || []);
const selectedVariant = ref(variants.value.length > 0 ? variants.value[0] : null);
const selectVariant = (variant) => { selectedVariant.value = variant; };

const mainImage = computed(() => {
  if (selectedVariant.value && selectedVariant.value.image) {
    return `/images/products/${selectedVariant.value.image}`;
  }
  return props.product.image ? `/images/products/${props.product.image}` : '/images/products/no-image.png';
});

const currentDisplayPrice = computed(() => {
  if (selectedVariant.value) {
    return parseFloat(selectedVariant.value.price) || props.product.display_price;
  }
  return props.product.display_price;
});

const added = ref(false);
const handleAddToCart = () => {
  const itemToAdd = {
    ...props.product,
    display_price: currentDisplayPrice.value,
  };
  if (selectedVariant.value) {
    itemToAdd.variant_id = selectedVariant.value.id;
    itemToAdd.variant_name = selectedVariant.value.name;
    itemToAdd.variant_price = selectedVariant.value.price;
  }
  for (let i = 0; i < selectedQuantity.value; i++) {
    addToCart(itemToAdd);
  }
  added.value = true;
  setTimeout(() => { added.value = false; }, 2000);
  window.dispatchEvent(new CustomEvent('open-cart'));
};
</script>

<template>
  <Head :title="`${product.name} | ${s?.store_name || 'المتجر'}`" />

  <StoreLayout>
    <div class="bg-gray-50 dark:bg-night-950 min-h-screen py-8 md:py-14 transition-colors duration-500" dir="rtl">
      <div class="container mx-auto px-4 max-w-7xl">

        <!-- Breadcrumb -->
        <nav class="flex items-center text-xs font-bold mb-8 gap-2 px-1 overflow-x-auto no-scrollbar pb-1 text-gray-400">
          <Link href="/" class="hover:text-brand dark:hover:text-accent transition-colors whitespace-nowrap">الرئيسية</Link>
          <ChevronRight class="w-3 h-3 text-gray-300 dark:text-gray-700 rotate-180 flex-shrink-0" />
          <Link href="/shop" class="hover:text-brand dark:hover:text-accent transition-colors whitespace-nowrap">المتجر</Link>
          <ChevronRight class="w-3 h-3 text-gray-300 dark:text-gray-700 rotate-180 flex-shrink-0" />
          <span class="text-gray-700 dark:text-gray-300 truncate max-w-[180px]">{{ product.name }}</span>
        </nav>

        <div class="grid lg:grid-cols-2 gap-10 lg:gap-16 items-start">

          <!-- ====== IMAGE SECTION ====== -->
          <div class="space-y-4">
            <!-- Main Image -->
            <div class="relative aspect-square bg-white dark:bg-night-900 rounded-[2.5rem] flex items-center justify-center border border-gray-100 dark:border-night-800 shadow-[0_20px_60px_-15px_rgba(0,0,0,0.08)] dark:shadow-[0_20px_60px_-15px_rgba(0,0,0,0.5)] overflow-hidden group">
              <div class="absolute inset-0 bg-gradient-to-br from-brand/4 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-700"></div>

              <img
                :src="mainImage"
                :alt="product.name"
                class="relative w-full h-full object-contain p-10 transition-all duration-700 group-hover:scale-105 drop-shadow-[0_8px_24px_rgba(0,0,0,0.08)]"
                onerror="this.src='/images/products/no-image.png'"
              />

              <div v-if="product.discount" class="absolute top-5 right-5">
                <div class="bg-red-500 text-white font-black px-4 py-1.5 rounded-2xl uppercase tracking-widest text-[10px] shadow-lg shadow-red-500/30 flex items-center gap-1.5">
                   <Tag class="w-3 h-3" />
                   تخفيض
                </div>
              </div>
            </div>
          </div>

          <!-- ====== CONTENT SECTION ====== -->
          <div class="flex flex-col pt-2" dir="rtl">

            <!-- Category & Code -->
            <div class="flex items-center justify-between mb-5">
              <span class="text-[10px] font-black uppercase tracking-[0.25em] text-brand dark:text-accent bg-brand/8 border border-brand/15 px-4 py-1.5 rounded-full">
                {{ product.category?.name || 'المنتجات المميزة' }}
              </span>
              <span class="text-[10px] font-bold text-gray-400 dark:text-gray-600 tracking-widest">#{{ product.id }}</span>
            </div>

            <!-- Product Title -->
            <h1 class="text-2xl md:text-3xl font-black text-gray-900 dark:text-white leading-tight mb-5 tracking-tight">
              {{ product.name }}
            </h1>

            <!-- Price -->
            <div class="flex flex-col mb-6 pb-6 border-b border-gray-100 dark:border-night-800">
              <div class="flex items-end gap-3">
                <span class="text-3xl font-black text-brand dark:text-accent tracking-tighter tabular-nums leading-none">
                  {{ formatPrice(currentDisplayPrice) }}
                  <span class="text-sm opacity-50 mr-1 font-bold">{{ s?.currency_code || 'ج.م' }}</span>
                </span>
                <span v-if="product.base_price > currentDisplayPrice" class="text-base text-gray-300 dark:text-gray-700 line-through font-bold mb-1">
                  {{ formatPrice(product.base_price) }}
                </span>
              </div>
              <p class="text-[9px] font-bold text-gray-400 dark:text-gray-600 mt-2">شامل ضريبة القيمة المضافة · شحن مجاني لكافة محافظات مصر</p>
            </div>

            <!-- Variant Picker -->
            <div v-if="variants.length > 0" class="mb-8">
              <h4 class="font-black text-gray-500 dark:text-gray-500 mb-4 text-[10px] uppercase tracking-widest">اختر النوع المفضل</h4>
              <div class="flex flex-wrap gap-2.5">
                <button
                  v-for="variant in variants"
                  :key="variant.id"
                  @click="selectVariant(variant)"
                  :class="[
                    'px-5 py-2.5 border-2 rounded-xl font-black transition-all duration-300 text-xs flex items-center gap-2',
                    selectedVariant?.id === variant.id
                      ? 'border-brand dark:border-accent bg-brand/8 dark:bg-accent/8 text-brand dark:text-accent ring-4 ring-brand/8'
                      : 'border-gray-100 dark:border-night-800 text-gray-400 dark:text-gray-600 hover:border-brand/40 dark:hover:border-accent/40'
                  ]"
                >
                  <div class="w-1.5 h-1.5 rounded-full" :class="selectedVariant?.id === variant.id ? 'bg-brand dark:bg-accent' : 'bg-gray-300 dark:bg-gray-700'"></div>
                  {{ variant.name }}
                </button>
              </div>
            </div>

            <!-- Description -->
            <div v-if="product.note" class="mb-8">
              <h4 class="font-black text-gray-400 mb-3 text-[10px] uppercase tracking-widest">نظرة عامة</h4>
              <div class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed" v-html="product.note"></div>
            </div>

            <!-- Trust Markers — Horizontal Row -->
            <div class="flex items-center gap-6 mb-6 pb-6 border-b border-gray-100 dark:border-night-800 flex-wrap">
               <div class="flex items-center gap-2">
                 <Truck class="w-3.5 h-3.5 text-brand dark:text-accent flex-shrink-0" />
                 <div>
                   <p class="text-[9px] font-black text-gray-900 dark:text-white leading-none">توصيل سريع</p>
                   <p class="text-[8px] text-gray-400 font-bold">للمزارع والشركات</p>
                 </div>
               </div>
               <div class="w-px h-6 bg-gray-100 dark:bg-night-800 hidden sm:block"></div>
               <div class="flex items-center gap-2">
                 <ShieldCheck class="w-3.5 h-3.5 text-brand dark:text-accent flex-shrink-0" />
                 <div>
                   <p class="text-[9px] font-black text-gray-900 dark:text-white leading-none">جودة معتمدة</p>
                   <p class="text-[8px] text-gray-400 font-bold">شهادة مطابقة</p>
                 </div>
               </div>
               <div class="w-px h-6 bg-gray-100 dark:bg-night-800 hidden sm:block"></div>
               <div class="flex items-center gap-2">
                 <RotateCcw class="w-3.5 h-3.5 text-brand dark:text-accent flex-shrink-0" />
                 <div>
                   <p class="text-[9px] font-black text-gray-900 dark:text-white leading-none">إرجاع خلال 14 يوم</p>
                   <p class="text-[8px] text-gray-400 font-bold">بشروط التخزين</p>
                 </div>
               </div>
            </div>

            <!-- Add to Cart -->
            <div class="flex items-stretch gap-4">
              <!-- Qty Stepper -->
              <div class="flex items-center bg-gray-50 dark:bg-night-800 rounded-lg h-12 overflow-hidden border border-gray-100 dark:border-night-700 shadow-inner px-1 shrink-0">
                <button @click="selectedQuantity > 1 && selectedQuantity--" class="w-8 h-8 flex items-center justify-center text-gray-400 hover:text-brand transition-all hover:bg-white dark:hover:bg-night-700 rounded-lg text-base font-bold">−</button>
                <input type="number" v-model="selectedQuantity" min="1" class="w-8 h-full bg-transparent border-none text-center font-black text-gray-900 dark:text-white focus:ring-0 p-0 text-sm tabular-nums" />
                <button @click="selectedQuantity++" class="w-8 h-8 flex items-center justify-center text-gray-400 hover:text-brand transition-all hover:bg-white dark:hover:bg-night-700 rounded-lg text-base font-bold">+</button>
              </div>

              <!-- CTA Button -->
              <button
                @click="handleAddToCart"
                :class="[
                  'flex-grow h-12 rounded-lg font-black text-sm transition-all shadow-lg duration-400 flex items-center justify-center gap-3 group overflow-hidden relative',
                  added
                    ? 'bg-brand text-white shadow-brand/30 scale-[1.01]'
                    : 'bg-gray-900 dark:bg-brand hover:bg-brand dark:hover:bg-accent text-white dark:text-gray-900 hover:-translate-y-0.5 hover:shadow-xl hover:shadow-brand/20'
                ]"
              >
                <div v-if="!added" class="absolute inset-0 bg-white/8 -translate-x-full group-hover:translate-x-full transition-transform duration-700"></div>
                <ShoppingCart v-if="!added" class="w-5 h-5 relative z-10" />
                <Check v-else class="w-5 h-5 relative z-10" />
                <span class="relative z-10">{{ added ? 'تمت الإضافة!' : 'أضف للسلة' }}</span>
              </button>
            </div>

            <!-- B2B Link -->
            <div class="mt-6 text-right">
               <Link href="/wholesale-request" class="inline-flex items-center gap-1.5 text-[10px] font-black text-gray-400 hover:text-brand dark:hover:text-accent transition-all group">
                 <span>هل تشتري كميات كبيرة؟ اطلب عرض سعر B2B</span>
                 <ArrowRight class="w-3 h-3 group-hover:-translate-x-0.5 transition-transform rotate-180" />
               </Link>
            </div>

          </div>
        </div>
      </div>
    </div>
  </StoreLayout>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
