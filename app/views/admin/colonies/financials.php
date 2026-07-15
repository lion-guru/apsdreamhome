<?php
/**
 * Colony Financial Summary View
 * Data: $colony, $total_bookings, $total_plots_value
 */
$page_title = $page_title ?? 'Financial Summary';
$colony = $colony ?? [];
$total_bookings = $total_bookings ?? 0;
$total_plots_value = $total_plots_value ?? 0;
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Financial Summary - <?= htmlspecialchars($colony['name'] ?? 'Colony') ?></h4>
        <a href="<?= BASE_URL ?>/admin/colonies" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i> Back to Colonies</a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card aps-cp-card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <i">
                        <div class="flex-shrink-0 me-3"><span class="badge bg-primary rounded-pill p-2"><i class="fas fa-rupee-sign"></i></span></div>
                        <div>
                            <div class="aps-cp-stat-label">Total Booking Value</div>
                            <div class="aps-cp-stat-value">₹<?= number_format($total_bookings, 2) ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card aps-cp-card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3"><span class="badge bg-success rounded-pill p-2"><i class="fas fa-map-marked-alt"></i></span></div>
                        <div>
                            <div class="aps-cp-stat-label">Total Plots Value</div>
                            <div class="aps-cp-stat-value">₹<?= number_format($total_plots_value, 2) ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card aps-cp-card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3"><span class="badge bg-info rounded-pill p-2"><i class="fas fa-chart-line"></i></span></div>
                        <div>
                            <div class="aps-cp-stat-label">Pipeline Value</div>
                            <div class="aps-cp-stat-value">₹<?= number_format($total_bookings + $total_plots_value, 2) ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card aps-cp-card">
        <div class="card-header aps-cp-card-header">
            <i class="fas fa-table me-2"></i>Colony Details
        </div>
        <div class="card-body aps-cp-card-body">
            <table class="table table-sm table-hover mb-0">
                <tbody>
                    <tr><td width="30%">Colony ID</td><td><?= htmlspecialchars($colony['id'] ?? '') ?></td></tr>
                    <tr><td>Name</td><td><?= htmlspecialchars($colony['name'] ?? '') ?></td></tr>
                    <tr><td>Status</td><td><span class="aps-cp-badge badge bg-<?= ($colony['status'] ?? '') === 'active' ? 'success' : 'secondary' ?>"><?= htmlspecialchars(ucfirst($colony['status'] ?? 'Unknown')) ?></span></td></tr>
                    <tr><td>District</td><td><?= htmlspecialchars($colony['district_name'] ?? '') ?></td></tr>
                    <tr><td>State</td><td><?= htmlspecialchars($colony['state_name'] ?? '') ?></td></tr>
                    <tr><td>Created</td><td><?= $colony['created_at'] ? date('d M Y', strtotime($colony['created_at'])) : '—' ?></td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>