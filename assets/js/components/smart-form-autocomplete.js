/**
 * Smart Form Autocomplete Component
 * For location, pincode, and bank IFSC autofill
 */

class SmartFormAutocomplete {
    constructor() {
        this.apiBase = '/api/';
        this.debounceTimer = null;
        this.cache = {};
    }
    
    // ==========================================
    // LOCATION CASCADING DROPDOWNS
    // ==========================================
    
    /**
     * Initialize cascading location dropdowns
     * Usage: new SmartFormAutocomplete().initLocationCascade('#country', '#state', '#district', '#city');
     */
    initLocationCascade(countryEl, stateEl, districtEl, cityEl, options = {}) {
        const defaults = {
            countryId: 1, // India
            onStateChange: null,
            onDistrictChange: null,
            onCityChange: null
        };
        const opts = { ...defaults, ...options };
        
        this.setupSelect(countryEl, () => this.loadStates(countryEl, stateEl, opts.countryId));
        this.setupSelect(stateEl, () => this.loadDistricts(stateEl, districtEl, opts.onStateChange));
        this.setupSelect(districtEl, () => this.loadCities(districtEl, cityEl, opts.onDistrictChange));
        this.setupSelect(cityEl, opts.onCityChange);
        
        // Load states on init
        if (opts.loadOnInit !== false) {
            setTimeout(() => this.loadStates(countryEl, stateEl, opts.countryId), 100);
        }
    }
    
    setupSelect(selector, onChange) {
        const el = typeof selector === 'string' ? document.querySelector(selector) : selector;
        if (el && onChange) {
            el.addEventListener('change', onChange);
        }
    }
    
    async loadStates(countryEl, stateEl, countryId = 1) {
        const countrySelect = document.querySelector(countryEl);
        const stateSelect = document.querySelector(stateEl);
        if (!stateSelect) return;
        
        // Show loading
        this.showLoading(stateSelect);
        
        try {
            const response = await fetch(`${this.apiBase}locations/states?country_id=${countryId || countrySelect?.value || 1}`);
            const data = await response.json();
            
            this.populateSelect(stateSelect, data, 'id', 'name', 'Select State');
            
            // Clear dependent dropdowns
            this.clearSelect(stateSelect.nextElementSibling);
            
        } catch (error) {
            console.error('Error loading states:', error);
            this.showError(stateSelect, 'Failed to load states');
        }
    }
    
    async loadDistricts(stateEl, districtEl, callback) {
        const stateSelect = document.querySelector(stateEl);
        const districtSelect = document.querySelector(districtEl);
        if (!districtSelect || !stateSelect?.value) return;
        
        this.showLoading(districtSelect);
        
        try {
            const response = await fetch(`${this.apiBase}locations/districts?state_id=${stateSelect.value}`);
            const data = await response.json();
            
            this.populateSelect(districtSelect, data, 'id', 'name', 'Select District');
            
            // Clear cities
            this.clearSelect(districtSelect.nextElementSibling);
            
            if (callback) callback(data);
            
        } catch (error) {
            console.error('Error loading districts:', error);
        }
    }
    
    async loadCities(districtEl, cityEl, callback) {
        const districtSelect = document.querySelector(districtEl);
        const citySelect = document.querySelector(cityEl);
        if (!citySelect || !districtSelect?.value) return;
        
        this.showLoading(citySelect);
        
        try {
            const response = await fetch(`${this.apiBase}locations/cities?district_id=${districtSelect.value}`);
            const data = await response.json();
            
            this.populateSelect(citySelect, data, 'id', 'name', 'Select City');
            
            if (callback) callback(data);
            
        } catch (error) {
            console.error('Error loading cities:', error);
        }
    }
    
    // ==========================================
    // PINCODE AUTO-FILL
    // ==========================================
    
    /**
     * Initialize pincode auto-fill
     * Usage: new SmartFormAutocomplete().initPincodeAutofill('#pincode', {
     *     onFound: (data) => { fill city, district, state }
     * });
     */
    initPincodeAutofill(pincodeEl, callbacks = {}) {
        const input = document.querySelector(pincodeEl);
        if (!input) return;
        
        input.addEventListener('blur', async () => {
            const pincode = input.value.trim();
            if (pincode.length < 4) return;
            
            // Show loading state
            input.classList.add('loading');
            
            try {
                const response = await fetch(`${this.apiBase}locations/pincode/${pincode}`);
                const data = await response.json();
                
                input.classList.remove('loading');
                
                if (data.found) {
                    input.classList.add('is-valid');
                    input.classList.remove('is-invalid');
                    
                    if (callbacks.onFound) {
                        callbacks.onFound(data);
                    }
                } else {
                    input.classList.add('is-invalid');
                    input.classList.remove('is-valid');
                    
                    if (callbacks.onNotFound) {
                        callbacks.onNotFound(data);
                    }
                }
                
            } catch (error) {
                input.classList.remove('loading');
                console.error('Pincode lookup error:', error);
            }
        });
    }
    
