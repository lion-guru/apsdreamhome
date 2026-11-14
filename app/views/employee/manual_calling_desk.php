<?php
/**
 * Manual Calling Desk — Jio Fiber Free Calling & SMS
 * Uses tel: and sms: protocols to open phone dialer/SMS app
 * No API needed — uses Jio Fiber's free calling & SMS
 */
$page_title = $page_title ?? 'Manual Calling Desk — Jio Fiber Free Calling';
$leads = $leads ?? [];
$todayFollowups = $todayFollowups ?? [];
$recentCalls = $recentCalls ?? [];
?>

<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h4 class="mb-0">
                <i class="fas fa-phone-volume me-2 text-success"></i>Manual Calling Desk
                <span class="badge bg-success ms-2">Jio Fiber Free Calling</span>
            </h4>
            <div>
                <span class="text-muted small">
                    <i class="fas fa-info-circle me-1"></i>
                    Click-to-Call & SMS — uses your Jio Fiber phone (free, no API needed)
                </span>
            </div>
        </div>
    </div>
</div>

<div class="alert alert-info d-flex align-items-center">
    <i class="fas fa-lightbulb fa-2x me-3 text-warning"></i>
    <div>
        <strong>How it works:</strong> Click the green 📞 button → your phone dialer opens with the number → make the call (free via Jio Fiber) → after call, select outcome and click "Log Call". 
        Click the blue 📱 button → your SMS app opens with pre-filled message → send (free via Jio Fiber).
    </div>
</div>

