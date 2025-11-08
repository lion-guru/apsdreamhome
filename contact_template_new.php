<?php
/**
 * Enhanced Contact Page - APS Dream Homes Pvt Ltd
 * Professional Contact Form and Information
 */

require_once 'includes/enhanced_universal_template.php';

// Database connection with error handling
try {
    define('INCLUDED_FROM_MAIN', true);
    require_once 'includes/db_connection.php';

    // Initialize variables
    $company_name = 'APS Dream Homes Pvt Ltd';
    $company_phone = '+91-9554000001';
    $company_email = 'info@apsdreamhomes.com';
    $company_address = '123, Kunraghat Main Road, Near Railway Station, Gorakhpur, UP - 273008';

    // Database operations with fallback
    if ($pdo) {
        try {
            // Fetch company settings
            $stmt = $pdo->query("SELECT * FROM company_settings WHERE id = 1");
            if ($company_data = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $company_name = $company_data['company_name'];
                $company_phone = $company_data['phone'];
                $company_email = $company_data['email'];
                $company_address = $company_data['address'];
            }
        } catch (Exception $e) {
            // Database error - use fallback data
            error_log("Database query error: " . $e->getMessage());
        }
    }

    // Build contact page content
    $content = '
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center">
                    <h1 class="display-4 fw-bold mb-4">Contact Us</h1>
                    <p class="lead mb-4">Get in touch with ' . htmlspecialchars($company_name) . ' for all your real estate needs. We\'re here to help you find your dream home.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Information Section -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <h2 class="section-title text-center mb-5">Get In Touch</h2>
                    <div class="row g-4">
                        <div class="col-md-4 mb-4">
                            <div class="card h-100 shadow-custom text-center">
                                <div class="card-body p-4">
                                    <div class="mb-3">
                                        <i class="fas fa-map-marker-alt fa-3x text-primary"></i>
                                    </div>
                                    <h5>Visit Our Office</h5>
                                    <p class="mb-0">' . nl2br(htmlspecialchars($company_address)) . '</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4">
                            <div class="card h-100 shadow-custom text-center">
                                <div class="card-body p-4">
                                    <div class="mb-3">
                                        <i class="fas fa-phone-alt fa-3x text-success"></i>
                                    </div>
                                    <h5>Call Us</h5>
                                    <p class="mb-0">
                                        <a href="tel:' . htmlspecialchars($company_phone) . '" class="text-decoration-none">' . htmlspecialchars($company_phone) . '</a><br>
                                        <small class="text-muted">Mon-Sat: 9:30 AM - 7:00 PM</small>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4">
                            <div class="card h-100 shadow-custom text-center">
                                <div class="card-body p-4">
                                    <div class="mb-3">
                                        <i class="fas fa-envelope fa-3x text-info"></i>
                                    </div>
                                    <h5>Email Us</h5>
                                    <p class="mb-0">
                                        <a href="mailto:' . htmlspecialchars($company_email) . '" class="text-decoration-none">' . htmlspecialchars($company_email) . '</a><br>
                                        <small class="text-muted">We reply within 24 hours</small>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Form Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card shadow-custom">
                        <div class="card-body p-5">
                            <h3 class="text-center mb-4">Send us a Message</h3>
                            <form id="contactForm" class="needs-validation" novalidate>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="name" class="form-label">Full Name *</label>
                                        <input type="text" class="form-control" id="name" name="name" required>
                                        <div class="invalid-feedback">Please enter your full name.</div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label">Email Address *</label>
                                        <input type="email" class="form-control" id="email" name="email" required>
                                        <div class="invalid-feedback">Please enter a valid email address.</div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="phone" class="form-label">Phone Number *</label>
                                        <input type="tel" class="form-control" id="phone" name="phone" required>
                                        <div class="invalid-feedback">Please enter your phone number.</div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="subject" class="form-label">Subject *</label>
                                        <select class="form-control" id="subject" name="subject" required>
                                            <option value="">Select Subject</option>
                                            <option value="Property Inquiry">Property Inquiry</option>
                                            <option value="Site Visit">Site Visit</option>
                                            <option value="Investment">Investment Opportunity</option>
                                            <option value="Partnership">Partnership</option>
                                            <option value="Other">Other</option>
                                        </select>
                                        <div class="invalid-feedback">Please select a subject.</div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="message" class="form-label">Message *</label>
                                    <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                                    <div class="invalid-feedback">Please enter your message.</div>
                                </div>
                                <div class="text-center">
                                    <button type="submit" class="btn btn-primary btn-lg px-5">
                                        <i class="fas fa-paper-plane me-2"></i>Send Message
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Service Areas Section -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <h2 class="section-title text-center mb-5">Service Areas</h2>
                    <div class="row g-4">
                        <div class="col-md-3 mb-4">
                            <div class="card h-100 shadow-custom text-center">
                                <div class="card-body p-4">
                                    <h5>Gorakhpur</h5>
                                    <p class="mb-0">Main office and primary service area</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-4">
                            <div class="card h-100 shadow-custom text-center">
                                <div class="card-body p-4">
                                    <h5>Lucknow</h5>
                                    <p class="mb-0">Expanding services in state capital</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-4">
                            <div class="card h-100 shadow-custom text-center">
                                <div class="card-body p-4">
                                    <h5>Varanasi</h5>
                                    <p class="mb-0">Heritage city development projects</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-4">
                            <div class="card h-100 shadow-custom text-center">
                                <div class="card-body p-4">
                                    <h5>Kanpur</h5>
                                    <p class="mb-0">Industrial city real estate services</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <h2 class="section-title text-center mb-5">Frequently Asked Questions</h2>
                    <div class="accordion" id="faqAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="faq1">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse1">
                                    How can I schedule a site visit?
                                </button>
                            </h2>
                            <div id="collapse1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    You can schedule a site visit by calling us at ' . htmlspecialchars($company_phone) . ' or filling out the contact form above. Our team will get back to you within 24 hours to arrange a convenient time.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="faq2">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse2">
                                    What documents do I need for property purchase?
                                </button>
                            </h2>
                            <div id="collapse2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    For property purchase, you\'ll need: Aadhar Card, PAN Card, passport-sized photographs, address proof, and income proof. Our team will guide you through the complete documentation process.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="faq3">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse3">
                                    Do you offer home loans assistance?
                                </button>
                            </h2>
                            <div id="collapse3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Yes, we have tie-ups with major banks and financial institutions. Our team can help you get the best home loan rates and assist with the complete loan application process.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="faq4">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse4">
                                    What is the booking amount for properties?
                                </button>
                            </h2>
                            <div id="collapse4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    The booking amount varies by property type and location. Typically, it ranges from ₹50,000 to ₹2,00,000. Contact our sales team for specific property booking amounts.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>';

    // Add JavaScript for form handling
    $scripts = '
    <script>
        // Contact form validation and submission
        document.getElementById("contactForm").addEventListener("submit", function(e) {
            e.preventDefault();

            const form = this;
            const submitBtn = form.querySelector("button[type=\'submit\']");
            const originalText = submitBtn.innerHTML;

            // Validate form
            if (!form.checkValidity()) {
                form.classList.add("was-validated");
                return;
            }

            // Show loading state
            submitBtn.innerHTML = "<i class=\'fas fa-spinner fa-spin me-2\'></i>Sending...";
            submitBtn.disabled = true;

            // Collect form data
            const formData = new FormData(form);

            // Simulate form submission (replace with actual API call)
            setTimeout(() => {
                alert("Thank you for your message! We will get back to you within 24 hours.");
                form.reset();
                form.classList.remove("was-validated");

                // Reset button
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }, 2000);
        });

        // Phone number formatting
        document.getElementById("phone").addEventListener("input", function(e) {
            let value = e.target.value.replace(/\D/g, "");
            if (value.length > 0) {
                if (value.length <= 3) {
                    value = value;
                } else if (value.length <= 6) {
                    value = value.slice(0, 3) + "-" + value.slice(3);
                } else {
                    value = value.slice(0, 3) + "-" + value.slice(3, 6) + "-" + value.slice(6, 10);
                }
            }
            e.target.value = value;
        });
    </script>';

    // Render page using enhanced template
    page($content, 'Contact Us - ' . htmlspecialchars($company_name), $scripts);

} catch (Exception $e) {
    // Error handling with fallback content
    $error_content = '
    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="alert alert-warning">
                        <h4>Contact APS Dream Homes Pvt Ltd</h4>
                        <p>Get in touch with us for all your real estate needs.</p>
                        <div class="row g-4 mt-4">
                            <div class="col-md-4 text-center">
                                <i class="fas fa-phone-alt fa-2x text-primary mb-3"></i>
                                <h5>Call Us</h5>
                                <p class="mb-0">+91-9554000001</p>
                            </div>
                            <div class="col-md-4 text-center">
                                <i class="fas fa-envelope fa-2x text-success mb-3"></i>
                                <h5>Email Us</h5>
                                <p class="mb-0">info@apsdreamhomes.com</p>
                            </div>
                            <div class="col-md-4 text-center">
                                <i class="fas fa-map-marker-alt fa-2x text-info mb-3"></i>
                                <h5>Visit Us</h5>
                                <p class="mb-0">123, Kunraghat Main Road<br>Gorakhpur, UP - 273008</p>
                            </div>
                        </div>
                        <div class="mt-4">
                            <a href="index_template.php" class="btn btn-primary me-2">Homepage</a>
                            <a href="about_template.php" class="btn btn-success me-2">About Us</a>
                            <a href="properties_template.php" class="btn btn-info">Properties</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>';

    page($error_content, 'Contact Us - APS Dream Homes Pvt Ltd');
}
?>