    // ==========================================
    // BANK IFSC AUTO-FILL
    // ==========================================
    
    /**
     * Initialize bank IFSC auto-fill
     * Usage: new SmartFormAutocomplete().initBankIfsc('#ifsc', {
     *     onFound: (data) => { fill bank name, branch, address }
     * });
     */
    initBankIfsc(ifscEl, callbacks = {}) {
        const input = document.querySelector(ifscEl);
        if (!input) return;
        
        input.addEventListener('blur', async () => {
            const ifsc = input.value.trim().toUpperCase();
            if (ifsc.length < 8) return;
            
            input.classList.add('loading');
            
            try {
                const response = await fetch(`${this.apiBase}banks/ifsc/${ifsc}`);
                const data = await response.json();
                
                input.classList.remove('loading');
                
                if (data.found) {
                    input.classList.add('is-valid');
                    input.classList.remove('is-invalid');
                    
                    if (callbacks.onFound) {
                        callbacks.onFound(data);
                    }
                } else {
                    input.classList.add('is-invalid');
                    input.classList.remove('is-valid');
                    
                    if (callbacks.onNotFound) {
                        callbacks.onNotFound(data);
                    }
                }
                
            } catch (error) {
                input.classList.remove('loading');
                console.error('IFSC lookup error:', error);
            }
        });
    }
    
    /**
     * Initialize bank name search/autocomplete
     * Usage: new SmartFormAutocomplete().initBankSearch('#bankSearch', '#bankId', {
     *     onSelect: (bank) => { fill bank id }
     * });
     */
    initBankSearch(inputEl, hiddenIdEl, callbacks = {}) {
        const input = document.querySelector(inputEl);
        if (!input) return;
        
        // Create datalist for autocomplete
        const datalist = document.createElement('datalist');
        datalist.id = input.getAttribute('list') || 'bank-list';
        input.setAttribute('list', datalist.id);
        input.parentNode.appendChild(datalist);
        
        input.addEventListener('input', () => {
            this.debounce(async () => {
                const query = input.value.trim();
                if (query.length < 2) return;
                
                try {
                    const response = await fetch(`${this.apiBase}banks/search?q=${encodeURIComponent(query)}`);
                    const data = await response.json();
                    
                    datalist.innerHTML = '';
                    data.forEach(bank => {
                        const option = document.createElement('option');
                        option.value = bank.name;
                        option.dataset.id = bank.id;
                        option.dataset.short = bank.short_name;
                        datalist.appendChild(option);
                    });
                    
                } catch (error) {
                    console.error('Bank search error:', error);
                }
            }, 300);
        });
        
        input.addEventListener('change', () => {
            const selected = datalist.querySelector(`option[value="${input.value}"]`);
            if (selected && callbacks.onSelect) {
                callbacks.onSelect({
                    id: selected.dataset.id,
                    name: selected.value,
                    short_name: selected.dataset.short
                });
            }
        });
    }
    
    // ==========================================
    // UPI VALIDATION
    // ==========================================
    
    initUpiValidation(upiEl, callbacks = {}) {
        const input = document.querySelector(upiEl);
        if (!input) return;
        
        input.addEventListener('blur', async () => {
            const upi = input.value.trim();
            if (!upi || !upi.includes('@')) return;
            
            // Basic UPI format validation
            const upiRegex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+$/;
            if (!upiRegex.test(upi)) {
                input.classList.add('is-invalid');
                return;
            }
            
            // Extract UPI handle (e.g., 'paytm' from 'name@paytm')
            const handle = upi.split('@')[1].toLowerCase();
            
            // Known UPI apps
            const knownHandles = {
                'okhdfcbank': 'HDFC Bank',
                'okbizaxisbank': 'Axis Bank',
                'okicici': 'ICICI Bank',
                'okstatebank': 'SBI',
                'ybl': 'Yes Bank',
                'upi': 'Unified Payments',
                'paytm': 'Paytm',
                'phonepe': 'PhonePe',
                'gpay': 'Google Pay',
                'amazonpay': 'Amazon Pay',
                'mobikwik': 'MobiKwik'
            };
            
            const bankName = knownHandles[handle] || 'UPI';
            
            input.classList.add('is-valid');
            
            if (callbacks.onValid) {
                callbacks.onValid({
                    upi: upi,
                    handle: handle,
                    provider: bankName
                });
            }
        });
    }
    
    // ==========================================
    // ACCOUNT NUMBER VALIDATION
    // ==========================================
    
