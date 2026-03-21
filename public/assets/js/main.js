/**
 * APS Dream Home - Main JavaScript
 * Core functionality and initialization
 */

// ===== GLOBAL VARIABLES =====
window.BASE_URL = "http://localhost/apsdreamhome/public";
window.APS = {
  version: "1.0.0",
  debug: true,
  modules: {},
};

// ===== UTILITY FUNCTIONS =====
const apsUtils = {
  // Debounce function
  debounce: function (func, wait, immediate) {
    let timeout;
    return function executedFunction(...args) {
      const later = () => {
        timeout = null;
        if (!immediate) func(...args);
      };
      const callNow = immediate && !timeout;
      clearTimeout(timeout);
      timeout = setTimeout(later, wait);
      if (callNow) func(...args);
    };
  },

  // Throttle function
  throttle: function (func, limit) {
    let inThrottle;
    return function (...args) {
      if (!inThrottle) {
        func.apply(this, args);
        inThrottle = true;
        setTimeout(() => (inThrottle = false), limit);
      }
    };
  },

  // Format currency
  formatCurrency: function (amount, currency = "₹") {
    return currency + new Intl.NumberFormat("en-IN").format(amount);
  },

  // Format date
  formatDate: function (date, format = "DD MMM YYYY") {
    const d = new Date(date);
    const day = d.getDate();
    const month = d.toLocaleString("default", { month: "short" });
    const year = d.getFullYear();

    return format
      .replace("DD", day.toString().padStart(2, "0"))
      .replace("MMM", month)
      .replace("YYYY", year);
  },

  // Generate unique ID
  generateId: function (prefix = "aps") {
    return prefix + "_" + Math.random().toString(36).substr(2, 9);
  },

  // Check if element is in viewport
  isInViewport: function (element) {
    const rect = element.getBoundingClientRect();
    return (
      rect.top >= 0 &&
      rect.left >= 0 &&
      rect.bottom <=
        (window.innerHeight || document.documentElement.clientHeight) &&
      rect.right <= (window.innerWidth || document.documentElement.clientWidth)
    );
  },

  // Smooth scroll to element
  scrollToElement: function (element, offset = 0) {
    const elementPosition = element.offsetTop - offset;
    window.scrollTo({
      top: elementPosition,
      behavior: "smooth",
    });
  },

  // Show loading state
  showLoading: function (element, message = "Loading...") {
    const loadingHtml = `
            <div class="aps-loading">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">${message}</span>
                </div>
                <div class="loading-text mt-2">${message}</div>
            </div>
        `;
    element.innerHTML = loadingHtml;
    element.classList.add("loading");
  },

  // Hide loading state
  hideLoading: function (element, content = "") {
    element.classList.remove("loading");
    if (content) {
      element.innerHTML = content;
    }
  },

  // Show toast notification
  showToast: function (message, type = "info", duration = 5000) {
    const toastId = this.generateId("toast");
    const toastHtml = `
            <div id="${toastId}" class="toast toast-${type} position-fixed" 
                 style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
                <div class="toast-header">
                    <i class="fas fa-${this.getToastIcon(type)} me-2"></i>
                    <strong class="me-auto">${this.getToastTitle(type)}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">
                    ${message}
                </div>
            </div>
        `;

    document.body.insertAdjacentHTML("beforeend", toastHtml);

    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement);
    toast.show();

    // Auto-remove after duration
    setTimeout(() => {
      toastElement.remove();
    }, duration + 1000);
  },

  // Get toast icon based on type
  getToastIcon: function (type) {
    const icons = {
      success: "check-circle",
      error: "exclamation-triangle",
      warning: "exclamation-circle",
      info: "info-circle",
    };
    return icons[type] || icons.info;
  },

  // Get toast title based on type
  getToastTitle: function (type) {
    const titles = {
      success: "Success",
      error: "Error",
      warning: "Warning",
      info: "Info",
    };
    return titles[type] || titles.info;
  },

  // Validate email
  isValidEmail: function (email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
  },

  // Validate phone
  isValidPhone: function (phone) {
    const phoneRegex = /^[6-9]\d{9}$/;
    return phoneRegex.test(phone);
  },

  // Sanitize input
  sanitizeInput: function (input) {
    const div = document.createElement("div");
    div.textContent = input;
    return div.innerHTML;
  },

  // Copy to clipboard
  copyToClipboard: function (text) {
    if (navigator.clipboard) {
      return navigator.clipboard.writeText(text);
    } else {
      // Fallback for older browsers
      const textArea = document.createElement("textarea");
      textArea.value = text;
      textArea.style.position = "fixed";
      textArea.style.left = "-999999px";
      document.body.appendChild(textArea);
      textArea.focus();
      textArea.select();

      try {
        document.execCommand("copy");
        return Promise.resolve();
      } catch (err) {
        return Promise.reject(err);
      } finally {
        document.body.removeChild(textArea);
      }
    }
  },

  // Get device type
  getDeviceType: function () {
    const width = window.innerWidth;
    if (width < 576) return "mobile";
    if (width < 768) return "tablet";
    if (width < 992) return "tablet-lg";
    if (width < 1200) return "desktop";
    return "desktop-lg";
  },

  // Check if mobile device
  isMobile: function () {
    return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(
      navigator.userAgent,
    );
  },

  // Get browser info
  getBrowserInfo: function () {
    const ua = navigator.userAgent;
    let browserName = "Unknown";
    let browserVersion = "Unknown";

    if (ua.indexOf("Chrome") > -1) {
      browserName = "Chrome";
      browserVersion = ua.match(/Chrome\/(\d+)/)[1];
    } else if (ua.indexOf("Safari") > -1) {
      browserName = "Safari";
      browserVersion = ua.match(/Version\/(\d+)/)[1];
    } else if (ua.indexOf("Firefox") > -1) {
      browserName = "Firefox";
      browserVersion = ua.match(/Firefox\/(\d+)/)[1];
    } else if (ua.indexOf("Edge") > -1) {
      browserName = "Edge";
      browserVersion = ua.match(/Edge\/(\d+)/)[1];
    }

    return { name: browserName, version: browserVersion };
  },

  // Log to console with prefix
  log: function (...args) {
    if (window.APS.debug) {
      console.log("[APS]", ...args);
    }
  },

  // Error logging
  error: function (...args) {
    console.error("[APS ERROR]", ...args);
  },

  // Warning logging
  warn: function (...args) {
    console.warn("[APS WARNING]", ...args);
  },
};

