<?php
$documents = $documents ?? [];
$categories = $categories ?? [];
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-file-contract me-2 text-primary"></i>Legal Documents</h2>
        <div>
            <a href="<?= BASE_URL ?>/admin/legal/ai-composer" class="btn btn-outline-info btn-sm me-1"><i class="fas fa-magic me-1"></i>AI Generate</a>
            <a href="<?= BASE_URL ?>/admin/legal/documents/create" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>New Document</a>
        </div>
    </div>

    <div class="aps-cp-card mb-4">
        <div class="aps-cp-card-body">
            <form method="GET" class="row g-2 align-items-end">
    <?php echo CSRFProtection::csrfField(); ?>
                <div class="col-md-2"><label class="form-label small">Status</label><select name="status" class="form-select form-select-sm" onchange="this.form.submit()"><option value="">All</option><option value="draft" <?= ($_GET['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option><option value="final" <?= ($_GET['status'] ?? '') === 'final' ? 'selected' : '' ?>>Final</option><option value="signed" <?= ($_GET['status'] ?? '') === 'signed' ? 'selected' : '' ?>>Signed</option><option value="expired" <?= ($_GET['status'] ?? '') === 'expired' ? 'selected' : '' ?>>Expired</option><option value="cancelled" <?= ($_GET['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelled</option></select></div>
                <div class="col-md-2"><label class="form-label small">Category</label><select name="category_id" class="form-select form-select-sm" onchange="this.form.submit()"><option value="">All</option><?php foreach ($categories as $c): ?><option value="<?= $c['id'] ?>" <?= ($_GET['category_id'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['name'] ?? '') ?></option><?php endforeach; ?></select></div>
                <div class="col-md-2"><label class="form-label small">Entity Type</label><select name="entity_type" class="form-select form-select-sm" onchange="this.form.submit()"><option value="">All</option><option value="booking" <?= ($_GET['entity_type'] ?? '') === 'booking' ? 'selected' : '' ?>>Booking</option><option value="customer" <?= ($_GET['entity_type'] ?? '') === 'customer' ? 'selected' : '' ?>>Customer</option><option value="associate" <?= ($_GET['entity_type'] ?? '') === 'associate' ? 'selected' : '' ?>>Associate</option><option value="property" <?= ($_GET['entity_type'] ?? '') === 'property' ? 'selected' : '' ?>>Property</option><option value="loan" <?= ($_GET['entity_type'] ?? '') === 'loan' ? 'selected' : '' ?>>Loan</option></select></div>
                <div class="col-md-3"><label class="form-label small">Search</label><input type="text" name="search" class="form-control form-control-sm" placeholder="Title or document #..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>"></div>
                <div class="col-md-1"><button class="btn btn-sm btn-outline-primary w-100">Filter</button></div>
            </form>
        </div>
    </div>

    <?php if (empty($documents)): ?>
        <div class="text-center text-muted py-5"><i class="fas fa-file-contract fa-3x mb-3"></i><p>No documents found</p></div>
    <?php else: ?>
        <div class="aps-cp-card">
            <div class="aps-cp-card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>Doc #</th><th>Title</th><th>Customer</th><th>Category</th><th>Status</th><th>KYC</th><th>Created</th><th>Actions</th></tr></thead>
                        <tbody>
                        <?php foreach ($documents as $d): ?>
                            <tr>
                                <td><a href="<?= BASE_URL ?>/admin/legal/documents/<?= $d['id'] ?>" class="fw-bold"><?= htmlspecialchars($d['document_number'] ?? '-') ?></a></td>
                                <td><?= htmlspecialchars(substr($d['title'] ?? '', 0, 60)) ?></td>
                                <td class="small"><?= htmlspecialchars($d['customer_name'] ?? '-') ?></td>
                                <td><small><?= htmlspecialchars($d['category_name'] ?? '-') ?></small></td>
                                <td><span class="badge bg-<?= match($d['status']) { 'signed' => 'success', 'final' => 'info', 'draft' => 'secondary', 'expired' => 'warning', 'cancelled' => 'danger', default => 'secondary' } ?>"><?= $d['status'] ?></span></td>
                                <td><?= !empty($d['kyc_verified']) ? '<i class="fas fa-check-circle text-success"></i>' : '<i class="fas fa-times-circle text-muted"></i>' ?></td>
                                <td class="small"><?= date('d M Y', strtotime($d['created_at'] ?? 'now')) ?></td>
                                <td>
                                    <a href="<?= BASE_URL ?>/admin/legal/documents/<?= $d['id'] ?>" class="btn btn-sm btn-outline-primary" title="View"><i class="fas fa-eye"></i></a>
                                    <a href="<?= BASE_URL ?>/admin/legal/documents/<?= $d['id'] ?>/preview" class="btn btn-sm btn-outline-info" title="Preview" target="_blank"><i class="fas fa-print"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
