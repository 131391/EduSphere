/**
 * disabled-btn.js
 * Injects a lock icon into every disabled button that isn't already
 * showing a spinner. Works with Alpine.js reactive :disabled bindings.
 */
(function () {
    const ICON_CLASS = '__disabled-lock-icon__';
    const SPINNER_SELECTORS = '.animate-spin, [data-spinner]';

    function hasSpinner(btn) {
        return btn.querySelector(SPINNER_SELECTORS) !== null;
    }

    function injectIcon(btn) {
        if (btn.querySelector('.' + ICON_CLASS)) return; // already injected
        if (hasSpinner(btn)) return;                      // spinner visible — skip
        const icon = document.createElement('i');
        icon.className = 'fas fa-lock ' + ICON_CLASS;
        icon.style.cssText = 'font-size:0.7em;opacity:0.75;margin-right:0.35rem;flex-shrink:0;';
        btn.insertBefore(icon, btn.firstChild);
    }

    function removeIcon(btn) {
        const icon = btn.querySelector('.' + ICON_CLASS);
        if (icon) icon.remove();
    }

    function syncButton(btn) {
        if (btn.disabled) {
            injectIcon(btn);
        } else {
            removeIcon(btn);
        }
    }

    function syncAll() {
        document.querySelectorAll('button[\\:disabled], button').forEach(syncButton);
    }

    // Initial pass after Alpine has hydrated
    document.addEventListener('alpine:initialized', syncAll);

    // Watch for attribute mutations (Alpine toggling disabled)
    const observer = new MutationObserver(function (mutations) {
        mutations.forEach(function (mutation) {
            if (mutation.type === 'attributes' && mutation.attributeName === 'disabled') {
                syncButton(mutation.target);
            }
            if (mutation.type === 'childList') {
                mutation.addedNodes.forEach(function (node) {
                    if (node.nodeType === 1) {
                        if (node.tagName === 'BUTTON') syncButton(node);
                        node.querySelectorAll && node.querySelectorAll('button').forEach(syncButton);
                    }
                });
            }
        });
    });

    observer.observe(document.body, {
        attributes: true,
        attributeFilter: ['disabled'],
        childList: true,
        subtree: true,
    });
})();
