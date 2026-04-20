<script setup>
import { Head, router, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { 
  ShieldCheck as ShieldCheckIcon,
  Plus as PlusIcon,
  FileEdit as PencilSquareIcon,
  Trash2 as TrashIcon,
  Search as MagnifyingGlassIcon,
  ChevronLeft as ChevronRightIcon,
  Lock as LockClosedIcon,
  Check as CheckIcon,
  Info as InformationCircleIcon,
  ShieldAlert
} from 'lucide-vue-next';

const props = defineProps({
  roles: Array,
  all_permissions: Array,
});

const selectedRole = ref(null);
const searchPermission = ref('');
const loading = ref(false);

const permissionGroups = [
  { name: 'المبيعات والطلبات', pattern: /Sales|Quotations|Pos|Orders|Collections|payment_sales/i },
  { name: 'المشتريات والمخزون', pattern: /Purchases|product|warehouse|brand|category|unit|adjustment|count_stock|inventory/i },
  { name: 'التقارير والتحليلات', pattern: /report/i },
  { name: 'النظام والإعدادات', pattern: /setting|backup|mail|sms|gateway|module|appearance|translations|woocommerce|quickbooks/i },
  { name: 'الموارد البشرية', pattern: /employee|attendance|leave|holiday|company|department|designation|office_shift|payroll/i },
  { name: 'المالية والمحاسبة', pattern: /account|deposit|expense|transfer_money|cash_register|payment_methods/i },
  { name: 'أخرى', pattern: /./ } // Catch-all
];

const groupedPermissions = computed(() => {
  const result = {};
  permissionGroups.forEach(group => {
    result[group.name] = [];
  });

  props.all_permissions.forEach(perm => {
    let assigned = false;
    for (let group of permissionGroups) {
      if (group.pattern.test(perm.name)) {
        if (group.name === 'أخرى' && assigned) continue;
        result[group.name].push(perm);
        assigned = true;
        if (group.name !== 'أخرى') break;
      }
    }
  });

  return Object.fromEntries(Object.entries(result).filter(([_, perms]) => perms.length > 0));
});

const selectRole = (role) => {
  selectedRole.value = JSON.parse(JSON.stringify(role)); // Deep clone
};

const hasPermission = (permissionName) => {
  if (!selectedRole.value) return false;
  return selectedRole.value.permissions.some(p => p.name === permissionName);
};

const togglePermission = (permission) => {
  if (!selectedRole.value) return;
  const index = selectedRole.value.permissions.findIndex(p => p.name === permission.name);
  if (index === -1) {
    selectedRole.value.permissions.push(permission);
  } else {
    selectedRole.value.permissions.splice(index, 1);
  }
};

const toggleGroup = (groupName) => {
  const groupPerms = groupedPermissions.value[groupName];
  const allInGroupSelected = groupPerms.every(p => hasPermission(p.name));
  
  groupPerms.forEach(p => {
    const isSelected = hasPermission(p.name);
    if (allInGroupSelected) {
      if (isSelected) togglePermission(p);
    } else {
      if (!isSelected) togglePermission(p);
    }
  });
};

const saveRole = async () => {
  if (!selectedRole.value) return;
  loading.value = true;
  try {
    const permissionsSlugs = selectedRole.value.permissions.map(p => p.name);
    await axios.put(`/api/roles/${selectedRole.value.id}`, {
      role: {
        name: selectedRole.value.name,
        description: selectedRole.value.description
      },
      permissions: permissionsSlugs
    });
    router.reload({ only: ['roles'] });
    alert('تم تحديث الصلاحيات بنجاح!');
  } catch (error) {
    alert('فشل تحديث الدور: ' + (error.response?.data?.message || 'خطأ غير معروف'));
  } finally {
    loading.value = false;
  }
};

// Create role modal
const showCreateModal = ref(false);
const newRoleName = ref('');
const newRoleDesc = ref('');
const creatingRole = ref(false);
const createError = ref(null);

const createRole = async () => {
  if (!newRoleName.value.trim()) { createError.value = 'اسم الدور مطلوب'; return; }
  creatingRole.value = true;
  createError.value = null;
  try {
    await axios.post('/api/roles', { name: newRoleName.value, description: newRoleDesc.value });
    showCreateModal.value = false;
    newRoleName.value = '';
    newRoleDesc.value = '';
    router.reload({ only: ['roles'] });
  } catch (err) {
    createError.value = err.response?.data?.message || 'تعذر إنشاء الدور';
  } finally {
    creatingRole.value = false;
  }
};

const deleteRole = async () => {
  if (!selectedRole.value) return;
  if (!confirm(`هل أنت متأكد من حذف الدور "${selectedRole.value.name}"؟`)) return;
  try {
    await axios.delete(`/api/roles/${selectedRole.value.id}`);
    selectedRole.value = null;
    router.reload({ only: ['roles'] });
  } catch (err) {
    alert(err.response?.data?.message || 'تعذر حذف الدور');
  }
};

onMounted(() => {
  if (props.roles.length > 0) {
    selectRole(props.roles[0]);
  }
});
</script>

<template>
  <AdminLayout>
    <Head title="إدارة الأدوار والصلاحيات - DawPOS" />
    <div class="space-y-12 pb-24 p-6 md:p-12 font-['Cairo']" dir="rtl">
      <!-- Header -->
      <div class="flex flex-col md:flex-row md:items-end justify-between gap-10">
        <div class="space-y-3">
          <div class="flex items-center gap-3 text-[#04724D] font-black text-xs uppercase tracking-[0.4em]">
            <ShieldAlert class="w-5 h-5" />
            الأمن والتحكم
          </div>
          <h1 class="text-4xl md:text-6xl font-black text-gray-900 dark:text-white tracking-tight">إدارة الأدوار والصلاحيات</h1>
          <p class="text-gray-500 font-bold text-lg">تحديد ضوابط الوصول الدقيقة وصلاحيات النظام لكل مجموعة مستخدمين.</p>
        </div>
        <div class="flex items-center gap-5">
            <button @click="showCreateModal = true" class="bg-[#04724D] hover:bg-[#035a3d] text-[#3EFF8B] px-10 py-5 rounded-[24px] font-black text-xs uppercase tracking-[0.2em] shadow-2xl shadow-[#04724D]/30 transition-all flex items-center gap-3 active:scale-95 group">
                <PlusIcon class="h-5 w-5 group-hover:rotate-90 transition-transform" />
                إنشاء دور جديد
            </button>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
        <!-- Role List Sidebar -->
        <div class="lg:col-span-3 space-y-8">
          <div class="bg-white dark:bg-[#0A0A0A] p-6 rounded-[32px] shadow-3xl shadow-[#04724D]/5 border border-gray-100 dark:border-gray-800 relative overflow-hidden">
            <div class="absolute -top-12 -right-12 w-32 h-32 bg-[#04724D]/[0.02] blur-3xl rounded-full"></div>
            
            <h3 class="text-[10px] font-black text-gray-400 dark:text-gray-600 uppercase tracking-[0.3em] mb-8 px-4 relative z-10">مستويات الوصول</h3>
            <div class="space-y-2 relative z-10">
              <button 
                v-for="role in roles" 
                :key="role.id"
                @click="selectRole(role)"
                :class="selectedRole?.id === role.id ? 'bg-[#04724D] text-[#3EFF8B] shadow-xl shadow-[#04724D]/20 border-transparent scale-105' : 'text-gray-600 dark:text-gray-400 border-transparent hover:bg-gray-50 dark:hover:bg-white/5'"
                class="w-full flex items-center justify-between p-5 rounded-2xl border transition-all duration-500 group"
              >
                <div class="flex items-center gap-4">
                  <div :class="selectedRole?.id === role.id ? 'bg-white/10 text-white' : 'bg-gray-100 dark:bg-white/5 text-gray-400 group-hover:bg-white/10'" class="w-12 h-12 rounded-xl flex items-center justify-center transition-all">
                    <ShieldCheckIcon class="w-6 h-6" />
                  </div>
                  <div class="text-right">
                    <p class="text-sm font-black">{{ role.name }}</p>
                    <p class="text-[10px] font-bold opacity-60 mt-1 tabular-nums font-mono">{{ role.permissions.length }} صلاحية مفعلة</p>
                  </div>
                </div>
                <ChevronRightIcon class="w-5 h-5 opacity-40 group-hover:opacity-100 group-hover:-translate-x-1 transition-all" />
              </button>
            </div>
          </div>

          <div class="bg-gradient-to-br from-[#04724D] to-[#035a3d] p-10 rounded-[32px] text-white shadow-3xl shadow-[#04724D]/20 relative overflow-hidden group">
            <div class="absolute -top-12 -left-12 opacity-10 group-hover:scale-125 transition-transform duration-1000">
              <LockClosedIcon class="w-40 h-40" />
            </div>
            <h4 class="text-sm font-black mb-4 relative z-10 uppercase tracking-widest text-[#3EFF8B]">تلميح أمني</h4>
            <p class="text-xs font-bold text-white/70 leading-relaxed relative z-10">احرص دائماً على تطبيق مبدأ "أقل الصلاحيات الممكنة". امنح فقط الصلاحيات اللازمة لوظيفة المستخدم.</p>
          </div>
        </div>

        <!-- Permissions Grid -->
        <div class="lg:col-span-9 space-y-8">
          <div v-if="selectedRole" class="bg-white dark:bg-[#0A0A0A] rounded-[56px] shadow-3xl shadow-[#04724D]/5 border border-gray-100 dark:border-gray-800 overflow-hidden flex flex-col h-full relative">
            <div class="absolute -top-24 -right-24 w-96 h-96 bg-[#04724D]/[0.02] blur-[120px] rounded-full pointer-events-none"></div>

            <div class="p-12 border-b border-gray-100 dark:border-gray-800 flex flex-col md:flex-row md:items-center justify-between gap-10 bg-gray-50/10 dark:bg-white/[0.01] relative z-10">
              <div class="flex items-center gap-6">
                <div class="p-5 bg-[#04724D]/10 rounded-3xl border border-[#04724D]/5">
                  <ShieldCheckIcon class="w-8 h-8 text-[#04724D] dark:text-[#3EFF8B]" />
                </div>
                <div>
                  <h2 class="text-3xl font-black text-gray-900 dark:text-white">{{ selectedRole.name }}</h2>
                  <p class="text-sm font-bold text-gray-400 mt-2">{{ selectedRole.description || 'لا يوجد وصف متاح لهذا الدور' }}</p>
                </div>
              </div>
              <div class="flex items-center gap-4">
                <button 
                  @click="saveRole"
                  :disabled="loading"
                  class="bg-[#04724D] text-[#3EFF8B] px-10 py-5 rounded-[24px] font-black text-xs uppercase tracking-widest hover:shadow-2xl hover:shadow-[#04724D]/30 transition-all active:scale-95 disabled:opacity-50 flex items-center gap-3 group"
                >
                  <div v-if="loading" class="w-4 h-4 border-2 border-[#3EFF8B]/30 border-t-[#3EFF8B] rounded-full animate-spin"></div>
                   <CheckIcon v-else class="w-5 h-5 group-hover:scale-125 transition-transform" />
                  حفظ التغييرات
                </button>
                <button @click="deleteRole" class="p-5 text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-500/5 rounded-[24px] transition-all border border-transparent hover:border-red-100 dark:hover:border-red-500/20 active:scale-90">
                  <TrashIcon class="w-7 h-7" />
                </button>
              </div>
            </div>

            <div class="flex-1 overflow-y-auto p-12 custom-scrollbar max-h-[calc(100vh-400px)] relative z-10">
              <!-- Search & Info -->
              <div class="flex flex-col md:flex-row items-center justify-between gap-8 mb-12">
                <div class="relative w-full md:w-[450px] group">
                  <MagnifyingGlassIcon class="w-6 h-6 absolute right-6 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-[#04724D] transition-all" />
                  <input 
                    v-model="searchPermission"
                    type="text" 
                    placeholder="تصفية الصلاحيات..." 
                    class="w-full pr-16 pl-6 py-5 bg-gray-50 dark:bg-white/[0.03] border-none rounded-[24px] focus:ring-4 focus:ring-[#04724D]/10 text-sm font-black transition-all"
                  />
                </div>
                <div class="flex items-center gap-3 text-[10px] text-[#04724D] dark:text-[#3EFF8B] font-black uppercase tracking-[0.2em] bg-[#04724D]/5 px-6 py-4 rounded-2xl border border-[#04724D]/5 tabular-nums font-mono">
                  <InformationCircleIcon class="w-4 h-4" />
                  {{ selectedRole.permissions.length }} من أصل {{ all_permissions.length }} صلاحية نشطة
                </div>
              </div>

              <!-- Permission Groups -->
              <div class="space-y-20">
                <div v-for="(perms, groupName) in groupedPermissions" :key="groupName" class="space-y-8">
                  <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 pb-4">
                    <h4 class="text-lg font-black text-gray-900 dark:text-white flex items-center gap-4">
                      <span class="w-2 h-2 rounded-full bg-[#3EFF8B] shadow-sm shadow-[#3EFF8B]/50 animate-pulse"></span>
                      {{ groupName }}
                    </h4>
                    <button 
                      @click="toggleGroup(groupName)"
                      class="text-[10px] font-black uppercase tracking-[0.3em] text-[#04724D] dark:text-[#3EFF8B] hover:scale-105 transition-transform"
                    >
                      تحديد الكل في المجموعة
                    </button>
                  </div>
                  
                  <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
                    <button 
                      v-for="perm in perms" 
                      :key="perm.id"
                      @click="togglePermission(perm)"
                      :class="hasPermission(perm.name) ? 'bg-[#04724D]/5 border-[#04724D]/30 shadow-lg shadow-[#04724D]/5' : 'bg-white dark:bg-white/[0.01] border-gray-100 dark:border-gray-800 hover:border-[#04724D] dark:hover:border-[#04724D] hover:bg-gray-50 dark:hover:bg-white/5'"
                      class="flex items-center justify-between p-6 rounded-[28px] border transition-all duration-500 text-right group/item relative overflow-hidden"
                    >
                      <div class="relative z-10">
                        <p class="text-xs font-black text-gray-800 dark:text-gray-200 group-hover/item:text-[#04724D] transition-colors">{{ perm.name.replace(/_/g, ' ') }}</p>
                        <p class="text-[9px] font-black text-gray-400 dark:text-gray-600 uppercase tracking-tighter mt-1 font-mono uppercase">{{ perm.name }}</p>
                      </div>
                      <div 
                        :class="hasPermission(perm.name) ? 'bg-[#04724D] border-transparent scale-110 shadow-lg shadow-[#04724D]/30' : 'bg-gray-50 dark:bg-white/5 border-gray-100 dark:border-gray-800'"
                        class="w-7 h-7 rounded-xl border flex items-center justify-center transition-all group-hover/item:scale-110 relative z-10"
                      >
                        <CheckIcon v-if="hasPermission(perm.name)" class="w-4 h-4 text-[#3EFF8B]" />
                      </div>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
  
          <div v-else class="h-full flex flex-col items-center justify-center text-center p-32 bg-gray-50/30 dark:bg-white/[0.01] border-4 border-dashed border-gray-100 dark:border-gray-800 rounded-[56px] relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-[#04724D]/[0.02] to-transparent pointer-events-none"></div>
            <div class="w-32 h-32 bg-white dark:bg-[#1A1A1A] rounded-[40px] shadow-2xl shadow-black/5 flex items-center justify-center mb-10 relative z-10 border border-gray-100 dark:border-gray-800">
              <LockClosedIcon class="w-12 h-12 text-gray-200 dark:text-gray-800" />
            </div>
            <h3 class="text-3xl font-black text-gray-900 dark:text-white mb-4 relative z-10">لم يتم اختيار دور</h3>
            <p class="text-gray-400 font-bold max-w-sm mx-auto leading-relaxed relative z-10 text-lg">اختر دوراً من القائمة الجانبية لعرض وإدارة صلاحياته الدقيقة في النظام.</p>
          </div>
        </div>
      </div>
      <!-- Create Role Modal -->
      <div v-if="showCreateModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" dir="rtl">
        <div class="bg-white dark:bg-[#121212] rounded-[32px] w-full max-w-md shadow-2xl overflow-hidden">
          <div class="bg-[#04724D] p-6 flex items-center justify-between">
            <h2 class="text-lg font-black text-white font-['Cairo']">إنشاء دور جديد</h2>
            <button @click="showCreateModal = false" class="text-white/70 hover:text-white text-2xl leading-none">&times;</button>
          </div>
          <div class="p-8 space-y-4">
            <div v-if="createError" class="text-red-600 text-xs font-bold bg-red-50 dark:bg-red-500/10 px-4 py-2 rounded-xl">{{ createError }}</div>
            <div class="space-y-2">
              <label class="text-xs font-black text-gray-400 uppercase tracking-widest font-['Cairo']">اسم الدور *</label>
              <input v-model="newRoleName" type="text" class="w-full rounded-xl bg-gray-50 dark:bg-white/5 border-none p-4 text-sm font-black dark:text-gray-200 font-['Cairo']" placeholder="مثال: مدير المبيعات" />
            </div>
            <div class="space-y-2">
              <label class="text-xs font-black text-gray-400 uppercase tracking-widest font-['Cairo']">وصف اختياري</label>
              <textarea v-model="newRoleDesc" rows="3" class="w-full rounded-xl bg-gray-50 dark:bg-white/5 border-none p-4 text-sm font-black dark:text-gray-200 font-['Cairo'] resize-none"></textarea>
            </div>
          </div>
          <div class="p-6 border-t border-gray-100 dark:border-gray-800 flex justify-end gap-3">
            <button @click="showCreateModal = false" class="px-6 py-3 rounded-xl bg-gray-100 dark:bg-white/5 text-sm font-black font-['Cairo'] dark:text-gray-300">إلغاء</button>
            <button @click="createRole" :disabled="creatingRole" class="px-8 py-3 rounded-xl bg-[#04724D] text-white text-sm font-black font-['Cairo'] disabled:opacity-60 flex items-center gap-2">
              <span v-if="creatingRole" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
              {{ creatingRole ? 'جاري الإنشاء...' : 'إنشاء الدور' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 5px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #04724D11;
  border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #04724D33;
}
</style>
