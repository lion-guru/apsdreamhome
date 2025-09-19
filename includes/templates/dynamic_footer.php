<?php
require_once __DIR__ . '/../../includes/db_settings.php';

// Default footer settings
$default_footer = [
    'footer_content' => 'Your trusted partner in real estate, providing premium properties across Uttar Pradesh with a commitment to excellence and customer satisfaction.',
    'footer_links' => [
        ['url' => '/apsdreamhomefinal/about.php', 'text' => 'About Us', 'aria_label' => 'Learn more about APS Dream Homes'],
        ['url' => '/apsdreamhomefinal/properties.php', 'text' => 'Properties', 'aria_label' => 'View our properties'],
        ['url' => '/apsdreamhomefinal/contact.php', 'text' => 'Contact', 'aria_label' => 'Get in touch with us'],
        ['url' => '/apsdreamhomefinal/careers.php', 'text' => 'Careers', 'aria_label' => 'View career opportunities'],
        ['url' => '/apsdreamhomefinal/legal.php', 'text' => 'Legal', 'aria_label' => 'View legal information']
    ],
    'social_links' => [
        ['platform' => 'facebook', 'url' => 'https://facebook.com/apsdreamhomes', 'aria_label' => 'Follow us on Facebook'],
        ['platform' => 'twitter', 'url' => 'https://twitter.com/apsdreamhomes', 'aria_label' => 'Follow us on Twitter'],
        ['platform' => 'instagram', 'url' => 'https://instagram.com/apsdreamhomes', 'aria_label' => 'Follow us on Instagram'],
        ['platform' => 'linkedin', 'url' => 'https://linkedin.com/company/apsdreamhomes', 'aria_label' => 'Connect with us on LinkedIn']
    ]
];

// Try to fetch settings from database
$conn = get_db_connection();
$settings = $default_footer;
if ($conn) {
    $sql = "SELECT * FROM site_settings WHERE setting_name IN ('footer_content', 'footer_links', 'social_links')";
    try {
        $result = $conn->query($sql);
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                if ($row['setting_name'] === 'footer_links' || $row['setting_name'] === 'social_links') {
                    $settings[$row['setting_name']] = json_decode($row['value'], true);
                } else {
                    $settings[$row['setting_name']] = $row['value'];
                }
            }
            $result->free();
        }
    } catch (Exception $e) {
        error_log('Footer DB error: ' . $e->getMessage());
    }
}

$social_icons = [
    'facebook' => 'fab fa-facebook',
    'twitter' => 'fab fa-twitter',
    'instagram' => 'fab fa-instagram',
    'linkedin' => 'fab fa-linkedin'
];
?>
<style>
.site-footer {
    background: #222;
    color: #fff;
    padding: 2.5rem 0 1rem;
    font-size: 1rem;
    position: relative;
    z-index: 1000;
}
.site-footer .footer-heading {
    font-size: 1.2rem;
    font-weight: 600;
    margin-bottom: 1rem;
    color: #fff;
}
.site-footer .footer-content {
    margin-bottom: 1.5rem;
}
.site-footer .footer-links {
    list-style: none;
    padding: 0;
    margin: 0;
}
.site-footer .footer-links li {
    margin-bottom: 0.5rem;
}
.site-footer .footer-links a {
    color: #fff;
    text-decoration: none;
    transition: color 0.2s;
}
.site-footer .footer-links a:hover,
.site-footer .footer-links a:focus {
    color: #007bff;
    outline: none;
}
.site-footer .social-links {
    display: flex;
    gap: 1rem;
    margin-top: 1rem;
}
.site-footer .social-links a {
    color: #fff;
    font-size: 1.4rem;
    border-radius: 50%;
    background: rgba(255,255,255,0.08);
    width: 2.3rem;
    height: 2.3rem;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.2s, color 0.2s;
}
.site-footer .social-links a:hover,
.site-footer .social-links a:focus {
    background: #007bff;
    color: #fff;
}
.site-footer .contact-info address,
.site-footer .contact-info p {
    margin-bottom: 0.5rem;
    color: #ccc;
}
.site-footer .footer-bottom {
    border-top: 1px solid rgba(255,255,255,0.1);
    margin-top: 2rem;
    padding-top: 1rem;
    text-align: center;
    font-size: 0.95rem;
    color: #bbb;
}
@media (max-width: 767.98px) {
    .site-footer .footer-content,
    .site-footer .footer-links,
    .site-footer .contact-info {
        margin-bottom: 1.5rem;
    }
    .site-footer .social-links {
        justify-content: center;
    }
}
</style>
<footer class="site-footer" role="contentinfo">
    <div class="container">
        <div class="row">
            <div class="col-md-4">
                <div class="footer-content">
                    <h2 class="footer-heading">About APS Dream Homes</h2>
                    <p><?php echo htmlspecialchars($settings['footer_content']); ?></p>
                </div>
            </div>
            <div class="col-md-4">
                <h2 class="footer-heading">Quick Links</h2>
                <nav aria-label="Footer Navigation">
                    <ul class="footer-links">
                        <?php foreach ($settings['footer_links'] as $link): ?>
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
                    <?php foreach ($settings['social_links'] as $social): ?>
                        <?php $platform = $social['platform'] ?? 'Social'; ?>
                        <a href="<?php echo htmlspecialchars($social['url']); ?>"
                           target="_blank"
                           rel="noopener noreferrer"
                           aria-label="<?php echo htmlspecialchars($social['aria_label'] ?? ('Follow us on ' . (is_string($platform) && $platform ? ucfirst($platform) : 'Social'))); ?>">
                            <i class="<?php echo $social_icons[$platform] ?? 'fas fa-link'; ?>" aria-hidden="true"></i>
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
<!-- Bootstrap JS Bundle with Popper -->
<script src="<?php echo $base_url; ?>assets/js/bootstrap.bundle.min.js"></script>
<!-- Custom JS -->
<script src="<?php echo $base_url; ?>assets/js/main.js"></script>
<!-- Additional JS -->
<?php if(isset($additional_js)) echo $additional_js; ?>

</body>
</html>
