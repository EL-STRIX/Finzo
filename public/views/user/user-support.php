<?php
$portal = 'user';
$pageTitle = 'Help & Support | Finzo Banking';
ob_start();
?>

<!-- Header -->
<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8 ds-animate-fade">
    <div>
        <div class="text-xs font-bold text-text-secondary uppercase tracking-widest mb-1">Customer Support</div>
        <h1 class="font-display text-3xl font-extrabold text-text-primary tracking-tight">Helpdesk &amp; Dispute Ticketing</h1>
        <p class="text-text-secondary text-sm mt-1">Raise support inquiries, dispute unauthorized transactions, and track resolution progress in real-time.</p>
    </div>
    <div class="flex items-center gap-3">
        <button onclick="openCreateTicketModal()" class="btn btn-primary shadow-lg hover:shadow-primary/25 transition-all">
            <span class="material-symbols-outlined text-[20px]">support_agent</span>
            <span>Raise New Ticket</span>
        </button>
    </div>
</div>

<!-- Support Tickets Table -->
<div class="ds-card p-6 border border-border-subtle mb-8 ds-animate-slide-up">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h3 class="font-headline font-bold text-xl text-text-primary">My Support Tickets &amp; Disputes</h3>
            <p class="text-xs text-text-secondary">Overview of all open, in-progress, and resolved support inquiries.</p>
        </div>
        <div class="w-full sm:w-48 relative z-10">
            <div class="finzo-dropdown relative w-full" id="user-status-filter-dropdown">
                <input type="hidden" id="user-status-filter" value="all">
                <div class="finzo-dropdown-trigger fin-input !flex items-center justify-between cursor-pointer w-full !py-2 !h-[40px] text-xs">
                    <span class="dropdown-label font-bold text-xs text-text-primary">All Statuses</span>
                    <span class="material-symbols-outlined chevron transition-transform duration-300 text-text-secondary text-[18px]" aria-hidden="true">expand_more</span>
                </div>
                <div class="finzo-dropdown-menu !z-[1000] !w-full">
                    <div class="finzo-dropdown-option is-selected text-xs" data-value="all">All Statuses</div>
                    <div class="finzo-dropdown-option text-xs" data-value="open">Open</div>
                    <div class="finzo-dropdown-option text-xs" data-value="in_progress">In Progress</div>
                    <div class="finzo-dropdown-option text-xs" data-value="resolved">Resolved</div>
                </div>
            </div>
        </div>
    </div>

    <div id="tickets-container" class="space-y-4">
        <div class="py-12 text-center text-text-secondary text-xs font-bold uppercase tracking-wider">
            Loading support tickets...
        </div>
    </div>
</div>

<!-- Ticket Messaging Drawer / Modal -->
<div id="ticket-modal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="ds-card max-w-2xl w-full p-6 bg-surface border border-border-subtle max-h-[90vh] flex flex-col">
        <div class="flex items-center justify-between border-b border-border-subtle pb-4 mb-4">
            <div>
                <h3 class="font-headline font-bold text-lg text-text-primary" id="ticket-modal-title">Ticket Details</h3>
                <p class="text-xs text-text-secondary" id="ticket-modal-sub">Ticket Thread</p>
            </div>
            <button onclick="closeTicketModal()" class="w-8 h-8 rounded-full hover:bg-surface-hover flex items-center justify-center text-text-secondary">
                <span class="material-symbols-outlined text-lg">close</span>
            </button>
        </div>

        <div id="ticket-messages-box" class="flex-1 overflow-y-auto custom-scrollbar space-y-4 p-2 mb-4 max-h-[50vh]">
            <!-- Messages -->
        </div>

        <form id="reply-ticket-form" onsubmit="submitReply(event)" class="border-t border-border-subtle pt-4 flex gap-3">
            <input type="hidden" id="reply-ticket-id" value="" />
            <input type="text" id="reply-message" class="fin-input flex-1" required placeholder="Type your reply message..." />
            <button type="submit" class="btn btn-primary">
                <span class="material-symbols-outlined text-sm">send</span>
                <span>Send</span>
            </button>
        </form>
    </div>
