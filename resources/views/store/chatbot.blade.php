@extends('layouts.store')

@section('content')
<style>
  .chat-container {
    max-width: 900px;
    margin: 0 auto;
    height: calc(100vh - 220px);
    min-height: 500px;
    display: flex;
    flex-direction: column;
    background: #fff;
    border-radius: 1rem;
    box-shadow: 0 4px 24px rgba(0,0,0,.08);
    overflow: hidden;
  }
  .chat-header {
    background: linear-gradient(135deg, var(--brand), var(--brand-2));
    color: #fff;
    padding: 1.25rem 1.5rem;
    display: flex;
    align-items: center;
    gap: .75rem;
  }
  .chat-header .bot-avatar {
    width: 44px; height: 44px;
    background: rgba(255,255,255,.2);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.4rem;
  }
  .chat-header h5 { margin: 0; font-weight: 600; }
  .chat-header small { opacity: .85; }
  .chat-status { display: flex; align-items: center; gap: .35rem; }
  .chat-status .dot { width: 8px; height: 8px; background: #4ade80; border-radius: 50%; }

  .chat-messages {
    flex: 1;
    overflow-y: auto;
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    gap: 1rem;
    background: #f8f9fc;
  }
  .chat-messages::-webkit-scrollbar { width: 6px; }
  .chat-messages::-webkit-scrollbar-thumb { background: #ccc; border-radius: 3px; }

  .msg { max-width: 85%; animation: fadeInUp .3s ease; }
  .msg-bot { align-self: flex-start; }
  .msg-user { align-self: flex-end; }

  .msg-bubble {
    padding: .85rem 1.1rem;
    border-radius: 1rem;
    font-size: .925rem;
    line-height: 1.5;
    word-wrap: break-word;
  }
  .msg-bot .msg-bubble {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-bottom-left-radius: .25rem;
    color: #1f2937;
  }
  .msg-user .msg-bubble {
    background: var(--brand);
    color: #fff;
    border-bottom-right-radius: .25rem;
  }
  .msg-time {
    font-size: .7rem;
    color: #9ca3af;
    margin-top: .25rem;
    padding: 0 .5rem;
  }
  .msg-user .msg-time { text-align: right; }

  /* Product cards in chat */
  .chat-products {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: .75rem;
    margin-top: .5rem;
  }
  .chat-product-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: .75rem;
    overflow: hidden;
    transition: box-shadow .2s, transform .2s;
    cursor: pointer;
  }
  .chat-product-card:hover {
    box-shadow: 0 4px 16px rgba(0,0,0,.1);
    transform: translateY(-2px);
  }
  .chat-product-card img {
    width: 100%; height: 120px;
    object-fit: cover;
    background: #f3f4f6;
  }
  .chat-product-card .card-body {
    padding: .6rem .75rem;
  }
  .chat-product-card .p-name {
    font-weight: 600; font-size: .85rem;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
  }
  .chat-product-card .p-price {
    color: var(--brand); font-weight: 700; font-size: .9rem;
  }
  .chat-product-card .p-old-price {
    text-decoration: line-through; color: #9ca3af; font-size: .78rem; margin-left: .3rem;
  }
  .chat-product-card .p-meta {
    font-size: .75rem; color: #6b7280; margin-top: .15rem;
  }
  .chat-product-card .stock-badge {
    font-size: .7rem; padding: .15rem .45rem; border-radius: .25rem;
  }
  .chat-product-card .stock-in { background: #dcfce7; color: #166534; }
  .chat-product-card .stock-out { background: #fee2e2; color: #991b1b; }

  /* Category chips */
  .chat-categories {
    display: flex; flex-wrap: wrap; gap: .5rem; margin-top: .5rem;
  }
  .chat-cat-chip {
    padding: .4rem .9rem;
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 2rem;
    font-size: .82rem;
    cursor: pointer;
    transition: all .2s;
    display: flex; align-items: center; gap: .35rem;
  }
  .chat-cat-chip:hover { background: var(--brand); color: #fff; border-color: var(--brand); }
  .chat-cat-chip .count { background: #f3f4f6; padding: .1rem .4rem; border-radius: 1rem; font-size: .72rem; }
  .chat-cat-chip:hover .count { background: rgba(255,255,255,.25); color: #fff; }

  /* Comparison table */
  .chat-compare-table {
    width: 100%; font-size: .85rem; margin-top: .5rem;
    border-collapse: separate; border-spacing: 0;
    border: 1px solid #e5e7eb; border-radius: .5rem; overflow: hidden;
  }
  .chat-compare-table th {
    background: #f9fafb; padding: .5rem .75rem; text-align: left;
    font-weight: 600; border-bottom: 1px solid #e5e7eb;
  }
  .chat-compare-table td {
    padding: .5rem .75rem; border-bottom: 1px solid #f3f4f6;
  }
  .chat-compare-table tr:last-child td { border-bottom: none; }
  .our-row { background: #f0fdf4; }
  .comp-row { background: #fff; }

  /* Suggestion chips */
  .suggestions {
    display: flex; flex-wrap: wrap; gap: .4rem; margin-top: .65rem;
  }
  .suggestion-chip {
    padding: .35rem .75rem;
    background: #f3f4f6;
    border: 1px solid #e5e7eb;
    border-radius: 2rem;
    font-size: .8rem;
    cursor: pointer;
    transition: all .2s;
    white-space: nowrap;
  }
  .suggestion-chip:hover {
    background: var(--brand);
    color: #fff;
    border-color: var(--brand);
  }

  /* Stock info cards */
  .chat-stock-card {
    background: #fff; border: 1px solid #e5e7eb;
    border-radius: .75rem; padding: .75rem;
    display: flex; align-items: center; gap: .75rem;
    margin-top: .35rem;
  }
  .chat-stock-card img { width: 56px; height: 56px; object-fit: cover; border-radius: .5rem; }
  .chat-stock-card .info { flex: 1; }
  .chat-stock-card .s-name { font-weight: 600; font-size: .85rem; }
  .chat-stock-card .s-price { color: var(--brand); font-weight: 600; font-size: .85rem; }

  /* Input area */
  .chat-input-area {
    padding: 1rem 1.25rem;
    background: #fff;
    border-top: 1px solid #e5e7eb;
    display: flex;
    gap: .75rem;
    align-items: center;
  }
  .chat-input-area input {
    flex: 1;
    border: 1px solid #e5e7eb;
    border-radius: 2rem;
    padding: .65rem 1.25rem;
    font-size: .92rem;
    outline: none;
    transition: border-color .2s;
  }
  .chat-input-area input:focus { border-color: var(--brand); }
  .chat-input-area .btn-send {
    width: 44px; height: 44px;
    border-radius: 50%;
    background: var(--brand);
    color: #fff;
    border: none;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.1rem;
    cursor: pointer;
    transition: opacity .2s;
  }
  .chat-input-area .btn-send:hover { opacity: .85; }
  .chat-input-area .btn-send:disabled { opacity: .5; cursor: not-allowed; }

  /* Typing indicator */
  .typing-indicator { display: flex; gap: 4px; padding: .5rem 0; }
  .typing-indicator span {
    width: 8px; height: 8px; background: #9ca3af; border-radius: 50%;
    animation: bounce .6s infinite alternate;
  }
  .typing-indicator span:nth-child(2) { animation-delay: .15s; }
  .typing-indicator span:nth-child(3) { animation-delay: .3s; }

  @keyframes bounce {
    from { transform: translateY(0); }
    to { transform: translateY(-6px); }
  }
  @keyframes fadeInUp {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
  }

  /* Markdown in bot messages */
  .msg-bot .msg-bubble strong { font-weight: 700; }
  .msg-bot .msg-bubble ul, .msg-bot .msg-bubble ol { padding-left: 1.2rem; margin: .3rem 0; }
  .msg-bot .msg-bubble li { margin-bottom: .15rem; }

  /* RTL overrides for Arabic */
  [dir="rtl"] .chat-header { direction: rtl; }
  [dir="rtl"] .chat-input-area { direction: rtl; }
  [dir="rtl"] .chat-input-area input { text-align: right; }
  [dir="rtl"] .msg-bot .msg-bubble { border-bottom-left-radius: 1rem; border-bottom-right-radius: .25rem; text-align: right; direction: rtl; }
  [dir="rtl"] .msg-user .msg-bubble { border-bottom-right-radius: 1rem; border-bottom-left-radius: .25rem; text-align: right; direction: rtl; }
  [dir="rtl"] .msg-user .msg-time { text-align: left; }
  [dir="rtl"] .msg-bot .msg-bubble ul, [dir="rtl"] .msg-bot .msg-bubble ol { padding-left: 0; padding-right: 1.2rem; }
  [dir="rtl"] .chat-product-card .p-old-price { margin-left: 0; margin-right: .3rem; }
  [dir="rtl"] .chat-compare-table th { text-align: right; }
  [dir="rtl"] .suggestions { direction: rtl; }
  [dir="rtl"] .chat-categories { direction: rtl; }
</style>

<section class="py-4">
  <div class="container">

    <div class="chat-container" id="chatApp">
      {{-- Header --}}
      <div class="chat-header">
        <div class="bot-avatar"><i class="bi bi-robot"></i></div>
        <div>
          <h5>{{ __('messages.ChatbotTitle') }}</h5>
          <div class="chat-status">
            <span class="dot"></span>
            <small>{{ __('messages.ChatbotOnline') }}</small>
          </div>
        </div>
      </div>

      {{-- Messages --}}
      <div class="chat-messages" id="chatMessages"></div>

      {{-- Input --}}
      <div class="chat-input-area">
        <input type="text" id="chatInput" placeholder="{{ __('messages.ChatbotTypePlaceholder') }}" autocomplete="off" />
        <button class="btn-send" id="chatSend" title="{{ __('messages.ChatbotSend') }}">
          <i class="bi bi-send-fill"></i>
        </button>
      </div>
    </div>

  </div>
</section>

<script>
(function() {
  const CSRF = document.querySelector('meta[name="csrf-token"]').content;
  const BASE = '{{ url("/online_store/chatbot") }}';
  const NOIMG = '{{ asset("images/products/no-image.png") }}';
  const messagesEl = document.getElementById('chatMessages');
  const inputEl = document.getElementById('chatInput');
  const sendBtn = document.getElementById('chatSend');

  // Localized JS strings from Blade
  const LANG = {
    inStock: @json(__('messages.ChatbotInStock')),
    outOfStock: @json(__('messages.ChatbotOutOfStock')),
    ourStore: @json(__('messages.ChatbotOurStore')),
    source: @json(__('messages.ChatbotSource')),
    product: @json(__('messages.Product')),
    price: @json(__('messages.Price')),
    noComparisonData: @json(__('messages.ChatbotNoComparisonData')),
    error: @json(__('messages.ChatbotError')),
    help: @json(__('messages.ChatbotHelp')),
    showCategories: @json(__('messages.ChatbotShowCategories')),
    showProducts: @json(__('messages.ChatbotShowAllProducts')),
    catClickPrefix: @json(app()->getLocale() === 'ar' ? 'أرني منتجات' : 'Show me'),
  };

  let conversationContext = {};

  // ======== Init ========
  addBotMessage({
    type: 'text',
    message: @json(__('messages.ChatbotWelcome')),
    suggestions: [@json(__('messages.ChatbotShowCategories')), @json(__('messages.ChatbotRecommendProducts')), @json(__('messages.ChatbotWhatsInStock')), @json(__('messages.ChatbotHelp'))]
  });

  // ======== Events ========
  sendBtn.addEventListener('click', sendMessage);
  inputEl.addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); }
  });

  function sendMessage() {
    const text = inputEl.value.trim();
    if (!text) return;
    inputEl.value = '';
    addUserMessage(text);
    fetchBotResponse(text);
  }

  // ======== Add Messages ========
  function addUserMessage(text) {
    const wrapper = document.createElement('div');
    wrapper.className = 'msg msg-user';
    wrapper.innerHTML = '<div class="msg-bubble">' + escapeHtml(text) + '</div>'
      + '<div class="msg-time">' + timeNow() + '</div>';
    messagesEl.appendChild(wrapper);
    scrollBottom();
  }

  function addBotMessage(data) {
    const wrapper = document.createElement('div');
    wrapper.className = 'msg msg-bot';

    let html = '<div class="msg-bubble">' + renderMarkdown(data.message || '') + '</div>';

    // Render products
    if (data.type === 'products' && data.products && data.products.length) {
      html += renderProductCards(data.products);
    }

    // Render stock info
    if (data.type === 'stock' && data.products && data.products.length) {
      html += renderStockCards(data.products);
    }

    // Render categories
    if (data.type === 'categories' && data.categories && data.categories.length) {
      html += renderCategoryChips(data.categories);
    }

    // Render comparison
    if (data.type === 'comparison' && data.comparison) {
      html += renderComparison(data.comparison);
    }

    // Render suggestions
    if (data.suggestions && data.suggestions.length) {
      html += renderSuggestions(data.suggestions);
    }

    html += '<div class="msg-time">' + timeNow() + '</div>';
    wrapper.innerHTML = html;
    messagesEl.appendChild(wrapper);

    // Wire up suggestion clicks
    wrapper.querySelectorAll('.suggestion-chip').forEach(function(chip) {
      chip.addEventListener('click', function() {
        var text = this.textContent.trim();
        inputEl.value = text;
        sendMessage();
      });
    });

    // Wire up category chip clicks
    wrapper.querySelectorAll('.chat-cat-chip').forEach(function(chip) {
      chip.addEventListener('click', function() {
        var catName = this.dataset.name;
        inputEl.value = LANG.catClickPrefix + ' ' + catName;
        sendMessage();
      });
    });

    scrollBottom();
  }

  function addTypingIndicator() {
    var el = document.createElement('div');
    el.className = 'msg msg-bot';
    el.id = 'typing-indicator';
    el.innerHTML = '<div class="msg-bubble"><div class="typing-indicator"><span></span><span></span><span></span></div></div>';
    messagesEl.appendChild(el);
    scrollBottom();
  }

  function removeTypingIndicator() {
    var el = document.getElementById('typing-indicator');
    if (el) el.remove();
  }

  // ======== Fetch ========
  function fetchBotResponse(text) {
    sendBtn.disabled = true;
    inputEl.disabled = true;
    addTypingIndicator();

    fetch(BASE + '/message', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': CSRF,
        'Accept': 'application/json',
      },
      body: JSON.stringify({
        message: text,
        context: conversationContext,
      })
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
      removeTypingIndicator();
      addBotMessage(data);
      sendBtn.disabled = false;
      inputEl.disabled = false;
      inputEl.focus();
    })
    .catch(function(err) {
      removeTypingIndicator();
      addBotMessage({
        type: 'text',
        message: LANG.error,
        suggestions: [LANG.help, LANG.showCategories]
      });
      sendBtn.disabled = false;
      inputEl.disabled = false;
      inputEl.focus();
      console.error('Chatbot error:', err);
    });
  }

  // ======== Renderers ========
  function renderProductCards(products) {
    var html = '<div class="chat-products">';
    products.forEach(function(p) {
      var stockClass = p.in_stock ? 'stock-in' : 'stock-out';
      var stockText = p.in_stock ? LANG.inStock : LANG.outOfStock;
      var oldPrice = (p.original_price && p.has_discount) ? '<span class="p-old-price">' + escapeHtml(p.original_price) + '</span>' : '';

      html += '<div class="chat-product-card">'
        + '<img src="' + escapeHtml(p.image || NOIMG) + '" alt="' + escapeHtml(p.name) + '" onerror="this.src=\'' + NOIMG + '\'">'
        + '<div class="card-body">'
        + '<div class="p-name" title="' + escapeHtml(p.name) + '">' + escapeHtml(p.name) + '</div>'
        + '<div><span class="p-price">' + escapeHtml(p.price) + '</span>' + oldPrice + '</div>'
        + '<div class="p-meta">' + escapeHtml(p.category || '') + (p.brand ? ' · ' + escapeHtml(p.brand) : '') + '</div>'
        + '<span class="stock-badge ' + stockClass + '">' + stockText + '</span>'
        + '</div></div>';
    });
    html += '</div>';
    return html;
  }

  function renderStockCards(products) {
    var html = '';
    products.forEach(function(p) {
      var stockClass = p.in_stock ? 'stock-in' : 'stock-out';
      var stockText = p.stock_label || (p.in_stock ? LANG.inStock : LANG.outOfStock);
      html += '<div class="chat-stock-card">'
        + '<img src="' + escapeHtml(p.image || NOIMG) + '" alt="' + escapeHtml(p.name) + '" onerror="this.src=\'' + NOIMG + '\'">'
        + '<div class="info">'
        + '<div class="s-name">' + escapeHtml(p.name) + '</div>'
        + '<div class="s-price">' + escapeHtml(p.price) + '</div>'
        + '<span class="stock-badge ' + stockClass + '">' + escapeHtml(stockText) + '</span>'
        + '</div></div>';
    });
    return html;
  }

  function renderCategoryChips(categories) {
    var html = '<div class="chat-categories">';
    categories.forEach(function(c) {
      html += '<div class="chat-cat-chip" data-id="' + c.id + '" data-name="' + escapeHtml(c.name) + '">'
        + '<i class="bi bi-tag"></i> ' + escapeHtml(c.name)
        + '<span class="count">' + (c.product_count || 0) + '</span>'
        + '</div>';
    });
    html += '</div>';
    return html;
  }

  function renderComparison(comp) {
    var html = '<table class="chat-compare-table">';
    html += '<thead><tr><th>' + escapeHtml(LANG.source) + '</th><th>' + escapeHtml(LANG.product) + '</th><th>' + escapeHtml(LANG.price) + '</th></tr></thead><tbody>';

    if (comp.our_products && comp.our_products.length) {
      comp.our_products.forEach(function(p) {
        var stockIcon = p.in_stock ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-x-circle-fill text-danger"></i>';
        html += '<tr class="our-row"><td><strong>' + escapeHtml(LANG.ourStore) + '</strong></td>'
          + '<td>' + escapeHtml(p.name) + ' ' + stockIcon + '</td>'
          + '<td><strong>' + escapeHtml(p.price) + '</strong></td></tr>';
      });
    }

    if (comp.competitors && comp.competitors.length) {
      comp.competitors.forEach(function(c) {
        html += '<tr class="comp-row"><td>' + escapeHtml(c.competitor_name) + '</td>'
          + '<td>' + escapeHtml(c.product_name) + '</td>'
          + '<td>' + escapeHtml(c.price) + '</td></tr>';
      });
    }

    if ((!comp.our_products || !comp.our_products.length) && (!comp.competitors || !comp.competitors.length)) {
      html += '<tr><td colspan="3" class="text-center text-muted">' + escapeHtml(LANG.noComparisonData) + '</td></tr>';
    }

    html += '</tbody></table>';
    return html;
  }

  function renderSuggestions(suggestions) {
    var html = '<div class="suggestions">';
    suggestions.forEach(function(s) {
      html += '<span class="suggestion-chip">' + escapeHtml(s) + '</span>';
    });
    html += '</div>';
    return html;
  }

  // ======== Helpers ========
  function escapeHtml(str) {
    if (!str) return '';
    var d = document.createElement('div');
    d.textContent = str;
    return d.innerHTML;
  }

  function renderMarkdown(text) {
    if (!text) return '';
    // Simple markdown: bold, lists, newlines
    var html = escapeHtml(text);
    html = html.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
    html = html.replace(/^- (.+)$/gm, '<li>$1</li>');
    html = html.replace(/(<li>.*<\/li>)/s, '<ul>$1</ul>');
    html = html.replace(/\n/g, '<br>');
    return html;
  }

  function timeNow() {
    var d = new Date();
    return d.getHours().toString().padStart(2, '0') + ':' + d.getMinutes().toString().padStart(2, '0');
  }

  function scrollBottom() {
    setTimeout(function() {
      messagesEl.scrollTop = messagesEl.scrollHeight;
    }, 50);
  }

})();
</script>
@endsection
