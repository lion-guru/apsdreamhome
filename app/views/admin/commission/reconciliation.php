<?php
/**
 * Commission Reconciliation Dashboard
 * Shows daily audit results for mlm_commission_ledger (single source of truth).
 */
$reconciliation = $data ?? [];
$summary = $reconciliation['summary'] ?? [];
$health = $summary['health'] ?? 'unknown';
$healthColors = ['healthy' => '#10b981', 'warning' => '#f59e0b', 'critical' => '#ef4444'];
$healthColor = $healthColors[$health] ?? '#6b7280';
$base = defined('BASE_URL') ? BASE_URL : '';
?>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="<?= $base ?>/admin/dashboard">Admin</a></li>
                    <li class="breadcrumb-item"><a href="<?= $base ?>/admin/commission/calculations">Commissions</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Reconciliation</li>
                </ol>
            </nav>
            <h2 class="h3 mb-0 text-gray-800"><i class="fas fa-balance-scale me-2 text-primary"></i> Commission Reconciliation</h2>
        </div>
        <div>
            <span class="badge px-3 py-2 text-white text-uppercase" style="background-color: <?= htmlspecialchars($healthColor) ?>; font-size: 0.85rem;">
                <i class="fas fa-heartbeat me-1"></i> Health: <?= htmlspecialchars(strtoupper($health)) ?>
            </span>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 border-start border-primary border-4">
                <div class="card-body">
                    <div class="text-muted small text-uppercase fw-bold mb-1">Ledger Entries</div>
                    <div class="h3 mb-0 text-gray-800 fw-bold"><?= number_format($reconciliation['ledger_total'] ?? 0) ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 border-start border-danger border-4">
                <div class="card-body">
                    <div class="text-muted small text-uppercase fw-bold mb-1">Critical Issues</div>
                    <div class="h3 mb-0 text-danger fw-bold"><?= number_format($summary['critical_issues'] ?? 0) ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 border-start border-warning border-4">
                <div class="card-body">
                    <div class="text-muted small text-uppercase fw-bold mb-1">Warnings</div>
                    <div class="h3 mb-0 text-warning fw-bold"><?= number_format($summary['warnings'] ?? 0) ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Discrepancy Tables -->
    <?php if (!empty($reconciliation['orphaned_ledger_no_booking'])): ?>
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-danger text-white py-3">
                <h5 class="mb-0"><i class="fas fa-link me-2"></i> Orphaned Ledger Entries (<?= count($reconciliation['orphaned_ledger_no_booking']) ?>)</h5>
            </div>
            <div class="card-body p-0">
                <div class="p-3 bg-light text-muted small">Commission entries referencing bookings that no longer exist in the system.</div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Ledger ID</th>
                                <th>Booking ID</th>
                                <th>User ID</th>
                                <th>Commission Type</th>
                                <th>Amount</th>
                                <th class="pe-3">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reconciliation['orphaned_ledger_no_booking'] as $ol): ?>
                                <tr>
                                    <td class="ps-3"><?= htmlspecialchars((string)($ol['id'] ?? '')) ?></td>
                                    <td><?= htmlspecialchars((string)($ol['booking_id'] ?? '')) ?></td>
                                    <td><?= htmlspecialchars((string)($ol['beneficiary_user_id'] ?? '')) ?></td>
                                    <td><?= htmlspecialchars((string)($ol['commission_type'] ?? '')) ?></td>
                                    <td class="fw-bold">₹<?= number_format((float)($ol['amount'] ?? 0), 2) ?></td>
                                    <td class="pe-3">
                                        <span class="badge <?= ($ol['status'] ?? '') === 'paid' ? 'bg-success' : 'bg-warning text-dark' ?>">
                                            <?= htmlspecialchars((string)($ol['status'] ?? '')) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!empty($reconciliation['missing_beneficiary'])): ?>
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-warning text-dark py-3">
                <h5 class="mb-0"><i class="fas fa-user-slash me-2"></i> Missing Beneficiaries (<?= count($reconciliation['missing_beneficiary']) ?>)</h5>
            </div>
            <div class="card-body">
                <p class="text-muted mb-0">Commission entries pointing to user IDs that no longer exist in the database.</p>
            </div>
        </div>
    <?php endif; ?>

    <?php if (empty($reconciliation['orphaned_ledger_no_booking']) && empty($reconciliation['missing_beneficiary']) && empty($reconciliation['negative_entries'])): ?>
        <div class="card shadow-sm border-0 text-center py-5 mb-4">
            <div class="card-body">
                <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                <h3 class="text-gray-800 fw-bold">All Systems Reconciled!</h3>
                <p class="text-muted mb-0">No discrepancies found across commission ledger, users, and booking tables.</p>
            </div>
        </div>
    <?php endif; ?>

    <div class="text-muted small text-end">
        <i class="fas fa-clock me-1"></i> Last reconciled: <?= htmlspecialchars((string)($reconciliation['timestamp'] ?? 'Never')) ?>
    </div>
</div>
