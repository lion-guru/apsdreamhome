<?php
$page_title = $page_title ?? 'Site Visits Management';
$currentPage = $currentPage ?? 'site-visits';
$visits = $visits ?? [];
$stats = $stats ?? ['total'=>0,'today'=>0,'upcoming'=>0,'completed'=>0,'cancelled'=>0];
$active_tab = $active_tab ?? 'all';
$search = $search ?? '';

$statusMap = [
    'scheduled' => ['color'=>'primary','icon'=>'fa-calendar-check'],
    'rescheduled' => ['color'=>'warning','icon'=>'fa-calendar-alt'],
    'completed' => ['color'=>'success','icon'=>'fa-check-circle'],
    'cancelled' => ['color'=>'secondary','icon'=>'fa-times-circle'],
    'in_progress' => ['color'=>'info','icon'=>'fa-spinner'],
    'no_show' => ['color'=>'danger','icon'=>'fa-user-slash'],
];
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
            <a href="?tab=<?= $tKey ?>" class="card border-0 shadow-sm text-decoration-none <?= $active_tab === $tKey ? 'border-' . $tColors[$tKey] : '' ?>" class="style-13097">
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
                <div class="input-group" class="style-67695">
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
                    <i class="fas fa-map-marker-alt fa-3x text-muted mb-3" class="style-82835"></i>
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
                                    <select class="form-select form-select-sm status-select" data-id="<?= $v['id'] ?>" class="style-68062">
                                        <?php foreach ($statusMap as $sKey => $sInfo): ?>
                                            <option value="<?= $sKey ?>" <?= $v['status'] === $sKey ? 'selected' : '' ?>><?= ucfirst($sKey) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td>
                                    <?php if (!empty($v['rating'])): ?>
                                        <span class="style-62159"><?php for ($i=1; $i<=5; $i++): ?><i class="fas fa-star<?= $i <= $v['rating'] ? '' : '-o' ?>"></i><?php endfor; ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($v['visitor_phone'])): ?>
                                        <a href="tel:<?= htmlspecialchars($v['visitor_phone'] ?? '') ?>" class="btn btn-sm btn-outline-success" title="Call"><i class="fas fa-phone"></i></a>
                                    <?php endif; ?>
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
                alert('Failed: ' + (d.error || 'Unknown error'));
            }
        });
    });
});
</script>
