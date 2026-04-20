<template>
  <div class="min-h-screen bg-gray-50 dark:bg-night-950 flex flex-col transition-colors duration-300" dir="rtl" lang="ar">

    <!-- Announcement Bar -->
    <div class="bg-gradient-to-l from-brand to-accent text-white py-2 text-xs font-bold">
      <div class="container mx-auto px-4 flex justify-between items-center">
        <div class="flex items-center gap-2 opacity-90">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
          {{ settings.topbar_text_left || 'مرحباً بك في متجرنا' }}
        </div>
        <div class="hidden md:flex items-center gap-2">
          <span class="bg-white/15 backdrop-blur px-3 py-0.5 rounded-full border border-white/20 tracking-wide">
            {{ settings.topbar_text_right || 'خدمة عملاء 24/7' }}
          </span>
        </div>
      </div>
    </div>

    <!-- Navbar -->
    <nav
      class="bg-white/80 dark:bg-night-900/80 backdrop-blur-xl border-b border-gray-100/80 dark:border-night-800/80 sticky top-0 z-50 transition-all duration-300"
      :class="scrolled ? 'shadow-[0_4px_30px_rgba(0,0,0,0.06)]' : ''"
    >
      <div class="container mx-auto px-4 h-16 flex items-center justify-between gap-6">

        <!-- Logo -->
        <Link href="/" class="flex items-center gap-2.5 font-black text-lg text-gray-900 dark:text-white hover:text-brand dark:hover:text-accent transition-colors flex-shrink-0">
          <div v-if="settings.logo_path" class="h-9 w-9 rounded-xl overflow-hidden flex-shrink-0 shadow-sm border border-gray-100 dark:border-night-800">
            <img :src="`/${settings.logo_path}`" alt="شعار المتجر" class="h-full w-full object-contain" />
          </div>
          <div v-else class="h-9 w-9 rounded-xl bg-gradient-to-br from-brand to-accent flex items-center justify-center flex-shrink-0 shadow-md">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
          </div>
          <span class="tracking-tight leading-none">{{ settings.store_name || 'المتجر' }}</span>
        </Link>

        <!-- Desktop Nav Links -->
        <div class="hidden lg:flex items-center gap-1 font-semibold text-sm text-gray-600 dark:text-gray-300">
          <Link href="/" class="px-4 py-2 rounded-xl hover:bg-gray-100 dark:hover:bg-night-800 hover:text-brand dark:hover:text-accent transition-all">الرئيسية</Link>
          <Link href="/shop" class="px-4 py-2 rounded-xl hover:bg-gray-100 dark:hover:bg-night-800 hover:text-brand dark:hover:text-accent transition-all">المتجر</Link>
          <Link href="/chatbot" class="px-4 py-2 rounded-xl hover:bg-gray-100 dark:hover:bg-night-800 hover:text-brand dark:hover:text-accent transition-all">المساعد الذكي</Link>
          <Link href="/contact" class="px-4 py-2 rounded-xl hover:bg-gray-100 dark:hover:bg-night-800 hover:text-brand dark:hover:text-accent transition-all">تواصل معنا</Link>
          <Link href="/wholesale-request" class="px-4 py-2 rounded-xl bg-brand/10 dark:bg-accent/10 text-brand dark:text-accent font-black transition-all hover:bg-brand hover:text-white dark:hover:bg-accent dark:hover:text-night-900 text-xs uppercase tracking-wider">مبيعات الجملة</Link>
        </div>

        <!-- Right Actions -->
        <div class="flex items-center gap-1.5">
          <!-- Theme Toggle -->
          <button @click="toggleTheme" class="p-2 text-gray-500 dark:text-gray-400 hover:text-brand dark:hover:text-accent hover:bg-gray-100 dark:hover:bg-night-800 rounded-xl transition-all">
            <svg v-if="isDark" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
            <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" /></svg>
          </button>

          <!-- User -->
          <div v-if="$page.props.auth?.store_user">
            <Link href="/account" class="flex items-center gap-2 hover:bg-gray-100 dark:hover:bg-night-800 p-1.5 rounded-xl transition-all">
              <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-brand to-accent text-white flex items-center justify-center font-black text-sm shadow-md">
                {{ $page.props.auth.store_user.name?.charAt(0).toUpperCase() }}
              </div>
            </Link>
          </div>
          <div v-else class="hidden sm:block">
            <Link href="/customer/login" class="px-4 py-2 bg-gray-900 dark:bg-white text-white dark:text-gray-900 text-xs font-black rounded-xl hover:bg-brand dark:hover:bg-brand dark:hover:text-white transition-all shadow-sm tracking-wide">
              دخول
            </Link>
          </div>

          <!-- Cart -->
          <button @click="isCartOpen = true" class="relative p-2 text-gray-500 dark:text-gray-400 hover:text-brand dark:hover:text-accent hover:bg-gray-100 dark:hover:bg-night-800 rounded-xl transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            <span v-if="cart.items.length > 0"
              class="absolute -top-0.5 -left-0.5 bg-brand text-white text-[9px] min-w-[16px] h-[16px] px-1 rounded-full flex items-center justify-center font-black shadow-sm border border-white dark:border-night-900 leading-none">
              {{ cart.items.length }}
            </span>
          </button>

          <!-- Mobile Hamburger -->
          <button @click="mobileMenuOpen = !mobileMenuOpen" class="lg:hidden p-2 text-gray-500 dark:text-gray-400 hover:text-brand hover:bg-gray-100 dark:hover:bg-night-800 rounded-xl transition-all">
            <svg v-if="!mobileMenuOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
          </button>
        </div>
      </div>

      <!-- Mobile Slide-Down Menu -->
      <transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0 -translate-y-2"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 -translate-y-2"
      >
        <div v-if="mobileMenuOpen" class="lg:hidden border-t border-gray-100 dark:border-night-800 bg-white/95 dark:bg-night-900/95 backdrop-blur-xl">
          <div class="container mx-auto px-4 py-4 flex flex-col gap-1">
            <Link href="/" @click="mobileMenuOpen = false" class="px-4 py-3 rounded-xl text-sm font-bold text-gray-700 dark:text-gray-300 hover:bg-brand/5 hover:text-brand transition-all">الرئيسية</Link>
            <Link href="/shop" @click="mobileMenuOpen = false" class="px-4 py-3 rounded-xl text-sm font-bold text-gray-700 dark:text-gray-300 hover:bg-brand/5 hover:text-brand transition-all">المتجر</Link>
            <Link href="/chatbot" @click="mobileMenuOpen = false" class="px-4 py-3 rounded-xl text-sm font-bold text-gray-700 dark:text-gray-300 hover:bg-brand/5 hover:text-brand transition-all">المساعد الذكي</Link>
            <Link href="/contact" @click="mobileMenuOpen = false" class="px-4 py-3 rounded-xl text-sm font-bold text-gray-700 dark:text-gray-300 hover:bg-brand/5 hover:text-brand transition-all">تواصل معنا</Link>
            <Link href="/wholesale-request" @click="mobileMenuOpen = false" class="px-4 py-3 bg-brand/10 rounded-xl text-sm font-black text-brand transition-all text-center mt-1">مبيعات الجملة B2B</Link>
            <div class="border-t border-gray-100 dark:border-night-800 pt-3 mt-1">
              <Link v-if="!$page.props.auth?.store_user" href="/customer/login" class="block px-4 py-3 bg-gray-900 dark:bg-brand text-white dark:text-night-950 rounded-xl text-sm font-black text-center transition-all">تسجيل الدخول</Link>
            </div>
          </div>
        </div>
      </transition>
    </nav>

    <CartDrawer :isOpen="isCartOpen" @close="isCartOpen = false" />

    <!-- Page Content -->
    <main class="flex-grow">
      <slot />
    </main>

    <!-- Floating Chatbot FAB -->
    <Link href="/chatbot"
      class="fixed bottom-6 left-6 md:bottom-8 md:left-8 z-[40] group flex items-center gap-3"
    >
      <div class="bg-gray-900 dark:bg-white absolute left-full ml-3 px-4 py-2 rounded-2xl text-white dark:text-gray-900 font-bold text-xs whitespace-nowrap opacity-0 -translate-x-2 group-hover:opacity-100 group-hover:translate-x-0 transition-all duration-300 pointer-events-none shadow-xl border border-white/10 dark:border-night-800">
        هل يمكنني مساعدتك؟ 🤖
      </div>
      <div class="w-12 h-12 bg-brand text-white rounded-2xl flex items-center justify-center shadow-xl shadow-brand/30 hover:scale-110 active:scale-95 transition-all duration-300 border-b-4 border-black/10 relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-tr from-white/20 to-transparent"></div>
        <svg class="w-6 h-6 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
      </div>
    </Link>

    <!-- Footer -->
    <footer class="bg-gray-950 dark:bg-night-950 text-gray-400 dark:text-gray-500 pt-14 pb-8 mt-16 transition-colors duration-300 border-t border-white/5">
      <div class="container mx-auto px-4">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-10 mb-10">

          <!-- Brand -->
          <div class="col-span-2 md:col-span-1">
            <div class="flex items-center gap-2.5 mb-4">
              <div class="h-9 w-9 rounded-xl bg-gradient-to-br from-brand to-accent flex items-center justify-center shadow-lg flex-shrink-0">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
              </div>
              <span class="text-base font-black text-white tracking-tight">{{ settings.store_name || 'المتجر' }}</span>
            </div>
            <p class="text-xs leading-relaxed opacity-60 font-medium">{{ settings.footer_text || 'نوفر لك أفضل المنتجات بأعلى جودة وأسرع توصيل.' }}</p>
          </div>

          <!-- Shop -->
          <div>
            <h6 class="font-black text-white/80 mb-4 text-xs uppercase tracking-widest">التسوق</h6>
            <ul class="space-y-2.5 text-xs">
              <li><Link href="/shop" class="hover:text-brand transition-colors opacity-70 hover:opacity-100">جميع المنتجات</Link></li>
              <li><Link href="/shop" class="hover:text-brand transition-colors opacity-70 hover:opacity-100">العروض والتخفيضات</Link></li>
              <li><Link href="/wholesale-request" class="hover:text-brand transition-colors font-black text-brand dark:text-accent">طلب أسعار الجملة B2B</Link></li>
            </ul>
          </div>

          <!-- Account -->
          <div>
            <h6 class="font-black text-white/80 mb-4 text-xs uppercase tracking-widest">حسابي</h6>
            <ul class="space-y-2.5 text-xs">
              <li><Link href="/customer/login" class="hover:text-brand transition-colors opacity-70 hover:opacity-100">تسجيل الدخول</Link></li>
              <li><Link href="/account/orders" class="hover:text-brand transition-colors opacity-70 hover:opacity-100">طلباتي</Link></li>
            </ul>
          </div>

          <!-- Support -->
          <div>
            <h6 class="font-black text-white/80 mb-4 text-xs uppercase tracking-widest">الدعم</h6>
            <ul class="space-y-2.5 text-xs">
              <li><Link href="/contact" class="hover:text-brand transition-colors opacity-70 hover:opacity-100">تواصل معنا</Link></li>
              <li><Link href="/chatbot" class="hover:text-brand transition-colors opacity-70 hover:opacity-100">المساعد الذكي</Link></li>
            </ul>
          </div>
        </div>

        <!-- Bottom Bar -->
        <div class="border-t border-white/5 pt-6 flex flex-col sm:flex-row items-center justify-between gap-3">
          <p class="text-[10px] opacity-40 font-medium">© {{ new Date().getFullYear() }} {{ settings.store_name || 'المتجر' }}. جميع الحقوق محفوظة.</p>
          <div class="flex items-center gap-2 text-[10px] opacity-40">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            دفع آمن ومشفر
          </div>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref, onMounted, onUnmounted } from 'vue';
