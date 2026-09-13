<?php
$portal = 'admin';
$pageTitle = 'Finzo Admin — Helpdesk & Customer Support';
include __DIR__ . '/_guard.php';

$extraScripts = '<script defer src="/Finzo/public/assets/js/admin/admin-support.js?v=' . time() . '"></script>';

ob_start();
?>

<!-- TOP PAGE HEADER -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6 relative z-10">
    <div>
        <div class="flex items-center gap-2 mb-1">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-primary/10 text-primary border border-primary/20">
                <span class="w-1.5 h-1.5 rounded-full bg-primary animate-pulse"></span>
                Operations Live
            </span>
            <span class="text-text-muted text-xs font-mono">Module: Helpdesk &amp; Disputes</span>
        </div>
        <h1 class="text-2xl font-headline font-black text-text-primary tracking-tight">Helpdesk &amp; Dispute Management</h1>
        <p class="text-xs font-bold text-text-secondary uppercase tracking-widest mt-1">Customer Support Orchestration, SLA Routing &amp; Dispute Resolution</p>
    </div>
    <div class="flex items-center gap-3">
        <button id="btn-refresh-tickets" class="btn btn-secondary !px-4 flex items-center gap-2" onclick="loadSupportTickets()">
            <span class="material-symbols-outlined text-[18px]">refresh</span>
            <span class="font-bold text-xs">Refresh Queue</span>
        </button>
    </div>
</div>

<!-- KPI METRIC CARDS -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="ds-card p-5 border border-border-subtle bg-surface flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-primary/10 text-primary flex items-center justify-center shrink-0">
            <span class="material-symbols-outlined text-2xl">confirmation_number</span>
        </div>
        <div>
            <div class="text-[11px] font-bold text-text-secondary uppercase tracking-wider">Total Inquiries</div>
            <div id="stat-total" class="text-2xl font-headline font-black text-text-primary mt-0.5">0</div>
        </div>
    </div>

    <div class="ds-card p-5 border border-border-subtle bg-surface flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-amber-500/10 text-amber-500 flex items-center justify-center shrink-0">
            <span class="material-symbols-outlined text-2xl">pending</span>
        </div>
        <div>
            <div class="text-[11px] font-bold text-text-secondary uppercase tracking-wider">Open Tickets</div>
            <div id="stat-open" class="text-2xl font-headline font-black text-amber-500 mt-0.5">0</div>
        </div>
    </div>

    <div class="ds-card p-5 border border-border-subtle bg-surface flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-indigo-500/10 text-indigo-400 flex items-center justify-center shrink-0">
            <span class="material-symbols-outlined text-2xl">sync</span>
        </div>
        <div>
            <div class="text-[11px] font-bold text-text-secondary uppercase tracking-wider">In Progress</div>
            <div id="stat-in-progress" class="text-2xl font-headline font-black text-indigo-400 mt-0.5">0</div>
        </div>
    </div>

    <div class="ds-card p-5 border border-border-subtle bg-surface flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center shrink-0">
            <span class="material-symbols-outlined text-2xl">task_alt</span>
        </div>
        <div>
            <div class="text-[11px] font-bold text-text-secondary uppercase tracking-wider">Resolved</div>
            <div id="stat-resolved" class="text-2xl font-headline font-black text-emerald-400 mt-0.5">0</div>
        </div>
    </div>
</div>

