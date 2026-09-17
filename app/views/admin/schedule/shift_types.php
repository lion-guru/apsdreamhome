<?php
$page_title = $page_title ?? 'Shift Types - APS Dream Home';
$page_heading = $page_heading ?? 'Shift Types';
$shift_types = $shift_types ?? [];
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-clock me-2"></i><?= htmlspecialchars($page_heading ?? '') ?></h4>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addShiftTypeModal"><i class="fas fa-plus"></i> Add Shift Type</button>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <?php if (empty($shift_types)): ?>
                <div class="text-center py-5 text-muted"><i class="fas fa-clock fa-3x mb-3"></i><p>No shift types defined yet.</p></div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>Name</th><th>Code</th><th>Time</th><th>Duration</th><th>Overnight</th><th>Assigned</th><th>Status</th><th>Actions</th></tr></thead>
                        <tbody>
                        <?php foreach ($shift_types as $st): ?>
                            <tr>
                                <td><span class="badge"><?= htmlspecialchars($st['name'] ?? '') ?></span></td>
                                <td><?= htmlspecialchars($st['code'] ?? '') ?></td>
                                <td><?= htmlspecialchars($st['start_time'] ?? '') ?>-<?= htmlspecialchars($st['end_time'] ?? '') ?></td>
                                <td><?= htmlspecialchars($st['duration_hours'] ?? '') ?>h</td>
                                <td><?= !empty($st['is_overnight']) ? 'Yes' : 'No' ?></td>
                                <td><?= $st['assigned_count'] ?? 0 ?></td>
                                <td><?= !empty($st['is_active']) ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' ?></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary edit-shift" data-id="<?= $st['id'] ?>"><i class="fas fa-edit"></i></button>
                                    <form method="POST" action="<?= BASE_URL ?>/admin/schedule/shift-types/delete/<?= $st['id'] ?>" class="d-inline" data-aps-confirm="Delete this shift type?">
                                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" aria-label="Delete"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Add Shift Type Modal -->
<div class="modal fade" id="addShiftTypeModal" tabindex="-1" aria-labelledby="addShiftTypeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addShiftTypeModalLabel"><i class="fas fa-clock me-2"></i>Add Shift Type</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="<?= BASE_URL ?>/admin/schedule/shift-types/store">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label" for="stName">Name *</label>
                            <input type="text" class="form-control" id="stName" name="name" required maxlength="100" placeholder="e.g. Morning Shift">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="stCode">Code *</label>
                            <input type="text" class="form-control" id="stCode" name="code" required maxlength="20" placeholder="e.g. MOR">
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="stDesc">Description</label>
                            <input type="text" class="form-control" id="stDesc" name="description" maxlength="255">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="stStart">Start Time *</label>
                            <input type="time" class="form-control" id="stStart" name="start_time" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="stEnd">End Time *</label>
                            <input type="time" class="form-control" id="stEnd" name="end_time" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="stDuration">Duration (hrs)</label>
                            <input type="number" class="form-control" id="stDuration" name="duration_hours" min="0" max="24" step="0.5" value="8">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="stBreak">Break (mins)</label>
                            <input type="number" class="form-control" id="stBreak" name="break_duration" min="0" max="480" value="60">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="stColor">Color</label>
                            <input type="color" class="form-control form-control-color w-100" id="stColor" name="color" value="#007bff">
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="stOvernight" name="is_overnight" value="1">
                                <label class="form-check-label" for="stOvernight">Overnight shift (spans midnight)</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-plus me-1"></i>Add Shift Type</button>
                </div>
            </form>
        </div>
    </div>
</div>
