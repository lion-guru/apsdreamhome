<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-edit me-2"></i>Email Template Editor</h4>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#templateModal" onclick="resetForm()">
            <i class="fas fa-plus me-1"></i>New Template
        </button>
    </div>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success alert-dismissible fade show"><?php echo htmlspecialchars($success); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show"><?php echo htmlspecialchars($error); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>

    <div class="card aps-cp-card">
        <div class="card-body aps-cp-card-body">
            <?php if (!empty($templates)): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Code</th>
                                <th>Name</th>
                                <th>Subject</th>
                                <th>Last Updated</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($templates as $tpl): ?>
                                <tr>
                                    <td><code><?php echo htmlspecialchars($tpl['template_code'] ?? ''); ?></code></td>
                                    <td><?php echo htmlspecialchars($tpl['template_name'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($tpl['subject'] ?? ''); ?></td>
                                    <td><?php echo date('d M Y H:i', strtotime($tpl['updated_at'] ?? $tpl['created_at'] ?? 'now')); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" onclick="editTemplate(<?php echo htmlspecialchars(json_encode($tpl)); ?>)">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted text-center mb-0 py-4"><i class="fas fa-inbox me-2"></i>No templates found. Click "New Template" to create one.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Template Editor Modal -->
<div class="modal fade" id="templateModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="<?= BASE_URL ?>/admin/email-templates/save">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-pen me-2"></i>Edit Template</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Template Code <span class="text-danger">*</span></label>
                        <input type="text" name="template_code" id="tpl_code" class="form-control" required readonly>
                        <small class="text-muted">Unique identifier (auto-generated, read-only after creation)</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Template Name</label>
                        <input type="text" name="template_name" id="tpl_name" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Subject <span class="text-danger">*</span></label>
                        <input type="text" name="subject" id="tpl_subject" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Body HTML</label>
                        <textarea name="body_html" id="tpl_body" class="form-control" rows="12"></textarea>
                        <small class="text-muted">HTML email body. Use {{variable}} placeholders as needed.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save Template</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editTemplate(tpl) {
    document.getElementById('tpl_code').value = tpl.template_code || '';
    document.getElementById('tpl_name').value = tpl.template_name || '';
    document.getElementById('tpl_subject').value = tpl.subject || '';
    document.getElementById('tpl_body').value = tpl.body_html || tpl.html_content || '';
    new bootstrap.Modal(document.getElementById('templateModal')).show();
}

function resetForm() {
    document.getElementById('tpl_code').value = '';
    document.getElementById('tpl_code').removeAttribute('readonly');
    document.getElementById('tpl_name').value = '';
    document.getElementById('tpl_subject').value = '';
    document.getElementById('tpl_body').value = '';
}
</script>