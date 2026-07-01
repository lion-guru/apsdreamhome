<div class="container-fluid py-4">
    <h4 class="mb-4"><i class="fas fa-bell me-2"></i>Send Test Notification</h4>
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form method="POST" action="<?= BASE_URL ?>/notification/send-test">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                <div class="mb-3">
                    <label class="form-label">Recipient</label>
                    <select name="user_id" class="form-select" required>
                        <option value="">Select User</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Title</label>
                    <input type="text" name="title" class="form-control" value="Test Notification" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Message</label>
                    <textarea name="message" class="form-control" rows="3" required>This is a test notification from the ERP system.</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Channel</label>
                    <select name="channel" class="form-select">
                        <option value="in_app">In-App</option>
                        <option value="email">Email</option>
                        <option value="sms">SMS</option>
                        <option value="push">Push Notification</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane me-1"></i>Send Test</button>
            </form>
        </div>
    </div>
</div>
