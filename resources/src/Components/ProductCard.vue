<script setup>
import { Link } from '@inertiajs/vue3';
import { useCart } from '@/Composables/useCart';
import { ShoppingBag, Eye, Package } from 'lucide-vue-next';

const props = defineProps({
  product: Object,
  settings: Object,
});

const emit = defineEmits(['quickView', 'addToCart']);

const formatPrice = (price) => {
  return Number(price).toLocaleString('ar-EG', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 2,
  });
};
</script>

<template>
  <div class="group relative bg-white dark:bg-night-900 rounded-[1.5rem] overflow-hidden transition-all duration-500 border border-gray-100 dark:border-night-800 hover:border-brand/30 dark:hover:border-accent/30 hover:shadow-[0_20px_40px_-12px_rgba(4,114,77,0.15)] dark:hover:shadow-[0_20px_40px_-12px_rgba(0,0,0,0.6)] flex flex-col h-full font-['Cairo']">

    <!-- Image Area -->
    <div class="relative aspect-square overflow-hidden bg-gray-50/50 dark:bg-night-800/30 shrink-0">
      <Link :href="`/product/${product.id}`" class="w-full h-full block">
        <img
          :src="product.image ? `/${product.image}` : '/images/products/no-image.png'"
          :alt="product.name"
          class="w-full h-full object-contain p-5 group-hover:scale-105 transition-transform duration-700 ease-in-out"
          onerror="this.src='/images/products/no-image.png'"
        />
      </Link>

      <!-- Badges -->
      <div class="absolute top-2.5 right-2.5 flex flex-col gap-1.5 items-end">
        <span v-if="product.discount" class="bg-red-500 text-white text-[8px] font-black px-2 py-0.5 rounded-full uppercase tracking-widest shadow-lg">تخفيض</span>
        <span v-if="product.unit" class="bg-brand/10 dark:bg-accent/10 text-brand dark:text-accent border border-brand/20 dark:border-accent/20 text-[8px] font-black px-2 py-0.5 rounded-full uppercase tracking-tighter shadow-sm">
          {{ product.unit.short_name || product.unit.name }}
        </span>
      </div>

      <!-- Quick View -->
      <div class="absolute top-2.5 left-2.5 opacity-0 group-hover:opacity-100 translate-y-1 group-hover:translate-y-0 transition-all duration-300">
        <button
          @click.prevent="$emit('quickView', product)"
          class="w-8 h-8 flex items-center justify-center bg-white/95 dark:bg-night-800/95 backdrop-blur-sm rounded-lg text-gray-500 dark:text-gray-400 hover:bg-brand hover:text-white shadow-xl transition-all border border-gray-100 dark:border-night-700"
        >
          <Eye class="w-3.5 h-3.5" />
        </button>
      </div>

      <!-- Quick Add to Cart Pill -->
      <div class="absolute inset-x-2.5 bottom-2.5 opacity-0 group-hover:opacity-100 translate-y-2 group-hover:translate-y-0 transition-all duration-400">
        <button
          @click.prevent="$emit('addToCart', product)"
          class="w-full bg-gray-900/90 dark:bg-brand/95 backdrop-blur-md text-white dark:text-night-950 py-2 rounded-xl font-black text-[10px] hover:bg-brand dark:hover:bg-accent transition-all flex items-center justify-center gap-2 shadow-2xl border border-white/5"
        >
          <ShoppingBag class="w-3.5 h-3.5" />
          أضف للسلة
        </button>
      </div>
    </div>

    <!-- Info Section -->
    <div class="p-3.5 flex flex-col flex-grow text-right">
      <div class="flex items-center justify-between gap-2 mb-1">
        <p class="text-[8px] font-black uppercase tracking-[0.2em] text-gray-400 dark:text-gray-600 leading-none">
          {{ product.category?.name || 'زراعي' }}
        </p>
        <div v-if="product.stock_qty <= 5 && product.stock_qty > 0" class="flex items-center gap-1 text-orange-500 text-[8px] font-black">
           <Package class="w-2.5 h-2.5" />
           وشك النفاد
        </div>
      </div>

      <Link :href="`/product/${product.id}`" class="font-black text-xs text-gray-900 dark:text-white group-hover:text-brand dark:group-hover:text-accent transition-colors line-clamp-2 leading-snug min-h-[2.4rem] mb-3">
        {{ product.name }}
      </Link>

      <div class="mt-auto flex items-end justify-between gap-2">
        <span v-if="product.base_price > product.display_price" class="text-[10px] text-gray-300 dark:text-gray-700 line-through font-bold tabular-nums self-end mb-0.5">
          {{ formatPrice(product.base_price) }}
        </span>
        <div class="flex items-baseline gap-1 mr-auto">
          <span class="text-xs font-black text-brand dark:text-accent tracking-tighter tabular-nums">
            {{ formatPrice(product.display_price) }}
          </span>
          <span class="text-[8px] text-brand dark:text-accent font-bold opacity-60">
            {{ settings?.currency_code || 'ج.م' }}
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

