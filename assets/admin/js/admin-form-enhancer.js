/**
 * APS Dream Home - Admin Form Enhancer
 * Integrates SmartFormAutocomplete + Client-side validation + UX helpers
 * Must be loaded AFTER admin.js and smart-form-autocomplete.js
 */
(function () {
    'use strict';

    var smartForm = null;

    function initAdminFormEnhancer() {

        // =============================================
        // 1. SMART FORM AUTOCOMPLETE INTEGRATION
        // =============================================

        if (typeof window.SmartFormAutocomplete !== 'undefined') {
            smartForm = new window.SmartFormAutocomplete();

            // Location cascade: country -> state -> district -> city
            var countryEl = document.querySelector('select[name="country_id"]');
            var stateEl = document.querySelector('select[name="state_id"]');
            var districtEl = document.querySelector('select[name="district_id"]');
            var cityEl = document.querySelector('select[name="city_id"]');

            if (countryEl && stateEl && districtEl && cityEl) {
                smartForm.initLocationCascade(countryEl, stateEl, districtEl, cityEl, {
                    loadOnInit: true,
                    onCityChange: function () {
                        var citySelect = cityEl;
                        if (citySelect.value) {
                            citySelect.classList.add('is-valid');
                            citySelect.classList.remove('is-invalid');
                        }
                    }
                });
            } else if (stateEl && districtEl && cityEl) {
                // Partial cascade (no country, default to India)
                smartForm.initLocationCascade(null, stateEl, districtEl, cityEl, {
                    loadOnInit: true
                });
            }

            // Pincode auto-fill on #pincode
            smartForm.initPincodeAutofill('#pincode', {
                onFound: function (data) {
                    var cityInput = document.querySelector('input[name="city"], input[name="city_id"], #city');
                    var districtInput = document.querySelector('input[name="district"], input[name="district_id"], #district');
                    var stateInput = document.querySelector('input[name="state"], input[name="state_id"], #state');

                    if (cityInput && data.city) cityInput.value = data.city;
                    if (districtInput && data.district) districtInput.value = data.district;
                    if (stateInput && data.state) stateInput.value = data.state;
                },
                onNotFound: function () {
                    var feedback = document.querySelector('#pincode-feedback');
                    if (!feedback) return;
                    feedback.textContent = 'Pincode not found';
                    feedback.className = 'text-danger small mt-1';
                }
            });

            // Bank IFSC auto-fill on #ifsc
            smartForm.initBankIfsc('#ifsc', {
                onFound: function (data) {
                    var bankInput = document.querySelector('input[name="bank_name"], #bank_name');
                    var branchInput = document.querySelector('input[name="branch"], #branch');
                    if (bankInput && data.bank_name) bankInput.value = data.bank_name;
                    if (branchInput && data.branch) branchInput.value = data.branch;
                }
            });

            // Bank search on #bank_search
            smartForm.initBankSearch('#bank_search', '#bank_id');
        }

        // =============================================
        // 2. PRICE AUTO-FILL
        // =============================================

        var priceSelectors = [
            'select[name="property_id"]',
            'select[name="plot_id"]',
            'select[name="product_id"]',
            'select[name="item_id"]'
        ];

        priceSelectors.forEach(function (sel) {
            var el = document.querySelector(sel);
            if (!el) return;
            el.addEventListener('change', function () {
                var selectedOption = this.options[this.selectedIndex];
                var price = selectedOption.getAttribute('data-price') || selectedOption.getAttribute('data-amount') || '';
                var target = document.querySelector('input[name="amount"], input[name="total"], input[name="price"], #amount, #total, #price');
                if (target && price) {
                    target.value = price;
                    // Trigger input event for any listeners
                    var evt = new Event('input', { bubbles: true });
                    target.dispatchEvent(evt);
                }
            });
        });

        // Auto-fill for static price fields (data-price attribute on select option will fill amount)
        var priceFields = document.querySelectorAll('input[name="amount"], input[name="total"], input[name="price"]');
        priceFields.forEach(function (field) {
            if (!field.hasAttribute('data-auto-calc')) return;
            var triggerSelect = document.querySelector('select[name="property_id"], select[name="plot_id"], select[name="product_id"]');
            if (triggerSelect) {
                triggerSelect.addEventListener('change', function () {
                    var opt = this.options[this.selectedIndex];
                    var price = opt.getAttribute('data-price') || '';
                    if (price) field.value = price;
                });
            }
        });

        // =============================================
        // 3. CLIENT-SIDE FORM VALIDATION
        // =============================================

        var forms = document.querySelectorAll('form[data-validate="true"], form.needs-validation');
        forms.forEach(function (form) {
            form.addEventListener('submit', function (e) {
                var errors = [];
                var fields = form.querySelectorAll('input, select, textarea');

                fields.forEach(function (field) {
                    // Required field check
                    if (field.hasAttribute('required') && !field.disabled) {
                        var val = field.value.trim();
                        if (val === '' || val === '0' || val === field.querySelector('option:first-child')?.value) {
                            errors.push((field.getAttribute('data-label') || field.name || 'A field') + ' is required');
                            field.classList.add('is-invalid');
                            return;
                        }
                    }

                    // Email validation
                    if (field.type === 'email' && field.value.trim()) {
                        var emailVal = field.value.trim();
                        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailVal)) {
                            errors.push('Invalid email in ' + (field.getAttribute('data-label') || field.name));
                            field.classList.add('is-invalid');
                            return;
                        }
                    }

                    // Phone validation
                    if ((field.type === 'tel' || /phone/i.test(field.name)) && field.value.trim()) {
                        var phone = field.value.trim().replace(/[\s\-\(\)\+]/g, '');
                        // Indian mobile: 10 digits starting with 6-9, or +91 prefix
                        if (!/^(?:\+?91)?[6-9]\d{9}$/.test(phone)) {
                            errors.push('Invalid phone number in ' + (field.getAttribute('data-label') || field.name) + ' (10-digit Indian mobile)');
                            field.classList.add('is-invalid');
                        }
                    }

                    // Minimum length
                    var minLen = field.getAttribute('data-minlength');
                    if (minLen && field.value.trim().length < parseInt(minLen, 10)) {
                        errors.push((field.getAttribute('data-label') || field.name) + ' must be at least ' + minLen + ' characters');
                        field.classList.add('is-invalid');
                    }
                });

                // CSRF check - if token rendered as data attribute on form
                var csrfMeta = document.querySelector('meta[name="csrf-token"]');
                if (csrfMeta && !csrfMeta.getAttribute('content')) {
                    errors.push('Session expired. Please refresh the page.');
                }

                if (errors.length > 0) {
                    e.preventDefault();
                    showValidationErrors(form, errors);
                    // Focus first invalid field
                    var firstInvalid = form.querySelector('.is-invalid');
                    if (firstInvalid) firstInvalid.focus();
                }
            });

            // Clear validation state on input
            form.querySelectorAll('.is-invalid').forEach(function (el) {
                el.addEventListener('input', function () {
                    this.classList.remove('is-invalid');
                });
                el.addEventListener('change', function () {
                    this.classList.remove('is-invalid');
                });
            });
        });

        function showValidationErrors(form, errors) {
            // Remove existing error alert
            var existing = form.querySelector('.form-validation-alert');
            if (existing) existing.remove();

            var alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-danger alert-dismissible fade show form-validation-alert';
            alertDiv.setAttribute('role', 'alert');
            alertDiv.innerHTML = '<strong><i class="fas fa-exclamation-triangle me-2"></i>Please fix the following errors:</strong><ul class="mb-0 mt-2">' +
                errors.map(function (e) { return '<li>' + escapeHtml(e) + '</li>'; }).join('') +
                '</ul><button type="button" class="btn-close" data-bs-dismiss="alert"></button>';

            form.insertBefore(alertDiv, form.firstChild);
        }

        // =============================================
        // 4. PHONE VALIDATION (real-time)
        // =============================================

        var phoneFields = document.querySelectorAll('input[type="tel"], input[name*="phone"]');
        phoneFields.forEach(function (field) {
            field.addEventListener('blur', function () {
                var val = this.value.trim().replace(/[\s\-\(\)\+]/g, '');
                if (!val) return;
                if (/^(?:\+?91)?[6-9]\d{9}$/.test(val)) {
                    this.classList.add('is-valid');
                    this.classList.remove('is-invalid');
                    var feedback = getFeedbackEl(this);
                    if (feedback) {
                        feedback.textContent = 'Valid phone number';
                        feedback.className = 'valid-feedback';
                    }
                } else {
                    this.classList.add('is-invalid');
                    this.classList.remove('is-valid');
                    var feedback = getFeedbackEl(this);
                    if (feedback) {
                        feedback.textContent = 'Enter a valid 10-digit Indian mobile number';
                        feedback.className = 'invalid-feedback';
                    }
                }
            });
        });

        // =============================================
        // 5. EMAIL VALIDATION (real-time)
        // =============================================

        var emailFields = document.querySelectorAll('input[type="email"]');
        emailFields.forEach(function (field) {
            field.addEventListener('blur', function () {
                var val = this.value.trim();
                if (!val) return;
                if (/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) {
                    this.classList.add('is-valid');
                    this.classList.remove('is-invalid');
                } else {
                    this.classList.add('is-invalid');
                    this.classList.remove('is-valid');
                }
            });
        });

        // =============================================
        // 6. AUTO-DISMISS ALERTS (5 seconds)
        // =============================================

        var alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
        alerts.forEach(function (alert) {
            setTimeout(function () {
                if (alert.parentNode) {
                    var closeBtn = alert.querySelector('.btn-close');
                    if (closeBtn) {
                        // Use Bootstrap dismiss if available
                        var bsAlert = window.bootstrap && window.bootstrap.Alert;
                        if (bsAlert) {
                            var inst = bsAlert.getOrCreateInstance
                                ? bsAlert.getOrCreateInstance(alert)
                                : new bsAlert(alert);
                            inst.close();
                        } else {
                            alert.classList.remove('show');
                            setTimeout(function () {
                                if (alert.parentNode) alert.parentNode.removeChild(alert);
                            }, 300);
                        }
                    } else {
                        // No close button - just fade out
                        alert.style.transition = 'opacity 0.5s';
                        alert.style.opacity = '0';
                        setTimeout(function () {
                            if (alert.parentNode) alert.parentNode.removeChild(alert);
                        }, 500);
                    }
                }
            }, 5000);
        });

        // =============================================
        // 7. CONFIRM DIALOG HELPER
        // =============================================

        document.querySelectorAll('[data-confirm]').forEach(function (el) {
            el.addEventListener('click', function (e) {
                var message = this.getAttribute('data-confirm') || 'Are you sure?';
                if (!confirm(message)) {
                    e.preventDefault();
                    e.stopPropagation();
                    return false;
                }
            });
        });

        // =============================================
        // 8. CSRF TOKEN AUTO-ATTACH
        // =============================================

        var csrfMeta = document.querySelector('meta[name="csrf-token"]');
        var csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : null;
        if (csrfToken) {
            // Auto-attach CSRF to all AJAX fetch calls
            var originalFetch = window.fetch;
            window.fetch = function (url, opts) {
                opts = opts || {};
                opts.headers = opts.headers || {};
                if (opts.method && opts.method.toUpperCase() !== 'GET') {
                    opts.headers['X-CSRF-Token'] = csrfToken;
                }
                return originalFetch.call(window, url, opts);
            };
        }
    }

    // =============================================
    // UTILITY HELPERS
    // =============================================

    function escapeHtml(str) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }

    function getFeedbackEl(field) {
        var parent = field.parentNode;
        if (!parent) return null;
        var feedback = parent.querySelector('.invalid-feedback, .valid-feedback');
        if (feedback) return feedback;
        // Look for next sibling
        var next = field.nextElementSibling;
        if (next && (next.classList.contains('invalid-feedback') || next.classList.contains('valid-feedback'))) {
            return next;
        }
        return null;
    }

    // =============================================
    // INITIALIZATION
    // =============================================

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAdminFormEnhancer);
    } else {
        initAdminFormEnhancer();
    }
})();
