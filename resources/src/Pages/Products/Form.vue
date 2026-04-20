<script setup>
import { Head, router, useForm, Link } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { 
  ChevronRightIcon,
  CheckIcon,
  PhotoIcon,
  TagIcon,
  BanknotesIcon,
  CubeIcon,
  ShieldCheckIcon,
  PlusIcon,
  TrashIcon,
  InformationCircleIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
  product: Object,
  categories: Array,
  brands: Array,
  units: Array,
  warehouses: Array,
  is_edit: Boolean,
});

const form = useForm({
  _method: props.is_edit ? 'PUT' : 'POST',
  name: props.product?.name || '',
  code: props.product?.code || '',
  Type_barcode: props.product?.Type_barcode || 'CODE128',
  category_id: props.product?.category_id || '',
  sub_category_id: props.product?.sub_category_id || null,
  brand_id: props.product?.brand_id || '',
  type: props.product?.type || 'is_single',
  cost: props.product?.cost || 0,
  price: props.product?.price || 0,
  wholesale_price: props.product?.wholesale_price || 0,
  min_price: props.product?.min_price || 0,
  unit_id: props.product?.unit_id || '',
  unit_sale_id: props.product?.unit_sale_id || '',
  unit_purchase_id: props.product?.unit_purchase_id || '',
  TaxNet: props.product?.TaxNet || 0,
  tax_method: props.product?.tax_method || '1',
  discount: props.product?.discount || 0,
  discount_method: props.product?.discount_method || '1',
  stock_alert: props.product?.stock_alert || 0,
  weight: props.product?.weight || null,
  note: props.product?.note || '',
  is_active: props.product ? !!props.product.is_active : true,
  is_featured: props.product ? !!props.product.is_featured : false,
  is_imei: props.product ? !!props.product.is_imei : false,
  not_selling: props.product ? !!props.product.not_selling : false,
  hide_from_online_store: props.product ? !!props.product.hide_from_online_store : false,
  production_date: props.product?.production_date || null,
  expiry_date: props.product?.expiry_date || null,
  has_guarantee: props.product ? !!props.product.has_guarantee : false,
  guarantee_period: props.product?.guarantee_period || null,
  guarantee_unit: props.product?.guarantee_unit || 'days',
  image: null,
  variants: props.product?.variants || [],
  materiels: props.product?.combinedProducts || [],
  warehouses: {} // Opening stock map: {warehouse_id: {qte: 0}}
});

const activeTab = ref('general');
const imagePreview = ref(null);
const loading = ref(false);
const imageInputRef = ref(null);
const submitError = ref(null);

const generateCode = () => {
  const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
  const rand = Array.from({ length: 8 }, () => chars[Math.floor(Math.random() * chars.length)]).join('');
  form.code = rand;
};

const triggerImagePicker = () => {
  imageInputRef.value?.click();
};

const tabs = [
  { id: 'general', name: 'المعلومات العامة', icon: TagIcon },
  { id: 'pricing', name: 'التسعير والمخزون', icon: BanknotesIcon },
  { id: 'stock', name: 'الرصيد الافتتاحي', icon: CubeIcon },
  { id: 'advanced', name: 'إعدادات متقدمة', icon: ShieldCheckIcon },
];

const onImageChange = (e) => {
  const file = e.target.files[0];
  if (file) {
    form.image = file;
    imagePreview.value = URL.createObjectURL(file);
  }
};

const addVariant = () => {
  form.variants.push({ text: '', code: '', cost: 0, price: 0 });
};

const removeVariant = (index) => {
  form.variants.splice(index, 1);
};