<!-- MAIN TICKETS TABLE CARD -->
<div class="ds-card p-6 border border-border-subtle mb-8 bg-surface">
    <!-- Queue Header & Filter Tabs -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6 border-b border-border-subtle pb-5">
        <div>
            <h3 class="font-headline font-bold text-lg text-text-primary">Support Inquiries &amp; Disputes Queue</h3>
            <p class="text-xs text-text-secondary mt-0.5">Live queue of tickets submitted by registered Finzo account holders</p>
        </div>

        <!-- Status Filter Tabs -->
        <div class="flex items-center gap-1.5 p-1 bg-background rounded-xl border border-border-subtle overflow-x-auto">
            <button class="status-tab-btn active-tab px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all text-primary bg-primary/10 border border-primary/30 flex items-center gap-1.5" data-status="all">
                <span>All</span>
                <span id="tab-count-all" class="px-1.5 py-0.2 rounded-full text-[10px] bg-primary/20 text-primary font-mono">0</span>
            </button>
            <button class="status-tab-btn px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all text-text-secondary border border-transparent hover:text-text-primary flex items-center gap-1.5" data-status="open">
                <span>Open</span>
                <span id="tab-count-open" class="px-1.5 py-0.2 rounded-full text-[10px] bg-amber-500/20 text-amber-500 font-mono">0</span>
            </button>
            <button class="status-tab-btn px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all text-text-secondary border border-transparent hover:text-text-primary flex items-center gap-1.5" data-status="in_progress">
                <span>In Progress</span>
                <span id="tab-count-in-progress" class="px-1.5 py-0.2 rounded-full text-[10px] bg-indigo-500/20 text-indigo-400 font-mono">0</span>
            </button>
            <button class="status-tab-btn px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all text-text-secondary border border-transparent hover:text-text-primary flex items-center gap-1.5" data-status="resolved">
                <span>Resolved</span>
                <span id="tab-count-resolved" class="px-1.5 py-0.2 rounded-full text-[10px] bg-emerald-500/20 text-emerald-400 font-mono">0</span>
            </button>
            <button class="status-tab-btn px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all text-text-secondary border border-transparent hover:text-text-primary flex items-center gap-1.5" data-status="closed">
                <span>Closed</span>
                <span id="tab-count-closed" class="px-1.5 py-0.2 rounded-full text-[10px] bg-text-secondary/20 text-text-secondary font-mono">0</span>
            </button>
        </div>
    </div>

    <!-- Search & Priority Filters -->
    <div class="flex flex-col sm:flex-row items-center gap-3 mb-5">
        <div class="relative flex-1 w-full">
            <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-text-muted text-[20px]">search</span>
            <input type="text" id="search-ticket-input" class="fin-input w-full pl-10 pr-4 py-2 text-xs" placeholder="Search by ticket #, customer name, customer ID, or subject..." oninput="handleSearchInput()" />
        </div>
        <div class="w-full sm:w-56 relative z-10">
            <div class="finzo-dropdown relative w-full" id="priority-filter-dropdown">
                <input type="hidden" id="filter-priority-select" value="all">
                <div class="finzo-dropdown-trigger fin-input !flex items-center justify-between cursor-pointer w-full !py-2 !h-[42px] text-xs">
                    <span class="dropdown-label font-bold text-xs text-text-primary">All Priorities</span>
                    <span class="material-symbols-outlined chevron transition-transform duration-300 text-text-secondary text-[18px]" aria-hidden="true">expand_more</span>
                </div>
                <div class="finzo-dropdown-menu !z-[1000] !w-full">
                    <div class="finzo-dropdown-option is-selected text-xs" data-value="all">All Priorities</div>
                    <div class="finzo-dropdown-option text-xs" data-value="urgent">Urgent</div>
                    <div class="finzo-dropdown-option text-xs" data-value="high">High Priority</div>
                    <div class="finzo-dropdown-option text-xs" data-value="medium">Medium Priority</div>
                    <div class="finzo-dropdown-option text-xs" data-value="low">Low Priority</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tickets Table -->
    <div class="overflow-x-auto rounded-xl border border-border-subtle">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-border-subtle text-[11px] font-extrabold uppercase text-text-secondary tracking-wider bg-background/60">
                    <th class="px-4 py-3.5">Ticket No</th>
                    <th class="px-4 py-3.5">Customer</th>
                    <th class="px-4 py-3.5">Category &amp; Subject</th>
                    <th class="px-4 py-3.5">Priority</th>
                    <th class="px-4 py-3.5">Status</th>
                    <th class="px-4 py-3.5">Updated</th>
                    <th class="px-4 py-3.5 text-right">Action</th>
                </tr>
            </thead>
            <tbody id="tickets-table-body" class="divide-y divide-border-subtle/50 bg-surface">
                <!-- Dynamic ticket rows injected via admin-support.js -->
            </tbody>
        </table>
    </div>
</div>

