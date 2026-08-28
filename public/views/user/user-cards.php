<?php
$portal = 'user';
$pageTitle = 'Debit & Credit Cards | Finzo Banking';
ob_start();
?>

<!-- Header -->
<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8 ds-animate-fade">
    <div>
        <div class="text-xs font-bold text-text-secondary uppercase tracking-widest mb-1">Financial Products</div>
        <h1 class="font-display text-3xl font-extrabold text-text-primary tracking-tight">Cards &amp; Payment Controls</h1>
        <p class="text-text-secondary text-sm mt-1">Manage physical &amp; virtual debit/credit cards, reveal CVV, freeze cards, and configure online transaction limits.</p>
    </div>
    <div class="flex items-center gap-3">
        <button onclick="openRequestCardModal()" class="btn btn-primary shadow-lg hover:shadow-primary/25 transition-all">
            <span class="material-symbols-outlined text-[20px]">credit_card</span>
            <span>Request New Card</span>
        </button>
    </div>
</div>

<!-- Virtual Card Showcase & Quick Security Controls Grid -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mb-8 ds-animate-slide-up">
    
    <!-- 3D Interactive Card Showcase -->
    <div class="lg:col-span-5 flex flex-col items-center">
        <div class="w-full max-w-sm aspect-[1.586/1] rounded-2xl p-6 text-white relative shadow-2xl overflow-hidden transition-transform duration-500 hover:scale-[1.02] cursor-pointer group bg-gradient-to-tr from-slate-950 via-slate-900 to-accent border border-white/20" id="virtual-card-box">
            <!-- Glass Glow & Background Elements -->
            <div class="absolute -right-12 -bottom-12 w-48 h-48 bg-primary/20 rounded-full blur-3xl group-hover:bg-primary/30 transition-all"></div>
            <div class="absolute -left-12 -top-12 w-48 h-48 bg-accent/20 rounded-full blur-3xl"></div>

            <!-- Front Side -->
            <div id="card-front" class="relative z-10 h-full flex flex-col justify-between">
                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-amber-400 text-2xl">contactless</span>
                        <span class="text-xs font-bold uppercase tracking-widest opacity-80" id="card-display-type">Virtual Debit</span>
                    </div>
                    <span class="font-headline font-black text-xl tracking-wider text-white" id="card-display-network">VISA</span>
                </div>

                <!-- Chip -->
                <div class="my-auto">
                    <div class="w-11 h-8 rounded-md bg-gradient-to-br from-amber-300 via-amber-400 to-amber-600 border border-amber-200/50 shadow-inner flex items-center justify-center mb-3">
                        <div class="w-7 h-5 border border-amber-800/30 rounded-sm"></div>
                    </div>
                    <div class="font-mono text-xl font-bold tracking-[0.2em] text-shadow" id="card-display-number">•••• •••• •••• 4829</div>
                </div>

                <div class="flex justify-between items-end">
                    <div>
                        <div class="text-[9px] font-bold uppercase tracking-widest text-white/60">Card Holder</div>
                        <div class="font-bold text-sm uppercase tracking-wider font-mono text-white" id="card-display-holder">SUJAY PAUL</div>
                    </div>
                    <div class="text-right">
                        <div class="text-[9px] font-bold uppercase tracking-widest text-white/60">Expires</div>
                        <div class="font-bold text-sm font-mono text-white" id="card-display-expiry">12/28</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3 mt-4">
            <button onclick="revealCVV()" class="btn btn-secondary btn-sm">
                <span class="material-symbols-outlined text-sm">visibility</span>
                <span>Reveal CVV</span>
            </button>
            <button onclick="openChangePinModal()" class="btn btn-secondary btn-sm">
                <span class="material-symbols-outlined text-sm">lock_reset</span>
                <span>Change PIN</span>
            </button>
        </div>
    </div>

    <!-- Quick Controls & Security Settings -->
    <div class="lg:col-span-7 ds-card p-6 border border-border-subtle bg-surface/80 flex flex-col justify-between">
        <div>
            <div class="flex items-center justify-between border-b border-border-subtle pb-4 mb-6">
                <div>
                    <h3 class="font-headline font-bold text-lg text-text-primary">Card Security Controls</h3>
                    <p class="text-xs text-text-secondary">Instantly toggle card access or adjust daily online transaction caps.</p>
                </div>
                <span id="active-card-status-tag" class="px-2.5 py-1 rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 text-[10px] font-black uppercase tracking-wider">ACTIVE</span>
            </div>

            <div class="space-y-6">
                <!-- Freeze Card Toggle -->
                <div class="flex items-center justify-between p-4 rounded-xl bg-surface border border-border-subtle">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-rose-500/10 text-rose-500 flex items-center justify-center">
                            <span class="material-symbols-outlined text-xl">ac_unit</span>
                        </div>
                        <div>
                            <div class="font-bold text-sm text-text-primary">Freeze Card Temporarily</div>
                            <div class="text-xs text-text-secondary">Block all online, ATM, and POS transactions immediately.</div>
                        </div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" id="freeze-card-toggle" onchange="toggleCardFreeze(this)" class="sr-only peer" />
                        <div class="w-11 h-6 bg-border-subtle peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-rose-500"></div>
                    </label>
                </div>

                <!-- Daily Online Purchase Limit Slider -->
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <label class="text-xs font-bold uppercase tracking-wider text-text-secondary">Daily Online Purchase Limit</label>
                        <span id="limit-purchase-label" class="font-display font-bold text-sm text-primary">₹ 50,000</span>
                    </div>
                    <input type="range" id="limit-purchase-slider" min="5000" max="200000" step="5000" value="50000" class="w-full h-2 bg-border-subtle rounded-lg appearance-none cursor-pointer accent-primary" oninput="document.getElementById('limit-purchase-label').textContent = '₹ ' + (parseInt(this.value)).toLocaleString('en-IN')" />
                </div>

                <!-- Daily ATM Withdrawal Limit Slider -->
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <label class="text-xs font-bold uppercase tracking-wider text-text-secondary">Daily ATM Withdrawal Limit</label>
                        <span id="limit-atm-label" class="font-display font-bold text-sm text-primary">₹ 25,000</span>
                    </div>
                    <input type="range" id="limit-atm-slider" min="2000" max="100000" step="1000" value="25000" class="w-full h-2 bg-border-subtle rounded-lg appearance-none cursor-pointer accent-primary" oninput="document.getElementById('limit-atm-label').textContent = '₹ ' + (parseInt(this.value)).toLocaleString('en-IN')" />
                </div>
            </div>
        </div>

        <div class="pt-6 border-t border-border-subtle flex justify-end">
            <button onclick="saveCardLimits()" class="btn btn-primary btn-sm">
                <span class="material-symbols-outlined text-sm">save</span>
                <span>Save Limits</span>
            </button>
        </div>
    </div>
