/* ═══════════════════════════════════════════
   FINZO — Customer Portal Shared JavaScript
   ═══════════════════════════════════════════ */

// ── THEME ──
const THEME_KEY = 'finzo-user-theme';

function initTheme() {
  const saved = localStorage.getItem(THEME_KEY);
  const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
  if (saved === 'dark' || (!saved && prefersDark)) {
    document.documentElement.classList.add('dark');
  }
  updateThemeIcon();
}

function toggleTheme() {
  const isDark = document.documentElement.classList.toggle('dark');
  localStorage.setItem(THEME_KEY, isDark ? 'dark' : 'light');
  updateThemeIcon();
}

function updateThemeIcon() {
  const isDark = document.documentElement.classList.contains('dark');
  const icons = document.querySelectorAll('[data-theme-icon]');
  icons.forEach(i => i.textContent = isDark ? 'light_mode' : 'dark_mode');
  const labels = document.querySelectorAll('[data-theme-label]');
  labels.forEach(l => l.textContent = isDark ? 'Light Mode' : 'Dark Mode');
}

initTheme();

// ── USER STATE (localStorage demo) ──
// PHP_BACKEND: Replace these with $_SESSION values from PHP
const STATE_KEYS = {
  STATUS: 'finzo_status',           // pending | under_review | active | rejected | deactivated
  NAME: 'finzo_user_name',
  EMAIL: 'finzo_user_email',
  PHONE: 'finzo_user_phone',
  ACCOUNT_NO: 'finzo_account_no',
  ACCOUNT_TYPE: 'finzo_account_type',
  BALANCE: 'finzo_balance',
  REJECTION_REASON: 'finzo_rejection_reason',
  NOTIF_COUNT: 'finzo_notif_count',
};

const UserState = {
  get status() { return localStorage.getItem(STATE_KEYS.STATUS) || 'pending'; },
  get name() { return localStorage.getItem(STATE_KEYS.NAME) || 'Rahul Kumar'; },
  get email() { return localStorage.getItem(STATE_KEYS.EMAIL) || 'rahul.kumar@gmail.com'; },
  get phone() { return localStorage.getItem(STATE_KEYS.PHONE) || '+91 98765 43210'; },
  get accountNo() { return localStorage.getItem(STATE_KEYS.ACCOUNT_NO) || ''; },
  get accountType() { return localStorage.getItem(STATE_KEYS.ACCOUNT_TYPE) || 'savings'; },
  get balance() { return localStorage.getItem(STATE_KEYS.BALANCE) || '0'; },
  get rejectionReason() { return localStorage.getItem(STATE_KEYS.REJECTION_REASON) || ''; },
  get notifCount() { return parseInt(localStorage.getItem(STATE_KEYS.NOTIF_COUNT) || '2'); },
  get isActive() { return this.status === 'active'; },
  get isPending() { return this.status === 'pending'; },
  get isUnderReview() { return this.status === 'under_review'; },
  get isRejected() { return this.status === 'rejected'; },
  get initials() { 
    return this.name.split(' ').map(n => n[0]).join('').substring(0,2).toUpperCase(); 
  },
  get isBalanceUnlocked() {
    return sessionStorage.getItem('finzo_balance_unlocked') === 'true';
  },
  unlockBalance() {
    sessionStorage.setItem('finzo_balance_unlocked', 'true');
    document.querySelectorAll('.balance-hidden-mask').forEach(el => {
      el.classList.remove('balance-hidden-mask');
      if (el.hasAttribute('data-real-value')) {
        el.textContent = el.getAttribute('data-real-value');
      }
    });
  },
  set(key, val) { localStorage.setItem(STATE_KEYS[key], val); },
};

// ── BALANCE SECURITY (PIN) ──
const DEMO_PIN = '1234';
function initBalanceMasking() {
  document.querySelectorAll('.balance-hidden-mask').forEach(el => {
    el.addEventListener('click', (e) => {
      e.stopPropagation();
      if (!UserState.isBalanceUnlocked) {
        showPINModal();
      }
    });
  });
}

