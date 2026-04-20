<script setup>
import { computed, ref, watch } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { useCart } from '@/Composables/useCart';

const props = defineProps({
  isOpen: Boolean,
  product: Object,
});

const emit = defineEmits(['close']);
const { addToCart } = useCart();
const s = computed(() => usePage().props.settings || {});

const formatPrice = (price) => {
  return Number(price).toLocaleString('ar-EG', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });
};

const selectedQuantity = ref(1);
const variants = computed(() => props.product?.variants || []);
const selectedVariant = ref(null);

watch(() => props.product, (newVal) => {
  if (newVal && newVal.variants && newVal.variants.length > 0) {
    selectedVariant.value = newVal.variants[0];
  } else {
    selectedVariant.value = null;
  }
  selectedQuantity.value = 1;
}, { immediate: true });

const mainImage = computed(() => {
  if (!props.product) return '/images/products/no-image.png';
  if (selectedVariant.value && selectedVariant.value.image) {
    return `/images/products/${selectedVariant.value.image}`;
  }
  return props.product.image ? `/images/products/${props.product.image}` : '/images/products/no-image.png';
});

const currentDisplayPrice = computed(() => {
  if (!props.product) return 0;
  if (selectedVariant.value) {
    return parseFloat(selectedVariant.value.price) || props.product.display_price;
  }
  return props.product.display_price;
});

const handleAddToCart = () => {
  if (!props.product) return;
  const itemToAdd = { ...props.product, display_price: currentDisplayPrice.value };
  if (selectedVariant.value) {
    itemToAdd.variant_id = selectedVariant.value.id;
    itemToAdd.variant_name = selectedVariant.value.name;
    itemToAdd.variant_price = selectedVariant.value.price;
  }
  for (let i = 0; i < selectedQuantity.value; i++) { addToCart(itemToAdd); }
  emit('close');
  window.dispatchEvent(new CustomEvent('open-cart'));
};
</script>

