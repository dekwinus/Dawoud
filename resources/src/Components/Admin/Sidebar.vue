<script setup>
import { ref, computed, watchEffect } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import { 
  LayoutGrid as HomeIcon, 
  Tag as TagIcon, 
  Users as UsersIcon, 
  Box as CubeIcon, 
  ShoppingCart as ShoppingCartIcon, 
  History as ReceiptRefundIcon,
  BarChart3 as ChartBarIcon,
  Settings as Cog6ToothIcon,
  UserCircle as UserGroupIcon,
  Wallet as BanknotesIcon,
  RotateCcw as ArrowPathIcon,
  AlertCircle as ExclamationCircleIcon,
  Layers as RectangleStackIcon,
  ChevronLeft,
  Receipt as ReceiptIcon,
  ArrowLeftRight as ArrowLeftRightIcon,
  Skull as SkullIcon,
  FileText as FileTextIcon,
  Warehouse as WarehouseIcon,
  PiggyBank as PiggyBankIcon,
  Users2 as HRMIcon,
} from 'lucide-vue-next';

const page = usePage();
const permissions = computed(() => page.props.auth.permissions || []);

const menuItems = [
  { name: 'لوحة التحكم', icon: HomeIcon, href: '/admin/dashboard', id: 'dashboard' },
  { 
    name: 'المنتجات', 
    icon: TagIcon, 
    id: 'products',
    permission: ['products_view', 'category', 'brand', 'unit'],
    children: [
      { name: 'قائمة المنتجات', href: '/admin/products' },
      { name: 'تعديل المخزون', href: '/admin/adjustments' },
      { name: 'التصنيفات', href: '/admin/products/Categories' },
      { name: 'العلامات التجارية', href: '/admin/products/Brands' },
      { name: 'الوحدات', href: '/admin/products/Units' },
    ]
  },
  { 
    name: 'المبيعات', 
    icon: ShoppingCartIcon, 
    id: 'sales',
    permission: ['Sales_view', 'Pos_view'],
    children: [
      { name: 'قائمة المبيعات', href: '/admin/sales' },
      { name: 'نقطة البيع POS', href: '/admin/pos' },
      { name: 'الشحنات', href: '/admin/shipments' },
    ]
  },
  { 
    name: 'المشتريات', 
    icon: RectangleStackIcon, 
    id: 'purchases',
    permission: ['Purchases_view'],
    children: [
      { name: 'قائمة المشتريات', href: '/admin/purchases' },
      { name: 'إضافة مشتريات', href: '/admin/purchases/create' },
    ]
  },
  { 
    name: 'العملاء والموردين', 
    icon: UserGroupIcon, 
    id: 'people',
    permission: ['Customers_view', 'Suppliers_view'],
    children: [
      { name: 'العملاء', href: '/admin/people/customers' },
      { name: 'الموردين', href: '/admin/people/suppliers' },
    ]
  },
  { 
    name: 'الحسابات', 
    icon: BanknotesIcon, 
    id: 'accounting',
    permission: ['account', 'chart_of_accounts'],
    children: [
      { name: 'الحسابات المالية', href: '/admin/accounts' },
      { name: 'شجرة الحسابات', href: '/admin/accounting-v2/chart-of-accounts' },
      { name: 'قيود اليومية', href: '/admin/accounting-v2/journal-entries' },
    ]
  },
  { 
    name: 'التقارير', 
    icon: ChartBarIcon, 
    id: 'reports',
    permission: ['Reports_sales', 'Reports_profit', 'accounting_trial_balance', 'accounting_balance_sheet', 'accounting_tax_summary'],
    children: [
      { name: 'الأرباح والخسائر', href: '/admin/reports/profit-loss' },
      { name: 'تقرير المبيعات', href: '/admin/reports/sales' },
      { name: 'تقرير المخزون', href: '/admin/reports/inventory' },
      { name: 'ميزان المراجعة', href: '/admin/reports/trial-balance' },
      { name: 'الميزانية العمومية', href: '/admin/reports/balance-sheet' },
      { name: 'ملخص الضرائب', href: '/admin/reports/tax-summary' },
    ]
  },
  { 
    name: 'المرتجعات', 
    icon: ArrowPathIcon, 
    id: 'returns',
    permission: ['sale_return', 'purchase_return'],
    children: [
      { name: 'مرتجعات المبيعات', href: '/admin/returns/sales' },
      { name: 'مرتجعات المشتريات', href: '/admin/returns/purchases' },
    ]
  },
  { 
    name: 'عروض الأسعار', 
    icon: FileTextIcon, 
    id: 'quotations',
    permission: ['Quotations_view'],
    children: [
      { name: 'قائمة العروض', href: '/admin/quotations' },
    ]
  },
  { 
    name: 'المصروفات', 
    icon: ReceiptIcon, 
    id: 'expenses',
    permission: ['Expenses_view'],
    children: [
      { name: 'قائمة المصروفات', href: '/admin/expenses' },
    ]
  },
  { 
    name: 'الإيداعات', 
    icon: PiggyBankIcon, 
    id: 'deposits',
    permission: ['Deposit_view'],
    children: [
      { name: 'قائمة الإيداعات', href: '/admin/deposits' },
    ]
  },
  { 
    name: 'الصندوق النقدي', 
    icon: BanknotesIcon, 
    id: 'cash_register',
    permission: ['cash_register_view'],
    children: [
      { name: 'إدارة الصندوق', href: '/admin/cash-register' },
    ]
  },
  { 
    name: 'المخزون', 
    icon: WarehouseIcon, 
    id: 'inventory',
    permission: ['Transfer_view', 'Damage_view'],
    children: [
      { name: 'تحويلات المخزون', href: '/admin/transfers' },
      { name: 'سجل التلف', href: '/admin/damages' },
      { name: 'المستودعات', href: '/admin/warehouses' },
    ]
  },
  { 
    name: 'الموارد البشرية', 
    icon: HRMIcon, 
    id: 'hrm',
    permission: ['employee_view'],
    children: [
      { name: 'الموظفون', href: '/admin/hrm/employees' },
      { name: 'الأقسام', href: '/admin/hrm/departments' },
      { name: 'المسميات الوظيفية', href: '/admin/hrm/designations' },
      { name: 'ورديات العمل', href: '/admin/hrm/office-shift' },
      { name: 'الحضور والانصراف', href: '/admin/hrm/attendance' },
      { name: 'الإجازات', href: '/admin/hrm/leave' },
      { name: 'أنواع الإجازات', href: '/admin/hrm/leave-types' },
      { name: 'العطلات', href: '/admin/hrm/holiday' },
      { name: 'الرواتب', href: '/admin/hrm/payroll' },
      { name: 'الشركات', href: '/admin/hrm/company' },
    ]
  },
  { 
    name: 'الخدمة والصيانة', 
    icon: FileTextIcon, 
    id: 'service_jobs',
    permission: ['service_jobs_view'],
    children: [
      { name: 'طلبات الخدمة', href: '/admin/service-jobs' },
    ]
  },
  { 
    name: 'المتجر الإلكتروني', 
    icon: FileTextIcon, 
    id: 'online_store',
    permission: ['store_view'],
    children: [
      { name: 'طلبات المتجر', href: '/admin/store/orders' },
      { name: 'البانرات', href: '/admin/store/banners' },
    ]
  },
  { 
    name: 'المشاريع', 
    icon: FileTextIcon, 
    id: 'projects',
    permission: ['project_view'],
    children: [
      { name: 'المشاريع', href: '/admin/projects' },
      { name: 'المهام', href: '/admin/tasks' },
      { name: 'الأصول الثابتة', href: '/admin/assets' },
    ]
  },
  { 
    name: 'الإعدادات', 
    icon: Cog6ToothIcon, 
    id: 'settings',
    permission: ['setting_system'],
    children: [
      { name: 'إعدادات النظام', href: '/admin/settings' },
      { name: 'إدارة المستخدمين', href: '/admin/users' },
      { name: 'الصلاحيات', href: '/admin/roles' },
      { name: 'طرق الدفع', href: '/admin/settings/payment-methods' },
      { name: 'الحقول المخصصة', href: '/admin/settings/custom-fields' },
    ]
  }
];

