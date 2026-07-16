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

// ── USER STATE (Session-backed) ──
// Populated from PHP session via API. Falls back to localStorage for offline demo mode.
const STATE_KEYS = {
  NOTIF_COUNT: 'finzo_notif_count',
  STATUS: 'finzo_user_status',
};

// Internal cache — populated by fetchUserSession()
const _userCache = {
  status: 'pending',
  id: 0,
  customerId: '',
  name: 'User',
  email: '',
  phone: '',
  accountNo: '',
  accountType: 'savings',
  balance: '0',
  rejectionReason: '',
  rejectedAt: '',
  pastRejections: [],
  notifCount: parseInt(localStorage.getItem(STATE_KEYS.NOTIF_COUNT) || '0'),
  avatarUrl: null,
  _loaded: false,
};

/**
 * Fetch user session from backend and populate UserState cache.
 * Called once on page load. Rebuilds sidebar/navbar after data arrives.
 */
async function fetchUserSession() {
  try {
    const res = await fetch('../../../../api/user/dashboard.php', { credentials: 'same-origin' });
    if (res.status === 401 || res.status === 403) {   // Not logged in — redirect to login
      window.location.href = '../../../../resources/views/auth/login.html';
      return;
    }
    const data = await res.json();
    if (data.success) {
      _userCache.status = data.user?.status || 'pending';
      _userCache.id = data.user?.id || 0;
      _userCache.customerId = data.user?.customer_id || '';
      _userCache.name = data.user?.name || 'User';
      _userCache.email = data.user?.email || '';
      _userCache.phone = data.user?.phone || '';
      _userCache.accountNo = data.account?.account_no || '';
      _userCache.accountType = data.account?.account_type || 'savings';
      _userCache.balance = data.account?.balance || '0';
      _userCache.interestRate = data.account?.interest_rate || '0';
      _userCache.dailyLimit = data.account?.daily_limit || '0';
      _userCache.openedDate = data.account?.opened_date || '';
      _userCache.rejectionReason = data.user?.rejection_reason || '';
      _userCache.rejectedAt = data.user?.updated_at || '';
      _userCache.pastRejections = data.past_rejections || [];
      _userCache.notifCount = data.notification_count || 0;
      _userCache.avatarUrl = data.user?.avatar_url || null;
      _userCache._loaded = true;

      // Sync metadata back to localStorage
      localStorage.setItem(STATE_KEYS.STATUS, _userCache.status);
      localStorage.setItem(STATE_KEYS.NOTIF_COUNT, String(_userCache.notifCount));
    }
  } catch (err) {
    console.warn('Session fetch failed, using cached state:', err.message);
  }
}

const UserState = {
  get status() { return _userCache.status; },
  get id() { return _userCache.id; },
  get customerId() { return _userCache.customerId; },
  get name() { return _userCache.name; },
  get email() { return _userCache.email; },
  get phone() { return _userCache.phone; },
  get accountNo() { return _userCache.accountNo; },
  get accountType() { return _userCache.accountType; },
  get balance() { return _userCache.balance; },
  get interestRate() { return _userCache.interestRate; },
  get dailyLimit() { return _userCache.dailyLimit; },
  get openedDate() { return _userCache.openedDate; },
  get rejectionReason() { return _userCache.rejectionReason; },
  get rejectedAt() { return _userCache.rejectedAt; },
  get pastRejections() { return _userCache.pastRejections; },
  get notifCount() { return _userCache.notifCount; },
  get avatarUrl() { return _userCache.avatarUrl; },
  get isActive() { return this.status === 'active'; },
  get isPending() { return this.status === 'pending'; },
  get isUnderReview() { return this.status === 'under_review'; },
  get isRejected() { return this.status === 'rejected'; },
  get isLoaded() { return _userCache._loaded; },
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
    document.querySelectorAll('.reveal-balance-btn .material-symbols-outlined').forEach(icon => {
      icon.textContent = 'visibility_off';
    });
  },
  lockBalance() {
    sessionStorage.removeItem('finzo_balance_unlocked');
    document.querySelectorAll('[data-real-value]').forEach(el => {
      el.classList.add('balance-hidden-mask');
      el.textContent = '₹ ••••';
    });
    document.querySelectorAll('.reveal-balance-btn .material-symbols-outlined').forEach(icon => {
      icon.textContent = 'visibility';
    });
  },
  set(key, val) {
    _userCache[key.toLowerCase()] = val;
    if (STATE_KEYS[key]) {
      localStorage.setItem(STATE_KEYS[key], val);
    }
  },
};

// ── BALANCE SECURITY (PIN) ──
// PIN is verified server-side via /api/user/verify-pin.php (or kept client-side for demo)

