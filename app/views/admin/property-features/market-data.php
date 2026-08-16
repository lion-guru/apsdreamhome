<?php $page_title = 'Market Data'; ?>
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-2"><i class="fas fa-chart-bar text-success me-2"></i>Market Data</h1>
            <p class="text-muted">Track property market trends and pricing data</p>
        </div>
    </div>

    <?php if ($msg = $_SESSION['flash_success'] ?? null): ?><div class="alert alert-success alert-dismissible fade show"><?= htmlspecialchars($msg ?? '') ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php unset($_SESSION['flash_success']); endif; ?>
    <?php if ($msg = $_SESSION['flash_error'] ?? null): ?><div class="alert alert-danger alert-dismissible fade show"><?= htmlspecialchars($msg ?? '') ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php unset($_SESSION['flash_error']); endif; ?>

    <div class="row">
        <!-- Filter -->
        <div class="col-lg-3 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filters</h5></div>
                <div class="card-body aps-cp-card-body">
                    <form method="GET">
                        <div class="mb-3">
                            <label class="form-label small">Location</label>
                            <select name="location" class="form-select">
                                <option value="">All Locations</option>
                                <?php foreach ($locations as $l): ?>
                                    <option value="<?= htmlspecialchars($l['location'] ?? '') ?>" <?= ($filter_location ?? '') === $l['location'] ? 'selected' : '' ?>><?= htmlspecialchars($l['location'] ?? '') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Property Type</label>
                            <select name="type" class="form-select">
                                <option value="">All Types</option>
                                <?php foreach ($types as $t): ?>
                                    <option value="<?= htmlspecialchars($t['property_type'] ?? '') ?>" <?= ($filter_type ?? '') === $t['property_type'] ? 'selected' : '' ?>><?= htmlspecialchars(ucfirst($t['property_type'])) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search me-1"></i>Apply</button>
                        <?php if (!empty($filter_location) || !empty($filter_type)): ?>
                            <a href="<?= BASE_URL ?>/admin/property-features/market-data" class="btn btn-outline-secondary w-100 mt-2"><i class="fas fa-times me-1"></i>Clear</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

            <!-- Add Market Data -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-plus me-2"></i>Add Entry</h5></div>
                <div class="card-body aps-cp-card-body">
                    <form method="POST" action="<?= BASE_URL ?>/admin/property-features/market-data/store">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="mb-2"><input type="text" name="location" class="form-control form-control-sm" placeholder="Location *" required></div>
                        <div class="mb-2">
                            <select name="property_type" class="form-select form-select-sm" required>
                                <option value="">Type *</option>
                                <option value="apartment">Apartment</option>
                                <option value="house">House</option>
                                <option value="land">Land</option>
                                <option value="commercial">Commercial</option>
                            </select>
                        </div>
                        <div class="mb-2"><input type="date" name="data_date" class="form-control form-control-sm" value="<?= date('Y-m-d') ?>"></div>
                        <div class="row g-2 mb-2">
                            <div class="col-6"><input type="number" step="0.01" name="avg_price_per_sqft" class="form-control form-control-sm" placeholder="Avg Price/sqft"></div>
                            <div class="col-6"><input type="number" step="0.01" name="median_price" class="form-control form-control-sm" placeholder="Median Price"></div>
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-6"><input type="number" step="0.01" name="price_trend_percentage" class="form-control form-control-sm" placeholder="Trend %"></div>
                            <div class="col-6"><input type="number" step="0.1" name="days_on_market_avg" class="form-control form-control-sm" placeholder="Days on Market"></div>
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-6"><input type="number" name="inventory_count" class="form-control form-control-sm" placeholder="Inventory"></div>
                            <div class="col-6"><input type="number" step="0.01" name="sales_volume" class="form-control form-control-sm" placeholder="Sales Volume"></div>
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-6"><input type="number" step="0.01" name="rental_yield_avg" class="form-control form-control-sm" placeholder="Rental Yield %"></div>
                            <div class="col-6">
                                <select name="market_sentiment" class="form-select form-select-sm">
                                    <option value="bullish">Bullish</option>
                                    <option value="neutral" selected>Neutral</option>
                                    <option value="bearish">Bearish</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-2"><input type="number" step="0.1" name="confidence_score" class="form-control form-control-sm" placeholder="Confidence (0-100)" value="75"></div>
                        <div class="mb-2"><input type="text" name="data_source" class="form-control form-control-sm" placeholder="Source" value="internal"></div>
                        <button type="submit" class="btn btn-success w-100 btn-sm"><i class="fas fa-save me-1"></i>Save Entry</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="col-lg-9">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-table me-2"></i>Market Data Records</h5></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light"><tr><th class="ps-4">Date</th><th>Location</th><th>Type</th><th>Avg Price/sqft</th><th>Median Price</th><th>Trend %</th><th>Inventory</th><th>Sentiment</th><th>Source</th></tr></thead>
                            <tbody>
                                <?php if (empty($entries)): ?>
                                    <tr><td colspan="9" class="text-center py-5 text-muted"><i class="fas fa-chart-bar fa-3x d-block mb-3"></i>No market data found</td></tr>
                                <?php else: ?>
                                    <?php foreach ($entries as $e): ?>
                                    <tr>
                                        <td class="ps-4 small"><?= date('M Y', strtotime($e['data_date'] ?? 'now')) ?></td>
                                        <td><?= htmlspecialchars($e['location'] ?? '-') ?></td>
                                        <td><span class="badge bg-info text-dark"><?= htmlspecialchars(ucfirst($e['property_type'] ?? '')) ?></span></td>
                                        <td>₹<?= number_format(floatval($e['avg_price_per_sqft'] ?? 0), 2) ?></td>
                                        <td>₹<?= number_format(floatval($e['median_price'] ?? 0), 2) ?></td>
                                        <td>
                                            <?php $trend = floatval($e['price_trend_percentage'] ?? 0); ?>
                                            <span class="text-<?= $trend >= 0 ? 'success' : 'danger' ?>">
                                                <i class="fas fa-caret-<?= $trend >= 0 ? 'up' : 'down' ?>"></i>
                                                <?= ($trend >= 0 ? '+' : '') . number_format($trend, 2) ?>%
                                            </span>
                                        </td>
                                        <td><?= number_format($e['inventory_count'] ?? 0) ?></td>
                                        <td>
                                            <?php $sent = $e['market_sentiment'] ?? 'neutral'; $sc = match($sent){'bullish'=>'success','bearish'=>'danger',default=>'warning'}; ?>
                                            <span class="badge bg-<?= $sc ?>-subtle text-<?= $sc ?>"><?= ucfirst($sent) ?></span>
                                        </td>
                                        <td><small class="text-muted"><?= htmlspecialchars($e['data_source'] ?? '-') ?></small></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
