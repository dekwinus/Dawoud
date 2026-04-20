<script setup>
import { Head, usePage, router, Link } from '@inertiajs/vue3';
import { ref, watch, onMounted } from 'vue';
import axios from 'axios';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { 
  UserPlus as UserPlusIcon, 
  MoreVertical as EllipsisVerticalIcon,
  Edit3 as PencilSquareIcon,
  Trash2 as TrashIcon,
  Search as MagnifyingGlassIcon,
  Filter as FunnelIcon,
  User as UserCircleIcon,
  Mail as EnvelopeIcon,
  Phone as PhoneIcon,
  BadgeCheck as CheckBadgeIcon,
  XCircle as XCircleIcon,
  ShieldCheck as ShieldCheckIcon,
  Warehouse as BuildingOfficeIcon,
  Calendar
} from 'lucide-vue-next';
import { debounce } from 'lodash';

const props = defineProps({
  roles: Array,
  warehouses: Array,
});

const users = ref([]);
const totalRows = ref(0);
const loading = ref(true);

const filters = ref({
  search: '',
  role_id: '',
  statut: '',
  limit: '10',
  page: 1,
  SortField: 'id',
  SortType: 'desc'
});

const fetchUsers = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/api/users', { 
      params: filters.value 
    });
    users.value = response.data.users;
    totalRows.value = response.data.totalRows;
  } catch (error) {
    console.error('Failed to fetch users:', error);
  } finally {
    loading.value = false;
  }
};

watch(filters, debounce(() => {
  fetchUsers();
}, 300), { deep: true });

onMounted(() => {
  fetchUsers();
});

const toggleStatut = async (user) => {
  try {
    const newStatut = user.statut ? 0 : 1;
    await axios.put(`/api/users_switch_activated/${user.id}`, {
      id: user.id,
      statut: newStatut
    });
    user.statut = newStatut;
  } catch (error) {
    console.error('Failed to toggle status:', error);
  }
};

const deleteUser = async (user) => {
  if (confirm(`هل أنت متأكد من حذف المستخدم ${user.username}؟`)) {
    try {
      await axios.delete(`/api/users/${user.id}`);
      fetchUsers();
    } catch (error) {
      alert(error.response?.data?.message || 'فشل حذف المستخدم');
    }
  }
};

const getRoleName = (roleId) => {
  const role = props.roles.find(r => r.id === roleId);
  return role ? role.name : 'بدون صلاحية';
};

const getAvatarUrl = (avatar) => {
  return avatar ? `/images/avatar/${avatar}` : '/images/avatar/no_avatar.png';
};

// Modal state
const showModal = ref(false);
const editingId = ref(null);
const saving = ref(false);
const saveError = ref(null);
const form = ref({
  username: '', firstname: '', lastname: '', email: '', phone: '',
  password: '', role_id: '', warehouse_id: '', is_all_warehouses: true, statut: 1
});

const openCreate = () => {
  editingId.value = null;
  form.value = {
    username: '', firstname: '', lastname: '', email: '', phone: '',
    password: '', role_id: props.roles[0]?.id || '', warehouse_id: props.warehouses?.[0]?.id || '',
    is_all_warehouses: true, statut: 1
  };
  saveError.value = null;
  showModal.value = true;
};

const openEdit = (user) => {
  editingId.value = user.id;
  form.value = {
    username: user.username || '', firstname: user.firstname || '',
    lastname: user.lastname || '', email: user.email || '', phone: user.phone || '',
    password: '', role_id: user.role_id || '', warehouse_id: user.warehouse_id || '',
    is_all_warehouses: user.is_all_warehouses ?? true, statut: user.statut ?? 1
  };
  saveError.value = null;
  showModal.value = true;
};

