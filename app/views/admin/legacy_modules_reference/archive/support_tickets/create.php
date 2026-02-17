<?php include APP_PATH . '/views/admin/layouts/header.php'; ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-plus me-2"></i>Create Support Ticket</h2>
        <a href="<?php echo BASE_URL; ?>/admin/support-tickets" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to List
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="<?php echo BASE_URL; ?>/admin/support-tickets/store" method="POST">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label for="subject" class="form-label">Subject <span class="text-danger">*</span></label>
                        <input type="text" name="subject" id="subject" class="form-control" required placeholder="Enter ticket subject">
                    </div>

                    <div class="col-md-4">
                        <label for="category" class="form-label">Category</label>
                        <select name="category" id="category" class="form-select">
                            <option value="General">General Inquiry</option>
                            <option value="Technical">Technical Support</option>
                            <option value="Billing">Billing & Payments</option>
                            <option value="Feature Request">Feature Request</option>
                            <option value="Bug Report">Bug Report</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="user_id" class="form-label">User (Requester ID)</label>
                        <!-- Assuming we can input ID for now, ideally a search select -->
                        <input type="number" name="user_id" id="user_id" class="form-control" placeholder="Enter User ID" required>
                        <small class="text-muted">Enter the ID of the user requesting support.</small>
                    </div>

                    <div class="col-md-6">
                        <label for="priority" class="form-label">Priority</label>
                        <select name="priority" id="priority" class="form-select">
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                        </select>
                    </div>

                    <div class="col-md-12">
                        <label for="message" class="form-label">Message / Description</label>
                        <textarea name="message" id="message" class="form-control" rows="5" required placeholder="Describe the issue..."></textarea>
                    </div>

                    <div class="col-md-6">
                        <label for="assigned_to" class="form-label">Assign To (Agent)</label>
                        <select name="assigned_to" id="assigned_to" class="form-select">
                            <option value="">-- Unassigned --</option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?php echo $user['id']; ?>">
                                    <?php echo htmlspecialchars($user['name']); ?> (<?php echo htmlspecialchars($user['email']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="internal_notes" class="form-label">Internal Notes (Admin Only)</label>
                        <textarea name="internal_notes" id="internal_notes" class="form-control" rows="2" placeholder="Notes for staff..."></textarea>
                    </div>

                    <div class="col-12 text-end mt-4">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-save me-1"></i> Create Ticket
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include APP_PATH . '/views/admin/layouts/footer.php'; ?>
