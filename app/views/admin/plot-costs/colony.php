<?php
/**
 * Plot Cost Calculator - Colony Detail
 * Shows cost breakdown and plot pricing
 */

$page_title = 'Colony Cost Detail - APS Dream Home';
include __DIR__ . '/../../layouts/admin_header.php';
?>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/admin/plot-costs">Plot Costs</a></li>
                    <li class="breadcrumb-item active"><?= htmlspecialchars($colony['name'] ?? 'Colony') ?></li>
                </ol>
            </nav>
            <h1 class="h3 mb-2">
                <i class="fas fa-home me-2"></i><?= htmlspecialchars($colony['name'] ?? 'Colony') ?>
            </h1>
            <p class="text-muted"><?= htmlspecialchars($colony['location'] ?? '') ?></p>
        </div>
        <a href="/admin/plot-costs" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to List
        </a>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?= htmlspecialchars($_SESSION['success']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['success']); endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <?= htmlspecialchars($_SESSION['error']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['error']); endif; ?>

    <!-- Cost Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-primary">
                <div class="card-body text-center">
                    <h6 class="text-primary">Land Cost</h6>
                    <h3 class="mb-0">₹<?= number_format($costs['land'] ?? 0) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body text-center">
                    <h6 class="text-success">Development Cost</h6>
                    <h3 class="mb-0">₹<?= number_format($costs['development'] ?? 0) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <h6 class="text-warning">Amenities Cost</h6>
                    <h3 class="mb-0">₹<?= number_format($costs['amenities'] ?? 0) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-danger">
                <div class="card-body text-center">
                    <h6 class="text-danger">Total Cost</h6>
                    <h3 class="mb-0">₹<?= number_format($costs['total'] ?? 0) ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Cost Form -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-plus me-2"></i>Add Cost Entry</h5>
        </div>
        <div class="card-body">
            <form action="/admin/plot-costs/add-cost" method="POST" class="row g-3">
                <input type="hidden" name="colony_id" value="<?= $colony['id'] ?>">
                
                <div class="col-md-3">
                    <label class="form-label">Cost Type</label>
                    <select name="cost_type" class="form-select" required>
                        <option value="">Select Type</option>
                        <option value="land">Land</option>
                        <option value="development">Development</option>
                        <option value="amenities">Amenities</option>
                        <option value="legal">Legal</option>
                        <option value="misc">Miscellaneous</option>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">Description</label>
                    <input type="text" name="description" class="form-control" placeholder="e.g., Road construction" required>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">Amount (₹)</label>
                    <input type="number" name="amount" class="form-control" placeholder="0" min="0" step="0.01" required>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">Rate/sqft (₹)</label>
                    <input type="number" name="per_sqft_rate" class="form-control" placeholder="0" min="0" step="0.01">
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">Total Area</label>
                    <input type="number" name="total_area" class="form-control" placeholder="0" min="0" step="0.01">
                </div>
                
                <div class="col-12">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-plus me-2"></i>Add Cost
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Cost Breakdown Table -->
    <?php if (!empty($costBreakdown)): ?>
    <div class="card mb-4">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Cost Breakdown</h5>
        </div>
        <div class="card-body">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th class="text-end">Total</th>
                        <th class="text-center">Entries</th>
                        <th class="text-end">% of Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($costBreakdown as $cb): ?>
                    <tr>
                        <td>
                            <span class="badge bg-<?= $cb['cost_type'] === 'land' ? 'primary' : ($cb['cost_type'] === 'development' ? 'success' : ($cb['cost_type'] === 'amenities' ? 'warning' : 'secondary')) ?>">
                                <?= ucfirst($cb['cost_type']) ?>
                            </span>
                        </td>
                        <td class="text-end">₹<?= number_format($cb['total']) ?></td>
                        <td class="text-center"><?= $cb['entries'] ?></td>
                        <td class="text-end"><?= $costs['total'] > 0 ? round(($cb['total'] / $costs['total']) * 100, 1) : 0 ?>%</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="table-dark">
                    <tr>
                        <th>Total</th>
                        <th class="text-end">₹<?= number_format($costs['total']) ?></th>
                        <th></th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- Calculate Prices Form -->
    <div class="card mb-4">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="fas fa-calculator me-2"></i>Calculate Plot Prices</h5>
        </div>
        <div class="card-body">
            <form action="/admin/plot-costs/calculate" method="POST" class="row g-3 align-items-end">
                <input type="hidden" name="colony_id" value="<?= $colony['id'] ?>">
                
                <div class="col-md-4">
                    <label class="form-label">Profit Margin (%)</label>
                    <input type="number" name="margin_percent" class="form-control" value="25" min="0" max="100" required>
                    <small class="text-muted">Added to cost price</small>
                </div>
                
                <div class="col-md-4">
                    <button type="submit" class="btn btn-success w-100">
                        <i class="fas fa-sync me-2"></i>Recalculate All Plot Prices
                    </button>
                </div>
                
                <div class="col-md-4">
                    <a href="/admin/plot-costs/report/<?= $colony['id'] ?>" class="btn btn-info w-100">
                        <i class="fas fa-file-alt me-2"></i>View Cost Report
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Plots List -->
    <div class="card">
        <div class="card-header bg-secondary text-white">
            <h5 class="mb-0"><i class="fas fa-th me-2"></i>Plots (<?= count($plots) ?>)</h5>
        </div>
        <div class="card-body">
            <?php if (empty($plots)): ?>
                <p class="text-muted text-center py-4">No plots found for this colony.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Plot #</th>
                                <th>Area (sqft)</th>
                                <th>Cost Price</th>
                                <th>Selling Price</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($plots as $plot): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($plot['plot_number'] ?? 'N/A') ?></strong></td>
                                <td><?= number_format($plot['area_sqft'] ?? 0) ?></td>
                                <td>₹<?= number_format($plot['cost_price'] ?? 0) ?></td>
                                <td class="text-success">
                                    <strong>₹<?= number_format($plot['total_price'] ?? 0) ?></strong>
                                </td>
                                <td>
                                    <?php 
                                    $status = $plot['status'] ?? 'available';
                                    $badgeClass = $status === 'booked' ? 'danger' : ($status === 'sold' ? 'secondary' : 'success');
                                    ?>
                                    <span class="badge bg-<?= $badgeClass ?>"><?= ucfirst($status) ?></span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../layouts/admin_footer.php'; ?>
