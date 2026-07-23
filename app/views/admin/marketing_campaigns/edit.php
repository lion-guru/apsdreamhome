<?php
$page_title = $page_title ?? 'Edit Campaign';
$page_heading = $page_heading ?? 'Edit Campaign';
$campaign = $campaign ?? [];
$templates = $templates ?? [];
$audience = $audience ?? [];
$base = defined('BASE_URL') ? BASE_URL : '/' . trim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><?= htmlspecialchars($page_heading) ?></h2>
            <p class="text-muted mb-0">Update campaign #<?= (int)($campaign['id'] ?? 0) ?> details</p>
        </div>
        <a href="<?= $base ?>/admin/marketing-campaigns" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back
        </a>
    </div>


    <div class="card border-0 shadow-sm">
        <div class="card-body aps-cp-card-body">
            <form method="POST" action="<?= $base ?>/admin/marketing-campaigns/<?= (int)($campaign['id'] ?? 0) ?>/update">
                <input type="hidden" name="id" value="<?= (int)($campaign['id'] ?? 0) ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label">Campaign Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($campaign['name'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Channel</label>
                        <select name="type" class="form-select">
                            <?php foreach (['email' => 'Email', 'sms' => 'SMS', 'whatsapp' => 'WhatsApp'] as $k => $v): ?>
                                <option value="<?= $k ?>" <?= ($campaign['type'] ?? '') === $k ? 'selected' : '' ?>><?= $v ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2"><?= htmlspecialchars($campaign['description'] ?? '') ?></textarea>
                    </div>
                    <?php if (($campaign['type'] ?? '') === 'email'): ?>
                    <div class="col-12">
                        <label class="form-label">Subject Line</label>
                        <input type="text" name="subject" class="form-control" value="<?= htmlspecialchars($campaign['subject'] ?? '') ?>">
                    </div>
                    <?php endif; ?>
                    <div class="col-12">
                        <label class="form-label">Message Body</label>
                        <textarea name="content" class="form-control" rows="10" required><?= htmlspecialchars($campaign['content'] ?? '') ?></textarea>
                        <small class="text-muted">Variables: <code>{{name}}</code> <code>{{first_name}}</code> <code>{{email}}</code> <code>{{phone}}</code> <code>{{unsubscribe_url}}</code></small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Schedule (optional)</label>
                        <input type="datetime-local" name="scheduled_at" class="form-control" value="<?= !empty($campaign['scheduled_at']) ? date('Y-m-d\TH:i', strtotime($campaign['scheduled_at'])) : '' ?>">
                    </div>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save</button>
                    <a href="<?= $base ?>/admin/marketing-campaigns/show/<?= (int)($campaign['id'] ?? 0) ?>" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
