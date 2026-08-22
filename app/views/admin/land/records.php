<?php
$page_title = $page_title ?? 'Land Records';
$land_records = $land_records ?? [];
?>
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1">Land Records</h1>
                    <p class="text-muted mb-0">Manage land registry records</p>
                </div>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addRecordModal">
                    <i class="fas fa-plus me-1"></i>Add Record
                </button>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0"><i class="fas fa-book me-2"></i>Land Registry Records</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">#</th>
                            <th>Title</th>
                            <th>Location</th>
                            <th>Area</th>
                            <th>Owner</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($land_records)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fas fa-book fa-3x d-block mb-3"></i>
                                No land records found
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($land_records as $i => $r): ?>
                        <tr>
                            <td class="ps-4"><?php echo $i + 1; ?></td>
                            <td><strong><?php echo $r['land_title'] ?? 'Record #' . $r['id']; ?></strong></td>
                            <td><?php echo $r['location'] ?? '-'; ?></td>
                            <td><?php echo number_format($r['area'] ?? 0, 2); ?> sqft</td>
                            <td><?php echo $r['owner_name'] ?? '-'; ?></td>
                            <td class="text-end pe-4">
                                <a href="<?php echo BASE_URL; ?>/admin/land/show/<?php echo e($r['id']); ?>" class="btn btn-sm btn-outline-info"><i class="fas fa-eye"></i></a>
                                <a href="<?php echo BASE_URL; ?>/admin/land/edit/<?php echo e($r['id']); ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Record Modal -->
<div class="modal fade" id="addRecordModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus me-2"></i>Add Land Record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="<?php echo BASE_URL; ?>/admin/land/records/store">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Land Title <span class="text-danger">*</span></label>
                        <input type="text" name="land_title" class="form-control" required placeholder="e.g. Plot 45, Suryoday Heights">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Location <span class="text-danger">*</span></label>
                        <input type="text" name="location" class="form-control" required placeholder="e.g. Gorakhpur">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Area (sqft) <span class="text-danger">*</span></label>
                        <input type="number" name="area" class="form-control" required step="0.01" min="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Owner Name</label>
                        <input type="text" name="owner_name" class="form-control" placeholder="e.g. Ram Singh">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save Record</button>
                </div>
            </form>
        </div>
    </div>
</div>
