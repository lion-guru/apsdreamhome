<?php
$activities = $activities ?? [];
$stats = $stats ?? ['total' => 0, 'today' => 0, 'this_week' => 0, 'types' => []];
$filter = $filter ?? '';
?>

<style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
.act-timeline { position: relative; padding-left: 30px; }
.act-timeline::before { content: ''; position: absolute; left: 14px; top: 0; bottom: 0; width: 2px; background: linear-gradient(to bottom, #3b82f6, #8b5cf6); }
.act-dot { position: absolute; left: -28px; top: 18px; width: 12px; height: 12px; border-radius: 50%; border: 2px solid #fff; box-shadow: 0 0 0 2px #3b82f6; }
.act-dot.type-login { background: #3b82f6; box-shadow: 0 0 0 2px #3b82f6; }
.act-dot.type-task { background: #10b981; box-shadow: 0 0 0 2px #10b981; }
.act-dot.type-attendance { background: #f59e0b; box-shadow: 0 0 0 2px #f59e0b; }
.act-dot.type-leave { background: #ef4444; box-shadow: 0 0 0 2px #ef4444; }
.act-dot.type-document { background: #8b5cf6; box-shadow: 0 0 0 2px #8b5cf6; }
.act-dot.type-system { background: #6b7280; box-shadow: 0 0 0 2px #6b7280; }
.act-card { border: 1px solid #e2e8f0; border-radius: 12px; transition: all 0.2s; }
.act-card:hover { border-color: #3b82f6; box-shadow: 0 4px 15px rgba(59,130,246,0.08); }
.act-stat { border: none; border-radius: 12px; }
.filter-btn { border-radius: 20px; font-size: 0.85rem; padding: 6px 16px; }
.filter-btn.active { background: #3b82f6; color: #fff; border-color: #3b82f6; }
</style>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold"><i class="fas fa-history me-2 text-primary"></i>Activity Timeline</h4>
            <p class="text-muted mb-0 small">Your recent activity history</p>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card act-stat shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary" class="style-89430"><i class="fas fa-list"></i></div>
                    <div><div class="fw-bold fs-4"><?= $stats['total'] ?></div><div class="text-muted small">Total Activities</div></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card act-stat shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon bg-success bg-opacity-10 text-success" class="style-89430"><i class="fas fa-calendar-day"></i></div>
                    <div><div class="fw-bold fs-4 text-success"><?= $stats['today'] ?></div><div class="text-muted small">Today</div></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card act-stat shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon bg-info bg-opacity-10 text-info" class="style-89430"><i class="fas fa-calendar-week"></i></div>
                    <div><div class="fw-bold fs-4 text-info"><?= $stats['this_week'] ?></div><div class="text-muted small">This Week</div></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card act-stat shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning" class="style-89430"><i class="fas fa-chart-pie"></i></div>
                    <div><div class="fw-bold fs-4 text-warning"><?= count($stats['types']) ?></div><div class="text-muted small">Activity Types</div></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Type Breakdown -->
    <?php if (!empty($stats['types'])): ?>
    <div class="card shadow-sm mb-4 border-0">
        <div class="card-body py-3">
            <span class="fw-semibold small me-3">Activity Types:</span>
            <?php
            $typeIcons = ['login' => 'sign-in-alt', 'task' => 'tasks', 'attendance' => 'calendar-check', 'leave' => 'calendar-minus', 'document' => 'file-alt', 'system' => 'cog'];
            $typeColors = ['login' => 'primary', 'task' => 'success', 'attendance' => 'warning', 'leave' => 'danger', 'document' => 'purple', 'system' => 'secondary'];
            foreach ($stats['types'] as $type => $count):
                $icon = $typeIcons[$type] ?? 'circle';
                $color = $typeColors[$type] ?? 'secondary';
            ?>
                <span class="badge bg-<?= $color ?> bg-opacity-10 text-<?= $color ?> me-2 mb-1" class="style-79756">
                    <i class="fas fa-<?= $icon ?> me-1"></i><?= ucfirst(htmlspecialchars($type)) ?>: <?= $count ?>
                </span>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Filter Tabs -->
    <div class="d-flex gap-2 mb-4 flex-wrap">
        <?php
        $allFilters = ['' => 'All', 'login' => 'Login', 'task' => 'Tasks', 'attendance' => 'Attendance', 'leave' => 'Leaves', 'document' => 'Documents', 'system' => 'System'];
        foreach ($allFilters as $key => $label):
            $isActive = $filter === $key ? 'active' : '';
        ?>
            <a href="?type=<?= $key ?>" class="btn btn-sm btn-outline-primary filter-btn <?= $isActive ?>"><?= $label ?></a>
        <?php endforeach; ?>
    </div>

    <!-- Activity Timeline -->
    <?php if (empty($activities)): ?>
        <div class="card shadow-sm border-0">
            <div class="card-body text-center py-5">
                <div class="mb-3"><i class="fas fa-history fa-4x text-muted opacity-25"></i></div>
                <h5 class="text-muted">No Activities Found</h5>
                <p class="text-muted small">No activity records match your current filter.</p>
            </div>
        </div>
    <?php else: ?>
        <div class="act-timeline">
            <?php foreach ($activities as $a):
                $actType = $a['action'] ?? 'system';
                $dotClass = 'type-' . ($actType);
                $timestamp = $a['created_at'] ?? $a['timestamp'] ?? '';
                $timeAgo = '';
                if ($timestamp) {
                    $diff = time() - strtotime($timestamp);
                    if ($diff < 60) $timeAgo = 'Just now';
                    elseif ($diff < 3600) $timeAgo = floor($diff / 60) . 'm ago';
                    elseif ($diff < 86400) $timeAgo = floor($diff / 3600) . 'h ago';
                    else $timeAgo = floor($diff / 86400) . 'd ago';
                }
            ?>
                <div class="card act-card shadow-sm mb-3 ms-4 position-relative">
                    <div class="act-dot <?= $dotClass ?>"></div>
                    <div class="card-body py-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <h6 class="mb-1 fw-semibold"><?= htmlspecialchars($a['description'] ?? $a['action'] ?? 'Activity') ?></h6>
                                <?php if (!empty($a['context'])): ?>
                                    <p class="text-muted small mb-1">
                                        <?php
                                        $ctx = $a['context'];
                                        if (is_string($ctx)) $ctx = json_decode($ctx, true);
                                        if (is_array($ctx) && !empty($ctx)) {
                                            echo htmlspecialchars(implode(', ', array_slice(array_map(function($k, $v) { return $k . ': ' . (is_array($v) ? json_encode($v) : $v); }, array_keys($ctx), array_values($ctx)), 0, 3)));
                                        }
                                        ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                            <div class="text-end ms-3">
                                <small class="text-muted"><?= htmlspecialchars($timeAgo) ?></small>
                                <div><span class="badge bg-<?= $typeColors[$actType] ?? 'secondary' ?> bg-opacity-10 text-<?= $typeColors[$actType] ?? 'secondary' ?>" class="style-68658"><?= ucfirst(htmlspecialchars($actType)) ?></span></div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
