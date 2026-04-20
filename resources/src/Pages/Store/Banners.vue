<script setup>
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import axios from 'axios';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { 
  Image as ImageIcon, 
  Plus, 
  Trash2, 
  Edit, 
  Globe, 
  Link as LinkIcon, 
  Eye, 
  EyeOff,
  MoreVertical,
  Layers,
  X
} from 'lucide-vue-next';

const banners = ref([]);
const loading = ref(false);
const showForm = ref(false);
const saving = ref(false);
const editingId = ref(null);
const form = ref({ title: '', image: '', link: '', is_active: true });

const fetchBanners = async () => {
  loading.value = true;
  try {
    const { data } = await axios.get('/api/store/banners');
    banners.value = data.banners || data.data || [];
  } catch (e) {
    console.error(e);
  } finally { loading.value = false; }
};

const openCreate = () => { 
  editingId.value = null; 
  form.value = { title: '', image: '', link: '', is_active: true }; 
  showForm.value = true; 
};

const openEdit = (b) => { 
  editingId.value = b.id; 
  form.value = { title: b.title||'', image: b.image||'', link: b.link||'', is_active: b.is_active??true }; 
  showForm.value = true; 
};

const submit = async () => {
  saving.value = true;
  try {
    if (editingId.value) await axios.put(`/api/store/banners/${editingId.value}`, form.value);
    else await axios.post('/api/store/banners', form.value);
    showForm.value = false; 
    fetchBanners();
  } catch (e) { 
    alert(e.response?.data?.message || 'فشل الحفظ'); 
  } finally { saving.value = false; }
};

const deleteBanner = async (id) => {
  if (!confirm('حذف البانر؟')) return;
  await axios.delete(`/api/store/banners/${id}`);
  fetchBanners();
};

onMounted(fetchBanners);
</script>

