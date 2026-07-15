<?php
$page_title = $page_title ?? 'Call Analytics';
$days = $days ?? 30;
$outcomes = $outcomes ?? [];
$methods = $methods ?? [];
$daily = $daily ?? [];
$totals = $totals ?? ['total' => 0,'connected' => 0,'not_answered' => 0,'busy' => 0,'call_later' => 0,'whatsapp' => 0,'sms' => 0];
$day_labels = $day_labels ?? [];
$day_totals = $day_totals ?? [];
$day_connected = $day_connected ?? [];
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h2 class="mb-0"><i class="fas fa-chart-pie me-2 text-info"></i>Call Analytics</h2>
        <div>
            <a href="<?= BASE_URL ?>/admin/ai-calling/auto-dialer" class="btn btn-outline-primary btn-sm"><i class="fas fa-phone-volume me-1"></i>Auto Dialer</a>
            <div class="btn-group btn-group-sm">
                <a href="?days=7" class="btn btn-outline-secondary <?= $days==7?'active':'' ?>">7d</a>
                <a href="?days=30" class="btn btn-outline-secondary <?= $days==30?'active':'' ?>">30d</a>
                <a href="?days=90" class="btn btn-outline-secondary <?= $days==90?'active':'' ?>">90d</a>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6"><div class="aps-cp-card"><div class="aps-cp-card-body">
            <div class="d-flex align-items-center"><div class="flex-shrink-0 me-3"><span class="badge bg-primary rounded-pill p-2"><i class="fas fa-phone"></i></span></div>
            <div><div class="aps-cp-stat-label">Total Activities</div><div class="aps-cp-stat-value"><?= number_format($totals['total'] ?? 0) ?></div></div></div>
        </div></div></div>
        <div class="col-md-3 col-6"><div class="aps-cp-card"><div class="aps-cp-card-body">
            <div class="d-flex align-items-center"><div class="flex-shrink-0 me-3"><span class="badge bg-success rounded-pill p-2"><i class="fas fa-check"></i></span></div>
            <div><div class="aps-cp-stat-label">Connected</div><div class="aps-cp-stat-value text-success"><?= number_format($totals['connected'] ?? 0) ?></div></div></div>
        </div></div></div>
        <div class="col-md-3 col-6"><div class="aps-cp-card"><div class="aps-cp-card-body">
            <div class="d-flex align-items-center"><div class="flex-shrink-0 me-3"><span class="badge bg-warning rounded-pill p-2"><i class="fas fa-comment"></i></span></div>
            <div><div class="aps-cp-stat-label">WhatsApp</div><div class="aps-cp-stat-value text-info"><?= number_format($totals['whatsapp'] ?? 0) ?></div></div></div>
        </div></div></div>
        <div class="col-md-3 col-6"><div class="aps-cp-card"><div class="aps-cp-card-body">
            <div class="d-flex align-items-center"><div class="flex-shrink-0 me-3"><span class="badge bg-secondary rounded-pill p-2"><i class="fas fa-sms"></i></span></div>
            <div><div class="aps-cp-stat-label">SMS</div><div class="aps-cp-stat-value"><?= number_format($totals['sms'] ?? 0) ?></div></div></div>
        </div></div></div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4"><div class="card-header bg-white"><h5 class="mb-0"><i class="fas fa-chart-line me-2 text-primary"></i>Daily Call Volume (<?= $days ?> days)</h5></div>
                <div class="card-body"><canvas id="dailyChart" height="90"></canvas></div></div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow-sm mb-4"><div class="card-header bg-white"><h5 class="mb-0"><i class="fas fa-bullseye me-2 text-success"></i>Call Outcomes</h5></div>
                <div class="card-body"><canvas id="outcomeChart" height="220"></canvas></div></div>
        </div>
        <div class="col-lg-6">
            <div class="card shadow-sm"><div class="card-header bg-white"><h5 class="mb-0"><i class="fas fa-layer-group me-2 text-info"></i>By Method</h5></div>
                <div class="card-body"><canvas id="methodChart" height="200"></canvas></div></div>
        </div>
        <div class="col-lg-6">
            <div class="card shadow-sm"><div class="card-header bg-white"><h5 class="mb-0"><i class="fas fa-table me-2 text-secondary"></i>Outcome Breakdown</h5></div>
                <div class="card-body p-0"><div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light"><tr><th>Outcome</th><th class="text-end">Count</th></tr></thead>
                        <tbody>
                            <?php if (empty($outcomes)): ?><tr><td colspan="2" class="text-center text-muted py-3">No data</td></tr>
                            <?php else: foreach ($outcomes as $o): ?><tr><td><?= ucfirst(str_replace('_', ' ', $o['outcome'])) ?></td><td class="text-end"><?= number_format($o['total']) ?></td></tr><?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div></div></div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
const labels = <?= json_encode($day_labels) ?>;
const totals = <?= json_encode(array_map('intval', $day_totals)) ?>;
const connected = <?= json_encode(array_map('intval', $day_connected)) ?>;
new Chart(document.getElementById('dailyChart'), {
    type: 'line',
    data: { labels, datasets: [
        { label: 'Total', data: totals, borderColor: '#3b82f6', backgroundColor: 'rgba(59,130,246,.1)', fill: true, tension: .3 },
        { label: 'Connected', data: connected, borderColor: '#22c55e', backgroundColor: 'rgba(34,197,94,.1)', fill: true, tension: .3 }
    ]},
    options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
});

const ocLabels = <?= json_encode(array_map(fn($o)=>ucfirst(str_replace('_',' ',$o['outcome'])), $outcomes)) ?>;
const ocData = <?= json_encode(array_map('intval', array_column($outcomes, 'total'))) ?>;
new Chart(document.getElementById('outcomeChart'), {
    type: 'doughnut',
    data: { labels: ocLabels, datasets: [{ data: ocData, backgroundColor: ['#22c55e','#eab308','#ef4444','#3b82f6','#a855f7','#64748b'] }] },
    options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
});

const mLabels = <?= json_encode(array_map('ucfirst', array_column($methods, 'method'))) ?>;
const mData = <?= json_encode(array_map('intval', array_column($methods, 'total'))) ?>;
new Chart(document.getElementById('methodChart'), {
    type: 'bar',
    data: { labels: mLabels, datasets: [{ label: 'Count', data: mData, backgroundColor: '#0ea5e9' }] },
    options: { responsive: true, plugins: { legend: { display: false } } }
});
</script>
