<?php

/**
 * Email Templates Management
 */
$page_title = $page_title ?? 'Email Templates';
$templates = $templates ?? [];
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-1"><i class="fas fa-envelope me-2 text-primary"></i>Email Templates</h1>
                <p class="text-muted">Manage email templates for automated communications</p>
            </div>
            <div class="d-flex gap-2">
                <a href="<?= BASE_URL ?>/docs/COMMUNICATION_SETUP_GUIDE.md#-email-templates-setup" target="_blank" class="btn btn-outline-primary">
                    <i class="fas fa-book me-1"></i> View Setup Guide
                </a>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#templateModal">
                    <i class="fas fa-plus me-1"></i> New Template
                </button>
            </div>
        </div>
    </div>

    <?php if (empty($templates)): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="fas fa-envelope-open-text fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No templates created yet</h5>
                <p class="text-muted mb-3">Create your first email template to get started</p>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#templateModal">
                    <i class="fas fa-plus me-1"></i> Create Template
                </button>
            </div>
        </div>
    <?php else: ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Subject</th>
                                <th>Category</th>
                                <th>Variables</th>
                                <th>Status</th>
                                <th>Updated</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($templates as $t): ?>
                                <tr>
                                    <td class="fw-semibold"><?= htmlspecialchars($t['name'] ?? '') ?></td>
                                    <td><?= htmlspecialchars(mb_strimwidth($t['subject'], 0, 50, '...')) ?></td>
                                    <td>
                                        <span class="badge bg-secondary"><?= htmlspecialchars($t['category'] ?? 'general') ?></span>
                                    </td>
                                    <td>
                                        <code class="small"><?= htmlspecialchars($t['variables'] ?? '[]') ?></code>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= ($t['is_active'] ?? 0) ? 'success' : 'secondary' ?>">
                                            <?= ($t['is_active'] ?? 0) ? 'Active' : 'Inactive' ?>
                                        </span>
                                    </td>
                                    <td><?= date('d M Y', strtotime($t['updated_at'] ?? $t['created_at'])) ?></td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-primary" 
                                                    onclick="editTemplate(<?= htmlspecialchars(json_encode($t)) ?>)"
                                                    data-bs-toggle="modal" data-bs-target="#templateModal">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form method="POST" action="<?= BASE_URL ?>/admin/communication/email-templates/<?= $t['id'] ?>/delete" class="d-inline" onsubmit="return confirm('Delete this template?')">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                                <button type="submit" class="btn btn-outline-danger"><i class="fas fa-trash"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Template Modal -->
    <div class="modal fade" id="templateModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <form method="POST" action="<?= BASE_URL ?>/admin/communication/email-templates" id="templateForm">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="template_id" id="templateId">
                    
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitle"><i class="fas fa-plus me-1"></i> New Template</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Template Name *</label>
                                <input type="text" name="name" class="form-control" id="inputName" placeholder="welcome_customer" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Category</label>
                                <select name="category" class="form-select" id="inputCategory">
                                    <option value="general">General</option>
                                    <option value="welcome">Welcome</option>
                                    <option value="transactional">Transactional</option>
                                    <option value="promotional">Promotional</option>
                                    <option value="notification">Notification</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Subject *</label>
                                <input type="text" name="subject" class="form-control" id="inputSubject" placeholder="Welcome to APS Dream Home!" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">HTML Body</label>
                                <textarea name="body_html" class="form-control" id="inputBodyHtml" rows="10" placeholder="<h1>Welcome {{name}}!</h1><p>Thank you for joining us...</p>"></textarea>
                                <div class="form-text">Use {{variable_name}} for placeholders. Available: {{name}}, {{phone}}, {{email}}, {{lead_id}}, {{property_title}}, {{property_price}}, {{date}}, {{time}}</div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Text Body (fallback)</label>
                                <textarea name="body_text" class="form-control" id="inputBodyText" rows="5" placeholder="Welcome {{name}}! Thank you for joining us..."></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Variables (JSON)</label>
                                <textarea name="variables" class="form-control" id="inputVariables" rows="3" placeholder='["name", "phone", "email"]'>["name", "phone", "email"]</textarea>
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input type="checkbox" name="is_active" class="form-check-input" id="inputIsActive" checked>
                                    <label class="form-check-label" for="inputIsActive">Active</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Save Template
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function editTemplate(template) {
    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-edit me-1"></i> Edit Template';
    document.getElementById('templateId').value = template.id;
    document.getElementById('inputName').value = template.name;
    document.getElementById('inputCategory').value = template.category || 'general';
    document.getElementById('inputSubject').value = template.subject;
    document.getElementById('inputBodyHtml').value = template.body_html || '';
    document.getElementById('inputBodyText').value = template.body_text || '';
    document.getElementById('inputVariables').value = template.variables || '["name", "phone", "email"]';
    document.getElementById('inputIsActive').checked = template.is_active == 1;
}

document.getElementById('templateModal').addEventListener('hidden.bs.modal', function() {
    document.getElementById('templateForm').reset();
    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-plus me-1"></i> New Template';
    document.getElementById('templateId').value = '';
});
</script>