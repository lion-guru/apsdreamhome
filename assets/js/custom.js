/**
 * APS Dream Home - Modern JavaScript
 * Enhanced with modern features and better organization
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize AOS Animation
    if (typeof AOS !== 'undefined') {
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true,
            mirror: false
        });
    }

    // Initialize LazyLoad
    if (typeof LazyLoad !== 'undefined') {
        new LazyLoad({
            elements_selector: '.lazy',
            threshold: 100,
            callback_loaded: (img) => {
                img.classList.add('loaded');
            }
        });
    }

    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;
            
            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                window.scrollTo({
                    top: targetElement.offsetTop - 80,
                    behavior: 'smooth'
                });
            }
        });
    });

    // Sticky header on scroll
    const header = document.querySelector('.main-header');
    if (header) {
        let lastScroll = 0;
        window.addEventListener('scroll', () => {
            const currentScroll = window.pageYOffset;
            
            if (currentScroll <= 0) {
                header.classList.remove('scroll-up');
                return;
            }
            
            if (currentScroll > lastScroll && !header.classList.contains('scroll-down')) {
                // Scroll down
                header.classList.remove('scroll-up');
                header.classList.add('scroll-down');
            } else if (currentScroll < lastScroll && header.classList.contains('scroll-down')) {
                // Scroll up
                header.classList.remove('scroll-down');
                header.classList.add('scroll-up');
            }
            
            lastScroll = currentScroll;
            
            // Add/remove background on scroll
            if (currentScroll > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });
    }

    // Mobile menu toggle
    const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
    const mobileMenu = document.querySelector('.mobile-menu');
    if (mobileMenuToggle && mobileMenu) {
        mobileMenuToggle.addEventListener('click', () => {
            mobileMenuToggle.classList.toggle('active');
            mobileMenu.classList.toggle('active');
            document.body.classList.toggle('mobile-menu-open');
        });
    }

    // Back to top button
    const backToTopButton = document.querySelector('.back-to-top');
    if (backToTopButton) {
        window.addEventListener('scroll', () => {
            if (window.pageYOffset > 300) {
                backToTopButton.classList.add('show');
            } else {
                backToTopButton.classList.remove('show');
            }
        });

        backToTopButton.addEventListener('click', (e) => {
            e.preventDefault();
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }

    // Initialize property image gallery if it exists
    initPropertyGallery();
    
    // Initialize property filtering if it exists
    initPropertyFiltering();
    
    // Initialize newsletter form if it exists
    initNewsletterForm();
});

/**
 * Property Related Functions
 */

// Toggle favorite status
function toggleFavorite(propertyId, event) {
    if (event) event.stopPropagation();
    
    const heartIcon = event ? event.currentTarget.querySelector('i') : null;
    const isFavorite = heartIcon ? heartIcon.classList.contains('fas') : false;
    
    // Toggle UI state immediately for better UX
    if (heartIcon) {
        heartIcon.classList.toggle('far');
        heartIcon.classList.toggle('fas');
        heartIcon.classList.toggle('text-danger');
        
        // Add animation class
        heartIcon.classList.add('animate__animated', 'animate__heartBeat');
        setTimeout(() => {
            heartIcon.classList.remove('animate__animated', 'animate__heartBeat');
        }, 1000);
    }
    
    // Make API call to update favorite status
    fetch('api/toggle-favorite.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            property_id: propertyId,
            is_favorite: !isFavorite
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success toast notification
            showToast(
                isFavorite ? 'Removed from favorites' : 'Added to favorites',
                'success'
            );
            
            // Update favorite count if element exists
            const favoriteCount = document.getElementById('favorite-count');
            if (favoriteCount) {
                const currentCount = parseInt(favoriteCount.textContent) || 0;
                favoriteCount.textContent = isFavorite ? currentCount - 1 : currentCount + 1;
            }
        } else {
            // Revert UI on error
            if (heartIcon) {
                heartIcon.classList.toggle('far');
                heartIcon.classList.toggle('fas');
                heartIcon.classList.toggle('text-danger');
            }
            
            // Show error message
            showToast(data.message || 'An error occurred', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        // Revert UI on error
        if (heartIcon) {
            heartIcon.classList.toggle('far');
            heartIcon.classList.toggle('fas');
            heartIcon.classList.toggle('text-danger');
        }
        showToast('Failed to update favorites. Please try again.', 'error');
    });
}

