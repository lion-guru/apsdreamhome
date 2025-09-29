        </main>

        <!-- Footer -->
        <footer class="footer">
            <div class="container">
                <div class="row">
                    <div class="col-lg-4 mb-4">
                        <h5 class="mb-3">
                            <i class="fas fa-home me-2"></i>APS Dream Homes Pvt Ltd
                        </h5>
                        <p>Leading real estate developer in Gorakhpur with 8+ years of excellence in property development and customer satisfaction.</p>

                        <div class="social-links mt-3">
                            <a href="https://facebook.com/apsdreamhomes" target="_blank" title="Facebook">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="https://instagram.com/apsdreamhomes" target="_blank" title="Instagram">
                                <i class="fab fa-instagram"></i>
                            </a>
                            <a href="https://linkedin.com/company/aps-dream-homes-pvt-ltd" target="_blank" title="LinkedIn">
                                <i class="fab fa-linkedin-in"></i>
                            </a>
                            <a href="https://youtube.com/apsdreamhomes" target="_blank" title="YouTube">
                                <i class="fab fa-youtube"></i>
                            </a>
                        </div>
                    </div>

                    <div class="col-lg-2 col-md-6 mb-4">
                        <h5 class="mb-3">Quick Links</h5>
                        <ul class="list-unstyled">
                            <li class="mb-2"><a href="index.php">Home</a></li>
                            <li class="mb-2"><a href="properties_template.php">Properties</a></li>
                            <li class="mb-2"><a href="about_template.php">About Us</a></li>
                            <li class="mb-2"><a href="contact_template.php">Contact</a></li>
                        </ul>
                    </div>

                    <div class="col-lg-3 col-md-6 mb-4">
                        <h5 class="mb-3">Contact Info</h5>
                        <ul class="list-unstyled">
                            <li class="mb-2">
                                <i class="fas fa-map-marker-alt me-2"></i>
                                123, Kunraghat Main Road<br>
                                Near Railway Station<br>
                                Gorakhpur, UP - 273008
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-phone-alt me-2"></i>
                                <a href="tel:+919554000001">+91-9554000001</a>
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-envelope me-2"></i>
                                <a href="mailto:info@apsdreamhomes.com">info@apsdreamhomes.com</a>
                            </li>
                            <li>
                                <i class="fas fa-clock me-2"></i>
                                Mon-Sat: 9:30 AM - 7:00 PM<br>
                                Sun: 10:00 AM - 5:00 PM
                            </li>
                        </ul>
                    </div>

                    <div class="col-lg-3 col-md-6 mb-4">
                        <h5 class="mb-3">Newsletter</h5>
                        <p>Subscribe for latest property updates and exclusive deals</p>
                        <form class="mb-3 newsletter-form">
                            <div class="input-group">
                                <input type="email" class="form-control" placeholder="Your Email" required>
                                <button class="btn btn-primary" type="submit">Subscribe</button>
                            </div>
                        </form>
                    </div>
                </div>

                <hr class="my-4" style="background: rgba(255,255,255,0.2);">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <p class="mb-0">
                            &copy; 2025 APS Dream Homes Pvt Ltd. All rights reserved.<br>
                            <small>Registration No: U70109UP2022PTC163047</small>
                        </p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <a href="#" class="me-3">Privacy Policy</a>
                        <a href="#">Terms of Service</a>
                    </div>
                </div>
            </div>
        </footer>

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
                        alert('Thank you for subscribing to our newsletter!');
                        this.reset();
                    }
                });
            });
        </script>
    </body>
    </html>
