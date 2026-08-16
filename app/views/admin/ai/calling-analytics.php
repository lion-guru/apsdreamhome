<?php
$page_title = $page_title ?? 'Calling Analytics - APS Dream Home';
$totals = $totals ?? ['total'=>0,'completed'=>0,'failed'=>0,'interested'=>0,'not_interested'=>0,'avg_duration'=>0];
$day_labels = $day_labels ?? [];
$day_totals = $day_totals ?? [];
$day_completed = $day_completed ?? [];
$day_interested = $day_interested ?? [];
$byAgent = $byAgent ?? [];
$days = $days ?? 30;
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h2 class="mb-0"><i class="fas fa-chart-line me-2 text-primary"></i>Calling Analytics</h2>
        <div class="d-flex gap-2">
            <select id="daysFilter" class="form-select form-select-sm" class="style-30246" onchange="window.location='?days='+this.value">
                <?php foreach ([7=>7,14=>14,30=>30,60=>60,90=>90] as $v=>$l): ?>
                    <option value="<?= $v ?>" <?= $days == $v ? 'selected' : '' ?>>Last <?= $l ?> days</option>
                <?php endforeach; ?>
            </select>
            <a href="<?= BASE_URL ?>/admin/ai-calling/dashboard" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-2 col-4">
            <div class="card border-0 shadow-sm text-center py-3" class="style-46740">
                <div class="style-64786"><?= number_format($totals['total']) ?></div>
                <div class="small text-muted">Total Calls</div>
            </div>
        </div>
        <div class="col-md-2 col-4">
            <div class="card border-0 shadow-sm text-center py-3" class="style-46740">
                <div class="style-29702"><?= number_format($totals['completed']) ?></div>
                <div class="small text-muted">Completed</div>
            </div>
        </div>
        <div class="col-md-2 col-4">
            <div class="card border-0 shadow-sm text-center py-3" class="style-46740">
                <div class="style-21276"><?= number_format($totals['failed']) ?></div>
                <div class="small text-muted">Failed</div>
            </div>
        </div>
        <div class="col-md-2 col-4">
            <div class="card border-0 shadow-sm text-center py-3" class="style-46740">
                <div class="style-29702"><?= number_format($totals['interested']) ?></div>
                <div class="small text-muted">Interested</div>
            </div>
        </div>
        <div class="col-md-2 col-4">
            <div class="card border-0 shadow-sm text-center py-3" class="style-46740">
                <div class="style-21276"><?= number_format($totals['not_interested']) ?></div>
                <div class="small text-muted">Not Interested</div>
            </div>
        </div>
        <div class="col-md-2 col-4">
            <div class="card border-0 shadow-sm text-center py-3" class="style-46740">
                <div class="style-29911"><?= round($totals['avg_duration']) ?>s</div>
                <div class="small text-muted">Avg Duration</div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom"><h6 class="mb-0">Daily Call Volume</h6></div>
                <div class="card-body">
                    <canvas id="dailyChart" height="120"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom"><h6 class="mb-0">Response Distribution</h6></div>
                <div class="card-body">
                    <canvas id="responseChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($byAgent)): ?>
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom"><h6 class="mb-0">Performance by AI Agent</h6></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>Agent</th><th>Total Calls</th><th>Completed</th><th>Interested</th><th>Success Rate</th><th>Interest Rate</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($byAgent as $a): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($a['ai_agent_id'] ?? '') ?></strong></td>
                            <td><?= number_format($a['total']) ?></td>
                            <td><?= number_format($a['completed']) ?></td>
                            <td><?= number_format($a['interested']) ?></td>
                            <td><span class="badge bg-<?= $a['total']>0 && $a['completed']/$a['total']>0.5 ? 'success' : 'warning' ?>"><?= $a['total']>0 ? round($a['completed']/$a['total']*100,1) : 0 ?>%</span></td>
                            <td><span class="badge bg-<?= $a['total']>0 && $a['interested']/$a['total']>0.3 ? 'success' : 'secondary' ?>"><?= $a['total']>0 ? round($a['interested']/$a['total']*100,1) : 0 ?>%</span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script src="<?= BASE_URL ?>/assets/js/vendor/chart.umd.js"></script>
<script>
const labels = <?= json_encode($day_labels) ?>;
const totals = <?= json_encode($day_totals) ?>;
const completed = <?= json_encode($day_completed) ?>;
const interested = <?= json_encode($day_interested) ?>;

if (labels.length > 0) {
    new Chart(document.getElementById('dailyChart'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                { label: 'Total', data: totals, backgroundColor: 'rgba(59,130,246,0.5)', borderColor: 'rgba(59,130,246,1)', borderWidth: 1 },
                { label: 'Completed', data: completed, backgroundColor: 'rgba(22,163,74,0.5)', borderColor: 'rgba(22,163,74,1)', borderWidth: 1 },
                { label: 'Interested', data: interested, backgroundColor: 'rgba(245,158,11,0.5)', borderColor: 'rgba(245,158,11,1)', borderWidth: 1 }
            ]
        },
        options: { responsive: true, plugins: { legend: { position: 'top' } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
    });
}

const respLabels = ['Interested', 'Not Interested', 'Completed', 'Failed'];
const respData = [<?= $totals['interested'] ?>, <?= $totals['not_interested'] ?>, <?= $totals['completed'] ?>, <?= $totals['failed'] ?>];
const respColors = ['#16a34a', '#dc2626', '#3b82f6', '#f59e0b'];
if (document.getElementById('responseChart')) {
    new Chart(document.getElementById('responseChart'), {
        type: 'doughnut',
        data: { labels: respLabels, datasets: [{ data: respData, backgroundColor: respColors }] },
        options: { responsive: true, plugins: { legend: { position: 'bottom', labels: { boxWidth: 12 } } } }
    });
}
</script>