const selectedParent = ref('dashboard');
const isSecondaryOpen = ref(false);

const hasPermission = (item) => {
  if (!item.permission) return true;
  if (!Array.isArray(permissions.value) || permissions.value.length === 0) return true;
  return item.permission.some(p => permissions.value.includes(p));
};

const activeMenu = computed(() => menuItems.find(m => m.id === selectedParent.value));

const findMenuByUrl = (url) => {
  return menuItems.find((item) => {
    if (item.children?.some((child) => url.startsWith(child.href))) return true;
    return item.href ? url.startsWith(item.href) : false;
  });
};

watchEffect(() => {
  const matched = findMenuByUrl(page.url || '');
  if (!matched) return;
  selectedParent.value = matched.id;
  isSecondaryOpen.value = Boolean(matched.children);
});

const selectMenu = (item) => {
  if (item.children) {
    if (selectedParent.value === item.id) {
       isSecondaryOpen.value = !isSecondaryOpen.value;
    } else {
       selectedParent.value = item.id;
       isSecondaryOpen.value = true;
    }
  } else {
    selectedParent.value = item.id;
    isSecondaryOpen.value = false;
    if (item.href) {
      router.visit(item.href);
    }
  }
};
</script>

<template>
  <div class="flex h-screen bg-gray-50 dark:bg-[#0A0A0A] overflow-hidden" dir="rtl">
    <!-- Main Sidebar (Level 1) - Luxury Arabic Design -->
    <div class="w-28 flex-none bg-[#04724D] dark:bg-[#042A1C] flex flex-col items-center py-10 shadow-[20px_0_60px_rgba(0,0,0,0.1)] z-50 relative overflow-hidden">
      <!-- Abstract BG Decoration -->
      <div class="absolute -top-12 -right-12 w-32 h-32 bg-white/5 blur-3xl rounded-full"></div>
      <div class="absolute -bottom-12 -left-12 w-32 h-32 bg-[#3EFF8B]/10 blur-3xl rounded-full"></div>

      <div class="mb-14 relative z-10 p-2 bg-white/10 rounded-[20px] backdrop-blur-md border border-white/10">
        <Link href="/admin/dashboard">
          <img src="/images/logo.png" class="h-10 w-10 object-contain brightness-0 invert hover:scale-110 transition-transform duration-500" alt="Logo">
        </Link>
      </div>
      
      <nav class="flex-1 space-y-3 w-full px-3 relative z-10 overflow-y-auto custom-scrollbar">
        <template v-for="item in menuItems" :key="item.id">
          <button 
            v-if="hasPermission(item)"
            @click="selectMenu(item)"
            :class="[
              selectedParent === item.id 
                ? 'bg-white/15 text-[#3EFF8B] border-[#3EFF8B] shadow-lg shadow-black/10' 
                : 'text-white/60 hover:text-white hover:bg-white/5 border-transparent'
            ]"
            class="w-full flex flex-col items-center py-5 rounded-[24px] transition-all duration-500 border-r-4 group relative overflow-hidden"
          >
            <div v-if="selectedParent === item.id" class="absolute -right-4 top-1/2 -translate-y-1/2 w-8 h-8 bg-[#3EFF8B]/20 blur-xl rounded-full"></div>
            <component :is="item.icon" class="h-6 w-6 mb-2 group-hover:scale-110 transition-transform duration-500" />
            <span class="text-[9px] font-black uppercase tracking-widest text-center px-1 font-['Cairo'] opacity-80 group-hover:opacity-100">{{ item.name }}</span>
          </button>
        </template>
      </nav>

      <!-- Bottom System Version -->
      <div class="mt-auto py-4 relative z-10">
         <p class="text-[8px] font-black text-white/30 uppercase tracking-[0.4em] rotate-180 [writing-mode:vertical-lr]">DawPOS V5.4</p>
      </div>
    </div>

    <!-- Secondary Sidebar (Level 2) - Floating Glass Design -->
    <transition
      enter-active-class="transition duration-500 ease-out"
      enter-from-class="translate-x-full opacity-0"
      enter-to-class="translate-x-0 opacity-100"
      leave-active-class="transition duration-300 ease-in"
      leave-from-class="translate-x-0 opacity-100"
      leave-to-class="translate-x-full opacity-0"
    >
        <div 
          v-if="isSecondaryOpen && activeMenu?.children"
          class="w-72 flex-none bg-white/95 dark:bg-[#121212]/95 backdrop-blur-2xl border-l border-gray-100 dark:border-gray-800/50 shadow-2xl z-40 relative"
        >
          <div class="p-10 h-full overflow-y-auto custom-scrollbar pb-36">
            <div class="flex items-center justify-between mb-10">
               <h2 class="text-lg font-black text-gray-900 dark:text-white flex items-center gap-4 font-['Cairo'] tracking-tight">
                <div class="p-2.5 bg-[#04724D]/10 rounded-2xl">
                    <component :is="activeMenu.icon" class="h-6 w-6 text-[#04724D] dark:text-[#3EFF8B]" />
                </div>
                {{ activeMenu.name }}
              </h2>
              <button @click="isSecondaryOpen = false" class="text-gray-300 hover:text-gray-900 dark:text-gray-600 dark:hover:text-white transition-all">
                <ChevronLeft class="h-6 w-6 rotate-180" />
              </button>
            </div>
            
            <ul class="space-y-3">
              <li v-for="child in activeMenu.children" :key="child.name">
                <Link 
                  :href="child.href"
                  class="flex items-center px-6 py-4 rounded-[22px] text-xs font-black transition-all duration-500 group relative font-['Cairo'] tracking-tight"
                  :class="page.url.startsWith(child.href) 
                    ? 'bg-[#04724D] text-white shadow-xl shadow-[#04724D]/20' 
                    : 'text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-white/5 hover:text-[#04724D] dark:hover:text-[#3EFF8B]'"
                >
                  <span class="w-1.5 h-1.5 rounded-full ml-4 transition-all duration-500 group-hover:scale-150" 
                        :class="page.url.startsWith(child.href) ? 'bg-[#3EFF8B]' : 'bg-gray-300 dark:bg-gray-700'"></span>
                  {{ child.name }}
                  
                  <div v-if="page.url.startsWith(child.href)" class="absolute left-4 top-1/2 -translate-y-1/2 w-1 h-4 bg-[#3EFF8B] rounded-full"></div>
                </Link>
              </li>
            </ul>
          </div>
          
          <!-- Secondary Mini Footer -->
          <div class="absolute bottom-8 right-10 left-10 p-5 bg-gray-50 dark:bg-white/5 rounded-3xl border border-gray-100 dark:border-gray-800 flex items-center justify-between">
             <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest font-['Cairo']">مركز العمليات</p>
             <div class="flex items-center gap-1.5">
                <div class="w-1.5 h-1.5 rounded-full bg-[#3EFF8B] shadow-sm shadow-[#3EFF8B]/50 animate-pulse"></div>
                <span class="text-[9px] font-black text-[#04724D] dark:text-[#3EFF8B] tracking-widest">نشط</span>
             </div>
          </div>
        </div>
    </transition>
  </div>
