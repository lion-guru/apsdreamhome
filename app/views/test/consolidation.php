<?php
// Set page title and layout
$title = 'Test Page - View Consolidation';
$layout = 'modern';

// Capture the content for layout injection
ob_start();
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h3 class="mb-0">
                    <i class="fas fa-check-circle me-2"></i>View Consolidation Test
                </h3>
            </div>
            <div class="card-body">
                <h4>Modern Layout Integration Successful!</h4>
                <p>This page demonstrates that the view consolidation is working correctly.</p>
                
                <div class="row mt-4">
                    <div class="col-md-6">
                        <h5>Features Tested:</h5>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-success me-2"></i>Modern layout integration</li>
                            <li><i class="fas fa-check text-success me-2"></i>Bootstrap 5 styling</li>
                            <li><i class="fas fa-check text-success me-2"></i>Font Awesome icons</li>
                            <li><i class="fas fa-check text-success me-2"></i>Poppins font family</li>
                            <li><i class="fas fa-check text-success me-2"></i>Responsive design</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h5>Navigation Test:</h5>
                        <div class="d-grid gap-2">
                            <a href="/auth/login" class="btn btn-primary">
                                <i class="fas fa-sign-in-alt me-2"></i>Test Login Page
                            </a>
                            <a href="/auth/register" class="btn btn-success">
                                <i class="fas fa-user-plus me-2"></i>Test Register Page
                            </a>
                            <a href="/" class="btn btn-outline-secondary">
                                <i class="fas fa-home me-2"></i>Back to Home
                            </a>
                        </div>
                    </div>
                </div>
                
                <hr class="my-4">
                
                <div class="alert alert-info">
                    <h6>View Consolidation Status:</h6>
                    <p class="mb-0">Phase 3 of the implementation plan is progressing successfully. The modern layout has been created and authentication views have been migrated to use the unified templating system.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();

// Include the layout
require_once __DIR__ . '/../layouts/' . $layout . '.php';
?>