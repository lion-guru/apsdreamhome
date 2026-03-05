<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - APS Dream Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <?php include '../app/views/includes/header.php'; ?>

    <div class="container mt-5">
        <div class="row g-4">
            <div class="col-lg-6">
                <h1 class="h3 mb-3">Get in Touch</h1>
                <p class="text-muted">Have questions about a property or need help with buying/selling? Send us a message and we’ll respond shortly.</p>

                <form action="/contact" method="POST" class="mt-4">
                    <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                    <div class="mb-3">
                        <label for="name" class="form-label">Your Name</label>
                        <input type="text" id="name" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" name="email" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="tel" id="phone" name="phone" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="subject" class="form-label">Subject</label>
                            <input type="text" id="subject" name="subject" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="message" class="form-label">Message</label>
                        <textarea id="message" name="message" rows="5" class="form-control" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Send Message</button>
                </form>
            </div>
            <div class="col-lg-6">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h2 class="h5">Contact Information</h2>
                        <p class="text-muted">Reach us directly using the details below.</p>
                        <ul class="list-unstyled mt-3">
                            <li class="mb-2"><strong>Address:</strong> 123 Dream Street, City, Country</li>
                            <li class="mb-2"><strong>Phone:</strong> <a href="tel:+1234567890">+1 (234) 567-890</a></li>
                            <li class="mb-2"><strong>Email:</strong> <a href="mailto:info@apsdreamhome.com">info@apsdreamhome.com</a></li>
                            <li class="mb-2"><strong>Working Hours:</strong> Mon - Sat, 9:00 AM - 6:00 PM</li>
                        </ul>
                        <div class="ratio ratio-16x9 mt-3">
                            <iframe src="https://maps.google.com/maps?q=mumbai&t=&z=13&ie=UTF8&iwloc=&output=embed" allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../app/views/includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<form id="interiorDesignContactForm" class="contact-form" data-aos="fade-up">
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="name">Your Name</label>
                            <input type="text" 
                                   name="name" 
                                   class="form-control" 
                                   id="name" 
                                   required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="email">Your Email</label>
                            <input type="email" 
                                   class="form-control" 
                                   name="email" 
                                   id="email" 
                                   required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" 
                                   class="form-control" 
                                   name="phone" 
                                   id="phone" 
                                   required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="projectType">Project Type</label>
                            <select class="form-control" 
                                    name="projectType" 
                                    id="projectType" 
                                    required>
                                <option value="">Select Project Type</option>
                                <option value="Residential">Residential</option>
                                <option value="Commercial">Commercial</option>
                                <option value="Office">Office</option>
                                <option value="Retail">Retail</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="budget">Estimated Budget</label>
                        <select class="form-control" 
                                name="budget" 
                                id="budget" 
                                required>
                            <option value="">Select Budget Range</option>
                            <option value="Below $10,000">Below $10,000</option>
                            <option value="$10,000 - $25,000">$10,000 - $25,000</option>
                            <option value="$25,000 - $50,000">$25,000 - $50,000</option>
                            <option value="$50,000 - $100,000">$50,000 - $100,000</option>
                            <option value="Above $100,000">Above $100,000</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="message">Project Details</label>
                        <textarea class="form-control" 
                                  name="message" 
                                  id="message" 
                                  rows="5" 
                                  required></textarea>
                    </div>
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary">Schedule Consultation</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section

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
</section

<section class="microsite-section py-5" id="microsite-enquiry">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-6">
                <h2 class="section-title">Plan Your Visit</h2>
                <p class="section-subtitle">Share your details and our sales team will reach out with availability, pricing, and site tour slots.</p>
                <ul class="list-unstyled microsite-contact-list">
                    <?php if (!empty($phone)): ?>
                    <li><i class="fa-solid fa-phone me-2"></i> <a href="tel:<?php echo h($phone); ?>"><?php echo h($phone); ?></a></li>
                    <?php endif; ?>
                    <?php if (!empty($email)): ?>
                    <li><i class="fa-solid fa-envelope me-2"></i> <a href="mailto:<?php echo h($email); ?>"><?php echo h($email); ?></a></li>
                    <?php endif; ?>
                    <?php if (!empty($whatsapp)): ?>
                    <li><i class="fa-brands fa-whatsapp me-2 text-success"></i> <a href="<?php echo h($whatsapp); ?>" target="_blank" rel="noopener">Chat on WhatsApp</a></li>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <form id="microsite-enquiry-form" data-endpoint="<?php echo h($enquiryEndpoint); ?>">
                            <input type="hidden" name="project_code" value="<?php echo h($projectCode); ?>">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" name="name" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Phone Number</label>
                                    <input type="tel" name="phone" class="form-control" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Message</label>
                                    <textarea name="message" class="form-control" rows="4" placeholder="<?php echo h($defaultSubject); ?>" required></textarea>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <span class="submit-text">Submit Enquiry</span>
                                        <span class="spinner-border spinner-border-sm ms-2 d-none" role="status" aria-hidden="true"></span>
                                    </button>
                                </div>
                            </div>
                        </form>
                        <div class="alert alert-success d-none mt-3" id="microsite-enquiry-success">
                            Thank you! Our team will contact you shortly.
                        </div>
                        <div class="alert alert-danger d-none mt-3" id="microsite-enquiry-error">
                            Something went wrong. Please try again later.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section
</body>
</html>
