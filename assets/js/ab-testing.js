/**
 * ab-testing.js - A/B testing client-side helper.
 *
 * Reads the assigned variant from the data-variant attribute on elements
 * marked with data-experiment="<name>" and ships a click event to
 * /api/ab/track on first interaction. Idempotent and silent on failure.
 *
 * Usage (no setup needed beyond including this script):
 *   <a data-experiment="homepage_cta" data-variant="treatment" href="...">CTA</a>
 */
(function () {
    'use strict';

    if (window.__abTestingInit) return;
    window.__abTestingInit = true;

    var TRACK_URL = (typeof BASE_URL !== 'undefined' ? BASE_URL : '') + '/api/ab/track';
    var fired     = {}; // dedupe by experiment:variant

    function send(experiment, variant, eventType, metadata) {
        if (!experiment || !variant) return;
        var key = experiment + '|' + variant;
        if (fired[key]) return;            // dedupe within page
        fired[key] = true;

        try {
            var body = JSON.stringify({
                experiment_name: experiment,
                variant: variant,
                event_type: eventType || 'click',
                metadata: metadata || {}
            });
            if (navigator.sendBeacon) {
                var blob = new Blob([body], { type: 'application/json' });
                navigator.sendBeacon(TRACK_URL, blob);
                return;
            }
            fetch(TRACK_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: body,
                keepalive: true
            }).catch(function () { /* silent */ });
        } catch (e) { /* silent */ }
    }

    function attach(el) {
        var exp = el.getAttribute('data-experiment');
        var var_ = el.getAttribute('data-variant');
        if (!exp) return;
        el.addEventListener('click', function () {
            send(exp, var_, 'click', { href: el.getAttribute('href') || null });
        }, { passive: true });
    }

    function init() {
        var nodes = document.querySelectorAll('[data-experiment]');
        for (var i = 0; i < nodes.length; i++) attach(nodes[i]);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Expose for manual fire (e.g. on form submit)
    window.ABTest = { track: send };
})();
