<?php
$portal = 'user';
$pageTitle = 'Complete Your Profile | Finzo Banking';

$initialBranches = [];
try {
    $pdo = \App\Shared\Core\Database::getInstance();
    $stmt = $pdo->query("SELECT id, name, branch_code, state FROM branches WHERE status = 'active' ORDER BY name ASC");
    $initialBranches = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (\Throwable $e) {
    // Fallback will be handled by client-side loadBranches()
}

ob_start();
?>
<style>
.step-content { display: none; }
.step-content.active { display: block; }
</style>

<div class="mb-8 ds-animate-fade">
    <div class="text-sm font-bold text-text-muted uppercase tracking-wider mb-1">Onboarding</div>
    <h1 class="font-display text-3xl font-extrabold text-text-main tracking-tight">Complete Your Profile</h1>
    <p class="text-text-muted mt-1">Complete the remaining required KYC information to activate your Finzo Banking account.</p>
</div>

<!-- Stepper Container -->
<div class="ds-card p-6 lg:p-8 max-w-4xl mx-auto mb-8 ds-animate-slide-up">
    <!-- Stepper Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between relative mb-4">
            <!-- Step 1 -->
            <div class="flex flex-col items-center z-10">
                <div id="sc1" class="w-8 h-8 rounded-full bg-primary-base text-white flex items-center justify-center border-4 border-surface shadow-md transition-colors duration-300 font-bold text-xs">
                    <span class="material-symbols-outlined text-[16px]">person</span>
                </div>
                <span id="sl1" class="text-[10px] font-bold text-primary-base uppercase tracking-widest mt-2 transition-colors duration-300">Personal</span>
            </div>

            <!-- Line 1 -->
            <div id="line1" class="flex-1 h-0.5 bg-border-main -mt-6 transition-colors duration-300 mx-2"></div>

            <!-- Step 2 -->
            <div class="flex flex-col items-center z-10">
                <div id="sc2" class="w-8 h-8 rounded-full bg-surface-elevated text-text-muted font-bold text-xs flex items-center justify-center border-4 border-surface transition-colors duration-300">2</div>
                <span id="sl2" class="text-[10px] font-bold text-text-muted uppercase tracking-widest mt-2 transition-colors duration-300">Address</span>
            </div>

            <!-- Line 2 -->
            <div id="line2" class="flex-1 h-0.5 bg-border-main -mt-6 transition-colors duration-300 mx-2"></div>

            <!-- Step 3 -->
            <div class="flex flex-col items-center z-10">
                <div id="sc3" class="w-8 h-8 rounded-full bg-surface-elevated text-text-muted font-bold text-xs flex items-center justify-center border-4 border-surface transition-colors duration-300">3</div>
                <span id="sl3" class="text-[10px] font-bold text-text-muted uppercase tracking-widest mt-2 transition-colors duration-300">Documents</span>
            </div>

            <!-- Line 3 -->
            <div id="line3" class="flex-1 h-0.5 bg-border-main -mt-6 transition-colors duration-300 mx-2"></div>

            <!-- Step 4 -->
            <div class="flex flex-col items-center z-10">
                <div id="sc4" class="w-8 h-8 rounded-full bg-surface-elevated text-text-muted font-bold text-xs flex items-center justify-center border-4 border-surface transition-colors duration-300">4</div>
                <span id="sl4" class="text-[10px] font-bold text-text-muted uppercase tracking-widest mt-2 transition-colors duration-300">Banking</span>
            </div>
        </div>

        <!-- Progress Bar Background -->
        <div class="w-full bg-surface-elevated h-1.5 rounded-full overflow-hidden">
            <div id="progress-bar" class="bg-primary-base h-full transition-all duration-300" style="width: 25%;"></div>
        </div>
    </div>

    <!-- STEP 1: PERSONAL DETAILS -->
    <div class="step-content active" id="step1">
        <div class="mb-6 border-b border-border-subtle pb-3">
            <h3 class="font-display text-lg font-bold text-text-main">1. Personal Information</h3>
            <p class="text-xs text-text-muted mt-0.5">Please provide your personal records as registered on legal identification.</p>
        </div>

        <!-- Profile Photo Upload -->
        <div class="mb-6">
            <label class="fin-label fin-label-required mb-2">Profile Photo</label>
            <div class="flex items-center gap-5">
                <div class="w-24 h-24 rounded-full border-2 border-dashed border-border-strong hover:border-primary bg-surface-elevated flex items-center justify-center cursor-pointer transition-colors overflow-hidden relative group" id="profile-box" onclick="document.getElementById('profile-input').click()">
                    <input type="file" id="profile-input" accept="image/jpeg,image/png" class="hidden" onchange="handleUpload(this,'profile-box','profile-preview')" />
                    <div id="profile-preview-wrap" class="text-center p-2">
                        <span class="material-symbols-outlined text-2xl text-text-muted group-hover:text-primary transition-colors">add_a_photo</span>
                        <div class="text-[10px] font-bold text-text-secondary mt-0.5">Upload</div>
                    </div>
                    <img loading="lazy" id="profile-preview" class="w-full h-full object-cover hidden" alt="Profile Preview" />
                </div>
                <div class="text-xs text-text-muted leading-relaxed">
                    <p class="font-semibold text-text-main">Passport-style headshot</p>
                    <p>Clear face, white or plain background. JPG or PNG under 5MB.</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <!-- Date of Birth -->
            <div class="fin-input-container">
                <label for="dob" class="fin-label fin-label-required">Date of Birth</label>
                <div class="fin-input-wrapper">
                    <span class="material-symbols-outlined input-icon-left">calendar_today</span>
                    <input type="date" id="dob" class="fin-input has-icon-left" required />
                </div>
            </div>

            <!-- Gender Dropdown -->
            <div class="fin-input-container">
                <label class="fin-label fin-label-required">Gender</label>
                <div class="finzo-dropdown relative w-full">
                    <button type="button" class="finzo-dropdown-trigger fin-input w-full text-left flex items-center justify-between">
                        <span class="dropdown-label text-text-muted">Select gender</span>
                        <span class="material-symbols-outlined transition-transform duration-200 chevron">expand_more</span>
                    </button>
                    <div class="finzo-dropdown-menu">
                        <div class="finzo-dropdown-option" data-value="Male">Male</div>
                        <div class="finzo-dropdown-option" data-value="Female">Female</div>
                        <div class="finzo-dropdown-option" data-value="Other">Other</div>
                        <div class="finzo-dropdown-option" data-value="Prefer not to say">Prefer not to say</div>
                    </div>
                    <input type="hidden" id="gender" name="gender" />
                </div>
            </div>

            <!-- Father's Name -->
            <div class="fin-input-container">
                <label for="father-name" class="fin-label fin-label-required">Father's Legal Name</label>
                <div class="fin-input-wrapper">
                    <span class="material-symbols-outlined input-icon-left">person</span>
                    <input type="text" id="father-name" class="fin-input has-icon-left" placeholder="Full name as on ID" required />
                </div>
            </div>

            <!-- Mother's Name -->
            <div class="fin-input-container">
                <label for="mother-name" class="fin-label fin-label-required">Mother's Legal Name</label>
                <div class="fin-input-wrapper">
                    <span class="material-symbols-outlined input-icon-left">person</span>
                    <input type="text" id="mother-name" class="fin-input has-icon-left" placeholder="Full name as on ID" required />
                </div>
            </div>

            <!-- Marital Status -->
            <div class="fin-input-container">
                <label class="fin-label fin-label-required">Marital Status</label>
                <div class="finzo-dropdown relative w-full">
                    <button type="button" class="finzo-dropdown-trigger fin-input w-full text-left flex items-center justify-between">
                        <span class="dropdown-label text-text-muted">Select status</span>
                        <span class="material-symbols-outlined transition-transform duration-200 chevron">expand_more</span>
                    </button>
                    <div class="finzo-dropdown-menu">
                        <div class="finzo-dropdown-option" data-value="Single">Single</div>
                        <div class="finzo-dropdown-option" data-value="Married">Married</div>
                        <div class="finzo-dropdown-option" data-value="Divorced">Divorced</div>
                        <div class="finzo-dropdown-option" data-value="Widowed">Widowed</div>
                        <div class="finzo-dropdown-option" data-value="Prefer not to say">Prefer not to say</div>
                    </div>
                    <input type="hidden" id="marital-status" name="marital-status" />
                </div>
            </div>

            <!-- Nationality -->
            <div class="fin-input-container">
                <label for="nationality" class="fin-label fin-label-required">Nationality</label>
                <div class="fin-input-wrapper">
                    <span class="material-symbols-outlined input-icon-left">flag</span>
                    <input type="text" id="nationality" class="fin-input has-icon-left" value="Indian" placeholder="e.g. Indian" required />
                </div>
            </div>

            <!-- Occupation -->
            <div class="fin-input-container">
                <label for="occupation" class="fin-label fin-label-required">Occupation</label>
                <div class="fin-input-wrapper">
                    <span class="material-symbols-outlined input-icon-left">work</span>
                    <input type="text" id="occupation" class="fin-input has-icon-left" placeholder="e.g. Software Engineer, Business Owner" required />
                </div>
            </div>

            <!-- Annual Income -->
            <div class="fin-input-container">
                <label class="fin-label fin-label-required">Annual Income Range</label>
                <div class="finzo-dropdown relative w-full">
                    <button type="button" class="finzo-dropdown-trigger fin-input w-full text-left flex items-center justify-between">
                        <span class="dropdown-label text-text-muted">Select income bracket</span>
                        <span class="material-symbols-outlined transition-transform duration-200 chevron">expand_more</span>
                    </button>
                    <div class="finzo-dropdown-menu">
                        <div class="finzo-dropdown-option" data-value="Below ₹2,50,000">Below ₹2,50,000</div>
                        <div class="finzo-dropdown-option" data-value="₹2,50,000 - ₹5,00,000">₹2,50,000 - ₹5,00,000</div>
                        <div class="finzo-dropdown-option" data-value="₹5,00,000 - ₹10,00,000">₹5,00,000 - ₹10,00,000</div>
                        <div class="finzo-dropdown-option" data-value="₹10,00,000 - ₹25,00,000">₹10,00,000 - ₹25,00,000</div>
                        <div class="finzo-dropdown-option" data-value="Above ₹25,00,000">Above ₹25,00,000</div>
                    </div>
                    <input type="hidden" id="income" name="income" />
                </div>
            </div>

            <!-- Source of Income -->
            <div class="fin-input-container md:col-span-2">
                <label class="fin-label fin-label-required">Source of Income</label>
                <div class="finzo-dropdown relative w-full">
                    <button type="button" class="finzo-dropdown-trigger fin-input w-full text-left flex items-center justify-between">
                        <span class="dropdown-label text-text-muted">Select source</span>
                        <span class="material-symbols-outlined transition-transform duration-200 chevron">expand_more</span>
                    </button>
                    <div class="finzo-dropdown-menu">
                        <div class="finzo-dropdown-option" data-value="Salary / Employment">Salary / Employment</div>
                        <div class="finzo-dropdown-option" data-value="Business / Self-employed">Business / Self-employed</div>
                        <div class="finzo-dropdown-option" data-value="Investments / Dividends">Investments / Dividends</div>
                        <div class="finzo-dropdown-option" data-value="Inheritance / Family Support">Inheritance / Family Support</div>
                        <div class="finzo-dropdown-option" data-value="Other">Other</div>
                    </div>
                    <input type="hidden" id="source-of-income" name="source-of-income" />
                </div>
            </div>
        </div>

        <div class="flex justify-end mt-8 pt-5 border-t border-border-subtle">
            <button type="button" class="btn btn-primary" onclick="nextStep(1)">
                <span>Continue to Address</span>
                <span class="material-symbols-outlined text-lg">arrow_forward</span>
            </button>
        </div>
    </div>

    <!-- STEP 2: ADDRESS -->
    <div class="step-content" id="step2">
        <div class="mb-6 border-b border-border-subtle pb-3">
            <h3 class="font-display text-lg font-bold text-text-main">2. Residential Address</h3>
            <p class="text-xs text-text-muted mt-0.5">Enter your primary residential address for communications and verification.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <!-- Street Address -->
            <div class="fin-input-container md:col-span-2">
                <label for="street" class="fin-label fin-label-required">Flat / House No. & Street Address</label>
                <div class="fin-input-wrapper">
                    <span class="material-symbols-outlined input-icon-left">home</span>
                    <input type="text" id="street" class="fin-input has-icon-left" placeholder="e.g. Flat 402, Green Valley Apartments, MG Road" required />
                </div>
            </div>

            <!-- City -->
            <div class="fin-input-container">
                <label for="city" class="fin-label fin-label-required">City / District</label>
                <div class="fin-input-wrapper">
                    <span class="material-symbols-outlined input-icon-left">location_city</span>
                    <input type="text" id="city" class="fin-input has-icon-left" placeholder="e.g. Mumbai" required />
                </div>
            </div>

            <!-- State -->
            <div class="fin-input-container">
                <label for="state" class="fin-label fin-label-required">State / Province</label>
                <div class="fin-input-wrapper">
                    <span class="material-symbols-outlined input-icon-left">map</span>
                    <input type="text" id="state" class="fin-input has-icon-left" placeholder="e.g. Maharashtra" required />
                </div>
            </div>

            <!-- Pincode -->
            <div class="fin-input-container">
                <label for="pincode" class="fin-label fin-label-required">PIN Code (6 Digits)</label>
                <div class="fin-input-wrapper">
                    <span class="material-symbols-outlined input-icon-left">pin</span>
                    <input type="text" id="pincode" maxlength="6" class="fin-input has-icon-left" placeholder="e.g. 400001" required />
                </div>
            </div>

            <!-- Country -->
            <div class="fin-input-container">
                <label class="fin-label">Country</label>
                <div class="fin-input-wrapper">
                    <span class="material-symbols-outlined input-icon-left">public</span>
                    <input type="text" class="fin-input has-icon-left" value="India" readonly disabled />
                </div>
            </div>
        </div>

        <div class="flex justify-between mt-8 pt-5 border-t border-border-subtle">
            <button type="button" class="btn btn-secondary" onclick="prevStep(2)">
                <span class="material-symbols-outlined text-lg">arrow_back</span>
                <span>Back</span>
            </button>
            <button type="button" class="btn btn-primary" onclick="nextStep(2)">
                <span>Continue to Documents</span>
                <span class="material-symbols-outlined text-lg">arrow_forward</span>
            </button>
        </div>
    </div>

    <!-- STEP 3: IDENTITY DOCUMENTS -->
    <div class="step-content" id="step3">
        <div class="mb-6 border-b border-border-subtle pb-3">
            <h3 class="font-display text-lg font-bold text-text-main">3. Identity Documents (KYC)</h3>
            <p class="text-xs text-text-muted mt-0.5">Government-issued identity verification compliant with RBI KYC guidelines.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-6">
            <!-- ID Type Dropdown -->
            <div class="fin-input-container">
                <label class="fin-label fin-label-required">Document Type</label>
                <div class="finzo-dropdown relative w-full">
                    <button type="button" class="finzo-dropdown-trigger fin-input w-full text-left flex items-center justify-between">
                        <span class="dropdown-label text-text-muted">Select ID type</span>
                        <span class="material-symbols-outlined transition-transform duration-200 chevron">expand_more</span>
                    </button>
                    <div class="finzo-dropdown-menu">
                        <div class="finzo-dropdown-option" data-value="Aadhaar">Aadhaar Card</div>
                        <div class="finzo-dropdown-option" data-value="PAN">PAN Card</div>
                        <div class="finzo-dropdown-option" data-value="Passport">Passport</div>
                        <div class="finzo-dropdown-option" data-value="Voter ID">Voter ID</div>
                        <div class="finzo-dropdown-option" data-value="Driving License">Driving License</div>
                    </div>
                    <input type="hidden" id="id-type" name="id-type" />
                </div>
            </div>

            <!-- ID Number -->
            <div class="fin-input-container">
                <label for="id-number" class="fin-label fin-label-required">Document Number</label>
                <div class="fin-input-wrapper">
                    <span class="material-symbols-outlined input-icon-left">badge</span>
                    <input type="text" id="id-number" class="fin-input has-icon-left" placeholder="Enter full ID / card number" required />
                </div>
            </div>
        </div>

        <!-- Document Uploads (Front & Back) -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-6">
            <!-- Front Image -->
            <div>
                <label class="fin-label fin-label-required mb-2">Front Side of Document</label>
                <div class="border-2 border-dashed border-border-strong hover:border-primary bg-surface-elevated rounded-lg p-4 flex flex-col items-center justify-center cursor-pointer transition-colors text-center h-36 relative group overflow-hidden" id="front-box" onclick="document.getElementById('front-input').click()">
                    <input type="file" id="front-input" accept="image/jpeg,image/png" class="hidden" onchange="handleUpload(this,'front-box','front-preview')" />
                    <div id="front-preview-wrap">
                        <span class="material-symbols-outlined text-3xl text-text-muted group-hover:text-primary transition-colors mb-1">document_scanner</span>
                        <div class="text-xs font-bold text-text-main">Upload Front Side</div>
                        <div class="text-[10px] text-text-muted mt-0.5">JPG or PNG (Max 5MB)</div>
                    </div>
                    <img loading="lazy" id="front-preview" class="w-full h-full object-cover rounded-md hidden" alt="Front Preview" />
                </div>
            </div>

            <!-- Back Image -->
            <div>
                <label class="fin-label fin-label-required mb-2">Back Side of Document</label>
                <div class="border-2 border-dashed border-border-strong hover:border-primary bg-surface-elevated rounded-lg p-4 flex flex-col items-center justify-center cursor-pointer transition-colors text-center h-36 relative group overflow-hidden" id="back-box" onclick="document.getElementById('back-input').click()">
                    <input type="file" id="back-input" accept="image/jpeg,image/png" class="hidden" onchange="handleUpload(this,'back-box','back-preview')" />
                    <div id="back-preview-wrap">
                        <span class="material-symbols-outlined text-3xl text-text-muted group-hover:text-primary transition-colors mb-1">document_scanner</span>
                        <div class="text-xs font-bold text-text-main">Upload Back Side</div>
                        <div class="text-[10px] text-text-muted mt-0.5">JPG or PNG (Max 5MB)</div>
                    </div>
                    <img loading="lazy" id="back-preview" class="w-full h-full object-cover rounded-md hidden" alt="Back Preview" />
                </div>
            </div>
        </div>

        <div class="p-3 bg-info-surface/40 border border-info/20 rounded-lg flex items-start gap-2 text-xs text-text-secondary">
            <span class="material-symbols-outlined text-info text-base shrink-0 mt-0.5">lock</span>
            <span>Your identity documents are encrypted at rest (AES-256) and verified strictly by compliance personnel.</span>
        </div>

        <div class="flex justify-between mt-8 pt-5 border-t border-border-subtle">
            <button type="button" class="btn btn-secondary" onclick="prevStep(3)">
                <span class="material-symbols-outlined text-lg">arrow_back</span>
                <span>Back</span>
            </button>
            <button type="button" class="btn btn-primary" onclick="nextStep(3)">
                <span>Continue to Banking</span>
                <span class="material-symbols-outlined text-lg">arrow_forward</span>
            </button>
        </div>
    </div>

    <!-- STEP 4: BANKING PREFERENCES -->
    <div class="step-content" id="step4">
        <div class="mb-6 border-b border-border-subtle pb-3">
            <h3 class="font-display text-lg font-bold text-text-main">4. Banking Information & Preferences</h3>
            <p class="text-xs text-text-muted mt-0.5">Choose your primary account type and servicing branch.</p>
        </div>

        <!-- Account Tier Selection -->
        <div class="mb-6">
            <label class="fin-label fin-label-required mb-3 block">Select Account Tier</label>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <label class="cursor-pointer">
                    <input type="radio" name="account-type" value="savings" class="hidden peer" checked />
                    <div class="p-4 rounded-xl border border-border-main peer-checked:border-primary peer-checked:bg-primary/5 hover:border-primary/50 transition-colors h-full flex flex-col justify-between">
                        <div>
                            <div class="flex justify-between items-center mb-1">
                                <span class="font-display text-sm font-bold text-text-main">Standard Savings</span>
                                <span class="material-symbols-outlined text-primary text-lg opacity-0 peer-checked:opacity-100 transition-opacity">check_circle</span>
                            </div>
                            <div class="text-xs text-text-muted leading-relaxed">Zero maintenance, 4% APY, instant transfers. Best for personal banking.</div>
                        </div>
                    </div>
                </label>

                <label class="cursor-pointer">
                    <input type="radio" name="account-type" value="current" class="hidden peer" />
                    <div class="p-4 rounded-xl border border-border-main peer-checked:border-primary peer-checked:bg-primary/5 hover:border-primary/50 transition-colors h-full flex flex-col justify-between">
                        <div>
                            <div class="flex justify-between items-center mb-1">
                                <span class="font-display text-sm font-bold text-text-main">Business Current</span>
                                <span class="material-symbols-outlined text-primary text-lg opacity-0 peer-checked:opacity-100 transition-opacity">check_circle</span>
                            </div>
                            <div class="text-xs text-text-muted leading-relaxed">Higher daily limits, unlimited transaction processing, commercial banking.</div>
                        </div>
                    </div>
                </label>
            </div>
        </div>

        <!-- Preferred Branch -->
        <div class="fin-input-container mb-6">
            <label class="fin-label fin-label-required">Servicing Branch</label>
            <div class="finzo-dropdown relative w-full" id="branch-dropdown-container">
                <button type="button" class="finzo-dropdown-trigger fin-input w-full text-left flex items-center justify-between">
                    <span class="dropdown-label text-text-muted">Select local branch</span>
                    <span class="material-symbols-outlined transition-transform duration-200 chevron">expand_more</span>
                </button>
                <div class="finzo-dropdown-menu" id="branch-options">
                    <?php if (!empty($initialBranches)): ?>
                        <?php foreach ($initialBranches as $ib): ?>
                            <?php 
                                $code = !empty($ib['branch_code']) ? ' (' . $ib['branch_code'] . ')' : '';
                                $state = !empty($ib['state']) ? ' — ' . $ib['state'] : '';
                                $displayName = $ib['name'] . $code . $state;
                            ?>
                            <div class="finzo-dropdown-option" data-value="<?= htmlspecialchars((string)$ib['id']) ?>"><?= htmlspecialchars($displayName) ?></div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="finzo-dropdown-option" data-value="">Loading branches...</div>
                    <?php endif; ?>
                </div>
                <input type="hidden" id="branch" name="branch" />
            </div>
        </div>

        <!-- Terms Agreement -->
        <div class="mb-6 p-4 bg-surface-elevated rounded-xl border border-border-subtle">
            <label class="flex items-start gap-3 cursor-pointer">
                <input type="checkbox" id="terms" class="fin-checkbox-input mt-0.5" required />
                <span class="text-xs font-medium text-text-secondary leading-relaxed">
                    I confirm that the personal, address, and document information provided is accurate and agree to Finzo's <a href="/Finzo/public/views/components/terms-of-service.html" target="_blank" class="font-semibold text-primary hover:underline">Terms of Service</a> and <a href="/Finzo/public/views/components/privacy-policy.html" target="_blank" class="font-semibold text-primary hover:underline">Privacy Policy</a>.
                </span>
            </label>
        </div>

        <div class="flex justify-between mt-8 pt-5 border-t border-border-subtle">
            <button type="button" class="btn btn-secondary" onclick="prevStep(4)">
                <span class="material-symbols-outlined text-lg">arrow_back</span>
                <span>Back</span>
            </button>
            <button type="button" class="btn btn-primary" id="submit-btn" onclick="submitProfile()">
                <span class="material-symbols-outlined text-lg">done_all</span>
                <span>Submit Application</span>
            </button>
        </div>
    </div>
</div>

<script defer src="/Finzo/public/assets/js/user/complete-profile.js"></script>

<?php
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>