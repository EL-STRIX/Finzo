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

    // Toasts placeholder
    window.showToast = function(message, type = 'success') {
        alert(message);
    }
});
