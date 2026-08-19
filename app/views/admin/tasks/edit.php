<?php $pageTitle = 'Edit Task'; ?>
<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title"><i class="fas fa-edit me-2"></i>Edit Task</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/tasks">Tasks</a></li>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/tasks/show/<?= $task['id'] ?? 0 ?>"><?= $task['title'] ?? 'Task' ?></a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <form method="POST" action="<?= BASE_URL ?>/admin/tasks/update/<?= $task['id'] ?? 0 ?>">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                <div class="row g-3">
                    <div class="col-md-8"><label class="form-label">Title <span class="text-danger">*</span></label><input type="text" name="title" class="form-control" value="<?= $task['title'] ?? '' ?>" required></div>
                    <div class="col-md-4"><label class="form-label">Priority</label><select name="priority" class="form-select"><option value="low" <?= ($task['priority'] ?? '') === 'low' ? 'selected' : '' ?>>Low</option><option value="medium" <?= ($task['priority'] ?? '') === 'medium' ? 'selected' : '' ?>>Medium</option><option value="high" <?= ($task['priority'] ?? '') === 'high' ? 'selected' : '' ?>>High</option></select></div>
                    <div class="col-md-6"><label class="form-label">Assigned To</label><select name="assigned_to" class="form-select"><option value="">Unassigned</option><?php foreach ($users as $u): ?><option value="<?= $u['id'] ?>" <?= ($task['assigned_to'] ?? '') == $u['id'] ? 'selected' : '' ?>><?= $u['name'] ?? $u['username'] ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-3"><label class="form-label">Due Date</label><input type="date" name="due_date" class="form-control" value="<?= $task['due_date'] ?? '' ?>"></div>
                    <div class="col-md-3"><label class="form-label">Status</label><select name="status" class="form-select"><option value="pending" <?= ($task['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option><option value="in_progress" <?= ($task['status'] ?? '') === 'in_progress' ? 'selected' : '' ?>>In Progress</option><option value="completed" <?= ($task['status'] ?? '') === 'completed' ? 'selected' : '' ?>>Completed</option></select></div>
                    <div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="3"><?= $task['description'] ?? '' ?></textarea></div>
                    <div class="col-12"><button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Update Task</button> <a href="<?= BASE_URL ?>/admin/tasks" class="btn btn-secondary">Cancel</a></div>
                </div>
            </form>
        </div>
    </div>
</div>