// ===== INITIALIZATION =====
document.addEventListener("DOMContentLoaded", function () {
  apsUtils.log("APS Dream Home initializing...");

  // Initialize modules
  initializeModules();

  // Setup global event listeners
  setupGlobalEventListeners();

  // Initialize components
  initializeComponents();

  // Setup analytics
  setupAnalytics();

  apsUtils.log("APS Dream Home initialized successfully");
});

// ===== MODULE INITIALIZATION =====
function initializeModules() {
  // Initialize premium header
  if (typeof initPremiumHeader === "function") {
    initPremiumHeader();
    apsUtils.log("Premium header initialized");
  }

  // Initialize animations
  if (window.APSAnimations && typeof window.APSAnimations.init === "function") {
    window.APSAnimations.init();
    apsUtils.log("Animations initialized");
  }

  // Initialize property search
  if (window.propertySearch) {
    apsUtils.log("Property search initialized");
  }

  // Initialize contact form
  if (window.contactFormManager) {
    apsUtils.log("Contact form initialized");
  }
}

// ===== GLOBAL EVENT LISTENERS =====
function setupGlobalEventListeners() {
  // Smooth scroll for anchor links
  document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
    anchor.addEventListener("click", function (e) {
      e.preventDefault();
      const targetId = this.getAttribute("href");
      const targetElement = document.querySelector(targetId);

      if (targetElement) {
        const headerHeight =
          document.querySelector(".premium-header")?.offsetHeight || 0;
        apsUtils.scrollToElement(targetElement, headerHeight + 20);
      }
    });
  });

  // Back to top button
  const backToTopBtn = document.getElementById("backToTop");
  if (backToTopBtn) {
    backToTopBtn.addEventListener("click", function () {
      window.scrollTo({
        top: 0,
        behavior: "smooth",
      });
    });

    // Show/hide back to top button
    window.addEventListener(
      "scroll",
      apsUtils.throttle(function () {
        if (window.scrollY > 300) {
          backToTopBtn.classList.add("show");
        } else {
          backToTopBtn.classList.remove("show");
        }
      }, 100),
    );
  }

  // WhatsApp button
  const whatsappBtn = document.querySelector(".whatsapp-link");
  if (whatsappBtn) {
    whatsappBtn.addEventListener("click", function (e) {
      e.preventDefault();
      const message = encodeURIComponent(
        "Hello! I'm interested in APS Dream Home properties.",
      );
      window.open(`https://wa.me/919XXXXXXXXXX?text=${message}`, "_blank");
    });
  }

  // Keyboard navigation
  document.addEventListener("keydown", function (e) {
    // ESC key to close modals/menus
    if (e.key === "Escape") {
      closeAllModals();
    }
  });

  // Window resize
  window.addEventListener(
    "resize",
    apsUtils.debounce(function () {
      handleWindowResize();
    }, 250),
  );

  // Before unload
  window.addEventListener("beforeunload", function (e) {
    // Check if there are unsaved changes
    if (hasUnsavedChanges()) {
      e.preventDefault();
      e.returnValue = "";
    }
  });
}

