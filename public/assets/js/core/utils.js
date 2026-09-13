/**
 * Finzo Core Utilities
 * Centralized formatting and UI helpers used across all portals.
 */

window.FinzoUtils = {
    /**
     * Format number as Indian Rupee (INR)
     * @param {number|string} amount 
     * @returns {string}
     */
    formatINR: function(amount) {
        const num = parseFloat(amount) || 0;
        return new Intl.NumberFormat('en-IN', {
            style: 'currency',
            currency: 'INR',
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(num);
    },

    /**
     * Format date to a readable string (e.g. 28 Apr 2026)
     * @param {string|Date} dateStr 
     * @returns {string}
     */
    formatDate: function(dateStr) {
        if (!dateStr) return 'N/A';
        const date = new Date(dateStr);
        if (isNaN(date.getTime())) return 'N/A';
        return date.toLocaleDateString('en-IN', {
            day: '2-digit',
            month: 'short',
            year: 'numeric'
        });
    },

    /**
     * Format date and time to a readable string (e.g. 28 Apr 2026, 14:30)
     * @param {string|Date} dateStr 
     * @returns {string}
     */
    formatDateTime: function(dateStr) {
        if (!dateStr) return 'N/A';
        const date = new Date(dateStr);
        if (isNaN(date.getTime())) return 'N/A';
        return date.toLocaleDateString('en-IN', {
            day: '2-digit',
            month: 'short',
            year: 'numeric'
        }) + ', ' + date.toLocaleTimeString('en-US', {
            hour: '2-digit',
            minute: '2-digit',
            hour12: true
        });
    },

    /**
     * Generate an institutional-grade gradient based on initials
     * @param {string} initials 
     * @returns {string} CSS gradient string
     */
    getAvatarGradient: function(initials) {
        if (!initials) return 'linear-gradient(135deg, #cbd5e1 0%, #94a3b8 100%)';
        const charCode = initials.charCodeAt(0) || 65;
        const colorPairs = [
            ['#3b82f6', '#2563eb'], // Blue
            ['#10b981', '#059669'], // Emerald
            ['#f59e0b', '#d97706'], // Amber
            ['#ef4444', '#dc2626'], // Red
            ['#8b5cf6', '#7c3aed'], // Violet
            ['#ec4899', '#db2777'], // Pink
            ['#14b8a6', '#0d9488'], // Teal
        ];
        const index = charCode % colorPairs.length;
        const [start, end] = colorPairs[index];
        return `linear-gradient(135deg, ${start} 0%, ${end} 100%)`;
    },

    /**
     * Show a toast notification (forwards to portal-specific showToast if available)
     * @param {string} message 
     * @param {string} type 
     * @param {number} duration 
     */
    showToast: function(message, type = 'success', duration = 4000) {
        if (typeof window.showToast === 'function') {
            window.showToast(message, type, duration);
        } else if (typeof window.portal !== 'undefined' && typeof window.portal.showToast === 'function') {
            window.portal.showToast(message, type, duration);
        } else {
            alert(message);
        }
    },

    /**
     * Convert numeric amount to words (Rupees Only)
     * @param {number|string} amount 
     * @returns {string}
     */
    amountToWords: function(amount) {
        const ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
        const tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];
        
        function numToWords(n) {
            if (n === 0) return '';
            if (n < 20) return ones[n];
            if (n < 100) return tens[Math.floor(n / 10)] + (n % 10 ? ' ' + ones[n % 10] : '');
            if (n < 1000) return ones[Math.floor(n / 100)] + ' Hundred' + (n % 100 ? ' ' + numToWords(n % 100) : '');
            if (n < 100000) return numToWords(Math.floor(n / 1000)) + ' Thousand' + (n % 1000 ? ' ' + numToWords(n % 1000) : '');
            if (n < 10000000) return numToWords(Math.floor(n / 100000)) + ' Lakh' + (n % 100000 ? ' ' + numToWords(n % 100000) : '');
            return numToWords(Math.floor(n / 10000000)) + ' Crore' + (n % 10000000 ? ' ' + numToWords(n % 10000000) : '');
        }

        const num = Math.floor(parseFloat(amount));
        if (isNaN(num) || num <= 0) return '';
        return (numToWords(num) + ' Rupees Only').trim();
    },

    /**
     * Return a "time ago" string (e.g. 2 hours ago)
     * @param {string|Date} dateStr 
     * @returns {string}
     */
    timeAgo: function(dateStr) {
        const seconds = Math.floor((new Date() - new Date(dateStr)) / 1000);
        if (seconds < 60) return 'Just now';
        const intervals = { year: 31536000, month: 2592000, week: 604800, day: 86400, hour: 3600, minute: 60 };
        for (let i in intervals) {
            const count = Math.floor(seconds / intervals[i]);
            if (count > 0) return count + ' ' + i + (count > 1 ? 's' : '') + ' ago';
        }
        return 'Long ago';
    },

    /**
     * Debounce a function call
     * @param {Function} func 
     * @param {number} wait milliseconds
     * @returns {Function}
     */
    debounce: function(func, wait) {
        let timeout;
        return function(...args) {
            const context = this;
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(context, args), wait);
        };
    /**
     * Convert number to words in Indian numbering system (Rupees)
     * @param {number|string} number 
     * @returns {string}
     */
    numberToWords: function(number) {
        let num = Math.floor(Math.abs(Number(number) || 0));
        if (num === 0) return 'Zero';
        const ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
        const tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];

        function convertLessThanOneThousand(n) {
            if (n === 0) return '';
            if (n < 20) return ones[n];
            if (n < 100) return tens[Math.floor(n / 10)] + (n % 10 !== 0 ? ' ' + ones[n % 10] : '');
            return ones[Math.floor(n / 100)] + ' Hundred' + (n % 100 !== 0 ? ' ' + convertLessThanOneThousand(n % 100) : '');
        }

        let parts = [];
        const crore = Math.floor(num / 10000000);
        num %= 10000000;
        const lakh = Math.floor(num / 100000);
        num %= 100000;
        const thousand = Math.floor(num / 1000);
        num %= 1000;
        const remainder = num;

        if (crore > 0) parts.push(convertLessThanOneThousand(crore) + ' Crore');
        if (lakh > 0) parts.push(convertLessThanOneThousand(lakh) + ' Lakh');
        if (thousand > 0) parts.push(convertLessThanOneThousand(thousand) + ' Thousand');
        if (remainder > 0) parts.push(convertLessThanOneThousand(remainder));

        return parts.join(' ').trim();
    }
};