// Share property
function shareProperty(propertyId, title = '', text = '') {
    const shareData = {
        title: title || 'Check out this property',
        text: text || 'I found this amazing property on APS Dream Home!',
        url: `${window.location.origin}/property_details.php?id=${propertyId}`,
    };

    if (navigator.share) {
        navigator.share(shareData)
            .then(() => {
                showToast('Shared successfully!', 'success');
            })
            .catch((error) => {
                console.log('Error sharing:', error);
                showToast('Failed to share. Please try again.', 'error');
            });
    } else {
        // Fallback for browsers that don't support Web Share API
        const url = shareData.url;
        const shareText = `${shareData.title}\n${shareData.text}\n\n${url}`;
        
        // Try to copy to clipboard
        if (navigator.clipboard) {
            navigator.clipboard.writeText(shareText)
                .then(() => {
                    showToast('Link copied to clipboard!', 'success');
                })
                .catch(() => {
                    // Fallback to prompt if clipboard fails
                    prompt('Copy this link to share:', url);
                });
        } else {
            // Fallback to prompt if clipboard API not available
            prompt('Copy this link to share:', url);
        }
    }
}

// Schedule a property visit
function scheduleVisit(propertyId, propertyTitle = '') {
    // Create or find modal element
    let modal = document.getElementById('scheduleVisitModal');
    
    if (!modal) {
        // Create modal if it doesn't exist
        modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.id = 'scheduleVisitModal';
        modal.setAttribute('tabindex', '-1');
        modal.setAttribute('aria-hidden', 'true');
        
        // Format the modal HTML as a single template literal
        const modalHTML = `
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Schedule a Visit</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="visitForm" novalidate>
                            <input type="hidden" name="property_id" value="${propertyId}">
                            ${propertyTitle ? `
                            <div class="mb-3">
                                <label class="form-label">Property</label>
                                <p class="fw-semibold">${propertyTitle}</p>
                            </div>` : ''}
                            <div class="mb-3">
                                <label for="visitDate" class="form-label">Preferred Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="visitDate" name="visit_date" required>
                                <div class="invalid-feedback">Please select a date</div>
                            </div>
                            <div class="mb-3">
                                <label for="visitTime" class="form-label">Preferred Time <span class="text-danger">*</span></label>
                                <select class="form-select" id="visitTime" name="visit_time" required>
                                    <option value="" selected disabled>Select time</option>
                                    <option value="09:00">09:00 AM</option>
                                    <option value="10:00">10:00 AM</option>
                                    <option value="11:00">11:00 AM</option>
                                    <option value="12:00">12:00 PM</option>
                                    <option value="13:00">01:00 PM</option>
                                    <option value="14:00">02:00 PM</option>
                                    <option value="15:00">03:00 PM</option>
                                    <option value="16:00">04:00 PM</option>
                                    <option value="17:00">05:00 PM</option>
                                </select>
                                <div class="invalid-feedback">Please select a time</div>
                            </div>
                            <div class="mb-3">
                                <label for="name" class="form-label">Your Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" required>
                                <div class="invalid-feedback">Please enter your name</div>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" required>
                                <div class="invalid-feedback">Please enter a valid email</div>
                            </div>
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" id="phone" name="phone" required>
                                <div class="invalid-feedback">Please enter your phone number</div>
                            </div>
                            <div class="mb-3">
                                <label for="message" class="form-label">Additional Notes</label>
                                <textarea class="form-control" id="message" name="message" rows="3"></textarea>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                    <span class="btn-text">Schedule Visit</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        `;
        
        // Set the innerHTML
        modal.innerHTML = modalHTML;
        
        // Add modal to the document
        document.body.appendChild(modal);
        
        // Initialize Bootstrap modal
        const bsModal = new bootstrap.Modal(modal);
        
        // Handle form submission
        const form = modal.querySelector('#visitForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                submitVisitRequest(form);
            });
        }
        
        // Show the modal
        bsModal.show();
        
        // Set minimum date to today
        const today = new Date().toISOString().split('T')[0];
        const dateInput = modal.querySelector('#visitDate');
        if (dateInput) {
            dateInput.min = today;
            // Set default date to tomorrow
            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            dateInput.valueAsDate = tomorrow;
        }
        
        // Initialize phone input formatting
        const phoneInput = modal.querySelector('#phone');
        if (phoneInput) {
            // Add input mask for phone number
            phoneInput.addEventListener('input', function(e) {
                let x = e.target.value.replace(/\D/g, '').match(/(\d{0,3})(\d{0,3})(\d{0,4})/);
                e.target.value = !x[2] ? x[1] : `(${x[1]}) ${x[2]}${x[3] ? `-${x[3]}` : ''}`;
            });
        }
    } else {
        // Update property ID if modal already exists
        const propertyIdInput = modal.querySelector('input[name="property_id"]');
        if (propertyIdInput) {
            propertyIdInput.value = propertyId;
        }
        
        // Update property title if provided
        if (propertyTitle) {
            const propertyTitleEl = modal.querySelector('.modal-body p');
            if (propertyTitleEl) {
                propertyTitleEl.textContent = propertyTitle;
            }
        }
        
        // Show the modal
        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();
    }
}