const submit = async () => {
  if (!form.name?.trim()) {
    submitError.value = 'اسم المنتج مطلوب';
    return;
  }
  submitError.value = null;
  loading.value = true;

  const fd = new FormData();
  const skip = ['image', 'variants', 'materiels', 'warehouses', '_method'];
  Object.entries(form).forEach(([k, v]) => {
    if (skip.includes(k)) return;
    if (v === null || v === undefined) return;
    fd.append(k, v);
  });

  if (form.image) fd.append('image', form.image);

  form.variants.forEach((v, i) => {
    fd.append(`variants[${i}][text]`, v.text || '');
    fd.append(`variants[${i}][code]`, v.code || '');
    fd.append(`variants[${i}][cost]`, v.cost || 0);
    fd.append(`variants[${i}][price]`, v.price || 0);
  });

  Object.entries(form.warehouses).forEach(([wid, val]) => {
    fd.append(`warehouses[${wid}][qte]`, val?.qte || 0);
  });

  if (props.is_edit) fd.append('_method', 'PUT');

  try {
    const url = props.is_edit ? `/api/products/${props.product.id}` : '/api/products';
    await axios.post(url, fd, { headers: { 'Content-Type': 'multipart/form-data' } });
    router.visit('/admin/products');
  } catch (err) {
    const msg = err.response?.data?.message
      || (err.response?.data?.errors ? Object.values(err.response.data.errors).flat().join('\n') : null)
      || 'تعذر حفظ المنتج، حاول مرة أخرى';
    submitError.value = msg;
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  // Initialize warehouse quantity map
  props.warehouses.forEach(w => {
    form.warehouses[w.id] = { qte: 0 };
  });
});
</script>

