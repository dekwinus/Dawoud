<script setup>
import { useForm, Head, Link } from '@inertiajs/vue3';
import { 
  EnvelopeIcon, 
  CheckCircleIcon,
  ArrowLeftIcon,
  ArrowRightIcon
} from '@heroicons/vue/24/outline';

defineProps({
  errors: Object,
  settings: Object,
  status: String,
});

const form = useForm({
  email: '',
});

const submit = () => {
  form.post(route('password.email'));
};
</script>

<template>
  <Head title="استعادة كلمة المرور" />
  
  <div dir="rtl" class="min-h-screen flex bg-white font-sans selection:bg-[#3EFF8B]/30 tracking-tight">
    <!-- Right Section: Premium Banner (Flipped for RTL) -->
    <div class="hidden lg:flex lg:w-1/2 bg-[#04724D] relative overflow-hidden flex-col justify-between p-16 order-2">
      <!-- Animated Background Circles -->
      <div class="absolute top-0 right-0 w-full h-full overflow-hidden pointer-events-none opacity-20">
        <div class="absolute -top-24 -right-24 w-96 h-96 bg-[#3EFF8B] rounded-full blur-[120px] animate-pulse"></div>
        <div class="absolute top-1/2 -left-24 w-64 h-64 bg-white rounded-full blur-[100px] opacity-10"></div>
      </div>

      <div class="relative z-10 text-right">
        <img :src="`/images/${settings?.logo || 'logo.png'}`" class="h-12 w-auto mb-12 filter brightness-0 invert mr-0 ml-auto" alt="DawPOS Logo">
        
        <h1 class="text-6xl font-black text-white leading-[1.2] mb-6">
          الأمان هو <br/>
          <span class="text-[#3EFF8B]">أولويتنا القصوى.</span>
        </h1>
        <p class="text-white/70 text-xl max-w-md leading-relaxed font-medium">
          استعد الوصول إلى حسابك بسرعة وأمان من خلال بروتوكول المصادقة المتقدم الخاص بنا.
        </p>
      </div>

      <div class="relative z-10">
        <div class="flex items-center gap-4 p-6 bg-white/5 backdrop-blur-md rounded-3xl border border-white/10 max-w-sm mr-0 ml-auto flex-row-reverse">
          <div class="w-12 h-12 rounded-2xl bg-[#3EFF8B] flex items-center justify-center flex-shrink-0">
            <CheckCircleIcon class="h-7 w-7 text-[#04724D]" />
          </div>
          <div class="text-right">
            <p class="text-white font-bold tracking-tight">وضع الاستعادة</p>
            <p class="text-white/50 text-xs text-left">Auth Protocol v2.1 Activated</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Left Section: Forgot Password Form -->
    <div class="w-full lg:w-1/2 flex flex-col justify-center px-8 sm:px-16 lg:px-24 xl:px-32 bg-gray-50/50 order-1">
      <div class="lg:hidden mb-12 flex justify-center">
        <img :src="`/images/${settings?.logo || 'logo.png'}`" class="h-10 w-auto" alt="Logo">
      </div>

      <div class="max-w-md w-full mx-auto text-right">
        <Link 
          :href="route('login')" 
          class="inline-flex items-center gap-2 text-xs font-black text-[#04724D] uppercase tracking-widest mb-10 hover:translate-x-[4px] transition-transform"
        >
          <ArrowRightIcon class="h-4 w-4" />
          العودة لتسجيل الدخول
        </Link>

        <h2 class="text-4xl font-black text-gray-900 mb-2 tracking-tight">نسيت كلمة المرور؟</h2>
        <p class="text-gray-500 font-bold mb-10 text-lg">لا تقلق. أدخل بريدك الإلكتروني وسنرسل لك تعليمات استعادة الوصول.</p>

        <!-- Success Status -->
        <div v-if="status" class="mb-8 p-6 bg-[#04724D]/5 border border-[#04724D]/10 rounded-2xl flex items-start gap-4 animate-in fade-in slide-in-from-top-4 duration-700 flex-row-reverse">
          <div class="mt-1 w-6 h-6 rounded-full bg-[#04724D] flex items-center justify-center flex-shrink-0">
            <CheckCircleIcon class="h-4 w-4 text-white" />
          </div>
          <div class="text-right">
            <p class="text-[#04724D] font-bold text-sm leading-relaxed">{{ status }}</p>
          </div>
        </div>

        <form @submit.prevent="submit" class="space-y-6 text-right">
          <!-- Email Input -->
          <div class="space-y-2">
            <label class="text-sm font-black text-gray-400 uppercase tracking-widest mr-1">البريد الإلكتروني</label>
            <div class="relative group">
              <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                <EnvelopeIcon class="h-5 w-5 text-gray-400 group-focus-within:text-[#04724D] transition-colors" />
              </div>
              <input 
                v-model="form.email"
                type="email" 
                required
                dir="ltr"
                class="block w-full pr-11 pl-4 py-4 bg-white border border-gray-200 rounded-2xl focus:ring-4 focus:ring-[#3EFF8B]/10 focus:border-[#04724D] transition-all font-bold text-gray-900 placeholder:text-gray-300 text-left"
                placeholder="name@company.com"
              >
            </div>
            <p v-if="errors.email" class="text-red-500 text-xs font-bold mr-1 animate-pulse">{{ errors.email }}</p>
          </div>

          <!-- Submit Button -->
          <button 
            type="submit" 
            :disabled="form.processing"
            class="w-full bg-[#04724D] hover:bg-[#035a3d] disabled:bg-gray-300 text-white font-black py-5 rounded-2xl shadow-xl shadow-[#04724D]/20 transition-all active:scale-[0.98] flex items-center justify-center gap-3 overflow-hidden group"
          >
            <span v-if="form.processing" class="flex items-center gap-2">
              <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r=10 stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              جاري المعالجة...
            </span>
            <template v-else>
              إرسال رابط الاستعادة
              <ArrowLeftIcon class="h-5 w-5 group-hover:-translate-x-1 transition-transform" />
            </template>
          </button>
        </form>

        <!-- Footer Info -->
        <p class="mt-12 text-center text-[10px] font-bold text-gray-400 uppercase tracking-[0.2em]">
          Powered by DawPOS Unified Core &copy; {{ new Date().getFullYear() }}
        </p>
      </div>
    </div>
  </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;700;900&display=swap');

:deep(body), :deep(html), .font-sans {
  font-family: 'Cairo', 'Inter', sans-serif !important;
}

input:focus::placeholder {
  opacity: 0.5;
  transition: opacity 0.2s ease;
}
</style>
