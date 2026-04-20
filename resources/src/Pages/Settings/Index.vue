<script setup>
import { Head, router } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import axios from 'axios';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { 
  Settings as Cog6ToothIcon, 
  Palette as PaintBrushIcon, 
  Monitor as ComputerDesktopIcon, 
  CircleDollarSign as CurrencyDollarIcon, 
  CloudUpload as CloudArrowUpIcon,
  CheckCircle2 as CheckCircleIcon,
  Image as PhotoIcon,
  Globe as GlobeAltIcon,
  Phone as PhoneIcon,
  Mail as EnvelopeIcon,
  MapPin as MapPinIcon,
  ShoppingBag as ShoppingBagIcon,
  ChevronLeft
} from 'lucide-vue-next';

const props = defineProps({
  settings: Object,
  currencies: Array,
  clients: Array,
  warehouses: Array,
  languages: Array,
  timezones: Array,
});

const activeTab = ref('general');
const loading = ref(false);
const form = ref({ ...props.settings });
const logoPreview = ref(null);
const faviconPreview = ref(null);

const tabs = [
  { id: 'general', name: 'إعدادات عامة', icon: Cog6ToothIcon },
  { id: 'appearance', name: 'المظهر والهوية', icon: PaintBrushIcon },
  { id: 'pos', name: 'إعدادات نقطة البيع', icon: ComputerDesktopIcon },
  { id: 'finance', name: 'المالية والضرائب', icon: CurrencyDollarIcon },
  { id: 'backups', name: 'النسخ الاحتياطي', icon: CloudArrowUpIcon },
  { id: 'store', name: 'إعدادات المتجر', icon: ShoppingBagIcon },
];

const onLogoChange = (e) => {
  const file = e.target.files[0];
  if (file) {
    form.value.logo_file = file;
    logoPreview.value = URL.createObjectURL(file);
  }
};

const saveSettings = async () => {
  loading.value = true;
  try {
    const formData = new FormData();
    Object.keys(form.value).forEach(key => {
      if (form.value[key] !== null && form.value[key] !== undefined) {
        formData.append(key, form.value[key]);
      }
    });
    
    // Custom handling for file uploads
    if (form.value.logo_file) formData.append('logo', form.value.logo_file);

    await axios.post(`/api/settings/${form.value.id}?_method=PUT`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });
    
    alert('تم حفظ الإعدادات بنجاح!');
    router.reload();
  } catch (error) {
    console.error('Save failed:', error);
    alert('فشل حفظ الإعدادات');
  } finally {
    loading.value = false;
  }
};
</script>

