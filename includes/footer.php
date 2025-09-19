<?php
/**
 * Dynamic Footer Component
 * Include this file in your pages using: include_once __DIR__ . '/includes/footer.php';
 */

// Database connection
$conn = null;
try {
    require_once __DIR__ . '/../admin/config.php';
    require_once __DIR__ . '/db_config.php';
    $conn = getDbConnection();
} catch (Exception $e) {
    error_log("Database connection error: " . $e->getMessage());
}

// Default values
$footerData = [
    'company_name' => 'APS Dream Home',
    'tagline' => 'Your Trusted Real Estate Partner',
    'about_text' => 'We provide the best real estate services with a focus on customer satisfaction and quality properties.',
    'address' => '123 Dream Avenue, Mumbai, Maharashtra 400001',
    'phone' => '+91 98765 43210',
    'email' => 'info@apsdreamhome.com',
    'working_hours' => 'Mon-Sat: 9:00 AM - 8:00 PM',
    'copyright' => '&copy; ' . date('Y') . ' APS Dream Home. All rights reserved.',
    'links' => [
        ['title' => 'Home', 'url' => 'index.php'],
        ['title' => 'Properties', 'url' => 'properties.php'],
        ['title' => 'About Us', 'url' => 'about.php'],
        ['title' => 'Contact', 'url' => 'contact.php'],
        ['title' => 'Privacy Policy', 'url' => 'privacy-policy.php'],
        ['title' => 'Terms & Conditions', 'url' => 'terms.php']
    ],
    'social_links' => [
        ['icon' => 'fab fa-facebook-f', 'url' => '#', 'title' => 'Facebook'],
        ['icon' => 'fab fa-twitter', 'url' => '#', 'title' => 'Twitter'],
        ['icon' => 'fab fa-instagram', 'url' => '#', 'title' => 'Instagram'],
        ['icon' => 'fab fa-linkedin-in', 'url' => '#', 'title' => 'LinkedIn'],
        ['icon' => 'fab fa-youtube', 'url' => '#', 'title' => 'YouTube']
    ]
];

// Fetch dynamic content from database if available
if ($conn) {
    try {
        $stmt = $conn->query("SELECT * FROM site_settings WHERE setting_group = 'footer'");
        while ($row = $stmt->fetch_assoc()) {
            $key = str_replace('footer_', '', $row['setting_name']);
            $value = $row['setting_value'];
            
            // Handle JSON fields
            if (in_array($key, ['links', 'social_links'])) {
                $value = json_decode($value, true) ?: [];
            }
            
            $footerData[$key] = $value;
        }
    } catch (Exception $e) {
        error_log("Error fetching footer data: " . $e->getMessage());
    }
}
?>

