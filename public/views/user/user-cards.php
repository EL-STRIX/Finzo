<?php
$portal = 'user';
$pageTitle = 'Debit & Credit Cards | Finzo Banking';
ob_start();
?>

<!-- Header -->
<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8 ds-animate-fade">
    <div>
        <div class="flex items-center gap-2 mb-1">
            <span class="text-xs font-bold text-text-secondary uppercase tracking-widest">Financial Products</span>
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-accent/10 text-accent border border-accent/20">
                <span class="w-1.5 h-1.5 rounded-full bg-accent animate-pulse"></span>
                Coming Soon
            </span>
        </div>
        <h1 class="font-display text-3xl font-extrabold text-text-primary tracking-tight">Next-Gen Cards &amp; Controls</h1>
        <p class="text-text-secondary text-sm mt-1">Experience a sneak peek of our upcoming biometric metal cards, virtual disposable cards, and intelligent spend controls.</p>
    </div>
    <div class="flex items-center gap-3">
        <button id="btn-user-card-waitlist" class="btn btn-primary shadow-lg hover:shadow-primary/25 transition-all flex items-center gap-2">
            <span class="material-symbols-outlined text-[20px]">credit_card</span>
            <span>Join Metal Card Waitlist</span>
        </button>
    </div>
</div>

