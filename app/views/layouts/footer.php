<?php
// Load site settings from DB (same cache as header)
if (!isset($GLOBALS['_site_settings_cache'])) {
    $GLOBALS['_site_settings_cache'] = [];
    try {
        $scPdo = \App\Core\Database\Database::getInstance()->getPdo();
        $scRows = $scPdo->query("SELECT content_key, content_value FROM site_content WHERE section = 'settings' AND is_active = 1")->fetchAll(PDO::FETCH_KEY_PAIR);
        $GLOBALS['_site_settings_cache'] = $scRows;
    } catch (\Exception $e) { error_log(__METHOD__ . ': ' . $e->getMessage()); }
}
$sc = function($key, $default = '') { return $GLOBALS['_site_settings_cache'][$key] ?? $default; };
?>
<!-- Footer Stats Banner -->
<div class="footer-stats-banner bg-primary bg-gradient text-white py-3">
    <div class="container">
        <div class="row text-center">
            <div class="col-6 col-md-3 mb-2 mb-md-0">
                <div class="fs-3 fw-bold">4+</div>
                <div class="small opacity-75"><?= __('years_experience') ?></div>
            </div>
            <div class="col-6 col-md-3 mb-2 mb-md-0">
                <div class="fs-3 fw-bold">500+</div>
                <div class="small opacity-75"><?= __('about_stat_families') ?></div>
            </div>
            <div class="col-6 col-md-3">
                <div class="fs-3 fw-bold">4</div>
                <div class="small opacity-75"><?= __('footer_premium_colonies') ?></div>
            </div>
            <div class="col-6 col-md-3">
                <div class="fs-3 fw-bold">204+</div>
                <div class="small opacity-75"><?= __('footer_plots_available') ?></div>
            </div>
        </div>
    </div>
</div>

