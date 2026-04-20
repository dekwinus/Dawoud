<script setup>
import { useForm, Head } from '@inertiajs/vue3';
import StoreLayout from '@/Layouts/StoreLayout.vue';
import { 
  Building2, 
  User, 
  Mail, 
  Phone, 
  TrendingUp, 
  MessageSquare,
  Send,
  CheckCircle2,
  ChevronRight,
  ShieldCheck,
  Star
} from 'lucide-vue-next';

const props = defineProps({ s: Object });

const form = useForm({
  company_name: '',
  contact_name: '',
  email: '',
  phone: '',
  expected_volume: '',
  notes: '',
});

const submit = () => {
  form.post('/wholesale-request', {
    preserveScroll: true,
    onSuccess: () => form.reset(),
  });
};
</script>

<template>
  <Head :title="`طلب أسعار الجملة B2B | ${s?.store_name || 'المتجر'}`" />

  <StoreLayout>
    <div class="bg-gray-50 dark:bg-night-950 min-h-screen py-10 md:py-20 transition-colors duration-500 font-['Cairo']" dir="rtl">
      <div class="container mx-auto px-4 max-w-6xl">
        
        <div class="grid lg:grid-cols-2 gap-16 items-start">
           
           <!-- Info Section -->
           <div class="space-y-12">
              <div>
                 <div class="flex items-center gap-3 text-brand dark:text-accent text-xs font-black uppercase tracking-[0.3em] mb-4">
                    <div class="w-10 h-[2px] bg-brand dark:bg-accent"></div>
                    <span>حلول الأعمال B2B</span>
                 </div>
                 <h1 class="text-3xl md:text-5xl font-black text-gray-900 dark:text-white tracking-tighter leading-tight italic">انضم لشبكة <br><span class="text-brand dark:text-accent">موزعينا المعتمدين</span></h1>
              </div>

              <p class="text-xl text-gray-500 dark:text-gray-400 font-medium leading-relaxed max-w-lg">نحن نوفر حلول توريد متكاملة وبأفضل الأسعار التنافسية في السوق. املأ النموذج وسيتصل بك أحد مستشارينا خلال 24 ساعة.</p>

              <div class="space-y-8">
                 <div class="flex items-start gap-6 group">
                    <div class="w-14 h-14 bg-white dark:bg-night-900 rounded-2xl shadow-lg flex items-center justify-center text-brand dark:text-accent border border-gray-100 dark:border-night-800 group-hover:scale-110 transition-transform">
                       <TrendingUp class="w-6 h-6" />
                    </div>
                    <div>
                       <h3 class="text-lg font-black text-gray-900 dark:text-white mb-1">أسعار تنافسية حصرية</h3>
                       <p class="text-sm text-gray-500 dark:text-gray-400 font-bold">هوامش ربح مرتفعة تضمن نمو أعمالك بشكل مستدام.</p>
                    </div>
                 </div>

                 <div class="flex items-start gap-6 group">
                    <div class="w-14 h-14 bg-white dark:bg-night-900 rounded-2xl shadow-lg flex items-center justify-center text-brand dark:text-accent border border-gray-100 dark:border-night-800 group-hover:scale-110 transition-transform">
                       <ShieldCheck class="w-6 h-6" />
                    </div>
                    <div>
                       <h3 class="text-lg font-black text-gray-900 dark:text-white mb-1">ضمان التوريد والجودة</h3>
                       <p class="text-sm text-gray-500 dark:text-gray-400 font-bold">جميع منتجاتنا أصلية وتخضع لأعلى معايير الجودة العالمية.</p>
                    </div>
                 </div>

                 <div class="flex items-start gap-6 group">
                    <div class="w-14 h-14 bg-white dark:bg-night-900 rounded-2xl shadow-lg flex items-center justify-center text-brand dark:text-accent border border-gray-100 dark:border-night-800 group-hover:scale-110 transition-transform">
                       <Star class="w-6 h-6" />
                    </div>
                    <div>
                       <h3 class="text-lg font-black text-gray-900 dark:text-white mb-1">دعم فني مخصص</h3>
                       <p class="text-sm text-gray-500 dark:text-gray-400 font-bold">مدير حساب خاص لعملياتك لضمان سلاسة التوريد واللوجستيات.</p>
                    </div>
                 </div>
              </div>
              
              <!-- Trust Marker -->
              <div class="p-8 bg-gray-900 dark:bg-brand rounded-[2.5rem] text-white dark:text-night-950 flex items-center justify-between shadow-2xl">
                 <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-white/10 dark:bg-black/10 rounded-xl flex items-center justify-center">
                       <Building2 class="w-6 h-6" />
                    </div>
                    <p class="font-black text-lg">أكثر من +500 شريك نجاح</p>
                 </div>
                 <ChevronRight class="w-6 h-6 opacity-40 shrink-0" />
              </div>
           </div>

           <!-- Form Section -->
           <div class="relative">
              <div class="absolute -inset-4 bg-brand/5 dark:bg-accent/5 rounded-[4rem] blur-3xl pointer-events-none"></div>
              
              <div class="bg-white/80 dark:bg-night-900/80 backdrop-blur-2xl rounded-[3.5rem] p-8 md:p-14 shadow-2xl border border-white dark:border-night-800 relative overflow-hidden">
                 
                 <div v-if="$page.props.flash?.success" class="bg-brand/10 dark:bg-accent/10 p-8 rounded-[2rem] text-center mb-8 border border-brand/20 dark:border-accent/20 animate-in zoom-in duration-500">
                    <CheckCircle2 class="w-16 h-16 text-brand dark:text-accent mx-auto mb-4" />
                    <h3 class="text-xl font-black text-gray-900 dark:text-white mb-2">تم استلام طلبك بنجاح!</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 font-bold leading-relaxed">{{ $page.props.flash.success }}</p>
                 </div>

                 <form v-else @submit.prevent="submit" class="space-y-6">
                    <div class="grid md:grid-cols-2 gap-6">
                       <div class="space-y-2">
                          <label class="text-xs font-black text-gray-400 dark:text-gray-600 uppercase tracking-widest ml-2 flex items-center gap-2">
                             <Building2 class="w-3.5 h-3.5" /> اسم المؤسسة / الشركة
                          </label>
                          <input v-model="form.company_name" class="w-full bg-gray-50 dark:bg-night-800 border-none rounded-2xl py-4 px-6 font-black text-gray-900 dark:text-white focus:ring-4 focus:ring-brand/10" required />
                       </div>
                       <div class="space-y-2">
                          <label class="text-xs font-black text-gray-400 dark:text-gray-600 uppercase tracking-widest ml-2 flex items-center gap-2">
                             <User class="w-3.5 h-3.5" /> اسم المسؤول
                          </label>
                          <input v-model="form.contact_name" class="w-full bg-gray-50 dark:bg-night-800 border-none rounded-2xl py-4 px-6 font-black text-gray-900 dark:text-white focus:ring-4 focus:ring-brand/10" required />
                       </div>
                    </div>

                    <div class="grid md:grid-cols-2 gap-6">
                       <div class="space-y-2">
                          <label class="text-xs font-black text-gray-400 dark:text-gray-600 uppercase tracking-widest ml-2 flex items-center gap-2">
                             <Mail class="w-3.5 h-3.5" /> البريد الإلكتروني
                          </label>
                          <input v-model="form.email" type="email" class="w-full bg-gray-50 dark:bg-night-800 border-none rounded-2xl py-4 px-6 font-black text-gray-900 dark:text-white focus:ring-4 focus:ring-brand/10" required />
                       </div>
                       <div class="space-y-2">
                          <label class="text-xs font-black text-gray-400 dark:text-gray-600 uppercase tracking-widest ml-2 flex items-center gap-2">
                             <Phone class="w-3.5 h-3.5" /> رقم الهاتف
                          </label>
                          <input v-model="form.phone" type="tel" class="w-full bg-gray-50 dark:bg-night-800 border-none rounded-2xl py-4 px-6 font-black text-gray-900 dark:text-white focus:ring-4 focus:ring-brand/10" required />
                       </div>
                    </div>

                    <div class="space-y-2">
                       <label class="text-xs font-black text-gray-400 dark:text-gray-600 uppercase tracking-widest ml-2 flex items-center gap-2">
                          <TrendingUp class="w-3.5 h-3.5" /> حجم الطلبات المتوقع شهرياً (اختياري)
                       </label>
                       <select v-model="form.expected_volume" class="w-full bg-gray-50 dark:bg-night-800 border-none rounded-2xl py-4 px-6 font-black text-gray-900 dark:text-white focus:ring-4 focus:ring-brand/10 appearance-none">
                          <option value="">اختر الحجم المتوقع...</option>
                          <option value="small">أقل من 10,000 {{ s.currency_code }}</option>
                          <option value="medium">10,000 - 50,000 {{ s.currency_code }}</option>
                          <option value="large">أكثر من 50,000 {{ s.currency_code }}</option>
                       </select>
                    </div>

                    <div class="space-y-2">
                       <label class="text-xs font-black text-gray-400 dark:text-gray-600 uppercase tracking-widest ml-2 flex items-center gap-2">
                          <MessageSquare class="w-3.5 h-3.5" /> تفاصيل إضافية / طلبات خاصة
                       </label>
                       <textarea v-model="form.notes" rows="4" class="w-full bg-gray-50 dark:bg-night-800 border-none rounded-2xl py-4 px-6 font-black text-gray-900 dark:text-white focus:ring-4 focus:ring-brand/10 resize-none"></textarea>
                    </div>

                    <button
                      type="submit"
                      :disabled="form.processing"
                      class="w-full h-20 bg-gray-900 dark:bg-brand text-white dark:text-night-950 rounded-[2.5rem] font-black text-xl hover:scale-[1.02] active:scale-[0.98] transition-all flex items-center justify-center gap-4 group shadow-2xl shadow-gray-400 dark:shadow-brand/20 disabled:opacity-50 overflow-hidden mt-10"
                    >
                      <div class="absolute inset-0 bg-white/10 -translate-x-full group-hover:translate-x-full transition-transform duration-1000"></div>
                      <span v-if="!form.processing">إرسال الطلب الآن</span>
                      <svg v-else class="animate-spin h-6 w-6" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                      <Send v-if="!form.processing" class="w-6 h-6 group-hover:-translate-x-2 transition-transform" />
                    </button>
                 </form>
                 
                 <div class="mt-10 pt-10 border-t border-gray-100 dark:border-night-800 text-center">
                    <p class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-[0.3em] leading-relaxed">بإرسالك لهذا النموذج، أنت توافق على معالجة بيانات شركتك بخصوص طلبات الانضمام لشبكة الموزعين المعتمدين.</p>
                 </div>
              </div>
           </div>
        </div>
      </div>
    </div>
  </StoreLayout>
</template>

<style scoped>
</style>
