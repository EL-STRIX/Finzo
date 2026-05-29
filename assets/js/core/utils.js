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
    }
};

// Aliases for shorter usage if window is already populated
window.formatINR = window.FinzoUtils.formatINR;
window.formatDate = window.FinzoUtils.formatDate;

// ==========================================
// Dropdowns Engine
// ==========================================
(function() {
    function initDropdowns() {
        document.addEventListener('click', function(e) {
            const trigger = e.target.closest('.finzo-dropdown-trigger');
            if (!trigger) return;
            
            e.preventDefault();
            e.stopPropagation();
            
            const dropdown = trigger.closest('.finzo-dropdown');
            const menu = dropdown.querySelector('.finzo-dropdown-menu');
            
            const isOpen = trigger.classList.contains('is-open');
            closeAllDropdowns();
            
            if (!isOpen) {
                trigger.classList.add('is-open');
                menu.classList.add('is-open');
            }
        });

        document.addEventListener('click', function(e) {
            const option = e.target.closest('.finzo-dropdown-option');
            if (!option) return;
            
            if (option.tagName !== 'A' && option.tagName !== 'BUTTON') {
                e.preventDefault();
            } else if (option.tagName === 'BUTTON' && !option.hasAttribute('onclick')) {
                e.preventDefault();
            }
            e.stopPropagation();
            
            const dropdown = option.closest('.finzo-dropdown');
            const trigger = dropdown.querySelector('.finzo-dropdown-trigger');
            const menu = dropdown.querySelector('.finzo-dropdown-menu');
            const input = dropdown.querySelector('input[type="hidden"]');
            const label = trigger.querySelector('.dropdown-label');
            
            const value = option.getAttribute('data-value');
            const text = option.textContent.trim();
            
            dropdown.querySelectorAll('.finzo-dropdown-option').forEach(opt => opt.classList.remove('is-selected'));
            option.classList.add('is-selected');
            
            if (input) {
                input.value = value;
                input.dispatchEvent(new Event('change', { bubbles: true }));
            }
            if (label) label.textContent = text;
            
            trigger.classList.remove('is-open');
            menu.classList.remove('is-open');
        });

        document.addEventListener('click', function(e) {
            if (!e.target.closest('.finzo-dropdown')) {
                closeAllDropdowns();
            }
        });
    }

    function closeAllDropdowns() {
        document.querySelectorAll('.finzo-dropdown-trigger.is-open').forEach(el => el.classList.remove('is-open'));
        document.querySelectorAll('.finzo-dropdown-menu.is-open').forEach(el => el.classList.remove('is-open'));
    }

    window.closeAllDropdowns = closeAllDropdowns;

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDropdowns);
    } else {
        initDropdowns();
    }
})();
