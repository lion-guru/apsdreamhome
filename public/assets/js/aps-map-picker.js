/**
 * APS Map Location Picker
 * Allows picking location from map and auto-filling address fields
 * Uses Leaflet + OpenStreetMap + Nominatim for reverse geocoding
 */
(function () {
  'use strict';

  const BASE_URL = window.APS_BASE_URL || '/apsdreamhome';

  // Default map center (Gorakhpur)
  const DEFAULT_CENTER = [26.7606, 83.3732];
  const DEFAULT_ZOOM = 13;

  // Field mappings for auto-fill
  const ADDRESS_FIELDS = {
    address_line1: ['[data-field="address_line1"]', '[name="address_line1"]', '[name="address"]', '[name="address1"]'],
    address_line2: ['[data-field="address_line2"]', '[name="address_line2"]', '[name="address2"]'],
    city: ['[data-field="city"]', '[name="city"]'],
    district: ['[data-field="district"]', '[name="district"]'],
    state: ['[data-field="state"]', '[name="state"]'],
    pincode: ['[data-field="pincode"]', '[name="pincode"]', '[name="pin_code"]', '[name="zipcode"]'],
    latitude: ['[data-field="latitude"]', '[name="latitude"]', '[name="lat"]'],
    longitude: ['[data-field="longitude"]', '[name="longitude"]', '[name="lng"]', '[name="lon"]'],
    landmark: ['[data-field="landmark"]', '[name="landmark"]'],
  };

  // Nominatim reverse geocoding endpoint
  const NOMINATIM_URL = 'https://nominatim.openstreetmap.org/reverse';

  let mapInstance = null;
  let marker = null;
  let currentModal = null;
  let debounceTimer = null;

  function init() {
    // Find all map picker triggers
    document.querySelectorAll('[data-map-picker]').forEach(btn => {
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        openMapPicker(this);
      });
    });

    // Find GPS location buttons
    document.querySelectorAll('[data-gps-location]').forEach(btn => {
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        getCurrentLocation(this);
      });
    });

    // Auto-initialize on forms with data-auto-map="true"
    document.querySelectorAll('form[data-auto-map="true"]').forEach(form => {
      // Add map picker button after address field
      enhanceForm(form);
    });
  }

  function enhanceForm(form) {
    const addressField = form.querySelector('[name="address"], [name="address_line1"], [data-field="address"]');
    if (addressField && !addressField.dataset.mapEnhanced) {
      addressField.dataset.mapEnhanced = 'true';

      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'btn btn-outline-secondary btn-sm ms-2';
      btn.innerHTML = '<i class="fas fa-map-marker-alt me-1"></i>Pick on Map';
      btn.dataset.mapPicker = 'true';
      btn.dataset.targetForm = '#' + form.id;

      addressField.parentNode.appendChild(btn);
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        openMapPicker(this);
      });
    }
  }

  function openMapPicker(trigger) {
    const formId = trigger.dataset.targetForm || findParentForm(trigger)?.id;
    const targetForm = formId ? document.getElementById(formId) : findParentForm(trigger);

    // Create modal
    const modalId = 'mapPickerModal_' + Date.now();
    const modalHtml = `
            <div class="modal fade" id="${modalId}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-xl modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title mb-0">
                                <i class="fas fa-map-marked-alt me-2"></i>Pick Location on Map
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-0" style="height: 70vh; min-height: 500px;">
                            <div id="${modalId}_map" style="width: 100%; height: 100%;"></div>
                        </div>
                        <div class="modal-footer">
                            <div class="me-auto">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Click on map to set location. Drag marker to adjust.
                                </small>
                            </div>
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-outline-secondary" data-action="current-location">
                                    <i class="fas fa-crosshairs me-1"></i>My Location
                                </button>
                                <button type="button" class="btn btn-outline-secondary" data-action="search">
                                    <i class="fas fa-search me-1"></i>Search Place
                                </button>
                                <button type="button" class="btn btn-primary" data-action="confirm">
                                    <i class="fas fa-check me-1"></i>Use This Location
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;

    document.body.insertAdjacentHTML('beforeend', modalHtml);

    const modalEl = document.getElementById(modalId);
    const modal = new bootstrap.Modal(modalEl);
    currentModal = modal;

    modalEl.addEventListener('shown.bs.modal', function () {
      initMap(modalId + '_map', targetForm);
    });

    modalEl.addEventListener('hidden.bs.modal', function () {
      if (mapInstance) {
        mapInstance.remove();
        mapInstance = null;
        marker = null;
      }
      modalEl.remove();
    });

    // Handle modal actions
    modalEl.querySelector('[data-action="confirm"]').addEventListener('click', function () {
      if (marker) {
        fillAddressFields(targetForm, marker.getLatLng());
        modal.hide();
      }
    });

    modalEl.querySelector('[data-action="current-location"]').addEventListener('click', function () {
      getCurrentLocation(this, modalId + '_map');
    });

    modalEl.querySelector('[data-action="search"]').addEventListener('click', function () {
      searchPlace(modalId + '_map');
    });

    modal.show();
  }

  function findParentForm(element) {
    let el = element;
    while (el && el.tagName !== 'FORM') {
      el = el.parentElement;
    }
    return el;
  }

  function initMap(mapDivId, targetForm) {
    const mapDiv = document.getElementById(mapDivId);
    if (!mapDiv) return;

    // Initialize map
    mapInstance = L.map(mapDivId, {
      zoomControl: true,
      attributionControl: true,
    }).setView(DEFAULT_CENTER, DEFAULT_ZOOM);

    // Add OpenStreetMap tiles
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
      maxZoom: 19,
    }).addTo(mapInstance);

    // Add click handler
    mapInstance.on('click', function (e) {
      setMarker(e.latlng);
    });

    // Try to get existing location from form
    const existingLat = targetForm?.querySelector('[name="latitude"], [name="lat"]')?.value;
    const existingLng = targetForm?.querySelector('[name="longitude"], [name="lng"], [name="lon"]')?.value;

    if (existingLat && existingLng) {
      const latlng = [parseFloat(existingLat), parseFloat(existingLng)];
      mapInstance.setView(latlng, 16);
      setMarker(latlng);
    } else {
      // Try to get current location
      getCurrentLocation(null, mapDivId, false);
    }
  }

  function setMarker(latlng) {
    if (marker) {
      marker.setLatLng(latlng);
    } else {
      marker = L.marker(latlng, { draggable: true }).addTo(mapInstance);
      marker.on('dragend', function (e) {
        updateMarkerInfo(e.target.getLatLng());
      });
    }
    mapInstance.setView(latlng, 16);
    updateMarkerInfo(latlng);
  }

  function updateMarkerInfo(latlng) {
    // Show coordinates in a tooltip
    if (marker) {
      marker
        .bindTooltip(`Lat: ${latlng.lat.toFixed(6)}<br>Lng: ${latlng.lng.toFixed(6)}`, {
          permanent: false,
          direction: 'top',
        })
        .openTooltip();
    }
  }

  function getCurrentLocation(trigger, mapDivId, showModal = true) {
    const btn = trigger || document.querySelector('[data-action="current-location"]');
    const originalHtml = btn?.innerHTML;

    if (btn) {
      btn.disabled = true;
      btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Getting Location...';
    }

    if (!navigator.geolocation) {
      showToast('Geolocation is not supported by your browser', 'error');
      if (btn) {
        btn.disabled = false;
        btn.innerHTML = originalHtml;
      }
      return;
    }

    navigator.geolocation.getCurrentPosition(
      function (position) {
        const latlng = [position.coords.latitude, position.coords.longitude];

        if (mapInstance) {
          mapInstance.setView(latlng, 16);
          setMarker(latlng);
        } else if (mapDivId) {
          // Initialize map with this location
          setTimeout(() => {
            if (mapInstance) {
              mapInstance.setView(latlng, 16);
              setMarker(latlng);
            }
          }, 100);
        }

        // Also fill form fields if we're in a modal
        const modal = document.querySelector('.modal.show');
        if (modal) {
          const form = modal.querySelector('[data-target-form]')
            ? document.getElementById(modal.querySelector('[data-target-form]').dataset.targetForm)
            : null;
          if (form) fillAddressFields(form, latlng);
        }

        showToast('Location acquired successfully!', 'success');
      },
      function (error) {
        let msg = 'Unable to get your location. ';
        switch (error.code) {
          case error.PERMISSION_DENIED:
            msg += 'Please enable location access in browser settings.';
            break;
          case error.POSITION_UNAVAILABLE:
            msg += 'Location information is unavailable.';
            break;
          case error.TIMEOUT:
            msg += 'Location request timed out.';
            break;
        }
        showToast(msg, 'error');
      },
      {
        enableHighAccuracy: true,
        timeout: 10000,
        maximumAge: 0,
      }
    );

    if (btn) {
      btn.disabled = false;
      btn.innerHTML = originalHtml;
    }
  }

  function searchPlace(mapDivId) {
    const query = prompt('Enter place name, address, or landmark:');
    if (!query) return;

    showToast('Searching...', 'info');

    fetch(
      `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=5&countrycodes=in`
    )
      .then(r => r.json())
      .then(results => {
        if (results.length === 0) {
          showToast('No results found', 'error');
          return;
        }

        if (results.length === 1) {
          selectPlace(results[0]);
        } else {
          // Show selection dialog
          const options = results.map((r, i) => `${i + 1}. ${r.display_name}`).join('\n');
          const choice = prompt(`${options}\n\nEnter number (1-${results.length}):`);
          const idx = parseInt(choice) - 1;
          if (idx >= 0 && idx < results.length) {
            selectPlace(results[idx]);
          }
        }
      })
      .catch(err => {
        showToast('Search failed: ' + err.message, 'error');
      });

    function selectPlace(place) {
      const latlng = [parseFloat(place.lat), parseFloat(place.lon)];
      if (mapInstance) {
        mapInstance.setView(latlng, 16);
        setMarker(latlng);
      }

      // Also fill form if we have address data
      const modal = document.querySelector('.modal.show');
      if (modal) {
        const form = modal.querySelector('[data-target-form]')
          ? document.getElementById(modal.querySelector('[data-target-form]').dataset.targetForm)
          : null;
        if (form && place.address) {
          fillAddressFromNominatim(form, place.address, latlng);
        }
      }

      showToast('Location found!', 'success');
    }
  }

  async function reverseGeocode(latlng) {
    try {
      const response = await fetch(
        `${NOMINATIM_URL}?format=json&lat=${latlng.lat}&lon=${latlng.lng}&zoom=18&addressdetails=1`
      );
      const data = await response.json();
      return data.address || {};
    } catch (err) {
      console.error('Reverse geocoding failed:', err);
      return {};
    }
  }

  async function fillAddressFields(form, latlng) {
    if (!form) return;

    showToast('Looking up address...', 'info');

    const address = await reverseGeocode(latlng);
    fillAddressFromNominatim(form, address, latlng);
    showToast('Address filled!', 'success');
  }

  function fillAddressFromNominatim(form, address, latlng) {
    // Map Nominatim fields to form fields
    const mappings = {
      house_number: 'address_line1',
      road: 'address_line1',
      neighbourhood: 'address_line2',
      suburb: 'address_line2',
      city: 'city',
      town: 'city',
      village: 'city',
      county: 'district',
      state_district: 'district',
      state: 'state',
      postcode: 'pincode',
      country_code: 'country',
    };

    // Fill lat/lng
    setFormValue(form, 'latitude', latlng.lat);
    setFormValue(form, 'longitude', latlng.lng);

    // Build address line 1
    let addressLine1 = '';
    if (address.house_number) addressLine1 += address.house_number + ' ';
    if (address.road) addressLine1 += address.road;
    if (!addressLine1 && address.neighbourhood) addressLine1 = address.neighbourhood;
    if (!addressLine1 && address.suburb) addressLine1 = address.suburb;
    if (addressLine1) setFormValue(form, 'address_line1', addressLine1);

    // Address line 2
    let addressLine2 = '';
    if (address.neighbourhood && !addressLine1.includes(address.neighbourhood)) addressLine2 = address.neighbourhood;
    if (address.suburb && !addressLine2.includes(address.suburb))
      addressLine2 += (addressLine2 ? ', ' : '') + address.suburb;
    if (addressLine2) setFormValue(form, 'address_line2', addressLine2);

    // City
    const city = address.city || address.town || address.village || '';
    if (city) setFormValue(form, 'city', city);

    // District
    const district = address.county || address.state_district || '';
    if (district) setFormValue(form, 'district', district);

    // State
    if (address.state) setFormValue(form, 'state', address.state);

    // Pincode
    if (address.postcode) setFormValue(form, 'pincode', address.postcode);

    // Country
    if (address.country_code) setFormValue(form, 'country', address.country_code.toUpperCase());
  }

  function setFormValue(form, fieldName, value) {
    // Try multiple possible field names
    const selectors = ADDRESS_FIELDS[fieldName] || [`[name="${fieldName}"]`];

    for (const selector of selectors) {
      const field = form.querySelector(selector);
      if (field && value) {
        field.value = value;
        field.dispatchEvent(new Event('change', { bubbles: true }));
        field.dispatchEvent(new Event('input', { bubbles: true }));
        break;
      }
    }
  }

  function showToast(message, type = 'info') {
    if (window.APS && window.APS.toast) {
      window.APS.toast(message, type);
    } else {
      // Fallback
      const toast = document.createElement('div');
      toast.className = `toast align-items-center text-white bg-${type === 'error' ? 'danger' : type === 'success' ? 'success' : 'primary'} border-0 position-fixed bottom-0 end-0 m-3`;
      toast.style.zIndex = '9999';
      toast.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">${message}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            `;
      document.body.appendChild(toast);
      const bsToast = new bootstrap.Toast(toast, { delay: 3000 });
      bsToast.show();
      toast.addEventListener('hidden.bs.toast', () => toast.remove());
    }
  }

  // Expose globally
  window.APSMapPicker = {
    open: openMapPicker,
    getCurrentLocation: getCurrentLocation,
    fillAddress: fillAddressFields,
  };

  // Initialize on DOM ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
