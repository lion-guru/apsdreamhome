<?php
$page_title = $page_title ?? 'Legal Pages';
$terms_content = $terms_content ?? '';
$privacy_content = $privacy_content ?? '';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">Legal Pages</h1>
        <p class="text-muted mb-0">Manage Terms & Conditions and Privacy Policy</p>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-file-contract me-2"></i>Terms & Conditions</h5>
            </div>
            <div class="card-body">
                <p class="text-muted">Manage your terms and conditions page content.</p>
                <a href="#" class="btn btn-outline-primary">
                    <i class="fas fa-edit me-2"></i>Edit Terms
                </a>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-shield-alt me-2"></i>Privacy Policy</h5>
            </div>
            <div class="card-body">
                <p class="text-muted">Manage your privacy policy page content.</p>
                <a href="#" class="btn btn-outline-primary">
                    <i class="fas fa-edit me-2"></i>Edit Privacy Policy
                </a>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Recent Updates</h5>
    </div>
    <div class="card-body">
        <p class="text-muted">No recent updates to legal pages.</p>
    </div>
</div>