/**
 * Visitor Tracking JavaScript
 * Tracks visitor behavior and captures incomplete registrations
 */

(function () {
  'use strict';

  // Get BASE_URL from PHP variable or construct it
  const BASE_URL = typeof window.BASE_URL !== 'undefined' ? window.BASE_URL : '/apsdreamhome';

  // Track page view
  function trackPageView() {
    const pageUrl = window.location.pathname;
    const pageTitle = document.title;

    fetch(BASE_URL + '/track/page-view', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: `page_url=${encodeURIComponent(pageUrl)}&page_title=${encodeURIComponent(pageTitle)}`,
    }).catch(err => console.error('Page view tracking failed:', err));
  }

  // Track incomplete registration
  function trackIncompleteRegistration(formData, step, totalSteps) {
    const data = {
      ...formData,
      step_completed: step,
      total_steps: totalSteps,
      registration_type: 'standard',
    };

    fetch(BASE_URL + '/track/incomplete-registration', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: new URLSearchParams(data),
    }).catch(err => console.error('Incomplete registration tracking failed:', err));
  }

  // Track visitor interest
  function trackInterest(interestType, additionalData = {}) {
    const data = {
      interest_type: interestType,
      ...additionalData,
    };

    fetch(BASE_URL + '/track/interest', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: new URLSearchParams(data),
    }).catch(err => console.error('Interest tracking failed:', err));
  }

  // Track property interest
  function trackPropertyInterest(propertyId, propertyType) {
    trackInterest('property_view', {
      property_id: propertyId,
      property_type: propertyType,
    });
  }

  // Track button clicks (for CTAs)
  function trackCTA(ctaType, additionalData = {}) {
    trackInterest('cta_click', {
      cta_type: ctaType,
      ...additionalData,
    });
  }

  // Auto-save registration form data
  function setupFormTracking(formSelector, formName) {
    const form = document.querySelector(formSelector);
    if (!form) return;

    let formData = {};
    let lastStep = 1;
    const totalSteps = 3; // Adjust based on form

    // Save data on input change
    form.addEventListener('input', function (e) {
      if (e.target.name) {
        formData[e.target.name] = e.target.value;
      }
    });

    // Save data on blur (when user leaves a field)
    form.addEventListener(
      'blur',
      function (e) {
        if (e.target.name) {
          trackIncompleteRegistration(formData, lastStep, totalSteps);
        }
      },
      true
    );

    // Track form submission
    form.addEventListener('submit', function (e) {
      formData = new FormData(form);
      const data = {};
      formData.forEach((value, key) => {
        data[key] = value;
      });
      trackIncompleteRegistration(data, totalSteps, totalSteps);
    });
  }

  // Track property interest
  function trackPropertyInterest(propertyId, propertyType) {
    trackInterest('property_view', {
      property_id: propertyId,
      property_type: propertyType,
    });
  }

  // Track button clicks (for CTAs)
  function trackCTA(ctaType, additionalData = {}) {
    trackInterest('cta_click', {
      cta_type: ctaType,
      ...additionalData,
    });
  }

  // Initialize tracking
  function init() {
    // Track initial page view
    trackPageView();

    // Track page changes (SPA)
    let lastUrl = location.href;
    new MutationObserver(() => {
      const url = location.href;
      if (url !== lastUrl) {
        lastUrl = url;
        trackPageView();
      }
    }).observe(document, { subtree: true, childList: true });

    // Setup form tracking for common forms
    setupFormTracking('#customer-register-form', 'customer_registration');
    setupFormTracking('#associate-register-form', 'associate_registration');
    setupFormTracking('#agent-register-form', 'agent_registration');
    setupFormTracking('#property-posting-form', 'property_posting');
    setupFormTracking('#inquiry-form', 'inquiry');

    // Track time on page
    let timeOnPage = 0;
    setInterval(() => {
      timeOnPage += 30;
      if (timeOnPage % 60 === 0 && timeOnPage >= 60) {
        // Update time on site every minute
        fetch(BASE_URL + '/track/page-view', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: `page_url=${encodeURIComponent(window.location.pathname)}&time_spent=${timeOnPage}`,
        }).catch(err => console.error('Time tracking failed:', err));
      }
    }, 30000); // Every 30 seconds

    // Track before page unload
    window.addEventListener('beforeunload', function () {
      // Save any pending form data
      const forms = document.querySelectorAll('form');
      forms.forEach(form => {
        if (form.id) {
          const formData = {};
          const inputs = form.querySelectorAll('input, select, textarea');
          inputs.forEach(input => {
            if (input.name && input.value) {
              formData[input.name] = input.value;
            }
          });
          if (Object.keys(formData).length > 0) {
            navigator.sendBeacon(BASE_URL + '/track/incomplete-registration', new URLSearchParams(formData));
          }
        }
      });
    });
  }

  // Make functions globally available
  window.VisitorTracking = {
    trackPageView,
    trackIncompleteRegistration,
    trackInterest,
    trackPropertyInterest,
    trackCTA,
  };

  // Initialize when DOM is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
