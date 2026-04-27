/**
 * Finzo Component Loader v2.0
 * Unified systemic injection for Navbars, Sidebars, and Footers.
 */

async function loadComponent(elementId, componentPath) {
    const placeholder = document.getElementById(elementId);
    if (!placeholder) return;

    try {
        const response = await fetch(componentPath);
        if (response.ok) {
            const html = await response.text();
            placeholder.innerHTML = html;
            
            // Fix absolute paths (starting with /) to include the project base path
            const basePath = window.FinzoBasePath || '/Finzo/';
            placeholder.querySelectorAll('[href^="/"], [src^="/"]').forEach(el => {
                const attr = el.hasAttribute('href') ? 'href' : 'src';
                const originalVal = el.getAttribute(attr);
                // Only prefix if it's a project path (views, assets, components)
                if (originalVal.startsWith('/views/') || originalVal.startsWith('/assets/') || originalVal.startsWith('/components/')) {
                    el.setAttribute(attr, basePath + originalVal.substring(1));
                }
            });

            // Post-load logic: Active Link Highlighting
            highlightActiveLinks(placeholder);

            // Sync theme icons/labels if theme engine is present
            if (window.FinzoTheme) {
                window.FinzoTheme.apply();
            }
        } else {
            console.error(`Status ${response.status}: Failed to load ${componentPath}`);
        }
    } catch (error) {
        console.error(`Network Error loading ${componentPath}:`, error);
    }
}

function highlightActiveLinks(container) {
    const fullPath = window.location.pathname.replace('.html', '').replace(/\/$/, "");
    const links = container.querySelectorAll('a');
    
    links.forEach(link => {
        const linkHref = link.getAttribute('href');
        if (!linkHref) return;
        
        const cleanHref = linkHref.replace('.html', '').replace(/\/$/, "");
        
        if (fullPath === cleanHref || fullPath.endsWith(cleanHref)) {
            // Apply active styles for Nav
            if (link.classList.contains('nav-link')) {
                link.classList.add('nav-link-active');
            }
            // Apply active styles for admin-style Sidebar (user panel navy theme)
            if (link.classList.contains('admin-nav-item')) {
                link.classList.add('user-sidebar-active');
            }
            // Legacy sidebar support
            else if (link.closest('aside') || container.id === 'sidebar-placeholder') {
                link.classList.add('sidebar-link-active');
            }
        }
    });
}

// Global initialization
document.addEventListener('DOMContentLoaded', () => {
    // Detect base path dynamically (works in any subdirectory)
    const basePath = document.querySelector('script[src*="component-loader"]')?.src
        ? new URL(document.querySelector('script[src*="component-loader"]').src).pathname.replace(/assets\/js\/component-loader\.js$/, '')
        : '/Finzo/';

    window.FinzoBasePath = basePath;

    // Top Navbar (Landing/Auth)
    loadComponent('navbar-placeholder', basePath + 'components/navbar.html');
    
    // Side Sidebar (Dashboard/Admin)
    loadComponent('sidebar-placeholder', basePath + 'components/sidebar.html');
    
    // Universal Footer
    loadComponent('footer-placeholder', basePath + 'components/footer.html');
});
