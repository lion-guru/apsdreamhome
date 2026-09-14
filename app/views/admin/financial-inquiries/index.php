<?php
$page_title = $page_title ?? 'Financial Inquiries';
$active_page = 'financial-inquiries';

$statusLabels = [
    'new' => ['label' => 'New', 'class' => 'primary'],
    'contacted' => ['label' => 'Contacted', 'class' => 'info'],
    'converted' => ['label' => 'Converted', 'class' => 'success'],
    'closed' => ['label' => 'Closed', 'class' => 'secondary'],
];
?>
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-money-check-alt"></i> Financial Inquiries</h1>
</div>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show"><?= htmlspecialchars($_SESSION['success']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>
<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show"><?= htmlspecialchars($_SESSION['error']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<!-- Stats -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white h-100">
            <div class="card-body text-center py-3">
                <h3><?= $stats['total'] ?? 0 ?></h3>
                <p class="mb-0 small">Total Inquiries</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white h-100">
            <div class="card-body text-center py-3">
                <h3><?= $stats['new_count'] ?? 0 ?></h3>
                <p class="mb-0 small">New</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-dark h-100">
            <div class="card-body text-center py-3">
                <h3><?= $stats['contacted'] ?? 0 ?></h3>
                <p class="mb-0 small">Contacted</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white h-100">
            <div class="card-body text-center py-3">
                <h3><?= $stats['converted'] ?? 0 ?></h3>
                <p class="mb-0 small">Converted</p>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="<?= BASE_URL ?>/admin/financial-inquiries">
            <div class="row g-2">
                <div class="col-md-4">
                    <input type="text" class="form-control" name="search" placeholder="Search name, email, phone..." value="<?= htmlspecialchars($search ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="status">
                        <option value="">All Statuses</option>
                        <?php foreach ($statusLabels as $key => $info): ?>
                            <option value="<?= $key ?>" <?= ($status ?? '') === $key ? 'selected' : '' ?>><?= $info['label'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filter</button>
                    <a href="<?= BASE_URL ?>/admin/financial-inquiries" class="btn btn-secondary">Clear</a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Inquiries Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Contact</th>
                        <th>Service Interest</th>
                        <th>Status</th>
                        <th>Assigned</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($inquiries)): ?>
                        <tr><td colspan="8" class="text-center py-4 text-muted"><i class="fas fa-inbox fa-2x mb-2"></i><br>No inquiries found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($inquiries as $inq): ?>
                            <?php $sl = $statusLabels[$inq['status']] ?? ['label' => $inq['status'], 'class' => 'secondary']; ?>
                            <tr class="<?= $inq['status'] === 'new' ? 'table-info' : '' ?>">
                                <td><?= $inq['id'] ?></td>
                                <td><strong><?= htmlspecialchars($inq['name'] ?? '') ?></strong></td>
                                <td>
                                    <?php if (!empty($inq['email'])): ?><small><i class="fas fa-envelope me-1"></i><?= htmlspecialchars($inq['email']) ?></small><br><?php endif; ?>
                                    <?php if (!empty($inq['phone'])): ?><small><i class="fas fa-phone me-1"></i><?= htmlspecialchars($inq['phone']) ?></small><?php endif; ?>
                                </td>
                                <td><span class="badge bg-light text-dark"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $inq['service_interest'] ?? 'General'))) ?></span></td>
                                <td><span class="badge bg-<?= $sl['class'] ?>"><?= $sl['label'] ?></span></td>
                                <td><?= htmlspecialchars($inq['assigned_name'] ?? '<em>Unassigned</em>') ?></td>
                                <td><small><?= date('d M Y', strtotime($inq['created_at'])) ?></small></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= BASE_URL ?>/admin/financial-inquiries/<?= $inq['id'] ?>" class="btn btn-outline-primary" title="View"><i class="fas fa-eye"></i></a>
                                        <a href="<?= BASE_URL ?>/admin/financial-inquiries/<?= $inq['id'] ?>/delete" class="btn btn-outline-danger" title="Delete" onclick="return confirm('Delete this inquiry?')"><i class="fas fa-trash"></i></a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if (($totalPages ?? 1) > 1): ?>
            <nav class="mt-3">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $page - 1 ?>&status=<?= $status ?? '' ?>&search=<?= $search ?? '' ?>">Previous</a>
                    </li>
                    <?php for ($i = max(1, $page - 3); $i <= min($totalPages, $page + 3); $i++): ?>
                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>&status=<?= $status ?? '' ?>&search=<?= $search ?? '' ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $page + 1 ?>&status=<?= $status ?? '' ?>&search=<?= $search ?? '' ?>">Next</a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>