</template>

<style scoped>
/* Enhanced sidebar scrolling with smooth behavior */
.custom-scrollbar {
  scrollbar-width: 8px;
  scrollbar-color: rgba(4, 114, 77, 0.3) transparent;
  transition: scrollbar-color 0.3s ease;
  scroll-behavior: smooth;
}

.custom-scrollbar:hover {
  scrollbar-color: rgba(4, 114, 77, 0.5) transparent;
}

/* Chrome/Safari/Edge implementation */
.custom-scrollbar::-webkit-scrollbar {
  width: 8px;
  height: 8px;
}

.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
  border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(4, 114, 77, 0.2);
  border-radius: 10px;
  transition: all 0.3s ease;
  border: 2px solid transparent;
}

.custom-scrollbar:hover::-webkit-scrollbar-thumb {
  background: rgba(4, 114, 77, 0.4);
  border-color: rgba(4, 114, 77, 0.1);
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: rgba(4, 114, 77, 0.6);
  border-color: rgba(4, 114, 77, 0.2);
}

/* Dark mode adjustments */
:global(.dark) .custom-scrollbar {
  scrollbar-color: rgba(62, 255, 139, 0.2) transparent;
}

:global(.dark) .custom-scrollbar:hover {
  scrollbar-color: rgba(62, 255, 139, 0.4) transparent;
}

:global(.dark) .custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(62, 255, 139, 0.15);
}

:global(.dark) .custom-scrollbar:hover::-webkit-scrollbar-thumb {
  background: rgba(62, 255, 139, 0.3);
}

:global(.dark) .custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: rgba(62, 255, 139, 0.5);
}

:global(.dark) .custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: rgba(62, 255, 139, 0.3);
}

/* Ensuring the primary sidebar also has SmartScroll behavior */
nav.custom-scrollbar {
  overflow-y: auto;
  overflow-x: hidden;
}
</style>
