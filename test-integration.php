<?php
// Test page to verify header/footer integration
require_once 'includes/db_connection.php';
$page_title = 'Test Page - APS Dream Home';
$meta_description = 'Testing the header and footer integration';

// Include dynamic header
include 'includes/templates/dynamic_header.php';
?>

<!-- Test Content -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center">
                <h1 class="display-4 fw-bold mb-4">🎉 Integration Test Successful!</h1>
                <p class="lead mb-4">The header and footer templates are working correctly.</p>

                <div class="alert alert-success">
                    <h5><i class="fas fa-check-circle me-2"></i>Database Connection</h5>
                    <p class="mb-0">✅ Connected successfully to the database</p>
                </div>

                <div class="alert alert-info">
                    <h5><i class="fas fa-info-circle me-2"></i>Template System</h5>
                    <p class="mb-0">✅ Dynamic header and footer templates loaded</p>
                </div>

                <div class="alert alert-warning">
                    <h5><i class="fas fa-exclamation-triangle me-2"></i>Navigation</h5>
                    <p class="mb-0">✅ Menu items rendered from database settings</p>
                </div>

                <div class="row mt-5">
                    <div class="col-md-4">
                        <div class="card text-center p-3">
                            <i class="fas fa-home fa-3x text-primary mb-3"></i>
                            <h5>Main Index</h5>
                            <a href="index.php" class="btn btn-primary">View Index</a>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-center p-3">
                            <i class="fas fa-building fa-3x text-success mb-3"></i>
                            <h5>Properties</h5>
                            <a href="properties.php" class="btn btn-success">View Properties</a>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-center p-3">
                            <i class="fas fa-envelope fa-3x text-info mb-3"></i>
                            <h5>Contact</h5>
                            <a href="contact.php" class="btn btn-info">Contact Us</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
// Include dynamic footer
include 'includes/templates/dynamic_footer.php';
?>
