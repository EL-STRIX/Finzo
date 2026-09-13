/**
 * Finzo Staff — Dashboard Handler
 * Alias / Forwarder for staff-dashboard.js
 */

// If loaded directly, ensure staff-dashboard.js functionality is available
if (!window.FinzoStaffDashboard) {
    const s = document.createElement('script');
    s.src = '/Finzo/public/assets/js/staff/staff-dashboard.js';
    s.defer = true;
    document.head.appendChild(s);
}
