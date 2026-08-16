<?php
$page_title = $page_title ?? 'Agreements - APS Dream Home';
$active_page = 'agreements';
$agreements = $agreements ?? [];
$stats = $stats ?? [];
$filters = $filters ?? ['type' => '', 'status' => '', 'search' => '', 'date_from' => '', 'date_to' => ''];
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-file-contract me-2"></i>Agreements</h1>
    <a href="<?= BASE_URL ?>/admin/agreements/create" class="btn btn-primary"><i class="fas fa-plus me-1"></i>New Agreement</a>
</div>

<?php if (isset($_SESSION['flash_success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($_SESSION['flash_success'] ?? '') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>
<?php if (isset($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($_SESSION['flash_error'] ?? '') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-md-2">
        <div class="card bg-primary text-white">
            <div class="card-body text-center">
                <h4 class="mb-0"><?= $stats['total'] ?? 0 ?></h4>
                <small>Total</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card bg-secondary text-white">
            <div class="card-body text-center">
                <h4 class="mb-0"><?= $stats['draft'] ?? 0 ?></h4>
                <small>Draft</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card bg-warning text-dark">
            <div class="card-body text-center">
                <h4 class="mb-0"><?= $stats['pending_signature'] ?? 0 ?></h4>
                <small>Pending Sig.</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card bg-info text-white">
            <div class="card-body text-center">
                <h4 class="mb-0"><?= $stats['signed'] ?? 0 ?></h4>
                <small>Signed</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <h4 class="mb-0"><?= $stats['registered'] ?? 0 ?></h4>
                <small>Registered</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card bg-danger text-white">
            <div class="card-body text-center">
                <h4 class="mb-0"><?= ($stats['cancelled'] ?? 0) + ($stats['expired'] ?? 0) ?></h4>
                <small>Cancelled/Exp.</small>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<form method="GET" class="row g-3 mb-4">
    <?php echo CSRFProtection::csrfField(); ?>
    <div class="col-md-2">
        <select class="form-select" name="type">
            <option value="">All Types</option>
            <?php foreach (['sale_deed' => 'Sale Deed', 'allotment' => 'Allotment', 'mortgage' => 'Mortgage', 'lease' => 'Lease', 'nda' => 'NDA', 'joint_venture' => 'Joint Venture', 'other' => 'Other'] as $val => $label): ?>
                <option value="<?= $val ?>" <?= ($filters['type'] ?? '') === $val ? 'selected' : '' ?>><?= $label ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-2">
        <select class="form-select" name="status">
            <option value="">All Status</option>
            <?php foreach (['draft' => 'Draft', 'pending_signature' => 'Pending Signature', 'signed' => 'Signed', 'registered' => 'Registered', 'cancelled' => 'Cancelled', 'expired' => 'Expired'] as $val => $label): ?>
                <option value="<?= $val ?>" <?= ($filters['status'] ?? '') === $val ? 'selected' : '' ?>><?= $label ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-2">
        <input type="text" class="form-control" name="search" placeholder="Search agreements..." value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
    </div>
    <div class="col-md-2">
        <input type="date" class="form-control" name="date_from" value="<?= htmlspecialchars($filters['date_from'] ?? '') ?>" placeholder="From">
    </div>
    <div class="col-md-2">
        <input type="date" class="form-control" name="date_to" value="<?= htmlspecialchars($filters['date_to'] ?? '') ?>" placeholder="To">
    </div>
    <div class="col-md-2 d-flex gap-1">
        <button type="submit" class="btn btn-primary flex-fill"><i class="fas fa-search"></i> Filter</button>
        <a href="<?= BASE_URL ?>/admin/agreements" class="btn btn-secondary"><i class="fas fa-redo"></i></a>
    </div>
</form>

<!-- Agreements Table -->
<div class="card aps-cp-card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Agreement No</th>
                        <th>Type</th>
                        <th>Plot</th>
                        <th>Colony</th>
                        <th>Party A</th>
                        <th>Party B</th>
                        <th>Value</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($agreements)): ?>
                        <tr>
                            <td colspan="11" class="text-center py-4 text-muted">
                                <i class="fas fa-file-contract fa-3x mb-3 d-block"></i>
                                No agreements found. <a href="<?= BASE_URL ?>/admin/agreements/create">Create one</a>.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $i = ($current_page - 1) * 20 + 1; ?>
                        <?php foreach ($agreements as $a): ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td><code><?= htmlspecialchars($a['agreement_number'] ?? '') ?></code></td>
                                <td>
                                    <?php
                                    $typeLabels = ['sale_deed' => 'Sale Deed', 'allotment' => 'Allotment', 'mortgage' => 'Mortgage', 'lease' => 'Lease', 'nda' => 'NDA', 'joint_venture' => 'Joint Venture', 'other' => 'Other'];
                                    $typeBadge = match($a['agreement_type'] ?? '') {
                                        'sale_deed' => 'primary',
                                        'allotment' => 'success',
                                        'mortgage' => 'warning',
                                        'lease' => 'info',
                                        'nda' => 'secondary',
                                        'joint_venture' => 'dark',
                                        default => 'secondary'
                                    };
                                    ?>
                                    <span class="badge bg-<?= $typeBadge ?>"><?= $typeLabels[$a['agreement_type']] ?? $a['agreement_type'] ?></span>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($a['plot_number'] ?? '—') ?></strong>
                                    <?php if (!empty($a['block'])): ?>
                                        <br><small class="text-muted">Block: <?= htmlspecialchars($a['block'] ?? '') ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><small><?= htmlspecialchars($a['colony_name'] ?? '—') ?></small></td>
                                <td><?= htmlspecialchars($a['party_a_name'] ?? '—') ?></td>
                                <td><?= htmlspecialchars($a['party_b_name'] ?? '—') ?></td>
                                <td>Rs. <?= number_format(floatval($a['total_value'] ?? 0), 0) ?></td>
                                <td>
                                    <?php
                                    $statusBadge = match($a['status'] ?? 'draft') {
                                        'draft' => 'secondary',
                                        'pending_signature' => 'warning',
                                        'signed' => 'info',
                                        'registered' => 'success',
                                        'cancelled' => 'danger',
                                        'expired' => 'dark',
                                        default => 'secondary'
                                    };
                                    $statusLabels = ['draft' => 'Draft', 'pending_signature' => 'Pending Sig.', 'signed' => 'Signed', 'registered' => 'Registered', 'cancelled' => 'Cancelled', 'expired' => 'Expired'];
                                    ?>
                                    <span class="badge bg-<?= $statusBadge ?>"><?= $statusLabels[$a['status']] ?? ucfirst($a['status']) ?></span>
                                </td>
                                <td><small><?= date('d M Y', strtotime($a['agreement_date'] ?? 'now')) ?></small></td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= BASE_URL ?>/admin/agreements/<?= $a['id'] ?>" class="btn btn-outline-primary" title="View"><i class="fas fa-eye"></i></a>
                                        <?php if ($a['booking_id']): ?>
                                            <a href="<?= BASE_URL ?>/admin/agreements/preview/<?= $a['booking_id'] ?>/allotment" class="btn btn-outline-success" title="Generate PDF" target="_blank"><i class="fas fa-file-pdf"></i></a>
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
<?php if ($total_pages > 1): ?>
<nav class="mt-3">
    <ul class="pagination justify-content-center">
        <li class="page-item <?= $current_page <= 1 ? 'disabled' : '' ?>">
            <a class="page-link" href="?page=<?= $current_page - 1 ?>&type=<?= urlencode($filters['type'] ?? '') ?>&status=<?= urlencode($filters['status'] ?? '') ?>&search=<?= urlencode($filters['search'] ?? '') ?>&date_from=<?= urlencode($filters['date_from'] ?? '') ?>&date_to=<?= urlencode($filters['date_to'] ?? '') ?>">Previous</a>
        </li>
        <?php for ($p = max(1, $current_page - 2); $p <= min($total_pages, $current_page + 2); $p++): ?>
            <li class="page-item <?= $p == $current_page ? 'active' : '' ?>">
                <a class="page-link" href="?page=<?= $p ?>&type=<?= urlencode($filters['type'] ?? '') ?>&status=<?= urlencode($filters['status'] ?? '') ?>&search=<?= urlencode($filters['search'] ?? '') ?>&date_from=<?= urlencode($filters['date_from'] ?? '') ?>&date_to=<?= urlencode($filters['date_to'] ?? '') ?>"><?= $p ?></a>
            </li>
        <?php endfor; ?>
        <li class="page-item <?= $current_page >= $total_pages ? 'disabled' : '' ?>">
            <a class="page-link" href="?page=<?= $current_page + 1 ?>&type=<?= urlencode($filters['type'] ?? '') ?>&status=<?= urlencode($filters['status'] ?? '') ?>&search=<?= urlencode($filters['search'] ?? '') ?>&date_from=<?= urlencode($filters['date_from'] ?? '') ?>&date_to=<?= urlencode($filters['date_to'] ?? '') ?>">Next</a>
        </li>
    </ul>
</nav>
<?php endif; ?>