<template>
  <AdminLayout>
    <Head :title="is_edit ? 'تعديل المنتج' : 'إضافة منتج جديد'" />
    <div class="p-8 space-y-8" dir="rtl">
      <!-- Header -->
      <div class="flex items-center justify-between mb-8">
        <div class="flex items-center gap-4">
          <Link href="/admin/products" class="p-3 bg-white border border-gray-100 rounded-2xl text-gray-400 hover:text-[#04724D] transition-all shadow-sm">
            <ChevronRightIcon class="w-5 h-5" />
          </Link>
          <div>
            <h1 class="text-3xl font-black text-gray-900 font-['Cairo'] tracking-tight">{{ is_edit ? 'تعديل المنتج' : 'إضافة منتج جديد' }}</h1>
            <p class="text-gray-500 font-medium">تحديد خصائص المنتج، الأصناف المتاحة، وأرصدة المخزون.</p>
          </div>
        </div>
        <div v-if="submitError" class="text-red-500 text-xs font-bold bg-red-50 px-4 py-2 rounded-xl max-w-sm text-right font-['Cairo']">{{ submitError }}</div>
        <button 
          @click="submit"
          :disabled="loading"
          class="bg-[#04724D] text-white px-10 py-4 rounded-2xl font-black text-lg hover:bg-[#058a5e] transition-all shadow-xl shadow-[#04724D]/20 flex items-center gap-3 disabled:opacity-70 font-['Cairo']"
        >
          <div v-if="loading" class="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin"></div>
          <CheckIcon v-else class="w-6 h-6" />
          {{ is_edit ? 'تحديث المنتج' : 'حفظ المنتج' }}
        </button>
      </div>

      <div class="flex flex-col lg:flex-row gap-8">
        <!-- Form Navigation -->
        <div class="lg:w-80 space-y-3">
          <button 
            v-for="tab in tabs" 
            :key="tab.id"
            @click="activeTab = tab.id"
            :class="activeTab === tab.id ? 'bg-[#04724D]/10 text-[#04724D] border-[#04724D]/20 shadow-sm' : 'text-gray-500 border-transparent hover:bg-gray-50'"
            class="w-full flex items-center gap-4 p-5 rounded-2xl border transition-all text-sm font-black font-['Cairo'] group"
          >
            <component :is="tab.icon" :class="activeTab === tab.id ? 'text-[#04724D]' : 'text-gray-400 group-hover:text-gray-600'" class="w-6 h-6 transition-colors" />
            {{ tab.name }}
          </button>

          <div class="p-8 bg-[#3EFF8B]/10 rounded-[32px] mt-8 border border-[#3EFF8B]/20">
            <div class="flex items-center gap-3 mb-4">
              <InformationCircleIcon class="w-6 h-6 text-[#04724D]" />
              <h4 class="text-xs font-black text-[#04724D] uppercase tracking-widest font-['Cairo']">معاينة المنتج</h4>
            </div>
            <p class="text-[11px] text-[#04724D]/70 font-bold leading-relaxed mb-6 font-['Cairo']">أكمل الحقول لترى كيف سيظهر المنتج في مخزونك ونقطة البيع.</p>
            <div class="bg-white p-5 rounded-3xl border border-[#3EFF8B]/30 shadow-sm flex items-center gap-4 transition-all">
              <div class="w-14 h-14 rounded-2xl bg-gray-50 flex items-center justify-center border border-gray-100 shadow-inner">
                 <img v-if="imagePreview" :src="imagePreview" class="w-full h-full object-contain rounded-2xl p-1" />
                 <TagIcon v-else class="w-6 h-6 text-gray-200" />
              </div>
              <div class="min-w-0">
                 <p class="text-sm font-black text-gray-900 truncate font-['Cairo']">{{ form.name || 'اسم المنتج' }}</p>
                 <p class="text-xs font-black text-[#04724D] tracking-tight">{{ form.price }} ج.م</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Form Content -->
        <div class="flex-1">
          <div class="bg-white rounded-[40px] shadow-2xl shadow-[#04724D]/5 border border-gray-100 overflow-hidden min-h-[600px]">
            
            <!-- General Tab -->
            <div v-if="activeTab === 'general'" class="p-10 space-y-12 animate-in fade-in slide-in-from-bottom-4 duration-500">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-10">
                <div class="space-y-3">
                  <label class="text-xs font-black text-gray-400 uppercase tracking-widest mr-1 font-['Cairo']">اسم المنتج</label>
                  <input v-model="form.name" type="text" class="w-full bg-gray-50 dark:bg-white/5 border-none rounded-2xl p-5 text-sm font-black focus:ring-2 focus:ring-[#04724D]/20 transition-all text-gray-700 dark:text-gray-200" placeholder="أدخل اسم المنتج..." />
                </div>
                <div class="space-y-3">
                  <label class="text-xs font-black text-gray-400 uppercase tracking-widest mr-1 font-['Cairo']">كود المنتج (SKU)</label>
                  <div class="flex gap-2">
                    <input v-model="form.code" type="text" class="flex-1 bg-gray-50 dark:bg-white/5 border-none rounded-2xl p-5 text-sm font-black focus:ring-2 focus:ring-[#04724D]/20 font-mono text-gray-700 dark:text-gray-200 uppercase" />
                    <button type="button" @click="generateCode" class="px-6 bg-[#04724D]/5 text-[#04724D] rounded-2xl border border-[#04724D]/20 hover:bg-[#04724D]/10 transition-all font-black text-xs font-['Cairo'] whitespace-nowrap">⚡ إنشاء</button>
                  </div>
                </div>
                <div class="space-y-3">
                  <label class="text-xs font-black text-gray-400 uppercase tracking-widest mr-1 font-['Cairo']">التصنيف</label>
                  <select v-model="form.category_id" class="w-full bg-gray-50 border-none rounded-2xl p-5 text-sm font-black focus:ring-2 focus:ring-[#04724D]/20 text-gray-700 font-['Cairo']">
                    <option value="">اختر التصنيف</option>
                    <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                  </select>
                </div>
                <div class="space-y-3">
                  <label class="text-xs font-black text-gray-400 uppercase tracking-widest mr-1 font-['Cairo']">العلامة التجارية</label>
                  <select v-model="form.brand_id" class="w-full bg-gray-50 border-none rounded-2xl p-5 text-sm font-black focus:ring-2 focus:ring-[#04724D]/20 text-gray-700 font-['Cairo']">
                    <option value="">اختر العلامة التجارية</option>
                    <option v-for="b in brands" :key="b.id" :value="b.id">{{ b.name }}</option>
                  </select>
                </div>
                <div class="md:col-span-2 space-y-3">
                  <label class="text-xs font-black text-gray-400 uppercase tracking-widest mr-1 font-['Cairo']">صورة المنتج</label>
                  <div class="flex items-start gap-10 bg-gray-50/50 p-8 rounded-[32px] border border-dashed border-gray-100">
                     <div @click="triggerImagePicker" class="w-40 h-40 rounded-[32px] bg-white border-2 border-dashed border-gray-200 flex items-center justify-center overflow-hidden transition-all hover:border-[#04724D]/30 relative group shadow-sm cursor-pointer">
                        <img v-if="imagePreview || product?.image" :src="imagePreview || '/images/products/'+product?.image" class="w-full h-full object-contain p-3" />
                        <div v-else class="flex flex-col items-center gap-2">
                           <PhotoIcon class="w-10 h-10 text-gray-200" />
                           <span class="text-[10px] text-gray-300 font-bold">انقر للرفع</span>
                        </div>
                     </div>
                     <input ref="imageInputRef" type="file" @change="onImageChange" accept="image/*" class="hidden" />
                     <div class="flex-1 space-y-4">
                        <p class="text-xs text-gray-400 leading-relaxed font-bold font-['Cairo']">قم بتحميل صورة واضحة للمنتج. يفضل أن تكون الخلفية بيضاء لعرض أفضل في نقطة البيع. التنسيقات المدعومة: PNG, JPG, WEBP. الحد الأقصى للحجم: 2 ميجابايت.</p>
                        <button type="button" @click="triggerImagePicker" class="text-xs font-black text-[#04724D] px-6 py-3 bg-[#04724D]/5 rounded-xl border border-[#04724D]/20 hover:bg-[#04724D]/10 transition-all font-['Cairo']">📎 اختيار ملف</button>
                        <p v-if="form.image" class="text-[10px] text-[#04724D] font-bold font-['Cairo']">✓ {{ form.image.name }}</p>
                     </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Pricing Tab -->
            <div v-if="activeTab === 'pricing'" class="p-10 space-y-12 animate-in fade-in slide-in-from-bottom-4 duration-500">
              <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div class="space-y-3 col-span-1">
                  <label class="text-xs font-black text-gray-400 uppercase tracking-widest mr-1 font-['Cairo']">نوع المنتج</label>
                  <select v-model="form.type" class="w-full bg-gray-50 border-none rounded-2xl p-5 text-sm font-black focus:ring-2 focus:ring-[#04724D]/20 text-gray-700 font-['Cairo']">
                    <option value="is_single">فردي</option>
                    <option value="is_variant">متعدد (أصناف)</option>
                    <option value="is_combo">مجموعة</option>
                    <option value="is_service">خدمة</option>
                  </select>
                </div>
                <div class="space-y-3 col-span-1">
                  <label class="text-xs font-black text-gray-400 uppercase tracking-widest mr-1 font-['Cairo']">الوحدة</label>
                  <select v-model="form.unit_id" class="w-full bg-gray-50 border-none rounded-2xl p-5 text-sm font-black focus:ring-2 focus:ring-[#04724D]/20 text-gray-700 font-['Cairo']">
                    <option v-for="u in units" :key="u.id" :value="u.id">{{ u.name }} ({{ u.ShortName }})</option>
                  </select>
                </div>
                <div class="space-y-3 col-span-1">
                   <label class="text-xs font-black text-gray-400 uppercase tracking-widest mr-1 font-['Cairo']">طريقة الضريبة</label>
                   <select v-model="form.tax_method" class="w-full bg-gray-50 border-none rounded-2xl p-5 text-sm font-black focus:ring-2 focus:ring-[#04724D]/20 text-gray-700 font-['Cairo']">
                     <option value="1">غير شاملة</option>
                     <option value="2">شاملة</option>
                   </select>
                </div>
                <div class="space-y-3 col-span-1">
                  <label class="text-xs font-black text-gray-400 uppercase tracking-widest mr-1 font-['Cairo']">نوع الخصم</label>
                  <select v-model="form.discount_method" class="w-full bg-gray-50 border-none rounded-2xl p-5 text-sm font-black focus:ring-2 focus:ring-[#04724D]/20 text-gray-700 font-['Cairo']">
                    <option value="1">ثابت</option>
                    <option value="2">نسبة مئوية</option>
                  </select>
                </div>
              </div>

              <div v-if="form.type !== 'is_variant'" class="grid grid-cols-1 md:grid-cols-4 gap-8 p-10 bg-gray-50/50 rounded-[40px] border border-gray-100 shadow-inner">
                <div class="space-y-3">
                  <label class="text-xs font-black text-gray-900 uppercase tracking-widest mr-1 font-['Cairo']">تكلفة الوحدة</label>
                  <input v-model="form.cost" type="number" class="w-full bg-white border-none rounded-2xl p-5 text-sm font-black focus:ring-2 focus:ring-[#04724D]/20 shadow-sm" />
                </div>
                <div class="space-y-3">
                  <label class="text-xs font-black text-gray-900 uppercase tracking-widest mr-1 font-['Cairo']">سعر البيع</label>
                  <input v-model="form.price" type="number" class="w-full bg-white border-none rounded-2xl p-5 text-sm font-black focus:ring-2 focus:ring-[#04724D]/20 shadow-sm" />
                </div>
                <div class="space-y-3">
                  <label class="text-xs font-black text-gray-900 uppercase tracking-widest mr-1 font-['Cairo']">سعر الجملة</label>
                  <input v-model="form.wholesale_price" type="number" class="w-full bg-white border-none rounded-2xl p-5 text-sm font-black focus:ring-2 focus:ring-[#04724D]/20 shadow-sm" />
                </div>
                <div class="space-y-3">
                  <label class="text-xs font-black text-gray-900 uppercase tracking-widest mr-1 font-['Cairo']">الحد الأدنى للسعر</label>
                  <input v-model="form.min_price" type="number" class="w-full bg-white border-none rounded-2xl p-5 text-sm font-black focus:ring-2 focus:ring-[#04724D]/20 shadow-sm" />
                </div>
              </div>

              <!-- Variants Section -->
              <div v-if="form.type === 'is_variant'" class="space-y-8">
                 <div class="flex items-center justify-between">
                    <h4 class="text-lg font-black text-gray-900 font-['Cairo']">أصناف المنتج (الألوان، المقاسات...)</h4>
                    <button @click="addVariant" type="button" class="px-6 py-2.5 bg-[#04724D]/10 text-[#04724D] rounded-xl font-black text-xs font-['Cairo'] flex items-center gap-2 hover:bg-[#04724D]/20 transition-all">
                      <PlusIcon class="w-5 h-5" /> إضافة صنف
                    </button>
                 </div>
                 <div class="grid grid-cols-1 gap-4">
                    <div v-for="(v, index) in form.variants" :key="index" class="p-6 bg-white border border-gray-100 rounded-[32px] grid grid-cols-12 gap-6 items-center shadow-sm hover:shadow-md transition-all">
                       <div class="col-span-3">
                          <input v-model="v.text" type="text" placeholder="الاسم (أحمر، XL..)" class="w-full bg-gray-50 border-none rounded-2xl p-4 text-xs font-bold focus:ring-1 font-['Cairo']" />
                       </div>
                       <div class="col-span-3">
                          <input v-model="v.code" type="text" placeholder="كود الصنف SKU" class="w-full bg-gray-50 border-none rounded-2xl p-4 text-xs font-mono focus:ring-1" />
                       </div>
                       <div class="col-span-2">
                          <input v-model="v.cost" type="number" placeholder="التكلفة" class="w-full bg-gray-50 border-none rounded-2xl p-4 text-xs font-bold focus:ring-1" />
                       </div>
                       <div class="col-span-2">
                          <input v-model="v.price" type="number" placeholder="السعر" class="w-full bg-gray-50 border-none rounded-2xl p-4 text-xs font-bold focus:ring-1" />
                       </div>
                       <div class="col-span-2 flex justify-end">
                          <button @click="removeVariant(index)" type="button" class="p-3 text-gray-300 hover:text-red-500 transition-colors bg-red-50 rounded-xl">
                             <TrashIcon class="w-6 h-6" />
                          </button>
                       </div>
                    </div>
                 </div>
              </div>
            </div>

            <!-- Stock Tab -->
            <div v-if="activeTab === 'stock'" class="p-10 space-y-10 animate-in fade-in slide-in-from-bottom-4 duration-500">
              <div class="bg-amber-50 p-8 rounded-[32px] border border-amber-100 flex gap-5">
                 <InformationCircleIcon class="w-8 h-8 text-amber-600 shrink-0" />
                 <p class="text-xs text-amber-900/70 leading-relaxed font-bold font-['Cairo']">تعريف الرصيد الافتتاحي سيقوم بإنشاء "تعديل مخزني" تلقائي لتحديد الكمية الأولية وتكلفة الأساس في مخازنك. ينصح بهذا فقط للإعدادات الجديدة.</p>
              </div>
              
              <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                 <div v-for="w in warehouses" :key="w.id" class="p-8 bg-gray-50/50 rounded-[32px] border border-gray-100 space-y-4 shadow-inner">
                    <div class="flex items-center gap-3">
                       <div class="w-3 h-3 rounded-full bg-[#3EFF8B] shadow-sm"></div>
                       <h5 class="text-sm font-black text-gray-700 uppercase tracking-tight font-['Cairo']">{{ w.name }}</h5>
                    </div>
                    <div class="space-y-2">
                       <label class="text-[11px] font-black text-gray-400 uppercase mr-1 font-['Cairo'] tracking-widest">الكمية الافتتاحية</label>
                       <input v-model="form.warehouses[w.id].qte" type="number" class="w-full bg-white border-none rounded-2xl p-5 text-sm font-black focus:ring-2 focus:ring-[#04724D]/20 shadow-sm" />
                    </div>
                 </div>
              </div>
            </div>

            <!-- Advanced Tab -->
            <div v-if="activeTab === 'advanced'" class="p-10 space-y-16 animate-in fade-in slide-in-from-bottom-4 duration-500">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-x-16 gap-y-16">
                 <div class="space-y-8">
                    <h4 class="text-lg font-black text-gray-900 border-b border-gray-50 pb-4 font-['Cairo']">الحالة والتحكم</h4>
                    <div class="space-y-5">
                       <label class="flex items-center justify-between p-6 bg-gray-50/50 border border-gray-100 rounded-[2rem] cursor-pointer hover:bg-gray-100/50 transition-all font-['Cairo']">
                          <span class="text-sm font-black text-gray-700">تفعيل المنتج (نشط)</span>
                          <div @click="form.is_active = !form.is_active" :class="form.is_active ? 'bg-[#04724D]' : 'bg-gray-200'" class="w-14 h-8 rounded-full relative transition-all shadow-inner">
                             <div :class="form.is_active ? 'translate-x-7' : 'translate-x-1'" class="w-6 h-6 bg-white rounded-full absolute top-1 transition-all shadow-md"></div>
                          </div>
                       </label>
                       <label class="flex items-center justify-between p-6 bg-gray-50/50 border border-gray-100 rounded-[2rem] cursor-pointer hover:bg-gray-100/50 transition-all font-['Cairo']">
                          <span class="text-sm font-black text-gray-700">تمييز المنتج في المتجر أونلاين</span>
                          <div @click="form.is_featured = !form.is_featured" :class="form.is_featured ? 'bg-[#04724D]' : 'bg-gray-200'" class="w-14 h-8 rounded-full relative transition-all shadow-inner">
                             <div :class="form.is_featured ? 'translate-x-7' : 'translate-x-1'" class="w-6 h-6 bg-white rounded-full absolute top-1 transition-all shadow-md"></div>
                          </div>
                       </label>
                       <label class="flex items-center justify-between p-6 bg-red-50/50 border border-red-100/30 rounded-[2rem] cursor-pointer hover:bg-red-50 transition-all text-red-600 font-['Cairo']">
                          <span class="text-sm font-black">إيقاف البيع والطلب</span>
                          <div @click="form.not_selling = !form.not_selling" :class="form.not_selling ? 'bg-red-500' : 'bg-gray-200'" class="w-14 h-8 rounded-full relative transition-all shadow-inner">
                             <div :class="form.not_selling ? 'translate-x-7' : 'translate-x-1'" class="w-6 h-6 bg-white rounded-full absolute top-1 transition-all shadow-md"></div>
                          </div>
                       </label>
                    </div>
                 </div>

                 <div class="space-y-8">
                    <h4 class="text-lg font-black text-gray-900 border-b border-gray-50 pb-4 font-['Cairo']">المطابقة والضمانات</h4>
                    <div class="grid grid-cols-2 gap-8">
                       <div class="space-y-3">
                          <label class="text-[11px] font-black text-gray-400 uppercase tracking-widest mr-1 font-['Cairo']">تنبيه انخفاض المخزون</label>
                          <input v-model="form.stock_alert" type="number" class="w-full bg-gray-50 border-none rounded-2xl p-5 text-sm font-black" />
                       </div>
                       <div class="space-y-3">
                          <label class="text-[11px] font-black text-gray-400 uppercase tracking-widest mr-1 font-['Cairo']">وزن المنتج (كجم)</label>
                          <input v-model="form.weight" type="number" class="w-full bg-gray-50 border-none rounded-2xl p-5 text-sm font-black" />
                       </div>
                    </div>
                    <div class="flex items-center gap-6">
                       <label class="flex-1 flex items-center justify-between p-6 bg-[#3EFF8B]/10 rounded-[2rem] border border-[#3EFF8B]/20 cursor-pointer font-['Cairo']">
                          <span class="text-xs font-black text-[#04724D] uppercase tracking-widest">تفعيل الضمان</span>
                          <div @click="form.has_guarantee = !form.has_guarantee" :class="form.has_guarantee ? 'bg-[#04724D]' : 'bg-gray-200'" class="w-14 h-8 rounded-full relative transition-all shadow-inner">
                             <div :class="form.has_guarantee ? 'translate-x-7' : 'translate-x-1'" class="w-6 h-6 bg-white rounded-full absolute top-1 transition-all shadow-md"></div>
                          </div>
                       </label>
                    </div>
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
.fade-in {
  animation: fadeIn 0.4s ease-out;
}
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(8px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
