<?php
/** Admin Recruitment - Show Job */

$page_title = $page_title ?? 'Job: ' . ($job['title'] ?? 'Unknown');
$job = $job ?? [];
$applications = $applications ?? [];
?>

<style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
.app-row { transition: background 0.15s; }
.app-row:hover { background: #fefcf6; }
.status-badge { font-size: 0.7rem; padding: 0.2rem 0.5rem; border-radius: 999px; }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold"><i class="fas fa-briefcase me-2 text-primary"></i><?= htmlspecialchars($job['title'] ?? 'Unknown') ?></h4>
            <p class="text-muted mb-0 small"><?= htmlspecialchars($job['job_code'] ?? '') ?> • <?= htmlspecialchars($job['department'] ?? '') ?></p>
        </div>
        <div class="d-flex gap-2">
            <?php if ($job['status'] === 'draft'): ?>
                <a href="<?= BASE_URL ?>/admin/recruitment/<?= $job['id'] ?>/publish" class="btn btn-success" onclick="return confirm('Publish this job?')"><i class="fas fa-paper-plane me-1"></i> Publish</a>
            <?php elseif ($job['status'] === 'published'): ?>
                <a href="<?= BASE_URL ?>/admin/recruitment/<?= $job['id'] ?>/applications" class="btn btn-info"><i class="fas fa-users me-1"></i> Applications (<?= $job['applications_count'] ?? 0 ?>)</a>
            <?php endif; ?>
            <a href="<?= BASE_URL ?>/admin/recruitment/<?= $job['id'] ?>/edit" class="btn btn-outline-secondary"><i class="fas fa-edit me-1"></i> Edit</a>
            <a href="<?= BASE_URL ?>/admin/recruitment" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Back</a>
        </div>
    </div>

    <!-- Job Header -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-semibold">Job Overview</h5>
            <div class="d-flex gap-2">
                <span class="badge bg-<?= $job['employment_type'] === 'full_time' ? 'primary' : ($job['employment_type'] === 'contract' ? 'info' : 'secondary') ?>">
                    <?= ucfirst(str_replace('_', ' ', $job['employment_type'])) ?>
                </span>
                <span class="badge bg-<?= 
                    $job['status'] === 'published' ? 'success' : 
                    ($job['status'] === 'draft' ? 'secondary' : 
                    ($job['status'] === 'on_hold' ? 'warning' : 'primary')) ?> status-badge">
                    <?= ucfirst($job['status']) ?>
                </span>
            </div>
        </div>
        <div class="card-body">
            <div class="row g-3 mb-3">
                <div class="col-md-3">
                    <small class="text-muted">Job Code</small>
                    <div class="fw-semibold"><code><?= htmlspecialchars($job['job_code']) ?></code></div>
                </div>
                <div class="col-md-3">
                    <small class="text-muted">Department</small>
                    <div class="fw-semibold"><?= htmlspecialchars($job['department'] ?? '—') ?></div>
                </div>
                <div class="col-md-3">
                    <small class="text-muted">Designation</small>
                    <div class="fw-semibold"><?= htmlspecialchars($job['designation'] ?? '—') ?></div>
                </div>
                <div class="col-md-3">
                    <small class="text-muted">Location</small>
                    <div class="fw-semibold"><?= htmlspecialchars($job['location'] ?? '—') ?></div>
                </div>
            </div>
            <div class="row g-3">
                <div class="col-md-3">
                    <small class="text-muted">Salary Range</small>
                    <div class="fw-semibold"><?= $job['salary_min'] || $job['salary_max'] ? '₹' . number_format($job['salary_min'] ?? 0) . ' - ₹' . number_format($job['salary_max'] ?? 0) . ' ' . ($job['salary_currency'] ?? 'INR') : 'Not specified' ?></div>
                </div>
                <div class="col-md-3">
                    <small class="text-muted">Experience</small>
                    <div class="fw-semibold"><?= ($job['experience_min'] ?? 0) . ' - ' . ($job['experience_max'] ?? 0) ?> years</div>
                </div>
                <div class="col-md-3">
                    <small class="text-muted">Openings</small>
                    <div class="fw-semibold"><?= $job['openings'] - ($job['hired_count'] ?? 0) ?> / <?= $job['openings'] ?></div>
                </div>
                <div class="col-md-3">
                    <small class="text-muted">Remote</small>
                    <div class="fw-semibold"><?= !empty($job['is_remote']) ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>' ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Job Details -->
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0 fw-semibold"><i class="fas fa-file-alt me-2"></i>Description</h5>
                </div>
                <div class="card-body">
                    <div><?= nl2br(htmlspecialchars($job['description'] ?? 'No description')) ?></div>
                </div>
            </div>
            <?php if (!empty($job['requirements'])): ?>
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0 fw-semibold"><i class="fas fa-list-check me-2"></i>Requirements</h5>
                </div>
                <div class="card-body">
                    <div><?= nl2br(htmlspecialchars($job['requirements'])) ?></div>
                </div>
            </div>
            <?php endif; ?>
            <?php if (!empty($job['responsibilities'])): ?>
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0 fw-semibold"><i class="fas fa-tasks me-2"></i>Responsibilities</h5>
                </div>
                <div class="card-body">
                    <div><?= nl2br(htmlspecialchars($job['responsibilities'])) ?></div>
                </div>
            </div>
            <?php endif; ?>
            <?php if (!empty($job['benefits'])): ?>
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0 fw-semibold"><i class="fas fa-gift me-2"></i>Benefits</h5>
                </div>
                <div class="card-body">
                    <div><?= nl2br(htmlspecialchars($job['benefits'])) ?></div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div class="col-md-6">
            <?php if (!empty($job['skills_required'])): ?>
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0 fw-semibold"><i class="fas fa-code me-2"></i>Skills Required</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-1">
                        <?php 
                        $skills = is_array($job['skills_required']) ? $job['skills_required'] : explode("\n", $job['skills_required']);
                        foreach ($skills as $skill): 
                            $skill = trim($skill);
                            if ($skill): ?>
                                <span class="badge bg-light text-dark me-1 mb-1" style="background: #f1f5f9; color: #475569;"><?= htmlspecialchars($skill) ?></span>
                            <?php endif; 
                        endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($job['education_requirement'])): ?>
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0 fw-semibold"><i class="fas fa-graduation-cap me-2"></i>Education</h5>
                </div>
                <div class="card-body">
                    <div><?= htmlspecialchars($job['education_requirement']) ?></div>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($job['benefits'])): ?>
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0 fw-semibold"><i class="fas fa-gift me-2"></i>Benefits</h5>
                </div>
                <div class="card-body">
                    <div><?= nl2br(htmlspecialchars($job['benefits'])) ?></div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Applications -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-semibold"><i class="fas fa-users me-2"></i>Applications (<?= count($applications) ?>)</h5>
        </div>
        <div class="card-body p-0">
            <?php if (empty($applications)): ?>
            <div class="text-center py-4 text-muted">No applications yet</div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 80px">#</th>
                            <th>Candidate</th>
                            <th style="width: 150px">Stage</th>
                            <th style="width: 120px">Applied</th>
                            <th style="width: 120px">Screened By</th>
                            <th style="width: 120px">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($applications as $index => $app): 
                            $statusClass = [
                                'applied' => 'secondary',
                                'screening' => 'info',
                                'shortlisted' => 'primary',
                                'interview_scheduled' => 'warning',
                                'interviewed' => 'info',
                                'offer_extended' => 'success',
                                'offer_accepted' => 'success',
                                'offer_declined' => 'danger',
                                'hired' => 'success',
                                'rejected' => 'danger',
                                'withdrawn' => 'secondary',
                            ][$app['status']] ?? 'secondary';
                        ?>
                        <tr class="app-row">
                            <td><?= $index + 1 ?></td>
                            <td>
                                <div class="fw-semibold"><?= htmlspecialchars($app['candidate_name']) ?></div>
                                <div class="small text-muted"><?= htmlspecialchars($app['email']) ?></div>
                            </td>
                            <td>
                                <span class="badge bg-<?= $statusClass ?> status-badge">
                                    <?= ucfirst(str_replace('_', ' ', $app['status'])) ?>
                                </span>
                            </td>
                            <td><?= date('d M Y', strtotime($app['applied_at'])) ?></td>
                            <td><?= htmlspecialchars($app['screened_by_name'] ?? '—') ?></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= BASE_URL ?>/admin/recruitment/applications/<?= $app['id'] ?>" class="btn btn-outline-primary" title="View"><i class="fas fa-eye"></i></a>
                                </div>
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