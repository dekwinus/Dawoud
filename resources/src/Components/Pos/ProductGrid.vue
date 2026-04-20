<script setup>
import { ShoppingCart, PackageSearch } from 'lucide-vue-next';

const props = defineProps({
  products: Array,
  loading: Boolean
});

const emit = defineEmits(['select']);

const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(value); // This should be dynamic later
};
</script>

<template>
  <div v-if="loading" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
    <div v-for="i in 10" :key="i" class="bg-white dark:bg-[#121212] rounded-2xl p-4 border border-gray-100 dark:border-gray-800 animate-pulse">
        <div class="h-32 bg-gray-100 dark:bg-white/5 rounded-xl mb-3"></div>
        <div class="h-4 bg-gray-100 dark:bg-white/5 rounded w-3/4 mb-2"></div>
        <div class="h-3 bg-gray-100 dark:bg-white/5 rounded w-1/2"></div>
    </div>
  </div>

  <div v-else-if="products.length === 0" class="h-full flex flex-col items-center justify-center text-gray-400 py-20">
      <PackageSearch class="h-16 w-16 mb-4 opacity-20" />
      <p class="text-lg font-medium">No products found</p>
      <p class="text-sm">Try adjusting your filters or search query</p>
  </div>

  <div v-else class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
    <button
      v-for="product in products"
      :key="product.id"
      @click="emit('select', product)"
      class="group bg-white dark:bg-[#121212] rounded-2xl p-3 border border-gray-100 dark:border-gray-800 hover:border-[#3EFF8B]/30 hover:shadow-xl hover:shadow-[#04724D]/5 transition-all duration-300 relative overflow-hidden flex flex-col text-left"
    >
      <!-- Stock Badge -->
      <div 
        class="absolute top-2 right-2 px-2 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wider z-10"
        :class="product.qte > 5 ? 'bg-green-100 text-green-600 dark:bg-green-900/20 dark:text-green-400' : 'bg-red-100 text-red-600 dark:bg-red-900/20 dark:text-red-400'"
      >
        {{ product.qte }} in stock
      </div>

      <!-- Image -->
      <div class="h-32 bg-gray-50 dark:bg-white/5 rounded-xl mb-3 overflow-hidden flex items-center justify-center relative">
        <img 
            v-if="product.image" 
            :src="'/images/products/' + product.image" 
            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
        />
        <div v-else class="text-gray-300 dark:text-gray-700">
            <ShoppingCart class="w-8 h-8" />
        </div>
        
        <!-- Hover Overlay -->
        <div class="absolute inset-0 bg-[#04724D]/60 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
             <span class="text-white text-xs font-bold uppercase tracking-widest">Add to Cart</span>
        </div>
      </div>

      <!-- Info -->
      <div class="flex-1">
          <h3 class="text-xs font-bold text-gray-800 dark:text-gray-200 line-clamp-2 leading-snug mb-1">{{ product.name }}</h3>
          <p class="text-[10px] text-gray-400 font-medium mb-2">{{ product.code }}</p>
      </div>

      <div class="flex items-center justify-between mt-auto pt-2 border-t border-gray-50 dark:border-gray-800">
          <span class="text-sm font-black text-[#04724D] dark:text-[#3EFF8B]">{{ formatCurrency(product.Net_price || product.price) }}</span>
          <div class="w-6 h-6 rounded-lg bg-gray-100 dark:bg-white/5 flex items-center justify-center group-hover:bg-[#04724D] group-hover:text-white transition-all">
              <span class="text-xs font-bold">+</span>
          </div>
      </div>
    </button>
  </div>
</template>