<!-- Footer Start -->
<footer class="footer position-relative bg-dark text-white pt-5 pb-4">
    <!-- Decorative Elements -->
    <div class="footer-shape position-absolute top-0 start-0 w-100 h-100">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
    </div>
    
    <div class="container position-relative">
        <div class="row g-4">
            <!-- Company Info -->
            <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
                <div class="footer-about">
                    <a href="index.php" class="d-inline-block mb-3">
                        <h2 class="text-white mb-2"><?php echo htmlspecialchars($footerData['company_name']); ?></h2>
                    </a>
                    <p class="text-white-50 mb-4"><?php echo htmlspecialchars($footerData['tagline']); ?></p>
                    <p class="text-white-50"><?php echo htmlspecialchars($footerData['about_text']); ?></p>
                    
                    <!-- Social Icons -->
                    <div class="social-links mt-4">
                        <?php foreach ($footerData['social_links'] as $social): ?>
                            <a href="<?php echo htmlspecialchars($social['url']); ?>" 
                               class="text-white me-2 mb-2" 
                               title="<?php echo htmlspecialchars($social['title']); ?>"
                               target="_blank" 
                               rel="noopener noreferrer">
                                <i class="<?php echo htmlspecialchars($social['icon']); ?> fa-lg"></i>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <!-- Quick Links -->
            <div class="col-lg-2 col-md-6 col-6 mb-4 mb-md-0">
                <h5 class="text-white mb-4">Quick Links</h5>
                <ul class="footer-links list-unstyled">
                    <?php 
                    $quickLinks = array_slice($footerData['links'], 0, 6);
                    foreach ($quickLinks as $link): 
                    ?>
                        <li class="mb-2">
                            <a href="<?php echo htmlspecialchars($link['url']); ?>" 
                               class="text-white-50 text-decoration-none d-flex align-items-center">
                                <i class="fas fa-chevron-right me-2 small text-primary"></i>
                                <?php echo htmlspecialchars($link['title']); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            
            <!-- Property Types -->
            <div class="col-lg-2 col-md-6 col-6">
                <h5 class="text-white mb-4">Property Types</h5>
                <ul class="footer-links list-unstyled">
                    <li class="mb-2">
                        <a href="#" class="text-white-50 text-decoration-none d-flex align-items-center">
                            <i class="fas fa-chevron-right me-2 small text-primary"></i>
                            Apartments
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="#" class="text-white-50 text-decoration-none d-flex align-items-center">
                            <i class="fas fa-chevron-right me-2 small text-primary"></i>
                            Villas
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="#" class="text-white-50 text-decoration-none d-flex align-items-center">
                            <i class="fas fa-chevron-right me-2 small text-primary"></i>
                            Plots
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="#" class="text-white-50 text-decoration-none d-flex align-items-center">
                            <i class="fas fa-chevron-right me-2 small text-primary"></i>
                            Commercial
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="#" class="text-white-50 text-decoration-none d-flex align-items-center">
                            <i class="fas fa-chevron-right me-2 small text-primary"></i>
                            Farm Houses
                        </a>
                    </li>
                </ul>
            </div>
            
            <!-- Contact Info -->
            <div class="col-lg-5 col-md-6">
                <h5 class="text-white mb-4">Contact Us</h5>
                <ul class="contact-info list-unstyled">
                    <li class="d-flex mb-4">
                        <div class="icon-box flex-shrink-0 me-3">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div>
                            <h6 class="text-white mb-1">Our Location</h6>
                            <p class="mb-0 text-white-50"><?php echo nl2br(htmlspecialchars($footerData['address'])); ?></p>
                        </div>
                    </li>
                    <li class="d-flex mb-4">
                        <div class="icon-box flex-shrink-0 me-3">
                            <i class="fas fa-phone-alt"></i>
                        </div>
                        <div>
                            <h6 class="text-white mb-1">Phone Number</h6>
                            <a href="tel:<?php echo preg_replace('/[^0-9+]/', '', $footerData['phone']); ?>" class="text-white-50 text-decoration-none">
                                <?php echo htmlspecialchars($footerData['phone']); ?>
                            </a>
                        </div>
                    </li>
                    <li class="d-flex mb-4">
                        <div class="icon-box flex-shrink-0 me-3">
                            <i class="far fa-envelope"></i>
                        </div>
                        <div>
                            <h6 class="text-white mb-1">Email Address</h6>
                            <a href="mailto:<?php echo htmlspecialchars($footerData['email']); ?>" class="text-white-50 text-decoration-none">
                                <?php echo htmlspecialchars($footerData['email']); ?>
                            </a>
                        </div>
                    </li>
                    <li class="d-flex">
                        <div class="icon-box flex-shrink-0 me-3">
                            <i class="far fa-clock"></i>
                        </div>
                        <div>
                            <h6 class="text-white mb-1">Working Hours</h6>
                            <p class="mb-0 text-white-50"><?php echo htmlspecialchars($footerData['working_hours']); ?></p>
                        </div>
                    </li>
                </ul>
                
                <!-- Newsletter -->
                <div class="newsletter mt-4">
                    <h5 class="text-white mb-3">Newsletter</h5>
                    <p class="text-white-50 mb-3">Subscribe to our newsletter for updates on new properties and offers.</p>
                    <form id="newsletterForm" class="d-flex" novalidate>
                        <input type="email" class="form-control" placeholder="Your email address" required>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="far fa-paper-plane"></i>
                        </button>
                    </form>
                    <div class="form-text text-white-50 mt-2">
                        <i class="fas fa-lock me-2"></i>We respect your privacy. Unsubscribe at any time.
                    </div>
                </div>
            </div>
        </div>
        
        <hr class="my-4 bg-secondary">
        
        <!-- Copyright -->
        <div class="row align-items-center">
            <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
                <p class="mb-0 text-white-50">
                    <?php echo $footerData['copyright']; ?>
                </p>
            </div>
            <div class="col-md-6 text-center text-md-end">
                <ul class="list-inline mb-0">
                    <li class="list-inline-item">
                        <a href="#" class="text-white-50 text-decoration-none">Privacy Policy</a>
                    </li>
                    <li class="list-inline-item mx-2">•</li>
                    <li class="list-inline-item">
                        <a href="#" class="text-white-50 text-decoration-none">Terms of Service</a>
                    </li>
                    <li class="list-inline-item mx-2">•</li>
                    <li class="list-inline-item">
                        <a href="#" class="text-white-50 text-decoration-none">Sitemap</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</footer>

