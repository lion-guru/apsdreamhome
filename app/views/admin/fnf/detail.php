<?php
/**
 * F&F Settlement Detail View
 * Variables: $settlement (row with employee_name, email, phone, employee_code, designation, department, joining_date), $components (decoded settlement_details array)
 */

$page_title = $page_title ?? ('Settlement: ' . ($settlement['settlement_no'] ?? ''));
$settlement = $settlement ?? [];
$components = is_array($components) ? $components : [];
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-file-invoice-dollar me-2"></i><?= htmlspecialchars($page_title) ?></h1>
        <a href="<?= BASE_URL ?>/admin/fnf/history" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to History
        </a>
    </div>

    <!-- Status + Actions -->
    <div class="card mb-4">
        <div class="card-body d-flex flex-wrap gap-2 align-items-center justify-content-between">
            <div>
                <?php $st = $settlement['status'] ?? 'calculated'; ?>
                <span class="badge fs-6 bg-<?=
                    $st === 'paid' ? 'success' :
                    ($st === 'approved' ? 'info' :
                    ($st === 'calculated' ? 'warning' : 'secondary')) ?>">
                    <?= ucfirst($st) ?>
                </span>
                <span class="text-muted ms-2">Exit: <?= ucfirst(htmlspecialchars($settlement['exit_type'] ?? '-')) ?></span>
            </div>
            <div class="d-flex gap-2">
                <?php if ($st === 'calculated'): ?>
                <form method="POST" action="<?= BASE_URL ?>/admin/fnf/approve/<?= (int)$settlement['id'] ?>" class="d-inline" onsubmit="return confirm('Approve this settlement?');">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    <button type="submit" class="btn btn-success"><i class="fas fa-check me-1"></i> Approve</button>
                </form>
                <?php endif; ?>
                <?php if ($st === 'approved'): ?>
                <form method="POST" action="<?= BASE_URL ?>/admin/fnf/mark-paid/<?= (int)$settlement['id'] ?>" class="d-inline" onsubmit="return confirm('Mark as paid?');">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="hidden" name="payment_reference" value="PAID-<?= htmlspecialchars($settlement['settlement_no'] ?? '') ?>">
                    <button type="submit" class="btn btn-success"><i class="fas fa-money-bill-wave me-1"></i> Mark Paid</button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Employee + Employment Summary -->
    <div class="card mb-4">
        <div class="card-header bg-light"><h5 class="mb-0"><i class="fas fa-user me-2"></i>Employee &amp; Employment</h5></div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3"><strong>Name:</strong> <?= htmlspecialchars($settlement['employee_name'] ?? '-') ?></div>
                <div class="col-md-3"><strong>Code:</strong> <?= htmlspecialchars($settlement['employee_code'] ?? '-') ?></div>
                <div class="col-md-3"><strong>Designation:</strong> <?= htmlspecialchars($settlement['designation'] ?? '-') ?></div>
                <div class="col-md-3"><strong>Department:</strong> <?= htmlspecialchars($settlement['department'] ?? '-') ?></div>
                <div class="col-md-3"><strong>Email:</strong> <?= htmlspecialchars($settlement['email'] ?? '-') ?></div>
                <div class="col-md-3"><strong>Phone:</strong> <?= htmlspecialchars($settlement['phone'] ?? '-') ?></div>
                <div class="col-md-3"><strong>Joining:</strong> <?= !empty($settlement['joining_date']) ? date('d M Y', strtotime($settlement['joining_date'])) : '-' ?></div>
                <div class="col-md-3"><strong>Resigned:</strong> <?= !empty($settlement['resignation_date']) ? date('d M Y', strtotime($settlement['resignation_date'])) : '-' ?></div>
                <div class="col-md-3"><strong>Last Working Day:</strong> <?= !empty($settlement['last_working_day']) ? date('d M Y', strtotime($settlement['last_working_day'])) : '-' ?></div>
                <div class="col-md-3"><strong>Notice:</strong> <?= (int)($settlement['notice_served_days'] ?? 0) ?>/<?= (int)($settlement['notice_period_days'] ?? 0) ?> days served</div>
            </div>
        </div>
    </div>

    <!-- Money Summary -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-success text-white"><div class="card-body text-center">
                <h3>₹<?= number_format($settlement['earnings_total'] ?? 0, 2) ?></h3><small>Total Earnings</small>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="card bg-danger text-white"><div class="card-body text-center">
                <h3>₹<?= number_format($settlement['deductions_total'] ?? 0, 2) ?></h3><small>Total Deductions</small>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="card bg-primary text-white"><div class="card-body text-center">
                <h3>₹<?= number_format($settlement['net_payable'] ?? 0, 2) ?></h3><small>Net Payable</small>
            </div></div>
        </div>
    </div>

    <!-- Component Breakdown -->
    <div class="card mb-4">
        <div class="card-header bg-light"><h5 class="mb-0"><i class="fas fa-list-alt me-2"></i>Component Breakdown</h5></div>
        <div class="card-body p-0">
            <?php if (empty($components)): ?>
            <div class="text-center py-4 text-muted">No component details stored for this settlement.</div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light"><tr><th>Component</th><th>Description</th><th class="text-end">Amount (₹)</th></tr></thead>
                    <tbody>
                        <?php foreach ($components as $key => $comp): ?>
                        <?php
                            $label = is_array($comp) ? ($comp['label'] ?? $comp['description'] ?? $key) : $key;
                            $desc = is_array($comp) ? ($comp['description'] ?? '') : (string)$comp;
                            $amount = is_array($comp) ? (float)($comp['amount'] ?? 0) : (float)$comp;
                        ?>
                        <tr>
                            <td><strong><?= htmlspecialchars(ucwords(str_replace('_', ' ', (string)$key))) ?></strong><br><small class="text-muted"><?= htmlspecialchars((string)$label) ?></small></td>
                            <td><small><?= htmlspecialchars((string)$desc) ?></small></td>
                            <td class="text-end fw-bold <?= $amount < 0 ? 'text-danger' : 'text-success' ?>">₹<?= number_format($amount, 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Audit -->
    <div class="card">
        <div class="card-body">
            <small class="text-muted">
                Created: <?= !empty($settlement['created_at']) ? date('d M Y H:i', strtotime($settlement['created_at'])) : '-' ?> |
                Approved: <?= !empty($settlement['approved_at']) ? date('d M Y H:i', strtotime($settlement['approved_at'])) : '-' ?> |
                Paid: <?= !empty($settlement['paid_at']) ? date('d M Y H:i', strtotime($settlement['paid_at'])) : '-' ?>
                <?= !empty($settlement['payment_reference']) ? '| Ref: ' . htmlspecialchars($settlement['payment_reference']) : '' ?>
            </small>
        </div>
    </div>
</div>

<style>
.card { border-radius: 0.75rem; box-shadow: 0 2px 10px rgba(0,0,0,0.08); }
.table th { border-top: none; font-weight: 600; color: #495057; }
</style>