<footer class="bg-dark text-white py-5">
    <div class="container">
        <!-- Newsletter Signup -->
        <div class="row mb-5 pb-4 border-bottom border-secondary">
            <div class="col-lg-6">
                <h5 class="text-white mb-2"><i class="fas fa-envelope me-2"></i><?= __('newsletter') ?></h5>
                <p class="text-light mb-0">Get latest property updates and exclusive offers delivered to your inbox</p>
            </div>
            <div class="col-lg-6">
                <form action="<?php echo BASE_URL; ?>/subscribe" method="POST" class="d-flex gap-2" id="newsletterForm">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="email" name="email" class="form-control" placeholder="<?= __('newsletter_ph_email') ?>" required>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> <?= __('subscribe') ?>
                    </button>
                </form>
                <div id="newsletterMessage" class="mt-2" class="style-54390"></div>
            </div>
        </div>
        
        <div class="row">
            <!-- Company Info -->
            <div class="col-lg-4 col-md-6 mb-4">
                <h5 class="mb-3 text-white"><?= htmlspecialchars($sc('company_name', 'APS Dream Homes Pvt Ltd')) ?></h5>
                <p class="text-light"><?= $sc('footer_about', __('footer_company_desc')) ?></p>
                <div class="social-links mt-3">
                    <?php if ($sc('social_facebook')): ?>
                    <a href="<?= htmlspecialchars($sc('social_facebook')) ?>" target="_blank" class="text-white me-3 social-icon"><i class="fab fa-facebook-f"></i></a>
                    <?php endif; ?>
                    <?php if ($sc('social_instagram')): ?>
                    <a href="<?= htmlspecialchars($sc('social_instagram')) ?>" target="_blank" class="text-white me-3 social-icon"><i class="fab fa-instagram"></i></a>
                    <?php endif; ?>
                    <?php if ($sc('social_youtube')): ?>
                    <a href="<?= htmlspecialchars($sc('social_youtube')) ?>" target="_blank" class="text-white me-3 social-icon"><i class="fab fa-youtube"></i></a>
                    <?php endif; ?>
                    <?php if ($sc('social_linkedin')): ?>
                    <a href="<?= htmlspecialchars($sc('social_linkedin')) ?>" target="_blank" class="text-white me-3 social-icon"><i class="fab fa-linkedin-in"></i></a>
                    <?php endif; ?>
                    <?php if ($sc('social_telegram')): ?>
                    <a href="<?= htmlspecialchars($sc('social_telegram')) ?>" target="_blank" class="text-white me-3 social-icon"><i class="fab fa-telegram"></i></a>
                    <?php endif; ?>
                    <?php if ($sc('contact_whatsapp')): ?>
                    <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $sc('contact_whatsapp')) ?>" target="_blank" class="text-white me-3 social-icon"><i class="fab fa-whatsapp"></i></a>
                    <?php endif; ?>
                </div>
                <style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
                    .social-icon {
                        display: inline-flex;
                        align-items: center;
                        justify-content: center;
                        width: 40px;
                        height: 40px;
                        border-radius: 50%;
                        background: rgba(255,255,255,0.1);
                        transition: all 0.3s ease;
                    }
                    .social-icon:hover {
                        background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
                        transform: translateY(-3px);
                    }
                </style>
            </div>

            <!-- Quick Links -->
            <div class="col-lg-2 col-md-6 mb-4">
                <h6 class="text-uppercase mb-3"><?= __('quick_links') ?></h6>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="<?php echo BASE_URL; ?>/" class="text-light text-decoration-none"><?= __('home') ?></a></li>
                    <li class="mb-2"><a href="<?php echo BASE_URL; ?>/properties" class="text-light text-decoration-none"><?= __('properties') ?></a></li>
                    <li class="mb-2"><a href="<?php echo BASE_URL; ?>/company/projects" class="text-light text-decoration-none"><?= __('projects') ?></a></li>
                    <li class="mb-2"><a href="<?php echo BASE_URL; ?>/about" class="text-light text-decoration-none"><?= __('about_us') ?></a></li>
                    <li class="mb-2"><a href="<?php echo BASE_URL; ?>/contact" class="text-light text-decoration-none"><?= __('contact_us') ?></a></li>
                    <li class="mb-2"><a href="<?php echo BASE_URL; ?>/admin/login" class="text-light text-decoration-none"><?= __('admin') ?></a></li>
                    <li class="mb-2"><a href="<?php echo BASE_URL; ?>/mobile-app" class="text-light text-decoration-none"><i class="fas fa-mobile-alt me-1"></i>📱 Download App</a></li>
                </ul>
            </div>

            <!-- Services -->
            <div class="col-lg-3 col-md-6 mb-4">
                <h6 class="text-uppercase mb-3"><?= __('our_services') ?></h6>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="<?php echo BASE_URL; ?>/services" class="text-light text-decoration-none"><?= __('property_sales') ?></a></li>
                    <li class="mb-2"><a href="<?php echo BASE_URL; ?>/services" class="text-light text-decoration-none"><?= __('property_valuation') ?></a></li>
                    <li class="mb-2"><a href="<?php echo BASE_URL; ?>/interior-design" class="text-light text-decoration-none"><?= __('interior_design') ?></a></li>
                    <li class="mb-2"><a href="<?php echo BASE_URL; ?>/legal/services" class="text-light text-decoration-none"><?= __('legal_documentation') ?></a></li>
                    <li class="mb-2"><a href="<?php echo BASE_URL; ?>/resell" class="text-light text-decoration-none"><?= __('resell_properties') ?></a></li>
                </ul>
            </div>

            <!-- Contact Info -->
            <div class="col-lg-3 col-md-6 mb-4">
                <h6 class="text-uppercase mb-3"><?= __('contact_info') ?></h6>
                <ul class="list-unstyled">
                    <li class="mb-3">
                        <i class="fas fa-map-marker-alt me-2 text-primary"></i>
                        <span class="text-light"><?= $sc('contact_address', __('footer_address')) ?></span>
                    </li>
                    <li class="mb-3">
                        <i class="fas fa-phone me-2 text-primary"></i>
                        <a href="tel:<?= preg_replace('/[^0-9+]/', '', $sc('contact_phone', '+919277121112')) ?>" class="text-light text-decoration-none"><?= htmlspecialchars($sc('contact_phone', '+91 92771 21112')) ?></a>
                    </li>
                    <?php if ($sc('contact_phone_2')): ?>
                    <li class="mb-3">
                        <i class="fas fa-phone me-2 text-primary"></i>
                        <a href="tel:<?= preg_replace('/[^0-9+]/', '', $sc('contact_phone_2')) ?>" class="text-light text-decoration-none"><?= htmlspecialchars($sc('contact_phone_2')) ?></a>
                    </li>
                    <?php endif; ?>
                    <li class="mb-3">
                        <i class="fas fa-envelope me-2 text-primary"></i>
                        <a href="mailto:<?= htmlspecialchars($sc('contact_email', 'info@apsdreamhome.com')) ?>" class="text-light text-decoration-none"><?= htmlspecialchars($sc('contact_email', 'info@apsdreamhome.com')) ?></a>
                    </li>
                    <li class="mb-3">
                        <i class="fas fa-clock me-2 text-primary"></i>
                        <span class="text-light"><?= $sc('contact_working_hours', __('footer_business_hours')) ?></span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Bottom Bar -->
        <div class="border-top border-secondary pt-4 mt-4">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-0 text-light">&copy; <?php echo date('Y'); ?> APS Dream Home. <?= __('all_rights_reserved') ?></p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="<?php echo BASE_URL; ?>/privacy" class="text-light me-3 text-decoration-none"><?= __('privacy_policy') ?></a>
                    <a href="<?php echo BASE_URL; ?>/legal/terms-conditions" class="text-light text-decoration-none"><?= __('terms_of_service') ?></a>
                </div>
            </div>
        </div>
    </div>
