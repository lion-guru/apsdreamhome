<?php
$page_title = $page_title ?? 'Campaign Stats';
$page_heading = $page_heading ?? 'Campaign Stats';
$stats = $stats ?? [];
$recipients = $recipients ?? [];
$campaign_id = $campaign_id ?? ($stats['campaign']['id'] ?? 0);
$base = defined('BASE_URL') ? BASE_URL : '/' . trim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
$byStatus = $stats['by_status'] ?? [];
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h2 class="mb-1"><?= htmlspecialchars($page_heading) ?></h2>
            <p class="text-muted mb-0">Real-time delivery metrics for campaign #<?= (int) $campaign_id ?></p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= $base ?>/admin/marketing-campaigns/<?= (int) $campaign_id ?>/export" class="btn btn-outline-success">
                <i class="fas fa-file-csv me-1"></i>Export CSV
            </a>
            <a href="<?= $base ?>/admin/marketing-campaigns/show/<?= (int) $campaign_id ?>" class="btn btn-outline-primary">
                <i class="fas fa-info-circle me-1"></i>Details
            </a>
            <a href="<?= $base ?>/admin/marketing-campaigns" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back
            </a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <?php
        $cards = [
            ['Sent',          $stats['sent']         ?? 0, 'paper-plane',    'primary'],
            ['Delivered',     $stats['delivered']    ?? 0, 'check-circle',   'success'],
            ['Opened',        $stats['opened']       ?? 0, 'envelope-open',  'info'],
            ['Clicked',       $stats['clicked']      ?? 0, 'mouse-pointer',  'warning'],
            ['Bounced',       $stats['bounced']      ?? 0, 'exclamation-triangle', 'danger'],
            ['Unsubscribed',  $stats['unsubscribed'] ?? 0, 'user-slash',     'secondary'],
        ];
        foreach ($cards as $c): ?>
            <div class="col-md-2 col-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-<?= $c[2] ?> fa-2x text-<?= $c[3] ?> mb-2"></i>
                        <h3 class="mb-1"><?= number_format($c[1]) ?></h3>
                        <small class="text-muted"><?= $c[0] ?></small>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <h6 class="text-muted">Open Rate</h6>
                    <h3 class="mb-0 text-info"><?= number_format($stats['open_rate'] ?? 0, 1) ?>%</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <h6 class="text-muted">Click Rate</h6>
                    <h3 class="mb-0 text-warning"><?= number_format($stats['click_rate'] ?? 0, 1) ?>%</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <h6 class="text-muted">Total Recipients</h6>
                    <h3 class="mb-0 text-primary"><?= number_format($stats['total'] ?? 0) ?></h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Recipient List</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Channel</th>
                            <th>Status</th>
                            <th>Delivered</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recipients)): ?>
                            <tr><td colspan="6" class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x d-block mb-2 text-muted" aria-hidden="true"></i>No recipients yet</td></tr>
                        <?php else: foreach ($recipients as $r):
                            $statusClass = ['sent' => 'info', 'delivered' => 'success', 'opened' => 'primary', 'clicked' => 'warning', 'failed' => 'danger', 'bounced' => 'danger'][$r['status']] ?? 'secondary';
                        ?>
                            <tr>
                                <td><?= htmlspecialchars($r['name'] ?? '—') ?></td>
                                <td><small><?= htmlspecialchars($r['email'] ?? '') ?></small></td>
                                <td><small><?= htmlspecialchars($r['phone'] ?? '') ?></small></td>
                                <td><span class="badge bg-secondary"><?= htmlspecialchars($r['channel'] ?? '') ?></span></td>
                                <td><span class="badge bg-<?= $statusClass ?>"><?= htmlspecialchars($r['status'] ?? 'pending') ?></span></td>
                                <td><small><?= !empty($r['delivered_at']) ? date('M j H:i', strtotime($r['delivered_at'])) : '—' ?></small></td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
