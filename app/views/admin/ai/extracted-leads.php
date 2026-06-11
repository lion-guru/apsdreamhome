<?php
$page_title = $page_title ?? 'Extracted Leads from Calls';
$extracted = $extracted ?? [];
$totalExtracted = $totalExtracted ?? 0;
$verifiedCount = $verifiedCount ?? 0;
$pendingVerify = $pendingVerify ?? 0;
$hotCount = $hotCount ?? 0;
$convertedCount = $convertedCount ?? 0;
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-user-plus me-2 text-primary"></i>Extracted Leads from Calls</h2>
        <div>
            <a href="<?= BASE_URL ?>/admin/voice-agents/sessions" class="btn btn-outline-primary btn-sm"><i class="fas fa-history me-1"></i>Call Sessions</a>
            <a href="<?= BASE_URL ?>/admin/leads" class="btn btn-primary btn-sm"><i class="fas fa-list me-1"></i>All Leads</a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="aps-cp-card"><div class="aps-cp-card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3"><span class="badge bg-primary rounded-pill p-2"><i class="fas fa-user-plus"></i></span></div>
                    <div><div class="aps-cp-stat-label">Total Extracted</div><div class="aps-cp-stat-value"><?= $totalExtracted ?></div></div>
                </div>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="aps-cp-card"><div class="aps-cp-card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3"><span class="badge bg-success rounded-pill p-2"><i class="fas fa-check-circle"></i></span></div>
                    <div><div class="aps-cp-stat-label">Verified</div><div class="aps-cp-stat-value text-success"><?= $verifiedCount ?></div><div class="aps-cp-stat-meta">Pending: <?= $pendingVerify ?></div></div>
                </div>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="aps-cp-card"><div class="aps-cp-card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3"><span class="badge bg-danger rounded-pill p-2"><i class="fas fa-fire"></i></span></div>
                    <div><div class="aps-cp-stat-label">Hot Leads</div><div class="aps-cp-stat-value text-danger"><?= $hotCount ?></div></div>
                </div>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="aps-cp-card"><div class="aps-cp-card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3"><span class="badge bg-info rounded-pill p-2"><i class="fas fa-link"></i></span></div>
                    <div><div class="aps-cp-stat-label">Converted to Lead</div><div class="aps-cp-stat-value text-info"><?= $convertedCount ?></div></div>
                </div>
            </div></div>
        </div>
    </div>

    <div class="aps-cp-card">
        <div class="aps-cp-card-header"><i class="fas fa-list me-2"></i>Extracted Leads</div>
        <div class="aps-cp-card-body">
            <?php if (empty($extracted)): ?>
                <div class="text-center text-muted py-4"><i class="fas fa-user-plus fa-2x mb-2"></i><p>No leads extracted from calls yet</p></div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>ID</th><th>Name</th><th>Phone</th><th>Email</th><th>Budget</th><th>Location</th><th>Interest</th><th>Timeline</th><th>Verified</th><th>Linked Lead</th><th>Date</th></tr></thead>
                        <tbody>
                        <?php foreach ($extracted as $e): ?>
                            <tr>
                                <td>#<?= $e['id'] ?></td>
                                <td><strong><?= htmlspecialchars($e['extracted_name'] ?? 'N/A') ?></strong></td>
                                <td><code class="small"><?= htmlspecialchars($e['extracted_phone'] ?? '') ?></code></td>
                                <td class="small"><?= htmlspecialchars($e['extracted_email'] ?? '') ?></td>
                                <td class="small"><?= htmlspecialchars($e['extracted_budget'] ?? 'N/A') ?></td>
                                <td class="small"><?= htmlspecialchars($e['extracted_location'] ?? 'N/A') ?></td>
                                <td><span class="aps-cp-badge badge bg-<?= $e['interest_level'] === 'hot' ? 'danger' : ($e['interest_level'] === 'warm' ? 'warning' : 'secondary') ?>"><?= ucfirst(htmlspecialchars($e['interest_level'])) ?></span></td>
                                <td><span class="aps-cp-badge badge bg-info"><?= str_replace('_',' ', ucfirst(htmlspecialchars($e['buying_timeline'] ?? 'N/A'))) ?></span></td>
                                <td><span class="aps-cp-badge badge bg-<?= $e['is_verified'] ? 'success' : 'warning' ?>"><?= $e['is_verified'] ? 'Verified' : 'Pending' ?></span></td>
                                <td>
                                    <?php if ($e['auto_created_lead_id']): ?>
                                        <a href="<?= BASE_URL ?>/admin/leads/<?= $e['auto_created_lead_id'] ?>" class="text-primary small"><strong>#<?= $e['auto_created_lead_id'] ?></strong></a>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-muted small"><?= date('d M Y', strtotime($e['created_at'])) ?></td>
                            </tr>
                            <?php if ($e['extracted_requirements']): ?>
                                <tr><td colspan="11" class="bg-light"><small class="text-muted"><strong>Requirements:</strong> <?= htmlspecialchars($e['extracted_requirements']) ?></small></td></tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
