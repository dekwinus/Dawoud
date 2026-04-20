<script setup>
import { useForm, Head, Link } from '@inertiajs/vue3';
import StoreLayout from '@/Layouts/StoreLayout.vue';
import { 
  UserPlus, 
  Mail, 
  Lock, 
  User, 
  ArrowLeft, 
  ShieldCheck,
  ChevronRight,
  Fingerprint,
  Info
} from 'lucide-vue-next';

const props = defineProps({ s: Object });

const form = useForm({
  username: '',
  email: '',
  password: '',
  password_confirmation: '',
});

const submit = () => {
  form.post('/customer/register', {
    onFinish: () => form.reset('password', 'password_confirmation'),
  });
};
</script>

<template>
  <Head :title="`إنشاء حساب | ${s?.store_name || 'المتجر'}`" />

  <StoreLayout>
    <div class="bg-gray-50 dark:bg-night-950 min-h-[90vh] flex items-center justify-center py-20 px-4 transition-colors duration-500 overflow-hidden relative">
      
      <!-- Premium Background Ornaments -->
      <div class="absolute top-0 left-0 w-[800px] h-[800px] bg-brand/5 dark:bg-accent/5 rounded-full blur-[120px] -translate-y-1/2 -translate-x-1/2 pointer-events-none"></div>
      <div class="absolute bottom-0 right-0 w-[500px] h-[500px] bg-brand/5 dark:bg-accent/5 rounded-full blur-[100px] translate-y-1/2 translate-x-1/4 pointer-events-none"></div>

      <div class="w-full max-w-2xl relative z-10">
        
        <!-- Register Card -->
        <div class="bg-white/80 dark:bg-night-900/80 backdrop-blur-2xl rounded-[4rem] p-10 md:p-16 shadow-2xl shadow-gray-200/50 dark:shadow-black/60 border border-white dark:border-night-800 relative overflow-hidden">
          
          <!-- Top Accent -->
          <div class="absolute top-0 inset-x-0 h-2 bg-gradient-to-r from-brand to-accent"></div>

          <div class="text-center mb-12">
             <div class="inline-flex items-center justify-center w-24 h-24 bg-brand/10 dark:bg-accent/10 text-brand dark:text-accent rounded-[2.5rem] mb-8 shadow-xl shadow-brand/10 dark:shadow-accent/5">
                <UserPlus class="w-10 h-10" />
             </div>
             <h1 class="text-3xl md:text-4xl font-black text-gray-900 dark:text-white mb-4 tracking-tighter italic">ابدأ نمو أعمالك</h1>
             <p class="text-gray-400 dark:text-gray-500 font-medium">أنشئ حساباً جديداً للانضمام لشبكة موزعينا والاستفادة من عروض B2B.</p>
          </div>

          <form @submit.prevent="submit" class="space-y-8">
            
            <div class="grid md:grid-cols-2 gap-8">
               <!-- Username Input -->
               <div class="space-y-2">
                 <label class="text-xs font-black text-gray-400 dark:text-gray-600 uppercase tracking-[0.2em] ml-2 block flex items-center gap-2">
                   <User class="w-3.5 h-3.5" />
                   اسم المستخدم *
                 </label>
                 <input v-model="form.username" type="text"
                   class="w-full bg-gray-50 dark:bg-night-800 border-none rounded-[1.5rem] py-5 px-6 focus:ring-4 focus:ring-brand/10 dark:focus:ring-accent/10 transition-all font-black text-gray-900 dark:text-white placeholder-gray-300 dark:placeholder-gray-700"
                   placeholder="الاسم الثلاثي..." required />
                 <p v-if="form.errors.username" class="text-xs text-red-500 font-bold mt-1 mr-2">{{ form.errors.username }}</p>
               </div>

               <!-- Email Input -->
               <div class="space-y-2">
                 <label class="text-xs font-black text-gray-400 dark:text-gray-600 uppercase tracking-[0.2em] ml-2 block flex items-center gap-2">
                   <Mail class="w-3.5 h-3.5" />
                   البريد الإلكتروني *
                 </label>
                 <input v-model="form.email" type="email"
                   class="w-full bg-gray-50 dark:bg-night-800 border-none rounded-[1.5rem] py-5 px-6 focus:ring-4 focus:ring-brand/10 dark:focus:ring-accent/10 transition-all font-black text-gray-900 dark:text-white placeholder-gray-300 dark:placeholder-gray-700"
                   placeholder="name@company.com" required />
                 <p v-if="form.errors.email" class="text-xs text-red-500 font-bold mt-1 mr-2">{{ form.errors.email }}</p>
               </div>
            </div>

            <div class="grid md:grid-cols-2 gap-8">
               <!-- Password Input -->
               <div class="space-y-2">
                 <label class="text-xs font-black text-gray-400 dark:text-gray-600 uppercase tracking-[0.2em] ml-2 block flex items-center gap-2">
                   <Lock class="w-3.5 h-3.5" />
                   كلمة المرور *
                 </label>
                 <input v-model="form.password" type="password"
                   class="w-full bg-gray-50 dark:bg-night-800 border-none rounded-[1.5rem] py-5 px-6 focus:ring-4 focus:ring-brand/10 dark:focus:ring-accent/10 transition-all font-black text-gray-900 dark:text-white placeholder-gray-300 dark:placeholder-gray-700"
                   placeholder="••••••••" required />
                 <p v-if="form.errors.password" class="text-xs text-red-500 font-bold mt-1 mr-2">{{ form.errors.password }}</p>
               </div>

               <!-- Password Confirmation -->
               <div class="space-y-2">
                 <label class="text-xs font-black text-gray-400 dark:text-gray-600 uppercase tracking-[0.2em] ml-2 block flex items-center gap-2">
                   <Fingerprint class="w-3.5 h-3.5" />
                   تأكيد كلمة المرور *
                 </label>
                 <input v-model="form.password_confirmation" type="password"
                   class="w-full bg-gray-50 dark:bg-night-800 border-none rounded-[1.5rem] py-5 px-6 focus:ring-4 focus:ring-brand/10 dark:focus:ring-accent/10 transition-all font-black text-gray-900 dark:text-white placeholder-gray-300 dark:placeholder-gray-700"
                   placeholder="••••••••" required />
               </div>
            </div>

            <!-- B2B Informational Alert -->
            <div class="bg-brand/5 dark:bg-accent/5 p-6 rounded-[2rem] border border-brand/10 dark:border-accent/10 flex gap-4 mt-6">
               <div class="w-10 h-10 bg-white dark:bg-night-900 rounded-xl flex items-center justify-center text-brand dark:text-accent shadow-sm flex-shrink-0">
                  <Info class="w-5 h-5" />
               </div>
               <div class="space-y-1">
                  <p class="text-[10px] font-black text-gray-900 dark:text-white uppercase tracking-widest">ملاحظة لعملاء الجملة</p>
                  <p class="text-xs text-gray-500 dark:text-gray-400 font-medium leading-relaxed">بمجرد إنشاء الحساب، سيقوم فريقنا بمراجعة بياناتك لتفعيل مميزات أسعار B2B تلقائياً على حسابك.</p>
               </div>
            </div>

            <!-- Submit Button -->
            <button
              type="submit"
              :disabled="form.processing"
              class="w-full h-20 bg-gray-900 dark:bg-brand text-white dark:text-night-950 rounded-[2.5rem] font-black text-xl hover:scale-[1.02] active:scale-[0.98] transition-all flex items-center justify-center gap-4 group shadow-2xl shadow-gray-400 dark:shadow-brand/20 disabled:opacity-50 overflow-hidden relative mt-12"
            >
              <div class="absolute inset-0 bg-white/10 -translate-x-full group-hover:translate-x-full transition-transform duration-1000"></div>
              <span v-if="!form.processing">إنشاء الحساب الآن</span>
              <svg v-else class="animate-spin h-6 w-6" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
              <ArrowLeft v-if="!form.processing" class="w-6 h-6 group-hover:-translate-x-2 transition-transform" />
            </button>
          </form>

          <!-- Footer -->
          <div class="mt-12 text-center">
             <p class="text-sm font-bold text-gray-400 dark:text-gray-600 mb-4">لديك حساب بالفعل؟</p>
             <Link href="/customer/login" class="inline-flex items-center gap-3 text-brand dark:text-accent font-black text-sm group">
               تسجيل الدخول
               <ArrowLeft class="w-5 h-5 group-hover:-translate-x-1 transition-transform" />
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
             <a href="#" class="text-[10px] font-black text-gray-400 uppercase tracking-widest hover:text-brand transition-colors">اتفاقية استخدام مبيعات الجملة</a>
             <a href="#" class="text-[10px] font-black text-gray-400 uppercase tracking-widest hover:text-brand transition-colors">الخصوصية</a>
           </div>
        </div>

      </div>
    </div>
  </StoreLayout>
</template>

<style scoped>
</style>
