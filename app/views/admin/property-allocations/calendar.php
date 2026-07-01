<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Property Availability Calendar</h1>
        <a href="<?= BASE_URL ?>/admin/property-allocations" class="btn btn-secondary">Back to Allocations</a>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="row mb-4">
        <div class="col-md-3"><div class="card bg-success text-white p-3"><h5>Available</h5><h2><?= count($available ?? []) ?></h2></div></div>
        <div class="col-md-3"><div class="card bg-warning text-white p-3"><h5>Booked</h5><h2><?= count($booked ?? []) ?></h2></div></div>
        <div class="col-md-3"><div class="card bg-danger text-white p-3"><h5>Sold</h5><h2><?= count($sold ?? []) ?></h2></div></div>
        <div class="col-md-3"><div class="card bg-secondary text-white p-3"><h5>Blocked</h5><h2><?= count($blocked ?? []) ?></h2></div></div>
    </div>

    <div class="card aps-cp-card">
        <div class="card-header aps-cp-card-header"><h5>All Properties</h5></div>
        <div class="card-body aps-cp-card-body">
            <div class="table-responsive"><table class="table table-striped">
                <thead>
                    <tr><th>Plot #</th><th>Title</th><th>Location</th><th>Area (sqft)</th><th>Price</th><th>Status</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($all_properties as $p): ?>
                    <tr>
                        <td><?= htmlspecialchars($p['plot_number'] ?? '') ?></td>
                        <td><?= htmlspecialchars($p['title'] ?? '') ?></td>
                        <td><?= htmlspecialchars($p['location'] ?? '') ?></td>
                        <td><?= number_format($p['area_sqft'] ?? 0) ?></td>
                        <td>₹<?= number_format($p['price'] ?? 0) ?></td>
                        <td>
                            <span class="badge bg-<?= $p['status'] === 'available' ? 'success' : ($p['status'] === 'booked' ? 'warning' : ($p['status'] === 'sold' ? 'danger' : 'secondary')) ?>">
                                <?= ucfirst($p['status'] ?? 'unknown') ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table></div>
        </div>
    </div>
</div>
