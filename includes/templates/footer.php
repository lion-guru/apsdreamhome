    </main> <!-- End main-content -->

    <footer id="main-footer" class="bg-dark text-white pt-5 pb-4">
        <div class="container text-center text-md-start">
            <div class="row text-center text-md-start">
                <div class="col-md-3 col-lg-3 col-xl-3 mx-auto mt-3">
                    <h5 class="text-uppercase mb-4 fw-bold text-warning">APS Dream Home</h5>
                    <p>
                        Your trusted partner in finding the perfect property. We offer a wide range of services to meet all your real estate needs.
                    </p>
                    <img src="<?php echo SITE_URL; ?>/assets/images/logo-white.png" alt="APS Dream Home Footer Logo" height="40" class="mt-2">
                </div>

                <div class="col-md-2 col-lg-2 col-xl-2 mx-auto mt-3">
                    <h5 class="text-uppercase mb-4 fw-bold text-warning">Properties</h5>
                    <p><a href="<?php echo SITE_URL; ?>/properties.php?type=sale" class="text-white" style="text-decoration: none;">For Sale</a></p>
                    <p><a href="<?php echo SITE_URL; ?>/properties.php?type=rent" class="text-white" style="text-decoration: none;">For Rent</a></p>
                    <p><a href="<?php echo SITE_URL; ?>/properties.php?featured=true" class="text-white" style="text-decoration: none;">Featured</a></p>
                    <p><a href="<?php echo SITE_URL; ?>/submit-property.php" class="text-white" style="text-decoration: none;">Submit Property</a></p>
                </div>

                <div class="col-md-3 col-lg-2 col-xl-2 mx-auto mt-3">
                    <h5 class="text-uppercase mb-4 fw-bold text-warning">Useful Links</h5>
                    <p><a href="<?php echo SITE_URL; ?>/about.php" class="text-white" style="text-decoration: none;">About Us</a></p>
                    <p><a href="<?php echo SITE_URL; ?>/services.php" class="text-white" style="text-decoration: none;">Services</a></p>
                    <p><a href="<?php echo SITE_URL; ?>/blog.php" class="text-white" style="text-decoration: none;">Blog</a></p>
                    <p><a href="<?php echo SITE_URL; ?>/faq.php" class="text-white" style="text-decoration: none;">FAQ</a></p>
                    <p><a href="<?php echo SITE_URL; ?>/terms.php" class="text-white" style="text-decoration: none;">Terms & Conditions</a></p>
                </div>

                <div class="col-md-4 col-lg-3 col-xl-3 mx-auto mt-3">
                    <h5 class="text-uppercase mb-4 fw-bold text-warning">Contact</h5>
                    <p><i class="fas fa-home me-3"></i>123 Real Estate St, Property City, PC 45678</p>
                    <p><i class="fas fa-envelope me-3"></i>info@apsdreamhome.com</p>
                    <p><i class="fas fa-phone me-3"></i>+91 98765 43210</p>
                    <p><i class="fas fa-print me-3"></i>+01 234 567 89</p> <!-- Example fax -->
                </div>
            </div>

            <hr class="mb-4">

            <div class="row align-items-center">
                <div class="col-md-7 col-lg-8">
                    <p class="text-center text-md-start mb-3 mb-md-0">
                        Copyright &copy; <?php echo date('Y'); ?> <a href="<?php echo SITE_URL; ?>" class="text-white fw-bold">APS Dream Home</a>. All rights reserved.
                    </p>
                </div>
                <div class="col-md-5 col-lg-4">
                    <div class="text-center text-md-end">
                        <ul class="list-unstyled list-inline mb-0">
                            <li class="list-inline-item">
                                <a href="#" class="btn-floating btn-sm text-white" style="font-size: 23px;"><i class="fab fa-facebook-f"></i></a>
                            </li>
                            <li class="list-inline-item">
                                <a href="#" class="btn-floating btn-sm text-white" style="font-size: 23px;"><i class="fab fa-twitter"></i></a>
                            </li>
                            <li class="list-inline-item">
                                <a href="#" class="btn-floating btn-sm text-white" style="font-size: 23px;"><i class="fab fa-linkedin-in"></i></a>
                            </li>
                            <li class="list-inline-item">
                                <a href="#" class="btn-floating btn-sm text-white" style="font-size: 23px;"><i class="fab fa-instagram"></i></a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <!-- Pass PHP variables to JavaScript -->
    <?php /* render_js_variables([ 
        'siteUrl' => SITE_URL,
        'apiUrl' => SITE_URL . '/api',
        'isLoggedIn' => isset($_SESSION['user_id']),
        // Add other global JS variables here
    ]); */ ?>

    <!-- Core JS -->
    <!-- jQuery 3.6.0 (required for Bootstrap) -->
    <script defer src="<?php echo SITE_URL; ?>/assets/js/jquery.min.js"
            onerror="this.onerror=null; this.src='https://code.jquery.com/jquery-3.6.0.min.js'"></script>
    
    <!-- Bootstrap 5.3.0 JS Bundle with Popper -->
    <script defer src="<?php echo SITE_URL; ?>/assets/js/bootstrap.bundle.min.js"
            onerror="this.onerror=null; this.src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'"></script>
    
    <!-- Swiper JS -->
    <script defer src="<?php echo SITE_URL; ?>/assets/plugins/swiper/swiper-bundle.min.js"
            onerror="this.onerror=null; this.src='https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.js'"></script>
    <script defer src="<?php echo SITE_URL; ?>/assets/plugins/aos/aos.js"></script>

    <!-- Custom JS (defer loading) -->
    <script defer src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
    
    <?php if (isset($page_data['custom_js']) && is_array($page_data['custom_js'])): ?>
        <?php foreach ($page_data['custom_js'] as $script_path): ?>
    <script defer src="<?php echo SITE_URL . e($script_path); ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php /* Performance metrics block disabled by default */ ?>
    <?php if (false): ?>
    <div id="performance-metrics" style="position: fixed; bottom: 0; right: 0; background: rgba(0,0,0,0.7); color: white; padding: 10px; font-size: 12px; z-index: 9999;">
        <?php 
        $metrics = get_performance_metrics();
        echo "Page loaded in: " . e($metrics['execution_time']) . " s | ";
        echo "Memory usage: " . e($metrics['memory_usage']) . " MB | ";
        echo "DB queries: " . e($conn->query_count ?? 0);
        ?>
    </div>
    <?php endif; ?>

</body>
</html>
