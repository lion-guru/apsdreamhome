<?php
$page_title = $page_title ?? 'AI Reports - APS Dream Home';
$totalCalls = $totalCalls ?? 0;
$completedCalls = $completedCalls ?? 0;
$totalLeads = $totalLeads ?? 0;
$hotLeads = $hotLeads ?? 0;
$totalCampaigns = $totalCampaigns ?? 0;
$activeCampaigns = $activeCampaigns ?? 0;
$conversionBySource = $conversionBySource ?? [];
$callsByScript = $callsByScript ?? [];
$leadLabels = $leadLabels ?? [];
$leadTotals = $leadTotals ?? [];
$leadQualified = $leadQualified ?? [];
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h2 class="mb-0"><i class="fas fa-file-chart-line me-2 text-primary"></i>AI Reports</h2>
        <a href="<?= BASE_URL ?>/admin/ai" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back to AI</a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm text-center py-3" style="border-left:4px solid #3b82f6;border-radius:10px">
                <div style="font-size:1.4rem;font-weight:700;color:#3b82f6"><?= number_format($totalCalls) ?></div>
                <div class="small text-muted">Total AI Calls</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm text-center py-3" style="border-left:4px solid #16a34a;border-radius:10px">
                <div style="font-size:1.4rem;font-weight:700;color:#16a34a"><?= number_format($completedCalls) ?></div>
                <div class="small text-muted">Completed Calls</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm text-center py-3" style="border-left:4px solid #8b5cf6;border-radius:10px">
                <div style="font-size:1.4rem;font-weight:700;color:#8b5cf6"><?= number_format($totalLeads) ?></div>
                <div class="small text-muted">Total Leads</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm text-center py-3" style="border-left:4px solid #f59e0b;border-radius:10px">
                <div style="font-size:1.4rem;font-weight:700;color:#f59e0b"><?= number_format($hotLeads) ?></div>
                <div class="small text-muted">Hot Leads</div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom"><h6 class="mb-0">Lead Acquisition Trend (30 days)</h6></div>
                <div class="card-body"><canvas id="leadsChart" height="100"></canvas></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom"><h6 class="mb-0">Lead Sources</h6></div>
                <div class="card-body">
                    <?php if (empty($conversionBySource)): ?>
                        <p class="text-muted text-center py-3">No source data</p>
                    <?php else: ?>
                        <?php foreach (array_slice($conversionBySource, 0, 8) as $src): ?>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small"><?= htmlspecialchars($src['source']) ?></span>
                            <div>
                                <span class="badge bg-primary"><?= $src['total'] ?></span>
                                <small class="text-muted"><?= $src['total'] > 0 ? round($src['won']/$src['total']*100,1) : 0 ?>% won</small>
                            </div>
                        </div>
                        <div class="progress mb-2" style="height:4px">
                            <div class="progress-bar bg-primary" style="width:<?= $src['total'] > 0 ? min(100, $src['won']/$src['total']*100) : 0 ?>%"></div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($callsByScript)): ?>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom"><h6 class="mb-0">Call Script Performance</h6></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>Script</th><th>Total Calls</th><th>Completed</th><th>Interested</th><th>Success Rate</th><th>Interest Rate</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($callsByScript as $s): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($s['script_name']) ?></strong></td>
                            <td><?= number_format($s['total']) ?></td>
                            <td><?= number_format($s['completed']) ?></td>
                            <td><?= number_format($s['interested']) ?></td>
                            <td><span class="badge bg-<?= $s['total']>0 && $s['completed']/$s['total']>0.5 ? 'success' : 'warning' ?>"><?= $s['total']>0 ? round($s['completed']/$s['total']*100,1) : 0 ?>%</span></td>
                            <td><span class="badge bg-<?= $s['total']>0 && $s['interested']/$s['total']>0.2 ? 'success' : 'secondary' ?>"><?= $s['total']>0 ? round($s['interested']/$s['total']*100,1) : 0 ?>%</span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
const leadLabels = <?= json_encode($leadLabels) ?>;
const leadTotals = <?= json_encode($leadTotals) ?>;
const leadQualified = <?= json_encode($leadQualified) ?>;
if (leadLabels.length > 0 && document.getElementById('leadsChart')) {
    new Chart(document.getElementById('leadsChart'), {
        type: 'line',
        data: {
            labels: leadLabels,
            datasets: [
                { label: 'Total Leads', data: leadTotals, borderColor: '#3b82f6', backgroundColor: 'rgba(59,130,246,0.1)', fill: true, tension: 0.3 },
                { label: 'Qualified', data: leadQualified, borderColor: '#16a34a', backgroundColor: 'rgba(22,163,74,0.1)', fill: true, tension: 0.3 }
            ]
        },
        options: { responsive: true, plugins: { legend: { position: 'top' } }, scales: { y: { beginAtZero: true } } }
    });
}
</script>
