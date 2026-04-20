<script setup>
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import axios from 'axios';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { CreditCard, Plus, Trash2, Edit } from 'lucide-vue-next';

const methods = ref([]);
const loading = ref(false);
const showForm = ref(false);
const saving = ref(false);
const editingId = ref(null);
const form = ref({ name: '', description: '' });

const fetchMethods = async () => {
  loading.value = true;
  try {
    const { data } = await axios.get('/api/payment_methods');
    methods.value = data.methods || [];
  } finally { loading.value = false; }
};

const openCreate = () => { editingId.value = null; form.value = { name: '', description: '' }; showForm.value = true; };
const openEdit = (m) => { editingId.value = m.id; form.value = { name: m.name||'', description: m.description||'' }; showForm.value = true; };

const submit = async () => {
  saving.value = true;
  try {
    if (editingId.value) await axios.put(`/api/payment_methods/${editingId.value}`, form.value);
    else await axios.post('/api/payment_methods', form.value);
    showForm.value = false; fetchMethods();
  } catch (e) { alert(e.response?.data?.message || 'فشل الحفظ'); }
  finally { saving.value = false; }
};

const deleteMethod = async (id) => {
  if (!confirm('حذف طريقة الدفع؟')) return;
  await axios.delete(`/api/payment_methods/${id}`);
  fetchMethods();
};

onMounted(fetchMethods);
</script>

<template>
  <AdminLayout>
    <Head title="طرق الدفع" />
    <div class="space-y-8 p-6 md:p-10 font-['Cairo']" dir="rtl">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
          <div class="flex items-center gap-2 text-[#04724D] text-xs font-black uppercase tracking-widest mb-1"><CreditCard class="w-4 h-4" />الإعدادات</div>
          <h1 class="text-4xl font-black text-gray-900 dark:text-white">طرق الدفع</h1>
        </div>
        <button @click="openCreate" class="bg-[#04724D] hover:bg-[#035a3d] text-[#3EFF8B] px-8 py-4 rounded-[20px] font-black text-xs uppercase tracking-widest flex items-center gap-2 transition-all">
          <Plus class="h-5 w-5" />إضافة طريقة
        </button>
      </div>

      <div class="bg-white dark:bg-[#0A0A0A] rounded-[40px] border border-gray-100 dark:border-gray-800 overflow-hidden">
        <div v-if="loading" class="p-8 space-y-3">
          <div v-for="i in 4" :key="i" class="h-16 bg-gray-100 dark:bg-white/5 rounded-2xl animate-pulse"></div>
        </div>
        <div v-else-if="methods.length === 0" class="py-20 text-center text-gray-400 font-black text-sm">لا توجد طرق دفع</div>
        <div v-else class="divide-y divide-gray-50 dark:divide-gray-800">
          <div v-for="m in methods" :key="m.id" class="flex items-center justify-between px-8 py-5 group hover:bg-gray-50/50 dark:hover:bg-white/[0.01] transition-all">
            <div class="flex items-center gap-4">
              <div class="w-12 h-12 rounded-2xl bg-[#04724D]/10 flex items-center justify-center"><CreditCard class="w-5 h-5 text-[#04724D]" /></div>
              <div>
                <p class="font-black text-gray-900 dark:text-white">{{ m.name }}</p>
                <p class="text-xs text-gray-400">{{ m.description || '—' }}</p>
              </div>
            </div>
            <div class="flex gap-2 opacity-0 group-hover:opacity-100 transition-all">
              <button @click="openEdit(m)" class="p-3 bg-white dark:bg-[#1A1A1A] text-gray-400 hover:text-[#04724D] rounded-2xl border border-gray-100 dark:border-gray-800"><Edit class="h-4 w-4" /></button>
              <button @click="deleteMethod(m.id)" class="p-3 bg-white dark:bg-[#1A1A1A] text-gray-400 hover:text-red-500 rounded-2xl border border-gray-100 dark:border-gray-800"><Trash2 class="h-4 w-4" /></button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-if="showForm" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
      <div class="bg-white dark:bg-[#121212] rounded-[32px] w-full max-w-sm p-8 space-y-5 shadow-2xl">
        <h2 class="text-xl font-black text-gray-900 dark:text-white">{{ editingId ? 'تعديل طريقة' : 'إضافة طريقة دفع' }}</h2>
        <div class="space-y-4">
          <div class="space-y-1"><label class="text-xs text-gray-400 font-black">الاسم *</label><input v-model="form.name" class="w-full rounded-2xl bg-gray-50 dark:bg-white/5 border-none py-3 px-4 text-sm font-black dark:text-white" /></div>
          <div class="space-y-1"><label class="text-xs text-gray-400 font-black">الوصف</label><input v-model="form.description" class="w-full rounded-2xl bg-gray-50 dark:bg-white/5 border-none py-3 px-4 text-sm font-black dark:text-white" /></div>
        </div>
        <div class="flex justify-end gap-3">
          <button @click="showForm=false" class="px-6 py-3 rounded-2xl bg-gray-100 dark:bg-white/10 text-sm font-black text-gray-700 dark:text-gray-300">إلغاء</button>
          <button @click="submit" :disabled="saving" class="px-6 py-3 rounded-2xl bg-[#04724D] text-white text-sm font-black disabled:opacity-60">{{ saving ? 'جاري...' : 'حفظ' }}</button>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
