<?php $pageTitle = $page_title ?? 'Transfer Property Ownership'; ?>
<div class="container-fluid py-4">
    <h4 class="mb-4"><i class="fas fa-exchange-alt me-2"></i><?= htmlspecialchars($pageTitle) ?></h4>
    <div class="row g-3">
        <div class="col-md-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent"><h5 class="mb-0"><i class="fas fa-building me-2"></i>Property</h5></div>
                <div class="card-body aps-cp-card-body">
                    <p class="mb-1"><strong><?= htmlspecialchars($property['title'] ?? '-') ?></strong></p>
                    <p class="text-muted"><?= htmlspecialchars($property['city'] ?? '') ?> — ₹<?= number_format($property['price'] ?? 0) ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent"><h5 class="mb-0"><i class="fas fa-user-plus me-2"></i>Transfer Details</h5></div>
                <div class="card-body aps-cp-card-body">
                    <form method="post">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="mb-3">
                            <label class="form-label">New Owner Blockchain Address</label>
                            <input type="text" name="new_owner_address" class="form-control font-monospace" placeholder="0x..." required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Transfer Reason</label>
                            <select name="transfer_reason" class="form-select">
                                <option value="sale">Sale</option>
                                <option value="gift">Gift</option>
                                <option value="inheritance">Inheritance</option>
                                <option value="transfer">Internal Transfer</option>
                            </select>
                        </div>
                        <div class="alert alert-warning small">
                            <i class="fas fa-exclamation-triangle me-1"></i>This action will initiate a blockchain transaction. Gas fees apply.
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-exchange-alt me-1"></i>Initiate Transfer</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
