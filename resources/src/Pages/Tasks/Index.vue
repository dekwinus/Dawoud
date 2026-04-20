<script setup>
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import axios from 'axios';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { CheckSquare, Plus, Trash2, Edit } from 'lucide-vue-next';

const tasks = ref([]);
const loading = ref(false);
const showForm = ref(false);
const saving = ref(false);
const editingId = ref(null);
const form = ref({ title: '', description: '', project_id: '', assigned_to: '', status: 'pending', due_date: '' });

const fetchTasks = async () => {
  loading.value = true;
  try {
    const { data } = await axios.get('/api/tasks');
    tasks.value = data.tasks || data.data || [];
  } finally { loading.value = false; }
};

const openCreate = () => { editingId.value = null; form.value = { title: '', description: '', project_id: '', assigned_to: '', status: 'pending', due_date: '' }; showForm.value = true; };
const openEdit = (t) => { editingId.value = t.id; form.value = { title: t.title||'', description: t.description||'', project_id: t.project_id||'', assigned_to: t.assigned_to||'', status: t.status||'pending', due_date: t.due_date||'' }; showForm.value = true; };

const submit = async () => {
  saving.value = true;
  try {
    if (editingId.value) await axios.put(`/api/tasks/${editingId.value}`, form.value);
    else await axios.post('/api/tasks', form.value);
    showForm.value = false; fetchTasks();
  } catch (e) { alert(e.response?.data?.message || 'فشل الحفظ'); }
  finally { saving.value = false; }
};

const deleteTask = async (id) => {
  if (!confirm('حذف المهمة؟')) return;
  await axios.delete(`/api/tasks/${id}`);
  fetchTasks();
};

const toggleStatus = async (id, status) => {
  try {
    await axios.patch(`/api/tasks/${id}`, { status });
    fetchTasks();
  } catch (e) { alert('فشل تحديث الحالة'); }
};

const statusClass = (s) => ({ pending: 'bg-orange-100 text-orange-700', in_progress: 'bg-blue-100 text-blue-700', completed: 'bg-green-100 text-green-700', cancelled: 'bg-red-100 text-red-600' }[s] || 'bg-gray-100 text-gray-600');
const statusLabel = (s) => ({ pending: 'معلق', in_progress: 'جاري التنفيذ', completed: 'مكتمل', cancelled: 'ملغي' }[s] || s);

onMounted(fetchTasks);
</script>

