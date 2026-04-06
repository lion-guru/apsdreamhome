<?php
/**
 * Admin Job Applications View
 * HR/Admin can review and manage job applications
 */

$page_title = $page_title ?? 'Job Applications';
$applications = $applications ?? [];
$jobs = $jobs ?? [];
$selected_job = $selected_job ?? null;
$statuses = $statuses ?? ['new', 'reviewed', 'shortlisted', 'interviewed', 'offered', 'hired', 'rejected'];
$error = $error ?? null;
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0"><i class="fas fa-users me-2"></i>Job Applications</h1>
            <p class="text-muted mb-0">Review and manage candidate applications</p>
        </div>
        <div>
            <a href="<?php echo BASE_URL; ?>/admin/jobs" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back to Jobs
            </a>
        </div>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <!-- Filter Card -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="<?php echo BASE_URL; ?>/admin/jobs/applications" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Filter by Job</label>
                    <select name="job_id" class="form-select" onchange="this.form.submit()">
                        <option value="">All Jobs</option>
                        <?php foreach ($jobs as $job): ?>
                            <option value="<?php echo $job['id']; ?>" <?php echo $selected_job == $job['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($job['title']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Filter by Status</label>
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        <?php foreach ($statuses as $status): ?>
                            <option value="<?php echo $status; ?>"><?php echo ucfirst($status); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <a href="<?php echo BASE_URL; ?>/admin/jobs/applications" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-sync me-1"></i>Reset Filters
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Applications Table -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Candidate</th>
                            <th>Job Position</th>
                            <th>Contact</th>
                            <th>Experience</th>
                            <th>Applied</th>
                            <th>Status</th>
                            <th>Resume</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($applications)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    <i class="fas fa-inbox fa-2x mb-2"></i>
                                    <p>No applications found.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($applications as $app): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($app['first_name'] . ' ' . $app['last_name']); ?></strong>
                                        <br>
                                        <small class="text-muted"><?php echo htmlspecialchars($app['city'] ?? 'N/A'); ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-info"><?php echo htmlspecialchars($app['job_title']); ?></span>
                                    </td>
                                    <td>
                                        <small>
                                            <i class="fas fa-envelope me-1"></i><?php echo htmlspecialchars($app['email']); ?><br>
                                            <i class="fas fa-phone me-1"></i><?php echo htmlspecialchars($app['phone']); ?>
                                        </small>
                                    </td>
                                    <td><?php echo htmlspecialchars($app['experience'] ?? 'N/A'); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($app['applied_at'])); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo getStatusColor($app['status']); ?>">
                                            <?php echo ucfirst($app['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($app['resume_path']): ?>
                                            <a href="<?php echo BASE_URL . '/' . $app['resume_path']; ?>" 
                                               class="btn btn-sm btn-outline-primary" target="_blank">
                                                <i class="fas fa-file-pdf me-1"></i>View
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">No resume</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="<?php echo BASE_URL; ?>/admin/jobs/applications/view/<?php echo $app['id']; ?>" 
                                           class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye me-1"></i>Review
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
function getStatusColor($status) {
    $colors = [
        'new' => 'secondary',
        'reviewed' => 'info',
        'shortlisted' => 'primary',
        'interviewed' => 'warning',
        'offered' => 'success',
        'hired' => 'success',
        'rejected' => 'danger'
    ];
    return $colors[$status] ?? 'secondary';
}
?>
