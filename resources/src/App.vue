<template>
  <div>
    <!-- Initial app loader: shown immediately after refresh until core data is ready -->
    <div v-if="!Loading" class="daw-loader-overlay">
      <div class="daw-unified-loader">
        <div class="daw-loader-ring"></div>
        <div class="daw-loader-logo">
          <img src="/images/logo.png" alt="logo">
        </div>
      </div>
      <h3 class="daw-loader-text">
        {{ $t ? ($t('pos.Loading_Application') || 'Loading application...') : 'Loading application...' }}
      </h3>
    </div>

    <router-view></router-view>

    <!-- Global offline sync fullscreen loader -->
    <div v-if="globalSyncActive" class="daw-loader-overlay sync-active">
      <div class="daw-unified-loader">
        <div class="daw-loader-ring sync-ring"></div>
        <div class="daw-loader-logo">
          <img src="/images/logo.png" alt="logo">
        </div>
      </div>
      <h3 class="daw-loader-text">
        {{ $t ? ($t('pos.Syncing_offline_sales') || 'Syncing offline sales...') : 'Syncing offline sales...' }}
      </h3>
    </div>


  </div>
</template>


<script>
import { mapActions, mapGetters } from "vuex";

export default {
  data() {
    return {
      Loading:false,
      globalSyncActive: false,
    };
  },
  computed: {
    
    ...mapGetters("config", ["getThemeMode"]),
    ...mapGetters(["isAuthenticated","show_language","currentUser"]),
    themeName() {
      return this.getThemeMode.dark ? "dark-theme" : " ";
    },
    rtl() {
      return this.getThemeMode.rtl ? "rtl" : " ";
    },

    isPosPage() {
      return this.$route.path === '/app/pos';
    },
    titleTemplate() {
      return `%s | ${this.currentUser?.page_title_suffix || "Ultimate Inventory With POS"}`;
    }
  },

  metaInfo() {
    return {
      // if no subcomponents specify a metaInfo.title, this title will be used
      title: "DawPOS",
      titleTemplate: this.titleTemplate,

      bodyAttrs: {
        class: [this.themeName, "text-left"]
      },
      htmlAttrs: {
        dir: this.rtl
      },
      
    };
  },

  beforeDestroy() {
    // Clean up listeners
    try {
      if (typeof window !== 'undefined' && window.Fire && window.Fire.$off) {
        window.Fire.$off('offline-sync:start', this.onGlobalSyncStart);
        window.Fire.$off('offline-sync:end', this.onGlobalSyncEnd);
        window.Fire.$off('offline-sync:auto-result', this.onGlobalSyncResult);
      }
    } catch (e) {}
  },
  methods:{
    ...mapActions([
      "refreshUserPermissions",
    ]),
    async initializeApp() {
      try {
        // Ensure initial permissions and user info are fetched
        await this.refreshUserPermissions(this.$i18n);
      } catch (e) {
        // ignore; guards/interceptors will handle routing on auth errors
      } finally {
        this.Loading = true;
        // Signal that the app rendered initial route and is allowed to hide loader when no pending requests
        if (window) {
          window.__appReadyToHideLoader = true;
          if (typeof window.__hideInitialLoaderIfDone === 'function') {
            window.__hideInitialLoaderIfDone();
          }
        }
      }
    },
    onGlobalSyncStart() {
      this.globalSyncActive = true;
    },
    onGlobalSyncEnd() {
      this.globalSyncActive = false;
    },
    onGlobalSyncResult(payload) {
      try {
        const syncedCount = Number(payload && payload.syncedCount || 0);
        const lastError = payload && payload.lastError;
        // If at least one offline sale was synced successfully and there is no error,
        // reload the current page to reflect updated data everywhere – except when
        // the user is on the POS screen with a potentially active cart. In that
        // case, POS itself will decide if/when to reload via its own confirmation
        // flow to avoid disrupting an in‑progress checkout.
        if (syncedCount > 0 && !lastError) {
          const isPosRoute = this.$route &&
            (this.$route.name === 'pos' ||
             String(this.$route.path || '').includes('/app/pos'));
          if (!isPosRoute) {
            if (typeof window !== 'undefined' && window.location && typeof window.location.reload === 'function') {
              window.location.reload();
            }
          }
        }
      } catch (e) {}
    },
  },

  beforeMount() {
    // Replace timeout with awaited initialization
    this.initializeApp();
  },
  
  mounted() {
    // Listen for global offline sync start/end/result events
    try {
      if (typeof window !== 'undefined' && window.Fire && window.Fire.$on) {
        window.Fire.$on('offline-sync:start', this.onGlobalSyncStart);
        window.Fire.$on('offline-sync:end', this.onGlobalSyncEnd);
        window.Fire.$on('offline-sync:auto-result', this.onGlobalSyncResult);
      }
    } catch (e) {}
  },
};
</script>
<style scoped>
.daw-loader-overlay {
  position: fixed;
  inset: 0;
  background: rgba(248, 250, 252, 0.95);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  z-index: 999999;
}

.daw-loader-overlay.sync-active {
  background: rgba(15, 23, 42, 0.85);
  color: #fff;
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

.sync-ring::before { border-top-color: #38bdf8; border-right-color: #38bdf8; }
.sync-ring::after { border-bottom-color: #e2e8f0; border-left-color: #e2e8f0; }

.daw-loader-text {
  font-size: 1.15rem;
  font-weight: 600;
  margin: 0;
  letter-spacing: 0.5px;
  color: #334155;
  animation: pulse-op 2s ease-in-out infinite;
}

.sync-active .daw-loader-text {
  color: #fff;
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
