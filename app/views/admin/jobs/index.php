<?php
/**
 * Admin Jobs Index View
 * HR/Admin can view and manage all job postings
 */

$page_title = $page_title ?? 'Job Management';
$jobs = $jobs ?? [];
$error = $error ?? null;
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0"><i class="fas fa-briefcase me-2"></i>Job Management</h1>
            <p class="text-muted mb-0">Manage job postings and view applications</p>
        </div>
        <div>
            <a href="<?php echo BASE_URL; ?>/admin/jobs/applications" class="btn btn-outline-primary me-2">
                <i class="fas fa-users me-1"></i>Applications
            </a>
            <a href="<?php echo BASE_URL; ?>/admin/jobs/create" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i>Post New Job
            </a>
        </div>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Job Title</th>
                            <th>Department</th>
                            <th>Location</th>
                            <th>Type</th>
                            <th>Posted</th>
                            <th>Status</th>
                            <th>Applications</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($jobs)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    <i class="fas fa-inbox fa-2x mb-2"></i>
                                    <p>No jobs posted yet. <a href="<?php echo BASE_URL; ?>/admin/jobs/create">Post your first job</a></p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($jobs as $job): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($job['title']); ?></strong>
                                        <br><small class="text-muted"><?php echo htmlspecialchars($job['experience']); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($job['department']); ?></td>
                                    <td><?php echo htmlspecialchars($job['location']); ?></td>
                                    <td>
                                        <span class="badge bg-info"><?php echo htmlspecialchars($job['job_type']); ?></span>
                                    </td>
                                    <td>
                                        <?php echo date('M d, Y', strtotime($job['posted_date'])); ?>
                                        <br><small class="text-muted">by <?php echo htmlspecialchars($job['posted_by_name'] ?? 'Admin'); ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo $job['status'] === 'active' ? 'success' : ($job['status'] === 'closed' ? 'danger' : 'secondary'); ?>">
                                            <?php echo ucfirst($job['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="<?php echo BASE_URL; ?>/admin/jobs/applications/<?php echo $job['id']; ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-users me-1"></i><?php echo $job['application_count']; ?> Applications
                                        </a>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?php echo BASE_URL; ?>/admin/jobs/edit/<?php echo $job['id']; ?>" class="btn btn-outline-secondary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-outline-danger" onclick="deleteJob(<?php echo $job['id']; ?>)" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
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

<script>
function deleteJob(id) {
    if (confirm('Are you sure you want to delete this job posting? All applications will also be deleted.')) {
        fetch('<?php echo BASE_URL; ?>/admin/jobs/delete/' + id, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.error);
            }
        });
    }
}
</script>
