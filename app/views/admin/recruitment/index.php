<?php
/** Admin Recruitment / ATS - List */

$page_title = $page_title ?? 'Recruitment / ATS';
$jobs = $jobs ?? [];
$filters = $filters ?? [];
$stats = $stats ?? [];
?>

<style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
.job-row { transition: background 0.15s; }
.job-row:hover { background: #fefcf6; }
.status-badge { font-size: 0.7rem; padding: 0.2rem 0.5rem; border-radius: 999px; }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold"><i class="fas fa-briefcase me-2 text-primary"></i>Recruitment / ATS</h4>
            <p class="text-muted mb-0 small">Manage job postings, applications, and hiring pipeline</p>
        </div>
        <a href="<?= BASE_URL ?>/admin/recruitment/create" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Create Job Posting
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card stat-card border-0 shadow-sm bg-primary text-white">
                <div class="card-body text-center">
                    <div class="fw-bold fs-3"><?= $stats['jobs_by_status']['published'] ?? 0 ?></div>
                    <div class="text-white-50 small">Active Postings</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card border-0 shadow-sm bg-info text-white">
                <div class="card-body text-center">
                    <div class="fw-bold fs-3"><?= $stats['jobs_by_status']['draft'] ?? 0 ?></div>
                    <div class="text-white-50 small">Draft</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card border-0 shadow-sm bg-success text-white">
                <div class="card-body text-center">
                    <div class="fw-bold fs-3"><?= $stats['apps_by_status']['applied'] ?? 0 ?></div>
                    <div class="text-white-50 small">Applications</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card border-0 shadow-sm bg-warning text-white">
                <div class="card-body text-center">
                    <div class="fw-bold fs-3"><?= $stats['active_interviews'] ?? 0 ?></div>
                    <div class="text-white-50 small">Active Interviews</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Search</label>
                    <input type="text" name="search" class="form-control form-control-sm" value="<?= htmlspecialchars($filters['search'] ?? '') ?>" placeholder="Title, code, department...">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        <option value="draft" <?= ($filters['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option>
                        <option value="published" <?= ($filters['status'] ?? '') === 'published' ? 'selected' : '' ?>>Published</option>
                        <option value="on_hold" <?= ($filters['status'] ?? '') === 'on_hold' ? 'selected' : '' ?>>On Hold</option>
                        <option value="closed" <?= ($filters['status'] ?? '') === 'closed' ? 'selected' : '' ?>>Closed</option>
                        <option value="cancelled" <?= ($filters['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Department</label>
                    <select name="department" class="form-select form-select-sm">
                        <option value="">All Departments</option>
                        <option value="sales" <?= ($filters['department'] ?? '') === 'sales' ? 'selected' : '' ?>>Sales</option>
                        <option value="engineering" <?= ($filters['department'] ?? '') === 'engineering' ? 'selected' : '' ?>>Engineering</option>
                        <option value="marketing" <?= ($filters['department'] ?? '') === 'marketing' ? 'selected' : '' ?>>Marketing</option>
                        <option value="hr" <?= ($filters['department'] ?? '') === 'hr' ? 'selected' : '' ?>>HR</option>
                        <option value="finance" <?= ($filters['department'] ?? '') === 'finance' ? 'selected' : '' ?>>Finance</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-sm w-100"><i class="fas fa-search me-1"></i> Filter</button>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <a href="<?= BASE_URL ?>/admin/recruitment" class="btn btn-outline-secondary btn-sm w-100">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Jobs Table -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <?php if (empty($jobs)): ?>
            <div class="text-center py-5">
                <i class="fas fa-briefcase fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No Job Postings</h5>
                <p class="text-muted small mb-3">Create your first job posting to start hiring</p>
                <a href="<?= BASE_URL ?>/admin/recruitment/create" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Create Job</a>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 100px">Code</th>
                            <th>Title</th>
                            <th style="width: 120px">Department</th>
                            <th style="width: 120px">Type</th>
                            <th style="width: 120px">Status</th>
                            <th style="width: 100px">Applications</th>
                            <th style="width: 100px">Openings</th>
                            <th style="width: 150px">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($jobs as $job): 
                            $statusClass = [
                                'draft' => 'secondary',
                                'published' => 'success',
                                'on_hold' => 'warning',
                                'closed' => 'primary',
                                'cancelled' => 'danger',
                            ][$job['status']] ?? 'secondary';
                            
                            $appsCount = $job['applications_count'] ?? 0;
                        ?>
                        <tr class="job-row">
                            <td><code class="small"><?= htmlspecialchars($job['job_code']) ?></code></td>
                            <td>
                                <div class="fw-semibold"><?= htmlspecialchars($job['title']) ?></div>
                                <div class="small text-muted text-truncate" style="max-width: 300px;"><?= htmlspecialchars($job['location'] ?? '') ?></div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark" style="background: #f1f5f9; color: #475569;">
                                    <?= htmlspecialchars($job['department'] ?? '—') ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-<?= $job['employment_type'] === 'full_time' ? 'primary' : ($job['employment_type'] === 'contract' ? 'info' : 'secondary') ?>">
                                    <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $job['employment_type']))) ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-<?= $statusClass ?> status-badge">
                                    <?= ucfirst($job['status']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="fw-semibold"><?= $appsCount ?></span>
                                <?php if ($job['openings'] > 0): ?>
                                    <small class="text-muted ms-1">/ <?= $job['openings'] ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="fw-semibold"><?= $job['openings'] - $job['hired_count'] ?> left</div>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= BASE_URL ?>/admin/recruitment/<?= $job['id'] ?>" class="btn btn-outline-primary" title="View"><i class="fas fa-eye"></i></a>
                                    <a href="<?= BASE_URL ?>/admin/recruitment/<?= $job['id'] ?>/edit" class="btn btn-outline-secondary" title="Edit"><i class="fas fa-edit"></i></a>
                                    <?php if ($job['status'] === 'draft'): ?>
                                        <a href="<?= BASE_URL ?>/admin/recruitment/<?= $job['id'] ?>/publish" class="btn btn-outline-success" onclick="return confirm('Publish this job?')"><i class="fas fa-paper-plane"></i></a>
                                    <?php elseif ($job['status'] === 'published'): ?>
                                        <a href="<?= BASE_URL ?>/admin/recruitment/<?= $job['id'] ?>/applications" class="btn btn-outline-info"><i class="fas fa-users"></i></a>
                                    <?php endif; ?>
                                    <a href="<?= BASE_URL ?>/admin/recruitment/<?= $job['id'] ?>/delete" class="btn btn-outline-danger" onclick="return confirm('Delete this job?')"><i class="fas fa-trash"></i></a>
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