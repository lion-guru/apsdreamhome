<div class="container-fluid py-4">
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/push-notifications">Push Notifications</a></li>
                <li class="breadcrumb-item active">Send Push</li>
            </ol>
        </nav>
        <h1 class="h3 mb-1 fw-bold">Send Push Notification</h1>
        <p class="text-muted mb-0">Send a push notification to all subscribers or a specific user</p>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form id="pushForm" onsubmit="return sendPush(event)">
                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Target</label>
                            <select name="target_user" class="form-select">
                                <option value="all">All Subscribers (<?= number_format($stats['total_subscribers'] ?? 0) ?> users)</option>
                            </select>
                            <small class="text-muted">Notifications are delivered to users with active browser push subscriptions.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" placeholder="e.g. New Property Alert!" required maxlength="100">
                            <small class="text-muted">Max 100 characters. Keep it short and attention-grabbing.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Body <span class="text-danger">*</span></label>
                            <textarea name="body" class="form-control" rows="4" placeholder="e.g. Check out our new 3BHK apartments in Lucknow starting at ₹45 Lakh!" required maxlength="300"></textarea>
                            <small class="text-muted">Max 300 characters. The main notification message.</small>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">URL (optional)</label>
                            <input type="text" name="url" class="form-control" placeholder="/properties" value="/">
                            <small class="text-muted">Page to open when the notification is clicked. Use relative paths (e.g. /properties).</small>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary px-4" id="sendBtn">
                                <i class="fas fa-paper-plane me-1"></i> Send Now
                            </button>
                            <a href="<?= BASE_URL ?>/admin/push-notifications" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body aps-cp-card-body">
                    <h6 class="fw-bold mb-3"><i class="fas fa-info-circle text-primary me-1"></i> How it works</h6>
                    <ul class="small text-muted mb-0" class="style-51338">
                        <li>Push notifications are sent to all users with an active browser subscription</li>
                        <li>Users must have granted push permission in their browser</li>
                        <li>Notifications appear even when the site is not open</li>
                        <li>Failed deliveries are logged with error details</li>
                        <li>Expired subscriptions are auto-deactivated</li>
                    </ul>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="fs-3 fw-bold text-primary"><?= number_format($stats['total_subscribers'] ?? 0) ?></div>
                    <div class="text-muted small">Active Subscribers</div>
                    <div class="mt-2">
                        <span class="badge bg-success"><?= number_format($stats['sent_today'] ?? 0) ?> sent today</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="result" class="mt-3" class="style-2248"></div>
</div>

<script>
function sendPush(e) {
    e.preventDefault();
    const btn = document.getElementById('sendBtn');
    const form = document.getElementById('pushForm');
    const result = document.getElementById('result');
    const formData = new FormData(form);

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Sending...';
    result.style.display = 'none';

    fetch('<?= BASE_URL ?>/admin/push-notifications/send', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(d => {
        result.style.display = 'block';
        if (d.success) {
            result.innerHTML = '<div class="alert alert-success"><i class="fas fa-check-circle me-1"></i> ' + (d.message || 'Notification sent!') + '</div>';
            form.reset();
            .catch(err => console.error('Request failed:', err));
        } else {
            result.innerHTML = '<div class="alert alert-danger"><i class="fas fa-exclamation-circle me-1"></i> ' + (d.error || 'Failed to send') + '</div>';
        }
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-paper-plane me-1"></i> Send Now';
    })
    .catch(err => {
        result.style.display = 'block';
        result.innerHTML = '<div class="alert alert-danger"><i class="fas fa-exclamation-circle me-1"></i> Network error: ' + err.message + '</div>';
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-paper-plane me-1"></i> Send Now';
    });

    return false;
}
</script>