</div>

<!-- Create Ticket Modal -->
<div id="create-ticket-modal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="ds-card max-w-lg w-full p-6 bg-surface border border-border-subtle">
        <div class="flex items-center justify-between border-b border-border-subtle pb-4 mb-4">
            <h3 class="font-headline font-bold text-lg text-text-primary">Raise Support Ticket</h3>
            <button onclick="closeCreateTicketModal()" class="w-8 h-8 rounded-full hover:bg-surface-hover flex items-center justify-center text-text-secondary">
                <span class="material-symbols-outlined text-lg">close</span>
            </button>
        </div>

        <form id="create-ticket-form" onsubmit="submitCreateTicket(event)" class="space-y-4">
            <div class="relative z-30">
                <label class="fin-label fin-label-required">Inquiry Category</label>
                <div class="finzo-dropdown relative w-full" id="ticket-category-dropdown">
                    <input type="hidden" id="ticket-category" value="Transaction Dispute">
                    <div class="finzo-dropdown-trigger fin-input !flex items-center justify-between cursor-pointer w-full !py-2.5 !h-[44px] text-sm">
                        <span class="dropdown-label font-bold text-sm text-text-primary">Transaction Dispute</span>
                        <span class="material-symbols-outlined chevron transition-transform duration-300 text-text-secondary" aria-hidden="true">expand_more</span>
                    </div>
                    <div class="finzo-dropdown-menu !z-[3500] !w-full">
                        <div class="finzo-dropdown-option is-selected text-sm" data-value="Transaction Dispute">Transaction Dispute</div>
                        <div class="finzo-dropdown-option text-sm" data-value="Card Lock / PIN Issue">Card Lock / PIN Issue</div>
                        <div class="finzo-dropdown-option text-sm" data-value="Account & Profile Inquiry">Account &amp; Profile Inquiry</div>
                        <div class="finzo-dropdown-option text-sm" data-value="Loan & Interest Query">Loan &amp; Interest Query</div>
                        <div class="finzo-dropdown-option text-sm" data-value="Technical Issue">Technical / App Issue</div>
                    </div>
                </div>
            </div>

            <div class="relative z-20">
                <label class="fin-label fin-label-required">Priority Level</label>
                <div class="finzo-dropdown relative w-full" id="ticket-priority-dropdown">
                    <input type="hidden" id="ticket-priority" value="medium">
                    <div class="finzo-dropdown-trigger fin-input !flex items-center justify-between cursor-pointer w-full !py-2.5 !h-[44px] text-sm">
                        <span class="dropdown-label font-bold text-sm text-text-primary">Medium Priority</span>
                        <span class="material-symbols-outlined chevron transition-transform duration-300 text-text-secondary" aria-hidden="true">expand_more</span>
                    </div>
                    <div class="finzo-dropdown-menu !z-[3500] !w-full">
                        <div class="finzo-dropdown-option text-sm" data-value="low">Low Priority</div>
                        <div class="finzo-dropdown-option is-selected text-sm" data-value="medium">Medium Priority</div>
                        <div class="finzo-dropdown-option text-sm" data-value="high">High Priority</div>
                        <div class="finzo-dropdown-option text-sm" data-value="urgent">Urgent / Fraud Alert</div>
                    </div>
                </div>
            </div>

            <div>
                <label class="fin-label fin-label-required">Subject Summary</label>
                <input type="text" id="ticket-subject" class="fin-input w-full" required placeholder="Brief title of your inquiry" />
            </div>

            <div>
                <label class="fin-label fin-label-required">Detailed Description</label>
                <textarea id="ticket-message" class="fin-input w-full" rows="4" required placeholder="Provide clear details regarding your issue or disputed transaction reference"></textarea>
            </div>

            <div class="pt-4 flex justify-end gap-3 border-t border-border-subtle">
                <button type="button" onclick="closeCreateTicketModal()" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-primary">Submit Ticket</button>
            </div>
        </form>
    </div>
</div>

<script defer src="/Finzo/public/assets/js/user/user-support.js"></script>

<?php
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
