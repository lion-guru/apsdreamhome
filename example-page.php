<?php
// Start session and include necessary files
session_start();
require_once __DIR__ . '/includes/config/config.php';
require_once __DIR__ . '/includes/functions/common-functions.php';

// Set page specific variables
$page_title = "Example Page - APS Dream Homes";
$meta_description = "Example page demonstrating the updated template structure for APS Dream Homes website";

// Additional CSS for this page (optional)
$additional_css = '<style>
    /* Page specific CSS */
    .example-section {
        padding: 60px 0;
        background-color: var(--bg-light);
    }
    .example-card {
        transition: all 0.3s ease;
    }
    .example-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
</style>';

// Include the updated common header
include(__DIR__ . '/includes/templates/header.php');
?>

<!-- Main Content Start -->
<div class="example-section">
    <div class="container">
        <div class="row">
            <div class="col-md-12 text-center mb-5">
                <h2 class="section-title">Example Page</h2>
                <p class="lead">This is an example page showing how to use the updated common header and footer.</p>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card example-card">
                    <div class="card-header">
                        <h4 class="m-0">Feature 1</h4>
                    </div>
                    <div class="card-body">
                        <p>This is an example card showing how to use the common CSS styles defined in optimized.css.</p>
                        <button class="btn btn-primary">Learn More</button>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="card example-card">
                    <div class="card-header">
                        <h4 class="m-0">Feature 2</h4>
                    </div>
                    <div class="card-body">
                        <p>All pages should use the updated common header and footer for consistency across the website.</p>
                        <button class="btn btn-primary">Learn More</button>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="card example-card">
                    <div class="card-header">
                        <h4 class="m-0">Feature 3</h4>
                    </div>
                    <div class="card-body">
                        <p>Use the updated-config-paths.php file to ensure all asset paths are consistent and maintainable.</p>
                        <button class="btn btn-primary">Learn More</button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-5">
            <div class="col-md-12">
                <div class="alert alert-info">
                    <h4>How to Use This Template</h4>
                    <p>This example page demonstrates how to create a new page using the updated common header and footer. Follow these steps:</p>
                    <ol>
                        <li>Include config.php, updated-config-paths.php, and common-functions.php at the top of your file</li>
                        <li>Set page-specific variables like $page_title, $meta_description, and $additional_css</li>
                        <li>Include updated-common-header.php</li>
                        <li>Add your page content</li>
                        <li>Set $additional_js if needed</li>
                        <li>Include updated-common-footer.php</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Main Content End -->

<?php
// Additional JS for this page (optional)
$additional_js = '<script>
    // Page specific JavaScript
    document.addEventListener("DOMContentLoaded", function() {
        const exampleCards = document.querySelectorAll(".example-card");
        
        exampleCards.forEach(card => {
            card.addEventListener("click", function() {
                alert("You clicked on a card!");
            });
        });
    });
</script>';

// Include the updated common footer
include(__DIR__ . '/includes/templates/footer.php');
?>