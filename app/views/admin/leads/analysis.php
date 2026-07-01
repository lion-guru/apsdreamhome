<?php $page_title = 'Lead Analysis'; ?>
<div class="container-fluid py-4">
    <h2 class="mb-4"><i class="fas fa-chart-bar me-2"></i>Lead Analysis</h2>
    <div class="row mb-4">
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body text-center"><h3><?= number_format($total) ?></h3><small class="text-muted">Total Leads</small></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body text-center"><h3 class="text-success"><?= number_format($converted) ?></h3><small class="text-muted">Converted</small></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body text-center"><h3 class="text-primary"><?= $conv_rate ?>%</h3><small class="text-muted">Conversion Rate</small></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body text-center"><h3 class="text-info"><?= count($by_city) ?></h3><small class="text-muted">Cities Covered</small></div></div></div>
    </div>
    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-funnel-dollar me-2"></i>Conversion by Source</h6></div>
                <div class="card-body p-0">
                    <?php if (empty($by_source)): ?>
                        <p class="text-muted text-center py-3">No data</p>
                    <?php else: ?>
                        <div class="table-responsive"><table class="table table-sm mb-0">
                            <thead><tr><th>Source</th><th>Total</th><th>Converted</th><th>Rate</th></tr></thead>
                            <tbody>
                            <?php foreach ($by_source as $bs): ?>
                                <tr>
                                    <td><?= htmlspecialchars($bs['source'] ?? 'Unknown') ?></td>
                                    <td><?= $bs['total'] ?></td>
                                    <td><?= $bs['converted'] ?></td>
                                    <td><strong><?= $bs['total'] > 0 ? round(($bs['converted'] / $bs['total']) * 100, 1) : 0 ?>%</strong></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-calendar me-2"></i>Monthly Trend (6 Months)</h6></div>
                <div class="card-body p-0">
                    <?php if (empty($monthly)): ?>
                        <p class="text-muted text-center py-3">No data</p>
                    <?php else: ?>
                        <div class="table-responsive"><table class="table table-sm mb-0">
                            <thead><tr><th>Month</th><th>Total</th><th>Converted</th><th>Rate</th></tr></thead>
                            <tbody>
                            <?php foreach ($monthly as $m): ?>
                                <tr>
                                    <td><?= $m['month'] ?></td>
                                    <td><?= $m['total'] ?></td>
                                    <td><?= $m['converted'] ?></td>
                                    <td><strong><?= $m['total'] > 0 ? round(($m['converted'] / $m['total']) * 100, 1) : 0 ?>%</strong></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php if (!empty($by_city)): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-map-marker-alt me-2"></i>Top Cities</h6></div>
            <div class="card-body p-0">
                <div class="table-responsive"><table class="table table-sm mb-0">
                    <thead><tr><th>City</th><th>Count</th></tr></thead>
                    <tbody>
                    <?php foreach ($by_city as $c): ?>
                        <tr><td><?= htmlspecialchars($c['city']) ?></td><td><strong><?= $c['cnt'] ?></strong></td></tr>
                    <?php endforeach; ?>
                    </tbody>
                </table></div>
            </div>
        </div>
    <?php endif; ?>
</div>
