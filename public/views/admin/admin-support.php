<?php
$portal = 'admin';
$pageTitle = 'Finzo Admin — Helpdesk & Support (Coming Soon)';
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
                In Development
            </span>
            <span class="text-text-muted text-xs font-mono">Module: Customer Operations 2.0</span>
        </div>
        <h1 class="text-2xl font-headline font-black text-text-primary tracking-tight">Enterprise Helpdesk &amp; Dispute Resolution</h1>
        <p class="text-xs font-bold text-text-secondary uppercase tracking-widest mt-1">Customer Support Orchestration, SLA Routing &amp; Financial Dispute Investigation</p>
    </div>
    <div class="flex items-center gap-3">
        <button id="btn-notify-support" class="btn btn-secondary !px-4 flex items-center gap-2">
            <span class="material-symbols-outlined text-[18px]">notifications_active</span>
            <span class="font-bold text-xs">Notify on Launch</span>
        </button>
    </div>
</div>

<!-- COMING SOON HERO BANNER -->
<div class="ds-card p-8 relative overflow-hidden shadow-2xl mb-8 border border-border-subtle bg-gradient-to-br from-surface via-surface to-primary/5">
    <div class="absolute -right-24 -top-24 w-80 h-80 bg-primary/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -left-20 -bottom-20 w-72 h-72 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="relative z-10 max-w-3xl">
        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-primary/20 to-primary/5 text-primary flex items-center justify-center mb-6 border border-primary/20 shadow-lg">
            <span class="material-symbols-outlined text-3xl">support_agent</span>
        </div>

        <h2 class="text-3xl font-display font-black text-text-primary tracking-tight mb-3">
            Centralized Banking Helpdesk &amp; Dispute Management Engine
        </h2>
        <p class="text-text-secondary text-sm leading-relaxed mb-6">
            The enterprise-grade administrative support and financial dispute resolution workstation is undergoing active development. This console will equip Finzo support officers and tier-2 risk analysts with real-time customer conversation streams, automated SLA escalation queues, chargeback evidence workflows, and dual-custody card unlock controls.
        </p>

        <div class="flex flex-wrap items-center gap-3">
            <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-background/80 border border-border-subtle text-xs font-semibold text-text-secondary">
                <span class="material-symbols-outlined text-emerald-500 text-sm">check_circle</span>
                <span>Database Schemas Ready (<code class="text-text-primary font-mono text-[11px]">support_tickets</code>, <code class="text-text-primary font-mono text-[11px]">support_ticket_messages</code>)</span>
            </div>
            <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-background/80 border border-border-subtle text-xs font-semibold text-text-secondary">
                <span class="material-symbols-outlined text-primary text-sm">sync</span>
                <span>User Portal Support Active &amp; Integrated</span>
            </div>
            <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-background/80 border border-border-subtle text-xs font-semibold text-text-secondary">
                <span class="material-symbols-outlined text-amber-500 text-sm">schedule</span>
                <span>Admin Suite Target: Q4 2026</span>
            </div>
        </div>
    </div>
</div>

