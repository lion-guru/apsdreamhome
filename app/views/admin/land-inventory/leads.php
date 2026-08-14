<?php
$leads    = $leads    ?? [];
$filters  = $filters  ?? [];
$statuses = $statuses ?? [];
$sources  = $sources  ?? [];
$statusColors = [
    'new'             => 'secondary',
    'screening'       => 'info',
    'visit_done'      => 'primary',
    'dd'              => 'warning',
    'negotiation'     => 'warning',
    'legal'           => 'info',
    'sale_agreement'  => 'success',
    'registered'      => 'success',
    'rejected'        => 'danger',
    'dropped'         => 'dark',
];
?>
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="fas fa-mountain text-primary me-2"></i>Land Acquisition — Leads</h4>
            <small class="text-muted">Pipeline from broker/source through registration</small>
        </div>
        <a href="<?= BASE_URL ?>/admin/land-inventory/leads/create" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i>New Lead
        </a>
    </div>

    <?php if ($msg = \App\Core\Session::flash('success')): ?>
        <div class="alert alert-success alert-dismissible fade show"><?= htmlspecialchars($msg) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
    <?php if ($msg = \App\Core\Session::flash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show"><?= htmlspecialchars($msg) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>

    <div class="aps-cp-card">
        <div class="aps-cp-card-header">
            <span><i class="fas fa-filter me-2"></i>Filters</span>
            <a href="<?= BASE_URL ?>/admin/land-inventory/leads" class="btn btn-sm btn-outline-secondary">Reset</a>
        </div>
        <div class="aps-cp-card-body">
            <form method="get" class="row g-3 align-items-end">
    <?php echo CSRFProtection::csrfField(); ?>
                <div class="col-md-3">
                    <label class="form-label small">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        <?php foreach ($statuses as $s): ?>
                            <option value="<?= $s ?>" <?= ($filters['status'] ?? '') === $s ? 'selected' : '' ?>>
                                <?= ucwords(str_replace('_',' ',$s)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">District</label>
                    <input type="text" name="district" class="form-control form-control-sm"
                           value="<?= htmlspecialchars($filters['district'] ?? '') ?>" placeholder="e.g. Gorakhpur">
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Source</label>
                    <select name="source" class="form-select form-select-sm">
                        <option value="">All Sources</option>
                        <?php foreach ($sources as $s): ?>
                            <option value="<?= $s ?>" <?= ($filters['source'] ?? '') === $s ? 'selected' : '' ?>>
                                <?= ucfirst($s) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-search me-1"></i>Apply
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="aps-cp-card">
        <div class="aps-cp-card-header">
            <span><i class="fas fa-list me-2"></i>Leads (<?= count($leads) ?>)</span>
        </div>
        <div class="aps-cp-card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Owner</th>
                            <th>Location</th>
                            <th>Area</th>
                            <th>Expected ₹</th>
                            <th>Source</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($leads as $l): ?>
                        <tr>
                            <td><code>#<?= (int)($l['id'] ?? 0) ?></code></td>
                            <td>
                                <strong><?= htmlspecialchars($l['land_owner_name'] ?? '—') ?></strong><br>
                                <small class="text-muted"><?= htmlspecialchars($l['owner_phone'] ?? '') ?></small>
                            </td>
                            <td>
                                <?= htmlspecialchars($l['village'] ?? '') ?>
                                <?php if (!empty($l['tehsil'])): ?>, <?= htmlspecialchars($l['tehsil']) ?><?php endif; ?><br>
                                <small class="text-muted"><?= htmlspecialchars($l['district'] ?? '') ?>, <?= htmlspecialchars($l['state'] ?? '') ?></small>
                            </td>
                            <td>
                                <?= number_format((float)($l['area_sqft'] ?? 0)) ?> sqft<br>
                                <small class="text-muted"><?= number_format((float)($l['area_acres'] ?? 0), 2) ?> ac</small>
                            </td>
                            <td>₹<?= number_format((float)($l['expected_price'] ?? 0)) ?></td>
                            <td>
                                <span class="badge bg-light text-dark">
                                    <?= htmlspecialchars(ucfirst($l['lead_source'] ?? '—')) ?>
                                </span>
                                <?php if (!empty($l['broker_name'])): ?>
                                    <br><small class="text-muted"><?= htmlspecialchars($l['broker_name']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-<?= $statusColors[$l['status'] ?? 'new'] ?? 'secondary' ?>">
                                    <?= ucwords(str_replace('_',' ', $l['status'] ?? 'new')) ?>
                                </span>
                            </td>
                            <td>
                                <small><?= htmlspecialchars(substr($l['created_at'] ?? '', 0, 10)) ?></small>
                            </td>
                            <td class="text-end text-nowrap">
                                <a href="<?= BASE_URL ?>/admin/land-inventory/leads/<?= (int)$l['id'] ?>" class="btn btn-sm btn-info" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="<?= BASE_URL ?>/admin/land-inventory/leads/<?= (int)$l['id'] ?>/edit" class="btn btn-sm btn-primary" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($leads)): ?>
                        <tr><td colspan="9" class="text-center text-muted py-5">
                            <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                            No land leads yet. <a href="<?= BASE_URL ?>/admin/land-inventory/leads/create">Create the first one</a>.
                        </td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
