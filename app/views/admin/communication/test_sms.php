<div class="container-fluid py-4">
    <h4 class="mb-4"><i class="fas fa-sms me-2"></i>Test SMS</h4>
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form method="POST" action="<?= BASE_URL ?>/admin/communication/test-sms">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                <div class="mb-3">
                    <label class="form-label">Recipient Phone</label>
                    <input type="tel" name="phone" class="form-control" placeholder="+91XXXXXXXXXX" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Message</label>
                    <textarea name="message" class="form-control" rows="3">Test SMS from APS Dream Home ERP system.</textarea>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-sms me-1"></i>Send Test SMS</button>
            </form>
        </div>
    </div>
</div>