<!-- PLANNED CAPABILITIES GRID -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="ds-card p-6 border border-border-subtle hover:border-primary/40 transition-all group">
        <div class="w-12 h-12 rounded-xl bg-primary/10 text-primary flex items-center justify-center mb-4 group-hover:scale-105 transition-transform">
            <span class="material-symbols-outlined text-2xl">confirmation_number</span>
        </div>
        <h3 class="font-headline font-bold text-base text-text-primary mb-2">Omnichannel Queue &amp; SLA</h3>
        <p class="text-xs text-text-secondary leading-relaxed">
            Automated ticket categorization, priority-based triage (Urgent, High, Medium, Low), and strict SLA timers with automated reassignment for delayed inquiries.
        </p>
    </div>

    <div class="ds-card p-6 border border-border-subtle hover:border-accent/40 transition-all group">
        <div class="w-12 h-12 rounded-xl bg-accent/10 text-accent flex items-center justify-center mb-4 group-hover:scale-105 transition-transform">
            <span class="material-symbols-outlined text-2xl">gavel</span>
        </div>
        <h3 class="font-headline font-bold text-base text-text-primary mb-2">Dispute &amp; Chargeback Hub</h3>
        <p class="text-xs text-text-secondary leading-relaxed">
            One-click linkage to customer ledger transactions, merchant dispute documentation uploads, provisional credit issuance, and payment network arbitration.
        </p>
    </div>

    <div class="ds-card p-6 border border-border-subtle hover:border-emerald-500/40 transition-all group">
        <div class="w-12 h-12 rounded-xl bg-emerald-500/10 text-emerald-500 flex items-center justify-center mb-4 group-hover:scale-105 transition-transform">
            <span class="material-symbols-outlined text-2xl">forum</span>
        </div>
        <h3 class="font-headline font-bold text-base text-text-primary mb-2">Dual-Agent Collaborative Chat</h3>
        <p class="text-xs text-text-secondary leading-relaxed">
            Seamless multi-agent conversation threads, internal private notes invisible to customers, canned regulatory disclaimers, and instant audit trails.
        </p>
    </div>

    <div class="ds-card p-6 border border-border-subtle hover:border-rose-500/40 transition-all group">
        <div class="w-12 h-12 rounded-xl bg-rose-500/10 text-rose-500 flex items-center justify-center mb-4 group-hover:scale-105 transition-transform">
            <span class="material-symbols-outlined text-2xl">security</span>
        </div>
        <h3 class="font-headline font-bold text-base text-text-primary mb-2">Fraud Telemetry &amp; Lockout</h3>
        <p class="text-xs text-text-secondary leading-relaxed">
            Real-time fraud probability scores computed from device footprints, IP velocity, and direct links to emergency card freezes or account unlock workflows.
        </p>
    </div>
</div>

<!-- INTERACTIVE PROTOTYPE CONSOLE (PREVIEW WORKSTATION) -->
<div class="ds-card p-6 border border-border-subtle mb-8 bg-surface/90">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6 border-b border-border-subtle pb-4">
        <div>
            <div class="flex items-center gap-2">
                <h3 class="font-headline font-bold text-lg text-text-primary">Live Triage Console Preview</h3>
                <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-accent/10 text-accent border border-accent/20">Interactive Mockup</span>
            </div>
            <p class="text-xs text-text-secondary mt-0.5">Explore the future administrative support queue interface and message inspect drawers</p>
        </div>

        <!-- Filter Tabs -->
        <div class="flex items-center gap-1.5 p-1 bg-background rounded-lg border border-border-subtle overflow-x-auto">
            <button class="filter-tab-btn active-tab px-3 py-1.5 rounded-md text-xs font-bold transition-all text-primary bg-primary/10 border border-primary" data-status="all">
                All Tickets
            </button>
            <button class="filter-tab-btn px-3 py-1.5 rounded-md text-xs font-bold transition-all text-text-secondary border border-transparent hover:text-text-primary" data-status="open">
                Open (1)
            </button>
            <button class="filter-tab-btn px-3 py-1.5 rounded-md text-xs font-bold transition-all text-text-secondary border border-transparent hover:text-text-primary" data-status="in_progress">
                In Progress (2)
            </button>
            <button class="filter-tab-btn px-3 py-1.5 rounded-md text-xs font-bold transition-all text-text-secondary border border-transparent hover:text-text-primary" data-status="resolved">
                Resolved (1)
            </button>
        </div>
    </div>

    <!-- Ticket Table -->
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-border-subtle text-[11px] font-extrabold uppercase text-text-secondary tracking-wider bg-background/50">
                    <th class="px-4 py-3">Ticket No</th>
                    <th class="px-4 py-3">Customer</th>
                    <th class="px-4 py-3">Subject &amp; Category</th>
                    <th class="px-4 py-3">Priority</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Action</th>
                </tr>
            </thead>
            <tbody id="preview-tickets-body" class="divide-y divide-border-subtle/40">
                <!-- Injected via admin-support.js -->
            </tbody>
        </table>
    </div>
</div>

