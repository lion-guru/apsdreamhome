<?php
/**
 * Contact Page - APS Dream Home
 * Professional contact form and office information
 */

// Set page title and description for layout
$page_title = $title ?? 'Contact Us - APS Dream Home';
$page_description = $description ?? 'Get in touch with APS Dream Home for all your real estate needs. Visit our office or call us to find your dream property.';
?>

<!-- Include Header -->
<?php include __DIR__ . '/../layouts/header_new.php'; ?>

<!-- Hero Section -->
<section class="hero-section bg-primary text-white py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <div class="hero-content">
                    <h1 class="display-4 fw-bold mb-4">
                        Contact <span class="text-warning">APS Dream Home</span>
                    </h1>
                    <p class="lead mb-4">
                        Get in touch with us for all your real estate needs. Our team is ready to help you find your perfect property.
                    </p>
                    <div class="d-flex flex-wrap gap-3">
                        <a href="<?php echo BASE_URL; ?>tel:<?php echo str_replace([' ', '-'], '', $contact_info['phone_numbers'][0] ?? '+917007444842'); ?>" class="btn btn-warning btn-lg me-2">
                            <i class="fas fa-phone me-2"></i>Call Now
                        </a>
                        <a href="<?php echo BASE_URL; ?>mailto:<?php echo $contact_info['email_addresses'][0] ?? 'info@apsdreamhomes.com'; ?>" class="btn btn-outline-light btn-lg">
                            <i class="fas fa-envelope me-2"></i>Email Us
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="hero-image text-center">
                    <img src="<?php echo BASE_URL; ?>/public/assets/images/contact-hero.jpg"
                         alt="Contact APS Dream Home"
                         class="img-fluid rounded shadow-lg"
                         onerror="this.src='https://via.placeholder.com/600x400/667eea/ffffff?text=Contact+APS+Dream+Home'">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contact Info Section -->
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center mb-5">
            <div class="col-lg-8 text-center">
                <h2 class="display-5 fw-bold mb-3">Get In Touch</h2>
                <p class="lead text-muted">Visit our office or contact us using the information below</p>
            </div>
        </div>

        <div class="row g-4">
            <?php foreach ($office_locations as $location): ?>
                <div class="col-lg-6">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body p-4">
                            <h5 class="card-title text-primary mb-3">
                                <i class="fas fa-map-marker-alt me-2"></i><?php echo htmlspecialchars($location['name']); ?>
                            </h5>
                            <div class="contact-details mb-3">
                                <p class="mb-2">
                                    <strong>Address:</strong><br>
                                    <?php echo htmlspecialchars($location['address']); ?><br>
                                    <?php echo htmlspecialchars($location['city']); ?>, 
                                    <?php echo htmlspecialchars($location['state']); ?> - 
                                    <?php echo htmlspecialchars($location['pincode']); ?>
                                </p>
                                <p class="mb-2">
                                    <strong>Phone:</strong> 
                                    <a href="tel:<?php echo str_replace([' ', '-'], '', $location['phone']); ?>" class="text-decoration-none">
                                        <?php echo htmlspecialchars($location['phone']); ?>
                                    </a>
                                </p>
                                <p class="mb-2">
                                    <strong>Email:</strong> 
                                    <a href="mailto:<?php echo htmlspecialchars($location['email']); ?>" class="text-decoration-none">
                                        <?php echo htmlspecialchars($location['email']); ?>
                                    </a>
                                </p>
                            </div>
                            <?php if (!empty($location['map_embed'])): ?>
                                <div class="map-container mt-3">
                                    <iframe src="<?php echo htmlspecialchars($location['map_embed']); ?>" 
                                            width="100%" 
                                            height="250" 
                                            frameborder="0" 
                                            style="border:0" 
                                            allowfullscreen>
                                    </iframe>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Contact Form Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row justify-content-center mb-5">
            <div class="col-lg-8 text-center">
                <h2 class="display-5 fw-bold mb-3">Send Us a Message</h2>
                <p class="lead text-muted">Have questions? Fill out the form below and we'll get back to you soon</p>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <form id="contactForm" class="needs-validation" novalidate>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="name" class="form-label"><?php echo $contact_form['fields']['name']['label']; ?> *</label>
                                        <input type="text" class="form-control" id="name" name="name" required>
                                        <div class="invalid-feedback">
                                            Please provide your full name.
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="email" class="form-label"><?php echo $contact_form['fields']['email']['label']; ?> *</label>
                                        <input type="email" class="form-control" id="email" name="email" required>
                                        <div class="invalid-feedback">
                                            Please provide a valid email address.
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="phone" class="form-label"><?php echo $contact_form['fields']['phone']['label']; ?></label>
                                        <input type="tel" class="form-control" id="phone" name="phone">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="subject" class="form-label"><?php echo $contact_form['fields']['subject']['label']; ?> *</label>
                                        <select class="form-select" id="subject" name="subject" required>
                                            <?php foreach ($contact_form['fields']['subject']['options'] as $value => $label): ?>
                                                <option value="<?php echo $value; ?>"><?php echo htmlspecialchars($label); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label for="message" class="form-label"><?php echo $contact_form['fields']['message']['label']; ?> *</label>
                                        <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                                        <div class="invalid-feedback">
                                            Please provide your message.
                                        </div>
                                    </div>
                                </div>
                                <div class="text-center mt-4">
                                    <button type="submit" class="btn btn-primary btn-lg px-5">
                                        <i class="fas fa-paper-plane me-2"></i>Send Message
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Working Hours Section -->
<section class="py-5 bg-primary text-white">
    <div class="container">
        <div class="row justify-content-center mb-4">
            <div class="col-lg-8 text-center">
                <h2 class="display-5 fw-bold mb-3">Office Hours</h2>
            </div>
        </div>
        
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="row text-center">
                    <div class="col-md-6">
                        <div class="hours-card">
                            <h5 class="text-warning mb-3">
                                <i class="fas fa-clock me-2"></i>Weekdays
                            </h5>
                            <p class="mb-0"><?php echo $contact_info['working_hours']['weekdays']; ?></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="hours-card">
                            <h5 class="text-warning mb-3">
                                <i class="fas fa-clock me-2"></i>Sunday
                            </h5>
                            <p class="mb-0"><?php echo $contact_info['working_hours']['sunday']; ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Include Footer -->
