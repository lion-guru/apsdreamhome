<!-- Tenant List — Super Admin -->
<?php
$tenants = $tenants ?? [];
$total = $total ?? 0;
$page = $page ?? 1;
$pages = $pages ?? 1;
$filters = $filters ?? [];
$stats = $stats ?? [];
$plans = $plans ?? [];
$base = BASE_URL ?? '';
?>
<style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
.tenant-list-header { background: linear-gradient(135deg, #0f2027, #203a43, #2c5364); color: #fff; border-radius: 12px; padding: 24px; margin-bottom: 20px; }
.tenant-row:hover { background: #f8fafc; }
.usage-bar { height: 6px; border-radius: 3px; background: #e2e8f0; overflow: hidden; }
.usage-fill { height: 100%; border-radius: 3px; transition: width 0.3s; }
</style>

<div class="tenant-list-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-0"><i class="fas fa-building me-2"></i>Tenant Management</h4>
            <p class="mb-0 mt-1" class="style-91394">Manage all SaaS tenants and subscriptions (<?= $total ?> total)</p>
        </div>
        <div>
            <a href="<?= $base ?>/admin/tenants/dashboard" class="btn btn-outline-light btn-sm me-2"><i class="fas fa-chart-line me-1"></i>Dashboard</a>
            <a href="<?= $base ?>/admin/tenants/onboard" class="btn btn-light btn-sm"><i class="fas fa-plus me-1"></i>New Tenant</a>
        </div>
    </div>
</div>

<!-- Flash messages -->
<?php if (!empty($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i><?= $_SESSION['success'] ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>
<?php if (!empty($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show"><i class="fas fa-exclamation-circle me-2"></i><?= $_SESSION['error'] ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<!-- Stats Row -->
<div class="row mb-3">
    <div class="col-lg-3 col-6">
        <div class="small-box" class="style-75630">
            <div class="inner"><h3><?= $stats['total_tenants'] ?? 0 ?></h3><p>Total Tenants</p></div>
            <div class="icon"><i class="fas fa-building"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box" class="style-55192">
            <div class="inner"><h3><?= $stats['active_tenants'] ?? 0 ?></h3><p>Active</p></div>
            <div class="icon"><i class="fas fa-check-circle"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box" class="style-48582">
            <div class="inner"><h3>₹<?= number_format($stats['monthly_revenue'] ?? 0) ?></h3><p>MRR</p></div>
            <div class="icon"><i class="fas fa-rupee-sign"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box" class="style-23498">
            <div class="inner"><h3><?= $stats['trial_tenants'] ?? 0 ?></h3><p>Trial</p></div>
            <div class="icon"><i class="fas fa-flask"></i></div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" action="<?= $base ?>/admin/tenants" class="row g-2 align-items-center">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Search tenants..." value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Status</option>
                    <?php foreach (['active','trial','suspended','cancelled'] as $s): ?>
                        <option value="<?= $s ?>" <?= ($filters['status'] ?? '') === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select name="plan_id" class="form-select form-select-sm">
                    <option value="">All Plans</option>
                    <?php foreach ($plans as $p): ?>
                        <option value="<?= $p['id'] ?>" <?= (int)($filters['plan_id'] ?? 0) === (int)$p['id'] ? 'selected' : '' ?>><?= htmlspecialchars($p['name'] ?? '') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary btn-sm w-100"><i class="fas fa-search me-1"></i>Filter</button>
            </div>
            <div class="col-md-2">
                <a href="<?= $base ?>/admin/tenants" class="btn btn-outline-secondary btn-sm w-100">Clear</a>
            </div>
        </form>
    </div>
</div>

<!-- Tenants Table -->
<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="style-77391">#</th>
                        <th>Tenant</th>
                        <th>Plan</th>
                        <th>Status</th>
                        <th>Users</th>
                        <th>Leads</th>
                        <th>Contact</th>
                        <th>Created</th>
                        <th class="style-50190">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($tenants)): ?>
                        <tr><td colspan="9" class="text-center text-muted py-5">No tenants found</td></tr>
                    <?php else: ?>
                        <?php $i = ($page - 1) * ($per_page ?? 20); foreach ($tenants as $t): $i++; ?>
                            <tr class="tenant-row">
                                <td class="text-muted"><?= $i ?></td>
                                <td>
                                    <div class="fw-semibold"><?= htmlspecialchars($t['name'] ?? '') ?></div>
                                    <small class="text-muted"><code><?= htmlspecialchars($t['slug'] ?? '') ?></code></small>
                                    <?php if ($t['domain']): ?>
                                        <br><small class="text-muted"><i class="fas fa-globe me-1"></i><?= htmlspecialchars($t['domain'] ?? '') ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge bg-info"><?= htmlspecialchars($t['plan_name'] ?? 'Free') ?></span></td>
                                <td>
                                    <?php
                                    $sc = ['active'=>'success','trial'=>'warning','suspended'=>'danger','cancelled'=>'secondary'];
                                    ?>
                                    <span class="badge bg-<?= $sc[$t['status']] ?? 'secondary' ?>"><?= ucfirst($t['status']) ?></span>
                                </td>
                                <td>
                                    <span><?= $t['users_count'] ?? 0 ?></span>/<small><?= $t['max_users'] ?></small>
                                    <?php
                                    $usagePct = $t['max_users'] > 0 ? round(($t['users_count'] ?? 0) / $t['max_users'] * 100) : 0;
                                    $barColor = $usagePct > 80 ? '#ef4444' : ($usagePct > 50 ? '#f59e0b' : '#10b981');
                                    ?>
                                    <div class="usage-bar mt-1"><div class="usage-fill" class="style-76358"></div></div>
                                </td>
                                <td>
                                    <span><?= $t['leads_count'] ?? 0 ?></span>/<small><?= number_format($t['max_leads']) ?></small>
                                </td>
                                <td>
                                    <small class="text-muted"><?= htmlspecialchars($t['contact_name'] ?? '—') ?></small>
                                    <br><small class="text-muted"><?= htmlspecialchars($t['contact_email'] ?? '') ?></small>
                                </td>
                                <td><small class="text-muted"><?= date('d M Y', strtotime($t['created_at'])) ?></small></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= $base ?>/admin/tenants/<?= $t['id'] ?>" class="btn btn-outline-primary" title="View"><i class="fas fa-eye"></i></a>
                                        <a href="<?= $base ?>/admin/tenants/<?= $t['id'] ?>/edit" class="btn btn-outline-secondary" title="Edit"><i class="fas fa-edit"></i></a>
                                        <?php if (($t['status'] ?? '') === 'active' && ($_SESSION['admin_role'] ?? '') === 'super_admin'): ?>
                                            <form method="POST" action="<?= $base ?>/admin/tenants/<?= $t['id'] ?>/switch" class="style-35851">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                                <button type="submit" class="btn btn-outline-success btn-sm" title="Switch to this tenant" onclick="return confirm('Switch to <?= htmlspecialchars($t['name'] ?? '') ?>?')">
                                                    <i class="fas fa-exchange-alt"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Pagination -->
<?php if ($pages > 1): ?>
    <nav class="mt-3">
        <ul class="pagination justify-content-center">
            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($filters['search'] ?? '') ?>&status=<?= urlencode($filters['status'] ?? '') ?>&plan_id=<?= urlencode($filters['plan_id'] ?? '') ?>">Prev</a>
            </li>
            <?php
            $start = max(1, $page - 3);
            $end = min($pages, $page + 3);
            for ($p = $start; $p <= $end; $p++): ?>
                <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $p ?>&search=<?= urlencode($filters['search'] ?? '') ?>&status=<?= urlencode($filters['status'] ?? '') ?>&plan_id=<?= urlencode($filters['plan_id'] ?? '') ?>"><?= $p ?></a>
                </li>
            <?php endfor; ?>
            <li class="page-item <?= $page >= $pages ? 'disabled' : '' ?>">
                <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($filters['search'] ?? '') ?>&status=<?= urlencode($filters['status'] ?? '') ?>&plan_id=<?= urlencode($filters['plan_id'] ?? '') ?>">Next</a>
            </li>
        </ul>
    </nav>
<?php endif; ?>
