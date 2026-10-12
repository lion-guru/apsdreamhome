<?php
/**
 * Customer PM Surya Ghar Solar Application Tracking View
 */
$page_title = $page_title ?? 'My Green Solar Applications';
$requests = $requests ?? [];
?>

<div class="container py-4">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h3 class="fw-bold mb-0 text-dark">
                <i class="fas fa-sun text-warning me-2"></i>My Solar & Subsidy Applications
            </h3>
            <p class="text-muted small mb-0">Track your PM Surya Ghar installation progress, UP-NEDA subsidy clearance & PVVNL net-metering status.</p>
        </div>
        <div>
            <a href="/customer/solar-calculator" class="btn btn-warning text-dark fw-bold btn-sm">
                <i class="fas fa-calculator me-1"></i>New Solar Calculation
            </a>
        </div>
    </div>

    <?php if (empty($requests)): ?>
        <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white">
            <i class="fas fa-solar-panel fa-3x mb-3 text-warning opacity-50"></i>
            <h5 class="fw-bold text-dark">No Solar Applications Yet</h5>
            <p class="text-muted mb-4">Calculate feasibility and submit your PM Surya Ghar rooftop solar application for your plot.</p>
            <div>
                <a href="/customer/solar-calculator" class="btn btn-primary px-4">
                    Open Solar Calculator & Apply
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($requests as $req): ?>
                <div class="col-12">
                    <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
                        <div class="card-header bg-light py-3 d-flex flex-wrap justify-content-between align-items-center">
                            <div>
                                <span class="badge bg-dark font-monospace me-2"><?= htmlspecialchars($req['request_token']) ?></span>
                                <span class="text-muted small"><?= date('d M Y, h:i A', strtotime($req['created_at'])) ?></span>
                            </div>
                            <div>
                                <?php
                                $badgeClass = match($req['status']) {
                                    'installed' => 'bg-success',
                                    'discom_sanctioned' => 'bg-primary',
                                    'subsidy_approved' => 'bg-info',
                                    'site_feasibility' => 'bg-warning text-dark',
                                    'rejected' => 'bg-danger',
                                    default => 'bg-secondary'
                                };
                                ?>
                                <span class="badge <?= $badgeClass ?> px-3 py-2 text-uppercase">
                                    <?= ucfirst(str_replace('_', ' ', $req['status'])) ?>
                                </span>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-4 align-items-center">
                                <div class="col-md-3 border-end">
                                    <span class="text-muted small d-block">System Specification</span>
                                    <h4 class="fw-bold text-dark mb-1"><?= number_format($req['system_capacity_kw'], 1) ?> kW Capacity</h4>
                                    <p class="text-muted small mb-0">~<?= number_format($req['estimated_monthly_units']) ?> Units / Month Generation</p>
                                    <?php if (!empty($req['plot_number'])): ?>
                                        <div class="badge bg-light text-dark border mt-2">
                                            Plot <?= htmlspecialchars($req['plot_number']) ?> (<?= htmlspecialchars($req['colony_name']) ?>)
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-3 border-end">
                                    <span class="text-muted small d-block">Cost & Subsidy Breakdown</span>
                                    <div class="small">Cost: <strong>₹<?= number_format($req['estimated_project_cost']) ?></strong></div>
                                    <div class="small text-success">Govt Subsidy: <strong>- ₹<?= number_format($req['central_subsidy_amount'] + $req['state_subsidy_amount']) ?></strong></div>
                                    <div class="fw-bold text-primary mt-1">Net Payable: ₹<?= number_format($req['net_customer_payable']) ?></div>
                                </div>
                                <div class="col-md-3 border-end">
                                    <span class="text-muted small d-block">DISCOM & Net-Metering</span>
                                    <div class="small fw-semibold"><?= htmlspecialchars($req['discom_provider']) ?></div>
                                    <div class="small text-muted">CA No: <?= htmlspecialchars($req['consumer_number'] ?? 'Pending') ?></div>
                                    <?php if (!empty($req['sanction_ref'])): ?>
                                        <div class="badge bg-success bg-opacity-10 text-success border border-success mt-1">
                                            Sanction Ref: <?= htmlspecialchars($req['sanction_ref']) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-3">
                                    <span class="text-muted small d-block">EPC Vendor Partner</span>
                                    <div class="fw-semibold text-dark"><?= htmlspecialchars($req['installer_partner'] ?? 'Assigning Approved Partner...') ?></div>
                                    <?php if (!empty($req['installer_contact'])): ?>
                                        <div class="small text-muted"><i class="fas fa-phone me-1"></i><?= htmlspecialchars($req['installer_contact']) ?></div>
                                    <?php endif; ?>
                                    <?php if (!empty($req['installed_at'])): ?>
                                        <div class="small text-success mt-1"><i class="fas fa-check-double me-1"></i>Commissioned on <?= date('d M Y', strtotime($req['installed_at'])) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
