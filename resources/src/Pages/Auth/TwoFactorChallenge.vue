<script setup>
import { ref, onMounted } from 'vue';
import { useForm, Head, router } from '@inertiajs/vue3';
import { 
  ShieldCheck,
  ShieldAlert,
  ArrowRight as ArrowRightIcon,
  RefreshCw as RefreshIcon,
  KeyRound,
  LayoutGrid
} from 'lucide-vue-next';

const props = defineProps({
  email: String,
  errors: Object,
  settings: Object,
});

const form = useForm({
  code: '',
});

const resendForm = useForm({});
const resendCooldown = ref(0);
const resendMessage = ref('');

const submit = () => {
  form.post('/auth/two-factor', {
    onFinish: () => form.reset('code'),
  });
};

const resendCode = () => {
  if (resendCooldown.value > 0) return;

  resendForm.post('/auth/two-factor/resend', {
    onSuccess: () => {
      resendCooldown.value = 60;
      startCooldown();
    },
  });
};

const startCooldown = () => {
  const timer = setInterval(() => {
    resendCooldown.value--;
    if (resendCooldown.value <= 0) {
      clearInterval(timer);
    }
  }, 1000);
};

onMounted(() => {
    // Optional: Auto-focus first digit or input
});

</script>

<template>
  <Head title="التحقق بخطوتين - DawPOS Pro Max" />
  
  <div dir="rtl" class="min-h-screen flex bg-white dark:bg-[#0A0A0A] font-['Cairo'] selection:bg-[#3EFF8B]/30 tracking-tight overflow-hidden">
    <!-- Right Section: PRO MAX Luxury Banner (Arabic/RTL) -->
    <div class="hidden lg:flex lg:w-1/2 bg-[#04724D] relative overflow-hidden flex-col justify-between p-20 order-2">
      <!-- High-Fidelity Animated Background -->
      <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-[600px] h-[600px] bg-[#3EFF8B]/20 rounded-full blur-[160px] animate-pulse"></div>
        <div class="absolute top-1/2 -left-40 w-[500px] h-[500px] bg-white/[0.05] rounded-full blur-[140px]"></div>
        <div class="absolute bottom-0 inset-x-0 h-1/2 bg-gradient-to-t from-black/20 to-transparent"></div>
      </div>

      <div class="relative z-10">
        <div class="mb-16 inline-flex p-4 bg-white/10 backdrop-blur-2xl rounded-3xl border border-white/10 shadow-2xl">
            <img :src="`/images/${settings?.logo || 'logo.png'}`" class="h-10 w-auto filter brightness-0 invert" alt="DawPOS Logo">
        </div>
        
        <h1 class="text-6xl xl:text-8xl font-black text-white leading-[1.1] mb-10 tracking-tighter">
          أمان فائق <br/>
          <span class="text-[#3EFF8B] underline decoration-wavy decoration-[#3EFF8B]/30 underline-offset-8">لحماية بياناتك.</span>
        </h1>
        
        <div class="grid grid-cols-2 gap-8 max-w-lg">
            <div class="space-y-3">
                <div class="w-12 h-12 rounded-2xl bg-white/10 flex items-center justify-center border border-white/10 backdrop-blur-md">
                    <ShieldCheck class="h-6 w-6 text-[#3EFF8B]" />
                </div>
                <h3 class="text-white font-black text-lg">تحقق ثنائي</h3>
                <p class="text-white/50 text-xs font-bold leading-relaxed">نظام تحقق متطور يضمن وصولك الآمن والوحيد للنظام.</p>
            </div>
            <div class="space-y-3">
                <div class="w-12 h-12 rounded-2xl bg-white/10 flex items-center justify-center border border-white/10 backdrop-blur-md">
                    <KeyRound class="h-6 w-6 text-[#3EFF8B]" />
                </div>
                <h3 class="text-white font-black text-lg">رموز مؤقتة</h3>
                <p class="text-white/50 text-xs font-bold leading-relaxed">رموز التحقق صالحة لمدة محدودة جداً لضمان أقصى درجات الأمان.</p>
            </div>
        </div>
      </div>

      <div class="relative z-10 mt-20">
        <div class="flex items-center gap-5 p-8 bg-white/5 backdrop-blur-3xl rounded-[40px] border border-white/10 shadow-3xl group hover:bg-white/10 transition-all duration-700">
          <div class="w-16 h-16 rounded-[24px] bg-gradient-to-br from-[#3EFF8B] to-[#10B981] flex items-center justify-center shadow-2xl shadow-[#3EFF8B]/20 group-hover:rotate-12 transition-transform duration-500">
            <LayoutGrid class="h-8 w-8 text-[#04724D]" />
          </div>
          <div>
            <p class="text-white text-xl font-black tracking-tight">نظام موحد بالكامل</p>
            <p class="text-[#3EFF8B] text-xs font-black tracking-[0.4em] uppercase opacity-70 mt-1">V5.4.2 ELITE PROTOCOL</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Left Section: 2FA Challenge Form -->
    <div class="w-full lg:w-1/2 flex flex-col justify-center px-10 md:px-24 xl:px-40 bg-gray-50/30 dark:bg-[#0A0A0A] order-1 relative">
      <div class="absolute top-0 right-0 w-full h-full bg-[#04724D]/[0.02] pointer-events-none blur-[120px]"></div>
      
      <div class="lg:hidden mb-16 flex justify-center scale-125">
        <img :src="`/images/${settings?.logo || 'logo.png'}`" class="h-10 w-auto" alt="Logo">
      </div>

      <div class="max-w-md w-full mx-auto relative z-10">
        <div class="space-y-4 mb-16">
            <div class="inline-flex items-center gap-2 px-4 py-2 bg-[#04724D]/5 rounded-full border border-[#04724D]/10">
                <div class="w-2 h-2 rounded-full bg-[#04724D] animate-pulse"></div>
                <span class="text-[10px] font-black text-[#04724D] uppercase tracking-widest">تأكيد الهوية - الخطوة الثانية</span>
            </div>
            <h2 class="text-5xl font-black text-gray-900 dark:text-white tracking-tighter">التحقق بخطوتين</h2>
            <p class="text-gray-400 font-bold text-xl">لقد أرسلنا رمز التحقق إلى بريدك الإلكتروني:</p>
            <p class="text-[#04724D] font-black text-lg underline underline-offset-4 decoration-dashed opacity-80">{{ email }}</p>
        </div>

        <form @submit.prevent="submit" class="space-y-10">
          <!-- OTP Input -->
          <div class="space-y-3">
            <label class="text-[11px] font-black text-gray-400 dark:text-gray-600 uppercase tracking-[0.3em] mr-2">رمز التحقق (6 أرقام)</label>
            <div class="relative group">
              <div class="absolute inset-y-0 right-7 flex items-center pointer-events-none">
                <ShieldAlert class="h-5 w-5 text-gray-400 group-focus-within:text-[#04724D] transition-all" />
              </div>
              <input 
                v-model="form.code"
                type="text" 
                required
                maxlength="6"
                dir="ltr"
                autofocus
                class="block w-full pr-16 pl-6 py-8 bg-white dark:bg-white/[0.02] border-2 border-gray-100 dark:border-gray-800 rounded-[28px] focus:ring-8 focus:ring-[#04724D]/5 focus:border-[#04724D] transition-all font-black text-gray-900 dark:text-white placeholder:text-gray-200 dark:placeholder:text-gray-800 text-center text-4xl tracking-[0.5em] tabular-nums"
                placeholder="000000"
              >
            </div>
            <p v-if="errors.code" class="text-red-500 text-xs font-black mr-2 animate-bounce text-center">{{ errors.code }}</p>
          </div>

          <!-- Resend & Support -->
          <div class="flex flex-col gap-6 px-2">
              <button 
                type="button"
                @click="resendCode"
                :disabled="resendCooldown > 0 || resendForm.processing"
                class="flex items-center justify-center gap-3 text-xs font-black text-gray-500 hover:text-[#04724D] disabled:text-gray-300 transition-colors uppercase tracking-widest"
              >
                <RefreshIcon :class="{'animate-spin': resendForm.processing}" class="h-4 w-4" />
                <span v-if="resendCooldown > 0">إعادة الإرسال بعد {{ resendCooldown }} ثانية</span>
                <span v-else>لم يصلك الرمز؟ إعادة إرسال الكود</span>
              </button>
              
              <div v-if="$page.props.flash?.message" class="text-green-500 text-[11px] font-black text-center bg-green-500/5 py-3 rounded-2xl border border-green-500/10">
                  {{ $page.props.flash.message }}
              </div>
          </div>

          <!-- Submit Button -->
          <button 
            type="submit" 
            :disabled="form.processing || form.code.length < 6"
            class="w-full bg-[#04724D] hover:bg-[#035a3d] disabled:bg-gray-100 dark:disabled:bg-white/5 disabled:text-gray-300 text-white font-black py-6 rounded-[32px] shadow-[0_20px_50px_rgba(4,114,77,0.3)] transition-all active:scale-[0.96] flex items-center justify-center gap-4 group overflow-hidden relative"
          >
            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000 ease-in-out"></div>
            
            <span v-if="form.processing" class="flex items-center gap-3">
              <svg class="animate-spin h-6 w-6 text-[#3EFF8B]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              جاري فحص الرمز...
            </span>
            <template v-else>
              <span class="text-lg">تأكيد الدخول</span>
              <ArrowRightIcon class="h-6 w-6 group-hover:translate-x-2 transition-transform" />
            </template>
          </button>
        </form>

        <!-- Footer Info -->
        <div class="mt-20 flex flex-col items-center gap-4">
            <div class="w-10 h-1 bg-gray-100 dark:bg-gray-800 rounded-full"></div>
            <p class="text-[10px] font-black text-gray-300 dark:text-gray-700 uppercase tracking-[0.5em] text-center">
              DawPOS Unified Security Protocol &copy; {{ new Date().getFullYear() }} <br/>
              Advanced Identity Verification
            </p>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;700;900&display=swap');

input:focus::placeholder {
  opacity: 0.1;
  transition: opacity 0.2s ease;
}

.shadow-3xl {
    box-shadow: 0 40px 100px -20px rgba(0,0,0,0.1);
}
</style>
