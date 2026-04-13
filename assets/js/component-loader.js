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
    // Top Navbar (Landing/Auth)
    loadComponent('navbar-placeholder', '/components/navbar.html');
    
    // Side Sidebar (Dashboard/Admin)
    loadComponent('sidebar-placeholder', '/components/sidebar.html');
    
    // Universal Footer
    loadComponent('footer-placeholder', '/components/footer.html');
});
