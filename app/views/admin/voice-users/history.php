<?php
/**
 * AI Call History — outcome-aware view for admins.
 *
 * Expected data (from VoiceAgentAdminController::history()):
 *   $calls        - rows: s.* (phone, duration_seconds, status, customer_response,
 *                      interest_level, follow_up_date, recording_url, created_at)
 *                      + lead_name, lead_phone, lead_interest?, script_name
 *   $scripts_list - [['script_code','script_name'], ...] for the Script filter
 *   $filters      - active filter values
 *   $pagination   - ['page','per_page','offset','total','total_pages']
 */
$calls = $calls ?? [];
$scripts_list = $scripts_list ?? [];
$filters = $filters ?? [];
$pagination = $pagination ?? ['page' => 1, 'per_page' => 20, 'offset' => 0, 'total' => 0, 'total_pages' => 1];

$fmtDuration = static function ($s): string {
    $s = (int)$s;
    return $s >= 60 ? sprintf('%d:%02d', intdiv($s, 60), $s % 60) : ($s > 0 ? $s . 's' : '—');
};

$outcomeMeta = [
    'interested'     => ['class' => 'success', 'label' => 'Interested'],
    'callback'       => ['class' => 'info',    'label' => 'Callback'],
    'transfer'       => ['class' => 'primary', 'label' => 'Transferred'],
    'not_interested' => ['class' => 'warning', 'label' => 'Not Interested'],
    'dnd'            => ['class' => 'danger',  'label' => 'DND'],
    'no_response'    => ['class' => 'secondary', 'label' => 'No Response'],
];
$interestMeta = [
    'hot'  => ['class' => 'danger', 'label' => 'HOT'],
    'warm' => ['class' => 'warning', 'label' => 'WARM'],
    'cold' => ['class' => 'info', 'label' => 'COLD'],
    'none' => ['class' => 'secondary', 'label' => 'NONE'],
];
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="fas fa-phone-volume text-primary me-2" aria-hidden="true"></i>AI Call History</h4>
    <span class="text-muted small">Total: <?= (int)($pagination['total'] ?? 0) ?> call(s)</span>
</div>

