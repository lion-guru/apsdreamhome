<!-- Inquiries Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">Website Inquiries</h1>
        <p class="text-muted mb-0">All website form submissions</p>
    </div>
</div>

<?php if (isset($success) && $success): ?>
<div class="alert alert-success alert-dismissible fade show">
    <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if (isset($error) && $error): ?>
<div class="alert alert-danger alert-dismissible fade show">
    <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Stats Row -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <a href="<?php echo BASE_URL; ?>/admin/inquiries" class="text-decoration-none">
            <div class="card border-0 shadow-sm <?php echo !$status ? 'bg-primary text-white' : ''; ?>">
                <div class="card-body text-center">
                    <div class="h2 mb-0"><?php echo $total; ?></div>
                    <small><?php echo !$status ? '' : 'All Inquiries'; ?></small>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="<?php echo BASE_URL; ?>/admin/inquiries?status=new" class="text-decoration-none">
            <div class="card border-0 shadow-sm <?php echo $status === 'new' ? 'bg-danger text-white' : ''; ?>">
                <div class="card-body text-center">
                    <div class="h2 mb-0"><?php echo $newCount; ?></div>
                    <small>New</small>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="<?php echo BASE_URL; ?>/admin/inquiries?status=pending" class="text-decoration-none">
            <div class="card border-0 shadow-sm <?php echo $status === 'pending' ? 'bg-warning text-white' : ''; ?>">
                <div class="card-body text-center">
                    <div class="h2 mb-0"><?php echo $pendingCount; ?></div>
                    <small>Pending</small>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="<?php echo BASE_URL; ?>/admin/inquiries?status=contacted" class="text-decoration-none">
            <div class="card border-0 shadow-sm <?php echo $status === 'contacted' ? 'bg-success text-white' : ''; ?>">
                <div class="card-body text-center">
                    <div class="h2 mb-0"><?php echo $contactedCount; ?></div>
                    <small>Contacted</small>
                </div>
            </div>
        </a>
    </div>
</div>

<!-- Filter Form -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Search by name, email, phone..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="new" <?php echo $status === 'new' ? 'selected' : ''; ?>>New</option>
                    <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="contacted" <?php echo $status === 'contacted' ? 'selected' : ''; ?>>Contacted</option>
                    <option value="closed" <?php echo $status === 'closed' ? 'selected' : ''; ?>>Closed</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search me-1"></i>Filter
                </button>
            </div>
            <div class="col-md-3">
                <a href="<?php echo BASE_URL; ?>/admin/inquiries" class="btn btn-outline-secondary w-100">
                    <i class="fas fa-times me-1"></i>Clear
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Inquiries Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <?php if (!empty($inquiries)): ?>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="border-0 ps-4">Name</th>
                        <th class="border-0">Contact</th>
                        <th class="border-0">Type</th>
                        <th class="border-0">Message</th>
                        <th class="border-0">Status</th>
                        <th class="border-0">Date</th>
                        <th class="border-0 text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($inquiries as $inq): ?>
                    <tr class="<?php echo $inq['status'] === 'new' ? 'table-warning' : ''; ?>">
                        <td class="ps-4">
                            <div class="fw-semibold"><?php echo htmlspecialchars(inq['name'] ?? ''); ?></div>
                            <?php if (!empty($inq['email'])): ?>
                            <small class="text-muted"><?php echo htmlspecialchars(inq['email'] ?? ''); ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div><i class="fas fa-phone text-muted me-1"></i> <?php echo htmlspecialchars(inq['phone'] ?? ''); ?></div>
                        </td>
                        <td>
                            <span class="badge bg-secondary"><?php echo htmlspecialchars(ucfirst($inq['type'] ?? 'General')); ?></span>
                        </td>
                        <td style="max-width: 200px;">
                            <small class="text-muted"><?php echo htmlspecialchars(substr($inq['message'], 0, 80)); ?><?php echo strlen($inq['message']) > 80 ? '...' : ''; ?></small>
                        </td>
                        <td>
                            <?php
                            $statusClass = match($inq['status']) {
                                'new' => 'bg-danger',
                                'pending' => 'bg-warning',
                                'contacted' => 'bg-info',
                                'closed' => 'bg-secondary',
                                default => 'bg-secondary'
                            };
                            ?>
                            <span class="badge <?php echo $statusClass; ?>"><?php echo htmlspecialchars(ucfirst($inq['status'])); ?></span>
                        </td>
                        <td>
                            <small><?php echo date('d M Y', strtotime($inq['created_at'])); ?></small>
                        </td>
                        <td class="text-end pe-4">
                            <div class="btn-group">
                                <a href="<?php echo BASE_URL; ?>/admin/inquiries/view/<?php echo $inq['id']; ?>" class="btn btn-sm btn-outline-primary" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="https://wa.me/91<?php echo preg_replace('/[^0-9]/', '', $inq['phone']); ?>" target="_blank" class="btn btn-sm btn-success" title="WhatsApp">
                                    <i class="fab fa-whatsapp"></i>
                                </a>
                                <form method="POST" action="<?php echo BASE_URL; ?>/admin/inquiries/delete/<?php echo $inq['id']; ?>" class="d-inline" onsubmit="return confirm('Delete this inquiry?');">
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="card-footer bg-transparent">
            <nav>
                <ul class="pagination justify-content-center mb-0">
                    <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $page - 1; ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>">Previous</a>
                    </li>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                    </li>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $page + 1; ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>">Next</a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
        <?php endif; ?>

        <?php else: ?>
        <div class="text-center py-5">
            <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
            <h5 class="text-muted">No inquiries found</h5>
            <?php if ($search || $status): ?>
            <a href="<?php echo BASE_URL; ?>/admin/inquiries" class="btn btn-primary">View All</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