<template>
  <div v-show="isOpen" class="relative z-[60]" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <!-- Backdrop -->
    <transition
      enter-active-class="ease-out duration-300"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="ease-in duration-200"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div v-if="isOpen" class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm transition-opacity" @click="$emit('close')"></div>
    </transition>

    <div class="fixed inset-0 z-[60] w-screen overflow-y-auto">
      <div class="flex min-h-full items-center justify-center p-4 sm:p-0">
        <transition
          enter-active-class="ease-out duration-300 transform"
          enter-from-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
          enter-to-class="opacity-100 translate-y-0 sm:scale-100"
          leave-active-class="ease-in duration-200 transform"
          leave-from-class="opacity-100 translate-y-0 sm:scale-100"
          leave-to-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        >
          <div v-if="isOpen && product" class="relative transform overflow-hidden rounded-[2.5rem] bg-white dark:bg-night-900 text-right shadow-2xl transition-all w-full max-w-4xl border border-gray-100 dark:border-night-800">

            <!-- Close Button -->
            <button @click="$emit('close')"
              class="absolute top-6 left-6 z-10 p-3 text-gray-400 dark:text-gray-500 hover:text-gray-900 dark:hover:text-white bg-white/90 dark:bg-night-800/90 rounded-[1.25rem] hover:bg-gray-100 dark:hover:bg-night-700 backdrop-blur transition-all shadow-sm border border-gray-100 dark:border-night-700">
              <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>

            <div class="flex flex-col md:flex-row">
              <!-- Image Column -->
              <div class="w-full md:w-1/2 bg-gradient-to-br from-gray-50 to-gray-100 dark:from-night-800 dark:to-night-900 flex items-center justify-center p-12 min-h-[400px] relative">
                <img :src="mainImage" :alt="product.name"
                  class="max-h-[420px] object-contain drop-shadow-2xl rounded-3xl transition-transform duration-700 hover:scale-110"
                  onerror="this.src='/images/products/no-image.png'" />
                <div v-if="product.discount" class="absolute top-8 right-8">
                  <span class="bg-red-500 text-white font-black px-4 py-2 rounded-2xl uppercase tracking-tighter text-xs shadow-xl shadow-red-500/20">تخفيض خاص</span>
                </div>
              </div>

              <!-- Content Column -->
              <div class="w-full md:w-1/2 p-10 md:p-14 flex flex-col justify-center bg-white dark:bg-night-900">
                <span class="text-xs font-black uppercase tracking-[0.3em] text-brand dark:text-accent mb-3 block">
                  {{ product.category?.name || 'المنتجات المميزة' }}
                </span>

                <h2 class="text-3xl md:text-4xl font-black text-gray-900 dark:text-white mb-4 leading-tight tracking-tight">
                  <Link :href="`/product/${product.id}`" class="hover:text-brand dark:hover:text-accent transition-colors">{{ product.name }}</Link>
                </h2>

                <!-- Pricing -->
                <div class="flex items-end gap-4 mb-8">
                  <div class="flex flex-col">
                    <span class="text-xs font-bold text-gray-400 dark:text-gray-500 mb-1">السعر الحالي</span>
                    <span class="text-4xl font-black text-brand dark:text-accent tracking-tighter">
                      {{ formatPrice(currentDisplayPrice) }}
                      <span class="text-lg uppercase opacity-60 ml-1">{{ s?.currency_code || 'ج.م' }}</span>
                    </span>
                  </div>
                  <span v-if="product.base_price > currentDisplayPrice" class="text-xl text-gray-300 dark:text-gray-600 line-through font-bold mb-1.5 px-3">
                    {{ formatPrice(product.base_price) }}
                  </span>
                </div>

                <!-- Variant Picker -->
                <div v-if="variants.length > 0" class="mb-10">
                  <h4 class="font-black text-gray-900 dark:text-white mb-3 text-xs uppercase tracking-widest opacity-50">اختر النوع</h4>
                  <div class="flex flex-wrap gap-3">
                    <button
                      v-for="variant in variants" :key="variant.id"
                      @click="selectedVariant = variant"
                      :class="['px-5 py-3 border-2 rounded-2xl font-black text-sm transition-all duration-300', selectedVariant?.id === variant.id ? 'border-brand dark:border-accent bg-brand/5 dark:bg-accent/5 text-brand dark:text-accent shadow-lg shadow-brand/10' : 'border-gray-100 dark:border-night-800 text-gray-500 dark:text-gray-400 hover:border-gray-200 dark:hover:border-night-700 hover:bg-gray-50 dark:hover:bg-white/5']"
                    >
                      {{ variant.name }}
                    </button>
                  </div>
                </div>

                <!-- Quantity + Add to Cart -->
                <div class="flex flex-col sm:flex-row items-stretch gap-4 mt-auto pt-8 border-t border-gray-100 dark:border-night-800">
                  <div class="flex items-center bg-gray-50 dark:bg-night-800 rounded-[1.25rem] h-14 w-full sm:w-36 shrink-0 overflow-hidden border border-gray-100 dark:border-night-700 px-1 shadow-inner">
                    <button @click="selectedQuantity > 1 && selectedQuantity--" class="w-10 h-10 flex items-center justify-center text-gray-400 dark:text-gray-500 hover:text-brand dark:hover:text-accent font-black text-xl transition-all hover:bg-white dark:hover:bg-night-700 rounded-xl shadow-sm">−</button>
                    <input type="number" v-model="selectedQuantity" min="1" class="flex-1 h-full bg-transparent border-none text-center font-black text-gray-900 dark:text-white focus:ring-0 p-0 text-base" />
                    <button @click="selectedQuantity++" class="w-10 h-10 flex items-center justify-center text-gray-400 dark:text-gray-500 hover:text-brand dark:hover:text-accent font-black text-xl transition-all hover:bg-white dark:hover:bg-night-700 rounded-xl shadow-sm">+</button>
                  </div>
                  <button @click="handleAddToCart"
                    class="flex-grow h-14 bg-gray-900 dark:bg-brand hover:bg-brand dark:hover:bg-accent text-white dark:text-gray-900 rounded-[1.25rem] font-extrabold transition-all shadow-2xl shadow-gray-900/20 dark:shadow-brand/20 hover:-translate-y-1 active:translate-y-0 duration-300 flex items-center justify-center gap-3 text-base">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    <span>أضف للسلة الآن</span>
                  </button>
                </div>

                <div class="mt-6 text-center">
                  <Link :href="`/product/${product.id}`"
                    class="text-xs font-black text-gray-300 dark:text-gray-600 hover:text-brand dark:hover:text-accent uppercase tracking-[0.2em] transition-all underline underline-offset-8 decoration-2">
                    عرض كافة المواصفات
                  </Link>
                </div>
              </div>
            </div>
          </div>
        </transition>
      </div>
    </div>
  </div>
</template>
