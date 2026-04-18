<template>
  <div class="auth-layout-split">
    <!-- Brand Banner (Left in LTR, Right in RTL) -->
    <div class="auth-banner">
      <div class="banner-content">
        <h1 class="welcome-heading">{{ $t('Welcome_back') || 'Welcome back!' }}</h1>
        <p class="welcome-subtext">
          {{ $t('Sign_in_to_access_your_account_and_keep_your_operations_in_sync') || 'Sign in to access your account and keep your operations in sync.' }}
        </p>
      </div>
    </div>

    <!-- Form Area (Right in LTR, Left in RTL) -->
    <div class="auth-form-wrapper">
      <div class="auth-form-card animate-in">
        <div class="form-header">
          <h2 class="form-title">{{ $t('SignIn') || 'Sign In' }}</h2>
          <p class="form-subtitle">
            {{ $t('Access_your_dashboard_and_manage_everything_from_one_place') || 'Access your dashboard and manage everything from one place.' }}
          </p>
        </div>

        <validation-observer ref="submit_login">
          <b-form @submit.prevent="Submit_Login">
            <!-- Email Field -->
            <validation-provider
              name="Email Address"
              :rules="{ required: true}"
              v-slot="validationContext"
            >
              <b-form-group class="modern-input-group">
                <label class="modern-label">{{ $t('Email') || 'Email' }}</label>
                <div class="input-with-icon">
                  <span class="static-icon">@</span>
                  <b-form-input
                    :state="getValidationState(validationContext)"
                    class="rounded-input email-input"
                    type="email"
                    v-model="email"
                    placeholder="you@company.com"
                  ></b-form-input>
                </div>
                <b-form-invalid-feedback>{{ validationContext.errors[0] }}</b-form-invalid-feedback>
              </b-form-group>
            </validation-provider>

            <!-- Password Field -->
            <validation-provider
              name="Password"
              :rules="{ required: true}"
              v-slot="validationContext"
            >
              <b-form-group class="modern-input-group mt-3">
                <label class="modern-label">{{ $t('Password') || 'Password' }}</label>
                <div class="input-with-icon password-wrapper">
                  <span class="static-icon fw-bold">..</span>
                  <b-form-input
                    :state="getValidationState(validationContext)"
                    class="rounded-input password-input"
                    :type="showPassword ? 'text' : 'password'"
                    v-model="password"
                    :placeholder="$t('Enter_your_password') || 'Enter your password'"
                  ></b-form-input>
                  <span class="show-toggle" @click="showPassword = !showPassword">
                    {{ showPassword ? ($t('Hide') || 'Hide') : ($t('Show') || 'Show') }}
                  </span>
                </div>
                <b-form-invalid-feedback>{{ validationContext.errors[0] }}</b-form-invalid-feedback>
              </b-form-group>
            </validation-provider>

            <!-- Forgot Password -->
            <div class="text-right mt-2 mb-4 form-actions">
              <a href="/password/reset" class="forgot-link">
                {{ $t('Forgot_password') || 'Forgot password?' }}
              </a>
            </div>

            <!-- Submit Button -->
            <b-button
              type="submit"
              class="btn-submit"
              :disabled="loading"
            >
              <span v-if="!loading">{{ $t('SignIn') || 'Sign In' }}</span>
              <b-spinner v-else small label="Loading..."></b-spinner>
            </b-button>
          </b-form>
        </validation-observer>
      </div>
    </div>
  </div>
</template>

<script>
import { mapGetters } from "vuex";
import NProgress from "nprogress";

export default {
  metaInfo: {
    title: "SignIn"
  },
  data() {
    return {
      email: "",
      password: "",
      loading: false,
      showPassword: false
    };
  },
  computed: {
    ...mapGetters(["isAuthenticated", "error"])
  },
  methods: {
    Submit_Login() {
      this.$refs.submit_login.validate().then(success => {
        if (!success) {
          this.makeToast("danger", this.$t("Please_fill_the_form_correctly"), this.$t("Failed"));
        } else {
          this.Login();
        }
      });
    },

    getValidationState({ dirty, validated, valid = null }) {
      return dirty || validated ? valid : null;
    },

    Login() {
      let self = this;
      NProgress.start();
      NProgress.set(0.1);
      self.loading = true;
      axios
        .post("/login", { email: self.email, password: self.password }, { baseURL: '' })
        .then(response => {
          this.makeToast("success", this.$t("Successfully_Logged_In"), this.$t("Success"));
          window.location = '/';
          NProgress.done();
          this.loading = false;
        })
        .catch(error => {
          NProgress.done();
          this.loading = false;
          let msg = this.$t("Incorrect_Login");
          if (error && typeof error === 'object' && error.message) msg = error.message;
          this.makeToast("danger", msg, this.$t("Failed"));
        });
    },

    makeToast(variant, msg, title) {
      this.$root.$bvToast.toast(msg, { title: title, variant: variant, solid: true });
    }
  }
};
</script>

