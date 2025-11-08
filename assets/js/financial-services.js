/**
 * Financial Services JavaScript - Updated to use shared utilities
 * Removed duplicate initialization code (now handled by utils.js)
 */

// Import shared utilities
import { initAOS, initFormHandling, initPhoneFormatting, initSmoothScrolling, initTooltips, initLazyLoading } from './utils.js';

// Initialize AOS animations
AOS.init({
    duration: 800,
    easing: 'ease-in-out',
    once: true,
    mirror: false
});

document.addEventListener('DOMContentLoaded', function() {
    // Initialize form handling using shared utility
    const financialServicesForm = document.getElementById('financialServicesForm');
    if (financialServicesForm) {
        initializeFormHandling();
    }

    // Initialize phone number formatting using shared utility
    const phoneInput = document.getElementById('phone');
    if (phoneInput) {
        initializePhoneFormatting(phoneInput);
    }

    // Initialize smooth scrolling using shared utility
    initializeSmoothScrolling();

    // Initialize Bootstrap tooltips using shared utility
    initializeTooltips();

    // Initialize lazy loading using shared utility
    initializeLazyLoading();

    // Process URL parameters
    processUrlParameters();

    // Initialize process timeline animations
    initializeProcessTimeline();

    // Initialize FAQ accordion
    initializeFaqAccordion();

    // Initialize investment calculator
    initializeInvestmentCalculator();
});

// Form handling initialization
function initializeFormHandling() {
    const form = document.getElementById('financialServicesForm');
    
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Validate form
        if (!validateForm(form)) {
            return;
        }

        // Show loading state
        const submitButton = form.querySelector('button[type="submit"]');
        const originalButtonText = submitButton.innerHTML;
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Sending...';
        submitButton.disabled = true;

        try {
            // Collect form data
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());

            // Send data to API
            const response = await fetch('/api/financial-services/contact', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });

            if (!response.ok) {
                throw new Error('Network response was not ok');
            }

            // Show success message
            Swal.fire({
                icon: 'success',
                title: 'Message Sent!',
                text: 'We will contact you shortly regarding your financial inquiry.',
                confirmButtonColor: 'var(--primary-color)'
            });

            // Reset form
            form.reset();

        } catch (error) {
            console.error('Error:', error);
            
            // Show error message
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Something went wrong! Please try again later.',
                confirmButtonColor: 'var(--primary-color)'
            });
        } finally {
            // Restore button state
            submitButton.innerHTML = originalButtonText;
            submitButton.disabled = false;
        }
    });
}

// Form validation
function validateForm(form) {
    let isValid = true;
    const requiredFields = form.querySelectorAll('[required]');
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            isValid = false;
            field.classList.add('is-invalid');
        } else {
            field.classList.remove('is-invalid');
        }
    });

    // Email validation
    const emailField = form.querySelector('#email');
    if (emailField && emailField.value) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(emailField.value)) {
            isValid = false;
            emailField.classList.add('is-invalid');
        }
    }

    return isValid;
}

// Phone number formatting
function initializePhoneFormatting(input) {
    input.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length > 0) {
            if (value.length <= 3) {
                value = value;
            } else if (value.length <= 6) {
                value = value.slice(0, 3) + '-' + value.slice(3);
            } else {
                value = value.slice(0, 3) + '-' + value.slice(3, 6) + '-' + value.slice(6, 10);
            }
        }
        e.target.value = value;
    });
}

// Smooth scrolling for anchor links
function initializeSmoothScrolling() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

// Initialize Bootstrap tooltips
function initializeTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

// Initialize lazy loading for images
function initializeLazyLoading() {
    const lazyImages = document.querySelectorAll('img[loading="lazy"]');
    
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    observer.unobserve(img);
                }
            });
        });

        lazyImages.forEach(img => imageObserver.observe(img));
    }
}

// Process URL parameters
function processUrlParameters() {
    const urlParams = new URLSearchParams(window.location.search);
    
    // Handle service type selection
    const serviceType = urlParams.get('service');
    if (serviceType) {
        const serviceSelect = document.getElementById('serviceType');
        if (serviceSelect) {
            serviceSelect.value = serviceType;
        }
    }

    // Handle scroll to section
    const section = urlParams.get('section');
    if (section) {
        const targetSection = document.getElementById(section);
        if (targetSection) {
            setTimeout(() => {
                targetSection.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }, 500);
        }
    }
}

// Initialize process timeline animations
function initializeProcessTimeline() {
    const processSteps = document.querySelectorAll('.process-step');
    
    if ('IntersectionObserver' in window) {
        const stepObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('active');
                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.5
        });

        processSteps.forEach(step => stepObserver.observe(step));
    }
}

// Initialize FAQ accordion
function initializeFaqAccordion() {
    const accordionItems = document.querySelectorAll('.accordion-item');
    
    accordionItems.forEach(item => {
        const button = item.querySelector('.accordion-button');
        const collapse = item.querySelector('.accordion-collapse');
        
        if (button && collapse) {
            const bsCollapse = new bootstrap.Collapse(collapse, {
                toggle: false
            });

            button.addEventListener('click', () => {
                accordionItems.forEach(otherItem => {
                    if (otherItem !== item) {
                        const otherCollapse = otherItem.querySelector('.accordion-collapse');
                        if (otherCollapse && bootstrap.Collapse.getInstance(otherCollapse)) {
                            bootstrap.Collapse.getInstance(otherCollapse).hide();
                        }
                    }
                });
            });
        }
    });
}

// Initialize investment calculator
function initializeInvestmentCalculator() {
    const calculator = document.getElementById('investmentCalculator');
    if (!calculator) return;

    const calculateButton = calculator.querySelector('#calculateButton');
    const resultDiv = calculator.querySelector('#calculationResult');

    calculateButton.addEventListener('click', () => {
        const amount = parseFloat(calculator.querySelector('#investmentAmount').value);
        const period = parseInt(calculator.querySelector('#investmentPeriod').value);
        const rate = parseFloat(calculator.querySelector('#interestRate').value) / 100;

        if (isNaN(amount) || isNaN(period) || isNaN(rate)) {
            Swal.fire({
                icon: 'error',
                title: 'Invalid Input',
                text: 'Please enter valid numbers for all fields.',
                confirmButtonColor: 'var(--primary-color)'
            });
            return;
        }

        // Calculate future value using compound interest formula
        const futureValue = amount * Math.pow(1 + rate, period);
        const totalInterest = futureValue - amount;

        resultDiv.innerHTML = `
            <div class="alert alert-success mt-3">
                <h4 class="alert-heading">Investment Projection</h4>
                <p>Initial Investment: $${amount.toFixed(2)}</p>
                <p>Future Value: $${futureValue.toFixed(2)}</p>
                <p>Total Interest Earned: $${totalInterest.toFixed(2)}</p>
            </div>
        `;
    });
}