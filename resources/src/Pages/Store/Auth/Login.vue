<script setup>
import { useForm, Head, Link } from '@inertiajs/vue3';
import StoreLayout from '@/Layouts/StoreLayout.vue';
import { 
  Lock, 
  Mail, 
  ArrowLeft, 
  UserPlus, 
  ShieldCheck,
  ChevronRight,
  Eye,
  EyeOff
} from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps({ s: Object });

const showPassword = ref(false);

const form = useForm({
  email: '',
  password: '',
  remember: false,
});

const submit = () => {
  form.post('/customer/login', {
    onFinish: () => form.reset('password'),
  });
};
</script>

<template>
  <Head :title="`تسجيل الدخول | ${s?.store_name || 'المتجر'}`" />

  <StoreLayout>
    <div class="bg-gray-50 dark:bg-night-950 min-h-[90vh] flex items-center justify-center py-20 px-4 transition-colors duration-500 overflow-hidden relative">
      
      <!-- Premium Background Ornaments -->
      <div class="absolute top-0 right-0 w-[800px] h-[800px] bg-brand/5 dark:bg-accent/5 rounded-full blur-[120px] -translate-y-1/2 translate-x-1/2 pointer-events-none"></div>
      <div class="absolute bottom-0 left-0 w-[500px] h-[500px] bg-brand/5 dark:bg-accent/5 rounded-full blur-[100px] translate-y-1/2 -translate-x-1/4 pointer-events-none"></div>

      <div class="w-full max-w-xl relative z-10">
        
        <!-- Login Card -->
        <div class="bg-white/80 dark:bg-night-900/80 backdrop-blur-2xl rounded-[4rem] p-10 md:p-16 shadow-2xl shadow-gray-200/50 dark:shadow-black/60 border border-white dark:border-night-800 relative overflow-hidden">
          
          <!-- Top Accent -->
          <div class="absolute top-0 inset-x-0 h-2 bg-gradient-to-r from-brand to-accent"></div>

          <div class="text-center mb-12">
             <div class="inline-flex items-center justify-center w-24 h-24 bg-brand/10 dark:bg-accent/10 text-brand dark:text-accent rounded-[2.5rem] mb-8 shadow-xl shadow-brand/10 dark:shadow-accent/5">
                <Lock class="w-10 h-10" />
             </div>
             <h1 class="text-3xl md:text-4xl font-black text-gray-900 dark:text-white mb-4 tracking-tighter italic">مرحباً بك مجدداً</h1>
             <p class="text-gray-400 dark:text-gray-500 font-medium">سجل دخولك للوصول إلى أسعار الجملة وطلباتك القائمة.</p>
          </div>

          <form @submit.prevent="submit" class="space-y-8">
            
            <!-- Email Input -->
            <div class="space-y-2">
              <label class="text-xs font-black text-gray-400 dark:text-gray-600 uppercase tracking-[0.2em] ml-2 block flex items-center gap-2">
                <Mail class="w-3.5 h-3.5" />
                البريد الإلكتروني
              </label>
              <div class="relative group">
                <input v-model="form.email" type="email"
                  class="w-full bg-gray-50 dark:bg-night-800 border-none rounded-[1.5rem] py-5 px-6 focus:ring-4 focus:ring-brand/10 dark:focus:ring-accent/10 transition-all font-black text-gray-900 dark:text-white placeholder-gray-300 dark:placeholder-gray-700"
                  placeholder="name@company.com" required />
              </div>
              <p v-if="form.errors.email" class="text-xs text-red-500 font-bold mt-1 mr-2">{{ form.errors.email }}</p>
            </div>

            <!-- Password Input -->
            <div class="space-y-2">
              <div class="flex justify-between items-center px-2">
                <label class="text-xs font-black text-gray-400 dark:text-gray-600 uppercase tracking-[0.2em] block flex items-center gap-2">
                  <ShieldCheck class="w-3.5 h-3.5" />
                  كلمة المرور
                </label>
                <Link v-if="false" href="/forgot-password" class="text-[10px] font-black text-brand dark:text-accent uppercase hover:underline">نسيت كلمة المرور؟</Link>
              </div>
              <div class="relative group">
                <input v-model="form.password" :type="showPassword ? 'text' : 'password'"
                  class="w-full bg-gray-50 dark:bg-night-800 border-none rounded-[1.5rem] py-5 px-6 focus:ring-4 focus:ring-brand/10 dark:focus:ring-accent/10 transition-all font-black text-gray-900 dark:text-white placeholder-gray-300 dark:placeholder-gray-700"
                  placeholder="••••••••" required />
                <button type="button" @click="showPassword = !showPassword" class="absolute left-6 top-1/2 -translate-y-1/2 text-gray-300 dark:text-gray-600 hover:text-brand transition-colors">
                  <Eye v-if="!showPassword" class="w-5 h-5" />
                  <EyeOff v-else class="w-5 h-5" />
                </button>
              </div>
              <p v-if="form.errors.password" class="text-xs text-red-500 font-bold mt-1 mr-2">{{ form.errors.password }}</p>
            </div>

            <!-- Remember & Meta -->
            <div class="flex items-center justify-between px-2">
               <label class="flex items-center gap-3 cursor-pointer group">
                  <div class="relative w-5 h-5">
                    <input v-model="form.remember" type="checkbox" class="peer hidden" />
                    <div class="w-full h-full bg-gray-100 dark:bg-night-800 rounded-lg border-2 border-gray-200 dark:border-night-700 peer-checked:bg-brand dark:peer-checked:bg-accent peer-checked:border-transparent transition-all"></div>
                    <Check class="absolute inset-0 w-3.5 h-3.5 text-white dark:text-night-950 m-auto opacity-0 peer-checked:opacity-100 transition-opacity" />
                  </div>
                  <span class="text-xs font-black text-gray-400 dark:text-gray-600 uppercase tracking-widest group-hover:text-gray-900 dark:group-hover:text-white transition-colors">تذكرني</span>
               </label>
            </div>

            <!-- Submit Button -->
            <button
              type="submit"
              :disabled="form.processing"
              class="w-full h-20 bg-gray-900 dark:bg-brand text-white dark:text-night-950 rounded-[2rem] font-black text-xl hover:scale-[1.02] active:scale-[0.98] transition-all flex items-center justify-center gap-4 group shadow-2xl shadow-gray-400 dark:shadow-brand/20 disabled:opacity-50 overflow-hidden relative mt-12"
            >
              <div class="absolute inset-0 bg-white/10 -translate-x-full group-hover:translate-x-full transition-transform duration-1000"></div>
              <span v-if="!form.processing">تسجيل الدخول</span>
              <svg v-else class="animate-spin h-6 w-6" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
              <ArrowLeft v-if="!form.processing" class="w-6 h-6 group-hover:-translate-x-2 transition-transform" />
            </button>
          </form>

          <!-- Footer -->
          <div class="mt-12 text-center">
             <p class="text-sm font-bold text-gray-400 dark:text-gray-600 mb-4">ليس لديك حساب بعد؟</p>
             <Link href="/customer/register" class="inline-flex items-center gap-3 text-brand dark:text-accent font-black text-sm group">
               أنشئ حساباً جديداً
               <UserPlus class="w-5 h-5 group-hover:scale-110 transition-transform" />
             </Link>
          </div>

        </div>

        <!-- Extra Links -->
        <div class="mt-12 flex flex-col md:flex-row items-center justify-between px-10 gap-6">
           <Link href="/shop" class="flex items-center gap-2 text-xs font-black text-gray-400 hover:text-gray-900 dark:hover:text-white transition-all group">
              <ChevronRight class="w-4 h-4" />
              العودة للمتجر
           </Link>
           <div class="flex items-center gap-6">
             <a href="#" class="text-[10px] font-black text-gray-400 uppercase tracking-widest hover:text-brand transition-colors">الشروط والأحكام</a>
             <a href="#" class="text-[10px] font-black text-gray-400 uppercase tracking-widest hover:text-brand transition-colors">سياسة الخصوصية</a>
           </div>
        </div>

      </div>
    </div>
  </StoreLayout>
</template>

<style scoped>
/* Custom Checkbox override if needed */
</style>
