<?php
$page_title = $page_title ?? 'Create Campaign';
$page_heading = $page_heading ?? 'Create Marketing Campaign';
$content = $content ?? '';
$templates = $templates ?? [];
$audience_count = $audience_count ?? 0;
ob_start();
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Create Marketing Campaign</h2>
            <p class="text-muted mb-0">Design and schedule a new email, SMS, or WhatsApp campaign</p>
        </div>
        <a href="<?= BASE_URL ?>/admin/marketing-campaigns" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>

    <form method="POST" action="<?= BASE_URL ?>/admin/marketing-campaigns/store">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
        <div class="row g-3">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Campaign Details</h5>
                    </div>
                    <div class="card-body aps-cp-card-body">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label">Campaign Name *</label>
                                <input type="text" class="form-control" name="name" required placeholder="e.g. Diwali Property Sale 2026">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Channel Type *</label>
                                <select class="form-select" name="type" id="typeSelect" required onchange="updateFields()">
                                    <option value="email">📧 Email</option>
                                    <option value="sms">📱 SMS</option>
                                    <option value="whatsapp">💬 WhatsApp</option>
                                    <option value="push">🔔 Push Notification</option>
                                    <option value="multi">🌐 Multi-channel</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Status *</label>
                                <select class="form-select" name="status" required>
                                    <option value="draft">📝 Draft (don't send)</option>
                                    <option value="scheduled">⏰ Scheduled</option>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Description (internal)</label>
                                <textarea class="form-control" name="description" rows="2" placeholder="Internal notes about this campaign"></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Template (optional)</label>
                                <select class="form-select" name="template_id" id="templateSelect" onchange="loadTemplate()">
                                    <option value="">Custom content</option>
                                    <?php foreach ($templates as $t): ?>
                                                        <option value="<?= $t['id'] ?>" data-type="<?= $t['type'] ?>" data-subject="<?= htmlspecialchars($t['subject'] ?? '', ENT_QUOTES) ?>" data-body="<?= htmlspecialchars($t['body'], ENT_QUOTES) ?>"><?= htmlspecialchars($t['name'] ?? '') ?> (<?= ucfirst($t['type']) ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Scheduled Send Time</label>
                                <input type="datetime-local" class="form-control" name="scheduled_at">
                                <small class="text-muted">Leave blank to send immediately or save as draft</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Content</h5>
                    </div>
                    <div class="card-body aps-cp-card-body">
                        <div class="mb-3" id="subjectGroup">
                            <label class="form-label">Subject Line</label>
                            <input type="text" class="form-control" name="subject" id="subjectField" placeholder="Email subject line">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Message Body *</label>
                            <textarea class="form-control" name="content" id="contentField" rows="10" required placeholder="Your campaign message..."></textarea>
                            <small class="text-muted">Use <code>{{name}}</code>, <code>{{city}}</code>, <code>{{price}}</code>, etc. for personalization</small>
                        </div>
                        <div class="alert alert-info small mb-0">
                            <strong>Available variables:</strong> <code>{{name}}</code> <code>{{property_type}}</code> <code>{{city}}</code> <code>{{price}}</code> <code>{{location}}</code> <code>{{area}}</code> <code>{{link}}</code> <code>{{discount}}</code>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0"><i class="fas fa-users me-2"></i>Target Audience</h5>
                    </div>
                    <div class="card-body aps-cp-card-body">
                        <div class="mb-3">
                            <label class="form-label">Audience Size</label>
                            <input type="text" class="form-control" value="<?= number_format($audience_count) ?> recipients" disabled>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">User Role Filter</label>
                            <select class="form-select" name="audience_role">
                                <option value="">All users</option>
                                <option value="customer">Customers only</option>
                                <option value="associate">Associates</option>
                                <option value="agent">Agents</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">City Filter</label>
                            <input type="text" class="form-control" name="audience_city" placeholder="e.g. Gorakhpur">
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-lg w-100">
                    <i class="fas fa-save me-1"></i> Create Campaign
                </button>
            </div>
        </div>
    </form>
</div>

<script>
function loadTemplate() {
    const sel = document.getElementById('templateSelect');
    const opt = sel.options[sel.selectedIndex];
    if (!opt.value) return;
    document.getElementById('subjectField').value = opt.dataset.subject || '';
    document.getElementById('contentField').value = opt.dataset.body || '';
    document.getElementById('typeSelect').value = opt.dataset.type;
    updateFields();
}
function updateFields() {
    const type = document.getElementById('typeSelect').value;
    document.getElementById('subjectGroup').style.display = (type === 'email') ? 'block' : 'none';
}
updateFields();
</script>

<?php
$content = ob_get_clean();
include APP_PATH . '/views/layouts/admin.php';
