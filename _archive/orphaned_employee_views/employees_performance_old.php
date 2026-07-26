<?php
$overall = $overall ?? ['tasks_completed' => 0, 'on_time_rate' => 0, 'rating' => 0, 'attendance_percent' => 0, 'total_tasks' => 0];
$reviews = $reviews ?? [];
$recent_tasks = $recent_tasks ?? [];
?>

<style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
.perf-stat { border: none; border-radius: 12px; transition: transform 0.2s; }
.perf-stat:hover { transform: translateY(-2px); }
.perf-stat .stat-icon { width: 52px; height: 52px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; }
.rating-stars { color: #f59e0b; letter-spacing: 2px; }
.review-card { border: 1px solid #e2e8f0; border-radius: 12px; transition: all 0.2s; }
.review-card:hover { box-shadow: 0 4px 15px rgba(0,0,0,0.06); }
.task-row { border-left: 3px solid; }
.task-row.priority-high { border-left-color: #ef4444; }
.task-row.priority-medium { border-left-color: #f59e0b; }
.task-row.priority-low { border-left-color: #10b981; }
</style>

<div class="container-fluid">
    <div class="mb-4">
        <h4 class="mb-1 fw-bold"><i class="fas fa-chart-line me-2 text-primary"></i>Performance Overview</h4>
        <p class="text-muted mb-0 small">Track your performance metrics, reviews, and goals</p>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card perf-stat shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary"><i class="fas fa-check-double"></i></div>
                    <div>
                        <div class="fw-bold fs-4"><?= (int)($overall['tasks_completed'] ?? 0) ?></div>
                        <div class="text-muted small">Tasks Completed</div>
                        <small class="text-muted">of <?= (int)($overall['total_tasks'] ?? 0) ?> total</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card perf-stat shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon bg-success bg-opacity-10 text-success"><i class="fas fa-clock"></i></div>
                    <div>
                        <div class="fw-bold fs-4 text-success"><?= (int)($overall['on_time_rate'] ?? 0) ?>%</div>
                        <div class="text-muted small">On-Time Rate</div>
                        <small class="text-muted"><?= (int)($overall['tasks_completed'] ?? 0) > 0 ? 'Tasks delivered on time' : 'No completed tasks' ?></small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card perf-stat shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning"><i class="fas fa-star"></i></div>
                    <div>
                        <div class="fw-bold fs-4 text-warning"><?= number_format((float)($overall['rating'] ?? 0), 1) ?></div>
                        <div class="text-muted small">Avg Rating</div>
                        <div class="rating-stars">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star<?= $i <= round((float)($overall['rating'] ?? 0)) ? '' : '-o' ?>"></i>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card perf-stat shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon bg-info bg-opacity-10 text-info"><i class="fas fa-user-check"></i></div>
                    <div>
                        <div class="fw-bold fs-4 text-info"><?= (int)($overall['attendance_percent'] ?? 0) ?>%</div>
                        <div class="text-muted small">Attendance</div>
                        <small class="text-muted">Last 30 days</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Performance Rating Visual -->
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-bottom-0 pt-3">
                    <h6 class="fw-bold mb-0"><i class="fas fa-bullseye me-2 text-primary"></i>Performance Score</h6>
                </div>
                <div class="card-body text-center">
                    <?php
                    $taskScore = $overall['total_tasks'] > 0 ? round(($overall['tasks_completed'] / $overall['total_tasks']) * 100) : 0;
                    $overallScore = round(($taskScore + (float)($overall['on_time_rate'] ?? 0) + ((float)($overall['rating'] ?? 0) / 5 * 100) + (float)($overall['attendance_percent'] ?? 0)) / 4);
                    $scoreColor = $overallScore >= 75 ? '#10b981' : ($overallScore >= 50 ? '#f59e0b' : '#ef4444');
                    ?>
                    <div class="position-relative d-inline-block mb-3">
                        <svg width="160" height="160" viewBox="0 0 160 160">
                            <circle cx="80" cy="80" r="70" fill="none" stroke="#e2e8f0" stroke-width="12"/>
                            <circle cx="80" cy="80" r="70" fill="none" stroke="<?= $scoreColor ?>" stroke-width="12" stroke-linecap="round"
                                stroke-dasharray="<?= 2 * M_PI * 70 ?>" stroke-dashoffset="<?= 2 * M_PI * 70 * (1 - $overallScore / 100) ?>"
                                transform="rotate(-90 80 80)" style="transition: stroke-dashoffset 1s ease;"/>
                        </svg>
                        <div class="position-absolute top-50 start-50 translate-middle text-center">
                            <div class="fw-bold fs-2" style="color: <?= $scoreColor ?>"><?= $overallScore ?>%</div>
                            <small class="text-muted">Overall</small>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="d-flex justify-content-between small mb-1"><span class="text-muted">Task Completion</span><span class="fw-semibold"><?= $taskScore ?>%</span></div>
                        <div class="progress mb-2" style="height:6px;"><div class="progress-bar bg-primary" style="width:<?= $taskScore ?>%"></div></div>
                        <div class="d-flex justify-content-between small mb-1"><span class="text-muted">On-Time Delivery</span><span class="fw-semibold"><?= (int)($overall['on_time_rate'] ?? 0) ?>%</span></div>
                        <div class="progress mb-2" style="height:6px;"><div class="progress-bar bg-success" style="width:<?= (int)($overall['on_time_rate'] ?? 0) ?>%"></div></div>
                        <div class="d-flex justify-content-between small mb-1"><span class="text-muted">Rating</span><span class="fw-semibold"><?= number_format((float)($overall['rating'] ?? 0) / 5 * 100, 0) ?>%</span></div>
                        <div class="progress mb-2" style="height:6px;"><div class="progress-bar bg-warning" style="width:<?= number_format((float)($overall['rating'] ?? 0) / 5 * 100, 0) ?>%"></div></div>
                        <div class="d-flex justify-content-between small mb-1"><span class="text-muted">Attendance</span><span class="fw-semibold"><?= (int)($overall['attendance_percent'] ?? 0) ?>%</span></div>
                        <div class="progress" style="height:6px;"><div class="progress-bar bg-info" style="width:<?= (int)($overall['attendance_percent'] ?? 0) ?>%"></div></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Tasks -->
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-bottom-0 pt-3">
                    <h6 class="fw-bold mb-0"><i class="fas fa-tasks me-2 text-primary"></i>Recent Tasks</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($recent_tasks)): ?>
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-clipboard fa-3x mb-2 opacity-25"></i>
                            <p class="mb-0 small">No recent tasks</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($recent_tasks as $rt):
                            $prio = strtolower($rt['priority'] ?? 'medium');
                            $prioClass = 'priority-' . $prio;
                        ?>
                            <div class="d-flex align-items-start gap-2 mb-3 pb-3 border-bottom task-row <?= $prioClass ?> ps-2">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 fw-semibold" style="font-size:0.9rem;"><?= htmlspecialchars($rt['title'] ?? '') ?></h6>
                                    <div class="d-flex gap-2 align-items-center">
                                        <?php
                                        $sColor = match(strtolower($rt['status'] ?? '')) { 'completed' => 'success', 'in progress' => 'info', default => 'warning' };
                                        ?>
                                        <span class="badge bg-<?= $sColor ?> bg-opacity-10 text-<?= $sColor ?>" style="font-size:0.7rem;"><?= ucfirst(htmlspecialchars($rt['status'] ?? 'pending')) ?></span>
                                        <span class="badge bg-<?= match($prio) { 'high' => 'danger', 'low' => 'success', default => 'warning' } ?> bg-opacity-10 text-<?= match($prio) { 'high' => 'danger', 'low' => 'success', default => 'warning' } ?>" style="font-size:0.7rem;"><?= ucfirst(htmlspecialchars($prio)) ?></span>
                                    </div>
                                    <?php if (!empty($rt['due_date'])): ?>
                                        <small class="text-muted"><i class="fas fa-calendar me-1"></i><?= date('d M', strtotime($rt['due_date'])) ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Performance Reviews -->
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-bottom-0 pt-3">
                    <h6 class="fw-bold mb-0"><i class="fas fa-star me-2 text-primary"></i>Performance Reviews</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($reviews)): ?>
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-award fa-3x mb-2 opacity-25"></i>
                            <p class="mb-0 small">No reviews yet</p>
                            <small class="text-muted">Reviews will appear after evaluation</small>
                        </div>
                    <?php else: ?>
                        <?php foreach (array_slice($reviews, 0, 5) as $r): ?>
                            <div class="review-card p-3 mb-2">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="rating-stars mb-1">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="fas fa-star<?= $i <= (int)($r['overall_rating'] ?? 0) ? '' : '-o' ?>" style="font-size:0.75rem;"></i>
                                            <?php endfor; ?>
                                        </div>
                                        <?php if (!empty($r['reviewer_name'])): ?>
                                            <small class="text-muted">by <?= htmlspecialchars($r['reviewer_name']) ?></small>
                                        <?php endif; ?>
                                    </div>
                                    <small class="text-muted"><?= !empty($r['review_date']) ? date('d M Y', strtotime($r['review_date'])) : '' ?></small>
                                </div>
                                <?php if (!empty($r['comments'])): ?>
                                    <p class="small mb-0 mt-1" style="color:#475569;"><?= htmlspecialchars(mb_substr($r['comments'], 0, 100)) ?><?= strlen($r['comments']) > 100 ? '...' : '' ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
