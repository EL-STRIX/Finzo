/**
 * Finzo Main Interactions (Tailwind)
 * Integrates institutional logic with the motion engine.
 */

$(document).ready(function() {
    
    // --- ORIGINAL CORE LOGIC ---

    // Top Navigation Profile Toggle
    $(document).on('click', 'header button:has(img)', function() {
        const dropdown = $(this).next();
        dropdown.toggleClass('hidden');
    });

    // --- NEW: Password Visibility Toggle ---
    $(document).on('click', '.material-symbols-outlined', function() {
        const icon = $(this);
        const input = icon.siblings('input[type="password"], input[type="text"]');
        
        if (input.length && (icon.text().trim() === 'visibility' || icon.text().trim() === 'visibility_off')) {
            const isPassword = input.attr('type') === 'password';
            input.attr('type', isPassword ? 'text' : 'password');
            icon.text(isPassword ? 'visibility_off' : 'visibility');
        }
    });

    // Mobile Sidebar Toggle
    $(document).on('click', '.material-symbols-outlined:contains("menu")', function() {
        $('aside.fixed').toggleClass('-translate-x-full');
    });

    // --- NEW: Dashboard Balance Visibility Toggle ---
    $(document).on('click', '#toggle-balance', function() {
        const btn = $(this);
        const icon = btn.find('.material-symbols-outlined');
        const balanceEl = $('#dash-balance');
        const accountEl = $('#dash-account');
        
        const isHidden = icon.text().trim() === 'visibility_off';
        
        if (isHidden) {
            // Show real values
            icon.text('visibility');
            balanceEl.text(balanceEl.attr('data-real-value'));
            accountEl.text(accountEl.attr('data-real-value'));
            btn.addClass('text-primary').removeClass('text-slate-400');
        } else {
            // Hide values
            icon.text('visibility_off');
            balanceEl.text('••••••••');
            accountEl.text('•••• •••• •••• ' + accountEl.attr('data-real-value').slice(-4));
            btn.addClass('text-slate-400').removeClass('text-primary');
        }
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
        console.log(`[Toast] ${type.toUpperCase()}: ${message}`);
    }

    // --- INSTITUTIONAL MOTION ENGINE ---

    // Intersection Observer for Reveal Animations
    const motionObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('revealed');
                
                // Trigger Count-Up if it's a stat
                if (entry.target.hasAttribute('data-count-to')) {
                    animateValue(entry.target);
                }
                
                // Unobserve after reveal to save cycles
                motionObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });

    // Initialize observers for everything with reveal-up
    function initMotion() {
        $('.reveal-up').each(function() {
            motionObserver.observe(this);
        });
    }
    initMotion();

    // Stat Counter Utility
    function animateValue(obj) {
        const start = 0;
        const endEndAttr = obj.getAttribute('data-count-to');
        const end = parseFloat(endEndAttr.replace(/[^\d.]/g, ''));
        const duration = 1500;
        const prefix = obj.getAttribute('data-prefix') || '';
        const suffix = obj.getAttribute('data-suffix') || '';
        let startTimestamp = null;

        const step = (timestamp) => {
            if (!startTimestamp) startTimestamp = timestamp;
            const progress = Math.min((timestamp - startTimestamp) / duration, 1);
            const value = progress * (end - start) + start;
            
            // Format number with commas
            const formatted = Math.floor(value).toLocaleString();
            obj.innerHTML = prefix + formatted + suffix;
            
            if (progress < 1) {
                window.requestAnimationFrame(step);
            } else {
                // Ensure final value is exact
                obj.innerHTML = prefix + end.toLocaleString() + suffix;
            }
        };
        window.requestAnimationFrame(step);
    }

    // Smooth Scroll for anchor links
    $(document).on('click', 'a[href^="#"]', function(e) {
        const targetId = $(this).attr('href');
        if (targetId === '#' || !targetId.startsWith('#')) return;
        
        const target = $(targetId);
        if (target.length) {
            e.preventDefault();
            $('html, body').animate({
                scrollTop: target.offset().top - 100
            }, 800);
        }
    });

    // Re-initialize motion for dynamically loaded content
    window.refreshMotion = initMotion;
});
