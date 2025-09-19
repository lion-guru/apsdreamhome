/**
 * APS Dream Home - Main JavaScript
 * This file contains all the interactive functionality for the website
 */

// Import styles
import '../css/main.css';

// Import modules
import { initPropertyFilters } from './filters';
import { initImageGallery } from './gallery';
import { initPropertySearch } from './search';

/**
 * Main application object
 */
const APSDreamHome = {
  // Configuration
  config: {
    // Add any configuration options here
  },

  /**
   * Initialize all components
   */
  init() {
    this.initTooltips();
    this.initBackToTop();
    this.initCounters();
    this.initLazyLoading();
    this.initEventListeners();
    this.initServiceWorker();
    this.initImageErrorHandling();
    this.initScheduleVisitModal();
    this.initPropertySearch();
  },

  /**
   * Initialize property search functionality
   */
  initPropertySearch() {
    // Import dynamically to avoid circular dependencies
    import('./search').then(({ initPropertySearch }) => {
      initPropertySearch();
    });
  },

  /**
   * Initialize tooltips using Bootstrap
   */
  initTooltips() {
    if (typeof bootstrap === 'undefined' || !bootstrap.Tooltip) {
      console.warn('Bootstrap Tooltip not available');
      return;
    }

    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(tooltipEl => new bootstrap.Tooltip(tooltipEl, {
      trigger: 'hover focus'
    }));
  },

  /**
   * Initialize back to top button
   */
  initBackToTop() {
    const backToTop = document.querySelector('.back-to-top');
    if (!backToTop) return;
    
    const toggleBackToTop = () => {
      backToTop.classList.toggle('active', window.scrollY > 300);
    };
    
    window.addEventListener('scroll', toggleBackToTop);
    toggleBackToTop();
    
    backToTop.addEventListener('click', (e) => {
      e.preventDefault();
      window.scrollTo({
        top: 0,
        behavior: 'smooth'
      });
    });
  },

  /**
   * Initialize counters animation
   */
  initCounters() {
    const counters = document.querySelectorAll('.counter');
    if (!counters.length) return;
    
    const options = {
      threshold: 0.5,
      rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          const counter = entry.target;
          const target = +counter.getAttribute('data-target');
          const count = +counter.innerText;
          const increment = target / 100;
          
          const updateCounter = () => {
            if (count < target) {
              counter.innerText = Math.ceil(count + increment);
              setTimeout(updateCounter, 20);
            } else {
              counter.innerText = target.toLocaleString();
            }
          };
          
          updateCounter();
          observer.unobserve(counter);
        }
      });
    }, options);
    
    counters.forEach(counter => observer.observe(counter));
  },

  /**
   * Initialize lazy loading for images
   */
  initLazyLoading() {
    if (!('IntersectionObserver' in window)) return;
    
    const lazyLoad = (target) => {
      const io = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            const img = entry.target;
            img.src = img.dataset.src;
            if (img.dataset.srcset) img.srcset = img.dataset.srcset;
            img.removeAttribute('data-src');
            img.removeAttribute('data-srcset');
            observer.unobserve(img);
          }
        });
      });

      io.observe(target);
    };

    const lazyImages = document.querySelectorAll('img[data-src]');
    lazyImages.forEach(lazyLoad);
  },

  /**
   * Initialize event listeners
   */
  initEventListeners() {
    // Use event delegation for better performance
    document.addEventListener('click', (e) => {
      // Loading buttons
      const button = e.target.closest('.btn-loading');
      if (button) {
        button.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Loading...';
        button.disabled = true;
      }

      // Favorites
      const favButton = e.target.closest('.add-to-favorites');
      if (favButton) {
        e.preventDefault();
        this.toggleFavorite(favButton);
      }
    });
  },

  /**
   * Toggle favorite status
   * @param {HTMLElement} button - The favorite button element
   */
  toggleFavorite(button) {
    const propertyId = button.dataset.propertyId;
    const icon = button.querySelector('i');
    
    if (icon.classList.contains('far')) {
      icon.classList.replace('far', 'fas');
      icon.classList.add('text-danger');
      this.showToast('Added to favorites!', 'success');
    } else {
      icon.classList.replace('fas', 'far');
      icon.classList.remove('text-danger');
      this.showToast('Removed from favorites', 'info');
    }
    
    // Here you would typically make an AJAX call to update favorites
    // this.updateFavorites(propertyId);
  },

  /**
   * Initialize service worker
   */
  initServiceWorker() {
    if ('serviceWorker' in navigator) {
      window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js')
          .then(registration => {
            console.log('ServiceWorker registration successful with scope: ', registration.scope);
          })
          .catch(error => {
            console.error('ServiceWorker registration failed: ', error);
          });
      });
    }
  },

  /**
   * Handle image loading errors
   */
  initImageErrorHandling() {
    document.addEventListener('error', (e) => {
      if (e.target.tagName.toLowerCase() === 'img') {
        e.target.src = 'data:image/svg+xml;charset=UTF-8,%3Csvg%20width%3D%22300%22%20height%3D%22200%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Crect%20width%3D%22300%22%20height%3D%22200%22%20fill%3D%22%23f8f9fa%22%2F%3E%3Ctext%20x%3D%22150%22%20y%3D%22100%22%20font-family%3D%22Arial%2C%20sans-serif%22%20font-size%3D%2214%22%20text-anchor%3D%22middle%22%20dominant-baseline%3D%22middle%22%20fill%3D%22%236c757d%22%3EImage%20not%20found%3C%2Ftext%3E%3C%2Fsvg%3E';
        e.target.alt = 'Image not available';
      }
    }, true);
  },

  /**
   * Initialize schedule visit modal
   */
  initScheduleVisitModal() {
    const modal = document.getElementById('scheduleVisitModal');
    if (!modal) return;

    // Set up modal show event
    modal.addEventListener('show.bs.modal', (event) => {
      const button = event.relatedTarget;
      const propertyId = button.getAttribute('data-property-id');
      const propertyTitle = button.getAttribute('data-property-title');
      
      const modalTitle = modal.querySelector('.modal-title');
      const propertyIdInput = modal.querySelector('input[name="property_id"]');
      
      if (modalTitle) modalTitle.textContent = `Schedule Visit - ${propertyTitle}`;
      if (propertyIdInput) propertyIdInput.value = propertyId;
      
      // Set minimum date to tomorrow
      const today = new Date();
      const tomorrow = new Date(today);
      tomorrow.setDate(tomorrow.getDate() + 1);
      const minDate = tomorrow.toISOString().split('T')[0];
      const dateInput = modal.querySelector('#visitDate');
      
      if (dateInput) {
        dateInput.min = minDate;
        dateInput.value = minDate;
      }
    });
    
    // Handle form submission
    const submitButton = document.getElementById('submitVisit');
    if (submitButton) {
      submitButton.addEventListener('click', () => this.handleVisitForm(modal));
    }
  },

  /**
   * Handle visit form submission
   * @param {HTMLElement} modal - The modal element
   */
  handleVisitForm(modal) {
    const form = document.getElementById('visitForm');
    if (!form) return;

    if (form.checkValidity()) {
      const formData = new FormData(form);
      console.log('Form submitted:', Object.fromEntries(formData));
      
      this.showToast('Visit scheduled successfully!', 'success');
      
      // Hide the modal
      if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        const modalInstance = bootstrap.Modal.getInstance(modal);
        if (modalInstance) modalInstance.hide();
      } else {
        modal.style.display = 'none';
        document.body.classList.remove('modal-open');
        const backdrop = document.querySelector('.modal-backdrop');
        if (backdrop) backdrop.remove();
      }
      
      // Reset the form
      form.reset();
    } else {
      form.reportValidity();
    }
  },

  /**
   * Show toast notification
   * @param {string} message - The message to display
   * @param {string} type - The type of toast (success, error, info, warning)
   */
  showToast(message, type = 'info') {
    let toastContainer = document.getElementById('toast-container');
    
    // Create toast container if it doesn't exist
    if (!toastContainer) {
      toastContainer = document.createElement('div');
      toastContainer.id = 'toast-container';
      toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
      document.body.appendChild(toastContainer);
    }
    
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type} border-0`;
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'assertive');
    toast.setAttribute('aria-atomic', 'true');
    
    toast.innerHTML = `
      <div class="d-flex">
        <div class="toast-body">${message}</div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
    `;
    
    toastContainer.appendChild(toast);
    
    if (typeof bootstrap !== 'undefined' && bootstrap.Toast) {
      const bsToast = new bootstrap.Toast(toast);
      bsToast.show();
    } else {
      // Fallback if Bootstrap Toast is not available
      toast.style.display = 'block';
      setTimeout(() => {
        toast.style.opacity = '1';
      }, 100);
    }
    
    // Remove toast after it's hidden
    const hideToast = () => {
      if (toast.parentNode) {
        toast.parentNode.removeChild(toast);
      }
    };
    
    toast.addEventListener('hidden.bs.toast', hideToast);
    
    // Auto-hide after 5 seconds
    setTimeout(hideToast, 5000);
  }
};

// Initialize the application when the DOM is fully loaded
document.addEventListener('DOMContentLoaded', () => {
  // Initialize the main application
  APSDreamHome.init();
  
  // Initialize property filters and gallery
  initPropertyFilters();
  initImageGallery();
  
  // Initialize AOS (Animate On Scroll)
  if (typeof AOS !== 'undefined') {
    AOS.init({
      duration: 800,
      easing: 'ease-in-out',
      once: true
    });
  }
});

// Make APSDreamHome available globally
window.APSDreamHome = APSDreamHome;
