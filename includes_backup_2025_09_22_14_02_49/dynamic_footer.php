<?php
try {
    require_once __DIR__ . '/../admin/config.php';
    require_once __DIR__ . '/../includes/db_config.php';

    // Fetch footer settings from database
    $conn = getDbConnection();
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
        $conn->close();
    }

    $footerContent = $settings['footer_content'] ?? '';
    $footerLinks = json_decode($settings['footer_links'] ?? '[]', true);
    $socialLinks = json_decode($settings['social_links'] ?? '[]', true);
?>

<footer class="site-footer bg-dark text-light py-5 mt-5">
    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-4">
                <h5>About APS Real Estate</h5>
                <p>Your trusted partner in real estate, providing quality properties and excellent service across India.</p>
                <div class="social-links mt-3">
                    <?php foreach ($socialLinks as $social): ?>
                        <a href="<?php echo htmlspecialchars($social['url']); ?>" class="text-light me-3" target="_blank">
                            <i class="fab <?php echo htmlspecialchars($social['icon']); ?> fa-lg"></i>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <h5>Quick Links</h5>
                <ul class="list-unstyled">
                    <?php foreach ($footerLinks as $link): ?>
                        <li class="mb-2">
                            <a href="<?php echo htmlspecialchars($link['url']); ?>" class="text-light text-decoration-none">
                                <?php echo htmlspecialchars($link['text']); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="col-md-4 mb-4">
                <h5>Contact Us</h5>
                <address class="text-light">
                    <p><i class="fas fa-map-marker-alt me-2"></i> APS Real Estate Office</p>
                    <p><i class="fas fa-phone me-2"></i> +91 XXXXXXXXXX</p>
                    <p><i class="fas fa-envelope me-2"></i> info@apsrealestate.com</p>
                </address>
            </div>
        </div>
        <hr class="bg-light">
        <div class="row">
            <div class="col-md-12 text-center">
                <p class="mb-0">&copy; <?php echo date('Y'); ?> APS Real Estate. All rights reserved.</p>
            </div>
        </div>
    </div>
</footer>

</body>
</html>
<?php
} catch (Throwable $e) {
    include __DIR__ . '/static_footer.php';
}
?>