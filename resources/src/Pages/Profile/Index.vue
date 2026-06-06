<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, ref } from 'vue';
import {
  BadgeCheck,
  Camera,
  Eye,
  EyeOff,
  LockKeyhole,
  Mail,
  Phone,
  Save,
  ShieldCheck,
  UserRound,
  Warehouse,
} from 'lucide-vue-next';

const props = defineProps({
  profile: { type: Object, required: true },
  role: { type: String, default: '' },
  warehouses: { type: Array, default: () => [] },
});

const page = usePage();
const avatarPreview = ref(null);
const showCurrentPassword = ref(false);
const showNewPassword = ref(false);

const profileForm = useForm({
  firstname: props.profile.firstname || '',
  lastname: props.profile.lastname || '',
  username: props.profile.username || '',
  email: props.profile.email || '',
  phone: props.profile.phone || '',
  avatar: null,
});

const passwordForm = useForm({
  current_password: '',
  password: '',
  password_confirmation: '',
});

const fullName = computed(() => {
  const name = `${profileForm.firstname} ${profileForm.lastname}`.trim();
  return name || profileForm.username;
});

const avatarUrl = computed(() => {
  if (avatarPreview.value) return avatarPreview.value;
  return `/images/avatar/${props.profile.avatar || 'no_avatar.png'}`;
});

const successMessage = computed(() => page.props.flash?.success || '');

const selectAvatar = (event) => {
  const file = event.target.files?.[0];
  if (!file) return;

  if (avatarPreview.value) URL.revokeObjectURL(avatarPreview.value);
  avatarPreview.value = URL.createObjectURL(file);
  profileForm.avatar = file;
};

const updateProfile = () => {
  profileForm.post('/admin/profile', {
    forceFormData: true,
    preserveScroll: true,
    onSuccess: () => {
      profileForm.avatar = null;
    },
  });
};

const updatePassword = () => {
  passwordForm.put('/admin/profile/password', {
    preserveScroll: true,
    onSuccess: () => passwordForm.reset(),
  });
};

onBeforeUnmount(() => {
  if (avatarPreview.value) URL.revokeObjectURL(avatarPreview.value);
});
</script>

