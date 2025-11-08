<?php
/**
 * Sitemap - APS Dream Home
 */
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sitemap - APS Dream Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container py-5">
        <div class="row">
            <div class="col-12">
                <h1 class="mb-4">Sitemap</h1>

                <div class="card">
                    <div class="card-body">
                        <h5>Main Pages</h5>
                        <ul class="list-unstyled">
                            <li><a href="<?php echo (defined('BASE_URL') ? BASE_URL : '/'); ?>">Home</a></li>
                            <li><a href="<?php echo (defined('BASE_URL') ? BASE_URL : '/'); ?>properties">Properties</a></li>
                            <li><a href="<?php echo (defined('BASE_URL') ? BASE_URL : '/'); ?>projects">Projects</a></li>
                            <li><a href="<?php echo (defined('BASE_URL') ? BASE_URL : '/'); ?>about">About Us</a></li>
                            <li><a href="<?php echo (defined('BASE_URL') ? BASE_URL : '/'); ?>contact">Contact</a></li>
                        </ul>

                        <h5 class="mt-4">User Services</h5>
                        <ul class="list-unstyled">
                            <li><a href="<?php echo (defined('BASE_URL') ? BASE_URL : '/'); ?>login">Login</a></li>
                            <li><a href="<?php echo (defined('BASE_URL') ? BASE_URL : '/'); ?>register">Register</a></li>
                            <li><a href="<?php echo (defined('BASE_URL') ? BASE_URL : '/'); ?>customer-dashboard">Customer Dashboard</a></li>
                            <li><a href="<?php echo (defined('BASE_URL') ? BASE_URL : '/'); ?>associate-dashboard">Associate Dashboard</a></li>
                        </ul>

                        <h5 class="mt-4">Business Services</h5>
                        <ul class="list-unstyled">
                            <li><a href="<?php echo (defined('BASE_URL') ? BASE_URL : '/'); ?>associate">Join as Associate</a></li>
                            <li><a href="<?php echo (defined('BASE_URL') ? BASE_URL : '/'); ?>property-management">Property Management</a></li>
                            <li><a href="<?php echo (defined('BASE_URL') ? BASE_URL : '/'); ?>legal-services">Legal Services</a></li>
                            <li><a href="<?php echo (defined('BASE_URL') ? BASE_URL : '/'); ?>financial-services">Financial Services</a></li>
                        </ul>

                        <h5 class="mt-4">Information</h5>
                        <ul class="list-unstyled">
                            <li><a href="<?php echo (defined('BASE_URL') ? BASE_URL : '/'); ?>blog">Blog</a></li>
                            <li><a href="<?php echo (defined('BASE_URL') ? BASE_URL : '/'); ?>news">News & Updates</a></li>
                            <li><a href="<?php echo (defined('BASE_URL') ? BASE_URL : '/'); ?>gallery">Gallery</a></li>
                            <li><a href="<?php echo (defined('BASE_URL') ? BASE_URL : '/'); ?>testimonials">Testimonials</a></li>
                            <li><a href="<?php echo (defined('BASE_URL') ? BASE_URL : '/'); ?>faq">FAQs</a></li>
                        </ul>

                        <h5 class="mt-4">Legal</h5>
                        <ul class="list-unstyled">
                            <li><a href="<?php echo (defined('BASE_URL') ? BASE_URL : '/'); ?>privacy-policy">Privacy Policy</a></li>
                            <li><a href="<?php echo (defined('BASE_URL') ? BASE_URL : '/'); ?>terms-of-service">Terms of Service</a></li>
                        </ul>
                    </div>
                </div>

                <div class="mt-4">
                    <a href="<?php echo (defined('BASE_URL') ? BASE_URL : '/'); ?>" class="btn btn-primary">Back to Home</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
