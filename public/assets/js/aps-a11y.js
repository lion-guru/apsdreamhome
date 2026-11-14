/* APS accessibility helpers.
   Keyboard support for role="button" elements that use inline onclick handlers.
   HTML-in-JS templates (e.g. palette swatches) also get this via markup. */
(function () {
    'use strict';
    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Enter' && e.key !== ' ' && e.key !== 'Spacebar') return;
        var t = e.target;
        if (!t || typeof t.closest !== 'function') return;
        var el = t.closest('[role="button"]');
        if (!el) return;
        // Do not steal activation from a native interactive element inside the control.
        var inner = t.closest('a, button, input, select, textarea');
        if (inner && inner !== el) return;
        // Only elements carrying an inline onclick are actionable this way.
        if (!el.hasAttribute('onclick')) return;
        e.preventDefault();
        el.click();
    });
})();
