/**
 * Finzo Main Interactions (Tailwind)
 * Integrates institutional logic with the motion engine.
 */

$(document).ready(function() {

    // ============================================================
    // FINZO CUSTOM DROPDOWN CONTROLLER
    // Handles all [data-finzo-dropdown] elements on any page.
    // ============================================================
    // Dropdowns are now handled by assets/js/admin-dropdowns.js for consistent premium behavior.



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
        
        // Safety check: Avoid toggling if not activated
        const realValue = balanceEl.attr('data-real-value');
        if (realValue === 'Pending Activation' || realValue === 'Unavailable') {
            window.showToast("Please complete your profile to activate your account.", "warning");
            return;
        }

        const isHidden = icon.text().trim() === 'visibility_off' || icon.text().trim() === 'lock';
        
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
            accountEl.text('•••• •••• •••• ' + (accountEl.attr('data-real-value').slice(-4) || 'XXXX'));
            btn.addClass('text-slate-400').removeClass('text-primary');
        }
    });

    // Re-highlight sidebar if dynamic navigation occurs (handled by highlightActiveSidebar)

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

        // Optionally create a visible toast element here
    }

    // --- NEW: Panel Management (Global) ---
    window.togglePanel = function(openId, closeId) {
        const panel = $('#' + openId);
        const other = $('#' + closeId);
        if (other.length) other.addClass('hidden');
        panel.toggleClass('hidden');
    }

    // Individual notification clicks
    $(document).on('click', '.notif-item', function() {
        const item = $(this);
        const title = item.find('p.font-semibold').text();
        
        item.removeClass('is-unread').addClass('is-read');
        $('#notif-panel').addClass('hidden');
        
        window.showToast(`Viewing: ${title}`, 'info');
        
        // Hide badge if no unreads left
        if ($('.notif-item.is-unread').length === 0) {
            $('#notif-badge').addClass('scale-0');
        }
    });

    // Mark all read
    $(document).on('click', '#mark-all-read', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        $('.notif-item').removeClass('is-unread').addClass('is-read');
        $('#notif-badge').addClass('scale-0');
        
        window.showToast('All notifications marked as read', 'success');
    });

    // Global click-outside to close panels
    $(document).on('click', function(e) {
        const target = $(e.target);
        
        // Notification Panel
        if (!target.closest('#notif-wrapper').length && !target.closest('#notif-panel').length) {
            $('#notif-panel').addClass('hidden');
        }
        
        // Settings Panel
        if (!target.closest('#settings-wrapper').length && !target.closest('#settings-panel').length) {
            $('#settings-panel').addClass('hidden');
        }
    });

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

    // --- NEW: Sidebar Auto-Highlighting (Extension-Aware) ---
    function highlightActiveSidebar() {
        const fullPath = window.location.pathname.replace('.html', '').replace(/\/$/, "");
        
        $('aside nav a').each(function() {
            const link = $(this);
            let href = link.attr('href');
            if (!href) return;
            
            // Normalize href for comparison
            const cleanHref = href.replace('.html', '').replace(/\/$/, "");
            
            if (fullPath === cleanHref || fullPath.endsWith(cleanHref)) {
                link.addClass('sidebar-link-active').removeClass('text-slate-400 opacity-60');
                link.find('.material-symbols-outlined').css('color', '#818cf8');
            } else {
                link.removeClass('sidebar-link-active').addClass('text-slate-400');
                link.find('.material-symbols-outlined').css('color', '');
            }
        });
    }
    highlightActiveSidebar();

    // Re-initialize motion for dynamically loaded content
    window.refreshMotion = initMotion;
});
