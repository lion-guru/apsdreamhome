/**
 * APS Dream Home - Lead Capture System
 */

document.addEventListener('DOMContentLoaded', function() {
    // Contact form handling
    const contactForm = document.querySelector('#contactForm');
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = {};
            formData.forEach((value, key) => {
                data[key] = value;
            });
            
            // Validate form
            const errors = apsUtils.validateForm(this);
            if (errors.length > 0) {
                apsUtils.showNotification(errors.join(', '), 'error');
                return;
            }
            
            // Show loading
            apsUtils.showLoading();
            
            // Submit contact form
            apsUtils.ajax('/api/contact/submit', {
                method: 'POST',
                body: JSON.stringify(data)
            })
            .then(function(response) {
                apsUtils.hideLoading();
                if (response.success) {
                    apsUtils.showNotification('Message sent successfully! We will contact you soon.', 'success');
                    contactForm.reset();
                } else {
                    apsUtils.showNotification(response.message || 'Failed to send message', 'error');
                }
            })
            .catch(function(error) {
                apsUtils.hideLoading();
                apsUtils.showNotification('Network error. Please try again.', 'error');
            });
        });
    }
    
    // Property inquiry form
    const propertyInquiryForm = document.querySelector('#propertyInquiryForm');
    if (propertyInquiryForm) {
        propertyInquiryForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = {};
            formData.forEach((value, key) => {
                data[key] = value;
            });
            
            // Validate form
            const errors = apsUtils.validateForm(this);
            if (errors.length > 0) {
                apsUtils.showNotification(errors.join(', '), 'error');
                return;
            }
            
            // Show loading
            apsUtils.showLoading();
            
            // Submit inquiry
            apsUtils.ajax('/api/property/inquiry', {
                method: 'POST',
                body: JSON.stringify(data)
            })
            .then(function(response) {
                apsUtils.hideLoading();
                if (response.success) {
                    apsUtils.showNotification('Inquiry sent successfully! Property owner will contact you soon.', 'success');
                    propertyInquiryForm.reset();
                    
                    // Close modal if exists
                    const modal = document.querySelector('#inquiryModal');
                    if (modal) {
                        const bsModal = bootstrap.Modal.getInstance(modal);
                        if (bsModal) {
                            bsModal.hide();
                        }
                    }
                } else {
                    apsUtils.showNotification(response.message || 'Failed to send inquiry', 'error');
                }
            })
            .catch(function(error) {
                apsUtils.hideLoading();
                apsUtils.showNotification('Network error. Please try again.', 'error');
            });
        });
    }
    
    // Quick contact buttons
    const quickContactButtons = document.querySelectorAll('.quick-contact');
    quickContactButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            const type = this.getAttribute('data-type');
            const propertyId = this.getAttribute('data-property-id');
            
            if (type && propertyId) {
                // Open inquiry modal with property pre-filled
                const modal = document.querySelector('#inquiryModal');
                if (modal) {
                    const propertyInput = modal.querySelector('#propertyId');
                    if (propertyInput) {
                        propertyInput.value = propertyId;
                    }
                    
                    const bsModal = bootstrap.Modal.getInstance(modal) || new bootstrap.Modal(modal);
                    bsModal.show();
                }
            }
        });
    });
    
    // Schedule visit form
    const scheduleVisitForm = document.querySelector('#scheduleVisitForm');
    if (scheduleVisitForm) {
        scheduleVisitForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = {};
            formData.forEach((value, key) => {
                data[key] = value;
            });
            
            // Validate form
            const errors = apsUtils.validateForm(this);
            if (errors.length > 0) {
                apsUtils.showNotification(errors.join(', '), 'error');
                return;
            }
            
            // Show loading
            apsUtils.showLoading();
            
            // Submit visit request
            apsUtils.ajax('/api/property/schedule-visit', {
                method: 'POST',
                body: JSON.stringify(data)
            })
            .then(function(response) {
                apsUtils.hideLoading();
                if (response.success) {
                    apsUtils.showNotification('Visit scheduled successfully! We will confirm your appointment.', 'success');
                    scheduleVisitForm.reset();
                    
                    // Close modal if exists
                    const modal = document.querySelector('#scheduleVisitModal');
                    if (modal) {
                        const bsModal = bootstrap.Modal.getInstance(modal);
                        if (bsModal) {
                            bsModal.hide();
                        }
                    }
                } else {
                    apsUtils.showNotification(response.message || 'Failed to schedule visit', 'error');
                }
            })
            .catch(function(error) {
                apsUtils.hideLoading();
                apsUtils.showNotification('Network error. Please try again.', 'error');
            });
        });
    }
    
    // Phone number formatting
    const phoneInputs = document.querySelectorAll('input[type="tel"]');
    phoneInputs.forEach(function(input) {
        input.addEventListener('input', function() {
            // Remove all non-numeric characters
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    });
    
    // Character counter for textareas
    const textareas = document.querySelectorAll('textarea[maxlength]');
    textareas.forEach(function(textarea) {
        const maxLength = parseInt(textarea.getAttribute('maxlength'));
        const counter = document.createElement('small');
        counter.className = 'text-muted';
        counter.style.cssText = 'float: right; margin-top: 5px;';
        
        // Update counter function
        function updateCounter() {
            const remaining = maxLength - textarea.value.length;
            counter.textContent = `${remaining} characters remaining`;
            counter.style.color = remaining < 50 ? '#dc3545' : '#6c757d';
        }
        
        // Add counter after textarea
        textarea.parentNode.appendChild(counter);
        
        // Update on input
        textarea.addEventListener('input', updateCounter);
        updateCounter();
    });
});
