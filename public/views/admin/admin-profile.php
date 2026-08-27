<?php
$portal = 'admin';
$pageTitle = 'Finzo Admin — Executive Governance & Administrative Dossier';
// Admin portal guard — server-side authentication & role check
include __DIR__ . '/_guard.php';
if (!defined('FINZO_ROOT')) {
    require_once dirname(__DIR__, 3) . '/bootstrap/app.php';
}
\App\Shared\Security\SessionManager::start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];
ob_start();
?>

<div class="space-y-6">

    <!-- ═══════════════════════════════════════════════════════════════ -->
    <!-- 1. INSTITUTIONAL EXECUTIVE COMMAND HEADER                       -->
    <!-- ═══════════════════════════════════════════════════════════════ -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-4 border-b border-border-subtle">
        <div>
            <div class="flex items-center gap-2 text-[11px] font-bold text-text-secondary uppercase tracking-wider mb-1">
                <span>Corporate Governance</span>
                <span>/</span>
                <span>Central Operations</span>
                <span>/</span>
                <span class="text-text-primary font-bold">Officer Profile &amp; Authorities</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-headline font-bold text-text-primary tracking-tight">Executive Governance &amp; Administrative Dossier</h1>
            <p class="text-xs text-text-secondary mt-0.5">Central core banking administrative personnel records, cryptographic access controls, and statutory audit ledger.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5 shrink-0">
            <!-- Active Session Protocol Pill -->
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-surface border border-border-subtle text-xs text-text-secondary shadow-xs">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                <span class="font-mono text-[11px] font-bold text-text-primary">FIPS 140-2 Level 3 Cryptographic Mode</span>
            </div>

            <!-- Export Dossier Action -->
            <button type="button" onclick="window.AdminProfile.exportActivityCSV()" class="px-3.5 py-1.5 rounded-lg text-xs font-bold bg-surface hover:bg-surface-hover text-text-primary border border-border-subtle shadow-xs inline-flex items-center gap-1.5 transition-colors cursor-pointer" title="Export Administrative Audit Log">
                <span class="material-symbols-outlined text-[16px]" aria-hidden="true">download</span>
                <span>Export Dossier</span>
            </button>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════ -->
    <!-- 2. HIGH-DENSITY INSTITUTIONAL OFFICER DOSSIER STRIP             -->
    <!-- ═══════════════════════════════════════════════════════════════ -->
    <div class="ds-card p-5 sm:p-6 bg-surface border border-border-subtle rounded-xl shadow-xs">
        <div class="grid grid-cols-1 xl:grid-cols-12 gap-6 items-center">
            
            <!-- Left: Officer Portrait & Core Banking Identity (xl:col-span-7) -->
            <div class="xl:col-span-7 flex flex-col sm:flex-row items-center sm:items-start gap-5 text-center sm:text-left">
                <!-- Officer Official Portrait Container -->
                <div class="relative shrink-0">
                    <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-xl bg-surface-subtle border-2 border-border-subtle flex items-center justify-center text-xl sm:text-2xl font-bold text-text-primary shadow-xs overflow-hidden relative" id="avatar-preview-container">
                        <span id="avatar-initials" class="uppercase select-none font-mono font-bold tracking-wider">--</span>
                        <img loading="lazy" id="avatar-img-preview" src="" class="hidden w-full h-full object-cover" alt="Officer Portrait">
                    </div>

                    <!-- Verified Security Badge Overlay -->
                    <span class="absolute -bottom-1 -left-1 w-5 h-5 rounded-full bg-emerald-500 border-2 border-surface flex items-center justify-center text-white" title="Identity Verified by Security Officer">
                        <span class="material-symbols-outlined text-[12px]">verified</span>
                    </span>

                    <!-- Portrait Update Trigger -->
                    <label for="photo-upload" class="absolute -bottom-1.5 -right-1.5 w-7 h-7 bg-zinc-900 text-white hover:bg-black dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-100 rounded-lg flex items-center justify-center cursor-pointer shadow-xs transition-transform hover:scale-105 active:scale-95 border border-surface" title="Update Officer Portrait">
                        <span class="material-symbols-outlined text-[14px]" aria-hidden="true">photo_camera</span>
                    </label>
                    <input type="file" id="photo-upload" accept="image/jpeg, image/png" class="hidden" onchange="window.AdminProfile.uploadPhoto(this)">
                </div>

                <!-- Identity Information -->
                <div class="space-y-2 flex-1 min-w-0">
                    <div>
                        <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2">
                            <h2 class="text-lg sm:text-xl font-headline font-bold text-text-primary tracking-tight truncate" id="profile-name-display">Loading Administrative Profile...</h2>
                            <span id="badge-role" class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900 shadow-xs">Super Admin</span>
                        </div>
                        <p class="text-xs text-text-secondary font-medium mt-0.5">Principal Systems Administrator &bull; Treasury Operations &bull; Central HQ Branch #001</p>
                    </div>

                    <!-- Officer UID & Corporate Email Pills -->
                    <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2 text-xs">
                        <button type="button" onclick="window.AdminProfile.copyText(document.getElementById('profile-admin-id-input').value, 'Admin ID')" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded bg-surface-subtle hover:bg-surface-hover border border-border-subtle font-mono text-[11px] font-bold text-text-primary transition-colors group cursor-pointer" title="Copy Administrator Code">
                            <span class="material-symbols-outlined text-[13px] text-text-secondary" aria-hidden="true">badge</span>
                            <span id="hero-admin-id-text">ID: ---</span>
                            <span class="material-symbols-outlined text-[11px] opacity-40 group-hover:opacity-100" aria-hidden="true">content_copy</span>
                        </button>

                        <button type="button" onclick="window.AdminProfile.copyText(document.getElementById('profile-email-input').value, 'Email')" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded bg-surface-subtle hover:bg-surface-hover border border-border-subtle text-[11px] text-text-secondary hover:text-text-primary transition-colors group cursor-pointer" title="Copy Corporate Email">
                            <span class="material-symbols-outlined text-[13px] text-text-secondary" aria-hidden="true">mail</span>
                            <span id="hero-email-text">admin@finzo.com</span>
                            <span class="material-symbols-outlined text-[11px] opacity-40 group-hover:opacity-100" aria-hidden="true">content_copy</span>
                        </button>
                    </div>

                    <!-- Statutory & Operational Status Badges -->
                    <div class="flex flex-wrap items-center justify-center sm:justify-start gap-1.5 pt-0.5">
                        <span id="badge-status" class="px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 text-[10px] font-bold uppercase tracking-wider flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            Active &bull; Operational
                        </span>
                        <span id="badge-access" class="px-2 py-0.5 rounded bg-surface-subtle text-text-primary border border-border-subtle text-[10px] font-bold uppercase tracking-wider">
                            Level 4 Full Clearance
                        </span>
                        <span id="badge-sq-status" class="px-2 py-0.5 rounded bg-surface-subtle text-text-secondary border border-border-subtle text-[10px] font-bold uppercase tracking-wider flex items-center gap-1">
                            <span class="material-symbols-outlined text-[12px]" aria-hidden="true">shield</span>
                            <span id="badge-sq-label">Recovery: Checking...</span>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Right: Real Institutional Risk & Clearance Parameters (xl:col-span-5) -->
            <div class="xl:col-span-5 grid grid-cols-1 sm:grid-cols-2 gap-3 pt-4 xl:pt-0 border-t xl:border-t-0 xl:border-l xl:border-border-subtle xl:pl-6">
                <!-- Authority Metric 1: Financial Release Limits -->
                <div class="p-3 rounded-lg bg-surface-subtle border border-border-subtle">
                    <span class="text-[10px] font-bold text-text-secondary uppercase tracking-wider block mb-0.5">Clearing &amp; Release Limit</span>
                    <div class="flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-text-primary text-[18px]">currency_rupee</span>
                        <span class="text-sm font-headline font-bold text-text-primary">50,00,000 / Day</span>
                    </div>
                    <span class="text-[10px] text-text-secondary block mt-0.5">Dual-Approval Required &gt; 50L</span>
                </div>

                <!-- Authority Metric 2: Compliance Standard -->
                <div class="p-3 rounded-lg bg-surface-subtle border border-border-subtle">
                    <span class="text-[10px] font-bold text-text-secondary uppercase tracking-wider block mb-0.5">Compliance Standard</span>
                    <div class="flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-emerald-600 dark:text-emerald-400 text-[18px]">verified_user</span>
                        <span class="text-sm font-headline font-bold text-emerald-600 dark:text-emerald-400" id="hero-security-score">100% Verified</span>
                    </div>
                    <span class="text-[10px] text-text-secondary block mt-0.5">RBI / SOC-2 Type II Certified</span>
                </div>

                <!-- Authority Metric 3: Session Security -->
                <div class="p-3 rounded-lg bg-surface-subtle border border-border-subtle">
                    <span class="text-[10px] font-bold text-text-secondary uppercase tracking-wider block mb-0.5">Transport Cryptography</span>
                    <div class="flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-text-primary text-[18px]">lock</span>
                        <span class="text-xs font-mono font-bold text-text-primary truncate" id="hero-session-status">TLS 1.3 / AES-256</span>
                    </div>
                    <span class="text-[10px] text-text-secondary block mt-0.5">Zero-Trust Encrypted Tunnel</span>
                </div>

                <!-- Authority Metric 4: Credential Health -->
                <div class="p-3 rounded-lg bg-surface-subtle border border-border-subtle">
                    <span class="text-[10px] font-bold text-text-secondary uppercase tracking-wider block mb-0.5">Credential Rotation Cycle</span>
                    <div class="flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-text-primary text-[18px]">schedule</span>
                        <span class="text-xs font-bold text-text-primary">90-Day Policy</span>
                    </div>
                    <span class="text-[10px] text-emerald-600 dark:text-emerald-400 font-semibold block mt-0.5">Policy Satisfied</span>
                </div>
            </div>

        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════ -->
    <!-- 3. SECTION TABS NAVIGATION                                      -->
    <!-- ═══════════════════════════════════════════════════════════════ -->
    <div class="flex items-center gap-1 border-b border-border-subtle pb-px overflow-x-auto custom-scrollbar" role="tablist">
        <button type="button" role="tab" aria-selected="true" data-tab="tab-overview" onclick="window.AdminProfile.switchTab('tab-overview')" class="profile-tab-btn flex items-center gap-2 px-4 py-2.5 rounded-t-lg text-xs font-bold uppercase tracking-wider transition-all border-b-2 border-zinc-900 dark:border-white text-zinc-900 dark:text-white bg-surface shadow-2xs cursor-pointer">
            <span class="material-symbols-outlined text-[17px]" aria-hidden="true">badge</span>
            <span>Officer Dossier &amp; Authorities</span>
        </button>

        <button type="button" role="tab" aria-selected="false" data-tab="tab-security" onclick="window.AdminProfile.switchTab('tab-security')" class="profile-tab-btn flex items-center gap-2 px-4 py-2.5 rounded-t-lg text-xs font-bold uppercase tracking-wider transition-all border-b-2 border-transparent text-text-secondary hover:text-text-primary hover:bg-surface-hover cursor-pointer">
            <span class="material-symbols-outlined text-[17px]" aria-hidden="true">shield</span>
            <span>Authentication &amp; Cryptography</span>
        </button>

        <button type="button" role="tab" aria-selected="false" data-tab="tab-activity" onclick="window.AdminProfile.switchTab('tab-activity')" class="profile-tab-btn flex items-center gap-2 px-4 py-2.5 rounded-t-lg text-xs font-bold uppercase tracking-wider transition-all border-b-2 border-transparent text-text-secondary hover:text-text-primary hover:bg-surface-hover cursor-pointer">
            <span class="material-symbols-outlined text-[17px]" aria-hidden="true">receipt_long</span>
            <span>Regulatory Audit Ledger</span>
        </button>

        <button type="button" role="tab" aria-selected="false" id="tab-btn-directory" data-tab="tab-directory" onclick="window.AdminProfile.switchTab('tab-directory')" class="profile-tab-btn hidden items-center gap-2 px-4 py-2.5 rounded-t-lg text-xs font-bold uppercase tracking-wider transition-all border-b-2 border-transparent text-text-secondary hover:text-text-primary hover:bg-surface-hover cursor-pointer">
            <span class="material-symbols-outlined text-[17px]" aria-hidden="true">admin_panel_settings</span>
            <span>RBAC Directory Governance</span>
        </button>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════ -->
    <!-- 4. TAB 1: OFFICER DOSSIER & DELEGATED AUTHORITIES               -->
    <!-- ═══════════════════════════════════════════════════════════════ -->
    <div id="tab-overview" class="tab-panel space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            
            <!-- Left: Officer Information & Authorities Matrix (lg:col-span-8) -->
            <div class="lg:col-span-8 space-y-6">
                <!-- Card 1: Official Officer Records -->
                <div class="ds-card p-5 sm:p-6 bg-surface border border-border-subtle rounded-xl shadow-xs">
                    <div class="flex items-center justify-between pb-4 mb-5 border-b border-border-subtle">
                        <div>
                            <h3 class="font-headline font-bold text-sm text-text-primary flex items-center gap-2">
                                <span class="material-symbols-outlined text-text-secondary text-[18px]" aria-hidden="true">assignment_ind</span>
                                Statutory Officer Records &amp; Identification
                            </h3>
                            <p class="text-xs text-text-secondary mt-0.5">Verified personnel records maintained under Central Banking Oversight Regulations.</p>
                        </div>
                        <span class="px-2.5 py-1 bg-surface-subtle text-text-secondary text-[10px] font-mono font-bold rounded border border-border-subtle" id="meta-role-label">System Role</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-5 gap-y-4">
                        <div>
                            <label for="profile-admin-id-input" class="fin-label mb-1 block text-[10px] font-bold text-text-secondary uppercase tracking-wider">System Identifier (Immutable UID)</label>
                            <div class="fin-input-wrapper">
                                <span class="material-symbols-outlined input-icon-left text-text-secondary">fingerprint</span>
                                <input type="text" id="profile-admin-id-input" class="fin-input has-icon-left opacity-75 bg-surface-subtle cursor-not-allowed font-mono text-text-primary text-xs" readonly>
                            </div>
                        </div>

                        <div>
                            <label for="profile-name-input" class="fin-label mb-1 block text-[10px] font-bold text-text-secondary uppercase tracking-wider">Full Legal Name *</label>
                            <div class="fin-input-wrapper">
                                <span class="material-symbols-outlined input-icon-left text-text-secondary">person</span>
                                <input type="text" id="profile-name-input" class="fin-input has-icon-left bg-surface text-text-primary border-border-subtle text-xs" placeholder="Full Legal Name as per Government ID">
                            </div>
                        </div>

                        <div>
                            <label for="profile-dob-input" class="fin-label mb-1 block text-[10px] font-bold text-text-secondary uppercase tracking-wider">Date of Birth</label>
                            <div class="fin-input-wrapper">
                                <span class="material-symbols-outlined input-icon-left text-text-secondary">calendar_today</span>
                                <input type="date" id="profile-dob-input" class="fin-input has-icon-left bg-surface text-text-primary border-border-subtle text-xs">
                            </div>
                        </div>

                        <div>
                            <label class="fin-label mb-1 block text-[10px] font-bold text-text-secondary uppercase tracking-wider">Gender</label>
                            <div class="finzo-dropdown" id="dropdown-gender">
                                <input type="hidden" id="profile-gender-input" value="">
                                <button type="button" class="finzo-dropdown-trigger bg-surface text-text-primary border border-border-subtle rounded-lg px-3.5 py-2 flex items-center justify-between w-full text-xs font-semibold hover:border-text-primary transition-colors focus:ring-2 focus:ring-text-primary focus:outline-none" aria-haspopup="listbox" aria-expanded="false">
                                    <div class="flex items-center gap-2">
                                        <span class="material-symbols-outlined text-text-secondary text-[16px]" aria-hidden="true">wc</span>
                                        <span class="dropdown-label text-text-primary">Select Gender</span>
                                    </div>
                                    <span class="material-symbols-outlined text-[16px] chevron transition-transform text-text-secondary" aria-hidden="true">expand_more</span>
                                </button>
                                <div class="finzo-dropdown-menu bg-surface dark:bg-[#18181B] border border-border-subtle rounded-lg shadow-xl py-1 z-50" role="listbox">
                                    <div class="finzo-dropdown-option px-3.5 py-1.5 text-xs font-semibold text-text-primary hover:bg-surface-hover cursor-pointer transition-colors" data-value="Male">Male</div>
                                    <div class="finzo-dropdown-option px-3.5 py-1.5 text-xs font-semibold text-text-primary hover:bg-surface-hover cursor-pointer transition-colors" data-value="Female">Female</div>
                                    <div class="finzo-dropdown-option px-3.5 py-1.5 text-xs font-semibold text-text-primary hover:bg-surface-hover cursor-pointer transition-colors" data-value="Other">Other</div>
                                    <div class="finzo-dropdown-option px-3.5 py-1.5 text-xs font-semibold text-text-primary hover:bg-surface-hover cursor-pointer transition-colors" data-value="Prefer not to say">Prefer not to say</div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label for="profile-phone-input" class="fin-label mb-1 block text-[10px] font-bold text-text-secondary uppercase tracking-wider">Official Direct Line / Mobile</label>
                            <div class="fin-input-wrapper">
                                <span class="material-symbols-outlined input-icon-left text-text-secondary">call</span>
                                <input type="text" id="profile-phone-input" class="fin-input has-icon-left bg-surface text-text-primary border-border-subtle text-xs" placeholder="Official contact telephone number">
                            </div>
                        </div>

                        <div>
                            <label for="profile-email-input" class="fin-label mb-1 block text-[10px] font-bold text-text-secondary uppercase tracking-wider">Institutional Email (Immutable)</label>
                            <div class="fin-input-wrapper">
                                <span class="material-symbols-outlined input-icon-left text-text-secondary">mail</span>
                                <input type="email" id="profile-email-input" class="fin-input has-icon-left opacity-75 bg-surface-subtle cursor-not-allowed text-text-primary text-xs" readonly>
                            </div>
                        </div>

                        <div>
                            <label class="fin-label mb-1 block text-[10px] font-bold text-text-secondary uppercase tracking-wider">Operating Division</label>
                            <div class="fin-input-wrapper">
                                <span class="material-symbols-outlined input-icon-left text-text-secondary">domain</span>
                                <input type="text" value="Central Core Banking &amp; Treasury Governance" class="fin-input has-icon-left opacity-75 bg-surface-subtle cursor-not-allowed text-text-primary text-xs" readonly>
                            </div>
                        </div>

                        <div>
                            <label class="fin-label mb-1 block text-[10px] font-bold text-text-secondary uppercase tracking-wider">Assigned Office Location</label>
                            <div class="fin-input-wrapper">
                                <span class="material-symbols-outlined input-icon-left text-text-secondary">location_on</span>
                                <input type="text" value="Finzo Corporate Tower, Level 14, Mumbai" class="fin-input has-icon-left opacity-75 bg-surface-subtle cursor-not-allowed text-text-primary text-xs" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 pt-4 border-t border-border-subtle flex flex-col sm:flex-row items-center justify-between gap-3">
                        <span class="text-xs text-text-secondary flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[15px] text-emerald-600 dark:text-emerald-400" aria-hidden="true">check_circle</span>
                            Modifications are committed directly to core database with audit stamps
                        </span>
                        <button type="button" onclick="window.AdminProfile.saveProfile()" class="w-full sm:w-auto px-6 py-2 rounded-lg text-xs font-bold uppercase tracking-wider inline-flex items-center justify-center gap-1.5 bg-zinc-900 text-white hover:bg-black dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-100 shadow-xs transition-all cursor-pointer">
                            <span class="material-symbols-outlined text-[16px]" aria-hidden="true">save</span>
                            <span>Save Profile</span>
                        </button>
                    </div>
                </div>

                <!-- Card 2: Delegated Operational Authorities & Financial Mandates (Real Banking System Feature) -->
                <div class="ds-card p-5 sm:p-6 bg-surface border border-border-subtle rounded-xl shadow-xs">
                    <div class="flex items-center justify-between pb-3.5 mb-4 border-b border-border-subtle">
                        <div>
                            <h3 class="font-headline font-bold text-sm text-text-primary flex items-center gap-2">
                                <span class="material-symbols-outlined text-text-secondary text-[18px]" aria-hidden="true">gavel</span>
                                Delegated Operational Authorities &amp; Financial Mandates
                            </h3>
                            <p class="text-xs text-text-secondary mt-0.5">Active administrative privileges granted under the Central Risk &amp; Governance Matrix.</p>
                        </div>
                        <span class="px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 text-[10px] font-bold uppercase tracking-wider border border-emerald-500/20">
                            Clearance Verified
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                        <div class="p-3 rounded-lg bg-surface-subtle border border-border-subtle flex items-start gap-2.5">
                            <span class="material-symbols-outlined text-emerald-600 dark:text-emerald-400 text-[18px] shrink-0 mt-0.5">check_circle</span>
                            <div>
                                <span class="font-bold text-text-primary block">General Ledger Mutation Authority</span>
                                <span class="text-[11px] text-text-secondary">Level 4 authorization to post adjustments and journal corrections.</span>
                            </div>
                        </div>

                        <div class="p-3 rounded-lg bg-surface-subtle border border-border-subtle flex items-start gap-2.5">
                            <span class="material-symbols-outlined text-emerald-600 dark:text-emerald-400 text-[18px] shrink-0 mt-0.5">check_circle</span>
                            <div>
                                <span class="font-bold text-text-primary block">Single-Signatory Release Limit</span>
                                <span class="text-[11px] text-text-secondary">Authorized up to ₹50,00,000 individual transfer release.</span>
                            </div>
                        </div>

                        <div class="p-3 rounded-lg bg-surface-subtle border border-border-subtle flex items-start gap-2.5">
                            <span class="material-symbols-outlined text-blue-600 dark:text-blue-400 text-[18px] shrink-0 mt-0.5">security</span>
                            <div>
                                <span class="font-bold text-text-primary block">High-Value Settlement Mandate</span>
                                <span class="text-[11px] text-text-secondary">Dual-signatory verification (Four-Eyes Principle) enforced on &gt; ₹50L.</span>
                            </div>
                        </div>

                        <div class="p-3 rounded-lg bg-surface-subtle border border-border-subtle flex items-start gap-2.5">
                            <span class="material-symbols-outlined text-emerald-600 dark:text-emerald-400 text-[18px] shrink-0 mt-0.5">check_circle</span>
                            <div>
                                <span class="font-bold text-text-primary block">Staff &amp; Branch Administration</span>
                                <span class="text-[11px] text-text-secondary">Full authority to provision, audit, or suspend branch staff across all nodes.</span>
                            </div>
                        </div>

                        <div class="p-3 rounded-lg bg-surface-subtle border border-border-subtle flex items-start gap-2.5">
                            <span class="material-symbols-outlined text-emerald-600 dark:text-emerald-400 text-[18px] shrink-0 mt-0.5">check_circle</span>
                            <div>
                                <span class="font-bold text-text-primary block">Account Unlock &amp; Recovery Clearance</span>
                                <span class="text-[11px] text-text-secondary">Super Admin exclusive privilege to clear lockouts on administrator IDs.</span>
                            </div>
                        </div>

                        <div class="p-3 rounded-lg bg-surface-subtle border border-border-subtle flex items-start gap-2.5">
                            <span class="material-symbols-outlined text-amber-600 dark:text-amber-400 text-[18px] shrink-0 mt-0.5">lock_clock</span>
                            <div>
                                <span class="font-bold text-text-primary block">Yield &amp; Interest Engine Parameters</span>
                                <span class="text-[11px] text-text-secondary">Requires quorum vote of Central Asset-Liability Committee (ALCO).</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Session Telemetry & Governance Overview (lg:col-span-4) -->
            <div class="lg:col-span-4 space-y-6">
                <!-- Panel 1: Session Security & Terminal Metadata -->
                <div class="ds-card p-5 bg-surface border border-border-subtle rounded-xl shadow-xs space-y-3.5">
                    <div class="flex items-center gap-2.5 pb-3 border-b border-border-subtle">
                        <div class="w-8 h-8 rounded-lg bg-surface-subtle text-text-primary flex items-center justify-center border border-border-subtle">
                            <span class="material-symbols-outlined text-[17px]" aria-hidden="true">terminal</span>
                        </div>
                        <div>
                            <h3 class="font-headline font-bold text-sm text-text-primary">Session Telemetry</h3>
                            <p class="text-[10px] font-semibold text-text-secondary uppercase tracking-wider">Active Terminal State</p>
                        </div>
                    </div>

                    <div class="space-y-2.5 text-xs">
                        <div class="flex justify-between items-center py-1 border-b border-border-subtle">
                            <span class="text-[10px] font-bold text-text-secondary uppercase tracking-wider">Last Authenticated</span>
                            <span class="font-semibold text-text-primary" id="sec-last-login">—</span>
                        </div>
                        <div class="flex justify-between items-center py-1 border-b border-border-subtle">
                            <span class="text-[10px] font-bold text-text-secondary uppercase tracking-wider">Bound IP Address</span>
                            <span class="font-mono font-bold text-text-primary" id="sec-last-ip">—</span>
                        </div>
                        <div class="flex justify-between items-center py-1 border-b border-border-subtle">
                            <span class="text-[10px] font-bold text-text-secondary uppercase tracking-wider">Password Last Rotated</span>
                            <span class="font-semibold text-text-primary" id="sec-last-pwd">—</span>
                        </div>
                        <div class="flex justify-between items-center py-1 border-b border-border-subtle">
                            <span class="text-[10px] font-bold text-text-secondary uppercase tracking-wider">RBAC Role Scope</span>
                            <span class="font-bold text-text-primary" id="sec-role-scope">Super Administrator</span>
                        </div>
                        <div class="flex justify-between items-center py-1">
                            <span class="text-[10px] font-bold text-text-secondary uppercase tracking-wider">Terminal ID</span>
                            <span class="font-mono text-[11px] text-text-secondary">TERM-HQ-0194A</span>
                        </div>
                    </div>
                </div>

                <!-- Panel 2: Multi-Factor Authentication Policy -->
                <div class="ds-card p-5 bg-surface-subtle border border-border-subtle rounded-xl shadow-xs space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-text-primary uppercase tracking-wider">Two-Factor Authentication</span>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" id="sec-2fa-toggle" class="sr-only peer" onchange="window.AdminProfile.toggle2FA(this.checked)">
                            <div class="w-10 h-5 bg-zinc-300 dark:bg-zinc-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white dark:after:bg-zinc-900 after:border-border-subtle after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-zinc-900 dark:peer-checked:bg-white dark:peer-checked:after:bg-zinc-900"></div>
                        </label>
                    </div>
                    <p class="text-xs text-text-secondary leading-relaxed">Require secondary authentication challenge on all administrative logins, high-value transfers, and system parameter overrides.</p>
                    <div class="pt-1">
                        <span class="text-[10px] font-mono font-bold uppercase tracking-wider px-2.5 py-1 rounded bg-surface border border-border-subtle text-text-secondary" id="sec-2fa-label">DISABLED</span>
                    </div>
                </div>

                <!-- Panel 3: Institutional Compliance Badges -->
                <div class="ds-card p-5 bg-surface border border-border-subtle rounded-xl shadow-xs space-y-3">
                    <span class="text-[10px] font-bold text-text-secondary uppercase tracking-wider block">Statutory Audit Attestations</span>
                    <div class="space-y-2 text-xs">
                        <div class="flex items-center justify-between py-1 border-b border-border-subtle">
                            <span class="text-text-secondary">AML/CFT Officer Clearance</span>
                            <span class="text-emerald-600 dark:text-emerald-400 font-bold text-[11px]">Certified (Class 1)</span>
                        </div>
                        <div class="flex items-center justify-between py-1 border-b border-border-subtle">
                            <span class="text-text-secondary">Security Vetting Level</span>
                            <span class="font-bold text-[11px] text-text-primary">Tier-4 Root Custodian</span>
                        </div>
                        <div class="flex items-center justify-between py-1">
                            <span class="text-text-secondary">Mandatory Annual Audit</span>
                            <span class="text-emerald-600 dark:text-emerald-400 font-bold text-[11px]">Satisfied 2026</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════ -->
    <!-- 5. TAB 2: AUTHENTICATION, CRYPTOGRAPHY & CREDENTIALS            -->
    <!-- ═══════════════════════════════════════════════════════════════ -->
    <div id="tab-security" class="tab-panel hidden space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            <!-- Credential Rotation Card -->
            <div class="ds-card p-5 sm:p-6 bg-surface border border-border-subtle rounded-xl shadow-xs space-y-4">
                <div class="flex items-center gap-2.5 pb-3 border-b border-border-subtle">
                    <div class="w-8 h-8 rounded-lg bg-surface-subtle text-text-primary flex items-center justify-center border border-border-subtle">
                        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">lock_reset</span>
                    </div>
                    <div>
                        <h3 class="font-headline font-bold text-sm text-text-primary">Administrative Credential Rotation</h3>
                        <p class="text-xs text-text-secondary mt-0.5">Enforce statutory 90-day password complexity standards</p>
                    </div>
                </div>

                <div class="p-3 rounded-lg bg-surface-subtle border border-border-subtle text-xs text-text-secondary leading-relaxed">
                    <span class="font-bold text-text-primary block mb-0.5">Central Banking Password Standard:</span>
                    Must contain at least 8 characters including uppercase letters, numbers, and special symbols. Re-using previous 5 passwords is prohibited.
                </div>

                <div class="space-y-3.5">
                    <div>
                        <label for="curr-pw" class="fin-label mb-1 block text-[10px] font-bold text-text-secondary uppercase tracking-wider">Current Password *</label>
                        <div class="fin-input-wrapper relative">
                            <span class="material-symbols-outlined input-icon-left text-text-secondary">lock</span>
                            <input type="password" id="curr-pw" class="fin-input has-icon-left pr-10 bg-surface text-text-primary border-border-subtle text-xs" placeholder="Enter current administrative password">
                            <button type="button" onclick="window.AdminProfile.togglePW('curr-pw')" class="absolute right-3 text-text-secondary hover:text-text-primary focus:outline-none cursor-pointer" title="Toggle Visibility">
                                <span class="material-symbols-outlined text-[17px]">visibility</span>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label for="new-pw" class="fin-label mb-1 block text-[10px] font-bold text-text-secondary uppercase tracking-wider">New Password *</label>
                        <div class="fin-input-wrapper relative">
                            <span class="material-symbols-outlined input-icon-left text-text-secondary">key</span>
                            <input type="password" id="new-pw" class="fin-input has-icon-left pr-10 bg-surface text-text-primary border-border-subtle text-xs" placeholder="Minimum 8 characters" oninput="window.AdminProfile.checkStrength(this.value)">
                            <button type="button" onclick="window.AdminProfile.togglePW('new-pw')" class="absolute right-3 text-text-secondary hover:text-text-primary focus:outline-none cursor-pointer" title="Toggle Visibility">
                                <span class="material-symbols-outlined text-[17px]">visibility</span>
                            </button>
                        </div>
                        <div class="mt-1.5">
                            <div class="password-strength-bar w-full h-1 bg-surface-subtle rounded-full overflow-hidden">
                                <div class="password-strength-fill h-full transition-all duration-300 rounded-full" id="pw-bar" style="width:0%; background:var(--color-primary-base);"></div>
                            </div>
                            <div class="flex justify-between items-center mt-1">
                                <span class="text-[10px] text-text-secondary">Institutional Complexity Meter</span>
                                <span class="text-[10px] font-bold uppercase tracking-wider" id="pw-text"></span>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="conf-pw" class="fin-label mb-1 block text-[10px] font-bold text-text-secondary uppercase tracking-wider">Confirm New Password *</label>
                        <div class="fin-input-wrapper relative">
                            <span class="material-symbols-outlined input-icon-left text-text-secondary">check_circle</span>
                            <input type="password" id="conf-pw" class="fin-input has-icon-left pr-10 bg-surface text-text-primary border-border-subtle text-xs" placeholder="Confirm new password">
                            <button type="button" onclick="window.AdminProfile.togglePW('conf-pw')" class="absolute right-3 text-text-secondary hover:text-text-primary focus:outline-none cursor-pointer" title="Toggle Visibility">
                                <span class="material-symbols-outlined text-[17px]">visibility</span>
                            </button>
                        </div>
                    </div>

                    <div class="pt-2">
                        <button type="button" onclick="window.AdminProfile.updatePassword()" class="w-full py-2.5 rounded-lg text-xs font-bold uppercase tracking-wider bg-zinc-900 text-white hover:bg-black dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-100 shadow-xs transition-all cursor-pointer">
                            Commit Credential Rotation
                        </button>
                    </div>
                </div>
            </div>

            <!-- Self-Service Security Recovery Challenge Card -->
            <div class="ds-card p-5 sm:p-6 bg-surface border border-border-subtle rounded-xl shadow-xs space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-border-subtle">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-surface-subtle text-text-primary flex items-center justify-center border border-border-subtle">
                            <span class="material-symbols-outlined text-[18px]" aria-hidden="true">quiz</span>
                        </div>
                        <div>
                            <h3 class="font-headline font-bold text-sm text-text-primary">Self-Service Recovery Challenge</h3>
                            <p class="text-xs text-text-secondary mt-0.5">Cryptographic recovery challenge for locked administrative credentials</p>
                        </div>
                    </div>
                    <div id="sq-status-container"></div>
                </div>

                <p class="text-xs text-text-secondary leading-relaxed">Configure an internal recovery challenge question to restore administrative access if account lockouts occur due to repeated authentication failures.</p>

                <form id="sq-setup-form" onsubmit="window.AdminProfile.saveSecurityQuestion(event)" class="space-y-3.5">
                    <div>
                        <label for="sq-question" class="fin-label mb-1 block text-[10px] font-bold text-text-secondary uppercase tracking-wider">Recovery Question *</label>
                        <input type="text" id="sq-question" class="fin-input bg-surface text-text-primary border-border-subtle text-xs" placeholder="e.g. In what city was your initial core banking induction conducted?" minlength="10" required>
                    </div>
                    <div>
                        <label for="sq-answer" class="fin-label mb-1 block text-[10px] font-bold text-text-secondary uppercase tracking-wider">Recovery Answer *</label>
                        <input type="password" id="sq-answer" class="fin-input bg-surface text-text-primary border-border-subtle text-xs" placeholder="Confidential recovery answer" minlength="3" required>
                    </div>
                    <div class="pt-2 border-t border-border-subtle">
                        <label for="sq-curr-pw" class="fin-label mb-1 block text-[10px] font-bold text-text-secondary uppercase tracking-wider">Current Password (Authentication Verification) *</label>
                        <input type="password" id="sq-curr-pw" class="fin-input bg-surface text-text-primary border-border-subtle text-xs" placeholder="Verify officer credentials" required>
                    </div>
                    <div class="pt-2 flex justify-end">
                        <button type="submit" id="btn-save-sq" class="px-5 py-2 rounded-lg text-xs font-bold uppercase tracking-wider bg-zinc-900 text-white hover:bg-black dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-100 shadow-xs transition-all cursor-pointer">
                            <span id="sq-btn-text">Save Security Challenge</span>
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════ -->
    <!-- 6. TAB 3: REGULATORY AUDIT LEDGER                               -->
    <!-- ═══════════════════════════════════════════════════════════════ -->
    <div id="tab-activity" class="tab-panel hidden space-y-6">
        <div class="ds-card bg-surface border border-border-subtle rounded-xl shadow-xs overflow-hidden">
            <div class="p-5 border-b border-border-subtle flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-surface-subtle">
                <div>
                    <h3 class="font-headline font-bold text-sm text-text-primary flex items-center gap-2">
                        <span class="material-symbols-outlined text-text-secondary text-[18px]" aria-hidden="true">receipt_long</span>
                        Administrative Operations Audit Ledger
                    </h3>
                    <p class="text-xs text-text-secondary mt-0.5">Chronological, tamper-evident record of administrative operations, mutations, and session events.</p>
                </div>
                <div class="flex items-center gap-2">
                    <span id="activity-count-badge" class="px-2.5 py-1 bg-surface text-text-secondary text-[10px] font-mono font-bold rounded border border-border-subtle">0 Records</span>
                </div>
            </div>

            <!-- Filter Controls -->
            <div class="p-4 border-b border-border-subtle bg-surface flex flex-col lg:flex-row lg:items-center justify-between gap-3">
                <div class="flex flex-wrap items-center gap-3 flex-1">
                    <div class="fin-input-wrapper flex-1 min-w-[220px]">
                        <span class="material-symbols-outlined input-icon-left text-text-secondary">search</span>
                        <input type="text" id="activity-search-input" placeholder="Search audit logs by event, details, or IP address..." class="fin-input has-icon-left bg-surface text-text-primary border-border-subtle rounded-lg text-xs">
                    </div>

                    <div class="flex items-center gap-1.5">
                        <span class="text-[10px] font-bold text-text-secondary uppercase tracking-wider">From:</span>
                        <input type="date" id="activity-from-date" class="fin-input !h-9 bg-surface text-text-primary border-border-subtle rounded-lg text-xs px-2.5">
                    </div>

                    <div class="flex items-center gap-1.5">
                        <span class="text-[10px] font-bold text-text-secondary uppercase tracking-wider">To:</span>
                        <input type="date" id="activity-to-date" class="fin-input !h-9 bg-surface text-text-primary border-border-subtle rounded-lg text-xs px-2.5">
                    </div>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    <button type="button" onclick="window.AdminProfile.resetActivityFilter()" class="px-3 py-2 rounded-lg text-xs font-semibold bg-surface text-text-primary hover:bg-surface-hover border border-border-subtle shadow-2xs cursor-pointer transition-colors" title="Reset Filters">
                        <span class="material-symbols-outlined text-[15px]" aria-hidden="true">restart_alt</span>
                    </button>
                    <button type="button" onclick="window.AdminProfile.exportActivityCSV()" class="px-3.5 py-2 text-xs rounded-lg inline-flex items-center gap-1.5 font-bold uppercase tracking-wider bg-surface text-text-primary hover:bg-surface-hover border border-border-subtle shadow-2xs cursor-pointer transition-colors">
                        <span class="material-symbols-outlined text-[15px]" aria-hidden="true">download</span>
                        <span>Export CSV</span>
                    </button>
                </div>
            </div>

            <!-- Table -->
            <div class="ds-table-wrapper w-full border-0 shadow-none rounded-none bg-transparent overflow-x-auto">
                <table class="ds-table w-full text-left min-w-[700px]">
                    <thead class="bg-surface-subtle border-b border-border-subtle">
                        <tr>
                            <th class="py-2.5 px-5 text-[10px] font-bold text-text-secondary uppercase tracking-wider" scope="col">Event Classification</th>
                            <th class="py-2.5 px-5 text-[10px] font-bold text-text-secondary uppercase tracking-wider" scope="col">Operation Context &amp; Details</th>
                            <th class="py-2.5 px-5 text-[10px] font-bold text-text-secondary uppercase tracking-wider" scope="col">Originating IP</th>
                            <th class="py-2.5 px-5 text-right text-[10px] font-bold text-text-secondary uppercase tracking-wider" scope="col">Timestamp (UTC)</th>
                        </tr>
                    </thead>
                    <tbody id="activity-table-body" class="divide-y divide-border-subtle text-xs">
                        <tr><td colspan="4" class="text-center italic opacity-50 py-10 text-xs">Loading regulatory audit records...</td></tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="p-4 border-t border-border-subtle flex flex-col sm:flex-row items-center justify-between gap-3 bg-surface-subtle" id="activity-pagination-container">
                <div class="text-[11px] font-bold text-text-secondary uppercase tracking-wider" id="activity-pagination-info">Showing 0 to 0 of 0</div>
                <div class="flex gap-1.5">
                    <button id="activity-prev-btn" onclick="window.AdminProfile.prevActivityPage()" class="px-3 py-1 text-xs rounded-md font-bold bg-surface text-text-primary hover:bg-surface-hover border border-border-subtle shadow-2xs cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed transition-colors">Previous</button>
                    <button id="activity-next-btn" onclick="window.AdminProfile.nextActivityPage()" class="px-3 py-1 text-xs rounded-md font-bold bg-surface text-text-primary hover:bg-surface-hover border border-border-subtle shadow-2xs cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed transition-colors">Next</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════ -->
    <!-- 7. TAB 4: RBAC DIRECTORY & ADMINISTRATIVE GOVERNANCE            -->
    <!-- ═══════════════════════════════════════════════════════════════ -->
    <div id="tab-directory" class="tab-panel hidden space-y-6">
        
        <!-- LOCKED ACCOUNTS OVERSIGHT -->
        <div id="locked-admins-container" class="ds-card bg-surface border border-danger-border rounded-xl shadow-xs overflow-hidden hidden">
            <div class="p-4 border-b border-danger-border flex justify-between items-center bg-danger-bg">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-danger/20 text-danger flex items-center justify-center">
                        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">lock_clock</span>
                    </div>
                    <div>
                        <h3 class="font-headline font-bold text-sm text-danger">Locked Administrative Accounts</h3>
                        <p class="text-[11px] text-danger/80 mt-0.5">Accounts locked due to repeated failed authentication attempts.</p>
                    </div>
                </div>
                <span id="locked-count-badge" class="px-2 py-0.5 bg-danger/20 text-danger text-[10px] font-bold rounded uppercase tracking-wider border border-danger/30">0 Locked</span>
            </div>
            <div class="ds-table-wrapper w-full border-0 shadow-none rounded-none bg-transparent overflow-x-auto">
                <table class="ds-table w-full text-left min-w-[800px]">
                    <thead class="bg-surface-subtle border-b border-border-subtle">
                        <tr>
                            <th class="py-2.5 px-5 text-[10px] font-bold text-text-secondary uppercase tracking-wider" scope="col">Administrator</th>
                            <th class="py-2.5 px-4 text-[10px] font-bold text-text-secondary uppercase tracking-wider" scope="col">Role</th>
                            <th class="py-2.5 px-4 text-[10px] font-bold text-text-secondary uppercase tracking-wider" scope="col">Lock Timestamp</th>
                            <th class="py-2.5 px-4 text-[10px] font-bold text-text-secondary uppercase tracking-wider" scope="col">Trigger</th>
                            <th class="py-2.5 px-4 text-[10px] font-bold text-text-secondary uppercase tracking-wider" scope="col">Failed Challenges</th>
                            <th class="py-2.5 px-4 text-[10px] font-bold text-text-secondary uppercase tracking-wider" scope="col">Recovery Setup</th>
                            <th class="py-2.5 px-4 text-right text-[10px] font-bold text-text-secondary uppercase tracking-wider" scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody id="locked-admins-body" class="divide-y divide-border-subtle text-xs"></tbody>
                </table>
            </div>
        </div>

        <!-- GLOBAL RBAC DIRECTORY CARD -->
        <div id="admin-management-container" class="ds-card bg-surface border border-border-subtle rounded-xl shadow-xs overflow-hidden">
            <div class="p-5 border-b border-border-subtle flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-surface-subtle">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-surface text-text-primary flex items-center justify-center border border-border-subtle shadow-2xs">
                        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">admin_panel_settings</span>
                    </div>
                    <div>
                        <h3 class="font-headline font-bold text-sm text-text-primary">Administrative Directory &amp; RBAC Control</h3>
                        <p class="text-xs text-text-secondary mt-0.5">Manage administrative credentials, security roles, and system authorities.</p>
                    </div>
                </div>
                <div class="flex items-center gap-2.5">
                    <span id="management-count-badge" class="px-2.5 py-1 bg-surface text-text-secondary text-[10px] font-mono font-bold rounded border border-border-subtle">0 Admins</span>
                    <button id="header-add-admin-btn" type="button" onclick="window.AdminProfile.showAddModal()" class="px-3.5 py-1.5 rounded-lg text-xs font-bold uppercase tracking-wider inline-flex items-center gap-1.5 bg-zinc-900 text-white hover:bg-black dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-100 shadow-xs transition-all cursor-pointer">
                        <span class="material-symbols-outlined text-[15px]" aria-hidden="true">person_add</span>
                        <span>Provision Administrator</span>
                    </button>
                </div>
            </div>

            <!-- Filter Controls -->
            <div class="p-4 border-b border-border-subtle bg-surface flex flex-col sm:flex-row gap-3">
                <div class="fin-input-wrapper flex-1">
                    <span class="material-symbols-outlined input-icon-left text-text-secondary">search</span>
                    <input type="text" id="admin-search-input" placeholder="Search by name, corporate email, or ID..." class="fin-input has-icon-left bg-surface text-text-primary border-border-subtle rounded-lg text-xs">
                </div>
                
                <div class="flex flex-wrap sm:flex-nowrap gap-2.5">
                    <div class="finzo-dropdown min-w-[140px]" id="dropdown-admin-status-filter">
                        <button type="button" class="finzo-dropdown-trigger bg-surface text-text-primary border border-border-subtle rounded-lg px-3 py-2 flex items-center justify-between w-full text-xs font-semibold hover:border-text-primary transition-colors focus:ring-2 focus:ring-text-primary focus:outline-none cursor-pointer" aria-haspopup="listbox" aria-expanded="false">
                            <div class="flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[15px] text-text-secondary" aria-hidden="true">toggle_on</span>
                                <span class="dropdown-label text-text-primary font-bold">All Statuses</span>
                            </div>
                            <span class="material-symbols-outlined text-[16px] chevron transition-transform text-text-secondary" aria-hidden="true">expand_more</span>
                        </button>
                        <div class="finzo-dropdown-menu bg-surface dark:bg-[#18181B] border border-border-subtle rounded-lg shadow-xl py-1 z-50 min-w-[140px]" role="listbox">
                            <div class="finzo-dropdown-option is-selected px-3.5 py-1.5 text-xs font-semibold text-text-primary hover:bg-surface-hover cursor-pointer transition-colors" data-value="all">All Statuses</div>
                            <div class="finzo-dropdown-option px-3.5 py-1.5 text-xs font-semibold text-text-primary hover:bg-surface-hover cursor-pointer transition-colors" data-value="active">Active</div>
                            <div class="finzo-dropdown-option px-3.5 py-1.5 text-xs font-semibold text-text-primary hover:bg-surface-hover cursor-pointer transition-colors" data-value="suspended">Suspended</div>
                        </div>
                        <input type="hidden" id="admin-status-filter" value="all">
                    </div>

                    <div class="finzo-dropdown min-w-[140px]" id="dropdown-admin-role-filter">
                        <button type="button" class="finzo-dropdown-trigger bg-surface text-text-primary border border-border-subtle rounded-lg px-3 py-2 flex items-center justify-between w-full text-xs font-semibold hover:border-text-primary transition-colors focus:ring-2 focus:ring-text-primary focus:outline-none cursor-pointer" aria-haspopup="listbox" aria-expanded="false">
                            <div class="flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[15px] text-text-secondary" aria-hidden="true">shield_person</span>
                                <span class="dropdown-label text-text-primary font-bold">All Roles</span>
                            </div>
                            <span class="material-symbols-outlined text-[16px] chevron transition-transform text-text-secondary" aria-hidden="true">expand_more</span>
                        </button>
                        <div class="finzo-dropdown-menu bg-surface dark:bg-[#18181B] border border-border-subtle rounded-lg shadow-xl py-1 z-50 min-w-[140px]" role="listbox">
                            <div class="finzo-dropdown-option is-selected px-3.5 py-1.5 text-xs font-semibold text-text-primary hover:bg-surface-hover cursor-pointer transition-colors" data-value="all">All Roles</div>
                            <div class="finzo-dropdown-option px-3.5 py-1.5 text-xs font-semibold text-text-primary hover:bg-surface-hover cursor-pointer transition-colors" data-value="admin">Admin</div>
                            <div class="finzo-dropdown-option px-3.5 py-1.5 text-xs font-semibold text-text-primary hover:bg-surface-hover cursor-pointer transition-colors" data-value="moderator">Moderator</div>
                        </div>
                        <input type="hidden" id="admin-role-filter" value="all">
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="ds-table-wrapper w-full border-0 shadow-none rounded-none bg-transparent overflow-x-auto">
                <table class="ds-table w-full text-left min-w-[900px]">
                    <thead class="bg-surface-subtle border-b border-border-subtle">
                        <tr>
                            <th class="py-2.5 px-5 text-[10px] font-bold text-text-secondary uppercase tracking-wider" scope="col">ID</th>
                            <th class="py-2.5 px-5 text-[10px] font-bold text-text-secondary uppercase tracking-wider" scope="col">Administrator Details</th>
                            <th class="py-2.5 px-5 text-[10px] font-bold text-text-secondary uppercase tracking-wider" scope="col">Role</th>
                            <th class="py-2.5 px-5 text-[10px] font-bold text-text-secondary uppercase tracking-wider" scope="col">Access Scope</th>
                            <th class="py-2.5 px-5 text-[10px] font-bold text-text-secondary uppercase tracking-wider" scope="col">Status</th>
                            <th class="py-2.5 px-5 text-[10px] font-bold text-text-secondary uppercase tracking-wider" scope="col">Last Active</th>
                            <th class="py-2.5 px-5 text-[10px] font-bold text-text-secondary uppercase tracking-wider" scope="col">Provisioned By</th>
                            <th class="py-2.5 px-5 text-right text-[10px] font-bold text-text-secondary uppercase tracking-wider" scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="admin-management-body" class="divide-y divide-border-subtle text-xs"></tbody>
                </table>
            </div>
        </div>

    </div>

