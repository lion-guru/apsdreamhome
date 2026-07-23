<div class="container-fluid py-4">
    <?php
    $plotList = $plots ?? [];
    $availableCount = 0;
    $bookedCount = 0;
    $soldCount = 0;
    foreach ($plotList as $pl) {
        $st = strtolower($pl['status'] ?? 'available');
        if ($st === 'available' || $st === 'hold' || $st === 'reserved') $availableCount++;
        elseif ($st === 'booked') $bookedCount++;
        elseif ($st === 'sold') $soldCount++;
    }
    ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-th"></i> Plot Management</h2>
        <div>
            <a href="<?= BASE_URL ?>/admin/plots/create" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Plot
            </a>
            <a href="<?= BASE_URL ?>/admin/plots/categories" class="btn btn-secondary">
                <i class="fas fa-tags"></i> Categories
            </a>
        </div>
    </div>
    
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body aps-cp-card-body">
                    <h5>Total Plots</h5>
                    <h3><?= count($plotList) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body aps-cp-card-body">
                    <h5>Available</h5>
                    <h3><?= $availableCount ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body aps-cp-card-body">
                    <h5>Booked</h5>
                    <h3><?= $bookedCount ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body aps-cp-card-body">
                    <h5>Sold</h5>
                    <h3><?= $soldCount ?></h3>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card aps-cp-card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">All Plots</h5>
            <div class="btn-group">
                <button class="btn btn-sm btn-outline-secondary">Available</button>
                <button class="btn btn-sm btn-outline-secondary">Booked</button>
                <button class="btn btn-sm btn-outline-secondary">Sold</button>
            </div>
        </div>
        <div class="card-body aps-cp-card-body">
            <?php if (empty($plots)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-th-large fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No plots found. Add your first plot!</p>
                    <a href="<?= BASE_URL ?>/admin/plots/create" class="btn btn-primary">Add Plot</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Plot No</th>
                                <th>Size</th>
                                <th>Colony</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Owner</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($plots as $plot): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($plot['plot_number'] ?? $plot['id']) ?></strong></td>
                                <td><?= $plot['area_sqft'] ?? 0 ?> sqft</td>
                                <td><?= htmlspecialchars($plot['colony_name'] ?? 'N/A') ?></td>
                                <td>₹<?= number_format($plot['total_price'] ?? $plot['price'] ?? 0) ?></td>
                                <td>
                                    <span class="badge bg-<?= ($plot['status'] ?? '') === 'available' ? 'success' : (($plot['status'] ?? '') === 'sold' ? 'danger' : 'warning') ?>">
                                        <?= ucfirst($plot['status'] ?? 'available') ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($plot['customer_name'] ?? '-') ?></td>
                                <td>
                                    <a href="<?= BASE_URL ?>/admin/plots/<?= $plot['id'] ?>" class="btn btn-sm btn-info">View</a>
                                    <a href="<?= BASE_URL ?>/admin/plots/<?= $plot['id'] ?>/edit" class="btn btn-sm btn-warning">Edit</a>
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