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
        } else {
            console.error(`Status ${response.status}: Failed to load ${componentPath}`);
        }
    } catch (error) {
        console.error(`Network Error loading ${componentPath}:`, error);
    }
}

function highlightActiveLinks(container) {
    const currentPath = window.location.pathname;
    const links = container.querySelectorAll('a');
    
    links.forEach(link => {
        const linkPath = link.getAttribute('href');
        if (linkPath && currentPath.endsWith(linkPath)) {
            // Apply active styles for Nav
            if (link.classList.contains('nav-link')) {
                link.classList.add('nav-link-active');
            }
            // Apply active styles for Sidebar
            if (container.parentElement && container.parentElement.tagName === 'ASIDE' || link.closest('aside')) {
                link.classList.add('bg-white', 'text-indigo-700', 'font-bold', 'shadow-sm');
                link.classList.remove('text-slate-600');
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
