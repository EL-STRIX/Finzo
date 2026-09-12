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
    <div class="flex items-center justify-between mb-6">
        <div>
            <h3 class="font-headline font-bold text-xl text-text-primary">My Support Tickets &amp; Disputes</h3>
            <p class="text-xs text-text-secondary">Overview of all open, in-progress, and resolved support inquiries.</p>
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
            <div>
                <label class="fin-label fin-label-required">Inquiry Category</label>
                <select id="ticket-category" class="fin-select w-full" required>
                    <option value="Transaction Dispute">Transaction Dispute</option>
                    <option value="Card Lock / PIN Issue">Card Lock / PIN Issue</option>
                    <option value="Account & Profile Inquiry">Account &amp; Profile Inquiry</option>
                    <option value="Loan & Interest Query">Loan &amp; Interest Query</option>
                    <option value="Technical Issue">Technical / App Issue</option>
                </select>
            </div>

            <div>
                <label class="fin-label fin-label-required">Priority Level</label>
                <select id="ticket-priority" class="fin-select w-full" required>
                    <option value="low">Low Priority</option>
                    <option value="medium" selected>Medium Priority</option>
                    <option value="high">High Priority</option>
                    <option value="urgent">Urgent / Fraud Alert</option>
                </select>
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
