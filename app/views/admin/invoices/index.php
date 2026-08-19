<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Invoice Management</h1>
            <p class="text-muted mb-0">Generate, manage, and track professional invoices with GST/TDS breakdowns</p>
        </div>
        <div>
            <a href="<?= BASE_URL ?>/admin/invoices/manage/create" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Create Invoice
            </a>
        </div>
    </div>

    <?php if (!empty($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($_GET['success'] ?? '') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (!empty($_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($_GET['error'] ?? '') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card aps-cp-card border-start border-primary border-4">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted text-uppercase small">Total Invoices</h6>
                            <h3 class="mb-0 fw-bold"><?= (int)($stats['total_count'] ?? 0) ?></h3>
                        </div>
                        <div class="text-primary"><i class="fas fa-file-invoice fa-2x opacity-50"></i></div>
                    </div>
                    <div class="mt-2"><small class="text-muted">₹<?= number_format($stats['total_amount'] ?? 0, 2) ?> total value</small></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card aps-cp-card border-start border-success border-4">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted text-uppercase small">Paid</h6>
                            <h3 class="mb-0 fw-bold text-success"><?= (int)($stats['paid_count'] ?? 0) ?></h3>
                        </div>
                        <div class="text-success"><i class="fas fa-check-circle fa-2x opacity-50"></i></div>
                    </div>
                    <div class="mt-2"><small class="text-muted">₹<?= number_format($stats['paid_amount'] ?? 0, 2) ?> collected</small></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card aps-cp-card border-start border-warning border-4">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted text-uppercase small">Pending</h6>
                            <h3 class="mb-0 fw-bold text-warning"><?= (int)($stats['pending_count'] ?? 0) ?></h3>
                        </div>
                        <div class="text-warning"><i class="fas fa-clock fa-2x opacity-50"></i></div>
                    </div>
                    <div class="mt-2"><small class="text-muted">₹<?= number_format($stats['pending_amount'] ?? 0, 2) ?> outstanding</small></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card aps-cp-card border-start border-danger border-4">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted text-uppercase small">Overdue</h6>
                            <h3 class="mb-0 fw-bold text-danger"><?= (int)($stats['overdue_count'] ?? 0) ?></h3>
                        </div>
                        <div class="text-danger"><i class="fas fa-exclamation-triangle fa-2x opacity-50"></i></div>
                    </div>
                    <div class="mt-2"><small class="text-muted">₹<?= number_format($stats['overdue_amount'] ?? 0, 2) ?> past due</small></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card aps-cp-card mb-4">
        <div class="card-header aps-cp-card-header">
            <span><i class="fas fa-filter me-2"></i>Filter Invoices</span>
        </div>
        <div class="card-body aps-cp-card-body">
            <form method="GET" action="<?= BASE_URL ?>/admin/invoices/manage" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        <option value="draft" <?= ($filters['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option>
                        <option value="sent" <?= ($filters['status'] ?? '') === 'sent' ? 'selected' : '' ?>>Sent</option>
                        <option value="viewed" <?= ($filters['status'] ?? '') === 'viewed' ? 'selected' : '' ?>>Viewed</option>
                        <option value="paid" <?= ($filters['status'] ?? '') === 'paid' ? 'selected' : '' ?>>Paid</option>
                        <option value="overdue" <?= ($filters['status'] ?? '') === 'overdue' ? 'selected' : '' ?>>Overdue</option>
                        <option value="cancelled" <?= ($filters['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Invoice #, client name..." value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">From</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" value="<?= htmlspecialchars($filters['date_from'] ?? '') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">To</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" value="<?= htmlspecialchars($filters['date_to'] ?? '') ?>">
                </div>
                <div class="col-md-2 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-search me-1"></i>Filter</button>
                    <a href="<?= BASE_URL ?>/admin/invoices/manage" class="btn btn-sm btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card aps-cp-card">
        <div class="card-header aps-cp-card-header">
            <span><i class="fas fa-list me-2"></i>All Invoices (<?= number_format($total ?? 0) ?>)</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Invoice #</th>
                            <th>Client</th>
                            <th class="text-end">Amount</th>
                            <th class="text-end">Tax</th>
                            <th class="text-end">Total</th>
                            <th>Date</th>
                            <th>Due</th>
                            <th>Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($invoices)): ?>
                            <?php foreach ($invoices as $inv): ?>
                                <?php
                                $statusClass = [
                                    'paid' => 'success', 'sent' => 'primary', 'viewed' => 'info',
                                    'overdue' => 'danger', 'cancelled' => 'secondary', 'draft' => 'warning',
                                ];
                                $class = $statusClass[$inv['status']] ?? 'secondary';
                                ?>
                                <tr>
                                    <td>
                                        <a href="<?= BASE_URL ?>/admin/invoices/manage/<?= $inv['id'] ?>" class="fw-bold text-decoration-none">
                                            <?= htmlspecialchars($inv['invoice_number'] ?? '') ?>
                                        </a>
                                    </td>
                                    <td>
                                        <div class="fw-medium"><?= htmlspecialchars($inv['client_name'] ?? '') ?></div>
                                        <?php if (!empty($inv['client_email'])): ?>
                                            <small class="text-muted"><?= htmlspecialchars($inv['client_email'] ?? '') ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">₹<?= number_format($inv['subtotal'] ?? 0, 2) ?></td>
                                    <td class="text-end">₹<?= number_format($inv['tax_amount'] ?? 0, 2) ?></td>
                                    <td class="text-end fw-bold">₹<?= number_format($inv['total_amount'] ?? 0, 2) ?></td>
                                    <td><small><?= date('d M Y', strtotime($inv['invoice_date'])) ?></small></td>
                                    <td><small><?= date('d M Y', strtotime($inv['due_date'])) ?></small></td>
                                    <td><span class="badge bg-<?= $class ?>"><?= ucfirst($inv['status']) ?></span></td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= BASE_URL ?>/admin/invoices/manage/<?= $inv['id'] ?>" class="btn btn-outline-primary" title="View"><i class="fas fa-eye"></i></a>
                                            <a href="<?= BASE_URL ?>/admin/invoices/manage/<?= $inv['id'] ?>/edit" class="btn btn-outline-secondary" title="Edit"><i class="fas fa-edit"></i></a>
                                            <a href="<?= BASE_URL ?>/admin/invoices/<?= $inv['id'] ?>/pdf" class="btn btn-outline-success" title="PDF" target="_blank"><i class="fas fa-file-pdf"></i></a>
                                            <?php if ($inv['status'] !== 'paid'): ?>
                                                <form method="POST" action="<?= BASE_URL ?>/admin/invoices/manage/<?= $inv['id'] ?>/mark-paid" class="d-inline" onsubmit="return confirm('Mark this invoice as paid?')">
                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                                    <button class="btn btn-outline-success btn-sm" title="Mark Paid" aria-label="Confirm"><i class="fas fa-check"></i></button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="fas fa-file-invoice fa-3x mb-3 opacity-25"></i>
                                        <p class="mb-2 fw-medium">No invoices found</p>
                                        <a href="<?= BASE_URL ?>/admin/invoices/manage/create" class="btn btn-sm btn-primary mt-1">Create First Invoice</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php if (($total_pages ?? 1) > 1): ?>
            <div class="card-footer bg-white d-flex justify-content-center">
                <nav>
                    <ul class="pagination pagination-sm mb-0">
                        <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                            <?php
                            $qs = http_build_query(array_merge($filters, ['page' => $p]));
                            ?>
                            <li class="page-item <?= $p == $page ? 'active' : '' ?>">
                                <a class="page-link" href="<?= BASE_URL ?>/admin/invoices/manage?<?= $qs ?>"><?= $p ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>
    </div>
</div>
