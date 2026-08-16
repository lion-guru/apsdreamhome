<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-briefcase me-2"></i>Job Listings</h1>
        <a href="<?= BASE_URL ?>/admin/directory" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-dark">
                        <tr><th>ID</th><th>Title</th><th>Type</th><th>Category</th><th>Company</th><th>Location</th><th>Type</th><th>Status</th><th>Date</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php if (empty($jobs)): ?>
                            <tr><td colspan="10" class="text-center text-muted py-4">No jobs posted.</td></tr>
                        <?php else: ?>
                            <?php foreach ($jobs as $j): ?>
                                <tr>
                                    <td><?= $j['id'] ?></td>
                                    <td><?= htmlspecialchars($j['title'] ?? '') ?></td>
                                    <td><span class="badge bg-secondary"><?= str_replace('_', ' ', $j['job_type'] ?? 'gig') ?></span></td>
                                    <td><?= htmlspecialchars($j['category'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($j['business_name'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($j['location'] ?? '') ?></td>
                                    <td><?= $j['is_seeking'] ? '<span class="badge bg-info">Seeking Work</span>' : '<span class="badge bg-success">Hiring</span>' ?></td>
                                    <td><span class="badge bg-<?= $j['status'] === 'active' ? 'success' : 'secondary' ?>"><?= $j['status'] ?></span></td>
                                    <td><?= date('d M Y', strtotime($j['created_at'])) ?></td>
                                    <td>
                                        <a href="<?= BASE_URL ?>/admin/directory/delete-job/<?= $j['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete job?')"><i class="fas fa-trash"></i></a>
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