// ===== COMPONENT INITIALIZATION =====
function initializeComponents() {
  // Initialize tooltips
  const tooltipTriggerList = [].slice.call(
    document.querySelectorAll('[data-bs-toggle="tooltip"]'),
  );
  tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });

  // Initialize popovers
  const popoverTriggerList = [].slice.call(
    document.querySelectorAll('[data-bs-toggle="popover"]'),
  );
  popoverTriggerList.map(function (popoverTriggerEl) {
    return new bootstrap.Popover(popoverTriggerEl);
  });

  // Initialize lazy loading
  initializeLazyLoading();

  // Initialize form validation
  initializeFormValidation();

  // Initialize image galleries
  initializeImageGalleries();
}

// ===== LAZY LOADING =====
function initializeLazyLoading() {
  const lazyImages = document.querySelectorAll("img[data-src]");

  if ("IntersectionObserver" in window) {
    const imageObserver = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          const img = entry.target;
          img.src = img.dataset.src;
          img.classList.add("loaded");
          imageObserver.unobserve(img);
        }
      });
    });

    lazyImages.forEach((img) => {
      imageObserver.observe(img);
    });
  } else {
    // Fallback for older browsers
    lazyImages.forEach((img) => {
      img.src = img.dataset.src;
      img.classList.add("loaded");
    });
  }
}

// ===== FORM VALIDATION =====
function initializeFormValidation() {
  const forms = document.querySelectorAll(".needs-validation");

  forms.forEach((form) => {
    form.addEventListener("submit", function (e) {
      if (!form.checkValidity()) {
        e.preventDefault();
        e.stopPropagation();
      }

      form.classList.add("was-validated");
    });
  });
}

// ===== IMAGE GALLERIES =====
function initializeImageGalleries() {
  const galleries = document.querySelectorAll(".image-gallery");

  galleries.forEach((gallery) => {
    gallery.addEventListener("click", function (e) {
      if (e.target.tagName === "IMG") {
        openImageModal(e.target.src, e.target.alt);
      }
    });
  });
}

