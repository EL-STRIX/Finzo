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
            window.location.href = '/Finzo/public/views/user/dashboard.html';
        } else {
            window.location.href = '/Finzo/public/views/auth/login.html';
        }
    });

    // Toast Notification System
    window.showToast = function(message, type = 'success', duration = 4000) {
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.style.cssText = 'position:fixed;top:20px;right:20px;z-index:99999;display:flex;flex-direction:column;gap:10px;';
            document.body.appendChild(container);
        }

        const icons = { success: 'check_circle', error: 'error', warning: 'warning', info: 'info' };
        const colors = { success: '#10b981', error: '#ef4444', warning: '#f59e0b', info: '#3b82f6' };

        const toast = document.createElement('div');
        toast.style.cssText = `display:flex;align-items:center;gap:10px;padding:14px 20px;background:#fff;border-left:4px solid ${colors[type] || colors.info};border-radius:10px;box-shadow:0 8px 30px rgba(0,0,0,0.12);font-family:'Inter',sans-serif;font-size:13px;font-weight:600;color:#1e293b;transform:translateX(120%);transition:transform 0.4s cubic-bezier(0.4,0,0.2,1);max-width:380px;`;
        toast.innerHTML = `
            <span class="material-symbols-outlined" style="color:${colors[type] || colors.info};font-size:20px;">${icons[type] || 'info'}</span>
            <div style="flex:1">${message}</div>
            <button onclick="this.parentElement.style.transform='translateX(120%)';setTimeout(()=>this.parentElement.remove(),400)" style="background:none;border:none;cursor:pointer;opacity:0.4;padding:0;">
                <span class="material-symbols-outlined" style="font-size:16px;color:#64748b;">close</span>
            </button>
        `;

        container.appendChild(toast);
        setTimeout(() => toast.style.transform = 'translateX(0)', 10);

        setTimeout(() => {
            toast.style.transform = 'translateX(120%)';
            setTimeout(() => toast.remove(), 400);
        }, duration);
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
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const targetId = this.getAttribute('href');
            if (targetId === '#' || targetId === '') return;
            
            const target = document.querySelector(targetId);
            if (target) {
                e.preventDefault();
                isScrollingProgrammatically = true;

                // Remove active class from all links and add to clicked link
                document.querySelectorAll('.nav-link').forEach(link => link.classList.remove('nav-link-active'));
                this.classList.add('nav-link-active');

                const nav = document.querySelector('nav');
                const navbarHeight = nav ? nav.offsetHeight : 80;
                
                const targetPosition = target.getBoundingClientRect().top + window.pageYOffset - navbarHeight + 10;
                
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });

                // Reset flag after expected scroll time
                setTimeout(() => {
                    isScrollingProgrammatically = false;
                }, 800);
            }
        });
    });

    // --- NEW: Scrollspy (Underline follows page view) ---
    let isScrollingProgrammatically = false;

    function initScrollspy() {
        const navLinks = document.querySelectorAll('.nav-link');
        const nav = document.querySelector('nav');
        const navbarHeight = nav ? nav.offsetHeight : 80;

        let scrollTimeout;
        window.addEventListener('scroll', function() {
            // Do not update while we are animating via JS to prevent flickering/stuck feeling
            if (isScrollingProgrammatically) return;

            // Throttle to improve performance
            if (scrollTimeout) return;
            scrollTimeout = setTimeout(() => {
                const scrollPos = window.pageYOffset + navbarHeight + 150;

                navLinks.forEach(link => {
                    const href = link.getAttribute('href');
                    if (!href || !href.startsWith('#') || href.length <= 1) return;
                    
                    const section = document.querySelector(href);
                    if (section) {
                        const top = section.getBoundingClientRect().top + window.pageYOffset;
                        const bottom = top + section.offsetHeight;

                        if (scrollPos >= top && scrollPos <= bottom) {
                            navLinks.forEach(l => l.classList.remove('nav-link-active'));
                            link.classList.add('nav-link-active');
                        }
                    }
                });
                
                // Special case: Top of page
                if (window.pageYOffset < 100) {
                    navLinks.forEach(l => l.classList.remove('nav-link-active'));
                    const homeLink = document.querySelector('.nav-link[href="#home"]');
                    if (homeLink) homeLink.classList.add('nav-link-active');
                }
                scrollTimeout = null;
            }, 100);
        });
    }

    // Initialize Scrollspy after a small delay to ensure elements are ready
    setTimeout(initScrollspy, 500);

    // --- NEW: Sidebar Auto-Highlighting (Extension-Aware) ---
    function highlightActiveSidebar() {
        const fullPath = window.location.pathname.replace('.html', '').replace(/\/$/, "");
        
        document.querySelectorAll('aside nav a').forEach(link => {
            const href = link.getAttribute('href');
            if (!href || href === '#' || href.startsWith('javascript')) return;
            
            // Clean up the href for comparison (remove .html and trailing slashes)
            const cleanHref = href.replace('.html', '').replace(/\/$/, "");
            
            if (fullPath.endsWith(cleanHref)) {
                link.classList.add('active', 'bg-blue-50', 'dark:bg-blue-900/20', 'text-blue-600', 'dark:text-blue-400');
            }
        });
    }
    highlightActiveSidebar();

    // Re-initialize motion for dynamically loaded content
    window.refreshMotion = initMotion;
});
