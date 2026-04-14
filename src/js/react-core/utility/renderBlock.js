import { createRoot } from '@wordpress/element';
import domReady from '@wordpress/dom-ready';

/**
 * render Block
 **/
export function renderBlock(containerId, element) {
    domReady(() => {
        const container = document.getElementById(containerId);

        if (!container) {
            return;
        }

        const root = createRoot(container);
        root.render(element);
    });
}
