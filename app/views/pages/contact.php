<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $data['title']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 80px 0;
        }
        .contact-section {
            padding: 80px 0;
        }
        .contact-card {
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }
        .info-card {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            transition: transform 0.3s ease;
        }
        .info-card:hover {
            transform: translateY(-5px);
        }
        .info-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(45deg, #667eea, #764ba2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin: 0 auto 20px;
            font-size: 1.5rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-primary {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: 600;
        }
        .btn-primary:hover {
            background: linear-gradient(45deg, #764ba2, #667eea);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .map-container {
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 25px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <?php include '../app/views/includes/header.php'; ?>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center">
                    <h1 class="display-4 fw-bold mb-4">Get In Touch</h1>
                    <p class="lead mb-4">
                        Have questions about our properties or services? We'd love to hear from you.
                        Contact us today and let's discuss how we can help you find your dream home.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="contact-section">
        <div class="container">
            <div class="row">
                <!-- Contact Information -->
                <div class="col-lg-4">
                    <div class="contact-card">
                        <h3 class="mb-4">Contact Information</h3>

                        <div class="info-card mb-4">
                            <div class="info-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <h5>Visit Our Office</h5>
                            <p class="text-muted mb-0">
                                123, Kunraghat Main Road<br>
                                Near Railway Station<br>
                                Gorakhpur, UP - 273008
                            </p>
                        </div>

                        <div class="info-card mb-4">
                            <div class="info-icon">
                                <i class="fas fa-phone"></i>
                            </div>
                            <h5>Call Us</h5>
                            <p class="text-muted mb-0">
                                <a href="tel:+919554000001" class="text-decoration-none">+91-9554000001</a><br>
                                <small>Mon-Sat: 9:30 AM - 7:00 PM</small>
                            </p>
                        </div>

                        <div class="info-card mb-4">
                            <div class="info-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <h5>Email Us</h5>
                            <p class="text-muted mb-0">
                                <a href="mailto:info@apsdreamhomes.com" class="text-decoration-none">info@apsdreamhomes.com</a><br>
                                <small>We'll respond within 24 hours</small>
                            </p>
                        </div>

                        <div class="info-card">
                            <div class="info-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <h5>Business Hours</h5>
                            <p class="text-muted mb-0">
                                Monday - Saturday: 9:30 AM - 7:00 PM<br>
                                Sunday: 10:00 AM - 5:00 PM<br>
                                <small>Holidays: By appointment only</small>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Contact Form -->
                <div class="col-lg-8">
                    <div class="contact-card">
                        <h3 class="mb-4">Send us a Message</h3>

                        <?php if (isset($_SESSION['success'])): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i><?php echo $_SESSION['success']; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                <?php unset($_SESSION['success']); ?>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i><?php echo $_SESSION['error']; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                <?php unset($_SESSION['error']); ?>
                            </div>
                        <?php endif; ?>

                        <form action="/contact" method="POST" id="contactForm">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">Full Name *</label>
                                    <input type="text" class="form-control" id="name" name="name"
                                           value="<?php echo htmlspecialchars($_SESSION['form_data']['name'] ?? ''); ?>"
                                           required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email Address *</label>
                                    <input type="email" class="form-control" id="email" name="email"
                                           value="<?php echo htmlspecialchars($_SESSION['form_data']['email'] ?? ''); ?>"
                                           required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" id="phone" name="phone"
                                           value="<?php echo htmlspecialchars($_SESSION['form_data']['phone'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="subject" class="form-label">Subject *</label>
                                    <select class="form-control" id="subject" name="subject" required>
                                        <option value="">Select a subject</option>
                                        <option value="general" <?php echo ($_SESSION['form_data']['subject'] ?? '') == 'general' ? 'selected' : ''; ?>>General Inquiry</option>
                                        <option value="property" <?php echo ($_SESSION['form_data']['subject'] ?? '') == 'property' ? 'selected' : ''; ?>>Property Information</option>
                                        <option value="booking" <?php echo ($_SESSION['form_data']['subject'] ?? '') == 'booking' ? 'selected' : ''; ?>>Booking Inquiry</option>
                                        <option value="complaint" <?php echo ($_SESSION['form_data']['subject'] ?? '') == 'complaint' ? 'selected' : ''; ?>>Complaint</option>
                                        <option value="partnership" <?php echo ($_SESSION['form_data']['subject'] ?? '') == 'partnership' ? 'selected' : ''; ?>>Partnership</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="message" class="form-label">Message *</label>
                                <textarea class="form-control" id="message" name="message" rows="6"
                                          required><?php echo htmlspecialchars($_SESSION['form_data']['message'] ?? ''); ?></textarea>
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="newsletter" name="newsletter">
                                    <label class="form-check-label" for="newsletter">
                                        Subscribe to our newsletter for latest property updates and exclusive deals
                                    </label>
                                </div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-paper-plane me-2"></i>Send Message
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Map Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h3 class="text-center mb-4">Find Us</h3>
                    <div class="map-container">
                        <!-- Google Maps Embed -->
                        <iframe
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3558.6!2d83.3736!3d26.7606!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3991446a7c6b0d0f%3A0x8f7c4b3e2d1a0f5c!2sGorakhpur%20Railway%20Station!5e0!3m2!1sen!2sin!4v1623456789012!5m2!1sen!2sin"
                            width="100%"
                            height="400"
                            style="border:0;"
                            allowfullscreen=""
                            loading="lazy">
                        </iframe>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include '../app/views/includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation
        document.getElementById('contactForm').addEventListener('submit', function(e) {
            const name = document.getElementById('name').value.trim();
            const email = document.getElementById('email').value.trim();
            const message = document.getElementById('message').value.trim();

            if (name === '' || email === '' || message === '') {
                e.preventDefault();
                alert('Please fill in all required fields.');
                return false;
            }

            // Basic email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                e.preventDefault();
                alert('Please enter a valid email address.');
                return false;
            }

            return true;
        });

        // Clear form data from session after successful submission
        <?php if (isset($_SESSION['form_data'])): ?>
            <?php unset($_SESSION['form_data']); ?>
        <?php endif; ?>
    </script>
</body>
</html>