</footer>

<?php
// Ensure NavigationHelper is available in footer scope
if (!isset($nav) || !$nav instanceof \App\Helpers\NavigationHelper) {
    $nav = \App\Helpers\NavigationHelper::getInstance();
}
require __DIR__ . '/../components/navigation/mobile_bottom_nav.php';
?>

<!-- APS Premium Animations (scroll reveal + card tilt) -->
<script src="<?php echo BASE_URL; ?>/assets/js/premium-animations.js?v=20260725"></script>
<!-- APS Voice Booking Widget (component in base.php) -->

<script>
// Newsletter Form Handler
document.getElementById('newsletterForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const form = this;
    const messageDiv = document.getElementById('newsletterMessage');
    const emailInput = form.querySelector('input[name="email"]');
    
    fetch('<?php echo BASE_URL; ?>/subscribe', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'email=' + encodeURIComponent(emailInput.value)
    })
    .then(response => response.json())
    .then(data => {
        messageDiv.style.display = 'block';
        if (data.success) {
            messageDiv.innerHTML = '<span class="text-success"><i class="fas fa-check-circle"></i> ' + data.message + '</span>';
            emailInput.value = '';
        } else {
            messageDiv.innerHTML = '<span class="text-danger"><i class="fas fa-exclamation-circle"></i> ' + data.message + '</span>';
        }
        setTimeout(() => { messageDiv.style.display = 'none'; }, 5000);
    })
    .catch(error => {
        messageDiv.style.display = 'block';
        messageDiv.innerHTML = '<span class="text-danger"><i class="fas fa-exclamation-circle"></i> Something went wrong. Please try again.</span>';
    });
});
</script>
<?php
// gtag 'page_view' event with the actual document title + path. Emitted
// whenever GA4_MEASUREMENT_ID is non-empty. With the G-PLACEHOLDER default,
// the gtag() call is a no-op at the GA4 endpoint (unknown IDs are ignored),
// so this is safe to ship with the placeholder. Replace the env var in
// .env with a real G-XXXXXXXXXX to enable actual tracking.
$ga4_footer_id = $_ENV['GA4_MEASUREMENT_ID'] ?? getenv('GA4_MEASUREMENT_ID') ?: 'G-PLACEHOLDER';
$ga4_footer_id = is_string($ga4_footer_id) ? trim($ga4_footer_id) : 'G-PLACEHOLDER';
$ga4_footer_enabled = ($ga4_footer_id !== '');
if ($ga4_footer_enabled):
?>
<script>
  if (typeof gtag === 'function') {
    gtag('event', 'page_view', {
      page_title: document.title,
      page_path: window.location.pathname + window.location.search,
      page_location: window.location.href
    });
  }
</script>
<?php endif; ?>
<?php if (isset($GLOBALS['_html_doc_started']) && !isset($GLOBALS['_layout_handles_close'])): ?>
</body>
</html>
<?php endif; ?>