</div>

<!-- My Cards List -->
<div class="ds-card p-6 border border-border-subtle mb-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h3 class="font-headline font-bold text-xl text-text-primary">All Active &amp; Pending Cards</h3>
            <p class="text-xs text-text-secondary">Overview of all physical and virtual cards issued to your banking profile.</p>
        </div>
    </div>

    <div id="cards-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div class="col-span-full py-12 text-center text-text-secondary text-xs font-bold uppercase tracking-wider">
            Loading cards...
        </div>
    </div>
</div>

<!-- Change PIN Modal -->
<div id="pin-modal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="ds-card max-w-sm w-full p-6 bg-surface border border-border-subtle">
        <div class="flex items-center justify-between border-b border-border-subtle pb-4 mb-4">
            <h3 class="font-headline font-bold text-lg text-text-primary">Change 4-Digit Card PIN</h3>
            <button onclick="closeChangePinModal()" class="w-8 h-8 rounded-full hover:bg-surface-hover flex items-center justify-center text-text-secondary">
                <span class="material-symbols-outlined text-lg">close</span>
            </button>
        </div>

        <form id="change-pin-form" onsubmit="submitChangePin(event)" class="space-y-4">
            <div>
                <label class="fin-label fin-label-required">Current 4-Digit PIN</label>
                <input type="password" id="old-pin" maxlength="4" pattern="\d{4}" class="fin-input w-full font-mono text-center tracking-widest text-lg" required placeholder="••••" />
            </div>

            <div>
                <label class="fin-label fin-label-required">New 4-Digit PIN</label>
                <input type="password" id="new-pin" maxlength="4" pattern="\d{4}" class="fin-input w-full font-mono text-center tracking-widest text-lg" required placeholder="••••" />
            </div>

            <div>
                <label class="fin-label fin-label-required">Confirm New PIN</label>
                <input type="password" id="confirm-pin" maxlength="4" pattern="\d{4}" class="fin-input w-full font-mono text-center tracking-widest text-lg" required placeholder="••••" />
            </div>

            <div class="pt-4 flex justify-end gap-3 border-t border-border-subtle">
                <button type="button" onclick="closeChangePinModal()" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-primary">Update PIN</button>
            </div>
        </form>
    </div>
</div>

<!-- Request Card Modal -->
<div id="request-card-modal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="ds-card max-w-lg w-full p-6 bg-surface border border-border-subtle">
        <div class="flex items-center justify-between border-b border-border-subtle pb-4 mb-4">
            <h3 class="font-headline font-bold text-lg text-text-primary">Request New Debit/Credit Card</h3>
            <button onclick="closeRequestCardModal()" class="w-8 h-8 rounded-full hover:bg-surface-hover flex items-center justify-center text-text-secondary">
                <span class="material-symbols-outlined text-lg">close</span>
            </button>
        </div>

        <form id="request-card-form" onsubmit="submitCardRequest(event)" class="space-y-4">
            <div>
                <label class="fin-label fin-label-required">Select Card Product</label>
                <select id="request-card-product" class="fin-select w-full" required>
                    <option value="">Select card product...</option>
                </select>
            </div>

            <div>
                <label class="fin-label">Special Delivery / Customization Notes</label>
                <textarea id="request-card-notes" class="fin-input w-full" rows="3" placeholder="Optional notes for branch issuance"></textarea>
            </div>

            <div class="pt-4 flex justify-end gap-3 border-t border-border-subtle">
                <button type="button" onclick="closeRequestCardModal()" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-primary">Submit Request</button>
            </div>
        </form>
    </div>
</div>

<script defer src="/Finzo/public/assets/js/user/user-cards.js"></script>

<?php
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
