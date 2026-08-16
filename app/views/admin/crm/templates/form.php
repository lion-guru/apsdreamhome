<?php
$page_title = $page_title ?? 'Create Template';
$template = $template ?? null;
$isEdit = !empty($template['id']);
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1 fw-bold"><i class="fas fa-<?= $isEdit ? 'edit' : 'plus-circle' ?> me-2 text-primary"></i><?= $isEdit ? 'Edit' : 'Create' ?> Template</h2>
            <p class="text-muted mb-0">Create a reusable email or SMS template with merge fields</p>
        </div>
        <a href="<?= BASE_URL ?>/admin/crm/templates" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i> Back</a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form method="POST" action="<?= BASE_URL ?>/admin/crm/templates/<?= $isEdit ? $template['id'] . '/update' : 'store' ?>">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Template Name *</label>
                                <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($template['name'] ?? '') ?>" required placeholder="e.g. Welcome Lead Email">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Type</label>
                                <select class="form-select" name="type" id="templateType">
                                    <option value="email" <?= ($template['type'] ?? 'email') === 'email' ? 'selected' : '' ?>>Email</option>
                                    <option value="sms" <?= ($template['type'] ?? '') === 'sms' ? 'selected' : '' ?>>SMS</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Category</label>
                                <select class="form-select" name="category">
                                    <option value="general" <?= ($template['category'] ?? '') === 'general' ? 'selected' : '' ?>>General</option>
                                    <option value="follow_up" <?= ($template['category'] ?? '') === 'follow_up' ? 'selected' : '' ?>>Follow-up</option>
                                    <option value="proposal" <?= ($template['category'] ?? '') === 'proposal' ? 'selected' : '' ?>>Proposal</option>
                                    <option value="welcome" <?= ($template['category'] ?? '') === 'welcome' ? 'selected' : '' ?>>Welcome</option>
                                    <option value="promotion" <?= ($template['category'] ?? '') === 'promotion' ? 'selected' : '' ?>>Promotion</option>
                                    <option value="nurture" <?= ($template['category'] ?? '') === 'nurture' ? 'selected' : '' ?>>Nurture</option>
                                    <option value="transactional" <?= ($template['category'] ?? '') === 'transactional' ? 'selected' : '' ?>>Transactional</option>
                                </select>
                            </div>
                        </div>

                        <div id="emailFields">
                            <div class="mt-3">
                                <label class="form-label fw-bold">Subject</label>
                                <input type="text" class="form-control" name="subject" value="<?= htmlspecialchars($template['subject'] ?? '') ?>" placeholder="e.g. Welcome to APS Dream Home, {{name}}!">
                            </div>
                        </div>

                        <div class="mt-3">
                            <label class="form-label fw-bold">Body *</label>
                            <textarea class="form-control" name="body" rows="12" required placeholder="Dear {{name}}, ..."><?= htmlspecialchars($template['body'] ?? '') ?></textarea>
                        </div>

                        <div class="mt-3 p-3 bg-light rounded">
                            <h6 class="fw-bold text-muted mb-2"><i class="fas fa-code me-1"></i> Available Merge Fields</h6>
                            <div class="d-flex flex-wrap gap-2">
                                <code class="bg-white px-2 py-1 rounded border">{{name}}</code>
                                <code class="bg-white px-2 py-1 rounded border">{{phone}}</code>
                                <code class="bg-white px-2 py-1 rounded border">{{email}}</code>
                                <code class="bg-white px-2 py-1 rounded border">{{city}}</code>
                                <code class="bg-white px-2 py-1 rounded border">{{budget}}</code>
                                <code class="bg-white px-2 py-1 rounded border">{{source}}</code>
                                <code class="bg-white px-2 py-1 rounded border">{{assigned_to}}</code>
                            </div>
                            <small class="text-muted mt-2 d-block">These will be replaced with actual lead data when sending.</small>
                        </div>

                        <div class="mt-4 d-flex gap-2">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> <?= $isEdit ? 'Update' : 'Create' ?> Template</button>
                            <a href="<?= BASE_URL ?>/admin/crm/templates" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="fw-bold"><i class="fas fa-eye me-1"></i> Preview</h6>
                    <div id="templatePreview" class="p-3 bg-light rounded" class="style-64467">
                        <em class="text-muted">Start typing to see preview...</em>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('templateType');
    const emailFields = document.getElementById('emailFields');
    typeSelect.addEventListener('change', function() {
        emailFields.style.display = this.value === 'sms' ? 'none' : 'block';
    });

    const bodyField = document.querySelector('textarea[name="body"]');
    const nameField = document.querySelector('input[name="name"]');
    const subjectField = document.querySelector('input[name="subject"]');
    const preview = document.getElementById('templatePreview');

    function updatePreview() {
        let body = bodyField.value || '<em class="text-muted">Start typing to see preview...</em>';
        body = body.replace(/\{\{name\}\}/g, '<strong>John Doe</strong>');
        body = body.replace(/\{\{phone\}\}/g, '98XX-XXX-XXXX');
        body = body.replace(/\{\{email\}\}/g, 'john@example.com');
        body = body.replace(/\{\{city\}\}/g, 'Your City');
        body = body.replace(/\{\{budget\}\}/g, '₹XX,XX,XXX');
        body = body.replace(/\{\{source\}\}/g, 'Channel');
        preview.innerHTML = body;
    }
    if (bodyField) bodyField.addEventListener('input', updatePreview);
    if (bodyField) updatePreview();
});
</script>
