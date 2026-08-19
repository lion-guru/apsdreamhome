<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Job Applicants</h2>
        <a href="<?php echo defined('BASE_URL') ? BASE_URL : '/' . trim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/'); ?>/admin/careers" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left"></i> Back to Careers
        </a>
    </div>

    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="alert alert-<?php echo htmlspecialchars($_SESSION['flash_type'] ?? 'info', ENT_QUOTES, 'UTF-8'); ?> alert-dismissible fade show">
            <?php echo htmlspecialchars($_SESSION['flash_message'] ?? '', ENT_QUOTES, 'UTF-8'); unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Filters -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="<?php echo defined('BASE_URL') ? BASE_URL : '/' . trim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/'); ?>/admin/applicants" class="row g-3">
    <?php echo CSRFProtection::csrfField(); ?>
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Search by name, email, phone..." value="<?php echo htmlspecialchars($filters['search'] ?? ''); ?>">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="new" <?php echo ($filters['status'] ?? '') === 'new' ? 'selected' : ''; ?>>New</option>
                        <option value="reviewed" <?php echo ($filters['status'] ?? '') === 'reviewed' ? 'selected' : ''; ?>>Reviewed</option>
                        <option value="shortlisted" <?php echo ($filters['status'] ?? '') === 'shortlisted' ? 'selected' : ''; ?>>Shortlisted</option>
                        <option value="approved" <?php echo ($filters['status'] ?? '') === 'approved' ? 'selected' : ''; ?>>Approved</option>
                        <option value="rejected" <?php echo ($filters['status'] ?? '') === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="career_id" class="form-select">
                        <option value="">All Positions</option>
                        <?php if (!empty($careers)): ?>
                            <?php foreach ($careers as $c): ?>
                                <option value="<?php echo $c['id']; ?>" <?php echo ($filters['career_id'] ?? '') == $c['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($c['title'] ?? ''); ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i> Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Stats -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-left-primary shadow-sm">
                <div class="card-body">
                    <div class="text-xs fw-bold text-primary text-uppercase mb-1">Total Applicants</div>
                    <div class="h5 mb-0"><?php echo number_format($total ?? 0); ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-success shadow-sm">
                <div class="card-body">
                    <div class="text-xs fw-bold text-success text-uppercase mb-1">Approved</div>
                    <div class="h5 mb-0 text-success">
                        <?php
                        $approvedCount = 0;
                        if (!empty($applicants)) {
                            foreach ($applicants as $a) {
                                if (($a['status'] ?? '') === 'approved') $approvedCount++;
                            }
                        }
                        echo $approvedCount;
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-warning shadow-sm">
                <div class="card-body">
                    <div class="text-xs fw-bold text-warning text-uppercase mb-1">Pending Review</div>
                    <div class="h5 mb-0 text-warning">
                        <?php
                        $pendingCount = 0;
                        if (!empty($applicants)) {
                            foreach ($applicants as $a) {
                                if (in_array(($a['status'] ?? 'new'), ['new', 'reviewed'])) $pendingCount++;
                            }
                        }
                        echo $pendingCount;
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-danger shadow-sm">
                <div class="card-body">
                    <div class="text-xs fw-bold text-danger text-uppercase mb-1">Rejected</div>
                    <div class="h5 mb-0 text-danger">
                        <?php
                        $rejectedCount = 0;
                        if (!empty($applicants)) {
                            foreach ($applicants as $a) {
                                if (($a['status'] ?? '') === 'rejected') $rejectedCount++;
                            }
                        }
                        echo $rejectedCount;
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Applicants Table -->
    <div class="card shadow-sm">
        <div class="card-body aps-cp-card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Applicant</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Position</th>
                            <th>Status</th>
                            <th>Applied On</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($applicants)): ?>
                            <?php foreach ($applicants as $app): ?>
                                <tr>
                                    <td><?php echo $app['id'] ?? '-'; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($app['full_name'] ?? $app['applicant_name'] ?? 'N/A'); ?></strong>
                                    </td>
                                    <td><?php echo htmlspecialchars($app['email'] ?? $app['applicant_email'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($app['phone'] ?? $app['applicant_phone'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($app['career_title'] ?? $app['title'] ?? ''); ?></td>
                                    <td>
                                        <?php
                                        $status = $app['status'] ?? 'new';
                                        $badgeClass = match($status) {
                                            'approved' => 'bg-success',
                                            'rejected' => 'bg-danger',
                                            'shortlisted' => 'bg-info',
                                            'reviewed' => 'bg-primary',
                                            default => 'bg-warning',
                                        };
                                        ?>
                                        <span class="badge <?php echo $badgeClass; ?>"><?php echo ucfirst($status); ?></span>
                                    </td>
                                    <td><?php echo date('d M Y', strtotime($app['created_at'] ?? 'now')); ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?php echo defined('BASE_URL') ? BASE_URL : '/'; ?>/admin/careers/manage/applications/<?php echo $app['id'] ?? 0; ?>" class="btn btn-outline-info" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if ($status !== 'approved'): ?>
                                                <a href="<?php echo defined('BASE_URL') ? BASE_URL : '/'; ?>/admin/careers/applicants/<?php echo $app['id'] ?? 0; ?>/status?status=approved" class="btn btn-outline-success" title="Approve" data-aps-confirm="Approve this applicant?">
                                                    <i class="fas fa-check"></i>
                                                </a>
                                            <?php endif; ?>
                                            <?php if ($status !== 'rejected'): ?>
                                                <a href="<?php echo defined('BASE_URL') ? BASE_URL : '/'; ?>/admin/careers/applicants/<?php echo $app['id'] ?? 0; ?>/status?status=rejected" class="btn btn-outline-danger" title="Reject" data-aps-confirm="Reject this applicant?">
                                                    <i class="fas fa-times"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                    No applicants found.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if (($total_pages ?? 1) > 1): ?>
                <nav class="mt-3">
                    <ul class="pagination justify-content-center mb-0">
                        <li class="page-item <?php echo ($page ?? 1) <= 1 ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo ($page ?? 1) - 1; ?>&search=<?php echo urlencode($filters['search'] ?? ''); ?>&status=<?php echo urlencode($filters['status'] ?? ''); ?>&career_id=<?php echo urlencode($filters['career_id'] ?? ''); ?>">Previous</a>
                        </li>
                        <?php
                        $startPage = max(1, ($page ?? 1) - 2);
                        $endPage = min($total_pages ?? 1, ($page ?? 1) + 2);
                        for ($i = $startPage; $i <= $endPage; $i++):
                        ?>
                            <li class="page-item <?php echo $i == ($page ?? 1) ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($filters['search'] ?? ''); ?>&status=<?php echo urlencode($filters['status'] ?? ''); ?>&career_id=<?php echo urlencode($filters['career_id'] ?? ''); ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?php echo ($page ?? 1) >= ($total_pages ?? 1) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo ($page ?? 1) + 1; ?>&search=<?php echo urlencode($filters['search'] ?? ''); ?>&status=<?php echo urlencode($filters['status'] ?? ''); ?>&career_id=<?php echo urlencode($filters['career_id'] ?? ''); ?>">Next</a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</div>
