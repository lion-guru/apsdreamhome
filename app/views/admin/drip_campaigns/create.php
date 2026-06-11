<?php
$page_title = $page_title ?? 'Create Drip Campaign';
$page_heading = $page_heading ?? 'Create Drip Campaign';
$content = $content ?? '';
ob_start();
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Create Drip Campaign</h2>
            <p class="text-muted mb-0">Set up an automated email sequence for lead nurturing</p>
        </div>
        <a href="<?= BASE_URL ?>/admin/drip-campaigns" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>

    <form method="POST" action="<?= BASE_URL ?>/admin/drip-campaigns/store" id="dripForm">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Campaign Details</h5>
            </div>
            <div class="card-body aps-cp-card-body">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label">Campaign Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required placeholder="e.g., Welcome Series, Property Inquiry Follow-up">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="draft">Draft</option>
                            <option value="active">Active</option>
                            <option value="paused">Paused</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Trigger Event</label>
                        <select name="trigger_event" class="form-select">
                            <option value="new_lead">New Lead Created</option>
                            <option value="property_inquiry">Property Inquiry</option>
                            <option value="site_visit_booked">Site Visit Booked</option>
                            <option value="manual">Manual Enrollment Only</option>
                        </select>
                        <small class="text-muted">When to auto-enroll leads into this campaign</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Description</label>
                        <input type="text" name="description" class="form-control" placeholder="Internal notes about this campaign">
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-envelope me-2"></i>Email Sequence</h5>
                <button type="button" class="btn btn-sm btn-outline-primary" id="addEmailBtn">
                    <i class="fas fa-plus me-1"></i> Add Email
                </button>
            </div>
            <div class="card-body aps-cp-card-body" id="emailsContainer">
                <p class="text-muted small mb-3">
                    <i class="fas fa-info-circle"></i> Add emails in order. Each can have its own delay and channel.
                    Available variables: <code>{{name}}</code>, <code>{{email}}</code>, <code>{{property_title}}</code>, <code>{{agent_name}}</code>, <code>{{phone}}</code>
                </p>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2">
            <a href="<?= BASE_URL ?>/admin/drip-campaigns" class="btn btn-light">Cancel</a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-1"></i> Create Campaign
            </button>
        </div>
    </form>
</div>

<script>
let emailCount = 0;
function addEmailRow() {
    const n = ++emailCount;
    const html = `
    <div class="email-row border rounded p-3 mb-3 bg-light" data-row="${n}">
        <div class="d-flex justify-content-between mb-2">
            <h6 class="mb-0">Email #${n}</h6>
            <button type="button" class="btn btn-sm btn-outline-danger remove-email" data-row="${n}">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="row g-2">
            <div class="col-md-6">
                <label class="form-label small">Subject</label>
                <input type="text" name="emails[${n}][subject]" class="form-control form-control-sm" required placeholder="Welcome to our property network">
            </div>
            <div class="col-md-2">
                <label class="form-label small">Delay Days</label>
                <input type="number" name="emails[${n}][delay_days]" class="form-control form-control-sm" value="0" min="0">
            </div>
            <div class="col-md-2">
                <label class="form-label small">Delay Hours</label>
                <input type="number" name="emails[${n}][delay_hours]" class="form-control form-control-sm" value="0" min="0">
            </div>
            <div class="col-md-2">
                <label class="form-label small">Channel</label>
                <select name="emails[${n}][channel]" class="form-select form-select-sm">
                    <option value="email">Email</option>
                    <option value="sms">SMS</option>
                    <option value="whatsapp">WhatsApp</option>
                </select>
            </div>
            <div class="col-12">
                <label class="form-label small">Body</label>
                <textarea name="emails[${n}][body]" class="form-control form-control-sm" rows="5" required placeholder="Hi {{name}},&#10;&#10;Thanks for your interest..."></textarea>
            </div>
        </div>
    </div>`;
    const div = document.createElement('div');
    div.innerHTML = html;
    document.getElementById('emailsContainer').appendChild(div.firstChild);
}
document.getElementById('addEmailBtn').addEventListener('click', addEmailRow);
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('remove-email') || e.target.closest('.remove-email')) {
        const btn = e.target.classList.contains('remove-email') ? e.target : e.target.closest('.remove-email');
        const row = btn.dataset.row;
        const el = document.querySelector(`.email-row[data-row="${row}"]`);
        if (el) el.remove();
    }
});
addEmailRow();
</script>
<?php
$content = ob_get_clean();
include APP_PATH . '/views/admin/layouts/unified.php';