<template>
  <AdminLayout>
    <Head title="الملف الشخصي" />

    <div class="space-y-8 p-2 sm:p-4" dir="rtl">
      <div>
        <p class="mb-2 text-xs font-black uppercase tracking-[0.25em] text-[#04724D] dark:text-[#3EFF8B]">
          إعدادات الحساب
        </p>
        <h1 class="text-3xl font-black text-gray-900 dark:text-white">الملف الشخصي</h1>
        <p class="mt-2 text-sm font-medium text-gray-500 dark:text-gray-400">
          حدّث بياناتك وصورتك وكلمة المرور من صفحة واحدة.
        </p>
      </div>

      <div
        v-if="successMessage"
        aria-live="polite"
        class="flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-black text-emerald-700 dark:border-emerald-900/60 dark:bg-emerald-950/30 dark:text-emerald-300"
      >
        <BadgeCheck class="h-5 w-5 shrink-0" />
        {{ successMessage }}
      </div>

      <div class="grid gap-8 xl:grid-cols-[320px_minmax(0,1fr)]">
        <aside class="space-y-6">
          <section class="rounded-[32px] border border-gray-100 bg-white p-7 shadow-sm dark:border-gray-800 dark:bg-[#121212]">
            <div class="text-center">
              <div class="relative mx-auto mb-5 h-28 w-28">
                <img
                  :src="avatarUrl"
                  :alt="`صورة ${fullName}`"
                  class="h-28 w-28 rounded-[30px] border-4 border-white object-cover shadow-xl dark:border-gray-800"
                />
                <label
                  for="profile-avatar"
                  class="absolute -bottom-2 -left-2 flex h-12 w-12 cursor-pointer items-center justify-center rounded-2xl bg-[#04724D] text-white shadow-lg transition hover:bg-[#058a5e] focus-within:ring-4 focus-within:ring-[#04724D]/20"
                  aria-label="تغيير الصورة الشخصية"
                >
                  <Camera class="h-5 w-5" />
                  <input
                    id="profile-avatar"
                    type="file"
                    accept="image/jpeg,image/png,image/webp"
                    class="sr-only"
                    @change="selectAvatar"
                  />
                </label>
              </div>

              <h2 class="truncate text-xl font-black text-gray-900 dark:text-white">{{ fullName }}</h2>
              <p class="mt-1 truncate text-sm font-medium text-gray-500">{{ profileForm.email }}</p>
            </div>

            <div class="mt-7 space-y-3 border-t border-gray-100 pt-6 dark:border-gray-800">
              <div class="flex items-center justify-between rounded-2xl bg-gray-50 px-4 py-3 dark:bg-white/5">
                <span class="flex items-center gap-2 text-xs font-bold text-gray-500">
                  <ShieldCheck class="h-4 w-4" />
                  الدور
                </span>
                <span class="text-xs font-black text-gray-900 dark:text-white">{{ role || 'مستخدم' }}</span>
              </div>
              <div class="flex items-center justify-between rounded-2xl bg-gray-50 px-4 py-3 dark:bg-white/5">
                <span class="flex items-center gap-2 text-xs font-bold text-gray-500">
                  <LockKeyhole class="h-4 w-4" />
                  التحقق بخطوتين
                </span>
                <span
                  class="rounded-full px-3 py-1 text-[11px] font-black"
                  :class="profile.two_factor_enabled
                    ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300'
                    : 'bg-gray-200 text-gray-600 dark:bg-white/10 dark:text-gray-400'"
                >
                  {{ profile.two_factor_enabled ? 'مفعّل' : 'غير مفعّل' }}
                </span>
              </div>
            </div>
          </section>

          <section class="rounded-[32px] border border-gray-100 bg-white p-7 shadow-sm dark:border-gray-800 dark:bg-[#121212]">
            <div class="mb-4 flex items-center gap-3">
              <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#04724D]/10 text-[#04724D] dark:text-[#3EFF8B]">
                <Warehouse class="h-5 w-5" />
              </div>
              <div>
                <h3 class="text-sm font-black text-gray-900 dark:text-white">المستودعات المعيّنة</h3>
                <p class="text-xs text-gray-400">{{ warehouses.length }} مستودع</p>
              </div>
            </div>
            <div v-if="warehouses.length" class="flex flex-wrap gap-2">
              <span
                v-for="warehouse in warehouses"
                :key="warehouse"
                class="rounded-xl bg-gray-50 px-3 py-2 text-xs font-bold text-gray-600 dark:bg-white/5 dark:text-gray-300"
              >
                {{ warehouse }}
              </span>
            </div>
            <p v-else class="text-sm font-medium text-gray-400">لا توجد مستودعات محددة لهذا الحساب.</p>
          </section>
        </aside>

        <main class="space-y-8">
          <form
            class="rounded-[32px] border border-gray-100 bg-white p-6 shadow-sm sm:p-8 dark:border-gray-800 dark:bg-[#121212]"
            @submit.prevent="updateProfile"
          >
            <div class="mb-8 flex items-center gap-4">
              <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#04724D]/10 text-[#04724D] dark:text-[#3EFF8B]">
                <UserRound class="h-6 w-6" />
              </div>
              <div>
                <h2 class="text-lg font-black text-gray-900 dark:text-white">البيانات الأساسية</h2>
                <p class="text-sm text-gray-400">البيانات الظاهرة في النظام والفواتير.</p>
              </div>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
              <div>
                <label for="firstname" class="mb-2 block text-sm font-black text-gray-700 dark:text-gray-300">الاسم الأول *</label>
                <input id="firstname" v-model="profileForm.firstname" required autocomplete="given-name" class="profile-input" />
                <p v-if="profileForm.errors.firstname" role="alert" class="mt-2 text-xs font-bold text-red-600">{{ profileForm.errors.firstname }}</p>
              </div>
              <div>
                <label for="lastname" class="mb-2 block text-sm font-black text-gray-700 dark:text-gray-300">اسم العائلة *</label>
                <input id="lastname" v-model="profileForm.lastname" required autocomplete="family-name" class="profile-input" />
                <p v-if="profileForm.errors.lastname" role="alert" class="mt-2 text-xs font-bold text-red-600">{{ profileForm.errors.lastname }}</p>
              </div>
              <div>
                <label for="username" class="mb-2 block text-sm font-black text-gray-700 dark:text-gray-300">اسم المستخدم *</label>
                <input id="username" v-model="profileForm.username" required autocomplete="username" class="profile-input" />
                <p v-if="profileForm.errors.username" role="alert" class="mt-2 text-xs font-bold text-red-600">{{ profileForm.errors.username }}</p>
              </div>
              <div>
                <label for="email" class="mb-2 block text-sm font-black text-gray-700 dark:text-gray-300">البريد الإلكتروني *</label>
                <div class="relative">
                  <Mail class="absolute right-4 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" />
                  <input id="email" v-model="profileForm.email" required type="email" autocomplete="email" class="profile-input pr-12" />
                </div>
                <p v-if="profileForm.errors.email" role="alert" class="mt-2 text-xs font-bold text-red-600">{{ profileForm.errors.email }}</p>
              </div>
              <div class="md:col-span-2">
                <label for="phone" class="mb-2 block text-sm font-black text-gray-700 dark:text-gray-300">رقم الهاتف</label>
                <div class="relative">
                  <Phone class="absolute right-4 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" />
                  <input id="phone" v-model="profileForm.phone" type="tel" autocomplete="tel" class="profile-input pr-12" />
                </div>
                <p v-if="profileForm.errors.phone" role="alert" class="mt-2 text-xs font-bold text-red-600">{{ profileForm.errors.phone }}</p>
              </div>
            </div>

            <div class="mt-8 flex justify-end border-t border-gray-100 pt-6 dark:border-gray-800">
              <button type="submit" :disabled="profileForm.processing" class="primary-button">
                <Save class="h-5 w-5" />
                {{ profileForm.processing ? 'جارٍ الحفظ...' : 'حفظ البيانات' }}
              </button>
            </div>
          </form>

          <form
            class="rounded-[32px] border border-gray-100 bg-white p-6 shadow-sm sm:p-8 dark:border-gray-800 dark:bg-[#121212]"
            @submit.prevent="updatePassword"
          >
            <div class="mb-8 flex items-center gap-4">
              <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300">
                <LockKeyhole class="h-6 w-6" />
              </div>
              <div>
                <h2 class="text-lg font-black text-gray-900 dark:text-white">تغيير كلمة المرور</h2>
                <p class="text-sm text-gray-400">استخدم كلمة مرور لا تقل عن 8 أحرف.</p>
              </div>
            </div>

            <div class="grid gap-6">
              <div>
                <label for="current-password" class="mb-2 block text-sm font-black text-gray-700 dark:text-gray-300">كلمة المرور الحالية *</label>
                <div class="relative">
                  <input
                    id="current-password"
                    v-model="passwordForm.current_password"
                    :type="showCurrentPassword ? 'text' : 'password'"
                    required
                    autocomplete="current-password"
                    class="profile-input pl-12"
                  />
                  <button
                    type="button"
                    class="absolute left-2 top-1/2 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-xl text-gray-400 hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-white/10 dark:hover:text-white"
                    :aria-label="showCurrentPassword ? 'إخفاء كلمة المرور' : 'إظهار كلمة المرور'"
                    @click="showCurrentPassword = !showCurrentPassword"
                  >
                    <EyeOff v-if="showCurrentPassword" class="h-5 w-5" />
                    <Eye v-else class="h-5 w-5" />
                  </button>
                </div>
                <p v-if="passwordForm.errors.current_password" role="alert" class="mt-2 text-xs font-bold text-red-600">{{ passwordForm.errors.current_password }}</p>
              </div>

              <div class="grid gap-6 md:grid-cols-2">
                <div>
                  <label for="new-password" class="mb-2 block text-sm font-black text-gray-700 dark:text-gray-300">كلمة المرور الجديدة *</label>
                  <div class="relative">
                    <input
                      id="new-password"
                      v-model="passwordForm.password"
                      :type="showNewPassword ? 'text' : 'password'"
                      required
                      minlength="8"
                      autocomplete="new-password"
                      class="profile-input pl-12"
                    />
                    <button
                      type="button"
                      class="absolute left-2 top-1/2 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-xl text-gray-400 hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-white/10 dark:hover:text-white"
                      :aria-label="showNewPassword ? 'إخفاء كلمة المرور' : 'إظهار كلمة المرور'"
                      @click="showNewPassword = !showNewPassword"
                    >
                      <EyeOff v-if="showNewPassword" class="h-5 w-5" />
                      <Eye v-else class="h-5 w-5" />
                    </button>
                  </div>
                  <p v-if="passwordForm.errors.password" role="alert" class="mt-2 text-xs font-bold text-red-600">{{ passwordForm.errors.password }}</p>
                </div>
                <div>
                  <label for="password-confirmation" class="mb-2 block text-sm font-black text-gray-700 dark:text-gray-300">تأكيد كلمة المرور *</label>
                  <input
                    id="password-confirmation"
                    v-model="passwordForm.password_confirmation"
                    :type="showNewPassword ? 'text' : 'password'"
                    required
                    minlength="8"
                    autocomplete="new-password"
                    class="profile-input"
                  />
                </div>
              </div>
            </div>

            <div class="mt-8 flex justify-end border-t border-gray-100 pt-6 dark:border-gray-800">
              <button type="submit" :disabled="passwordForm.processing" class="primary-button">
                <LockKeyhole class="h-5 w-5" />
                {{ passwordForm.processing ? 'جارٍ التحديث...' : 'تحديث كلمة المرور' }}
              </button>
            </div>
          </form>
        </main>
      </div>
    </div>
  </AdminLayout>
</template>

<style scoped>
.profile-input {
  @apply min-h-12 w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 text-base font-bold text-gray-900 outline-none transition focus:border-[#04724D] focus:ring-4 focus:ring-[#04724D]/10 dark:border-gray-700 dark:bg-white/5 dark:text-white;
}

.primary-button {
  @apply flex min-h-12 cursor-pointer items-center justify-center gap-2 rounded-2xl bg-[#04724D] px-7 py-3 text-sm font-black text-white shadow-lg shadow-[#04724D]/20 transition hover:bg-[#058a5e] focus:outline-none focus:ring-4 focus:ring-[#04724D]/20 disabled:cursor-not-allowed disabled:opacity-50;
}
</style>
