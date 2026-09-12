(async function () {
  await fetchUserSession();
  const s = UserState.status;
  if (s === 'active')   { window.location.href = 'dashboard.php'; return; }
  if (s === 'pending')  { window.location.href = 'complete-profile.php'; return; }
  if (s === 'rejected') { window.location.href = 'account-rejected.php'; return; }

  // ─── Helpers ────────────────────────────────────────────────────────────────
  function fmtDate(iso) {
    if (!iso) return '—';
    const d = new Date(iso);
    if (isNaN(d)) return iso;
    return d.toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' }) +
           ' · ' +
           d.toLocaleTimeString('en-IN', { hour: '2-digit', minute: '2-digit', hour12: true });
  }

  // ─── Fill email ─────────────────────────────────────────────────────────────
  const emailEl = document.getElementById('user-email-show');
  if (emailEl && UserState.email) emailEl.textContent = UserState.email;

  // ─── Fetch full profile for precise timestamps ────────────────────────────
  let memberSince = UserState.memberSince || null;
  let kycSubmittedAt = null;
  try {
    let res;
    if (window.api && typeof window.api.get === 'function') {
      res = await window.api.get('/user/profile');
    } else {
      const r = await fetch('/Finzo/public/api/v1/user/profile', { credentials: 'same-origin' });
      res = await r.json();
    }
    if (res && res.success && res.user) {
      memberSince    = res.user.created_at    || memberSince;
      kycSubmittedAt = res.user.kyc_submitted_at || res.user.updated_at || null;
    }
  } catch (e) {
    console.warn('Could not fetch profile timestamps:', e);
  }

  // ─── Populate static timestamps ──────────────────────────────────────────
  const tsReg  = document.getElementById('ts-registered');
  const tsKyc  = document.getElementById('ts-kyc-submitted');
  const tsSub  = document.getElementById('kyc-submitted-display');

  if (tsReg)  tsReg.textContent  = fmtDate(memberSince);
  if (tsKyc)  tsKyc.textContent  = fmtDate(kycSubmittedAt);
  if (tsSub)  tsSub.textContent  = fmtDate(kycSubmittedAt);

  // ─── Inject past rejection history ───────────────────────────────────────
  const rejectionSlot = document.getElementById('rejection-history');
  const rejections = UserState.pastRejections || [];

  if (rejections.length > 0 && rejectionSlot) {
    let html = '';
    rejections.forEach((rej) => {
      const when = fmtDate(rej.rejected_at);
      html += `
        <div class="tl-entry flex gap-4 relative pb-6">
          <div class="w-9 h-9 rounded-full bg-rose-500/10 border-2 border-rose-500/30 flex items-center justify-center shrink-0 z-10">
            <span class="material-symbols-outlined text-rose-500 text-base">cancel</span>
          </div>
          <div class="flex-1 pt-1">
            <div class="flex items-start justify-between gap-2">
              <div>
                <div class="text-sm font-bold text-rose-500">Application Rejected</div>
                <div class="text-xs text-[var(--color-text-main)] mt-2 p-2.5 bg-rose-500/5 border border-rose-500/15 rounded-lg leading-relaxed">${rej.reason || 'No reason provided.'}</div>
              </div>
              <div class="text-[10px] font-bold text-text-disabled uppercase tracking-widest shrink-0 text-right mt-0.5">${when}</div>
            </div>
          </div>
        </div>
        <div class="tl-entry flex gap-4 relative pb-6">
          <div class="w-9 h-9 rounded-full bg-emerald-500/10 border-2 border-emerald-500/30 flex items-center justify-center shrink-0 z-10">
            <span class="material-symbols-outlined text-emerald-500 text-base">refresh</span>
          </div>
          <div class="flex-1 pt-1">
            <div class="text-sm font-bold text-text-main">Profile Resubmitted</div>
            <div class="text-xs text-text-muted mt-0.5">Updated details and documents submitted for re-review.</div>
            <div class="text-[10px] font-bold text-text-disabled uppercase tracking-widest mt-1.5">${fmtDate(kycSubmittedAt)}</div>
          </div>
        </div>
      `;
    });
    rejectionSlot.innerHTML = html;
  }

  document.querySelector('.page-wrap')?.classList.add('ready');
})();
