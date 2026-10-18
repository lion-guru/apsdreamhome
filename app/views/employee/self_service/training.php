<?php
/** Employee Self-Service Training */

$page_title = $page_title ?? 'My Training';
$in_progress = $in_progress ?? [];
$completed = $completed ?? [];
$assigned = $assigned ?? [];
$summary = $summary ?? [];
?>

<style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
.course-card {
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 1.25rem;
    margin-bottom: 1rem;
    background: #fafafa;
    transition: all 0.2s;
}
.course-card:hover {
    border-color: #7c2d12;
    box-shadow: 0 4px 15px rgba(124,45,18,0.1);
}
.course-card.in_progress { border-left: 4px solid #3b82f6; }
.course-card.completed { border-left: 4px solid #22c55e; }
.course-card.assigned { border-left: 4px solid #f59e0b; }
.course-card.overdue { border-left: 4px solid #ef4444; }
.progress-bar-mini { height: 8px; border-radius: 4px; }
.category-badge { font-size: 0.7rem; padding: 0.15rem 0.4rem; border-radius: 15px; }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold"><i class="fas fa-graduation-cap me-2 text-primary"></i>My Training</h4>
            <p class="text-muted mb-0 small">Track your learning progress and assigned courses</p>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card stat-card border-0 shadow-sm bg-primary text-white">
                <div class="card-body text-center">
                    <div class="fw-bold fs-3"><?= $summary['total'] ?? 0 ?></div>
                    <div class="text-white-50 small">Total Courses</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card border-0 shadow-sm bg-info text-white">
                <div class="card-body text-center">
                    <div class="fw-bold fs-3"><?= $summary['in_progress'] ?? 0 ?></div>
                    <div class="text-white-50 small">In Progress</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card border-0 shadow-sm bg-success text-white">
                <div class="card-body text-center">
                    <div class="fw-bold fs-3"><?= $summary['completed'] ?? 0 ?></div>
                    <div class="text-white-50 small">Completed</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card border-0 shadow-sm bg-warning text-white">
                <div class="card-body text-center">
                    <div class="fw-bold fs-3"><?= $summary['overdue'] ?? 0 ?></div>
                    <div class="text-white-50 small">Overdue</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <ul class="nav nav-tabs mb-4" id="trainingTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="inprogress-tab" data-bs-toggle="tab" data-bs-target="#inprogress" type="button" role="tab">
                In Progress <span class="badge bg-info ms-1"><?= count($in_progress) ?></span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="assigned-tab" data-bs-toggle="tab" data-bs-target="#assigned" type="button" role="tab">
                Assigned <span class="badge bg-warning text-dark ms-1"><?= count($assigned) ?></span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="completed-tab" data-bs-toggle="tab" data-bs-target="#completed" type="button" role="tab">
                Completed <span class="badge bg-success ms-1"><?= count($completed) ?></span>
            </button>
        </li>
    </ul>

    <div class="tab-content" id="trainingTabsContent">
        <!-- In Progress -->
        <div class="tab-pane fade show active" id="inprogress" role="tabpanel">
            <?php if (empty($in_progress)): ?>
            <div class="text-center py-5">
                <i class="fas fa-play-circle fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No In-Progress Courses</h5>
                <p class="text-muted small">Start a course to see it here</p>
            </div>
            <?php else: ?>
            <div class="row">
                <?php foreach ($in_progress as $course): 
                    $isOverdue = $course['due_date'] && $course['due_date'] < date('Y-m-d') && $course['status'] !== 'completed';
                ?>
                <div class="col-md-6 col-lg-4 mb-3">
                    <div class="course-card in_progress">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="category-badge bg-info"><?= htmlspecialchars(ucfirst($course['course_category'] ?? 'other')) ?></span>
                            <span class="badge bg-primary">In Progress</span>
                        </div>
                        <h6 class="fw-semibold"><?= htmlspecialchars($course['course_title']) ?></h6>
                        <p class="small text-muted mb-2"><?= htmlspecialchars($course['course_code']) ?></p>
                        <div class="progress progress-bar-mini mb-2">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $course['progress'] ?>%" aria-valuenow="<?= $course['progress'] ?>" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <div class="d-flex justify-content-between small text-muted mb-2">
                            <span><?= $course['progress'] ?>% Complete</span>
                            <?php if ($course['due_date']): ?>
                                <span class="<?= $isOverdue ? 'text-danger fw-bold' : '' ?>">
                                    Due: <?= date('d M Y', strtotime($course['due_date'])) ?>
                                    <?php if ($isOverdue): ?><i class="fas fa-exclamation-circle ms-1"></i><?php endif; ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($course['material_url'])): ?>
                            <a href="<?= htmlspecialchars($course['material_url']) ?>" target="_blank" class="btn btn-sm btn-outline-primary w-100">
                                <i class="fas fa-book me-1"></i> View Materials
                            </a>
                        <?php else: ?>
                            <a href="<?= BASE_URL ?>/employee/self-service/training/<?= $course['id'] ?>" class="btn btn-sm btn-primary w-100">
                                <i class="fas fa-play me-1"></i> Continue Learning
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Assigned -->
        <div class="tab-pane fade" id="assigned" role="tabpanel">
            <?php if (empty($assigned)): ?>
            <div class="text-center py-5">
                <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No Assigned Courses</h5>
                <p class="text-muted small">New assignments will appear here</p>
            </div>
            <?php else: ?>
            <div class="row">
                <?php foreach ($assigned as $course): 
                    $isOverdue = $course['due_date'] && $course['due_date'] < date('Y-m-d') && $course['status'] !== 'completed';
                    $statusClass = $isOverdue ? 'overdue' : 'assigned';
                ?>
                <div class="col-md-6 col-lg-4 mb-3">
                    <div class="course-card <?= $statusClass ?>">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="category-badge bg-warning text-dark"><?= htmlspecialchars(ucfirst($course['course_category'] ?? 'other')) ?></span>
                            <span class="badge bg-<?= $isOverdue ? 'danger' : 'warning' ?> text-<?= $isOverdue ? 'white' : 'dark' ?>">
                                <?= $isOverdue ? 'Overdue' : 'Assigned' ?>
                            </span>
                        </div>
                        <h6 class="fw-semibold"><?= htmlspecialchars($course['course_title']) ?></h6>
                        <p class="small text-muted mb-2"><?= htmlspecialchars($course['course_code']) ?></p>
                        <?php if ($course['due_date']): ?>
                            <div class="small text-muted mb-2">
                                Due: <?= date('d M Y', strtotime($course['due_date'])) ?>
                                <?php if ($isOverdue): ?><i class="fas fa-exclamation-circle text-danger ms-1"></i><?php endif; ?>
                            </div>
                        <?php endif; ?>
                        <div class="small text-muted mb-3">
                            Format: <?= htmlspecialchars(ucfirst($course['format'])) ?> • <?= $course['duration_hours'] ?> hrs
                            <?php if (!empty($course['assessment_required'])): ?>
                                • Assessment Required
                            <?php endif; ?>
                        </div>
                        <a href="<?= BASE_URL ?>/employee/self-service/training/<?= $course['id'] ?>" class="btn btn-sm btn-primary w-100">
                            <i class="fas fa-play me-1"></i> Start Course
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Completed -->
        <div class="tab-pane fade" id="completed" role="tabpanel">
            <?php if (empty($completed)): ?>
            <div class="text-center py-5">
                <i class="fas fa-check-circle fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No Completed Courses</h5>
                <p class="text-muted small">Complete courses to earn certificates</p>
            </div>
            <?php else: ?>
            <div class="row">
                <?php foreach ($completed as $course): ?>
                <div class="col-md-6 col-lg-4 mb-3">
                    <div class="course-card completed">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="category-badge bg-success"><?= htmlspecialchars(ucfirst($course['course_category'] ?? 'other')) ?></span>
                            <span class="badge bg-success">Completed</span>
                        </div>
                        <h6 class="fw-semibold"><?= htmlspecialchars($course['course_title']) ?></h6>
                        <p class="small text-muted mb-2"><?= htmlspecialchars($course['course_code']) ?></p>
                        <div class="small text-muted mb-3">
                            Completed: <?= $course['completed_at'] ? date('d M Y', strtotime($course['completed_at'])) : '—' ?>
                            <?php if (!empty($course['score'])): ?>
                                • Score: <span class="fw-semibold <?= $course['score'] >= 70 ? 'text-success' : 'text-danger' ?>"><?= $course['score'] ?>%</span>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($course['certificate_issued'])): ?>
                            <a href="<?= BASE_URL ?>/employee/self-service/certifications" class="btn btn-sm btn-outline-success w-100">
                                <i class="fas fa-certificate me-1"></i> View Certificate
                            </a>
                        <?php else: ?>
                            <button class="btn btn-sm btn-outline-secondary w-100" disabled>
                                <i class="fas fa-certificate me-1"></i> Certificate Pending
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>