const saveUser = async () => {
  if (!form.value.username?.trim() || !form.value.email?.trim()) {
    saveError.value = 'اسم المستخدم والبريد الإلكتروني مطلوبان';
    return;
  }
  saving.value = true;
  saveError.value = null;
  try {
    const payload = { ...form.value };
    // Map role_id to role for backend compatibility
    payload.role = payload.role_id;
    delete payload.role_id;
    if (!payload.password) delete payload.password;
    if (editingId.value) {
      await axios.put(`/api/users/${editingId.value}`, payload);
    } else {
      if (!payload.password) { saveError.value = 'كلمة المرور مطلوبة للمستخدم الجديد'; saving.value = false; return; }
      await axios.post('/api/users', payload);
    }
    showModal.value = false;
    fetchUsers();
  } catch (err) {
    saveError.value = err.response?.data?.message
      || Object.values(err.response?.data?.errors || {}).flat().join(' | ')
      || 'تعذر الحفظ';
  } finally {
    saving.value = false;
  }
};
</script>

<template>
  <AdminLayout>
    <Head title="إدارة المستخدمين - DawPOS" />
    
    <div class="p-8 space-y-8" dir="rtl">
      <!-- Header -->
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="text-right">
          <h1 class="text-xl font-black text-gray-900 dark:text-white font-['Cairo'] tracking-tight">إدارة المستخدمين</h1>
          <p class="text-gray-500 dark:text-gray-400 font-medium font-['Cairo']">إدارة الوصول بصلاحيات مخصصة لكل فرع وموظف</p>
        </div>
        <button 
          @click="openCreate"
          class="inline-flex items-center gap-2 bg-[#04724D] hover:bg-[#058a5e] text-white px-8 py-3.5 rounded-2xl font-black text-sm shadow-xl shadow-[#04724D]/20 transition-all font-['Cairo']">
          <UserPlusIcon class="w-5 h-5" />
          إضافة مستخدم جديد
        </button>
      </div>

      <!-- Filters Bar -->
      <div class="bg-white dark:bg-[#121212] p-4 rounded-[28px] shadow-sm border border-gray-100 dark:border-gray-800 flex flex-wrap items-center gap-4">
        <div class="relative flex-1 min-w-[300px]">
          <MagnifyingGlassIcon class="w-5 h-5 absolute right-4 top-1/2 -translate-y-1/2 text-gray-400" />
          <input 
            v-model="filters.search"
            type="text" 
            placeholder="البحث بالاسم، البريد الإلكتروني أو الهاتف..." 
            class="w-full pr-12 pl-4 py-3 bg-gray-50 dark:bg-white/5 border-none rounded-xl focus:ring-2 focus:ring-[#04724D]/20 text-sm font-black text-gray-800 dark:text-gray-200 font-['Cairo'] transition-all"
          />
        </div>

        <div class="flex items-center gap-3">
          <select 
            v-model="filters.role_id"
            class="bg-gray-50 dark:bg-white/5 border-none rounded-xl py-3 px-6 text-sm font-black text-gray-600 dark:text-gray-300 focus:ring-2 focus:ring-[#04724D]/20 cursor-pointer font-['Cairo']"
          >
            <option value="">كل الصلاحيات</option>
            <option v-for="role in roles" :key="role.id" :value="role.id">{{ role.name }}</option>
          </select>

          <select 
            v-model="filters.statut"
            class="bg-gray-50 dark:bg-white/5 border-none rounded-xl py-3 px-6 text-sm font-black text-gray-600 dark:text-gray-300 focus:ring-2 focus:ring-[#04724D]/20 cursor-pointer font-['Cairo']"
          >
            <option value="">كل الحالات</option>
            <option value="1">نشط</option>
            <option value="0">غير نشط</option>
          </select>

          <div class="h-8 w-px bg-gray-100 dark:bg-gray-800 mx-1"></div>

          <button class="p-3 bg-gray-50 dark:bg-white/5 rounded-xl text-gray-400 hover:text-[#04724D] transition-all">
            <FunnelIcon class="w-5 h-5" />
          </button>
        </div>
      </div>

      <!-- Users Table -->
      <div class="bg-white dark:bg-[#121212] rounded-[40px] shadow-sm border border-gray-100 dark:border-gray-800 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-right">
            <thead>
              <tr class="bg-gray-50/50 dark:bg-white/[0.01] text-[10px] text-gray-400 font-black uppercase tracking-[0.2em] border-b border-gray-50 dark:border-gray-800 font-['Cairo']">
                <th class="px-10 py-6">بيانات المستخدم</th>
                <th class="px-8 py-6">الصلاحيات والوصول</th>
                <th class="px-8 py-6">الاتصال</th>
                <th class="px-8 py-6 text-center">الحالة</th>
                <th class="px-10 py-6 text-left">الإجراءات</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 dark:divide-gray-800 font-['Cairo']">
              <tr v-for="user in users" :key="user.id" class="group hover:bg-gray-50/50 dark:hover:bg-white/[0.01] transition-colors">
                <!-- User Info -->
                <td class="px-10 py-6">
                  <div class="flex items-center gap-5">
                    <div class="relative">
                      <img 
                        :src="getAvatarUrl(user.avatar)" 
                        class="w-14 h-14 rounded-2xl object-cover ring-4 ring-white dark:ring-gray-900 shadow-md group-hover:scale-105 transition-transform"
                      />
                      <div 
                        :class="user.statut ? 'bg-[#3EFF8B]' : 'bg-gray-300 dark:bg-gray-700'"
                        class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full border-2 border-white dark:border-gray-900"
                      ></div>
                    </div>
                    <div class="flex flex-col">
                      <span class="text-sm font-black text-gray-900 dark:text-white">{{ user.username }}</span>
                      <span class="text-xs text-gray-500 font-bold opacity-70">{{ user.firstname }} {{ user.lastname }}</span>
                    </div>
                  </div>
                </td>

                <!-- Role & Access -->
                <td class="px-8 py-6">
                  <div class="flex flex-col gap-2 text-right">
                    <div class="flex items-center gap-2 text-xs font-black text-[#04724D] dark:text-[#3EFF8B]">
                      <ShieldCheckIcon class="w-4 h-4" />
                      {{ getRoleName(user.role_id) }}
                    </div>
                    <div class="flex items-center gap-2 text-[10px] text-gray-400 font-black tracking-tight">
                      <BuildingOfficeIcon class="w-3.5 h-3.5" />
                      {{ user.is_all_warehouses ? 'وصول كامل لكل المخازن' : 'وصول محدود لمخازن محددة' }}
                    </div>
                  </div>
                </td>

                <!-- Contact -->
                <td class="px-8 py-6 text-right">
                  <div class="flex flex-col gap-1.5">
                    <div class="flex items-center gap-2 text-xs font-black text-gray-600 dark:text-gray-400">
                      <EnvelopeIcon class="w-4 h-4 text-gray-400 opacity-50" />
                      <span class="font-mono">{{ user.email }}</span>
                    </div>
                    <div class="flex items-center gap-2 text-xs font-black text-gray-600 dark:text-gray-400">
                      <PhoneIcon class="w-4 h-4 text-gray-400 opacity-50" />
                      <span class="font-mono">{{ user.phone }}</span>
                    </div>
                  </div>
                </td>

                <!-- Status -->
                <td class="px-8 py-6">
                  <div class="flex justify-center">
                    <button 
                      @click="toggleStatut(user)"
                      :class="user.statut ? 'bg-green-100 dark:bg-green-500/10 text-green-700 dark:text-green-400 border-green-200' : 'bg-red-100 dark:bg-red-500/10 text-red-700 dark:text-red-400 border-red-200'"
                      class="px-5 py-2 rounded-full text-[10px] font-black uppercase tracking-[0.1em] transition-all hover:scale-105 active:scale-95 border"
                    >
                      {{ user.statut ? 'نشط' : 'غير نشط' }}
                    </button>
                  </div>
                </td>

                <!-- Actions -->
                <td class="px-10 py-6">
                  <div class="flex items-center justify-start gap-2 opacity-0 group-hover:opacity-100 transition-all transform translate-x-2 group-hover:translate-x-0">
                    <button 
                      @click="openEdit(user)"
                      class="p-2.5 text-gray-400 hover:text-[#04724D] hover:bg-[#04724D]/5 rounded-xl transition-all border border-transparent hover:border-[#04724D]/10"
                      title="تعديل المستخدم"
                    >
                      <PencilSquareIcon class="w-5 h-5" />
                    </button>
                    <button 
                      @click="deleteUser(user)"
                      class="p-2.5 text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-500/10 rounded-xl transition-all border border-transparent hover:border-red-500/10"
                      title="حذف المستخدم"
                    >
                      <TrashIcon class="w-5 h-5" />
                    </button>
                  </div>
                </td>
              </tr>

              <!-- Empty State -->
              <tr v-if="!users.length && !loading" class="text-right">
                <td colspan="5" class="py-32 text-center">
                  <div class="flex flex-col items-center gap-6">
                    <div class="p-8 bg-gray-50 dark:bg-white/5 rounded-[40px] relative">
                        <div class="absolute inset-0 bg-[#04724D] blur-[80px] opacity-10"></div>
                        <UserCircleIcon class="w-20 h-20 text-gray-200 dark:text-gray-700 relative z-10" />
                    </div>
                    <p class="text-gray-400 font-black text-lg font-['Cairo']">لم يتم العثور على أي مستخدمين يطابقون بحثك</p>
                    <button 
                      @click="filters.search = ''; filters.role_id = ''; filters.statut = ''"
                      class="text-[#04724D] font-black text-sm hover:underline font-['Cairo'] uppercase tracking-widest"
                    >
                      إعادة ضبط جميع الفلاتر
                    </button>
                  </div>
                </td>
              </tr>

              <!-- Loading State -->
              <tr v-if="loading">
                <td colspan="5" class="py-32 text-center">
                  <div class="animate-pulse flex flex-col items-center gap-6 opacity-30">
                    <div class="w-14 h-14 bg-gray-200 dark:bg-gray-800 rounded-full"></div>
                    <div class="h-5 w-48 bg-gray-200 dark:bg-gray-800 rounded-full"></div>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination Footer -->
        <div class="px-10 py-6 border-t border-gray-50 dark:border-gray-800 flex flex-col sm:flex-row items-center justify-between bg-gray-50/30 dark:bg-white/[0.01] gap-6 font-['Cairo']">
          <div class="flex items-center gap-6 font-black text-gray-500 dark:text-gray-400">
            <span class="text-xs">عرض {{ users.length }} من إجمالي {{ totalRows }} مستخدم</span>
            <select 
              v-model="filters.limit"
              class="bg-white dark:bg-[#1A1A1A] border-none rounded-xl py-2 px-5 text-xs font-black focus:ring-2 focus:ring-[#04724D]/20 shadow-sm border border-gray-100 dark:border-gray-800 cursor-pointer"
            >
              <option value="10">10 لكل صفحة</option>
              <option value="25">25 لكل صفحة</option>
              <option value="50">50 لكل صفحة</option>
            </select>
          </div>
          
          <div class="flex items-center gap-3">
            <button 
              :disabled="filters.page === 1"
              @click="filters.page--"
              class="px-8 py-3 bg-white dark:bg-[#1A1A1A] border border-gray-100 dark:border-gray-800 rounded-2xl text-xs font-black text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-white/5 disabled:opacity-40 disabled:cursor-not-allowed shadow-sm transition-all"
            >
              السابق
            </button>
            <button 
              :disabled="filters.page * filters.limit >= totalRows"
              @click="filters.page++"
              class="px-10 py-3 bg-[#04724D] text-white rounded-2xl text-xs font-black hover:bg-[#058a5e] disabled:opacity-50 disabled:cursor-not-allowed shadow-xl shadow-[#04724D]/20 transition-all"
            >
              التالي
            </button>
          </div>
        </div>
      </div>
      <!-- Create/Edit Modal -->
      <div v-if="showModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" dir="rtl">
        <div class="bg-white dark:bg-[#121212] rounded-[32px] w-full max-w-2xl shadow-2xl overflow-hidden">
          <div class="bg-[#04724D] p-6 flex items-center justify-between">
            <h2 class="text-lg font-black text-white font-['Cairo']">{{ editingId ? 'تعديل مستخدم' : 'إضافة مستخدم جديد' }}</h2>
            <button @click="showModal = false" class="text-white/70 hover:text-white text-2xl leading-none">&times;</button>
          </div>
          <div class="p-8 space-y-5 max-h-[70vh] overflow-y-auto">
            <div v-if="saveError" class="text-red-600 text-xs font-bold bg-red-50 dark:bg-red-500/10 px-4 py-2 rounded-xl">{{ saveError }}</div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
              <div class="space-y-2">
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest font-['Cairo']">اسم المستخدم *</label>
                <input v-model="form.username" type="text" class="w-full rounded-xl bg-gray-50 dark:bg-white/5 border-none p-4 text-sm font-black dark:text-gray-200 font-['Cairo']" />
              </div>
              <div class="space-y-2">
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest font-['Cairo']">البريد الإلكتروني *</label>
                <input v-model="form.email" type="email" class="w-full rounded-xl bg-gray-50 dark:bg-white/5 border-none p-4 text-sm font-black dark:text-gray-200 font-mono" />
              </div>
              <div class="space-y-2">
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest font-['Cairo']">الاسم الأول</label>
                <input v-model="form.firstname" type="text" class="w-full rounded-xl bg-gray-50 dark:bg-white/5 border-none p-4 text-sm font-black dark:text-gray-200 font-['Cairo']" />
              </div>
              <div class="space-y-2">
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest font-['Cairo']">الاسم الأخير</label>
                <input v-model="form.lastname" type="text" class="w-full rounded-xl bg-gray-50 dark:bg-white/5 border-none p-4 text-sm font-black dark:text-gray-200 font-['Cairo']" />
              </div>
              <div class="space-y-2">
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest font-['Cairo']">الهاتف *</label>
                <input v-model="form.phone" type="text" class="w-full rounded-xl bg-gray-50 dark:bg-white/5 border-none p-4 text-sm font-black dark:text-gray-200 font-mono" dir="ltr" />
              </div>
              <div class="space-y-2">
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest font-['Cairo']">{{ editingId ? 'كلمة المرور (اتركها فارغة للإبقاء)' : 'كلمة المرور *' }}</label>
                <input v-model="form.password" type="password" class="w-full rounded-xl bg-gray-50 dark:bg-white/5 border-none p-4 text-sm font-black dark:text-gray-200 font-mono" />
              </div>
              <div class="space-y-2">
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest font-['Cairo']">الصلاحية</label>
                <select v-model="form.role_id" class="w-full rounded-xl bg-gray-50 dark:bg-white/5 border-none p-4 text-sm font-black dark:text-gray-200 font-['Cairo']">
                  <option v-for="r in roles" :key="r.id" :value="r.id">{{ r.name }}</option>
                </select>
              </div>
              <div class="space-y-2">
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest font-['Cairo']">الحالة</label>
                <select v-model="form.statut" class="w-full rounded-xl bg-gray-50 dark:bg-white/5 border-none p-4 text-sm font-black dark:text-gray-200 font-['Cairo']">
                  <option :value="1">نشط</option>
                  <option :value="0">غير نشط</option>
                </select>
              </div>
              <div class="md:col-span-2 flex items-center gap-3 p-4 bg-gray-50 dark:bg-white/5 rounded-xl">
                <input v-model="form.is_all_warehouses" type="checkbox" id="allWh" class="w-4 h-4 accent-[#04724D]" />
                <label for="allWh" class="text-sm font-black text-gray-700 dark:text-gray-300 font-['Cairo']">وصول لجميع المستودعات</label>
              </div>
            </div>
          </div>
          <div class="p-6 border-t border-gray-100 dark:border-gray-800 flex justify-end gap-3">
            <button @click="showModal = false" class="px-6 py-3 rounded-xl bg-gray-100 dark:bg-white/5 text-sm font-black font-['Cairo'] dark:text-gray-300">إلغاء</button>
            <button @click="saveUser" :disabled="saving" class="px-8 py-3 rounded-xl bg-[#04724D] text-white text-sm font-black font-['Cairo'] disabled:opacity-60 flex items-center gap-2">
              <span v-if="saving" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
              {{ saving ? 'جاري الحفظ...' : 'حفظ' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

