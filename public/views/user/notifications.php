<?php
$portal = 'user';
$pageTitle = 'Notifications';
ob_start();
?>
<div class="flex justify-between items-end mb-8 ds-animate-fade">
    <div>
      <div class="text-sm font-bold text-text-muted uppercase tracking-wider mb-1">Communication Center</div>
      <h1 class="font-display text-3xl font-extrabold text-text-main tracking-tight">Activity Feed</h1>
      <p class="text-text-muted mt-1">Unified broadcast from the Finzo ecosystem.</p>
    </div>
    <button data-action="markAllRead()" class="btn btn-secondary btn-sm no-print mb-1">Mark all as read</button>
  </div>

  <div class="flex flex-col lg:flex-row gap-8">
    <!-- LEFT: FILTERS SIDEBAR -->
    <div class="w-full lg:w-1/4 ds-animate-slide-up">
      <div class="ds-card sticky top-24">
        <div class="ds-card-body p-6">
          <div class="text-xs font-bold text-text-muted uppercase tracking-widest mb-4">Signal Distribution</div>
          
          <!-- UNREAD ALERTS BUBBLE -->
          <div class="p-4 rounded-xl bg-primary-surface border border-primary-base/20 mb-6 flex flex-col items-center justify-center text-center">
            <div class="text-xs font-bold text-primary-base-base uppercase tracking-widest mb-1">Unread Alerts</div>
            <div class="font-display text-4xl font-black text-primary-base-base" id="unread-stat">0</div>
          </div>

          <!-- FILTERS -->
          <div class="flex flex-col gap-2">
            <button class="filter-item w-full flex items-center justify-between p-3 rounded-lg font-bold text-sm transition-colors text-primary-base-base bg-primary-surface" data-action="filterNotifs(&#039;all&#039;,this)">
              <div class="flex items-center gap-3">
                <span class="material-symbols-outlined text-lg" aria-hidden="true">grid_view</span> All Broadcasts
              </div>
              <span class="text-xs py-0.5 px-2 rounded-full bg-white/50 border border-primary-base/10" id="count-all">0</span>
            </button>
            
            <button class="filter-item w-full flex items-center justify-between p-3 rounded-lg font-bold text-sm transition-colors text-text-muted hover:bg-surface-subtle hover:text-text-main" data-action="filterNotifs(&#039;transaction&#039;,this)">
              <div class="flex items-center gap-3">
                <span class="material-symbols-outlined text-lg" aria-hidden="true">payments</span> Transactions
              </div>
              <span class="text-xs py-0.5 px-2 rounded-full bg-border-main/50" id="count-transaction">0</span>
            </button>
            
            <button class="filter-item w-full flex items-center justify-between p-3 rounded-lg font-bold text-sm transition-colors text-text-muted hover:bg-surface-subtle hover:text-text-main" data-action="filterNotifs(&#039;security&#039;,this)">
              <div class="flex items-center gap-3">
                <span class="material-symbols-outlined text-lg" aria-hidden="true">security</span> Security
              </div>
              <span class="text-xs py-0.5 px-2 rounded-full bg-border-main/50" id="count-security">0</span>
            </button>
            
            <button class="filter-item w-full flex items-center justify-between p-3 rounded-lg font-bold text-sm transition-colors text-text-muted hover:bg-surface-subtle hover:text-text-main" data-action="filterNotifs(&#039;system&#039;,this)">
              <div class="flex items-center gap-3">
                <span class="material-symbols-outlined text-lg" aria-hidden="true">settings</span> System
              </div>
              <span class="text-xs py-0.5 px-2 rounded-full bg-border-main/50" id="count-system">0</span>
            </button>
          </div>

          <div class="mt-8 pt-6 border-t border-dashed border-border-main">
            <button data-action="clearAll()" class="w-full flex items-center justify-center gap-2 p-3 text-sm font-bold text-danger-base hover:bg-danger-surface rounded-lg transition-colors">
              <span class="material-symbols-outlined" aria-hidden="true">delete_sweep</span> Delete All Records
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- RIGHT: NOTIFICATION FEED -->
    <div class="w-full lg:w-3/4 ds-animate-slide-up delay-100">
      <div class="ds-card min-h-[400px]">
        <div id="notif-container" class="flex flex-col">
          <!-- Populated by JS -->
        </div>
      </div>
    </div>
  </div>
</div>

<!-- View Notification Modal -->
<div id="viewNotifModal" class="modal-overlay hidden fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4" role="dialog" aria-modal="true">
  <div class="ds-card max-w-md w-full p-6 animate-in zoom-in-95 duration-200">
    <div id="viewNotifIcon" class="w-16 h-16 mx-auto rounded-2xl flex items-center justify-center mb-4"></div>
    <div id="viewNotifTitle" class="font-display text-xl font-bold text-center text-text-main mb-1">Broadcast Subject</div>
    <div id="viewNotifDate" class="text-center text-xs font-bold text-text-muted uppercase tracking-wider mb-6">TIMESTAMP</div>
    
    <div id="viewNotifBody" class="p-4 rounded-xl bg-surface-subtle border border-border-main text-sm text-text-main leading-relaxed mb-6">
      Message body content.
    </div>
    
    <div class="flex gap-4">
      <button class="btn btn-secondary flex-1" data-action="closeModal(&#039;viewNotifModal&#039;)">Read</button>
      <button class="btn btn-danger flex-1" data-action="deleteCurrentNotif()">Delete Record</button>
    </div>
  </div>
</div>


<script defer src="/Finzo/public/assets/js/core/utils.js"></script>
  <script defer src="/Finzo/public/assets/js/core/api.js"></script>
<script defer src="/Finzo/public/assets/js/user/notifications.js"></script>
<?php
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>

