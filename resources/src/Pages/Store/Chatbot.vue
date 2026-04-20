<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted, nextTick } from 'vue';
import axios from 'axios';
import StoreLayout from '@/Layouts/StoreLayout.vue';
import {
  Bot, Send, Sparkles, ArrowLeft, Zap, ShieldCheck,
  ShoppingBag, BarChart3, Loader2, Trash2, Search,
  Package, Tag, Star, ChevronRight, MessageCircle,
  ThumbsUp, Building2, Headphones, RefreshCw, X
} from 'lucide-vue-next';

const props = defineProps({ s: Object, categories: Array, brands: Array });

// ── State ─────────────────────────────────────────────────────────────
const inputMessage = ref('');
const loading = ref(false);
const chatContainer = ref(null);
const inputRef = ref(null);
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
    .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
    .replace(/\*(.*?)\*/g, '<em>$1</em>')
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
    const { data } = await axios.post('/chatbot/message', { message: content });
    messages.value.push({
      role: 'bot',
      content: data.reply || 'عذراً، لم أتمكن من معالجة الطلب حالياً.',
      time: nowTime(),
      id: msgIdCounter++,
      products: data.products || [],
      categories: data.categories || [],
      suggestions: data.suggestions || [],
      type: data.type || 'text',
    });
  } catch {
    messages.value.push({
      role: 'bot',
      content: 'حدث خطأ أثناء الاتصال. يرجى المحاولة مجدداً.',
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
};

onMounted(() => {
  scrollToBottom();
  inputRef.value?.focus();
});
</script>

<template>
  <StoreLayout>
    <div class="bg-gray-50 dark:bg-night-950 font-['Cairo'] transition-colors duration-500" dir="rtl">

      <!-- Compact Hero Header -->
      <div class="relative overflow-hidden bg-white dark:bg-night-900 border-b border-gray-100 dark:border-night-800 py-10 md:py-14">
        <div class="absolute inset-0 pointer-events-none">
          <div class="absolute -top-20 -right-20 w-80 h-80 bg-brand/5 rounded-full blur-[80px]"></div>
          <div class="absolute bottom-0 left-0 w-60 h-60 bg-accent/5 rounded-full blur-[60px]"></div>
          <div class="absolute inset-0 opacity-[0.025]" style="background-image:radial-gradient(circle,#04724D 1px,transparent 1px);background-size:28px 28px;"></div>
        </div>
        <div class="container mx-auto px-4 relative z-10">
          <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 max-w-5xl mx-auto">
            <div>
              <div class="flex items-center gap-2 text-brand dark:text-accent text-[10px] font-black uppercase tracking-[0.3em] mb-3">
                <div class="w-8 h-[2px] bg-brand dark:bg-accent"></div>
                <span>مساعد ذكي</span>
              </div>
              <h1 class="text-2xl md:text-4xl font-black text-gray-900 dark:text-white tracking-tighter leading-none mb-3">
                <span class="text-brand dark:text-accent">DawAI</span> — مستشارك الذكي
              </h1>
              <p class="text-sm text-gray-400 dark:text-gray-500 font-medium leading-relaxed max-w-lg">
                ابحث عن منتجاتنا، قارن الأسعار، تحقق من المخزون، واستفسر عن طلبات الجملة — كل ذلك في محادثة واحدة.
              </p>
            </div>
            <div class="flex items-center gap-3 flex-shrink-0">
              <div class="bg-green-50 dark:bg-green-500/10 border border-green-200 dark:border-green-500/20 px-5 py-3 rounded-2xl flex items-center gap-3">
                <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse shadow-lg shadow-green-500/50"></div>
                <div>
                  <p class="text-[9px] font-black text-green-600 dark:text-green-400 uppercase tracking-widest leading-none mb-0.5">حالة النظام</p>
                  <p class="text-xs font-black text-green-700 dark:text-green-300 leading-none">متصل ومتاح</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Chat Interface -->
      <div class="container mx-auto px-4 py-8 max-w-5xl">
        <div class="bg-white dark:bg-night-900 rounded-[2.5rem] shadow-2xl shadow-gray-200/40 dark:shadow-none border border-gray-100 dark:border-night-800 overflow-hidden flex flex-col transition-colors duration-500" style="height: 78vh; min-height: 500px;">

          <!-- Chat Topbar -->
          <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-night-800 bg-gray-50/50 dark:bg-night-800/30 flex-shrink-0">
            <div class="flex items-center gap-3">
              <div class="relative">
                <div class="w-10 h-10 bg-gradient-to-br from-brand to-accent rounded-xl flex items-center justify-center text-white shadow-lg shadow-brand/20">
                  <Bot class="w-5 h-5" />
                </div>
                <div class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-green-500 border-2 border-white dark:border-night-900 rounded-full"></div>
              </div>
              <div>
                <h2 class="text-sm font-black text-gray-900 dark:text-white leading-none mb-0.5">DawAI Agri-Assistant</h2>
                <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest flex items-center gap-1.5">
                  <Zap class="w-2.5 h-2.5 text-brand" />
                  استجابة فورية · مدعوم بـ AI
                </p>
              </div>
            </div>
            <div class="flex items-center gap-2">
              <button @click="clearChat" class="p-2 text-gray-300 dark:text-gray-600 hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 rounded-xl transition-all" title="مسح المحادثة">
                <RefreshCw class="w-4 h-4" />
              </button>
            </div>
          </div>

          <!-- Messages Container -->
          <div ref="chatContainer" class="flex-grow overflow-y-auto px-4 md:px-6 py-6 space-y-5 chat-scrollbar">

            <!-- Message Bubbles -->
            <div v-for="(msg, idx) in messages" :key="msg.id ?? idx"
              :class="['flex items-end gap-3 animate-in fade-in slide-in-from-bottom-3 duration-400', msg.role === 'user' ? 'flex-row-reverse' : '']"
            >

              <!-- Avatar -->
              <div class="flex-shrink-0 mb-1">
                <div v-if="msg.role === 'bot'"
                  class="w-8 h-8 bg-gradient-to-br from-brand to-accent rounded-xl flex items-center justify-center text-white shadow-md shadow-brand/20 flex-shrink-0">
                  <Bot class="w-4 h-4" />
                </div>
                <div v-else
                  class="w-8 h-8 bg-gray-900 dark:bg-night-700 rounded-xl flex items-center justify-center text-white dark:text-gray-200 flex-shrink-0 shadow">
                  <span class="text-[10px] font-black">أنت</span>
                </div>
              </div>

              <!-- Bubble Content -->
              <div :class="['max-w-[78%] md:max-w-[72%] space-y-2', msg.role === 'user' ? 'items-end' : 'items-start', 'flex flex-col']">

                <!-- Main Text Bubble -->
                <div :class="[
                  'px-5 py-4 relative overflow-hidden leading-relaxed text-sm font-medium break-words',
                  msg.role === 'bot'
                    ? 'bg-gray-50 dark:bg-night-800 text-gray-800 dark:text-gray-200 rounded-2xl rounded-bl-sm border border-gray-100 dark:border-night-700'
                    : msg.isError
                      ? 'bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 rounded-2xl rounded-br-sm border border-red-100 dark:border-red-500/20'
                      : 'bg-gray-900 dark:bg-brand text-white dark:text-night-950 rounded-2xl rounded-br-sm shadow-lg shadow-gray-900/10'
                ]">
                  <!-- Subtle bot glow -->
                  <div v-if="msg.role === 'bot'" class="absolute top-0 right-0 w-20 h-20 bg-brand/5 rounded-full blur-2xl pointer-events-none -translate-y-1/2 translate-x-1/2"></div>

                  <!-- Message text with mini markdown -->
                  <div class="relative z-10 whitespace-pre-wrap" v-html="formatContent(msg.content)"></div>

                  <!-- Product Cards in Message -->
                  <div v-if="msg.products && msg.products.length > 0" class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-3 relative z-10">
                    <Link v-for="p in msg.products" :key="p.id" :href="`/product/${p.id}`"
                      class="group bg-white dark:bg-night-900 rounded-2xl p-3 flex items-center gap-3 border border-gray-100 dark:border-night-700 hover:border-brand dark:hover:border-accent hover:shadow-lg transition-all duration-300">
                      <div class="w-14 h-14 bg-gray-50 dark:bg-night-800 rounded-xl p-2 flex-shrink-0 overflow-hidden border border-gray-100 dark:border-night-700 group-hover:scale-105 transition-transform duration-500">
                        <img :src="p.image || '/images/products/no-image.png'" class="w-full h-full object-contain" onerror="this.src='/images/products/no-image.png'" />
                      </div>
                      <div class="min-w-0 flex-1">
                        <p class="font-black text-gray-900 dark:text-white text-xs truncate group-hover:text-brand dark:group-hover:text-accent transition-colors leading-tight mb-1">{{ p.name }}</p>
                        <div class="flex items-center justify-between gap-2">
                          <span class="text-brand dark:text-accent font-black text-xs tabular-nums">{{ p.price }}</span>
                          <span v-if="p.stock_label"
                            :class="['text-[9px] font-black px-2 py-0.5 rounded-full',
                              p.in_stock ? 'bg-green-50 dark:bg-green-500/10 text-green-600 dark:text-green-400 border border-green-200 dark:border-green-500/20'
                                         : 'bg-red-50 dark:bg-red-500/10 text-red-500 dark:text-red-400 border border-red-100 dark:border-red-500/20'
                            ]">
                            {{ p.stock_label }}
                          </span>
                        </div>
                      </div>
                      <ChevronRight class="w-3.5 h-3.5 text-gray-300 group-hover:text-brand transition-colors rotate-180 flex-shrink-0" />
                    </Link>
                  </div>

                  <!-- Category Chips in Message -->
                  <div v-if="msg.categories && msg.categories.length > 0" class="mt-4 flex flex-wrap gap-2 relative z-10">
                    <button v-for="cat in msg.categories" :key="cat.id" @click="sendMessage(cat.name)"
                      class="bg-white dark:bg-night-900 border border-gray-200 dark:border-night-700 px-3 py-2 rounded-xl text-[10px] font-black text-gray-600 dark:text-gray-400 hover:bg-brand hover:text-white hover:border-brand dark:hover:bg-accent dark:hover:text-night-950 dark:hover:border-accent transition-all flex items-center gap-1.5">
                      <Tag class="w-3 h-3 opacity-60" />
                      {{ cat.name }}
                      <span class="opacity-50 text-[9px]">({{ cat.product_count }})</span>
                    </button>
                  </div>

                  <!-- Dynamic Suggestions in Message -->
                  <div v-if="msg.suggestions && msg.suggestions.length > 0" class="mt-4 flex flex-wrap gap-2 relative z-10 border-t border-gray-100 dark:border-night-700 pt-3">
                    <button v-for="sug in msg.suggestions" :key="sug" @click="sendMessage(sug)"
                      :class="[
                        'text-[10px] font-black px-3 py-1.5 rounded-full transition-all active:scale-95',
                        msg.role === 'user'
                          ? 'bg-white/15 text-white border border-white/20 hover:bg-white/25'
                          : 'bg-brand/8 dark:bg-accent/8 text-brand dark:text-accent border border-brand/15 dark:border-accent/15 hover:bg-brand hover:text-white hover:border-brand dark:hover:bg-accent dark:hover:text-night-950 dark:hover:border-accent'
                      ]">
                      {{ sug }}
                    </button>
                  </div>
                </div>

                <!-- Timestamp -->
                <p :class="['text-[9px] font-bold text-gray-300 dark:text-gray-700 uppercase tracking-wider px-1', msg.role === 'user' ? 'text-right' : 'text-left']">
                  {{ msg.time }}
                </p>
              </div>
            </div>

            <!-- Typing Indicator -->
            <div v-if="loading" class="flex items-end gap-3 animate-in fade-in duration-300">
              <div class="w-8 h-8 bg-gradient-to-br from-brand to-accent rounded-xl flex items-center justify-center text-white shadow-md flex-shrink-0">
                <Bot class="w-4 h-4" />
              </div>
              <div class="bg-gray-50 dark:bg-night-800 rounded-2xl rounded-bl-sm px-5 py-4 border border-gray-100 dark:border-night-700 flex items-center gap-2">
                <div class="flex items-center gap-1.5">
                  <span class="w-2 h-2 bg-brand rounded-full animate-bounce" style="animation-delay:0ms"></span>
                  <span class="w-2 h-2 bg-brand rounded-full animate-bounce" style="animation-delay:150ms"></span>
                  <span class="w-2 h-2 bg-brand rounded-full animate-bounce" style="animation-delay:300ms"></span>
                </div>
                <span class="text-xs font-bold text-gray-400 dark:text-gray-500">يكتب...</span>
              </div>
            </div>
          </div>

          <!-- Input Area -->
          <div class="border-t border-gray-100 dark:border-night-800 bg-white dark:bg-night-900 flex-shrink-0 transition-colors duration-500">

            <!-- Suggestion Tabs -->
            <div class="px-4 md:px-6 pt-3 pb-2">
              <div class="flex items-center gap-2 mb-2.5">
                <!-- Tab Buttons -->
                <div class="flex items-center gap-1 bg-gray-50 dark:bg-night-800 rounded-xl p-1 border border-gray-100 dark:border-night-700 flex-shrink-0">
                  <button v-for="(group, i) in suggestionGroups" :key="i" @click="activeSuggestionGroup = i"
                    :class="['flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[9px] font-black uppercase tracking-widest transition-all',
                      activeSuggestionGroup === i
                        ? 'bg-white dark:bg-night-700 text-brand dark:text-accent shadow-sm'
                        : 'text-gray-400 dark:text-gray-600 hover:text-gray-600 dark:hover:text-gray-400']">
                    <component :is="group.icon" class="w-3 h-3" />
                    <span class="hidden sm:inline">{{ group.label }}</span>
                  </button>
                </div>

                <!-- Pill Suggestions -->
                <div class="flex items-center gap-2 overflow-x-auto no-scrollbar flex-grow">
                  <button v-for="pill in activePills" :key="pill.text"
                    @click="sendMessage(pill.text)"
                    :disabled="loading"
                    class="flex items-center gap-1.5 bg-gray-50 dark:bg-night-800 hover:bg-brand/8 dark:hover:bg-accent/8 hover:text-brand dark:hover:text-accent border border-gray-100 dark:border-night-700 hover:border-brand/20 dark:hover:border-accent/20 text-gray-500 dark:text-gray-400 px-3 py-2 rounded-xl text-[10px] font-black whitespace-nowrap transition-all active:scale-95 flex-shrink-0 disabled:opacity-40 disabled:cursor-not-allowed">
                    <component :is="pill.icon" class="w-3 h-3 opacity-60 flex-shrink-0" />
                    {{ pill.text }}
                  </button>
                </div>
              </div>
            </div>

            <!-- Text Input -->
            <div class="px-4 md:px-6 pb-4">
              <div class="relative">
                <div class="absolute right-4 top-1/2 -translate-y-1/2 text-brand dark:text-accent pointer-events-none">
                  <Sparkles class="w-4 h-4 opacity-60" />
                </div>

                <textarea
                  ref="inputRef"
                  v-model="inputMessage"
                  @keydown.enter.exact.prevent="sendMessage()"
                  @keydown.enter.shift.exact="inputMessage += '\n'"
                  rows="1"
                  maxlength="1000"
                  placeholder="اسألني عن أي منتج، سعر، أو خدمة... (Enter للإرسال)"
                  class="w-full bg-gray-50 dark:bg-night-800 border border-gray-200 dark:border-night-700 rounded-2xl pr-10 pl-24 py-4 text-sm font-medium resize-none focus:ring-2 focus:ring-brand/15 focus:border-brand dark:focus:border-brand text-gray-900 dark:text-white placeholder-gray-300 dark:placeholder-gray-700 transition-all leading-relaxed overflow-hidden"
                  style="min-height:52px; max-height:120px;"
                  @input="$event.target.style.height = 'auto'; $event.target.style.height = Math.min($event.target.scrollHeight, 120) + 'px'"
                ></textarea>

                <div class="absolute left-2 top-1/2 -translate-y-1/2 flex items-center gap-1.5">
                  <span class="text-[9px] font-bold text-gray-300 dark:text-gray-700 tabular-nums hidden md:block">{{ charCount }}/1000</span>
                  <button
                    @click="sendMessage()"
                    :disabled="loading || !inputMessage.trim()"
                    class="h-10 w-10 bg-gray-900 dark:bg-brand text-white dark:text-night-950 rounded-xl flex items-center justify-center shadow-lg shadow-gray-900/10 hover:bg-brand dark:hover:bg-accent hover:scale-105 active:scale-95 transition-all disabled:opacity-30 disabled:cursor-not-allowed disabled:scale-100 overflow-hidden relative group/btn"
                  >
                    <div class="absolute inset-0 bg-white/10 -translate-x-full group-hover/btn:translate-x-full transition-transform duration-700"></div>
                    <Send class="w-4 h-4 rotate-180 relative z-10" />
                  </button>
                </div>
              </div>

              <!-- Footer Notice -->
              <div class="flex items-center justify-between mt-2 px-1">
                <p class="text-[9px] font-bold text-gray-300 dark:text-gray-700 flex items-center gap-1.5">
                  <ShieldCheck class="w-3 h-3 text-brand/40" />
                  محادثة آمنة ومشفرة · لا يتم حفظ بياناتك
                </p>
                <p class="text-[9px] font-bold text-gray-300 dark:text-gray-700 hidden sm:block">
                  Shift+Enter لسطر جديد
                </p>
              </div>
            </div>
          </div>

        </div>
      </div>

      <!-- Bottom Features Strip -->
      <div class="container mx-auto px-4 pb-16 max-w-5xl">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <div v-for="feat in [
            { icon: Search, label: 'بحث ذكي', sub: 'عن أي منتج' },
            { icon: BarChart3, label: 'مقارنة الأسعار', sub: 'بدقة عالية' },
            { icon: Package, label: 'حالة المخزون', sub: 'مباشرة' },
            { icon: Building2, label: 'أسعار الجملة', sub: 'للمؤسسات' },
          ]" :key="feat.label"
            class="bg-white dark:bg-night-900 border border-gray-100 dark:border-night-800 rounded-2xl p-4 flex items-center gap-3 hover:border-brand/20 dark:hover:border-accent/20 transition-all group">
            <div class="w-9 h-9 rounded-xl bg-brand/8 dark:bg-accent/8 flex items-center justify-center text-brand dark:text-accent flex-shrink-0 group-hover:scale-110 transition-transform">
              <component :is="feat.icon" class="w-4.5 h-4.5" />
            </div>
            <div>
              <p class="text-xs font-black text-gray-900 dark:text-white leading-none mb-0.5">{{ feat.label }}</p>
              <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wide">{{ feat.sub }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </StoreLayout>
</template>

<style scoped>
.chat-scrollbar::-webkit-scrollbar { width: 4px; }
.chat-scrollbar::-webkit-scrollbar-track { background: transparent; }
.chat-scrollbar::-webkit-scrollbar-thumb { background: rgba(4,114,77,0.12); border-radius: 20px; }
.dark .chat-scrollbar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.05); }

.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

.animate-in {
  animation-duration: 0.4s;
  animation-timing-function: cubic-bezier(0.16, 1, 0.3, 1);
  animation-fill-mode: both;
}
@keyframes fade-in { from { opacity: 0 } to { opacity: 1 } }
@keyframes slide-in-from-bottom-3 { from { transform: translateY(12px) } to { transform: translateY(0) } }
.fade-in { animation-name: fade-in; }
.slide-in-from-bottom-3 { animation-name: slide-in-from-bottom-3; }
</style>
