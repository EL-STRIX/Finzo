(async function() {
  await fetchUserSession();
  const s = UserState.status;
  if (s === 'active') { window.location.href = 'dashboard.php'; return; }
  if (s === 'pending') { window.location.href = 'complete-profile.php'; return; }
  if (s === 'under_review') { window.location.href = 'account-pending.php'; return; }
  
  const reason = UserState.rejectionReason;
  if (reason) {
    const reasonEl = document.getElementById('tl-rejection-reason');
    if (reasonEl) reasonEl.textContent = reason;
  }
  
  const rejectedAt = UserState.rejectedAt;
  if (rejectedAt) {
    const timeEl = document.getElementById('tl-rejection-time');
    if (timeEl) {
      const d = new Date(rejectedAt);
      const formatted = d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) + ' at ' + d.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
      timeEl.textContent = formatted;
    }
  }
  
  document.querySelector('.page-wrap').classList.add('ready');
})();