</div>

<!-- ═══════════════════════════════════════════════════════════════ -->
<!-- 8. MODALS & DIALOGS                                             -->
<!-- ═══════════════════════════════════════════════════════════════ -->

<!-- CREATE ADMINISTRATOR MODAL -->
<div id="add-admin-modal" class="fixed inset-0 z-[2000] hidden items-center justify-center p-4" role="dialog" aria-modal="true">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-xs transition-all" onclick="window.AdminProfile.closeAddModal()"></div>
    
    <div class="relative w-full max-w-2xl bg-surface text-text-primary rounded-xl overflow-hidden shadow-2xl flex flex-col max-h-[90vh] border border-border-subtle animate-modal-enter z-10">
        
        <!-- Header -->
        <div class="p-4 sm:p-5 border-b border-border-subtle flex justify-between items-center sticky top-0 bg-surface z-10 shrink-0">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-surface-subtle text-text-primary flex items-center justify-center border border-border-subtle">
                    <span class="material-symbols-outlined text-[18px]" aria-hidden="true">person_add</span>
                </div>
                <div>
                    <h3 class="font-headline font-bold text-base text-text-primary tracking-tight">Provision Administrator Account</h3>
                    <p class="text-xs text-text-secondary mt-0.5">Assign corporate credentials and delegated administrative permissions</p>
                </div>
            </div>
            <button type="button" onclick="window.AdminProfile.closeAddModal()" class="p-1 hover:bg-surface-hover rounded-md transition-colors text-text-secondary hover:text-text-primary cursor-pointer">
                <span class="material-symbols-outlined" aria-hidden="true">close</span>
            </button>
        </div>

        <!-- Body -->
        <div class="p-5 sm:p-6 overflow-y-auto flex-1 space-y-5 custom-scrollbar">
            
            <!-- SECTION 1: Personal & Contact Details -->
            <div>
                <div class="text-xs font-bold tracking-wider text-text-primary uppercase mb-3 py-1 border-b border-border-subtle flex items-center gap-2">
                    <span class="material-symbols-outlined text-sm" aria-hidden="true">badge</span>
                    1. Official Identity &amp; Contact Details
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label for="add-admin-id" class="fin-label mb-1 block text-[10px] font-bold text-text-secondary uppercase tracking-wider">Assigned Administrator UID</label>
                        <input type="text" id="add-admin-id" class="fin-input opacity-70 bg-surface-subtle font-mono text-text-primary text-xs" readonly>
                    </div>
                    <div>
                        <label for="add-name" class="fin-label mb-1 block text-[10px] font-bold text-text-secondary uppercase tracking-wider">Full Legal Name *</label>
                        <input type="text" id="add-name" class="fin-input bg-surface text-text-primary text-xs" placeholder="e.g. Rahul Sharma">
                        <span id="err-add-name" class="text-danger text-[11px] font-bold mt-1 hidden"></span>
                    </div>
                    <div>
                        <label for="add-dob" class="fin-label mb-1 block text-[10px] font-bold text-text-secondary uppercase tracking-wider">Date of Birth *</label>
                        <input type="date" id="add-dob" class="fin-input bg-surface text-text-primary text-xs">
                        <span id="err-add-dob" class="text-danger text-[11px] font-bold mt-1 hidden"></span>
                    </div>
                    <div>
                        <label class="fin-label mb-1 block text-[10px] font-bold text-text-secondary uppercase tracking-wider">Gender *</label>
                        <div class="finzo-dropdown" id="dropdown-add-gender">
                            <input type="hidden" id="add-gender" value="">
                            <button type="button" class="finzo-dropdown-trigger bg-surface text-text-primary border border-border-subtle rounded-lg px-3 py-2 flex items-center justify-between w-full text-xs font-semibold hover:border-text-primary transition-colors focus:ring-2 focus:ring-text-primary focus:outline-none" aria-haspopup="listbox" aria-expanded="false">
                                <span class="dropdown-label text-text-primary">Select Gender</span>
                                <span class="material-symbols-outlined text-[16px] chevron transition-transform text-text-secondary" aria-hidden="true">expand_more</span>
                            </button>
                            <div class="finzo-dropdown-menu bg-surface dark:bg-[#18181B] border border-border-subtle rounded-lg shadow-xl py-1 z-50" role="listbox">
                                <div class="finzo-dropdown-option px-3.5 py-1.5 text-xs font-semibold text-text-primary hover:bg-surface-hover cursor-pointer" data-value="Male">Male</div>
                                <div class="finzo-dropdown-option px-3.5 py-1.5 text-xs font-semibold text-text-primary hover:bg-surface-hover cursor-pointer" data-value="Female">Female</div>
                                <div class="finzo-dropdown-option px-3.5 py-1.5 text-xs font-semibold text-text-primary hover:bg-surface-hover cursor-pointer" data-value="Other">Other</div>
                                <div class="finzo-dropdown-option px-3.5 py-1.5 text-xs font-semibold text-text-primary hover:bg-surface-hover cursor-pointer" data-value="Prefer not to say">Prefer not to say</div>
                            </div>
                        </div>
                        <span id="err-add-gender" class="text-danger text-[11px] font-bold mt-1 hidden"></span>
                    </div>
                    <div>
                        <label for="add-phone" class="fin-label mb-1 block text-[10px] font-bold text-text-secondary uppercase tracking-wider">Contact Number *</label>
                        <input type="text" id="add-phone" class="fin-input bg-surface text-text-primary text-xs" placeholder="10-digit mobile number">
                        <span id="err-add-phone" class="text-danger text-[11px] font-bold mt-1 hidden"></span>
                    </div>
                    <div>
                        <label for="add-email" class="fin-label mb-1 block text-[10px] font-bold text-text-secondary uppercase tracking-wider">Institutional Email *</label>
                        <input type="email" id="add-email" class="fin-input bg-surface text-text-primary text-xs" placeholder="official@finzo.com">
                        <span id="err-add-email" class="text-danger text-[11px] font-bold mt-1 hidden"></span>
                    </div>
                </div>
            </div>

            <!-- SECTION 2: Initial Credentials -->
            <div>
                <div class="text-xs font-bold tracking-wider text-text-primary uppercase mb-3 py-1 border-b border-border-subtle flex items-center gap-2">
                    <span class="material-symbols-outlined text-sm" aria-hidden="true">key</span>
                    2. Authentication Credentials
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label for="add-pw" class="fin-label mb-1 block text-[10px] font-bold text-text-secondary uppercase tracking-wider">Initial Password *</label>
                        <input type="password" id="add-pw" class="fin-input bg-surface text-text-primary text-xs" placeholder="Minimum 8 characters" oninput="window.AdminProfile.checkAddStrength(this.value)">
                        <div class="password-strength-bar w-full h-1 bg-surface-subtle rounded-full overflow-hidden mt-1.5">
                            <div class="password-strength-fill h-full transition-all duration-300 rounded-full" id="add-pw-bar" style="width:0%; background:var(--color-primary-base);"></div>
                        </div>
                        <span id="err-add-pw" class="text-danger text-[11px] font-bold mt-1 hidden"></span>
                    </div>
                    <div>
                        <label for="add-conf-pw" class="fin-label mb-1 block text-[10px] font-bold text-text-secondary uppercase tracking-wider">Confirm Password *</label>
                        <input type="password" id="add-conf-pw" class="fin-input bg-surface text-text-primary text-xs" placeholder="Confirm password">
                        <span id="err-add-conf-pw" class="text-danger text-[11px] font-bold mt-1 hidden"></span>
                    </div>
                </div>
            </div>

            <!-- SECTION 3: Recovery Challenge & Role Assignment -->
            <div>
                <div class="text-xs font-bold tracking-wider text-text-primary uppercase mb-3 py-1 border-b border-border-subtle flex items-center gap-2">
                    <span class="material-symbols-outlined text-sm" aria-hidden="true">shield</span>
                    3. Recovery Challenge &amp; Delegated Role
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label for="add-seq-q" class="fin-label mb-1 block text-[10px] font-bold text-text-secondary uppercase tracking-wider">Security Question *</label>
                        <input type="text" id="add-seq-q" class="fin-input bg-surface text-text-primary text-xs" placeholder="e.g. What city was your initial core banking induction conducted?">
                        <span id="err-add-seq-q" class="text-danger text-[11px] font-bold mt-1 hidden"></span>
                    </div>
                    <div>
                        <label for="add-seq-a" class="fin-label mb-1 block text-[10px] font-bold text-text-secondary uppercase tracking-wider">Secret Answer *</label>
                        <input type="password" id="add-seq-a" class="fin-input bg-surface text-text-primary text-xs" placeholder="Confidential recovery answer">
                        <span id="err-add-seq-a" class="text-danger text-[11px] font-bold mt-1 hidden"></span>
                    </div>
                </div>

                <div class="mt-3.5 space-y-3.5">
                    <div>
                        <label class="fin-label mb-1 block text-[10px] font-bold text-text-secondary uppercase tracking-wider">Authorization Role Assignment *</label>
                        <div class="finzo-dropdown" id="dropdown-add-role">
                            <input type="hidden" id="add-role" value="" onchange="window.AdminProfile.updateRolePreview(this.value)">
                            <button type="button" class="finzo-dropdown-trigger bg-surface text-text-primary border border-border-subtle rounded-lg px-3 py-2 flex items-center justify-between w-full text-xs font-semibold hover:border-text-primary transition-colors focus:ring-2 focus:ring-text-primary focus:outline-none" aria-haspopup="listbox" aria-expanded="false">
                                <span class="dropdown-label text-text-primary">Select Role</span>
                                <span class="material-symbols-outlined text-[16px] chevron transition-transform text-text-secondary" aria-hidden="true">expand_more</span>
                            </button>
                            <div class="finzo-dropdown-menu bg-surface dark:bg-[#18181B] border border-border-subtle rounded-lg shadow-xl py-1 z-50" role="listbox">
                                <div class="finzo-dropdown-option px-3.5 py-1.5 text-xs font-semibold text-text-primary hover:bg-surface-hover cursor-pointer" data-value="admin">Administrator &mdash; Standard Operational Authority</div>
                                <div class="finzo-dropdown-option px-3.5 py-1.5 text-xs font-semibold text-text-primary hover:bg-surface-hover cursor-pointer" data-value="moderator">Moderator &mdash; Verification &amp; Approval Oversight</div>
                            </div>
                        </div>
                        <span id="err-add-role" class="text-danger text-[11px] font-bold mt-1 hidden"></span>
                    </div>

                    <div id="role-preview-box" class="p-3 rounded-lg bg-surface-subtle border border-border-subtle text-xs text-text-secondary hidden leading-relaxed"></div>

                    <div>
                        <label for="new-admin-photo" class="fin-label mb-1 block text-[10px] font-bold text-text-secondary uppercase tracking-wider">Officer Portrait Photo (Optional)</label>
                        <input type="file" id="new-admin-photo" accept="image/jpeg, image/png, image/webp" class="fin-input bg-surface text-text-primary text-xs" onchange="window.AdminProfile.previewNewPhoto(this)">
                        <span id="err-add-photo" class="text-danger text-[11px] font-bold mt-1 hidden"></span>
                        <div class="mt-2.5 flex items-center gap-2.5">
                            <img id="new-avatar-preview" src="" class="w-10 h-10 rounded-lg object-cover hidden border border-border-subtle shadow-2xs" alt="Preview">
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Footer -->
        <div class="p-4 border-t border-border-subtle bg-surface-subtle flex justify-end gap-2.5 sticky bottom-0 z-10 shrink-0">
            <button type="button" onclick="window.AdminProfile.closeAddModal()" class="px-4 py-2 rounded-lg text-xs font-semibold bg-surface text-text-primary hover:bg-surface-hover border border-border-subtle cursor-pointer transition-colors">Cancel</button>
            <button type="button" id="add-admin-submit-btn" onclick="window.AdminProfile.submitAddAdmin()" class="px-5 py-2 rounded-lg text-xs font-bold uppercase tracking-wider flex items-center gap-1.5 bg-zinc-900 text-white hover:bg-black dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-100 shadow-xs cursor-pointer transition-all">
                <span>Provision Account</span>
                <span class="material-symbols-outlined animate-spin text-[15px] hidden" aria-hidden="true">progress_activity</span>
            </button>
        </div>
    </div>
