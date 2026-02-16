<?php include APP_PATH . '/views/admin/layouts/header.php'; ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-edit me-2"></i>Edit Ticket #<?php echo $ticket['id']; ?></h2>
        <a href="<?php echo BASE_URL; ?>/admin/support-tickets" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to List
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="<?php echo BASE_URL; ?>/admin/support-tickets/update/<?php echo $ticket['id']; ?>" method="POST">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label for="subject" class="form-label">Subject <span class="text-danger">*</span></label>
                        <input type="text" name="subject" id="subject" class="form-control" value="<?php echo htmlspecialchars($ticket['subject']); ?>" required>
                    </div>

                    <div class="col-md-4">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="open" <?php echo ($ticket['status'] == 'open') ? 'selected' : ''; ?>>Open</option>
                            <option value="pending" <?php echo ($ticket['status'] == 'pending') ? 'selected' : ''; ?>>Pending (Waiting for User)</option>
                            <option value="closed" <?php echo ($ticket['status'] == 'closed') ? 'selected' : ''; ?>>Closed</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="category" class="form-label">Category</label>
                        <select name="category" id="category" class="form-select">
                            <option value="General" <?php echo ($ticket['category'] == 'General') ? 'selected' : ''; ?>>General Inquiry</option>
                            <option value="Technical" <?php echo ($ticket['category'] == 'Technical') ? 'selected' : ''; ?>>Technical Support</option>
                            <option value="Billing" <?php echo ($ticket['category'] == 'Billing') ? 'selected' : ''; ?>>Billing & Payments</option>
                            <option value="Feature Request" <?php echo ($ticket['category'] == 'Feature Request') ? 'selected' : ''; ?>>Feature Request</option>
                            <option value="Bug Report" <?php echo ($ticket['category'] == 'Bug Report') ? 'selected' : ''; ?>>Bug Report</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="priority" class="form-label">Priority</label>
                        <select name="priority" id="priority" class="form-select">
                            <option value="low" <?php echo ($ticket['priority'] == 'low') ? 'selected' : ''; ?>>Low</option>
                            <option value="medium" <?php echo ($ticket['priority'] == 'medium') ? 'selected' : ''; ?>>Medium</option>
                            <option value="high" <?php echo ($ticket['priority'] == 'high') ? 'selected' : ''; ?>>High</option>
                        </select>
                    </div>

                    <div class="col-md-12">
                        <label for="message" class="form-label">Message / Description</label>
                        <textarea name="message" id="message" class="form-control" rows="5" required><?php echo htmlspecialchars($ticket['message']); ?></textarea>
                    </div>

                    <div class="col-md-6">
                        <label for="assigned_to" class="form-label">Assign To (Agent)</label>
                        <select name="assigned_to" id="assigned_to" class="form-select">
                            <option value="">-- Unassigned --</option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?php echo $user['id']; ?>" <?php echo ($ticket['assigned_to'] == $user['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($user['name']); ?> (<?php echo htmlspecialchars($user['email']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="satisfaction_rating" class="form-label">Satisfaction Rating (0-5)</label>
                        <input type="number" name="satisfaction_rating" id="satisfaction_rating" class="form-control" min="0" max="5" value="<?php echo htmlspecialchars($ticket['satisfaction_rating'] ?? ''); ?>">
                        <small class="text-muted">User rating upon closure.</small>
                    </div>

                    <div class="col-md-12">
                        <label for="internal_notes" class="form-label">Internal Notes (Admin Only)</label>
                        <textarea name="internal_notes" id="internal_notes" class="form-control" rows="3"><?php echo htmlspecialchars($ticket['internal_notes']); ?></textarea>
                    </div>

                    <div class="col-12 text-end mt-4">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-save me-1"></i> Update Ticket
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include APP_PATH . '/views/admin/layouts/footer.php'; ?>
