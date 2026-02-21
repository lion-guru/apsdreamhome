<?php

/**
 * Contact Page Template
 * Displays contact information and contact form
 */

// Contact info is passed from PageController
$contact = $contact_info ?? [
    'phone' => '+91-1234567890',
    'email' => 'info@apsdreamhome.com',
    'address' => '123 Main Street, Gorakhpur, Uttar Pradesh - 273001',
    'hours' => 'Mon - Sat: 9:00 AM - 8:00 PM, Sun: 10:00 AM - 6:00 PM'
];
?>

<section class="contact-hero py-5">
    <div class="container py-4">
        <div class="row align-items-center g-4">
            <div class="col-lg-6 text-white">
                <span class="badge bg-primary-subtle text-primary-emphasis rounded-pill px-3 py-2 mb-3">We’re here for you</span>
                <h1 class="display-5 fw-bold mb-3">Let’s start a conversation</h1>
                <p class="lead text-white-50 mb-4">Speak with our property advisors to get personalised recommendations, arrange site visits or clarify any questions.</p>
                <div class="d-flex flex-column gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <span class="contact-icon-circle bg-primary-subtle text-primary-emphasis rounded-circle d-inline-flex align-items-center justify-content-center">
                            <i class="fas fa-phone"></i>
                        </span>
                        <div>
                            <p class="mb-0 small text-white-50">Call us anytime</p>
                            <a href="tel:<?php echo preg_replace('/[^0-9+]/', '', $contact['phone']); ?>" class="fs-5 text-white text-decoration-none fw-semibold"><?php echo $contact['phone']; ?></a>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <span class="contact-icon-circle">
                            <i class="fas fa-envelope"></i>
                        </span>
                        <div>
                            <p class="mb-0 small text-white-50">Drop us a line</p>
                            <a href="mailto:<?php echo $contact['email']; ?>" class="fs-5 text-white text-decoration-none fw-semibold"><?php echo $contact['email']; ?></a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                    <div class="card-body p-4 p-md-5">
                        <span class="badge bg-primary-subtle text-primary-emphasis rounded-pill px-3 py-2 mb-3">Quick response</span>
                        <h2 class="h4 fw-semibold mb-3">Tell us about your requirements</h2>
                        <form action="<?php echo BASE_URL; ?>contact/send" method="POST" class="row g-3">
                            <div class="col-md-6">
                                <label for="first_name" class="form-label fw-semibold">First name</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" required>
                            </div>
                            <div class="col-md-6">
                                <label for="last_name" class="form-label fw-semibold">Last name</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" required>
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label fw-semibold">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="col-md-6">
                                <label for="phone" class="form-label fw-semibold">Phone</label>
                                <input type="tel" class="form-control" id="phone" name="phone" required>
                            </div>
                            <div class="col-12">
                                <label for="subject" class="form-label fw-semibold">Topic</label>
                                <select class="form-select" id="subject" name="subject" required>
                                    <option value="">Select a topic</option>
                                    <option value="property">Property enquiry</option>
                                    <option value="site_visit">Schedule a site visit</option>
                                    <option value="support">Customer support</option>
                                    <option value="partnership">Partnership</option>
                                    <option value="other">Something else</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label for="message" class="form-label fw-semibold">How can we help?</label>
                                <textarea class="form-control" id="message" name="message" rows="5" placeholder="Share your requirements or questions" required></textarea>
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="newsletter" name="newsletter">
                                    <label class="form-check-label" for="newsletter">Send me curated property updates and launches</label>
                                </div>
                            </div>
                            <div class="col-12 d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">Send message</button>
                                <p class="text-muted small mb-0 text-center">We aim to respond within 30 minutes during business hours.</p>
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
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body">
                        <h5 class="fw-semibold mb-2"><i class="fas fa-map-marker-alt text-primary me-2"></i>Visit our office</h5>
                        <p class="text-secondary mb-0"><?php echo $contact['address']; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body">
                        <h5 class="fw-semibold mb-2"><i class="fas fa-clock text-primary me-2"></i>Business hours</h5>
                        <p class="text-secondary mb-0"><?php echo $contact['hours']; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body">
                        <h5 class="fw-semibold mb-3">Stay connected</h5>
                        <div class="d-flex gap-2">
                            <a href="#" class="btn btn-outline-secondary btn-sm rounded-circle"><i class="fab fa-facebook-f"></i></a>
                            <a href="#" class="btn btn-outline-secondary btn-sm rounded-circle"><i class="fab fa-twitter"></i></a>
                            <a href="#" class="btn btn-outline-secondary btn-sm rounded-circle"><i class="fab fa-instagram"></i></a>
                            <a href="#" class="btn btn-outline-secondary btn-sm rounded-circle"><i class="fab fa-linkedin-in"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5 contact-cta-section">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-lg-8 text-white">
                <h2 class="fw-bold mb-2">Still have questions?</h2>
                <p class="text-white-50 mb-0">Our support team is available 7 days a week to guide you through financing, paperwork and property visits.</p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <a href="tel:<?php echo preg_replace('/[^0-9+]/', '', $contact['phone']); ?>" class="btn btn-warning btn-lg me-lg-2 mb-2 mb-lg-0">
                    <i class="fas fa-phone-alt me-2"></i>Call now
                </a>
                <a href="<?php echo BASE_URL; ?>about" class="btn btn-outline-light btn-lg">
                    <i class="fas fa-info-circle me-2"></i>Know more
                </a>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../layouts/footer.php'; ?>