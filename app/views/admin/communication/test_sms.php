<?php
/**
 * Admin Communication - Test SMS
 */
$page_title = $page_title ?? 'Test SMS - APS Dream Home';
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
                    <li class="breadcrumb-item active" aria-current="page">Test SMS</li>
                </ol>
            </nav>
            <h2 class="h3 mb-0 text-gray-800"><i class="fas fa-sms me-2 text-primary"></i> Send Test SMS</h2>
        </div>
        <a href="<?= $base ?>/admin/communication/queue" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Back to Queue
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 text-primary"><i class="fas fa-comment-dots me-2"></i> Compose Test SMS</h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="<?= $base ?>/admin/communication/send-test-sms">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

                        <div class="mb-3">
                            <label class="form-label fw-bold">Recipient Mobile Number <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">+91</span>
                                <input type="text" name="to_phone" class="form-control" placeholder="9876543210" required pattern="[0-9]{10}">
                            </div>
                            <small class="text-muted">Enter 10-digit mobile number to verify SMS gateway configuration.</small>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">SMS Message Content</label>
                            <textarea name="message" rows="4" class="form-control" maxlength="160" required>APS Dream Home: This is a test SMS from the administrative console to verify SMS gateway integration.</textarea>
                            <small class="text-muted">Standard SMS length is up to 160 characters.</small>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="<?= $base ?>/admin/communication/queue" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-1"></i> Send Test SMS Now
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
