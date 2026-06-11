<?php $pageTitle = $page_title ?? 'Digital Signature'; ?>
<div class="container-fluid py-4">
    <h4 class="mb-4"><i class="fas fa-pen-signature me-2"></i><?= htmlspecialchars($pageTitle) ?></h4>
    <div class="row g-3">
        <div class="col-md-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent"><h5 class="mb-0"><i class="fas fa-building me-2"></i>Property</h5></div>
                <div class="card-body aps-cp-card-body">
                    <p class="mb-1"><strong><?= htmlspecialchars($property['title'] ?? '-') ?></strong></p>
                    <p class="text-muted"><?= htmlspecialchars($property['city'] ?? '') ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent"><h5 class="mb-0"><i class="fas fa-edit me-2"></i>Apply Digital Signature</h5></div>
                <div class="card-body aps-cp-card-body">
                    <form method="post">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="mb-3">
                            <label class="form-label">Digital Signature</label>
                            <textarea name="signature" class="form-control font-monospace" rows="4" placeholder="Paste your digital signature or use signature pad..." required></textarea>
                        </div>
                        <div class="alert alert-info small">
                            <i class="fas fa-info-circle me-1"></i>Your signature will be hashed and stored on the blockchain for verification.
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-pen-signature me-1"></i>Apply Signature</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
