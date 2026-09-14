<?php
$stats = $stats ?? [];
$total = $total ?? 0;
$page = $page ?? 1;
$total_pages = $total_pages ?? 1;
$search = $search ?? '';
$entity_type = $entity_type ?? '';
$entity_types = $entity_types ?? [];
$base = defined('BASE_URL') ? BASE_URL : '/apsdreamhome';
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0"><i class="fas fa-search me-2"></i>Search History</h2>
    </div>

    <div class="alert alert-info alert-dismissible fade show mb-4" role="alert" style="border-left: 4px solid #0d6efd;">
        <i class="fas fa-info-circle me-2"></i>
        <small>Search history tracks all user searches across the platform. Data is used for analytics and search improvement.</small>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>

    <!-- Stats Row -->
    <div class="row mb-4">
        <div class="col-md-4 mb-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-3">
                    <div class="text-primary fw-bold fs-4"><?= number_format($stats['total_searches'] ?? $total) ?></div>
                    <small class="text-muted">Total Searches</small>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-3">
                    <div class="text-info fw-bold fs-4"><?= number_format($stats['unique_users'] ?? 0) ?></div>
                    <small class="text-muted">Unique Users</small>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-3">
                    <div class="text-warning fw-bold fs-4"><?= htmlspecialchars($stats['top_entity'] ?? '-') ?></div>
                    <small class="text-muted">Most Searched Entity</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Search & Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header aps-cp-card-header"><i class="fas fa-filter me-2"></i>Filters</div>
        <div class="card-body py-3">
            <form method="GET" action="<?= $base ?>/admin/search-history" class="row g-2 align-items-end">
                <div class="col-md-5">
                    <label class="form-label small fw-semibold">Search</label>
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="User name or search term..." value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Entity Type</label>
                    <select name="entity_type" class="form-select form-select-sm">
                        <option value="all" <?= $entity_type === 'all' || $entity_type === '' ? 'selected' : '' ?>>All Types</option>
                        <?php foreach ($entity_types as $et): ?>
                            <?php if ($et === 'all') continue; ?>
                            <option value="<?= htmlspecialchars($et) ?>" <?= $entity_type === $et ? 'selected' : '' ?>><?= ucfirst(htmlspecialchars($et)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search me-1"></i>Search</button>
                    <a href="<?= $base ?>/admin/search-history" class="btn btn-outline-secondary btn-sm">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Results Table -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header aps-cp-card-header">
            <i class="fas fa-list me-2"></i>Search Records
            <span class="badge bg-primary ms-2"><?= number_format($total) ?></span>
        </div>
        <div class="card-body p-0">
            <?php if (empty($history)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-search fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No search history records found.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width:60px">ID</th>
                                <th>User Name</th>
                                <th>User Role</th>
                                <th>Entity Type</th>
                                <th>Filters</th>
                                <th style="width:80px">Results</th>
                                <th>IP Address</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($history as $h): ?>
                            <tr>
                                <td><small class="text-muted">#<?= (int)($h['id'] ?? 0) ?></small></td>
                                <td>
                                    <?php if (!empty($h['user_name'])): ?>
                                        <span class="fw-semibold"><?= htmlspecialchars($h['user_name']) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted fst-italic">Anonymous</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $role = $h['user_role'] ?? '';
                                    $roleClass = match(true) {
                                        $role === 'admin' => 'bg-danger',
                                        $role === 'associate' => 'bg-success',
                                        $role === 'agent' => 'bg-primary',
                                        $role === 'customer' => 'bg-info',
                                        default => 'bg-secondary',
                                    };
                                    ?>
                                    <span class="badge <?= $roleClass ?>"><?= htmlspecialchars($role ?: '-') ?></span>
                                </td>
                                <td>
                                    <?php
                                    $et = $h['entity_type'] ?? '';
                                    $etClass = match($et) {
                                        'properties' => 'bg-primary',
                                        'plots' => 'bg-success',
                                        'colonies' => 'bg-info',
                                        'leads' => 'bg-warning text-dark',
                                        default => 'bg-secondary',
                                    };
                                    ?>
                                    <span class="badge <?= $etClass ?>"><?= htmlspecialchars($et ?: '-') ?></span>
                                </td>
                                <td>
                                    <?php
                                    $filtersRaw = $h['filters'] ?? '';
                                    if (is_string($filtersRaw)) {
                                        $filtersDecoded = json_decode($filtersRaw, true);
                                    } else {
                                        $filtersDecoded = $filtersRaw;
                                    }
                                    $filtersStr = is_array($filtersDecoded) ? json_encode($filtersDecoded, JSON_UNESCAPED_UNICODE) : (string)$filtersRaw;
                                    $preview = mb_strlen($filtersStr) > 60 ? mb_substr($filtersStr, 0, 60) . '...' : $filtersStr;
                                    ?>
                                    <small class="text-muted" title="<?= htmlspecialchars($filtersStr) ?>"><?= htmlspecialchars($preview) ?></small>
                                </td>
                                <td class="text-center"><span class="badge bg-light text-dark"><?= (int)($h['results_count'] ?? 0) ?></span></td>
                                <td><small class="text-muted"><?= htmlspecialchars($h['ip_address'] ?? '-') ?></small></td>
                                <td><small class="text-muted"><?= date('d M Y H:i', strtotime($h['created_at'] ?? 'now')) ?></small></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
        <nav aria-label="Search history pagination">
            <ul class="pagination justify-content-center">
                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= $base ?>/admin/search-history?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&entity_type=<?= urlencode($entity_type) ?>">Previous</a>
                </li>
                <?php
                $startPage = max(1, $page - 2);
                $endPage = min($total_pages, $page + 2);
                for ($i = $startPage; $i <= $endPage; $i++):
                ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                        <a class="page-link" href="<?= $base ?>/admin/search-history?page=<?= $i ?>&search=<?= urlencode($search) ?>&entity_type=<?= urlencode($entity_type) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= $base ?>/admin/search-history?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&entity_type=<?= urlencode($entity_type) ?>">Next</a>
                </li>
            </ul>
        </nav>
        <div class="text-center text-muted small mb-4">Page <?= $page ?> of <?= $total_pages ?> (<?= number_format($total) ?> total records)</div>
    <?php endif; ?>
</div>
