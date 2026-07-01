<div class="container-fluid py-4">
    <h4 class="mb-4"><i class="fas fa-envelope me-2"></i>Test Email</h4>
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form method="POST" action="<?= BASE_URL ?>/admin/communication/test-email">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                <div class="mb-3">
                    <label class="form-label">Recipient Email</label>
                    <input type="email" name="to_email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Subject</label>
                    <input type="text" name="subject" class="form-control" value="Test Email from APS Dream Home" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Message</label>
                    <textarea name="message" class="form-control" rows="5">This is a test email from APS Dream Home ERP system.</textarea>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane me-1"></i>Send Test Email</button>
            </form>
        </div>
    </div>
</div>
