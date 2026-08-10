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
      onCityChange: null,
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

    this.showLoading(stateSelect);

    try {
      const response = await fetch(
        `${this.apiBase}locations/states?country_id=${countryId || countrySelect?.value || 1}`
      );
      const data = await response.json();

      this.populateSelect(stateSelect, data, 'id', 'name', 'Select State');

      // Clear dependent dropdowns
      this.clearSelect(stateSelect.nextElementSibling);
    } catch (error) {
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
      /* Error handled silently */
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
      /* Error handled silently */
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
        /* Pincode lookup error handled silently */
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
        /* IFSC lookup error handled silently */
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
      const query = input.value.trim();
      if (query.length < 2) return;

      clearTimeout(this.debounceTimer);
      this.debounceTimer = setTimeout(() => {
        this.loadBankSuggestions(query, datalist, callbacks);
      }, 300);
    });
  }

  async loadBankSuggestions(query, datalist, callbacks) {
    const cacheKey = 'bank_search_' + query;
    if (this.cache[cacheKey]) {
      this.populateDatalist(datalist, this.cache[cacheKey]);
      return;
    }

    try {
      const response = await fetch(`${this.apiBase}banks/search?query=${encodeURIComponent(query)}`);
      const data = await response.json();

      if (data.success && data.data) {
        this.cache[cacheKey] = data.data;
        this.populateDatalist(datalist, data.data);
      }

      if (callbacks.onLoad) callbacks.onLoad(data);
    } catch (error) {
      /* Bank search error handled silently */
    }
  }

  populateDatalist(datalist, items) {
    datalist.innerHTML = '';
    items.forEach(item => {
      const option = document.createElement('option');
      option.value = item.id;
      option.textContent = `${item.name} - ${item.branch} (${item.ifsc})`;
      datalist.appendChild(option);
    });
  }

  // ==========================================
  // ACCOUNT VALIDATION
  // ==========================================

  /**
   * Validate bank account number using IFSC
   * Usage: new SmartFormAutocomplete().validateAccount('#accountNo', '#ifsc', {
   *     onValid: () => { show success },
   *     onInvalid: () => { show error }
   * });
   */
  validateAccount(accountNoEl, ifscEl, callbacks = {}) {
    const accountInput = document.querySelector(accountNoEl);
    const ifscInput = document.querySelector(ifscEl);
    if (!accountInput || !ifscInput) return;

    accountInput.addEventListener('blur', async () => {
      const accountNo = accountInput.value.trim();
      const ifsc = ifscInput.value.trim().toUpperCase();

      if (accountNo.length < 9 || ifsc.length < 11) return;

      accountInput.classList.add('loading');

      try {
        const response = await fetch(`${this.apiBase}banks/validate-account`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ account_no: accountNo, ifsc: ifsc }),
        });
        const data = await response.json();

        accountInput.classList.remove('loading');

        if (data.valid) {
          accountInput.classList.add('is-valid');
          accountInput.classList.remove('is-invalid');
          if (callbacks.onValid) callbacks.onValid(data);
        } else {
          accountInput.classList.add('is-invalid');
          accountInput.classList.remove('is-valid');
          if (callbacks.onInvalid) callbacks.onInvalid(data);
        }
      } catch (error) {
        accountInput.classList.remove('loading');
        /* Account validation error handled silently */
      }
    });
  }

  // ==========================================
  // UPI AUTO-FILL
  // ==========================================

  /**
   * Initialize UPI payment suggestion
   * Usage: new SmartFormAutocomplete().initUPIAutofill('#upiId', {
   *     onFound: (provider) => { fill VPA }
   * });
   */
  initUPIAutofill(upiEl, callbacks = {}) {
    const input = document.querySelector(upiEl);
    if (!input) return;

    input.addEventListener('input', () => {
      const upi = input.value.trim();
      if (upi.includes('@')) return;

      clearTimeout(this.debounceTimer);
      this.debounceTimer = setTimeout(() => {
        this.suggestUPI(upi, callbacks);
      }, 500);
    });
  }

  async suggestUPI(upi, callbacks) {
    try {
      const response = await fetch(`${this.apiBase}payments/upi-suggest?prefix=${encodeURIComponent(upi)}`);
      const data = await response.json();

      if (data.suggestions && data.suggestions.length > 0) {
        input.value = data.suggestions[0];
        if (callbacks.onSuggestion) callbacks.onSuggestion(data);
      }
    } catch (error) {
      /* UPI suggestion error handled silently */
    }
  }

  // ==========================================
  // HELPER METHODS
  // ==========================================

  showLoading(selectEl) {
    const options = selectEl.querySelectorAll('option');
    options.forEach(opt => (opt.disabled = true));
    selectEl.disabled = true;
  }

  hideLoading(selectEl) {
    const options = selectEl.querySelectorAll('option');
    options.forEach(opt => (opt.disabled = false));
    selectEl.disabled = false;
  }

  showError(selectEl, message) {
    const errorDiv = document.createElement('div');
    errorDiv.className = 'text-danger small mt-1';
    errorDiv.textContent = message;
    selectEl.parentNode.appendChild(errorDiv);
    setTimeout(() => errorDiv.remove(), 3000);
  }

  populateSelect(select, data, valueKey, labelKey, defaultLabel) {
    select.innerHTML = `<option value="">${defaultLabel}</option>`;
    if (data && Array.isArray(data)) {
      data.forEach(item => {
        const option = document.createElement('option');
        option.value = item[valueKey];
        option.textContent = item[labelKey];
        select.appendChild(option);
      });
    }
    this.hideLoading(select);
  }

  clearSelect(select) {
    if (select) {
      select.innerHTML = '<option value="">Select</option>';
    }
  }
}

// Initialize globally
if (typeof window.SmartFormAutocomplete === 'undefined') {
  window.SmartFormAutocomplete = SmartFormAutocomplete;
}
