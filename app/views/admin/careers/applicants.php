<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Job Applicants</h2>
        <a href="<?php echo defined('BASE_URL') ? BASE_URL : '/apsdreamhome'; ?>/admin/jobs" class="btn btn-outline-primary">Back to Jobs</a>
    </div>

    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="alert alert-<?php echo $_SESSION['flash_type'] ?? 'info'; ?> alert-dismissible fade show">
            <?php echo $_SESSION['flash_message']; unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

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
                            <th>Career</th>
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
                                    <td><?php echo htmlspecialchars($app['applicant_name'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($app['applicant_email'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($app['applicant_phone'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($app['career_title'] ?? ''); ?></td>
                                    <td><span class="badge bg-<?php echo ($app['status'] ?? 'new') === 'approved' ? 'success' : (($app['status'] ?? 'new') === 'rejected' ? 'danger' : 'warning'); ?>"><?php echo ucfirst($app['status'] ?? 'new'); ?></span></td>
                                    <td><?php echo date('d M Y', strtotime($app['created_at'] ?? 'now')); ?></td>
                                    <td>
                                        <a href="#" class="btn btn-sm btn-outline-info">View</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="8" class="text-center text-muted py-3">No applicants found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
