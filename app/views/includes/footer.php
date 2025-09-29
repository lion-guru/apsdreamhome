<?php
/**
 * Enhanced Footer for APS Dream Home
 * Includes dynamic database-driven content with fallback
 */

// Dynamic footer with fallback
$dynamicFooterError = false;
try {
    // Try to load dynamic content from database
    if (file_exists('../admin/config.php')) {
        require_once '../admin/config.php';
    }
    if (file_exists('../includes/db_config.php')) {
        require_once '../includes/db_config.php';
    }

    $conn = function_exists('getDbConnection') ? getDbConnection() : null;

    // Get footer settings from database if available
    if ($conn) {
        $sql = "SELECT setting_name, setting_value FROM site_settings WHERE setting_name IN ('footer_about', 'footer_contact', 'footer_social_links', 'footer_copyright')";
        $result = $conn->query($sql);
        $footer_settings = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $footer_settings[$row['setting_name']] = $row['setting_value'];
            }
        }

        $footer_about = $footer_settings['footer_about'] ?? 'Your trusted partner in finding your dream property. We offer the best properties at the best prices.';
        $footer_contact = $footer_settings['footer_contact'] ?? '123 Property Street, City, State 12345 | Phone: +1 234 567 8900 | Email: info@apsdreamhome.com';
        $footer_copyright = $footer_settings['footer_copyright'] ?? 'APS Dream Home. All rights reserved.';
    } else {
        $footer_about = 'Your trusted partner in finding your dream property. We offer the best properties at the best prices.';
        $footer_contact = '123 Property Street, City, State 12345 | Phone: +1 234 567 8900 | Email: info@apsdreamhome.com';
        $footer_copyright = 'APS Dream Home. All rights reserved.';
    }
} catch (Throwable $e) {
    $dynamicFooterError = true;
    $footer_about = 'Your trusted partner in finding your dream property. We offer the best properties at the best prices.';
    $footer_contact = '123 Property Street, City, State 12345 | Phone: +1 234 567 8900 | Email: info@apsdreamhome.com';
    $footer_copyright = 'APS Dream Home. All rights reserved.';
}
?>
    </div>
    <footer class="bg-dark text-white mt-5">
        <div class="container py-4">
            <div class="row">
                <div class="col-md-4">
                    <h5>About APS Dream Home</h5>
                    <p><?php echo $footer_about; ?></p>
                </div>
                <div class="col-md-4">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="/about" class="text-white">About Us</a></li>
                        <li><a href="/properties" class="text-white">Properties</a></li>
                        <li><a href="/contact" class="text-white">Contact Us</a></li>
                        <li><a href="/privacy" class="text-white">Privacy Policy</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Contact Info</h5>
                    <address>
                        <?php echo $footer_contact; ?>
                    </address>
                </div>
            </div>
            <hr>
            <div class="text-center">
                <p>&copy; <?php echo date('Y'); ?> <?php echo $footer_copyright; ?></p>
            </div>
        </div>
    </footer>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/main.js"></script>
</body>
</html>
