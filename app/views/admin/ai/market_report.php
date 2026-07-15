<div class="container-fluid py-4">
    <h4 class="mb-4"><i class="fas fa-chart-line me-2 text-info"></i>Market Intelligence Report</h4>
    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-sm mb-3">
                <div class="card-header"><i class="fas fa-tag me-1"></i>Price Trends</div>
                <div class="card-body">
                    <?php if (!empty($report['price_trends'])): ?>
                        <?php foreach ($report['price_trends'] as $trend): ?>
                            <div class="d-flex justify-content-between mb-2">
                                <span><?= htmlspecialchars($trend['colony'] ?? $trend['name'] ?? '') ?></span>
                                <span class="text-<?= ($trend['change'] ?? 0) >= 0 ? 'success' : 'danger' ?>"><?= ($trend['change'] ?? 0) >= 0 ? '+' : '' ?><?= number_format($trend['change'] ?? 0, 1) ?>%</span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted mb-0">Insufficient data for price trends.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm mb-3">
                <div class="card-header"><i class="fas fa-users me-1"></i>Demand Analysis</div>
                <div class="card-body">
                    <?php if (!empty($report['demand'])): ?>
                        <?php foreach ($report['demand'] as $key => $value): ?>
                            <div class="d-flex justify-content-between mb-2">
                                <span><?= ucfirst(str_replace('_', ' ', $key)) ?></span>
                                <strong><?= htmlspecialchars($value) ?></strong>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted mb-0">Run AI analysis to generate demand insights.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="card shadow-sm">
        <div class="card-header"><i class="fas fa-lightbulb me-1"></i>Recommendations</div>
        <div class="card-body">
            <?php if (!empty($report['recommendations'])): ?>
                <ul class="mb-0">
                    <?php foreach ($report['recommendations'] as $rec): ?>
                        <li class="mb-1"><?= htmlspecialchars($rec) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="text-muted mb-0">Run market analysis to generate recommendations.</p>
            <?php endif; ?>
        </div>
    </div>
</div>