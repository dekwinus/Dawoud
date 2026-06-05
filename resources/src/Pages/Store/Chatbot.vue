<script setup>
import { Link, usePage, Head } from '@inertiajs/vue3';
import { ref, computed, onMounted, nextTick } from 'vue';
import axios from 'axios';
import StoreLayout from '@/Layouts/StoreLayout.vue';
import { useCart } from '@/Composables/useCart';
import {
  Bot, Send, Sparkles, ArrowLeft, Zap, ShieldCheck,
  ShoppingBag, BarChart3, Loader2, Trash2, Search,
  Package, Tag, Star, ChevronRight, MessageCircle,
  ThumbsUp, Building2, Headphones, RefreshCw, X,
  ShoppingCart, Check, Sprout, Info
} from 'lucide-vue-next';

const props = defineProps({ s: Object, categories: Array, brands: Array });

// ── Comp & Store ──────────────────────────────────────────────────────
const { addToCart } = useCart();
const page = usePage();
const settings = computed(() => props.s || page.props.settings || {});

// ── State ─────────────────────────────────────────────────────────────
const inputMessage = ref('');
const loading = ref(false);
const chatContainer = ref(null);
const inputRef = ref(null);
const chatContext = ref([]); // Stores message context for the server
const charCount = computed(() => inputMessage.value.length);

const messages = ref([
  {
    role: 'bot',
    content: `أهلاً وسهلاً بك في **DawAI Agri**! مساعدك الزراعي الذكي 🌱\n\nأنا هنا لدعم مزرعتك في:\n• 🔍 البحث عن أفضل أنواع البذور والمحاصيل\n• 🌾 مراجعة أسعار الأعلاف والمواد الخام\n• 🧪 استشارة الأسمدة والمبيدات المناسبة\n• 🚛 طلبات التوريد بالجملة للمزارع والشركات`,
    time: nowTime(),
    id: 0,
  }
]);

// ── Pre-made Suggestion Groups ──────────────────────────────────────
const suggestionGroups = [
  {
    label: 'البذور والمحاصيل',
    icon: Sprout,
    pills: [
      { text: 'أجود أنواع بذور الذرة', icon: Sparkles },
      { text: 'أصناف الكتان المتاحة', icon: Tag },
      { text: 'بذور القمح جيزة 171', icon: Search },
    ]
  },
  {
    label: 'الأعلاف والتسمين',
    icon: ShoppingBag,
    pills: [
      { text: 'أسعار الأعلاف اليوم', icon: BarChart3 },
      { text: 'علف 21% بروتين', icon: Zap },
      { text: 'مركزات تسمين 5%', icon: Star },
    ]
  },
  {
    label: 'الأسمدة والمبيدات',
    icon: ShieldCheck,
    pills: [
      { text: 'نترات نشادر 33.5%', icon: Zap },
      { text: 'يوريا 46% محبب', icon: Search },
      { text: 'مبيدات حشرية آمنة', icon: MessageCircle },
    ]
  },
  {
    label: 'عقود التوريد',
    icon: Building2,
    pills: [
      { text: 'طلب توريد للمزارع', icon: Headphones },
      { text: 'تسهيلات ائتمانية', icon: ShieldCheck },
      { text: 'شحن كميات كبيرة', icon: Search },
    ]
  },
];

const activeSuggestionGroup = ref(0);
const activePills = computed(() => suggestionGroups[activeSuggestionGroup.value].pills);

// ── Cart & Actions ──────────────────────────────────────────────────
const addedProductIds = ref(new Set());

const handleAddToCart = (product) => {
  const itemToAdd = {
    ...product,
    display_price: product.price_raw || product.price,
  };
  addToCart(itemToAdd);
  addedProductIds.value.add(product.id);
  setTimeout(() => {
    addedProductIds.value.delete(product.id);
  }, 2000);
  window.dispatchEvent(new CustomEvent('open-cart'));
};

