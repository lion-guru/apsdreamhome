<?php $pageTitle = 'Property Detail Report'; ?>
<?php $property = $property ?? null; $history = $history ?? []; ?>
<div class="container-fluid py-4">
    <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="<?= BASE_URL ?>admin/dashboard"><i class="fas fa-home"></i> Dashboard</a></li><li class="breadcrumb-item"><a href="<?= BASE_URL ?>reports/dashboard">Reports</a></li><li class="breadcrumb-item"><a href="<?= BASE_URL ?>reports/properties">Properties</a></li><li class="breadcrumb-item active">Property Detail</li></ol></nav>
    <?php if (!$property): ?>
    <div class="card border-0 shadow-sm"><div class="card-body text-center py-5"><i class="fas fa-exclamation-circle fa-3x text-muted mb-3"></i><h6 class="text-muted">Property not found</h6><a href="<?= BASE_URL ?>reports/properties" class="btn btn-primary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a></div></div>
    <?php else: ?>
    <div class="d-flex justify-content-between align-items-center mb-4"><h4 class="mb-0"><?= htmlspecialchars($property['name'] ?? $property['title'] ?? 'Property Report') ?></h4><a href="<?= BASE_URL ?>reports/generate?type=property&id=<?= $property['id'] ?? 0 ?>" class="btn btn-primary btn-sm"><i class="fas fa-download me-1"></i>Export PDF</a></div>
    <div class="row g-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100"><div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Basic Info</h6></div>
                <div class="card-body aps-cp-card-body"><div class="table-responsive"><table class="table table-sm table-responsive"><tbody>
                    <tr><th>Type</th><td><?= htmlspecialchars(ucfirst($property['type'] ?? '-')) ?></td></tr>
                    <tr><th>Price</th><td>â‚¹<?= number_format($property['price'] ?? 0) ?></td></tr>
                    <tr><th>Area</th><td><?= htmlspecialchars($property['area'] ?? '-') ?> sq.ft.</td></tr>
                    <tr><th>Location</th><td><?= htmlspecialchars($property['location'] ?? $property['city'] ?? '-') ?></td></tr>
                    <tr><th>Status</th><td><span class="badge bg-<?= ($property['status'] ?? '') === 'available' ? 'success' : 'danger' ?>"><?= ucfirst($property['status'] ?? '-') ?></span></td></tr>
                    <tr><th>Listed On</th><td><?= htmlspecialchars($property['created_at'] ?? '-') ?></td></tr>
                </tbody></table></div></div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100"><div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-chart-simple me-2"></i>Performance</h6></div>
                <div class="card-body aps-cp-card-body"><div class="table-responsive"><table class="table table-sm table-responsive"><tbody>
                    <tr><th>Total Views</th><td><?= number_format($property['views'] ?? 0) ?></td></tr>
                    <tr><th>Inquiries</th><td><?= number_format($property['inquiries'] ?? 0) ?></td></tr>
                    <tr><th>Lead Score</th><td><?= $property['lead_score'] ?? 'N/A' ?></td></tr>
                    <tr><th>Days on Market</th><td><?= $property['days_on_market'] ?? 'N/A' ?></td></tr>
                </tbody></table></div></div>
            </div>
        </div>
    </div>
    <div class="card border-0 shadow-sm mt-4">
        <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-history me-2"></i>Activity Timeline</h6></div>
        <div class="card-body aps-cp-card-body">
            <?php if (empty($history)): ?><p class="text-muted mb-0 text-center py-3">No activity recorded for this property</p>
            <?php else: ?><ul class="list-unstyled mb-0"><?php foreach ($history as $h): ?>
                <li class="py-2 border-bottom"><i class="fas fa-circle text-<?= $h['type'] === 'created' ? 'success' : ($h['type'] === 'inquiry' ? 'info' : 'secondary') ?> me-2" class="style-67926"></i><strong><?= htmlspecialchars(ucfirst($h['type'] ?? 'event')) ?></strong> â€” <?= htmlspecialchars($h['description'] ?? '') ?> <span class="float-end text-muted small"><?= htmlspecialchars($h['date'] ?? '') ?></span></li>
            <?php endforeach; ?></ul><?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
