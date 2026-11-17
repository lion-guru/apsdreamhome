<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-map-marker-alt text-primary me-2"></i>GPS Check-In Monitor</h1>
            <div class="text-muted small mt-1">Office geofence &amp; field-site attendance with anti-fraud heuristics. Flags don't block attendance — HR reviews them.</div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="<?= BASE_URL ?>/admin/hrm/work-zones" class="btn btn-outline-primary"><i class="fas fa-map-marked-alt me-1"></i>Work Zones</a>
            <form method="get" action="<?= BASE_URL ?>/admin/hrm/check_in_monitor" class="d-flex align-items-center gap-2">
                <input type="date" name="date" value="<?= htmlspecialchars($date) ?>" class="form-control" style="max-width:180px" aria-label="Date">
                <button type="submit" class="btn btn-outline-secondary"><i class="fas fa-filter me-1"></i>Apply</button>
            </form>
        </div>
    </div>

    <?php if ($fetchError): ?>
        <div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i><?= htmlspecialchars($fetchError) ?></div>
    <?php endif; ?>

    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card shadow border-left-primary h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Active Employees</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= (int)($counts['total'] ?? 0) ?></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card shadow border-left-success h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Checked In Today</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= (int)($counts['present'] ?? 0) ?></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card shadow border-left-warning h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Flagged For Review</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= (int)($counts['flagged'] ?? 0) ?></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card shadow border-left-danger h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Not Checked In</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= (int)($counts['absent'] ?? 0) ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-table me-2"></i>Today's Board (<?= htmlspecialchars($date) ?>)</h6>
            <span class="small text-muted">Flags are advisory — HR decides.</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Employee</th>
                            <th>Department</th>
                            <th>Check-In</th>
                            <th>Zone / Distance</th>
                            <th>GPS</th>
                            <th>Flags</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($rows)): ?>
                            <tr><td colspan="7" class="text-center text-muted py-4">
                                <i class="fas fa-user-clock fa-2x mb-2 d-block"></i>No check-ins recorded yet for <?= htmlspecialchars($date) ?>.
                            </td></tr>
                        <?php endif; ?>
                        <?php foreach ($rows as $r): ?>
                            <?php
                            $status = $r['status'] ?? null;
                            $badge = 'secondary';
                            $label = 'Not checked in';
                            if ($status === 'valid' || $status === 'approved') { $badge = 'success'; $label = $status === 'approved' ? 'Approved' : 'Valid'; }
                            elseif ($status === 'mock_gps') { $badge = 'danger'; $label = 'Mock GPS'; }
                            elseif ($status === 'out_of_zone') { $badge = 'warning'; $label = 'Out of zone'; }
                            elseif ($status === 'review_required') { $badge = 'info'; $label = 'Review (selfie)'; }
                            ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold"><?= htmlspecialchars($r['employee_name'] ?? '—') ?></div>
                                    <div class="text-muted small">#<?= (int)($r['employee_id'] ?? 0) ?><?= !empty($r['designation']) ? ' · ' . htmlspecialchars($r['designation']) : '' ?></div>
                                </td>
                                <td><?= htmlspecialchars($r['department'] ?? '—') ?></td>
                                <td>
                                    <?php if (!empty($r['check_in_at'])): ?>
                                        <div><?= date('H:i', strtotime($r['check_in_at'])) ?></div>
                                        <?php if (!empty($r['check_out_at'])): ?>
                                            <div class="text-muted small">Out <?= date('H:i', strtotime($r['check_out_at'])) ?></div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($r['zone_name'])): ?>
                                        <div><?= htmlspecialchars($r['zone_name']) ?></div>
                                        <div class="text-muted small"><?= isset($r['distance_from_zone_m']) ? (int)$r['distance_from_zone_m'] . 'm from zone' : '' ?></div>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($r['selfie_path'])): ?>
                                        <a href="<?= BASE_URL ?>/<?= htmlspecialchars($r['selfie_path']) ?>" target="_blank" rel="noopener" class="badge bg-primary text-decoration-none"><i class="fas fa-camera me-1"></i>Selfie</a>
                                    <?php endif; ?>
                                    <?php if (!empty($r['is_mock_gps'])): ?>
                                        <span class="badge bg-danger"><i class="fas fa-location-crosshairs me-1"></i>Mock</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $badge ?>"><?= htmlspecialchars($label) ?></span>
                                    <?php if (!empty($r['anomalies'])): ?>
                                        <div class="small text-muted mt-1">
                                            <?php foreach ($r['anomalies'] as $a): ?>
                                                <div title="<?= htmlspecialchars($a['detail']) ?>">· <?= htmlspecialchars($a['code']) ?></div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                        <?php if (!empty($r['check_in_at']) && $status !== 'valid' && $status !== 'approved'): ?>
                                        <button class="btn btn-sm btn-outline-success me-1 aps-review-btn" data-action="approve" data-checkin="<?= (int)($r['checkin_id'] ?? 0) ?>"><i class="fas fa-check"></i><span class="d-none d-md-inline ms-1">Approve</span></button>
                                        <button class="btn btn-sm btn-outline-danger aps-review-btn" data-action="reject" data-checkin="<?= (int)($r['checkin_id'] ?? 0) ?>"><i class="fas fa-times"></i><span class="d-none d-md-inline ms-1">Reject</span></button>
                                    <?php elseif (!empty($r['check_in_at'])): ?>
                                        <button class="btn btn-sm btn-outline-primary aps-ai-btn" data-emp="<?= (int)$r['employee_id'] ?>"><i class="fas fa-wand-magic-sparkles"></i><span class="d-none d-md-inline ms-1">AI Summary</span></button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="alert alert-info small mt-3">
        <i class="fas fa-shield-halved me-2"></i><strong>Anti-fraud &amp; privacy note:</strong> flags come from client-reported GPS accuracy + client mock-location signal + server-side distance/travel heuristics. They are advisory only — approval/rejection is an HR decision recorded with a note. Selfies are stored on disk and shown to HR only on this monitor.
    </div>
