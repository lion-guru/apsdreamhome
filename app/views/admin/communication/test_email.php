<?php
/**
 * Admin Communication - Test Email
 */
$page_title = $page_title ?? 'Test Email - APS Dream Home';
$base = defined('BASE_URL') ? BASE_URL : '';
$csrf_token = $_SESSION['csrf_token'] ?? '';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="<?= $base ?>/admin/dashboard">Admin</a></li>
                    <li class="breadcrumb-item"><a href="<?= $base ?>/admin/communication/queue">Communication</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Test Email</li>
                </ol>
            </nav>
            <h2 class="h3 mb-0 text-gray-800"><i class="fas fa-paper-plane me-2 text-primary"></i> Send Test Email</h2>
        </div>
        <a href="<?= $base ?>/admin/communication/queue" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Back to Queue
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 text-primary"><i class="fas fa-envelope me-2"></i> Compose Test Email</h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="<?= $base ?>/admin/communication/test-email">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

                        <div class="mb-3">
                            <label class="form-label fw-bold">Recipient Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="to_email" class="form-control" placeholder="recipient@example.com" required>
                            <small class="text-muted">Enter a valid email address to verify SMTP deliverability.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Email Subject</label>
                            <input type="text" name="subject" class="form-control" value="Test Email from APS Dream Home" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Message Content</label>
                            <textarea name="message" rows="5" class="form-control" required>This is a test email sent from the APS Dream Home admin panel to verify mail delivery configuration.</textarea>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="<?= $base ?>/admin/communication/queue" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-1"></i> Send Test Email Now
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
