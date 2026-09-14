<?php
$page_title = $page_title ?? 'Site Visits Management';
$currentPage = $currentPage ?? 'site-visits';
$visits = $visits ?? [];
$stats = $stats ?? ['total'=>0,'today'=>0,'upcoming'=>0,'completed'=>0,'cancelled'=>0];
$active_tab = $active_tab ?? 'all';
$search = $search ?? '';

$statusMap = [
    'scheduled' => ['color'=>'primary','icon'=>'fa-calendar-check'],
    'confirmed' => ['color'=>'info','icon'=>'fa-user-check'],
    'rescheduled' => ['color'=>'warning','icon'=>'fa-calendar-alt'],
    'completed' => ['color'=>'success','icon'=>'fa-check-circle'],
    'interested' => ['color'=>'success','icon'=>'fa-thumbs-up'],
    'token_booked' => ['color'=>'success','icon'=>'fa-ticket-alt'],
    'not_interested' => ['color'=>'dark','icon'=>'fa-thumbs-down'],
    'cancelled' => ['color'=>'secondary','icon'=>'fa-times-circle'],
    'in_progress' => ['color'=>'info','icon'=>'fa-spinner'],
    'no_show' => ['color'=>'danger','icon'=>'fa-user-slash'],
];
$executives = $executives ?? [];
?>

<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="fas fa-map-marker-alt text-primary me-2"></i>Site Visits Management</h4>
            <small class="text-muted">View and manage all property site visits</small>
        </div>
    </div>

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <?php foreach (['all'=>'Total','today'=>'Today','upcoming'=>'Upcoming','completed'=>'Completed','cancelled'=>'Cancelled'] as $tKey => $tLabel):
            $tColors = ['all'=>'primary','today'=>'warning','upcoming'=>'info','completed'=>'success','cancelled'=>'secondary'];
        ?>
        <div class="col">
            <a href="?tab=<?= $tKey ?>" class="card border-0 shadow-sm text-decoration-none <?= $active_tab === $tKey ? 'border-' . $tColors[$tKey] : '' ?>">
                <div class="card-body p-3 text-center">
                    <div class="fs-3 fw-bold text-<?= $tColors[$tKey] ?>"><?= $stats[$tKey === 'all' ? 'total' : $tKey] ?? 0 ?></div>
                    <div class="small text-muted"><?= $tLabel ?></div>
                </div>
            </a>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Search -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <form class="d-flex gap-2" method="GET">
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" class="form-control" name="q" placeholder="Search name, phone, lead..." value="<?= htmlspecialchars($search ?? '') ?>">
                </div>
                <input type="hidden" name="tab" value="<?= htmlspecialchars($active_tab ?? '') ?>">
                <button type="submit" class="btn btn-primary btn-sm">Search</button>
                <?php if ($search): ?>
                    <a href="?tab=<?= $active_tab ?>" class="btn btn-outline-secondary btn-sm"><i class="fas fa-times"></i></a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Visits Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <?php if (empty($visits)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-map-marker-alt fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No site visits found</h5>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Visitor</th>
                                <th>Date & Time</th>
                                <th>Lead</th>
                                <th>Associate</th>
                                <th>Status</th>
                                <th>Rating</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($visits as $v):
                                $sv = $statusMap[$v['status']] ?? ['color'=>'secondary','icon'=>'fa-circle'];
                                $isToday = ($v['visit_date'] === date('Y-m-d'));
                                $isPast = strtotime($v['visit_date']) < time();
                            ?>
                            <tr class="<?= $isToday ? 'table-light' : '' ?>">
                                <td>
                                    <strong><?= htmlspecialchars($v['visitor_name'] ?? '') ?></strong>
                                    <br><small class="text-muted"><i class="fas fa-phone me-1"></i><?= htmlspecialchars($v['visitor_phone'] ?? '') ?></small>
                                    <?php if (!empty($v['notes'])): ?>
                                        <br><small class="text-muted" title="<?= htmlspecialchars($v['notes'] ?? '') ?>"><?= htmlspecialchars(mb_substr($v['notes'] ?? '', 0, 50)) ?>...</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?= $v['visit_date'] ? date('d M Y', strtotime($v['visit_date'])) : '—' ?></strong>
                                    <br><small class="text-muted"><?= $v['visit_time'] ? date('h:i A', strtotime($v['visit_time'])) : '—' ?></small>
                                    <?php if ($isToday): ?><span class="badge bg-primary ms-1">Today</span><?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($v['lead_name'])): ?>
                                        <span class="badge bg-light text-dark"><?= htmlspecialchars($v['lead_name'] ?? '') ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($v['associate_name'])): ?>
                                        <?= htmlspecialchars($v['associate_name'] ?? '') ?>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <select class="form-select form-select-sm status-select" data-id="<?= $v['id'] ?>" >
                                        <?php foreach ($statusMap as $sKey => $sInfo): ?>
                                            <option value="<?= $sKey ?>" <?= $v['status'] === $sKey ? 'selected' : '' ?>><?= ucfirst($sKey) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td>
                                    <?php if (!empty($v['rating'])): ?>
                                        <span ><?php for ($i=1; $i<=5; $i++): ?><i class="fas fa-star<?= $i <= $v['rating'] ? '' : '-o' ?>"></i><?php endfor; ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <?php if (!empty($v['visitor_phone'])): ?>
                                            <a href="tel:<?= htmlspecialchars($v['visitor_phone'] ?? '') ?>" class="btn btn-outline-success" title="Call"><i class="fas fa-phone"></i></a>
                                        <?php endif; ?>
                                        <button class="btn btn-outline-primary" title="Assign Executive &amp; Cab" onclick="openAssignModal(<?= (int)$v['id'] ?>, '<?= htmlspecialchars(addslashes($v['visitor_name'] ?? ''), ENT_QUOTES) ?>')"><i class="fas fa-user-check"></i></button>
                                        <button class="btn btn-outline-success" title="Send WhatsApp Pin" onclick="sendVisitPin(<?= (int)$v['id'] ?>, this)"><i class="fab fa-whatsapp"></i></button>
                                        <button class="btn btn-outline-warning" title="Record Outcome" onclick="openOutcomeModal(<?= (int)$v['id'] ?>, '<?= htmlspecialchars(addslashes($v['visitor_name'] ?? ''), ENT_QUOTES) ?>')"><i class="fas fa-clipboard-check"></i></button>
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

