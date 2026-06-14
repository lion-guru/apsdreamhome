<?php
$page_title = $page_title ?? 'Create NPS Survey';
$page_heading = $page_heading ?? 'Create New Survey';
$content = $content ?? '';
ob_start();
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Create NPS Survey</h2>
            <p class="text-muted mb-0">Set up a new customer satisfaction survey</p>
        </div>
        <a href="<?= BASE_URL ?>/admin/nps" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>
    <form method="POST" action="<?= BASE_URL ?>/admin/nps/store">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
        <div class="card border-0 shadow-sm">
            <div class="card-body aps-cp-card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Survey Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" required placeholder="e.g., Post-Visit Satisfaction">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Description</label>
                        <input type="text" name="description" class="form-control" placeholder="Internal notes about this survey">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Main Question</label>
                        <input type="text" name="question_text" class="form-control" value="How likely are you to recommend us to a friend or colleague?" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Scale Minimum Label (0)</label>
                        <input type="text" name="scale_min_label" class="form-control" value="Not at all likely" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Scale Maximum Label (10)</label>
                        <input type="text" name="scale_max_label" class="form-control" value="Extremely likely" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Follow-up Question (Optional)</label>
                        <input type="text" name="follow_up_question" class="form-control" placeholder="What could we improve?">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        <select name="is_active" class="form-select">
                            <option value="1" selected>Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Send Immediately After Trigger</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="send_immediately" value="1">
                        </div>
                        <small class="text-muted">If checked, survey will be sent right after trigger event occurs</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Delay Days</label>
                        <input type="number" name="delay_days" class="form-control" min="0" placeholder="0 for no delay">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Delay Hours</label>
                        <input type="number" name="delay_hours" class="form-control" min="0" placeholder="0 for no delay">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Trigger Event</label>
                        <select name="trigger_event" class="form-select">
                            <option value="manual">Manual Send Only</option>
                            <option value="property_viewed">After Property Viewed</option>
                            <option value="inquiry_made">After Inquiry Made</option>
                            <option value="visit_completed">After Visit Completed</option>
                            <option value="lead_converted">After Lead Converted</option>
                            <option value="property_sold">After Property Sold</option>
                        </select>
                        <small class="text-muted">When to automatically send this survey</small>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-white text-end">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Create Survey</button>
            </div>
        </div>
    </form>
</div>
<?php
$content = ob_get_clean();
include APP_PATH . '/views/admin/layouts/admin.php';
