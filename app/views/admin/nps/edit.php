<?php
$page_title = $page_title ?? 'Edit NPS Survey';
$page_heading = $page_heading ?? 'Edit Survey';
$content = $content ?? '';
$survey = $survey ?? [];
ob_start();
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Edit Survey #<?= (int)($survey['id'] ?? 0) ?></h2>
            <p class="text-muted mb-0">Update customer satisfaction survey settings</p>
        </div>
        <a href="<?= BASE_URL ?>/admin/nps" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>
    <form method="POST" action="<?= BASE_URL ?>/admin/nps/update">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="id" value="<?= (int)($survey['id'] ?? 0) ?>">
        <div class="card border-0 shadow-sm">
            <div class="card-body aps-cp-card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Survey Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" required value="<?= htmlspecialchars($survey['title'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Description</label>
                        <input type="text" name="description" class="form-control" value="<?= htmlspecialchars($survey['description'] ?? '') ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Main Question</label>
                        <input type="text" name="question_text" class="form-control" value="<?= htmlspecialchars($survey['question_text'] ?? 'How likely are you to recommend us to a friend or colleague?') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Scale Minimum Label (0)</label>
                        <input type="text" name="scale_min_label" class="form-control" value="<?= htmlspecialchars($survey['scale_min_label'] ?? 'Not at all likely') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Scale Maximum Label (10)</label>
                        <input type="text" name="scale_max_label" class="form-control" value="<?= htmlspecialchars($survey['scale_max_label'] ?? 'Extremely likely') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Follow-up Question (Optional)</label>
                        <input type="text" name="follow_up_question" class="form-control" value="<?= htmlspecialchars($survey['follow_up_question'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        <select name="is_active" class="form-select">
                            <option value="1" <?= !empty($survey['is_active']) ? 'selected' : '' ?>>Active</option>
                            <option value="0" <?= empty($survey['is_active']) ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Send Immediately After Trigger</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="send_immediately" value="1" <?= !empty($survey['send_immediately']) ? 'checked' : '' ?>>
                        </div>
                        <small class="text-muted">If checked, survey will be sent right after trigger event occurs</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Delay Days</label>
                        <input type="number" name="delay_days" class="form-control" min="0" value="<?= (int)($survey['delay_days'] ?? 0) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Delay Hours</label>
                        <input type="number" name="delay_hours" class="form-control" min="0" value="<?= (int)($survey['delay_hours'] ?? 0) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Trigger Event</label>
                        <select name="trigger_event" class="form-select">
                            <?php
                            $triggers = [
                                'manual' => 'Manual Send Only',
                                'property_viewed' => 'After Property Viewed',
                                'inquiry_made' => 'After Inquiry Made',
                                'visit_completed' => 'After Visit Completed',
                                'lead_converted' => 'After Lead Converted',
                                'property_sold' => 'After Property Sold'
                            ];
                            $current = $survey['trigger_event'] ?? 'manual';
                            foreach ($triggers as $val => $label): ?>
                                <option value="<?= $val ?>" <?= $current === $val ? 'selected' : '' ?>><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">When to automatically send this survey</small>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-white text-end">
                <a href="<?= BASE_URL ?>/admin/nps/show/<?= (int)($survey['id'] ?? 0) ?>" class="btn btn-outline-secondary me-2">Cancel</a>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Update Survey</button>
            </div>
        </div>
    </form>
</div>
<?php
$content = ob_get_clean();
include APP_PATH . '/views/layouts/admin.php';
