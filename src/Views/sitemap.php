<?php
// APS Dream Homes - Site Map / Explore Page
$page_title = "Explore the Site - APS Dream Homes";
$meta_description = "Explore all the public pages and features of APS Dream Homes, including properties, news, contact, and more.";
$additional_css = '<link rel="stylesheet" href="' . get_asset_url('css/home.css', 'assets') . '">';
require_once __DIR__ . '/includes/templates/dynamic_header.php';
?>

<section class="section-padding bg-light">
    <div class="container">
        <h1 class="fw-bold text-primary mb-4 text-center">Explore Our Site</h1>
        <p class="lead text-secondary text-center mb-5">Browse all sections and features available to our users</p>
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="explore-card bg-white shadow-lg rounded-4 p-4 h-100 text-center">
                            <i class="fas fa-home fa-2x text-primary mb-3"></i>
                            <h4 class="fw-bold mb-2">Home</h4>
                            <p class="text-secondary mb-2">Start your journey with our modern homepage.</p>
                            <a href="<?php echo $base_url; ?>index.php" class="btn btn-outline-primary rounded-pill">Go to Home</a>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="explore-card bg-white shadow-lg rounded-4 p-4 h-100 text-center">
                            <i class="fas fa-building fa-2x text-primary mb-3"></i>
                            <h4 class="fw-bold mb-2">Property Listings</h4>
                            <p class="text-secondary mb-2">Browse all available properties for sale or rent.</p>
                            <a href="<?php echo $base_url; ?>property-listings.php" class="btn btn-outline-primary rounded-pill">View Properties</a>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="explore-card bg-white shadow-lg rounded-4 p-4 h-100 text-center">
                            <i class="fas fa-info-circle fa-2x text-primary mb-3"></i>
                            <h4 class="fw-bold mb-2">About Us</h4>
                            <p class="text-secondary mb-2">Learn about our company, mission, and team.</p>
                            <a href="<?php echo $base_url; ?>about.php" class="btn btn-outline-primary rounded-pill">About APS</a>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="explore-card bg-white shadow-lg rounded-4 p-4 h-100 text-center">
                            <i class="fas fa-envelope fa-2x text-primary mb-3"></i>
                            <h4 class="fw-bold mb-2">Contact</h4>
                            <p class="text-secondary mb-2">Get in touch with our team for any queries.</p>
                            <a href="<?php echo $base_url; ?>contact.php" class="btn btn-outline-primary rounded-pill">Contact Us</a>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="explore-card bg-white shadow-lg rounded-4 p-4 h-100 text-center">
                            <i class="fas fa-newspaper fa-2x text-primary mb-3"></i>
                            <h4 class="fw-bold mb-2">News & Updates</h4>
                            <p class="text-secondary mb-2">Read the latest news and announcements.</p>
                            <a href="<?php echo $base_url; ?>news.php" class="btn btn-outline-primary rounded-pill">See News</a>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="explore-card bg-white shadow-lg rounded-4 p-4 h-100 text-center">
                            <i class="fas fa-user-plus fa-2x text-primary mb-3"></i>
                            <h4 class="fw-bold mb-2">Register</h4>
                            <p class="text-secondary mb-2">Create your account to access more features.</p>
                            <a href="<?php echo $base_url; ?>register.php" class="btn btn-outline-primary rounded-pill">Register</a>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="explore-card bg-white shadow-lg rounded-4 p-4 h-100 text-center">
                            <i class="fas fa-sign-in-alt fa-2x text-primary mb-3"></i>
                            <h4 class="fw-bold mb-2">Login</h4>
                            <p class="text-secondary mb-2">Access your personalized dashboard.</p>
                            <a href="<?php echo $base_url; ?>login.php" class="btn btn-outline-primary rounded-pill">Login</a>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="explore-card bg-white shadow-lg rounded-4 p-4 h-100 text-center">
                            <i class="fas fa-tachometer-alt fa-2x text-primary mb-3"></i>
                            <h4 class="fw-bold mb-2">User Dashboard</h4>
                            <p class="text-secondary mb-2">Manage your profile, properties, and more.</p>
                            <a href="<?php echo $base_url; ?>user_dashboard.php" class="btn btn-outline-primary rounded-pill">Dashboard</a>
                        </div>
                    </div>
                    <!-- Add more sections as needed -->
                </div>
            </div>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/includes/templates/dynamic_footer.php'; ?>