function showPINModal() {
  const existing = document.getElementById('pin-entry-modal');
  if (existing) return;

  const modal = document.createElement('div');
  modal.id = 'pin-entry-modal';
  modal.className = 'modal-overlay';
  modal.innerHTML = `
    <div class="modal-card pin-modal-card">
      <div class="modal-icon" style="background:rgba(8,145,178,0.1)">
        <span class="material-symbols-outlined" style="color:#0891b2;font-size:28px">lock</span>
      </div>
      <div class="modal-title">Security Verification</div>
      <div class="modal-text">Please enter your 4-digit PIN to reveal financial information.</div>
      
      <div class="pin-display" id="pin-dots">
        <div class="pin-dot"></div>
        <div class="pin-dot"></div>
        <div class="pin-dot"></div>
        <div class="pin-dot"></div>
      </div>

      <input type="password" id="pin-input" class="pin-hidden-input" maxlength="4" autofocus readonly>
      
      <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap: 12px; max-width: 240px; margin: 0 auto 24px;">
        ${[1,2,3,4,5,6,7,8,9].map(n => `<button class="btn btn-ghost pin-key" data-val="${n}">${n}</button>`).join('')}
        <button class="btn btn-ghost pin-key" onclick="clearPIN()">C</button>
        <button class="btn btn-ghost pin-key" data-val="0">0</button>
        <button class="btn btn-ghost pin-key" onclick="closePINModal()">✕</button>
      </div>

      <div class="modal-actions">
        <button class="btn btn-ghost btn-full" onclick="closePINModal()">Cancel</button>
      </div>
    </div>`;
  
  document.body.appendChild(modal);
  
  let currentPin = '';
  const dots = modal.querySelectorAll('.pin-dot');
  
  const updateDots = () => {
    dots.forEach((dot, i) => {
      dot.classList.toggle('active', i < currentPin.length);
    });
  };

  modal.querySelectorAll('.pin-key[data-val]').forEach(btn => {
    btn.onclick = () => {
      if (currentPin.length < 4) {
        currentPin += btn.getAttribute('data-val');
        updateDots();
        if (currentPin.length === 4) {
          setTimeout(() => {
            if (currentPin === DEMO_PIN) {
              UserState.unlockBalance();
              showToast('Security Verified', 'success');
              closePINModal();
            } else {
              currentPin = '';
              updateDots();
              showToast('Incorrect PIN', 'error');
              // Optional: Add a shake animation to the dots
              document.getElementById('pin-dots').style.animation = 'none';
              void document.getElementById('pin-dots').offsetWidth;
              document.getElementById('pin-dots').style.animation = 'shake 0.4s';
            }
          }, 200);
        }
      }
    };
  });

  window.clearPIN = () => { currentPin = ''; updateDots(); };
  window.closePINModal = () => { modal.remove(); };
}

// Add shake animation if not in CSS
if (!document.getElementById('security-animations')) {
  const style = document.createElement('style');
  style.id = 'security-animations';
  style.innerHTML = `
    @keyframes shake {
      0%, 100% { transform: translateX(0); }
      25% { transform: translateX(-8px); }
      75% { transform: translateX(8px); }
    }
  `;
  document.head.appendChild(style);
}

// Demo: Initialize demo state if not set
if (!localStorage.getItem(STATE_KEYS.STATUS)) {
  localStorage.setItem(STATE_KEYS.STATUS, 'pending');
  localStorage.setItem(STATE_KEYS.NAME, 'Rahul Kumar');
  localStorage.setItem(STATE_KEYS.EMAIL, 'rahul.kumar@gmail.com');
  localStorage.setItem(STATE_KEYS.PHONE, '+91 98765 43210');
  localStorage.setItem(STATE_KEYS.NOTIF_COUNT, '2');
}

