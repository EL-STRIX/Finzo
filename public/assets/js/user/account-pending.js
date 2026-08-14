(async function() {
  await fetchUserSession();
  const s = UserState.status;
  if (s === 'active') { window.location.href = 'dashboard.php'; return; }
  if (s === 'pending') { window.location.href = 'complete-profile.php'; return; }
  if (s === 'rejected') { window.location.href = 'account-rejected.php'; return; }
  
  // Status is under_review — reveal the page
  const emailEl = document.getElementById('user-email-show');
  if (emailEl && UserState.email) emailEl.textContent = UserState.email;

  if (UserState.pastRejections && UserState.pastRejections.length > 0) {
    const timeline = document.querySelector('.timeline');
    let htmlToInsert = '';
    
    // Convert connector of step 2 to error
    const connector = timeline.children[1].querySelector('.tl-connector');
    if(connector) connector.className = 'w-0.5 flex-1 min-h-[2rem] bg-gradient-to-b from-success-base to-danger-base my-1 tl-connector error';
    
    UserState.pastRejections.forEach((rej, index) => {
      const d = new Date(rej.rejected_at);
      const formatted = d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) + ' at ' + d.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
      const isLast = index === UserState.pastRejections.length - 1;
      const nextConnectorClass = isLast ? 'bg-gradient-to-b from-success-base to-info-base' : 'bg-gradient-to-b from-success-base to-danger-base';
      
      htmlToInsert += `
        <div class="flex gap-4">
          <div class="flex flex-col items-center">
            <div class="w-9 h-9 rounded-full bg-danger-surface border-2 border-danger-base/30 flex items-center justify-center shrink-0">
              <span class="material-symbols-outlined text-danger-base text-lg">cancel</span>
            </div>
            <div class="w-0.5 flex-1 min-h-[2rem] bg-success-base my-1"></div>
          </div>
          <div class="pb-6 flex-1">
            <div class="text-sm font-bold text-danger-base">Application Rejected</div>
            <div class="text-xs text-[var(--color-text-main)] mt-2 p-3 bg-danger-surface/40 border border-danger-base/20 rounded-lg leading-relaxed">${rej.reason}</div>
            <div class="text-[10px] font-bold text-[var(--color-text-disabled)] uppercase tracking-widest mt-2">${formatted}</div>
          </div>
        </div>
        <div class="flex gap-4">
          <div class="flex flex-col items-center">
            <div class="w-9 h-9 rounded-full bg-success-surface flex items-center justify-center shrink-0">
              <span class="material-symbols-outlined text-success-base text-lg">refresh</span>
            </div>
            <div class="w-0.5 flex-1 min-h-[2rem] ${nextConnectorClass} my-1"></div>
          </div>
          <div class="pb-6 flex-1">
            <div class="text-sm font-bold text-[var(--color-text-main)]">Profile Resubmitted</div>
            <div class="text-xs text-[var(--color-text-muted)] mt-0.5">Details updated successfully</div>
            <div class="text-[10px] font-bold text-[var(--color-text-disabled)] uppercase tracking-widest mt-1.5">Recent</div>
          </div>
        </div>
      `;
    });
    
    timeline.children[1].insertAdjacentHTML('afterend', htmlToInsert);
  }

  document.querySelector('.page-wrap').classList.add('ready');
})();