<template>
  <AdminLayout>
    <Head title="إعدادات النظام - DawPOS" />
    
    <div class="p-8 space-y-8" dir="rtl">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="text-right">
                <h1 class="text-xl font-black text-gray-900 dark:text-white font-['Cairo'] tracking-tight">إعدادات النظام</h1>
                <p class="text-gray-500 dark:text-gray-400 font-medium font-['Cairo']">تحكم في التفضيلات العامة وعلامتك التجارية</p>
            </div>
            <button 
                @click="saveSettings"
                :disabled="loading"
                class="bg-[#04724D] hover:bg-[#058a5e] text-white px-10 py-4 rounded-2xl font-black text-sm shadow-xl shadow-[#04724D]/20 transition-all flex items-center gap-2 disabled:opacity-70 font-['Cairo']"
            >
                <div v-if="loading" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></div>
                حفظ الإعدادات
            </button>
        </div>

        <div class="flex flex-col lg:flex-row gap-8">
        <!-- Sidebar Navigation -->
        <div class="lg:w-80 space-y-3">
            <button 
            v-for="tab in tabs" 
            :key="tab.id"
            @click="activeTab = tab.id"
            :class="activeTab === tab.id ? 'bg-[#04724D]/5 text-[#04724D] border-[#04724D]/20 shadow-sm' : 'text-gray-500 border-transparent hover:bg-gray-50 dark:hover:bg-white/5'"
            class="w-full flex items-center justify-between p-5 rounded-2xl border transition-all font-black text-sm group font-['Cairo']"
            >
                <div class="flex items-center gap-4">
                    <component :is="tab.icon" :class="activeTab === tab.id ? 'text-[#04724D]' : 'text-gray-400 group-hover:text-gray-600'" class="w-5 h-5 transition-colors" />
                    <span>{{ tab.name }}</span>
                </div>
                <ChevronLeft v-if="activeTab === tab.id" class="w-4 h-4 opacity-50" />
            </button>
        </div>

        <!-- Settings Content -->
        <div class="flex-1">
            <div class="bg-white dark:bg-[#121212] rounded-[40px] shadow-sm border border-gray-100 dark:border-gray-800 overflow-hidden min-h-[600px]">
            
            <!-- General Settings -->
            <div v-if="activeTab === 'general'" class="p-10 space-y-10 animate-in fade-in slide-in-from-left-4 duration-500 text-right">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                <div class="space-y-3">
                    <label class="text-xs font-black text-gray-400 uppercase mr-1 font-['Cairo'] tracking-widest">اسم الشركة</label>
                    <input v-model="form.CompanyName" type="text" class="w-full bg-gray-50 dark:bg-white/5 border-none rounded-2xl p-5 text-sm font-black text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-[#04724D]/20 font-['Cairo'] transition-all" />
                </div>
                <div class="space-y-3">
                    <label class="text-xs font-black text-gray-400 uppercase mr-1 font-['Cairo'] tracking-widest">البريد الإلكتروني للشركة</label>
                    <input v-model="form.email" type="email" class="w-full bg-gray-50 dark:bg-white/5 border-none rounded-2xl p-5 text-sm font-black text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-[#04724D]/20 font-mono transition-all text-left" />
                </div>
                <div class="space-y-3">
                    <label class="text-xs font-black text-gray-400 uppercase mr-1 font-['Cairo'] tracking-widest">رقم الهاتف</label>
                    <input v-model="form.CompanyPhone" type="text" class="w-full bg-gray-50 dark:bg-white/5 border-none rounded-2xl p-5 text-sm font-black text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-[#04724D]/20 font-mono transition-all text-left px-5" dir="ltr" />
                </div>
                <div class="space-y-3">
                    <label class="text-xs font-black text-gray-400 uppercase mr-1 font-['Cairo'] tracking-widest">المستودع الافتراضي</label>
                    <select v-model="form.warehouse_id" class="w-full bg-gray-50 dark:bg-white/5 border-none rounded-2xl p-5 text-sm font-black text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-[#04724D]/20 font-['Cairo'] transition-all">
                    <option v-for="w in warehouses" :key="w.id" :value="w.id">{{ w.name }}</option>
                    </select>
                </div>
                <div class="md:col-span-2 space-y-3">
                    <label class="text-xs font-black text-gray-400 uppercase mr-1 font-['Cairo'] tracking-widest">عنوان الشركة</label>
                    <textarea v-model="form.CompanyAdress" rows="4" class="w-full bg-gray-50 dark:bg-white/5 border-none rounded-2xl p-5 text-sm font-black text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-[#04724D]/20 font-['Cairo'] transition-all resize-none"></textarea>
                </div>
                </div>
            </div>

            <!-- Appearance -->
            <div v-if="activeTab === 'appearance'" class="p-10 space-y-12 animate-in fade-in slide-in-from-left-4 duration-500 text-right">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-15">
                <!-- Logo Upload -->
                <div class="space-y-6">
                    <div>
                    <h4 class="text-base font-black text-gray-900 dark:text-white mb-1 font-['Cairo']">شعار الشركة</h4>
                    <p class="text-xs text-gray-400 font-bold font-['Cairo']">يستخدم في شاشة تسجيل الدخول والتقارير.</p>
                    </div>
                    <div class="relative group">
                    <div class="w-full h-56 bg-gray-50 dark:bg-white/5 rounded-[32px] border-2 border-dashed border-gray-200 dark:border-gray-800 flex flex-col items-center justify-center overflow-hidden transition-all group-hover:border-[#04724D]/30 shadow-inner">
                        <img v-if="logoPreview || form.logo" :src="logoPreview || '/images/' + form.logo" class="max-h-full max-w-full object-contain p-6" />
                        <div v-else class="flex flex-col items-center text-gray-400">
                        <PhotoIcon class="w-12 h-12 mb-3 opacity-20" />
                        <span class="text-xs font-black font-['Cairo']">يوصى بـ 800x800 بكسل</span>
                        </div>
                    </div>
                    <input type="file" @change="onLogoChange" class="absolute inset-0 opacity-0 cursor-pointer" />
                    </div>
                </div>

                <!-- Brand Styling -->
                <div class="space-y-8">
                    <div>
                    <h4 class="text-base font-black text-gray-900 dark:text-white mb-1 font-['Cairo']">تجربة العلامة التجارية</h4>
                    <p class="text-xs text-gray-400 font-bold font-['Cairo']">تخصيص السلوك البصري للتطبيق.</p>
                    </div>
                    <div class="space-y-4">
                    <div class="flex items-center justify-between p-6 bg-gray-50 dark:bg-white/5 rounded-[24px] border border-transparent hover:border-gray-100 dark:hover:border-white/10 transition-all font-['Cairo']">
                        <span class="text-sm font-black text-gray-700 dark:text-gray-300">دعم الوضع الداكن (Dark Mode)</span>
                        <button 
                        @click="form.dark_mode = !form.dark_mode"
                        :class="form.dark_mode ? 'bg-[#04724D]' : 'bg-gray-200 dark:bg-gray-800'"
                        class="relative inline-flex h-7 w-12 items-center rounded-full transition-colors focus:outline-none"
                        >
                        <span :class="form.dark_mode ? '-translate-x-6' : '-translate-x-1'" class="inline-block h-5 w-5 transform rounded-full bg-white transition-transform"></span>
                        </button>
                    </div>
                    <div class="flex items-center justify-between p-6 bg-gray-50 dark:bg-white/5 rounded-[24px] border border-transparent hover:border-gray-100 dark:hover:border-white/10 transition-all font-['Cairo']">
                        <span class="text-sm font-black text-gray-700 dark:text-gray-300">تخطيط RTL (عربي)</span>
                        <button 
                        @click="form.rtl = !form.rtl"
                        :class="form.rtl ? 'bg-[#04724D]' : 'bg-gray-200 dark:bg-gray-800'"
                        class="relative inline-flex h-7 w-12 items-center rounded-full transition-colors focus:outline-none"
                        >
                        <span :class="form.rtl ? '-translate-x-6' : '-translate-x-1'" class="inline-block h-5 w-5 transform rounded-full bg-white transition-transform"></span>
                        </button>
                    </div>
                    </div>
                </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                <div class="space-y-3">
                    <label class="text-xs font-black text-gray-400 uppercase mr-1 font-['Cairo'] tracking-widest">نص التذييل (Footer)</label>
                    <input v-model="form.footer" type="text" class="w-full bg-gray-50 dark:bg-white/5 border-none rounded-2xl p-5 text-sm font-black text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-[#04724D]/20 font-['Cairo'] transition-all" />
                </div>
                <div class="space-y-3">
                    <label class="text-xs font-black text-gray-400 uppercase mr-1 font-['Cairo'] tracking-widest">تطوير بواسطة</label>
                    <input v-model="form.developed_by" type="text" class="w-full bg-gray-50 dark:bg-white/5 border-none rounded-2xl p-5 text-sm font-black text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-[#04724D]/20 font-['Cairo'] transition-all" />
                </div>
                </div>
            </div>

            <!-- Placeholder for other tabs -->
            <div v-if="['pos', 'finance', 'backups', 'store'].includes(activeTab)" class="p-20 text-center flex flex-col items-center justify-center space-y-6 opacity-30 min-h-[500px]">
                <div class="relative">
                    <div class="absolute inset-0 bg-[#04724D] blur-[60px] opacity-20"></div>
                    <Cog6ToothIcon class="w-20 h-20 text-[#تفضيص #04724D animate-spin-slow relative z-10" />
                </div>
                <p class="text-lg font-black text-gray-400 font-['Cairo']">يتم حالياً وضع اللمسات النهائية على هذا القسم...</p>
            </div>

            <!-- Store Settings -->
            <div v-if="activeTab === 'store'" class="p-10 space-y-10 animate-in fade-in slide-in-from-left-4 duration-500 text-right">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                    <div class="space-y-3">
                        <label class="text-xs font-bold text-gray-400 uppercase mr-1 font-['Cairo'] tracking-widest">اسم المتجر</label>
                        <input v-model="form.store_name" type="text" class="w-full bg-gray-50 dark:bg-white/5 border-none rounded-2xl p-5 text-sm font-bold text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-[#04724D]/20 font-['Cairo'] transition-all" placeholder="اسم المتجر الإلكتروني" />
                    </div>
                    <div class="space-y-3">
                        <label class="text-xs font-bold text-gray-400 uppercase mr-1 font-['Cairo'] tracking-widest">الوصف</label>
                        <textarea v-model="form.store_description" rows="3" class="w-full bg-gray-50 dark:bg-white/5 border-none rounded-2xl p-5 text-sm font-bold text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-[#04724D]/20 font-['Cairo'] transition-all resize-none" placeholder="وصف المتجر"></textarea>
                    </div>
                    <div class="space-y-3">
                        <label class="text-xs font-bold text-gray-400 uppercase mr-1 font-['Cairo'] tracking-widest">العملة</label>
                        <select v-model="form.store_currency" class="w-full bg-gray-50 dark:bg-white/5 border-none rounded-2xl p-5 text-sm font-bold text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-[#04724D]/20 font-['Cairo'] transition-all">
                            <option value="">اخترار العملة</option>
                            <option v-for="c in currencies" :key="c.id" :value="c.id">{{ c.name }}</option>
                        </select>
                    </div>
                    <div class="space-y-3">
                        <label class="text-xs font-bold text-gray-400 uppercase mr-1 font-['Cairo'] tracking-widest">اللغة الافتراضية</label>
                        <select v-model="form.store_language" class="w-full bg-gray-50 dark:bg-white/5 border-none rounded-2xl p-5 text-sm font-bold text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-[#04724D]/20 font-['Cairo'] transition-all">
                            <option value="">اخترار اللغة</option>
                            <option v-for="l in languages" :key="l.locale" :value="l.locale">{{ l.name }}</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                    <div class="space-y-3">
                        <label class="text-xs font-bold text-gray-400 uppercase mr-1 font-['Cairo'] tracking-widest">البريد للطلبات</label>
                        <input v-model="form.store_email" type="email" class="w-full bg-gray-50 dark:bg-white/5 border-none rounded-2xl p-5 text-sm font-bold text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-[#04724D]/20 font-['Cairo'] transition-all" placeholder="store@example.com" />
                    </div>
                    <div class="space-y-3">
                        <label class="text-xs font-bold text-gray-400 uppercase mr-1 font-['Cairo'] tracking-widest">رقم الهاتف للدعم</label>
                        <input v-model="form.store_phone" type="text" class="w-full bg-gray-50 dark:bg-white/5 border-none rounded-2xl p-5 text-sm font-bold text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-2 focus:ring-[#04724D]/20 font-['Cairo'] transition-all" placeholder="+966 123 4567" />
                    </div>
                    <div class="space-y-3">
                        <label class="text-xs font-bold text-gray-400 uppercase mr-1 font-['Cairo'] tracking-widest">عنوان المتجر</label>
                        <input v-model="form.store_address" type="text" class="w-full bg-gray-50 dark:bg-white/5 border-none rounded-2xl p-5 text-sm font-bold text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-2 focus:ring-[#04724D]/20 font-['Cairo'] transition-all" placeholder="العنوان الكامل" />
                    </div>
                    <div class="space-y-3">
                        <label class="text-xs font-bold text-gray-400 uppercase mr-1 font-['Cairo'] tracking-widest">مستودع الافتراضي للطلبات</label>
                        <select v-model="form.store_warehouse" class="w-full bg-gray-50 dark:bg-white/5 border-none rounded-2xl p-5 text-sm font-bold text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-[#04724D]/20 font-['Cairo'] transition-all">
                            <option value="">اخترار المستودع</option>
                            <option v-for="w in warehouses" :key="w.id" :value="w.id">{{ w.name }}</option>
                        </select>
                    </div>
                </div>
                <div class="space-y-3">
                    <div class="flex items-center justify-between p-6 bg-gray-50 dark:bg-white/5 rounded-2xl border border-transparent hover:border-gray-100 dark:hover:border-white/10 transition-all font-['Cairo']">
                        <span class="text-sm font-bold text-gray-700 dark:text-gray-300">تفعيل المتجر</span>
                        <button 
                        @click="form.store_enabled = !form.store_enabled"
                        :class="form.store_enabled ? 'bg-[#04724D]' : 'bg-gray-200 dark:bg-gray-800'"
                        class="relative inline-flex h-7 w-12 items-center rounded-full transition-colors focus:outline-none"
                        >
                        <span :class="form.store_enabled ? '-translate-x-6' : '-translate-x-1'" class="inline-block h-5 w-5 transform rounded-full bg-white transition-transform"></span>
                        </button>
                    </div>
                    <div class="flex items-center justify-between p-6 bg-gray-50 dark:bg-white/5 rounded-2xl border border-transparent hover:border-gray-100 dark:hover:border-white/10 transition-all font-['Cairo']">
                        <span class="text-sm font-bold text-gray-700 dark:text-300">السلة الوصول للضيوف</span>
                        <button 
                        @click="form.allow_guest_checkout = !form.allow_guest_checkout"
                        :class="form.allow_guest_checkout ? 'bg-[#04724D]' : 'bg-gray-200 dark:bg-gray-800'"
                        class="relative inline-flex h-7 w-12 items-center rounded-full transition-colors focus:outline-none"
                        >
                        <span :class="form.allow_guest_checkout ? '-translate-x-6' : '-translate-x-1'" class="inline-block h-5 w-5 transform rounded-full bg-white transition-transform"></span>
                        </button>
                    </div>
                </div>
            </div>

            </div>
        </div>
        </div>
    </div>
  </AdminLayout>
</template>

<style scoped>
@keyframes spin-slow {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}
.animate-spin-slow {
  animation: spin-slow 12s linear infinite;
}
input:focus, select:focus, textarea:focus {
  box-shadow: 0 10px 15px -3px rgba(4, 114, 77, 0.1), 0 4px 6px -2px rgba(4, 114, 77, 0.05);
}
</style>