const formatCurrency = (val) => {
  return Number(val).toLocaleString('ar-EG', { minimumFractionDigits: 0 });
};

// ── Utilities ──────────────────────────────────────────────────────
function nowTime() {
  return new Date().toLocaleTimeString('ar-EG', { hour: '2-digit', minute: '2-digit' });
}

const scrollToBottom = async () => {
  await nextTick();
  if (chatContainer.value) {
    chatContainer.value.scrollTo({ top: chatContainer.value.scrollHeight, behavior: 'smooth' });
  }
};

// ── Markdown-lite formatter ────────────────────────────────────────
const formatContent = (text) => {
  if (!text) return '';
  return text
    .replace(/\*\*(.*?)\*\*/g, '<strong class="text-brand dark:text-accent font-black">$1</strong>')
    .replace(/\*(.*?)\*/g, '<em class="text-gray-900 dark:text-white font-bold">$1</em>')
    .replace(/^• /gm, '<span class="inline-block w-1.5 h-1.5 rounded-full bg-brand dark:bg-accent mr-2 mb-0.5 flex-shrink-0"></span>')
    .replace(/\n/g, '<br/>');
};

// ── Send ───────────────────────────────────────────────────────────
let msgIdCounter = 1;

const sendMessage = async (text = null) => {
  const content = (text || inputMessage.value).trim();
  if (!content || loading.value) return;

  messages.value.push({ role: 'user', content, time: nowTime(), id: msgIdCounter++ });
  if (!text) inputMessage.value = '';
  loading.value = true;
  await scrollToBottom();
  inputRef.value?.focus();

  try {
    const { data } = await axios.post('/chatbot/message', { 
        message: content,
        context: chatContext.value
    });
    
    // Update context from server
    if (data.context) chatContext.value = data.context;

    messages.value.push({
      role: 'bot',
      content: data.reply || 'عذراً، لم أتمكن من معالجة الطلب حالياً.',
      time: nowTime(),
      id: msgIdCounter++,
      products: data.products || [],
      categories: data.categories || [],
      comparison: data.comparison || null,
      suggestions: data.suggestions || [],
      type: data.type || 'text',
    });
  } catch (err) {
    console.error(err);
    messages.value.push({
      role: 'bot',
      content: 'حدث خطأ أثناء الاتصال بالخادم. يرجى المحاولة مجدداً.',
      time: nowTime(),
      id: msgIdCounter++,
      isError: true,
    });
  } finally {
    loading.value = false;
    await scrollToBottom();
  }
};

const clearChat = () => {
  messages.value = [messages.value[0]];
  chatContext.value = [];
};

onMounted(() => {
  scrollToBottom();
  inputRef.value?.focus();
});
</script>

