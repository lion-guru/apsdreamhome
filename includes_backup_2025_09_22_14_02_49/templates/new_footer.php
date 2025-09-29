<?php require_once(__DIR__.'/../config/base_url.php'); ?>
</main>
    <footer class="footer-section">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="footer-about">
                        <div class="footer-logo mb-3">
                            <img src="<?php echo $base_url; ?>assets/images/aps-logo.png" alt="APS Dream Homes Logo" class="img-fluid" style="max-height: 60px;">
                        </div>
                        <p class="footer-description">Your trusted partner in real estate, providing premium properties across Uttar Pradesh with a commitment to excellence and customer satisfaction.</p>
                        <div class="social-links">
                            <a href="https://www.facebook.com/AbhaySinghSuryawansi/" class="social-link"><i class="fab fa-facebook-f"></i></a>
                            <a href="https://www.instagram.com/apsdreamhomes" class="social-link"><i class="fab fa-instagram"></i></a>
                            <a href="#" class="social-link"><i class="fab fa-linkedin-in"></i></a>
                            <a href="#" class="social-link"><i class="fab fa-youtube"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="footer-links">
                        <h5 class="footer-title">Quick Links</h5>
                        <ul class="footer-menu">
                            <li><a href="<?php echo $base_url; ?>about.php"><i class="fas fa-angle-right"></i> About Us</a></li>
                            <li><a href="<?php echo $base_url; ?>properties.php"><i class="fas fa-angle-right"></i> Properties</a></li>
                            <li><a href="<?php echo $base_url; ?>contact.php"><i class="fas fa-angle-right"></i> Contact</a></li>
                            <li><a href="<?php echo $base_url; ?>career.php"><i class="fas fa-angle-right"></i> Careers</a></li>
                            <li><a href="<?php echo $base_url; ?>legal.php"><i class="fas fa-angle-right"></i> Legal</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="footer-contact">
                        <h5 class="footer-title">Contact Info</h5>
                        <div class="contact-info">
                            <div class="contact-item">
                                <i class="fas fa-map-marker-alt"></i>
                                <p>1st Floor Near Ganpati Lawn,<br>Singhariya, Kunraghat<br>Gorakhpur, Uttar Pradesh - 273008</p>
                            </div>
                            <div class="contact-item">
                                <i class="fas fa-phone-alt"></i>
                                <p><a href="tel:+917007444842">+91 9277121112</a></p>
                            </div>
                            <div class="contact-item">
                                <i class="fas fa-envelope"></i>
                                <p><a href="mailto:info@apsdreamhomes.com">info@apsdreamhomes.com</a></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="footer-bottom mt-5">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <p class="copyright mb-0">&copy; <?php echo date('Y'); ?> APS Dream Homes. All rights reserved.</p>
                    </div>
                    <div class="col-md-6">
                        <ul class="footer-bottom-links">
                            <li><a href="<?php echo $base_url; ?>privacy-policy.php">Privacy Policy</a></li>
                            <li><a href="<?php echo $base_url; ?>terms.php">Terms & Conditions</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    <?php if(isset($additional_js)) echo $additional_js; ?>
</body>
</html>