<!-- ARCHITECTURE BLUEPRINT FOR FUTURE IMPLEMENTATION -->
<div class="ds-card p-6 border border-border-subtle bg-surface/90 mb-8">
    <div class="flex items-center gap-3 mb-4 pb-4 border-b border-border-subtle">
        <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
            <span class="material-symbols-outlined text-xl">developer_board</span>
        </div>
        <div>
            <h3 class="font-headline font-bold text-base text-text-primary">Architectural Blueprint &amp; Implementation Contract</h3>
            <p class="text-xs text-text-secondary">Clean architecture design specification for implementing the backend admin support services</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 text-xs">
        <div class="space-y-3">
            <div class="font-bold text-text-primary uppercase tracking-wider text-[11px]">Planned REST API Contracts</div>
            <div class="p-3 rounded-lg bg-background border border-border-subtle font-mono text-[11px] space-y-1.5">
                <div class="text-emerald-500"><span class="font-bold">GET</span> /api/v1/admin/support/tickets <span class="text-text-muted">(List &amp; filter tickets with user metadata)</span></div>
                <div class="text-emerald-500"><span class="font-bold">GET</span> /api/v1/admin/support/tickets/{id} <span class="text-text-muted">(Fetch full thread &amp; linked transaction details)</span></div>
                <div class="text-accent"><span class="font-bold">POST</span> /api/v1/admin/support/tickets/{id}/reply <span class="text-text-muted">(Post official response as admin/staff)</span></div>
                <div class="text-purple-500"><span class="font-bold">PATCH</span> /api/v1/admin/support/tickets/{id}/status <span class="text-text-muted">(Update status: in_progress, resolved, closed)</span></div>
                <div class="text-amber-500"><span class="font-bold">POST</span> /api/v1/admin/support/tickets/{id}/assign <span class="text-text-muted">(Assign ticket to internal support officer)</span></div>
            </div>
        </div>

        <div class="space-y-3">
            <div class="font-bold text-text-primary uppercase tracking-wider text-[11px]">Decoupled Architecture Components</div>
            <ul class="space-y-2 text-text-secondary">
                <li class="flex items-start gap-2">
                    <span class="material-symbols-outlined text-text-muted text-[14px] mt-0.5">chevron_right</span>
                    <div><strong>Controller:</strong> <code class="text-text-primary">App\Presentation\Api\V1\Admin\AdminSupportController</code> (Thin controller handling pagination, filters, and JSON response).</div>
                </li>
                <li class="flex items-start gap-2">
                    <span class="material-symbols-outlined text-text-muted text-[14px] mt-0.5">chevron_right</span>
                    <div><strong>Service:</strong> <code class="text-text-primary">App\Application\Services\AdminSupportService</code> implementing <code class="text-text-primary">AdminSupportServiceInterface</code> (SLA computation, fraud heuristics, audit events).</div>
                </li>
                <li class="flex items-start gap-2">
                    <span class="material-symbols-outlined text-text-muted text-[14px] mt-0.5">chevron_right</span>
                    <div><strong>Repository:</strong> <code class="text-text-primary">App\Infrastructure\Repositories\AdminSupportRepository</code> targeting active MySQL tables <code class="text-text-primary">support_tickets</code> and <code class="text-text-primary">support_ticket_messages</code>.</div>
                </li>
            </ul>
        </div>
    </div>
</div>

<!-- PREVIEW MODAL / CONVERSATION THREAD DRAWER -->
<div id="preview-ticket-modal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="ds-card max-w-2xl w-full p-6 bg-surface border border-border-subtle max-h-[90vh] flex flex-col shadow-2xl">
        <div class="flex items-center justify-between border-b border-border-subtle pb-4 mb-4">
            <div>
                <h3 class="font-headline font-bold text-lg text-text-primary" id="preview-modal-title">Ticket Details</h3>
                <p class="text-xs text-text-secondary mt-0.5" id="preview-modal-sub">Ticket Conversation Preview</p>
            </div>
            <button onclick="window.closeMockTicketModal()" class="w-8 h-8 rounded-full hover:bg-surface-elevated flex items-center justify-center text-text-secondary">
                <span class="material-symbols-outlined text-lg">close</span>
            </button>
        </div>

        <div id="preview-modal-messages" class="flex-1 overflow-y-auto custom-scrollbar space-y-3 p-2 mb-4 max-h-[50vh]">
            <!-- Injected via admin-support.js -->
        </div>

        <div class="border-t border-border-subtle pt-4 bg-background/50 -mx-6 -mb-6 p-6 rounded-b-2xl">
            <div class="flex items-center justify-between gap-3 text-xs text-text-muted">
                <span class="flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-sm text-primary">lock</span>
                    Interactive Preview Mode (Replies disabled until backend deployment)
                </span>
                <button type="button" onclick="window.closeMockTicketModal()" class="btn btn-secondary !py-1.5 !px-4 text-xs">
                    Close Preview
                </button>
            </div>
        </div>
    </div>
</div>

<?php
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