// ── SIDEBAR BUILDER ──
function buildSidebar(activePage) {
  const status = UserState.status;
  const isActive = status === 'active';
  const isPending = status === 'pending';
  const isUnderReview = status === 'under_review';
  const isRejected = status === 'rejected';

  // Pages locked when not active
  const lockedWhenInactive = ['accounts', 'transfer', 'transactions', 'statement', 'beneficiary'];
  
  const mainLinks = [
    { id: 'dashboard', icon: 'dashboard', label: 'Dashboard', href: 'dashboard.html' },
    { id: 'accounts', icon: 'account_balance', label: 'My Accounts', href: 'accounts.html', lockedWhenInactive: true },
    { id: 'transfer', icon: 'swap_horiz', label: 'Fund Transfer', href: 'transfer.html', lockedWhenInactive: true },
    { id: 'transactions', icon: 'receipt_long', label: 'Transactions', href: 'transactions.html', lockedWhenInactive: true },
    { id: 'beneficiary', icon: 'group', label: 'Beneficiaries', href: 'beneficiary.html', lockedWhenInactive: true },
    { id: 'statement', icon: 'description', label: 'Statement', href: 'statement.html', lockedWhenInactive: true },
    { id: 'notifications', icon: 'notifications', label: 'Notifications', href: 'notifications.html', badge: UserState.notifCount > 0 ? UserState.notifCount : null },
  ];

  const settingsLinks = [
    { id: 'profile', icon: 'person', label: 'My Profile', href: 'profile.html' },
    { id: 'change-password', icon: 'lock', label: 'Change Password', href: 'change-password.html' },
  ];

  // Add complete-profile for pending/rejected
  if (isPending || isRejected) {
    settingsLinks.unshift({ id: 'complete-profile', icon: 'assignment_ind', label: isPending ? 'Complete Profile' : 'Resubmit Profile', href: 'complete-profile.html', highlight: true });
  }

  const html = `
    <div id="user-sidebar-overlay" class="sidebar-overlay"></div>
    <aside class="sidebar" id="user-sidebar">
      <div class="sidebar-brand">
        <div class="sidebar-brand-name">Finzo</div>
        <div class="sidebar-brand-sub">Customer Portal</div>
      </div>
      <div class="sidebar-user">
        <div class="sidebar-user-card">
          <div class="sidebar-avatar">${UserState.initials}</div>
          <div>
            <div class="sidebar-user-name">${UserState.name}</div>
            <div class="sidebar-user-status">
              <span class="sidebar-status-dot"></span>
              ${getStatusLabel(status)}
            </div>
          </div>
        </div>
      </div>
      <nav class="sidebar-nav">
        <div class="sidebar-section">Main</div>
        ${mainLinks.map(link => {
          const locked = link.lockedWhenInactive && !isActive;
          const isActive2 = activePage === link.id;
          return `
            <a href="${locked ? '#' : link.href}" 
               class="nav-item ${isActive2 ? 'active' : ''} ${locked ? 'locked' : ''} ${link.highlight ? 'highlight-item' : ''}"
               ${locked ? 'title="Available after account activation"' : ''}>
              <span class="material-symbols-outlined nav-icon">${link.icon}</span>
              <span style="flex:1">${link.label}</span>
              ${link.badge ? `<span class="nav-badge">${link.badge}</span>` : ''}
              ${locked ? '<span class="material-symbols-outlined" style="font-size:14px;color:rgba(255,255,255,0.25)">lock</span>' : ''}
            </a>`;
        }).join('')}
        
        <div class="sidebar-section">Settings</div>
        ${settingsLinks.map(link => {
          const isActive2 = activePage === link.id;
          return `
            <a href="${link.href}" class="nav-item ${isActive2 ? 'active' : ''}" 
               style="${link.highlight ? 'color:var(--primary);background:rgba(8,145,178,0.08);' : ''}">
              <span class="material-symbols-outlined nav-icon">${link.icon}</span>
              ${link.label}
            </a>`;
        }).join('')}

        <div class="sidebar-section">Account</div>
        <div class="sidebar-bottom">
          <button class="nav-item" onclick="toggleTheme()">
            <span class="material-symbols-outlined nav-icon" data-theme-icon>dark_mode</span>
            <span data-theme-label>Dark Mode</span>
          </button>
          <a href="../auth/login.html" class="nav-item logout-item">
            <span class="material-symbols-outlined nav-icon">logout</span>
            Logout
          </a>
        </div>
      </nav>
    </aside>`;

  const container = document.getElementById('sidebar-root');
  if (container) container.innerHTML = html;
  updateThemeIcon();

  // Bind mobile sidebar overlay dismiss
  const overlay = document.getElementById('user-sidebar-overlay');
  if(overlay) {
    overlay.addEventListener('click', () => {
      document.getElementById('user-sidebar').classList.remove('mobile-open');
      overlay.classList.remove('active');
    });
  }
}

