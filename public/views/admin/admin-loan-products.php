<?php
$portal = 'admin';
$pageTitle = 'Finzo Admin — Loan Products';
$pageHeader = '
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-xl font-headline font-black text-text-primary tracking-tight">Loan Products</h1>
        <p class="text-xs font-bold text-text-secondary uppercase tracking-widest mt-0.5">Configure lending products and interest rates</p>
    </div>
    <div class="flex items-center gap-3">
        <button id="btn-create-product" class="btn btn-primary !px-5 flex items-center gap-2">
            <span class="material-symbols-outlined text-[18px]" aria-hidden="true">add</span>
            <span class="font-bold">Create Product</span>
        </button>
    </div>
</div>
';
$extraScripts = '<script defer src="/Finzo/public/assets/js/admin/admin-loan-products.js"></script>';
ob_start();
?>

<!-- TABLE WRAPPER -->
<div class="ds-card ds-card-dropdown-fix relative z-20 overflow-hidden">
    <!-- TABLE HEADER -->
    <div class="p-4 sm:p-6 flex flex-col sm:flex-row items-center justify-between gap-4 border-b border-border-subtle bg-background">
        <div class="text-sm font-bold text-text-primary uppercase tracking-widest flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-[20px]">real_estate_agent</span> Active Products
        </div>
        <div class="relative w-full sm:w-64">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <span class="material-symbols-outlined text-text-muted text-[18px]">search</span>
            </div>
            <input type="text" id="product-search" class="fin-input !pl-9 !py-2 !h-9 !text-sm w-full bg-white dark:bg-surface" placeholder="Search products..." />
        </div>
    </div>

    <!-- TABLE AREA -->
    <div class="ds-table-wrapper bg-white dark:bg-surface min-h-[300px]">
        <table class="ds-table">
            <thead>
                <tr>
                    <th class="w-16">ID</th>
                    <th>Product Name</th>
                    <th>Interest Rate</th>
                    <th>Amount Range (₹)</th>
                    <th>Tenure Range (Months)</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody id="products-tbody">
                <tr><td colspan="6" class="py-12 text-center"><span class="loader"></span></td></tr>
            </tbody>
        </table>
    </div>
</div>

<!-- CREATE PRODUCT MODAL -->
<div id="product-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 hidden">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm modal-backdrop" onclick="document.getElementById('product-modal').classList.add('hidden')"></div>
    <div class="bg-surface border border-border-subtle rounded-2xl shadow-2xl w-full max-w-2xl relative z-10 max-h-[90vh] flex flex-col">
        <div class="p-6 border-b border-border-subtle flex justify-between items-center bg-background rounded-t-2xl">
            <h3 class="font-headline font-black tracking-tight text-lg" id="product-modal-title">Create Loan Product</h3>
            <button class="w-8 h-8 flex items-center justify-center rounded-lg bg-surface-subtle text-text-muted hover:text-text-primary hover:bg-border-subtle transition-colors" onclick="document.getElementById('product-modal').classList.add('hidden')">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
        </div>
        <div class="p-6 overflow-y-auto custom-scrollbar flex-1">
            <form id="product-form" class="space-y-6">
                
                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <label class="fin-label">Product Name</label>
                        <input type="text" id="product-name" name="name" class="fin-input" placeholder="e.g. Home Loan Plus" required />
                    </div>
                    <div>
                        <label class="fin-label">Description</label>
                        <textarea id="product-description" name="description" class="fin-input" placeholder="Details about this loan product..." rows="2" required></textarea>
                    </div>
                    
                    <div>
                        <label class="fin-label">Interest Rate (% p.a.)</label>
                        <input type="number" id="product-rate" name="interest_rate" class="fin-input" placeholder="e.g. 10.5" step="0.01" required />
                    </div>

                    <div class="grid grid-cols-2 gap-4 border-t border-border-subtle pt-6 mt-2">
                        <div>
                            <label class="fin-label">Min Amount (₹)</label>
                            <input type="number" id="product-min-amt" name="min_amount" class="fin-input" placeholder="10000" required />
                        </div>
                        <div>
                            <label class="fin-label">Max Amount (₹)</label>
                            <input type="number" id="product-max-amt" name="max_amount" class="fin-input" placeholder="5000000" required />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="fin-label">Min Tenure (Months)</label>
                            <input type="number" id="product-min-tenure" name="min_tenure_months" class="fin-input" placeholder="6" required />
                        </div>
                        <div>
                            <label class="fin-label">Max Tenure (Months)</label>
                            <input type="number" id="product-max-tenure" name="max_tenure_months" class="fin-input" placeholder="360" required />
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="p-6 border-t border-border-subtle bg-background rounded-b-2xl flex justify-end gap-3">
            <button type="button" class="btn btn-secondary !px-5" onclick="document.getElementById('product-modal').classList.add('hidden')">Cancel</button>
            <button type="button" class="btn btn-primary !px-5" id="btn-save-product">Save Product</button>
        </div>
    </div>
</div>

<?php
$slot = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