<template>
  <Head :title="`DawAI Smart Assistant | ${settings.store_name}`" />
  
  <StoreLayout>
    <div class="bg-gray-50 dark:bg-night-950 font-['Cairo'] transition-colors duration-500 overflow-hidden" dir="rtl">

      <!-- Premium Hero Header -->
      <div class="relative overflow-hidden bg-white dark:bg-night-900 border-b border-gray-100 dark:border-night-800 pt-16 pb-12 md:pt-24 md:pb-20">
        <div class="absolute inset-0 pointer-events-none">
          <div class="absolute -top-20 -right-20 w-80 h-80 bg-brand/10 rounded-full blur-[100px] animate-pulse"></div>
          <div class="absolute -bottom-20 -left-20 w-80 h-80 bg-accent/5 rounded-full blur-[100px]"></div>
          <div class="absolute inset-0 opacity-[0.03]" style="background-image:radial-gradient(circle,#04724D 1.5px,transparent 1.5px);background-size:32px 32px;"></div>
        </div>
        
        <div class="container mx-auto px-4 relative z-10">
          <div class="max-w-4xl mx-auto text-center">
            <div class="inline-flex items-center gap-2.5 bg-brand/8 dark:bg-accent/8 border border-brand/15 dark:border-accent/15 px-5 py-2 rounded-full mb-8 backdrop-blur-md animate-in slide-in-from-top-4 duration-700">
               <div class="w-1.5 h-1.5 bg-brand dark:bg-accent rounded-full animate-ping"></div>
               <span class="text-[10px] font-black text-brand dark:text-accent uppercase tracking-[0.3em]">مستشارك الزراعي الذكي</span>
            </div>
            
            <h1 class="text-4xl md:text-6xl font-black text-gray-900 dark:text-white mb-6 tracking-tighter leading-tight animate-in fade-in duration-1000">
              مرحباً بك في <span class="text-brand dark:text-accent">DawAI</span>
            </h1>
            <p class="text-lg text-gray-500 dark:text-gray-400 font-medium leading-relaxed max-w-2xl mx-auto">
              أول نظام ذكاء اصطناعي متخصص في دعم المزارع المصري. ابحث عن منتجاتك، قارن الأسعار، واطلب احتياجاتك في محادثة واحدة.
            </p>
          </div>
        </div>
      </div>

      <!-- Chat Interface -->
      <div class="container mx-auto px-4 -mt-10 md:-mt-16 pb-16 relative z-20">
        <div class="bg-white/90 dark:bg-night-900/95 backdrop-blur-2xl rounded-[2.5rem] shadow-[0_30px_100px_-20px_rgba(4,114,77,0.15)] dark:shadow-none border border-white dark:border-night-800 overflow-hidden flex flex-col transition-all duration-500 max-w-5xl mx-auto" style="height: 75vh; min-height: 600px;">

          <!-- Chat Topbar -->
          <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100/50 dark:border-night-800 bg-white/50 dark:bg-night-800/20 flex-shrink-0">
            <div class="flex items-center gap-4">
              <div class="relative">
                <div class="w-12 h-12 bg-gradient-to-br from-brand to-accent rounded-2xl flex items-center justify-center text-white shadow-xl shadow-brand/20 group hover:rotate-12 transition-transform">
                  <Bot class="w-6 h-6" />
                </div>
                <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-green-500 border-2 border-white dark:border-night-900 rounded-full shadow-lg pulse-green"></div>
              </div>
              <div>
                <h2 class="text-base font-black text-gray-900 dark:text-white leading-none mb-1">DawAI Smart Assistant</h2>
                <div class="flex items-center gap-2">
                   <div class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></div>
                   <p class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest">متاح الآن · B2B Support</p>
                </div>
              </div>
            </div>
            <div class="flex items-center gap-2">
              <button @click="clearChat" class="p-3 text-gray-400 dark:text-gray-600 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 rounded-2xl transition-all group" title="مسح المحادثة">
                <RefreshCw class="w-5 h-5 group-active:rotate-180 transition-transform duration-500" />
              </button>
            </div>
          </div>

          <!-- Messages Container -->
          <div ref="chatContainer" class="flex-grow overflow-y-auto px-4 md:px-8 py-8 space-y-8 chat-scrollbar scroll-smooth">

            <!-- Message Bubbles -->
            <div v-for="(msg, idx) in messages" :key="msg.id ?? idx"
              :class="['flex items-end gap-3 animate-message', msg.role === 'user' ? 'flex-row-reverse' : '']"
            >

              <!-- Avatar -->
              <div class="flex-shrink-0 mb-2">
                <div v-if="msg.role === 'bot'"
                  class="w-10 h-10 bg-white dark:bg-night-800 rounded-2xl flex items-center justify-center text-brand dark:text-accent shadow-sm border border-gray-100 dark:border-night-700">
                  <Bot class="w-5 h-5" />
                </div>
                <div v-else
                   class="w-10 h-10 bg-gray-900 dark:bg-brand rounded-2xl flex items-center justify-center text-white dark:text-night-950 font-black shadow-lg">
                   <span class="text-xs">أنت</span>
                </div>
              </div>

              <!-- Bubble Content -->
              <div :class="['max-w-[85%] md:max-w-[75%] space-y-2', msg.role === 'user' ? 'items-end' : 'items-start', 'flex flex-col']">

                <!-- Main Text Bubble -->
                <div :class="[
                  'px-6 py-5 relative overflow-hidden leading-relaxed text-sm md:text-base font-medium transition-all group bubble-shadow',
                  msg.role === 'bot'
                    ? 'bg-gray-50/80 dark:bg-night-800/80 text-gray-800 dark:text-gray-200 rounded-[2rem] rounded-bl-sm border border-gray-100 dark:border-night-700'
                    : msg.isError
                      ? 'bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 rounded-[2rem] rounded-br-sm border border-red-100 dark:border-red-500/20'
                      : 'bg-gray-900 dark:bg-brand text-white dark:text-night-950 rounded-[2rem] rounded-br-sm'
                ]">
                  <!-- Message text with mini markdown -->
                  <div class="relative z-10 whitespace-pre-wrap" v-html="formatContent(msg.content)"></div>

                  <!-- Enhanced Product Bento Cards -->
                  <div v-if="msg.products && msg.products.length > 0" class="mt-6 grid grid-cols-6 gap-3 md:gap-4 relative z-10">
                    <div v-for="(p, pIdx) in msg.products" :key="p.id"
                      :class="[
                        'group relative bg-white/40 dark:bg-night-950/40 backdrop-blur-md rounded-[2rem] p-4 flex flex-col border border-white dark:border-night-800 hover:border-brand/40 dark:hover:border-accent/40 hover:shadow-2xl transition-all duration-700 overflow-hidden',
                        // Bento spans: First item is large, others standard
                        pIdx === 0 ? 'col-span-6 sm:col-span-4 row-span-2' : 'col-span-3 sm:col-span-2'
                      ]"
                    >
                      <!-- Accent Gradient -->
                      <div class="absolute -top-10 -right-10 w-32 h-32 bg-brand/5 dark:bg-accent/5 rounded-full blur-3xl group-hover:scale-150 transition-transform duration-700"></div>

                      <div :class="['flex items-center mb-4', pIdx === 0 ? 'gap-6' : 'gap-3 flex-col sm:flex-row']">
                        <div :class="[
                          'bg-white dark:bg-night-900 rounded-2xl flex-shrink-0 overflow-hidden border border-gray-100 dark:border-night-800 transition-all duration-700 p-2',
                          pIdx === 0 ? 'w-24 h-24 hover:scale-105' : 'w-14 h-14'
                        ]">
                          <img :src="p.image || '/images/products/no-image.png'" class="w-full h-full object-contain" />
                        </div>
                        
                        <div class="min-w-0 flex-1">
                          <div v-if="p.is_featured && pIdx === 0" class="inline-flex items-center gap-1 bg-brand/10 dark:bg-accent/10 px-2 py-0.5 rounded-full mb-2">
                             <Sparkles class="w-2.5 h-2.5 text-brand dark:text-accent" />
                             <span class="text-[8px] font-black text-brand dark:text-accent uppercase">ترشيح ذكي</span>
                          </div>
                          <Link :href="`/product/${p.id}`" 
                            :class="['font-black text-gray-900 dark:text-white leading-tight hover:text-brand transition-colors block mb-1', 
                            pIdx === 0 ? 'text-lg' : 'text-[10px] truncate']"
                          >
                            {{ p.name }}
                          </Link>
                          <div class="flex items-center gap-2">
                             <span :class="['text-brand dark:text-accent font-black tabular-nums', pIdx === 0 ? 'text-xl' : 'text-xs']">{{ p.price }}</span>
                             <span v-if="p.in_stock" class="w-1 h-1 bg-green-500 rounded-full animate-pulse"></span>
                             <span v-if="p.in_stock" class="text-[9px] font-bold text-gray-400">متوفر</span>
                          </div>
                        </div>
                      </div>

                      <div v-if="pIdx === 0 && p.note" class="mb-4">
                        <p class="text-xs text-gray-500 dark:text-gray-400 line-clamp-2 leading-relaxed">{{ p.note }}</p>
                      </div>
                      
                      <div class="flex items-center gap-2 mt-auto">
                        <button 
                          @click="handleAddToCart(p)"
                          :class="[
                            'flex-grow h-11 rounded-2xl flex items-center justify-center gap-2 text-[10px] sm:text-xs font-black transition-all active:scale-90 px-4',
                            addedProductIds.has(p.id)
                              ? 'bg-green-500 text-white shadow-lg shadow-green-500/30' 
                              : 'bg-gray-900 dark:bg-brand text-white dark:text-night-950 hover:bg-brand dark:hover:bg-accent hover:shadow-xl hover:shadow-brand/20'
                          ]"
                        >
                          <Check v-if="addedProductIds.has(p.id)" class="w-4 h-4" />
                          <ShoppingCart v-else class="w-4 h-4" />
                          <span class="hidden xs:inline">{{ addedProductIds.has(p.id) ? 'تمت الإضافة' : 'أضف للسلة' }}</span>
                        </button>
                        <Link :href="`/product/${p.id}`" class="w-11 h-11 rounded-2xl bg-white dark:bg-night-800 border border-gray-100 dark:border-night-700 flex items-center justify-center text-gray-400 hover:text-brand hover:border-brand/40 transition-all flex-shrink-0 shadow-sm">
                          <Info class="w-5 h-5" />
                        </Link>
                      </div>
                    </div>
                  </div>

                  <!-- Category Chips in Message -->
                  <div v-if="msg.categories && msg.categories.length > 0" class="mt-6 flex flex-wrap gap-2.5 relative z-10">
                    <button v-for="cat in msg.categories" :key="cat.id" @click="sendMessage(cat.name)"
                      class="bg-white dark:bg-night-950 border border-gray-100 dark:border-night-800 px-4 py-2.5 rounded-2xl text-[10px] font-black text-gray-600 dark:text-gray-400 hover:bg-brand hover:text-white hover:border-brand dark:hover:bg-accent dark:hover:text-night-950 transition-all flex items-center gap-2 shadow-sm">
                      <Tag class="w-3.5 h-3.5 opacity-40 shrink-0" />
                      <span>{{ cat.name }}</span>
                    </button>
                  </div>

                  <!-- Dynamic Suggestions in Message -->
                  <div v-if="msg.suggestions && msg.suggestions.length > 0" class="mt-6 flex flex-wrap gap-2 relative z-10 border-t border-gray-100 dark:border-night-700/50 pt-5">
                    <button v-for="sug in msg.suggestions" :key="sug" @click="sendMessage(sug)"
                      :class="[
                        'text-[10px] font-black px-4 py-2 rounded-full transition-all active:scale-95 shadow-sm',
                        msg.role === 'user'
                          ? 'bg-white/10 text-white border border-white/20 hover:bg-white/20'
                          : 'bg-brand/10 dark:bg-accent/10 text-brand dark:text-accent border border-brand/20 dark:border-accent/20 hover:bg-brand hover:text-white dark:hover:bg-accent dark:hover:text-night-950'
                      ]">
                      {{ sug }}
                    </button>
                  </div>
                </div>

                <!-- Footer Stats -->
                <div :class="['flex items-center gap-3 px-2', msg.role === 'user' ? 'flex-row-reverse' : '']">
                  <p class="text-[10px] font-black text-gray-300 dark:text-gray-600 tracking-tighter uppercase tabular-nums">
                    {{ msg.time }}
                  </p>
                  <div v-if="msg.role === 'bot' && !msg.isError" class="flex gap-1">
                     <button class="p-1 text-gray-300 hover:text-brand transition-colors"><ThumbsUp class="w-3 h-3" /></button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Typing Indicator -->
            <div v-if="loading" class="flex items-end gap-3 animate-in fade-in duration-500">
              <div class="w-10 h-10 bg-white dark:bg-night-800 rounded-2xl flex items-center justify-center text-brand dark:text-accent border border-gray-100 dark:border-night-700 shadow-sm">
                <Bot class="w-5 h-5" />
              </div>
              <div class="bg-gray-50 dark:bg-night-800 rounded-[2rem] rounded-bl-sm px-6 py-5 border border-gray-100 dark:border-night-700 flex items-center gap-4">
                <div class="flex items-center gap-1.5 px-1">
                  <div class="w-1.5 h-1.5 bg-brand dark:bg-accent rounded-full animation-typing-dot-1"></div>
                  <div class="w-1.5 h-1.5 bg-brand dark:bg-accent rounded-full animation-typing-dot-2"></div>
                  <div class="w-1.5 h-1.5 bg-brand dark:bg-accent rounded-full animation-typing-dot-3"></div>
                </div>
                <span class="text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest">تحليل الطلب...</span>
              </div>
            </div>
          </div>

          <!-- Interaction Area -->
          <div class="border-t border-gray-100 dark:border-night-800 bg-white/50 dark:bg-night-900/50 backdrop-blur-md flex-shrink-0 transition-all duration-500">

            <!-- Suggestion Engine -->
            <div class="px-4 md:px-8 pt-4 pb-2">
              <div class="flex flex-col gap-3">
                <!-- Tab Controls -->
                <div class="flex items-center gap-1 bg-gray-50 dark:bg-night-800/80 rounded-2xl p-1.5 border border-gray-100 dark:border-night-800 self-start">
                  <button v-for="(group, i) in suggestionGroups" :key="i" @click="activeSuggestionGroup = i"
                    :class="['flex items-center gap-2 px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all',
                      activeSuggestionGroup === i
                        ? 'bg-white dark:bg-night-700 text-brand dark:text-accent shadow-xl shadow-brand/10 ring-1 ring-gray-100 dark:ring-night-600'
                        : 'text-gray-400 dark:text-gray-500 hover:text-gray-700 dark:hover:text-gray-300']">
                    <component :is="group.icon" class="w-3.5 h-3.5" />
                    <span class="hidden sm:inline">{{ group.label }}</span>
                  </button>
                </div>

                <!-- Chips Window -->
                <div class="flex items-center gap-2.5 overflow-x-auto no-scrollbar pb-1">
                  <button v-for="pill in activePills" :key="pill.text"
                    @click="sendMessage(pill.text)"
                    :disabled="loading"
                    class="flex items-center gap-2 bg-white dark:bg-night-800 hover:bg-brand/5 dark:hover:bg-accent/5 hover:text-brand dark:hover:text-accent border border-gray-200 dark:border-night-700 hover:border-brand/30 dark:hover:border-accent/30 text-gray-500 dark:text-gray-400 px-4 py-2.5 rounded-2xl text-xs font-bold whitespace-nowrap transition-all active:scale-95 flex-shrink-0 disabled:opacity-40 disabled:cursor-not-allowed shadow-sm"
                  >
                    <component :is="pill.icon" class="w-3.5 h-3.5 opacity-60" />
                    <span>{{ pill.text }}</span>
                  </button>
                </div>
              </div>
            </div>

            <!-- Composer Area -->
            <div class="px-4 md:px-8 pb-6 pt-2">
              <div class="relative group/composer">
                <div class="absolute inset-0 bg-brand/3 dark:bg-accent/3 rounded-[2rem] blur-2xl opacity-0 group-focus-within/composer:opacity-100 transition-opacity"></div>
                
                <div class="relative bg-gray-50 dark:bg-night-800/80 border-2 border-gray-100 dark:border-night-750 focus-within:border-brand dark:focus-within:border-accent rounded-[2rem] transition-all duration-500 p-2 flex items-end gap-2">
                   
                  <div class="flex-shrink-0 p-2 text-brand dark:text-accent opacity-40">
                    <Sparkles class="w-5 h-5" />
                  </div>

                  <textarea
                    ref="inputRef"
                    v-model="inputMessage"
                    @keydown.enter.exact.prevent="sendMessage()"
                    @keydown.enter.shift.exact="inputMessage += '\n'"
                    rows="1"
                    maxlength="1000"
                    placeholder="اسألني عن أي منتج، سعر، أو خدمة... (Enter للإرسال)"
                    class="flex-grow bg-transparent border-none focus:ring-0 py-3 text-sm md:text-base font-bold text-gray-900 dark:text-white placeholder-gray-300 dark:placeholder-gray-700 leading-relaxed resize-none overflow-hidden"
                    style="min-height:48px; max-height:150px;"
                    @input="$event.target.style.height = 'auto'; $event.target.style.height = Math.min($event.target.scrollHeight, 150) + 'px'"
                  ></textarea>

                  <button
                    @click="sendMessage()"
                    :disabled="loading || !inputMessage.trim()"
                    class="h-12 w-12 bg-gray-900 dark:bg-brand text-white dark:text-night-950 rounded-[1.5rem] flex items-center justify-center shadow-xl shadow-gray-950/20 hover:scale-105 active:scale-95 transition-all disabled:opacity-20 disabled:grayscale disabled:cursor-not-allowed group/send"
                  >
                    <Send class="w-5 h-5 rotate-180 group-hover:-translate-y-0.5 group-hover:translate-x-0.5 transition-transform" />
                  </button>
                </div>

                <!-- Footer Tip -->
                <div class="flex items-center justify-between mt-3 px-3">
                  <div class="flex items-center gap-4 text-[10px] font-black text-gray-300 dark:text-gray-600 uppercase tracking-widest">
                    <span class="flex items-center gap-1"><ShieldCheck class="w-3 h-3" /> مشفر بالكامل</span>
                    <span class="flex items-center gap-1"><Info class="w-3 h-3" /> Shift+Enter لسطر جديد</span>
                  </div>
                  <span class="text-[10px] font-black text-gray-300 dark:text-gray-600 tabular-nums">{{ charCount }}/1000</span>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>

    </div>
  </StoreLayout>
