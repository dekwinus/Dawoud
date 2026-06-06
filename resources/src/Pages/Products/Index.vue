<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref, onMounted, computed } from 'vue';
import { 
  Plus, 
  Search, 
  Filter, 
  Download, 
  Package, 
  MoreHorizontal, 
  Edit, 
  Trash2, 
  Eye, 
  Layers, 
  Activity,
  ArrowUpDown,
  Tag
} from 'lucide-vue-next';
import axios from 'axios';
import { router, Head } from '@inertiajs/vue3';

const props = defineProps({
  categories: Array,
  brands: Array,
  units: Array,
  warehouses: Array
});

const products = ref([]);
const totalRows = ref(0);
const loading = ref(false);
const filters = ref({
  search: '',
  category_id: '',
  brand_id: '',
  warehouse_id: '',
  page: 1,
  limit: 10,
  SortField: 'id',
  SortType: 'desc'
});

const unitsById = computed(() => {
    const map = new Map();
    (props.units || []).forEach((u) => {
        if (u?.id == null) return;
        map.set(Number(u.id), u.ShortName || u.name || '—');
    });
    return map;
});

const resolveUnitName = (product) => {
    const direct = product.unit_name ?? product.unit;
    if (typeof direct === 'string' && direct.trim() !== '') return direct;

    const unitId = Number(product.unit_id ?? product.unit_sale_id);
    if (Number.isFinite(unitId) && unitsById.value.has(unitId)) {
        return unitsById.value.get(unitId);
    }

    return '—';
};

const fetchProducts = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/admin/products/data', { params: filters.value });
        products.value = (response.data.products || []).map((p) => ({
            ...p,
            category_name: p.category_name ?? p.category ?? '—',
            brand_name: p.brand_name ?? p.brand ?? '—',
            unit_name: resolveUnitName(p),
            qte: toSafeNumber(p.qte, 0),
        }));
        totalRows.value = response.data.totalRows;
    } catch (e) {
        console.error("Failed to fetch products", e);
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchProducts();
});

const handleFilter = () => {
    filters.value.page = 1;
    fetchProducts();
};

const deleteProduct = async (id) => {
    if (!confirm('هل أنت متأكد من حذف هذا المنتج؟')) return;
    try {
        await axios.post(`/admin/products/${id}/archive`, { confirm: true });
        fetchProducts();
    } catch (e) {
        alert(e.response?.data?.message || 'تعذر حذف المنتج');
    }
};

const exportProducts = async () => {
    try {
        const { data } = await axios.get('/admin/products/data', { params: { ...filters.value, limit: 9999, page: 1 } });
        const rows = data.products || [];
        const headers = ['id', 'name', 'code', 'category_name', 'brand_name', 'unit_name', 'price', 'cost', 'qte'];
        const csv = [headers.join(','), ...rows.map(r => headers.map(h => JSON.stringify(r[h] ?? '')).join(','))].join('\n');
        const blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' });
        const a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = 'products.csv';
        a.click();
        URL.revokeObjectURL(a.href);
    } catch (e) { console.error(e); }
};

const toSafeNumber = (value, fallback = 0) => {
    const n = Number(value);
    return Number.isFinite(n) ? n : fallback;
};

const extractNumber = (value, fallback = 0) => {
    if (typeof value === 'number') return Number.isFinite(value) ? value : fallback;
    if (typeof value === 'string') {
        const firstLine = value.split('\n')[0]?.trim() || '';
        if (!firstLine) return fallback;
        const normalized = firstLine.replace(/,/g, '').replace(/[^0-9.-]/g, '');
        return toSafeNumber(normalized, fallback);
    }
    return toSafeNumber(value, fallback);
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('ar-EG', { style: 'currency', currency: 'EGP' }).format(extractNumber(value, 0));
};
</script>

