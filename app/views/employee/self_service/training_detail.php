<?php
/** Employee Self-Service Training Course Detail */

$page_title = $page_title ?? 'Course Details';
$assignment = $assignment ?? [];
?>

<style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
.module-item {
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 0.75rem;
    background: white;
    transition: all 0.2s;
}
.module-item:hover {
    border-color: #7c2d12;
}
.module-item.completed {
    background: #f0fdf4;
    border-color: #22c55e;
}
.module-item.current {
    border-color: #3b82f6;
    background: #eff6ff;
}
.module-item.locked {
    opacity: 0.6;
    background: #f8fafc;
}
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="<?= BASE_URL ?>/employee/self-service/training" class="btn btn-outline-secondary btn-sm mb-2">
                <i class="fas fa-arrow-left me-1"></i> Back to Training
            </a>
            <h4 class="mb-1 fw-bold"><?= htmlspecialchars($assignment['course_title']) ?></h4>
            <p class="text-muted mb-0 small"><?= htmlspecialchars($assignment['course_code']) ?> • <?= htmlspecialchars(ucfirst($assignment['course_category'] ?? 'other')) ?></p>
        </div>
        <div class="d-flex gap-2">
            <span class="badge bg-<?= 
                $assignment['status'] === 'completed' ? 'success' : 
                ($assignment['status'] === 'in_progress' ? 'primary' : 'warning') 
            ?> fs-6">
                <?= ucfirst(str_replace('_', ' ', $assignment['status'])) ?>
            </span>
        </div>
    </div>

    <!-- Progress Header -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-center">
                <div class="col-md-4">
                    <div class="text-center">
                        <div class="fw-bold fs-2 text-primary"><?= $assignment['progress'] ?>%</div>
                        <div class="text-muted small">Progress</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="progress" style="height: 16px;">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $assignment['progress'] ?>%" aria-valuenow="<?= $assignment['progress'] ?>" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
                <div class="col-md-4 text-center">
                    <div class="small text-muted">
                        <?php if (!empty($assignment['score'])): ?>
                            Score: <span class="fw-semibold <?= $assignment['score'] >= 70 ? 'text-success' : 'text-danger' ?>"><?= $assignment['score'] ?>%</span>
                        <?php else: ?>
                            Not graded yet
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Course Info -->
        <div class="col-lg-8">
            <!-- Course Details -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0 fw-semibold"><i class="fas fa-info-circle me-2"></i>Course Details</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted">Format</label>
                            <div><?= htmlspecialchars(ucfirst($assignment['format'])) ?></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted">Duration</label>
                            <div><?= $assignment['duration_hours'] ?> hours</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted">Assessment</label>
                            <div><?= !empty($assignment['assessment_required']) ? 'Required (Passing: ' . $assignment['passing_score'] . '%)' : 'No assessment' ?></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted">Valid For</label>
                            <div><?= $assignment['valid_for_months'] ?? 12 ?> months</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold small text-muted">Description</label>
                            <div class="text-muted"><?= htmlspecialchars($assignment['description'] ?? 'No description') ?></div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold small text-muted">Prerequisites</label>
                            <div class="text-muted"><?= htmlspecialchars($assignment['prerequisites'] ?? 'None') ?></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted">Instructor</label>
                            <div><?= htmlspecialchars($assignment['instructor_name'] ?? '—') ?><?= !empty($assignment['instructor_email']) ? ' (' . $assignment['instructor_email'] . ')' : '' ?></div>
                        </div>
                        <?php if (!empty($assignment['material_url'])): ?>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted">Materials</label>
                            <a href="<?= htmlspecialchars($assignment['material_url']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-external-link-alt me-1"></i> View Materials
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Learning Modules (simulated) -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-semibold"><i class="fas fa-book me-2"></i>Learning Modules</h5>
                    <?php if ($assignment['status'] === 'in_progress'): ?>
                        <div class="d-flex gap-2">
                            <input type="range" id="progressSlider" min="0" max="100" value="<?= $assignment['progress'] ?>" class="form-range flex-grow-1 me-2" style="max-width: 200px;">
                            <span class="fw-semibold text-primary" id="progressValue"><?= $assignment['progress'] ?>%</span>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <div id="modulesContainer">
                        <?php 
                        // Simulated modules based on progress
                        $modules = [
                            'Introduction & Overview',
                            'Core Concepts & Fundamentals',
                            'Practical Applications',
                            'Case Studies & Examples',
                            'Assessment & Quiz'
                        ];
                        foreach ($modules as $i => $module): 
                            $moduleProgress = min(100, max(0, $assignment['progress'] - $i * 20));
                            $status = $moduleProgress >= 100 ? 'completed' : ($moduleProgress > 0 ? 'current' : 'locked');
                        ?>
                        <div class="module-item <?= $status ?>">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="flex-shrink-0">
                                        <?php if ($status === 'completed'): ?>
                                            <i class="fas fa-check-circle text-success fa-lg"></i>
                                        <?php elseif ($status === 'current'): ?>
                                            <i class="fas fa-play-circle text-primary fa-lg"></i>
                                        <?php else: ?>
                                            <i class="fas fa-lock text-muted fa-lg"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <div class="fw-semibold"><?= $module ?></div>
                                        <div class="small text-muted">
                                            <?php if ($status === 'completed'): ?>
                                                <i class="fas fa-check me-1"></i> Completed
                                            <?php elseif ($status === 'current'): ?>
                                                <i class="fas fa-play me-1"></i> In Progress (<?= $moduleProgress ?>%)
                                            <?php else: ?>
                                                <i class="fas fa-lock me-1"></i> Locked
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <?php if ($status !== 'locked'): ?>
                                        <div class="progress" style="width: 100px; height: 6px;">
                                            <div class="progress-bar bg-<?= $status === 'completed' ? 'success' : 'primary' ?>" role="progressbar" style="width: <?= $moduleProgress ?>%"></div>
                                        </div>
                                    <?php endif; ?>
                                    <button class="btn btn-sm btn-<?= $status === 'completed' ? 'outline-success' : ($status === 'current' ? 'outline-primary' : 'outline-secondary disabled') ?>" 
                                            onclick="<?= $status !== 'locked' ? 'continueModule(' . ($i + 1) . ')' : '' ?>" 
                                            <?= $status === 'locked' ? 'disabled' : '' ?>>
                                        <?= $status === 'completed' ? 'Review' : ($status === 'current' ? 'Continue' : 'Locked') ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Update Progress Form -->
            <?php if ($assignment['status'] !== 'completed'): ?>
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0 fw-semibold"><i class="fas fa-chart-line me-2"></i>Update Progress</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= BASE_URL ?>/employee/self-service/training/progress" id="progressForm">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                        <input type="hidden" name="assignment_id" value="<?= $assignment['id'] ?>">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Progress (%)</label>
                                <input type="range" name="progress" class="form-range" min="0" max="100" value="<?= $assignment['progress'] ?>" id="progressInput" oninput="document.getElementById('progressDisplay').textContent = this.value + '%'">
                                <div class="text-center fw-semibold" id="progressDisplay"><?= $assignment['progress'] ?>%</div>
                            </div>
                            <div class="col-md-6 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-save me-1"></i> Update Progress
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0 fw-semibold"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-column gap-2">
                        <?php if (!empty($assignment['material_url'])): ?>
                            <a href="<?= htmlspecialchars($assignment['material_url']) ?>" target="_blank" class="btn btn-outline-primary">
                                <i class="fas fa-book me-2"></i> Course Materials
                            </a>
                        <?php endif; ?>
                        <?php if ($assignment['status'] === 'completed' && !$assignment['certificate_issued']): ?>
                            <button class="btn btn-outline-success" disabled>
                                <i class="fas fa-certificate me-2"></i> Certificate Pending
                            </button>
                        <?php elseif ($assignment['certificate_issued']): ?>
                            <a href="<?= BASE_URL ?>/employee/self-service/certifications" class="btn btn-outline-success">
                                <i class="fas fa-certificate me-2"></i> View Certificate
                            </a>
                        <?php endif; ?>
                        <a href="<?= BASE_URL ?>/employee/self-service/certifications" class="btn btn-outline-info">
                            <i class="fas fa-award me-2"></i> My Certifications
                        </a>
                    </div>
                </div>
            </div>

            <!-- Course Info Card -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0 fw-semibold"><i class="fas fa-clock me-2"></i>Timeline</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0 timeline">
                        <li class="timeline-item mb-3 pb-3 border-bottom">
                            <div class="small text-muted">Assigned</div>
                            <div class="fw-semibold"><?= date('d M Y', strtotime($assignment['assigned_at'])) ?></div>
                        </li>
                        <?php if (!empty($assignment['started_at'])): ?>
                        <li class="timeline-item mb-3 pb-3 border-bottom">
                            <div class="small text-muted">Started</div>
                            <div class="fw-semibold"><?= date('d M Y', strtotime($assignment['started_at'])) ?></div>
                        </li>
                        <?php endif; ?>
                        <?php if (!empty($assignment['completed_at'])): ?>
                        <li class="timeline-item mb-3">
                            <div class="small text-muted">Completed</div>
                            <div class="fw-semibold text-success"><?= date('d M Y', strtotime($assignment['completed_at'])) ?></div>
                        </li>
                        <?php endif; ?>
                        <?php if (!empty($assignment['due_date'])): ?>
                        <li class="timeline-item mb-3">
                            <div class="small text-muted">Due Date</div>
                            <div class="fw-semibold <?= $assignment['due_date'] < date('Y-m-d') && $assignment['status'] !== 'completed' ? 'text-danger' : '' ?>">
                                <?= date('d M Y', strtotime($assignment['due_date'])) ?>
                            </div>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>

            <!-- Instructor Info -->
            <?php if (!empty($assignment['instructor_name'])): ?>
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h5 class="mb-0 fw-semibold"><i class="fas fa-user-tie me-2"></i>Instructor</h5>
                </div>
                <div class="card-body text-center">
                    <div class="avatar-circle bg-primary text-white mx-auto mb-2" style="width: 80px; height: 80px; line-height: 80px; font-size: 2rem;">
                        <?= strtoupper(substr($assignment['instructor_name'], 0, 1)) ?>
                    </div>
                    <h6 class="mb-1"><?= htmlspecialchars($assignment['instructor_name']) ?></h6>
                    <?php if (!empty($assignment['instructor_email'])): ?>
                        <a href="mailto:<?= htmlspecialchars($assignment['instructor_email']) ?>" class="small text-muted"><?= htmlspecialchars($assignment['instructor_email']) ?></a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
.timeline-item { position: relative; padding-left: 1.5rem; }
.timeline-item::before { content: ''; position: absolute; left: 0; top: 0.5rem; width: 8px; height: 8px; border-radius: 50%; background: #7c2d12; }
</style>

<script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
document.getElementById('progressForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    formData.append('csrf_token', '<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>');
    
    try {
        const response = await fetch('<?= BASE_URL ?>/employee/self-service/training/progress', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Failed to update progress');
        }
    } catch (err) {
        alert('Error: ' + err.message);
    }
});

const slider = document.getElementById('progressSlider');
if (slider) {
    slider.addEventListener('input', function() {
        document.getElementById('progressValue').textContent = this.value + '%';
    });
}
</script>