<script>
document.querySelectorAll('.status-select').forEach(function(sel) {
    sel.addEventListener('change', function() {
        var id = this.dataset.id;
        var status = this.value;
        showLoader();
        fetch('<?= BASE_URL ?>/admin/site-visits/' + id + '/status', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-CSRF-Token': '<?= $_SESSION['csrf_token'] ?? '' ?>'
            },
            body: 'status=' + status
        }).then(r => r.json()).then(d => {
            if (d.ok) {
                location.reload();
            } else {
                showToast('Failed: ' + (d.error || 'Unknown error'), 'danger');
            }
        ).finally(() => hideLoader());
    });
});
</script>

<!-- Assign Executive & Cab Modal (Site Visit Dispatch Engine) -->
<div class="modal fade" id="assignExecModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user-check me-2"></i>Assign Executive &amp; Cab</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="assignExecForm" onsubmit="return submitAssignForm(event)">
                <div class="modal-body">
                    <p class="text-muted small">Visitor: <strong id="assignVisitorName"></strong></p>
                    <input type="hidden" id="assignVisitId">
                    <div class="mb-3">
                        <label class="form-label">Sales Executive *</label>
                        <select id="assignExecId" class="form-select" required>
                            <option value="">— Select executive —</option>
                            <?php foreach ($executives as $ex): ?>
                                <option value="<?= (int)$ex['id'] ?>"><?= htmlspecialchars($ex['name'] ?? ('User #' . $ex['id'])) ?><?= !empty($ex['phone']) ? ' (' . htmlspecialchars($ex['phone']) . ')' : '' ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Cab Assigned</label>
                        <input type="text" id="assignCab" class="form-control" placeholder="e.g. UP53-AB-1234 (Driver Ramesh)">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Pickup Time</label>
                        <input type="text" id="assignPickupTime" class="form-control" placeholder="e.g. 10:30 AM from home">
                    </div>
                    <div id="assignResult" class="d-none">
                        <div class="alert alert-success py-2 small mb-2" id="assignMsg"></div>
                        <div class="d-flex gap-2">
                            <a href="#" id="assignWaCustomer" target="_blank" rel="noopener" class="btn btn-sm btn-success"><i class="fab fa-whatsapp me-1"></i>Customer Pin</a>
                            <a href="#" id="assignWaExec" target="_blank" rel="noopener" class="btn btn-sm btn-outline-success"><i class="fab fa-whatsapp me-1"></i>Executive Card</a>
                        </div>
                        <small class="text-muted d-block mt-1" id="assignApiStatus"></small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="assignSubmitBtn"><i class="fas fa-paper-plane me-1"></i>Confirm &amp; Send WhatsApp</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Visit Outcome Modal -->
<div class="modal fade" id="visitOutcomeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-clipboard-check me-2"></i>Visit Outcome</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="visitOutcomeForm" onsubmit="return submitOutcomeForm(event)">
                <div class="modal-body">
                    <p class="text-muted small">Visitor: <strong id="outcomeVisitorName"></strong></p>
                    <input type="hidden" id="outcomeVisitId">
                    <div class="mb-3">
                        <label class="form-label">Outcome *</label>
                        <select id="outcomeValue" class="form-select" required>
                            <option value="completed">Completed</option>
                            <option value="interested">Interested (creates CRM Opportunity)</option>
                            <option value="token_booked">Token Booked (creates CRM Opportunity)</option>
                            <option value="not_interested">Not Interested</option>
                            <option value="rescheduled">Rescheduled</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Plot Preference</label>
                        <input type="text" id="outcomePlotPref" class="form-control" placeholder="e.g. East-facing 1200 sqft near park">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Budget Feedback</label>
                        <input type="text" id="outcomeBudget" class="form-control" placeholder="e.g. 25 lakh">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Follow-up Date (for reschedule)</label>
                        <input type="date" id="outcomeFollowup" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea id="outcomeNotes" class="form-control" rows="2" placeholder="Visit feedback, objections, next steps..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-warning" id="outcomeSubmitBtn"><i class="fas fa-check me-1"></i>Save Outcome</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
