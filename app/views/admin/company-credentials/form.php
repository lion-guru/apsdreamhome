<?php
$credential = $credential ?? null;
$isEdit = !empty($credential['id']);
$action = $isEdit ? '/admin/company-credentials/' . $credential['id'] . '/update' : '/admin/company-credentials/store';

$typeLabels = [
    'gst' => 'GST (GSTIN)', 'pan' => 'PAN Card', 'tan' => 'TAN Number', 'cin' => 'CIN Number',
    'msme' => 'MSME Registration', 'rera' => 'RERA Registration', 'bank_account' => 'Bank Account', 'digital_signature' => 'Digital Signature',
];

$statusOptions = ['active' => 'Active', 'expired' => 'Expired', 'suspended' => 'Suspended', 'pending_renewal' => 'Pending Renewal'];
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3"><?= htmlspecialchars($page_heading ?? 'Credential Form') ?></h1>
        <a href="<?= BASE_URL ?>/admin/company-credentials" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back to List
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form method="POST" action="<?= BASE_URL . $action ?>">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="credential_type" class="form-label">Credential Type <span class="text-danger">*</span></label>
                                <select name="credential_type" id="credential_type" class="form-select" required>
                                    <option value="">Select Type</option>
                                    <?php foreach ($typeLabels as $val => $lbl): ?>
                                    <option value="<?= $val ?>" <?= ($credential['credential_type'] ?? '') === $val ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($lbl ?? '') ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="credential_label" class="form-label">Label <span class="text-danger">*</span></label>
                                <input type="text" name="credential_label" id="credential_label" class="form-control"
                                       value="<?= htmlspecialchars($credential['credential_label'] ?? '') ?>" required
                                       placeholder="e.g. Company GSTIN">
                            </div>

                            <div class="col-md-12">
                                <label for="credential_value" class="form-label">Value <span class="text-danger">*</span></label>
                                <input type="text" name="credential_value" id="credential_value" class="form-control font-monospace"
                                       value="<?= htmlspecialchars($credential['credential_value'] ?? '') ?>" required
                                       placeholder="e.g. 09AAACN1234F1Z5">
                            </div>

                            <div class="col-md-6">
                                <label for="issuer" class="form-label">Issuer</label>
                                <input type="text" name="issuer" id="issuer" class="form-control"
                                       value="<?= htmlspecialchars($credential['issuer'] ?? '') ?>"
                                       placeholder="e.g. GST Network, NSDL">
                            </div>

                            <div class="col-md-6">
                                <label for="status" class="form-label">Status</label>
                                <select name="status" id="status" class="form-select">
                                    <?php foreach ($statusOptions as $val => $lbl): ?>
                                    <option value="<?= $val ?>" <?= ($credential['status'] ?? 'active') === $val ? 'selected' : '' ?>>
                                        <?= $lbl ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="issue_date" class="form-label">Issue Date</label>
                                <input type="date" name="issue_date" id="issue_date" class="form-control"
                                       value="<?= htmlspecialchars($credential['issue_date'] ?? '') ?>">
                            </div>

                            <div class="col-md-6">
                                <label for="expiry_date" class="form-label">Expiry Date</label>
                                <input type="date" name="expiry_date" id="expiry_date" class="form-control"
                                       value="<?= htmlspecialchars($credential['expiry_date'] ?? '') ?>">
                            </div>

                            <div class="col-md-12">
                                <label for="document_path" class="form-label">Document Path</label>
                                <input type="text" name="document_path" id="document_path" class="form-control"
                                       value="<?= htmlspecialchars($credential['document_path'] ?? '') ?>"
                                       placeholder="Path to scanned document (optional)">
                            </div>

                            <div class="col-md-12">
                                <div class="form-check form-switch">
                                    <input type="checkbox" name="is_primary" id="is_primary" class="form-check-input"
                                           value="1" <?= !empty($credential['is_primary']) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="is_primary">Primary credential for this type</label>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <label for="notes" class="form-label">Notes</label>
                                <textarea name="notes" id="notes" class="form-control" rows="3"
                                          placeholder="Any additional notes..."><?= htmlspecialchars($credential['notes'] ?? '') ?></textarea>
                            </div>
                        </div>

                        <div class="mt-4 d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i><?= $isEdit ? 'Update Credential' : 'Create Credential' ?>
                            </button>
                            <a href="<?= BASE_URL ?>/admin/company-credentials" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
