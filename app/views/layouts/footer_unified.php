<?php
/**
 * APS Dream Home - Unified Footer
 * This file completes the HTML structure started by header_unified.php
 */

// Ensure this file is being included properly
if (!isset($is_logged_in)) {
    $is_logged_in = isset($_SESSION['user_id']);
    $user_type = $_SESSION['user_type'] ?? 'guest';
}
?>

        <!-- End of .content-wrapper -->
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer bg-dark text-light py-5">
        <div class="container">
            <!-- Top Footer Section -->
            <div class="row mb-4">
                <div class="col-12 text-center">
                    <div class="brand-container mb-3">
                        <i class="fas fa-home brand-icon"></i>
                        <div class="brand-text">
                            <span class="brand-title">APS Dream Home</span>
                            <span class="brand-subtitle">Real Estate</span>
                        </div>
                    </div>
                    <div class="social-links d-flex justify-content-center gap-3 mb-4">
                        <a href="https://facebook.com/apsdreamhomes" target="_blank" title="Facebook" class="social-link">
                            <i class="fab fa-facebook-f fa-lg"></i>
                        </a>
                        <a href="https://instagram.com/apsdreamhomes" target="_blank" title="Instagram" class="social-link">
                            <i class="fab fa-instagram fa-lg"></i>
                        </a>
                        <a href="https://linkedin.com/company/aps-dream-homes-pvt-ltd" target="_blank" title="LinkedIn" class="social-link">
                            <i class="fab fa-linkedin-in fa-lg"></i>
                        </a>
                        <a href="https://youtube.com/apsdreamhomes" target="_blank" title="YouTube" class="social-link">
                            <i class="fab fa-youtube fa-lg"></i>
                        </a>
                        <a href="https://wa.me/917007444842" target="_blank" title="WhatsApp" class="social-link">
                            <i class="fab fa-whatsapp fa-lg"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Main Footer Content -->
            <div class="row g-4">
                <div class="col-lg-3 col-md-6">
                    <h5 class="footer-heading mb-4">About Us</h5>
                    <p class="text-light-muted">Leading real estate developer in Gorakhpur with 8+ years of excellence in property development and customer satisfaction. Building dreams into reality with trust and innovation.</p>
                    <div class="mt-3">
                        <a href="<?php echo BASE_URL ?? '/'; ?>about" class="btn btn-outline-light btn-sm">Learn More</a>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <h5 class="footer-heading mb-4">Quick Links</h5>
                    <div class="row">
                        <div class="col-6">
                            <ul class="list-unstyled footer-links">
                                <li><a href="<?php echo BASE_URL ?? '/'; ?>">Home</a></li>
                                <li><a href="<?php echo BASE_URL ?? '/'; ?>properties">Properties</a></li>
                                <li><a href="<?php echo BASE_URL ?? '/'; ?>projects">Projects</a></li>
                                <li><a href="<?php echo BASE_URL ?? '/'; ?>resell">Resell</a></li>
                                <li><a href="<?php echo BASE_URL ?? '/'; ?>gallery">Gallery</a></li>
                            </ul>
                        </div>
                        <div class="col-6">
                            <ul class="list-unstyled footer-links">
                                <li><a href="<?php echo BASE_URL ?? '/'; ?>blog">Blog</a></li>
                                <li><a href="<?php echo BASE_URL ?? '/'; ?>career">Careers</a></li>
                                <li><a href="<?php echo BASE_URL ?? '/'; ?>team">Our Team</a></li>
                                <li><a href="<?php echo BASE_URL ?? '/'; ?>testimonials">Testimonials</a></li>
                                <li><a href="<?php echo BASE_URL ?? '/'; ?>faq">FAQs</a></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <h5 class="footer-heading mb-4">Contact Info</h5>
                    <ul class="list-unstyled contact-info">
                        <li class="d-flex mb-3">
                            <i class="fas fa-map-marker-alt me-3 mt-1"></i>
                            <div>
                                123, Kunraghat Main Road<br>
                                Near Railway Station<br>
                                Gorakhpur, UP - 273008
                            </div>
                        </li>
                        <li class="d-flex mb-3">
                            <i class="fas fa-phone-alt me-3 mt-1"></i>
                            <div>
                                <a href="tel:+919554000001" class="text-light">+91-9554000001</a><br>
                                <a href="tel:+919554000001" class="text-light">+91-9554000001</a>
                            </div>
                        </li>
                        <li class="d-flex mb-3">
                            <i class="fas fa-envelope me-3 mt-1"></i>
                            <div>
                                <a href="mailto:info@apsdreamhomes.com" class="text-light">info@apsdreamhomes.com</a><br>
                                <a href="mailto:sales@apsdreamhomes.com" class="text-light">sales@apsdreamhomes.com</a>
                            </div>
                        </li>
                        <li class="d-flex">
                            <i class="fas fa-clock me-3 mt-1"></i>
                            <div>
                                Mon-Sat: 9:30 AM - 7:00 PM<br>
                                Sun: 10:00 AM - 5:00 PM
                            </div>
                        </li>
                    </ul>
                </div>

                <div class="col-lg-3 col-md-6">
                    <h5 class="footer-heading mb-4">Newsletter</h5>
                    <p class="text-light-muted">Subscribe for latest property updates, exclusive deals, and market insights.</p>
                    <form class="newsletter-form mb-4" id="newsletterForm">
                        <div class="input-group">
                            <input type="email" name="email" class="form-control" placeholder="Your Email" required>
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                    </form>
                    <div class="download-apps">
                        <p class="mb-2">Download Our App:</p>
                        <a href="#" class="me-2">
                            <img src="<?php echo BASE_URL ?? '/'; ?>assets/images/google-play.png" alt="Google Play" style="height: 35px;">
                        </a>
                        <a href="#">
                            <img src="<?php echo BASE_URL ?? '/'; ?>assets/images/app-store.png" alt="App Store" style="height: 35px;">
                        </a>
                    </div>
                </div>
            </div>

            <!-- Bottom Footer -->
            <hr class="my-4 border-light opacity-25">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="mb-2 mb-md-0">
                        &copy; 2025 APS Dream Homes Pvt Ltd. All rights reserved.<br>
                        <small class="text-light-muted">Registration No: U70109UP2022PTC163047</small>
                    </p>
                </div>
                <div class="col-md-6">
                    <ul class="list-inline text-md-end mb-0">
                        <li class="list-inline-item">
                            <a href="<?php echo BASE_URL ?? '/'; ?>privacy-policy" class="text-light-muted">Privacy Policy</a>
                        </li>
                        <li class="list-inline-item mx-3">|</li>
                        <li class="list-inline-item">
                            <a href="<?php echo BASE_URL ?? '/'; ?>terms-of-service" class="text-light-muted">Terms of Service</a>
                        </li>
                        <li class="list-inline-item mx-3">|</li>
                        <li class="list-inline-item">
                            <a href="<?php echo BASE_URL ?? '/'; ?>sitemap" class="text-light-muted">Sitemap</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>

    <!-- Back to Top Button -->
    <button id="backToTop" class="btn btn-primary rounded-circle position-fixed bottom-0 end-0 m-4" style="display: none;">
        <i class="fas fa-arrow-up"></i>
    </button>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AOS Animation -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

    <script>
        // Initialize AOS
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true
        });

        // Newsletter subscription
        document.querySelectorAll('.newsletter-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const email = this.querySelector('input[type="email"]').value;
                if (email) {
                    // Show success message
                    const button = this.querySelector('button');
                    const originalText = button.innerHTML;
                    button.innerHTML = '<i class="fas fa-check"></i>';
                    button.classList.add('btn-success');

                    setTimeout(() => {
                        button.innerHTML = originalText;
                        button.classList.remove('btn-success');
                        this.reset();
                    }, 2000);
                }
            });
        });

        // Back to Top Button
        const backToTopButton = document.getElementById('backToTop');
        if (backToTopButton) {
            window.addEventListener('scroll', () => {
                if (window.scrollY > 300) {
                    backToTopButton.style.display = 'block';
                } else {
                    backToTopButton.style.display = 'none';
                }
            });

            backToTopButton.addEventListener('click', () => {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
        }

        // Mobile menu close on outside click
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.navbar-collapse') && !e.target.closest('.navbar-toggler')) {
                const navbarCollapse = document.querySelector('.navbar-collapse.show');
                if (navbarCollapse) {
                    const bsCollapse = new bootstrap.Collapse(navbarCollapse);
                    bsCollapse.hide();
                }
            }
        });

        // Flash message auto-dismiss
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            });
        });
    </script>
</body>
</html>
