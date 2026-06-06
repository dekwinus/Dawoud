<script setup>
import { ref } from 'vue';
import { Link, Head } from '@inertiajs/vue3';
import { useCart } from '@/Composables/useCart';
import StoreLayout from '@/Layouts/StoreLayout.vue';
import {
  ArrowLeft,
  ShoppingBag,
  Bot,
  Rocket,
  ShieldCheck,
  TrendingUp,
  Building2,
  Headphones,
  CheckCircle2,
  Sprout,
  Users
} from 'lucide-vue-next';
import ProductCard from '@/Components/ProductCard.vue';

const props = defineProps({
  s: Object,
  blocks: Array,
  categories: Array,
  banners: Array,
});

const { addToCart } = useCart();
</script>

<template>
  <StoreLayout>
    <Head :title="s?.seo_meta_title || s?.store_name || 'المتجر'" />
    <div class="bg-gray-50 dark:bg-night-950 transition-colors duration-500 font-['Cairo'] pb-20" dir="rtl">

      <!-- ============== PREMIUM HERO ============== -->
      <section class="relative min-h-[90vh] flex items-center overflow-hidden bg-white dark:bg-night-900 border-b border-gray-100 dark:border-night-800">
        <!-- Modern Background Assets -->
        <div class="absolute inset-0 pointer-events-none">
          <div class="absolute top-0 right-0 w-full h-full bg-[radial-gradient(circle_at_top_right,#04724D08,transparent_50%)]"></div>
          <div class="absolute bottom-0 left-0 w-full h-full bg-[radial-gradient(circle_at_bottom_left,#3EFF8B05,transparent_50%)]"></div>
          <div class="absolute inset-0 opacity-[0.015]" style="background-image: url('https://www.transparenttextures.com/patterns/carbon-fibre.png');"></div>
        </div>

        <div class="container mx-auto px-4 relative z-10 py-24 md:py-28">
          <div class="max-w-5xl mx-auto text-center space-y-12">
            <!-- Animated Badge -->
            <div class="inline-flex items-center gap-3 bg-brand/5 dark:bg-accent/5 text-brand dark:text-accent border border-brand/10 dark:border-accent/10 rounded-full px-7 py-3 text-xs md:text-sm font-black uppercase tracking-[0.2em] animate-in fade-in slide-in-from-top-4 duration-1000">
              <Sprout class="w-4 h-4 md:w-5 md:h-5 animate-bounce" />
              {{ s?.hero_badge || 'نظام التوريد الزراعي المتكامل' }}
            </div>

            <!-- Massive Headline -->
            <h1 class="text-6xl md:text-8xl lg:text-9xl font-black tracking-tighter text-gray-900 dark:text-white leading-[0.86] animate-in fade-in slide-in-from-bottom-8 duration-1000 delay-200">
              {{ (s?.hero_title && s.hero_title !== 'Sell online & in-store') ? s.hero_title : 'رعاية أفضل' }}<br>
              <span class="text-transparent bg-clip-text bg-gradient-to-l from-brand to-accent">إنتاج أوفر</span>
            </h1>

            <!-- Elegant Subtitle -->
            <p class="text-base md:text-xl text-gray-500 dark:text-gray-400 font-semibold max-w-3xl mx-auto leading-relaxed animate-in fade-in slide-in-from-bottom-6 duration-1000 delay-400">
               {{ (s?.hero_subtitle && !s.hero_subtitle.includes('Beautiful')) ? s.hero_subtitle : 'كل ما تحتاجه مزرعتك من أعلاف ومكملات ومستلزمات رعاية موثوقة للمواشي والدواجن، لتربية أفضل وإنتاج أقوى.' }}
            </p>

            <!-- Creative CTAs -->
            <div class="flex flex-col sm:flex-row items-center justify-center gap-5 pt-7 animate-in fade-in slide-in-from-bottom-4 duration-1000 delay-600">
              <Link href="/shop"
                class="w-full sm:w-auto h-16 px-12 bg-gray-900 dark:bg-brand text-white dark:text-night-950 rounded-2xl font-black text-base hover:scale-105 active:scale-95 transition-all shadow-2xl flex items-center justify-center gap-4 group overflow-hidden relative border border-white/5">
                <div class="absolute inset-0 bg-white/10 -translate-x-full group-hover:translate-x-full transition-transform duration-700"></div>
                تصفح المنتجات
                <ShoppingBag class="w-6 h-6 group-hover:rotate-12 transition-transform" />
              </Link>
              <Link href="/chatbot"
                class="w-full sm:w-auto h-16 px-12 bg-white dark:bg-night-800 text-gray-900 dark:text-white border border-gray-100 dark:border-night-700 rounded-2xl font-black text-base hover:bg-gray-50 dark:hover:bg-night-700 transition-all flex items-center justify-center gap-4 shadow-xl group">
                المساعد الذكي
                <Bot class="w-6 h-6 text-brand dark:text-accent group-hover:scale-125 transition-transform" />
              </Link>
            </div>
          </div>
        </div>

        <!-- Floating Abstract Elements -->
        <div class="absolute bottom-10 left-1/2 -translate-x-1/2 flex items-center gap-8 opacity-20 dark:opacity-40 grayscale hover:grayscale-0 transition-all duration-700">
           <!-- Add partner logos or icons here if needed -->
        </div>
      </section>

      <!-- ============== HIGH-DENSITY FEATURES ============== -->
      <section class="container mx-auto px-4 -mt-12 relative z-20">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
           <div v-for="(feat, i) in [
             { icon: ShieldCheck, label: 'جودة معتمدة', sub: 'بصمة حيوية 100%' },
             { icon: Rocket, label: 'توصيل سريع', sub: 'لكل أنحاء الجمهورية' },
             { icon: Building2, label: 'عقود مزارع', sub: 'تسهيلات ائتمانية' },
             { icon: Headphones, label: 'دعم فني', sub: 'استشارة زراعية' }
           ]" :key="i"
             class="bg-white/80 dark:bg-night-850/80 backdrop-blur-xl border border-white/50 dark:border-night-800 p-6 rounded-3xl shadow-xl shadow-gray-200/20 dark:shadow-none hover:-translate-y-1 transition-all duration-500 group">
             <div class="w-10 h-10 rounded-2xl bg-brand/10 dark:bg-accent/10 flex items-center justify-center text-brand dark:text-accent mb-4 group-hover:bg-brand group-hover:text-white dark:group-hover:bg-accent dark:group-hover:text-night-950 transition-all">
                <component :is="feat.icon" class="w-5 h-5" />
             </div>
             <p class="font-black text-gray-900 dark:text-white text-xs leading-none mb-1.5">{{ feat.label }}</p>
             <p class="text-[10px] font-bold text-gray-400 dark:text-gray-600 uppercase tracking-wide">{{ feat.sub }}</p>
           </div>
        </div>
      </section>

      <!-- ============== CATEGORIES GRID ============== -->
      <section v-if="categories && categories.length > 0" class="container mx-auto px-4 py-24">
        <div class="flex items-center justify-between gap-6 mb-12">
          <div class="text-right">
             <h2 class="text-3xl md:text-5xl font-black text-gray-900 dark:text-white tracking-tighter leading-none mb-3">تصفح <span class="text-brand dark:text-accent">الكتالوج</span></h2>
             <p class="text-xs text-gray-500 font-bold uppercase tracking-[0.2em]">اختر الفئة التي تخدم محاصيلك</p>
          </div>
          <Link href="/shop" class="group flex items-center gap-3 text-xs font-black text-brand dark:text-accent hover:underline decoration-2 underline-offset-8 transition-all">
             عرض الكل
             <ArrowLeft class="w-4 h-4 group-hover:-translate-x-1 transition-transform" />
          </Link>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
          <Link
            v-for="cat in categories.slice(0, 12)"
            :key="cat.id"
            :href="`/shop?category=${cat.id}`"
            class="group bg-white dark:bg-night-900 rounded-[2rem] p-6 text-center border border-gray-50 dark:border-night-800 hover:border-brand/40 dark:hover:border-accent/40 shadow-sm hover:shadow-2xl transition-all duration-500 overflow-hidden relative"
          >
            <!-- Animated background accent -->
            <div class="absolute inset-0 bg-brand/5 -translate-y-full group-hover:translate-y-0 transition-transform duration-500"></div>

            <div class="w-14 h-14 mx-auto mb-4 bg-gray-50 dark:bg-night-800 rounded-[1.25rem] flex items-center justify-center text-gray-300 dark:text-gray-700 group-hover:bg-brand dark:group-hover:bg-accent group-hover:text-white dark:group-hover:text-night-950 transition-all duration-500 shadow-inner group-hover:shadow-lg relative z-10">
               <Sprout class="w-7 h-7" v-if="cat.name.includes('بذور') || cat.name.includes('محاصيل')" />
               <ShoppingBag class="w-7 h-7" v-else />
            </div>
            <p class="font-black text-gray-700 dark:text-gray-300 group-hover:text-gray-900 dark:group-hover:text-white transition-colors text-xs uppercase tracking-wide leading-tight relative z-10">{{ cat.name }}</p>
          </Link>
        </div>
      </section>

      <!-- ============== PRODUCT SECTIONS ============== -->
      <div class="space-y-32 pb-10">
        <section v-for="(block, idx) in blocks" :key="idx" class="container mx-auto px-4">
          <template v-if="block.type === 'collection'">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-12">
               <div>
                  <h2 class="text-3xl md:text-5xl font-black text-gray-900 dark:text-white tracking-tighter leading-none mb-3">{{ block.title }}</h2>
                  <div class="h-1.5 w-24 bg-brand dark:bg-accent rounded-full"></div>
               </div>
               <Link :href="`/shop?collection=${block.collection?.slug || ''}`" class="h-10 px-6 bg-gray-50 dark:bg-night-800 text-gray-900 dark:text-white rounded-xl font-black text-[10px] hover:bg-brand hover:text-white dark:hover:bg-accent dark:hover:text-night-950 transition-all flex items-center gap-3 shadow-sm border border-gray-100 dark:border-night-700">
                  استكشاف المزيد
                  <ArrowLeft class="w-4 h-4" />
               </Link>
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 md:gap-8">
              <ProductCard
                v-for="product in block.products"
                :key="product.id"
                :product="product"
                :settings="s"
                @addToCart="addToCart"
              />
            </div>
          </template>
        </section>
      </div>

      <!-- ============== PREMIUM B2B CTA ============== -->
      <section class="container mx-auto px-4 pt-10">
        <div class="relative bg-gray-900 dark:bg-night-900 rounded-[3.5rem] p-12 md:p-24 text-white text-center overflow-hidden border border-white/5 shadow-2xl">
          <!-- Animated Background Elements -->
          <div class="absolute inset-0 pointer-events-none">
             <div class="absolute top-0 right-0 w-full h-full bg-[radial-gradient(circle_at_top_right,#04724D30,transparent_60%)]"></div>
             <div class="absolute bottom-0 left-0 w-full h-full bg-[radial-gradient(circle_at_bottom_left,#3EFF8B15,transparent_50%)]"></div>
             <div class="absolute inset-0 opacity-[0.03]" style="background-image:radial-gradient(circle,#fff 1px,transparent 1px);background-size:32px 32px;"></div>
          </div>

          <div class="relative z-10 max-w-3xl mx-auto space-y-10">
             <div class="inline-flex items-center gap-4 bg-brand/20 text-brand border border-brand/30 rounded-full px-8 py-3 text-[11px] font-black uppercase tracking-[0.4em]">
                <Rocket class="w-4 h-4" />
                حلول المؤسسات الزراعية
             </div>
             <h3 class="text-4xl md:text-7xl font-black tracking-[ -0.04em ] leading-[0.9] text-white">
               توريد <span class="text-brand">المحاصيل الكبرى</span><br>والدعم اللوجستي
             </h3>
             <p class="text-base text-gray-400 font-medium leading-relaxed max-w-xl mx-auto">نحن ندرك احتياجات المزارع الكبيرة والشركات. نوفر حلول تعاقدية مخصصة، دعم فني ميداني، وأنظمة توريد مستدامة.</p>
             <div class="flex flex-wrap items-center justify-center gap-4">
                <Link href="/wholesale-request"
                  class="h-14 px-12 bg-brand dark:bg-accent text-white dark:text-night-950 font-black text-sm rounded-2xl transition-all shadow-2xl shadow-brand/30 hover:scale-105 active:scale-95 group uppercase tracking-widest flex items-center justify-center gap-4">
                  تواصل للتوريد المباشر
                  <ArrowLeft class="w-5 h-5 group-hover:-translate-x-1 transition-transform" />
                </Link>
             </div>
          </div>
        </div>
      </section>

    </div>
  </StoreLayout>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
