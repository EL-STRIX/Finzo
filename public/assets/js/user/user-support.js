/**
 * Finzo User Portal — Customer Support & Helpdesk Handler
 */

let activeTicketId = null;

document.addEventListener('DOMContentLoaded', async () => {
    buildSidebar('support');
    buildNavbar('Customer Support', 'Helpdesk & Disputes');
    await fetchUserSession();
    await loadTickets();
});

async function loadTickets() {
    const container = document.getElementById('tickets-container');
    try {
        const res = await window.api.get('/user/support/tickets');
        if (res.success) {
            renderTickets(res.tickets || []);
        } else {
            container.innerHTML = `<div class="py-12 text-center text-text-secondary text-xs font-bold uppercase tracking-wider">${res.message || 'No tickets.'}</div>`;
        }
    } catch (e) {
        console.error('Failed to load tickets:', e);
        container.innerHTML = `<div class="py-12 text-center text-text-secondary text-xs font-bold uppercase tracking-wider">Error connecting to server.</div>`;
    }
}

function renderTickets(tickets) {
    const container = document.getElementById('tickets-container');
    if (!container) return;

    if (!tickets || !tickets.length) {
        container.innerHTML = '<div class="py-12 text-center text-text-secondary text-xs font-bold uppercase tracking-wider">No support tickets or disputes raised yet. Click "Raise New Ticket" to contact support.</div>';
        return;
    }

    container.innerHTML = tickets.map(t => {
        const isOpen = t.status === 'open';
        const inProgress = t.status === 'in_progress';
        const isResolved = t.status === 'resolved';

        const statusBadge = isResolved
            ? '<span class="px-2.5 py-1 rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 text-[10px] font-black uppercase tracking-wider">Resolved</span>'
            : (inProgress
                ? '<span class="px-2.5 py-1 rounded-full bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20 text-[10px] font-black uppercase tracking-wider">In Progress</span>'
                : '<span class="px-2.5 py-1 rounded-full bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 text-[10px] font-black uppercase tracking-wider">Open</span>');

        const dateStr = t.created_at ? new Date(t.created_at).toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' }) : '—';

        return `
        <div class="p-5 rounded-xl border border-border-subtle bg-surface/50 hover:bg-surface transition-all flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl bg-primary/10 text-primary flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-2xl">confirmation_number</span>
                </div>
                <div>
                    <div class="flex items-center gap-3 mb-1">
                        <h4 class="font-headline font-bold text-base text-text-primary">${t.subject}</h4>
                        ${statusBadge}
                    </div>
                    <div class="text-xs text-text-secondary">
                        <span class="font-mono font-bold text-text-primary">${t.ticket_no}</span> • Category: <span class="font-bold text-text-primary">${t.category}</span> • Raised on ${dateStr}
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-4 justify-between md:justify-end border-t md:border-t-0 border-border-subtle pt-3 md:pt-0">
                <button onclick="openTicketThread(${t.id})" class="btn btn-secondary btn-sm shrink-0">
                    <span class="material-symbols-outlined text-sm">forum</span>
                    <span>View Messages</span>
                </button>
            </div>
        </div>
        `;
    }).join('');
}

async function openTicketThread(ticketId) {
    activeTicketId = ticketId;
    document.getElementById('reply-ticket-id').value = ticketId;
    const modal = document.getElementById('ticket-modal');
    const box = document.getElementById('ticket-messages-box');
    const title = document.getElementById('ticket-modal-title');
    const sub = document.getElementById('ticket-modal-sub');

    box.innerHTML = '<div class="py-8 text-center text-text-secondary font-bold uppercase text-xs">Loading messages...</div>';
    modal.classList.remove('hidden');

    try {
        const res = await window.api.get(`/user/support/tickets/${ticketId}`);
        if (res.success && res.ticket) {
            title.textContent = `Ticket ${res.ticket.ticket_no} — ${res.ticket.subject}`;
            sub.textContent = `Category: ${res.ticket.category} | Status: ${(res.ticket.status).toUpperCase()}`;

            const messages = res.messages || [];
            box.innerHTML = messages.map(m => {
                const isUser = m.sender_type === 'user';
                const time = m.created_at ? new Date(m.created_at).toLocaleTimeString('en-IN', { hour: '2-digit', minute: '2-digit' }) : '';

                return `
                <div class="flex flex-col ${isUser ? 'items-end' : 'items-start'}">
                    <div class="text-[10px] font-bold text-text-secondary uppercase mb-1">${isUser ? 'You' : 'Support Officer'} • ${time}</div>
                    <div class="p-3.5 rounded-2xl max-w-md text-xs font-sans ${isUser ? 'bg-primary text-white rounded-br-none' : 'bg-surface-elevated text-text-primary border border-border-subtle rounded-bl-none'}">
                        ${m.message}
                    </div>
                </div>
                `;
            }).join('');

            box.scrollTop = box.scrollHeight;
        }
    } catch (e) {
        console.error('Failed to load thread:', e);
        box.innerHTML = '<div class="py-8 text-center text-rose-500 font-bold uppercase text-xs">Failed to load message thread.</div>';
    }
}

function closeTicketModal() {
    document.getElementById('ticket-modal')?.classList.add('hidden');
}

async function submitReply(e) {
    e.preventDefault();
    const message = document.getElementById('reply-message').value;
    if (!message || !activeTicketId) return;

    try {
        const formData = new FormData();
        formData.append('ticket_id', activeTicketId);
        formData.append('message', message);

        const res = await window.api.post(`/user/support/tickets/${activeTicketId}/reply`, formData, true);
        if (res.success) {
            document.getElementById('reply-message').value = '';
            await openTicketThread(activeTicketId);
        } else {
            showToast(res.message || 'Failed to post reply.', 'error');
        }
    } catch (e) {
        console.error('Reply ticket error:', e);
        showToast('Connection failed.', 'error');
    }
}

function openCreateTicketModal() {
    document.getElementById('create-ticket-modal')?.classList.remove('hidden');
}

function closeCreateTicketModal() {
    document.getElementById('create-ticket-modal')?.classList.add('hidden');
}

async function submitCreateTicket(e) {
    e.preventDefault();
    const category = document.getElementById('ticket-category').value;
    const priority = document.getElementById('ticket-priority').value;
    const subject = document.getElementById('ticket-subject').value;
    const message = document.getElementById('ticket-message').value;

    if (!subject || !message) {
        showToast('Please fill in required fields.', 'error');
        return;
    }

    try {
        const formData = new FormData();
        formData.append('category', category);
        formData.append('priority', priority);
        formData.append('subject', subject);
        formData.append('message', message);

        const res = await window.api.post('/user/support/tickets', formData, true);
        if (res.success) {
            showToast(res.message || 'Support ticket submitted successfully!', 'success');
            closeCreateTicketModal();
            document.getElementById('create-ticket-form').reset();
            await loadTickets();
        } else {
            showToast(res.message || 'Failed to create ticket.', 'error');
        }
    } catch (e) {
        console.error('Create ticket error:', e);
        showToast('Connection failed.', 'error');
    }
}
