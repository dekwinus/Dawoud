<script setup>
import { onMounted } from 'vue';
import Sidebar from '@/Components/Admin/Sidebar.vue';
import TopNav from '@/Components/Admin/TopNav.vue';
import { usePage } from '@inertiajs/vue3';

const { props } = usePage();

const layoutProps = defineProps({
  hideSidebar: {
    type: Boolean,
    default: false
  },
  font: {
    type: String,
    default: 'Cairo'
  }
});

onMounted(() => {
  if (props.flash.success) {
    console.log('Success:', props.flash.success);
  }
  if (props.flash.error) {
    console.error('Error:', props.flash.error);
  }
});
</script>

<template>
  <div 
    class="antialiased text-gray-900 bg-gray-50 flex h-screen overflow-hidden dark:bg-[#0A0A0A]" 
    dir="rtl" 
    lang="ar"
    :style="{ fontFamily: layoutProps.font + ', sans-serif' }"
  >
    <!-- Sidebar Component -->
    <Sidebar v-if="!layoutProps.hideSidebar" />

    <!-- Main Content Wrapper -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden relative">
      <!-- Top Navigation -->
      <TopNav />

      <!-- Content Area -->
      <div class="flex-1 overflow-y-auto custom-scrollbar flex flex-col">
        <main class="flex-1">
          <div class="p-4 sm:p-6 lg:p-8 max-w-[1920px] mx-auto">
            <!-- Page Header Slot -->
            <div v-if="$slots.header" class="mb-8">
              <slot name="header" />
            </div>

            <!-- Main Content Slot -->
            <transition
              enter-active-class="transition duration-500 ease-out"
              enter-from-class="opacity-0 translate-y-6"
              enter-to-class="opacity-100 translate-y-0"
              leave-active-class="transition duration-300 ease-in"
              leave-from-class="opacity-100 translate-y-0"
              leave-to-class="opacity-0 translate-y-6"
            >
              <div :key="$page.url">
                <slot />
              </div>
            </transition>
          </div>
        </main>

        <!-- Footer -->
        <footer class="py-10 px-12 text-center text-[10px] font-black text-gray-400 dark:text-gray-600 uppercase tracking-[0.4em] border-t border-gray-100 dark:border-gray-800 bg-white/50 dark:bg-transparent backdrop-blur-md">
           &copy; {{ new Date().getFullYear() }} {{ $page.props.settings?.CompanyName || 'DawPOS' }} 
           <span class="mx-3 opacity-30">|</span> 
           V5.4.2 ELITE PROTOCOL 
           <span class="mx-3 opacity-30">|</span> 
           DEVELOPED BY {{ $page.props.settings?.developed_by || 'DEKWIN' }}
        </footer>
      </div>
    </div>
  </div>
</template>

<style>
@import url('https://fonts.googleapis.com/css2?family=Cairo:wght@200;300;400;500;600;700;800;900;1000&display=swap');

/* Global Layout Styles */
.custom-scrollbar::-webkit-scrollbar {
  width: 5px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #04724D11;
  border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #04724D33;
}

body {
  @apply bg-gray-50 dark:bg-[#0A0A0A];
}

html,
body {
  color-scheme: light dark;
}

html.dark select,
html.dark input,
html.dark textarea {
  background-color: #111827;
  color: #e5e7eb;
  border-color: #374151;
}

html.dark option,
html.dark optgroup {
  background-color: #111827;
  color: #e5e7eb;
}

html.dark table thead th {
  color: #9ca3af;
}

html.dark table tbody td,
html.dark table tbody th {
  color: #e5e7eb;
}

/* Typography Overrides for Arab/RTL */
[dir="rtl"] {
  text-align: right;
}

/* Page Transitions */
.page-enter-active, .page-leave-active {
  transition: opacity 0.5s cubic-bezier(0.4, 0, 0.2, 1), transform 0.5s cubic-bezier(0.4, 0, 0.2, 1);
}
.page-enter-from, .page-leave-to {
  opacity: 0;
  transform: translateY(20px);
}
</style>
