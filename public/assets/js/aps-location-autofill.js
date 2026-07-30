/**
 * APS Location Autofill - Pincode & IFSC Lookup
 * Usage: Include this script and call APSLocation.init() on page load
 */
(function () {
  'use strict';

  const BASE_URL = window.APS_BASE_URL || '/apsdreamhome';

  // Cache for API responses
  const pincodeCache = new Map();
  const ifscCache = new Map();

  // Debounce timer
  let pincodeDebounce = null;
  let ifscDebounce = null;

  // Default selectors
  const SELECTORS = {
    pincode: '[data-autofill="pincode"]',
    ifsc: '[data-autofill="ifsc"]',
    gps: '[data-action="gps"]',
    map: '[data-action="map-picker"], [data-map-picker="true"]',
    ifscLookup: '[data-action="ifsc-lookup"]',
  };

  // Field mapping for pincode response
  const PINCODE_FIELDS = {
    city: '[data-field="city"], [name="city"], [name="district"]',
    district: '[data-field="district"], [name="district"]',
    state: '[data-field="state"], [name="state"]',
    state_code: '[data-field="state_code"]',
    latitude: '[data-field="latitude"], [name="latitude"]',
    longitude: '[data-field="longitude"], [name="longitude"]',
  };

  // Field mapping for IFSC response
  const IFSC_FIELDS = {
    bank_name: '[data-autofill="bank_name"], [name="bank_name"]',
    branch: '[data-autofill="branch"], [name="branch"], [name="branch_name"]',
    address: '[data-autofill="address"], [name="address"], [name="branch_address"]',
    city: '[data-autofill="city"], [name="city"]',
    district: '[data-autofill="district"], [name="district"]',
    state: '[data-autofill="state"], [name="state"]',
    pincode: '[data-autofill="pincode"], [name="pincode"], [name="pin_code"]',
  };

  function setFieldValue(selector, value) {
    const el = document.querySelector(selector);
    if (el && value) {
      el.value = value;
      // Trigger change event for any listeners
      el.dispatchEvent(new Event('change', { bubbles: true }));
      el.dispatchEvent(new Event('input', { bubbles: true }));
    }
  }

  function showTooltip(element, message, type = 'info') {
    // Remove existing tooltip
    const existing = element.parentNode.querySelector('.autofill-tooltip');
    if (existing) existing.remove();

    const tooltip = document.createElement('div');
    tooltip.className = `autofill-tooltip alert alert-${type} alert-sm mt-1`;
    tooltip.style.fontSize = '0.8rem';
    tooltip.style.maxWidth = '300px';
    tooltip.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'} me-1"></i>${message}`;

    element.parentNode.appendChild(tooltip);

    // Auto-remove after 5 seconds
    setTimeout(() => {
      if (tooltip.parentNode) tooltip.remove();
    }, 5000);
  }

  function clearTooltip(element) {
    const existing = element.parentNode.querySelector('.autofill-tooltip');
    if (existing) existing.remove();
  }

  function setLoading(element, loading) {
    if (loading) {
      element.classList.add('loading');
      const icon = document.createElement('span');
      icon.className = 'autofill-spinner ms-1';
      icon.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
      element.parentNode.appendChild(icon);
    } else {
      element.classList.remove('loading');
      const spinner = element.parentNode.querySelector('.autofill-spinner');
      if (spinner) spinner.remove();
    }
  }

  // ========== PINCODE AUTOCOMPLETE ==========
  async function fetchPincode(pincode) {
    if (pincodeCache.has(pincode)) {
      return pincodeCache.get(pincode);
    }

    try {
      const response = await fetch(`${BASE_URL}/api/locations/pincode/${pincode}`);
      const data = await response.json();
      pincodeCache.set(pincode, data);
      return data;
    } catch (error) {
      console.error('Pincode lookup failed:', error);
      return { found: false, error: true, message: 'Lookup failed' };
    }
  }

  function handlePincodeInput(element) {
    clearTimeout(pincodeDebounce);

    pincodeDebounce = setTimeout(async () => {
      const pincode = element.value.replace(/\D/g, '');

      if (pincode.length !== 6) {
        if (pincode.length > 0) {
          showTooltip(element, 'Enter 6-digit pincode', 'warning');
        }
        return;
      }

      setLoading(element, true);
      clearTooltip(element);

      const data = await fetchPincode(pincode);
      setLoading(element, false);

      if (data.found) {
        // Auto-fill fields
        Object.entries(PINCODE_FIELDS).forEach(([key, selector]) => {
          if (data[key]) setFieldValue(selector, data[key]);
        });

        // Also update state dropdown if it's a select
        if (data.state) {
          const stateSelect = document.querySelector('[name="state"], [data-field="state"]');
          if (stateSelect && stateSelect.tagName === 'SELECT') {
            const option = Array.from(stateSelect.options).find(
              opt =>
                opt.text.toLowerCase().includes(data.state.toLowerCase()) ||
                opt.value.toLowerCase().includes(data.state.toLowerCase())
            );
            if (option) {
              stateSelect.value = option.value;
              stateSelect.dispatchEvent(new Event('change', { bubbles: true }));
            }
          }
        }

        showTooltip(element, `Auto-filled: ${data.city}, ${data.state}`, 'success');
      } else {
        showTooltip(element, data.message || 'Pincode not found. Enter manually.', 'warning');
      }
    }, 500);
  }

  // ========== IFSC AUTOCOMPLETE ==========
  async function fetchIfsc(ifsc) {
    if (ifscCache.has(ifsc)) {
      return ifscCache.get(ifsc);
    }

    try {
      const response = await fetch(`${BASE_URL}/api/banks/ifsc/${ifsc}`);
      const data = await response.json();
      ifscCache.set(ifsc, data);
      return data;
    } catch (error) {
      console.error('IFSC lookup failed:', error);
      return { found: false, error: true, message: 'Lookup failed' };
    }
  }

  function handleIfscInput(element) {
    clearTimeout(ifscDebounce);

    ifscDebounce = setTimeout(async () => {
      const ifsc = element.value.trim().toUpperCase();

      if (ifsc.length < 8) {
        if (ifsc.length > 0) {
          showTooltip(element, 'Enter 11-character IFSC code', 'warning');
        }
        return;
      }

      // Validate IFSC format (4 letters + 7 alphanumeric)
      if (!/^[A-Z]{4}[A-Z0-9]{7}$/.test(ifsc)) {
        showTooltip(element, 'Invalid IFSC format (e.g., SBIN0001234)', 'error');
        return;
      }

      setLoading(element, true);
      clearTooltip(element);

      const data = await fetchIfsc(ifsc);
      setLoading(element, false);

      if (data.found) {
        Object.entries(IFSC_FIELDS).forEach(([key, selector]) => {
          if (data[key]) setFieldValue(selector, data[key]);
        });

        showTooltip(element, `Auto-filled: ${data.bank_name} - ${data.branch}`, 'success');
      } else {
        showTooltip(element, data.message || 'IFSC not found. Enter manually.', 'warning');

        // Suggest bank if available
        if (data.suggested_bank) {
          const bankField = document.querySelector('[data-autofill="bank_name"], [name="bank_name"]');
          if (bankField && !bankField.value) {
            bankField.value = data.suggested_bank;
            bankField.dispatchEvent(new Event('change', { bubbles: true }));
            showTooltip(element, `Suggested bank: ${data.suggested_bank}`, 'info');
          }
        }
      }
    }, 500);
  }

  function handleIfscLookup(button) {
    const ifscField =
      button.closest('.input-group')?.querySelector('[data-autofill="ifsc"], [name="ifsc_code"]') ||
      document.querySelector('[data-autofill="ifsc"], [name="ifsc_code"]');

    if (ifscField) {
      ifscField.focus();
      handleIfscInput(ifscField);
    }
  }

  // ========== GPS LOCATION ==========
  function handleGpsButton(button) {
    if (!navigator.geolocation) {
      showTooltip(button, 'Geolocation not supported in this browser', 'error');
      return;
    }

    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Getting Location...';

    navigator.geolocation.getCurrentPosition(
      async position => {
        const { latitude, longitude } = position.coords;

        // Fill lat/long fields
        setFieldValue('[name="latitude"], [data-field="latitude"]', latitude.toFixed(8));
        setFieldValue('[name="longitude"], [data-field="longitude"]', longitude.toFixed(8));

        // Reverse geocode to get address
        try {
          const response = await fetch(
            `https://nominatim.openstreetmap.org/reverse?format=json&lat=${latitude}&lon=${longitude}&zoom=18&addressdetails=1`
          );
          const data = await response.json();

          if (data.address) {
            const addr = data.address;

            // Fill address fields
            if (addr.road) setFieldValue('[name="address"], [data-field="address"]', addr.road);
            if (addr.suburb)
              setFieldValue(
                '[name="address"], [data-field="address"]',
                (addr.road ? addr.road + ', ' : '') + addr.suburb
              );
            if (addr.city) setFieldValue('[name="city"], [data-field="city"]', addr.city);
            else if (addr.town) setFieldValue('[name="city"], [data-field="city"]', addr.town);
            else if (addr.village) setFieldValue('[name="city"], [data-field="city"]', addr.village);
            if (addr.state) setFieldValue('[name="state"], [data-field="state"]', addr.state);
            if (addr.postcode) {
              setFieldValue('[name="pincode"], [data-field="pincode"]', addr.postcode);
              // Trigger pincode lookup
              const pincodeField = document.querySelector('[data-autofill-pincode]');
              if (pincodeField) handlePincodeInput(pincodeField);
            }
          }

          showTooltip(button, 'Location detected and address filled!', 'success');
        } catch (error) {
          console.error('Reverse geocoding failed:', error);
          showTooltip(button, 'Location detected but address lookup failed', 'warning');
        }

        button.disabled = false;
        button.innerHTML = '<i class="fas fa-location-crosshairs me-1"></i>Use My Location';
      },
      error => {
        let msg = 'Failed to get location';
        switch (error.code) {
          case error.PERMISSION_DENIED:
            msg = 'Location permission denied';
            break;
          case error.POSITION_UNAVAILABLE:
            msg = 'Location unavailable';
            break;
          case error.TIMEOUT:
            msg = 'Location request timed out';
            break;
        }
        showTooltip(button, msg, 'error');
        button.disabled = false;
        button.innerHTML = '<i class="fas fa-location-crosshairs me-1"></i>Use My Location';
      },
      { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
    );
  }

  // ========== MAP PIN LOCATION ==========
  let mapInstance = null;
  let mapMarker = null;

  function openMapModal(inputElement) {
    // Create modal
    const modalHtml = `
            <div class="modal fade" id="mapPickerModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-xl modal-fullscreen-md-down">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title"><i class="fas fa-map-marker-alt me-2"></i>Pick Location on Map</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-0">
                            <div id="mapPicker" style="height: 60vh;"></div>
                        </div>
                        <div class="modal-footer">
                            <div class="me-auto">
                                <small class="text-muted">Click on map to select location. Drag marker to adjust.</small>
                            </div>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-primary" id="confirmMapLocation">
                                <i class="fas fa-check me-1"></i>Confirm Location
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

    // Remove existing modal
    const existing = document.getElementById('mapPickerModal');
    if (existing) existing.remove();

    document.body.insertAdjacentHTML('beforeend', modalHtml);

    const modal = new bootstrap.Modal(document.getElementById('mapPickerModal'));
    modal.show();

    // Initialize map after modal shows
    document.getElementById('mapPickerModal').addEventListener('shown.bs.modal', () => {
      initMapPicker(inputElement);
    });

    // Handle confirm
    document.getElementById('confirmMapLocation').addEventListener('click', () => {
      if (mapMarker) {
        const pos = mapMarker.getLatLng();
        setFieldValue('[name="latitude"], [data-field="latitude"]', pos.lat.toFixed(8));
        setFieldValue('[name="longitude"], [data-field="longitude"]', pos.lng.toFixed(8));
        showTooltip(inputElement, `Location pinned: ${pos.lat.toFixed(6)}, ${pos.lng.toFixed(6)}`, 'success');
        modal.hide();
      }
    });
  }

  function initMapPicker(inputElement) {
    if (mapInstance) {
      mapInstance.remove();
    }

    mapInstance = L.map('mapPicker').setView([26.8467, 80.9462], 10); // Default to Lucknow

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '&copy; OpenStreetMap contributors',
      maxZoom: 19,
    }).addTo(mapInstance);

    // Try to get current location
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(
        position => {
          const { latitude, longitude } = position.coords;
          mapInstance.setView([latitude, longitude], 15);
          addMarker(latitude, longitude);
        },
        () => {
          // Default location already set
        },
        { enableHighAccuracy: true, timeout: 10000 }
      );
    }

    // Add marker on click
    mapInstance.on('click', e => {
      addMarker(e.latlng.lat, e.latlng.lng);
    });

    function addMarker(lat, lng) {
      if (mapMarker) {
        mapMarker.setLatLng([lat, lng]);
      } else {
        mapMarker = L.marker([lat, lng], { draggable: true }).addTo(mapInstance);
        mapMarker.on('dragend', e => {
          const pos = e.target.getLatLng();
          mapMarker = e.target;
        });
      }
      mapInstance.panTo([lat, lng]);
    }
  }

  // ========== INITIALIZATION ==========
  function init() {
    // Pincode autofill
    document.querySelectorAll(SELECTORS.pincode).forEach(el => {
      el.addEventListener('input', () => handlePincodeInput(el));
      el.addEventListener('blur', () => handlePincodeInput(el));
      el.setAttribute('maxlength', '6');
      el.setAttribute('placeholder', 'Enter 6-digit pincode');
    });

    // IFSC autofill
    document.querySelectorAll(SELECTORS.ifsc).forEach(el => {
      el.addEventListener('input', () => handleIfscInput(el));
      el.addEventListener('blur', () => handleIfscInput(el));
      el.setAttribute('maxlength', '11');
      el.setAttribute('placeholder', 'e.g., SBIN0001234');
      el.style.textTransform = 'uppercase';
    });

    // GPS button
    document.querySelectorAll(SELECTORS.gps).forEach(btn => {
      btn.addEventListener('click', () => handleGpsButton(btn));
    });

    // IFSC lookup button
    document.querySelectorAll(SELECTORS.ifscLookup).forEach(btn => {
      btn.addEventListener('click', () => handleIfscLookup(btn));
    });
  }

  // Auto-init on DOM ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

  // Export for manual init
  window.APSLocation = {
    init,
    fetchPincode,
    fetchIfsc,
    handleGpsButton,
    openMapModal,
    clearCache: () => {
      pincodeCache.clear();
      ifscCache.clear();
    },
  };
})();