function injectRevealButton(el) {
  // Prevent duplicate wrappers
  if (el.closest('.balance-reveal-wrapper')) return;

  const wrapper = document.createElement('span');
  wrapper.className = 'balance-reveal-wrapper';

  const btn = document.createElement('button');
  btn.className = 'reveal-balance-btn';
  btn.type = 'button';
  
  const isUnlocked = UserState.isBalanceUnlocked;
  btn.innerHTML = `<span class="material-symbols-outlined" style="font-size:18px">${isUnlocked ? 'visibility_off' : 'visibility'}</span>`;
  btn.title = isUnlocked ? 'Hide balance' : 'Enter PIN to reveal';

  const toggleVisibility = (e) => {
    e.preventDefault();
    e.stopPropagation();
    if (UserState.isBalanceUnlocked) {
      UserState.lockBalance();
    } else {
      showPINModal();
    }
  };

  el.addEventListener('click', (e) => {
    if (!UserState.isBalanceUnlocked) toggleVisibility(e);
  });
  btn.addEventListener('click', toggleVisibility);

  // Layout: Put them in the wrapper
  el.after(wrapper);
  wrapper.appendChild(el);
  wrapper.appendChild(btn);
}

function initBalanceMasking() {
  // Initial scan
  document.querySelectorAll('.balance-hidden-mask').forEach(injectRevealButton);

  // Set up observer for dynamic content
  const observer = new MutationObserver((mutations) => {
    mutations.forEach((mutation) => {
      mutation.addedNodes.forEach((node) => {
        if (node.nodeType === 1) { // Element node
          if (node.classList.contains('balance-hidden-mask')) {
            injectRevealButton(node);
          }
          node.querySelectorAll('.balance-hidden-mask').forEach(injectRevealButton);
        }
      });
      // Also watch for class changes on existing nodes
      if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
        if (mutation.target.classList.contains('balance-hidden-mask')) {
          injectRevealButton(mutation.target);
        }
      }
    });
  });

  observer.observe(document.body, {
    childList: true,
    subtree: true,
    attributes: true,
    attributeFilter: ['class']
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
          btn.innerHTML = '<span class="material-symbols-outlined animate-spin" style="font-size:16px">progress_activity</span>';
          fetch('../../../../api/user/verify-pin.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'same-origin',
            body: JSON.stringify({ pin: currentPin })
          })
          .then(res => res.json())
          .then(data => {
            if (data.success) {
              UserState.unlockBalance();
              showToast('Security Verified', 'success');
              closePINModal();
            } else {
              currentPin = '';
              updateDots();
              showToast(data.message || 'Incorrect PIN', 'error');
              document.getElementById('pin-dots').style.animation = 'none';
              void document.getElementById('pin-dots').offsetWidth;
              document.getElementById('pin-dots').style.animation = 'shake 0.4s';
            }
          })
          .catch(err => {
            currentPin = '';
            updateDots();
            showToast('Connection error', 'error');
          })
          .finally(() => {
            btn.innerHTML = btn.getAttribute('data-val');
          });
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

// Session init: fetch user state from backend on page load
// Pages should call fetchUserSession() in their DOMContentLoaded handler.

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
          <div class="sidebar-avatar" ${UserState.avatarUrl ? `style="background-image:url(${UserState.avatarUrl});background-size:cover;color:transparent"` : ''}>${UserState.initials}</div>
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
          <button class="nav-item logout-item" onclick="FinzoSession.logout()">
            <span class="material-symbols-outlined nav-icon">logout</span>
            Logout
          </button>
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
              <!-- Dynamically loaded -->
              <div style="padding:20px;text-align:center;font-size:12px;color:var(--text-placeholder)">Loading...</div>
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
  if (el) {
    el.classList.toggle('hidden');
    if (!el.classList.contains('hidden')) {
      loadNavbarNotifications();
    }
  }
}

async function loadNavbarNotifications() {
  const list = document.getElementById('notif-list');
  list.innerHTML = '<div style="padding:20px;text-align:center"><span class="material-symbols-outlined spin" style="font-size:24px;color:var(--text-placeholder)">autorenew</span></div>';
  try {
    const res = await fetch('../../../../api/user/notifications.php?per_page=4', { credentials: 'same-origin' });
    const data = await res.json();
    if (data.success && data.notifications) {
      if (data.notifications.length === 0) {
        list.innerHTML = '<div style="padding:20px;text-align:center;font-size:12px;color:var(--text-placeholder)">No recent notifications</div>';
      } else {
        list.innerHTML = data.notifications.map(n => `
          <div class="notif-item ${!n.is_read ? 'unread' : ''}">
            <div class="notif-item-icon" style="background:${n.icon_bg || 'rgba(8,145,178,0.1)'}">
              <span class="material-symbols-outlined" style="font-size:18px;color:${n.icon_color || '#0891b2'}">${n.icon || 'notifications'}</span>
            </div>
            <div>
              <div class="notif-item-title">${n.title}</div>
              <div class="notif-item-text" style="font-size:11px;color:var(--text-secondary);margin-top:2px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden">${n.message}</div>
              <div class="notif-item-time" style="font-size:10px;color:var(--text-placeholder);margin-top:4px">${n.created_at ? new Date(n.created_at).toLocaleDateString() : 'Recently'}</div>
            </div>
          </div>
        `).join('');
      }
    }
  } catch (e) {
    list.innerHTML = '<div style="padding:20px;text-align:center;font-size:12px;color:var(--danger)">Failed to load</div>';
  }
}

