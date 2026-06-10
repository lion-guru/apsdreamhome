<?php
$transactions = $transactions ?? [];
$stats = $stats ?? [];
$banks = $banks ?? [];
$financialYears = $financialYears ?? [];
$page_title = 'Banking & Reconciliation';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0"><i class="fas fa-university me-2"></i>Banking & Reconciliation</h1>
            <p class="text-muted mb-0">Manage bank transactions, reconciliation and financial periods</p>
        </div>
        <div>
            <a href="<?php echo BASE_URL; ?>/admin/banking/reconciliation" class="btn btn-warning me-2">
                <i class="fas fa-balance-scale me-1"></i>Reconciliation
            </a>
            <a href="<?php echo BASE_URL; ?>/admin/banking/financial-years" class="btn btn-outline-primary">
                <i class="fas fa-calendar-alt me-1"></i>Financial Years
            </a>
        </div>
    </div>

    <?php require __DIR__ . '/../partials/search_bar.php'; ?>
    <?php require __DIR__ . '/../partials/export_buttons.php'; ?>

    <!-- Stats -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-success bg-opacity-10 text-success rounded p-3">
                                <i class="fas fa-arrow-up fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Total Credits</h6>
                            <h3 class="mb-0">₹<?php echo number_format($stats['total_credits'] ?? 0, 2); ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-danger bg-opacity-10 text-danger rounded p-3">
                                <i class="fas fa-arrow-down fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Total Debits</h6>
                            <h3 class="mb-0">₹<?php echo number_format($stats['total_debits'] ?? 0, 2); ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-warning bg-opacity-10 text-warning rounded p-3">
                                <i class="fas fa-hourglass-half fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Pending Recon</h6>
                            <h3 class="mb-0"><?php echo $stats['pending_reconciliation'] ?? 0; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-info bg-opacity-10 text-info rounded p-3">
                                <i class="fas fa-check-double fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Reconciled</h6>
                            <h3 class="mb-0"><?php echo $stats['reconciled_count'] ?? 0; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <form method="GET" action="<?php echo BASE_URL; ?>/admin/banking" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Financial Year</label>
                    <select class="form-select" name="financial_year">
                        <option value="">All Years</option>
                        <?php foreach ($financialYears as $fy): ?>
                            <option value="<?php echo htmlspecialchars($fy['year'] ?? $fy); ?>" <?php echo (isset($_GET['financial_year']) && $_GET['financial_year'] === ($fy['year'] ?? $fy)) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($fy['year'] ?? $fy); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Period</label>
                    <select class="form-select" name="financial_period">
                        <option value="">All</option>
                        <option value="Q1" <?php echo (isset($_GET['financial_period']) && $_GET['financial_period'] === 'Q1') ? 'selected' : ''; ?>>Q1</option>
                        <option value="Q2" <?php echo (isset($_GET['financial_period']) && $_GET['financial_period'] === 'Q2') ? 'selected' : ''; ?>>Q2</option>
                        <option value="Q3" <?php echo (isset($_GET['financial_period']) && $_GET['financial_period'] === 'Q3') ? 'selected' : ''; ?>>Q3</option>
                        <option value="Q4" <?php echo (isset($_GET['financial_period']) && $_GET['financial_period'] === 'Q4') ? 'selected' : ''; ?>>Q4</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Recon Status</label>
                    <select class="form-select" name="reconciliation_status">
                        <option value="">All</option>
                        <option value="pending" <?php echo (isset($_GET['reconciliation_status']) && $_GET['reconciliation_status'] === 'pending') ? 'selected' : ''; ?>>Pending</option>
                        <option value="cleared" <?php echo (isset($_GET['reconciliation_status']) && $_GET['reconciliation_status'] === 'cleared') ? 'selected' : ''; ?>>Cleared</option>
                        <option value="bounced" <?php echo (isset($_GET['reconciliation_status']) && $_GET['reconciliation_status'] === 'bounced') ? 'selected' : ''; ?>>Bounced</option>
                        <option value="reconciled" <?php echo (isset($_GET['reconciliation_status']) && $_GET['reconciliation_status'] === 'reconciled') ? 'selected' : ''; ?>>Reconciled</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Bank</label>
                    <select class="form-select" name="bank_name">
                        <option value="">All Banks</option>
                        <?php foreach ($banks as $b): ?>
                            <option value="<?php echo htmlspecialchars($b['bank_name'] ?? $b); ?>" <?php echo (isset($_GET['bank_name']) && $_GET['bank_name'] === ($b['bank_name'] ?? $b)) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($b['bank_name'] ?? $b); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Date Range</label>
                    <div class="d-flex gap-2">
                        <input type="date" class="form-control" name="date_from" value="<?php echo htmlspecialchars($_GET['date_from'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="From">
                        <input type="date" class="form-control" name="date_to" value="<?php echo htmlspecialchars($_GET['date_to'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="To">
                    </div>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary me-2"><i class="fas fa-filter me-1"></i>Filter</button>
                    <a href="<?php echo BASE_URL; ?>/admin/banking" class="btn btn-outline-secondary">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0"><i class="fas fa-exchange-alt me-2"></i>Bank Transactions</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Bank Name</th>
                            <th>Account</th>
                            <th>Cheque No</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($transactions)): ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted py-5">
                                    <i class="fas fa-university fa-3x text-muted mb-3"></i>
                                    <h5>No Bank Transactions</h5>
                                    <p class="mb-3">Transactions with banking details will appear here.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($transactions as $t): ?>
                                <tr>
                                    <td><?php echo isset($t['date']) ? date('d M Y', strtotime($t['date'])) : '-'; ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo ($t['type'] ?? '') === 'credit' ? 'success' : 'danger'; ?>">
                                            <?php echo ucfirst($t['type'] ?? '-'); ?>
                                        </span>
                                    </td>
                                    <td class="fw-bold">₹<?php echo number_format($t['amount'] ?? 0, 2); ?></td>
                                    <td><?php echo htmlspecialchars($t['bank_name'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars(substr($t['account_number'] ?? '', -4) ? '****' . substr($t['account_number'] ?? '', -4) : '-'); ?></td>
                                    <td><?php echo htmlspecialchars($t['cheque_number'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars(mb_substr($t['description'] ?? '', 0, 50)); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo match($t['reconciliation_status'] ?? 'pending') {
                                            'reconciled' => 'success',
                                            'cleared' => 'info',
                                            'bounced' => 'danger',
                                            default => 'warning'
                                        }; ?>">
                                            <?php echo ucfirst($t['reconciliation_status'] ?? 'pending'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="<?php echo BASE_URL; ?>/admin/banking/show/<?php echo $t['id']; ?>" class="btn btn-sm btn-outline-info" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if (($t['reconciliation_status'] ?? '') !== 'reconciled'): ?>
                                            <a href="<?php echo BASE_URL; ?>/admin/banking/reconcile/<?php echo $t['id']; ?>" class="btn btn-sm btn-outline-success" title="Reconcile">
                                                <i class="fas fa-check-double"></i>
                                            </a>
                                        <?php endif; ?>
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
