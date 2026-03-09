/**
 * APS Dream Home - Contact Form JavaScript
 * Advanced contact form functionality with validation
 */

// ===== CONTACT FORM MANAGER =====
class ContactFormManager {
    constructor() {
        this.contactForm = document.getElementById('contactForm');
        this.submitButton = null;
        this.formFields = {};
        this.validationRules = {};
        this.isSubmitting = false;
        
        this.init();
    }
    
    init() {
        this.setupValidationRules();
        this.setupEventListeners();
        this.initFormFields();
        this.setupAutoComplete();
    }
    
    setupValidationRules() {
        this.validationRules = {
            name: {
                required: true,
                minLength: 2,
                maxLength: 100,
                pattern: /^[a-zA-Z\s]+$/,
                message: 'Please enter a valid name (letters only, 2-100 characters)'
            },
            email: {
                required: true,
                pattern: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
                message: 'Please enter a valid email address'
            },
            phone: {
                required: true,
                pattern: /^[6-9]\d{9}$/,
                message: 'Please enter a valid 10-digit phone number starting with 6-9'
            },
            service: {
                required: true,
                message: 'Please select a service type'
            },
            message: {
                required: true,
                minLength: 10,
                maxLength: 1000,
                message: 'Message must be between 10 and 1000 characters'
            }
        };
    }
    
