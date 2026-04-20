<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
  links: Array,
});
</script>

<template>
  <div v-if="links.length > 3">
    <div class="flex flex-wrap gap-2 justify-center">
      <template v-for="(link, k) in links" :key="k">
        <div v-if="link.url === null" 
             class="px-4 py-2 text-gray-400 text-sm border border-gray-100 rounded-xl cursor-not-allowed bg-white" 
             v-html="link.label" />
        <Link v-else 
              :href="link.url" 
              class="px-4 py-2 text-sm border rounded-xl transition-all duration-300" 
              :class="{ 'bg-brand text-white border-brand font-bold shadow-lg shadow-brand/20': link.active, 'bg-white text-gray-700 border-gray-200 hover:border-brand hover:text-brand': !link.active }" 
              v-html="link.label" />
      </template>
    </div>
  </div>
</template>
