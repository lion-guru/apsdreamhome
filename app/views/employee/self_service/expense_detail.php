<?php
/** Employee Self-Service - Expense Claim Detail */

$page_title = $page_title ?? 'Claim: ' . ($claim['claim_number'] ?? 'Unknown');
$claim = $claim ?? [];
?>

<style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
.item-row { border: 1px solid #e2e8f0; border-radius: 8px; padding: 1rem; margin-bottom: 0.75rem; background: #fafafa; }
.receipt-thumb { max-width: 80px; max-height: 80px; object-fit: cover; border-radius: 4px; border: 1px solid #e2e8f0; }
.status-badge { font-size: 0.75rem; padding: 0.35rem 0.75rem; border-radius: 999px; }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold"><i class="fas fa-receipt me-2 text-primary"></i><?= htmlspecialchars($claim['claim_number'] ?? 'Unknown') ?></h4>
            <p class="text-muted mb-0 small"><?= htmlspecialchars($claim['category_name'] ?? '') ?></p>
        </div>
        <a href="<?= BASE_URL ?>/employee/self-service/expenses" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>

    <!-- Status & Summary -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card stat-card border-0 shadow-sm bg-<?= 
                $claim['status'] === 'paid' ? 'primary' : 
                ($claim['status'] === 'approved' ? 'success' : 
                ($claim['status'] === 'rejected' ? 'danger' : 'warning')) ?> text-white">
                <div class="card-body text-center">
                    <div class="fw-bold fs-4"><?= ucfirst($claim['status']) ?></div>
                    <div class="text-white-50 small">Status</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card border-0 shadow-sm bg-primary text-white">
                <div class="card-body text-center">
                    <div class="fw-bold fs-4">₹<?= number_format($claim['total_amount'] ?? 0, 2) ?></div>
                    <div class="text-white-50 small">Total Amount</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card border-0 shadow-sm bg-info text-white">
                <div class="card-body text-center">
                    <div class="fw-bold fs-4">₹<?= number_format($claim['gst_amount'] ?? 0, 2) ?></div>
                    <div class="text-white-50 small">GST Amount</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card border-0 shadow-sm bg-secondary text-white">
                <div class="card-body text-center">
                    <div class="fw-bold fs-4"><?= count($claim['items'] ?? []) ?></div>
                    <div class="text-white-50 small">Items</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Claim Details -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0 fw-semibold"><i class="fas fa-info-circle me-2"></i>Claim Details</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="detail-row">
                        <div class="small text-muted">Claim Number</div>
                        <div class="fw-semibold"><?= htmlspecialchars($claim['claim_number'] ?? '—') ?></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="detail-row">
                        <div class="small text-muted">Category</div>
                        <div class="fw-semibold"><?= htmlspecialchars($claim['category_name'] ?? '—') ?></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="detail-row">
                        <div class="small text-muted">Claim Date</div>
                        <div class="fw-semibold"><?= $claim['claim_date'] ? date('d M Y', strtotime($claim['claim_date'])) : '—' ?></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="detail-row">
                        <div class="small text-muted">Submitted</div>
                        <div class="fw-semibold"><?= $claim['submitted_at'] ? date('d M Y H:i', strtotime($claim['submitted_at'])) : ($claim['created_at'] ? date('d M Y H:i', strtotime($claim['created_at'])) : '—') ?></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="detail-row">
                        <div class="small text-muted">Status</div>
                        <div class="fw-semibold">
                            <span class="badge bg-<?= 
                                $claim['status'] === 'paid' ? 'primary' : 
                                ($claim['status'] === 'approved' ? 'success' : 
                                ($claim['status'] === 'rejected' ? 'danger' : 'warning')) ?>">
                                <?= ucfirst($claim['status']) ?>
                            </span>
                        </div>
                    </div>
                </div>
                <?php if (!empty($claim['description'])): ?>
                <div class="col-12">
                    <div class="detail-row">
                        <div class="small text-muted">Description</div>
                        <div class="fw-semibold"><?= htmlspecialchars($claim['description']) ?></div>
                    </div>
                </div>
                <?php endif; ?>
                <?php if (!empty($claim['rejection_reason'])): ?>
                <div class="col-12">
                    <div class="detail-row">
                        <div class="small text-muted text-danger">Rejection Reason</div>
                        <div class="text-danger"><?= htmlspecialchars($claim['rejection_reason']) ?></div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Expense Items -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-semibold"><i class="fas fa-list me-2"></i>Expense Items</h5>
            <span class="badge bg-primary"><?= count($claim['items'] ?? []) ?> items</span>
        </div>
        <div class="card-body p-0">
            <?php if (empty($claim['items'])): ?>
            <div class="text-center py-4 text-muted">No items</div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 120px">Date</th>
                            <th>Description</th>
                            <th style="width: 120px">Amount</th>
                            <th style="width: 120px">GST</th>
                            <th style="width: 100px">GST %</th>
                            <th style="width: 120px">HSN/SAC</th>
                            <th style="width: 100px">Receipt</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($claim['items'] as $item): ?>
                        <tr>
                            <td><?= $item['expense_date'] ? date('d M Y', strtotime($item['expense_date'])) : '—' ?></td>
                            <td><?= htmlspecialchars($item['description']) ?></td>
                            <td class="fw-semibold">₹<?= number_format($item['amount'] ?? 0, 2) ?></td>
                            <td>₹<?= number_format($item['gst_amount'] ?? 0, 2) ?></td>
                            <td><?= $item['gst_rate'] ? $item['gst_rate'] . '%' : '—' ?></td>
                            <td><?= htmlspecialchars($item['hsn_sac_code'] ?? '—') ?></td>
                            <td>
                                <?php if (!empty($item['receipt_path'])): ?>
                                    <a href="<?= BASE_URL . '/' . $item['receipt_path'] ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-file-alt me-1"></i> View
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Workflow / Approval History -->
    <?php if (!empty($claim['workflow'])): ?>
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white">
            <h5 class="mb-0 fw-semibold"><i class="fas fa-history me-2"></i>Approval History</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 80px">Level</th>
                            <th>Approver</th>
                            <th style="width: 150px">Role</th>
                            <th style="width: 150px">Status</th>
                            <th style="width: 180px">Action Time</th>
                            <th>Comments</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($claim['workflow'] as $wf): 
                            $statusClass = [
                                'pending' => 'warning',
                                'approved' => 'success',
                                'rejected' => 'danger',
                                'delegated' => 'info',
                            ][$wf['status']] ?? 'secondary';
                        ?>
                        <tr>
                            <td><?= $wf['level'] ?></td>
                            <td><?= htmlspecialchars($wf['approver_name'] ?? '—') ?></td>
                            <td><?= htmlspecialchars($wf['approver_role'] ?? '—') ?></td>
                            <td><span class="badge bg-<?= $statusClass ?>"><?= ucfirst($wf['status']) ?></span></td>
                            <td><?= $wf['action_at'] ? date('d M Y H:i', strtotime($wf['action_at'])) : '—' ?></td>
                            <td><?= htmlspecialchars($wf['comments'] ?? '—') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>