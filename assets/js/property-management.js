// Initialize AOS animations
AOS.init({
    duration: 800,
    easing: 'ease-in-out',
    once: true,
    mirror: false
});

// Handle contact form submission
document.getElementById('propertyManagementForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Get form data
    const formData = new FormData(this);
    const data = {};
    formData.forEach((value, key) => data[key] = value);
    
    // Show loading state
    const submitButton = this.querySelector('button[type="submit"]');
    const originalText = submitButton.textContent;
    submitButton.disabled = true;
    submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Sending...';
    
    // Send form data to server
    fetch('/api/property-management-inquiry', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        // Show success message
        Swal.fire({
            icon: 'success',
            title: 'Thank You!',
            text: 'Your inquiry has been sent successfully. We will contact you soon.',
            confirmButtonColor: '#007bff'
        });
        
        // Reset form
        this.reset();
    })
    .catch(error => {
        // Show error message
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: 'Something went wrong. Please try again later.',
            confirmButtonColor: '#007bff'
        });
        console.error('Error:', error);
    })
    .finally(() => {
        // Reset button state
        submitButton.disabled = false;
        submitButton.textContent = originalText;
    });
});

// Phone number formatting
const phoneInput = document.getElementById('phone');
if (phoneInput) {
    phoneInput.addEventListener('input', function(e) {
        let x = e.target.value.replace(/\D/g, '').match(/(\d{0,3})(\d{0,3})(\d{0,4})/);
        e.target.value = !x[2] ? x[1] : '(' + x[1] + ') ' + x[2] + (x[3] ? '-' + x[3] : '');
    });
}

// Smooth scroll for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            window.scrollTo({
                top: target.offsetTop - 100,
                behavior: 'smooth'
            });
        }
    });
});

// Initialize tooltips
const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

// Lazy load images
const imageObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            const img = entry.target;
            const src = img.getAttribute('data-src');
            if (src) {
                img.src = src;
                img.removeAttribute('data-src');
                imageObserver.unobserve(img);
            }
        }
    });
}, {
    rootMargin: '50px'
});

document.querySelectorAll('img[data-src]').forEach(img => {
    imageObserver.observe(img);
});

// Process URL parameters
function processURLParams() {
    const urlParams = new URLSearchParams(window.location.search);
    const propertyType = urlParams.get('type');
    const source = urlParams.get('source');
    
    if (propertyType) {
        const propertyTypeSelect = document.getElementById('propertyType');
        if (propertyTypeSelect) {
            propertyTypeSelect.value = propertyType;
        }
    }
    
    if (source) {
        const messageInput = document.getElementById('message');
        if (messageInput) {
            messageInput.value = `Inquiry from ${source}: `;
        }
    }
}

// Initialize process timeline animation
function initProcessTimeline() {
    const processSteps = document.querySelectorAll('.process-step');
    let delay = 0;
    
    processSteps.forEach(step => {
        step.setAttribute('data-aos', 'fade-right');
        step.setAttribute('data-aos-delay', delay);
        delay += 100;
    });
}

// Initialize FAQ accordion
function initFAQAccordion() {
    const accordionItems = document.querySelectorAll('.accordion-item');
    
    accordionItems.forEach(item => {
        const button = item.querySelector('.accordion-button');
        const collapse = item.querySelector('.accordion-collapse');
        
        button.addEventListener('click', () => {
            const isExpanded = button.getAttribute('aria-expanded') === 'true';
            
            // Update button state
            button.setAttribute('aria-expanded', !isExpanded);
            button.classList.toggle('collapsed', isExpanded);
            
            // Update collapse state
            collapse.classList.toggle('show', !isExpanded);
            
            // Scroll into view if expanded
            if (!isExpanded) {
                setTimeout(() => {
                    item.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }, 300);
            }
        });
    });
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    processURLParams();
    initProcessTimeline();
    initFAQAccordion();
});