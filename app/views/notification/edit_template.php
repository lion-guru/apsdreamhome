
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-header">
                <h1 class="h2 mb-4">
                    <i class="fas fa-edit"></i> Edit Template
                </h1>
            </div>
            
            <div class="card aps-cp-card">
                <div class="card-body aps-cp-card-body">
                    <?php if (empty($template ?? null)): ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i> Template not found.
                        </div>
                    <?php else: ?>
                        <form method="POST" action="<?= BASE_URL ?>/admin/notification-management/templates/update">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                            <input type="hidden" name="id" value="<?= (int)($template['id'] ?? 0) ?>">
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Template Name</label>
                                    <input type="text" name="template_name" class="form-control" required value="<?= htmlspecialchars($template['template_name'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Channel</label>
                                    <select name="channel" class="form-select" required>
                                        <option value="email" <?= ($template['channel'] ?? '') === 'email' ? 'selected' : '' ?>>Email</option>
                                        <option value="sms" <?= ($template['channel'] ?? '') === 'sms' ? 'selected' : '' ?>>SMS</option>
                                        <option value="push" <?= ($template['channel'] ?? '') === 'push' ? 'selected' : '' ?>>Push Notification</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Subject</label>
                                <input type="text" name="subject" class="form-control" value="<?= htmlspecialchars($template['subject'] ?? '') ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Body</label>
                                <textarea name="body" class="form-control" rows="8" required><?= htmlspecialchars($template['body'] ?? '') ?></textarea>
                                <small class="text-muted">Use {{variable}} for dynamic content</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Variables (comma-separated)</label>
                                <input type="text" name="variables" class="form-control" value="<?= htmlspecialchars($template['variables'] ?? '') ?>">
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Update Template</button>
                                <a href="/admin/notification-management/templates" class="btn btn-outline-secondary">Cancel</a>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
