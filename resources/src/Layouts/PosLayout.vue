<script setup>
import { usePage } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';

const { props } = usePage();
const isDarkMode = ref(localStorage.getItem('pos-dark-mode') === 'true');

const toggleDarkMode = () => {
  isDarkMode.value = !isDarkMode.value;
  localStorage.setItem('pos-dark-mode', isDarkMode.value);
  if (isDarkMode.value) {
    document.documentElement.classList.add('dark');
  } else {
    document.documentElement.classList.remove('dark');
  }
};

onMounted(() => {
  if (isDarkMode.value) {
    document.documentElement.classList.add('dark');
  }
});
</script>

<template>
  <div class="min-h-screen bg-gray-50 dark:bg-[#0A0A0A] text-gray-900 dark:text-gray-100 font-sans antialiased overflow-hidden flex flex-col">
    <!-- POS Minimal Header -->
    <header class="h-16 bg-white dark:bg-[#141414] border-b border-gray-200 dark:border-gray-800 flex items-center justify-between px-4 z-50">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-[#04724D] rounded-xl flex items-center justify-center text-white font-bold text-xl shadow-lg shadow-[#04724D]/20">
          D
        </div>
        <div>
          <h1 class="font-bold text-lg leading-tight tracking-tight">DawPOS <span class="text-[#3EFF8B] ml-1">Terminal</span></h1>
          <p class="text-[10px] text-gray-400 font-medium uppercase tracking-[0.2em]">High Performance Interface</p>
        </div>
      </div>

      <div class="flex items-center gap-4">
        <!-- Status Indicator -->
        <div class="flex items-center gap-2 bg-green-50 dark:bg-green-900/10 px-3 py-1.5 rounded-full border border-green-100 dark:border-green-900/20">
          <div class="w-2 h-2 bg-[#3EFF8B] rounded-full animate-pulse"></div>
          <span class="text-xs font-semibold text-green-700 dark:text-green-400 uppercase tracking-wider">Online</span>
        </div>

        <button @click="toggleDarkMode" class="p-2.5 rounded-xl hover:bg-gray-100 dark:hover:bg-white/5 transition-all duration-300">
          <svg v-if="isDarkMode" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-yellow-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
          <svg v-else xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
        </button>

        <div class="h-8 w-[1px] bg-gray-200 dark:bg-gray-800 hidden sm:block"></div>

        <Link href="/app/dashboard" class="text-sm font-semibold text-gray-500 hover:text-[#04724D] transition-colors flex items-center gap-2">
           <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
           Back to Dashboard
        </Link>
      </div>
    </header>

    <!-- Main Terminal Area -->
    <main class="flex-1 overflow-hidden relative">
      <slot />
    </main>
  </div>
</template>

<style>
/* POS Specific Styles */
.dark body {
  background-color: #0A0A0A;
}

/* Custom Scrollbar for POS */
.pos-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.pos-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.pos-scrollbar::-webkit-scrollbar-thumb {
    background: #04724D44;
    border-radius: 10px;
}
.pos-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #04724D88;
}
</style>
