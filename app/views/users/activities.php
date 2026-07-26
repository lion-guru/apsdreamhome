<?php
$activities = $activities ?? [];
$stats = $stats ?? ['total' => 0, 'today' => 0, 'this_week' => 0, 'types' => []];
$filter = $filter ?? '';

function actIcon($action) {
    $map = ['login' => 'sign-in-alt', 'logout' => 'sign-out-alt', 'task' => 'tasks', 'attendance' => 'calendar-check', 'leave' => 'calendar-minus', 'document' => 'file-alt', 'system' => 'cog', 'note' => 'sticky-note', 'update' => 'edit', 'view' => 'eye', 'create' => 'plus-circle', 'delete' => 'trash-alt', 'check_in' => 'fingerprint', 'check_out' => 'door-open'];
    return $map[$action] ?? 'circle';
}
function actColor($action) {
    $map = ['login' => 'success', 'logout' => 'secondary', 'task' => 'info', 'attendance' => 'primary', 'leave' => 'warning', 'document' => 'purple', 'system' => 'dark', 'note' => 'info', 'check_in' => 'success', 'check_out' => 'danger'];
    return $map[$action] ?? 'secondary';
}
function actBadge($action) {
    $map = ['login' => 'success', 'logout' => 'secondary', 'task' => 'info', 'attendance' => 'primary', 'leave' => 'warning', 'document' => 'purple', 'system' => 'dark', 'note' => 'info', 'check_in' => 'success', 'check_out' => 'danger'];
    return $map[$action] ?? 'secondary';
}
function timeAgo($date) {
    if (!$date) return '';
    $diff = time() - strtotime($date);
    if ($diff < 60) return 'Just now';
    if ($diff < 3600) return floor($diff / 60) . 'm ago';
    if ($diff < 86400) return floor($diff / 3600) . 'h ago';
    if ($diff < 604800) return floor($diff / 86400) . 'd ago';
    return date('d M', strtotime($date));
}
$filtered = $activities;
if ($filter && in_array($filter, ['login','task','attendance','leave','document','system'])) {
    $filtered = array_filter($activities, function($a) use ($filter) { return ($a['action'] ?? '') === $filter; });
}
$maxTypeCount = max(array_values($stats['types'] ?: [1]));
?>