</template>

<style scoped>
.chat-scrollbar::-webkit-scrollbar { width: 5px; }
.chat-scrollbar::-webkit-scrollbar-track { background: transparent; }
.chat-scrollbar::-webkit-scrollbar-thumb { background: rgba(4,114,77,0.1); border-radius: 20px; }
.dark .chat-scrollbar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.05); }

.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

.animate-message {
  animation: message-in 0.6s cubic-bezier(0.16, 1, 0.3, 1) both;
}

@keyframes message-in {
  from { opacity: 0; transform: translateY(20px) scale(0.98); }
  to { opacity: 1; transform: translateY(0) scale(1); }
}

.bubble-shadow {
  box-shadow: 0 10px 40px -10px rgba(0,0,0,0.02);
}
.dark .bubble-shadow {
  box-shadow: 0 10px 40px -10px rgba(0,0,0,0.2);
}

.animation-typing-dot-1 { animation: typing 1.4s infinite 0ms; }
.animation-typing-dot-2 { animation: typing 1.4s infinite 200ms; }
.animation-typing-dot-3 { animation: typing 1.4s infinite 400ms; }

@keyframes typing {
  0%, 100% { opacity: 0.3; transform: scale(1); }
  50% { opacity: 1; transform: scale(1.3); }
}

.pulse-green {
  animation: pulse-green 2s infinite;
}
@keyframes pulse-green {
  0% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.4); }
  70% { box-shadow: 0 0 0 8px rgba(34, 197, 94, 0); }
  100% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); }
}
</style>
