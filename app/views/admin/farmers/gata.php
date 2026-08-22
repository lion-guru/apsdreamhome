<?php
$page_title = $page_title ?? 'Gata Records';
$gataRecords = $gata_records ?? [];
?>
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4><i class="fas fa-map text-info me-2"></i> Gata Records</h4>
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addGataModal"><i class="fas fa-plus me-1"></i>Add Gata</button>
    </div>

    <?php if ($msg = \App\Core\Session::flash('success')): ?>
        <div class="alert alert-success alert-dismissible fade show"><?php echo e($msg); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
    <?php if ($msg = \App\Core\Session::flash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show"><?php echo e($msg); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-list me-2"></i>All Gata Records</h5></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr><th>Gata ID</th><th>Site</th><th>Gata No</th><th>Total Area</th><th>Available Area</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($gataRecords as $g): ?>
                        <tr>
                            <td><code><?php echo htmlspecialchars($g['gata_id'] ?? ''); ?></code></td>
                            <td><?php echo htmlspecialchars($g['site_name'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($g['gata_no'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($g['area'] ?? '0'); ?> sq.ft</td>
                            <td><?php echo htmlspecialchars($g['available_area'] ?? '0'); ?> sq.ft</td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($gataRecords)): ?>
                        <tr><td colspan="5" class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x d-block mb-2 text-muted" aria-hidden="true"></i>No gata records found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Gata Modal -->
<div class="modal fade" id="addGataModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="<?php echo BASE_URL; ?>/admin/farmers/gata/store">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                <div class="modal-header"><h5 class="modal-title">Add Gata Record</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Site ID</label>
                        <input type="number" name="site_id" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Gata No</label>
                        <input type="text" name="gata_no" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Total Area (sq.ft)</label>
                        <input type="number" step="0.01" name="area" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Available Area (sq.ft)</label>
                        <input type="number" step="0.01" name="available_area" class="form-control">
                        <small class="text-muted">Leave empty to use total area</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
