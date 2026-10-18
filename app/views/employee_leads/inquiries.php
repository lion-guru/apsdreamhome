<?php
$inquiries = $inquiries ?? [];
$stats = $stats ?? ['total' => 0, 'pending' => 0, 'in_progress' => 0, 'completed' => 0, 'closed' => 0];
$total = $total ?? 0;
$page = $page ?? 1;
$totalPages = $totalPages ?? 1;
$currentStatus = $status ?? '';
$search = $search ?? '';
$base = BASE_URL ?? '';

$statusColors = [
    'pending' => 'bg-warning text-dark',
    'new' => 'bg-info text-white',
    'in_progress' => 'bg-primary text-white',
    'completed' => 'bg-success text-white',
    'closed' => 'bg-secondary text-white',
];
?>

<div class="container-fluid py-4">
    <!-- Breadcrumb & Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="<?= $base ?>/employee/dashboard"><i class="fas fa-home me-1"></i>Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Assigned Inquiries</li>
                </ol>
            </nav>
            <h2 class="h4 mb-0 fw-bold text-dark">
                <i class="fas fa-envelope-open-text text-primary me-2"></i>My Assigned Inquiries
            </h2>
            <p class="text-muted small mb-0">Customer inquiries routed and assigned to you for support & follow-up</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= $base ?>/employee/leads" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-user-tie me-1"></i>My Leads
            </a>
            <a href="<?= $base ?>/employee/tasks" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-tasks me-1"></i>My Tasks
            </a>
        </div>
    </div>

    <!-- KPI Metric Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3 col-xl">
            <a href="<?= $base ?>/employee/inquiries<?= $search ? '?search=' . urlencode($search) : '' ?>" class="card text-decoration-none border-0 shadow-sm rounded-3 h-100 <?= !$currentStatus || $currentStatus === 'all' ? 'border-start border-4 border-primary bg-primary bg-opacity-10' : 'bg-white' ?>">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small text-uppercase fw-semibold">All Inquiries</span>
                            <h3 class="fw-bold mb-0 text-dark"><?= (int)($stats['total'] ?? 0) ?></h3>
                        </div>
                        <div class="bg-primary bg-opacity-25 text-primary rounded-circle p-2 fs-5">
                            <i class="fas fa-inbox"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3 col-xl">
            <a href="<?= $base ?>/employee/inquiries?status=pending<?= $search ? '&search=' . urlencode($search) : '' ?>" class="card text-decoration-none border-0 shadow-sm rounded-3 h-100 <?= $currentStatus === 'pending' ? 'border-start border-4 border-warning bg-warning bg-opacity-10' : 'bg-white' ?>">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small text-uppercase fw-semibold">Pending</span>
                            <h3 class="fw-bold mb-0 text-warning"><?= (int)($stats['pending'] ?? 0) ?></h3>
                        </div>
                        <div class="bg-warning bg-opacity-25 text-warning rounded-circle p-2 fs-5">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3 col-xl">
            <a href="<?= $base ?>/employee/inquiries?status=in_progress<?= $search ? '&search=' . urlencode($search) : '' ?>" class="card text-decoration-none border-0 shadow-sm rounded-3 h-100 <?= $currentStatus === 'in_progress' ? 'border-start border-4 border-info bg-info bg-opacity-10' : 'bg-white' ?>">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small text-uppercase fw-semibold">In Progress</span>
                            <h3 class="fw-bold mb-0 text-info"><?= (int)($stats['in_progress'] ?? 0) ?></h3>
                        </div>
                        <div class="bg-info bg-opacity-25 text-info rounded-circle p-2 fs-5">
                            <i class="fas fa-spinner"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3 col-xl">
            <a href="<?= $base ?>/employee/inquiries?status=completed<?= $search ? '&search=' . urlencode($search) : '' ?>" class="card text-decoration-none border-0 shadow-sm rounded-3 h-100 <?= $currentStatus === 'completed' ? 'border-start border-4 border-success bg-success bg-opacity-10' : 'bg-white' ?>">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small text-uppercase fw-semibold">Completed</span>
                            <h3 class="fw-bold mb-0 text-success"><?= (int)($stats['completed'] ?? 0) ?></h3>
                        </div>
                        <div class="bg-success bg-opacity-25 text-success rounded-circle p-2 fs-5">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3 col-xl">
            <a href="<?= $base ?>/employee/inquiries?status=closed<?= $search ? '&search=' . urlencode($search) : '' ?>" class="card text-decoration-none border-0 shadow-sm rounded-3 h-100 <?= $currentStatus === 'closed' ? 'border-start border-4 border-secondary bg-secondary bg-opacity-10' : 'bg-white' ?>">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small text-uppercase fw-semibold">Closed</span>
                            <h3 class="fw-bold mb-0 text-secondary"><?= (int)($stats['closed'] ?? 0) ?></h3>
                        </div>
                        <div class="bg-secondary bg-opacity-25 text-secondary rounded-circle p-2 fs-5">
                            <i class="fas fa-archive"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Search & Filter Card -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-3">
            <form method="GET" action="<?= $base ?>/employee/inquiries" class="row g-2 align-items-center">
                <?php if ($currentStatus): ?>
                    <input type="hidden" name="status" value="<?= htmlspecialchars($currentStatus) ?>">
                <?php endif; ?>
                <div class="col-md-9 col-12">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control bg-light border-0" placeholder="Search by customer name, phone, email, or message..." value="<?= htmlspecialchars($search) ?>">
                    </div>
                </div>
                <div class="col-md-3 col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-1"></i>Search
                    </button>
                    <?php if ($search || $currentStatus): ?>
                        <a href="<?= $base ?>/employee/inquiries" class="btn btn-outline-secondary" title="Reset Filters">
                            <i class="fas fa-undo"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Inquiries Table Card -->
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold">Inquiries List (<?= $total ?> records)</h6>
            <span class="text-muted small">Showing page <?= $page ?> of <?= $totalPages ?></span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Customer</th>
                        <th>Contact</th>
                        <th>Interested In</th>
                        <th>Message Preview</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Received</th>
                        <th class="text-end" style="width: 140px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($inquiries)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="fas fa-inbox fa-3x mb-3 text-secondary opacity-50"></i>
                                <p class="mb-1 fw-semibold">No assigned inquiries found</p>
                                <span class="small">When admin or system routes an inquiry to you, it will appear here immediately.</span>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($inquiries as $inq): 
                            $statusKey = strtolower($inq['status'] ?? 'pending');
                            $badgeClass = $statusColors[$statusKey] ?? 'bg-secondary text-white';
                            $priorityKey = strtolower($inq['priority'] ?? 'medium');
                            $priorityBadge = ($priorityKey === 'high') ? 'badge bg-danger' : (($priorityKey === 'low') ? 'badge bg-secondary' : 'badge bg-info text-dark');
                        ?>
                            <tr class="<?= empty($inq['is_read']) ? 'table-warning fw-semibold' : '' ?>">
                                <td class="text-muted small">#<?= (int)$inq['id'] ?></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                            <i class="fas fa-user small"></i>
                                        </div>
                                        <div>
                                            <div class="text-dark"><?= htmlspecialchars($inq['name']) ?></div>
                                            <?php if (empty($inq['is_read'])): ?>
                                                <span class="badge bg-danger p-1" style="font-size: 0.65rem;">NEW</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div><a href="tel:<?= htmlspecialchars($inq['phone']) ?>" class="text-decoration-none text-dark"><i class="fas fa-phone-alt text-success me-1 small"></i><?= htmlspecialchars($inq['phone']) ?></a></div>
                                    <?php if (!empty($inq['email'])): ?>
                                        <div class="text-muted small"><i class="fas fa-envelope me-1"></i><?= htmlspecialchars($inq['email']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($inq['property_title'])): ?>
                                        <span class="badge bg-light text-dark border"><i class="fas fa-home text-primary me-1"></i><?= htmlspecialchars(substr($inq['property_title'], 0, 20)) ?>...</span>
                                    <?php elseif (!empty($inq['project_name'])): ?>
                                        <span class="badge bg-light text-dark border"><i class="fas fa-city text-info me-1"></i><?= htmlspecialchars($inq['project_name']) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted small"><i class="fas fa-question-circle me-1"></i>General</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="text-truncate text-muted small" style="max-width: 220px;" title="<?= htmlspecialchars($inq['message']) ?>">
                                        <?= htmlspecialchars($inq['message']) ?>
                                    </div>
                                    <?php if (!empty($inq['followup_count'])): ?>
                                        <span class="badge bg-light text-muted border" style="font-size: 0.7rem;">
                                            <i class="fas fa-history me-1"></i><?= (int)$inq['followup_count'] ?> follow-up(s)
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="<?= $priorityBadge ?>"><?= ucfirst($priorityKey) ?></span>
                                </td>
                                <td>
                                    <span class="badge <?= $badgeClass ?>"><?= ucfirst(str_replace('_', ' ', $statusKey)) ?></span>
                                </td>
                                <td class="text-muted small">
                                    <?= date('d M, Y', strtotime($inq['created_at'])) ?>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-1">
                                        <a href="tel:<?= htmlspecialchars($inq['phone']) ?>" class="btn btn-sm btn-outline-success" title="Quick Call">
                                            <i class="fas fa-phone"></i>
                                        </a>
                                        <a href="https://wa.me/91<?= preg_replace('/\D/', '', $inq['phone']) ?>?text=<?= urlencode('Hello ' . $inq['name'] . ', this is regarding your inquiry with APS Dream Home.') ?>" target="_blank" class="btn btn-sm btn-outline-success" title="WhatsApp">
                                            <i class="fab fa-whatsapp"></i>
                                        </a>
                                        <a href="<?= $base ?>/employee/inquiries/<?= (int)$inq['id'] ?>" class="btn btn-sm btn-primary" title="View & Handle Inquiry">
                                            <i class="fas fa-eye me-1"></i>Open
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="card-footer bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                <span class="small text-muted">Page <?= $page ?> of <?= $totalPages ?></span>
                <nav aria-label="Page navigation">
                    <ul class="pagination pagination-sm mb-0">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?= $base ?>/employee/inquiries?page=<?= $page - 1 ?>&status=<?= urlencode($currentStatus) ?>&search=<?= urlencode($search) ?>">Prev</a>
                            </li>
                        <?php endif; ?>
                        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                            <li class="page-item <?= ($p === $page) ? 'active' : '' ?>">
                                <a class="page-link" href="<?= $base ?>/employee/inquiries?page=<?= $p ?>&status=<?= urlencode($currentStatus) ?>&search=<?= urlencode($search) ?>"><?= $p ?></a>
                            </li>
                        <?php endfor; ?>
                        <?php if ($page < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?= $base ?>/employee/inquiries?page=<?= $page + 1 ?>&status=<?= urlencode($currentStatus) ?>&search=<?= urlencode($search) ?>">Next</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>
    </div>
</div>
