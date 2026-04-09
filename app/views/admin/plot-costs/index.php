<?php
/**
 * Plot Cost Calculator - Index
 * Lists all colonies with cost summary
 */

$page_title = 'Plot Development Cost Calculator - APS Dream Home';
include __DIR__ . '/../../layouts/admin_header.php';
?>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-2"><i class="fas fa-calculator me-2"></i>Plot Development Cost Calculator</h1>
            <p class="text-muted">Manage colony development costs and plot pricing</p>
        </div>
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

    <!-- Colonies List -->
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-home me-2"></i>Colonies</h5>
        </div>
        <div class="card-body">
            <?php if (empty($colonies)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-folder-open fa-4x text-muted mb-3"></i>
                    <p class="text-muted">No colonies found. <a href="/admin/colonies/add">Add a colony</a> to start tracking costs.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Colony Name</th>
                                <th>Location</th>
                                <th>Total Plots</th>
                                <th>Total Area (sqft)</th>
                                <th>Total Cost</th>
                                <th>Cost/Plot</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($colonies as $colony): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($colony['name'] ?? 'N/A') ?></strong>
                                </td>
                                <td><?= htmlspecialchars($colony['location'] ?? 'N/A') ?></td>
                                <td><span class="badge bg-info"><?= intval($colony['total_plots']) ?></span></td>
                                <td><?= number_format($colony['total_area_sqft'] ?? 0) ?></td>
                                <td>
                                    <strong class="text-success">₹<?= number_format($colony['total_cost'] ?? 0) ?></strong>
                                </td>
                                <td>
                                    <?php 
                                    $perPlot = $colony['total_plots'] > 0 
                                        ? ($colony['total_cost'] ?? 0) / $colony['total_plots'] 
                                        : 0;
                                    ?>
                                    ₹<?= number_format($perPlot, 0) ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="/admin/plot-costs/colony/<?= $colony['id'] ?>" class="btn btn-primary" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="/admin/plot-costs/report/<?= $colony['id'] ?>" class="btn btn-info" title="Cost Report">
                                            <i class="fas fa-file-alt"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Cost Types Legend -->
    <div class="card mt-4">
        <div class="card-header bg-secondary text-white">
            <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Cost Categories</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="d-flex align-items-center mb-2">
                        <span class="badge bg-primary me-2">Land</span>
                        <small>Land purchase, registry, mutation</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="d-flex align-items-center mb-2">
                        <span class="badge bg-success me-2">Development</span>
                        <small>Roads, drainage, electricity</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="d-flex align-items-center mb-2">
                        <span class="badge bg-warning me-2">Amenities</span>
                        <small>Park, club, garden</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="d-flex align-items-center mb-2">
                        <span class="badge bg-info me-2">Legal</span>
                        <small>Agreement, NOC, approvals</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../layouts/admin_footer.php'; ?>
