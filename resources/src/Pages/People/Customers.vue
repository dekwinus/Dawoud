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
  Building as BuildingOfficeIcon,
  Globe,
  MapPin,
  CreditCard,
  DollarSign
} from 'lucide-vue-next';
import { debounce } from 'lodash';

const props = defineProps({
  accounts: Array,
  payment_methods: Array,
});

const customers = ref([]);
const totalRows = ref(0);
const loading = ref(true);
const saving = ref(false);
const showForm = ref(false);
const editingId = ref(null);
const form = ref({
  name: '',
  email: '',
  phone: '',
  adresse: '',
  country: '',
  city: '',
  tax_number: '',
  opening_balance: 0,
  credit_limit: 0,
  is_royalty_eligible: false
});

const filters = ref({
  search: '',
  limit: '10',
  page: 1,
  SortField: 'id',
  SortType: 'desc'
});

const fetchCustomers = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/api/clients', { 
      params: filters.value 
    });
    customers.value = response.data.clients;
    totalRows.value = response.data.totalRows;
  } catch (error) {
    console.error('Failed to fetch customers:', error);
  } finally {
    loading.value = false;
  }
};

const openCreate = () => {
  editingId.value = null;
  form.value = {
    name: '',
    email: '',
    phone: '',
    adresse: '',
    country: '',
    city: '',
    tax_number: '',
    opening_balance: 0,
    credit_limit: 0,
    is_royalty_eligible: false
  };
  showForm.value = true;
};

const openEdit = async (customer) => {
  const { data } = await axios.get(`/api/clients/${customer.id}`);
  const c = data.client || {};
  editingId.value = customer.id;
  form.value = {
    name: c.name || '',
    email: c.email || '',
    phone: c.phone || '',
    adresse: c.adresse || '',
    country: c.country || '',
    city: c.city || '',
    tax_number: c.tax_number || '',
    opening_balance: Number(c.opening_balance || 0),
    credit_limit: Number(c.credit_limit || 0),
    is_royalty_eligible: Boolean(c.is_royalty_eligible)
  };
  showForm.value = true;
};

const submitForm = async () => {
  if (!form.value.name?.trim()) return;
  saving.value = true;
  const payload = {
    ...form.value,
    is_royalty_eligible: form.value.is_royalty_eligible ? 1 : 0
  };

  try {
    if (editingId.value) {
      await axios.put(`/api/clients/${editingId.value}`, payload);
    } else {
      await axios.post('/api/clients', payload);
    }
    showForm.value = false;
    await fetchCustomers();
  } catch (error) {
    alert(error.response?.data?.message || 'تعذر حفظ بيانات العميل');
  } finally {
    saving.value = false;
  }
};

watch(filters, debounce(() => {
  fetchCustomers();
}, 300), { deep: true });

onMounted(() => {
  fetchCustomers();
});

const deleteCustomer = async (customer) => {
  if (confirm(`هل أنت متأكد من حذف العميل ${customer.name}؟`)) {
    try {
      await axios.delete(`/api/clients/${customer.id}`);
      fetchCustomers();
    } catch (error) {
      alert(error.response?.data?.message || 'فشل حذف العميل');
    }
  }
};

const getAvatarUrl = (customer) => {
  return '/images/avatar/no_avatar.png'; // No avatar field on client model natively
};
</script>

