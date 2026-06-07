<?php $page_title = $page_title ?? 'Bank Accounts'; $page_heading = $page_heading ?? 'Bank Accounts Master'; ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-university me-2 text-primary"></i>Bank Accounts Master</h2>
        <a href="<?= BASE_URL ?>/admin/finance/bank-account-form" class="btn btn-primary"><i class="fas fa-plus me-1"></i>New Bank Account</a>
    </div>
    <div class="aps-cp-card">
        <div class="aps-cp-card-body p-0">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Account Name</th>
                        <th>Bank</th>
                        <th>A/c No.</th>
                        <th>IFSC</th>
                        <th>Type</th>
                        <th>Escrow</th>
                        <th class="text-end">Balance</th>
                        <th>Active</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($banks)): ?>
                    <tr><td colspan="9" class="text-center text-muted py-4">No bank accounts yet. Click "New Bank Account" to add one.</td></tr>
                <?php else: foreach ($banks as $b): ?>
                    <tr>
                        <td class="fw-semibold"><?= htmlspecialchars($b['account_name'] ?? '') ?></td>
                        <td><?= htmlspecialchars($b['bank_name'] ?? '') ?><br><small class="text-muted"><?= htmlspecialchars($b['branch'] ?? '') ?></small></td>
                        <td><code><?= htmlspecialchars($b['account_number'] ?? '') ?></code></td>
                        <td><code><?= htmlspecialchars($b['ifsc_code'] ?? '') ?></code></td>
                        <td><span class="badge bg-secondary"><?= htmlspecialchars($b['account_type'] ?? '') ?></span></td>
                        <td><?= !empty($b['is_escrow']) ? '<span class="badge bg-warning">Escrow</span>' : '-' ?></td>
                        <td class="text-end fw-bold">₹<?= number_format((float)($b['current_balance'] ?? 0), 2) ?></td>
                        <td><?= !empty($b['active']) ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' ?></td>
                        <td><a href="<?= BASE_URL ?>/admin/finance/bank-account-form?id=<?= (int)$b['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a></td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