<!-- Virtual Card Showcase & Interactive Security Preview Grid -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mb-8 ds-animate-slide-up">

    <!-- 3D Interactive Card Showcase -->
    <div class="lg:col-span-5 flex flex-col items-center">

        <!-- Perspective wrapper — required for 3D flip -->
        <div id="card-perspective" class="w-full max-w-sm" style="perspective: 1000px;">

            <!-- Inner card — flips in 3D space -->
            <div id="virtual-card-box"
                 class="relative w-full cursor-pointer group"
                 style="aspect-ratio:1.586/1; transform-style:preserve-3d; transition:transform 0.7s cubic-bezier(.4,0,.2,1); border-radius:1rem;">

                <!-- ══ FRONT FACE ══ -->
                <div id="card-front"
                     class="absolute inset-0 rounded-2xl p-6 text-white shadow-2xl overflow-hidden bg-gradient-to-tr from-slate-950 via-slate-900 to-accent border border-white/20"
                     style="backface-visibility:hidden; -webkit-backface-visibility:hidden;">

                    <!-- Ambient glow blobs -->
                    <div class="absolute -right-12 -bottom-12 w-48 h-48 bg-primary/20 rounded-full blur-3xl pointer-events-none"></div>
                    <div class="absolute -left-12 -top-12 w-48 h-48 bg-accent/20 rounded-full blur-3xl pointer-events-none"></div>

                    <!-- Front content -->
                    <div class="relative z-10 h-full flex flex-col justify-between">
                        <div class="flex justify-between items-center">
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-amber-400 text-2xl">contactless</span>
                                <span class="text-xs font-bold uppercase tracking-widest opacity-80" id="card-display-type">Metal Edition</span>
                            </div>
                            <span class="font-headline font-black text-xl tracking-wider text-white" id="card-display-network">FINZO</span>
                        </div>

                        <!-- EMV Chip & card number -->
                        <div class="my-auto">
                            <div class="w-11 h-8 rounded-md bg-gradient-to-br from-amber-300 via-amber-400 to-amber-600 border border-amber-200/50 shadow-inner flex items-center justify-center mb-3">
                                <div class="w-7 h-5 border border-amber-800/30 rounded-sm"></div>
                            </div>
                            <div class="font-mono text-xl font-bold tracking-[0.2em]" id="card-display-number">•••• •••• •••• 8842</div>
                        </div>

                        <div class="flex justify-between items-end">
                            <div>
                                <div class="text-[9px] font-bold uppercase tracking-widest text-white/60">Cardholder</div>
                                <div class="font-bold text-sm uppercase tracking-wider font-mono text-white" id="card-display-holder">VALUED FINZO MEMBER</div>
                            </div>
                            <div class="text-right">
                                <div class="text-[9px] font-bold uppercase tracking-widest text-white/60">Valid Thru</div>
                                <div class="font-bold text-sm font-mono text-white" id="card-display-expiry">12/29</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ══ BACK FACE ══ -->
                <div id="card-back"
                     class="absolute inset-0 rounded-2xl text-white shadow-2xl overflow-hidden bg-gradient-to-br from-slate-950 via-slate-900 to-slate-800 border border-white/20"
                     style="backface-visibility:hidden; -webkit-backface-visibility:hidden; transform:rotateY(180deg);">

                    <!-- Ambient glow (mirrored) -->
                    <div class="absolute -left-12 -bottom-12 w-48 h-48 bg-accent/20 rounded-full blur-3xl pointer-events-none"></div>
                    <div class="absolute -right-12 -top-12 w-48 h-48 bg-primary/20 rounded-full blur-3xl pointer-events-none"></div>

                    <!-- Magnetic stripe -->
                    <div class="absolute top-8 left-0 right-0 h-11 bg-gradient-to-r from-black via-zinc-900 to-black opacity-95"></div>

                    <!-- Back content -->
                    <div class="relative z-10 h-full flex flex-col justify-between p-6 pt-24">

                        <!-- Signature strip + CVV -->
                        <div class="flex items-center gap-3">
                            <!-- Signature panel -->
                            <div class="flex-1 h-10 rounded bg-white/90 flex items-center px-3 overflow-hidden">
                                <span class="font-mono text-slate-400 italic text-xs tracking-widest select-none" id="back-signature">Authorized Signature</span>
                            </div>
                            <!-- CVV box -->
                            <div class="flex flex-col items-center">
                                <div class="text-[8px] uppercase tracking-wider text-white/50 mb-0.5">CVV</div>
                                <div id="back-cvv"
                                     class="rounded px-3 py-1 font-mono font-bold text-sm tracking-widest transition-all duration-300"
                                     style="background:rgba(255,255,255,0.12); border:1px solid rgba(255,255,255,0.2); color:#fff; min-width:44px; text-align:center;">
                                    &bull;&bull;&bull;
                                </div>
                            </div>
                        </div>

                        <!-- Network & bank info -->
                        <div class="flex items-end justify-between">
                            <div>
                                <div class="text-[9px] font-bold uppercase tracking-widest text-white/50 mb-0.5">Issued By</div>
                                <div class="font-headline font-black text-sm tracking-widest text-white">FINZO BANK LTD.</div>
                                <div class="text-[9px] text-white/40 mt-0.5">finzobank.com &nbsp;|&nbsp; 1800-000-FINZO</div>
                            </div>
                            <!-- Hologram sticker simulation -->
                            <div class="w-10 h-10 rounded-full flex items-center justify-center"
                                 style="background: conic-gradient(from 0deg, #f59e0b, #6366f1, #10b981, #f59e0b); opacity:.85;">
                                <div class="w-7 h-7 rounded-full bg-slate-900 flex items-center justify-center">
                                    <span class="text-[8px] font-black text-white">F</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div><!-- /.virtual-card-box -->
        </div><!-- /.card-perspective -->

        <!-- Control buttons -->
        <div class="flex flex-wrap items-center gap-2 mt-5 justify-center">
            <button id="btn-toggle-cvv" class="btn btn-secondary btn-sm flex items-center gap-1.5">
                <span class="material-symbols-outlined text-sm">visibility</span>
                <span id="lbl-cvv">Reveal Demo Card Details</span>
            </button>
            <button id="btn-toggle-flip" class="btn btn-secondary btn-sm flex items-center gap-1.5">
                <span class="material-symbols-outlined text-sm">cached</span>
                <span id="lbl-flip">Flip Card</span>
            </button>
        </div>
    </div>

    <!-- Quick Controls & Security Settings Preview -->
    <div class="lg:col-span-7 ds-card p-6 border border-border-subtle bg-surface/80 flex flex-col justify-between">
        <div>
            <div class="flex items-center justify-between border-b border-border-subtle pb-4 mb-6">
                <div>
                    <h3 class="font-headline font-bold text-lg text-text-primary">Next-Gen Spend Controls</h3>
                    <p class="text-xs text-text-secondary">Interactive preview of upcoming biometric and localized payment limits.</p>
                </div>
                <span class="px-2.5 py-1 rounded-full bg-accent/10 text-accent border border-accent/20 text-[10px] font-black uppercase tracking-wider">PREVIEW</span>
            </div>

            <div class="space-y-6">
                <!-- Freeze Card Toggle Preview -->
                <div class="flex items-center justify-between p-4 rounded-xl bg-surface border border-border-subtle">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-rose-500/10 text-rose-500 flex items-center justify-center">
                            <span class="material-symbols-outlined text-xl">ac_unit</span>
                        </div>
                        <div>
                            <div class="font-bold text-sm text-text-primary">Instant Lock / Freeze</div>
                            <div class="text-xs text-text-secondary">Pause all contactless, ATM, and online transactions with one tap.</div>
                        </div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" id="preview-freeze-toggle" class="sr-only peer" />
                        <div class="w-11 h-6 bg-border-subtle peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-rose-500"></div>
                    </label>
                </div>

                <!-- Daily Online Purchase Limit Slider Preview -->
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <label class="text-xs font-bold uppercase tracking-wider text-text-secondary">Daily Online Purchase Cap</label>
                        <span id="limit-purchase-label" class="font-display font-bold text-sm text-primary">₹ 50,000</span>
                    </div>
                    <input type="range" id="limit-purchase-slider" min="5000" max="200000" step="5000" value="50000" class="w-full h-2 bg-border-subtle rounded-lg appearance-none cursor-pointer accent-primary" oninput="document.getElementById('limit-purchase-label').textContent = '₹ ' + (parseInt(this.value)).toLocaleString('en-IN')" />
                </div>

                <!-- Daily ATM Withdrawal Limit Slider Preview -->
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <label class="text-xs font-bold uppercase tracking-wider text-text-secondary">Daily ATM Withdrawal Cap</label>
                        <span id="limit-atm-label" class="font-display font-bold text-sm text-primary">₹ 25,000</span>
                    </div>
                    <input type="range" id="limit-atm-slider" min="2000" max="100000" step="1000" value="25000" class="w-full h-2 bg-border-subtle rounded-lg appearance-none cursor-pointer accent-primary" oninput="document.getElementById('limit-atm-label').textContent = '₹ ' + (parseInt(this.value)).toLocaleString('en-IN')" />
                </div>
            </div>
        </div>

        <div class="pt-6 border-t border-border-subtle flex justify-end">
            <button id="btn-save-preview-limits" class="btn btn-primary btn-sm flex items-center gap-1.5">
                <span class="material-symbols-outlined text-sm">bookmark</span>
                <span>Save Preferred Preferences</span>
            </button>
        </div>
    </div>
