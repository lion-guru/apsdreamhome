<?php
$templates = $templates ?? [];
$stats = $stats ?? ['total' => 0, 'active' => 0, 'inactive' => 0, 'message_logs' => 0];
?>
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="fab fa-whatsapp me-2 text-success"></i>WhatsApp Template Manager</h4>
            <p class="text-muted mb-0">Create, edit, and manage WhatsApp Business API message templates</p>
        </div>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#templateModal" onclick="resetTemplateForm()">
            <i class="fas fa-plus me-1"></i>New Template
        </button>
    </div>

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-white-50 small">Total Templates</div>
                            <div class="fs-3 fw-bold"><?= $stats['total'] ?></div>
                        </div>
                        <i class="fab fa-whatsapp fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-white-50 small">Active</div>
                            <div class="fs-3 fw-bold"><?= $stats['active'] ?></div>
                        </div>
                        <i class="fas fa-check-circle fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-secondary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-white-50 small">Inactive</div>
                            <div class="fs-3 fw-bold"><?= $stats['inactive'] ?></div>
                        </div>
                        <i class="fas fa-pause-circle fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-white-50 small">Messages Sent</div>
                            <div class="fs-3 fw-bold"><?= number_format($stats['message_logs']) ?></div>
                        </div>
                        <i class="fas fa-paper-plane fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Merge Fields Help -->
    <div class="alert alert-light border mb-4">
        <h6 class="alert-heading"><i class="fas fa-code me-1"></i>Available Merge Fields</h6>
        <div class="d-flex flex-wrap gap-2">
            <code>{{name}}</code> <code>{{phone}}</code> <code>{{email}}</code> <code>{{city}}</code>
            <code>{{budget}}</code> <code>{{colony}}</code> <code>{{plot_size}}</code> <code>{{emi_amount}}</code>
            <code>{{booking_ref}}</code> <code>{{due_date}}</code> <code>{{amount}}</code> <code>{{date}}</code>
        </div>
    </div>

    <!-- Templates Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <?php if (empty($templates)): ?>
                <div class="text-center py-5">
                    <i class="fab fa-whatsapp fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No templates found</p>
                    <p class="text-muted small">Create your first template using the button above, or sync from Meta Cloud API</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Language</th>
                                <th>Content Preview</th>
                                <th>Status</th>
                                <th>Updated</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($templates as $t): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($t['name'] ?? '') ?></strong></td>
                                <td><span class="badge bg-info"><?= htmlspecialchars($t['category'] ?? 'general') ?></span></td>
                                <td><small><?= strtoupper(htmlspecialchars($t['language'] ?? 'en')) ?></small></td>
                                <td>
                                    <small class="text-muted d-inline-block" class="style-41350">
                                        <?= htmlspecialchars(mb_substr(strip_tags($t['content'] ?? ''), 0, 80)) ?>
                                    </small>
                                </td>
                                <td>
                                    <?php if (($t['is_active'] ?? 0) == 1): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td><small class="text-muted"><?= date('d M Y', strtotime($t['updated_at'] ?? $t['created_at'] ?? '')) ?></small></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary" title="Edit" onclick='editTemplate(<?= json_encode($t) ?>)'>
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form method="POST" action="<?= BASE_URL ?>/admin/tools/whatsapp-templates/<?= $t['id'] ?>/delete" class="style-71727" onsubmit="return confirm('Delete this template?')">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                            <button type="submit" class="btn btn-outline-danger" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Template Modal -->
<div class="modal fade" id="templateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="templateModalTitle"><i class="fab fa-whatsapp me-2"></i>New Template</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= BASE_URL ?>/admin/tools/whatsapp-templates/save">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <input type="hidden" name="template_id" id="templateId" value="">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Template Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="templateName" class="form-control" placeholder="e.g. booking_confirmation" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Category</label>
                            <select name="category" id="templateCategory" class="form-select">
                                <option value="MARKETING">Marketing</option>
                                <option value="UTILITY">Utility</option>
                                <option value="AUTHENTICATION">Authentication</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Language</label>
                            <select name="language" id="templateLanguage" class="form-select">
                                <option value="en">English</option>
                                <option value="hi">Hindi</option>
                                <option value="en_IN">English (India)</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Template Content <span class="text-danger">*</span></label>
                            <textarea name="content" id="templateContent" class="form-control font-monospace" rows="8" required placeholder="Hi {{name}}, this is APS Dream Home. Your booking reference is {{booking_ref}}..."></textarea>
                            <div class="form-text">Use {{field_name}} for merge fields. WhatsApp allows up to 1024 characters.</div>
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input type="checkbox" name="is_active" id="templateActive" class="form-check-input" value="1" checked>
                                <label class="form-check-label" for="templateActive">Active</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success"><i class="fas fa-save me-1"></i>Save Template</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function resetTemplateForm() {
    document.getElementById('templateId').value = '';
    document.getElementById('templateName').value = '';
    document.getElementById('templateContent').value = '';
    document.getElementById('templateCategory').value = 'MARKETING';
    document.getElementById('templateLanguage').value = 'en';
    document.getElementById('templateActive').checked = true;
    document.getElementById('templateModalTitle').innerHTML = '<i class="fab fa-whatsapp me-2"></i>New Template';
}

function editTemplate(template) {
    document.getElementById('templateId').value = template.id;
    document.getElementById('templateName').value = template.name;
    document.getElementById('templateContent').value = template.content || '';
    document.getElementById('templateCategory').value = template.category || 'MARKETING';
    document.getElementById('templateLanguage').value = template.language || 'en';
    document.getElementById('templateActive').checked = template.is_active == 1;
    document.getElementById('templateModalTitle').innerHTML = '<i class="fab fa-whatsapp me-2"></i>Edit: ' + template.name;
    new bootstrap.Modal(document.getElementById('templateModal')).show();
}
</script>
