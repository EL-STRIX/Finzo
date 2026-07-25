/* ═══════════════════════════════════════════
   FINZO — Component Loader
   Lightweight Vanilla JS HTML partial loader
   ═══════════════════════════════════════════ */

(function() {
    async function loadComponents() {
        const elements = document.querySelectorAll('[data-include]');
        const promises = [];

        for (const el of elements) {
            const file = el.getAttribute('data-include');
            
            const promise = fetch(file)
                .then(response => {
                    if (response.ok) {
                        return response.text();
                    }
                    throw new Error(`Failed to load component: ${file}`);
                })
                .then(html => {
                    // Replace the element entirely with the fetched HTML
                    el.outerHTML = html;
                })
                .catch(err => {
                    console.error(err);
                    el.innerHTML = `<div class="text-red-500 font-bold p-4 border border-red-500/20 bg-red-50/50 rounded-xl">Error loading component</div>`;
                });
            
            promises.push(promise);
        }

        await Promise.all(promises);

        // Dispatch custom event to notify other scripts that DOM is fully ready
        const event = new CustomEvent('componentsLoaded');
        document.dispatchEvent(event);
        window.componentsLoaded = true;
    }

    // Run on DOMContentLoaded
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', loadComponents);
    } else {
        loadComponents();
    }
})();
