<?php include APP_PATH . '/views/admin/layouts/header.php'; ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-edit me-2"></i>Edit Task: <?php echo htmlspecialchars($task['title']); ?></h2>
        <a href="<?php echo BASE_URL; ?>/admin/tasks" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to List
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="<?php echo BASE_URL; ?>/admin/tasks/update/<?php echo $task['id']; ?>" method="POST">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label for="title" class="form-label">Task Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="title" class="form-control" value="<?php echo htmlspecialchars($task['title']); ?>" required>
                    </div>

                    <div class="col-md-4">
                        <label for="priority" class="form-label">Priority</label>
                        <select name="priority" id="priority" class="form-select">
                            <option value="low" <?php echo ($task['priority'] == 'low') ? 'selected' : ''; ?>>Low</option>
                            <option value="medium" <?php echo ($task['priority'] == 'medium') ? 'selected' : ''; ?>>Medium</option>
                            <option value="high" <?php echo ($task['priority'] == 'high') ? 'selected' : ''; ?>>High</option>
                        </select>
                    </div>

                    <div class="col-md-12">
                        <label for="description" class="form-label">Description</label>
                        <textarea name="description" id="description" class="form-control" rows="4"><?php echo htmlspecialchars($task['description']); ?></textarea>
                    </div>

                    <div class="col-md-6">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="pending" <?php echo ($task['status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                            <option value="in_progress" <?php echo ($task['status'] == 'in_progress') ? 'selected' : ''; ?>>In Progress</option>
                            <option value="completed" <?php echo ($task['status'] == 'completed') ? 'selected' : ''; ?>>Completed</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="assigned_to" class="form-label">Assign To</label>
                        <select name="assigned_to" id="assigned_to" class="form-select">
                            <option value="">-- Select User --</option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?php echo $user['id']; ?>" <?php echo ($task['assigned_to'] == $user['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($user['name']); ?> (<?php echo htmlspecialchars($user['email']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="due_date" class="form-label">Due Date</label>
                        <input type="date" name="due_date" id="due_date" class="form-control" value="<?php echo $task['due_date']; ?>">
                    </div>

                    <div class="col-md-6">
                        <label for="related_type" class="form-label">Related To (Type)</label>
                        <select name="related_type" id="related_type" class="form-select">
                            <option value="">-- None --</option>
                            <option value="lead" <?php echo ($task['related_type'] == 'lead') ? 'selected' : ''; ?>>Lead</option>
                            <option value="customer" <?php echo ($task['related_type'] == 'customer') ? 'selected' : ''; ?>>Customer</option>
                            <option value="project" <?php echo ($task['related_type'] == 'project') ? 'selected' : ''; ?>>Project</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="related_id" class="form-label">Related ID</label>
                        <input type="number" name="related_id" id="related_id" class="form-control" value="<?php echo $task['related_id']; ?>">
                    </div>

                    <div class="col-md-12">
                        <label for="notes" class="form-label">Additional Notes</label>
                        <textarea name="notes" id="notes" class="form-control" rows="3"><?php echo htmlspecialchars($task['notes']); ?></textarea>
                    </div>

                    <div class="col-12 text-end mt-4">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-save me-1"></i> Update Task
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include APP_PATH . '/views/admin/layouts/footer.php'; ?>
