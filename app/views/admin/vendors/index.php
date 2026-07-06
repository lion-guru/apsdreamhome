<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Vendor Management</h1>
            <p class="text-muted mb-0">Manage contractors, suppliers, and service providers</p>
        </div>
        <a href="<?= BASE_URL ?>/admin/vendors/create" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Vendor
        </a>
    </div>



    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body aps-cp-card-body">
                    <h6>Total Vendors</h6>
                    <h3><?= $stats['total'] ?? 0 ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body aps-cp-card-body">
                    <h6>Active</h6>
                    <h3><?= $stats['active'] ?? 0 ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-warning text-white">
                <div class="card-body aps-cp-card-body">
                    <h6>Inactive</h6>
                    <h3><?= $stats['inactive'] ?? 0 ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-danger text-white">
                <div class="card-body aps-cp-card-body">
                    <h6>Blacklisted</h6>
                    <h3><?= $stats['blacklisted'] ?? 0 ?></h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body aps-cp-card-body">
            <form method="GET" class="row g-3 mb-4">
                <div class="col-md-5">
                    <input type="text" class="form-control" name="search" placeholder="Search by name, contact, email, phone..." value="<?= htmlspecialchars($search ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="type">
                        <option value="">All Types</option>
                        <option value="contractor" <?= ($typeFilter ?? '') === 'contractor' ? 'selected' : '' ?>>Contractor</option>
                        <option value="supplier" <?= ($typeFilter ?? '') === 'supplier' ? 'selected' : '' ?>>Supplier</option>
                        <option value="service_provider" <?= ($typeFilter ?? '') === 'service_provider' ? 'selected' : '' ?>>Service Provider</option>
                        <option value="consultant" <?= ($typeFilter ?? '') === 'consultant' ? 'selected' : '' ?>>Consultant</option>
                        <option value="transport" <?= ($typeFilter ?? '') === 'transport' ? 'selected' : '' ?>>Transport</option>
                        <option value="other" <?= ($typeFilter ?? '') === 'other' ? 'selected' : '' ?>>Other</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="status">
                        <option value="">All Status</option>
                        <option value="active" <?= ($statusFilter ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= ($statusFilter ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        <option value="blacklisted" <?= ($statusFilter ?? '') === 'blacklisted' ? 'selected' : '' ?>>Blacklisted</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i> Filter</button>
                </div>
            </form>

            <?php if (empty($vendors)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-building fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No vendors found</p>
                    <a href="<?= BASE_URL ?>/admin/vendors/create" class="btn btn-primary">Add Your First Vendor</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Vendor Name</th>
                                <th>Type</th>
                                <th>PAN</th>
                                <th>Entity</th>
                                <th>TDS</th>
                                <th>KYC</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($vendors as $v): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($v['vendor_name'] ?? '') ?></strong>
                                    <br><small class="text-muted"><?= htmlspecialchars($v['contact_person'] ?? $v['phone'] ?? '-') ?></small>
                                </td>
                                <td>
                                    <?php
                                    $typeLabels = [
                                        'contractor' => ['badge' => 'primary', 'label' => 'Contractor'],
                                        'supplier' => ['badge' => 'info', 'label' => 'Supplier'],
                                        'service_provider' => ['badge' => 'success', 'label' => 'Service'],
                                        'consultant' => ['badge' => 'secondary', 'label' => 'Consultant'],
                                        'transport' => ['badge' => 'warning', 'label' => 'Transport'],
                                        'other' => ['badge' => 'dark', 'label' => 'Other'],
                                    ];
                                    $t = $typeLabels[$v['vendor_type'] ?? 'other'] ?? ['badge' => 'dark', 'label' => 'Other'];
                                    ?>
                                    <span class="badge bg-<?= $t['badge'] ?>"><?= $t['label'] ?></span>
                                </td>
                                <td><code><?= htmlspecialchars($v['pan_number'] ?? '-') ?></code></td>
                                <td>
                                    <?php
                                    $entityLabels = [
                                        'individual' => ['badge' => 'info', 'label' => 'Individual', 'tds' => '1%'],
                                        'company' => ['badge' => 'primary', 'label' => 'Company', 'tds' => '2%'],
                                        'partnership' => ['badge' => 'warning', 'label' => 'Partnership', 'tds' => '2%'],
                                        'proprietorship' => ['badge' => 'secondary', 'label' => 'Proprietorship', 'tds' => '1%'],
                                    ];
                                    $e = $entityLabels[$v['entity_type'] ?? 'individual'] ?? ['badge' => 'info', 'label' => '-', 'tds' => '1%'];
                                    ?>
                                    <span class="badge bg-<?= $e['badge'] ?>"><?= $e['label'] ?></span>
                                </td>
                                <td>
                                    <?php if (($v['is_tds_applicable'] ?? 1) == 1): ?>
                                        <span class="badge bg-dark"><?= htmlspecialchars($v['tds_section'] ?? '194C') ?></span>
                                        <small class="text-muted"><?= $e['tds'] ?? '1%' ?></small>
                                    <?php else: ?>
                                        <span class="badge bg-light text-dark">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $kycColors = [
                                        'verified' => 'success',
                                        'pending'  => 'warning',
                                        'rejected' => 'danger',
                                    ];
                                    $kycColor = $kycColors[$v['kyc_status'] ?? 'pending'] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?= $kycColor ?>"><?= ucfirst($v['kyc_status'] ?? 'pending') ?></span>
                                </td>
                                <td>
                                    <?php if (($v['status'] ?? '') === 'active'): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php elseif (($v['status'] ?? '') === 'inactive'): ?>
                                        <span class="badge bg-warning">Inactive</span>
                                    <?php elseif (($v['status'] ?? '') === 'blacklisted'): ?>
                                        <span class="badge bg-danger">Blacklisted</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary"><?= ucfirst($v['status'] ?? 'Unknown') ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= BASE_URL ?>/admin/vendors/show/<?= $v['id'] ?>" class="btn btn-info" title="View"><i class="fas fa-eye"></i></a>
                                        <a href="<?= BASE_URL ?>/admin/vendors/edit/<?= $v['id'] ?>" class="btn btn-primary" title="Edit"><i class="fas fa-edit"></i></a>
                                        <a href="<?= BASE_URL ?>/admin/vendors/contracts/<?= $v['id'] ?>" class="btn btn-secondary" title="Contracts"><i class="fas fa-file-contract"></i></a>
                                        <button type="button" class="btn btn-danger" title="Deactivate" onclick="deactivateVendor(<?= $v['id'] ?>, '<?= htmlspecialchars(addslashes($v['vendor_name'])) ?>')"><i class="fas fa-ban"></i></button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<form id="deactivateForm" method="POST" style="display:none;">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
</form>

<script>
function deactivateVendor(id, name) {
    if (confirm('Deactivate vendor "' + name + '"? This vendor will be marked as inactive.')) {
        var form = document.getElementById('deactivateForm');
        form.action = '<?= BASE_URL ?>/admin/vendors/delete/' + id;
        form.submit();
    }
}
</script>
