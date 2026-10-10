<?php
/** @var string $farmer_name */
/** @var string $village */
/** @var array $leads */
/** @var array $deals */
/** @var array $payments */
$farmer_name = $farmer_name ?? '';
$village     = $village ?? '';
$leads       = $leads ?? [];
$deals       = $deals ?? [];
$payments    = $payments ?? [];
$base        = defined('BASE_URL') ? BASE_URL : '';
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-1"><i class="fas fa-user-tie me-2"></i><?= htmlspecialchars($farmer_name) ?></h4>
        <?php if ($village): ?>
            <p class="text-muted mb-0"><i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($village) ?></p>
        <?php endif; ?>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= $base ?>/admin/land-inventory/farmer-ledger" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back to Ledger
        </a>
        <button class="btn btn-primary" onclick="window.print()">
            <i class="fas fa-print me-1"></i> Print Statement
        </button>
    </div>
</div>

<!-- Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="aps-cp-card text-center">
            <div class="aps-cp-card-body py-3">
                <div class="text-info fw-bold fs-3"><?= count($leads) ?></div>
                <div class="text-muted small">Total Leads</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="aps-cp-card text-center">
            <div class="aps-cp-card-body py-3">
                <div class="text-primary fw-bold fs-3"><?= count($deals) ?></div>
                <div class="text-muted small">Closed Deals</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="aps-cp-card text-center">
            <div class="aps-cp-card-body py-3">
                <div class="text-success fw-bold fs-3">
                    ₹<?= number_format(array_sum(array_column($deals, 'total_consideration')), 0) ?>
                </div>
                <div class="text-muted small">Total Deal Value</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="aps-cp-card text-center">
            <div class="aps-cp-card-body py-3">
                <div class="text-warning fw-bold fs-3">
                    ₹<?= number_format(array_sum(array_column($deals, 'advance_paid')), 0) ?>
                </div>
                <div class="text-muted small">Total Advance</div>
            </div>
        </div>
    </div>
</div>

<!-- Leads Section -->
<div class="aps-cp-card mb-4">
    <div class="aps-cp-card-header">
        <span><i class="fas fa-sitemap me-2"></i>Land Leads Pipeline</span>
        <span class="badge bg-info"><?= count($leads) ?></span>
    </div>
    <div class="aps-cp-card-body p-0">
        <?php if (empty($leads)): ?>
        <div class="text-center py-4 text-muted">No leads found</div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Lead ID</th>
                        <th>Source</th>
                        <th>Location</th>
                        <th>Area</th>
                        <th>Expected Price</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($leads as $lead): 
                        $leadDeals = array_filter($deals, fn($d) => $d['land_lead_id'] == $lead['id']);
                        $statusColors = [
                            'new' => 'secondary', 'screening' => 'info', 'visit_done' => 'primary',
                            'dd' => 'warning', 'negotiation' => 'orange', 'legal' => 'purple',
                            'sale_agreement' => 'info', 'registered' => 'success',
                            'rejected' => 'danger', 'dropped' => 'dark'
                        ];
                        $statusColor = $statusColors[$lead['status']] ?? 'secondary';
                    ?>
                    <tr>
                        <td>#<?= $lead['id'] ?></td>
                        <td>
                            <span class="badge bg-<?= $lead['lead_source'] === 'broker' ? 'primary' : ($lead['lead_source'] === 'referral' ? 'success' : 'secondary') ?>">
                                <?= ucfirst($lead['lead_source'] ?? '') ?>
                            </span>
                        </td>
                        <td>
                            <div class="small fw-medium"><?= htmlspecialchars($lead['village'] ?? '') ?></div>
                            <small class="text-muted"><?= htmlspecialchars(implode(', ', array_filter([$lead['tehsil'] ?? '', $lead['district'] ?? '', $lead['state'] ?? '']))) ?></small>
                        </td>
                        <td>
                            <div class="small"><?= number_format($lead['area_acres'] ?? 0, 2) ?> acres</div>
                            <?php if (($lead['area_sqft'] ?? 0) > 0): ?>
                                <small class="text-muted"><?= number_format($lead['area_sqft'] ?? 0, 0) ?> sqft</small>
                            <?php endif; ?>
                        </td>
                        <td>₹<?= number_format($lead['expected_price'] ?? 0, 0) ?></td>
                        <td>
                            <span class="badge bg-<?= $statusColor ?>"><?= ucfirst(str_replace('_', ' ', $lead['status'] ?? '')) ?></span>
                        </td>
                        <td><?= date('d M Y', strtotime($lead['created_at'])) ?></td>
                        <td>
                            <a href="<?= $base ?>/admin/land-inventory/leads/<?= $lead['id'] ?>" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Deals Section -->
