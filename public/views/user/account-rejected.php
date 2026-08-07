<?php
$portal = 'user';
$pageTitle = 'Finzo';
ob_start();
?>
<div class="auth-split-grid">
    
    <!-- Left Information Panel -->
    <div class="auth-left-panel">
      <!-- Top: Branding -->
      <div>
        <div class="flex items-center gap-2 mb-8">
          <div class="flex items-center justify-center w-10 h-10 bg-white/10 rounded-md text-white">
            <span class="material-symbols-outlined text-2xl">account_balance</span>
          </div>
          <span class="font-display font-bold text-xl text-white tracking-tight">Finzo</span>
        </div>
        
        <h2 class="font-display text-4xl font-bold text-white mb-4 leading-tight">
          Application Review Process
        </h2>
        <p class="text-white/80 text-sm leading-relaxed mb-10 max-w-md">
          Every application is reviewed carefully to maintain banking security and ensure all regulatory standards are met.
        </p>

        <!-- Security Features -->
        <div class="space-y-2">
          <div class="auth-security-block">
            <div class="auth-security-icon">
              <span class="material-symbols-outlined text-lg">verified_user</span>
            </div>
            <div class="auth-security-content">
              <h4>Identity Verification</h4>
              <p>Verification protects both customers and financial services.</p>
            </div>
          </div>
          
          <div class="auth-security-block">
            <div class="auth-security-icon">
              <span class="material-symbols-outlined text-lg">support_agent</span>
            </div>
            <div class="auth-security-content">
              <h4>Need Assistance?</h4>
              <p>Our banking support team is available if you require additional guidance.</p>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Bottom: Copyright -->
      <div class="mt-8 pt-6 border-t border-white/10">
        <p class="text-xs text-white/50 font-medium tracking-wide uppercase">
          &copy; 2026 Finzo Banking Group. All rights reserved.
        </p>
      </div>
    </div>
    
    <!-- Right Authentication Area -->
    <div class="auth-right-area">
      <!-- Language Selector -->
      <div class="auth-lang-selector">
        <span class="material-symbols-outlined text-base">language</span>
        <span>EN</span>
        <span class="material-symbols-outlined text-base">expand_more</span>
      </div>
      
      <!-- Auth Card -->
      <div class="auth-card-wrapper w-full max-w-[480px]">
        <!-- Brand Header (Mobile Only) -->
        <div class="auth-brand-header lg:hidden">
          <div class="auth-brand-logo-container cursor-pointer" data-href="/Finzo/public/index.php">
            <span class="material-symbols-outlined text-2xl">account_balance</span>
          </div>
        </div>
        
        <div class="mb-6 text-center lg:text-left">
          <div class="w-16 h-16 rounded-full bg-slate-50 border border-slate-200 flex items-center justify-center mx-auto lg:mx-0 mb-4">
              <span class="material-symbols-outlined text-3xl text-slate-500">assignment_late</span>
          </div>
          <h1 class="auth-hero-title text-2xl">Application Requires Attention</h1>
          <p class="auth-subtitle text-sm">We were unable to approve your application based on the information currently available. Please review the details below and submit the required corrections.</p>
        </div>

        <div class="auth-form-body">
            
            <!-- Subtle Status Indicator -->
            <div class="flex items-center gap-2 mb-6 p-3 bg-slate-50 border border-slate-100 rounded-md">
                <span class="relative flex h-3 w-3">
                  <span class="relative inline-flex rounded-full h-3 w-3 bg-slate-400"></span>
                </span>
                <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">Status:</span>
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-auto">Not Approved</span>
            </div>

            <!-- Existing Timeline Preserved exactly for JS compatibility -->
            <div class="mb-6 p-5 border border-border-main rounded-lg bg-white">
                <div class="timeline flex flex-col">
                    <!-- Item 1 -->
                    <div class="flex gap-4">
                        <div class="flex flex-col items-center">
                            <div class="w-8 h-8 rounded-full bg-success-surface flex items-center justify-center shrink-0">
                                <span class="material-symbols-outlined text-success-base text-base">check_circle</span>
                            </div>
                            <div class="w-px flex-1 min-h-[1.5rem] bg-success-base my-1"></div>
                        </div>
                        <div class="pb-4 flex-1">
                            <div class="text-sm font-bold text-text-main">Registered</div>
                            <div class="text-xs text-text-muted mt-0.5">Account created successfully</div>
                        </div>
                    </div>
                    
                    <!-- Item 2 -->
                    <div class="flex gap-4">
                        <div class="flex flex-col items-center">
                            <div class="w-8 h-8 rounded-full bg-success-surface flex items-center justify-center shrink-0">
                                <span class="material-symbols-outlined text-success-base text-base">check_circle</span>
                            </div>
                            <div class="w-px flex-1 min-h-[1.5rem] bg-success-base my-1"></div>
                        </div>
                        <div class="pb-4 flex-1">
                            <div class="text-sm font-bold text-text-main">Profile Submitted</div>
                            <div class="text-xs text-text-muted mt-0.5">All documents uploaded successfully</div>
                        </div>
                    </div>
                    
                    <!-- Item 3 -->
                    <div class="flex gap-4">
                        <div class="flex flex-col items-center">
                            <div class="w-8 h-8 rounded-full bg-success-surface flex items-center justify-center shrink-0">
                                <span class="material-symbols-outlined text-success-base text-base">check_circle</span>
                            </div>
                            <div class="w-px flex-1 min-h-[1.5rem] bg-gradient-to-b from-success-base to-slate-300 my-1"></div>
                        </div>
                        <div class="pb-4 flex-1">
                            <div class="text-sm font-bold text-text-main">Admin Review</div>
                            <div class="text-xs text-text-muted mt-0.5">Application was reviewed by our team</div>
                        </div>
                    </div>
                    
                    <!-- Item 4 (Rejection) -->
                    <div class="flex gap-4">
                        <div class="flex flex-col items-center">
                            <div class="w-8 h-8 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center shrink-0">
                                <span class="material-symbols-outlined text-slate-500 text-base">assignment_returned</span>
                            </div>
                        </div>
                        <div class="flex-1">
                            <div class="text-sm font-bold text-slate-700">Requires Correction</div>
                            <div id="tl-rejection-reason" class="text-xs text-slate-600 mt-2 p-3 bg-slate-50 border border-slate-100 rounded-md leading-relaxed">
                                Account was not approved
                            </div>
                            <div id="tl-rejection-time" class="text-xs font-bold text-text-disabled uppercase tracking-widest mt-2"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Steps to Resubmit -->
            <div class="p-4 bg-slate-50 border border-slate-100 rounded-lg flex items-start gap-3 mb-6">
                <span class="material-symbols-outlined text-primary-500 text-xl shrink-0 mt-0.5">checklist</span>
                <div>
                    <div class="text-xs font-bold text-slate-800 mb-2">Next Steps</div>
                    <ul class="text-xs text-slate-600 leading-relaxed space-y-2 list-none">
                        <li class="flex items-start gap-2">
                            <span class="w-4 h-4 rounded-full bg-primary-100 text-primary-600 flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">1</span>
                            <span>Review your submitted information.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="w-4 h-4 rounded-full bg-primary-100 text-primary-600 flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">2</span>
                            <span>Correct incomplete or incorrect details based on the reason above.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="w-4 h-4 rounded-full bg-primary-100 text-primary-600 flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">3</span>
                            <span>Resubmit your application for an expedited review.</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="pt-2 flex flex-col sm:flex-row gap-3">
                <a href="/Finzo/public/views/auth/login.php" class="btn bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 flex-1 flex justify-center shadow-sm">
                    Return to Login
                </a>
                <a href="complete-profile.php" class="btn btn-primary flex-1 flex justify-center gap-1.5 shadow-sm">
                    <span>Update Application</span>
                    <span class="material-symbols-outlined text-base">arrow_forward</span>
                </a>
            </div>

        </div>

        <div class="auth-trust-badge mt-6 border-t border-slate-100 pt-6">
          <span class="material-symbols-outlined text-sm">shield</span>
          <span>Enterprise Application Processing</span>
        </div>
      </div>

            <!-- Global Footer -->
      <div class="auth-global-footer">
        <ul>
          <li><span class="flex items-center gap-1"><span class="material-symbols-outlined text-xs">enhanced_encryption</span> 256-bit Encryption</span></li>
          <li><span class="flex items-center gap-1"><span class="material-symbols-outlined text-xs">verified_user</span> Secure Digital Banking Access</span></li>
          <li><span class="flex items-center gap-1"><span class="material-symbols-outlined text-xs">gpp_good</span> Privacy Protected</span></li>
          <li><span class="flex items-center gap-1"><span class="material-symbols-outlined text-xs">credit_card</span> PCI DSS Compliant</span></li>
        </ul>
      </div>
    </div>
  </div>
<?php
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>