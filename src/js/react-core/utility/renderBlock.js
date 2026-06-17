import { createRoot } from '@wordpress/element';
import domReady from '@wordpress/dom-ready';

/**
 * render Block
 **/
export function renderBlock(containerId, element) {
    domReady(() => {
        const mount = () => {
            const container = document.getElementById(containerId);
            if (container) {
                // Prevent multiple roots if MutationObserver fires twice
                if (!container.hasAttribute('data-react-mounted')) {
                    container.setAttribute('data-react-mounted', 'true');
                    const root = createRoot(container);
                    root.render(element);
                }
                return true;
            }
            return false;
        };

        if (!mount()) {
            const observer = new MutationObserver((mutations, obs) => {
                if (mount()) {
                    obs.disconnect();
                }
            });
            observer.observe(document.body, { childList: true, subtree: true });
            
            // Fallback timeout to prevent memory leaks if metabox never renders
            setTimeout(() => observer.disconnect(), 10000);
        }
    });
}