<div class="aps-cp-card mb-4">
    <div class="aps-cp-card-header">
        <span><i class="fas fa-handshake me-2"></i>Land Deals / Acquisitions</span>
        <span class="badge bg-primary"><?= count($deals) ?></span>
    </div>
    <div class="aps-cp-card-body p-0">
        <?php if (empty($deals)): ?>
        <div class="text-center py-4 text-muted">No deals found</div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Deal ID</th>
                        <th>Lead</th>
                        <th>Colony</th>
                        <th>Area</th>
                        <th>Consideration</th>
                        <th>Advance</th>
                        <th>Balance</th>
                        <th>Agreement</th>
                        <th>Registration</th>
                        <th>Mutation</th>
                        <th>Status</th>
                        <th>Payments</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($deals as $deal): 
                        $dealPayments = $payments[$deal['id']] ?? [];
                        $totalPaid = array_sum(array_column($dealPayments, 'amount'));
                        $statusColors = [
                            'in_progress' => 'warning', 'registered' => 'success',
                            'mutated' => 'primary', 'closed' => 'success', 'cancelled' => 'danger'
                        ];
                        $statusColor = $statusColors[$deal['status']] ?? 'secondary';
                    ?>
                    <tr>
                        <td>#<?= $deal['id'] ?></td>
                        <td>
                            <a href="<?= $base ?>/admin/land-inventory/leads/<?= $deal['land_lead_id'] ?>">
                                Lead #<?= $deal['land_lead_id'] ?>
                            </a>
                        </td>
                        <td><?= htmlspecialchars($deal['colony_id'] ? $deal['colony_id'] : '—') ?></td>
                        <td><?= number_format($deal['total_area_sqft'] ?? 0, 0) ?> sqft</td>
                        <td class="fw-semibold text-success">₹<?= number_format($deal['total_consideration'] ?? 0, 0) ?></td>
                        <td class="text-primary">₹<?= number_format($deal['advance_paid'] ?? 0, 0) ?></td>
                        <td class="<?= ($deal['balance_amount'] ?? 0) > 0 ? 'text-danger' : 'text-success' ?>">
                            ₹<?= number_format($deal['balance_amount'] ?? 0, 0) ?>
                        </td>
                        <td>
                            <?php if (!empty($deal['sale_agreement_date'])): ?>
                                <div class="small text-success">
                                    <i class="fas fa-file-contract me-1"></i><?= date('d M Y', strtotime($deal['sale_agreement_date'])) ?>
                                </div>
                                <small class="text-muted"><?= htmlspecialchars($deal['sale_agreement_number'] ?? '') ?></small>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($deal['registration_date'])): ?>
                                <div class="small text-success">
                                    <i class="fas fa-file-signature me-1"></i><?= date('d M Y', strtotime($deal['registration_date'])) ?>
                                </div>
                                <small class="text-muted"><?= htmlspecialchars($deal['registration_number'] ?? '') ?></small>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge bg-<?= 
                                $deal['mutation_status'] === 'completed' ? 'success' : 
                                ($deal['mutation_status'] === 'in_progress' ? 'warning' : 
                                ($deal['mutation_status'] === 'applied' ? 'info' : 'secondary')) ?>">
                                <?= ucfirst(str_replace('_', ' ', $deal['mutation_status'] ?? 'not_started')) ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-<?= $statusColor ?>"><?= ucfirst($deal['status'] ?? '') ?></span>
                        </td>
                        <td>
                            <div class="d-flex flex-wrap gap-1">
                                <?php foreach ($dealPayments as $p): ?>
                                    <span class="badge bg-light text-dark border" title="<?= htmlspecialchars($p['payment_mode'] ?? '') ?>">
                                        ₹<?= number_format($p['amount'] ?? 0, 0) ?>
                                    </span>
                                <?php endforeach; ?>
                                <?php if (empty($dealPayments)): ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </div>
                            <?php if ($dealPayments): ?>
                                <div class="small text-muted mt-1">Total: ₹<?= number_format($totalPaid, 0) ?></div>
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

<!-- Payment History Section -->
<?php 
$allPayments = [];
foreach ($payments as $dealId => $dealPayments) {
    foreach ($dealPayments as $p) {
        $p['deal_id'] = $dealId;
        $allPayments[] = $p;
    }
}
if (!empty($allPayments)):
    // Sort by date descending
    usort($allPayments, fn($a, $b) => strtotime($b['payment_date'] ?? 0) - strtotime($a['payment_date'] ?? 0));
?>
<div class="aps-cp-card">
    <div class="aps-cp-card-header">
        <span><i class="fas fa-history me-2"></i>Complete Payment History</span>
        <span class="badge bg-secondary"><?= count($allPayments) ?> Records</span>
    </div>
    <div class="aps-cp-card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Deal</th>
                        <th>Amount</th>
                        <th>Mode</th>
                        <th>Reference</th>
                        <th>Notes</th>
                        <th>Recorded By</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($allPayments as $p): 
                        $modeColors = [
                            'cash' => 'success', 'cheque' => 'primary', 'online' => 'info',
                            'bank_transfer' => 'primary', 'upi' => 'warning', 'dd' => 'secondary'
                        ];
                        $modeColor = $modeColors[$p['payment_mode'] ?? ''] ?? 'secondary';
                    ?>
                    <tr>
                        <td><?= date('d M Y', strtotime($p['payment_date'] ?? '')) ?></td>
                        <td>
                            <a href="<?= $base ?>/admin/land-inventory/acquisitions/<?= $p['deal_id'] ?>">
                                #<?= $p['deal_id'] ?>
                            </a>
                        </td>
                        <td class="fw-semibold">₹<?= number_format($p['amount'] ?? 0, 0) ?></td>
                        <td>
                            <span class="badge bg-<?= $modeColor ?>">
                                <?= ucfirst(str_replace('_', ' ', $p['payment_mode'] ?? '')) ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($p['reference_number'] ?? $p['cheque_number'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($p['notes'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($p['recorded_by_name'] ?? '—') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<style>
@media print {
    .btn, .badge { border: 1px solid #000 !important; }
    .aps-cp-card { box-shadow: none !important; border: 1px solid #dee2e6 !important; }
}
</style>