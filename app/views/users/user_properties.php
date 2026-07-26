<?php
$properties = $properties ?? [];
$status = $status ?? '';
$search = $search ?? '';
$page = $page ?? 1;
$totalPages = $totalPages ?? 1;
$total = $total ?? 0;

function propStatusBadge($status) {
    $map = ['approved' => 'success', 'pending' => 'warning', 'rejected' => 'danger', 'sold' => 'info', 'active' => 'success', 'inactive' => 'secondary', 'verified' => 'success'];
    $cls = $map[strtolower($status)] ?? 'secondary';
    return '<span class="badge bg-' . $cls . '">' . htmlspecialchars(ucfirst($status)) . '</span>';
}
function propTypeIcon($type) {
    $map = ['sell' => 'tag', 'rent' => 'key', 'resale' => 'exchange-alt', 'buy' => 'shopping-cart'];
    return $map[strtolower($type ?? '')] ?? 'building';
}

$statsAll = count($properties);
$pendingCount = count(array_filter($properties, fn($p) => strtolower($p['status'] ?? '') === 'pending'));
$approvedCount = count(array_filter($properties, fn($p) => strtolower($p['status'] ?? '') === 'approved' || strtolower($p['status'] ?? '') === 'active'));
$soldCount = count(array_filter($properties, fn($p) => strtolower($p['status'] ?? '') === 'sold'));
?>

<style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
.emp-prop-stat { border: none; border-radius: 12px; transition: transform 0.2s; }
.emp-prop-stat:hover { transform: translateY(-2px); }
.emp-prop-stat .stat-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; }
.emp-prop-card { border: 1px solid #e2e8f0; border-radius: 12px; transition: all 0.2s; overflow: hidden; }
.emp-prop-card:hover { border-color: #7c2d12; box-shadow: 0 4px 12px rgba(0,0,0,0.06); transform: translateY(-1px); }
.emp-prop-img { height: 140px; background: linear-gradient(135deg,#f1f5f9,#e2e8f0); display: flex; align-items: center; justify-content: center; color: #94a3b8; font-size: 2rem; }
.emp-prop-type-badge { position: absolute; top: 10px; left: 10px; }
</style>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold"><i class="fas fa-building me-2 text-primary"></i>User Properties</h4>
            <p class="text-muted mb-0 small"><?= $total ?> properties submitted by users</p>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card emp-prop-stat shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary"><i class="fas fa-building"></i></div>
                    <div><div class="fw-bold fs-4"><?= $total ?></div><div class="text-muted small">Total</div></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card emp-prop-stat shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon bg-success bg-opacity-10 text-success"><i class="fas fa-check-circle"></i></div>
                    <div><div class="fw-bold fs-4 text-success"><?= $approvedCount ?></div><div class="text-muted small">Approved</div></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card emp-prop-stat shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning"><i class="fas fa-clock"></i></div>
                    <div><div class="fw-bold fs-4 text-warning"><?= $pendingCount ?></div><div class="text-muted small">Pending</div></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card emp-prop-stat shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon bg-info bg-opacity-10 text-info"><i class="fas fa-tag"></i></div>
                    <div><div class="fw-bold fs-4 text-info"><?= $soldCount ?></div><div class="text-muted small">Sold</div></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body py-3">
            <form method="get" class="row g-2 align-items-center">
                <div class="col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Search properties..." value="<?= htmlspecialchars($search) ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="approved" <?= $status === 'approved' ? 'selected' : '' ?>>Approved</option>
                        <option value="sold" <?= $status === 'sold' ? 'selected' : '' ?>>Sold</option>
                        <option value="rejected" <?= $status === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-filter me-1"></i>Filter</button>
                    <a href="/employee/user-properties" class="btn btn-sm btn-outline-secondary">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Properties Grid -->
    <?php if (empty($properties)): ?>
        <div class="card shadow-sm border-0">
            <div class="card-body text-center py-5">
                <div class="mb-3"><i class="fas fa-building fa-4x text-muted opacity-25"></i></div>
                <h5 class="text-muted"><?= $search || $status ? 'No Matching Properties' : 'No Properties Yet' ?></h5>
                <p class="text-muted small">Properties submitted by users will appear here</p>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($properties as $p): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card emp-prop-card shadow-sm h-100">
                        <div class="position-relative">
                            <div class="emp-prop-img">
                                <i class="fas fa-<?= propTypeIcon($p['listing_type'] ?? $p['type'] ?? '') ?>"></i>
                            </div>
                            <span class="emp-prop-type-badge"><?= propStatusBadge($p['status'] ?? 'pending') ?></span>
                        </div>
                        <div class="card-body">
                            <h6 class="fw-bold mb-1"><?= htmlspecialchars($p['name'] ?: ($p['property_name'] ?? 'Property #' . ($p['id'] ?? ''))) ?></h6>
                            <div class="text-muted small mb-2">
                                <i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($p['location'] ?? $p['address'] ?? '—') ?>
                                <?php if (!empty($p['district_name']) || !empty($p['state_name'])): ?>
                                    , <?= htmlspecialchars(implode(', ', array_filter([$p['district_name'] ?? '', $p['state_name'] ?? '']))) ?>
                                <?php endif; ?>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="fw-bold" style="color:#7c2d12;">₹<?= number_format((float)($p['price'] ?? 0)) ?></div>
                                <span class="badge bg-light text-dark"><i class="fas fa-<?= propTypeIcon($p['listing_type'] ?? '') ?> me-1"></i><?= ucfirst(htmlspecialchars($p['listing_type'] ?? $p['type'] ?? '')) ?></span>
                            </div>
                            <?php if (!empty($p['phone'])): ?>
                                <div class="text-muted small mt-2"><i class="fas fa-phone me-1"></i><?= htmlspecialchars($p['phone']) ?></div>
                            <?php endif; ?>
                            <?php if (!empty($p['created_at'])): ?>
                                <div class="text-muted small mt-1"><i class="fas fa-clock me-1"></i><?= date('d M Y', strtotime($p['created_at'])) ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer bg-transparent border-top-0 d-flex gap-2">
                            <a href="/properties/<?= $p['id'] ?? 0 ?>" class="btn btn-sm btn-outline-primary flex-grow-1"><i class="fas fa-eye me-1"></i>View</a>
                            <?php if (strtolower($p['status'] ?? '') === 'pending'): ?>
                                <form method="POST" action="/employee/user-properties/action" class="d-inline flex-grow-1">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                    <input type="hidden" name="id" value="<?= $p['id'] ?? 0 ?>">
                                    <input type="hidden" name="action" value="approve">
                                    <button type="submit" class="btn btn-sm btn-success w-100"><i class="fas fa-check me-1"></i>Approve</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <nav class="mt-4"><ul class="pagination justify-content-center">
                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&status=<?= urlencode($status) ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
            </ul></nav>
        <?php endif; ?>
    <?php endif; ?>
</div>
