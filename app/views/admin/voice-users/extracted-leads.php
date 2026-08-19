<?php $leads = $leads ?? []; ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Extracted Leads from Calls</h4>
    <button class="btn btn-success btn-sm" onclick="convertAllVerified()"><i class="fas fa-check-double"></i> Convert All Verified</button>
</div>
<div class="card aps-cp-card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Interest</th>
                        <th>Source Call</th>
                        <th>Verified</th>
                        <th>Converted</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($leads)): ?>
                        <tr><td colspan="8" class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x d-block mb-2 text-muted" aria-hidden="true"></i>No extracted leads yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($leads as $l): ?>
                            <tr>
                                <td><?= htmlspecialchars($l['name'] ?? $l['lead_name'] ?? 'Unknown') ?></td>
                                <td><?= htmlspecialchars($l['phone'] ?? $l['customer_phone'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($l['email'] ?? '-') ?></td>
                                <td><span class="badge bg-info"><?= htmlspecialchars($l['interest'] ?? $l['property_interest'] ?? 'General') ?></span></td>
                                <td><small><?= htmlspecialchars($l['call_id'] ?? $l['source_call_id'] ?? 'N/A') ?></small></td>
                                <td><?php $v = $l['is_verified'] ?? 0; ?>
                                    <span class="badge bg-<?= $v ? 'success' : 'warning' ?>"><?= $v ? 'Verified' : 'Pending' ?></span>
                                </td>
                                <td><?php $cv = $l['is_converted'] ?? 0; ?>
                                    <span class="badge bg-<?= $cv ? 'primary' : 'secondary' ?>"><?= $cv ? 'Converted' : 'Not' ?></span>
                                </td>
                                <td>
                                    <?php if (!($l['is_verified'] ?? 0)): ?>
                                        <button class="btn btn-sm btn-outline-success" onclick="verifyLead(<?= (int)($l['id'] ?? 0) ?>)" title="Verify"><i class="fas fa-check"></i></button>
                                    <?php endif; ?>
                                    <?php if (!($l['is_converted'] ?? 0) && ($l['is_verified'] ?? 0)): ?>
                                        <button class="btn btn-sm btn-outline-primary" onclick="convertLead(<?= (int)($l['id'] ?? 0) ?>)" title="Convert"><i class="fas fa-exchange-alt"></i></button>
                                    <?php endif; ?>
                                    <button class="btn btn-sm btn-outline-info" onclick="viewLeadTimeline(<?= (int)($l['id'] ?? 0) ?>)" title="Details"><i class="fas fa-eye"></i></button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="leadTimelineModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Lead Timeline</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body" id="leadTimelineBody"><div class="text-center py-3"><i class="fas fa-spinner fa-spin"></i> Loading...</div></div></div></div></div>

<script>
function csrfToken() { return '<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>'; }
function verifyLead(id) {
    showLoader();
    fetch('<?= BASE_URL ?>admin/voice-users/ajax/convert-lead', {
        method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'csrf_token=' + encodeURIComponent(csrfToken()) + '&extracted_id=' + id
    }).then(r => r.json()).then(d => {
        if (d.success) { location.reload(); } else { showToast(d.message || 'Failed', 'danger'); }
    }).catch(() => showToast('Network error', 'danger')).finally(() => hideLoader());
}
function convertLead(id) { verifyLead(id); }
function convertAllVerified() {
    apsConfirm('Convert all verified leads to CRM?').then(function(ok) {
        if (!ok) return;
    });
    var btns = document.querySelectorAll('button[onclick^="verifyLead"]');
    var ids = []; btns.forEach(function(b) { var m = b.getAttribute('onclick').match(/\d+/); if (m) ids.push(m[0]); });
    if (!ids.length) { showToast('No verified leads to convert.', 'info'); return; }
    var done = 0;
    ids.forEach(function(id) {
        showLoader();
        fetch('<?= BASE_URL ?>admin/voice-users/ajax/convert-lead', {
            method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'csrf_token=' + encodeURIComponent(csrfToken()) + '&extracted_id=' + id
        }).then(function(r) { return r.json(); }).then(function() { done++; if (done === ids.length) location.reload(); ).finally(() => hideLoader());
    });
}
function viewLeadTimeline(id) {
    document.getElementById('leadTimelineBody').innerHTML = '<div class="text-center py-3"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';
    new bootstrap.Modal(document.getElementById('leadTimelineModal')).show();
    showLoader();
    fetch('<?= BASE_URL ?>admin/voice-users/ajax/lead-timeline/' + id).then(r => r.json()).then(d => {
        if (d.success && d.data && d.data.length) {
            var html = '<ul class="list-group">';
            d.data.forEach(function(item) { html += '<li class="list-group-item"><strong>' + (item.action || '') + '</strong> <small class="text-muted">' + (item.created_at || '') + '</small><br>' + (item.details || item.notes || '') + '</li>'; ).finally(() => hideLoader());
            .catch(err => console.error('Request failed:', err));
            html += '</ul>';
            document.getElementById('leadTimelineBody').innerHTML = html;
        } else {
            document.getElementById('leadTimelineBody').innerHTML = '<p class="text-muted">No timeline data found.</p>';
        }
    }).catch(() => { document.getElementById('leadTimelineBody').innerHTML = '<p class="text-muted">Could not load timeline.</p>'; });
}
</script>