<!-- TICKET DETAIL & CONVERSATION MODAL / DRAWER -->
<div id="ticket-modal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="ds-card max-w-3xl w-full p-6 bg-surface border border-border-subtle max-h-[92vh] flex flex-col shadow-2xl animate-in fade-in zoom-in-95 duration-200">
        
        <!-- Modal Top Bar -->
        <div class="flex items-start justify-between border-b border-border-subtle pb-4 mb-4">
            <div class="flex-1 pr-4">
                <div class="flex items-center gap-2 mb-1">
                    <span id="modal-ticket-no" class="font-mono text-xs font-bold px-2 py-0.5 rounded bg-primary/10 text-primary border border-primary/20">TICK000000</span>
                    <span id="modal-priority-badge"></span>
                    <span id="modal-status-badge"></span>
                </div>
                <h3 id="modal-ticket-subject" class="font-headline font-bold text-lg text-text-primary line-clamp-1">Ticket Subject</h3>
                <div id="modal-customer-info" class="text-xs text-text-secondary mt-1 flex flex-wrap items-center gap-2">
                    <!-- Injected customer telemetry -->
                </div>
            </div>
            <button onclick="closeTicketModal()" class="w-8 h-8 rounded-full hover:bg-surface-elevated flex items-center justify-center text-text-secondary transition-colors shrink-0">
                <span class="material-symbols-outlined text-lg">close</span>
            </button>
        </div>

        <!-- Ticket Quick Action Toolbar -->
        <div class="flex items-center justify-between gap-3 p-3 rounded-xl bg-background border border-border-subtle mb-4 relative z-30">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-text-muted text-sm">tune</span>
                <span class="text-xs font-bold text-text-secondary uppercase tracking-wider">Update Status:</span>
            </div>
            <div class="w-44 relative">
                <div class="finzo-dropdown relative w-full" id="quick-status-dropdown">
                    <input type="hidden" id="modal-quick-status" value="open">
                    <div class="finzo-dropdown-trigger fin-input !flex items-center justify-between cursor-pointer w-full !py-1.5 !h-[38px] text-xs font-bold">
                        <span class="dropdown-label text-xs font-bold text-text-primary">Open</span>
                        <span class="material-symbols-outlined chevron transition-transform duration-300 text-text-secondary text-[18px]" aria-hidden="true">expand_more</span>
                    </div>
                    <div class="finzo-dropdown-menu !z-[3500] !w-full">
                        <div class="finzo-dropdown-option is-selected text-xs font-bold" data-value="open">Open</div>
                        <div class="finzo-dropdown-option text-xs font-bold" data-value="in_progress">In Progress</div>
                        <div class="finzo-dropdown-option text-xs font-bold" data-value="resolved">Resolved</div>
                        <div class="finzo-dropdown-option text-xs font-bold" data-value="closed">Closed</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Message Thread Box -->
        <div id="modal-messages-container" class="flex-1 overflow-y-auto custom-scrollbar space-y-4 p-3 mb-4 max-h-[46vh] rounded-xl bg-background/40 border border-border-subtle/60">
            <!-- Messages dynamically loaded -->
        </div>

        <!-- Reply Composer -->
        <form id="admin-reply-form" onsubmit="submitAdminReply(event)" class="border-t border-border-subtle pt-4 space-y-3">
            <input type="hidden" id="reply-ticket-id" value="" />
            <div>
                <textarea id="reply-message-input" class="fin-input w-full text-xs" rows="3" required placeholder="Type your official response to the customer..."></textarea>
            </div>
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
                <label class="flex items-center gap-2 cursor-pointer text-xs text-text-secondary select-none">
                    <input type="checkbox" id="reply-mark-resolved" class="rounded border-border-subtle text-primary focus:ring-primary/20" />
                    <span>Mark ticket as <strong class="text-emerald-500">Resolved</strong> upon sending reply</span>
                </label>
                <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                    <button type="button" onclick="closeTicketModal()" class="btn btn-secondary !py-2 !px-4 text-xs">
                        Close
                    </button>
                    <button type="submit" id="btn-submit-reply" class="btn btn-primary !py-2 !px-5 text-xs flex items-center gap-1.5 shadow-md hover:shadow-primary/20">
                        <span class="material-symbols-outlined text-sm">send</span>
                        <span>Send Official Reply</span>
                    </button>
                </div>
            </div>
        </form>

    </div>
</div>

<?php
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