<template>
  <AdminLayout>
    <Head title="إدارة العملاء - DawPOS" />
    
    <div class="p-4 md:p-8 space-y-8" dir="rtl">
      <!-- Header -->
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="text-right">
          <h1 class="text-3xl font-black text-gray-900 dark:text-white font-['Cairo'] tracking-tight">إدارة العملاء</h1>
          <p class="text-gray-500 dark:text-gray-400 font-medium font-['Cairo']">إدارة قائمة العملاء والأرصدة ونقاط الولاء</p>
        </div>
        <button 
          @click="openCreate"
          class="inline-flex items-center gap-2 bg-[#04724D] hover:bg-[#058a5e] text-white px-8 py-3.5 rounded-2xl font-black text-sm shadow-xl shadow-[#04724D]/20 transition-all font-['Cairo']"
        >
          <UserPlusIcon class="w-5 h-5" />
          إضافة عميل جديد
        </button>
      </div>

      <!-- Filters Bar -->
      <div class="bg-white dark:bg-[#121212] p-4 rounded-[28px] shadow-sm border border-gray-100 dark:border-gray-800 flex flex-wrap items-center gap-4">
        <div class="relative flex-1 min-w-[300px]">
          <MagnifyingGlassIcon class="w-5 h-5 absolute right-4 top-1/2 -translate-y-1/2 text-gray-400" />
          <input 
            v-model="filters.search"
            type="text" 
            placeholder="البحث بالاسم، البريد الإلكتروني أو الهاتف، رمز العميل..." 
            class="w-full pr-12 pl-4 py-3 bg-gray-50 dark:bg-white/5 border-none rounded-xl focus:ring-2 focus:ring-[#04724D]/20 text-sm font-black text-gray-800 dark:text-gray-200 font-['Cairo'] transition-all"
          />
        </div>

        <div class="flex items-center gap-3">
          <button class="p-3 bg-gray-50 dark:bg-white/5 rounded-xl text-gray-400 hover:text-[#04724D] transition-all">
            <FunnelIcon class="w-5 h-5" />
          </button>
        </div>
      </div>

      <!-- Customers Table -->
      <div class="bg-white dark:bg-[#121212] rounded-[40px] shadow-sm border border-gray-100 dark:border-gray-800 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-right">
            <thead>
              <tr class="bg-gray-50/50 dark:bg-white/[0.01] text-[10px] text-gray-400 font-black uppercase tracking-[0.2em] border-b border-gray-50 dark:border-gray-800 font-['Cairo']">
                <th class="px-10 py-6">بيانات العميل</th>
                <th class="px-8 py-6">الاتصال والموقع</th>
                <th class="px-8 py-6 text-center">الرصيد / الديون (Due)</th>
                <th class="px-8 py-6 text-center">نقاط الولاء</th>
                <th class="px-10 py-6 text-left">الإجراءات</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 dark:divide-gray-800 font-['Cairo']">
              <tr v-for="customer in customers" :key="customer.id" class="group hover:bg-gray-50/50 dark:hover:bg-white/[0.01] transition-colors">
                <!-- Customer Info -->
                <td class="px-10 py-6">
                  <div class="flex items-center gap-5">
                    <div class="relative">
                      <div class="w-14 h-14 rounded-2xl bg-[#04724D]/10 flex items-center justify-center border-2 border-white dark:border-gray-900 group-hover:scale-105 transition-transform">
                          <UserCircleIcon class="w-7 h-7 text-[#04724D] dark:text-[#3EFF8B]" />
                      </div>
                      <div 
                        :class="customer.client_ecommerce === 'yes' ? 'bg-[#3EFF8B]' : 'bg-gray-300 dark:bg-gray-700'"
                        class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full border-2 border-white dark:border-gray-900"
                        title="يمتلك حساب بالمتجر الإلكتروني"
                      ></div>
                    </div>
                    <div class="flex flex-col">
                      <span class="text-sm font-black text-gray-900 dark:text-white">{{ customer.name }}</span>
                      <span class="text-[10px] text-gray-500 font-bold opacity-70 tracking-widest font-mono">CODE: {{ customer.code }}</span>
                    </div>
                  </div>
                </td>

                <!-- Contact & Location -->
                <td class="px-8 py-6">
                  <div class="flex flex-col gap-1.5 text-right">
                    <div class="flex items-center gap-2 text-xs font-black text-gray-600 dark:text-gray-400">
                      <PhoneIcon class="w-4 h-4 text-gray-400 opacity-50" />
                      <span class="font-mono">{{ customer.phone || 'غير مسجل' }}</span>
                    </div>
                    <div class="flex items-center gap-2 text-xs font-black text-gray-600 dark:text-gray-400 truncate max-w-[200px]" :title="customer.email">
                      <EnvelopeIcon class="w-4 h-4 text-gray-400 opacity-50" />
                      <span class="font-mono truncate">{{ customer.email || 'غير مسجل' }}</span>
                    </div>
                    <div v-if="customer.country || customer.city" class="flex items-center gap-2 text-[10px] text-gray-400 font-black tracking-tight">
                      <MapPin class="w-3.5 h-3.5 text-gray-400 opacity-50" />
                      {{ customer.country }} {{ customer.city ? `- ${customer.city}` : '' }}
                    </div>
                  </div>
                </td>

                <!-- Balance / Due -->
                <td class="px-8 py-6 text-center">
                  <div class="flex flex-col gap-1 items-center justify-center">
                     <span class="text-sm font-black font-mono" :class="customer.due > 0 ? 'text-red-500' : 'text-gray-900 dark:text-white'">
                        {{ Number(customer.due).toFixed(2) }} 
                     </span>
                     <span class="text-[9px] uppercase tracking-widest text-gray-400 font-black px-2 py-0.5 bg-gray-100 dark:bg-white/5 rounded-full inline-block mt-1">ديون مستحقة</span>
                  </div>
                </td>

                <!-- Loyalty / Points -->
                <td class="px-8 py-6 text-center">
                  <div class="flex justify-center">
                     <div class="flex items-center justify-center gap-2 px-4 py-2 rounded-xl bg-purple-50 dark:bg-purple-500/10 border border-purple-100 dark:border-purple-500/20 text-purple-700 dark:text-purple-400 text-xs font-black font-mono">
                        {{ customer.points || 0 }} 
                        <span class="text-[9px] uppercase tracking-widest">NTS</span>
                     </div>
                  </div>
                  <div v-if="customer.is_royalty_eligible" class="mt-2 text-[9px] flex justify-center uppercase tracking-widest text-[#04724D] dark:text-[#3EFF8B] font-black">
                     استحقاق العمولات
                  </div>
                </td>

                <!-- Actions -->
                <td class="px-10 py-6">
                  <div class="flex items-center justify-start gap-2 opacity-0 group-hover:opacity-100 transition-all transform translate-x-2 group-hover:translate-x-0">
                    <button 
                      @click="openEdit(customer)"
                      class="p-2.5 text-gray-400 hover:text-[#04724D] hover:bg-[#04724D]/5 rounded-xl transition-all border border-transparent hover:border-[#04724D]/10"
                      title="تعديل العميل"
                    >
                      <PencilSquareIcon class="w-5 h-5" />
                    </button>
                    <button 
                      @click="deleteCustomer(customer)"
                      class="p-2.5 text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-500/10 rounded-xl transition-all border border-transparent hover:border-red-500/10"
                      title="حذف العميل"
                    >
                      <TrashIcon class="w-5 h-5" />
                    </button>
                  </div>
                </td>
              </tr>

              <!-- Empty State -->
              <tr v-if="!customers.length && !loading" class="text-right">
                <td colspan="5" class="py-32 text-center">
                  <div class="flex flex-col items-center gap-6">
                    <div class="p-8 bg-gray-50 dark:bg-white/5 rounded-[40px] relative">
                        <div class="absolute inset-0 bg-[#04724D] blur-[80px] opacity-10"></div>
                        <UserCircleIcon class="w-20 h-20 text-gray-200 dark:text-gray-700 relative z-10" />
                    </div>
                    <p class="text-gray-400 font-black text-lg font-['Cairo']">لم يتم العثور على أي عملاء يطابقون بحثك</p>
                    <button 
                      @click="filters.search = ''"
                      class="text-[#04724D] font-black text-sm hover:underline font-['Cairo'] uppercase tracking-widest"
                    >
                      إعادة ضبط جميع الفلاتر
                    </button>
                  </div>
                </td>
              </tr>

              <!-- Loading State -->
              <tr v-if="loading" v-for="i in 5">
                <td colspan="5" class="py-6 px-10 text-center">
                  <div class="animate-pulse flex items-center gap-4 opacity-30">
                    <div class="w-14 h-14 bg-gray-200 dark:bg-gray-800 rounded-2xl"></div>
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
            <span class="text-xs">عرض {{ customers.length }} من إجمالي {{ totalRows }} عميل</span>
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

      <div v-if="showForm" class="fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl w-full max-w-3xl p-6 space-y-4 max-h-[90vh] overflow-y-auto">
          <h2 class="text-xl font-black">{{ editingId ? 'تعديل عميل' : 'إضافة عميل جديد' }}</h2>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div class="space-y-2">
              <label class="text-xs text-gray-500">الاسم</label>
              <input v-model="form.name" class="w-full rounded-xl bg-gray-50 border-0" />
            </div>
            <div class="space-y-2">
              <label class="text-xs text-gray-500">البريد الإلكتروني</label>
              <input v-model="form.email" type="email" class="w-full rounded-xl bg-gray-50 border-0" />
            </div>
            <div class="space-y-2">
              <label class="text-xs text-gray-500">الهاتف</label>
              <input v-model="form.phone" class="w-full rounded-xl bg-gray-50 border-0" />
            </div>
            <div class="space-y-2">
              <label class="text-xs text-gray-500">العنوان</label>
              <input v-model="form.adresse" class="w-full rounded-xl bg-gray-50 border-0" />
            </div>
            <div class="space-y-2">
              <label class="text-xs text-gray-500">الدولة</label>
              <input v-model="form.country" class="w-full rounded-xl bg-gray-50 border-0" />
            </div>
            <div class="space-y-2">
              <label class="text-xs text-gray-500">المدينة</label>
              <input v-model="form.city" class="w-full rounded-xl bg-gray-50 border-0" />
            </div>
            <div class="space-y-2">
              <label class="text-xs text-gray-500">الرقم الضريبي</label>
              <input v-model="form.tax_number" class="w-full rounded-xl bg-gray-50 border-0" />
            </div>
            <div class="space-y-2">
              <label class="text-xs text-gray-500">حد الائتمان</label>
              <input v-model.number="form.credit_limit" type="number" step="0.01" class="w-full rounded-xl bg-gray-50 border-0" />
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div class="space-y-2">
              <label class="text-xs text-gray-500">الرصيد الافتتاحي</label>
              <input v-model.number="form.opening_balance" type="number" step="0.01" class="w-full rounded-xl bg-gray-50 border-0" :disabled="Boolean(editingId)" />
            </div>
            <label class="flex items-center gap-2 mt-7 text-sm font-bold text-gray-600">
              <input v-model="form.is_royalty_eligible" type="checkbox" class="rounded border-gray-300 text-[#04724D]" />
              استحقاق نظام الولاء
            </label>
          </div>

          <div class="flex items-center justify-end gap-2">
            <button @click="showForm = false" class="px-4 py-2 rounded-xl bg-gray-100 text-sm font-bold">إلغاء</button>
            <button :disabled="saving" @click="submitForm" class="px-4 py-2 rounded-xl bg-[#04724D] text-white text-sm font-black disabled:opacity-60">{{ saving ? 'جاري الحفظ...' : 'حفظ' }}</button>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
