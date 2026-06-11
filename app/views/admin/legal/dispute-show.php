<?php
$page_title = $page_title ?? 'Dispute Details';
$dispute = $dispute ?? [];
?>
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1"><?php echo $dispute['title'] ?? 'Dispute Details'; ?></h1>
                    <p class="text-muted mb-0">Filed: <?php echo $dispute['filed_date'] ?? '-'; ?></p>
                </div>
                <div>
                    <a href="<?php echo BASE_URL; ?>/admin/legal/disputes" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Dispute Information</h5>
                </div>
                <div class="card-body aps-cp-card-body">
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Party A</div>
                        <div class="col-sm-8"><strong><?php echo $dispute['party_a'] ?? '-'; ?></strong></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Party B</div>
                        <div class="col-sm-8"><strong><?php echo $dispute['party_b'] ?? '-'; ?></strong></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Dispute Type</div>
                        <div class="col-sm-8"><span class="badge bg-info-subtle text-info rounded-pill px-3"><?php echo $dispute['dispute_type'] ?? '-'; ?></span></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Current Status</div>
                        <div class="col-sm-8">
                            <span class="badge bg-<?php echo ($dispute['status'] ?? '') === 'open' ? 'warning' : (($dispute['status'] ?? '') === 'resolved' ? 'success' : 'primary'); ?>-subtle text-<?php echo ($dispute['status'] ?? '') === 'open' ? 'warning' : (($dispute['status'] ?? '') === 'resolved' ? 'success' : 'primary'); ?> rounded-pill px-3">
                                <?php echo ucfirst($dispute['status'] ?? 'open'); ?>
                            </span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Assigned To</div>
                        <div class="col-sm-8"><?php echo $dispute['assigned_name'] ?? 'Unassigned'; ?></div>
                    </div>
                    <?php if (!empty($dispute['resolved_date'])): ?>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Resolved Date</div>
                        <div class="col-sm-8"><?php echo $dispute['resolved_date']; ?></div>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($dispute['description'])): ?>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Description</div>
                        <div class="col-sm-8"><?php echo nl2br(htmlspecialchars($dispute['description'])); ?></div>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($dispute['notes'])): ?>
                    <div class="row">
                        <div class="col-sm-4 text-muted">Notes</div>
                        <div class="col-sm-8"><?php echo nl2br(htmlspecialchars($dispute['notes'])); ?></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Update Status</h5>
                </div>
                <div class="card-body aps-cp-card-body">
                    <form method="POST" action="<?php echo BASE_URL; ?>/admin/legal/disputes/update/<?php echo $dispute['id'] ?? 0; ?>">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="open" <?php echo ($dispute['status'] ?? '') === 'open' ? 'selected' : ''; ?>>Open</option>
                                <option value="in_progress" <?php echo ($dispute['status'] ?? '') === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                <option value="resolved" <?php echo ($dispute['status'] ?? '') === 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                                <option value="closed" <?php echo ($dispute['status'] ?? '') === 'closed' ? 'selected' : ''; ?>>Closed</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Assigned To</label>
                            <select name="assigned_to" class="form-select">
                                <option value="">Unassigned</option>
                                <?php $users = $users ?? []; foreach ($users as $u): ?>
                                    <option value="<?php echo $u['id']; ?>" <?php echo (($dispute['assigned_to'] ?? 0) == $u['id']) ? 'selected' : ''; ?>><?php echo $u['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="4"><?php echo $dispute['notes'] ?? ''; ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-save me-1"></i>Update</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
