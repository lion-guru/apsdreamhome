<?php
/** Live telecalling desk — single screen. Data: today_stats, lead_queue,
 *  todays_follow_ups, scripts, objections, scripts_by_category, quick_scripts. */
$today_stats = $today_stats ?? [];
$lead_queue = $lead_queue ?? [];
$objections = $objections ?? [];
$quick_scripts = $quick_scripts ?? [];
$base = defined('BASE_URL') ? BASE_URL : '';
$csrf = htmlspecialchars($_SESSION['csrf_token'] ?? '');
$e = function ($v) { return htmlspecialchars((string)($v ?? '')); };
$first = $lead_queue[0] ?? null;
$nextSunday = date('Y-m-d', strtotime('next sunday'));
?>
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h4 class="mb-0"><i class="fas fa-headset me-2"></i>Live Calling Desk</h4>
        <div class="d-flex gap-2 flex-wrap">
            <span class="badge bg-primary fs-6">Calls today: <?= (int)($today_stats['total_calls'] ?? 0) ?></span>
            <span class="badge bg-success fs-6">Connected: <?= (int)($today_stats['connected_calls'] ?? 0) ?></span>
            <span class="badge bg-info text-dark fs-6">Target: <?= (int)($today_stats['total_calls'] ?? 0) ?>/<?= (int)($today_stats['daily_target'] ?? 50) ?></span>
        </div>
    </div>
    <div id="deskMsg"></div>
    <div class="row g-3">
        <!-- LEFT: active lead + log call + WA pitch -->
        <div class="col-lg-4">
            <div class="card shadow-sm mb-3"><div class="card-body">
                <h6 class="fw-bold">Queue (<?= count($lead_queue) ?>)</h6>
                <div class="list-group small" id="queueList" style="max-height:220px;overflow:auto;">
                    <?php foreach ($lead_queue as $i => $l): ?>
                        <button type="button" class="list-group-item list-group-item-action queue-pick <?= $i === 0 ? 'active' : '' ?>"
                            data-i="<?= $i ?>" data-name="<?= $e($l['name'] ?? '') ?>" data-phone="<?= $e(preg_replace('/\D/', '', (string)($l['phone'] ?? ''))) ?>"
                            data-id="<?= (int)($l['id'] ?? 0) ?>" data-budget="<?= $e($l['budget_range'] ?? ($l['budget'] ?? '')) ?>"
                            data-interest="<?= $e($l['property_interest'] ?? '') ?>" data-source="<?= $e($l['campaign_name'] ?? ($l['source'] ?? '')) ?>">
                            <strong><?= $e($l['name'] ?? '') ?></strong> · <?= $e($l['phone'] ?? '') ?>
                        </button>
                    <?php endforeach; ?>
                    <?php if (empty($lead_queue)): ?><div class="text-muted small p-2">Queue empty — no pending/follow-up leads assigned.</div><?php endif; ?>
                </div>
            </div></div>
            <div class="card shadow-sm"><div class="card-body">
                <h6 class="fw-bold">Active Lead</h6>
                <div id="activeCard">
                    <h5 id="acName"><?= $e($first['name'] ?? '—') ?></h5>
                    <p class="mb-1"><a id="acPhone" href="<?= $first ? 'tel:' . $e(preg_replace('/\D/', '', (string)$first['phone'])) : '#' ?>"><?= $e($first['phone'] ?? '') ?></a>
                        <span class="badge bg-light text-dark border ms-1" id="acBudget"><?= $e($first['budget_range'] ?? ($first['budget'] ?? '')) ?></span></p>
                    <p class="small text-muted mb-2" id="acMeta"><?= $e($first['property_interest'] ?? '') ?> · <?= $e($first['campaign_name'] ?? ($first['source'] ?? '')) ?></p>
                    <a id="acWa" target="_blank" rel="noopener" class="btn btn-success btn-sm w-100 mb-2" href="#"><i class="fab fa-whatsapp me-1"></i>Send WhatsApp Pitch</a>
                </div>
                <hr>
                <h6 class="fw-bold">Log Call</h6>
                <form id="logForm">
                    <input type="hidden" name="lead_id" id="logLeadId" value="<?= (int)($first['id'] ?? 0) ?>">
                    <div class="row g-2">
                        <div class="col-6"><label class="form-label small">Status</label>
                            <select name="call_status" class="form-select form-select-sm"><option>connected</option><option>busy</option><option>not_reachable</option></select></div>
                        <div class="col-6"><label class="form-label small">Outcome</label>
                            <select name="outcome" class="form-select form-select-sm"><option value="interested">Interested</option><option value="follow_up">Call later</option><option value="no_answer">No answer</option><option value="not_interested">Not interested</option><option value="converted">Converted</option></select></div>
                        <div class="col-6"><label class="form-label small">Duration (sec)</label><input type="number" name="duration" class="form-control form-control-sm" value="0" min="0"></div>
                        <div class="col-6"><label class="form-label small">Follow-up date</label><input type="date" name="next_follow_up" class="form-control form-control-sm"></div>
                        <div class="col-12"><label class="form-label small">Notes</label><textarea name="notes" class="form-control form-control-sm" rows="2"></textarea></div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm w-100 mt-2">Log Call</button>
                </form>
            </div></div>
        </div>
        <!-- CENTER: site visit booking -->
        <div class="col-lg-4">
            <div class="card shadow-sm"><div class="card-body">
                <h6 class="fw-bold"><i class="fas fa-bus me-1"></i>Book Sunday Cab Visit</h6>
                <form id="visitForm">
                    <input type="hidden" name="lead_id" id="visitLeadId" value="<?= (int)($first['id'] ?? 0) ?>">
                    <div class="mb-2"><label class="form-label small">Sunday date</label><input type="date" name="visit_date" class="form-control" value="<?= $e($nextSunday) ?>" required></div>
                    <div class="mb-2"><label class="form-label small">Pickup point</label><input type="text" name="pickup_point" class="form-control" placeholder="e.g. Gorakhpur station"></div>
                    <div class="mb-2"><label class="form-label small">Notes</label><textarea name="notes" class="form-control" rows="2"></textarea></div>
                    <button type="submit" class="btn btn-success w-100"><i class="fas fa-calendar-check me-1"></i>Schedule Free Sunday Cab</button>
                </form>
                <div id="visitResult" class="mt-2"></div>
            </div></div>
            <div class="card shadow-sm mt-3"><div class="card-body">
                <h6 class="fw-bold">Quick Scripts</h6>
                <?php foreach ($quick_scripts as $qs): ?>
                    <details class="small mb-1"><summary><?= $e($qs['script_name'] ?? $qs['title'] ?? 'Script') ?></summary><p class="mt-1"><?= nl2br($e($qs['script_text'] ?? $qs['content'] ?? '')) ?></p></details>
                <?php endforeach; ?>
                <?php if (empty($quick_scripts)): ?><p class="text-muted small mb-0">No quick scripts configured.</p><?php endif; ?>
            </div></div>
        </div>
        <!-- RIGHT: objections -->
        <div class="col-lg-4">
            <div class="card shadow-sm"><div class="card-body">
                <h6 class="fw-bold">Objection Handling (जवाब तैयार)</h6>
                <ul class="nav nav-pills small mb-2" role="tablist">
                    <?php $oi = 0; foreach ($objections as $key => $ob): ?>
                        <li class="nav-item"><button class="nav-link btn-sm <?= $oi === 0 ? 'active' : '' ?>" data-bs-toggle="pill" data-bs-target="#ob-<?= $e($key) ?>" type="button"><?= $e($ob['label'] ?? $key) ?></button></li>
                    <?php $oi++; endforeach; ?>
                </ul>
                <div class="tab-content">
                    <?php $oi = 0; foreach ($objections as $key => $ob): ?>
                        <div class="tab-pane fade <?= $oi === 0 ? 'show active' : '' ?>" id="ob-<?= $e($key) ?>">
                            <?php foreach (($ob['scripts'] ?? []) as $sk => $sv): ?>
                                <p class="small mb-1"><strong><?= $e(ucfirst(str_replace('_', ' ', $sk))) ?>:</strong> <?= $e($sv) ?></p>
                            <?php endforeach; ?>
                            <?php foreach (($ob['rebuttals'] ?? []) as $rk => $rv): ?>
                                <p class="small mb-1 text-primary"><strong><?= $e($rk) ?> →</strong> <?= $e($rv) ?></p>
                            <?php endforeach; ?>
                        </div>
                    <?php $oi++; endforeach; ?>
                    <?php if (empty($objections)): ?><p class="text-muted small">No objection scripts.</p><?php endif; ?>
                </div>
            </div></div>
        </div>
    </div>
