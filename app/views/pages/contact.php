<?php
// Contact Page - APS Dream Home - Enhanced UI/UX
?>

<!-- Hero Section with Better Design -->
<section class="py-5 bg-gradient-primary text-white position-relative overflow-hidden">
    <div class="position-absolute top-0 start-0 w-100 h-100" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 50%, #667eea 100%);"></div>
    <div class="position-absolute top-0 start-0 w-100 h-100" style="background: url('<?php echo BASE_URL; ?>/assets/images/pattern.png') repeat; opacity: 0.1;"></div>
    <div class="container position-relative">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <h1 class="display-4 fw-bold mb-4 animate-fade-in">Get In Touch</h1>
                <p class="lead mb-4 animate-fade-in-delay">Have questions about our properties or services? We're here to help you find your dream home.</p>
                <div class="d-flex flex-wrap gap-3 animate-fade-in-delay-2">
                    <a href="tel:+919277121112" class="btn btn-light btn-lg">
                        <i class="fas fa-phone-alt me-2"></i>Call Now
                    </a>
                    <a href="https://wa.me/919277121112" class="btn btn-success btn-lg" target="_blank">
                        <i class="fab fa-whatsapp me-2"></i>WhatsApp
                    </a>
                </div>
                <div class="mt-4 animate-fade-in-delay-2">
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-map-marker-alt me-2"></i>
                        <span>1st floor, Singhariya Chauraha, Gorakhpur</span>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-envelope me-2"></i>
                        <span>info@apsdreamhome.com</span>
                    </div>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-clock me-2"></i>
                        <span>Mon - Sat: 9:00 AM - 7:00 PM</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card shadow-lg border-0 animate-fade-in">
                    <div class="card-header bg-white border-0 pt-4 px-4">
                        <h3 class="card-title mb-0"><i class="fas fa-paper-plane text-primary me-2"></i>Send Message</h3>
                        <p class="text-muted small mb-0">Fill out the form below and we'll get back to you shortly.</p>
                    </div>
                    <div class="card-body p-4">
                        <form method="POST" action="<?php echo BASE_URL; ?>/contact" class="needs-validation">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">Full Name *</label>
                                    <input type="text" id="name" name="name" class="form-control" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email Address *</label>
                                    <input type="email" id="email" name="email" class="form-control" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label">Phone Number *</label>
                                    <input type="tel" id="phone" name="phone" class="form-control" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="subject" class="form-label">Subject *</label>
                                    <select id="subject" name="subject" class="form-control" required>
                                        <option value="">Select Subject</option>
                                        <option value="Property Inquiry">Property Inquiry</option>
                                        <option value="Schedule Visit">Schedule Visit</option>
                                        <option value="General Query">General Query</option>
                                        <option value="Complaint">Complaint</option>
                                        <option value="Feedback">Feedback</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label for="message" class="form-label">Message *</label>
                                    <textarea id="message" name="message" class="form-control" rows="5" required></textarea>
                                </div>
                            </div>
                            <div class="text-center">
                                <button type="submit" class="btn btn-primary btn-lg w-100">
                                    <i class="fas fa-paper-plane me-2"></i>Send Message
                                </button>
                                <p class="small text-muted mt-2">
                                    <i class="fas fa-shield-alt me-1"></i>Your information is secure and will never be shared.
                                </p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <h2 class="mb-4">Frequently Asked Questions</h2>
                <div class="accordion" id="faqAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            What types of properties do you offer?
                        </h2>
                        <div class="accordion-content">
                            <p>We offer residential apartments, villas, commercial spaces, and plots in Gorakhpur, Lucknow, and across Uttar Pradesh.</p>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            How can I schedule a property visit?
                        </h2>
                        <div class="accordion-content">
                            <p>You can call us at +91 92771 21112 / +91 70074 44842 or fill out the contact form. Our team will get back to you to arrange a convenient time.</p>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            Do you provide home loan assistance?
                        </h2>
                        <div class="accordion-content">
                            <p>Yes, we have partnerships with leading banks and financial institutions to help you with home loan assistance and documentation.</p>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            Are your properties legally verified?
                        </h2>
                        <div class="accordion-content">
                            <p>Absolutely! All our properties undergo thorough legal verification to ensure they are free from disputes and have clear titles.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <h3 class="card-title">Office Locations</h3>
                        <div class="office-location">
                            <h4>Head Office - Gorakhpur</h4>
                            <address>
                                1st floor, Singhariya Chauraha, Kunraghat, Deoria Road<br>
                                Gorakhpur, UP - 273008<br>
                                Phone: +91 92771 21112 / +91 70074 44842<br>
                                Email: info@apsdreamhome.com
                            </address>
                        </div>
                        <div class="map-container mt-3">
                            <iframe
                                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3559.991144111075!2d83.30122467380973!3d26.840233976690463!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x399149002e8a386b%3A0x907b565a09c02435!2sSuryoday%20Colony%20developed%20by%20APS%20Dream%20Homes!5e0!3m2!1sen!2sin!4v1775289074035!5m2!1sen!2sin"
                                width="100%"
                                height="250"
                                style="border:0; border-radius: 8px;"
                                allowfullscreen
                                loading="lazy">
                            </iframe>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>