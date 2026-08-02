<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= $base ?? BASE_URL ?>"><i class="fas fa-home"></i></a></li>
                    <li class="breadcrumb-item"><a href="<?= $base ?? BASE_URL ?>metaverse">Metaverse</a></li>
                    <li class="breadcrumb-item active">Investment Portfolio</li>
                </ol>
            </nav>
            <h1 class="display-5 fw-bold"><i class="fas fa-chart-pie me-3 text-success"></i><?= ($page_title ?? 'Investment Portfolio') ?></h1>
        </div>
    </div>

    <?php $portfolio = $portfolio ?? []; $market_performance = $market_performance ?? []; ?>

    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-0 text-center">
                <div class="card-body aps-cp-card-body">
                    <small class="text-muted d-block">Total Investment</small>
                    <h3 class="text-primary">₹<?= number_format($portfolio['total_investment'] ?? 0) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 text-center">
                <div class="card-body aps-cp-card-body">
                    <small class="text-muted d-block">Current Value</small>
                    <h3 class="text-success">₹<?= number_format($portfolio['current_value'] ?? 0) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 text-center">
                <div class="card-body aps-cp-card-body">
                    <small class="text-muted d-block">Total Return</small>
                    <h3 class="text-success">+₹<?= number_format($portfolio['total_return'] ?? 0) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 text-center">
                <div class="card-body aps-cp-card-body">
                    <small class="text-muted d-block">Return %</small>
                    <h3 class="text-success">+<?= ($portfolio['return_percentage'] ?? 0) ?>%</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0"><i class="fas fa-building me-2 text-success"></i>Properties Owned</h5>
                </div>
                <div class="card-body aps-cp-card-body">
                    <?php $properties = $portfolio['properties_owned'] ?? []; ?>
                    <?php if (empty($properties)): ?>
                    <p class="text-muted text-center py-3">No virtual properties in your portfolio yet.</p>
                    <?php else: ?>
                    <div class="table-responsive">
                        <div class="table-responsive"><table class="table table-hover table-responsive">
                            <thead class="table-light">
                                <tr><th>Property</th><th>Investment</th><th>Current Value</th><th>Return</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($properties as $prop): ?>
                                <tr>
                                    <td><?= ($prop['name'] ?? 'Property') ?></td>
                                    <td>₹<?= number_format($prop['investment'] ?? 0) ?></td>
                                    <td>₹<?= number_format($prop['current_value'] ?? 0) ?></td>
                                    <td class="text-success">+<?= ($prop['return'] ?? 0) ?>%</td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0"><i class="fas fa-chart-bar me-2 text-info"></i>Market Performance</h5>
                </div>
                <div class="card-body aps-cp-card-body">
                    <h6>Index: <?= ($market_performance['market_index'] ?? 'VREI') ?></h6>
                    <h3 class="text-info"><?= ($market_performance['current_value'] ?? '0') ?></h3>
                    <hr>
                    <div class="d-flex justify-content-between mb-2"><span>Today</span><strong class="text-success"><?= ($market_performance['change_today'] ?? '0%') ?></strong></div>
                    <div class="d-flex justify-content-between mb-2"><span>1 Month</span><strong class="text-success"><?= ($market_performance['change_1month'] ?? '0%') ?></strong></div>
                    <div class="d-flex justify-content-between mb-2"><span>1 Year</span><strong class="text-success"><?= ($market_performance['change_1year'] ?? '0%') ?></strong></div>
                    <div class="d-flex justify-content-between"><span>Volatility</span><strong><?= ($market_performance['volatility'] ?? 'N/A') ?></strong></div>
                </div>
            </div>
        </div>
    </div>
</div>
