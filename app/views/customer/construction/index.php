<?php
/**
 * Customer Construction Permits & Architectural Approvals List View
 */
$applications = $applications ?? [];
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1"><i class="fas fa-home text-primary me-2"></i>My Township Construction Approvals</h3>
            <p class="text-muted small mb-0">Track architectural map clearance & excavation permit status for your plots</p>
        </div>
        <div>
            <a href="/customer/construction/apply" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i>Apply for Construction Permit
            </a>
        </div>
    </div>

    <!-- Setback Rules Quick Guide -->
    <div class="card border-0 shadow-sm bg-primary-subtle text-primary-emphasis mb-4 p-3 rounded-3">
        <div class="d-flex align-items-start">
            <i class="fas fa-info-circle fs-4 me-3 mt-1 text-primary"></i>
            <div>
                <h6 class="fw-bold mb-1">Township Building Bylaws Guidelines (Gorakhpur Town Planning)</h6>
                <div class="small">
                    Before starting foundation digging or RCC casting, ensure adherence to mandatory setbacks:
                    <strong>Front Setback: min 10.0 ft</strong> (from road tar border), <strong>Rear Setback: min 5.0 ft</strong>, <strong>Side Setbacks: min 3.0 ft</strong>.
                    Rainwater harvesting (RWH) recharge pit is compulsory for built-up area &ge; 1,000 sq.ft.
                </div>
            </div>
        </div>
    </div>

    <!-- Applications List -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-muted small text-uppercase">
                        <tr>
                            <th class="ps-3">Permit Token</th>
                            <th>Plot & Township</th>
                            <th>Building Type</th>
                            <th>Built-up Area</th>
                            <th>Status</th>
                            <th>Validity</th>
                            <th class="text-end pe-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($applications)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="fas fa-drafting-compass fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                    You haven't submitted any construction approval applications yet.
                                    <div class="mt-3">
                                        <a href="/customer/construction/apply" class="btn btn-sm btn-primary">
                                            Apply for Construction Permit
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($applications as $app): ?>
                                <tr>
                                    <td class="ps-3">
                                        <span class="fw-bold font-monospace text-dark"><?= htmlspecialchars($app['permit_token']) ?></span>
                                        <div class="small text-muted"><?= date('d M Y', strtotime($app['created_at'])) ?></div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">Plot #<?= htmlspecialchars($app['plot_number']) ?></div>
                                        <div class="small text-muted"><?= htmlspecialchars($app['colony_name'] ?? 'APS Township') ?> (<?= number_format((float)$app['area_sqft'], 0) ?> sq.ft)</div>
                                    </td>
                                    <td class="text-capitalize">
                                        <?= str_replace('_', ' ', htmlspecialchars($app['building_type'])) ?>
                                    </td>
                                    <td>
                                        <?= number_format((float)$app['total_builtup_sqft'], 0) ?> sq.ft
                                    </td>
                                    <td>
                                        <?php if ($app['status'] === 'approved'): ?>
                                            <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Approved</span>
                                        <?php elseif ($app['status'] === 'site_inspected'): ?>
                                            <span class="badge bg-info text-dark"><i class="fas fa-ruler me-1"></i>Site Inspected</span>
                                        <?php elseif ($app['status'] === 'rejected'): ?>
                                            <span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i>Rejected</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i>Under Review</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($app['permit_validity_date'])): ?>
                                            <span class="small fw-semibold text-success">Valid till <?= date('d M Y', strtotime($app['permit_validity_date'])) ?></span>
                                        <?php else: ?>
                                            <span class="small text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end pe-3">
                                        <?php if ($app['status'] === 'approved'): ?>
                                            <a href="/customer/construction/permit/<?= $app['id'] ?>" target="_blank" class="btn btn-sm btn-outline-success">
                                                <i class="fas fa-print me-1"></i>Print Permit NOC
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted small">In Process</span>
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