</div>
<script>
(function () {
    var base = '<?= $base ?>';
    var csrf = '<?= $csrf ?>';
    function msg(html, ok) {
        document.getElementById('deskMsg').innerHTML =
            '<div class="alert alert-' + (ok === false ? 'danger' : 'success') + ' py-2">' + html + '</div>';
    }
    function postForm(path, fd, cb) {
        fetch(base + path, { method: 'POST', body: fd, headers: { 'X-CSRF-TOKEN': csrf } })
            .then(function (r) { return r.text().then(function (t) { return { status: r.status, text: t }; }); })
            .then(cb).catch(function (e) { msg('Request failed: ' + e, false); });
    }
    function waLink(name, phone) {
        var text = 'Hi ' + name + ', APS Dream Home se bol raha hoon! Premium plots ke liye Sunday FREE cab site visit available hai. Brochure + details bhejun? Reply YES.';
        return 'https://wa.me/91' + phone + '?text=' + encodeURIComponent(text);
    }
    function setActive(btn) {
        document.querySelectorAll('.queue-pick').forEach(function (b) { b.classList.remove('active'); });
        btn.classList.add('active');
        var name = btn.dataset.name || '', phone = btn.dataset.phone || '';
        document.getElementById('acName').textContent = name || '—';
        var ph = document.getElementById('acPhone');
        ph.textContent = btn.textContent.trim().split('·')[1] || phone;
        ph.href = phone ? 'tel:' + phone : '#';
        document.getElementById('acBudget').textContent = btn.dataset.budget || '';
        document.getElementById('acMeta').textContent = (btn.dataset.interest || '') + ' · ' + (btn.dataset.source || '');
        document.getElementById('logLeadId').value = btn.dataset.id || 0;
        document.getElementById('visitLeadId').value = btn.dataset.id || 0;
        document.getElementById('acWa').href = phone ? waLink(name.split(' ')[0], phone) : '#';
    }
    document.querySelectorAll('.queue-pick').forEach(function (b) {
        b.addEventListener('click', function () { setActive(b); });
    });
    var firstBtn = document.querySelector('.queue-pick');
    if (firstBtn) setActive(firstBtn);
    document.getElementById('logForm').addEventListener('submit', function (ev) {
        ev.preventDefault();
        postForm('/employee/telecalling/log-call', new FormData(this), function (res) {
            msg(res.status === 200 ? 'Call logged ✓' : 'Log failed (HTTP ' + res.status + ')', res.status === 200);
        });
    });
    document.getElementById('visitForm').addEventListener('submit', function (ev) {
        ev.preventDefault();
        var box = document.getElementById('visitResult');
        postForm('/employee/telecalling/book-site-visit', new FormData(this), function (res) {
            try {
                var j = JSON.parse(res.text);
                box.innerHTML = '<div class="alert alert-' + (j.success ? 'success' : 'danger') + ' py-2 small">' +
                    (j.success ? ('✓ ' + (j.message || 'Booked') + ' (visit #' + (j.visit_id || '?') + ')') : ('✗ ' + (j.error || 'Failed'))) + '</div>';
            } catch (e) {
                box.innerHTML = '<div class="alert alert-danger py-2 small">Booking failed (HTTP ' + res.status + ')</div>';
            }
        });
    });
})();
</script>
