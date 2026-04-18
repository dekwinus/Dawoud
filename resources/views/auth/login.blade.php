<!DOCTYPE html>
<html lang="ar" dir="rtl">
  <head>
    <meta charset="utf-8" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="/css/master.css">
    <link rel="icon" href="{{ asset('images/' . ($app_settings->favicon ?? 'favicon.ico')) }}">
    <title>{{ $app_settings->app_name ?? 'DawPOS | Ultimate Inventory With POS' }} - تسجيل الدخول</title>
    <!-- Use Changa font for better Arabic typography -->
    <link href="https://fonts.googleapis.com/css2?family=Changa:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
      :root {
        color-scheme: light;
        font-family: 'Changa', 'Inter', system-ui, -apple-system, sans-serif;
        --surface: #ffffff;
        --primary: #04724D;
        --primary-gradient: linear-gradient(135deg, #04724D 0%, #035a3d 100%);
        --accent: #3EFF8B;
        --text: #1e293b;
        --text-muted: #64748b;
        --border: #e2e8f0;
        --bg-color: #f1f5f9;
      }

      *, *::before, *::after { box-sizing: border-box; }
      body {
        margin: 0;
        min-height: 100vh;
        background-color: var(--bg-color);
        color: var(--text);
        overflow-x: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem;
      }

      .auth-container {
        display: flex;
        width: 100%;
        max-width: 1100px;
        background: var(--surface);
        border-radius: 20px;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.01);
        overflow: hidden;
        border: 1px solid var(--border);
      }

      /* FORM SIDE */
      .auth-form-side {
        flex: 1;
        padding: clamp(2rem, 5vw, 4rem);
        display: flex;
        flex-direction: column;
        justify-content: center;
        background: white;
      }

      /* HERO SIDE */
      .auth-hero-side {
        flex: 1;
        background: var(--primary);
        position: relative;
        display: flex;
        flex-direction: column;
        justify-content: center;
        padding: clamp(2rem, 5vw, 4rem);
        color: white;
        /* Geometric crisp patterns instead of laggy blurs */
        background-image: 
          radial-gradient(circle at 10% 20%, rgba(62, 255, 139, 0.1) 0%, transparent 20%),
          radial-gradient(circle at 90% 80%, rgba(255, 255, 255, 0.05) 0%, transparent 20%);
      }

      .hero-text-wrapper {
        position: relative;
        z-index: 2;
        text-align: right;
      }

      .hero-title {
        font-size: clamp(2.2rem, 4vw, 3.5rem);
        font-weight: 800;
        margin: 0 0 1rem 0;
        line-height: 1.2;
        letter-spacing: -0.02em;
        color: var(--accent);
      }

      .hero-subtitle {
        font-size: clamp(1rem, 1.5vw, 1.15rem);
        color: rgba(255, 255, 255, 0.95);
        line-height: 1.7;
        margin: 0;
      }

      .auth-logo {
        max-width: 130px;
        margin-bottom: 2rem;
        display: block;
      }

      .panel-title {
        font-size: clamp(1.8rem, 3vw, 2.2rem);
        font-weight: 700;
        margin: 0 0 0.5rem 0;
      }

      .panel-subtitle {
        font-size: 1rem;
        color: var(--text-muted);
        margin: 0 0 2rem 0;
      }

      form { display: grid; gap: 1.25rem; }
      
      .field { display: grid; gap: 0.5rem; text-align: start; }
      
      .field label {
        font-weight: 600;
        font-size: 0.95rem;
        color: var(--text);
      }

      .input-shell {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        border: 1px solid var(--border);
        border-radius: 10px;
        padding: 0 1rem;
        background: #f8fafc;
        transition: border-color 0.2s ease, background-color 0.2s ease;
      }

      .input-shell:focus-within {
        border-color: var(--primary);
        background: #ffffff;
      }

      .input-shell input {
        flex: 1;
        border: none;
        background: transparent;
        padding: 0.9rem 0;
        font-size: 1rem;
        color: var(--text);
        font-family: inherit;
      }

      .input-shell input:focus {
        outline: none;
      }

      .input-addon {
        color: var(--text-muted);
        font-weight: 700;
        font-size: 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
      }

      .toggle-password {
        border: none;
        background: #e2e8f0;
        color: var(--text);
        font-size: 0.85rem;
        font-weight: 700;
        font-family: inherit;
        cursor: pointer;
        padding: 0.4rem 0.8rem;
        border-radius: 6px;
        transition: background-color 0.2s ease;
      }

      .toggle-password:hover {
        background: #cbd5e1;
      }

      .form-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.95rem;
        margin-top: -0.25rem;
      }

      .auth-link {
        color: var(--primary);
        text-decoration: none;
        font-weight: 700;
      }

      .auth-link:hover { text-decoration: underline; }

      .auth-btn {
        padding: 1rem;
        border-radius: 10px;
        border: none;
        background: var(--primary);
        color: #fff;
        font-size: 1.1rem;
        font-weight: 700;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        margin-top: 1rem;
        font-family: inherit;
        transition: background-color 0.2s;
      }

      .auth-btn:hover { 
        background: var(--primary-dark);
      }

      /* ALERTS */
      .auth-alert {
        padding: 1rem;
        border-radius: 10px;
        font-size: 0.95rem;
        font-weight: 600;
        margin-bottom: 1.5rem;
      }
      .auth-alert.error {
        background: #fef2f2;
        color: #b91c1c;
        border: 1px solid #fecaca;
      }
      .auth-alert.success {
        background: #f0fdf4;
        color: #15803d;
        border: 1px solid #bbf7d0;
      }
      .auth-alert ul { margin: 0; padding-right: 1.5rem; }

      /* RESPONSIVE */
      @media (max-width: 900px) {
        .auth-container { flex-direction: column; max-width: 500px; }
        .auth-form-side { order: 2; padding: 2rem; }
        .auth-hero-side { order: 1; padding: 2.5rem 2rem; border-bottom: 1px solid var(--border); }
        .hero-title { font-size: 1.8rem; }
      }
    </style>
  </head>

  <body>
    <div class="auth-container">
      
      <!-- FORM SIDE -->
      <section class="auth-form-side">
        <!-- SYSTEM LOGO -->
        <img src="{{ asset('images/' . ($app_settings->logo ?? 'logo-default.png')) }}" alt="الشعار" class="auth-logo">

        <header>
          <h2 class="panel-title">تسجيل الدخول</h2>
          <p class="panel-subtitle">
            قم بالوصول إلى لوحة التحكم الخاصة بك وإدارة كل شيء من مكان واحد.
          </p>
        </header>

        @if (session('status'))
        <div class="auth-alert success">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
        <div class="auth-alert error">
          <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
        @endif

        <form id="login_form" method="POST" action="{{ route('login') }}">
          @csrf
          <div class="field">
            <label for="email">البريد الإلكتروني</label>
            <div class="input-shell">
              <span class="input-addon">@</span>
              <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="أدخل البريد الإلكتروني (you@domain.com)" required dir="ltr" style="text-align: right;" />
            </div>
          </div>

          <div class="field">
            <label for="password">كلمة المرور</label>
            <div class="input-shell">
              <span class="input-addon">••</span>
              <input id="password" type="password" name="password" placeholder="أدخل كلمة المرور" required />
              <button type="button" class="toggle-password" data-target="password" data-show-text="عرض" data-hide-text="إخفاء">عرض</button>
            </div>
          </div>

          <div class="form-meta">
            <!-- Remember Me -->
            <label style="display:flex; align-items:center; gap:8px; cursor:pointer; color:var(--text-muted); font-weight:600;">
              <input type="checkbox" name="remember" style="width:18px; height:18px; accent-color:var(--primary);">
              تذكرني
            </label>

            <!-- Forgot Password -->
            <a class="auth-link" href="{{ route('password.request') }}">هل نسيت كلمة المرور؟</a>
          </div>

          <button type="submit" class="auth-btn" id="login_submit_btn">
            <span class="btn-text">تسجيل الدخول</span>
            <span class="btn-loading" style="display:none"><span class="spinner"></span>جاري التحقق...</span>
          </button>
        </form>
      </section>

      <!-- HERO SIDE -->
      <section class="auth-hero-side">
        <div class="hero-text-wrapper">
          <h1 class="hero-title">مرحباً بعودتك!</h1>
          <p class="hero-subtitle">
            أهلاً بك في نظام إدارة DawPOS المميز. أدخل بياناتك للوصول إلى أدوات الإدارة الذكية ومزامنة جميع عملياتك بكفاءة عالية.
          </p>
        </div>
      </section>

    </div>

    <script>
      (function() {
        const form = document.getElementById('login_form');
        const submitBtn = document.getElementById('login_submit_btn');
        const showButtons = document.querySelectorAll('.toggle-password');

        showButtons.forEach(btn => {
          btn.addEventListener('click', () => {
            const target = document.getElementById(btn.dataset.target);
            const isHidden = target.type === 'password';
            target.type = isHidden ? 'text' : 'password';
            btn.textContent = isHidden ? btn.dataset.hideText : btn.dataset.showText;
          });
        });

        if (!form) return;
        let submitted = false;
        const btnText = submitBtn.querySelector('.btn-text');
        const btnLoading = submitBtn.querySelector('.btn-loading');
        form.addEventListener('submit', () => {
          if (submitted) return;
          submitted = true;
          submitBtn.disabled = true;
          btnText.style.display = 'none';
          btnLoading.style.display = 'inline-flex';
        });
      })();
    </script>
  </body>
</html>
