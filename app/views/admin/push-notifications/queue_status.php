<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 fw-bold">Queue Status</h1>
            <p class="text-muted mb-0">Monitor push notification queue health and processing</p>
        </div>
        <div class="d-flex gap-2">
            <form method="POST" action="<?= BASE_URL ?>/admin/push-notifications/queue/process" class="d-inline">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                <button type="submit" class="btn btn-success" id="processBtn" onclick="return processQueue();">
                    <i class="fas fa-play me-1"></i> Process Batch
                </button>
            </form>
            <a href="<?= BASE_URL ?>/admin/push-notifications" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="fs-3 fw-bold"><?= number_format($stats['total'] ?? 0) ?></div>
                    <div >Total</div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="fs-3 fw-bold"><?= number_format($stats['pending'] ?? 0) ?></div>
                    <div >Pending</div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="fs-3 fw-bold"><?= number_format($stats['processing'] ?? 0) ?></div>
                    <div >Processing</div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="fs-3 fw-bold"><?= number_format($stats['sent'] ?? 0) ?></div>
                    <div >Sent</div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="fs-3 fw-bold"><?= number_format($stats['failed'] ?? 0) ?></div>
                    <div >Failed</div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="fs-3 fw-bold"><?= number_format($stats['cancelled'] ?? 0) ?></div>
                    <div >Cancelled</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header">
                    <h6 class="mb-0 fw-bold">
                        <i class="fas fa-calendar-day me-1"></i> Today's Activity
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-3 text-center">
                        <div class="col-6">
                            <div class="fw-bold fs-4"><?= number_format($todayStats['sent_today'] ?? 0) ?></div>
                            <div >Sent Today</div>
                        </div>
                        <div class="col-6">
                            <div class="fw-bold fs-4"><?= number_format($todayStats['failed_today'] ?? 0) ?></div>
                            <div >Failed Today</div>
                        </div>
                    </div>
                    <?php
                        $todayTotal = ($todayStats['sent_today'] ?? 0) + ($todayStats['failed_today'] ?? 0);
                        $todayRate = $todayTotal > 0 ? round(($todayStats['sent_today'] ?? 0) / $todayTotal * 100) : 0;
                    ?>
                    <div class="mt-3">
                        <div class="d-flex justify-content-between mb-1">
                            <small >Success Rate</small>
                            <small ><?= $todayRate ?>%</small>
                        </div>
                        <div class="progress">
                            <div class="progress-bar"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header">
                    <h6 class="mb-0 fw-bold">
                        <i class="fas fa-bullhorn me-1"></i> Campaign Status
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-3 text-center">
                        <div class="col-6">
                            <div class="fw-bold fs-4"><?= number_format($campaignStats['total_campaigns'] ?? 0) ?></div>
                            <div >Total Campaigns</div>
                        </div>
                        <div class="col-6">
                            <div class="fw-bold fs-4"><?= number_format($campaignStats['active_campaigns'] ?? 0) ?></div>
                            <div >Active Campaigns</div>
                        </div>
                    </div>
                    <div class="mt-3">
                        <a href="<?= BASE_URL ?>/admin/push-notifications/campaigns" class="btn btn-sm w-100">
                            <i class="fas fa-arrow-right me-1"></i> View Campaigns
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="processResult" class="mt-3"></div>
</div>

<script>
function processQueue() {
    const btn = document.getElementById('processBtn');
    const result = document.getElementById('processResult');
    const formData = new FormData();
    const token = document.querySelector('input[name="csrf_token"]');
    if (token) formData.append('csrf_token', token.value);

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Processing...';
    result.style.display = 'none';

    fetch('<?= BASE_URL ?>/admin/push-notifications/queue/process', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(d => {
    .catch(err => console.error('Request failed:', err));
        result.style.display = 'block';
        if (d.success) {
            result.innerHTML = '<div class="alert alert-success"><i class="fas fa-check-circle me-1"></i> ' + (d.message || 'Queue processed!') + '</div>';
        } else {
            result.innerHTML = '<div class="alert alert-danger"><i class="fas fa-exclamation-circle me-1"></i> ' + (d.error || 'Failed to process') + '</div>';
        }
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-play me-1"></i> Process Batch';
    })
    .catch(err => {
        result.style.display = 'block';
        result.innerHTML = '<div class="alert alert-danger"><i class="fas fa-exclamation-circle me-1"></i> Network error: ' + err.message + '</div>';
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-play me-1"></i> Process Batch';
    });

    return false;
}
</script>