async function markAllRead() {
  document.querySelectorAll('.notif-item.unread').forEach(i => i.classList.remove('unread'));
  _userCache.notifCount = 0;
  localStorage.setItem(STATE_KEYS.NOTIF_COUNT, '0');
  // Call API to mark all as read
  try {
    await fetch('../../../../api/user/notifications.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      credentials: 'same-origin',
      body: 'action=mark_all_read'
    });
    // refresh the dropdown if open
    loadNavbarNotifications();
    // also refresh the badge
    const badge = document.querySelector('.navbar-right .nav-icon-btn span[style*="absolute"]');
    if (badge) badge.remove();
  } catch (e) { console.error('Failed to mark read', e); }
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
  // Session validation is handled server-side by middleware.
  // This provides a client-side fallback.
  const status = UserState.status;
  if (!status) { window.location.href = '../../../../resources/views/auth/login.html'; return false; }
  return true;
}

function requireActive(redirectTo = 'dashboard.html') {
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

  // Inactive users cannot access active-only pages
  if (activeOnlyPages.includes(currentPage) && status !== 'active') {
    if (status === 'under_review') {
      window.location.href = 'account-pending.html';
    } else {
      window.location.href = 'dashboard.html';
    }
    return;
  }

  // Active users cannot access KYC onboarding pages
  if (pendingOnlyPages.includes(currentPage) && status === 'active') {
    window.location.href = 'dashboard.html';
    return;
  }

  // Pending users (KYC not submitted) cannot access review or rejected pages
  if (status === 'pending' && (currentPage === 'account-pending' || currentPage === 'account-rejected')) {
    window.location.href = 'complete-profile.html';
    return;
  }

  // Under review users cannot access KYC form or rejected page
  if (status === 'under_review' && (currentPage === 'complete-profile' || currentPage === 'account-rejected')) {
    window.location.href = 'account-pending.html';
    return;
  }

  // Rejected users cannot access the under review page
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
// ── SESSION REFRESH (for testing and auto-update) ──
window.FinzoSession = {
  async refresh() {
    await fetchUserSession();
    location.reload();
  },
  async logout() {
    try {
      await fetch('../../../../api/auth/logout.php', { method: 'POST', credentials: 'same-origin' });
      // Clear local cache
      Object.values(STATE_KEYS).forEach(k => localStorage.removeItem(k));
      sessionStorage.clear();
      window.location.href = '../../../../resources/views/auth/login.html';
    } catch (e) {
      window.location.href = '../../../../resources/views/auth/login.html';
    }
  }
};

// ==========================================
// Finzo Dropdowns Engine (Shared)
// ==========================================
(function() {
    function initDropdowns() {
        document.addEventListener('click', function(e) {
            const trigger = e.target.closest('.finzo-dropdown-trigger');
            if (!trigger) return;
            
            e.preventDefault();
            e.stopPropagation();
            
            const dropdown = trigger.closest('.finzo-dropdown');
            const menu = dropdown.querySelector('.finzo-dropdown-menu');
            
            const isOpen = trigger.classList.contains('is-open');
            closeAllDropdowns();
            
            if (!isOpen) {
                trigger.classList.add('is-open');
                menu.classList.add('is-open');
            }
        });

        document.addEventListener('click', function(e) {
            const option = e.target.closest('.finzo-dropdown-option');
            if (!option) return;
            
            if (option.tagName !== 'A' && option.tagName !== 'BUTTON') {
                e.preventDefault();
            } else if (option.tagName === 'BUTTON' && !option.hasAttribute('onclick')) {
                e.preventDefault();
            }
            e.stopPropagation();
            
            const dropdown = option.closest('.finzo-dropdown');
            const trigger = dropdown.querySelector('.finzo-dropdown-trigger');
            const menu = dropdown.querySelector('.finzo-dropdown-menu');
            const input = dropdown.querySelector('input[type="hidden"]');
            const label = trigger.querySelector('.dropdown-label');
            
            const value = option.getAttribute('data-value');
            const text = option.textContent.trim();
            
            dropdown.querySelectorAll('.finzo-dropdown-option').forEach(opt => opt.classList.remove('is-selected'));
            option.classList.add('is-selected');
            
            if (input) {
                input.value = value;
                input.dispatchEvent(new Event('change', { bubbles: true }));
            }
            if (label) label.textContent = text;
            
            trigger.classList.remove('is-open');
            menu.classList.remove('is-open');
        });

        document.addEventListener('click', function(e) {
            if (!e.target.closest('.finzo-dropdown')) {
                closeAllDropdowns();
            }
        });
    }

    function closeAllDropdowns() {
        document.querySelectorAll('.finzo-dropdown-trigger.is-open').forEach(el => el.classList.remove('is-open'));
        document.querySelectorAll('.finzo-dropdown-menu.is-open').forEach(el => el.classList.remove('is-open'));
    }

    if (!window.closeAllDropdowns) window.closeAllDropdowns = closeAllDropdowns;

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDropdowns);
    } else {
        initDropdowns();
    }
})();