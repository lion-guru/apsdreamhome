<?php
// Chart of Accounts Index View
$accounts = $accounts ?? [];
$filters = $filters ?? ['search' => '', 'type' => '', 'active' => ''];
$types = $types ?? ['asset', 'liability', 'equity', 'income', 'expense'];
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Chart of Accounts</h1>
        <a href="<?= BASE_URL ?>/admin/chart-of-accounts/create" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Add Account
        </a>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" value="<?= htmlspecialchars($filters['search']) ?>" placeholder="Code, Name, Description...">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Account Type</label>
                    <select name="type" class="form-select">
                        <option value="">All Types</option>
                        <?php foreach ($types as $t): ?>
                        <option value="<?= $t ?>" <?= $filters['type'] === $t ? 'selected' : '' ?>><?= ucfirst($t) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="active" class="form-select">
                        <option value="">All</option>
                        <option value="1" <?= $filters['active'] === '1' ? 'selected' : '' ?>>Active</option>
                        <option value="0" <?= $filters['active'] === '0' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <a href="<?= BASE_URL ?>/admin/chart-of-accounts" class="btn btn-outline-secondary w-100">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Category</th>
                            <th>Parent</th>
                            <th>Opening Balance</th>
                            <th>Status</th>
                            <th>Usage</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($accounts)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">No accounts found</td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($accounts as $acc): ?>
                        <tr>
                            <td><code><?= htmlspecialchars($acc['account_code']) ?></code></td>
                            <td><?= htmlspecialchars($acc['account_name']) ?></td>
                            <td><span class="badge bg-<?= match($acc['account_type']) { 'asset' => 'success', 'liability' => 'danger', 'equity' => 'primary', 'income' => 'info', 'expense' => 'warning', default => 'secondary' } ?>"><?= ucfirst($acc['account_type']) ?></span></td>
                            <td><?= htmlspecialchars(str_replace('_', ' ', $acc['account_category'])) ?></td>
                            <td>
                                <?php if (!empty($acc['parent_account_id'])): ?>
                                <small class="text-muted">Parent ID: <?= $acc['parent_account_id'] ?></small>
                                <?php else: ?>
                                <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end"><?= number_format((float)$acc['opening_balance'], 2) ?></td>
                            <td>
                                <span class="badge bg-<?= $acc['is_active'] ? 'success' : 'secondary' ?>">
                                    <?= $acc['is_active'] ? 'Active' : 'Inactive' ?>
                                </span>
                            </td>
                            <td>
                                <?php if ((int)$acc['usage_count'] > 0): ?>
                                <span class="badge bg-info"><?= $acc['usage_count'] ?> journal lines</span>
                                <?php else: ?>
                                <span class="text-muted">Unused</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= BASE_URL ?>/admin/chart-of-accounts/edit/<?= $acc['id'] ?>" class="btn btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
                                    <?php if ((int)$acc['usage_count'] === 0): ?>
                                    <form method="POST" action="<?= BASE_URL ?>/admin/chart-of-accounts/delete/<?= $acc['id'] ?>" class="d-inline" onsubmit="return confirm('Delete this account?');">
                                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                        <button type="submit" class="btn btn-outline-danger" title="Delete"><i class="fas fa-trash"></i></button>
                                    </form>
                                    <?php else: ?>
                                    <button class="btn btn-outline-secondary" disabled title="Has journal entries - deactivate instead"><i class="fas fa-trash"></i></button>
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
</div>