<?php
$base = $base ?? (defined('BASE_URL') ? BASE_URL : '');
$status = $status ?? '';
$search = $search ?? '';
$total = (int)($total ?? 0);
$page = max(1, (int)($page ?? 1));
$totalPages = max(1, (int)($totalPages ?? 1));
$properties = $properties ?? [];
$resellSubmissions = $resellSubmissions ?? [];
$stats = $stats ?? [];
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">Resell Properties</h1>
        <p class="text-muted mb-0">Manage all resell property listings</p>
    </div>
    <div>
        <a href="<?= $base ?>/admin/resell-properties/create" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>New Property
        </a>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon primary"><i class="fas fa-building"></i></div>
            <div class="stat-content">
                <div class="stat-label">Total Properties</div>
                <div class="stat-value"><?= (int)($stats['total'] ?? 0) ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon success"><i class="fas fa-check-circle"></i></div>
            <div class="stat-content">
                <div class="stat-label">Active</div>
                <div class="stat-value"><?= (int)($stats['active'] ?? 0) ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon warning"><i class="fas fa-clock"></i></div>
            <div class="stat-content">
                <div class="stat-label">Pending</div>
                <div class="stat-value"><?= (int)($stats['pending'] ?? 0) ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon info"><i class="fas fa-handshake"></i></div>
            <div class="stat-content">
                <div class="stat-label">Sold</div>
                <div class="stat-value"><?= (int)($stats['sold'] ?? 0) ?></div>
            </div>
        </div>
    </div>
</div>

<div class="card aps-cp-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h2  class="fs-6 fw-semibold mb-0">All Listings (<?= $total ?>)</h2>
        <form class="d-flex gap-2" method="GET" action="<?= $base ?>/admin/resell-properties">
    <?php echo CSRFProtection::csrfField(); ?>
            <input aria-label="Search" type="text" class="form-control form-control-sm" name="search" placeholder="Search..." value="<?= htmlspecialchars($search ?? '') ?>">
            <select aria-label="Status" class="form-select form-select-sm" name="status" >
                <option value="">All Status</option>
                <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="verified" <?= $status === 'verified' ? 'selected' : '' ?>>Verified</option>
                <option value="approved" <?= $status === 'approved' ? 'selected' : '' ?>>Approved</option>
                <option value="rejected" <?= $status === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                <option value="sold" <?= $status === 'sold' ? 'selected' : '' ?>>Sold</option>
            </select>
            <button type="submit" class="btn btn-sm btn-primary">Filter</button>
            <?php if ($search || $status): ?>
                <a href="<?= $base ?>/admin/resell-properties" class="btn btn-sm btn-outline-secondary">Clear</a>
            <?php endif; ?>
        </form>
    </div>
    <div class="card-body aps-cp-card-body">
        <?php if (empty($properties)): ?>
            <div class="text-center py-5">
                <i class="fas fa-building fa-3x text-muted mb-3"></i>
                <p class="text-muted">No resell properties found.</p>
                <a href="<?= $base ?>/admin/resell-properties/create" class="btn btn-primary btn-sm">Add First Property</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th scope="col">Property Title</th>
                            <th scope="col">Type</th>
                            <th scope="col">Seller</th>
                            <th scope="col">Location</th>
                            <th scope="col">Price</th>
                            <th scope="col">Status</th>
                            <th scope="col">Views</th>
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($properties as $p): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($p['name'] ?? 'Untitled') ?></strong>
                                    <?php if (!empty($p['is_featured'])): ?>
                                        <span class="badge bg-warning ms-1"><i class="fas fa-star"></i></span>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge bg-primary"><?= ucfirst(htmlspecialchars($p['property_type'] ?? '')) ?></span></td>
                                <td><?= htmlspecialchars($p['seller_name'] ?? $p['name'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($p['location'] ?? $p['city_name'] ?? '') ?></td>
                                    <td><strong>₹<?= number_format((float)($p['price'] ?? 0)) ?></strong></td>
                                <td>
                                    <?php
                                    $statusColors = [
                                        'pending' => 'warning',
                                        'verified' => 'info',
                                        'approved' => 'success',
                                        'rejected' => 'danger',
                                        'sold' => 'secondary',
                                    ];
                                    $st = $p['status'] ?? 'pending';
                                    $color = $statusColors[$st] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?= $color ?>"><?= ucfirst($st) ?></span>
                                </td>
                                <td><?= (int)($p['views'] ?? 0) ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= $base ?>/admin/resell-properties/view/<?= $p['id'] ?>" class="btn btn-outline-info"><i class="fas fa-eye"></i></a>
                                        <a href="<?= $base ?>/admin/resell-properties/edit/<?= $p['id'] ?>" class="btn btn-outline-warning"><i class="fas fa-edit"></i></a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($totalPages > 1): ?>
                <nav>
                    <ul class="pagination pagination-sm justify-content-center mb-0">
                        <?php if ($page > 1): ?>
                            <li class="page-item"><a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status) ?>">Prev</a></li>
                        <?php endif; ?>
                        <?php
                        $start = max(1, $page - 2);
                        $end = min($totalPages, $page + 2);
                        for ($i = $start; $i <= $end; $i++): ?>
                            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status) ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        <?php if ($page < $totalPages): ?>
                            <li class="page-item"><a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status) ?>">Next</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Public Resell Marketplace Submissions (resell_properties) -->
