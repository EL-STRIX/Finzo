<?php
$pageTitle = 'Finzo';
$authBgImage = 'admin_bg.png';
$authHeroTitle = 'Enterprise System Management';
$authHeroSubtitle = 'Centralized core system configuration, security audit trail monitoring, interest yield management, and global access administration.';
$authSecurityIcon = 'security';
$authSecurityTitle = 'Zero-Trust Terminal Protection';
$authSecurityDesc = 'Hardware security token validation combined with IP whitelist checks protect administrative system privileges.';
ob_start();
?>
<div class="mb-8 text-center lg:text-left">
                        <h1 class="text-3xl font-bold font-display text-text-main mb-2 tracking-tight">Admin Authentication</h1>
                        <p class="text-sm text-text-muted leading-relaxed">Strictly authorized administrative personnel only.</p>
                    </div>

                    <!-- Role Switcher Component -->
                    <div class="flex bg-surface-subtle p-1 rounded-lg mb-8 border border-border-main" role="tablist" aria-label="Select Authentication Role">
                        <a href="login.php" class="flex-1 text-center py-2 text-sm font-medium text-text-muted hover:text-text-main transition-colors" role="tab" aria-selected="false">Customer</a>
                        <a href="staff-login.php" class="flex-1 text-center py-2 text-sm font-medium text-text-muted hover:text-text-main transition-colors" role="tab" aria-selected="false">Staff</a>
                        <a href="admin-login.php" class="flex-1 text-center py-2 text-sm font-semibold rounded-md bg-surface-main text-primary-base shadow-sm border border-border-subtle" role="tab" aria-selected="true">Admin</a>
                    </div>

                    <!-- Alert Warning Component -->
                    <div class="flex items-start gap-3 p-4 rounded-md mb-6 text-sm bg-danger-surface border border-danger-surface text-danger-text" role="alert">
                        <span class="material-symbols-outlined text-xl flex-shrink-0 mt-0.5" aria-hidden="true">warning</span>
                        <div>
                            <strong>Mandatory Audit Notice:</strong> Access to this terminal is subject to real-time activity recording and strict forensic logging.
                        </div>
                    </div>

                    <!-- Admin Form Component -->
                    <form data-action="admin-login" method="POST" novalidate>
                        <input type="hidden" name="csrf_token" value="" />
                        
                        <!-- Administrator Email Input Component -->
                        <div class="fin-input-container">
                            <label for="admin-id" class="fin-label fin-label-required">Administrator Email</label>
                            <div class="fin-input-wrapper">
                                <span class="material-symbols-outlined input-icon-left" aria-hidden="true">shield_person</span>
                                <input type="email" id="admin-id" name="email" class="fin-input has-icon-left" placeholder="admin@finzo.com" required autocomplete="username" />
                            </div>
                            <span class="fin-error-text" role="alert">
                                <span class="material-symbols-outlined" aria-hidden="true">error</span> Administrator email is required.
                            </span>
                        </div>

                        <!-- Security Token / Password Input Component -->
                        <div class="fin-input-container">
                            <label for="admin-token" class="fin-label fin-label-required">Security Token / Password</label>
                            <div class="fin-input-wrapper">
                                <span class="material-symbols-outlined input-icon-left" aria-hidden="true">vpn_key</span>
                                <input type="password" id="admin-token" name="password" class="fin-input has-icon-left has-icon-right" placeholder="Enter security token or password" required autocomplete="current-password" />
                                <button type="button" class="password-toggle-btn" aria-label="Toggle password visibility" tabindex="0">
                                    <span class="material-symbols-outlined" aria-hidden="true">visibility</span>
                                </button>
                            </div>
                            <span class="fin-error-text" role="alert">
                                <span class="material-symbols-outlined" aria-hidden="true">error</span> Security Token is required.
                            </span>
                        </div>

                        <!-- Submit Button Component -->
                        <button type="submit" class="btn btn-primary btn-full mt-2">
                            <span>Access Terminal</span>
                            <span class="material-symbols-outlined text-lg" aria-hidden="true">terminal</span>
                        </button>
                    </form>

                    <!-- Security Notice Component -->
                    <div class="flex items-start gap-3 bg-surface-subtle border border-border-subtle rounded-xl p-4 mt-8">
                        <span class="material-symbols-outlined text-primary-base text-xl flex-shrink-0 mt-0.5" aria-hidden="true">policy</span>
                        <div class="text-xs text-text-muted leading-relaxed">
                            <strong>Restricted Terminal:</strong> All administrative actions require hardware security key authentication and supervisory sign-off.
                        </div>
                    </div>

                    <!-- Trust Badges Component -->
                    <div class="flex flex-wrap items-center justify-center gap-4 mt-8 pt-6 border-t border-border-subtle">
                        <span class="inline-flex items-center gap-1.5 text-xs font-medium text-text-muted"><span class="material-symbols-outlined" aria-hidden="true">admin_panel_settings</span> Zero-Trust Control</span>
                        <span class="inline-flex items-center gap-1.5 text-xs font-medium text-text-muted"><span class="material-symbols-outlined" aria-hidden="true">fingerprint</span> Hardware Token SSL</span>
                    </div>
<?php
$slot = ob_get_clean();
include __DIR__ . '/../layouts/auth-layout.php';
?>