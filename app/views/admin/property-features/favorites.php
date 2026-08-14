<?php $page_title = 'Property Favorites'; ?>
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-2"><i class="fas fa-heart text-danger me-2"></i>Property Favorites</h1>
            <p class="text-muted">View which properties users are saving as favorites</p>
        </div>
    </div>

    <?php if ($msg = $_SESSION['flash_success'] ?? null): ?><div class="alert alert-success alert-dismissible fade show"><?= htmlspecialchars($msg) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php unset($_SESSION['flash_success']); endif; ?>
    <?php if ($msg = $_SESSION['flash_error'] ?? null): ?><div class="alert alert-danger alert-dismissible fade show"><?= htmlspecialchars($msg) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php unset($_SESSION['flash_error']); endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <div class="row align-items-center">
                <div class="col"><h5 class="mb-0"><i class="fas fa-list me-2"></i>All Favorites</h5></div>
                <div class="col-auto">
                    <form method="GET" class="d-flex">
    <?php echo CSRFProtection::csrfField(); ?>
                        <input type="text" name="search" class="form-control form-control-sm me-2" placeholder="Search property or user..." value="<?= htmlspecialchars($search ?? '') ?>" style="width:250px">
                        <button type="submit" class="btn btn-sm btn-outline-primary"><i class="fas fa-search"></i></button>
                        <?php if (!empty($search)): ?>
                            <a href="<?= BASE_URL ?>/admin/property-features/favorites" class="btn btn-sm btn-outline-secondary ms-2"><i class="fas fa-times"></i></a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light"><tr><th class="ps-4">#</th><th>Property</th><th>Price</th><th>User</th><th>Email</th><th class="text-end pe-4">Date Favorited</th></tr></thead>
                    <tbody>
                        <?php if (empty($favorites)): ?>
                            <tr><td colspan="6" class="text-center py-5 text-muted"><i class="fas fa-heart fa-3x d-block mb-3"></i><?= !empty($search) ? 'No favorites match your search' : 'No favorites yet' ?></td></tr>
                        <?php else: ?>
                            <?php foreach ($favorites as $i => $f): ?>
                            <tr>
                                <td class="ps-4"><?= $i + 1 ?></td>
                                <td><strong><?= htmlspecialchars($f['property_title'] ?? 'Property #' . $f['property_id']) ?></strong></td>
                                <td>₹<?= number_format(floatval($f['property_price'] ?? 0), 2) ?></td>
                                <td><?= htmlspecialchars($f['user_name'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($f['user_email'] ?? '-') ?></td>
                                <td class="text-end pe-4 small"><?= date('d M Y, h:i A', strtotime($f['created_at'] ?? 'now')) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
