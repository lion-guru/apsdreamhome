<?php
$page_title = $page_title ?? 'Shift Rotation - APS Dream Home';
$page_heading = $page_heading ?? 'Shift Rotation Management';
$rotations = $rotations ?? [];
$shift_types = $shift_types ?? [];
$departments = $departments ?? [];
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-sync-alt me-2"></i><?= htmlspecialchars($page_heading) ?></h4>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addRotationModal"><i class="fas fa-plus"></i> Add Rotation</button>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <?php if (empty($rotations)): ?>
                <div class="text-center py-5 text-muted"><i class="fas fa-sync fa-3x mb-3"></i><p>No rotation schedules defined yet.</p></div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>Name</th><th>Shift Type</th><th>Department</th><th>Start Date</th><th>End Date</th><th>Assigned</th><th>Actions</th></tr></thead>
                        <tbody>
                        <?php foreach ($rotations as $r): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($r['name'] ?? '') ?></strong></td>
                                <td><span class="badge" class="style-83574"><?= htmlspecialchars($r['shift_type_name'] ?? '') ?></span></td>
                                <td><?= htmlspecialchars($r['department'] ?? '') ?></td>
                                <td><?= htmlspecialchars($r['start_date'] ?? '') ?></td>
                                <td><?= htmlspecialchars($r['end_date'] ?? 'N/A') ?></td>
                                <td><?= $r['assigned_count'] ?? 0 ?></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></button>
                                    <form method="POST" action="<?= BASE_URL ?>/admin/schedule/rotations/<?= $r['id'] ?>/delete" class="d-inline" onsubmit="return confirm('Delete this rotation schedule?')">
                                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
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
