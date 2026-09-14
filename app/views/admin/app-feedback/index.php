<?php
$stats = $stats ?? ['total' => 0, 'new' => 0, 'acknowledged' => 0, 'in_progress' => 0, 'resolved' => 0, 'closed' => 0, 'avg_rating' => 0];
$feedback = $feedback ?? [];
$total = $total ?? 0;
$page = $page ?? 1;
$total_pages = $total_pages ?? 1;
$filters = $filters ?? [];
$page_title = $page_title ?? 'App Feedback';
$base = defined('BASE_URL') ? BASE_URL : '/' . trim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><i class="fas fa-comments me-2"></i>App Feedback</h2>
            <p class="text-muted mb-0">Manage user feedback from mobile and web apps</p>
        </div>
        <a href="<?= e($base) ?>/admin/dashboard" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
        </a>
    </div>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($_SESSION['success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($_SESSION['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['warning'])): ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i><?= htmlspecialchars($_SESSION['warning']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['warning']); ?>
    <?php endif; ?>

    <div class="row g-3 mb-4">
        <div class="col-md-3 col-xl-2">
            <a href="<?= e($base) ?>/admin/app-feedback" class="text-decoration-none">
                <div class="card border-0 shadow-sm <?= ($filters['status'] ?? '') === '' ? 'border-primary' : '' ?>">
                    <div class="card-body text-center py-3">
                        <div class="fs-3 fw-bold text-primary"><?= number_format($stats['total']) ?></div>
                        <small class="text-muted">Total</small>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-3 col-xl-2">
            <a href="<?= e($base) ?>/admin/app-feedback?status=new" class="text-decoration-none">
                <div class="card border-0 shadow-sm <?= ($filters['status'] ?? '') === 'new' ? 'border-primary' : '' ?>">
                    <div class="card-body text-center py-3">
                        <div class="fs-3 fw-bold text-primary"><?= number_format($stats['new']) ?></div>
                        <small class="text-muted">New</small>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-3 col-xl-2">
            <a href="<?= e($base) ?>/admin/app-feedback?status=acknowledged" class="text-decoration-none">
                <div class="card border-0 shadow-sm <?= ($filters['status'] ?? '') === 'acknowledged' ? 'border-info' : '' ?>">
                    <div class="card-body text-center py-3">
                        <div class="fs-3 fw-bold text-info"><?= number_format($stats['acknowledged']) ?></div>
                        <small class="text-muted">Acknowledged</small>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-3 col-xl-2">
            <a href="<?= e($base) ?>/admin/app-feedback?status=in_progress" class="text-decoration-none">
                <div class="card border-0 shadow-sm <?= ($filters['status'] ?? '') === 'in_progress' ? 'border-warning' : '' ?>">
                    <div class="card-body text-center py-3">
                        <div class="fs-3 fw-bold text-warning"><?= number_format($stats['in_progress']) ?></div>
                        <small class="text-muted">In Progress</small>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-3 col-xl-2">
            <a href="<?= e($base) ?>/admin/app-feedback?status=resolved" class="text-decoration-none">
                <div class="card border-0 shadow-sm <?= ($filters['status'] ?? '') === 'resolved' ? 'border-success' : '' ?>">
                    <div class="card-body text-center py-3">
                        <div class="fs-3 fw-bold text-success"><?= number_format($stats['resolved']) ?></div>
                        <small class="text-muted">Resolved</small>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-3 col-xl-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-3">
                    <div class="fs-3 fw-bold text-warning">
                        <?= $stats['avg_rating'] ?>
                        <i class="fas fa-star small"></i>
                    </div>
                    <small class="text-muted">Avg Rating</small>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body aps-cp-card-body">
            <form method="GET" class="row g-3">
                <input type="hidden" name="status" value="<?= htmlspecialchars($filters['status'] ?? '') ?>">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="Search by name or email..." value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
                </div>
                <div class="col-md-2">
                    <select name="type" class="form-select">
                        <option value="">All Types</option>
                        <option value="bug" <?= ($filters['type'] ?? '') === 'bug' ? 'selected' : '' ?>>Bug</option>
                        <option value="feature" <?= ($filters['type'] ?? '') === 'feature' ? 'selected' : '' ?>>Feature</option>
                        <option value="improvement" <?= ($filters['type'] ?? '') === 'improvement' ? 'selected' : '' ?>>Improvement</option>
                        <option value="complaint" <?= ($filters['type'] ?? '') === 'complaint' ? 'selected' : '' ?>>Complaint</option>
                        <option value="other" <?= ($filters['type'] ?? '') === 'other' ? 'selected' : '' ?>>Other</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="platform" class="form-select">
                        <option value="">All Platforms</option>
                        <option value="android" <?= ($filters['platform'] ?? '') === 'android' ? 'selected' : '' ?>>Android</option>
                        <option value="ios" <?= ($filters['platform'] ?? '') === 'ios' ? 'selected' : '' ?>>iOS</option>
                        <option value="web" <?= ($filters['platform'] ?? '') === 'web' ? 'selected' : '' ?>>Web</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search me-1"></i> Filter</button>
                </div>
                <?php if (!empty($filters['search']) || !empty($filters['type']) || !empty($filters['platform'])): ?>
                    <div class="col-md-1">
                        <a href="<?= e($base) ?>/admin/app-feedback" class="btn btn-outline-secondary w-100"><i class="fas fa-times"></i></a>
                    </div>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body aps-cp-card-body">
            <?php if (!empty($feedback)): ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Type</th>
                                <th>Platform</th>
                                <th>Rating</th>
                                <th>Title</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($feedback as $item): ?>
                                <tr>
                                    <td>#<?= (int)$item['id'] ?></td>
                                    <td>
                                        <div class="fw-semibold"><?= htmlspecialchars($item['user_name'] ?? 'Anonymous') ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($item['user_email'] ?? '') ?></small>
                                    </td>
                                    <td>
                                        <?php
                                        $typeColors = ['bug' => 'danger', 'feature' => 'primary', 'improvement' => 'info', 'complaint' => 'warning', 'other' => 'secondary'];
                                        $typeColor = $typeColors[$item['type'] ?? 'other'] ?? 'secondary';
                                        ?>
                                        <span class="badge bg-<?= $typeColor ?>"><?= ucfirst(htmlspecialchars($item['type'] ?? 'other')) ?></span>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= ($item['platform'] ?? '') === 'android' ? 'success' : (($item['platform'] ?? '') === 'ios' ? 'dark' : 'info') ?>">
                                            <?= ucfirst(htmlspecialchars($item['platform'] ?? 'N/A')) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star <?= $i <= (int)($item['rating'] ?? 0) ? 'text-warning' : 'text-muted' ?>"></i>
                                        <?php endfor; ?>
                                    </td>
                                    <td><?= htmlspecialchars($item['title'] ?? '') ?></td>
                                    <td>
                                        <?php
                                        $statusColors = ['new' => 'primary', 'acknowledged' => 'info', 'in_progress' => 'warning', 'resolved' => 'success', 'closed' => 'secondary'];
                                        $statusColor = $statusColors[$item['status'] ?? 'new'] ?? 'secondary';
                                        ?>
                                        <span class="badge bg-<?= $statusColor ?>"><?= ucfirst(str_replace('_', ' ', $item['status'] ?? 'new')) ?></span>
                                    </td>
                                    <td><?= isset($item['created_at']) ? date('M d, Y', strtotime($item['created_at'])) : '-' ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= e($base) ?>/admin/app-feedback/<?= (int)$item['id'] ?>" class="btn btn-outline-primary" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?= e($base) ?>/admin/app-feedback/<?= (int)$item['id'] ?>/delete" class="btn btn-outline-danger" title="Delete" onclick="return confirm('Delete this feedback? This cannot be undone.')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($total_pages > 1): ?>
                    <nav class="mt-3">
                        <ul class="pagination justify-content-center mb-0">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($filters['search'] ?? '') ?>&type=<?= urlencode($filters['type'] ?? '') ?>&status=<?= urlencode($filters['status'] ?? '') ?>&platform=<?= urlencode($filters['platform'] ?? '') ?>">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                            <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($filters['search'] ?? '') ?>&type=<?= urlencode($filters['type'] ?? '') ?>&status=<?= urlencode($filters['status'] ?? '') ?>&platform=<?= urlencode($filters['platform'] ?? '') ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                            <?php if ($page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($filters['search'] ?? '') ?>&type=<?= urlencode($filters['type'] ?? '') ?>&status=<?= urlencode($filters['status'] ?? '') ?>&platform=<?= urlencode($filters['platform'] ?? '') ?>">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No feedback found</h5>
                    <p class="text-muted">User-submitted feedback from mobile and web apps will appear here.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