import { useCart } from '@/Composables/useCart';
import CartDrawer from '@/Components/Store/CartDrawer.vue';

const page = usePage();
const settings = computed(() => page.props.settings || page.props.s || {});

const { cart, loadCart } = useCart();
const isCartOpen = ref(false);
const isDark = ref(false);
const mobileMenuOpen = ref(false);
const scrolled = ref(false);

const toggleTheme = () => {
  isDark.value = !isDark.value;
  if (isDark.value) {
    document.documentElement.classList.add('dark');
    localStorage.theme = 'dark';
  } else {
    document.documentElement.classList.remove('dark');
    localStorage.theme = 'light';
  }
};

const handleScroll = () => { scrolled.value = window.scrollY > 10; };
const openCartListener = () => { isCartOpen.value = true; };

onMounted(() => {
  loadCart();
  window.addEventListener('open-cart', openCartListener);
  window.addEventListener('scroll', handleScroll, { passive: true });

  if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
    isDark.value = true;
    document.documentElement.classList.add('dark');
  } else {
    isDark.value = false;
    document.documentElement.classList.remove('dark');
  }
});

onUnmounted(() => {
  window.removeEventListener('open-cart', openCartListener);
  window.removeEventListener('scroll', handleScroll);
});
</script>

<style>
@import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&display=swap');

body, .font-sans {
  font-family: 'Cairo', 'Segoe UI', sans-serif;
}
</style>