<template>
  <AdminLayout>
    <Head title="بانرات المتجر | إدارة المحتوى" />
    
    <div class="space-y-12 p-6 md:p-14 font-['Cairo'] transition-colors duration-500" dir="rtl">
      
      <!-- Header Section -->
      <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-8">
        <div>
          <div class="flex items-center gap-3 text-brand dark:text-accent text-[10px] font-black uppercase tracking-[0.3em] mb-3">
             <div class="w-8 h-[2px] bg-brand dark:bg-accent"></div>
             <span>الواجهة المرئية للمتجر</span>
          </div>
          <h1 class="text-4xl md:text-5xl font-black text-gray-900 dark:text-white tracking-tighter italic">إدارة البانرات</h1>
        </div>
        
        <button @click="openCreate" class="bg-gray-900 dark:bg-brand hover:scale-[1.05] active:scale-95 text-white dark:text-night-950 px-10 py-5 rounded-[2rem] font-black text-sm uppercase tracking-widest flex items-center gap-3 transition-all shadow-2xl shadow-brand/10 dark:shadow-brand/20">
          <Plus class="h-6 w-6" />
          إضافة بانر إعلاني
        </button>
      </div>

      <!-- Main Content Grid -->
      <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <div v-for="i in 3" :key="i" class="h-64 bg-white dark:bg-night-900 rounded-[3rem] border border-gray-100 dark:border-night-800 animate-pulse"></div>
      </div>
      
      <div v-else-if="banners.length === 0" class="bg-white dark:bg-night-900 rounded-[4rem] p-32 text-center border-2 border-dashed border-gray-100 dark:border-night-800">
        <div class="inline-flex items-center justify-center w-24 h-24 bg-gray-50 dark:bg-night-800 rounded-[2.5rem] text-gray-300 dark:text-gray-700 mb-8">
          <Layers class="w-10 h-10" />
        </div>
        <h3 class="text-2xl font-black text-gray-900 dark:text-white mb-2">لا توجد بانرات حالياً</h3>
        <p class="text-gray-500 dark:text-gray-400 font-medium max-w-sm mx-auto">ابدأ بحملة إعلانية جديدة لجذب العملاء وتنشيط مبيعات المتجر الإلكتروني.</p>
      </div>

      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <div v-for="b in banners" :key="b.id" class="group relative bg-white dark:bg-night-900 rounded-[3rem] overflow-hidden border border-gray-100 dark:border-night-800 shadow-xl shadow-gray-200/50 dark:shadow-black/30 hover:shadow-2xl transition-all duration-500 hover:-translate-y-2">
          
          <!-- Banner Preview -->
          <div class="aspect-[16/9] bg-gray-50 dark:bg-night-800 relative overflow-hidden">
            <img v-if="b.image" :src="b.image" class="w-full h-full object-cover transition-transform duration-1000 group-hover:scale-110" />
            <div v-else class="w-full h-full flex items-center justify-center text-gray-400 dark:text-gray-700">
              <ImageIcon class="w-12 h-12 opacity-20" />
            </div>

            <!-- Overlay Actions -->
            <div class="absolute inset-0 bg-gray-900/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-4 backdrop-blur-sm">
               <button @click="openEdit(b)" class="w-14 h-14 bg-white rounded-2xl flex items-center justify-center text-gray-900 hover:bg-brand hover:text-white transition-all shadow-xl"><Edit class="w-6 h-6" /></button>
               <button @click="deleteBanner(b.id)" class="w-14 h-14 bg-white rounded-2xl flex items-center justify-center text-red-500 hover:bg-red-500 hover:text-white transition-all shadow-xl"><Trash2 class="w-6 h-6" /></button>
            </div>

            <!-- Status Badge -->
            <div class="absolute top-6 right-6">
               <span :class="b.is_active ? 'bg-brand dark:bg-accent text-white dark:text-night-950 shadow-brand/20' : 'bg-gray-500 text-white shadow-gray-500/20'" class="px-5 py-2.5 rounded-2xl text-[10px] font-black shadow-xl uppercase tracking-widest flex items-center gap-2">
                  <div class="w-2 h-2 rounded-full bg-current animate-pulse"></div>
                  {{ b.is_active ? 'نشط الآن' : 'معطل' }}
               </span>
            </div>
          </div>

          <!-- Banner Info -->
          <div class="p-8">
            <h3 class="text-xl font-black text-gray-900 dark:text-white mb-3 truncate">{{ b.title }}</h3>
            <div class="flex items-center gap-3 text-gray-400 dark:text-gray-500">
               <LinkIcon class="w-4 h-4" />
               <p v-if="b.link" class="text-xs font-bold truncate max-w-[200px]">{{ b.link }}</p>
               <p v-else class="text-[10px] font-black uppercase tracking-widest italic opacity-50">لا يوجد رابط وجهة</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Form -->
    <div v-if="showForm" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div @click="showForm = false" class="absolute inset-0 bg-gray-900/40 dark:bg-black/60 backdrop-blur-md"></div>
      
      <div class="bg-white dark:bg-night-900 rounded-[3.5rem] w-full max-w-xl p-10 md:p-14 shadow-3xl relative animate-in fade-in zoom-in duration-300 border border-white dark:border-night-800">
        
        <button @click="showForm = false" class="absolute top-8 left-8 w-12 h-12 bg-gray-50 dark:bg-night-800 rounded-2xl flex items-center justify-center text-gray-400 hover:text-red-500 transition-all">
          <X class="w-6 h-6" />
        </button>

        <header class="mb-12">
          <div class="flex items-center gap-3 text-brand dark:text-accent text-[10px] font-black uppercase tracking-[0.2em] mb-2">
             <div class="w-6 h-1 bg-brand dark:bg-accent"></div>
             <span>{{ editingId ? 'تعديل البيانات' : 'محتوى جديد' }}</span>
          </div>
          <h2 class="text-3xl font-black text-gray-900 dark:text-white italic">{{ editingId ? 'تعديل البانر' : 'إضافة بانر جديد' }}</h2>
        </header>

        <form @submit.prevent="submit" class="space-y-8">
          <div class="space-y-3">
             <label class="text-xs font-black text-gray-400 dark:text-gray-600 uppercase tracking-widest ml-2 block">عنوان البانر *</label>
             <input v-model="form.title" class="w-full h-16 rounded-[1.5rem] bg-gray-50 dark:bg-night-800 border-none px-6 font-black text-gray-900 dark:text-white focus:ring-4 focus:ring-brand/10 dark:focus:ring-accent/10 transition-all" placeholder="خصومات الصيف..." required />
          </div>

          <div class="space-y-3">
             <label class="text-xs font-black text-gray-400 dark:text-gray-600 uppercase tracking-widest ml-2 block">رابط الصورة (URL)</label>
             <div class="relative">
                <input v-model="form.image" class="w-full h-16 rounded-[1.5rem] bg-gray-50 dark:bg-night-800 border-none px-6 font-black text-gray-900 dark:text-white focus:ring-4 focus:ring-brand/10" placeholder="https://..." />
                <ImageIcon class="absolute left-6 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-300 pointer-events-none" />
             </div>
          </div>

          <div class="space-y-3">
             <label class="text-xs font-black text-gray-400 dark:text-gray-600 uppercase tracking-widest ml-2 block">رابط الوجهة (Redirection)</label>
             <div class="relative">
                <input v-model="form.link" class="w-full h-16 rounded-[1.5rem] bg-gray-50 dark:bg-night-800 border-none px-6 font-black text-gray-900 dark:text-white focus:ring-4 focus:ring-brand/10" placeholder="https://store.daw/campaign..." />
                <Globe class="absolute left-6 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-300 pointer-events-none" />
             </div>
          </div>

          <label class="flex items-center gap-4 cursor-pointer group bg-gray-50 dark:bg-night-800 p-6 rounded-[2rem] border border-gray-100 dark:border-night-700 transition-all hover:bg-white dark:hover:bg-night-800">
            <div class="relative w-6 h-6">
               <input v-model="form.is_active" type="checkbox" class="peer hidden" />
               <div class="w-full h-full bg-white dark:bg-night-900 rounded-lg border-2 border-gray-200 dark:border-night-700 peer-checked:bg-brand dark:peer-checked:bg-accent peer-checked:border-transparent transition-all"></div>
               <div class="absolute inset-0 flex items-center justify-center opacity-0 peer-checked:opacity-100 transition-opacity text-white dark:text-night-950 font-black text-xs">✓</div>
            </div>
            <span class="text-sm font-black text-gray-700 dark:text-gray-300">تفعيل البانر فور الحفظ</span>
          </label>

          <footer class="flex items-center gap-4 pt-10 border-t border-gray-100 dark:border-night-800">
             <button @click="showForm = false" type="button" class="flex-1 h-16 rounded-[1.5rem] text-gray-400 font-black hover:text-gray-900 dark:hover:text-white transition-all">إلغاء</button>
             <button type="submit" :disabled="saving" class="flex-[2] h-20 rounded-[2rem] bg-gray-900 dark:bg-brand text-white dark:text-night-950 font-black text-xl shadow-2xl shadow-brand/20 hover:scale-[1.02] active:scale-[0.98] transition-all flex items-center justify-center gap-4">
               <div v-if="saving" class="w-6 h-6 border-4 border-white/30 border-t-white rounded-full animate-spin"></div>
               <span>{{ saving ? 'جاري الحفظ...' : 'حفظ البيانات' }}</span>
             </button>
          </footer>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 5px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(4, 114, 77, 0.1); border-radius: 10px; }
:global(.dark) .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(62, 255, 139, 0.1); }
</style>