<div class="card aps-cp-card mt-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h2 class="fs-6 fw-semibold mb-0">
            <i class="fas fa-store me-2 text-primary"></i>Public Resell Submissions (<?= count($resellSubmissions ?? []) ?>)
        </h2>
        <a href="<?= $base ?>/resell-properties" target="_blank" class="btn btn-sm btn-outline-primary">
            <i class="fas fa-external-link-alt me-1"></i>View Public Marketplace
        </a>
    </div>
    <div class="card-body aps-cp-card-body">
        <?php if (empty($resellSubmissions)): ?>
            <p class="text-muted text-center py-3 mb-0">No public resell marketplace submissions yet.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Title</th>
                            <th scope="col">Type</th>
                            <th scope="col">Seller</th>
                            <th scope="col">Asking Price</th>
                            <th scope="col">Area</th>
                            <th scope="col">Status</th>
                            <th scope="col">Moderate</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($resellSubmissions as $rp): ?>
                            <?php
                            $rpStatus = $rp['status'] ?? 'pending';
                            $rpColor = match ($rpStatus) {
                                'approved', 'active' => 'success',
                                'rejected' => 'danger',
                                'sold' => 'secondary',
                                default => 'warning',
                            };
                            ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($rp['title'] ?? 'Untitled') ?></strong>
                                    <div class="small text-muted"><?= htmlspecialchars($rp['location'] ?? '') ?></div>
                                </td>
                                <td><span class="badge bg-info text-dark"><?= ucfirst(htmlspecialchars($rp['property_type'] ?? '')) ?></span></td>
                                <td>
                                    <?= htmlspecialchars($rp['seller_name'] ?? 'User #' . (int)($rp['user_id'] ?? 0)) ?>
                                    <?php if (!empty($rp['seller_phone'])): ?>
                                        <div class="small text-muted"><?= htmlspecialchars($rp['seller_phone']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td><strong>₹<?= number_format((float)($rp['asking_price'] ?? 0)) ?></strong></td>
                                <td><?= number_format((float)($rp['area_sqft'] ?? 0)) ?> sqft</td>
                                <td><span class="badge bg-<?= $rpColor ?>"><?= ucfirst(htmlspecialchars($rpStatus)) ?></span></td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <form method="POST" action="<?= $base ?>/admin/resell-properties/moderate/<?= (int)$rp['id'] ?>" class="d-inline">
                                            <?= CSRFProtection::csrfField() ?>
                                            <input type="hidden" name="status" value="approved">
                                            <button type="submit" class="btn btn-sm btn-success" title="Approve">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                        <form method="POST" action="<?= $base ?>/admin/resell-properties/moderate/<?= (int)$rp['id'] ?>" class="d-inline">
                                            <?= CSRFProtection::csrfField() ?>
                                            <input type="hidden" name="status" value="rejected">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Reject">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </form>
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


