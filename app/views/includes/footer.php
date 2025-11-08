<?php
/**
 * APS Dream Home - Footer Include
 * This file now redirects to the unified footer system
 */

// Include the new unified footer
require_once __DIR__ . '/../layouts/footer_unified.php';
?>
        $company_address = '123, Kunraghat Main Road, Near Railway Station, Gorakhpur, UP - 273008';
    }
} catch (Throwable $e) {
    $dynamicFooterError = true;
    $footer_about = 'Leading real estate developer in Gorakhpur with 8+ years of excellence in property development and customer satisfaction.';
    $footer_contact = '123, Kunraghat Main Road, Near Railway Station, Gorakhpur, UP - 273008';
    $footer_social_links = [
        ['icon' => 'fab fa-facebook-f', 'url' => 'https://facebook.com/apsdreamhomes', 'title' => 'Facebook'],
        ['icon' => 'fab fa-instagram', 'url' => 'https://instagram.com/apsdreamhomes', 'title' => 'Instagram'],
        ['icon' => 'fab fa-linkedin-in', 'url' => 'https://linkedin.com/company/aps-dream-homes-pvt-ltd', 'title' => 'LinkedIn'],
        ['icon' => 'fab fa-youtube', 'url' => 'https://youtube.com/apsdreamhomes', 'title' => 'YouTube']
    ];
    $footer_copyright = 'APS Dream Homes Pvt Ltd. All rights reserved.';
    $contact_phone = '+91-9554000001';
    $contact_email = 'info@apsdreamhomes.com';
    $company_address = '123, Kunraghat Main Road, Near Railway Station, Gorakhpur, UP - 273008';
}
?>
    </div>

    <!-- Modern Footer -->
    <footer class="footer position-relative bg-dark text-white pt-5 pb-4" style="background: linear-gradient(135deg, #1a1e2e 0%, #16213e 100%);">
        <!-- Decorative gradient border -->
        <div class="footer-gradient-border"></div>

        <div class="container position-relative">
            <div class="row g-4">
                <!-- Company Info -->
                <div class="col-lg-4 mb-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="footer-about">
                        <h5 class="footer-title">
                            <i class="fas fa-home me-2"></i>APS Dream Homes Pvt Ltd
                        </h5>
                        <p class="footer-text"><?php echo htmlspecialchars($footer_about); ?></p>

                        <!-- Social Links -->
                        <div class="social-links mt-3">
                            <?php foreach ($footer_social_links as $social): ?>
                                <a href="<?php echo htmlspecialchars($social['url']); ?>"
                                   class="social-link"
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   title="<?php echo htmlspecialchars($social['title']); ?>">
                                    <i class="<?php echo htmlspecialchars($social['icon']); ?>"></i>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="col-lg-2 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="200">
                    <h5 class="footer-title">Quick Links</h5>
                    <ul class="footer-links">
                        <li><a href="/" class="footer-link"><i class="fas fa-chevron-right me-2"></i>Home</a></li>
                        <li><a href="/properties" class="footer-link"><i class="fas fa-chevron-right me-2"></i>Properties</a></li>
                        <li><a href="/about" class="footer-link"><i class="fas fa-chevron-right me-2"></i>About Us</a></li>
                        <li><a href="/contact" class="footer-link"><i class="fas fa-chevron-right me-2"></i>Contact</a></li>
                        <li><a href="/projects" class="footer-link"><i class="fas fa-chevron-right me-2"></i>Projects</a></li>
                    </ul>
                </div>

                <!-- Property Types -->
                <div class="col-lg-2 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="300">
                    <h5 class="footer-title">Property Types</h5>
                    <ul class="footer-links">
                        <li><a href="#" class="footer-link"><i class="fas fa-chevron-right me-2"></i>Apartments</a></li>
                        <li><a href="#" class="footer-link"><i class="fas fa-chevron-right me-2"></i>Villas</a></li>
                        <li><a href="#" class="footer-link"><i class="fas fa-chevron-right me-2"></i>Plots</a></li>
                        <li><a href="#" class="footer-link"><i class="fas fa-chevron-right me-2"></i>Commercial</a></li>
                        <li><a href="#" class="footer-link"><i class="fas fa-chevron-right me-2"></i>Farm Houses</a></li>
                    </ul>
                </div>

                <!-- Contact Info & Newsletter -->
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="400">
                    <h5 class="footer-title">Contact Us</h5>
                    <ul class="contact-info">
                        <li class="mb-3">
                            <div class="contact-item">
                                <i class="fas fa-map-marker-alt me-3"></i>
                                <div>
                                    <strong>Our Location</strong><br>
                                    <span><?php echo nl2br(htmlspecialchars($company_address)); ?></span>
                                </div>
                            </div>
                        </li>
                        <li class="mb-3">
                            <div class="contact-item">
                                <i class="fas fa-phone-alt me-3"></i>
                                <div>
                                    <strong>Phone Number</strong><br>
                                    <a href="tel:<?php echo preg_replace('/[^0-9+]/', '', $contact_phone); ?>" class="text-decoration-none">
                                        <?php echo htmlspecialchars($contact_phone); ?>
                                    </a>
                                </div>
                            </div>
                        </li>
                        <li class="mb-3">
                            <div class="contact-item">
                                <i class="fas fa-envelope me-3"></i>
                                <div>
                                    <strong>Email Address</strong><br>
                                    <a href="mailto:<?php echo htmlspecialchars($contact_email); ?>" class="text-decoration-none">
                                        <?php echo htmlspecialchars($contact_email); ?>
                                    </a>
                                </div>
                            </div>
                        </li>
                        <li>
                            <div class="contact-item">
                                <i class="fas fa-clock me-3"></i>
                                <div>
                                    <strong>Working Hours</strong><br>
                                    <span>Mon-Sat: 9:30 AM - 7:00 PM<br>Sun: 10:00 AM - 5:00 PM</span>
                                </div>
                            </div>
                        </li>
                    </ul>

                    <!-- Newsletter Subscription -->
                    <div class="newsletter-section mt-4">
                        <h6 class="mb-3">Newsletter</h6>
                        <p class="small text-muted mb-3">Subscribe for latest property updates and exclusive deals</p>
                        <form class="newsletter-form" novalidate>
                            <div class="input-group">
                                <input type="email" class="form-control" placeholder="Your Email" required>
                                <button class="btn btn-primary" type="submit">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                            </div>
                            <div class="form-text small mt-2">
                                <i class="fas fa-lock me-2"></i>We respect your privacy. Unsubscribe at any time.
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Footer Bottom -->
            <hr class="footer-divider">
            <div class="footer-bottom">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <p class="mb-0">
                            &copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($footer_copyright); ?><br>
                            <small>Registration No: U70109UP2022PTC163047</small>
                        </p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <div class="footer-bottom-links">
                            <a href="#" class="me-3">Privacy Policy</a>
                            <a href="#">Terms of Service</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Back to Top Button -->
    <a href="#" id="backToTop" class="back-to-top" title="Back to Top">
        <i class="fas fa-arrow-up"></i>
    </a>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>

    <!-- AOS Animation -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

    <!-- Custom Footer JS -->
    <script src="/assets/js/footer.js"></script>

    <style>
        /* Modern Footer Styling */
        .footer {
            position: relative;
            overflow: hidden;
        }

        .footer-gradient-border {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #4f46e5, #8b5cf6, #ec4899);
        }

        .footer-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            position: relative;
            padding-bottom: 10px;
            color: #fff;
        }

        .footer-title::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 40px;
            height: 2px;
            background: #4f46e5;
            border-radius: 2px;
        }

        .footer-text {
            color: #a0aec0;
            line-height: 1.7;
            margin-bottom: 1.5rem;
        }

        /* Social Links */
        .social-links {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .social-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .social-link:hover {
            background: #4f46e5;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(79, 70, 229, 0.3);
        }

        /* Footer Links */
        .footer-links {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .footer-links li {
            margin-bottom: 10px;
        }

        .footer-link {
            color: #a0aec0;
            text-decoration: none;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
        }

        .footer-link:hover {
            color: #4f46e5;
            padding-left: 5px;
        }

        .footer-link i {
            font-size: 0.7rem;
            transition: all 0.3s ease;
        }

        .footer-link:hover i {
            margin-right: 8px;
        }

        /* Contact Info */
        .contact-info {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .contact-info li {
            color: #a0aec0;
            font-size: 0.95rem;
            line-height: 1.6;
            margin-bottom: 15px;
        }

        .contact-item {
            display: flex;
            align-items: flex-start;
        }

        .contact-item i {
            color: #4f46e5;
            font-size: 1rem;
            margin-top: 3px;
            flex-shrink: 0;
        }

        .contact-item a {
            color: #a0aec0;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .contact-item a:hover {
            color: #4f46e5;
        }

        /* Newsletter Form */
        .newsletter-form .form-control {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #fff;
            border-radius: 4px 0 0 4px;
            box-shadow: none;
        }

        .newsletter-form .form-control:focus {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(79, 70, 229, 0.5);
            box-shadow: 0 0 0 0.25rem rgba(79, 70, 229, 0.25);
        }

        .newsletter-form .btn {
            border-radius: 0 4px 4px 0;
            background: #4f46e5;
            border: none;
            padding: 0 15px;
            transition: all 0.3s ease;
        }

        .newsletter-form .btn:hover {
            background: #4338ca;
        }

        /* Footer Bottom */
        .footer-divider {
            border: 0;
            height: 1px;
            background: rgba(255, 255, 255, 0.1);
            margin: 30px 0;
        }

        .footer-bottom {
            color: #718096;
            font-size: 0.9rem;
        }

        .footer-bottom-links a {
            color: #718096;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer-bottom-links a:hover {
            color: #4f46e5;
        }

        /* Back to Top Button */
        .back-to-top {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 50px;
            height: 50px;
            background: #4f46e5;
            color: #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            text-decoration: none;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            z-index: 1000;
            box-shadow: 0 5px 15px rgba(79, 70, 229, 0.3);
        }

        .back-to-top.visible {
            opacity: 1;
            visibility: visible;
        }

        .back-to-top:hover {
            background: #4338ca;
            color: #fff;
            transform: translateY(-3px);
        }

        /* Responsive Design */
        @media (max-width: 991.98px) {
            .footer {
                text-align: center;
            }

            .footer-title::after {
                left: 50%;
                transform: translateX(-50%);
            }

            .social-links {
                justify-content: center;
                margin-bottom: 20px;
            }

            .footer-links,
            .contact-info {
                margin-bottom: 30px;
            }

            .footer-bottom {
                text-align: center;
            }

            .footer-bottom-links {
                margin-top: 15px;
            }
        }

        @media (max-width: 767.98px) {
            .footer {
                padding: 60px 0 20px;
            }

            .footer-about,
            .footer-links,
            .contact-info {
                margin-bottom: 40px;
            }
        }
    </style>

    <script>
        // Initialize AOS animations
        document.addEventListener('DOMContentLoaded', function() {
            AOS.init({
                duration: 800,
                easing: 'ease-in-out',
                once: true,
                offset: 100
            });

            // Newsletter subscription
            document.querySelectorAll('.newsletter-form').forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const email = this.querySelector('input[type="email"]').value;
                    if (email) {
                        // Show success message (you can replace with actual API call)
                        alert('Thank you for subscribing to our newsletter!');
                        this.reset();
                    }
                });
            });

            // Back to top functionality
            const backToTop = document.getElementById('backToTop');
            window.addEventListener('scroll', function() {
                if (window.pageYOffset > 300) {
                    backToTop.classList.add('visible');
                } else {
                    backToTop.classList.remove('visible');
                }
            });

            backToTop.addEventListener('click', function(e) {
                e.preventDefault();
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
        });
    </script>

    <!-- Close HTML if not already closed -->
    <?php if (!isset($no_footer) || !$no_footer): ?>
    </body>
    </html>
    <?php endif; ?>
