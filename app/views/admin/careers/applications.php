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
        <form method="get" action="<?= BASE_URL ?>/admin/careers/manage/applications" class="d-flex gap-2 align-items-center flex-wrap px-3 pt-2 pb-2 border-bottom">
            <input type="text" name="search" aria-label="Search applications" class="form-control form-control-sm" style="max-width:260px" placeholder="Search name, email, phone..." value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
            <select name="position" aria-label="Filter by position" class="form-select form-select-sm" style="max-width:220px">
                <option value="">All Positions</option>
                <?php foreach (($positions ?? []) as $pos): ?>
                    <option value="<?= htmlspecialchars($pos) ?>" <?= (($filters['position'] ?? '') === $pos) ? 'selected' : '' ?>><?= htmlspecialchars($pos) ?></option>
                <?php endforeach; ?>
            </select>
            <select name="status" aria-label="Filter by status" class="form-select form-select-sm" style="max-width:180px">
                <option value="">All Statuses</option>
                <?php foreach (['pending','reviewing','shortlisted','rejected','hired'] as $st): ?>
                    <option value="<?= $st ?>" <?= (($filters['status'] ?? '') === $st) ? 'selected' : '' ?>><?= ucfirst($st) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search me-1"></i>Filter</button>
            <?php if (!empty(array_filter($filters ?? []))): ?>
                <a href="<?= BASE_URL ?>/admin/careers/manage/applications" class="btn btn-outline-secondary btn-sm">Reset</a>
            <?php endif; ?>
        </form>
        <div class="card-body aps-cp-card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="applicationsTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Name</th>
                            <th scope="col">Email</th>
                            <th scope="col">Phone</th>
                            <th scope="col">Experience</th>
                            <th scope="col">Current Company</th>
                            <th scope="col">Resume</th>
                            <th scope="col">Status</th>
                            <th scope="col">Applied</th>
                            <th scope="col">Actions</th>
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
                                        <a href="<?= BASE_URL ?>/admin/careers/manage/applications/<?= (int)$app['id'] ?>/resume" class="btn btn-sm btn-outline-primary">Download</a>
                                    <?php else: ?>
                                        <span class="text-muted">No resume</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $status = $app['status'] ?? 'pending';
                                    $badgeClass = match($status) {
                                        'shortlisted' => 'bg-success',
                                        'reviewing'   => 'bg-primary',
                                        'hired'       => 'bg-info text-dark',
                                        'rejected'    => 'bg-danger',
                                        default       => 'bg-warning text-dark'
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
                            <tr><td colspan="10" class="text-center text-muted py-4">No applications match your filters</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <?php if (!empty($pagination)): ?>
                <div class="d-flex justify-content-between align-items-center px-3 pb-3">
                    <small class="text-muted">
                        Page <?= (int)($pagination['current_page'] ?? 1) ?> of <?= (int)($pagination['total_pages'] ?? 1) ?>
                        &nbsp;&middot;&nbsp; <?= (int)($pagination['total'] ?? 0) ?> applications
                    </small>
                    <?php $navQuery = array_filter($filters ?? [], fn($v) => $v !== ''); ?>
                    <nav aria-label="Applications pagination">
                        <ul class="pagination pagination-sm mb-0">
                            <li class="page-item <?= (($pagination['current_page'] ?? 1) <= 1) ? 'disabled' : '' ?>">
                                <a class="page-link" href="<?= BASE_URL ?>/admin/careers/manage/applications?<?= http_build_query(array_merge($navQuery, ['page' => (int)($pagination['current_page'] ?? 1) - 1])) ?>" aria-label="Previous">&laquo;</a>
                            </li>
                            <li class="page-item <?= (($pagination['current_page'] ?? 1) >= ($pagination['total_pages'] ?? 1)) ? 'disabled' : '' ?>">
                                <a class="page-link" href="<?= BASE_URL ?>/admin/careers/manage/applications?<?= http_build_query(array_merge($navQuery, ['page' => (int)($pagination['current_page'] ?? 1) + 1])) ?>" aria-label="Next">&raquo;</a>
                            </li>
                        </ul>
                    </nav>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>