</div>

<!-- UNLOCK CONFIRMATION DIALOG -->
<div id="unlock-confirm-overlay" class="fixed inset-0 z-[2100] hidden items-center justify-center p-4" role="dialog" aria-modal="true">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-xs" onclick="window.AdminProfile.closeUnlockConfirm()"></div>
    <div class="relative w-full max-w-md bg-surface text-text-primary rounded-xl p-5 shadow-2xl border border-border-subtle z-10 space-y-3.5 animate-modal-enter">
        <div class="flex items-center gap-2.5 text-warning">
            <div class="w-9 h-9 rounded-lg bg-warning-bg text-warning flex items-center justify-center border border-warning-border">
                <span class="material-symbols-outlined text-lg" aria-hidden="true">lock_open</span>
            </div>
            <div>
                <h4 class="font-headline font-bold text-sm text-text-primary">Unlock Administrator Account</h4>
                <p class="text-xs text-text-secondary mt-0.5">Clear lockout flags &amp; reset authentication counter</p>
            </div>
        </div>
        <p id="unlock-confirm-msg" class="text-xs text-text-secondary leading-relaxed">Are you sure you want to restore access to this administrator account? Access permissions will be restored immediately.</p>
        <div class="flex justify-end gap-2 pt-2.5 border-t border-border-subtle">
            <button type="button" onclick="window.AdminProfile.closeUnlockConfirm()" class="px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-surface text-text-primary hover:bg-surface-hover border border-border-subtle cursor-pointer transition-colors">Cancel</button>
            <button type="button" id="confirm-unlock-btn" onclick="window.AdminProfile.confirmUnlock()" class="px-4 py-1.5 rounded-lg text-xs font-bold uppercase tracking-wider bg-zinc-900 text-white hover:bg-black dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-100 shadow-xs cursor-pointer transition-all">Confirm Unlock</button>
        </div>
    </div>
</div>

<?php
$extraScripts = '<script defer src="/Finzo/public/assets/js/admin/profile.js"></script>';
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>