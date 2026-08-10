<?php
$portal = 'admin';
$pageTitle = 'Finzo Admin — Card Products';
$pageHeader = '
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-xl font-headline font-black text-text-primary tracking-tight">Card Products</h1>
        <p class="text-xs font-bold text-text-secondary uppercase tracking-widest mt-0.5">Manage credit and debit card tiers</p>
    </div>
    <div class="flex items-center gap-3">
        <button id="btn-create-product" class="btn btn-primary !px-5 flex items-center gap-2">
            <span class="material-symbols-outlined text-[18px]" aria-hidden="true">add</span>
            <span class="font-bold">Create Card Product</span>
        </button>
    </div>
</div>
';
$extraScripts = '<script defer src="/Finzo/public/assets/js/admin/admin-card-products.js"></script>';
ob_start();
?>

<!-- TABLE WRAPPER -->
<div class="ds-card ds-card-dropdown-fix relative z-20 overflow-hidden">
    <div class="p-4 sm:p-6 flex flex-col sm:flex-row items-center justify-between gap-4 border-b border-border-subtle bg-background">
        <div class="text-sm font-bold text-text-primary uppercase tracking-widest flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-[20px]">featured_play_list</span> Active Card Products
        </div>
        <div class="relative w-full sm:w-64">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <span class="material-symbols-outlined text-text-muted text-[18px]">search</span>
            </div>
            <input type="text" id="product-search" class="fin-input !pl-9 !py-2 !h-9 !text-sm w-full bg-white dark:bg-surface" placeholder="Search cards..." />
        </div>
    </div>

    <!-- TABLE AREA -->
    <div class="ds-table-wrapper bg-white dark:bg-surface min-h-[300px]">
        <table class="ds-table">
            <thead>
                <tr>
                    <th class="w-16">ID</th>
                    <th>Product Name</th>
                    <th>Type</th>
                    <th>Network</th>
                    <th>Daily limits (Withdraw / POS)</th>
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
            <h3 class="font-headline font-black tracking-tight text-lg">Create Card Product</h3>
            <button class="w-8 h-8 flex items-center justify-center rounded-lg bg-surface-subtle text-text-muted hover:text-text-primary hover:bg-border-subtle transition-colors" onclick="document.getElementById('product-modal').classList.add('hidden')">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
        </div>
        <div class="p-6 overflow-y-auto custom-scrollbar flex-1">
            <form id="product-form" class="space-y-6">
                
                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <label class="fin-label">Card Name</label>
                        <input type="text" name="name" class="fin-input" placeholder="e.g. Platinum Visa" required />
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="fin-label">Card Type</label>
                            <select name="type" class="fin-input cursor-pointer" required>
                                <option value="debit">Debit Card</option>
                                <option value="credit">Credit Card</option>
                            </select>
                        </div>
                        <div>
                            <label class="fin-label">Card Network</label>
                            <select name="network" class="fin-input cursor-pointer" required>
                                <option value="visa">Visa</option>
                                <option value="mastercard">Mastercard</option>
                                <option value="rupay">RuPay</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 border-t border-border-subtle pt-6 mt-2">
                        <div>
                            <label class="fin-label">Daily ATM Withdrawal Limit (₹)</label>
                            <input type="number" name="daily_withdrawal_limit" class="fin-input" placeholder="e.g. 50000" required />
                        </div>
                        <div>
                            <label class="fin-label">Daily POS/Online Limit (₹)</label>
                            <input type="number" name="daily_purchase_limit" class="fin-input" placeholder="e.g. 200000" required />
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
