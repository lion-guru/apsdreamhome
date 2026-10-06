<?php
/**
 * Shift Types Management
*/

$page_title = $page_title ?? 'Shift Types';
$shift_types = $shift_types ?? [];
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-clock me-2"></i>Shift Types</h1>
        <a href="<?= BASE_URL ?>/admin/shift-roster/create-shift-type" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Add Shift Type
        </a>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <?php if (empty($shift_types)): ?>
            <div class="text-center py-5">
                <i class="fas fa-clock fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No Shift Types Defined</h5>
                <p class="text-muted">Create your first shift type to get started</p>
                <a href="<?= BASE_URL ?>/admin/shift-roster/create-shift-type" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i> Create First Shift
                </a>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Code</th>
                            <th>Timing</th>
                            <th>Duration</th>
                            <th>Color</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($shift_types as $st): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($st['name']) ?></strong>
                                <?php if (!empty($st['description'])): ?>
                                <br><small class="text-muted"><?= htmlspecialchars($st['description']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td><code><?= htmlspecialchars($st['code']) ?></code></td>
                            <td>
                                <?= date('g:i A', strtotime($st['start_time'])) ?> - 
                                <?= date('g:i A', strtotime($st['end_time'])) ?>
                            </td>
                            <td><?= $st['duration_hours'] ?> hrs</td>
                            <td>
                                <span class="badge" style="background: <?= $st['color'] ?>"><?= $st['color'] ?></span>
                            </td>
                            <td>
                                <span class="badge bg-<?= $st['is_active'] ? 'success' : 'secondary' ?>">
                                    <?= $st['is_active'] ? 'Active' : 'Inactive' ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="#" class="btn btn-outline-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="#" class="btn btn-outline-danger" title="Delete" onclick="return confirm('Delete this shift type?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
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

<style>
.card { border-radius: 0.75rem; box-shadow: 0 2px 10px rgba(0,0,0,0.08); }
.table th { border-top: none; font-weight: 600; color: #495057; }
.badge { font-size: 0.75rem; }
</style>