function getStatusLabel(status) {
  const labels = {
    pending: 'Profile Incomplete',
    under_review: 'Under Review',
    active: 'Account Active',
    rejected: 'Application Rejected',
    deactivated: 'Account Deactivated',
  };
  return labels[status] || 'Unknown';
}

// ── NAVBAR BUILDER ──
function buildNavbar(eyebrow, title) {
  const notifCount = UserState.notifCount;
  const html = `
    <nav class="navbar">
      <div class="navbar-left">
        <button class="nav-icon-btn nav-mobile-toggle" onclick="document.getElementById('user-sidebar').classList.add('mobile-open'); document.getElementById('user-sidebar-overlay').classList.add('active');" style="margin-right:4px;">
          <span class="material-symbols-outlined">menu</span>
        </button>
        <div class="navbar-title-wrap">
          <div class="navbar-eyebrow">${eyebrow}</div>
          <div class="navbar-title">${title}</div>
        </div>
      </div>
      <div class="navbar-right">
        <div style="position:relative">
          <button class="nav-icon-btn" onclick="toggleNotifDropdown()">
            <span class="material-symbols-outlined">notifications</span>
            ${notifCount > 0 ? `<span style="position:absolute;top:6px;right:6px;width:8px;height:8px;background:#f43f5e;border-radius:50%;border:2px solid var(--bg-navbar);animation:pulse 2s infinite"></span>` : ''}
          </button>
          <div id="notif-dropdown" class="notif-dropdown hidden">
            <div style="padding:14px 16px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center">
              <span style="font-size:14px;font-weight:700;color:var(--text-primary)">Notifications</span>
              <button onclick="markAllRead()" style="font-size:11px;font-weight:700;color:var(--primary);background:none;border:none;cursor:pointer">Mark all read</button>
            </div>
            <div id="notif-list">
              <div class="notif-item unread">
                <div class="notif-item-icon" style="background:rgba(8,145,178,0.1)"><span class="material-symbols-outlined" style="font-size:18px;color:#0891b2">info</span></div>
                <div>
                  <div class="notif-item-title">Welcome to Finzo!</div>
                  <div class="notif-item-text">Your account has been registered. Complete your profile to activate.</div>
                  <div class="notif-item-time">Today</div>
                </div>
              </div>
              <div class="notif-item unread">
                <div class="notif-item-icon" style="background:rgba(245,158,11,0.1)"><span class="material-symbols-outlined" style="font-size:18px;color:#f59e0b">pending</span></div>
                <div>
                  <div class="notif-item-title">Profile Incomplete</div>
                  <div class="notif-item-text">Please complete your identity verification to unlock banking.</div>
                  <div class="notif-item-time">Today</div>
                </div>
              </div>
            </div>
            <div style="padding:12px;text-align:center;border-top:1px solid var(--border)">
              <a href="notifications.html" style="font-size:12px;font-weight:700;color:var(--primary)">View all notifications →</a>
            </div>
          </div>
        </div>
        <button class="nav-icon-btn" onclick="toggleTheme()" title="Toggle theme">
          <span class="material-symbols-outlined" data-theme-icon>dark_mode</span>
        </button>
      </div>
    </nav>`;
  
  const container = document.getElementById('navbar-root');
  if (container) container.innerHTML = html;
  updateThemeIcon();
  
  // Re-initialize masking whenever navbar (and thus page) is ready
  initBalanceMasking();
}