var SV_CSRF = '<?= $_SESSION['csrf_token'] ?? '' ?>';
function openAssignModal(id, name) {
    document.getElementById('assignVisitId').value = id;
    document.getElementById('assignVisitorName').textContent = name;
    document.getElementById('assignResult').classList.add('d-none');
    new bootstrap.Modal(document.getElementById('assignExecModal')).show();
}
function submitAssignForm(e) {
    e.preventDefault();
    var id = document.getElementById('assignVisitId').value;
    var btn = document.getElementById('assignSubmitBtn');
    btn.disabled = true;
    showLoader();
    fetch('<?= BASE_URL ?>/admin/site-visits/' + id + '/assign', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-Token': SV_CSRF },
        body: 'executive_id=' + encodeURIComponent(document.getElementById('assignExecId').value)
            + '&cab_assigned=' + encodeURIComponent(document.getElementById('assignCab').value)
            + '&pickup_time=' + encodeURIComponent(document.getElementById('assignPickupTime').value)
    }).then(function(r) { return r.json(); }).then(function(d) {
        if (d.success) {
            document.getElementById('assignResult').classList.remove('d-none');
            document.getElementById('assignMsg').textContent = d.message + ' ' + (d.api_status || '');
            var cA = document.getElementById('assignWaCustomer');
            var eA = document.getElementById('assignWaExec');
            cA.style.display = d.whatsapp_customer_url ? '' : 'none';
            eA.style.display = d.whatsapp_exec_url ? '' : 'none';
            cA.href = d.whatsapp_customer_url || '#';
            eA.href = d.whatsapp_exec_url || '#';
            document.getElementById('assignApiStatus').textContent = d.api_status || '';
            if (d.whatsapp_customer_url) { window.open(d.whatsapp_customer_url, '_blank'); }
            showToast('Executive assigned & visit confirmed', 'success');
        } else {
            showToast('Failed: ' + (d.error || 'Unknown error'), 'danger');
        }
    }).catch(function() { showToast('Network error', 'danger'); })
    .finally(function() { btn.disabled = false; hideLoader(); });
    return false;
}
function sendVisitPin(id, btn) {
    if (btn) { btn.disabled = true; }
    showLoader();
    fetch('<?= BASE_URL ?>/admin/site-visits/' + id + '/send-pin', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-Token': SV_CSRF },
        body: ''
    }).then(function(r) { return r.json(); }).then(function(d) {
        if (d.success && d.whatsapp_customer_url) {
            window.open(d.whatsapp_customer_url, '_blank');
            showToast('WhatsApp pin opened' + (d.api_status ? ' — ' + d.api_status : ''), 'success');
        } else {
            showToast('Failed: ' + (d.error || 'Customer phone unavailable'), 'danger');
        }
    }).catch(function() { showToast('Network error', 'danger'); })
    .finally(function() { if (btn) { btn.disabled = false; } hideLoader(); });
}
function openOutcomeModal(id, name) {
    document.getElementById('outcomeVisitId').value = id;
    document.getElementById('outcomeVisitorName').textContent = name;
    new bootstrap.Modal(document.getElementById('visitOutcomeModal')).show();
}
function submitOutcomeForm(e) {
    e.preventDefault();
    var id = document.getElementById('outcomeVisitId').value;
    var btn = document.getElementById('outcomeSubmitBtn');
    btn.disabled = true;
    showLoader();
    fetch('<?= BASE_URL ?>/admin/site-visits/' + id + '/outcome', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-Token': SV_CSRF },
        body: 'outcome=' + encodeURIComponent(document.getElementById('outcomeValue').value)
            + '&plot_preference=' + encodeURIComponent(document.getElementById('outcomePlotPref').value)
            + '&budget_feedback=' + encodeURIComponent(document.getElementById('outcomeBudget').value)
            + '&followup_date=' + encodeURIComponent(document.getElementById('outcomeFollowup').value)
            + '&outcome_notes=' + encodeURIComponent(document.getElementById('outcomeNotes').value)
    }).then(function(r) { return r.json(); }).then(function(d) {
        if (d.success) {
            showToast(d.opportunity_created ? 'Outcome saved — CRM Opportunity #' + d.opportunity_id + ' created' : 'Outcome saved', 'success');
            setTimeout(function() { location.reload(); }, 900);
        } else {
            showToast('Failed: ' + (d.error || 'Unknown error'), 'danger');
        }
    }).catch(function() { showToast('Network error', 'danger'); })
    .finally(function() { btn.disabled = false; hideLoader(); });
    return false;
}
</script>
