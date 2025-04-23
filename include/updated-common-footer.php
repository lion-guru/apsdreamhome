<?php
/**
 * Modern, dynamic footer with fallback for APS Dream Homes
 * Tries to load content, links, socials from DB; on error, shows static trusted footer
 */
$dynamicFooterError = false;
$footerContent = '';
$footerLinks = $socialLinks = [];
try {
    require_once __DIR__ . '/../admin/config.php';
    require_once __DIR__ . '/../includes/db_config.php';
    $conn = function_exists('getDbConnection') ? getDbConnection() : null;
    $settings = [];
    if ($conn) {
        $sql = "SELECT * FROM site_settings WHERE setting_name IN ('footer_content', 'footer_links', 'social_links')";
        $result = $conn->query($sql);
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $settings[$row['setting_name']] = $row['value'];
            }
            $result->free();
        }
    }
    $footerContent = $settings['footer_content'] ?? '';
    $footerLinks = json_decode($settings['footer_links'] ?? '[]', true);
    $socialLinks = json_decode($settings['social_links'] ?? '[]', true);
} catch (Throwable $e) {
    $dynamicFooterError = true;
}
?>
</main>
<footer class="footer-section">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <div class="footer-about">
                    <div class="footer-logo mb-3">
                        <img src="<?php echo get_asset_url('aps-logo.png', 'images'); ?>" alt="APS Dream Homes Logo" class="img-fluid" style="max-height: 60px;">
                    </div>
                    <?php if (!$dynamicFooterError && !empty($footerContent)): ?>
                        <p class="footer-description"><?php echo $footerContent; ?></p>
                    <?php else: ?>
                        <p class="footer-description">
                            APS Dream Homes Private Limited is a prestigious real estate company registered under the Companies Act, 2013, launched on 26 April 2022. It provides services in buying, selling, construction, maintenance, development, advertising, and marketing various real estate projects. We strive to create an environment of trust and faith between our sales associates and customers, playing a vital role in shaping the land of our great nation through our core values of quality and customer satisfaction.
                        </p>
                    <?php endif; ?>
                    <div class="social-links">
                        <?php if (!$dynamicFooterError && !empty($socialLinks)): ?>
                            <?php foreach ($socialLinks as $social): ?>
                                <a href="<?php echo htmlspecialchars($social['url']); ?>" class="social-link" target="_blank" aria-label="<?php echo htmlspecialchars($social['icon']); ?>">
                                    <i class="fab <?php echo htmlspecialchars($social['icon']); ?>" title="<?php echo htmlspecialchars($social['icon']); ?>"></i>
                                </a>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <a href="https://www.facebook.com/AbhaySinghSuryawansi/" class="social-link" aria-label="Facebook" target="_blank"><i class="fab fa-facebook-f" title="Facebook"></i></a>
                            <a href="https://www.instagram.com/apsdreamhomes" class="social-link" aria-label="Instagram" target="_blank"><i class="fab fa-instagram" title="Instagram"></i></a>
                            <a href="#" class="social-link" aria-label="LinkedIn" target="_blank"><i class="fab fa-linkedin-in" title="LinkedIn"></i></a>
                            <a href="#" class="social-link" aria-label="YouTube" target="_blank"><i class="fab fa-youtube" title="YouTube"></i></a>
                            <a href="#" class="social-link" aria-label="Twitter" target="_blank"><i class="fab fa-twitter" title="Twitter"></i></a>
                            <a href="#" class="social-link" aria-label="Google Plus" target="_blank"><i class="fab fa-google-plus-g" title="Google Plus"></i></a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="footer-links">
                    <h5 class="footer-title">Quick Links</h5>
                    <ul class="footer-menu">
                        <?php if (!$dynamicFooterError && !empty($footerLinks)): ?>
                            <?php foreach ($footerLinks as $link): ?>
                                <li><a href="<?php echo htmlspecialchars($link['url']); ?>"><i class="fas fa-angle-right"></i> <?php echo htmlspecialchars($link['text']); ?></a></li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li><a href="<?php echo BASE_URL; ?>/about.php"><i class="fas fa-angle-right"></i> About Us</a></li>
                            <li><a href="#"><i class="fas fa-angle-right"></i> Featured Property</a></li>
                            <li><a href="#"><i class="fas fa-angle-right"></i> Submit Property</a></li>
                            <li><a href="<?php echo BASE_URL; ?>/property.php"><i class="fas fa-angle-right"></i> Properties</a></li>
                            <li><a href="<?php echo BASE_URL; ?>/agent.php"><i class="fas fa-angle-right"></i> Our Agents</a></li>
                            <li><a href="<?php echo BASE_URL; ?>/contact.php"><i class="fas fa-angle-right"></i> Contact</a></li>
                            <li><a href="<?php echo BASE_URL; ?>/career.php"><i class="fas fa-angle-right"></i> Careers</a></li>
                            <li><a href="<?php echo BASE_URL; ?>/legal.php"><i class="fas fa-angle-right"></i> Legal</a></li>
                            <h5 class="footer-title mt-4">Support</h5>
                            <ul class="footer-menu">
                                <li><a href="#"><i class="fas fa-angle-right"></i> Forum</a></li>
                                <li><a href="#"><i class="fas fa-angle-right"></i> Terms and Conditions</a></li>
                                <li><a href="#"><i class="fas fa-angle-right"></i> Frequently Asked Questions</a></li>
                            </ul>
                        <?php endif; ?>
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
                            <p><a href="tel:+917007444842">7007444842</a></p>
                            <p><a href="tel:+918318037728">8318037728</a></p>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-envelope"></i>
                            <p><a href="mailto:apsdreamhomes44@gmail.com">apsdreamhomes44@gmail.com</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Chat Widget -->
    <div class="chat-container">
        <span class="chat-text" style="color: blue;">Chat with us</span>
    </div>
    <script src="https://widget.cxgenie.ai/widget.js" data-aid="536ce99e-b07e-43ef-847a-0f372274e71d" data-lang="en"></script>
    <script>
        function openChat() {
            if (typeof cxgenie !== 'undefined' && cxgenie.open) {
                cxgenie.open();
            }
        }
        window.onload = function() {
            setTimeout(function() {
                openChat();
            }, 100);
        };
    </script>
    <div class="footer-bottom">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <p class="copyright"> APS Dream Homes. Developed By Abhay Singh. All Rights Reserved.</p>
                </div>
                <div class="col-md-6">
                    <ul class="footer-bottom-links">
                        <li><a href="<?php echo BASE_URL; ?>/legal.php">Terms & Conditions</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/PrivacyPolicy.php">Privacy Policy</a></li>
                        <li><a href="#">Site Map</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</footer>
<!-- Back to Top -->
<a href="#" class="back-to-top"><i class="fas fa-arrow-up"></i></a>
<!-- jQuery -->
<script src="<?php echo get_asset_url('jquery/jquery.min.js', 'vendor'); ?>"></script>
<!-- Bootstrap JS -->
<script src="<?php echo get_asset_url('bootstrap/js/bootstrap.bundle.min.js', 'vendor'); ?>"></script>
<!-- Custom JS -->
<script src="<?php echo get_asset_url('js/main.js'); ?>"></script>
<!-- Additional JS -->
<?php if(isset($additional_js)) echo $additional_js; ?>
<style>
  -webkit-text-size-adjust: 100%;
  -ms-text-size-adjust: 100%;
  text-size-adjust: 100%;
  -webkit-user-select: none;
  user-select: none;
  -webkit-backdrop-filter: blur(5px);
  backdrop-filter: blur(5px);
  -webkit-user-drag: none;
  user-drag: none;
  -webkit-background-clip: text;
  background-clip: text;
  /* Add other suggested compatibility fixes here */
</style>
</body>
</html>