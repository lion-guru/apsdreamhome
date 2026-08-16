<?php
$page_title = $page_title ?? 'AI Insights - APS Dream Home';
$totalLeads = $totalLeads ?? 0;
$newLeadsWeek = $newLeadsWeek ?? 0;
$hotLeads = $hotLeads ?? 0;
$coldLeads = $coldLeads ?? 0;
$unassignedLeads = $unassignedLeads ?? 0;
$totalCalls = $totalCalls ?? 0;
$interestedCalls = $interestedCalls ?? 0;
$avgCallDuration = $avgCallDuration ?? 0;
$staleLeads = $staleLeads ?? [];
$topPerformingScripts = $topPerformingScripts ?? [];
$sourcePerformance = $sourcePerformance ?? [];
$insights = $insights ?? [];
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h2 class="mb-0"><i class="fas fa-lightbulb me-2 text-warning"></i>AI Insights</h2>
        <a href="<?= BASE_URL ?>/admin/ai" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back to AI</a>
    </div>

    <?php if (!empty($insights)): ?>
    <div class="row g-3 mb-4">
        <?php foreach ($insights as $ins): ?>
        <div class="col-md-6">
            <div class="alert alert-<?= $ins['type'] ?> d-flex align-items-start gap-3 mb-0" class="style-46740">
                <i class="<?= $ins['icon'] ?> fa-2x mt-1"></i>
                <div><strong><?= $ins['title'] ?></strong><br><span class="small"><?= $ins['text'] ?></span></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm text-center py-3" class="style-46740">
                <div class="style-99485"><?= number_format($totalLeads) ?></div>
                <div class="small text-muted">Total Leads</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm text-center py-3" class="style-46740">
                <div class="style-29702"><?= number_format($hotLeads) ?></div>
                <div class="small text-muted">Hot Leads (Score â‰¥70)</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm text-center py-3" class="style-46740">
                <div class="style-21276"><?= number_format($coldLeads) ?></div>
                <div class="small text-muted">Cold Leads (Score &lt;30)</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm text-center py-3" class="style-46740">
                <div class="style-29911"><?= number_format($unassignedLeads) ?></div>
                <div class="small text-muted">Unassigned</div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom"><h6 class="mb-0">Call Performance</h6></div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-3"><span class="text-muted">Total Calls</span><strong><?= number_format($totalCalls) ?></strong></div>
                    <div class="d-flex justify-content-between mb-3"><span class="text-muted">Interested</span><strong class="text-success"><?= number_format($interestedCalls) ?></strong></div>
                    <div class="d-flex justify-content-between mb-3"><span class="text-muted">Interest Rate</span><strong><?= $totalCalls > 0 ? round($interestedCalls/$totalCalls*100,1) : 0 ?>%</strong></div>
                    <div class="d-flex justify-content-between"><span class="text-muted">Avg Duration</span><strong><?= round($avgCallDuration) ?>s</strong></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom"><h6 class="mb-0">Lead Pipeline Health</h6></div>
                <div class="card-body">
                    <?php
                    $total = max($totalLeads, 1);
                    $hotPct = round($hotLeads / $total * 100);
                    $coldPct = round($coldLeads / $total * 100);
                    $unassignedPct = round($unassignedLeads / $total * 100);
                    ?>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between small"><span class="text-success">Hot</span><span><?= $hotPct ?>%</span></div>
                        <div class="progress" class="style-32124"><div class="progress-bar bg-success" class="style-24533"></div></div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between small"><span class="text-danger">Cold</span><span><?= $coldPct ?>%</span></div>
                        <div class="progress" class="style-32124"><div class="progress-bar bg-danger" class="style-58791"></div></div>
                    </div>
                    <div>
                        <div class="d-flex justify-content-between small"><span class="text-warning">Unassigned</span><span><?= $unassignedPct ?>%</span></div>
                        <div class="progress" class="style-32124"><div class="progress-bar bg-warning" class="style-78464"></div></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom"><h6 class="mb-0">New Leads This Week</h6></div>
                <div class="card-body text-center py-4">
                    <div class="style-20181"><?= number_format($newLeadsWeek) ?></div>
                    <div class="text-muted mt-1">leads in the last 7 days</div>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($staleLeads)): ?>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom"><h6 class="mb-0"><i class="fas fa-clock text-warning me-2"></i>Stale Leads (No Activity &gt;7 days)</h6></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light"><tr><th>Lead</th><th>Phone</th><th>Status</th><th>Score</th><th>Last Updated</th><th>Days Stale</th></tr></thead>
                    <tbody>
                        <?php foreach ($staleLeads as $lead): ?>
                        <tr>
                            <td><a href="<?= BASE_URL ?>/admin/leads/<?= $lead['id'] ?>"><?= htmlspecialchars($lead['name'] ?? 'Unknown') ?></a></td>
                            <td><?= htmlspecialchars($lead['phone'] ?? '') ?></td>
                            <td><span class="badge bg-secondary"><?= ucfirst($lead['status'] ?? '') ?></span></td>
                            <td><span class="badge bg-<?= ($lead['score'] ?? 0) >= 50 ? 'success' : (($lead['score'] ?? 0) >= 30 ? 'warning' : 'danger') ?>"><?= $lead['score'] ?? 0 ?></span></td>
                            <td><small class="text-muted"><?= date('d M Y', strtotime($lead['updated_at'] ?? 'now')) ?></small></td>
                            <td><span class="badge bg-danger"><?= $lead['days_stale'] ?>d</span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($topPerformingScripts)): ?>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom"><h6 class="mb-0"><i class="fas fa-trophy text-success me-2"></i>Top Performing Call Scripts</h6></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light"><tr><th>Script</th><th>Total Calls</th><th>Interested</th><th>Conversion Rate</th></tr></thead>
                    <tbody>
                        <?php foreach ($topPerformingScripts as $s): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($s['script_name'] ?? '') ?></strong></td>
                            <td><?= number_format($s['total_calls_made']) ?></td>
                            <td><?= number_format($s['total_interested']) ?></td>
                            <td><span class="badge bg-success"><?= round($s['conversion_rate'], 1) ?>%</span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($sourcePerformance)): ?>
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom"><h6 class="mb-0"><i class="fas fa-filter text-info me-2"></i>Lead Source Win Rates</h6></div>
        <div class="card-body">
            <?php foreach ($sourcePerformance as $src): ?>
            <div class="d-flex align-items-center mb-3">
                <div class="flex-shrink-0 me-3" class="style-72730"><span class="small fw-semibold"><?= htmlspecialchars($src['source'] ?? '') ?></span></div>
                <div class="flex-grow-1">
                    <div class="progress" class="style-40280">
                        <div class="progress-bar bg-<?= $src['win_rate'] > 10 ? 'success' : ($src['win_rate'] > 5 ? 'warning' : 'secondary') ?>" class="style-46654">
                            <?= $src['win_rate'] ?>%
                        </div>
                    </div>
                </div>
                <div class="flex-shrink-0 ms-2" class="style-31652"><small class="text-muted"><?= $src['total'] ?> leads</small></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
