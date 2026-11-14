<?php $pageTitle = 'Farmer Details'; ?>
<?php $farmer = $farmer ?? null; $land_holdings = $land_holdings ?? []; ?>
<div class="container-fluid py-4">
    <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="<?= BASE_URL ?>admin/dashboard"><i class="fas fa-home"></i> Dashboard</a></li><li class="breadcrumb-item"><a href="<?= BASE_URL ?>farmers/list">Farmers</a></li><li class="breadcrumb-item active">Farmer Details</li></ol></nav>
    <?php if (!$farmer): ?>
    <div class="card border-0 shadow-sm"><div class="card-body text-center py-5"><i class="fas fa-exclamation-circle fa-3x text-muted mb-3"></i><h6 class="text-muted">Farmer not found</h6><a href="<?= BASE_URL ?>farmers/list" class="btn btn-primary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back to List</a></div></div>
    <?php else: ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h4 class="mb-1"><?= htmlspecialchars($farmer['name'] ?? '-') ?></h4><small class="text-muted">Farmer since <?= date('d M Y', strtotime($farmer['created_at'] ?? 'now')) ?></small></div>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>farmers/<?= $farmer['id'] ?? 0 ?>/edit" class="btn btn-primary btn-sm"><i class="fas fa-edit me-1"></i>Edit</a>
            <a href="<?= BASE_URL ?>farmers/list" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
        </div>
    </div>
    <div class="row g-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white"><h2  class="fs-6 fw-semibold mb-0"><i class="fas fa-info-circle me-2"></i>Personal Info</h2></div>
                <div class="card-body aps-cp-card-body">
                    <div class="table-responsive"><table class="table table-sm table-responsive">
                        <tr><th scope="col">Name</th><td><?= htmlspecialchars($farmer['name'] ?? '-') ?></td></tr>
                        <tr><th scope="col">Phone</th><td><?= htmlspecialchars($farmer['phone'] ?? '-') ?></td></tr>
                        <tr><th scope="col">Email</th><td><?= htmlspecialchars($farmer['email'] ?? '-') ?></td></tr>
                        <tr><th scope="col">Address</th><td><?= htmlspecialchars($farmer['address'] ?? '-') ?></td></tr>
                        <tr><th scope="col">State</th><td><?= htmlspecialchars($farmer['state_name'] ?? '-') ?></td></tr>
                        <tr><th scope="col">District</th><td><?= htmlspecialchars($farmer['district_name'] ?? '-') ?></td></tr>
                        <tr><th scope="col">Status</th><td><span class="badge bg-<?= ($farmer['status'] ?? '') === 'active' ? 'success' : 'secondary' ?>"><?= ucfirst($farmer['status'] ?? '-') ?></span></td></tr>
                    </table></div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white"><h2  class="fs-6 fw-semibold mb-0"><i class="fas fa-id-card me-2"></i>KYC & Bank Details</h2></div>
                <div class="card-body aps-cp-card-body">
                    <div class="table-responsive"><table class="table table-sm table-responsive">
                        <tr><th scope="col">Aadhar Number</th><td><?= htmlspecialchars($farmer['aadhar_number'] ?? '-') ?></td></tr>
                        <tr><th scope="col">PAN Number</th><td><?= htmlspecialchars($farmer['pan_number'] ?? '-') ?></td></tr>
                        <tr><th scope="col">Bank Account</th><td><?= htmlspecialchars($farmer['bank_account'] ?? '-') ?></td></tr>
                        <tr><th scope="col">IFSC Code</th><td><?= htmlspecialchars($farmer['ifsc_code'] ?? '-') ?></td></tr>
                    </table></div>
                </div>
            </div>
        </div>
    </div>
    <div class="card border-0 shadow-sm mt-4">
        <div class="card-header bg-white"><h2  class="fs-6 fw-semibold mb-0"><i class="fas fa-map me-2"></i>Land Holdings</h2></div>
        <div class="card-body aps-cp-card-body">
            <?php if (empty($land_holdings)): ?>
            <p class="text-muted text-center py-3 mb-0">No land holdings recorded yet</p>
            <?php else: ?>
            <div class="table-responsive"><div class="table-responsive"><table class="table table-sm table-responsive"><thead><tr><th scope="col">Khasra #</th><th scope="col">Area</th><th scope="col">Type</th><th scope="col">Location</th><th scope="col">Status</th></tr></thead>
                <tbody><?php foreach ($land_holdings as $lh): ?><tr>
                    <td><?= htmlspecialchars($lh['khasra_number'] ?? '-') ?></td>
                    <td><?= number_format($lh['land_area'] ?? 0, 2) ?> <?= htmlspecialchars($lh['land_area_unit'] ?? 'sqft') ?></td>
                    <td><?= htmlspecialchars(ucfirst($lh['land_type'] ?? '-')) ?></td>
                    <td><?= htmlspecialchars($lh['village'] ?? $lh['location'] ?? '-') ?>, <?= htmlspecialchars($lh['tehsil'] ?? '') ?></td>
                    <td><span class="badge bg-<?= ($lh['acquisition_status'] ?? 'not_acquired') === 'acquired' ? 'success' : 'warning' ?>"><?= ucfirst(str_replace('_', ' ', $lh['acquisition_status'] ?? 'not_acquired')) ?></span></td>
                </tr><?php endforeach; ?></tbody></table></div></div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