<form method="get" class="card mb-3 border-0 shadow-sm">
    <div class="card-body aps-cp-card-body">
        <div class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label" for="date_from">Date From</label>
                <input id="date_from" aria-label="Date from" type="date" name="date_from" class="form-control" value="<?= htmlspecialchars($filters['date_from'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label" for="date_to">Date To</label>
                <input id="date_to" aria-label="Date to" type="date" name="date_to" class="form-control" value="<?= htmlspecialchars($filters['date_to'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label" for="f_status">Status</label>
                <select id="f_status" aria-label="Status" name="status" class="form-select">
                    <option value="">All</option>
                    <?php foreach (['completed' => 'Completed', 'in_progress' => 'In progress', 'failed' => 'Failed', 'no_answer' => 'No Answer'] as $v => $lbl): ?>
                        <option value="<?= $v ?>" <?= ($filters['status'] ?? '') === $v ? 'selected' : '' ?>><?= $lbl ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label" for="f_outcome">Outcome</label>
                <select id="f_outcome" aria-label="Outcome" name="outcome" class="form-select">
                    <option value="">All</option>
                    <?php foreach ($outcomeMeta as $v => $m): ?>
                        <option value="<?= $v ?>" <?= ($filters['outcome'] ?? '') === $v ? 'selected' : '' ?>><?= $m['label'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label" for="f_script">Script</label>
                <select id="f_script" aria-label="Script" name="script" class="form-select">
                    <option value="">All</option>
                    <?php foreach ($scripts_list as $sr): ?>
                        <option value="<?= htmlspecialchars($sr['script_code']) ?>" <?= ($filters['script'] ?? '') === $sr['script_code'] ? 'selected' : '' ?>><?= htmlspecialchars($sr['script_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-1">
                <label class="form-label" for="f_search">Search</label>
                <input id="f_search" aria-label="Search" type="text" name="search" class="form-control" placeholder="Name/phone" value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
            </div>
        </div>
        <div class="row g-2 mt-1">
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-primary w-100"><i class="fas fa-search me-1" aria-hidden="true"></i>Filter</button>
            </div>
            <div class="col-md-2">
                <a href="<?= BASE_URL ?>/admin/voice-users/history" class="btn btn-outline-secondary w-100"><i class="fas fa-rotate-right me-1" aria-hidden="true"></i>Reset</a>
            </div>
        </div>
    </div>
</form>

<div class="card aps-cp-card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Lead / Phone</th>
                        <th scope="col">Script</th>
                        <th scope="col">Status</th>
                        <th scope="col">Outcome</th>
                        <th scope="col">Interest</th>
                        <th scope="col">Follow-up</th>
                        <th scope="col">Duration</th>
                        <th scope="col">Date/Time</th>
                        <th scope="col">Recording</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($calls)): ?>
                        <tr><td colspan="10" class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x d-block mb-2 text-muted" aria-hidden="true"></i>No call records found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($calls as $i => $c): ?>
                            <?php
                            $statusClass = match ($c['status'] ?? '') {
                                'completed' => 'success',
                                'in_progress' => 'primary',
                                'failed' => 'danger',
                                'no_answer' => 'warning',
                                default => 'secondary',
                            };
                            $om = $outcomeMeta[$c['customer_response'] ?? ''] ?? null;
                            $im = $interestMeta[$c['interest_level'] ?? ''] ?? $interestMeta['none'];
                            $dur = $fmtDuration($c['duration_seconds'] ?? 0);
                            ?>
                            <tr>
                                <td><?= (int)($pagination['offset'] ?? 0) + $i + 1 ?></td>
                                <td>
                                    <div class="fw-semibold"><?= htmlspecialchars($c['lead_name'] ?? '—') ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($c['lead_phone'] ?? $c['phone'] ?? '—') ?></small>
                                </td>
                                <td><small><?= htmlspecialchars($c['script_name'] ?? ($c['script_template'] ?? '—')) ?></small></td>
                                <td><span class="badge bg-<?= $statusClass ?>"><?= ucfirst(str_replace('_', ' ', $c['status'] ?? 'pending')) ?></span></td>
                                <td><?php if ($om): ?><span class="badge bg-<?= $om['class'] ?>"><?= $om['label'] ?></span><?php else: ?><span class="text-muted">—</span><?php endif; ?></td>
                                <td><?php if (($c['interest_level'] ?? '') !== 'none' && ($c['interest_level'] ?? '') !== ''): ?><span class="badge bg-<?= $im['class'] ?>"><?= $im['label'] ?></span><?php else: ?><span class="text-muted">—</span><?php endif; ?></td>
                                <td><small><?= htmlspecialchars($c['follow_up_date'] ?? '—') ?></small></td>
                                <td><small><?= htmlspecialchars($dur) ?></small></td>
                                <td><small><?= htmlspecialchars($c['created_at'] ?? '—') ?></small></td>
                                <td>
                                    <?php if (!empty($c['recording_url'])): ?>
                                        <?php $rec = $c['recording_url']; if (!preg_match('/\.(mp3|wav)(\?|$)/i', $rec)) $rec .= '.mp3'; ?>
                                        <audio controls preload="none" style="max-width:170px" aria-label="Call recording"><source src="<?= htmlspecialchars($rec) ?>" type="audio/mpeg"></audio>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if (($pagination['total_pages'] ?? 1) > 1): ?>
<nav class="mt-3" aria-label="Pagination">
    <ul class="pagination justify-content-center">
        <li class="page-item <?= ($pagination['page'] ?? 1) <= 1 ? 'disabled' : '' ?>">
            <a class="page-link" href="?page=<?= ($pagination['page'] ?? 1) - 1 ?>&<?= http_build_query(array_diff_key($filters, ['page' => ''])) ?>">Previous</a>
        </li>
        <?php for ($p = 1; $p <= $pagination['total_pages']; $p++): ?>
            <li class="page-item <?= $p == $pagination['page'] ? 'active' : '' ?>">
                <a class="page-link" href="?page=<?= $p ?>&<?= http_build_query(array_diff_key($filters, ['page' => ''])) ?>"><?= $p ?></a>
            </li>
        <?php endfor; ?>
        <li class="page-item <?= ($pagination['page'] ?? 1) >= ($pagination['total_pages'] ?? 1) ? 'disabled' : '' ?>">
            <a class="page-link" href="?page=<?= ($pagination['page'] ?? 1) + 1 ?>&<?= http_build_query(array_diff_key($filters, ['page' => ''])) ?>">Next</a>
        </li>
    </ul>
</nav>
<?php endif; ?>
