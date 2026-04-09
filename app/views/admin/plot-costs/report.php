<?php
/**
 * Plot Cost Calculator - Cost Report
 * Detailed cost analysis report for a colony
 */

$page_title = 'Cost Report - APS Dream Home';
include __DIR__ . '/../../layouts/admin_header.php';
?>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/admin/plot-costs">Plot Costs</a></li>
                    <li class="breadcrumb-item"><a href="/admin/plot-costs/colony/<?= $report['colony']['id'] ?>"><?= htmlspecialchars($report['colony']['name']) ?></a></li>
                    <li class="breadcrumb-item active">Report</li>
                </ol>
            </nav>
            <h1 class="h3 mb-2">
                <i class="fas fa-file-alt me-2"></i>Cost Report: <?= htmlspecialchars($report['colony']['name'] ?? '') ?>
            </h1>
            <p class="text-muted">Generated on <?= date('d M Y, h:i A') ?></p>
        </div>
        <div>
            <a href="/admin/plot-costs/colony/<?= $report['colony']['id'] ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back
            </a>
            <button onclick="window.print()" class="btn btn-primary">
                <i class="fas fa-print me-2"></i>Print
            </button>
        </div>
    </div>

    <!-- Executive Summary -->
    <div class="card mb-4 border-primary">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Executive Summary</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 text-center border-end">
                    <h6 class="text-muted">Total Investment</h6>
                    <h2 class="text-primary">₹<?= number_format($report['total_investment'] ?? 0) ?></h2>
                </div>
                <div class="col-md-3 text-center border-end">
                    <h6 class="text-muted">Total Plots</h6>
                    <h2><?= $report['total_plots'] ?? 0 ?></h2>
                </div>
                <div class="col-md-3 text-center border-end">
                    <h6 class="text-muted">Total Area</h6>
                    <h2><?= number_format($report['total_area_sqft'] ?? 0) ?> sqft</h2>
                </div>
                <div class="col-md-3 text-center">
                    <h6 class="text-muted">Avg Cost/sqft</h6>
                    <h2 class="text-success">₹<?= number_format($report['avg_cost_per_sqft'] ?? 0, 2) ?></h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Cost Breakdown by Category -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-pie-chart me-2"></i>Cost Breakdown</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th class="text-end">Amount</th>
                                <th class="text-end">%</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($report['cost_breakdown'] as $item): ?>
                            <tr>
                                <td><?= ucfirst($item['category']) ?></td>
                                <td class="text-end">₹<?= number_format($item['amount']) ?></td>
                                <td class="text-end"><?= round($item['percentage'], 1) ?>%</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-secondary">
                            <tr>
                                <th>Total</th>
                                <th class="text-end">₹<?= number_format($report['total_investment']) ?></th>
                                <th class="text-end">100%</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-tag me-2"></i>Price Recommendation</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 border-end">
                            <h6 class="text-muted">Min Price/sqft</h6>
                            <h3 class="text-success">₹<?= number_format($report['recommended_price_min'] ?? 0, 2) ?></h3>
                        </div>
                        <div class="col-6">
                            <h6 class="text-muted">Max Price/sqft</h6>
                            <h3 class="text-primary">₹<?= number_format($report['recommended_price_max'] ?? 0, 2) ?></h3>
                        </div>
                    </div>
                    <hr>
                    <div class="text-center">
                        <h6 class="text-muted">Suggested Selling Price</h6>
                        <h2 class="text-success">₹<?= number_format($report['recommended_price_avg'] ?? 0, 2) ?><small class="text-muted">/sqft</small></h2>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Per-Plot Cost Analysis -->
    <div class="card">
        <div class="card-header bg-warning">
            <h5 class="mb-0"><i class="fas fa-table me-2"></i>Per-Plot Cost Analysis</h5>
        </div>
        <div class="card-body">
            <?php if (empty($report['plot_analyses'])): ?>
                <p class="text-muted text-center py-4">No plot data available.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm table-striped">
                        <thead>
                            <tr>
                                <th>Plot #</th>
                                <th class="text-end">Area (sqft)</th>
                                <th class="text-end">Land Cost</th>
                                <th class="text-end">Dev. Cost</th>
                                <th class="text-end">Total Cost</th>
                                <th class="text-end">Cost/sqft</th>
                                <th class="text-end">Min Price</th>
                                <th class="text-end">Selling Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($report['plot_analyses'] as $plot): ?>
                            <tr>
                                <td><?= htmlspecialchars($plot['plot_number']) ?></td>
                                <td class="text-end"><?= number_format($plot['area_sqft']) ?></td>
                                <td class="text-end">₹<?= number_format($plot['land_cost']) ?></td>
                                <td class="text-end">₹<?= number_format($plot['development_cost']) ?></td>
                                <td class="text-end fw-bold">₹<?= number_format($plot['total_cost']) ?></td>
                                <td class="text-end">₹<?= number_format($plot['cost_per_sqft'], 2) ?></td>
                                <td class="text-end text-success">₹<?= number_format($plot['min_price']) ?></td>
                                <td class="text-end text-primary fw-bold">₹<?= number_format($plot['selling_price']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
@media print {
    .breadcrumb, .btn, footer { display: none !important; }
    .card { border: 1px solid #ddd !important; }
}
</style>

<?php include __DIR__ . '/../../layouts/admin_footer.php'; ?>
