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
        const gradients = [
            'linear-gradient(135deg, #002645 0%, #001d36 100%)', // Finzo Navy
            'linear-gradient(135deg, #1e293b 0%, #0f172a 100%)', // Slate
            'linear-gradient(135deg, #0ea5e9 0%, #0369a1 100%)', // Sky
            'linear-gradient(135deg, #6366f1 0%, #4338ca 100%)', // Indigo
            'linear-gradient(135deg, #0abc64 0%, #005228 100%)'  // Emerald
        ];
        let hash = 0;
        for (let i = 0; i < initials.length; i++) {
            hash = initials.charCodeAt(i) + ((hash << 5) - hash);
        }
        return gradients[Math.abs(hash) % gradients.length];
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
    }
};

// Aliases for shorter usage if window is already populated
window.formatINR = window.FinzoUtils.formatINR;
window.formatDate = window.FinzoUtils.formatDate;
