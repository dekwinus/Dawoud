<script setup>
import { ref, watch } from 'vue';
import { Search, MapPin, Grid, Layers, XCircle } from 'lucide-vue-next';

const props = defineProps({
  search: String,
  warehouses: Array,
  warehouseId: Number,
  categories: Array,
  brands: Array
});

const emit = defineEmits(['update:search', 'update:warehouseId', 'filter']);

const selectedCategory = ref(null);
const selectedBrand = ref(null);

const onSearch = (e) => {
  emit('update:search', e.target.value);
  emit('filter', { 
    search: e.target.value, 
    category_id: selectedCategory.value, 
    brand_id: selectedBrand.value 
  });
};

const onWarehouseChange = (e) => {
  emit('update:warehouseId', parseInt(e.target.value));
  emit('filter', { 
    search: props.search, 
    category_id: selectedCategory.value, 
    brand_id: selectedBrand.value 
  });
};

const onFilterChange = () => {
  emit('filter', { 
    search: props.search, 
    category_id: selectedCategory.value, 
    brand_id: selectedBrand.value 
  });
};

const resetFilters = () => {
    selectedCategory.value = null;
    selectedBrand.value = null;
    emit('update:search', '');
    emit('filter', {});
};
</script>

<template>
  <div class="bg-white dark:bg-[#121212] border-b border-gray-200 dark:border-gray-800 p-4 sticky top-0 z-10 space-y-4">
    <div class="flex items-center gap-4">
      <!-- Search Input -->
      <div class="flex-1 relative group">
        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
          <Search class="h-4 w-4 text-gray-400 group-focus-within:text-[#04724D] transition-colors" />
        </div>
        <input
          type="text"
          :value="search"
          @input="onSearch"
          placeholder="Search products by code or name (Alt + Q)"
          class="block w-full pl-10 pr-4 py-2.5 bg-gray-50 dark:bg-white/5 border-none rounded-xl text-sm focus:ring-2 focus:ring-[#04724D]/20 transition-all placeholder:text-gray-400 dark:placeholder:text-gray-600"
        />
      </div>

      <!-- Warehouse Selection -->
      <div class="flex items-center gap-2 bg-gray-50 dark:bg-white/5 px-3 py-1.5 rounded-xl border border-transparent focus-within:border-[#04724D]/30 transition-all">
        <MapPin class="h-4 w-4 text-gray-400" />
        <select
          :value="warehouseId"
          @change="onWarehouseChange"
          class="bg-transparent border-none text-xs font-semibold focus:ring-0 cursor-pointer text-gray-700 dark:text-gray-300 min-w-[140px]"
        >
          <option v-for="wh in warehouses" :key="wh.id" :value="wh.id">{{ wh.name }}</option>
        </select>
      </div>
    </div>

    <!-- Rapid Filter Bars -->
    <div class="flex items-center justify-between gap-3 overflow-x-auto pb-1 no-scrollbar">
      <div class="flex items-center gap-3">
        <!-- Categories -->
        <div class="flex items-center gap-2">
            <Grid class="h-3.5 w-3.5 text-gray-400" />
            <select
                v-model="selectedCategory"
                @change="onFilterChange"
                class="bg-gray-50 dark:bg-white/5 border-none rounded-lg text-[10px] font-bold uppercase tracking-wider focus:ring-1 focus:ring-[#04724D]/20 py-1 pl-2 pr-8"
            >
                <option :value="null">All Categories</option>
                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
            </select>
        </div>

        <!-- Brands -->
        <div class="flex items-center gap-2 border-l border-gray-200 dark:border-gray-800 pl-3">
            <Layers class="h-3.5 w-3.5 text-gray-400" />
            <select
                v-model="selectedBrand"
                @change="onFilterChange"
                class="bg-gray-50 dark:bg-white/5 border-none rounded-lg text-[10px] font-bold uppercase tracking-wider focus:ring-1 focus:ring-[#04724D]/20 py-1 pl-2 pr-8"
            >
                <option :value="null">All Brands</option>
                <option v-for="brand in brands" :key="brand.id" :value="brand.id">{{ brand.name }}</option>
            </select>
        </div>
      </div>

      <button 
        @click="resetFilters"
        class="flex items-center gap-1.5 px-3 py-1 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/10 text-red-500 transition-all duration-300 group"
      >
        <XCircle class="h-3.5 w-3.5 group-hover:rotate-90 transition-transform duration-500" />
        <span class="text-[10px] font-bold uppercase tracking-wider">Clear Filters</span>
      </button>
    </div>
  </div>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