<style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
.emp-act-stat { border: none; border-radius: 12px; transition: transform 0.2s; }
.emp-act-stat:hover { transform: translateY(-2px); }
.emp-act-stat .stat-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; }
.emp-act-timeline { position: relative; padding-left: 40px; }
.emp-act-timeline::before { content: ''; position: absolute; left: 19px; top: 0; bottom: 0; width: 2px; background: #e2e8f0; }
.emp-act-item { position: relative; margin-bottom: 20px; }
.emp-act-marker { position: absolute; left: -30px; top: 6px; width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; border: 3px solid #fff; box-shadow: 0 2px 6px rgba(0,0,0,0.1); font-size: 0.85rem; }
.emp-act-card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px 16px; transition: all 0.2s; }
.emp-act-card:hover { border-color: #cbd5e1; box-shadow: 0 2px 8px rgba(0,0,0,0.04); }
.emp-act-meta { font-size: 0.78rem; color: #64748b; }
.emp-act-type-btn { border: 1px solid #e2e8f0; border-radius: 20px; padding: 4px 14px; font-size: 0.8rem; text-decoration: none; transition: all 0.2s; color: #475569; }
.emp-act-type-btn:hover { background: #e2e8f0; }
.emp-act-type-btn.active { background: #7c2d12; color: #fff; border-color: #7c2d12; }
.emp-act-type-bar { height: 6px; border-radius: 3px; background: #e2e8f0; overflow: hidden; }
.emp-act-type-bar-fill { height: 100%; border-radius: 3px; transition: width 0.3s; }
</style>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold"><i class="fas fa-history me-2 text-primary"></i>Activity Timeline</h4>
            <p class="text-muted mb-0 small"><?= $stats['total'] ?> activities recorded</p>
        </div>
        <div class="d-flex gap-2">
            <a href="/employee/activities" class="btn btn-sm <?= !$filter ? 'btn-primary' : 'btn-outline-secondary' ?>">All</a>
            <a href="/employee/activities?type=login" class="btn btn-sm <?= $filter === 'login' ? 'btn-primary' : 'btn-outline-secondary' ?>"><i class="fas fa-sign-in-alt me-1"></i>Login</a>
            <a href="/employee/activities?type=task" class="btn btn-sm <?= $filter === 'task' ? 'btn-primary' : 'btn-outline-secondary' ?>"><i class="fas fa-tasks me-1"></i>Tasks</a>
            <a href="/employee/activities?type=attendance" class="btn btn-sm <?= $filter === 'attendance' ? 'btn-primary' : 'btn-outline-secondary' ?>"><i class="fas fa-calendar-check me-1"></i>Attendance</a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card emp-act-stat shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary"><i class="fas fa-list"></i></div>
                    <div><div class="fw-bold fs-4"><?= $stats['total'] ?></div><div class="text-muted small">Total</div></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card emp-act-stat shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon bg-success bg-opacity-10 text-success"><i class="fas fa-calendar-day"></i></div>
                    <div><div class="fw-bold fs-4 text-success"><?= $stats['today'] ?></div><div class="text-muted small">Today</div></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card emp-act-stat shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon bg-info bg-opacity-10 text-info"><i class="fas fa-calendar-week"></i></div>
                    <div><div class="fw-bold fs-4 text-info"><?= $stats['this_week'] ?></div><div class="text-muted small">This Week</div></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card emp-act-stat shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning"><i class="fas fa-tags"></i></div>
                    <div><div class="fw-bold fs-4 text-warning"><?= count($stats['types']) ?></div><div class="text-muted small">Types</div></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Timeline Column -->
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-transparent border-bottom-0 pt-3">
                    <h6 class="fw-bold mb-0"><i class="fas fa-stream me-2 text-primary"></i>Timeline</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($filtered)): ?>
                        <div class="text-center py-5">
                            <div class="mb-3"><i class="fas fa-history fa-4x text-muted opacity-25"></i></div>
                            <h5 class="text-muted"><?= $filter ? 'No activities of this type' : 'No Activities Yet' ?></h5>
                            <p class="text-muted small">Your activity history will appear here</p>
                        </div>
                    <?php else: ?>
                        <div class="emp-act-timeline">
                            <?php $lastDate = ''; foreach ($filtered as $a):
                                $actionDate = date('Y-m-d', strtotime($a['created_at'] ?? ''));
                                if ($actionDate !== $lastDate): $lastDate = $actionDate; ?>
                                    <div class="d-flex align-items-center gap-2 mb-3 mt-2">
                                        <div class="fw-semibold text-dark small"><?= date('d M Y', strtotime($actionDate)) ?></div>
                                        <div class="flex-grow-1" style="height:1px;background:#e2e8f0"></div>
                                        <?php if ($actionDate === date('Y-m-d')): ?>
                                            <span class="badge bg-success bg-opacity-10 text-success">Today</span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                                <div class="emp-act-item">
                                    <div class="emp-act-marker bg-<?= actColor($a['action'] ?? 'system') ?>">
                                        <i class="fas fa-<?= actIcon($a['action'] ?? 'system') ?>"></i>
                                    </div>
                                    <div class="emp-act-card">
                                        <div class="d-flex justify-content-between align-items-start mb-1">
                                            <div class="fw-semibold text-dark">
                                                <?= htmlspecialchars($a['description'] ?? ucfirst(str_replace('_', ' ', $a['action'] ?? 'Activity'))) ?>
                                            </div>
                                            <span class="badge bg-<?= actBadge($a['action'] ?? 'system') ?> bg-opacity-10 text-<?= actBadge($a['action'] ?? 'system') ?>">
                                                <?= ucfirst(htmlspecialchars($a['action'] ?? 'other')) ?>
                                            </span>
                                        </div>
                                        <?php
                                        $details = null;
                                        if (!empty($a['details'])) {
                                            $details = is_array($a['details']) ? $a['details'] : json_decode($a['details'], true);
                                        }
                                        ?>
                                        <?php if ($details && is_array($details) && count($details) > 0): ?>
                                            <div class="bg-light rounded p-2 mb-2" style="font-size:0.78rem;">
                                                <?php foreach ($details as $k => $v): ?>
                                                    <span class="me-3"><strong><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $k))) ?>:</strong> <?= htmlspecialchars(is_array($v) ? json_encode($v) : $v) ?></span>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($a['entity_type'])): ?>
                                            <div class="mb-1"><small class="text-muted"><i class="fas fa-link me-1"></i><?= htmlspecialchars(ucfirst($a['entity_type'])) ?><?php if (!empty($a['entity_id'])): ?> #<?= $a['entity_id'] ?><?php endif; ?></small></div>
                                        <?php endif; ?>
                                        <div class="d-flex align-items-center gap-3 emp-act-meta mt-1">
                                            <span><i class="fas fa-clock me-1"></i><?= date('h:i A', strtotime($a['created_at'] ?? '')) ?></span>
                                            <span><i class="fas fa-history me-1"></i><?= timeAgo($a['created_at'] ?? '') ?></span>
                                            <?php if (!empty($a['ip_address'])): ?>
                                                <span><i class="fas fa-globe me-1"></i><?= htmlspecialchars($a['ip_address']) ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Sidebar: Type Breakdown -->
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header bg-transparent border-bottom-0 pt-3">
                    <h6 class="fw-bold mb-0"><i class="fas fa-chart-pie me-2 text-primary"></i>By Type</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($stats['types'])): ?>
                        <p class="text-muted small mb-0">No data yet</p>
                    <?php else: ?>
                        <?php foreach ($stats['types'] as $type => $count): ?>
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle bg-<?= actColor($type) ?> bg-opacity-10" style="width:32px;height:32px;display:flex;align-items:center;justify-content:center;">
                                        <i class="fas fa-<?= actIcon($type) ?> text-<?= actColor($type) ?>" style="font-size:0.8rem;"></i>
                                    </div>
                                    <div>
                                        <div class="fw-semibold small text-dark"><?= ucfirst(htmlspecialchars($type)) ?></div>
                                        <div class="text-muted" style="font-size:0.7rem;"><?= $count ?> activities</div>
                                    </div>
                                </div>
                                <span class="badge bg-<?= actColor($type) ?> bg-opacity-10 text-<?= actColor($type) ?>"><?= $count ?></span>
                            </div>
                            <div class="emp-act-type-bar mb-3">
                                <div class="emp-act-type-bar-fill bg-<?= actColor($type) ?>" style="width: <?= $maxTypeCount > 0 ? round(($count / $maxTypeCount) * 100) : 0 ?>%"></div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Filter -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-transparent border-bottom-0 pt-3">
                    <h6 class="fw-bold mb-0"><i class="fas fa-filter me-2 text-primary"></i>Quick Filter</h6>
                </div>
                <div class="card-body d-flex flex-wrap gap-2">
                    <a href="/employee/activities" class="emp-act-type-btn <?= !$filter ? 'active' : '' ?>">All</a>
                    <?php foreach ($stats['types'] as $type => $count): ?>
                        <a href="/employee/activities?type=<?= urlencode($type) ?>" class="emp-act-type-btn <?= $filter === $type ? 'active' : '' ?>"><?= ucfirst(htmlspecialchars($type)) ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