</div>

<script <?= isset($GLOBALS['csp_nonce']) ? 'nonce="' . htmlspecialchars($GLOBALS['csp_nonce']) . '"' : '' ?>>
document.querySelectorAll('.aps-review-btn').forEach(btn => {
  btn.addEventListener('click', function () {
    const checkin = this.dataset.checkin, action = this.dataset.action;
    const note = window.prompt((action === 'approve' ? 'Approval note (optional):' : 'Rejection note (required):') , '');
    if (action === 'reject' && (!note || !note.trim())) { alert('Rejection note is required.'); return; }
    if (note === null && action === 'approve') { if (!confirm('Approve without note?')) return; }
    fetch('<?= BASE_URL ?>/admin/hrm/check_in_monitor/review?checkin_id=' + encodeURIComponent(checkin), {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-Token': (document.querySelector('meta[name="csrf-token"]') || {}).content || '' },
      body: 'action=' + encodeURIComponent(action) + '&note=' + encodeURIComponent(note || '')
    }).then(() => window.location.reload()).catch(() => alert('Failed — try again'));
  });
});
document.querySelectorAll('.aps-ai-btn').forEach(btn => {
  btn.addEventListener('click', function () {
    const emp = this.dataset.emp;
    this.disabled = true;
    const old = this.innerHTML;
    this.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span class="d-none d-md-inline ms-1">Thinking…</span>';
    fetch('<?= BASE_URL ?>/admin/hrm/check_in_monitor/ai-summary?employee_id=' + encodeURIComponent(emp), {
      credentials: 'same-origin',
      headers: { 'X-CSRF-Token': (document.querySelector('meta[name="csrf-token"]') || {}).content || '' }
    }).then(r => r.json()).then(j => {
      alert((j && j.summary) ? j.summary : ((j && j.error) ? j.error : 'AI summary unavailable'));
    }).catch(() => alert('AI summary request failed.')).finally(() => { this.disabled = false; this.innerHTML = old; });
  });
});
</script>
