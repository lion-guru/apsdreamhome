<?php
$lead      = $lead ?? [];
$documents = $documents ?? [];
$id = (int)($lead['id'] ?? 0);

$docTypes = [
    'mother_deed' => 'Mother Deed',
    'chain_of_title' => 'Chain of Title',
    'ec_30yr' => 'EC (30-year)',
    'patta' => 'Patta',
    'chitta' => 'Chitta',
    'fmb' => 'FMB Sketch',
    'a_register' => 'A-Register',
    'property_tax' => 'Property Tax Receipt',
    'kist_receipt' => 'Kist Receipt',
    'succession_cert' => 'Succession Certificate',
    'noc_co_owners' => 'NOC from Co-owners',
    'layout_plan' => 'Layout Plan',
    'conversion_order' => 'Conversion Order',
    'power_of_attorney' => 'Power of Attorney',
    'sale_agreement' => 'Sale Agreement',
    'registered_deed' => 'Registered Deed',
    'mutation_application' => 'Mutation Application',
    'other' => 'Other',
];
?>
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="fas fa-file-alt text-primary me-2"></i>Documents — Lead #<?= $id ?></h4>
            <small class="text-muted"><?= htmlspecialchars($lead['land_owner_name'] ?? '') ?></small>
        </div>
        <a href="<?= BASE_URL ?>/admin/land-inventory/leads/<?= $id ?>" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Back to Lead
        </a>
    </div>

    <div class="row g-3">
        <div class="col-md-4">
            <div class="aps-cp-card">
                <div class="aps-cp-card-header"><i class="fas fa-upload me-2"></i>Upload Document</div>
                <div class="aps-cp-card-body">
                    <form method="post" action="<?= BASE_URL ?>/admin/land-inventory/leads/<?= $id ?>/documents/upload" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                        <div class="mb-2">
                            <label class="form-label small">Document Type <span class="text-danger">*</span></label>
                            <select name="doc_type" class="form-select form-select-sm" required>
                                <?php foreach ($docTypes as $k => $v): ?>
                                    <option value="<?= $k ?>"><?= $v ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">Document #</label>
                            <input type="text" name="doc_number" class="form-control form-control-sm">
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">Document Date</label>
                            <input type="date" name="doc_date" class="form-control form-control-sm">
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">File (PDF / Image)</label>
                            <input type="file" name="document_file" class="form-control form-control-sm" accept=".pdf,image/*">
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">Verification Status</label>
                            <select name="verification_status" class="form-select form-select-sm">
                                <option value="pending">Pending</option>
                                <option value="verified">Verified</option>
                                <option value="missing">Missing</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">Remarks</label>
                            <textarea name="remarks" class="form-control form-control-sm" rows="2"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm w-100">
                            <i class="fas fa-upload me-1"></i>Upload
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="aps-cp-card">
                <div class="aps-cp-card-header"><i class="fas fa-list me-2"></i>Document Checklist (<?= count($documents) ?>)</div>
                <div class="aps-cp-card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr><th>Type</th><th>Doc #</th><th>Date</th><th>Status</th><th>File</th><th>Remarks</th></tr>
                            </thead>
                            <tbody>
                            <?php foreach ($documents as $d): ?>
                                <tr>
                                    <td><?= htmlspecialchars($docTypes[$d['doc_type']] ?? $d['doc_type']) ?></td>
                                    <td><small><?= htmlspecialchars($d['doc_number'] ?? '—') ?></small></td>
                                    <td><small><?= htmlspecialchars($d['doc_date'] ?? '—') ?></small></td>
                                    <td>
                                        <span class="badge bg-<?= ($d['verification_status'] ?? '') === 'verified' ? 'success' : (($d['verification_status'] ?? '') === 'rejected' ? 'danger' : 'warning') ?>">
                                            <?= htmlspecialchars(ucfirst($d['verification_status'] ?? 'pending')) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (!empty($d['file_path'])): ?>
                                            <a href="<?= BASE_URL ?>/<?= htmlspecialchars($d['file_path'] ?? '') ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-download"></i>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><small><?= htmlspecialchars($d['remarks'] ?? '—') ?></small></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($documents)): ?>
                                <tr><td colspan="6" class="text-center text-muted py-4">No documents uploaded yet.</td></tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
