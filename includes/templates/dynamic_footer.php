<?php
require_once __DIR__ . '/../../includes/db_settings.php';

// Default footer settings
$default_footer = [
    'footer_content' => 'Your trusted partner in real estate, providing premium properties across Uttar Pradesh with a commitment to excellence and customer satisfaction.',
    'footer_links' => json_encode([
        ['url' => '/apsdreamhomefinal/about.php', 'text' => 'About Us', 'aria_label' => 'Learn more about APS Dream Homes'],
        ['url' => '/apsdreamhomefinal/properties.php', 'text' => 'Properties', 'aria_label' => 'View our properties'],
        ['url' => '/apsdreamhomefinal/contact.php', 'text' => 'Contact', 'aria_label' => 'Get in touch with us'],
        ['url' => '/apsdreamhomefinal/careers.php', 'text' => 'Careers', 'aria_label' => 'View career opportunities'],
        ['url' => '/apsdreamhomefinal/legal.php', 'text' => 'Legal', 'aria_label' => 'View legal information']
    ]),
    'social_links' => json_encode([
        ['platform' => 'facebook', 'url' => 'https://facebook.com/apsdreamhomes', 'aria_label' => 'Follow us on Facebook'],
        ['platform' => 'twitter', 'url' => 'https://twitter.com/apsdreamhomes', 'aria_label' => 'Follow us on Twitter'],
        ['platform' => 'instagram', 'url' => 'https://instagram.com/apsdreamhomes', 'aria_label' => 'Follow us on Instagram'],
        ['platform' => 'linkedin', 'url' => 'https://linkedin.com/company/apsdreamhomes', 'aria_label' => 'Connect with us on LinkedIn']
    ])
];

// Initialize settings with defaults
$settings = [
    'footer_content' => $default_footer['footer_content'],
    'footer_links' => $default_footer['footer_links'],
    'social_links' => $default_footer['social_links']
];

// Try to fetch settings from database
$conn = get_db_connection();
if ($conn) {
    $sql = "SELECT * FROM site_settings WHERE setting_name IN ('footer_content', 'footer_links', 'social_links')";
    try {
        $result = $conn->query($sql);
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $settings[$row['setting_name']] = $row['value'];
            }
            $result->free();
        }
    } catch (Exception $e) {
        error_log('Footer DB error: ' . $e->getMessage());
        // Using default settings, no need to show error to user
    }
}

// Parse JSON data
$footer_links = json_decode($settings['footer_links'], true) ?: json_decode($default_footer['footer_links'], true);
$social_links = json_decode($settings['social_links'], true) ?: json_decode($default_footer['social_links'], true);
$footer_content = $settings['footer_content'] ?: $default_footer['footer_content'];

// Social media icon mapping
$social_icons = [
    'facebook' => 'fab fa-facebook',
    'twitter' => 'fab fa-twitter',
    'instagram' => 'fab fa-instagram',
    'linkedin' => 'fab fa-linkedin'
];
?>

