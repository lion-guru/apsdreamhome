<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'Support' ?> | APS Dream Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root { --primary: #2c3e50; }
        body { font-family: 'Segoe UI', sans-serif; }
        .support-hero {
            background: linear-gradient(rgba(44,62,80,0.9), rgba(44,62,80,0.9)),
                        url('https://images.unsplash.com/photo-1423666639041-f56000c27a9a?w=1920');
            background-size: cover;
            padding: 100px 0;
            color: white;
        }
        .contact-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            padding: 30px;
            height: 100%;
        }
        .support-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #3498db, #2c3e50);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: white;
            font-size: 28px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <nav class="navbar navbar-expand-lg navbar-dark" style="background: var(--primary);">
        <div class="container">
            <a class="navbar-brand fw-bold" href="<?= BASE_URL ?>">
                <i class="fas fa-home me-2"></i>APS Dream Home
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/properties">Properties</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/about">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/contact">Contact</a></li>
                    <li class="nav-item"><a class="nav-link active" href="<?= BASE_URL ?>/support">Support</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero -->
    <section class="support-hero text-center">
        <div class="container">
            <h1 class="display-4 fw-bold mb-3">Customer Support</h1>
            <p class="lead">We're here to help you with any questions or concerns</p>
        </div>
    </section>

    <!-- Support Options -->
    <section class="py-5">
        <div class="container">
            <div class="row g-4">
                <!-- Phone Support -->
                <div class="col-md-4">
                    <div class="contact-card text-center">
                        <div class="support-icon">
                            <i class="fas fa-phone"></i>
                        </div>
                        <h4>Phone Support</h4>
                        <p class="text-muted mb-3">Mon-Sat: 9AM - 6PM</p>
                        <h5 class="text-primary">+91 98765 43210</h5>
                        <p class="small text-muted mt-3">For immediate assistance, call us directly</p>
                    </div>
                </div>

                <!-- Email Support -->
                <div class="col-md-4">
                    <div class="contact-card text-center">
                        <div class="support-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <h4>Email Support</h4>
                        <p class="text-muted mb-3">24/7 Response</p>
                        <h5 class="text-primary">support@apsdreamhome.com</h5>
                        <p class="small text-muted mt-3">We'll respond within 24 hours</p>
                    </div>
                </div>

                <!-- WhatsApp -->
                <div class="col-md-4">
                    <div class="contact-card text-center">
                        <div class="support-icon">
                            <i class="fab fa-whatsapp"></i>
                        </div>
                        <h4>WhatsApp</h4>
                        <p class="text-muted mb-3">Quick Responses</p>
                        <h5 class="text-primary">+91 98765 43210</h5>
                        <p class="small text-muted mt-3">Chat with us on WhatsApp</p>
                    </div>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="row mt-5">
                <div class="col-lg-8 mx-auto">
                    <div class="contact-card">
                        <h4 class="text-center mb-4">
                            <i class="fas fa-headset me-2"></i>Submit a Support Ticket
                        </h4>
                        <form id="supportForm">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Your Name</label>
                                    <input type="text" name="name" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email Address</label>
                                    <input type="email" name="email" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Phone Number</label>
                                    <input type="tel" name="phone" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Subject</label>
                                    <select name="subject" class="form-select" required>
                                        <option value="">Select a topic</option>
                                        <option value="booking">Booking Inquiry</option>
                                        <option value="payment">Payment Issue</option>
                                        <option value="property">Property Details</option>
                                        <option value="complaint">File a Complaint</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Message</label>
                                    <textarea name="message" class="form-control" rows="5" required 
                                              placeholder="Tell us how we can help you..."></textarea>
                                </div>
                                <div class="col-12 text-center">
                                    <button type="submit" class="btn btn-primary btn-lg px-5">
                                        <i class="fas fa-paper-plane me-2"></i>Submit Ticket
                                    </button>
                                </div>
                            </div>
                        </form>
                        <div id="formResponse" class="mt-3" style="display: none;"></div>
                    </div>
                </div>
            </div>

            <!-- FAQ Section -->
            <div class="row mt-5">
                <div class="col-lg-10 mx-auto">
                    <h3 class="text-center mb-4">Frequently Asked Questions</h3>
                    <div class="accordion" id="faqAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                    How do I book a property?
                                </button>
                            </h2>
                            <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    You can book a property by visiting our office, calling us, or submitting an inquiry through our website. Our team will guide you through the entire process.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                    What payment options are available?
                                </button>
                            </h2>
                            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    We offer multiple payment options including bank transfer, cheque, and cash. EMI facilities are also available through leading banks.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                    Do you provide home loan assistance?
                                </button>
                            </h2>
                            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Yes, we have partnerships with multiple banks and financial institutions to help you get the best home loan rates with minimal documentation.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer style="background: var(--primary); color: white; padding: 40px 0;">
        <div class="container text-center">
            <p class="mb-0">&copy; 2024 APS Dream Home. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('supportForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const response = document.getElementById('formResponse');
            
            response.innerHTML = '<div class="alert alert-info"><i class="fas fa-spinner fa-spin me-2"></i>Sending your message...</div>';
            response.style.display = 'block';
            
            // Simulate form submission
            setTimeout(() => {
                response.innerHTML = '<div class="alert alert-success"><i class="fas fa-check-circle me-2"></i>Thank you! Your support ticket has been submitted. We will contact you soon.</div>';
                this.reset();
            }, 1500);
        });
    </script>
</body>
</html>
