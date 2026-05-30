<?php
// Contact Page - APS Dream Home - Enhanced UI/UX
$contactSuccess = $contact_success ?? false;
$contactError = $contact_error ?? '';
?>

<?php if ($contactSuccess): ?>
    <div class="container mt-4">
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>Thank you! Your message has been sent. We'll get back to you within 24 hours.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
<?php endif; ?>
<?php if ($contactError): ?>
    <div class="container mt-4">
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($contactError); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
<?php endif; ?>

<!-- Hero Section -->
<section class="py-5 bg-gradient-primary text-white position-relative overflow-hidden">
    <div class="position-absolute top-0 start-0 w-100 h-100" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 50%, #667eea 100%);"></div>
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
            </div>
            <div class="col-lg-6">
                <div class="card shadow-lg border-0">
                    <div class="card-header bg-primary text-white text-center py-3">
                        <h4 class="mb-0"><i class="fas fa-envelope me-2"></i>Send Us a Message</h4>
                    </div>
                    <div class="card-body p-4">
                        <form action="<?php echo BASE_URL; ?>/contact" method="POST">
                            <div class="mb-3">
                                <label for="name" class="form-label fw-bold">Your Name *</label>
                                <input type="text" name="name" id="name" class="form-control" placeholder="Enter your full name" required>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label fw-bold">Email *</label>
                                    <input type="email" name="email" id="email" class="form-control" placeholder="your@email.com" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label fw-bold">Phone *</label>
                                    <input type="tel" name="phone" id="phone" class="form-control" placeholder="+91 XXXXXXXXXX" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="subject" class="form-label fw-bold">Subject *</label>
                                <select name="subject" id="subject" class="form-select" required>
                                    <option value="">Select a subject...</option>
                                    <option value="buy">I want to Buy a Property</option>
                                    <option value="sell">I want to Sell my Property</option>
                                    <option value="rent">I want to Rent a Property</option>
                                    <option value="loan">Home Loan Assistance</option>
                                    <option value="legal">Legal Services</option>
                                    <option value="interior">Interior Design</option>
                                    <option value="general">General Inquiry</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="message" class="form-label fw-bold">Message *</label>
                                <textarea name="message" id="message" class="form-control" rows="4" placeholder="Tell us about your requirement..." required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary btn-lg w-100">
                                <i class="fas fa-paper-plane me-2"></i>Send Message
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
(function() {
    const params = ['utm_source','utm_medium','utm_campaign','utm_term','utm_content'];
    params.forEach(p => {
        const val = new URLSearchParams(window.location.search).get(p);
        if (val) {
            document.querySelectorAll(`input[name="${p}"]`).forEach(el => el.value = val);
            try { sessionStorage.setItem(p, val); } catch(e) {}
        }
    });
})();
</script>

<?php if (!empty($pageContent)): ?>
<section class="py-5 bg-white">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="cms-content p-4"><?php echo $pageContent; ?></div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <h2 class="mb-4">Frequently Asked Questions</h2>
                <div class="accordion" id="faqAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="faq1">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse1">
                                What types of properties do you offer?
                            </button>
                        </h2>
                        <div id="faqCollapse1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                We offer residential apartments, villas, commercial spaces, and plots in Gorakhpur, Lucknow, and across Uttar Pradesh.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="faq2">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse2">
                                How can I schedule a property visit?
                            </button>
                        </h2>
                        <div id="faqCollapse2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                You can call us at +91 92771 21112 / +91 70074 44842 or fill out the contact form. Our team will get back to you to arrange a convenient time.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="faq3">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse3">
                                Do you provide home loan assistance?
                            </button>
                        </h2>
                        <div id="faqCollapse3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Yes, we have partnerships with leading banks and financial institutions to help you with home loan assistance and documentation.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="faq4">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse4">
                                Are your properties legally verified?
                            </button>
                        </h2>
                        <div id="faqCollapse4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Absolutely! All our properties undergo thorough legal verification to ensure they are free from disputes and have clear titles.
                            </div>
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
