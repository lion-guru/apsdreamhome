<?php
$overall = $overall ?? ['tasks_completed' => 0, 'on_time_rate' => 0, 'rating' => 0, 'attendance_percent' => 0, 'total_tasks' => 0];
$reviews = $reviews ?? [];
$recent_tasks = $recent_tasks ?? [];

function gradeFromRating($r) {
    if ($r >= 4.5) return ['A+', 'success'];
    if ($r >= 4.0) return ['A', 'success'];
    if ($r >= 3.5) return ['B+', 'info'];
    if ($r >= 3.0) return ['B', 'primary'];
    if ($r >= 2.0) return ['C', 'warning'];
    return ['D', 'danger'];
}
function perfLevelBadge($level) {
    $map = ['excellent' => 'success', 'good' => 'info', 'average' => 'warning', 'needs_improvement' => 'danger', 'poor' => 'danger'];
    $cls = $map[strtolower($level ?? '')] ?? 'secondary';
    return '<span class="badge bg-' . $cls . '">' . ucfirst(htmlspecialchars($level ?? 'N/A')) . '</span>';
}
?>

<style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
.emp-perf-stat { border: none; border-radius: 14px; overflow: hidden; position: relative; }
.emp-perf-stat .stat-body { padding: 1.25rem; color: #fff; position: relative; z-index: 1; }
.emp-perf-stat .stat-bg { position: absolute; top: 0; right: 0; width: 100px; height: 100%; opacity: 0.1; font-size: 4rem; display: flex; align-items: center; justify-content: center; }
.emp-perf-stat .stat-val { font-size: 2rem; font-weight: 700; }
.emp-review-card { border: 1px solid #e2e8f0; border-radius: 12px; transition: all 0.2s; }
.emp-review-card:hover { box-shadow: 0 4px 15px rgba(0,0,0,0.08); transform: translateY(-1px); }
.emp-rating-stars { color: #f59e0b; letter-spacing: 2px; }
.emp-task-mini { border: 1px solid #e2e8f0; border-radius: 8px; padding: 8px 12px; font-size: 0.85rem; }
.emp-perf-progress { height: 8px; border-radius: 4px; background: #e2e8f0; }
.emp-perf-progress-fill { height: 100%; border-radius: 4px; }
</style>

<div class="container-fluid">
    <div class="mb-4">
        <h4 class="mb-1 fw-bold"><i class="fas fa-chart-line me-2 text-primary"></i>Performance Overview</h4>
        <p class="text-muted mb-0 small">Track your metrics, reviews, and task performance</p>
    </div>

    <!-- Top Stats -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card emp-perf-stat" class="style-35582">
                <div class="stat-body">
                    <div class="text-white-50 small mb-1"><i class="fas fa-check-double me-1"></i> Tasks Completed</div>
                    <div class="stat-val"><?= $overall['tasks_completed'] ?></div>
                    <div class="small text-white-50">of <?= $overall['total_tasks'] ?> total</div>
                </div>
                <div class="stat-bg"><i class="fas fa-check-double"></i></div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card emp-perf-stat" class="style-37236">
                <div class="stat-body">
                    <div class="text-white-50 small mb-1"><i class="fas fa-clock me-1"></i> On-Time Rate</div>
                    <div class="stat-val"><?= $overall['on_time_rate'] ?>%</div>
                    <div class="small text-white-50">delivery metric</div>
                </div>
                <div class="stat-bg"><i class="fas fa-clock"></i></div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card emp-perf-stat" class="style-74239">
                <div class="stat-body">
                    <div class="text-white-50 small mb-1"><i class="fas fa-star me-1"></i> Avg Rating</div>
                    <div class="stat-val"><?= number_format($overall['rating'], 1) ?></div>
                    <div class="small text-white-50">out of 5.0</div>
                </div>
                <div class="stat-bg"><i class="fas fa-star"></i></div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card emp-perf-stat" class="style-69747">
                <div class="stat-body">
                    <div class="text-white-50 small mb-1"><i class="fas fa-calendar-check me-1"></i> Attendance</div>
                    <div class="stat-val"><?= $overall['attendance_percent'] ?>%</div>
                    <div class="small text-white-50">last 30 days</div>
                </div>
                <div class="stat-bg"><i class="fas fa-calendar-check"></i></div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Left: Performance Gauge + Recent Tasks -->
        <div class="col-lg-7">
            <!-- Performance Meter -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0 fw-semibold"><i class="fas fa-tachometer-alt me-2 text-primary"></i>Performance Meter</h6>
                </div>
                <div class="card-body">
                    <?php
                    $overallScore = $overall['total_tasks'] > 0
                        ? round(($overall['tasks_completed'] / max($overall['total_tasks'], 1)) * 50 + $overall['on_time_rate'] * 0.3 + $overall['rating'] * 10, 0)
                        : 0;
                    $overallScore = min(100, max(0, $overallScore));
                    $scoreColor = $overallScore >= 80 ? '#10b981' : ($overallScore >= 60 ? '#f59e0b' : '#ef4444');
                    ?>
                    <div class="text-center mb-3">
                        <div class="style-86338">
                            <svg viewBox="0 0 160 80" class="style-27995">
                                <path d="M 10 75 A 70 70 0 0 1 150 75" fill="none" stroke="#e2e8f0" stroke-width="12" stroke-linecap="round"/>
                                <path d="M 10 75 A 70 70 0 0 1 150 75" fill="none" stroke="<?= $scoreColor ?>" stroke-width="12" stroke-linecap="round"
                                    stroke-dasharray="<?= $overallScore * 2.2 ?> 220" class="style-21665"/>
                            </svg>
                            <div class="style-7521">
                                <div class="fw-bold fs-3" class="style-42449"><?= $overallScore ?>%</div>
                                <div class="text-muted small">Overall Score</div>
                            </div>
                        </div>
                    </div>
                    <!-- Breakdown bars -->
                    <?php
                    $metrics = [
                        ['label' => 'Task Completion', 'value' => $overall['total_tasks'] > 0 ? round(($overall['tasks_completed'] / $overall['total_tasks']) * 100) : 0, 'color' => '#7c2d12'],
                        ['label' => 'On-Time Delivery', 'value' => $overall['on_time_rate'], 'color' => '#065f46'],
                        ['label' => 'Rating', 'value' => round($overall['rating'] * 20), 'color' => '#92400e'],
                        ['label' => 'Attendance', 'value' => $overall['attendance_percent'], 'color' => '#1e3a5f'],
                    ];
                    foreach ($metrics as $m): ?>
                        <div class="d-flex align-items-center gap-3 mb-2">
                            <div class="text-muted small" class="style-43624"><?= $m['label'] ?></div>
                            <div class="flex-grow-1 emp-perf-progress">
                                <div class="emp-perf-progress-fill" class="style-50659"></div>
                            </div>
                            <div class="fw-semibold small" class="style-66568"><?= $m['value'] ?>%</div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Recent Tasks -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0 fw-semibold"><i class="fas fa-clipboard-list me-2 text-primary"></i>Recent Tasks</h6>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($recent_tasks)): ?>
                        <div class="text-center text-muted py-4">No tasks found</div>
                    <?php else: ?>
                        <?php foreach ($recent_tasks as $rt):
                            $s = strtolower($rt['status'] ?? '');
                            $sColor = $s === 'completed' ? 'success' : ($s === 'in progress' ? 'info' : 'secondary');
                            $isOverdue = !empty($rt['due_date']) && $rt['due_date'] < date('Y-m-d') && $s !== 'completed';
                        ?>
                            <div class="d-flex align-items-center gap-3 px-3 py-2 border-bottom">
                                <div class="emp-task-mini d-flex align-items-center gap-2 flex-grow-1 <?= $isOverdue ? 'border-danger' : '' ?>">
                                    <span class="badge bg-<?= $sColor ?> bg-opacity-10 text-<?= $sColor ?>"><?= $s === 'completed' ? 'Done' : ucfirst($rt['status'] ?? 'Pending') ?></span>
                                    <span class="<?= $s === 'completed' ? 'text-decoration-line-through text-muted' : '' ?>"><?= htmlspecialchars($rt['title'] ?? '') ?></span>
                                </div>
                                <div class="text-end">
                                    <?php if (!empty($rt['due_date'])): ?>
                                        <span class="small <?= $isOverdue ? 'text-danger' : 'text-muted' ?>"><?= date('d M', strtotime($rt['due_date'])) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Right: Reviews -->
        <div class="col-lg-5">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-semibold"><i class="fas fa-star-half-alt me-2 text-primary"></i>Performance Reviews</h6>
                    <span class="badge bg-primary bg-opacity-10 text-primary"><?= count($reviews) ?> reviews</span>
                </div>
                <div class="card-body">
                    <?php if (empty($reviews)): ?>
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-clipboard fa-3x mb-3 opacity-25"></i>
                            <p>No reviews yet</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($reviews as $rv):
                            [$grade, $gradeColor] = gradeFromRating((float)($rv['overall_rating'] ?? 0));
                        ?>
                            <div class="card emp-review-card mb-3">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <span class="badge bg-<?= $gradeColor ?> fs-6 me-2"><?= $grade ?></span>
                                            <span class="fw-bold fs-5"><?= number_format((float)($rv['overall_rating'] ?? 0), 1) ?></span>
                                            <span class="text-muted">/5.0</span>
                                        </div>
                                        <?= perfLevelBadge($rv['performance_level'] ?? '') ?>
                                    </div>
                                    <div class="emp-rating-stars mb-2">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star<?= $i <= round((float)($rv['overall_rating'] ?? 0)) ? '' : '-o' ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <div class="small text-muted mb-2">
                                        <i class="fas fa-calendar me-1"></i> <?= date('d M Y', strtotime($rv['review_date'] ?? $rv['created_at'] ?? 'now')) ?>
                                        <?php if (!empty($rv['reviewer_name'])): ?>
                                            &middot; <i class="fas fa-user me-1"></i> <?= htmlspecialchars($rv['reviewer_name'] ?? '') ?>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!empty($rv['strengths'])): ?>
                                        <div class="small mb-1"><strong class="text-success"><i class="fas fa-thumbs-up me-1"></i>Strengths:</strong> <?= htmlspecialchars($rv['strengths'] ?? '') ?></div>
                                    <?php endif; ?>
                                    <?php if (!empty($rv['areas_for_improvement'])): ?>
                                        <div class="small mb-1"><strong class="text-warning"><i class="fas fa-bolt me-1"></i>Improve:</strong> <?= htmlspecialchars($rv['areas_for_improvement'] ?? '') ?></div>
                                    <?php endif; ?>
                                    <?php if (!empty($rv['reviewer_comments'])): ?>
                                        <div class="small mt-2 p-2 bg-light rounded"><i class="fas fa-quote-left me-1 text-muted"></i> <?= htmlspecialchars($rv['reviewer_comments'] ?? '') ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
