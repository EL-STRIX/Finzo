/**
 * Finzo Admin Portal — Helpdesk & Customer Support (Coming Soon Showcase)
 * Interactive UI preview handler with zero dead 404 network calls.
 */

(function() {
    'use strict';

    // Mock preview tickets data for interactive demo
    const mockTickets = [
        {
            id: 101,
            ticket_no: 'TICK20260913-0842',
            customer_name: 'Alexander Wright',
            customer_id: 'FNZ-USR-000104',
            category: 'Transaction Dispute',
            subject: 'Unauthorized international debit card charge ($450.00)',
            priority: 'urgent',
            status: 'open',
            created_at: '12 mins ago',
            messages: [
                { sender: 'Alexander Wright (Customer)', time: '12 mins ago', text: 'I noticed an unauthorized charge of $450.00 from a merchant named GlobalCloud Corp on my Obsidian debit card ending in 4108. I did not authorize this transaction.' },
                { sender: 'System Auto-Triage', time: '11 mins ago', text: 'Urgent fraud flag detected. Card temporarily locked for international ecommerce pending security review.' }
            ]
        },
        {
            id: 102,
            ticket_no: 'TICK20260912-9114',
            customer_name: 'Priya Sharma',
            customer_id: 'FNZ-USR-000219',
            category: 'Card Lock / PIN Issue',
            subject: 'PIN retry lockout after ATM keypad error',
            priority: 'high',
            status: 'in_progress',
            created_at: '2 hours ago',
            messages: [
                { sender: 'Priya Sharma (Customer)', time: '2 hours ago', text: 'Entered incorrect PIN 3 times at the airport ATM. Need an emergency temporary unlock.' },
                { sender: 'Officer Marcus (Admin Support)', time: '1 hour ago', text: 'Identity verified via biometric push challenge. Initiating one-time temporary PIN unlock.' }
            ]
        },
        {
            id: 103,
            ticket_no: 'TICK20260912-4521',
            customer_name: 'David Chen',
            customer_id: 'FNZ-USR-000350',
            category: 'Account & Profile Inquiry',
            subject: 'Updated business incorporation docs for tier upgrade',
            priority: 'medium',
            status: 'in_progress',
            created_at: '5 hours ago',
            messages: [
                { sender: 'David Chen (Customer)', time: '5 hours ago', text: 'Uploaded the updated Certificate of Incorporation for Chen Global LLC to request higher wire limits.' }
            ]
        },
        {
            id: 104,
            ticket_no: 'TICK20260911-7890',
            customer_name: 'Elena Rostova',
            customer_id: 'FNZ-USR-000088',
            category: 'Loan & Interest Query',
            subject: 'Request for amortization schedule prepayment breakdown',
            priority: 'low',
            status: 'resolved',
            created_at: '1 day ago',
            messages: [
                { sender: 'Elena Rostova (Customer)', time: '1 day ago', text: 'Could you please email me the updated amortization table reflecting my $10,000 principal prepayment?' },
                { sender: 'Support Officer (Staff)', time: '22 hours ago', text: 'Amortization PDF generated and dispatched to registered email. Ticket resolved.' }
            ]
        }
    ];

    let currentFilter = 'all';

    function init() {
        // 1. Notify on Launch button
        const notifyBtn = document.getElementById('btn-notify-support');
        if (notifyBtn) {
            notifyBtn.addEventListener('click', function() {
                if (window.showToast) {
                    window.showToast('You will be notified as soon as the Enterprise Helpdesk & Dispute Console backend is deployed.', 'info');
                }
            });
        }

        // 2. Setup filter tabs
        const tabBtns = document.querySelectorAll('.filter-tab-btn');
        tabBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                tabBtns.forEach(b => {
                    b.classList.remove('active-tab', 'text-primary', 'border-primary', 'bg-primary/10');
                    b.classList.add('text-text-secondary', 'border-transparent');
                });
                this.classList.add('active-tab', 'text-primary', 'border-primary', 'bg-primary/10');
                this.classList.remove('text-text-secondary', 'border-transparent');
                currentFilter = this.getAttribute('data-status');
                renderTickets();
            });
        });

        // 3. Render initial ticket table
        renderTickets();
    }

    function renderTickets() {
        const container = document.getElementById('preview-tickets-body');
        if (!container) return;

        const filtered = mockTickets.filter(t => {
            if (currentFilter === 'all') return true;
            return t.status === currentFilter;
        });

        if (!filtered.length) {
            container.innerHTML = `
                <tr>
                    <td colspan="6" class="py-12 text-center text-text-secondary text-xs font-bold uppercase tracking-wider">
                        No support tickets found in this queue category.
                    </td>
                </tr>
            `;
            return;
        }

        container.innerHTML = filtered.map(t => {
            let statusBadge = '';
            if (t.status === 'open') {
                statusBadge = '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-amber-500/10 text-amber-500 border border-amber-500/20"><span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>Open</span>';
            } else if (t.status === 'in_progress') {
                statusBadge = '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-indigo-500/10 text-indigo-400 border border-indigo-500/20"><span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>In Progress</span>';
            } else {
                statusBadge = '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-500/10 text-emerald-400 border border-emerald-500/20"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Resolved</span>';
            }

            let priorityBadge = '';
            if (t.priority === 'urgent') {
                priorityBadge = '<span class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase bg-rose-500/10 text-rose-500 border border-rose-500/20">Urgent</span>';
            } else if (t.priority === 'high') {
                priorityBadge = '<span class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase bg-amber-500/10 text-amber-500 border border-amber-500/20">High</span>';
            } else if (t.priority === 'medium') {
                priorityBadge = '<span class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase bg-blue-500/10 text-blue-400 border border-blue-500/20">Medium</span>';
            } else {
                priorityBadge = '<span class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase bg-text-secondary/10 text-text-secondary border border-border-subtle">Low</span>';
            }

            return `
                <tr class="border-b border-border-subtle/50 hover:bg-surface-elevated/40 transition-colors">
                    <td class="px-4 py-3.5 font-mono text-xs font-bold text-text-primary">
                        ${t.ticket_no}
                    </td>
                    <td class="px-4 py-3.5">
                        <div class="font-bold text-xs text-text-primary">${t.customer_name}</div>
                        <div class="text-[11px] font-mono text-text-muted">${t.customer_id}</div>
                    </td>
                    <td class="px-4 py-3.5">
                        <div class="font-bold text-xs text-text-primary line-clamp-1">${t.subject}</div>
                        <div class="text-[11px] text-text-muted">${t.category} • ${t.created_at}</div>
                    </td>
                    <td class="px-4 py-3.5 whitespace-nowrap">
                        ${priorityBadge}
                    </td>
                    <td class="px-4 py-3.5 whitespace-nowrap">
                        ${statusBadge}
                    </td>
                    <td class="px-4 py-3.5 text-right whitespace-nowrap">
                        <button onclick="window.viewMockTicket(${t.id})" class="btn btn-secondary !py-1 !px-2.5 text-xs flex items-center gap-1.5 ml-auto">
                            <span class="material-symbols-outlined text-sm">visibility</span>
                            <span>Preview</span>
                        </button>
                    </td>
                </tr>
            `;
        }).join('');
    }

    // Expose preview ticket modal handler to global scope
    window.viewMockTicket = function(ticketId) {
        const ticket = mockTickets.find(t => t.id === ticketId);
        if (!ticket) return;

        const modal = document.getElementById('preview-ticket-modal');
        const title = document.getElementById('preview-modal-title');
        const sub = document.getElementById('preview-modal-sub');
        const msgContainer = document.getElementById('preview-modal-messages');

        if (title) title.textContent = `${ticket.ticket_no} — ${ticket.subject}`;
        if (sub) sub.textContent = `Customer: ${ticket.customer_name} (${ticket.customer_id}) | Priority: ${ticket.priority.toUpperCase()} | Status: ${ticket.status.toUpperCase()}`;

        if (msgContainer) {
            msgContainer.innerHTML = ticket.messages.map(m => {
                const isAdmin = m.sender.includes('Admin') || m.sender.includes('Staff') || m.sender.includes('System');
                return `
                    <div class="p-3.5 rounded-xl border ${isAdmin ? 'bg-primary/5 border-primary/20 ml-6' : 'bg-surface border-border-subtle mr-6'}">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-xs font-bold ${isAdmin ? 'text-primary' : 'text-text-primary'}">${m.sender}</span>
                            <span class="text-[10px] text-text-muted">${m.time}</span>
                        </div>
                        <div class="text-xs text-text-secondary leading-relaxed">${m.text}</div>
                    </div>
                `;
            }).join('');
        }

        if (modal) modal.classList.remove('hidden');
    };

    window.closeMockTicketModal = function() {
        const modal = document.getElementById('preview-ticket-modal');
        if (modal) modal.classList.add('hidden');
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