    initAccountValidation(accountEl, callbacks = {}) {
        const input = document.querySelector(accountEl);
        if (!input) return;
        
        input.addEventListener('blur', async () => {
            const account = input.value.trim();
            if (account.length < 8) return;
            
            try {
                const response = await fetch(`${this.apiBase}banks/validate-account?account=${account}`);
                const data = await response.json();
                
                if (data.valid) {
                    input.classList.add('is-valid');
                    input.classList.remove('is-invalid');
                } else {
                    input.classList.add('is-invalid');
                    input.classList.remove('is-valid');
                }
                
                if (callbacks.onValidate) {
                    callbacks.onValidate(data);
                }
                
            } catch (error) {
                console.error('Account validation error:', error);
            }
        });
    }
    
    // ==========================================
    // UTILITY METHODS
    // ==========================================
    
    populateSelect(select, data, valueKey, labelKey, placeholder = 'Select') {
        if (!select) return;
        
        select.innerHTML = `<option value="">${placeholder}</option>`;
        
        data.forEach(item => {
            const option = document.createElement('option');
            option.value = item[valueKey];
            option.textContent = item[labelKey];
            select.appendChild(option);
        });
    }
    
    clearSelect(element) {
        if (!element || element.tagName !== 'SELECT') return;
        element.innerHTML = '<option value="">Select</option>';
    }
    
    showLoading(element) {
        if (element) {
            element.classList.add('loading');
        }
    }
    
    showError(element, message) {
        if (element) {
            element.classList.add('is-invalid');
            const feedback = element.nextElementSibling;
            if (feedback?.classList.contains('invalid-feedback')) {
                feedback.textContent = message;
            }
        }
    }
    
    debounce(func, wait) {
        clearTimeout(this.debounceTimer);
        this.debounceTimer = setTimeout(func, wait);
    }
}

// Create global instance
const smartForm = new SmartFormAutocomplete();

// ==========================================
// QUICK INITIALIZATION HELPERS
// ==========================================

/**
 * Initialize location form with pincode auto-fill
 */
function initLocationForm(formId = 'locationForm') {
    const form = document.querySelector(`#${formId}`);
    if (!form) return;
    
    smartForm.initLocationCascade(
        '#country_id',
        '#state_id',
        '#district_id',
        '#city_id'
    );
    
    smartForm.initPincodeAutofill('#pincode', {
        onFound: (data) => {
            // Auto-fill location fields if available
            if (document.querySelector('#city_id') && data.city_id) {
                // Need to load cities first then set
                loadAndSetCity(data.city_id);
            }
        },
        onNotFound: (data) => {
            showNotification('Pincode not found. Please enter address manually.', 'warning');
        }
    });
}

/**
 * Initialize bank form with IFSC lookup
 */
function initBankForm(formId = 'bankForm') {
    const form = document.querySelector(`#${formId}`);
    if (!form) return;
    
    smartForm.initBankSearch('#bank_name', '#bank_id', {
        onSelect: (bank) => {
            document.querySelector('#bank_id').value = bank.id;
        }
    });
    
    smartForm.initBankIfsc('#ifsc_code', {
        onFound: (data) => {
            // Auto-fill fields
            if (document.querySelector('#bank_name')) {
                document.querySelector('#bank_name').value = data.bank_name;
            }
            if (document.querySelector('#branch_name')) {
                document.querySelector('#branch_name').value = data.branch;
            }
            if (document.querySelector('#bank_address')) {
                document.querySelector('#bank_address').value = `${data.address || ''}, ${data.city || ''}, ${data.state || ''}`;
            }
        },
        onNotFound: (data) => {
            showNotification('IFSC code not found. Please enter bank details manually.', 'warning');
        }
    });
    
    smartForm.initUpiValidation('#upi_id', {
        onValid: (data) => {
            console.log('UPI Provider:', data.provider);
        }
    });
    
    smartForm.initAccountValidation('#account_number');
}

/**
 * Load and set city after state/district are loaded
 */
async function loadAndSetCity(cityId) {
    try {
        const stateSelect = document.querySelector('#state_id');
        if (!stateSelect?.value) return;
        
        const response = await fetch(`/api/locations/cities?state_id=${stateSelect.value}`);
        const cities = await response.json();
        
        const citySelect = document.querySelector('#city_id');
        if (citySelect) {
            citySelect.innerHTML = '<option value="">Select City</option>';
            cities.forEach(city => {
                const option = new Option(city.name, city.id);
                if (city.id == cityId) option.selected = true;
                citySelect.add(option);
            });
        }
    } catch (error) {
        console.error('Error loading cities:', error);
    }
}

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = SmartFormAutocomplete;
}
