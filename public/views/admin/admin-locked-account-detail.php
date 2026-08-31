<?php
$portal = 'admin';
$pageTitle = 'Locked Account Detail';
$pageHeader = '
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div class="flex items-center gap-4">
        <a href="admin-locked-accounts.php" class="w-10 h-10 flex items-center justify-center rounded-xl bg-surface border border-border-subtle hover:bg-surface-hover transition-colors text-text-secondary hover:text-text-primary shadow-xs shrink-0" title="Back to Locked Accounts">
            <span class="material-symbols-outlined text-base" aria-hidden="true">arrow_back</span>
        </a>
        <div>
            <h1 class="text-2xl font-headline font-black text-text-primary tracking-tight">Security Incident</h1>
            <p class="text-xs font-bold text-text-secondary uppercase tracking-widest mt-1">Locked Account Investigation &amp; Remediation Dossier</p>
        </div>
    </div>
</div>
';
ob_start();
?>

        <!-- PAGE CONTENT -->
        <div class="grid grid-cols-1 lg:grid-cols-[1fr_340px] gap-6">
            <!-- LEFT COLUMN -->
            <div class="space-y-6">
                <!-- PROFILE CARD -->
                <div class="ds-card p-6 sm:p-8">
                    <div class="flex flex-col sm:flex-row items-center gap-6 sm:gap-8 text-center sm:text-left">
                        <div id="d-avatar" class="w-20 h-20 rounded-2xl bg-primary-50 border border-primary-100 flex items-center justify-center text-2xl font-black text-primary shadow-lg shadow-primary/10 flex-shrink-0 overflow-hidden">
                            S
                        </div>
                        <div class="flex-1 min-w-0">
                            <h1 id="d-name" class="text-xl sm:text-2xl font-black font-headline tracking-tight text-text-primary mb-1">-</h1>
                            <p id="d-email" class="text-[10px] font-bold text-text-secondary tracking-widest uppercase mb-3">-</p>
                            <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2">
                                <span id="d-main-badge" class="px-3 py-1 bg-danger-bg text-danger text-[10px] font-black rounded-lg uppercase tracking-widest border border-danger">Locked</span>
                                <span id="d-acc-type-badge" class="px-3 py-1 bg-surface-subtle text-text-secondary border border-border-subtle text-[10px] font-black rounded-lg uppercase tracking-widest">-</span>
                            </div>
                        </div>
                    </div>

                    <!-- Account Details Grid -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-6 pt-6 border-t border-border-subtle">
                        <div>
                            <p class="text-[10px] font-black text-text-secondary uppercase tracking-widest mb-1">Account No</p>
                            <p id="d-acc-no" class="font-bold text-xs text-text-primary tracking-wider">-</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-text-secondary uppercase tracking-widest mb-1">Mobile</p>
                            <p id="d-phone" class="font-bold text-xs text-text-primary">-</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-text-secondary uppercase tracking-widest mb-1">Account Type</p>
                            <p id="d-acc-type" class="font-bold text-xs text-text-primary">-</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-text-secondary uppercase tracking-widest mb-1">Date Joined</p>
                            <p id="d-opened" class="font-bold text-xs text-text-primary">-</p>
                        </div>
                    </div>

                    <!-- Balance -->
                    <div class="mt-4 pt-4 border-t border-border-subtle">
                        <p class="text-[10px] font-black text-primary uppercase tracking-widest mb-1">Current Balance</p>
                        <p id="d-balance" class="text-xl font-black text-primary">-</p>
                    </div>
                </div>

                <!-- INCIDENT REPORT -->
                <div class="ds-card p-6 sm:p-8">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-8 h-8 rounded-lg bg-danger-bg flex items-center justify-center">
                            <span class="material-symbols-outlined text-lg text-danger" aria-hidden="true">gpp_maybe</span>
                        </div>
                        <h3 class="text-xs font-black text-text-primary uppercase tracking-widest">Incident Report</h3>
                    </div>
                    <div class="bg-danger-bg/30 p-5 rounded-xl border border-danger">
                        <div id="d-reason" class="text-sm font-bold text-text-primary leading-relaxed">-</div>
                    </div>
                </div>

                <!-- LOGIN HISTORY -->
                <div class="ds-card overflow-hidden mt-6 relative z-10 shadow-2xl transition-all duration-300">
                    <div class="absolute -right-20 -top-20 w-64 h-64 bg-primary/5 rounded-full blur-3xl -z-10 pointer-events-none"></div>
                    <div class="absolute -left-20 -bottom-20 w-64 h-64 bg-accent/5 rounded-full blur-3xl -z-10 pointer-events-none"></div>
                    <div class="px-6 py-4 border-b border-border-subtle flex items-center justify-between bg-background/40 backdrop-blur-xl relative z-10">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-surface-subtle flex items-center justify-center">
                                <span class="material-symbols-outlined text-lg text-text-secondary" aria-hidden="true">history</span>
                            </div>
                            <h3 class="text-xs font-black text-text-primary uppercase tracking-widest">Login History</h3>
                        </div>
                        <span id="log-count-badge" class="px-2.5 py-1 bg-surface-subtle text-text-secondary border border-border-subtle text-[10px] font-black rounded-md uppercase tracking-widest">0 Events</span>
                    </div>
                    <div class="ds-table-wrapper w-full border-0 shadow-none rounded-none bg-transparent">
                        <table class="ds-table w-full text-left min-w-[550px]">
                            <thead scope="col">
                                <tr>
                                    <th class="text-[10px] uppercase tracking-widest pl-6" scope="col">Status</th>
                                    <th class="text-[10px] uppercase tracking-widest" scope="col">IP Address</th>
                                    <th class="text-[10px] uppercase tracking-widest" scope="col">Date & Time</th>
                                </tr>
                            </thead>
                            <tbody id="audit-logs-body">
                                <tr><td colspan="3" class="p-10 text-center text-[10px] opacity-40 font-bold uppercase tracking-widest text-text-secondary">Loading...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- RIGHT COLUMN -->
            <div class="space-y-6">
                <!-- ACCESS CONTROL -->
                <div class="ds-card p-6" id="unlock-action-card">
                    <h3 class="text-[10px] font-black text-text-secondary uppercase tracking-widest mb-5">Access Control</h3>
                    <button type="button" onclick="openUnlockModal()" class="btn btn-primary w-full justify-center !py-3.5 shadow-lg shadow-warning/20 mb-4 !bg-warning !text-white !border-warning cursor-pointer">
                        <span class="material-symbols-outlined text-lg mr-1.5" aria-hidden="true">vpn_key</span> Generate &amp; Unlock
                    </button>
                    <div class="p-3 bg-warning-bg/30 rounded-xl border border-warning text-center">
                        <p class="text-[10px] font-black text-warning uppercase tracking-widest leading-relaxed">
                            Unlocking will provide a temporary PIN valid for 24 hours only.
                        </p>
                    </div>
                </div>

                <!-- TEMP PASS CARD (Hidden initially) -->
                <div id="temp-pass-card" class="ds-card p-6 hidden" >
                    <div class="text-[10px] font-black text-success uppercase tracking-widest mb-4">Account Reinstated</div>
                    <div class="p-4 bg-surface rounded-xl mb-3 border border-success-bg shadow-sm">
                        <div class="text-[10px] font-black text-success uppercase tracking-widest mb-1 text-center">Temporary PIN</div>
                        <div id="display-pass" class="text-center font-code text-2xl font-black text-success py-2 border-b border-border-subtle mb-3"></div>
                        <button type="button" onclick="copyDisplayPass()" class="w-full text-success text-[10px] font-black uppercase tracking-widest py-2 hover:bg-success-bg rounded transition-all cursor-pointer">Copy to Clipboard</button>
                    </div>
                    <div class="text-[10px] text-text-secondary text-center uppercase font-bold tracking-widest">Must reset PIN on first login</div>
                </div>

                <!-- LOCK CONTEXT -->
                <div class="ds-card p-6">
                    <h3 class="text-[10px] font-black text-text-secondary uppercase tracking-widest mb-5">Lock Details</h3>
                    <div class="space-y-4 text-text-primary">
                        <div class="flex items-center justify-between py-2 border-b border-border-subtle">
                            <span class="text-[10px] font-black uppercase tracking-widest text-text-secondary">Locked On</span>
                            <span id="d-locked-date" class="text-xs font-black">-</span>
                        </div>
                        <div class="flex items-center justify-between py-2 border-b border-border-subtle">
                            <span class="text-[10px] font-black uppercase tracking-widest text-text-secondary">Lock Time</span>
                            <span id="d-lock-time" class="text-xs font-black">-</span>
                        </div>
                        <div class="flex items-center justify-between py-2 border-b border-border-subtle">
                            <span class="text-[10px] font-black uppercase tracking-widest text-text-secondary">Locked By</span>
                            <span id="d-locked-by" class="text-xs font-black">-</span>
                        </div>
                        <div class="flex items-center justify-between py-2">
                            <span class="text-[10px] font-black uppercase tracking-widest text-text-secondary">IP Address</span>
                            <span id="d-ip" class="text-[10px] font-black font-mono tracking-widest">-</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <!-- MODAL -->
    <div id="unlock-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 hidden" role="dialog" aria-modal="true">
        <div class="absolute inset-0 bg-black/60 dark:bg-black/80 backdrop-blur-sm" onclick="closeModal()"></div>
        <div class="ds-card w-full max-w-md shadow-2xl overflow-hidden scale-95 transition-all duration-300 border border-border-subtle relative z-10 bg-surface" id="modal-card">
            <div class="p-8 text-center text-text-primary">
                <div class="w-20 h-20 rounded-full bg-warning-bg flex items-center justify-center mx-auto mb-6 text-warning border border-warning/20">
                    <span class="material-symbols-outlined text-4xl" aria-hidden="true">vpn_key</span>
                </div>
                <h3 class="text-xl font-black font-headline mb-3">Restore Access?</h3>
                <p class="text-sm text-text-secondary leading-relaxed mb-8">This will generate a 12-character one-time password for <strong id="modal-name" class="text-text-primary">-</strong>. User must reset their credentials immediately.</p>
                
                <div class="flex gap-3">
                    <button type="button" onclick="closeModal()" class="flex-1 btn btn-secondary py-3 justify-center text-xs font-bold uppercase tracking-wider cursor-pointer">Cancel</button>
                    <button type="button" id="gen-btn" onclick="doGenerate()" class="flex-1 btn btn-primary !bg-warning !border-warning !text-white py-3 justify-center shadow-lg shadow-warning/20 text-xs font-bold uppercase tracking-wider cursor-pointer">Authorize</button>
                </div>
            </div>
        </div>
    </div>

<?php
$extraScripts = '<script defer src="/Finzo/public/assets/js/admin/locked-account-detail.js?v=' . time() . '"></script>';
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>