<!-- Back to Top Button -->
<a href="#" id="backToTop" class="back-to-top">
    <i class="fas fa-arrow-up"></i>
</a>

<!-- Include Footer JS -->
<script src="<?php echo $base_url; ?>assets/js/footer.js"></script>

<!-- Custom Footer Styles -->
<style>
<?php include __DIR__ . '/../assets/css/footer.css'; ?>
</style>

<!-- Close HTML if not already closed -->
<?php if (!isset($no_footer) || !$no_footer): ?>
</body>
</html>
<?php endif; ?>

<!-- Footer Section -->
<footer class="full-row bg-secondary p-0">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="divider py-40">
                    <div class="row">
                        <div class="col-md-12 col-lg-4">
                            <div class="footer-widget mb-4">
                                <div class="footer-logo mb-4">
                                    <a href="index.php"><img class="logo-bottom" src="images/logo/restatelg.png" alt="image"></a>
                                </div>
                                <p class="pb-20 text-white">APS Dream Homes is a trusted real estate company offering premium residential and commercial properties across Uttar Pradesh. We are committed to providing quality housing solutions with excellent customer service.</p>
                                <div class="social-media">
                                    <a href="#" class="facebook"><i class="fab fa-facebook-f"></i></a>
                                    <a href="#" class="twitter"><i class="fab fa-twitter"></i></a>
                                    <a href="#" class="linkedin"><i class="fab fa-linkedin-in"></i></a>
                                    <a href="#" class="instagram"><i class="fab fa-instagram"></i></a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12 col-lg-8">
                            <div class="row">
                                <div class="col-md-4 col-lg-4">
                                    <div class="footer-widget footer-nav mb-4">
                                        <h4 class="widget-title text-white double-down-line-left position-relative">Quick Links</h4>
                                        <ul class="hover-text-primary">
                                            <li><a href="index.php" class="text-white">Home</a></li>
                                            <li><a href="about.php" class="text-white">About Us</a></li>
                                            <li><a href="gallary.php" class="text-white">Gallery</a></li>
                                            <li><a href="property.php" class="text-white">Properties</a></li>
                                            <li><a href="contact.php" class="text-white">Contact Us</a></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-md-4 col-lg-4">
                                    <div class="footer-widget footer-nav mb-4">
                                        <h4 class="widget-title text-white double-down-line-left position-relative">Projects</h4>
                                        <ul class="hover-text-primary">
                                            <li><a href="gorakhpur-suryoday-colony.php" class="text-white">Suryoday Colony</a></li>
                                            <li><a href="gorakhpur-raghunath-nagri.php" class="text-white">Raghunath Nagri</a></li>
                                            <li><a href="lucknow-ram-nagri.php" class="text-white">Ram Nagri</a></li>
                                            <li><a href="lucknow-project.php" class="text-white">Nawab City</a></li>
                                            <li><a href="budhacity.php" class="text-white">Budha City</a></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-md-4 col-lg-4">
                                    <div class="footer-widget">
                                        <h4 class="widget-title text-white double-down-line-left position-relative">Contact Us</h4>
                                        <ul class="text-white">
                                            <li class="hover-text-primary"><i class="fas fa-map-marker-alt text-white mr-2 font-13 mt-1"></i>APS Dream Homes, Gorakhpur, Uttar Pradesh</li>
                                            <li class="hover-text-primary"><i class="fas fa-phone-alt text-white mr-2 font-13 mt-1"></i>+91 7007444842</li>
                                            <li class="hover-text-primary"><i class="fas fa-envelope text-white mr-2 font-13 mt-1"></i>apsdreamhomes44@gmail.com</li>
                                        </ul>
                                    </div>
                                    <div class="footer-widget media-widget mt-4 text-white hover-text-primary">
                                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                                        <a href="#"><i class="fab fa-twitter"></i></a>
                                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                                        <a href="#"><i class="fab fa-google-plus-g"></i></a>
                                        <a href="#"><i class="fab fa-pinterest-p"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="copyright-text">
                    <p class="text-white">© <?php echo date('Y'); ?> APS Dream Homes. All Rights Reserved.</p>
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- Back to Top Button -->
<a href="#" id="backToTop" class="back-to-top">
    <i class="fas fa-arrow-up"></i>
</a>

<!-- Include Footer JS -->
<script src="<?php echo $base_url; ?>assets/js/footer.js"></script>

<!-- Custom Footer Styles -->
<style>
<?php include __DIR__ . '/../assets/css/footer.css'; ?>
</style>

<!-- Close HTML if not already closed -->
<?php if (!isset($no_footer) || !$no_footer): ?>
</body>
</html>
<?php endif; ?>
