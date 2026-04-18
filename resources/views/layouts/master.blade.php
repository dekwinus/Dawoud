<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width,initial-scale=1.0" />
    <title>{{ $app_settings->app_name ?? 'DawPOS | Ultimate Inventory With POS' }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Changa:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
      .loading_wrap {
        width: 100vw;
        height: 100vh;
        overflow: hidden;
        background: rgba(248, 250, 252, 1);
        display: flex;
        align-items: center;
        justify-content: center;
        position: fixed;
        z-index: 999999;
        top: 0;
        left: 0;
      }
      .daw-unified-loader {
        position: relative;
        width: 140px;
        height: 140px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 2rem;
      }
      .daw-loader-logo {
        position: absolute;
        height: 50px;
        max-width: 100px;
        z-index: 10;
        animation: pulse-op 2s ease-in-out infinite;
        display: flex;
        align-items: center;
        justify-content: center;
      }
      .daw-loader-logo img {
        max-height: 100%;
        max-width: 100%;
        object-fit: contain;
      }
      .daw-loader-ring {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 5;
      }
      .daw-loader-ring::before, .daw-loader-ring::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        border-radius: 50%;
        border: 4px solid transparent;
      }
      .daw-loader-ring::before {
        border-top-color: #04724D;
        border-right-color: #04724D;
        animation: loader-spin-fwd 1.2s cubic-bezier(0.68, -0.55, 0.265, 1.55) infinite;
      }
      .daw-loader-ring::after {
        border-bottom-color: #3EFF8B;
        border-left-color: #3EFF8B;
        animation: loader-spin-bwd 1.6s cubic-bezier(0.68, -0.55, 0.265, 1.55) infinite alternate;
      }
      @keyframes loader-spin-fwd {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
      }
      @keyframes loader-spin-bwd {
        0% { transform: rotate(360deg) scale(0.85); }
        100% { transform: rotate(-360deg) scale(1.1); }
      }
      @keyframes pulse-op {
        0%, 100% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.05); opacity: 0.8; }
      }
    </style>
  </head>

  <body class="text-left">
    <noscript>
      <strong>
        We're sorry but DawPOS doesn't work properly without JavaScript
        enabled. Please enable it to continue.</strong
      >
    </noscript>

    <!-- built files will be auto injected -->
    <div class="loading_wrap" id="loading_wrap">
      <div class="daw-unified-loader">
        <div class="daw-loader-ring"></div>
        <div class="daw-loader-logo">
          <img src="{{ asset('images/' . ($app_settings->logo ?? 'logo.png')) }}" alt="logo" />
        </div>
      </div>
    </div>
    <div id="app">
      <script src="/assets_setup/js/qrcode.js"></script>

    </div>


    <script src="/js/main.min.js?v=5.4&v={{ time() }}"></script>

  </body>
</html>
