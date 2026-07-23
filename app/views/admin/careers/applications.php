<?php
$page_title = $page_title ?? 'Job Applications - APS Dream Home';
$applications = $applications ?? [];
$job = $job ?? null;
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Job Applications</h1>
        <a href="<?= BASE_URL ?>/admin/careers" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Jobs
        </a>
    </div>

    <?php if ($job): ?>
    <div class="alert alert-info mb-4">
        <strong>Position:</strong> <?= htmlspecialchars($job['title'] ?? '') ?><br>
        <strong>Department:</strong> <?= htmlspecialchars($job['department'] ?? '') ?><br>
        <strong>Location:</strong> <?= htmlspecialchars($job['location'] ?? '') ?>
    </div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Applicants List</h6>
        </div>
        <div class="card-body aps-cp-card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="applicationsTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Experience</th>
                            <th>Current Company</th>
                            <th>Resume</th>
                            <th>Status</th>
                            <th>Applied</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($applications)): ?>
                            <?php foreach ($applications as $app): ?>
                            <tr>
                                <td><?= $app['id'] ?? '' ?></td>
                                <td><?= htmlspecialchars($app['full_name'] ?? '') ?></td>
                                <td><?= htmlspecialchars($app['email'] ?? '') ?></td>
                                <td><?= htmlspecialchars($app['phone'] ?? '') ?></td>
                                <td><?= htmlspecialchars($app['experience_years'] ?? '') ?> years</td>
                                <td><?= htmlspecialchars($app['current_company'] ?? '') ?></td>
                                <td>
                                    <?php if (!empty($app['resume_path'])): ?>
                                        <a href="<?= BASE_URL . $app['resume_path'] ?>" target="_blank" class="btn btn-sm btn-outline-primary">View</a>
                                    <?php else: ?>
                                        <span class="text-muted">No resume</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $status = $app['status'] ?? 'pending';
                                    $badgeClass = match($status) {
                                        'shortlisted' => 'bg-success',
                                        'interviewed' => 'bg-primary',
                                        'selected' => 'bg-info text-dark',
                                        'rejected' => 'bg-danger',
                                        default => 'bg-warning text-dark'
                                    };
                                    ?>
                                    <span class="badge <?= $badgeClass ?>"><?= ucfirst($status) ?></span>
                                </td>
                                <td><?= date('d M Y', strtotime($app['created_at'] ?? 'now')) ?></td>
                                <td>
                                    <a href="<?= BASE_URL ?>/admin/careers/manage/applications/<?= $app['id'] ?>" class="btn btn-sm btn-outline-primary">View</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="10" class="text-center text-muted py-4">No applications received yet</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>