<style>
    /* Cross-browser compatibility */
    .site-footer {
        text-rendering: optimizeLegibility;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
        background-color: #333;
        color: #fff;
        padding: 3rem 0 1rem;
        position: relative;
        z-index: 1000;
    }
    .footer-content {
        margin-bottom: 2rem;
    }
    .footer-heading {
        color: #fff;
        font-size: 1.25rem;
        font-weight: 600;
        margin-bottom: 1.25rem;
    }
    .footer-links {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .footer-links li {
        margin-bottom: 0.5rem;
    }
    .footer-links a {
        color: #fff;
        text-decoration: none;
        transition: all 0.3s ease;
        -webkit-transition: all 0.3s ease;
        -moz-transition: all 0.3s ease;
        -o-transition: all 0.3s ease;
        display: inline-block;
        position: relative;
    }
    .footer-links a:hover,
    .footer-links a:focus {
        color: #007bff;
        text-decoration: none;
        outline: none;
    }
    .footer-links a:focus-visible {
        outline: 2px solid #007bff;
        outline-offset: 2px;
    }
    .social-links {
        margin-top: 1rem;
        display: -webkit-box;
        display: -ms-flexbox;
        display: flex;
        gap: 1rem;
        -ms-flex-wrap: wrap;
        flex-wrap: wrap;
    }
    .social-links a {
        color: #fff;
        font-size: 1.5rem;
        transition: all 0.3s ease;
        -webkit-transition: all 0.3s ease;
        -moz-transition: all 0.3s ease;
        -o-transition: all 0.3s ease;
        display: -webkit-inline-box;
        display: -ms-inline-flexbox;
        display: inline-flex;
        -webkit-box-align: center;
        -ms-flex-align: center;
        align-items: center;
        -webkit-box-pack: center;
        -ms-flex-pack: center;
        justify-content: center;
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 50%;
        background-color: rgba(255, 255, 255, 0.1);
        -webkit-backface-visibility: hidden;
        backface-visibility: hidden;
    }
    .social-links a:hover,
    .social-links a:focus {
        color: #fff;
        background-color: #007bff;
        transform: translateY(-2px);
        -webkit-transform: translateY(-2px);
        -ms-transform: translateY(-2px);
        outline: none;
    }
    .social-links a:focus-visible {
        outline: 2px solid #007bff;
        outline-offset: 2px;
    }
    .contact-info {
        margin-bottom: 2rem;
    }
    .contact-info p {
        margin-bottom: 0.5rem;
        line-height: 1.6;
    }
    .footer-bottom {
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        padding-top: 1rem;
        margin-top: 2rem;
        text-align: center;
    }
    .footer-bottom p {
        margin-bottom: 0.5rem;
    }
    .footer-bottom a {
        color: #fff;
        text-decoration: none;
        transition: all 0.3s ease;
        -webkit-transition: all 0.3s ease;
        -moz-transition: all 0.3s ease;
        -o-transition: all 0.3s ease;
        padding: 0.25rem;
        position: relative;
        display: inline-block;
    }
    .footer-bottom a:hover,
    .footer-bottom a:focus {
        color: #007bff;
        text-decoration: none;
        outline: none;
    }
    .footer-bottom a:focus-visible {
        outline: 2px solid #007bff;
        outline-offset: 2px;
    }
    @media (max-width: 767.98px) {
        .footer-content,
        .footer-links,
        .contact-info {
            margin-bottom: 2rem;
        }
        .social-links {
            -webkit-box-pack: center;
            -ms-flex-pack: center;
            justify-content: center;
        }
    }
    @media (prefers-reduced-motion: reduce) {
        .footer-links a,
        .social-links a,
        .footer-bottom a {
            transition: none !important;
            -webkit-transition: none !important;
            -moz-transition: none !important;
            -o-transition: none !important;
            transform: none !important;
            -webkit-transform: none !important;
            -ms-transform: none !important;
        }
    }
</style>

<footer class="site-footer" role="contentinfo">
    <div class="container">
        <div class="row">
            <div class="col-md-4">
                <div class="footer-content">
                    <h2 class="footer-heading">About APS Dream Homes</h2>
                    <p><?php echo htmlspecialchars($footer_content); ?></p>
                </div>
            </div>
            <div class="col-md-4">
                <h2 class="footer-heading">Quick Links</h2>
                <nav aria-label="Footer Navigation">
                    <ul class="footer-links">
                        <?php foreach ($footer_links as $link): ?>
                            <li>
                                <a href="<?php echo htmlspecialchars($link['url']); ?>" 
                                   aria-label="<?php echo htmlspecialchars($link['aria_label'] ?? $link['text']); ?>">
                                    <?php echo htmlspecialchars($link['text']); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </nav>
            </div>
            <div class="col-md-4">
                <div class="contact-info">
                    <h2 class="footer-heading">Contact Info</h2>
                    <address>
                        1st Floor Near Ganpati Lawn,<br>
                        Singhariya, Kunraghat<br>
                        Gorakhpur, Uttar Pradesh - 273008
                    </address>
                    <p><strong>Phone:</strong> <a href="tel:+919277121112" aria-label="Call us at +91 9277121112">+91 9277121112</a></p>
                    <p><strong>Email:</strong> <a href="mailto:info@apsdreamhomes.com" aria-label="Email us at info@apsdreamhomes.com">info@apsdreamhomes.com</a></p>
                </div>
                <div class="social-links" role="list" aria-label="Social Media Links">
                    <?php foreach ($social_links as $social): ?>
                        <a href="<?php echo htmlspecialchars($social['url']); ?>" 
                           target="_blank" 
                           rel="noopener noreferrer" 
                           aria-label="<?php echo htmlspecialchars($social['aria_label'] ?? 'Follow us on ' . ucfirst($social['platform'])); ?>">
                            <i class="<?php echo $social_icons[$social['platform']] ?? 'fas fa-link'; ?>" aria-hidden="true"></i>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> APS Dream Homes. All rights reserved.</p>
            <nav aria-label="Legal Navigation">
                <p>
                    <a href="/apsdreamhomefinal/privacy.php" aria-label="View our Privacy Policy">Privacy Policy</a> |
                    <a href="/apsdreamhomefinal/terms.php" aria-label="View our Terms and Conditions">Terms & Conditions</a>
                </p>
            </nav>
        </div>
    </div>
</footer>
                    <a href="#" class="text-white"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>
            <div class="col-lg-4 mb-4 mb-lg-0">
                <h5 class="text-uppercase mb-4">Quick Links</h5>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="<?php echo $base_url; ?>" class="text-white">Home</a></li>
                    <li class="mb-2"><a href="<?php echo $base_url; ?>properties.php" class="text-white">Properties</a></li>
                    <li class="mb-2"><a href="<?php echo $base_url; ?>about.php" class="text-white">About</a></li>
                    <li class="mb-2"><a href="<?php echo $base_url; ?>contact.php" class="text-white">Contact</a></li>
                </ul>
            </div>
            <div class="col-lg-4">
                <h5 class="text-uppercase mb-4">Contact Us</h5>
                <p><i class="fas fa-map-marker-alt me-2"></i> APS Real Estate Office</p>
                <p><i class="fas fa-phone me-2"></i> +91 XXXXXXXXXX</p>
                <p><i class="fas fa-envelope me-2"></i> info@apsrealestate.com</p>
            </div>
        </div>
    </div>
    <div class="footer-bottom py-3 mt-4" style="background-color: rgba(0,0,0,0.2);">
        <div class="container text-center">
            <p class="mb-0">© <?php echo date('Y'); ?> APS Real Estate. All rights reserved.</p>
        </div>
    </div>
</footer>

<!-- Back to Top Button -->
<a href="#" class="back-to-top btn btn-primary rounded-circle" aria-label="Back to Top">
    <i class="fas fa-arrow-up"></i>
</a>

<!-- Bootstrap JS Bundle with Popper -->
<script src="<?php echo $base_url; ?>assets/js/bootstrap.bundle.min.js"></script>

<!-- Custom JS -->
<script src="<?php echo $base_url; ?>assets/js/main.js"></script>

<!-- Additional JS -->
<?php if(isset($additional_js)) echo $additional_js; ?>

</body>
</html>
<?php
// Get base URL if not already defined
if (!isset($base_url)) {
    $base_url = 'http://' . $_SERVER['HTTP_HOST'] . '/apsdreamhomefinal/';
}

// Helper function for asset URLs if not already defined
if (!function_exists('get_asset_url')) {
    function get_asset_url($path, $type = '') {
        $base_url = $GLOBALS['base_url'] ?? '';
        if (!empty($type)) {
            return $base_url . 'assets/' . $type . '/' . $path;
        }
        return $base_url . 'assets/' . $path;
    }
}
?>
</main>
<footer class="footer-section bg-dark text-white py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-4 mb-4 mb-lg-0">
                <h5 class="text-uppercase mb-4">About APS Real Estate</h5>
                <p>Your trusted partner in real estate, providing quality properties and excellent service across India.</p>
                <div class="social-links mt-4">
                    <a href="#" class="text-white me-3"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="text-white me-3"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="text-white me-3"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="text-white"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>
            <div class="col-lg-4 mb-4 mb-lg-0">
                <h5 class="text-uppercase mb-4">Quick Links</h5>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="<?php echo $base_url; ?>" class="text-white">Home</a></li>
                    <li class="mb-2"><a href="<?php echo $base_url; ?>properties.php" class="text-white">Properties</a></li>
                    <li class="mb-2"><a href="<?php echo $base_url; ?>about.php" class="text-white">About</a></li>
                    <li class="mb-2"><a href="<?php echo $base_url; ?>contact.php" class="text-white">Contact</a></li>
                </ul>
            </div>
            <div class="col-lg-4">
                <h5 class="text-uppercase mb-4">Contact Us</h5>
                <p><i class="fas fa-map-marker-alt me-2"></i> APS Real Estate Office</p>
                <p><i class="fas fa-phone me-2"></i> +91 XXXXXXXXXX</p>
                <p><i class="fas fa-envelope me-2"></i> info@apsrealestate.com</p>
            </div>
        </div>
    </div>
    <div class="footer-bottom py-3 mt-4" style="background-color: rgba(0,0,0,0.2);">
        <div class="container text-center">
            <p class="mb-0"> 2023 APS Real Estate. All rights reserved.</p>
        </div>
    </div>
</footer>

<!-- Back to Top Button -->
<a href="#" class="back-to-top btn btn-primary rounded-circle" aria-label="Back to Top">
    <i class="fas fa-arrow-up"></i>
</a>

<!-- Bootstrap JS Bundle with Popper -->
<script src="<?php echo $base_url; ?>assets/js/bootstrap.bundle.min.js"></script>

<!-- Custom JS -->
<script src="<?php echo $base_url; ?>assets/js/main.js"></script>

<!-- Additional JS -->
<?php if(isset($additional_js)) echo $additional_js; ?>

</body>
</html>
}
?>
</main>
<footer class="footer-section bg-dark text-white py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-4 mb-4 mb-lg-0">
                <h5 class="text-uppercase mb-4">About APS Real Estate</h5>
                <p>Your trusted partner in real estate, providing quality properties and excellent service across India.</p>
                <div class="social-links mt-4">
                    <a href="#" class="text-white me-3"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="text-white me-3"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="text-white me-3"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="text-white"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>
            <div class="col-lg-4 mb-4 mb-lg-0">
                <h5 class="text-uppercase mb-4">Quick Links</h5>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="<?php echo $base_url; ?>" class="text-white">Home</a></li>
                    <li class="mb-2"><a href="<?php echo $base_url; ?>properties.php" class="text-white">Properties</a></li>
                    <li class="mb-2"><a href="<?php echo $base_url; ?>about.php" class="text-white">About</a></li>
                    <li class="mb-2"><a href="<?php echo $base_url; ?>contact.php" class="text-white">Contact</a></li>
                </ul>
            </div>
            <div class="col-lg-4">
                <h5 class="text-uppercase mb-4">Contact Us</h5>
                <p><i class="fas fa-map-marker-alt me-2"></i> APS Real Estate Office</p>
                <p><i class="fas fa-phone me-2"></i> +91 XXXXXXXXXX</p>
                <p><i class="fas fa-envelope me-2"></i> info@apsrealestate.com</p>
            </div>
        </div>
    </div>
    <div class="footer-bottom py-3 mt-4" style="background-color: rgba(0,0,0,0.2);">
        <div class="container text-center">
            <p class="mb-0">