<style scoped>
/* Full Split Layout */
.auth-layout-split {
  display: flex;
  min-height: 100vh;
  width: 100vw;
  background-color: #f8fbfa; /* Very light mint/white */
  overflow: hidden;
}

/* Left side (Green Gradient) */
.auth-banner {
  flex: 1;
  background: linear-gradient(145deg, #04724D 0%, #15c68b 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 3rem;
  color: white;
}

.banner-content {
  max-width: 480px;
}

.welcome-heading {
  font-size: 3rem;
  font-weight: 700;
  margin-bottom: 1.5rem;
  line-height: 1.2;
}

.welcome-subtext {
  font-size: 1.15rem;
  line-height: 1.6;
  opacity: 0.9;
}

/* Right side (Form Area) */
.auth-form-wrapper {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem;
  background-color: #f8fbfa;
}

.auth-form-card {
  background: white;
  width: 100%;
  max-width: 450px;
  border-radius: 16px;
  padding: 3rem 2.5rem;
  box-shadow: 0 15px 35px rgba(0, 0, 0, 0.03);
}

.form-header {
  margin-bottom: 2rem;
}

.form-title {
  font-size: 2rem;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 0.5rem;
}

.form-subtitle {
  color: #64748b;
  font-size: 0.95rem;
  line-height: 1.5;
}

.modern-input-group {
  margin-bottom: 1.5rem;
}

.modern-label {
  font-weight: 500;
  color: #334155;
  font-size: 0.9rem;
  margin-bottom: 0.5rem;
  display: block;
}

.input-with-icon {
  position: relative;
  display: flex;
  align-items: center;
}

/* Soft rounded inputs with proper spacing for icons */
.rounded-input {
  border-radius: 12px !important;
  border: 1px solid #e2e8f0 !important;
  height: 50px !important;
  padding: 10px 16px !important;
  background-color: #f8fafc !important;
  color: #1e293b !important;
  transition: all 0.2s ease !important;
  font-size: 0.95rem !important;
  width: 100%;
}

.rounded-input:focus {
  border-color: #20c997 !important;
  box-shadow: 0 0 0 3px rgba(32, 201, 151, 0.1) !important;
  background-color: white !important;
  outline: none;
}

/* Hardcoded icons / toggles */
.static-icon {
  position: absolute;
  color: #64748b;
  font-size: 1rem;
  z-index: 2;
  top: 50%;
  transform: translateY(-50%);
}

.show-toggle {
  position: absolute;
  color: #04724D;
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
  z-index: 2;
  top: 50%;
  transform: translateY(-50%);
  user-select: none;
}

.show-toggle:hover {
  text-decoration: underline;
}

.forgot-link {
  color: #04724D;
  font-size: 0.9rem;
  font-weight: 600;
  text-decoration: none;
}

.forgot-link:hover {
  text-decoration: underline;
}

/* specific LTR/RTL spacing for absolute icons */
.input-with-icon .static-icon { left: 16px; }
.email-input { padding-left: 40px !important; }
.password-input { padding-left: 36px !important; padding-right: 60px !important; }
.show-toggle { right: 16px; }
.form-actions { text-align: right; }

[dir="rtl"] .input-with-icon .static-icon { left: auto; right: 16px; }
[dir="rtl"] .email-input { padding-left: 16px !important; padding-right: 40px !important; }
[dir="rtl"] .password-input { padding-right: 36px !important; padding-left: 60px !important; }
[dir="rtl"] .show-toggle { right: auto; left: 16px; }
[dir="rtl"] .form-actions { text-align: left; }

/* The Submit Button */
.btn-submit {
  background-color: #20c997;
  border-color: #20c997;
  color: white;
  border-radius: 12px;
  width: 100%;
  height: 52px;
  font-weight: 600;
  font-size: 1rem;
  transition: background-color 0.2s ease, transform 0.1s ease;
  margin-top: 0.5rem;
}

.btn-submit:hover:not(:disabled) {
  background-color: #17a077;
  border-color: #17a077;
}

.btn-submit:active:not(:disabled) {
  transform: translateY(1px);
}

.animate-in {
  animation: slide-up 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

@keyframes slide-up {
  0% {
    opacity: 0;
    transform: translateY(20px);
  }
  100% {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Responsive adjustment */
@media (max-width: 900px) {
  .auth-layout-split {
    flex-direction: column;
  }
  .auth-banner {
    flex: none;
    padding: 3rem 2rem;
    text-align: center;
  }
  .banner-content {
    margin: 0 auto;
  }
  .auth-form-wrapper {
    padding: 2rem 1rem;
  }
}
</style>
