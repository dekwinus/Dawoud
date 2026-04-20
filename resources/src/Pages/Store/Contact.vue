<script setup>
import { useForm, usePage, Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import StoreLayout from '@/Layouts/StoreLayout.vue';
import { 
  Mail, 
  Phone, 
  MapPin, 
  Send, 
  ArrowRight,
  MessageSquare
} from 'lucide-vue-next';

const props = defineProps({ s: Object });

const form = useForm({
  name: '',
  email: '',
  phone: '',
  subject: '',
  message: '',
  company: '', // Honeypot
});

const submit = () => {
  form.post('/contact', {
    preserveScroll: true,
    onSuccess: () => form.reset(),
  });
};

const flash = computed(() => usePage().props.flash || {});
</script>

<template>
  <Head :title="`اتصل بنا | ${s?.store_name || 'المتجر'}`" />

  <StoreLayout>
    <div class="bg-gray-50 dark:bg-night-950 transition-colors duration-500 overflow-hidden">
      <!-- Decoration Elements -->
      <div class="absolute top-0 right-0 w-[600px] h-[600px] bg-brand/5 dark:bg-accent/5 rounded-full blur-[120px] -translate-y-1/2 translate-x-1/3 pointer-events-none"></div>

      <section class="relative py-20 md:py-32">
        <div class="container mx-auto px-4 max-w-7xl">
          
          <!-- Header -->
          <div class="max-w-3xl mb-20">
            <div class="flex items-center gap-3 text-brand dark:text-accent mb-6">
              <div class="w-12 h-[2px] bg-brand dark:bg-accent"></div>
              <span class="text-xs font-black uppercase tracking-[0.4em]">تواصل معنا</span>
            </div>
            <h1 class="text-3xl md:text-5xl font-black text-gray-900 dark:text-white leading-tight tracking-tighter mb-8 italic">
              نحن هنا لمساعدتك في <br/>
              <span class="text-brand dark:text-accent not-italic">توسيع أعمالك.</span>
            </h1>
            <p class="text-xl text-gray-400 dark:text-gray-500 font-medium leading-relaxed max-w-2xl">
              سواء كنت تبحث عن استفسار تقني أو ترغب في بدء شراكة B2B جديدة، فريقنا المتخصص مستعد للرد عليك خلال ساعات العمل الرسمية.
            </p>
          </div>

          <div class="grid lg:grid-cols-12 gap-12 items-start relative">
            
            <!-- Contact Info Cards -->
            <div class="lg:col-span-4 space-y-6">
              
              <!-- Email Card -->
              <div class="group bg-white dark:bg-night-900 p-8 rounded-[2.5rem] border border-gray-100 dark:border-night-800 shadow-xl shadow-gray-200/50 dark:shadow-black/20 hover:border-brand/40 dark:hover:border-accent/40 transition-all duration-500">
                <div class="w-16 h-16 bg-brand/5 dark:bg-accent/5 text-brand dark:text-accent rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-500">
                  <Mail class="w-8 h-8" />
                </div>
                <h3 class="text-xl font-black text-gray-900 dark:text-white mb-2">راسلنا مباشرة</h3>
                <p class="text-sm text-gray-400 dark:text-gray-500 font-bold mb-4 uppercase tracking-widest">فريق المبيعات والدعم</p>
                <a v-if="s?.contact_email" :href="`mailto:${s.contact_email}`" class="text-lg font-black text-brand dark:text-accent hover:opacity-80 transition-opacity underline decoration-brand/20 underline-offset-8">
                  {{ s.contact_email }}
                </a>
                <p v-else class="text-gray-400 italic">غير متوفر حالياً</p>
              </div>

              <!-- Phone Card -->
              <div class="group bg-white dark:bg-night-900 p-8 rounded-[2.5rem] border border-gray-100 dark:border-night-800 shadow-xl shadow-gray-200/50 dark:shadow-black/20 hover:border-brand/40 dark:hover:border-accent/40 transition-all duration-500">
                <div class="w-16 h-16 bg-brand/5 dark:bg-accent/5 text-brand dark:text-accent rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-500">
                  <Phone class="w-8 h-8" />
                </div>
                <h3 class="text-xl font-black text-gray-900 dark:text-white mb-2">اتصل بنا</h3>
                <p class="text-sm text-gray-400 dark:text-gray-500 font-bold mb-4 uppercase tracking-widest">دعم العملاء (الأحد - الخميس)</p>
                <a v-if="s?.contact_phone" :href="`tel:${s.contact_phone.replace(/\s+/g, '')}`" class="text-2xl font-black text-gray-900 dark:text-white hover:text-brand dark:hover:text-accent transition-colors tabular-nums">
                  {{ s.contact_phone }}
                </a>
                <p v-else class="text-gray-400 italic">غير متوفر حالياً</p>
              </div>

              <!-- Address & Map -->
              <div class="group bg-white dark:bg-night-900 p-8 rounded-[2.5rem] border border-gray-100 dark:border-night-800 shadow-xl shadow-gray-200/50 dark:shadow-black/20 hover:border-brand/40 dark:hover:border-accent/40 transition-all duration-500">
                <div class="w-16 h-16 bg-brand/5 dark:bg-accent/5 text-brand dark:text-accent rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-500">
                  <MapPin class="w-8 h-8" />
                </div>
                <h3 class="text-xl font-black text-gray-900 dark:text-white mb-2">مقرنا</h3>
                <p v-if="s?.contact_address" class="text-gray-500 dark:text-gray-400 font-medium leading-relaxed mb-6">
                  {{ s.contact_address }}
                </p>
                
                <div v-if="s?.contact_address" class="rounded-3xl overflow-hidden grayscale hover:grayscale-0 transition-all duration-700 h-40 shadow-inner">
                   <iframe
                    :src="`https://www.google.com/maps?q=${encodeURIComponent(s.contact_address)}&output=embed`"
                    class="w-full h-full border-0"
                    loading="lazy"
                    allowfullscreen>
                  </iframe>
                </div>
              </div>
            </div>

            <!-- Form Section -->
            <div class="lg:col-span-8">
              <div class="bg-white dark:bg-night-900 p-8 md:p-16 rounded-[3.5rem] border border-gray-100 dark:border-night-800 shadow-2xl shadow-gray-200/50 dark:shadow-black/40 relative overflow-hidden">
                
                <!-- Success Message Overlay -->
                <div v-if="flash.success" class="absolute inset-0 bg-brand text-white z-40 flex flex-col items-center justify-center p-12 text-center animate-in fade-in duration-500">
                   <div class="w-24 h-24 bg-white/20 rounded-full flex items-center justify-center mb-8 animate-bounce">
                     <Send class="w-10 h-10" />
                   </div>
                   <h2 class="text-4xl font-black mb-4">تم الإرسال بنجاح!</h2>
                   <p class="text-xl font-medium opacity-80 mb-10 max-w-md">شكراً لتواصلك معنا. سنقوم بالرد عليك عبر البريد الإلكتروني في أقرب وقت ممكن.</p>
                   <button @click="flash.success = null" class="bg-white text-brand px-10 py-4 rounded-2xl font-black shadow-xl hover:scale-105 active:scale-95 transition-all">إرسال رسالة أخرى</button>
                </div>

                <div class="relative z-10">
                  <div class="flex items-center gap-4 mb-10">
                    <div class="w-12 h-12 bg-brand dark:bg-accent rounded-full flex items-center justify-center text-white dark:text-night-950">
                      <MessageSquare class="w-6 h-6" />
                    </div>
                    <div>
                      <h2 class="text-3xl font-black text-gray-900 dark:text-white tracking-tight">أرسل رسالتك</h2>
                      <p class="text-gray-400 dark:text-gray-600 font-bold uppercase tracking-widest text-[10px] mt-1">نحن نقرأ كل رسالة تصلنا</p>
                    </div>
                  </div>

                  <form @submit.prevent="submit" class="grid md:grid-cols-2 gap-x-8 gap-y-10">
                    <div class="space-y-3">
                      <label class="text-xs font-black text-gray-400 dark:text-gray-600 uppercase tracking-[0.2em] ml-2 block">الاسم بالكامل *</label>
                      <input v-model="form.name" type="text"
                        class="w-full bg-gray-50 dark:bg-night-800 border-none rounded-2xl py-5 px-6 focus:ring-4 focus:ring-brand/10 dark:focus:ring-accent/10 transition-all font-black text-gray-900 dark:text-white placeholder-gray-300 dark:placeholder-gray-700"
                        placeholder="مصطفى محمود..." required />
                      <p v-if="form.errors.name" class="text-xs text-red-500 font-bold mt-1">{{ form.errors.name }}</p>
                    </div>

                    <div class="space-y-3">
                      <label class="text-xs font-black text-gray-400 dark:text-gray-600 uppercase tracking-[0.2em] ml-2 block">البريد الإلكتروني *</label>
                      <input v-model="form.email" type="email"
                        class="w-full bg-gray-50 dark:bg-night-800 border-none rounded-2xl py-5 px-6 focus:ring-4 focus:ring-brand/10 dark:focus:ring-accent/10 transition-all font-black text-gray-900 dark:text-white placeholder-gray-300 dark:placeholder-gray-700"
                        placeholder="name@company.com" required />
                      <p v-if="form.errors.email" class="text-xs text-red-500 font-bold mt-1">{{ form.errors.email }}</p>
                    </div>

                    <div class="space-y-3">
                      <label class="text-xs font-black text-gray-400 dark:text-gray-600 uppercase tracking-[0.2em] ml-2 block">رقم الجوال</label>
                      <input v-model="form.phone" type="text"
                        class="w-full bg-gray-50 dark:bg-night-800 border-none rounded-2xl py-5 px-6 focus:ring-4 focus:ring-brand/10 dark:focus:ring-accent/10 transition-all font-black text-gray-900 dark:text-white placeholder-gray-300 dark:placeholder-gray-700"
                        placeholder="+20 123..." />
                    </div>

                    <div class="space-y-3">
                      <label class="text-xs font-black text-gray-400 dark:text-gray-600 uppercase tracking-[0.2em] ml-2 block">الموضوع</label>
                      <input v-model="form.subject" type="text"
                        class="w-full bg-gray-50 dark:bg-night-800 border-none rounded-2xl py-5 px-6 focus:ring-4 focus:ring-brand/10 dark:focus:ring-accent/10 transition-all font-black text-gray-900 dark:text-white placeholder-gray-300 dark:placeholder-gray-700"
                        placeholder="كيف يمكننا مساعدتك؟" />
                    </div>

                    <div class="md:col-span-2 space-y-3">
                      <label class="text-xs font-black text-gray-400 dark:text-gray-600 uppercase tracking-[0.2em] ml-2 block">تفاصيل الرسالة *</label>
                      <textarea v-model="form.message" rows="5"
                        class="w-full bg-gray-50 dark:bg-night-800 border-none rounded-[2rem] py-5 px-8 focus:ring-4 focus:ring-brand/10 dark:focus:ring-accent/10 transition-all font-black text-gray-900 dark:text-white placeholder-gray-300 dark:placeholder-gray-700 resize-none"
                        placeholder="اكتب رسالتك هنا بالتفصيل..."
                        required></textarea>
                      <p v-if="form.errors.message" class="text-xs text-red-500 font-bold mt-1">{{ form.errors.message }}</p>
                    </div>

                    <!-- Honeypot -->
                    <input v-model="form.company" type="text" class="hidden" tabindex="-1" />

                    <div class="md:col-span-2 pt-6">
                      <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full h-20 bg-gray-900 dark:bg-brand text-white dark:text-night-950 rounded-[2rem] font-black text-xl hover:scale-[1.02] active:scale-[0.98] transition-all flex items-center justify-center gap-4 group shadow-2xl shadow-gray-400 dark:shadow-brand/20 disabled:opacity-50 overflow-hidden relative"
                      >
                        <div class="absolute inset-0 bg-white/10 -translate-x-full group-hover:translate-x-full transition-transform duration-700"></div>
                        <Send v-if="!form.processing" class="w-6 h-6 group-hover:-translate-y-1 group-hover:translate-x-1 transition-transform" />
                        <svg v-else class="animate-spin h-6 w-6" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <span>{{ form.processing ? 'جاري الإرسال...' : 'إرسال الرسالة الآن' }}</span>
                      </button>
                    </div>
                  </form>
                </div>
              </div>

              <!-- Extra CTA -->
              <div class="mt-12 flex items-center justify-between px-8">
                 <p class="text-sm font-bold text-gray-400 dark:text-gray-600">هل تبحث عن إجابات سريعة؟</p>
                 <Link href="/shop" class="flex items-center gap-2 text-brand dark:text-accent font-black text-sm group">
                   تصفح المتجر
                   <ArrowRight class="w-4 h-4 group-hover:translate-x-[-4px] transition-transform" />
                 </Link>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
  </StoreLayout>
</template>

<style scoped>
.prose { max-width: none; }
</style>