<!-- Today's Followups -->
<?php if (!empty($todayFollowups)): ?>
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-warning">
            <div class="card-header bg-warning text-dark">
                <h2  class="fs-6 fw-semibold mb-0"><i class="fas fa-calendar-check me-1"></i>Today's Follow-ups (<?= count($todayFollowups) ?>)</h2>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Lead</th>
                                <th>Phone</th>
                                <th>Type</th>
                                <th>Due</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($todayFollowups as $f): ?>
                            <tr>
                                <td><?= htmlspecialchars($f['lead_name'] ?? 'Unknown') ?></td>
                                <td><?= htmlspecialchars($f['lead_phone'] ?? '') ?></td>
                                <td><span class="badge bg-info"><?= ucfirst($f['followup_type'] ?? 'call') ?></span></td>
                                <td><?= date('M d, H:i', strtotime($f['followup_date'])) ?></td>
                                <td>
                                    <a href="tel:<?= htmlspecialchars($f['lead_phone'] ?? '') ?>" class="btn btn-sm btn-success" title="Call (Jio Fiber Free)">
                                        <i class="fas fa-phone"></i>
                                    </a>
                                    <button class="btn btn-sm btn-primary sms-btn" 
                                            data-lead-id="<?= $f['lead_id'] ?>" 
                                            data-template="followup" 
                                            title="Send SMS (Jio Fiber Free)">
                                        <i class="fas fa-sms"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Leads to Call -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-users me-1"></i>Leads to Call (<?= count($leads) ?>)</h5>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-secondary" onclick="filterLeads('all')">All</button>
                    <button class="btn btn-outline-primary" onclick="filterLeads('new')">New</button>
                    <button class="btn btn-outline-warning" onclick="filterLeads('follow_up')">Follow-up</button>
                </div>
            </div>
            <div class="card-body p-0">
                <?php if (empty($leads)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                        <h5 class="text-muted">No Leads to Call!</h5>
                        <p class="text-muted">All leads have been contacted or are in progress.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="leadsTable">
                            <thead>
                                <tr>
                                    <th>Lead</th>
                                    <th>Phone</th>
                                    <th>Colony/Plot</th>
                                    <th>Status</th>
                                    <th>Interest</th>
                                    <th>Last Contact</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($leads as $lead): ?>
                                <tr data-status="<?= htmlspecialchars($lead['status']) ?>">
                                    <td>
                                        <strong><?= htmlspecialchars($lead['name'] ?? 'Unknown') ?></strong>
                                        <?php if (!empty($lead['email'])): ?>
                                            <br><small class="text-muted"><?= htmlspecialchars($lead['email']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <code><?= htmlspecialchars($lead['phone'] ?? 'N/A') ?></code>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($lead['location_preference'] ?? 'N/A') ?>
                                        <?php if (!empty($lead['property_interest'])): ?>
                                            <br><small class="text-muted"><?= htmlspecialchars($lead['property_interest']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= 
                                            $lead['status'] === 'new' ? 'primary' : 
                                            ($lead['status'] === 'qualified' ? 'success' : 
                                            ($lead['status'] === 'follow_up' ? 'warning' : 'secondary')) ?>">
                                            <?= ucfirst(str_replace('_', ' ', $lead['status'])) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">N/A</span>
                                    </td>
                                    <td>
                                        <small><?= $lead['created_at'] ? date('M d, H:i', strtotime($lead['created_at'])) : 'Never' ?></small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <!-- Click-to-Call (Jio Fiber Free) -->
                                            <a href="tel:<?= htmlspecialchars($lead['phone'] ?? '') ?>" 
                                               class="btn btn-success" title="Call via Jio Fiber (Free)">
                                                <i class="fas fa-phone"></i> Call
                                            </a>
                                            <!-- Click-to-SMS (Jio Fiber Free) -->
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-primary dropdown-toggle" 
                                                        data-bs-toggle="dropdown" title="Send SMS (Jio Fiber Free)">
                                                    <i class="fas fa-sms"></i> SMS
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item sms-template-btn" href="#" 
                                                           data-lead-id="<?= $lead['id'] ?>" data-template="site_visit">Site Visit Invite</a></li>
                                                    <li><a class="dropdown-item sms-template-btn" href="#" 
                                                           data-lead-id="<?= $lead['id'] ?>" data-template="emi_info">EMI Info</a></li>
                                                    <li><a class="dropdown-item sms-template-btn" href="#" 
                                                           data-lead-id="<?= $lead['id'] ?>" data-template="offer">Special Offer</a></li>
                                                    <li><a class="dropdown-item sms-template-btn" href="#" 
                                                           data-lead-id="<?= $lead['id'] ?>" data-template="followup">Follow-up</a></li>
                                                    <li><a class="dropdown-item sms-template-btn" href="#" 
                                                           data-lead-id="<?= $lead['id'] ?>" data-template="welcome">Welcome</a></li>
                                                </ul>
                                            </div>
                                            <!-- WhatsApp -->
                                            <a href="https://wa.me/91<?= preg_replace('/[^0-9]/', '', $lead['phone'] ?? '') ?>?text=<?= urlencode('Namaste! APS Dream Home se. Aapke liye special offer hai. Details ke liye call karein.') ?>" 
                                               target="_blank" class="btn btn-success" title="WhatsApp">
                                                <i class="fab fa-whatsapp"></i>
                                            </a>
                                            <!-- Log Call Outcome -->
                                            <button class="btn btn-outline-secondary log-call-btn" 
                                                    data-lead-id="<?= $lead['id'] ?>" 
                                                    data-lead-name="<?= htmlspecialchars($lead['name'] ?? '') ?>"
                                                    title="Log Call Outcome">
                                                <i class="fas fa-clipboard-check"></i>
                                            </button>
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
</div>

<!-- Recent Calls -->
<?php if (!empty($recentCalls)): ?>
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-history me-1"></i>Recent Calls (<?= count($recentCalls) ?>)</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>Lead</th>
                                <th>Phone</th>
                                <th>Outcome</th>
                                <th>Duration</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentCalls as $c): ?>
                            <tr>
                                <td><?= date('M d, H:i', strtotime($c['created_at'])) ?></td>
                                <td><?= htmlspecialchars($c['lead_name'] ?? 'Unknown') ?></td>
                                <td><?= htmlspecialchars($c['lead_phone'] ?? '') ?></td>
                                <td>
                                    <span class="badge bg-<?= 
                                        in_array($c['status'] ?? '', ['interested', 'connected']) ? 'success' : 
                                        (in_array($c['status'] ?? '', ['not_answered', 'busy', 'switched_off']) ? 'warning' : 'secondary') ?>">
                                        <?= ucfirst(str_replace('_', ' ', $c['status'] ?? 'unknown')) ?>
                                    </span>
                                </td>
                                <td><?= round($c['duration'] ?? 0) ?>s</td>
                                <td class="text-muted small"><?= htmlspecialchars(substr($c['notes'] ?? '', 0, 50)) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Log Call Modal -->
<div class="modal fade" id="logCallModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Log Call Outcome</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="logCallForm">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <input type="hidden" name="lead_id" id="log_lead_id">
                    <div class="mb-3">
                        <label class="form-label">Lead: <strong id="log_lead_name"></strong></label>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Outcome <span class="text-danger">*</span></label>
                        <select aria-label="Outcome" class="form-select" name="outcome" id="log_outcome" required>
                            <option value="">Select outcome...</option>
                            <option value="connected">Connected — Spoke with lead</option>
                            <option value="interested">Interested — Wants to proceed</option>
                            <option value="not_interested">Not Interested</option>
                            <option value="not_answered">Not Answered</option>
                            <option value="busy">Busy — Call later</option>
                            <option value="switched_off">Switched Off</option>
                            <option value="wrong_number">Wrong Number</option>
                            <option value="callback_requested">Callback Requested</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Duration (seconds)</label>
                        <input type="number" class="form-control" name="duration" id="log_duration" min="0" value="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea aria-label="Notes" class="form-control" name="notes" id="log_notes" rows="3" placeholder="Call notes..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="submitLogCall">Save Log</button>
            </div>
        </div>
    </div>
</div>

<!-- SMS Template Modal -->
<div class="modal fade" id="smsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Send SMS (Jio Fiber Free)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-success">
                    <i class="fas fa-info-circle me-2"></i>
                    SMS app will open with pre-filled message. Just hit Send — free via Jio Fiber!
                </div>
                <div class="mb-3">
                    <label class="form-label">To: <strong id="sms_phone"></strong></label>
                </div>
                <div class="mb-3">
                    <label class="form-label">Message:</label>
                    <textarea aria-label="Sms text" class="form-control" id="sms_text" rows="4" readonly></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="#" id="sms_send_link" class="btn btn-primary" target="_blank">
                    <i class="fas fa-sms me-1"></i>Open SMS App & Send
                </a>
            </div>
        </div>
    </div>
</div>

<script>
// Filter leads by status
function filterLeads(status) {
    const rows = document.querySelectorAll('#leadsTable tbody tr');
    rows.forEach(row => {
        if (status === 'all' || row.dataset.status === status) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

// SMS template buttons
document.querySelectorAll('.sms-template-btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        const leadId = this.dataset.leadId;
        const template = this.dataset.template;
        
        fetch('/employee/manual-calling/send-sms-template', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-Token': '<?= $_SESSION['csrf_token'] ?? '' ?>' },
            body: 'lead_id=' + encodeURIComponent(leadId) + '&template=' + encodeURIComponent(template)
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                document.getElementById('sms_phone').textContent = data.phone;
                document.getElementById('sms_text').value = data.sms_text;
                document.getElementById('sms_send_link').href = data.sms_link;
                new bootstrap.Modal(document.getElementById('smsModal')).show();
            } else {
                alert(data.message);
            }
        })
        .catch(() => alert('Failed to prepare SMS'));
    });
});

// Log call buttons
document.querySelectorAll('.log-call-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('log_lead_id').value = this.dataset.leadId;
        document.getElementById('log_lead_name').textContent = this.dataset.leadName;
        document.getElementById('log_outcome').value = '';
        document.getElementById('log_duration').value = '0';
        document.getElementById('log_notes').value = '';
        new bootstrap.Modal(document.getElementById('logCallModal')).show();
    });
});

// Submit log call
document.getElementById('submitLogCall').addEventListener('click', function() {
    const form = document.getElementById('logCallForm');
    const formData = new FormData(form);
    
    fetch('/employee/manual-calling/log-call', {
        method: 'POST',
        headers: { 'X-CSRF-Token': '<?= $_SESSION['csrf_token'] ?? '' ?>' },
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('logCallModal')).hide();
            location.reload();
        } else {
            alert(data.message);
        }
    })
    .catch(() => alert('Failed to log call'));
});
</script>
