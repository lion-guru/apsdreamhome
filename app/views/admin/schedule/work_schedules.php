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
