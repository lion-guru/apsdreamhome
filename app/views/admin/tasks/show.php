<?php $pageTitle = 'Task Details'; ?>
<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title"><i class="fas fa-tasks me-2"></i>Task Details</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/admin/tasks">Tasks</a></li>
                    <li class="breadcrumb-item active"><?= $task['title'] ?? 'Task' ?></li>
                </ul>
            </div>
            <div class="col-auto">
                <a href="/admin/tasks/edit/<?= $task['id'] ?? 0 ?>" class="btn btn-primary btn-sm"><i class="fas fa-edit me-1"></i>Edit</a>
                <a href="/admin/tasks" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
            </div>
        </div>
    </div>
    <?php if (empty($task)): ?>
        <div class="text-center py-5 text-muted"><i class="fas fa-tasks fa-4x d-block mb-3"></i><h5>Task not found</h5></div>
    <?php else: ?>
    <div class="row g-4">
        <div class="col-md-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center"><h5 class="mb-0"><?= $task['title'] ?></h5> <span class="badge bg-<?= ($task['status'] ?? 'pending') === 'completed' ? 'success' : (($task['status'] ?? 'pending') === 'in_progress' ? 'info' : 'warning') ?>-subtle text-<?= ($task['status'] ?? 'pending') === 'completed' ? 'success' : (($task['status'] ?? 'pending') === 'in_progress' ? 'info' : 'warning') ?> rounded-pill px-3"><?= ucfirst(str_replace('_', ' ', $task['status'] ?? 'Pending')) ?></span></div>
                <div class="card-body aps-cp-card-body"><p><?= nl2br($task['description'] ?? 'No description') ?></p></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Details</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="mb-3"><small class="text-muted d-block">Priority</small><span class="badge bg-<?= ($task['priority'] ?? 'medium') === 'high' ? 'danger' : (($task['priority'] ?? 'medium') === 'medium' ? 'warning' : 'info') ?>-subtle text-<?= ($task['priority'] ?? 'medium') === 'high' ? 'danger' : (($task['priority'] ?? 'medium') === 'medium' ? 'warning' : 'info') ?> rounded-pill px-3"><?= ucfirst($task['priority'] ?? 'Medium') ?></span></div>
                    <div class="mb-3"><small class="text-muted d-block">Assigned To</small><strong><?= $task['assignee_name'] ?? 'Unassigned' ?></strong></div>
                    <div class="mb-3"><small class="text-muted d-block">Due Date</small><strong><?= $task['due_date'] ? date('d M Y', strtotime($task['due_date'])) : 'No due date' ?></strong></div>
                    <div class="mb-3"><small class="text-muted d-block">Created By</small><?= $task['creator_name'] ?? 'System' ?></div>
                    <div><small class="text-muted d-block">Created</small><?= date('d M Y H:i', strtotime($task['created_at'] ?? 'now')) ?></div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
