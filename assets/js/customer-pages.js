/* APS Dream Home - Customer Pages JavaScript
   AJAX form helper, multi-step wizard, image preview, helpers
   Depends on: APS global (window.APS) from frontend-enhancements.js
   ---------------------------------------------------------------- */
(function() {
    'use strict';

    var CP = window.CustomerPages = window.CustomerPages || {};

    // ----- Configuration -----
    CP.config = {
        maxImageSize: 5 * 1024 * 1024,
        allowedImageTypes: ['image/jpeg', 'image/png', 'image/webp'],
        ifscDebounce: 500,
        csrfMetaName: 'csrf-token',
        counterWarn: 20
    };

    CP.init = function() {
        CP.initAjaxForms();
        CP.initWizards();
        CP.initImagePreviews();
        CP.initCounters();
        CP.initCopyButtons();
        CP.initCountUp();
        CP.initIfscLookup();
    };

    // ----- AJAX Form Helper -----
    CP.ajaxForm = function(formEl, options) {
        if (typeof formEl === 'string') formEl = document.querySelector(formEl);
        if (!formEl) return null;
        options = options || {};
        var onSuccess = options.onSuccess || function(res) {
            if (res && res.message) APS.toast(res.message, 'success');
        };
        var onError = options.onError || function(err) {
            APS.toast((err && err.message) || 'Submission failed', 'error');
        };

        formEl.addEventListener('submit', function(e) {
            e.preventDefault();
            if (typeof APS !== 'undefined' && !APS.validateForm(formEl)) {
                return;
            }
            var submitBtn = formEl.querySelector('[type="submit"]');
            var formData = new FormData(formEl);

            // Show loading state on button
            if (submitBtn) APS.showButtonLoader(submitBtn);

            // Determine if upload (has file inputs)
            var hasFile = formEl.querySelector('input[type="file"]');
            var url = formEl.getAttribute('action') || window.location.href;

            fetch(url, {
                method: formEl.getAttribute('method') || 'POST',
                body: formData,
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-Token': CP.getCsrfToken()
                }
            })
            .then(function(r) {
                if (submitBtn) APS.hideButtonLoader(submitBtn);
                var ct = r.headers.get('content-type') || '';
                if (ct.indexOf('application/json') !== -1) {
                    return r.json().then(function(data) { return { ok: r.ok, status: r.status, data: data }; });
                }
                return r.text().then(function(t) {
                    return { ok: r.ok, status: r.status, data: { success: r.ok, redirect: null, html: t } };
                });
            })
            .then(function(payload) {
                if (!payload.ok || (payload.data && payload.data.success === false)) {
                    var msg = (payload.data && (payload.data.message || payload.data.error)) || 'Submission failed';
                    throw new Error(msg);
                }
                if (payload.data && payload.data.redirect) {
                    APS.toast(payload.data.message || 'Success', 'success');
                    setTimeout(function() { window.location.href = payload.data.redirect; }, 600);
                } else {
                    onSuccess(payload.data || {});
                    if (options.reset !== false) formEl.reset();
                }
            })
            .catch(function(err) {
                if (submitBtn) APS.hideButtonLoader(submitBtn);
                onError(err);
            });
        });

        return formEl;
    };

    CP.initAjaxForms = function() {
        document.querySelectorAll('form[data-aps-ajax]').forEach(function(form) {
            var opts = {
                onSuccess: function(res) {
                    if (res.message) APS.toast(res.message, 'success');
                }
            };
            try {
                var customSuccess = form.getAttribute('data-aps-success');
                if (customSuccess) {
                    var fn = new Function('response', 'APS', customSuccess);
                    opts.onSuccess = function(res) { fn(res, window.APS); };
                }
            } catch (e) {}
            CP.ajaxForm(form, opts);
        });
    };

    // ----- CSRF Token Helper -----
    CP.getCsrfToken = function() {
        var meta = document.querySelector('meta[name="' + CP.config.csrfMetaName + '"]');
        if (meta) return meta.getAttribute('content');
        // Fallback to first input with csrf_token
        var input = document.querySelector('input[name="csrf_token"]');
        if (input) return input.value;
        return '';
    };

    // ----- Multi-Step Wizard -----
    CP.wizard = function(wizardEl, options) {
        if (typeof wizardEl === 'string') wizardEl = document.querySelector(wizardEl);
        if (!wizardEl) return null;
        options = options || {};
        var panels = wizardEl.querySelectorAll('.aps-cp-wizard-panel');
        var steps = wizardEl.querySelectorAll('.aps-cp-wizard-step');
        var progressBar = wizardEl.querySelector('.aps-cp-wizard-progress-bar');
        var prevBtn = wizardEl.querySelector('[data-wizard-prev]');
        var nextBtn = wizardEl.querySelector('[data-wizard-next]');
        var submitBtn = wizardEl.querySelector('[data-wizard-submit]');
        var form = wizardEl.querySelector('form');
        var current = 0;
        var total = panels.length;

        function show(index) {
            if (index < 0 || index >= total) return;
            panels.forEach(function(p, i) {
                p.classList.toggle('active', i === index);
            });
            steps.forEach(function(s, i) {
                s.classList.remove('active', 'completed');
                if (i < index) s.classList.add('completed');
                else if (i === index) s.classList.add('active');
            });
            if (progressBar) {
                progressBar.style.width = (((index + 1) / total) * 100) + '%';
            }
            current = index;
            if (prevBtn) prevBtn.disabled = (index === 0);
            if (nextBtn) nextBtn.style.display = (index === total - 1) ? 'none' : '';
            if (submitBtn) submitBtn.style.display = (index === total - 1) ? '' : 'none';
            // Scroll to top of wizard
            wizardEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        function validatePanel(panel) {
            var valid = true;
            panel.querySelectorAll('input, select, textarea').forEach(function(field) {
                if (typeof APS !== 'undefined' && !APS.validateField(field)) valid = false;
            });
            return valid;
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', function() {
                if (validatePanel(panels[current])) {
                    show(current + 1);
                } else {
                    APS.toast('Please fill in all required fields', 'warning');
                }
            });
        }
        if (prevBtn) {
            prevBtn.addEventListener('click', function() { show(current - 1); });
        }
        // Step click navigation
        steps.forEach(function(step, i) {
            step.addEventListener('click', function() {
                if (i <= current || (i === current + 1 && validatePanel(panels[current]))) {
                    show(i);
                } else if (i > current + 1) {
                    APS.toast('Please complete the current step first', 'warning');
                }
            });
            step.style.cursor = 'pointer';
        });

        // If form provided, AJAX submit
        if (form) {
            CP.ajaxForm(form, options);
        }

        show(0);
        return { show: show, current: function() { return current; } };
    };

    CP.initWizards = function() {
        document.querySelectorAll('[data-aps-wizard]').forEach(function(el) {
            CP.wizard(el);
        });
    };

    // ----- Image Preview -----
    CP.imagePreview = function(fileInput, previewEl, options) {
        if (typeof fileInput === 'string') fileInput = document.querySelector(fileInput);
        if (typeof previewEl === 'string') previewEl = document.querySelector(previewEl);
        if (!fileInput || !previewEl) return null;
        options = options || {};
        var maxFiles = options.maxFiles || 5;
        var onAdd = options.onAdd || function() {};
        var onRemove = options.onRemove || function() {};
        var files = [];

        function render() {
            previewEl.innerHTML = '';
            if (files.length === 0) {
                if (options.emptyHtml) previewEl.innerHTML = options.emptyHtml;
                return;
            }
            files.forEach(function(file, idx) {
                var thumb = document.createElement('div');
                thumb.className = 'aps-cp-image-thumb' + (idx === 0 ? ' is-primary' : '');
                var img = document.createElement('img');
                img.src = file.dataUrl;
                img.alt = file.name;
                img.loading = 'lazy';
                var remove = document.createElement('button');
                remove.type = 'button';
                remove.className = 'aps-cp-image-thumb-remove';
                remove.setAttribute('aria-label', 'Remove ' + file.name);
                remove.innerHTML = '<i class="fas fa-times"></i>';
                remove.addEventListener('click', function(e) {
                    e.preventDefault();
                    files.splice(idx, 1);
                    syncInput();
                    render();
                    onRemove(idx);
                });
                thumb.appendChild(img);
                thumb.appendChild(remove);
                previewEl.appendChild(thumb);
            });
        }

        function syncInput() {
            try {
                var dt = new DataTransfer();
                files.forEach(function(f) { dt.items.add(f.file); });
                fileInput.files = dt.files;
            } catch (e) { /* browser doesn't allow programmatic file input mutation */ }
        }

        fileInput.addEventListener('change', function() {
            var newFiles = Array.from(fileInput.files || []);
            var added = 0;
            newFiles.forEach(function(file) {
                if (files.length >= maxFiles) return;
                if (CP.config.allowedImageTypes.indexOf(file.type) === -1) {
                    APS.toast('Unsupported image type: ' + file.name, 'warning');
                    return;
                }
                if (file.size > CP.config.maxImageSize) {
                    APS.toast(file.name + ' is too large (max 5MB)', 'warning');
                    return;
                }
                var reader = new FileReader();
                reader.onload = function(e) {
                    files.push({ file: file, name: file.name, dataUrl: e.target.result });
                    added++;
                    if (added === newFiles.length) render();
                    onAdd(files.length);
                };
                reader.readAsDataURL(file);
            });
            if (newFiles.length === 0) render();
        });

        // Optional dropzone click
        var dropzone = fileInput.closest('.aps-cp-dropzone');
        if (dropzone) {
            dropzone.addEventListener('click', function(e) {
                if (e.target.tagName !== 'INPUT') fileInput.click();
            });
            ['dragenter', 'dragover'].forEach(function(ev) {
                dropzone.addEventListener(ev, function(e) {
                    e.preventDefault();
                    dropzone.classList.add('is-dragging');
                });
            });
            ['dragleave', 'drop'].forEach(function(ev) {
                dropzone.addEventListener(ev, function(e) {
                    e.preventDefault();
                    dropzone.classList.remove('is-dragging');
                });
            });
            dropzone.addEventListener('drop', function(e) {
                e.preventDefault();
                fileInput.files = e.dataTransfer.files;
                fileInput.dispatchEvent(new Event('change'));
            });
        }

        render();
        return { files: function() { return files; }, clear: function() { files = []; syncInput(); render(); } };
    };

    CP.initImagePreviews = function() {
        document.querySelectorAll('[data-aps-image-preview]').forEach(function(input) {
            var target = input.getAttribute('data-aps-image-preview');
            var preview = document.querySelector(target);
            if (preview) CP.imagePreview(input, preview, { maxFiles: parseInt(input.getAttribute('data-max-files') || '5', 10) });
        });
    };

    // ----- Character Counter -----
    CP.counter = function(inputEl, counterEl) {
        if (typeof inputEl === 'string') inputEl = document.querySelector(inputEl);
        if (typeof counterEl === 'string') counterEl = document.querySelector(counterEl);
        if (!inputEl || !counterEl) return;
        var max = parseInt(inputEl.getAttribute('maxlength') || '255', 10);
        function update() {
            var len = (inputEl.value || '').length;
            counterEl.textContent = len + ' / ' + max;
            counterEl.classList.toggle('text-danger', len > max - CP.config.counterWarn);
        }
        inputEl.addEventListener('input', update);
        update();
    };

    CP.initCounters = function() {
        document.querySelectorAll('[data-aps-counter]').forEach(function(input) {
            var target = input.getAttribute('data-aps-counter');
            var counter = document.querySelector(target);
            if (counter) CP.counter(input, counter);
        });
    };

    // ----- Copy to Clipboard -----
    CP.copy = function(btn, text) {
        if (typeof btn === 'string') btn = document.querySelector(btn);
        if (!btn) return;
        var getText = typeof text === 'function' ? text : function() { return text; };
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(getText()).then(function() {
                var orig = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-check"></i>';
                setTimeout(function() { btn.innerHTML = orig; }, 1800);
            });
        } else {
            // Fallback
            var ta = document.createElement('textarea');
            ta.value = getText();
            document.body.appendChild(ta);
            ta.select();
            try { document.execCommand('copy'); btn.innerHTML = '<i class="fas fa-check"></i>'; setTimeout(function() { btn.innerHTML = btn.getAttribute('data-orig-html') || btn.innerHTML; }, 1800); } catch (e) {}
            document.body.removeChild(ta);
        }
    };

    CP.initCopyButtons = function() {
        document.querySelectorAll('[data-aps-copy]').forEach(function(btn) {
            btn.setAttribute('data-orig-html', btn.innerHTML);
            var sourceSel = btn.getAttribute('data-aps-copy');
            btn.addEventListener('click', function() {
                var source = document.querySelector(sourceSel);
                CP.copy(btn, source ? source.value : sourceSel);
            });
        });
    };

    // ----- Count Up Animation -----
    CP.countUp = function(el, target, duration) {
        if (typeof el === 'string') el = document.querySelector(el);
        if (!el) return;
        duration = duration || 800;
        var start = 0;
        var startTime = null;
        function step(ts) {
            if (!startTime) startTime = ts;
            var p = Math.min((ts - startTime) / duration, 1);
            var eased = 1 - Math.pow(1 - p, 3);
            el.textContent = Math.floor(start + (target - start) * eased).toLocaleString();
            if (p < 1) requestAnimationFrame(step);
        }
        requestAnimationFrame(step);
    };

    CP.initCountUp = function() {
        document.querySelectorAll('[data-aps-count]').forEach(function(el) {
            var target = parseInt(el.getAttribute('data-aps-count') || '0', 10);
            if (target > 0) CP.countUp(el, target);
        });
    };

    // ----- IFSC Lookup (Bank Details) -----
    CP.ifscLookup = function(inputEl, options) {
        if (typeof inputEl === 'string') inputEl = document.querySelector(inputEl);
        if (!inputEl) return;
        options = options || {};
        var onFound = options.onFound || function() {};
        var onNotFound = options.onNotFound || function() {};
        var statusEl = options.statusEl;
        var bankNameEl = options.bankNameEl;
        var branchNameEl = options.branchNameEl;
        var triggerEl = options.triggerEl;
        var timer = null;
        var lastQueried = '';

        function setStatus(msg, type) {
            if (statusEl) {
                statusEl.innerHTML = '<i class="fas ' + (type === 'loading' ? 'fa-spinner fa-spin' : type === 'success' ? 'fa-check-circle' : type === 'warning' ? 'fa-exclamation-circle' : type === 'error' ? 'fa-times-circle' : 'fa-info-circle') + '"></i> ' + msg;
                statusEl.className = 'aps-cp-ifsc-status ' + (type || '');
            }
        }

        function lookup() {
            var ifsc = (inputEl.value || '').trim().toUpperCase();
            if (ifsc.length < 8) {
                setStatus('Enter a valid IFSC code (11 chars)', '');
                return;
            }
            if (ifsc === lastQueried) return;
            lastQueried = ifsc;
            inputEl.value = ifsc;
            setStatus('Looking up bank details...', 'loading');

            fetch('/api/banks/ifsc/' + encodeURIComponent(ifsc), {
                credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data && data.found) {
                    if (bankNameEl) bankNameEl.value = data.bank_name || '';
                    if (branchNameEl) branchNameEl.value = data.branch || '';
                    setStatus('Found: ' + (data.bank_name || '') + (data.branch ? ' — ' + data.branch : ''), 'success');
                    inputEl.classList.add('is-valid');
                    inputEl.classList.remove('is-invalid');
                    onFound(data);
                } else {
                    if (bankNameEl) bankNameEl.value = '';
                    if (branchNameEl) branchNameEl.value = '';
                    setStatus('IFSC not found. Enter bank details manually.', 'warning');
                    inputEl.classList.remove('is-valid');
                    onNotFound(data);
                }
            })
            .catch(function() {
                setStatus('Could not reach bank lookup. Enter manually.', 'error');
            });
        }

        inputEl.addEventListener('input', function() {
            this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '').slice(0, 11);
            clearTimeout(timer);
            timer = setTimeout(lookup, CP.config.ifscDebounce);
        });
        inputEl.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') { e.preventDefault(); lookup(); }
        });
        if (triggerEl) triggerEl.addEventListener('click', function(e) { e.preventDefault(); clearTimeout(timer); lookup(); });
    };

    CP.initIfscLookup = function() {
        document.querySelectorAll('[data-aps-ifsc]').forEach(function(input) {
            CP.ifscLookup(input, {
                statusEl: document.querySelector(input.getAttribute('data-aps-ifsc-status')),
                bankNameEl: document.querySelector(input.getAttribute('data-aps-ifsc-bank')),
                branchNameEl: document.querySelector(input.getAttribute('data-aps-ifsc-branch')),
                triggerEl: document.querySelector(input.getAttribute('data-aps-ifsc-trigger'))
            });
        });
    };

    // ----- Delete Confirm -----
    CP.confirmDelete = function(btn, options) {
        if (typeof btn === 'string') btn = document.querySelector(btn);
        if (!btn) return;
        options = options || {};
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var msg = options.message || 'Are you sure you want to delete this? This cannot be undone.';
            if (window.confirm(msg)) {
                if (typeof options.onConfirm === 'function') {
                    options.onConfirm();
                } else if (btn.tagName === 'A' && btn.href) {
                    window.location.href = btn.href;
                } else if (btn.closest('form')) {
                    btn.closest('form').submit();
                }
            }
        });
    };

    CP.initConfirmDelete = function() {
        document.querySelectorAll('[data-aps-confirm-delete]').forEach(function(btn) {
            CP.confirmDelete(btn, {
                message: btn.getAttribute('data-aps-confirm-delete')
            });
        });
    };

    CP.injectCsrf = function() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        if (!meta) return;
        var token = meta.getAttribute('content');
        if (!token) return;
        document.querySelectorAll('input[name="csrf_token"]').forEach(function(input) {
            if (!input.value) input.value = token;
        });
    };

    CP.fetchCsrf = function() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    };

    // ----- Boot -----
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() { CP.init(); CP.initConfirmDelete(); CP.injectCsrf(); });
    } else {
        CP.init();
        CP.initConfirmDelete();
        CP.injectCsrf();
    }
})();