<template>
  <AdminLayout>
    <Head title="المهام" />
    <div class="space-y-8 p-6 md:p-10 font-['Cairo']" dir="rtl">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
          <div class="flex items-center gap-2 text-[#04724D] text-xs font-bold uppercase tracking-widest mb-1"><CheckSquare class="w-4 h-4" />المشاريع</div>
          <h1 class="text-4xl font-bold text-gray-900 dark:text-white">المهام</h1>
        </div>
        <button @click="openCreate" class="bg-[#04724D] hover:bg-[#035a3d] text-[#3EFF8B] px-8 py-4 rounded-[20px] font-bold text-xs uppercase tracking-widest flex items-center gap-2 transition-all">
          <Plus class="h-5 w-5" />إضافة مهمة
        </button>
      </div>

      <div class="bg-white dark:bg-[#0A0A0A] rounded-[40px] border border-gray-100 dark:border-gray-800 overflow-hidden">
        <div v-if="loading" class="p-8 space-y-3">
          <div v-for="i in 4" :key="i" class="h-16 bg-gray-100 dark:bg-white/5 rounded-2xl animate-pulse"></div>
        </div>
        <div v-else-if="tasks.length===0" class="py-20 text-center text-gray-400 font-bold text-sm">لا توجد مهام</div>
        <div v-else class="divide-y divide-gray-50 dark:divide-gray-800">
          <div v-for="t in tasks" :key="t.id" class="flex items-center justify-between px-8 py-5 group hover:bg-gray-50/50 dark:hover:bg-white/[0.01] transition-all">
            <div class="flex items-center gap-4">
              <div class="w-12 h-12 rounded-2xl bg-[#04724D]/10 flex items-center justify-center"><CheckSquare class="w-5 h-5 text-[#04724D]" /></div>
              <div>
                <p class="font-bold text-gray-900 dark:text-white">{{ t.title }}</p>
                <p class="text-xs text-gray-400">{{ t.due_date || '—' }}</p>
              </div>
            </div>
            <div class="flex items-center gap-3">
              <select v-model="t.status" @change="toggleStatus(t.id, t.status)" :class="statusClass(t.status)" class="px-3 py-1 rounded-full text-[10px] font-bold border-none">
                <option value="pending">معلق</option><option value="in_progress">جاري</option><option value="completed">مكتمل</option><option value="cancelled">ملغي</option>
              </select>
              <div class="flex gap-2 opacity-0 group-hover:opacity-100 transition-all">
                <button @click="openEdit(t)" class="p-3 bg-white dark:bg-[#1A1A1A] text-gray-400 hover:text-[#04724D] rounded-2xl border border-gray-100 dark:border-gray-800"><Edit class="h-4 w-4" /></button>
                <button @click="deleteTask(t.id)" class="p-3 bg-white dark:bg-[#1A1A1A] text-gray-400 hover:text-red-500 rounded-2xl border border-gray-100 dark:border-gray-800"><Trash2 class="h-4 w-4" /></button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-if="showForm" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
      <div class="bg-white dark:bg-[#121212] rounded-[32px] w-full max-w-md p-8 space-y-5 shadow-2xl">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ editingId ? 'تعديل مهمة' : 'إضافة مهمة' }}</h2>
        <div class="space-y-4">
          <div class="space-y-1"><label class="text-xs text-gray-400 font-bold">العنوان *</label><input v-model="form.title" class="w-full rounded-2xl bg-gray-50 dark:bg-white/5 border-none py-3 px-4 text-sm font-bold dark:text-white" /></div>
          <div class="space-y-1"><label class="text-xs text-gray-400 font-bold">الوصف</label><textarea v-model="form.description" rows="2" class="w-full rounded-2xl bg-gray-50 dark:bg-white/5 border-none py-3 px-4 text-sm font-bold dark:text-white"></textarea></div>
          <div class="space-y-1"><label class="text-xs text-gray-400 font-bold">المشروع</label><input v-model.number="form.project_id" type="number" class="w-full rounded-2xl bg-gray-50 dark:bg-white/5 border-none py-3 px-4 text-sm font-bold dark:text-white" /></div>
          <div class="space-y-1"><label class="text-xs text-gray-400 font-bold">المسند إليه</label><input v-model.number="form.assigned_to" type="number" class="w-full rounded-2xl bg-gray-50 dark:bg-white/5 border-none py-3 px-4 text-sm font-bold dark:text-white" /></div>
          <div class="space-y-1"><label class="text-xs text-gray-400 font-bold">تاريخ الاستحقاق</label><input v-model="form.due_date" type="date" class="w-full rounded-2xl bg-gray-50 dark:bg-white/5 border-none py-3 px-4 text-sm font-bold dark:text-white" /></div>
          <div class="space-y-1"><label class="text-xs text-gray-400 font-bold">الحالة</label><select v-model="form.status" class="w-full rounded-2xl bg-gray-50 dark:bg-white/5 border-none py-3 px-4 text-sm font-bold dark:text-white"><option value="pending">معلق</option><option value="in_progress">جاري التنفيذ</option><option value="completed">مكتمل</option><option value="cancelled">ملغي</option></select></div>
        </div>
        <div class="flex justify-end gap-3">
          <button @click="showForm=false" class="px-6 py-3 rounded-2xl bg-gray-100 dark:bg-white/10 text-sm font-bold text-gray-700 dark:text-gray-300">إلغاء</button>
          <button @click="submit" :disabled="saving" class="px-6 py-3 rounded-2xl bg-[#04724D] text-white text-sm font-bold disabled:opacity-60">{{ saving ? 'جاري...' : 'حفظ' }}</button>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
