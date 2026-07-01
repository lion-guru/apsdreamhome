<?php
/** @var array $cronRuns */
$cronRuns = $cronRuns ?? [];
$base = defined('BASE_URL') ? BASE_URL : '';

$statusBadge = function ($s) {
    $map = [
        'success' => 'bg-success',
        'failed'  => 'bg-danger',
        'partial' => 'bg-warning text-dark',
        'running' => 'bg-info',
    ];
    return $map[$s] ?? 'bg-secondary';
};
?>
<div class="aps-cp-card mb-4">
    <div class="aps-cp-card-header d-flex justify-content-between align-items-center">
        <h5 class="m-0"><i class="fas fa-clock me-2"></i>Cron Run Log</h5>
        <code class="small text-muted">php scripts/cron_mlm_daily.php</code>
    </div>
    <div class="aps-cp-card-body p-0">
        <div class="table-responsive"><table class="table table-hover m-0">
            <thead>
                <tr>
                    <th>Run Date</th>
                    <th>Started</th>
                    <th>Finished</th>
                    <th>Status</th>
                    <th class="text-end">Promotions</th>
                    <th class="text-end">Clawbacks</th>
                    <th class="text-end">Payouts</th>
                    <th class="text-end">Duration (ms)</th>
                    <th>Errors</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($cronRuns)): ?>
                    <tr><td colspan="9" class="text-center text-muted py-3">No cron runs logged yet. Run <code>php scripts/cron_mlm_daily.php</code> to start.</td></tr>
                <?php else: foreach ($cronRuns as $r): ?>
                    <tr>
                        <td><?= htmlspecialchars((string)($r['run_date'] ?? '')) ?></td>
                        <td><?= htmlspecialchars((string)($r['started_at'] ?? '')) ?></td>
                        <td><?= htmlspecialchars((string)($r['finished_at'] ?? '—')) ?></td>
                        <td><span class="badge <?= $statusBadge($r['status'] ?? '') ?>"><?= htmlspecialchars((string)($r['status'] ?? '')) ?></span></td>
                        <td class="text-end"><?= (int)($r['rank_promotions'] ?? 0) ?></td>
                        <td class="text-end"><?= (int)($r['clawbacks'] ?? 0) ?></td>
                        <td class="text-end"><?= (int)($r['payouts_processed'] ?? 0) ?></td>
                        <td class="text-end"><?= (int)($r['duration_ms'] ?? 0) ?></td>
                        <td><small class="text-danger"><?= htmlspecialchars((string)($r['error_message'] ?? '')) ?></small></td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table></div>
    </div>
</div>
