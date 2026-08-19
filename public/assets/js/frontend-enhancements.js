/* APS Dream Home - Frontend Enhancements
   Loading states, error handling, toasts, validation, a11y helpers
   ---------------------------------------------------------------- */
(function() {
    'use strict';

    var APS = window.APS = window.APS || {};

    // ----- Configuration -----
    APS.config = {
        csrfToken: null,
        csrfHeader: 'X-CSRF-Token',
        toastDuration: 5000,
        defaultErrorMessage: 'Something went wrong. Please try again.',
        networkErrorMessage: 'Network error. Please check your connection and try again.',
    };

    APS.init = function() {
        APS.config.csrfToken = APS.getCsrfToken();
        APS.injectSkipLink();
        APS.injectBackToTop();
        APS.injectPageLoader();
        APS.injectLiveRegion();
        APS.bindGlobalHandlers();
        APS.enhanceForms();
        APS.enhanceImages();
        APS.enhanceDeleteActions();
        APS.bindLazyLoad();
        APS.initFloatingComponents();
    };

    // ----- CSRF token retrieval -----
    APS.getCsrfToken = function() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        if (meta) return meta.getAttribute('content');
        return null;
    };

    APS.csrfHeaders = function(headers) {
        headers = headers || {};
        if (APS.config.csrfToken) {
            headers[APS.config.csrfHeader] = APS.config.csrfToken;
        }
        return headers;
    };

    // ----- Skip link for accessibility -----
    APS.injectSkipLink = function() {
        if (document.querySelector('.aps-skip-link')) return;
        var link = document.createElement('a');
        link.className = 'aps-skip-link';
        link.href = '#aps-main-content';
        link.textContent = 'Skip to main content';
        document.body.insertBefore(link, document.body.firstChild);
        if (!document.getElementById('aps-main-content')) {
            var main = document.querySelector('main') || document.querySelector('[role="main"]');
            if (main && !main.id) main.id = 'aps-main-content';
        }
    };

    // ----- Back to top button -----
    APS.injectBackToTop = function() {
        if (document.querySelector('.aps-back-to-top')) return;
        var btn = document.createElement('button');
        btn.className = 'aps-back-to-top';
        btn.type = 'button';
        btn.setAttribute('aria-label', 'Back to top');
        btn.title = 'Back to top';
        btn.innerHTML = '<i class="fas fa-arrow-up" aria-hidden="true"></i>';
        btn.addEventListener('click', function() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
        document.body.appendChild(btn);
        var ticking = false;
        window.addEventListener('scroll', function() {
            if (!ticking) {
                window.requestAnimationFrame(function() {
                    if (window.pageYOffset > 400) {
                        btn.classList.add('aps-visible');
                    } else {
                        btn.classList.remove('aps-visible');
                    }
                    ticking = false;
                });
                ticking = true;
            }
        }, { passive: true });
    };

    // ----- Page loader overlay -----
    APS.injectPageLoader = function() {
        if (document.querySelector('.aps-page-loading')) return;
        var div = document.createElement('div');
        div.className = 'aps-page-loading';
        div.setAttribute('aria-hidden', 'true');
        div.innerHTML = '<span class="aps-spinner aps-spinner-lg" role="status"></span>';
        document.body.appendChild(div);
    };

    APS.showPageLoader = function() {
        var el = document.querySelector('.aps-page-loading');
        if (el) el.classList.add('aps-active');
    };
    APS.hidePageLoader = function() {
        var el = document.querySelector('.aps-page-loading');
        if (el) el.classList.remove('aps-active');
    };

    // ----- ARIA live region for screen reader announcements -----
    APS.injectLiveRegion = function() {
        if (document.querySelector('.aps-live-region')) return;
        var polite = document.createElement('div');
        polite.className = 'aps-live-region';
        polite.setAttribute('aria-live', 'polite');
        polite.setAttribute('aria-atomic', 'true');
        polite.id = 'aps-live-polite';
        var assertive = document.createElement('div');
        assertive.className = 'aps-live-region';
        assertive.setAttribute('aria-live', 'assertive');
        assertive.setAttribute('aria-atomic', 'true');
        assertive.id = 'aps-live-assertive';
        document.body.appendChild(polite);
        document.body.appendChild(assertive);
    };

    APS.announce = function(message, priority) {
        var id = priority === 'assertive' ? 'aps-live-assertive' : 'aps-live-polite';
        var el = document.getElementById(id);
        if (!el) return;
        el.textContent = '';
        setTimeout(function() { el.textContent = message; }, 50);
    };

    // ----- Toast notifications -----
    APS.injectToastContainer = function() {
        if (document.querySelector('.aps-toast-container')) return;
        var div = document.createElement('div');
        div.className = 'aps-toast-container';
        div.setAttribute('role', 'region');
        div.setAttribute('aria-label', 'Notifications');
        document.body.appendChild(div);
    };

    APS.toast = function(message, type, title) {
        APS.injectToastContainer();
        type = type || 'info';
        var icons = {
            success: 'fa-check-circle',
            error: 'fa-exclamation-circle',
            warning: 'fa-exclamation-triangle',
            info: 'fa-info-circle'
        };
        var titles = {
            success: title || 'Success',
            error: title || 'Error',
            warning: title || 'Warning',
            info: title || 'Info'
        };
        var toast = document.createElement('div');
        toast.className = 'aps-toast aps-toast-' + type;
        toast.setAttribute('role', type === 'error' ? 'alert' : 'status');
        toast.innerHTML =
            '<i class="aps-toast-icon fas ' + icons[type] + '" aria-hidden="true"></i>' +
            '<div class="aps-toast-body">' +
                '<p class="aps-toast-title">' + APS.escapeHtml(titles[type]) + '</p>' +
                '<p class="aps-toast-message">' + APS.escapeHtml(message) + '</p>' +
            '</div>' +
            '<button type="button" class="aps-toast-close" aria-label="Close notification">&times;</button>';
        var container = document.querySelector('.aps-toast-container');
        container.appendChild(toast);
        requestAnimationFrame(function() { toast.classList.add('aps-toast-show'); });
        APS.announce(titles[type] + ': ' + message, type === 'error' ? 'assertive' : 'polite');
        var closeBtn = toast.querySelector('.aps-toast-close');
        var timer = setTimeout(function() { dismiss(); }, APS.config.toastDuration);
        function dismiss() {
            clearTimeout(timer);
            toast.classList.remove('aps-toast-show');
            setTimeout(function() {
                if (toast.parentNode) toast.parentNode.removeChild(toast);
            }, 300);
        }
        closeBtn.addEventListener('click', dismiss);
        return { dismiss: dismiss };
    };

    // ----- HTML escape -----
    APS.escapeHtml = function(str) {
        if (str == null) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    };

    // ----- Fetch with CSRF + error handling -----
    APS.fetch = function(url, options) {
        options = options || {};
        options.headers = APS.csrfHeaders(options.headers || {});
        if (options.body && typeof options.body === 'object' && !(options.body instanceof FormData)) {
            options.headers['Content-Type'] = 'application/json';
            options.body = JSON.stringify(options.body);
        }
        options.credentials = 'same-origin';
        var showLoader = options.showLoader !== false;
        if (showLoader) APS.showButtonLoader(options.context);
        return fetch(url, options)
            .then(function(response) {
                if (showLoader) APS.hideButtonLoader(options.context);
                if (!response.ok) {
                    return response.json().catch(function() {
                        throw new Error('HTTP ' + response.status + ': ' + response.statusText);
                    }).then(function(data) {
                        var err = new Error(data.message || data.error || APS.config.defaultErrorMessage);
                        err.status = response.status;
                        err.data = data;
                        throw err;
                    });
                }
                return response.json().catch(function() { return response.text(); });
            })
            .catch(function(error) {
                if (showLoader) APS.hideButtonLoader(options.context);
                if (!error.status || error.status >= 500) {
                    error.message = error.message || APS.config.networkErrorMessage;
                }
                if (options.silent !== true) {
                    APS.toast(error.message || APS.config.defaultErrorMessage, 'error');
                }
                APS.logError(error, url, options);
                throw error;
            });
    };

    APS.logError = function(error, url, options) {
        try {
            var payload = {
                message: error.message,
                stack: error.stack,
                url: url,
                status: error.status,
                method: options.method || 'GET',
                timestamp: new Date().toISOString(),
                userAgent: navigator.userAgent
            };
            if (window.console && console.error) {
                console.error('[APS Error]', payload);
            }
        } catch (e) { /* ignore */ }
    };

    // ----- Button loading state -----
    APS.showButtonLoader = function(context) {
        if (!context) return;
        var btn = context instanceof HTMLElement ? context : document.querySelector(context);
        if (!btn) return;
        btn.classList.add('aps-loading');
        btn.disabled = true;
        if (!btn.querySelector('.aps-btn-spinner')) {
            var spinner = document.createElement('span');
            spinner.className = 'aps-btn-spinner aps-spinner aps-spinner-sm';
            spinner.setAttribute('aria-hidden', 'true');
            var label = document.createElement('span');
            label.className = 'aps-btn-label';
            while (btn.firstChild) label.appendChild(btn.firstChild);
            btn.appendChild(spinner);
            btn.appendChild(label);
        }
        btn.setAttribute('aria-busy', 'true');
    };

    APS.hideButtonLoader = function(context) {
        if (!context) return;
        var btn = context instanceof HTMLElement ? context : document.querySelector(context);
        if (!btn) return;
        btn.classList.remove('aps-loading');
        btn.disabled = false;
        btn.removeAttribute('aria-busy');
    };

    // ----- Form enhancement: real-time validation + submit handling -----
    APS.enhanceForms = function() {
        var forms = document.querySelectorAll('form[data-aps-validate], form:not([data-aps-no-validate])');
        forms.forEach(function(form) {
            // Skip forms without inputs to validate
            if (!form.querySelector('input, select, textarea')) return;
            // Real-time field validation
            form.querySelectorAll('input, select, textarea').forEach(function(field) {
                if (field.type === 'hidden' || field.type === 'submit' || field.type === 'button') return;
                field.addEventListener('blur', function() { APS.validateField(field); });
                field.addEventListener('input', function() {
                    var wrap = field.closest('.aps-form-field');
                    if (wrap && wrap.classList.contains('aps-has-error')) {
                        APS.validateField(field);
                    }
                });
            });
            // Form submit
            form.addEventListener('submit', function(e) {
                var valid = APS.validateForm(form);
                if (!valid) {
                    e.preventDefault();
                    var firstError = form.querySelector('.aps-has-error input, .aps-has-error select, .aps-has-error textarea');
                    if (firstError) {
                        firstError.focus();
                        APS.announce('Please fix the errors in the form', 'assertive');
                    }
                    return false;
                }
                var submitBtn = form.querySelector('button[type="submit"], input[type="submit"]');
                if (submitBtn && form.dataset.apsAjax !== 'true') {
                    APS.showButtonLoader(submitBtn);
                }
                return true;
            }, false);
        });
    };

    APS.validateForm = function(form) {
        var valid = true;
        form.querySelectorAll('input, select, textarea').forEach(function(field) {
            if (!APS.validateField(field)) valid = false;
        });
        return valid;
    };

    APS.validateField = function(field) {
        var wrap = field.closest('.aps-form-field') || field.parentElement;
        var value = (field.value || '').trim();
        var type = field.type;
        var required = field.hasAttribute('required') || field.hasAttribute('data-required');
        var isValid = true;
        var message = '';

        // Required check
        if (required && !value) {
            isValid = false;
            message = field.getAttribute('data-error-required') || (field.labels && field.labels[0] ? field.labels[0].textContent + ' is required' : 'This field is required');
        }
        // Email
        else if (type === 'email' && value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
            isValid = false;
            message = 'Please enter a valid email address';
        }
        // Phone (10-13 digits)
        else if ((field.getAttribute('pattern') === '[0-9]{10}' || type === 'tel') && value && !/^[0-9+\-\s()]{10,15}$/.test(value.replace(/\s/g, ''))) {
            isValid = false;
            message = 'Please enter a valid phone number';
        }
        // URL
        else if (type === 'url' && value) {
            try { new URL(value); } catch (e) {
                isValid = false;
                message = 'Please enter a valid URL';
            }
        }
        // Min length
        else if (field.minLength > 0 && value && value.length < field.minLength) {
            isValid = false;
            message = 'Must be at least ' + field.minLength + ' characters';
        }
        // Max length
        else if (field.maxLength > 0 && value.length > field.maxLength) {
            isValid = false;
            message = 'Must be no more than ' + field.maxLength + ' characters';
        }
        // Pattern
        else if (field.pattern && value) {
            var re = new RegExp('^' + field.pattern + '$');
            if (!re.test(value)) {
                isValid = false;
                message = field.title || 'Please match the requested format';
            }
        }
        // Number range
        else if (type === 'number' && value) {
            var n = parseFloat(value);
            if (isNaN(n)) { isValid = false; message = 'Please enter a number'; }
            else if (field.min !== '' && n < parseFloat(field.min)) { isValid = false; message = 'Minimum value is ' + field.min; }
            else if (field.max !== '' && n > parseFloat(field.max)) { isValid = false; message = 'Maximum value is ' + field.max; }
        }

        if (wrap) {
            if (isValid) {
                wrap.classList.remove('aps-has-error');
                if (value) wrap.classList.add('aps-has-success');
                else wrap.classList.remove('aps-has-success');
                var errEl = wrap.querySelector('.aps-field-error');
                if (errEl) errEl.textContent = '';
            } else {
                wrap.classList.add('aps-has-error');
                wrap.classList.remove('aps-has-success');
                var errEl = wrap.querySelector('.aps-field-error');
                if (!errEl) {
                    errEl = document.createElement('div');
                    errEl.className = 'aps-field-error';
                    errEl.setAttribute('role', 'alert');
                    wrap.appendChild(errEl);
                }
                errEl.textContent = message;
            }
        }
        // Required accessibility
        if (isValid) {
            field.removeAttribute('aria-invalid');
        } else {
            field.setAttribute('aria-invalid', 'true');
        }
        return isValid;
    };

    // ----- Image enhancement: lazy fade-in -----
    APS.enhanceImages = function() {
        if (!('IntersectionObserver' in window)) return;
        var imgs = document.querySelectorAll('img.aps-img-fade');
        if (imgs.length === 0) return;
        var observer = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    var img = entry.target;
                    if (img.complete) {
                        img.classList.add('aps-img-loaded');
                    } else {
                        img.addEventListener('load', function() {
                            img.classList.add('aps-img-loaded');
                        });
                        img.addEventListener('error', function() {
                            img.classList.add('aps-img-loaded');
                        });
                    }
                    observer.unobserve(img);
                }
            });
        });
        imgs.forEach(function(img) { observer.observe(img); });
    };

    // ----- Confirmation for destructive actions -----
    APS.enhanceDeleteActions = function() {
        document.querySelectorAll('[data-aps-confirm]').forEach(function(el) {
            if (el.tagName === 'FORM') {
                el.addEventListener('submit', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    var msg = el.getAttribute('data-aps-confirm') || 'Are you sure you want to proceed?';
                    apsConfirm(msg).then(function(ok) {
                        if (!ok) return;
                        el.removeAttribute('data-aps-confirm');
                        el.submit();
                    });
                });
            } else {
                el.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    var msg = el.getAttribute('data-aps-confirm') || 'Are you sure you want to proceed?';
                    apsConfirm(msg).then(function(ok) {
                        if (!ok) return;
                        if (el.tagName === 'A') {
                            window.location.href = el.getAttribute('href');
                        } else if (el.closest('form')) {
                            el.closest('form').submit();
                        } else {
                            el.removeAttribute('data-aps-confirm');
                            el.click();
                        }
                    });
                });
            }
        });
    };

    // ----- Lazy load images that are below the fold -----
    APS.bindLazyLoad = function() {
        if (!('IntersectionObserver' in window)) return;
        var imgs = document.querySelectorAll('img[data-src]:not([src])');
        if (imgs.length === 0) return;
        var observer = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    var img = entry.target;
                    var src = img.getAttribute('data-src');
                    if (src) {
                        img.src = src;
                        img.removeAttribute('data-src');
                    }
                    observer.unobserve(img);
                }
            });
        }, { rootMargin: '200px' });
        imgs.forEach(function(img) { observer.observe(img); });
    };

    // ----- Floating components on scroll -----
    APS.initFloatingComponents = function() {
        // Mark all images above the fold with eager loading
        var imgs = document.querySelectorAll('img:not([loading])');
        imgs.forEach(function(img) {
            var rect = img.getBoundingClientRect();
            if (rect.top < 200 && rect.top > -100) {
                img.setAttribute('loading', 'eager');
            } else {
                img.setAttribute('loading', 'lazy');
            }
        });
    };

    // ----- Global event handlers -----
    APS.bindGlobalHandlers = function() {
        // Handle unload - hide any lingering loaders
        window.addEventListener('beforeunload', function() {
            APS.hidePageLoader();
        });
        // Network status announcements
        window.addEventListener('online', function() {
            APS.toast('You are back online', 'success', 'Connected');
        });
        window.addEventListener('offline', function() {
            APS.toast('You are offline. Some features may not work.', 'warning', 'Disconnected');
        });
        // Mark external links
        document.querySelectorAll('a[href^="http"]:not([href*="' + window.location.host + '"])').forEach(function(link) {
            if (!link.hasAttribute('rel')) {
                link.setAttribute('rel', 'noopener noreferrer');
            }
            if (!link.hasAttribute('target')) {
                link.setAttribute('target', '_blank');
            }
        });
    };

    // ----- Auto-init -----
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', APS.init);
    } else {
        APS.init();
    }

    // ----- Page loader (top progress bar on navigation) -----
    APS.pageLoader = {
        el: null,
        show: function() {
            if (this.el) return;
            this.el = document.createElement('div');
            this.el.className = 'aps-page-loader';
            this.el.setAttribute('aria-hidden', 'true');
            document.body.appendChild(this.el);
        },
        hide: function() {
            if (!this.el) return;
            this.el.classList.add('is-hidden');
            var self = this;
            setTimeout(function() {
                if (self.el && self.el.parentNode) {
                    self.el.parentNode.removeChild(self.el);
                }
                self.el = null;
            }, 350);
        }
    };

    document.addEventListener('DOMContentLoaded', function() {
        var inFlight = 0;
        var origFetch = window.fetch;
        if (typeof origFetch === 'function') {
            window.fetch = function() {
                inFlight++;
                APS.pageLoader.show();
                return origFetch.apply(this, arguments).finally(function() {
                    inFlight--;
                    if (inFlight === 0) APS.pageLoader.hide();
                });
            };
        }
        // Also hide on full page load
        window.addEventListener('pageshow', function() { APS.pageLoader.hide(); });
        window.addEventListener('load', function() { APS.pageLoader.hide(); });
    });
})();
