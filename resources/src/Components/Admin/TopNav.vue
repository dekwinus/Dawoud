<script setup>
import { ref, onMounted } from 'vue';
import { usePage, Link, router } from '@inertiajs/vue3';
import { 
  Menu as Bars3Icon, 
  Search as MagnifyingGlassIcon, 
  Bell as BellIcon, 
  CircleUser as UserCircleIcon,
  ChevronDown as ChevronDownIcon,
  Globe as GlobeAltIcon,
  Power as PowerIcon,
  User as UserIcon,
  Settings as CogIcon,
  Layout as DashboardIcon,
  ShoppingCart,
  Maximize2,
  Sun,
  Moon
} from 'lucide-vue-next';
import { Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/vue';

const { props } = usePage();
const user = props.auth.user;
const languages = [
  { name: 'العربية', code: 'ar', flag: '🇪🇬' },
  { name: 'English', code: 'en', flag: '🇺🇸' },
];

const currentLang = ref(languages.find(l => l.code === props.locale) || languages[0]);

// Dark mode
const isDark = ref(false);
onMounted(() => {
  isDark.value = document.documentElement.classList.contains('dark');
});
const toggleDark = () => {
  isDark.value = !isDark.value;
  document.documentElement.classList.toggle('dark', isDark.value);
  localStorage.setItem('theme', isDark.value ? 'dark' : 'light');
};

const toggleFullScreen = () => {
  if (!document.fullscreenElement) {
    document.documentElement.requestFullscreen();
  } else {
    if (document.exitFullscreen) {
      document.exitFullscreen();
    }
  }
};
</script>

<template>
  <header class="h-24 bg-white/80 dark:bg-[#0A0A0A]/80 backdrop-blur-xl border-b border-gray-100 dark:border-gray-800/50 flex items-center justify-between px-10 z-30 sticky top-0 font-['Cairo'] tracking-tight" dir="rtl">
    <!-- Right: Search & Trigger (Arabic-First) -->
    <div class="flex items-center gap-8 flex-1">
      <button class="p-3.5 rounded-2xl bg-gray-50 dark:bg-white/5 hover:bg-white dark:hover:bg-white/10 text-gray-500 transition-all border border-transparent hover:border-gray-100 dark:hover:border-white/10 lg:hidden group">
        <Bars3Icon class="h-6 w-6 group-hover:scale-110 transition-transform" />
      </button>
      
      <div class="relative max-w-lg w-full hidden md:block group">
        <MagnifyingGlassIcon class="absolute right-5 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400 group-focus-within:text-[#04724D] transition-all" />
        <input 
          type="text" 
          placeholder="البحث السريع في النظام (اختصار ⌘K)" 
          class="w-full bg-gray-50/50 dark:bg-white/[0.03] border-gray-100 dark:border-gray-800 focus:border-[#04724D] focus:ring-[#3EFF8B]/10 rounded-2xl py-3.5 pr-14 pl-6 text-sm transition-all focus:bg-white dark:focus:bg-black font-black placeholder:text-gray-400 dark:placeholder:text-gray-600"
        >
      </div>
    </div>

    <!-- Left: Actions -->
    <div class="flex items-center gap-4">
      <!-- Dark Mode Toggle -->
      <button @click="toggleDark" class="hidden sm:flex p-3 rounded-2xl bg-gray-50 dark:bg-white/5 text-gray-500 hover:text-[#04724D] dark:hover:text-[#3EFF8B] transition-all border border-transparent hover:border-gray-100 dark:hover:border-white/10 group" :title="isDark ? 'وضع النهار' : 'الوضع الليلي'">
        <Sun v-if="isDark" class="h-5 w-5 group-hover:scale-110 transition-transform" />
        <Moon v-else class="h-5 w-5 group-hover:scale-110 transition-transform" />
      </button>

      <!-- Fullscreen Toggle -->
      <button @click="toggleFullScreen" class="hidden sm:flex p-3 rounded-2xl bg-gray-50 dark:bg-white/5 text-gray-500 hover:text-[#04724D] transition-all border border-transparent hover:border-gray-100 dark:hover:border-white/10 group">
        <Maximize2 class="h-5 w-5 group-hover:scale-110 transition-transform" />
      </button>

      <!-- POS Quick Link -->
      <Link 
        href="/admin/pos" 
        class="hidden xl:flex items-center gap-3 px-8 py-3.5 bg-[#04724D] hover:bg-[#035a3d] text-white text-sm font-black rounded-2xl shadow-xl shadow-[#04724D]/20 transition-all hover:-translate-y-1 active:scale-95 group"
      >
        <ShoppingCart class="h-5 w-5 text-[#3EFF8B]" />
        نقطة البيع (POS)
      </Link>

      <div class="h-8 w-[1px] bg-gray-200 dark:bg-gray-800 mx-3 hidden sm:block"></div>

      <!-- Language Switcher -->
      <Menu as="div" class="relative">
        <MenuButton class="p-2 px-4 rounded-2xl bg-gray-50 dark:bg-white/5 hover:bg-white dark:hover:bg-white/10 text-gray-600 dark:text-gray-400 transition-all flex items-center gap-3 border border-gray-100 dark:border-gray-800 group">
          <span class="text-xl leading-none grayscale group-hover:grayscale-0 transition-all">{{ currentLang.flag }}</span>
          <span class="text-[10px] font-black uppercase tracking-widest hidden lg:block">{{ currentLang.code }}</span>
          <ChevronDownIcon class="h-4 w-4 text-gray-400 transform group-hover:translate-y-0.5 transition-transform" />
        </MenuButton>
        <transition enter-active-class="transition duration-300 ease-out" enter-from-class="transform scale-95 opacity-0 translate-y-4" enter-to-class="transform scale-100 opacity-100 translate-y-0" leave-active-class="transition duration-150 ease-in" leave-from-class="transform scale-100 opacity-100 translate-y-0" leave-to-class="transform scale-95 opacity-0 translate-y-4">
          <MenuItems class="absolute left-0 mt-4 w-56 origin-top-left divide-y divide-gray-100 dark:divide-gray-800 rounded-3xl bg-white dark:bg-[#121212] shadow-3xl ring-1 ring-black/5 focus:outline-none p-3 space-y-2 border border-gray-100 dark:border-gray-800">
            <MenuItem v-for="lang in languages" :key="lang.code" v-slot="{ active }">
              <a :href="`/lang/${lang.code}`" :class="[active ? 'bg-[#04724D]/10 text-[#04724D] dark:text-[#3EFF8B]' : 'text-gray-700 dark:text-gray-300', 'group flex w-full items-center rounded-2xl px-4 py-3 text-sm font-black transition-all']">
                <span class="ml-4 text-2xl">{{ lang.flag }}</span>
                {{ lang.name }}
              </a>
            </MenuItem>
          </MenuItems>
        </transition>
      </Menu>

      <!-- Notifications -->
      <Link href="/admin/dashboard" class="relative p-3.5 rounded-2xl bg-gray-50 dark:bg-white/5 text-gray-600 dark:text-gray-400 hover:text-[#04724D] transition-all border border-gray-100 dark:border-gray-800 group flex items-center" title="التنبيهات">
        <BellIcon class="h-6 w-6 group-hover:rotate-12 transition-transform" />
      </Link>

      <!-- User Profile -->
      <Menu as="div" class="relative mr-2">
        <MenuButton class="flex items-center gap-4 p-1.5 pl-5 rounded-2xl bg-white dark:bg-[#1A1A1A] hover:shadow-2xl hover:shadow-[#04724D]/10 transition-all border border-gray-100 dark:border-gray-800 group">
          <div class="h-11 w-11 rounded-xl bg-gradient-to-br from-[#04724D] to-[#3EFF8B] p-[2.5px] shadow-lg shadow-[#04724D]/20">
            <div class="h-full w-full rounded-[9px] bg-white dark:bg-[#1A1A1A] flex items-center justify-center overflow-hidden">
               <img v-if="user?.avatar" :src="`/images/avatar/${user.avatar}`" class="h-full w-full object-cover">
               <UserCircleIcon v-else class="h-7 w-7 text-gray-300 dark:text-gray-700" />
            </div>
          </div>
          <div class="hidden sm:block text-right">
            <p class="text-xs font-black text-gray-900 dark:text-white leading-tight tracking-tight">{{ user?.username }}</p>
            <p class="text-[9px] font-black text-[#04724D] dark:text-[#3EFF8B] leading-none uppercase tracking-widest mt-1">مدير النظام (Pro Max)</p>
          </div>
          <ChevronDownIcon class="h-4 w-4 text-gray-400 group-hover:translate-y-0.5 transition-transform" />
        </MenuButton>
        <transition enter-active-class="transition duration-300 ease-out" enter-from-class="transform scale-95 opacity-0 translate-y-4" enter-to-class="transform scale-100 opacity-100 translate-y-0" leave-active-class="transition duration-150 ease-in" leave-from-class="transform scale-100 opacity-100 translate-y-0" leave-to-class="transform scale-95 opacity-0 translate-y-4">
          <MenuItems class="absolute left-0 mt-4 w-72 origin-top-left divide-y divide-gray-100 dark:divide-gray-800 rounded-[32px] bg-white dark:bg-[#121212] shadow-3xl ring-1 ring-black/5 focus:outline-none p-3 border border-gray-100 dark:border-gray-800">
            <div class="p-5 mb-3 bg-[#04724D]/[0.03] dark:bg-white/[0.02] rounded-[24px] border border-[#04724D]/5">
              <p class="text-[10px] font-black text-[#04724D] dark:text-[#3EFF8B] uppercase tracking-[0.3em] mb-2">الحساب النشط</p>
              <p class="text-xs font-black text-gray-900 dark:text-white truncate tabular-nums font-mono">{{ user?.email }}</p>
            </div>
            
            <div class="py-2 space-y-1">
              <MenuItem v-slot="{ active }">
                <Link href="/admin/profile" :class="[active ? 'bg-gray-50 dark:bg-white/5 text-[#04724D]' : 'text-gray-700 dark:text-gray-300', 'flex w-full items-center rounded-2xl px-4 py-3.5 text-sm font-black transition-all group']">
                  <UserIcon class="ml-4 h-5 w-5 opacity-40 group-hover:opacity-100 transition-opacity" />
                  الملف الشخصي
                </Link>
              </MenuItem>
              <MenuItem v-slot="{ active }">
                <Link href="/admin/settings" :class="[active ? 'bg-gray-50 dark:bg-white/5 text-[#04724D]' : 'text-gray-700 dark:text-gray-300', 'flex w-full items-center rounded-2xl px-4 py-3.5 text-sm font-black transition-all group']">
                  <CogIcon class="ml-4 h-5 w-5 opacity-40 group-hover:opacity-100 transition-opacity" />
                  إعدادات النظام
                </Link>
              </MenuItem>
              <MenuItem v-slot="{ active }">
                <Link href="/admin/dashboard" :class="[active ? 'bg-gray-50 dark:bg-white/5 text-[#04724D]' : 'text-gray-700 dark:text-gray-300', 'flex w-full items-center rounded-2xl px-4 py-3.5 text-sm font-black transition-all group']">
                  <DashboardIcon class="ml-4 h-5 w-5 opacity-40 group-hover:opacity-100 transition-opacity" />
                  لوحة التحكم
                </Link>
              </MenuItem>
            </div>

            <div class="pt-2 mt-2 border-t border-gray-100 dark:border-gray-800">
              <MenuItem v-slot="{ active }">
                <button @click="router.post('/logout')" :class="[active ? 'bg-red-50 dark:bg-red-500/5 text-red-600' : 'text-red-500', 'flex w-full items-center rounded-2xl px-4 py-5 text-sm font-black uppercase tracking-[0.2em] transition-all group']">
                  <PowerIcon class="ml-4 h-5 w-5 group-hover:scale-110 transition-transform" />
                  تسجيل الخروج
                </button>
              </MenuItem>
            </div>
          </MenuItems>
        </transition>
      </Menu>
    </div>
  </header>
</template>

<style scoped>
/* Shake animation relocated to global AdminLayout or kept here if isolated */
@keyframes shake {
  0%, 100% { transform: rotate(0deg); }
  25% { transform: rotate(10deg); }
  75% { transform: rotate(-10deg); }
}
.group:hover .shake {
  animation: shake 0.3s ease-in-out infinite;
}
</style>
