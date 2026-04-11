<!-- Page Header -->
<div class="mb-4">
    <a href="<?php echo BASE_URL; ?>/admin/leads" class="text-decoration-none text-muted">
        <i class="fas fa-arrow-left me-2"></i>Back to Leads
    </a>
    <h1 class="h3 mt-2 mb-1">Add New Lead</h1>
    <p class="text-muted">Create a new lead entry</p>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <form method="POST" action="<?php echo BASE_URL; ?>/admin/leads">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label fw-semibold">Email</label>
                            <input type="email" class="form-control" id="email" name="email">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label fw-semibold">Phone</label>
                            <input type="text" class="form-control" id="phone" name="phone">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="source_id" class="form-label fw-semibold">Source</label>
                            <select class="form-select" id="source_id" name="source_id">
                                <option value="">-- Select Source --</option>
                                <?php foreach ($sources as $source): ?>
                                    <option value="<?php echo $source['id']; ?>"><?php echo htmlspecialchars($source['name'] ?? ''); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label fw-semibold">Status</label>
                            <select class="form-select" id="status" name="status">
                                <?php foreach ($statuses as $status): ?>
                                    <option value="<?php echo $status['status_name']; ?>"><?php echo ucfirst($status['status_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="assigned_to" class="form-label fw-semibold">Assigned To</label>
                            <select class="form-select" id="assigned_to" name="assigned_to">
                                <option value="">-- Unassigned --</option>
                                <?php foreach ($assignees as $assignee): ?>
                                    <option value="<?php echo $assignee['id']; ?>"><?php echo htmlspecialchars($assignee['name'] ?? ''); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="message" class="form-label fw-semibold">Message</label>
                        <textarea class="form-control" id="message" name="message" rows="3" placeholder="Lead requirements or message..."></textarea>
                    </div>

                    <div class="d-flex gap-3">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Create Lead</button>
                        <a href="<?php echo BASE_URL; ?>/admin/leads" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>