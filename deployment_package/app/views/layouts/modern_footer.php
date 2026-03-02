<?php
/**
 * Modern Footer - APS Dream Home
 * Enhanced responsive footer with modern design
 */
?>

<!-- Modern Footer -->
<footer class="bg-dark text-light py-5 mt-5">
    <div class="container">
        <div class="row g-4">
            <!-- Company Info -->
            <div class="col-lg-4 col-md-6">
                <div class="footer-brand mb-4">
                    <h3 class="logo mb-3">
                        <i class="fas fa-home me-2"></i>APS Dream Home
                    </h3>
                    <p class="text-light opacity-75">
                        Your trusted partner in real estate. We help you find your dream home
                        with premium properties across Uttar Pradesh.
                    </p>
                </div>

                <!-- Contact Info -->
                <div class="contact-info">
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-map-marker-alt me-3 text-primary"></i>
                        <span>Kunraghat, Gorakhpur, UP - 273008</span>
                    </div>
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-phone-alt me-3 text-primary"></i>
                        <span>+91-9554000001</span>
                    </div>
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-envelope me-3 text-primary"></i>
                        <span>info@apsdreamhomes.com</span>
                    </div>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-clock me-3 text-primary"></i>
                        <span>Mon-Sat: 9:00 AM - 8:00 PM</span>
                    </div>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="col-lg-2 col-md-6">
                <h5 class="text-white mb-4">
                    <i class="fas fa-link me-2"></i>Quick Links
                </h5>
                <ul class="list-unstyled footer-links">
                    <li class="mb-2">
                        <a href="<?php echo BASE_URL; ?>properties" class="text-light opacity-75 text-decoration-none">
                            Browse Properties
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="<?php echo BASE_URL; ?>company-projects" class="text-light opacity-75 text-decoration-none">
                            Our Projects
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="<?php echo BASE_URL; ?>about" class="text-light opacity-75 text-decoration-none">
                            About Us
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="<?php echo BASE_URL; ?>contact" class="text-light opacity-75 text-decoration-none">
                            Contact Us
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="<?php echo BASE_URL; ?>associate" class="text-light opacity-75 text-decoration-none">
                            Join as Associate
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Services -->
            <div class="col-lg-3 col-md-6">
                <h5 class="text-white mb-4">
                    <i class="fas fa-cogs me-2"></i>Our Services
                </h5>
                <ul class="list-unstyled footer-links">
                    <li class="mb-2">
                        <a href="#" class="text-light opacity-75 text-decoration-none">
                            Property Sales
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="#" class="text-light opacity-75 text-decoration-none">
                            Real Estate Consulting
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="#" class="text-light opacity-75 text-decoration-none">
                            Investment Advisory
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="#" class="text-light opacity-75 text-decoration-none">
                            Property Management
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="#" class="text-light opacity-75 text-decoration-none">
                            Legal Documentation
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Newsletter & Social -->
            <div class="col-lg-3 col-md-6">
                <h5 class="text-white mb-4">
                    <i class="fas fa-newspaper me-2"></i>Stay Updated
                </h5>

                <!-- Newsletter Signup -->
                <form class="newsletter-form mb-4">
                    <div class="input-group">
                        <input type="email" class="form-control" placeholder="Enter your email" required>
                        <button class="btn btn-primary" type="submit">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </form>

                <!-- Social Media Links -->
                <div class="social-links">
                    <h6 class="text-white mb-3">Follow Us</h6>
                    <div class="d-flex gap-3">
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

        <!-- Footer Bottom -->
        <hr class="my-4 opacity-25">
        <div class="row align-items-center">
            <div class="col-md-6">
                <p class="mb-0 opacity-75">
                    © 2025 APS Dream Home Pvt Ltd. All rights reserved.
                </p>
            </div>
            <div class="col-md-6 text-md-end">
                <div class="footer-legal-links">
                    <a href="<?php echo BASE_URL; ?>privacy-policy" class="text-light opacity-75 text-decoration-none me-3">
                        Privacy Policy
                    </a>
                    <a href="<?php echo BASE_URL; ?>terms" class="text-light opacity-75 text-decoration-none">
                        Terms of Service
                    </a>
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- Footer Styles -->
<style>
    /* Footer Styles */
    .footer-brand .logo {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        font-size: 1.8rem;
        font-weight: 800;
    }

    .footer-links {
        line-height: 2;
    }

    .footer-links a:hover {
        opacity: 1 !important;
        color: #667eea !important;
    }

    .social-links .social-link {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .social-link.facebook { background: #1877f2; }
    .social-link.twitter { background: #1da1f2; }
    .social-link.instagram { background: linear-gradient(45deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%); }
    .social-link.linkedin { background: #0077b5; }
    .social-link.youtube { background: #ff0000; }

    .social-link:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }

    .newsletter-form .form-control {
        border-radius: 25px 0 0 25px;
        border: none;
        padding: 0.75rem 1rem;
    }

    .newsletter-form .btn {
        border-radius: 0 25px 25px 0;
        background: linear-gradient(135deg, #667eea, #764ba2);
        border: none;
        color: white;
    }

    .footer-legal-links a:hover {
        opacity: 1 !important;
        color: #667eea !important;
    }

    /* Animation for footer elements */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .footer-brand,
    .col-lg-2,
    .col-lg-3,
    .col-lg-4 {
        animation: fadeInUp 0.6s ease-out;
    }

    .col-lg-2 { animation-delay: 0.1s; }
    .col-lg-3 { animation-delay: 0.2s; }
    .col-lg-4 { animation-delay: 0.3s; }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .footer-brand .logo {
            font-size: 1.5rem;
        }

        .social-links .d-flex {
            justify-content: center;
        }

        .footer-legal-links {
            text-align: center;
            margin-top: 1rem;
        }
    }
</style>

<!-- Footer JavaScript -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Newsletter form submission
        const newsletterForm = document.querySelector('.newsletter-form');
        if (newsletterForm) {
            newsletterForm.addEventListener('submit', function(e) {
                e.preventDefault();

                const email = this.querySelector('input[type="email"]').value;
                const button = this.querySelector('button');

                // Show loading state
                const originalHTML = button.innerHTML;
                button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                button.disabled = true;

                // Simulate API call
                setTimeout(() => {
                    alert('Thank you for subscribing to our newsletter!');
                    this.querySelector('input[type="email"]').value = '';

                    // Reset button
                    button.innerHTML = originalHTML;
                    button.disabled = false;
                }, 1500);
            });
        }

        // Smooth scroll to top
        const scrollToTop = document.createElement('button');
        scrollToTop.innerHTML = '<i class="fas fa-chevron-up"></i>';
        scrollToTop.className = 'scroll-to-top';
        scrollToTop.style.cssText = `
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            cursor: pointer;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            z-index: 1000;
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        `;

        document.body.appendChild(scrollToTop);

        // Show/hide scroll to top button
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                scrollToTop.style.opacity = '1';
                scrollToTop.style.visibility = 'visible';
            } else {
                scrollToTop.style.opacity = '0';
                scrollToTop.style.visibility = 'hidden';
            }
        });

        // Scroll to top functionality
        scrollToTop.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });

        // Add hover effect to scroll to top
        scrollToTop.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-3px)';
            this.style.boxShadow = '0 8px 25px rgba(102, 126, 234, 0.4)';
        });

        scrollToTop.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '0 5px 15px rgba(102, 126, 234, 0.3)';
        });
    });
</script>
