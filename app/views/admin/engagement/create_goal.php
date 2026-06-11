<?php
$pageTitle = $pageTitle ?? 'Create Goal';
$base = $base ?? (defined('BASE_URL') ? BASE_URL : '/apsdreamhome');
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-plus-circle me-2 text-success"></i>Create Engagement Goal</h1>
        <a href="<?= $base ?>/admin/engagement/goals" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary">Goal Details</h6></div>
                <div class="card-body aps-cp-card-body">
                    <form method="POST" action="<?= $base ?>/admin/engagement/create-goal">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="mb-3">
                            <label class="form-label">Goal Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="e.g., Increase Property Views" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Describe what this goal aims to achieve"></textarea>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Target Value <span class="text-danger">*</span></label>
                                <input type="number" name="target_value" class="form-control" placeholder="e.g., 1000" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Unit</label>
                                <select name="unit" class="form-select">
                                    <option value="count">Count</option>
                                    <option value="percentage">Percentage</option>
                                    <option value="amount">Amount (₹)</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Deadline <span class="text-danger">*</span></label>
                                <input type="date" name="deadline" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Assigned Team</label>
                                <select name="assigned_team" class="form-select">
                                    <option value="">Select Team...</option>
                                    <option value="sales">Sales</option>
                                    <option value="marketing">Marketing</option>
                                    <option value="support">Support</option>
                                    <option value="all">All Teams</option>
                                </select>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success"><i class="fas fa-save me-1"></i>Create Goal</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
