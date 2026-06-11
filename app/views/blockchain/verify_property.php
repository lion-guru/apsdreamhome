<?php $pageTitle = $page_title ?? 'Verify Property on Blockchain'; ?>
<div class="container-fluid py-4">
    <h4 class="mb-4"><i class="fas fa-shield-alt me-2"></i><?= htmlspecialchars($pageTitle) ?></h4>
    <div class="row g-3">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent"><h5 class="mb-0"><i class="fas fa-building me-2"></i>Property Details</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="table-responsive"><table class="table table-sm table-responsive">
                        <tr><th>Title</th><td><?= htmlspecialchars($property['title'] ?? '-') ?></td></tr>
                        <tr><th>City</th><td><?= htmlspecialchars($property['city'] ?? '-') ?></td></tr>
                        <tr><th>Price</th><td>₹<?= number_format($property['price'] ?? 0) ?></td></tr>
                    </table></div>
                </div>
            </div>
            <?php if (!empty($existing_verification)): ?>
                <div class="card border-0 shadow-sm mt-3">
                    <div class="card-header bg-transparent"><h5 class="mb-0"><i class="fas fa-history me-2"></i>Existing Verification</h5></div>
                    <div class="card-body aps-cp-card-body">
                        <span class="badge bg-<?= ($existing_verification['blockchain_status'] ?? '') === 'verified' ? 'success' : 'warning' ?> fs-6"><?= strtoupper($existing_verification['blockchain_status'] ?? 'UNKNOWN') ?></span>
                        <p class="small text-muted mt-2 mb-0">Hash: <?= htmlspecialchars($existing_verification['blockchain_hash'] ?? '-') ?></p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent"><h5 class="mb-0"><i class="fas fa-link me-2"></i>Initiate Verification</h5></div>
                <div class="card-body aps-cp-card-body">
                    <form method="post">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="mb-3">
                            <label class="form-label">Property Documents</label>
                            <select name="document_type" class="form-select" required>
                                <option value="">Select document type...</option>
                                <option value="title_deed">Title Deed</option>
                                <option value="tax_receipts">Tax Receipts</option>
                                <option value="ownership_certificate">Ownership Certificate</option>
                                <option value="encumbrance">Encumbrance Certificate</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Additional Notes</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="Any additional information..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-link me-1"></i>Start Verification</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
