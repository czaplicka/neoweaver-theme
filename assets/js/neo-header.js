document.addEventListener('DOMContentLoaded', () => {
  // --- 1. ZEGAR ---
  const updateNeoClock = () => {
    const el = document.getElementById('neo-clock');
    if (!el) return;
    const now = new Date();
    const h = String(now.getHours()).padStart(2, '0');
    const m = String(now.getMinutes()).padStart(2, '0');
    const s = String(now.getSeconds()).padStart(2, '0');
    el.textContent = `${h}:${m}:${s}`;
  };
  updateNeoClock();
  setInterval(updateNeoClock, 1000);

  // --- 2. SYNC ---
  let currentSync = 98.4;
const syncEl = document.getElementById('neo-sync');

  const updateNeoSync = () => {
    if (!syncEl) return;
    currentSync += (Math.random() - 0.5) * 0.4;
    if (currentSync > 99.9) currentSync = 99.9;
    if (currentSync < 88.0) currentSync = 88.0;
    syncEl.textContent = currentSync.toFixed(1) + '%';
    if (currentSync < 92.0) {
      syncEl.classList.add('neo-sync-critical', 'neo-jitter-active');
    } else {
      syncEl.classList.remove('neo-sync-critical', 'neo-jitter-active');
    }
  };
  if (syncEl) setInterval(updateNeoSync, 1500);
});

// --- 3. MENU ---
window.toggleNeoMenu = function () {
  const nav = document.getElementById('neo-main-nav');
  if (!nav) return;
  nav.classList.toggle('is-open');
};

window.addEventListener('click', function (event) {
  const nav = document.getElementById('neo-main-nav');
  const trigger = event.target.closest('.neo-menu-trigger');
  if (nav && nav.classList.contains('is-open')) {
    if (!trigger && !event.target.closest('#neo-main-nav')) {
      nav.classList.remove('is-open');
    }
  }
});

// === 4. AUTH MODAL (POZA LISTENERAMI!) ===
window.neoOpenAuthModal = function () {
  const modal = document.getElementById('neo-auth-modal');
  if (!modal) return;
  modal.classList.add('is-open');
  document.body.classList.add('neo-lock-scroll');
};

window.neoCloseAuthModal = function () {
  const modal = document.getElementById('neo-auth-modal');
  if (!modal) return;
  modal.classList.remove('is-open');
  document.body.classList.remove('neo-lock-scroll');
};

window.neoSwitchAuthTab = function (mode) {
  const loginPanel = document.getElementById('neo-auth-login');
  const registerPanel = document.getElementById('neo-auth-register');
  const tabs = document.querySelectorAll('.neo-auth-tab');
  if (!loginPanel || !registerPanel) return;

  if (mode === 'login') {
    loginPanel.classList.add('is-active');
    registerPanel.classList.remove('is-active');
  } else {
    registerPanel.classList.add('is-active');
    loginPanel.classList.remove('is-active');
  }

  tabs.forEach(btn => {
    const target = btn.getAttribute('data-target');
    const active =
      (mode === 'login' && target === 'neo-auth-login') ||
      (mode === 'register' && target === 'neo-auth-register');
    btn.classList.toggle('is-active', active);
  });
};
