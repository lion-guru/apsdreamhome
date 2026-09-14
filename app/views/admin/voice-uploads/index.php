<?php
$stats = $stats ?? ['total' => 0, 'pending' => 0, 'processed' => 0, 'failed' => 0];
$uploads = $uploads ?? [];
$search = $search ?? '';
$status = $status ?? '';
$page = $page ?? 1;
$totalPages = $totalPages ?? 1;
$totalRows = $totalRows ?? 0;
$success = $success ?? null;
$error = $error ?? null;
?>
<div class="container-fluid py-4">
    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-microphone me-2"></i>Voice Uploads</h4>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <a href="<?= BASE_URL ?>/admin/voice-uploads" class="text-decoration-none">
                <div class="card shadow-sm border-<?= $status === '' ? 'primary' : 'light' ?>">
                    <div class="card-body text-center py-3">
                        <div class="fs-3 fw-bold text-primary"><?= $stats['total'] ?></div>
                        <small class="text-muted">Total</small>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-3 col-6">
            <a href="<?= BASE_URL ?>/admin/voice-uploads?status=pending" class="text-decoration-none">
                <div class="card shadow-sm border-<?= $status === 'pending' ? 'warning' : 'light' ?>">
                    <div class="card-body text-center py-3">
                        <div class="fs-3 fw-bold text-warning"><?= $stats['pending'] ?></div>
                        <small class="text-muted">Pending</small>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-3 col-6">
            <a href="<?= BASE_URL ?>/admin/voice-uploads?status=processed" class="text-decoration-none">
                <div class="card shadow-sm border-<?= $status === 'processed' ? 'success' : 'light' ?>">
                    <div class="card-body text-center py-3">
                        <div class="fs-3 fw-bold text-success"><?= $stats['processed'] ?></div>
                        <small class="text-muted">Processed</small>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-3 col-6">
            <a href="<?= BASE_URL ?>/admin/voice-uploads?status=failed" class="text-decoration-none">
                <div class="card shadow-sm border-<?= $status === 'failed' ? 'danger' : 'light' ?>">
                    <div class="card-body text-center py-3">
                        <div class="fs-3 fw-bold text-danger"><?= $stats['failed'] ?></div>
                        <small class="text-muted">Failed</small>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="get" action="<?= BASE_URL ?>/admin/voice-uploads" class="row g-2 align-items-end">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Search by name or phone..." value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="processed" <?= $status === 'processed' ? 'selected' : '' ?>>Processed</option>
                        <option value="failed" <?= $status === 'failed' ? 'selected' : '' ?>>Failed</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search me-1"></i>Filter</button>
                    <a href="<?= BASE_URL ?>/admin/voice-uploads" class="btn btn-outline-secondary">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header aps-cp-card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-list me-2"></i>Voice Uploads (<?= $totalRows ?> records)</span>
        </div>
        <div class="card-body aps-cp-card-body p-0">
            <?php if (empty($uploads)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-microphone-slash fa-4x d-block mb-3"></i>
                    <h5>No voice uploads found</h5>
                    <p>Voice uploads will appear here when users submit voice samples.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>User Name</th>
                                <th>Phone</th>
                                <th>Language</th>
                                <th>Duration</th>
                                <th>File Size</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($uploads as $u): ?>
                            <tr>
                                <td>#<?= (int)$u['id'] ?></td>
                                <td><?= htmlspecialchars($u['user_name'] ?? '—') ?></td>
                                <td><?= htmlspecialchars($u['user_phone'] ?? '—') ?></td>
                                <td><?= htmlspecialchars(strtoupper($u['language'] ?? 'hi')) ?></td>
                                <td><?= (int)($u['duration_seconds'] ?? 0) ?>s</td>
                                <td><?php
                                    $bytes = (int)($u['file_size_bytes'] ?? 0);
                                    if ($bytes >= 1048576) echo round($bytes / 1048576, 1) . ' MB';
                                    elseif ($bytes >= 1024) echo round($bytes / 1024, 1) . ' KB';
                                    else echo $bytes . ' B';
                                ?></td>
                                <td>
                                    <?php
                                    $statusColors = ['pending' => 'warning', 'processed' => 'success', 'failed' => 'danger', 'deleted' => 'secondary'];
                                    $color = $statusColors[$u['status']] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?= $color ?>"><?= ucfirst($u['status']) ?></span>
                                </td>
                                <td><?= date('d M Y H:i', strtotime($u['created_at'] ?? 'now')) ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= BASE_URL ?>/admin/voice-uploads/show/<?= (int)$u['id'] ?>" class="btn btn-outline-primary" title="View"><i class="fas fa-eye"></i></a>
                                        <?php if ($u['status'] === 'pending'): ?>
                                            <a href="<?= BASE_URL ?>/admin/voice-uploads/process/<?= (int)$u['id'] ?>" class="btn btn-outline-success" title="Mark Processed" onclick="return confirm('Mark this upload as processed?')"><i class="fas fa-check"></i></a>
                                        <?php endif; ?>
                                        <a href="<?= BASE_URL ?>/admin/voice-uploads/delete/<?= (int)$u['id'] ?>" class="btn btn-outline-danger" title="Delete" onclick="return confirm('Delete this voice upload?')"><i class="fas fa-trash"></i></a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($totalPages > 1): ?>
                <div class="d-flex justify-content-between align-items-center px-3 py-3">
                    <small class="text-muted">Page <?= $page ?> of <?= $totalPages ?></small>
                    <nav>
                        <ul class="pagination pagination-sm mb-0">
                            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                <a class="page-link" href="<?= BASE_URL ?>/admin/voice-uploads?search=<?= urlencode($search) ?>&status=<?= urlencode($status) ?>&page=<?= $page - 1 ?>">&laquo;</a>
                            </li>
                            <?php
                            $startPage = max(1, $page - 2);
                            $endPage = min($totalPages, $page + 2);
                            for ($i = $startPage; $i <= $endPage; $i++):
                            ?>
                            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                <a class="page-link" href="<?= BASE_URL ?>/admin/voice-uploads?search=<?= urlencode($search) ?>&status=<?= urlencode($status) ?>&page=<?= $i ?>"><?= $i ?></a>
                            </li>
                            <?php endfor; ?>
                            <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                                <a class="page-link" href="<?= BASE_URL ?>/admin/voice-uploads?search=<?= urlencode($search) ?>&status=<?= urlencode($status) ?>&page=<?= $page + 1 ?>">&raquo;</a>
                            </li>
                        </ul>
                    </nav>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
