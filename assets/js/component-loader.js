/**
 * Finzo Component Loader
 * Dynamically injects shared HTML components into the page.
 */

async function loadComponent(elementId, componentPath) {
    const placeholder = document.getElementById(elementId);
    if (!placeholder) return;

    try {
        const response = await fetch(componentPath);
        if (response.ok) {
            const html = await response.text();
            placeholder.innerHTML = html;
        } else {
            console.error(`Failed to load component: ${componentPath}`);
        }
    } catch (error) {
        console.error(`Error loading component: ${componentPath}`, error);
    }
}

// Auto-load common components on DOM loaded
document.addEventListener('DOMContentLoaded', () => {
    // Determine the path to the components folder based on current location
    // Since we are running on a local server, we can use absolute paths from root.
    loadComponent('footer-placeholder', '/components/footer.html');
});
