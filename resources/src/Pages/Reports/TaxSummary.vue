<script setup>
import { Head } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
import axios from 'axios';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { 
  FileText as FileIcon,
  RefreshCcw as RefreshIcon,
  Filter as FilterIcon,
  Calendar as CalendarIcon,
  Calculator as CalculatorIcon,
  Download as DownloadIcon
} from 'lucide-vue-next';

const loading = ref(true);
const data = ref([]);
const summary = ref({
  total_tax: 0,
  collected: 0,
  pending: 0
});
const totalRows = ref(0);
const page = ref(1);
const perPage = ref(10);
const search = ref('');

const filters = ref({
  from: new Date(new Date().setDate(new Date().getDate() - 30)).toISOString().split('T')[0],
  to: new Date().toISOString().split('T')[0]
});

const loadData = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/api/accounting/v2/reports/tax-summary', {
      params: {
        page: page.value,
        limit: perPage.value,
        search: search.value,
        from: filters.value.from,
        to: filters.value.to
      }
    });
    data.value = response.data.data;
    summary.value = response.data.summary || { total_tax: 0, collected: 0, pending: 0 };
    totalRows.value = response.data.totalRows;
  } catch (error) {
    console.error('Failed to load tax summary:', error);
  } finally {
    loading.value = false;
  }
};

onMounted(loadData);
</script>

<template>
  <AdminLayout>
    <Head title="ملخص الضرائب - DawPOS" />
    
    <div class="p-8 space-y-6" dir="rtl">
      <div class="flex items-center justify-between mb-6">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white font-['Cairo']">ملخص الضرائب</h1>
          <p class="text-gray-500 dark:text-gray-400 text-sm font-['Cairo']">عرض الضرائب المستحصلة والمعلقة</p>
        </div>
        <div class="flex items-center gap-3">
          <button 
            @click="loadData"
            class="px-4 py-2 rounded-xl bg-[#04724D] text-white text-sm font-bold font-['Cairo'] hover:bg-[#058a5e] transition-all flex items-center gap-2"
          >
            <RefreshIcon class="h-4 w-4" />
            تحديث
          </button>
        </div>
      </div>

      <!-- Summary Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl p-6 text-white">
          <div class="flex items-center justify-between mb-2">
            <h3 class="text-lg font-bold font-['Cairo']">إجمالي الضرائب</h3>
            <CalculatorIcon class="h-6 w-6 opacity-80" />
          </div>
          <p class="text-3xl font-black font-['Cairo']">{{ summary.total_tax }}</p>
        </div>
        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-2xl p-6 text-white">
          <div class="flex items-center justify-between mb-2">
            <h3 class="text-lg font-bold font-['Cairo']">تم التحصيل</h3>
            <FileIcon class="h-6 w-6 opacity-80" />
          </div>
          <p class="text-3xl font-black font-['Cairo']">{{ summary.collected }}</p>
        </div>
        <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-2xl p-6 text-white">
          <div class="flex items-center justify-between mb-2">
            <h3 class="text-lg font-bold font-['Cairo']">معلقة</h3>
            <FileIcon class="h-6 w-6 opacity-80" />
          </div>
          <p class="text-3xl font-black font-['Cairo']">{{ summary.pending }}</p>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white dark:bg-[#121212] rounded-2xl p-6 border border-gray-100 dark:border-gray-800 space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div class="space-y-2">
            <label class="text-xs text-gray-500 font-['Cairo']">من تاريخ</label>
            <input v-model="filters.from" type="date" class="w-full rounded-xl bg-gray-50 dark:bg-white/5 border-0 p-3 text-sm text-gray-800 dark:text-gray-200 font-['Cairo']" />
          </div>
          <div class="space-y-2">
            <label class="text-xs text-gray-500 font-['Cairo']">إلى تاريخ</label>
            <input v-model="filters.to" type="date" class="w-full rounded-xl bg-gray-50 dark:bg-white/5 border-0 p-3 text-sm text-gray-800 dark:text-gray-200 font-['Cairo']" />
          </div>
          <div class="space-y-2">
            <label class="text-xs text-gray-500 font-['Cairo']">بحث</label>
            <input v-model="search" @input="loadData" class="w-full rounded-xl bg-gray-50 dark:bg-white/5 border-0 p-3 text-sm text-gray-800 dark:text-gray-200 font-['Cairo']" placeholder="بحث..." />
          </div>
        </div>
      </div>

      <!-- Data Table -->
      <div class="bg-white dark:bg-[#121212] rounded-2xl border border-gray-100 dark:border-gray-800 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-white/5">
              <tr>
                <th class="p-4 text-right font-bold text-gray-700 dark:text-gray-300 font-['Cairo']">التاريخ</th>
                <th class="p-4 text-right font-bold text-gray-700 dark:text-gray-300 font-['Cairo']">الوصف</th>
                <th class="p-4 text-right font-bold text-gray-700 dark:text-gray-300 font-['Cairo']">النوع</th>
                <th class="p-4 text-right font-bold text-gray-700 dark:text-gray-300 font-['Cairo']">المبلغ</th>
                <th class="p-4 text-right font-bold text-gray-700 dark:text-gray-300 font-['Cairo']">الحالة</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="loading">
                <td colspan="5" class="p-8 text-center text-gray-500">جاري التحميل...</td>
              </tr>
              <tr v-else-if="data.length === 0">
                <td colspan="5" class="p-8 text-center text-gray-500">لا توجد بيانات</td>
              </tr>
              <tr v-for="row in data" :key="row.id" class="border-t dark:border-gray-800">
                <td class="p-4">{{ row.date }}</td>
                <td class="p-4">{{ row.description }}</td>
                <td class="p-4">{{ row.tax_type }}</td>
                <td class="p-4">{{ row.amount || 0 }}</td>
                <td class="p-4">
                  <span 
                    :class="{
                      'bg-green-100 text-green-700': row.status === 'paid',
                      'bg-yellow-100 text-yellow-700': row.status === 'pending',
                      'bg-red-100 text-red-700': row.status === 'overdue'
                    }"
                    class="px-3 py-1 rounded-full text-xs font-bold"
                  >
                    {{ row.status }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        
        <!-- Pagination -->
        <div class="p-4 border-t dark:border-gray-800 flex items-center justify-between">
          <div class="text-sm text-gray-500 dark:text-gray-400 font-['Cairo']">
            الإجمالي: {{ totalRows }}
          </div>
          <div class="flex items-center gap-2">
            <button 
              @click="page-- > 1; loadData()"
              :disabled="page === 1"
              class="px-4 py-2 rounded-xl bg-gray-100 dark:bg-white/5 text-sm font-bold text-gray-700 dark:text-gray-300 disabled:opacity-50 font-['Cairo']"
            >
              السابق
            </button>
            <span class="text-sm text-gray-500 dark:text-gray-400 font-['Cairo']">صفحة {{ page }}</span>
            <button 
              @click="page++; loadData()"
              :disabled="data.length < perPage"
              class="px-4 py-2 rounded-xl bg-gray-100 dark:bg-white/5 text-sm font-bold text-gray-700 dark:text-gray-300 disabled:opacity-50 font-['Cairo']"
            >
              التالي
            </button>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
