<template>
  <div class="bg-main">
    <div class="overlay"></div>
    <div class="center-wrapper">
      <div class="login-card" @keyup.enter="login">
        <div class="logo-wrap">
          <img src="../img/logo-bmt-nobg.png" class="logo-img" alt="Logo" />
        </div>
        <div class="divider"></div>
        <div class="form-group">
          <div class="input-wrap">
            <span class="input-icon">
              <svg
                xmlns="http://www.w3.org/2000/svg"
                width="16"
                height="16"
                fill="currentColor"
                viewBox="0 0 16 16"
              >
                <path
                  d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0zm4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4zm-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.029 10 8 10c-2.029 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664h10z"
                />
              </svg>
            </span>
            <input
              v-model="username"
              type="text"
              id="username"
              placeholder="Username"
              class="modern-input"
              autocomplete="username"
            />
          </div>
          <div class="input-wrap">
            <span class="input-icon">
              <svg
                xmlns="http://www.w3.org/2000/svg"
                width="16"
                height="16"
                fill="currentColor"
                viewBox="0 0 16 16"
              >
                <path
                  d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2zm3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"
                />
              </svg>
            </span>
            <input
              v-model="password"
              type="password"
              id="password"
              placeholder="Password"
              class="modern-input"
              autocomplete="current-password"
            />
          </div>
          <button class="login-btn" @click="login" :disabled="loading">
            <span class="btn-spinner" v-if="loading"></span>
            <span>{{ loading ? "Memproses..." : "Masuk" }}</span>
            <svg
              v-if="!loading"
              xmlns="http://www.w3.org/2000/svg"
              width="18"
              height="18"
              fill="currentColor"
              viewBox="0 0 16 16"
            >
              <path
                fill-rule="evenodd"
                d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8z"
              />
            </svg>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
*,
*::before,
*::after {
  box-sizing: border-box;
}

.bg-main {
  min-height: 100vh;
  width: 100%;
  background: url(../img/bg-medium.jpg) no-repeat center center fixed;
  -webkit-background-size: cover;
  -moz-background-size: cover;
  -o-background-size: cover;
  background-size: cover;
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
}

.overlay {
  position: absolute;
  inset: 0;
  background: rgba(0, 0, 0, 0.45);
  backdrop-filter: blur(2px);
  -webkit-backdrop-filter: blur(2px);
}

.center-wrapper {
  position: relative;
  z-index: 1;
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1.5rem;
}

.login-card {
  width: 100%;
  max-width: 380px;
  background: rgba(255, 255, 255, 0.232);
  border-radius: 20px;
  border: 1px solid rgba(255, 255, 255, 0.25);
  box-shadow:
    0 8px 32px rgba(0, 0, 0, 0.35),
    inset 0 1px 0 rgba(255, 255, 255, 0.2);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  padding: 2.5rem 2rem;
  animation: fadeUp 0.5s ease both;
}

@keyframes fadeUp {
  from {
    opacity: 0;
    transform: translateY(24px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.logo-wrap {
  display: flex;
  justify-content: center;
  margin-bottom: 1.5rem;
}

.logo-img {
  max-height: 80px;
  max-width: 220px;
  object-fit: contain;
  filter: drop-shadow(0 2px 8px rgba(0, 0, 0, 0.4));
}

.divider {
  height: 1px;
  background: linear-gradient(
    to right,
    transparent,
    rgba(255, 255, 255, 0.3),
    transparent
  );
  margin-bottom: 1.75rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.input-wrap {
  position: relative;
  display: flex;
  align-items: center;
}

.input-icon {
  position: absolute;
  left: 14px;
  color: rgba(255, 255, 255, 0.6);
  display: flex;
  pointer-events: none;
  transition: color 0.2s;
}

.input-wrap:focus-within .input-icon {
  color: rgba(255, 255, 255, 0.95);
}

.modern-input {
  width: 100%;
  padding: 0.8rem 1rem 0.8rem 2.75rem;
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 10px;
  color: #fff;
  font-size: 0.9rem;
  font-family: inherit;
  outline: none;
  transition:
    background 0.2s,
    border-color 0.2s,
    box-shadow 0.2s;
}

.modern-input::placeholder {
  color: rgba(255, 255, 255, 0.5);
}

.modern-input:focus {
  background: rgba(255, 255, 255, 0.18);
  border-color: rgba(255, 255, 255, 0.55);
  box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.08);
}

.login-btn {
  margin-top: 0.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.85rem 1.5rem;
  background: linear-gradient(135deg, #2563eb, #1d4ed8);
  border: none;
  border-radius: 10px;
  color: #fff;
  font-size: 0.95rem;
  font-weight: 600;
  letter-spacing: 0.03em;
  cursor: pointer;
  transition:
    transform 0.15s,
    box-shadow 0.15s,
    background 0.2s;
  box-shadow: 0 4px 16px rgba(37, 99, 235, 0.45);
  font-family: inherit;
}

.login-btn:hover {
  background: linear-gradient(135deg, #1d4ed8, #1e40af);
  transform: translateY(-1px);
  box-shadow: 0 6px 20px rgba(37, 99, 235, 0.55);
}

.login-btn:active {
  transform: translateY(0);
  box-shadow: 0 3px 10px rgba(37, 99, 235, 0.35);
}

.login-btn:disabled {
  opacity: 0.75;
  cursor: not-allowed;
  transform: none;
}

.login-btn svg {
  transition: transform 0.2s;
}

.login-btn:hover:not(:disabled) svg {
  transform: translateX(3px);
}

.btn-spinner {
  width: 16px;
  height: 16px;
  border: 2px solid rgba(255, 255, 255, 0.35);
  border-top-color: #fff;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
  flex-shrink: 0;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}
</style>

<script>
module.exports = {
  name: "",
  data: function () {
    return {
      username: "",
      password: "",
      loading: false,
    };
  },
  methods: {
    login: function () {
      if (this.loading) return;
      this.loading = true;
      var self = this;
      var resetLoading = function () {
        self.loading = false;
      };
      try {
        App.username = self.username;
        App.password = self.password;
        var result = App.login();
        var isPromiseLike = result && typeof result.then === "function";
        if (isPromiseLike) {
          result
            .then(function (res) {
              if (res && res.status === false) resetLoading();
            })
            .catch(resetLoading);
        } else {
          // If App.login does not return a Promise, do not block UI with timeout.
          resetLoading();
        }
      } catch (e) {
        resetLoading();
      }
    },
  },
  mounted: function () {},
};
</script>