</div>

<!-- UPCOMING CARDS PORTFOLIO -->
<div class="ds-card p-8 border border-border-subtle relative overflow-hidden bg-gradient-to-br from-surface via-surface to-accent/5 mb-8">
    <div class="max-w-2xl mb-8">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-accent/10 text-accent border border-accent/20 text-xs font-bold uppercase tracking-wider mb-3">
            <span class="material-symbols-outlined text-[16px]">stars</span>
            Launching Soon
        </div>
        <h2 class="text-2xl font-display font-extrabold text-text-primary tracking-tight mb-2">
            The Finzo Card Lineup
        </h2>
        <p class="text-text-secondary text-sm leading-relaxed">
            Crafted from aerospace-grade stainless steel and recycled ocean polymers, each Finzo card pairs hardware security with zero-wait mobile provisioning.
        </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="p-5 rounded-2xl bg-background/60 border border-border-subtle hover:border-accent/40 transition-all">
            <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-500 flex items-center justify-center mb-3">
                <span class="material-symbols-outlined text-xl">shield</span>
            </div>
            <h4 class="font-headline font-bold text-sm text-text-primary mb-1">Finzo Obsidian Metal</h4>
            <p class="text-xs text-text-secondary mb-3 leading-relaxed">Solid 16g steel credit card with 0% foreign exchange markup, worldwide airport lounge access, and concierge.</p>
            <span class="text-[11px] font-mono font-bold text-amber-500">Premium Tier • Invite Only</span>
        </div>

        <div class="p-5 rounded-2xl bg-background/60 border border-border-subtle hover:border-accent/40 transition-all">
            <div class="w-10 h-10 rounded-xl bg-blue-500/10 text-blue-500 flex items-center justify-center mb-3">
                <span class="material-symbols-outlined text-xl">vpn_key</span>
            </div>
            <h4 class="font-headline font-bold text-sm text-text-primary mb-1">Virtual Disposable Cards</h4>
            <p class="text-xs text-text-secondary mb-3 leading-relaxed">Generate single-use virtual card numbers instantly for online subscriptions that automatically self-destruct after checkout.</p>
            <span class="text-[11px] font-mono font-bold text-blue-500">Unlimited Virtual • 0 Fee</span>
        </div>

        <div class="p-5 rounded-2xl bg-background/60 border border-border-subtle hover:border-accent/40 transition-all">
            <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-500 flex items-center justify-center mb-3">
                <span class="material-symbols-outlined text-xl">eco</span>
            </div>
            <h4 class="font-headline font-bold text-sm text-text-primary mb-1">Finzo Eco Sapphire</h4>
            <p class="text-xs text-text-secondary mb-3 leading-relaxed">Sustainable recycled debit card linked to your high-yield savings account with instant contactless payments and round-up savings.</p>
            <span class="text-[11px] font-mono font-bold text-emerald-500">Standard Tier • Complimentary</span>
        </div>
    </div>
</div>

<script defer src="/Finzo/public/assets/js/user/user-cards.js"></script>

<?php
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
