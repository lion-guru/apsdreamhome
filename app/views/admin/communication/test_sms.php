<div class="container-fluid py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>admin/dashboard"><i class="fas fa-home"></i> Dashboard</a></li>
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>admin/communication/queue">Communication Queue</a></li>
            <li class="breadcrumb-item active">Test SMS</li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-sms me-2"></i>Send Test SMS</h4>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form method="post" action="<?= BASE_URL ?>admin/communication/test-sms">
                        <div class="mb-3">
                            <label class="form-label">Phone Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="to_phone" required placeholder="+91 9876543210">
                            <div class="form-text">Enter full number with country code</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Message <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="message" rows="4" maxlength="160" required>Test SMS from APS Dream Home. Your communication system is working!</textarea>
                            <div class="form-text">Max 160 characters for single SMS</div>
                        </div>
                        <button type="submit" class="btn btn-success"><i class="fas fa-paper-plane me-2"></i>Send Test SMS</button>
                        <a href="<?= BASE_URL ?>admin/communication/queue" class="btn btn-outline-secondary ms-2">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>SMS Provider</h6></div>
                <div class="card-body">
                    <p class="small text-muted mb-2">Current provider: <strong><?= htmlspecialchars($_ENV['SMS_PROVIDER'] ?? 'log') ?></strong></p>
                    <p class="small text-muted mb-2">In <strong>log</strong> mode, SMS is written to storage/logs/sms.log instead of being sent via API.</p>
                    <p class="small text-muted mb-0">Set <code>SMS_PROVIDER=twilio</code> or <code>SMS_PROVIDER=msg91</code> in .env for real delivery.</p>
                </div>
            </div>
        </div>
    </div>
</div>