let notifOpen = false;
function toggleNotifDropdown() {
  const el = document.getElementById('notif-dropdown');
  if (el) el.classList.toggle('hidden');
}

function markAllRead() {
  document.querySelectorAll('.notif-item.unread').forEach(i => i.classList.remove('unread'));
  localStorage.setItem(STATE_KEYS.NOTIF_COUNT, '0');
}

// Close dropdowns on outside click
document.addEventListener('click', e => {
  const dd = document.getElementById('notif-dropdown');
  if (dd && !dd.closest('[onclick]') && !e.target.closest('.nav-icon-btn') && !e.target.closest('.notif-dropdown')) {
    dd.classList.add('hidden');
  }
});

// ── TOAST ──
function showToast(message, type = 'success', duration = 3000) {
  let container = document.getElementById('toast-container');
  if (!container) {
    container = document.createElement('div');
    container.id = 'toast-container';
    container.className = 'toast-container';
    document.body.appendChild(container);
  }
  const toast = document.createElement('div');
  const icons = { success: 'check_circle', error: 'cancel', warning: 'warning', info: 'info' };
  const colors = { success: '#10b981', error: '#f43f5e', warning: '#f59e0b', info: '#0891b2' };
  toast.className = `toast toast-${type}`;
  toast.innerHTML = `<span class="material-symbols-outlined" style="color:${colors[type]};font-size:20px;flex-shrink:0">${icons[type]}</span><span>${message}</span>`;
  container.appendChild(toast);
  setTimeout(() => { toast.style.opacity = '0'; toast.style.transform = 'translateX(20px)'; toast.style.transition = '0.3s'; setTimeout(() => toast.remove(), 300); }, duration);
}

// ── MODAL ──
function openModal(id) { document.getElementById(id)?.classList.remove('hidden'); }
function closeModal(id) { document.getElementById(id)?.classList.add('hidden'); }
function openConfirm(title, text, onConfirm, danger = false) {
  const existing = document.getElementById('global-confirm-modal');
  if (existing) existing.remove();
  const modal = document.createElement('div');
  modal.id = 'global-confirm-modal';
  modal.className = 'modal-overlay';
  modal.innerHTML = `
    <div class="modal-card" style="max-width:400px">
      <div class="modal-icon" style="background:${danger ? 'rgba(244,63,94,0.1)' : 'rgba(8,145,178,0.1)'}">
        <span class="material-symbols-outlined" style="color:${danger ? '#f43f5e' : '#0891b2'};font-size:28px">${danger ? 'warning' : 'help'}</span>
      </div>
      <div class="modal-title">${title}</div>
      <div class="modal-text">${text}</div>
      <div class="modal-actions">
        <button class="btn btn-ghost" onclick="document.getElementById('global-confirm-modal').remove()">Cancel</button>
        <button class="btn ${danger ? 'btn-danger' : 'btn-primary'}" id="confirm-action-btn">Confirm</button>
      </div>
    </div>`;
  document.body.appendChild(modal);
  document.getElementById('confirm-action-btn').onclick = () => { modal.remove(); onConfirm(); };
}

