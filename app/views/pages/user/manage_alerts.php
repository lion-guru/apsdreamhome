<?php
$page_title = $page_title ?? 'Manage Alerts - APS Dream Home';
$current_page = 'manage-alerts';
$user = $user ?? [];
$searches = $searches ?? [];
$alertLog = $alertLog ?? [];
$flash_success = $_SESSION['flash_success'] ?? '';
$flash_error = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h3 class="fw-bold mb-1"><i class="fas fa-bell text-success me-2"></i><?= __('alerts_title', null, 'Manage Email Alerts') ?></h3>
            <p class="text-muted mb-0"><?= __('alerts_subtitle', null, "Turn alerts on/off for your saved searches. You'll get an email when a new property matches your criteria.") ?></p>
        </div>
        <a href="<?= BASE_URL ?>/user/saved-searches" class="btn btn-outline-primary rounded-pill">
            <i class="fas fa-arrow-left me-1"></i><?= __('alerts_back', null, 'Back to Saved Searches') ?>
        </a>
    </div>

    <?php if ($flash_success): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($flash_success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if ($flash_error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($flash_error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Active Alerts -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-0 py-3">
            <h5 class="mb-0"><i class="fas fa-bell text-primary me-2"></i><?= __('alerts_subscriptions', null, 'Alert Subscriptions') ?></h5>
        </div>
        <div class="card-body aps-cp-card-body">
            <?php if (empty($searches)): ?>
                <p class="text-muted text-center py-4 mb-0">
                    <?= __('alerts_no_searches', null, "You don't have any saved searches yet.") ?> <a href="<?= BASE_URL ?>/properties"><?= __('alerts_start_searching', null, 'Start searching') ?></a> <?= __('alerts_to_setup', null, 'to set up alerts.') ?>
                </p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th><?= __('alerts_th_search', null, 'Search Name') ?></th>
                                <th><?= __('alerts_th_filters', null, 'Filters') ?></th>
                                <th><?= __('alerts_th_last_run', null, 'Last Run') ?></th>
                                <th><?= __('alerts_th_email_alerts', null, 'Email Alerts') ?></th>
                                <th><?= __('alerts_th_action', null, 'Action') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($searches as $s):
                                $filters = is_array($s['filters'] ?? null) ? $s['filters'] : (json_decode($s['filters'] ?? '{}', true) ?: []);
                                $filterSummary = [];
                                if (!empty($filters['type'])) $filterSummary[] = 'Type: ' . ucfirst($filters['type']);
                                if (!empty($filters['listing'])) $filterSummary[] = ucfirst($filters['listing']);
                                if (!empty($filters['location'])) $filterSummary[] = $filters['location'];
                                if (!empty($filters['min_price']) || !empty($filters['max_price'])) {
                                    $fmt = fn($n) => '₹' . round($n / 100000, 1) . 'L';
                                    $filterSummary[] = (!empty($filters['min_price']) ? $fmt($filters['min_price']) : '?') . ' - ' . (!empty($filters['max_price']) ? $fmt($filters['max_price']) : '?');
                                }
                                $alertsOn = (int)($s['email_alerts'] ?? 0) === 1;
                            ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($s['name'] ?? 'Untitled') ?></strong></td>
                                <td><small class="text-muted"><?= htmlspecialchars(implode(' • ', $filterSummary) ?: '—') ?></small></td>
                                <td>
                                    <?php if (!empty($s['last_run_at'])): ?>
                                        <small><?= date('d M, H:i', strtotime($s['last_run_at'])) ?></small>
                                    <?php else: ?>
                                        <small class="text-muted"><?= __('alerts_never', null, 'Never') ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input js-toggle-alert" type="checkbox"
                                               data-search-id="<?= (int)$s['id'] ?>"
                                               id="alertSwitch<?= (int)$s['id'] ?>"
                                               <?= $alertsOn ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="alertSwitch<?= (int)$s['id'] ?>">
                                            <span class="badge bg-<?= $alertsOn ? 'success' : 'secondary' ?>-subtle text-<?= $alertsOn ? 'success' : 'secondary' ?>">
                                                <?= $alertsOn ? 'ON' : 'OFF' ?>
                                            </span>
                                        </label>
                                    </div>
                                </td>
                                <td>
                                    <a href="<?= BASE_URL ?>/user/saved-searches/<?= (int)$s['id'] ?>/execute?to=properties" class="btn btn-sm btn-outline-primary rounded-pill">
                                        <i class="fas fa-play me-1"></i><?= __('alerts_btn_run', null, 'Run') ?>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Alert Log -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 py-3">
            <h5 class="mb-0"><i class="fas fa-history text-info me-2"></i><?= __('alerts_history', null, 'Alert History') ?></h5>
        </div>
        <div class="card-body p-0">
            <?php if (empty($alertLog)): ?>
                <p class="text-muted text-center py-4 mb-0"><?= __('alerts_no_history', null, 'No alerts have been sent yet.') ?></p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th><?= __('alerts_th_sent', null, 'Sent At') ?></th>
                                <th><?= __('alerts_th_search', null, 'Search') ?></th>
                                <th><?= __('alerts_th_property', null, 'Property') ?></th>
                                <th><?= __('alerts_th_status', null, 'Status') ?></th>
                                <th><?= __('alerts_th_error', null, 'Error') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($alertLog as $log): ?>
                            <tr>
                                <td><small><?= date('d M Y, H:i', strtotime($log['sent_at'] ?? 'now')) ?></small></td>
                                <td><?= htmlspecialchars($log['search_name'] ?? '—') ?></td>
                                <td>
                                    <?php if (!empty($log['property_id'])): ?>
                                        <a href="<?= BASE_URL ?>/listing/<?= (int)$log['property_id'] ?>" target="_blank">
                                            <?= htmlspecialchars($log['property_name'] ?? 'Property #' . $log['property_id']) ?>
                                        </a>
                                    <?php else: ?>—<?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $statusClass = match($log['email_status'] ?? 'pending') {
                                        'sent' => 'success',
                                        'failed' => 'danger',
                                        default => 'warning'
                                    };
                                    ?>
                                    <span class="badge bg-<?= $statusClass ?>-subtle text-<?= $statusClass ?>">
                                        <?= htmlspecialchars(ucfirst($log['email_status'] ?? 'pending')) ?>
                                    </span>
                                </td>
                                <td><small class="text-muted"><?= htmlspecialchars($log['error_message'] ?? '') ?></small></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const baseUrl = '<?= BASE_URL ?>';
    document.querySelectorAll('.js-toggle-alert').forEach(input => {
        input.addEventListener('change', async function() {
            const id = this.dataset.searchId;
            const enabled = this.checked;
            const label = this.closest('td').querySelector('.form-check-label .badge');
            const original = label.textContent;
            this.disabled = true;
            try {
                const res = await fetch(baseUrl + '/user/saved-searches/' + id + '/alerts', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    body: JSON.stringify({ email_alerts: enabled ? 1 : 0 })
                });
                const data = await res.json();
                if (data.success) {
                    label.textContent = enabled ? 'ON' : 'OFF';
                    label.className = 'badge bg-' + (enabled ? 'success' : 'secondary') + '-subtle text-' + (enabled ? 'success' : 'secondary');
                } else {
                    alert('Failed: ' + (data.error || 'unknown'));
                    this.checked = !enabled;
                    label.textContent = original;
                }
            } catch (e) {
                alert('Network error');
                this.checked = !enabled;
                label.textContent = original;
            }
            this.disabled = false;
        });
    });
});
</script>