// Aliases for shorter usage if window is already populated
window.formatINR = window.FinzoUtils.formatINR;
window.formatDate = window.FinzoUtils.formatDate;
window.numberToWords = window.FinzoUtils.numberToWords;

// ==========================================
// Dropdowns Utilities (Delegates to FinzoUI)
// ==========================================
window.closeAllDropdowns = function() {
    if (window.FinzoUI && typeof window.FinzoUI.closeAllDropdowns === 'function') {
        window.FinzoUI.closeAllDropdowns();
    } else {
        document.querySelectorAll('.finzo-dropdown-trigger.is-open').forEach(el => el.classList.remove('is-open'));
        document.querySelectorAll('.finzo-dropdown-menu.is-open').forEach(el => el.classList.remove('is-open'));
    }
};

// ==========================================
// Global Event Delegates
// ==========================================
document.addEventListener('click', function(e) {
    // Handle data-href navigation
    const navBtn = e.target.closest('[data-href]');
    if (navBtn) {
        e.preventDefault();
        window.location.href = navBtn.getAttribute('data-href');
        return;
    }

    // Handle data-action execution
    const actionBtn = e.target.closest('[data-action]');
    if (actionBtn) {
        // Prevent default only if it's an anchor or button that would normally submit/navigate
        if (actionBtn.tagName === 'A' || actionBtn.tagName === 'BUTTON') {
            e.preventDefault();
        }
        const action = actionBtn.getAttribute('data-action');
        try {
            const fn = new Function('event', action);
            fn.call(actionBtn, e);
        } catch (err) {
            console.error('Error executing data-action:', action, err);
        }
    }
});
