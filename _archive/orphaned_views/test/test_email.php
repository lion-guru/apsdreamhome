<div class="container-fluid py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>admin/dashboard"><i class="fas fa-home"></i> Dashboard</a></li>
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>admin/communication/queue">Communication Queue</a></li>
            <li class="breadcrumb-item active">Test Email</li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-envelope me-2"></i>Send Test Email</h4>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form method="post" action="<?= BASE_URL ?>admin/communication/test-email">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="mb-3">
                            <label class="form-label">To Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" name="to_email" required placeholder="recipient@example.com">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Subject <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="subject" value="Test Email from APS Dream Home" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Message <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="message" rows="6" required>This is a test email sent from the APS Dream Home admin panel.</textarea>
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane me-2"></i>Send Test Email</button>
                        <a href="<?= BASE_URL ?>admin/communication/queue" class="btn btn-outline-secondary ms-2">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Notes</h6></div>
                <div class="card-body">
                    <p class="small text-muted mb-2">This will send a real email using the configured SMTP settings.</p>
                    <p class="small text-muted mb-2">Check the <a href="<?= BASE_URL ?>admin/settings/email">Email Settings</a> page to configure SMTP.</p>
                    <p class="small text-muted mb-0">Results appear in the Communication Queue.</p>
                </div>
            </div>
        </div>
    </div>
</div>
