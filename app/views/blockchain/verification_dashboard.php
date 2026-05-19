<?php $pageTitle = $page_title ?? 'Property Verification Dashboard'; ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($pageTitle) ?></h4>
        <a href="<?= ($base ?? BASE_URL) ?>blockchain/smart-contract" class="btn btn-outline-primary btn-sm"><i class="fas fa-file-contract me-1"></i>Smart Contract</a>
    </div>
    <?php $bs = $blockchain_stats ?? []; ?>
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="fs-1 text-primary mb-2"><i class="fas fa-link"></i></div>
                    <h5 class="mb-1"><?= number_format($bs['total_verifications'] ?? 0) ?></h5>
                    <small class="text-muted">Total Verifications</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="fs-1 text-success mb-2"><i class="fas fa-check-circle"></i></div>
                    <h5 class="mb-1"><?= number_format($bs['verified_properties'] ?? 0) ?></h5>
                    <small class="text-muted">Verified</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="fs-1 text-warning mb-2"><i class="fas fa-clock"></i></div>
                    <h5 class="mb-1"><?= number_format($bs['pending_verifications'] ?? 0) ?></h5>
                    <small class="text-muted">Pending</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="fs-1 text-danger mb-2"><i class="fas fa-times-circle"></i></div>
                    <h5 class="mb-1"><?= number_format($bs['failed_verifications'] ?? 0) ?></h5>
                    <small class="text-muted">Failed</small>
                </div>
            </div>
        </div>
    </div>
    <div class="row g-3">
        <div class="col-md-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent"><h5 class="mb-0"><i class="fas fa-home me-2"></i>Your Properties</h5></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <div class="table-responsive"><table class="table table-hover mb-0 table-responsive">
                            <thead class="table-light"><tr><th>Property</th><th>City</th><th>Status</th><th>Action</th></tr></thead>
                            <tbody>
                                <?php if (!empty($user_properties)): ?>
                                    <?php foreach ($user_properties as $p): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($p['title'] ?? '-') ?></td>
                                            <td><?= htmlspecialchars($p['city'] ?? '-') ?></td>
                                            <td><span class="badge bg-<?= ($p['blockchain_status'] ?? 'unverified') === 'verified' ? 'success' : (($p['blockchain_status'] ?? 'unverified') === 'pending' ? 'warning' : 'secondary') ?>"><?= ucfirst($p['blockchain_status'] ?? 'unverified') ?></span></td>
                                            <td><a href="<?= ($base ?? BASE_URL) ?>blockchain/verify/<?= $p['id'] ?? 0 ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-shield-alt"></i> Verify</a></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="4" class="text-center text-muted py-3">No properties found</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent"><h5 class="mb-0"><i class="fas fa-hourglass-half me-2"></i>Verification Requests</h5></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <div class="table-responsive"><table class="table table-hover mb-0 table-responsive">
                            <thead class="table-light"><tr><th>Property</th><th>Requested By</th><th>Date</th></tr></thead>
                            <tbody>
                                <?php if (!empty($verification_requests)): ?>
                                    <?php foreach ($verification_requests as $r): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($r['property_title'] ?? '-') ?></td>
                                            <td><?= htmlspecialchars($r['requested_by_name'] ?? '-') ?></td>
                                            <td><?= htmlspecialchars($r['requested_date'] ?? '-') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="3" class="text-center text-muted py-3">No pending requests</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
