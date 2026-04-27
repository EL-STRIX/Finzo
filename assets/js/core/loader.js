/**
 * Finzo Core Component Loader
 * Handles dynamic injection of Navbars, Sidebars, and Footers.
 */

window.FinzoLoader = {
    /**
     * Load a component HTML into a placeholder
     */
    load: async function(elementId, componentPath) {
        const placeholder = document.getElementById(elementId);
        if (!placeholder) return;

        try {
            const response = await fetch(componentPath);
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            
            const html = await response.text();
            placeholder.innerHTML = html;
            
            this.resolvePaths(placeholder);
            this.highlightActive(placeholder);

            if (window.FinzoTheme) window.FinzoTheme.apply();
            
        } catch (error) {
            console.error(`Loader error [${componentPath}]:`, error);
        }
    },

    /**
     * Fix relative paths based on project base
     */
    resolvePaths: function(container) {
        const basePath = window.FinzoBasePath || '/Finzo/';
        container.querySelectorAll('[href^="/"], [src^="/"]').forEach(el => {
            const attr = el.hasAttribute('href') ? 'href' : 'src';
            const originalVal = el.getAttribute(attr);
            if (/^\/(views|assets|components)/.test(originalVal)) {
                el.setAttribute(attr, basePath + originalVal.substring(1));
            }
        });
    },

    /**
     * Set active class on current page links
     */
    highlightActive: function(container) {
        const fullPath = window.location.pathname.replace('.html', '').replace(/\/$/, "");
        container.querySelectorAll('a').forEach(link => {
            const href = link.getAttribute('href');
            if (!href) return;
            const cleanHref = href.replace('.html', '').replace(/\/$/, "");
            
            if (fullPath === cleanHref || fullPath.endsWith(cleanHref)) {
                if (link.classList.contains('nav-link')) link.classList.add('nav-link-active');
                if (link.classList.contains('admin-nav-item')) link.classList.add('active');
                if (link.closest('aside')) link.classList.add('active');
            }
        });
    }
};

// Global Initialization
document.addEventListener('DOMContentLoaded', () => {
    // Dynamically detect base path
    const script = document.querySelector('script[src*="loader.js"]');
    const basePath = script ? new URL(script.src).pathname.replace(/assets\/js\/core\/loader\.js$/, '') : '/Finzo/';
    window.FinzoBasePath = basePath;

    // Standard Landing/Auth placeholders
    window.FinzoLoader.load('navbar-placeholder', basePath + 'components/navbar.html');
    window.FinzoLoader.load('sidebar-placeholder', basePath + 'components/sidebar.html');
    window.FinzoLoader.load('footer-placeholder', basePath + 'components/footer.html');
});
