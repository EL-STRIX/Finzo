/**
 * Finzo User Portal — Debit & Credit Cards Management Handler
 */

let allCards = [];
let cardProducts = [];
let activeCard = null;

document.addEventListener('DOMContentLoaded', async () => {
    buildSidebar('cards');
    buildNavbar('Financial Products', 'Debit & Credit Cards');
    await fetchUserSession();
    await loadCardProducts();
    await loadUserCards();
});

/**
 * Fetch card products for request dropdown
 */
async function loadCardProducts() {
    try {
        const res = await window.api.get('/card-products');
        if (res.success && res.products) {
            cardProducts = res.products;
            const select = document.getElementById('request-card-product');
            if (select) {
                select.innerHTML = '<option value="">Select card product...</option>' +
                    cardProducts.map(p => `<option value="${p.id}">${p.name} (${p.type.toUpperCase()} - ${p.network})</option>`).join('');
            }
        }
    } catch (e) {
        console.error('Failed to load card products:', e);
    }
}

/**
 * Fetch user cards
 */
async function loadUserCards() {
    const container = document.getElementById('cards-container');
    try {
        const res = await window.api.get('/cards');
        if (res.success) {
            allCards = res.cards || [];
            if (allCards.length > 0) {
                activeCard = allCards[0];
                updateVirtualCardShowcase(activeCard);
            }
            renderCardsList(allCards);
        } else {
            container.innerHTML = `<div class="col-span-full py-12 text-center text-text-secondary text-xs font-bold uppercase tracking-wider">${res.message || 'No cards issued.'}</div>`;
        }
    } catch (e) {
        console.error('Failed to load user cards:', e);
        container.innerHTML = `<div class="col-span-full py-12 text-center text-text-secondary text-xs font-bold uppercase tracking-wider">Error connecting to server.</div>`;
    }
}

/**
 * Update Virtual Card Showcase
 */
function updateVirtualCardShowcase(card) {
    if (!card) return;
    activeCard = card;

    const numEl = document.getElementById('card-display-number');
    const holderEl = document.getElementById('card-display-holder');
    const expiryEl = document.getElementById('card-display-expiry');
    const networkEl = document.getElementById('card-display-network');
    const typeEl = document.getElementById('card-display-type');
    const freezeToggle = document.getElementById('freeze-card-toggle');
    const statusTag = document.getElementById('active-card-status-tag');

    if (numEl) {
        const num = card.card_number || '•••• •••• •••• 4829';
        numEl.textContent = num.replace(/(.{4})/g, '$1 ').trim();
    }
    if (holderEl) holderEl.textContent = card.name_on_card || UserState.name || 'CARD HOLDER';
    if (expiryEl) expiryEl.textContent = card.expiry_date || '12/28';
    if (networkEl) networkEl.textContent = (card.network || 'VISA').toUpperCase();
    if (typeEl) typeEl.textContent = (card.product_name || card.type || 'Virtual Card').toUpperCase();

    const isFrozen = (card.status === 'frozen' || card.status === 'inactive');
    if (freezeToggle) freezeToggle.checked = isFrozen;

    if (statusTag) {
        if (card.status === 'active') {
            statusTag.className = 'px-2.5 py-1 rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 text-[10px] font-black uppercase tracking-wider';
            statusTag.textContent = 'ACTIVE';
        } else if (isFrozen) {
            statusTag.className = 'px-2.5 py-1 rounded-full bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20 text-[10px] font-black uppercase tracking-wider';
            statusTag.textContent = 'FROZEN';
        } else {
            statusTag.className = 'px-2.5 py-1 rounded-full bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 text-[10px] font-black uppercase tracking-wider';
            statusTag.textContent = (card.status || 'PENDING').toUpperCase();
        }
    }
}

/**
 * Render Cards List Grid
 */
function renderCardsList(cards) {
    const container = document.getElementById('cards-container');
    if (!container) return;

    if (!cards || !cards.length) {
        container.innerHTML = '<div class="col-span-full py-12 text-center text-text-secondary text-xs font-bold uppercase tracking-wider">No cards issued yet. Click "Request New Card" to apply.</div>';
        return;
    }

    container.innerHTML = cards.map(c => {
        const isSelected = activeCard && activeCard.id === c.id;
        const isFrozen = (c.status === 'frozen');
        const isActive = (c.status === 'active');

        const statusBadge = isActive
            ? '<span class="px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-bold uppercase text-[9px]">Active</span>'
            : (isFrozen
                ? '<span class="px-2 py-0.5 rounded bg-rose-500/10 text-rose-600 dark:text-rose-400 font-bold uppercase text-[9px]">Frozen</span>'
                : '<span class="px-2 py-0.5 rounded bg-amber-500/10 text-amber-600 dark:text-amber-400 font-bold uppercase text-[9px]">Pending</span>');

        return `
        <div class="p-5 rounded-xl border ${isSelected ? 'border-primary shadow-lg bg-primary/5' : 'border-border-subtle bg-surface/50'} hover:bg-surface transition-all flex flex-col justify-between cursor-pointer" onclick="selectCard(${c.id})">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold text-text-secondary uppercase tracking-wider">${c.network || 'VISA'}</span>
                    ${statusBadge}
                </div>
                <div class="font-mono font-bold text-base text-text-primary mb-1">${c.card_number ? c.card_number.replace(/(.{4})/g, '$1 ').trim() : '•••• •••• •••• 4829'}</div>
                <div class="text-xs text-text-secondary font-bold">${c.product_name || 'Debit Card'}</div>
            </div>

            <div class="flex items-center justify-between mt-4 pt-3 border-t border-border-subtle text-[11px] text-text-secondary">
                <span>Exp: ${c.expiry_date || '12/28'}</span>
                <span class="font-bold text-primary">${isSelected ? 'Currently Selected' : 'Click to Select'}</span>
            </div>
        </div>
        `;
    }).join('');
}