// Submit visit request form
function submitVisitRequest(form) {
    if (!form) return;
    
    // Validate form
    if (!form.checkValidity()) {
        form.classList.add('was-validated');
        return;
    }
    
    // Get form data
    const formData = new FormData(form);
    const submitButton = form.querySelector('button[type="submit"]');
    const spinner = submitButton ? submitButton.querySelector('.spinner-border') : null;
    const buttonText = submitButton ? submitButton.querySelector('.btn-text') : null;
    
    // Show loading state
    if (spinner && buttonText) {
        spinner.classList.remove('d-none');
        buttonText.textContent = 'Scheduling...';
    }
    
    // Disable submit button
    if (submitButton) {
        submitButton.disabled = true;
    }
    
    // Convert form data to JSON
    const jsonData = {};
    formData.forEach((value, key) => {
        jsonData[key] = value;
    });
    
    // Simulate API call (replace with actual API call)
    setTimeout(() => {
        // In a real application, you would make an AJAX request here
        console.log('Submitting visit request:', jsonData);
        
        // Simulate success response
        const success = Math.random() > 0.2; // 80% success rate for demo
        
        if (success) {
            // Show success message
            showToast('Visit scheduled successfully! Our agent will contact you soon.', 'success');
            
            // Close the modal
            const modal = bootstrap.Modal.getInstance(form.closest('.modal'));
            if (modal) {
                modal.hide();
            }
            
            // Reset form
            form.reset();
            form.classList.remove('was-validated');
        } else {
            // Show error message
            showToast('Failed to schedule visit. Please try again later.', 'error');
        }
        
        // Reset button state
        if (spinner && buttonText && submitButton) {
            spinner.classList.add('d-none');
            buttonText.textContent = 'Schedule Visit';
            submitButton.disabled = false;
        }
    }, 1500);
}

/**
 * Property Gallery
 */
function initPropertyGallery() {
    const gallery = document.querySelector('.property-gallery');
    if (!gallery) return;
    
    const mainImage = gallery.querySelector('.gallery-main-img');
    const thumbnails = gallery.querySelectorAll('.gallery-thumbnail');
    
    if (thumbnails.length > 0) {
        thumbnails.forEach(thumb => {
            thumb.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Remove active class from all thumbnails
                thumbnails.forEach(t => t.classList.remove('active'));
                
                // Add active class to clicked thumbnail
                this.classList.add('active');
                
                // Update main image
                if (mainImage) {
                    mainImage.src = this.href || this.dataset.image;
                    mainImage.alt = this.title || 'Property Image';
                    
                    // Add fade effect
                    mainImage.style.opacity = '0';
                    setTimeout(() => {
                        mainImage.style.opacity = '1';
                        mainImage.style.transition = 'opacity 0.3s ease';
                    }, 100);
                }
            });
        });
    }
    
    // Initialize lightbox if it exists
    if (typeof lightbox !== 'undefined') {
        lightbox.option({
            'resizeDuration': 200,
            'wrapAround': true,
            'showImageNumberLabel': false,
            'disableScrolling': true,
            'albumLabel': 'Image %1 of %2'
        });
    }
}

/**
 * Property Filtering
 */
function initPropertyFiltering() {
    const filterForm = document.getElementById('propertyFilterForm');
    if (!filterForm) return;
    
    // Price range slider
    const priceRange = document.getElementById('priceRange');
    const priceValue = document.getElementById('priceValue');
    
    if (priceRange && priceValue) {
        // Set initial value
        priceValue.textContent = formatCurrency(priceRange.value);
        
        // Update value on input
        priceRange.addEventListener('input', function() {
            priceValue.textContent = formatCurrency(this.value);
        });
    }
    
    // Reset filters
    const resetButton = filterForm.querySelector('.btn-reset');
    if (resetButton) {
        resetButton.addEventListener('click', function(e) {
            e.preventDefault();
            filterForm.reset();
            if (priceValue) priceValue.textContent = formatCurrency(priceRange.value);
            // Trigger filter update
            updatePropertyResults();
        });
    }
    
    // Submit form on filter change
    const filterInputs = filterForm.querySelectorAll('select, input[type="range"]');
    filterInputs.forEach(input => {
        input.addEventListener('change', function() {
            updatePropertyResults();
        });
    });
}

