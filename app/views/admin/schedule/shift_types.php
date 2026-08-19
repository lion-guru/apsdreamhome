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
                                <td><span class="badge" class="style-29995"><?= htmlspecialchars($st['name'] ?? '') ?></span></td>
                                <td><?= htmlspecialchars($st['code'] ?? '') ?></td>
                                <td><?= htmlspecialchars($st['start_time'] ?? '') ?>-<?= htmlspecialchars($st['end_time'] ?? '') ?></td>
                                <td><?= htmlspecialchars($st['duration_hours'] ?? '') ?>h</td>
                                <td><?= !empty($st['is_overnight']) ? 'Yes' : 'No' ?></td>
                                <td><?= $st['assigned_count'] ?? 0 ?></td>
                                <td><?= !empty($st['is_active']) ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' ?></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary edit-shift" data-id="<?= $st['id'] ?>"><i class="fas fa-edit"></i></button>
                                    <form method="POST" action="<?= BASE_URL ?>/admin/schedule/shift-types/<?= $st['id'] ?>/delete" class="d-inline" data-aps-confirm="Delete this shift type?">
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
