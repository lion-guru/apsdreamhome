<?php
$page_title = $page_title ?? 'Work Schedules - APS Dream Home';
$page_heading = $page_heading ?? 'Work Schedules';
$users = $users ?? [];
$departments = $departments ?? [];
$department = $department ?? '';
$day_names = $day_names ?? ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-briefcase me-2"></i><?= htmlspecialchars($page_heading ?? '') ?></h4>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addWorkScheduleModal"><i class="fas fa-plus"></i> Add Schedule</button>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <?php if (empty($users)): ?>
                <div class="text-center py-5 text-muted"><i class="fas fa-calendar fa-3x mb-3"></i><p>No work schedules defined yet.</p></div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>Employee</th><th>Department</th><th>Work Days</th><th>Shift</th><th>Status</th><th>Actions</th></tr></thead>
                        <tbody>
                        <?php foreach ($users as $emp): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($emp['name'] ?? '') ?></strong></td>
                                <td><?= htmlspecialchars($emp['department'] ?? '') ?></td>
                                <td>
                                    <?php if (!empty($emp['work_days'])): ?>
                                        <?php $days = explode(',', $emp['work_days']); foreach ($days as $d): ?>
                                            <span class="badge bg-info me-1"><?= htmlspecialchars($day_names[(int)$d] ?? $d) ?></span>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <span class="text-muted">Not set</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= !empty($emp['shift_start']) ? htmlspecialchars($emp['shift_start'] . ' - ' . $emp['shift_end'] ?? '') : '<span class="text-muted">--</span>' ?></td>
                                <td><?= !empty($emp['ws_active']) ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' ?></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary edit-schedule" data-id="<?= $emp['id'] ?>" data-wsid="<?= $emp['ws_id'] ?? 0 ?>"><i class="fas fa-edit"></i></button>
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

<!-- Add Work Schedule Modal -->
<div class="modal fade" id="addWorkScheduleModal" tabindex="-1" aria-labelledby="addWorkScheduleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addWorkScheduleModalLabel"><i class="fas fa-briefcase me-2"></i>Add Work Schedule</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="<?= BASE_URL ?>/admin/schedule/work-schedules/store">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <input type="hidden" name="ws_id" value="0">
                    <div class="mb-3">
                        <label class="form-label" for="wsEmployee">Employee *</label>
                        <select class="form-select" id="wsEmployee" name="employee_id" required>
                            <option value="">Select employee</option>
                            <?php foreach ($users as $u): ?>
                                <option value="<?= (int)$u['id'] ?>"><?= htmlspecialchars($u['name'] ?? ('ID ' . $u['id'])) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Work Days *</label>
                        <div class="d-flex flex-wrap gap-2">
                            <?php foreach ($day_names as $di => $dn): ?>
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="wsDay<?= $di ?>" name="work_days[]" value="<?= $di ?>" <?= $di >= 1 && $di <= 6 ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="wsDay<?= $di ?>"><?= htmlspecialchars(substr($dn, 0, 3)) ?></label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="wsStart">Shift Start *</label>
                            <input type="time" class="form-control" id="wsStart" name="shift_start" required value="09:00">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="wsEnd">Shift End *</label>
                            <input type="time" class="form-control" id="wsEnd" name="shift_end" required value="18:00">
                        </div>
                    </div>
                    <div class="form-check mt-3">
                        <input type="checkbox" class="form-check-input" id="wsActive" name="is_active" value="1" checked>
                        <label class="form-check-label" for="wsActive">Active</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-plus me-1"></i>Add Schedule</button>
                </div>
            </form>
        </div>
    </div>
</div>
