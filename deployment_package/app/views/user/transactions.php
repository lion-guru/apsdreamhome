<?php
/**
 * User Transactions View - APS Dream Home
 */
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 mb-0 text-gray-800 fw-bold">Transaction History</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>dashboard" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item active">Transactions</li>
            </ol>
        </nav>
    </div>

    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Transaction ID</th>
                            <th>Date</th>
                            <th>Description</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($transactions)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="fas fa-history fa-3x mb-3 d-block"></i>
                                    No transactions found.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($transactions as $tx): ?>
                                <tr>
                                    <td class="ps-4 fw-bold text-primary">#<?= $tx['id'] ?></td>
                                    <td><?= date('d M, Y', strtotime($tx['transaction_date'])) ?></td>
                                    <td><?= h($tx['description']) ?></td>
                                    <td class="fw-bold">₹<?= number_format($tx['amount'], 2) ?></td>
                                    <td>
                                        <?php if ($tx['status'] == 'completed'): ?>
                                            <span class="badge bg-success-subtle text-success rounded-pill px-3">Completed</span>
                                        <?php elseif ($tx['status'] == 'pending'): ?>
                                            <span class="badge bg-warning-subtle text-warning rounded-pill px-3">Pending</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger-subtle text-danger rounded-pill px-3">Failed</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-primary rounded-pill px-3">View Details</button>
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
