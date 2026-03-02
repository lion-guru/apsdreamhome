<?php include APP_PATH . '/views/admin/layouts/header.php'; ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-plus me-2"></i>Create New Task</h2>
        <a href="<?php echo BASE_URL; ?>/admin/tasks" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to List
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="<?php echo BASE_URL; ?>/admin/tasks/store" method="POST">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label for="title" class="form-label">Task Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="title" class="form-control" required placeholder="Enter task title">
                    </div>

                    <div class="col-md-4">
                        <label for="priority" class="form-label">Priority</label>
                        <select name="priority" id="priority" class="form-select">
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                        </select>
                    </div>

                    <div class="col-md-12">
                        <label for="description" class="form-label">Description</label>
                        <textarea name="description" id="description" class="form-control" rows="4" placeholder="Enter task details"></textarea>
                    </div>

                    <div class="col-md-6">
                        <label for="assigned_to" class="form-label">Assign To</label>
                        <select name="assigned_to" id="assigned_to" class="form-select">
                            <option value="">-- Select User --</option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?php echo $user['id']; ?>">
                                    <?php echo htmlspecialchars($user['name']); ?> (<?php echo htmlspecialchars($user['email']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="due_date" class="form-label">Due Date</label>
                        <input type="date" name="due_date" id="due_date" class="form-control">
                    </div>

                    <div class="col-md-6">
                        <label for="related_type" class="form-label">Related To (Type)</label>
                        <select name="related_type" id="related_type" class="form-select">
                            <option value="">-- None --</option>
                            <option value="lead" <?php echo ($related_type == 'lead') ? 'selected' : ''; ?>>Lead</option>
                            <option value="customer" <?php echo ($related_type == 'customer') ? 'selected' : ''; ?>>Customer</option>
                            <option value="project" <?php echo ($related_type == 'project') ? 'selected' : ''; ?>>Project</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="related_id" class="form-label">Related ID</label>
                        <input type="number" name="related_id" id="related_id" class="form-control" value="<?php echo htmlspecialchars($related_id ?? ''); ?>" placeholder="Enter ID">
                    </div>

                    <div class="col-md-12">
                        <label for="notes" class="form-label">Additional Notes</label>
                        <textarea name="notes" id="notes" class="form-control" rows="3" placeholder="Any extra notes..."></textarea>
                    </div>

                    <div class="col-12 text-end mt-4">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-save me-1"></i> Create Task
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include APP_PATH . '/views/admin/layouts/footer.php'; ?>
