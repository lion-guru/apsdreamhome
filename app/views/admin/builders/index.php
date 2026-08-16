<?php
$page_title = $page_title ?? 'Builder Management - APS Dream Home';
$page_heading = $page_heading ?? 'Builder Management';
$builders = $builders ?? [];
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-building me-2"></i><?= htmlspecialchars($page_heading ?? '') ?></h2>
        <a href="<?= BASE_URL ?>/admin/builders/create" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Add Builder</a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Company</th>
                            <th>Contact Person</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Projects</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($builders)): ?>
                            <?php foreach ($builders as $b): ?>
                            <tr>
                                <td><?= $b['id'] ?? '' ?></td>
                                <td><?= htmlspecialchars($b['name'] ?? '') ?></td>
                                <td><?= htmlspecialchars($b['company_name'] ?? '') ?></td>
                                <td><?= htmlspecialchars($b['contact_person'] ?? '') ?></td>
                                <td><?= htmlspecialchars($b['email'] ?? '') ?></td>
                                <td><?= htmlspecialchars($b['phone'] ?? '') ?></td>
                                <td><?= (int)($b['project_count'] ?? 0) ?></td>
                                <td>
                                    <?php
                                    $status = $b['status'] ?? 'active';
                                    $badgeClass = $status === 'active' ? 'bg-success' : ($status === 'inactive' ? 'bg-secondary' : 'bg-warning text-dark');
                                    ?>
                                    <span class="badge <?= $badgeClass ?>"><?= ucfirst($status) ?></span>
                                </td>
                                <td><?= date('d M Y', strtotime($b['created_at'] ?? 'now')) ?></td>
                                <td>
                                    <a href="<?= BASE_URL ?>/admin/builders/<?= $b['id'] ?>" class="btn btn-sm btn-outline-primary">View</a>
                                    <a href="<?= BASE_URL ?>/admin/builders/<?= $b['id'] ?>/edit" class="btn btn-sm btn-outline-secondary">Edit</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="10" class="text-center text-muted py-4">No builders found</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>