<?php
$configured = $configured ?? false;
$connected = $connected ?? false;
$email = $email ?? null;
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Google Calendar Sync</h1>
        <?php if ($connected): ?>
        <a href="<?= BASE_URL . '/admin/calendar-sync/list-events' ?>" class="btn btn-outline-primary"><i class="fas fa-list me-2"></i>View Events</a>
        <?php endif; ?>
    </div>

    <?php if (!$configured): ?>
    <div class="card mb-4"><div class="card-body">
        <h5><i class="fas fa-exclamation-triangle text-warning me-2"></i>Not Configured</h5>
        <p class="text-muted">Google Calendar sync requires these environment variables:</p>
        <ul>
            <li><code>GOOGLE_CLIENT_ID</code></li>
            <li><code>GOOGLE_CLIENT_SECRET</code></li>
            <li><code>GOOGLE_REDIRECT_URI</code> (set to <code><?= BASE_URL . '/admin/calendar-sync/callback' ?></code>)</li>
        </ul>
        <p class="text-muted">Set these in your <code>.env</code> file, then reload this page.</p>
    </div></div>
    <?php elseif (!$connected): ?>
    <div class="card mb-4"><div class="card-body text-center py-5">
        <i class="fas fa-calendar-alt fa-3x text-muted mb-3"></i>
        <h5>Connect your Google account</h5>
        <p class="text-muted">Sync CRM meetings to your Google Calendar automatically.</p>
        <a href="<?= BASE_URL . '/admin/calendar-sync/connect' ?>" class="btn btn-primary"><i class="fab fa-google me-2"></i>Connect Google Calendar</a>
    </div></div>
    <?php else: ?>
    <div class="card mb-4"><div class="card-body">
        <h5><i class="fas fa-check-circle text-success me-2"></i>Connected<?= $email ? ' as ' . htmlspecialchars($email) : '' ?></h5>
        <p class="text-muted">Your CRM meetings will sync to Google Calendar.</p>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-success" onclick="syncNow()"><i class="fas fa-sync me-2"></i>Sync Now</button>
            <a href="<?= BASE_URL . '/admin/calendar-sync/list-events' ?>" class="btn btn-outline-primary"><i class="fas fa-list me-2"></i>View Events</a>
            <form method="POST" action="<?= BASE_URL . '/admin/calendar-sync/disconnect' ?>" class="d-inline" onsubmit="return confirm('Disconnect Google Calendar?');"><input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>"><button class="btn btn-outline-danger"><i class="fas fa-unlink me-2"></i>Disconnect</button></form>
        </div>
        <div id="sync-status" class="mt-2"></div>
    </div></div>
    <?php endif; ?>
</div>

<script>
function syncNow() {
    if (!confirm('Sync CRM meetings to Google Calendar now?')) return;
    fetch('<?= BASE_URL . '/admin/calendar-sync/sync' ?>', {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded', 'X-Requested-With':'XMLHttpRequest'},
        body: 'csrf_token=' + encodeURIComponent('<?= $_SESSION['csrf_token'] ?? '' ?>')
    }).then(r => r.json()).then(d => {
        const el = document.getElementById('sync-status');
        if (d.success) { el.innerHTML = '<div class="alert alert-success py-1">' + d.message + '</div>'; }
        else { el.innerHTML = '<div class="alert alert-danger py-1">' + (d.error || 'Failed') + '</div>'; }
    }).catch(() => { document.getElementById('sync-status').innerHTML = '<div class="alert alert-danger py-1">Error'; });
}
</script>
