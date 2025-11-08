<?php
/**
 * Contact Page Template
 * Displays contact information and contact form
 */

?>

<!-- Hero Section for Contact Page -->
<section class="hero-contact py-5" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <div class="hero-content text-white">
                    <h1 class="display-4 fw-bold mb-4" data-aos="fade-up">
                        Get In <span class="text-warning">Touch</span>
                    </h1>
                    <p class="lead mb-4" data-aos="fade-up" data-aos-delay="100">
                        Ready to find your dream home? Contact our expert team today.
                        We're here to help you every step of the way.
                    </p>
                    <div class="contact-quick-info" data-aos="fade-up" data-aos-delay="200">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-phone-alt text-warning fa-2x me-3"></i>
                            <div>
                                <h5 class="text-white mb-0">Call Us Today</h5>
                                <p class="text-white-50 mb-0"><?php echo $contact_info['phone'] ?? '+91-1234567890'; ?></p>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <i class="fas fa-envelope text-warning fa-2x me-3"></i>
                            <div>
                                <h5 class="text-white mb-0">Email Us</h5>
                                <p class="text-white-50 mb-0"><?php echo $contact_info['email'] ?? 'info@apsdreamhome.com'; ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6" data-aos="fade-left">
                <div class="hero-image">
                    <img src="<?php echo ASSET_URL; ?>images/contact-hero.jpg"
                         alt="Contact APS Dream Home"
                         class="img-fluid rounded shadow"
                         onerror="this.src='https://via.placeholder.com/600x400/667eea/ffffff?text=Contact+Us'">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contact Information & Form Section -->
<section class="contact-section py-5">
    <div class="container">
        <div class="row g-5">
            <!-- Contact Information -->
            <div class="col-lg-4" data-aos="fade-right">
                <div class="contact-info-card">
                    <h3 class="section-title mb-4">
                        <i class="fas fa-address-card text-primary me-2"></i>
                        Contact Information
                    </h3>

                    <div class="contact-info-item mb-4">
                        <div class="contact-icon mb-3">
                            <i class="fas fa-map-marker-alt fa-2x text-primary"></i>
                        </div>
                        <h5>Our Office</h5>
                        <p class="mb-0"><?php echo $contact_info['address'] ?? '123 Main Street, Gorakhpur, Uttar Pradesh - 273001'; ?></p>
                    </div>

                    <div class="contact-info-item mb-4">
                        <div class="contact-icon mb-3">
                            <i class="fas fa-phone fa-2x text-success"></i>
                        </div>
                        <h5>Phone Number</h5>
                        <p class="mb-0">
                            <a href="tel:<?php echo str_replace(['+', '-', ' '], '', $contact_info['phone'] ?? '+911234567890'); ?>"
                               class="text-decoration-none">
                                <?php echo $contact_info['phone'] ?? '+91-1234567890'; ?>
                            </a>
                        </p>
                    </div>

                    <div class="contact-info-item mb-4">
                        <div class="contact-icon mb-3">
                            <i class="fas fa-envelope fa-2x text-warning"></i>
                        </div>
                        <h5>Email Address</h5>
                        <p class="mb-0">
                            <a href="mailto:<?php echo $contact_info['email'] ?? 'info@apsdreamhome.com'; ?>"
                               class="text-decoration-none">
                                <?php echo $contact_info['email'] ?? 'info@apsdreamhome.com'; ?>
                            </a>
                        </p>
                    </div>

                    <div class="contact-info-item mb-4">
                        <div class="contact-icon mb-3">
                            <i class="fas fa-clock fa-2x text-info"></i>
                        </div>
                        <h5>Business Hours</h5>
                        <p class="mb-0"><?php echo $contact_info['hours'] ?? 'Mon - Sat: 9:00 AM - 8:00 PM, Sun: 10:00 AM - 6:00 PM'; ?></p>
                    </div>

                    <!-- Social Media Links -->
                    <div class="social-media mt-4">
                        <h5>Follow Us</h5>
                        <div class="social-links">
                            <a href="#" class="social-link facebook">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="#" class="social-link twitter">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a href="#" class="social-link instagram">
                                <i class="fab fa-instagram"></i>
                            </a>
                            <a href="#" class="social-link linkedin">
                                <i class="fab fa-linkedin-in"></i>
                            </a>
                            <a href="#" class="social-link youtube">
                                <i class="fab fa-youtube"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="col-lg-8" data-aos="fade-left">
                <div class="contact-form-card">
                    <h3 class="section-title mb-4">
                        <i class="fas fa-paper-plane text-primary me-2"></i>
                        Send us a Message
                    </h3>

                    <form action="<?php echo BASE_URL; ?>contact/send" method="POST" class="contact-form">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="first_name" class="form-label">First Name *</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" required>
                            </div>
                            <div class="col-md-6">
                                <label for="last_name" class="form-label">Last Name *</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" required>
                            </div>

                            <div class="col-md-6">
                                <label for="email" class="form-label">Email Address *</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="col-md-6">
                                <label for="phone" class="form-label">Phone Number *</label>
                                <input type="tel" class="form-control" id="phone" name="phone" required>
                            </div>

                            <div class="col-12">
                                <label for="subject" class="form-label">Subject *</label>
                                <select class="form-select" id="subject" name="subject" required>
                                    <option value="">Select Subject</option>
                                    <option value="general">General Inquiry</option>
                                    <option value="property">Property Inquiry</option>
                                    <option value="support">Customer Support</option>
                                    <option value="complaint">Complaint</option>
                                    <option value="partnership">Business Partnership</option>
                                    <option value="media">Media Inquiry</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <label for="message" class="form-label">Message *</label>
                                <textarea class="form-control" id="message" name="message" rows="6"
                                          placeholder="Please describe your inquiry in detail..." required></textarea>
                            </div>

                            <div class="col-12">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="newsletter" name="newsletter">
                                    <label class="form-check-label" for="newsletter">
                                        Subscribe to our newsletter for property updates and exclusive offers
                                    </label>
                                </div>
                            </div>

                            <div class="col-12">
                                <button type="submit" class="btn btn-primary btn-lg px-5">
                                    <i class="fas fa-paper-plane me-2"></i>Send Message
                                </button>
                                <button type="reset" class="btn btn-outline-secondary btn-lg ms-3">
                                    <i class="fas fa-redo me-2"></i>Reset Form
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="cta-contact py-5" style="background: linear-gradient(135deg, #1a237e 0%, #667eea 100%);">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8 mb-4 mb-lg-0">
                <h3 class="text-white mb-3">
                    <i class="fas fa-comments me-2"></i>
                    Still Have Questions?
                </h3>
                <p class="text-white-50 mb-0">
                    Our friendly customer service team is ready to help you with any questions or concerns.
                    Don't hesitate to reach out - we're here to make your property journey smooth and successful!
                </p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <a href="tel:<?php echo str_replace(['+', '-', ' '], '', $contact_info['phone'] ?? '+911234567890'); ?>"
                   class="btn btn-warning btn-lg px-4 py-3 me-3">
                    <i class="fas fa-phone-alt me-2"></i>Call Now
                </a>
                <a href="<?php echo BASE_URL; ?>about" class="btn btn-outline-light btn-lg px-4 py-3">
                    <i class="fas fa-info-circle me-2"></i>Learn More
                </a>
            </div>
        </div>
    </div>
</section>