<?php include __DIR__ . '/../layouts/footer_new.php'; ?>

<!-- Custom JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Contact form validation and submission
    const contactForm = document.getElementById('contactForm');
    
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (!contactForm.checkValidity()) {
                // Show validation errors
                contactForm.classList.add('was-validated');
                return;
            }
            
            // Collect form data
            const formData = new FormData(contactForm);
            
            // Submit via fetch API
            fetch('<?php echo BASE_URL; ?>/api/contact', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    showAlert('Message sent successfully! We will get back to you soon.', 'success');
                    contactForm.reset();
                } else {
                    // Show error message
                    showAlert(data.message || 'Failed to send message. Please try again.', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('Network error. Please try again later.', 'error');
            });
        });
    }
    
    // Helper function to show alerts
    function showAlert(message, type) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(alertDiv);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.parentNode.removeChild(alertDiv);
            }
        }, 5000);
    }
});
</script>

<style>
.hours-card {
    background: rgba(255, 255, 255, 0.1);
    padding: 20px;
    border-radius: 8px;
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.map-container iframe {
    border-radius: 8px;
    border: 3px solid #e9ecef;
}

.position-fixed {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
}

.was-validated .form-control:invalid {
    border-color: #dc3545;
}

.invalid-feedback {
    display: none;
    color: #dc3545;
    font-size: 0.875rem;
    margin-top: 0.25rem;
}

.was-validated .form-control:invalid ~ .invalid-feedback {
    display: block;
}
</style>
