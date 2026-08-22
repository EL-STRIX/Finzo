(async function initPage() {
    await fetchUserSession();
    buildSidebar('notifications'); buildNavbar('Activity', 'Notifications');
  })();
  let activeFilter = 'all';
  let notifs = [];

  async function loadNotifications() {
    try {
      const res = await fetch('/Finzo/api/user/notifications.php', { credentials: 'same-origin' });
      const data = await res.json();
      if (data.success && data.notifications) {
        notifs = data.notifications.map((n, i) => ({
          id: n.id || i + 1,
          type: n.type || 'system',
          icon: n.icon || 'notifications',
          iconBg: n.icon_bg || 'bg-info-surface',
          iconColor: n.icon_color || 'text-info-base',
          title: n.title,
          body: n.body || n.message,
          time: n.created_at ? new Date(n.created_at).toLocaleString('en-IN', {day:'numeric',month:'short',year:'numeric', hour:'2-digit', minute:'2-digit', hour12:true}) : 'Recently',
          unread: !n.is_read
        }));
      }
    } catch (e) { console.warn('Failed to load notifications:', e); }
    renderNotifs();
  }

  function renderNotifs() {
    const filtered = notifs.filter(n => activeFilter === 'all' || n.type === activeFilter);
    const container = document.getElementById('notif-container');
    const unreadCount = notifs.filter(n => n.unread).length;
    
    document.getElementById('unread-stat').textContent = unreadCount;
    document.getElementById('count-all').textContent = notifs.length;
    document.getElementById('count-transaction').textContent = notifs.filter(n=>n.type==='transaction').length;
    document.getElementById('count-security').textContent = notifs.filter(n=>n.type==='security').length;
    document.getElementById('count-system').textContent = notifs.filter(n=>n.type==='system').length;

    if (filtered.length === 0) {
      container.innerHTML = `<div class="p-16 flex flex-col items-center justify-center text-center">
        <div class="w-16 h-16 rounded-full bg-[var(--color-surface-subtle)] text-[var(--color-text-disabled)] flex items-center justify-center mb-4">
          <span class="material-symbols-outlined text-3xl">notifications_off</span>
        </div>
        <h3 class="font-display text-lg font-bold text-[var(--color-text-main)] mb-1">Quiet Environment</h3>
        <p class="text-sm text-[var(--color-text-muted)]">No active broadcasts found for this segment.</p>
      </div>`;
      return;
    }

    container.innerHTML = filtered.map(n => {
      const isHtmlColor = n.iconBg.includes('rgba') || n.iconBg.includes('#');
      const bgStyle = isHtmlColor ? `style="background:${n.iconBg}"` : '';
      const bgClass = isHtmlColor ? '' : n.iconBg;
      
      const isHtmlText = n.iconColor.includes('rgba') || n.iconColor.includes('#');
      const textStyle = isHtmlText ? `style="color:${n.iconColor}"` : '';
      const textClass = isHtmlText ? '' : n.iconColor;
      
      const unreadBadge = n.unread ? `<span class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-8 bg-primary-base rounded-r-md"></span>` : '';
      const unreadBg = n.unread ? `bg-primary-surface/30` : `hover:bg-[var(--color-surface-subtle)]`;

      return `
      <div class="relative p-5 border-b border-[var(--color-border-main)] last:border-0 cursor-pointer transition-colors flex gap-4 ${unreadBg}" onclick="viewNotif(${n.id})">
        ${unreadBadge}
        <div class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0 ${bgClass}" ${bgStyle}>
          <span class="material-symbols-outlined text-xl ${textClass}" ${textStyle}>${n.icon}</span>
        </div>
        <div class="flex-1 min-w-0">
          <div class="flex justify-between items-start mb-1">
            <h4 class="font-bold text-[var(--color-text-main)] truncate pr-4 ${n.unread ? 'text-primary-base' : ''}">${n.title}</h4>
            <span class="text-[10px] font-bold text-[var(--color-text-muted)] uppercase tracking-wider shrink-0 mt-0.5">${n.time}</span>
          </div>
          <p class="text-sm text-[var(--color-text-secondary)] line-clamp-2 leading-relaxed">${n.body}</p>
        </div>
      </div>`;
    }).join('');
  }

  let currentViewNotifId = null;

  function viewNotif(id) {
    const n = notifs.find(x => x.id === id);
    if (n) {
      n.unread = false;
      currentViewNotifId = id;
      document.getElementById('viewNotifTitle').textContent = n.title;
      document.getElementById('viewNotifBody').textContent = n.body;
      document.getElementById('viewNotifDate').textContent = n.time;

      const iconContainer = document.getElementById('viewNotifIcon');
      const isHtmlColor = n.iconBg.includes('rgba') || n.iconBg.includes('#');
      const isHtmlText = n.iconColor.includes('rgba') || n.iconColor.includes('#');
      
      iconContainer.className = `w-16 h-16 mx-auto rounded-2xl flex items-center justify-center mb-4 ${isHtmlColor ? '' : n.iconBg}`;
      if (isHtmlColor) iconContainer.style.background = n.iconBg;
      else iconContainer.style.background = '';
      
      iconContainer.innerHTML = `<span class="material-symbols-outlined text-3xl ${isHtmlText ? '' : n.iconColor}" ${isHtmlText ? `style="color:${n.iconColor}"` : ''}>${n.icon}</span>`;

      fetch('/Finzo/api/user/notifications.php', { method: 'POST', headers: {'Content-Type':'application/x-www-form-urlencoded'}, credentials: 'same-origin', body: 'action=mark_read&notification_id=' + id }).catch(() => {});
      
      renderNotifs();
      
      const modal = document.getElementById('viewNotifModal');
      modal.classList.remove('hidden');
    }
  }
  
  function closeModal(id) {
    document.getElementById(id).classList.add('hidden');
  }

  function deleteCurrentNotif() {
    if (currentViewNotifId !== null) {
      fetch('/Finzo/api/user/notifications.php', { method: 'POST', headers: {'Content-Type':'application/x-www-form-urlencoded'}, credentials: 'same-origin', body: 'action=delete&notification_id=' + currentViewNotifId }).catch(() => {});
      notifs = notifs.filter(n => n.id !== currentViewNotifId);
      currentViewNotifId = null;
      renderNotifs();
      closeModal('viewNotifModal');
      showToast('Record deleted from ledger', 'success');
    }
  }

  function markAllRead() {
    fetch('/Finzo/api/user/notifications.php', { method: 'POST', headers: {'Content-Type':'application/x-www-form-urlencoded'}, credentials: 'same-origin', body: 'action=mark_all_read' }).catch(() => {});
    notifs.forEach(n => n.unread = false);
    renderNotifs();
    showToast('All broadcasts read', 'success');
  }

  function clearAll() {
    openConfirm('Delete All Records', 'This action will permanently remove all notifications from your signal ledger. Proceed?', () => {
      fetch('/Finzo/api/user/notifications.php', { method: 'POST', headers: {'Content-Type':'application/x-www-form-urlencoded'}, credentials: 'same-origin', body: 'action=clear_all' }).catch(() => {});
      notifs = [];
      renderNotifs();
      showToast('Activity feed cleared', 'success');
    }, false);
  }

  function filterNotifs(type, el) {
    activeFilter = type;
    
    document.querySelectorAll('.filter-item').forEach(b => {
      b.className = 'filter-item w-full flex items-center justify-between p-3 rounded-lg font-bold text-sm transition-colors text-[var(--color-text-muted)] hover:bg-[var(--color-surface-subtle)] hover:text-[var(--color-text-main)]';
      const badge = b.querySelector('span:last-child');
      badge.className = 'text-xs py-0.5 px-2 rounded-full bg-[var(--color-border-main)]/50';
    });
    
    el.className = 'filter-item w-full flex items-center justify-between p-3 rounded-lg font-bold text-sm transition-colors text-primary-base bg-primary-surface';
    el.querySelector('span:last-child').className = 'text-xs py-0.5 px-2 rounded-full bg-white/50 border border-primary-base/10';
    
    renderNotifs();
  }

  loadNotifications();
