<div class="container-fluid py-4">
    <h1 class="h3 mb-4"><i class="fas fa-chart-pie me-2"></i>Lead Source Analytics</h1>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <h6 class="text-muted">Total Leads</h6>
                    <h2 class="mb-0"><?= number_format($totalLeads ?? 0) ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <h6 class="text-muted">Sources Active</h6>
                    <h2 class="mb-0"><?= count($sourceData ?? []) ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <h6 class="text-muted">Top Source</h6>
                    <h2 class="mb-0 text-truncate">
                        <?php 
                        $topSource = !empty($sourceData) ? array_reduce($sourceData, function($carry, $item) { return ($carry === null || $item['count'] > $carry['count']) ? $item : $carry; }) : ['source' => 'N/A'];
                        echo htmlspecialchars(ucfirst($topSource['source'] ?? 'N/A')); 
                        ?>
                    </h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <h6 class="text-muted">This Month</h6>
                    <h2 class="mb-0"><?= number_format($monthlyLeads ?? 0) ?></h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart + Table -->
    <div class="row g-3">
        <div class="col-md-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white"><h5 class="mb-0">Source Distribution</h5></div>
                <div class="card-body aps-cp-card-body">
                    <?php if (!empty($sourceData)): ?>
                        <?php 
                        $colors = ['#3498db','#e74c3c','#2ecc71','#f39c12','#9b59b6','#1abc9c','#e67e22','#34495e'];
                        $maxVal = max(array_column($sourceData, 'count')) ?: 1;
                        ?>
                        <?php foreach ($sourceData as $i => $s): $pct = round(($s['count'] / ($totalLeads ?: 1)) * 100, 1); ?>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between">
                                    <span><i class="fas fa-circle me-2" style="color:<?= $colors[$i % count($colors)] ?>"></i><?= htmlspecialchars(ucfirst($s['source'] ?? 'unknown')) ?></span>
                                    <strong><?= $s['count'] ?> (<?= $pct ?>%)</strong>
                                </div>
                                <div class="progress" style="height:8px;">
                                    <div class="progress-bar" style="width:<?= $pct ?>%;background:<?= $colors[$i % count($colors)] ?>"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted text-center py-4">No lead source data available yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white"><h5 class="mb-0">Monthly Trend</h5></div>
                <div class="card-body aps-cp-card-body">
                    <?php if (!empty($monthlyTrend)): ?>
                        <div class="d-flex align-items-end gap-2" style="height:200px;">
                            <?php $maxM = max(array_column($monthlyTrend, 'count')) ?: 1; ?>
                            <?php foreach ($monthlyTrend as $m): $h = round(($m['count'] / $maxM) * 180); ?>
                                <div class="flex-fill text-center">
                                    <div style="height:<?= $h ?>px;background:linear-gradient(180deg,#3498db,#2980b9);border-radius:4px 4px 0 0;min-width:30px;" title="<?= $m['month'] ?>: <?= $m['count'] ?>"></div>
                                    <small class="text-muted" style="font-size:10px;"><?= htmlspecialchars(substr($m['month'], 0, 3)) ?></small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted text-center py-4">No monthly trend data yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Source Details Table -->
    <div class="card border-0 shadow-sm mt-3">
        <div class="card-header bg-white"><h5 class="mb-0">Source Breakdown</h5></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr><th>Source</th><th>Total Leads</th><th>This Month</th><th>Conversion</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($sourceData)): ?>
                            <?php foreach ($sourceData as $s): ?>
                                <tr>
                                    <td><i class="fas fa-<?= $s['source'] === 'website' ? 'globe' : ($s['source'] === 'whatsapp' ? 'fa-whatsapp' : ($s['source'] === 'referral' ? 'fa-user-friends' : 'fa-question-circle')) ?> me-2"></i><?= htmlspecialchars(ucfirst($s['source'] ?? 'unknown')) ?></td>
                                    <td><?= $s['count'] ?></td>
                                    <td><?= $s['monthly'] ?? 0 ?></td>
                                    <td><span class="badge bg-<?= ($s['conversion_pct'] ?? 0) > 20 ? 'success' : 'warning' ?>"><?= ($s['conversion_pct'] ?? 0) ?>%</span></td>
                                    <td><a href="<?= BASE_URL ?>/admin/leads?source=<?= urlencode($s['source'] ?? '') ?>" class="btn btn-sm btn-outline-primary">View</a></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center py-4 text-muted">No leads found to analyze.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
