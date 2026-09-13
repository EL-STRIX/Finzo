/**
 * Finzo Staff — Withdrawal Handler Alias
 * Unified into staff-deposit.js
 */
if (!window.FinzoStaffTransaction) {
    const s = document.createElement('script');
    s.src = '/Finzo/public/assets/js/staff/staff-deposit.js';
    s.defer = true;
    document.head.appendChild(s);
}
