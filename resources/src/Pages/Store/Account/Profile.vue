<script setup>
import { useForm, usePage, router, Link, Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import StoreLayout from '@/Layouts/StoreLayout.vue';
import { 
  User, 
  ShoppingBag, 
  LogOut, 
  ShieldCheck, 
  Mail, 
  Fingerprint,
  ChevronLeft,
  Camera,
  Check
} from 'lucide-vue-next';

const props = defineProps({
  s: Object,
  user: Object,
});

const form = useForm({
  username: props.user?.username || '',
  email: props.user?.email || '',
  password: '',
  password_confirmation: '',
});

const submit = () => {
  form.put('/account', {
    preserveScroll: true,
    onSuccess: () => form.reset('password', 'password_confirmation'),
  });
};

const flash = computed(() => usePage().props.flash || {});
</script>

<template>
  <Head :title="`ملفي الشخصي | ${s?.store_name || 'المتجر'}`" />

  <StoreLayout>
    <div class="bg-gray-50 dark:bg-night-950 min-h-screen py-12 md:py-24 transition-colors duration-500">
      <div class="container mx-auto px-4 max-w-6xl">
        
        <div class="flex flex-col lg:flex-row gap-12 items-start">
          
          <!-- Modern Sidebar Navigation -->
          <aside class="w-full lg:w-80 flex-shrink-0 sticky top-24">
            <div class="bg-white dark:bg-night-900 rounded-[3rem] p-8 shadow-xl shadow-gray-200/50 dark:shadow-black/20 border border-gray-100 dark:border-night-800 overflow-hidden relative">
               
               <!-- Profile Header -->
               <div class="mb-10 text-center relative">
                 <div class="relative inline-block mb-6 group">
                   <div class="w-24 h-24 bg-brand dark:bg-accent text-white dark:text-night-950 rounded-[2rem] flex items-center justify-center text-4xl font-black shadow-2xl shadow-brand/20 dark:shadow-accent/20 relative z-10 transition-transform duration-500 group-hover:scale-105">
                     {{ user?.username?.charAt(0).toUpperCase() }}
                   </div>
                   <!-- Subtle Decoration -->
                   <div class="absolute -inset-2 bg-brand/5 dark:bg-accent/5 rounded-[2.5rem] -rotate-6 group-hover:rotate-6 transition-transform duration-700"></div>
                   
                   <!-- Change Photo Button (Mock) -->
                   <button class="absolute -bottom-2 -right-2 w-10 h-10 bg-white dark:bg-night-800 rounded-2xl shadow-xl border border-gray-100 dark:border-night-700 flex items-center justify-center text-gray-400 hover:text-brand dark:hover:text-accent z-20 transition-all">
                     <Camera class="w-5 h-5" />
                   </button>
                 </div>
                 
                 <h2 class="text-2xl font-black text-gray-900 dark:text-white truncate px-4">{{ user?.username }}</h2>
                 <div class="flex justify-center mt-2">
                    <span class="text-[10px] font-black text-brand dark:text-accent bg-brand/10 dark:bg-accent/10 px-4 py-1 rounded-full uppercase tracking-widest">حساب عميل B2B</span>
                 </div>
               </div>

               <nav class="space-y-3">
                 <Link href="/account" class="flex items-center justify-between px-6 py-4 bg-brand/10 dark:bg-accent/10 text-brand dark:text-accent rounded-[1.5rem] font-black transition-all group">
                   <div class="flex items-center gap-4">
                     <User class="w-5 h-5" />
                     ملفي الشخصي
                   </div>
                   <ChevronLeft class="w-4 h-4 opacity-0 group-hover:opacity-100 transition-opacity" />
                 </Link>
                 
                 <Link href="/account/orders" class="flex items-center justify-between px-6 py-4 text-gray-400 dark:text-gray-500 hover:bg-gray-50 dark:hover:bg-night-800 hover:text-gray-900 dark:hover:text-white rounded-[1.5rem] font-bold transition-all group">
                   <div class="flex items-center gap-4">
                     <ShoppingBag class="w-5 h-5" />
                     طلباتي السابقة
                   </div>
                   <ChevronLeft class="w-4 h-4 opacity-0 group-hover:opacity-100 transition-opacity" />
                 </Link>

                 <div class="pt-6 mt-6 border-t border-gray-100 dark:border-night-800">
                    <form @submit.prevent="router.post('/customer/logout')">
                       <button type="submit" class="w-full flex items-center gap-4 px-6 py-4 text-red-400 hover:bg-red-50 dark:hover:bg-red-500/5 rounded-[1.5rem] font-bold transition-all">
                         <LogOut class="w-5 h-5" />
                         تسجيل الخروج
                       </button>
                    </form>
                 </div>
               </nav>
            </div>
          </aside>

          <!-- Main Content Area -->
          <main class="flex-grow">
            <div class="bg-white dark:bg-night-900 rounded-[3rem] p-8 md:p-16 shadow-2xl shadow-gray-200/50 dark:shadow-black/30 border border-gray-100 dark:border-night-800 relative overflow-hidden">
              
              <!-- Subtle Background Pattern -->
              <div class="absolute top-0 left-0 w-full h-full opacity-[0.03] pointer-events-none" style="background-image: radial-gradient(#04724D 1.5px, transparent 1.5px); background-size: 32px 32px;"></div>

              <div class="relative z-10">
                <header class="mb-12">
                   <h1 class="text-3xl md:text-4xl font-black text-gray-900 dark:text-white tracking-tighter mb-4 italic">إعدادات الحساب</h1>
                   <p class="text-gray-400 dark:text-gray-500 font-medium">قم بتحديث بياناتك الشخصية وإعدادات الأمان الخاصة بك.</p>
                </header>
                
                <div v-if="flash.status" class="bg-brand/10 dark:bg-accent/10 text-brand dark:text-accent p-6 rounded-[1.5rem] font-black mb-10 border border-brand/20 dark:border-accent/20 flex items-center gap-4 animate-in fade-in slide-in-from-top-4 duration-500">
                   <ShieldCheck class="w-7 h-7" />
                   {{ flash.status }}
                </div>

                <form @submit.prevent="submit" class="space-y-12">
                  
                  <!-- Basic Info Section -->
                  <div class="grid md:grid-cols-2 gap-10">
                    <div class="space-y-3">
                      <label class="text-xs font-black text-gray-400 dark:text-gray-600 uppercase tracking-[0.2em] ml-2 block flex items-center gap-2">
                        <User class="w-3.5 h-3.5" />
                        اسم المستخدم *
                      </label>
                      <input v-model="form.username" type="text" 
                        class="w-full bg-gray-50 dark:bg-night-800 border-none rounded-2xl py-5 px-6 focus:ring-4 focus:ring-brand/10 dark:focus:ring-accent/10 transition-all font-black text-gray-900 dark:text-white" />
                      <p v-if="form.errors.username" class="text-xs text-red-500 font-bold mt-1 ml-2">{{ form.errors.username }}</p>
                    </div>

                    <div class="space-y-3">
                      <label class="text-xs font-black text-gray-400 dark:text-gray-600 uppercase tracking-[0.2em] ml-2 block flex items-center gap-2">
                        <Mail class="w-3.5 h-3.5" />
                        البريد الإلكتروني *
                      </label>
                      <input v-model="form.email" type="email" 
                        class="w-full bg-gray-50 dark:bg-night-800 border-none rounded-2xl py-5 px-6 focus:ring-4 focus:ring-brand/10 dark:focus:ring-accent/10 transition-all font-black text-gray-900 dark:text-white" />
                      <p v-if="form.errors.email" class="text-xs text-red-500 font-bold mt-1 ml-2">{{ form.errors.email }}</p>
                    </div>
                  </div>

                  <!-- Security Section -->
                  <div class="p-10 bg-gray-50 dark:bg-night-800 rounded-[2.5rem] border border-gray-100 dark:border-night-700 relative overflow-hidden group">
                    <div class="absolute top-0 left-0 w-2 h-full bg-brand dark:bg-accent opacity-20 group-hover:opacity-100 transition-opacity"></div>
                    
                    <h4 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-widest mb-8 flex items-center gap-3">
                      <div class="w-8 h-8 bg-brand dark:bg-accent rounded-xl flex items-center justify-center text-white dark:text-night-950">
                        <Fingerprint class="w-5 h-5" />
                      </div>
                      تحديث كلمة المرور
                    </h4>

                    <div class="grid md:grid-cols-2 gap-10">
                      <div class="space-y-3">
                        <label class="text-xs font-black text-gray-400 dark:text-gray-600 uppercase tracking-[0.2em] ml-2 block">كلمة المرور الجديدة</label>
                        <input v-model="form.password" type="password" 
                          class="w-full bg-white dark:bg-night-900 border-none rounded-2xl py-5 px-6 focus:ring-4 focus:ring-brand/10 dark:focus:ring-accent/10 transition-all font-black text-gray-900 dark:text-white shadow-inner" 
                          placeholder="اتركها فارغة للإبقاء على الحالية" />
                        <p v-if="form.errors.password" class="text-xs text-red-500 font-bold mt-1 ml-2">{{ form.errors.password }}</p>
                      </div>

                      <div class="space-y-3">
                        <label class="text-xs font-black text-gray-400 dark:text-gray-600 uppercase tracking-[0.2em] ml-2 block">تأكيد كلمة المرور</label>
                        <input v-model="form.password_confirmation" type="password" 
                          class="w-full bg-white dark:bg-night-900 border-none rounded-2xl py-5 px-6 focus:ring-4 focus:ring-brand/10 dark:focus:ring-accent/10 transition-all font-black text-gray-900 dark:text-white shadow-inner" />
                      </div>
                    </div>
                  </div>

                  <!-- Footer Actions -->
                  <div class="flex items-center justify-between pt-8 border-t border-gray-100 dark:border-night-800">
                     <p class="text-[10px] font-black text-gray-300 dark:text-gray-600 uppercase tracking-widest hidden md:block">حماية البيانات مفعلة تلقائياً عبر DAW-Shield</p>
                     
                     <button 
                       type="submit" 
                       :disabled="form.processing"
                       class="w-full md:w-auto h-20 bg-gray-900 dark:bg-brand text-white dark:text-night-950 px-16 rounded-[2rem] font-black text-xl hover:scale-[1.02] active:scale-[0.98] transition-all flex items-center justify-center gap-4 group shadow-2xl shadow-gray-400 dark:shadow-brand/20 disabled:opacity-50 relative overflow-hidden"
                     >
                        <div class="absolute inset-0 bg-white/10 -translate-x-full group-hover:translate-x-full transition-transform duration-700"></div>
                        <Check v-if="!form.processing" class="w-6 h-6" />
                        <svg v-else class="animate-spin h-6 w-6" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <span>{{ form.processing ? 'جاري الحفظ...' : 'حفظ التغييرات' }}</span>
                     </button>
                  </div>
                </form>
              </div>
            </div>
          </main>
        </div>
      </div>
    </div>
  </StoreLayout>
</template>

<script>
export default {
    layout: null
}
</script>


