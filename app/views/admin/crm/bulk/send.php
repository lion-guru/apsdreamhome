<?php $page_title = $page_title ?? 'Bulk Email/SMS'; ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h2 class="mb-1 fw-bold"><i class="fas fa-paper-plane me-2 text-primary"></i>Bulk Email/SMS</h2>
            <p class="text-muted mb-0">Send personalized messages to lead segments</p>
        </div>
        <a href="<?= BASE_URL ?>/admin/crm/templates" class="btn btn-outline-primary"><i class="fas fa-file-alt me-1"></i> Manage Templates</a>
    </div>


    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-paper-plane me-2"></i>Compose & Send</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= BASE_URL ?>/admin/crm/bulk-send/send" id="bulkForm">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Channel</label>
                                <select class="form-select" name="channel" id="bulkChannel">
                                    <option value="email">Email</option>
                                    <option value="sms">SMS</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Target Segment</label>
                                <select class="form-select" name="segment_id" id="bulkSegment">
                                    <option value="">All Active Leads</option>
                                    <?php foreach ($segments as $s): ?>
                                        <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name'] ?? '') ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Template (optional)</label>
                                <select class="form-select" id="bulkTemplate">
                                    <option value="">-- Custom --</option>
                                    <?php foreach ($email_templates as $t): ?>
                                        <option value="<?= $t['id'] ?>" data-subject="<?= htmlspecialchars($t['subject'] ?? '') ?>" data-type="email">ðŸ“§ <?= htmlspecialchars($t['name'] ?? '') ?></option>
                                    <?php endforeach; ?>
                                    <?php foreach ($sms_templates as $t): ?>
                                        <option value="<?= $t['id'] ?>" data-type="sms">📱 <?= htmlspecialchars($t['name'] ?? '') ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div id="emailFields">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Subject</label>
                                <input type="text" class="form-control" name="subject" id="bulkSubject" placeholder="e.g. New properties matching your interest, {{name}}!">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Message Body *</label>
                            <textarea class="form-control" name="body" id="bulkBody" rows="8" required placeholder="Dear {{name}}, ..."></textarea>
                        </div>

                        <div class="p-3 bg-light rounded mb-3">
                            <small class="text-muted"><strong>Merge fields:</strong> {{name}} {{phone}} {{email}} {{city}} {{budget}} — will be replaced per lead</small>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-primary" onclick="previewRecipients()"><i class="fas fa-eye me-1"></i> Preview Recipients</button>
                            <button type="submit" class="btn btn-success" data-aps-confirm="Send this bulk message to all matched leads?"><i class="fas fa-paper-plane me-1"></i> Send Now</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Preview Panel -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-eye me-2"></i>Message Preview</h6>
                </div>
                <div class="card-body">
                    <div id="messagePreview" class="p-3 bg-light rounded style-92119">
                        <em class="text-muted">Type your message to see a preview with sample data</em>
                    </div>
                </div>
            </div>

            <!-- Recipient Preview -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-users me-2"></i>Recipients</h6>
                </div>
                <div class="card-body" id="recipientPreview">
                    <p class="text-muted text-center mb-0">Click "Preview Recipients" to see who will receive this</p>
                </div>
            </div>

            <!-- Recent Campaigns -->
            <?php if (!empty($recent_campaigns)): ?>
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-history me-2"></i>Recent Campaigns</h6>
                </div>
                <div class="card-body p-0">
                    <?php foreach (array_slice($recent_campaigns, 0, 5) as $c): ?>
                        <div class="px-3 py-2 style-95886">
                            <div class="d-flex justify-content-between">
                                <span class="fw-semibold style-87981"><?= htmlspecialchars(mb_strimwidth($c['name'] ?? '', 0, 40, '...')) ?></span>
                                <span class="badge bg-<?= ($c['status'] ?? '') === 'sent' ? 'success' : 'secondary' ?>"><?= ucfirst($c['status'] ?? '') ?></span>
                            </div>
                            <small class="text-muted"><?= (int)($c['sent_count'] ?? 0) ?> sent &middot; <?= date('d M', strtotime($c['created_at'])) ?></small>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const channel = document.getElementById('bulkChannel');
    const emailFields = document.getElementById('emailFields');
    channel.addEventListener('change', function() {
        emailFields.style.display = this.value === 'sms' ? 'none' : 'block';
    });

    const template = document.getElementById('bulkTemplate');
    template.addEventListener('change', function() {
        const opt = this.options[this.selectedIndex];
        if (opt.value) {
            document.getElementById('bulkSubject').value = opt.dataset.subject || '';
        }
    });

    const bodyField = document.getElementById('bulkBody');
    bodyField.addEventListener('input', function() {
        let body = this.value || '<em class="text-muted">Type your message to see a preview</em>';
        body = body.replace(/\{\{name\}\}/g, '<strong>John Doe</strong>');
        body = body.replace(/\{\{phone\}\}/g, '98XX-XXX-XXXX');
        body = body.replace(/\{\{email\}\}/g, 'john@example.com');
        body = body.replace(/\{\{city\}\}/g, 'Your City');
        body = body.replace(/\{\{budget\}\}/g, '₹XX,XX,XXX');
        document.getElementById('messagePreview').innerHTML = body;
    });
});

function previewRecipients() {
    const form = document.getElementById('bulkForm');
    const formData = new FormData(form);
    formData.set('preview', '1');

    fetch('<?= BASE_URL ?>/admin/crm/bulk-send/preview', {
        method: 'POST',
        body: formData
    }).then(r => r.json()).then(data => {
        const panel = document.getElementById('recipientPreview');
        .catch(err => console.error('Request failed:', err));
        if (data.total > 0) {
            let html = '<div class="mb-2"><span class="badge bg-primary fs-6">' + data.total + '</span> leads will receive this message</div>';
            data.leads.forEach(l => {
                html += '<div class="d-flex align-items-center gap-2 py-1 style-95886">';
                html += '<div class="style-54603">' + (l.name || 'N')[0].toUpperCase() + '</div>';
                html += '<div class="style-87981"><strong>' + (l.name || '') + '</strong><br><small class="text-muted">' + (l.phone || '') + '</small></div>';
                html += '</div>';
            });
            if (data.sample > 20) html += '<small class="text-muted mt-2 d-block">Showing 20 of ' + data.total + '</small>';
            panel.innerHTML = html;
        } else {
            panel.innerHTML = '<p class="text-muted text-center mb-0">No leads match this segment</p>';
        }
    });
}
</script>
