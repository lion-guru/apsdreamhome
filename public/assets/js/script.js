/**
 * Main JavaScript file for APS Dream Home
 * Handles common UI interactions and form validations
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize popovers
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // Handle form validations
    const forms = document.querySelectorAll('.needs-validation');
    Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });

    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });

    // Image preview for file uploads
    const imageInputs = document.querySelectorAll('.image-preview-input');
    imageInputs.forEach(input => {
        const preview = document.getElementById(input.dataset.preview);
        
        if (preview) {
            input.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    
                    reader.onload = function(e) {
                        preview.src = e.target.result;
                        preview.style.display = 'block';
                    }
                    
                    reader.readAsDataURL(file);
                }
            });
        }
    });

    // Mobile menu toggle
    const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
    const mobileMenu = document.getElementById('mobile-menu');
    
    if (mobileMenuToggle && mobileMenu) {
        mobileMenuToggle.addEventListener('click', function() {
            mobileMenu.classList.toggle('hidden');
        });
    }

    // Handle property image gallery
    const propertyThumbnails = document.querySelectorAll('.property-thumbnail');
    const mainPropertyImage = document.getElementById('main-property-image');
    
    if (propertyThumbnails.length && mainPropertyImage) {
        propertyThumbnails.forEach(thumbnail => {
            thumbnail.addEventListener('click', function() {
                // Update main image
                mainPropertyImage.src = this.src;
                mainPropertyImage.alt = this.alt;
                
                // Update active thumbnail
                document.querySelector('.property-thumbnail.active')?.classList.remove('active');
                this.classList.add('active');
            });
        });
    }

    // Handle booking form date validation
    const bookingForm = document.getElementById('booking-form');
    if (bookingForm) {
        const checkInInput = bookingForm.querySelector('input[name="check_in"]');
        const checkOutInput = bookingForm.querySelector('input[name="check_out"]');
        
        if (checkInInput && checkOutInput) {
            checkInInput.addEventListener('change', function() {
                checkOutInput.min = this.value;
                if (new Date(checkOutInput.value) < new Date(this.value)) {
                    checkOutInput.value = this.value;
                }
            });
        }
    }

    // Initialize Google Maps if needed
    if (typeof initMap === 'function') {
        initMap();
    }
});

// Helper function to format currency
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-IN', {
        style: 'currency',
        currency: 'INR',
        maximumFractionDigits: 0
    }).format(amount);
}

// Helper function to handle AJAX requests
async function fetchData(url, options = {}) {
    try {
        const response = await fetch(url, {
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                ...options.headers
            },
            ...options
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        return await response.json();
    } catch (error) {
        console.error('Error:', error);
        throw error;
    }
}