// Update property results based on filters
function updatePropertyResults() {
    const filterForm = document.getElementById('propertyFilterForm');
    if (!filterForm) return;
    
    // Show loading state
    const resultsContainer = document.getElementById('propertyResults');
    if (resultsContainer) {
        resultsContainer.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';
    }
    
    // Get form data
    const formData = new FormData(filterForm);
    
    // Convert form data to URLSearchParams
    const searchParams = new URLSearchParams();
    for (const [key, value] of formData.entries()) {
        if (value) searchParams.append(key, value);
    }
    
    // Simulate API call (replace with actual API call)
    setTimeout(() => {
        // In a real application, you would make an AJAX request here
        console.log('Updating property results with filters:', Object.fromEntries(searchParams.entries()));
        
        // Simulate filtered results
        const filteredProperties = []; // Replace with actual filtered data
        
        // Update results
        renderPropertyResults(filteredProperties);
    }, 800);
}

// Render property results
function renderPropertyResults(properties) {
    const resultsContainer = document.getElementById('propertyResults');
    if (!resultsContainer) return;
    
    if (!properties || properties.length === 0) {
        resultsContainer.innerHTML = `
            <div class="col-12 text-center py-5">
                <i class="fas fa-search fa-3x text-muted mb-3"></i>
                <h4>No properties found</h4>
                <p class="text-muted">Try adjusting your search or filter criteria</p>
                <button class="btn btn-outline-primary mt-2 btn-reset">Reset Filters</button>
            </div>
        `;
        return;
    }
    
    // Render property cards
    let html = '';
    properties.forEach(property => {
        html += `
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="property-card h-100">
                    <div class="property-image-container">
                        <img src="${property.image || 'assets/images/placeholder.jpg'}" 
                             alt="${property.title || 'Property'}" 
                             class="property-image">
                        <div class="property-badges">
                            <span class="badge badge-status">${property.status || 'For Sale'}</span>
                            <span class="badge badge-type">${property.type || 'Apartment'}</span>
                        </div>
                        <div class="property-actions">
                            <button class="btn btn-icon" onclick="toggleFavorite('${property.id}', event)">
                                <i class="${property.isFavorite ? 'fas' : 'far'} fa-heart"></i>
                            </button>
                            <button class="btn btn-icon" onclick="shareProperty('${property.id}', '${property.title}')">
                                <i class="fas fa-share-alt"></i>
                            </button>
                        </div>
                    </div>
                    <div class="property-content">
                        <h3 class="property-title">
                            <a href="property-details.php?id=${property.id}">${property.title || 'Luxury Property'}</a>
                        </h3>
                        <div class="property-location">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>${property.location || 'Location not specified'}</span>
                        </div>
                        <div class="property-features">
                            <div class="feature">
                                <i class="fas fa-bed"></i>
                                <span>${property.bedrooms || 0} Beds</span>
                            </div>
                            <div class="feature">
                                <i class="fas fa-bath"></i>
                                <span>${property.bathrooms || 0} Baths</span>
                            </div>
                            <div class="feature">
                                <i class="fas fa-ruler-combined"></i>
                                <span>${property.area || 0} sqft</span>
                            </div>
                        </div>
                        <div class="property-footer">
                            <div class="property-price">
                                $${(property.price || 0).toLocaleString()}
                                <span class="price-per-unit">$${(property.price_per_sqft || 0).toLocaleString()}/sqft</span>
                            </div>
                            <button class="btn btn-sm btn-outline-primary" 
                                    onclick="scheduleVisit('${property.id}', '${property.title}')">
                                Schedule Visit
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
    
    resultsContainer.innerHTML = html;
}

/**
 * Newsletter Form
 */
function initNewsletterForm() {
    const newsletterForm = document.getElementById('newsletterForm');
    if (!newsletterForm) return;
    
    newsletterForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const emailInput = this.querySelector('input[type="email"]');
        const submitButton = this.querySelector('button[type="submit"]');
        const buttonText = submitButton ? submitButton.querySelector('.btn-text') : null;
        const spinner = submitButton ? submitButton.querySelector('.spinner-border') : null;
        
        // Validate email
        if (!emailInput.value || !isValidEmail(emailInput.value)) {
            emailInput.classList.add('is-invalid');
            return;
        }
        
        // Show loading state
        if (buttonText && spinner) {
            buttonText.textContent = 'Subscribing...';
            spinner.classList.remove('d-none');
        }
        
        if (submitButton) {
            submitButton.disabled = true;
        }
        
        // Simulate API call (replace with actual API call)
        setTimeout(() => {
            console.log('Subscribing email:', emailInput.value);
            
            // Show success message
            showToast('Thank you for subscribing to our newsletter!', 'success');
            
            // Reset form
            this.reset();
            
            // Reset button state
            if (buttonText && spinner) {
                buttonText.textContent = 'Subscribe';
                spinner.classList.add('d-none');
            }
            
            if (submitButton) {
                submitButton.disabled = false;
            }
        }, 1500);
    });
    
    // Validate email on input
    const emailInput = newsletterForm.querySelector('input[type="email"]');
    if (emailInput) {
        emailInput.addEventListener('input', function() {
            if (this.value && !isValidEmail(this.value)) {
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
            }
        });
    }
}

/**
 * Helper Functions
 */

// Show toast notification
function showToast(message, type = 'info') {
    // Create toast container if it doesn't exist
    let toastContainer = document.getElementById('toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toast-container';
        toastContainer.style.position = 'fixed';
        toastContainer.style.top = '20px';
        toastContainer.style.right = '20px';
        toastContainer.style.zIndex = '9999';
        document.body.appendChild(toastContainer);
    }
    
    // Create toast element
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type} border-0 fade show`;
    toast.role = 'alert';
    toast.setAttribute('aria-live', 'assertive');
    toast.setAttribute('aria-atomic', 'true');
    
    // Set toast content based on type
    let icon = '';
    switch (type) {
        case 'success':
            icon = 'check-circle';
            break;
        case 'error':
            icon = 'exclamation-circle';
            break;
        case 'warning':
            icon = 'exclamation-triangle';
            break;
        case 'info':
        default:
            icon = 'info-circle';
    }
    
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body d-flex align-items-center">
                <i class="fas fa-${icon} me-2"></i>
                ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    `;
    
    // Add toast to container
    toastContainer.appendChild(toast);
    
    // Initialize Bootstrap toast
    const bsToast = new bootstrap.Toast(toast, {
        animation: true,
        autohide: true,
        delay: 5000
    });
    
    // Show toast
    bsToast.show();
    
    // Remove toast after it's hidden
    toast.addEventListener('hidden.bs.toast', function() {
        toast.remove();
    });
}

// Format currency
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
        maximumFractionDigits: 0
    }).format(amount);
}

// Validate email
function isValidEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(String(email).toLowerCase());
}
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="visitNotes" class="form-label">Additional Notes</label>
                            <textarea class="form-control" id="visitNotes" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="submitVisitBtn">
                        <i class="fas fa-calendar-check me-2"></i>Schedule Visit
                    </button>
                </div>
            </div>
        </div>`;
    
    // Add modal to the page
    document.body.appendChild(modal);
    
    // Initialize and show the modal
    const visitModal = new bootstrap.Modal(modal);
    visitModal.show();
    
    // Add event listener for the submit button
    modal.querySelector('#submitVisitBtn').addEventListener('click', function() {
        submitVisitRequest(propertyId);
    });
    
    // Clean up the modal after it's closed
    modal.addEventListener('hidden.bs.modal', function () {
        document.body.removeChild(modal);
    });
}

function submitVisitRequest(propertyId) {
    const form = document.getElementById('visitForm');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    const formData = {
        propertyId: propertyId,
        date: document.getElementById('visitDate').value,
        time: document.getElementById('visitTime').value,
        notes: document.getElementById('visitNotes').value
    };
    
    console.log('Submitting visit request:', formData);
    
    // Here you would typically make an AJAX call to submit the form
    // For now, we'll just show a success message
    const modal = bootstrap.Modal.getInstance(document.getElementById('scheduleVisitModal'));
    modal.hide();
    
    // Show success message
    alert('Your visit has been scheduled! We will contact you shortly to confirm the details.');
}

// Initialize components when the DOM is fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize AOS (Animate On Scroll)
    if (typeof AOS !== 'undefined') {
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true
        });
    }

    // Initialize Swiper if it exists on the page
    if (typeof Swiper !== 'undefined' && document.querySelector('.swiper')) {
        new Swiper('.swiper', {
            loop: true,
            autoplay: {
                delay: 5000,
                disableOnInteraction: false,
            },
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
        });
    }

    // Initialize LazyLoad for images
    if (typeof LazyLoad !== 'undefined') {
        new LazyLoad({
            elements_selector: '.lazy'
        });
    }
});