<template>
  <AdminLayout>
    <Head title="كتالوج المنتجات" />
    <div class="p-8 space-y-8" dir="rtl">
      <!-- Header -->
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
          <h1 class="text-xl font-black text-gray-800 dark:text-white tracking-tight font-['Cairo']">كتالوج المنتجات</h1>
          <p class="text-gray-500 dark:text-gray-400 font-medium">إدارة المخزون، الأسعار، ومستويات المنتجات</p>
        </div>
        <div class="flex items-center gap-3">
            <button @click="exportProducts" class="bg-white dark:bg-white/5 text-gray-600 dark:text-gray-300 px-5 py-3 rounded-2xl font-bold text-sm border border-gray-200 dark:border-gray-800 hover:bg-gray-50 transition-all flex items-center gap-2 font-['Cairo']">
                <Download class="h-4 w-4" />
                تصدير CSV
            </button>
            <button @click="router.visit('/admin/products/create')" class="bg-[#04724D] hover:bg-[#058a5e] text-white px-6 py-3 rounded-2xl font-black text-sm shadow-xl shadow-[#04724D]/25 transition-all flex items-center gap-2 font-['Cairo']">
                <Plus class="h-4 w-4" />
                إضافة منتج
            </button>
        </div>
      </div>

      <!-- Quick Summary -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div class="bg-[#04724D] p-6 rounded-[32px] text-white shadow-xl shadow-[#04724D]/20 overflow-hidden relative group">
              <div class="relative z-10">
                  <div class="flex items-center justify-between mb-4">
                      <div class="w-10 h-10 rounded-xl bg-white/20 backdrop-blur-md flex items-center justify-center">
                          <Package class="h-5 w-5 text-white" />
                      </div>
                  </div>
                  <p class="text-xs font-bold text-white/60 uppercase tracking-widest mb-1 font-['Cairo']">إجمالي المنتجات</p>
                  <h3 class="text-2xl font-black">{{ totalRows }}</h3>
              </div>
              <div class="absolute -left-4 -bottom-4 opacity-10 group-hover:scale-110 transition-transform duration-500">
                  <Package class="w-32 h-32" />
              </div>
          </div>
          <div class="bg-white dark:bg-[#121212] p-6 rounded-[32px] border border-gray-100 dark:border-gray-800 shadow-sm">
               <div class="flex items-center justify-between mb-4">
                   <div class="w-10 h-10 rounded-xl bg-gray-50 dark:bg-white/5 flex items-center justify-center">
                       <Tag class="h-5 w-5 text-[#04724D]" />
                   </div>
               </div>
               <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mb-1 font-['Cairo']">التصنيفات النشطة</p>
               <h3 class="text-2xl font-black text-gray-800 dark:text-white">{{ categories.length }}</h3>
          </div>
          <div class="bg-white dark:bg-[#121212] p-6 rounded-[32px] border border-gray-100 dark:border-gray-800 shadow-sm">
               <div class="flex items-center justify-between mb-4">
                   <div class="w-10 h-10 rounded-xl bg-gray-50 dark:bg-white/5 flex items-center justify-center">
                       <Activity class="h-5 w-5 text-[#3EFF8B]" />
                   </div>
                   <span class="text-[10px] font-bold text-red-500 bg-red-50 dark:bg-red-900/10 px-2 py-1 rounded-lg font-['Cairo']">8 مخزون منخفض</span>
               </div>
               <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mb-1 font-['Cairo']">طرق الدفع النشطة</p>
               <h3 class="text-2xl font-black text-gray-800 dark:text-white">{{ warehouses.length }}</h3>
          </div>
      </div>

      <!-- Catalog Table -->
      <div class="bg-white dark:bg-[#121212] rounded-[32px] border border-gray-100 dark:border-gray-800 shadow-sm overflow-hidden">
        <!-- Filter Bar -->
        <div class="p-6 border-b border-gray-100 dark:border-gray-800 flex flex-wrap items-center gap-4">
           <div class="flex-1 min-w-[300px] relative">
              <Search class="absolute right-4 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
              <input 
                v-model="filters.search" 
                @input="handleFilter"
                type="text" 
                placeholder="ابحث بالاسم، الكود، او الباركود..." 
                class="w-full pr-12 pl-4 py-3 bg-gray-50 dark:bg-white/5 border-none rounded-xl text-sm focus:ring-2 focus:ring-[#04724D]/20 font-medium"
              />
           </div>

           <div class="flex items-center gap-3">
              <select v-model="filters.category_id" @change="handleFilter" class="bg-gray-50 dark:bg-white/5 border-none rounded-xl px-4 py-3 text-sm font-bold text-gray-600 dark:text-gray-300 focus:ring-2 focus:ring-[#04724D]/20 font-['Cairo']">
                  <option value="">جميع التصنيفات</option>
                  <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
              </select>
              
              <select v-model="filters.warehouse_id" @change="handleFilter" class="bg-gray-50 dark:bg-white/5 border-none rounded-xl px-4 py-3 text-sm font-bold text-gray-600 dark:text-gray-300 focus:ring-2 focus:ring-[#04724D]/20 font-['Cairo']">
                  <option value="">جميع المخازن</option>
                  <option v-for="w in warehouses" :key="w.id" :value="w.id">{{ w.name }}</option>
              </select>

              <button class="p-3 bg-gray-50 dark:bg-white/5 text-gray-400 rounded-xl hover:text-gray-600 transition-colors">
                  <Filter class="h-4 w-4" />
              </button>
           </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
          <table class="w-full text-right border-collapse">
             <thead>
               <tr class="text-[11px] font-bold text-gray-400 uppercase tracking-wider bg-gray-50/50 dark:bg-white/[0.02] font-['Cairo']">
                  <th class="px-8 py-5">معلومات المنتج</th>
                  <th class="px-6 py-5">الكود</th>
                  <th class="px-6 py-5">التصنيف</th>
                  <th class="px-6 py-5">العلامة التجارية</th>
                  <th class="px-6 py-5">السعر</th>
                  <th class="px-6 py-5">المخزون الحالي</th>
                  <th class="px-6 py-5">الوحدة</th>
                  <th class="px-8 py-5 text-left">الإجراءات</th>
               </tr>
             </thead>
             <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                <tr v-if="loading" v-for="i in 5" :key="i" class="animate-pulse">
                   <td v-for="j in 8" :key="j" class="px-6 py-6"><div class="h-4 bg-gray-100 dark:bg-white/5 rounded w-full"></div></td>
                </tr>

                <tr v-else-if="products.length === 0" class="text-center">
                    <td colspan="8" class="py-20 text-gray-400 font-['Cairo'] text-lg">لم يتم العثور على منتجات</td>
                </tr>

                <tr v-for="product in products" :key="product.id" class="group hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition-colors">
                   <td class="px-8 py-5">
                      <div class="flex items-center gap-4">
                         <div class="w-12 h-12 rounded-xl bg-gray-50 dark:bg-white/5 flex items-center justify-center overflow-hidden border border-gray-100 dark:border-gray-800 shadow-sm transition-transform group-hover:scale-105">
                            <img v-if="product.image" :src="'/images/products/' + product.image" class="w-full h-full object-cover" />
                            <Package v-else class="h-5 w-5 text-gray-300" />
                         </div>
                         <div>
                            <div class="text-sm font-black text-gray-800 dark:text-white leading-tight mb-0.5 line-clamp-1 truncate max-w-[200px] font-['Cairo']">{{ product.name }}</div>
                            <div class="flex items-center gap-2">
                                <span v-if="product.type === 'is_variant'" class="text-[9px] font-black uppercase bg-purple-50 text-purple-600 dark:bg-purple-900/20 dark:text-purple-400 px-1.5 py-0.5 rounded font-['Cairo']">متعدد الألوان/الأحجام</span>
                                <span class="text-[10px] text-gray-400 font-mono">{{ product.code }}</span>
                            </div>
                         </div>
                      </div>
                   </td>
                   <td class="px-6 py-5 text-xs font-bold text-gray-600 dark:text-gray-400 font-mono">{{ product.code }}</td>
                   <td class="px-6 py-5 text-xs font-bold text-gray-600 dark:text-gray-400 font-['Cairo']">{{ product.category_name }}</td>
                   <td class="px-6 py-5 text-xs font-bold text-gray-600 dark:text-gray-400 font-['Cairo']">{{ product.brand_name || '—' }}</td>
                   <td class="px-6 py-5">
                      <div class="text-sm font-black text-[#04724D] dark:text-[#3EFF8B]">{{ formatCurrency(product.price) }}</div>
                      <div class="text-[10px] text-gray-400 line-through">{{ formatCurrency(product.cost) }}</div>
                   </td>
                   <td class="px-6 py-5">
                      <div class="flex items-center gap-2">
                         <div class="w-1.5 h-1.5 rounded-full" :class="product.qte > 10 ? 'bg-green-500 shadow-[0_0_8px_rgba(34,197,94,0.4)]' : 'bg-red-500 shadow-[0_0_8px_rgba(239,68,68,0.4)]'"></div>
                         <span class="text-sm font-black text-gray-800 dark:text-white">{{ product.qte }}</span>
                      </div>
                   </td>
                   <td class="px-6 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest font-['Cairo']">{{ product.unit_name }}</td>
                   <td class="px-8 py-5 text-left">
                      <div class="flex items-center justify-start gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                         <button @click="router.visit('/admin/products/' + product.id + '/edit')" class="p-2.5 bg-white dark:bg-[#1A1A1A] text-gray-400 hover:text-[#04724D] rounded-xl border border-gray-100 dark:border-gray-800 transition-all shadow-sm" title="عرض / تعديل">
                            <Eye class="h-4 w-4" />
                         </button>
                         <button @click="router.visit('/admin/products/' + product.id + '/edit')" class="p-2.5 bg-white dark:bg-[#1A1A1A] text-gray-400 hover:text-blue-500 rounded-xl border border-gray-100 dark:border-gray-800 transition-all shadow-sm" title="تعديل">
                            <Edit class="h-4 w-4" />
                         </button>
                         <button @click="deleteProduct(product.id)" class="p-2.5 bg-white dark:bg-[#1A1A1A] text-gray-400 hover:text-red-500 rounded-xl border border-gray-100 dark:border-gray-800 transition-all shadow-sm" title="حذف">
                            <Trash2 class="h-4 w-4" />
                         </button>
                      </div>
                   </td>
                 </tr>
              </tbody>
           </table>
        </div>

        <!-- Pagination -->
        <div class="p-8 border-t border-gray-50 dark:border-gray-800 flex items-center justify-between font-['Cairo']">
           <div class="text-sm text-gray-400 font-bold">
              الصفحة <span class="text-[#04724D] font-black px-1">{{ filters.page }}</span> 
           </div>
           <div class="flex items-center gap-2">
               <button @click="filters.page--; fetchProducts()" :disabled="filters.page === 1" class="px-6 py-2.5 rounded-xl bg-gray-50 dark:bg-white/5 text-gray-400 font-black text-sm hover:text-gray-800 transition-all disabled:opacity-30 border border-gray-100 dark:border-gray-800">السابق</button>
               <button @click="filters.page++; fetchProducts()" class="px-8 py-2.5 rounded-xl bg-[#04724D] text-white font-black text-sm hover:bg-[#058a5e] transition-all shadow-lg shadow-[#04724D]/20">التالي</button>
           </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<style scoped>
/* Page-specific styling if needed */
</style>
