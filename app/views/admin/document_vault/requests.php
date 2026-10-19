<?php
/** Admin Document Vault - Requests */

$page_title = $page_title ?? 'Document Requests';
$requests = $requests ?? [];
$categories = $categories ?? [];
$employees = $employees ?? [];
$filters = $filters ?? [];
?>

<style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
.req-row { transition: background 0.15s; }
.req-row:hover { background: #fefcf6; }
.status-badge { font-size: 0.7rem; padding: 0.2rem 0.5rem; border-radius: 999px; }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold"><i class="fas fa-clipboard-list me-2 text-primary"></i>Document Requests</h4>
            <p class="text-muted mb-0 small">Manage document requests from employees</p>
        </div>
        <a href="<?= BASE_URL ?>/admin/document-vault/requests/create" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Create Request
        </a>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Employee</label>
                    <select name="employee_id" class="form-select form-select-sm">
                        <option value="">All Employees</option>
                        <?php foreach ($employees as $emp): ?>
                            <option value="<?= $emp['id'] ?>" <?= ($filters['employee_id'] ?? '') == $emp['id'] ? 'selected' : '' ?>><?= htmlspecialchars($emp['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Category</label>
                    <select name="category_id" class="form-select form-select-sm">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= ($filters['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        <option value="pending" <?= ($filters['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="submitted" <?= ($filters['status'] ?? '') === 'submitted' ? 'selected' : '' ?>>Submitted</option>
                        <option value="verified" <?= ($filters['status'] ?? '') === 'verified' ? 'selected' : '' ?>>Verified</option>
                        <option value="rejected" <?= ($filters['status'] ?? '') === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                        <option value="overdue" <?= ($filters['status'] ?? '') === 'overdue' ? 'selected' : '' ?>>Overdue</option>
                        <option value="cancelled" <?= ($filters['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-sm w-100"><i class="fas fa-search me-1"></i> Filter</button>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <a href="<?= BASE_URL ?>/admin/document-vault/requests" class="btn btn-outline-secondary btn-sm w-100">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Requests Table -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <?php if (empty($requests)): ?>
            <div class="text-center py-5">
                <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No Document Requests</h5>
                <p class="text-muted small mb-3">Create a request to request documents from employees</p>
                <a href="<?= BASE_URL ?>/admin/document-vault/requests/create" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Create Request</a>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 120px">Request #</th>
                            <th>Employee</th>
                            <th style="width: 150px">Category</th>
                            <th style="width: 120px">Due Date</th>
                            <th style="width: 100px">Priority</th>
                            <th style="width: 120px">Status</th>
                            <th style="width: 150px">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requests as $req): 
                            $statusClass = [
                                'pending' => 'warning',
                                'submitted' => 'info',
                                'verified' => 'success',
                                'rejected' => 'danger',
                                'overdue' => 'danger',
                                'cancelled' => 'secondary',
                            ][$req['status']] ?? 'secondary';
                            
                            $isOverdue = $req['due_date'] && $req['due_date'] < date('Y-m-d') && $req['status'] !== 'verified';
                        ?>
                        <tr class="req-row">
                            <td><code class="small"><?= htmlspecialchars($req['request_number']) ?></code></td>
                            <td>
                                <div class="fw-semibold"><?= htmlspecialchars($req['employee_name']) ?></div>
                                <div class="small text-muted"><?= htmlspecialchars($req['employee_email'] ?? '') ?></div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark" style="background: #f1f5f9; color: #475569;">
                                    <?= htmlspecialchars($req['category_name'] ?? '—') ?>
                                </span>
                            </td>
                            <td>
                                <?= $req['due_date'] ? date('d M Y', strtotime($req['due_date'])) : '—' ?>
                                <?php if ($isOverdue): ?><span class="badge bg-danger ms-1">Overdue</span><?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-<?= 
                                    $req['priority'] === 'urgent' ? 'danger' : 
                                    ($req['priority'] === 'high' ? 'warning' : 
                                    ($req['priority'] === 'low' ? 'secondary' : 'primary')) ?>">
                                    <?= ucfirst($req['priority']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-<?= $statusClass ?> status-badge">
                                    <?= ucfirst($req['status']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= BASE_URL ?>/admin/document-vault/requests/<?= $req['id'] ?>" class="btn btn-outline-primary" title="View"><i class="fas fa-eye"></i></a>
                                    <?php if ($req['status'] === 'pending'): ?>
                                        <a href="<?= BASE_URL ?>/admin/document-vault/requests/<?= $req['id'] ?>/verify" class="btn btn-outline-success" onclick="return confirm('Verify this request?')"><i class="fas fa-check"></i></a>
                                    <?php endif; ?>
                                    <?php if ($req['status'] === 'pending'): ?>
                                        <a href="<?= BASE_URL ?>/admin/document-vault/requests/<?= $req['id'] ?>/cancel" class="btn btn-outline-danger" onclick="return confirm('Cancel this request?')"><i class="fas fa-times"></i></a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>