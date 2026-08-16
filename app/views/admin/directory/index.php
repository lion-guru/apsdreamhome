<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-store-alt me-2"></i>Directory Management</h1>
        <div>
            <a href="<?= BASE_URL ?>/admin/directory/listings" class="btn btn-primary me-2"><i class="fas fa-list me-1"></i>Listings</a>
            <a href="<?= BASE_URL ?>/admin/directory/categories" class="btn btn-outline-secondary me-2"><i class="fas fa-tags me-1"></i>Categories</a>
            <a href="<?= BASE_URL ?>/admin/directory/reviews" class="btn btn-outline-info me-2"><i class="fas fa-star me-1"></i>Reviews</a>
            <a href="<?= BASE_URL ?>/admin/directory/jobs" class="btn btn-outline-warning me-2"><i class="fas fa-briefcase me-1"></i>Jobs</a>
            <a href="<?= BASE_URL ?>/admin/directory/materials" class="btn btn-outline-success"><i class="fas fa-cubes me-1"></i>Materials</a>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-3"><div class="card bg-primary text-white"><div class="card-body aps-cp-card-body"><h6>Categories</h6><h3><?= $stats['total_categories'] ?? 0 ?></h3></div></div></div>
        <div class="col-md-3"><div class="card bg-success text-white"><div class="card-body aps-cp-card-body"><h6>Approved Listings</h6><h3><?= $stats['approved_listings'] ?? 0 ?></h3></div></div></div>
        <div class="col-md-3"><div class="card bg-warning text-dark"><div class="card-body aps-cp-card-body"><h6>Pending</h6><h3><?= $stats['pending_listings'] ?? 0 ?></h3></div></div></div>
        <div class="col-md-3"><div class="card bg-info text-white"><div class="card-body aps-cp-card-body"><h6>Active Jobs</h6><h3><?= $stats['active_jobs'] ?? 0 ?></h3></div></div></div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header aps-cp-card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">All Listings</h5>
            <a href="<?= BASE_URL ?>/admin/directory/listing-form" class="btn btn-sm btn-primary"><i class="fas fa-plus"></i> New Listing</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-dark">
                        <tr><th>ID</th><th>Business</th><th>Category</th><th>City</th><th>Phone</th><th>Status</th><th>Views</th><th>Rating</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php if (empty($listings)): ?>
                            <tr><td colspan="9" class="text-center text-muted py-4">No listings yet.</td></tr>
                        <?php else: ?>
                            <?php foreach ($listings as $l): ?>
                                <tr>
                                    <td><?= $l['id'] ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($l['business_name'] ?? '') ?></strong>
                                        <?php if ($l['is_featured']): ?><span class="badge bg-warning text-dark ms-1"><i class="fas fa-crown"></i></span><?php endif; ?>
                                        <?php if ($l['is_verified']): ?><span class="badge bg-info ms-1"><i class="fas fa-check-circle"></i></span><?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($l['category_name'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($l['city'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($l['phone'] ?? '') ?></td>
                                    <td><span class="badge bg-<?= $l['status'] === 'approved' ? 'success' : ($l['status'] === 'pending' ? 'warning' : 'danger') ?>"><?= $l['status'] ?></span></td>
                                    <td><?= number_format($l['views'] ?? 0) ?></td>
                                    <td><?= $l['rating'] > 0 ? number_format($l['rating'], 1) . ' ★' : '-' ?></td>
                                    <td>
                                        <a href="<?= BASE_URL ?>/admin/directory/listing-form/<?= $l['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                                        <a href="<?= BASE_URL ?>/services/listing/<?= $l['id'] ?>" target="_blank" class="btn btn-sm btn-outline-info"><i class="fas fa-eye"></i></a>
                                        <a href="<?= BASE_URL ?>/admin/directory/delete-listing/<?= $l['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this listing? All reviews will also be deleted.')"><i class="fas fa-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
