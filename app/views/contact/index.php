<?php
$page_title = $data['title'] ?? 'Contact Us - APS Dream Home';
$page_description = $data['description'] ?? 'Get in touch with APS Dream Home for your real estate needs. Visit our offices or call us.';
?>

<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h6 class="text-uppercase text-primary fw-bold">Get In Touch</h6>
            <h2 class="display-5 fw-bold">Contact APS Dream Home</h2>
            <div class="mx-auto bg-primary mt-3" style="height:4px;width:80px;border-radius:2px;"></div>
        </div>

        <div class="row g-4">
            <!-- Contact Form -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h5 class="card-title fw-bold mb-4">Send Us a Message</h5>
                        <form action="<?php echo BASE_URL; ?>contact" method="POST">
                            <div class="mb-3">
                                <label for="name" class="form-label">Full Name *</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address *</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone Number *</label>
                                <input type="tel" class="form-control" id="phone" name="phone" required>
                            </div>
                            <div class="mb-3">
                                <label for="subject" class="form-label">Subject</label>
                                <input type="text" class="form-control" id="subject" name="subject">
                            </div>
                            <div class="mb-3">
                                <label for="message" class="form-label">Message *</label>
                                <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary btn-lg w-100">Send Message</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h5 class="card-title fw-bold mb-4">Office Locations</h5>
                        <?php if (!empty($data['offices'])): ?>
                            <?php foreach ($data['offices'] as $office): ?>
                                <div class="mb-4">
                                    <h6 class="fw-bold text-primary"><?php echo htmlspecialchars($office->name); ?></h6>
                                    <p class="mb-2">
                                        <i class="fas fa-map-marker-alt text-primary me-2"></i>
                                        <?php echo htmlspecialchars($office->address); ?>
                                    </p>
                                    <p class="mb-2">
                                        <i class="fas fa-phone text-primary me-2"></i>
                                        <?php echo htmlspecialchars($office->phone); ?>
                                    </p>
                                    <p class="mb-2">
                                        <i class="fas fa-envelope text-primary me-2"></i>
                                        <?php echo htmlspecialchars($office->email); ?>
                                    </p>
                                    <p class="mb-0">
                                        <i class="fas fa-clock text-primary me-2"></i>
                                        <?php echo htmlspecialchars($office->timing); ?>
                                    </p>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted">Office information not available.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Quick Contact -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h5 class="card-title fw-bold mb-4">Quick Contact</h5>
                        <div class="d-flex gap-3 mb-3">
                            <a href="tel:+915512345678" class="btn btn-outline-primary flex-fill">
                                <i class="fas fa-phone me-2"></i>Call Us
                            </a>
                            <a href="mailto:info@apsdreamhome.com" class="btn btn-outline-primary flex-fill">
                                <i class="fas fa-envelope me-2"></i>Email
                            </a>
                        </div>
                        <div class="d-flex gap-3">
                            <a href="https://wa.me/915512345678" class="btn btn-success flex-fill">
                                <i class="fab fa-whatsapp me-2"></i>WhatsApp
                            </a>
                            <a href="#" class="btn btn-outline-primary flex-fill">
                                <i class="fas fa-map me-2"></i>Directions
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Map Section -->
<section class="py-5 bg-white">
    <div class="container">
        <div class="text-center mb-5">
            <h6 class="text-uppercase text-primary fw-bold">Find Us</h6>
            <h2 class="display-5 fw-bold">Our Locations</h2>
            <div class="mx-auto bg-primary mt-3" style="height:4px;width:80px;border-radius:2px;"></div>
        </div>

        <div class="row g-4">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-0">
                        <div class="bg-light rounded-top p-3">
                            <h6 class="fw-bold mb-0">Head Office - Gorakhpur</h6>
                        </div>
                        <div class="ratio ratio-16x9">
                            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3570.123456789!2d83.37654321!3d26.7654321!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMjbCsDQ1JzU5LjAiTiA4M8KwMjInMzUuMiJF!5e0!3m2!1sen!2sin!4v1234567890"
                                    style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-0">
                        <div class="bg-light rounded-top p-3">
                            <h6 class="fw-bold mb-0">Lucknow Branch</h6>
                        </div>
                        <div class="ratio ratio-16x9">
                            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3570.987654321!2d80.94654321!3d26.8454321!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMjbCsDQ1JzU5LjAiTiA4M8KwNTYnMzUuMiJF!5e0!3m2!1sen!2sin!4v1234567890"
                                    style="border:0;" allowfullscreen="" loading="lazy"></iframe>
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
        <div class="text-center mb-5">
            <h6 class="text-uppercase text-primary fw-bold">FAQ</h6>
            <h2 class="display-5 fw-bold">Frequently Asked Questions</h2>
            <div class="mx-auto bg-primary mt-3" style="height:4px;width:80px;border-radius:2px;"></div>
        </div>

        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="accordion" id="faqAccordion">
                    <div class="accordion-item border-0 shadow-sm mb-3">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                Do you provide home loan assistance?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Yes, we provide complete assistance with home loans from major banks and financial institutions. Our team will help you with the entire process.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item border-0 shadow-sm mb-3">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                Are your properties legally verified?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Absolutely! All our properties undergo thorough legal verification to ensure they are free from any disputes or legal complications.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item border-0 shadow-sm mb-3">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                Do you charge any brokerage fees?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                We believe in transparent pricing. Our fee structure is clearly communicated upfront with no hidden charges.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>