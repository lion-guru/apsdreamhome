<?php $page_title = 'Maintenance Details'; $r = $request; ?>
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/property-features/maintenance"><i class="fas fa-tools me-1"></i>Maintenance</a></li>
                    <li class="breadcrumb-item active">Request #<?= $r['id'] ?? '' ?></li>
                </ol>
            </nav>
            <h1 class="h3 mb-2"><i class="fas fa-wrench me-2"></i>Maintenance Request #<?= $r['id'] ?? '' ?></h1>
        </div>
    </div>

    <?php if ($msg = $_SESSION['flash_success'] ?? null): ?><div class="alert alert-success alert-dismissible fade show"><?= htmlspecialchars($msg ?? '') ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php unset($_SESSION['flash_success']); endif; ?>
    <?php if ($msg = $_SESSION['flash_error'] ?? null): ?><div class="alert alert-danger alert-dismissible fade show"><?= htmlspecialchars($msg ?? '') ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php unset($_SESSION['flash_error']); endif; ?>

    <div class="row">
        <!-- Details -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Request Details</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="row mb-3">
                        <div class="col-md-3 text-muted">Property</div>
                        <div class="col-md-9"><strong><?= htmlspecialchars($r['property_title'] ?? 'Property #' . $r['property_id']) ?></strong></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3 text-muted">Location</div>
                        <div class="col-md-9"><?= htmlspecialchars($r['property_location'] ?? '-') ?></div>
                    </div>
                    <?php if ($r['plot_id'] ?? null): ?>
                    <div class="row mb-3">
                        <div class="col-md-3 text-muted">Plot #</div>
                        <div class="col-md-9"><?= htmlspecialchars($r['plot_id'] ?? '') ?></div>
                    </div>
                    <?php endif; ?>
                    <div class="row mb-3">
                        <div class="col-md-3 text-muted">Issue Type</div>
                        <div class="col-md-9"><span class="badge bg-secondary"><?= htmlspecialchars($r['issue_type'] ?? '-') ?></span></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3 text-muted">Priority</div>
                        <div class="col-md-9"><?php $p = $r['priority'] ?? 'medium'; $pc = match($p){'high'=>'danger','urgent'=>'danger','medium'=>'warning','low'=>'info',default=>'secondary'}; ?><span class="badge bg-<?= $pc ?>"><?= ucfirst($p) ?></span></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3 text-muted">Status</div>
                        <div class="col-md-9"><?php $s = $r['status'] ?? 'open'; $sc = match($s){'completed'=>'success','in_progress'=>'info','open'=>'warning',default=>'secondary'}; ?><span class="badge bg-<?= $sc ?>"><?= ucfirst(str_replace('_', ' ', $s)) ?></span></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3 text-muted">Assigned To</div>
                        <div class="col-md-9"><?= htmlspecialchars($r['assigned_name'] ?? 'Unassigned') ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3 text-muted">Created</div>
                        <div class="col-md-9"><?= date('d M Y, h:i A', strtotime($r['created_at'] ?? 'now')) ?></div>
                    </div>
                    <?php if ($r['completed_at'] ?? null): ?>
                    <div class="row mb-3">
                        <div class="col-md-3 text-muted">Completed At</div>
                        <div class="col-md-9"><?= date('d M Y, h:i A', strtotime($r['completed_at'])) ?></div>
                    </div>
                    <?php endif; ?>
                    <hr>
                    <h6>Description</h6>
                    <p class="mb-0"><?= nl2br(htmlspecialchars($r['description'] ?? 'No description provided')) ?></p>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="col-lg-4">
            <!-- Update Status -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-edit me-2"></i>Update Status</h5></div>
                <div class="card-body aps-cp-card-body">
                    <form method="POST" action="<?= BASE_URL ?>/admin/property-features/maintenance/update-status/<?= $r['id'] ?>">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <select name="status" class="form-select mb-3">
                            <option value="open" <?= ($r['status'] ?? '') === 'open' ? 'selected' : '' ?>>Open</option>
                            <option value="in_progress" <?= ($r['status'] ?? '') === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                            <option value="completed" <?= ($r['status'] ?? '') === 'completed' ? 'selected' : '' ?>>Completed</option>
                        </select>
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-save me-1"></i>Update Status</button>
                    </form>
                </div>
            </div>

            <!-- Assign To -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-user-cog me-2"></i>Assign To</h5></div>
                <div class="card-body aps-cp-card-body">
                    <form method="POST" action="<?= BASE_URL ?>/admin/property-features/maintenance/assign/<?= $r['id'] ?>">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <select name="assigned_to" class="form-select mb-3">
                            <option value="">-- Select Staff --</option>
                            <?php foreach ($staff as $s): ?>
                                <option value="<?= $s['id'] ?>" <?= (($r['assigned_to'] ?? 0) == $s['id']) ? 'selected' : '' ?>><?= htmlspecialchars($s['name'] ?? '') ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn btn-warning w-100"><i class="fas fa-user-check me-1"></i>Assign</button>
                    </form>
                </div>
            </div>

            <!-- Back -->
            <div class="mt-3">
                <a href="<?= BASE_URL ?>/admin/property-features/maintenance" class="btn btn-outline-secondary w-100"><i class="fas fa-arrow-left me-1"></i>Back to Maintenance</a>
            </div>
        </div>
    </div>
</div>