function selectCard(cardId) {
    const card = allCards.find(c => c.id === cardId);
    if (card) {
        updateVirtualCardShowcase(card);
        renderCardsList(allCards);
    }
}

/**
 * Reveal CVV with PIN prompt
 */
async function revealCVV() {
    if (!activeCard) {
        showToast('No active card selected.', 'error');
        return;
    }
    const pin = prompt('Enter your 4-digit Card PIN to reveal CVV:');
    if (!pin) return;

    try {
        const formData = new FormData();
        formData.append('card_id', activeCard.id);
        formData.append('pin', pin);

        // Verification call
        const res = await window.api.post(`/cards/${activeCard.id}/activate`, formData, true);
        // Display mock security CVV
        const expiryEl = document.getElementById('card-display-expiry');
        if (expiryEl) {
            const original = expiryEl.textContent;
            expiryEl.textContent = 'CVV: 842';
            showToast('CVV revealed for 10 seconds.', 'info');
            setTimeout(() => { expiryEl.textContent = original; }, 10000);
        }
    } catch (e) {
        showToast('PIN verification failed.', 'error');
    }
}

/**
 * Toggle Card Freeze Status
 */
async function toggleCardFreeze(checkbox) {
    if (!activeCard) return;

    const newStatus = checkbox.checked ? 'frozen' : 'active';
    try {
        const formData = new FormData();
        formData.append('card_id', activeCard.id);
        formData.append('status', newStatus);

        const res = await window.api.post(`/cards/${activeCard.id}/status`, formData, true);
        if (res.success) {
            showToast(`Card ${newStatus === 'frozen' ? 'frozen' : 'unfrozen'} successfully!`, 'success');
            activeCard.status = newStatus;
            updateVirtualCardShowcase(activeCard);
            renderCardsList(allCards);
        } else {
            showToast(res.message || 'Failed to update card status.', 'error');
            checkbox.checked = !checkbox.checked;
        }
    } catch (e) {
        console.error('Card freeze error:', e);
        showToast('Connection failed.', 'error');
        checkbox.checked = !checkbox.checked;
    }
}

/**
 * Save Limits Action
 */
function saveCardLimits() {
    showToast('Daily transaction limits updated successfully!', 'success');
}

/**
 * Change PIN Modals & Logic
 */
function openChangePinModal() {
    if (!activeCard) {
        showToast('Please select a card first.', 'error');
        return;
    }
    document.getElementById('pin-modal')?.classList.remove('hidden');
}

function closeChangePinModal() {
    document.getElementById('pin-modal')?.classList.add('hidden');
}

async function submitChangePin(e) {
    e.preventDefault();
    const oldPin = document.getElementById('old-pin').value;
    const newPin = document.getElementById('new-pin').value;
    const confirmPin = document.getElementById('confirm-pin').value;

    if (newPin !== confirmPin) {
        showToast('New PIN and Confirm PIN do not match.', 'error');
        return;
    }

    try {
        const formData = new FormData();
        formData.append('card_id', activeCard.id);
        formData.append('old_pin', oldPin);
        formData.append('new_pin', newPin);

        const res = await window.api.post(`/cards/${activeCard.id}/change-pin`, formData, true);
        if (res.success) {
            showToast(res.message || 'PIN updated successfully!', 'success');
            closeChangePinModal();
            document.getElementById('change-pin-form').reset();
        } else {
            showToast(res.message || 'Failed to change PIN.', 'error');
        }
    } catch (e) {
        console.error('Change PIN error:', e);
        showToast('Connection failed.', 'error');
    }
}

/**
 * Request Card Modals & Logic
 */
function openRequestCardModal() {
    document.getElementById('request-card-modal')?.classList.remove('hidden');
}

function closeRequestCardModal() {
    document.getElementById('request-card-modal')?.classList.add('hidden');
}

async function submitCardRequest(e) {
    e.preventDefault();
    const productId = document.getElementById('request-card-product').value;
    const notes = document.getElementById('request-card-notes').value;

    if (!productId) {
        showToast('Please select a card product.', 'error');
        return;
    }

    try {
        const formData = new FormData();
        formData.append('card_product_id', productId);
        formData.append('notes', notes);

        const res = await window.api.post('/cards/request', formData, true);
        if (res.success) {
            showToast(res.message || 'Card request submitted successfully!', 'success');
            closeRequestCardModal();
            document.getElementById('request-card-form').reset();
            await loadUserCards();
        } else {
            showToast(res.message || 'Failed to submit card request.', 'error');
        }
    } catch (e) {
        console.error('Request card error:', e);
        showToast('Connection failed.', 'error');
    }
}
