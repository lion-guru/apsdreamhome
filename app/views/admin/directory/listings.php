<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-list me-2"></i>Manage Listings</h1>
        <a href="<?= BASE_URL ?>/admin/directory/listing-form" class="btn btn-primary"><i class="fas fa-plus me-1"></i>New Listing</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-header aps-cp-card-header">
            <form method="GET" class="row g-2 align-items-end">
    <?php echo CSRFProtection::csrfField(); ?>
                <div class="col-auto">
                    <select name="status" class="form-control form-control-sm" onchange="this.form.submit()">
                        <option value="">All Status</option>
                        <option value="approved" <?= $filterStatus === 'approved' ? 'selected' : '' ?>>Approved</option>
                        <option value="pending" <?= $filterStatus === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="rejected" <?= $filterStatus === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                    </select>
                </div>
                <div class="col-auto">
                    <select name="category_id" class="form-control form-control-sm" onchange="this.form.submit()">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= $filterCat == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-dark">
                        <tr><th>ID</th><th>Business</th><th>Category</th><th>Contact</th><th>City</th><th>Status</th><th>Verified</th><th>Featured</th><th>Views</th><th>Rating</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php if (empty($listings)): ?>
                            <tr><td colspan="11" class="text-center text-muted py-4">No listings found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($listings as $l): ?>
                                <tr>
                                    <td><?= $l['id'] ?></td>
                                    <td><strong><?= htmlspecialchars($l['business_name']) ?></strong></td>
                                    <td><?= htmlspecialchars($l['category_name'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($l['phone'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($l['city'] ?? '') ?></td>
                                    <td><span class="badge bg-<?= $l['status'] === 'approved' ? 'success' : ($l['status'] === 'pending' ? 'warning' : 'danger') ?>"><?= $l['status'] ?></span></td>
                                    <td><?= $l['is_verified'] ? '<i class="fas fa-check-circle text-info"></i>' : '-' ?></td>
                                    <td><?= $l['is_featured'] ? '<i class="fas fa-crown text-warning"></i>' : '-' ?></td>
                                    <td><?= number_format($l['views'] ?? 0) ?></td>
                                    <td><?= $l['rating'] > 0 ? number_format($l['rating'], 1) . ' ★' : '-' ?></td>
                                    <td>
                                        <a href="<?= BASE_URL ?>/admin/directory/listing-form/<?= $l['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                                        <a href="<?= BASE_URL ?>/services/listing/<?= $l['id'] ?>" target="_blank" class="btn btn-sm btn-outline-info"><i class="fas fa-eye"></i></a>
                                        <a href="<?= BASE_URL ?>/admin/directory/delete-listing/<?= $l['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete?')"><i class="fas fa-trash"></i></a>
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