// ── AMOUNT FORMATTER ──
function formatINR(amount) {
  const num = parseFloat(amount);
  if (isNaN(num)) return '₹0.00';
  return '₹' + num.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function amountToWords(amount) {
  const ones = ['','One','Two','Three','Four','Five','Six','Seven','Eight','Nine','Ten','Eleven','Twelve','Thirteen','Fourteen','Fifteen','Sixteen','Seventeen','Eighteen','Nineteen'];
  const tens = ['','','Twenty','Thirty','Forty','Fifty','Sixty','Seventy','Eighty','Ninety'];
  function numToWords(n) {
    if (n === 0) return 'Zero';
    if (n < 20) return ones[n];
    if (n < 100) return tens[Math.floor(n/10)] + (n%10 ? ' '+ones[n%10] : '');
    if (n < 1000) return ones[Math.floor(n/100)] + ' Hundred' + (n%100 ? ' '+numToWords(n%100) : '');
    if (n < 100000) return numToWords(Math.floor(n/1000)) + ' Thousand' + (n%1000 ? ' '+numToWords(n%1000) : '');
    if (n < 10000000) return numToWords(Math.floor(n/100000)) + ' Lakh' + (n%100000 ? ' '+numToWords(n%100000) : '');
    return numToWords(Math.floor(n/10000000)) + ' Crore' + (n%10000000 ? ' '+numToWords(n%10000000) : '');
  }
  const num = Math.floor(parseFloat(amount));
  if (isNaN(num) || num <= 0) return '';
  return numToWords(num) + ' Rupees Only';
}

// ── GREETING ──
function getGreeting() {
  const h = new Date().getHours();
  if (h < 12) return 'Good Morning';
  if (h < 17) return 'Good Afternoon';
  return 'Good Evening';
}

// ── REDIRECT GUARDS ──
function requireLogin() {
  // PHP_BACKEND: Replace with session check
  // if (!$_SESSION['user_id']) header('Location: /views/auth/login.html');
  const status = UserState.status;
  if (!status) { window.location.href = '../auth/login.html'; return false; }
  return true;
}

function requireActive(redirectTo = 'dashboard.html') {
  // PHP_BACKEND: Check $_SESSION['user_status'] === 'active'
  if (!UserState.isActive) { window.location.href = redirectTo; return false; }
  return true;
}

// ── STATUS REDIRECT LOGIC ──
function handleStatusRedirect(currentPage) {
  const status = UserState.status;
  // Pages only accessible when NOT active
  const pendingOnlyPages = ['complete-profile', 'account-pending', 'account-rejected'];
  // Pages only accessible when active
  const activeOnlyPages = ['accounts', 'account-detail', 'transfer', 'transactions', 'statement', 'beneficiary'];

  if (activeOnlyPages.includes(currentPage) && !UserState.isActive) {
    window.location.href = 'dashboard.html';
    return;
  }
  if (status === 'under_review' && currentPage === 'complete-profile') {
    window.location.href = 'account-pending.html';
    return;
  }
  if (status === 'rejected' && currentPage === 'account-pending') {
    window.location.href = 'account-rejected.html';
    return;
  }
}

// ── DEMO CONTROLS (for testing in browser) ──
// Call these in console to test different states:
// FinzoDemo.setActive() — sets account as active
// FinzoDemo.setPending() — back to pending
// FinzoDemo.setUnderReview() — under review
// FinzoDemo.setRejected('ID not clear') — rejected with reason
window.FinzoDemo = {
  setActive() {
    localStorage.setItem(STATE_KEYS.STATUS, 'active');
    localStorage.setItem(STATE_KEYS.ACCOUNT_NO, 'FNZ20260001');
    localStorage.setItem(STATE_KEYS.ACCOUNT_TYPE, 'savings');
    localStorage.setItem(STATE_KEYS.BALANCE, '500.00');
    location.reload();
  },
  setPending() {
    localStorage.setItem(STATE_KEYS.STATUS, 'pending');
    localStorage.removeItem(STATE_KEYS.ACCOUNT_NO);
    location.reload();
  },
  setUnderReview() {
    localStorage.setItem(STATE_KEYS.STATUS, 'under_review');
    location.reload();
  },
  setRejected(reason = 'ID document not clear. Please resubmit.') {
    localStorage.setItem(STATE_KEYS.STATUS, 'rejected');
    localStorage.setItem(STATE_KEYS.REJECTION_REASON, reason);
    location.reload();
  },
};