    setupEventListeners() {
        if (!this.contactForm) return;
        
        // Form submission
        this.contactForm.addEventListener('submit', (e) => {
            e.preventDefault();
            this.handleSubmit();
        });
        
        // Real-time validation
        const inputs = this.contactForm.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            input.addEventListener('blur', () => {
                this.validateField(input);
            });
            
            input.addEventListener('input', () => {
                this.clearFieldError(input);
            });
        });
        
        // Submit button
        this.submitButton = this.contactForm.querySelector('button[type="submit"]');
    }
    
    initFormFields() {
        if (!this.contactForm) return;
        
        const inputs = this.contactForm.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            this.formFields[input.name] = input;
        });
    }
    
    setupAutoComplete() {
        // Phone number formatting
        const phoneField = this.formFields.phone;
        if (phoneField) {
            phoneField.addEventListener('input', (e) => {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length > 10) {
                    value = value.slice(0, 10);
                }
                e.target.value = value;
            });
        }
        
        // Name field - capitalize first letter
        const nameField = this.formFields.name;
        if (nameField) {
            nameField.addEventListener('input', (e) => {
                const words = e.target.value.split(' ');
                const capitalizedWords = words.map(word => 
                    word.charAt(0).toUpperCase() + word.slice(1).toLowerCase()
                );
                e.target.value = capitalizedWords.join(' ');
            });
        }
    }
    
    validateField(field) {
        const fieldName = field.name;
        const value = field.value.trim();
        const rules = this.validationRules[fieldName];
        
        if (!rules) return true;
        
        // Clear previous error
        this.clearFieldError(field);
        
        // Required validation
        if (rules.required && !value) {
            this.showFieldError(field, 'This field is required');
            return false;
        }
        
        if (!value) return true; // Skip other validations if field is empty and not required
        
        // Pattern validation
        if (rules.pattern && !rules.pattern.test(value)) {
            this.showFieldError(field, rules.message);
            return false;
        }
        
        // Length validation
        if (rules.minLength && value.length < rules.minLength) {
            this.showFieldError(field, rules.message);
            return false;
        }
        
        if (rules.maxLength && value.length > rules.maxLength) {
            this.showFieldError(field, rules.message);
            return false;
        }
        
        // Custom validation for specific fields
        if (fieldName === 'email') {
            if (!this.isValidEmail(value)) {
                this.showFieldError(field, rules.message);
                return false;
            }
        }
        
        if (fieldName === 'phone') {
            if (!this.isValidPhone(value)) {
                this.showFieldError(field, rules.message);
                return false;
            }
        }
        
        // Field is valid
        field.classList.add('is-valid');
        return true;
    }
    
    isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
    isValidPhone(phone) {
        const phoneRegex = /^[6-9]\d{9}$/;
        return phoneRegex.test(phone);
    }
    
    showFieldError(field, message) {
        field.classList.add('is-invalid');
        field.classList.remove('is-valid');
        
        // Remove existing error
        const existingError = field.parentNode.querySelector('.field-error');
        if (existingError) {
            existingError.remove();
        }
        
        // Create error element
        const errorElement = document.createElement('div');
        errorElement.className = 'field-error text-danger small mt-1';
        errorElement.textContent = message;
        
        field.parentNode.appendChild(errorElement);
        
        // Shake animation
        field.style.animation = 'shake 0.5s';
        setTimeout(() => {
            field.style.animation = '';
        }, 500);
    }
    
    clearFieldError(field) {
        field.classList.remove('is-invalid', 'is-valid');
        const errorElement = field.parentNode.querySelector('.field-error');
        if (errorElement) {
            errorElement.remove();
        }
    }
    
    async handleSubmit() {
        if (this.isSubmitting) return;
        
        // Validate all fields
        let isValid = true;
        Object.values(this.formFields).forEach(field => {
            if (!this.validateField(field)) {
                isValid = false;
            }
        });
        
        if (!isValid) {
            this.showErrorMessage('Please fix the errors in the form before submitting.');
            return;
        }
        
        // Get form data
        const formData = new FormData(this.contactForm);
        const data = {};
        formData.forEach((value, key) => {
            data[key] = value.trim();
        });
        
        // Add additional data
        data.timestamp = new Date().toISOString();
        data.userAgent = navigator.userAgent;
        data.page = window.location.href;
        
        // Show loading state
        this.setSubmittingState(true);
        
        try {
            // Submit form
            const response = await fetch(`${BASE_URL}/api/contact/submit`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(data)
            });
            
            if (!response.ok) {
                throw new Error('Form submission failed');
            }
            
            const result = await response.json();
            
            if (result.success) {
                this.handleSuccess(result);
            } else {
                this.handleSubmissionError(result.message || 'Form submission failed');
            }
            
        } catch (error) {
            console.error('Contact form error:', error);
            this.handleSubmissionError('Unable to submit form. Please try again later.');
        } finally {
            this.setSubmittingState(false);
        }
    }
    
    setSubmittingState(isSubmitting) {
        this.isSubmitting = isSubmitting;
        
        if (this.submitButton) {
            if (isSubmitting) {
                this.submitButton.disabled = true;
                this.submitButton.innerHTML = `
                    <span class="spinner-border spinner-border-sm me-2" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </span>
                    Submitting...
                `;
            } else {
                this.submitButton.disabled = false;
                this.submitButton.innerHTML = `
                    <i class="fas fa-paper-plane me-2"></i>Send Message
                `;
            }
        }
        
        // Disable form fields during submission
        Object.values(this.formFields).forEach(field => {
            field.disabled = isSubmitting;
        });
    }
    
    handleSuccess(result) {
        // Show success message
        this.showSuccessMessage(result.message || 'Your message has been sent successfully!');
        
        // Reset form
        this.contactForm.reset();
        Object.values(this.formFields).forEach(field => {
            this.clearFieldError(field);
        });
        
        // Track conversion
        this.trackConversion(result);
        
        // Show follow-up options
        this.showFollowUpOptions();
    }
    
    handleSubmissionError(message) {
        this.showErrorMessage(message);
        
        // Re-enable form fields
        Object.values(this.formFields).forEach(field => {
            field.disabled = false;
        });
    }
    
    showSuccessMessage(message) {
        // Create success alert
        const alertHtml = `
            <div class="alert alert-success alert-dismissible fade show position-fixed" 
                 style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
                <div class="d-flex align-items-center">
                    <i class="fas fa-check-circle me-2"></i>
                    <div>
                        <strong>Success!</strong>
                        <div class="small">${message}</div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', alertHtml);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            const alert = document.querySelector('.alert-success');
            if (alert) {
                alert.remove();
            }
        }, 5000);
    }
    
    showErrorMessage(message) {
        // Create error alert
        const alertHtml = `
            <div class="alert alert-danger alert-dismissible fade show position-fixed" 
                 style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <div>
                        <strong>Error!</strong>
                        <div class="small">${message}</div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', alertHtml);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            const alert = document.querySelector('.alert-danger');
            if (alert) {
                alert.remove();
            }
        }, 5000);
    }
    
    showFollowUpOptions() {
        const modalHtml = `
            <div class="modal fade" id="followUpModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Thank you for contacting us!</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p>We'll get back to you within 24 hours. In the meantime, you can:</p>
                            <div class="d-grid gap-2">
                                <a href="#properties" class="btn btn-outline-primary">
                                    <i class="fas fa-search me-2"></i>Browse Properties
                                </a>
                                <a href="#services" class="btn btn-outline-secondary">
                                    <i class="fas fa-concierge-bell me-2"></i>Learn About Our Services
                                </a>
                                <a href="tel:+919XXXXXXXXXX" class="btn btn-success">
                                    <i class="fas fa-phone me-2"></i>Call Us Now
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('followUpModal'));
        modal.show();
    }
    
    trackConversion(result) {
        // Google Analytics 4 event
        if (typeof gtag !== 'undefined') {
            gtag('event', 'form_submit', {
                'event_category': 'contact_form',
                'event_label': 'contact_submission',
                'value': 1
            });
        }
        
        // Facebook Pixel event
        if (typeof fbq !== 'undefined') {
            fbq('track', 'Lead', {
                content_name: 'Contact Form Submission'
            });
        }
        
        // Custom tracking
        console.log('Contact form conversion tracked:', result);
    }
}

// ===== UTILITY FUNCTIONS =====
function addShakeAnimation() {
    const style = document.createElement('style');
    style.textContent = `
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
    `;
    document.head.appendChild(style);
}

// ===== INITIALIZATION =====
let contactFormManager;

document.addEventListener('DOMContentLoaded', function() {
    addShakeAnimation();
    contactFormManager = new ContactFormManager();
});

// Export for external use
window.ContactFormManager = ContactFormManager;
window.contactFormManager = contactFormManager;
