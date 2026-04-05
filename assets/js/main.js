/**
 * Finzo Main Interactions (Tailwind)
 */

$(document).ready(function() {
    // Top Navigation Profile Toggle
    $(document).on('click', 'header button:has(img)', function() {
        const dropdown = $(this).next();
        dropdown.toggleClass('hidden');
    });

    // Mobile Sidebar Toggle
    $(document).on('click', '.material-symbols-outlined:contains("menu")', function() {
        $('aside.fixed').toggleClass('-translate-x-full');
    });

    // Dark Mode Toggle Logic
    const darkModeBtn = $('.material-symbols-outlined:contains("dark_mode")').parent();
    darkModeBtn.on('click', function(e) {
        e.preventDefault();
        $('html').toggleClass('dark');
        // Icon swap
        if ($('html').hasClass('dark')) {
            $(this).find('.material-symbols-outlined').text('light_mode');
        } else {
            $(this).find('.material-symbols-outlined').text('dark_mode');
        }
    });

    // Authentication Guard Logic
    window.isAuthenticated = function() {
        return localStorage.getItem('finzo_logged_in') === 'true';
    };

    // Terminal Link Dynamic Routing
    $(document).on('click', '#terminal-link-btn', function(e) {
        e.preventDefault();
        if (window.isAuthenticated()) {
            window.location.href = 'views/user/dashboard.html';
        } else {
            window.location.href = 'views/auth/login.html';
        }
    });

    // Toasts placeholder
    window.showToast = function(message, type = 'success') {
        alert(message);
    }
});
