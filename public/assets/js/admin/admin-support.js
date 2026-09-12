/**
 * Finzo Admin Portal — Helpdesk & Customer Support Console
 * Real-time queue management, conversation threading, and official dispute resolution.
 */

(function() {
    'use strict';

    let currentFilter = 'all';
    let currentPriority = 'all';
    let searchQuery = '';
    let activeTicketId = null;
    let searchDebounceTimer = null;

    function init() {
        // Setup filter tab buttons
        const tabBtns = document.querySelectorAll('.status-tab-btn');
        tabBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                tabBtns.forEach(b => {
                    b.classList.remove('active-tab', 'text-primary', 'border-primary/30', 'bg-primary/10');
                    b.classList.add('text-text-secondary', 'border-transparent');
                });
                this.classList.add('active-tab', 'text-primary', 'border-primary/30', 'bg-primary/10');
                this.classList.remove('text-text-secondary', 'border-transparent');
                currentFilter = this.getAttribute('data-status') || 'all';
                loadSupportTickets();
            });
        });

        // Priority dropdown change listener
        const prioInput = document.getElementById('filter-priority-select');
        if (prioInput) {
            prioInput.addEventListener('change', function() {
                currentPriority = this.value || 'all';
                loadSupportTickets();
            });
        }

        // Quick status modal dropdown change listener
        const quickStatusInput = document.getElementById('modal-quick-status');
        if (quickStatusInput) {
            quickStatusInput.addEventListener('change', function() {
                submitStatusChange(this.value);
            });
        }

        // Initial load
        loadSupportTickets();
    }

    async function loadSupportTickets() {
        const tableBody = document.getElementById('tickets-table-body');
        if (!tableBody) return;

        tableBody.innerHTML = `
            <tr>
                <td colspan="7" class="py-12 text-center text-text-secondary">
                    <div class="flex flex-col items-center justify-center gap-2">
                        <span class="material-symbols-outlined animate-spin text-2xl text-primary">sync</span>
                        <span class="text-xs font-bold uppercase tracking-wider">Loading support tickets...</span>
                    </div>
                </td>
            </tr>
        `;

        try {
            const params = {};
            if (currentFilter && currentFilter !== 'all') params.status = currentFilter;
            if (currentPriority && currentPriority !== 'all') params.priority = currentPriority;
            if (searchQuery) params.search = searchQuery;

            const res = await window.api.get('/admin/support/tickets', params);

            if (res && res.success) {
                updateStats(res.stats || {});
                renderTicketsTable(res.tickets || []);
            } else {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="7" class="py-12 text-center text-rose-500 text-xs font-bold uppercase tracking-wider">
                            ${res.message || 'Failed to load tickets.'}
                        </td>
                    </tr>
                `;
            }
        } catch (e) {
            console.error('Failed to load tickets:', e);
            tableBody.innerHTML = `
                <tr>
                    <td colspan="7" class="py-12 text-center text-rose-500 text-xs font-bold uppercase tracking-wider">
                        Connection error. Failed to retrieve tickets.
                    </td>
                </tr>
            `;
        }
    }

    function updateStats(stats) {
        const total = stats.total ?? 0;
        const open = stats.open_count ?? 0;
        const inProgress = stats.in_progress_count ?? 0;
        const resolved = stats.resolved_count ?? 0;
        const closed = stats.closed_count ?? 0;

        // Top cards
        setElementText('stat-total', total);
        setElementText('stat-open', open);
        setElementText('stat-in-progress', inProgress);
        setElementText('stat-resolved', resolved + closed);

        // Tab count badges
        setElementText('tab-count-all', total);
        setElementText('tab-count-open', open);
        setElementText('tab-count-in-progress', inProgress);
        setElementText('tab-count-resolved', resolved);
        setElementText('tab-count-closed', closed);
    }

    function renderTicketsTable(tickets) {
        const tableBody = document.getElementById('tickets-table-body');
        if (!tableBody) return;

        if (!tickets || !tickets.length) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="7" class="py-16 text-center text-text-secondary">
                        <div class="flex flex-col items-center justify-center gap-2 max-w-sm mx-auto">
                            <span class="material-symbols-outlined text-4xl text-text-muted">forum</span>
                            <span class="font-headline font-bold text-sm text-text-primary">No Support Tickets Found</span>
                            <span class="text-xs text-text-muted">There are no customer inquiries matching your active status and priority filters.</span>
                        </div>
                    </td>
                </tr>
            `;
            return;
        }

        tableBody.innerHTML = tickets.map(t => {
            const priorityBadge = getPriorityBadge(t.priority);
            const statusBadge = getStatusBadge(t.status);
            const updatedDate = t.updated_at ? formatDate(t.updated_at) : '—';

            return `
                <tr class="border-b border-border-subtle/40 hover:bg-surface-elevated/40 transition-colors">
                    <td class="px-4 py-3.5 whitespace-nowrap font-mono text-xs font-bold text-text-primary">
                        ${escapeHtml(t.ticket_no)}
                    </td>
                    <td class="px-4 py-3.5">
                        <div class="font-bold text-xs text-text-primary">${escapeHtml(t.customer_name || 'Customer')}</div>
                        <div class="text-[11px] font-mono text-text-muted">${escapeHtml(t.customer_id || '')}</div>
                    </td>
                    <td class="px-4 py-3.5 max-w-xs">
                        <div class="font-bold text-xs text-text-primary truncate" title="${escapeHtml(t.subject)}">${escapeHtml(t.subject)}</div>
                        <div class="text-[11px] text-text-muted mt-0.5">${escapeHtml(t.category)}</div>
                    </td>
                    <td class="px-4 py-3.5 whitespace-nowrap">
                        ${priorityBadge}
                    </td>
                    <td class="px-4 py-3.5 whitespace-nowrap">
                        ${statusBadge}
                    </td>
                    <td class="px-4 py-3.5 whitespace-nowrap text-xs text-text-muted">
                        ${updatedDate}
                    </td>
                    <td class="px-4 py-3.5 text-right whitespace-nowrap">
                        <button onclick="window.openTicketModal(${t.id})" class="btn btn-secondary !py-1.5 !px-3 text-xs inline-flex items-center gap-1.5 hover:border-primary/40 hover:text-primary transition-all">
                            <span class="material-symbols-outlined text-sm">visibility</span>
                            <span>Inspect &amp; Reply</span>
                        </button>
                    </td>
                </tr>
            `;
        }).join('');
    }

    async function openTicketModal(ticketId) {
        activeTicketId = ticketId;
        const modal = document.getElementById('ticket-modal');
        const msgBox = document.getElementById('modal-messages-container');
        const replyTicketInput = document.getElementById('reply-ticket-id');
        const replyTextInput = document.getElementById('reply-message-input');

        if (replyTicketInput) replyTicketInput.value = ticketId;
        if (replyTextInput) replyTextInput.value = '';

        if (msgBox) {
            msgBox.innerHTML = `
                <div class="py-12 flex flex-col items-center justify-center gap-2 text-text-secondary">
                    <span class="material-symbols-outlined animate-spin text-xl text-primary">sync</span>
                    <span class="text-xs font-bold uppercase tracking-wider">Loading conversation thread...</span>
                </div>
            `;
        }

        if (modal) modal.classList.remove('hidden');

        try {
            const res = await window.api.get(`/admin/support/tickets/${ticketId}`);
            if (res && res.success && res.ticket) {
                const t = res.ticket;
                setElementText('modal-ticket-no', t.ticket_no);
                setElementText('modal-ticket-subject', t.subject);

                const prioEl = document.getElementById('modal-priority-badge');
                if (prioEl) prioEl.innerHTML = getPriorityBadge(t.priority);

                const statEl = document.getElementById('modal-status-badge');
                if (statEl) statEl.innerHTML = getStatusBadge(t.status);

                const custInfoEl = document.getElementById('modal-customer-info');
                if (custInfoEl) {
                    custInfoEl.innerHTML = `
                        <span class="font-bold text-text-primary">${escapeHtml(t.customer_name || 'Customer')}</span>
                        <span class="font-mono text-[11px] text-text-muted">(${escapeHtml(t.customer_id || '')})</span>
                        <span class="text-text-muted">•</span>
                        <span>Email: <strong class="text-text-primary">${escapeHtml(t.customer_email || '—')}</strong></span>
                        <span class="text-text-muted">•</span>
                        <span>Phone: <strong class="text-text-primary">${escapeHtml(t.customer_phone || '—')}</strong></span>
                        <span class="text-text-muted">•</span>
                        <span>Category: <strong class="text-text-primary">${escapeHtml(t.category)}</strong></span>
                    `;
                }

                setModalQuickStatus(t.status);

                renderMessages(res.messages || []);
            } else {
                if (msgBox) {
                    msgBox.innerHTML = `<div class="py-8 text-center text-rose-500 text-xs font-bold">${res.message || 'Failed to load thread.'}</div>`;
                }
            }
        } catch (e) {
            console.error('Error fetching ticket thread:', e);
            if (msgBox) {
                msgBox.innerHTML = `<div class="py-8 text-center text-rose-500 text-xs font-bold">Failed to load message thread.</div>`;
            }
        }
    }

    function renderMessages(messages) {
        const msgBox = document.getElementById('modal-messages-container');
        if (!msgBox) return;

        if (!messages.length) {
            msgBox.innerHTML = `<div class="py-8 text-center text-text-muted text-xs font-bold">No messages in this ticket thread yet.</div>`;
            return;
        }

        msgBox.innerHTML = messages.map(m => {
            const isAdmin = (m.sender_type === 'admin' || m.sender_type === 'staff');
            const senderLabel = isAdmin ? 'Official Support Officer' : (m.sender_name || 'Customer');
            const timeStr = m.created_at ? formatDate(m.created_at) : '';

            return `
                <div class="flex flex-col ${isAdmin ? 'items-end ml-10' : 'items-start mr-10'}">
                    <div class="flex items-center gap-1.5 text-[11px] text-text-muted mb-1 px-1">
                        <span class="font-bold ${isAdmin ? 'text-primary' : 'text-text-primary'}">${escapeHtml(senderLabel)}</span>
                        <span>•</span>
                        <span>${timeStr}</span>
                    </div>
                    <div class="p-4 rounded-2xl text-xs leading-relaxed max-w-xl shadow-sm ${
                        isAdmin 
                            ? 'bg-primary/10 border border-primary/20 text-text-primary rounded-tr-none' 
                            : 'bg-surface border border-border-subtle text-text-primary rounded-tl-none'
                    }">
                        <div class="whitespace-pre-wrap">${escapeHtml(m.message)}</div>
                    </div>
                </div>
            `;
        }).join('');

        // Scroll to bottom
        msgBox.scrollTop = msgBox.scrollHeight;
    }

    async function submitAdminReply(e) {
        e.preventDefault();
        const messageInput = document.getElementById('reply-message-input');
        const markResolvedCheck = document.getElementById('reply-mark-resolved');
        const submitBtn = document.getElementById('btn-submit-reply');
        const message = messageInput ? messageInput.value.trim() : '';

        if (!message || !activeTicketId) return;

        const originalBtnHtml = submitBtn ? submitBtn.innerHTML : '';
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="material-symbols-outlined animate-spin text-sm">sync</span><span>Sending...</span>';
        }

        try {
            const payload = {
                ticket_id: activeTicketId,
                message: message
            };

            if (markResolvedCheck && markResolvedCheck.checked) {
                payload.status = 'resolved';
            }

            const res = await window.api.post(`/admin/support/tickets/${activeTicketId}/reply`, payload);

            if (res && res.success) {
                if (messageInput) messageInput.value = '';
                if (markResolvedCheck) markResolvedCheck.checked = false;
                if (window.showToast) window.showToast('Official reply sent to customer successfully!', 'success');

                // Reload conversation thread and queue
                await openTicketModal(activeTicketId);
                await loadSupportTickets();
            } else {
                if (window.showToast) window.showToast((res && res.message) ? res.message : 'Failed to post reply.', 'error');
            }
        } catch (e) {
            console.error('Error posting admin reply:', e);
            if (window.showToast) window.showToast('Connection error while sending reply.', 'error');
        } finally {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnHtml;
            }
        }
    }

    async function submitStatusChange(status) {
        if (!activeTicketId || !status) return;

        try {
            const res = await window.api.post(`/admin/support/tickets/${activeTicketId}/status`, {
                ticket_id: activeTicketId,
                status: status
            });

            if (res && res.success) {
                if (window.showToast) window.showToast(`Ticket status updated to ${status.toUpperCase()}`, 'success');
                await openTicketModal(activeTicketId);
                await loadSupportTickets();
            } else {
                if (window.showToast) window.showToast((res && res.message) ? res.message : 'Failed to update status.', 'error');
            }
        } catch (e) {
            console.error('Error updating status:', e);
            if (window.showToast) window.showToast('Connection error while updating status.', 'error');
        }
    }

    function closeTicketModal() {
        const modal = document.getElementById('ticket-modal');
        if (modal) modal.classList.add('hidden');
        activeTicketId = null;
    }

    function setModalQuickStatus(status) {
        const input = document.getElementById('modal-quick-status');
        const dropdown = document.getElementById('quick-status-dropdown');
        if (!dropdown) return;
        const label = dropdown.querySelector('.dropdown-label');
        const options = dropdown.querySelectorAll('.finzo-dropdown-option');
        const statusMap = {
            'open': 'Open',
            'in_progress': 'In Progress',
            'resolved': 'Resolved',
            'closed': 'Closed'
        };
        const val = status || 'open';
        if (input) input.value = val;
        if (label) label.textContent = statusMap[val] || val;
        options.forEach(opt => {
            if (opt.getAttribute('data-value') === val) {
                opt.classList.add('is-selected');
                opt.setAttribute('aria-selected', 'true');
            } else {
                opt.classList.remove('is-selected');
                opt.setAttribute('aria-selected', 'false');
            }
        });
    }

    function handleSearchInput() {
        clearTimeout(searchDebounceTimer);
        searchDebounceTimer = setTimeout(() => {
            const searchInput = document.getElementById('search-ticket-input');
            searchQuery = searchInput ? searchInput.value.trim() : '';
            loadSupportTickets();
        }, 300);
    }

    function handlePriorityFilter() {
        const select = document.getElementById('filter-priority-select');
        currentPriority = select ? select.value : 'all';
        loadSupportTickets();
    }

    // Helpers
    function getPriorityBadge(prio) {
        const p = (prio || 'medium').toLowerCase();
        if (p === 'urgent') {
            return '<span class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase bg-rose-500/10 text-rose-500 border border-rose-500/20">Urgent</span>';
        }
        if (p === 'high') {
            return '<span class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase bg-amber-500/10 text-amber-500 border border-amber-500/20">High</span>';
        }
        if (p === 'low') {
            return '<span class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase bg-text-secondary/10 text-text-secondary border border-border-subtle">Low</span>';
        }
        return '<span class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase bg-blue-500/10 text-blue-400 border border-blue-500/20">Medium</span>';
    }

    function getStatusBadge(status) {
        const s = (status || 'open').toLowerCase();
        if (s === 'resolved') {
            return '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-500/10 text-emerald-400 border border-emerald-500/20"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Resolved</span>';
        }
        if (s === 'in_progress') {
            return '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-indigo-500/10 text-indigo-400 border border-indigo-500/20"><span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>In Progress</span>';
        }
        if (s === 'closed') {
            return '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-text-secondary/10 text-text-secondary border border-border-subtle">Closed</span>';
        }
        return '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-amber-500/10 text-amber-500 border border-amber-500/20"><span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>Open</span>';
    }

    function formatDate(dateStr) {
        try {
            const d = new Date(dateStr);
            return d.toLocaleDateString('en-IN', {
                day: '2-digit',
                month: 'short',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        } catch (e) {
            return dateStr;
        }
    }

    function setElementText(id, text) {
        const el = document.getElementById(id);
        if (el) el.textContent = text;
    }

    function escapeHtml(str) {
        if (str === null || str === undefined) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // Expose global handlers to window for inline HTML callers
    window.loadSupportTickets = loadSupportTickets;
    window.openTicketModal = openTicketModal;
    window.closeTicketModal = closeTicketModal;
    window.submitAdminReply = submitAdminReply;
    window.submitStatusChange = submitStatusChange;
    window.handleSearchInput = handleSearchInput;
    window.handlePriorityFilter = handlePriorityFilter;

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