// ===== IMAGE MODAL =====
function openImageModal(src, alt) {
  const modalHtml = `
        <div class="modal fade" id="imageModal" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">${alt}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center">
                        <img src="${src}" alt="${alt}" class="img-fluid rounded">
                    </div>
                </div>
            </div>
        </div>
    `;

  document.body.insertAdjacentHTML("beforeend", modalHtml);

  const modal = new bootstrap.Modal(document.getElementById("imageModal"));
  modal.show();

  // Remove modal from DOM after it's hidden
  document
    .getElementById("imageModal")
    .addEventListener("hidden.bs.modal", function () {
      this.remove();
    });
}

// ===== UTILITY FUNCTIONS =====
function closeAllModals() {
  const modals = document.querySelectorAll(".modal.show");
  modals.forEach((modal) => {
    const bootstrapModal = bootstrap.Modal.getInstance(modal);
    if (bootstrapModal) {
      bootstrapModal.hide();
    }
  });
}

function handleWindowResize() {
  // Update device type
  const deviceType = apsUtils.getDeviceType();
  document.body.setAttribute("data-device", deviceType);

  // Update header spacer
  const header = document.querySelector(".premium-header");
  const headerSpacer = document.querySelector(".header-spacer");

  if (header && headerSpacer) {
    headerSpacer.style.height = header.offsetHeight + "px";
  }
}

function hasUnsavedChanges() {
  // Check for unsaved form changes
  const forms = document.querySelectorAll('form[data-unsaved="true"]');
  return forms.length > 0;
}

// ===== ANALYTICS =====
function setupAnalytics() {
  // Track page view
  if (typeof gtag !== "undefined") {
    gtag("config", "GA_MEASUREMENT_ID", {
      page_title: document.title,
      page_location: window.location.href,
    });
  }

  // Track user interactions
  trackUserInteractions();
}

function trackUserInteractions() {
  // Track button clicks
  document.addEventListener("click", function (e) {
    const button = e.target.closest("button, .btn");
    if (button) {
      const buttonText = button.textContent.trim();
      const buttonClass = button.className;

      apsUtils.log("Button clicked:", buttonText, buttonClass);

      // Track in analytics
      if (typeof gtag !== "undefined") {
        gtag("event", "click", {
          event_category: "button",
          event_label: buttonText,
          value: 1,
        });
      }
    }
  });

  // Track form submissions
  document.addEventListener("submit", function (e) {
    const form = e.target;
    const formId = form.id || "unknown_form";

    // Skip admin login form - let it submit normally
    if (
      form.id === "adminLoginForm" ||
      form.classList.contains("admin-login-form")
    ) {
      apsUtils.log("Admin login form detected - allowing normal submission");
      return;
    }

    apsUtils.log("Form submitted:", formId);

    // Track in analytics
    if (typeof gtag !== "undefined") {
      gtag("event", "form_submit", {
        event_category: "form",
        event_label: formId,
        value: 1,
      });
    }
  });
}

// ===== COUNTER ANIMATION =====
function animateCounters() {
  const counters = document.querySelectorAll(".animate-counter");

  counters.forEach((counter) => {
    const target = parseInt(counter.getAttribute("data-target"));
    const duration = 2000; // 2 seconds
    const increment = target / (duration / 16); // 60fps
    let current = 0;

    const updateCounter = () => {
      current += increment;
      if (current < target) {
        counter.textContent = Math.ceil(current).toLocaleString();
        requestAnimationFrame(updateCounter);
      } else {
        counter.textContent = target.toLocaleString();
      }
    };

    // Start animation when element is in viewport
    const observer = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          updateCounter();
          observer.unobserve(entry.target);
        }
      });
    });

    observer.observe(counter);
  });
}

// Initialize counters when DOM is ready
document.addEventListener("DOMContentLoaded", function () {
  animateCounters();
});

// ===== EXPORT TO GLOBAL SCOPE =====
window.apsUtils = apsUtils;
window.APS.utils = apsUtils;

// Add to APS module registry
window.APS.modules.utils = apsUtils;
