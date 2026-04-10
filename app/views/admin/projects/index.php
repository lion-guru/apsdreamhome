<?php
$page_title = $page_title ?? 'Projects';
$projects = $projects ?? [];
$stats = $stats ?? ['total' => 0, 'under_construction' => 0, 'completed' => 0, 'planning' => 0, 'total_plots' => 0, 'available_plots' => 0, 'sold_plots' => 0];
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">Projects</h1>
        <p class="text-muted mb-0">Manage all projects</p>
    </div>
    <a href="/admin/projects/create" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>New Project
    </a>
</div>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon primary"><i class="fas fa-building"></i></div>
            <div class="stat-content">
                <div class="stat-label">Total Projects</div>
                <div class="stat-value"><?= count($projects) ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon warning"><i class="fas fa-cogs"></i></div>
            <div class="stat-content">
                <div class="stat-label">Under Construction</div>
                <div class="stat-value"><?= $stats['under_construction'] ?? 0 ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon success"><i class="fas fa-check-circle"></i></div>
            <div class="stat-content">
                <div class="stat-label">Completed</div>
                <div class="stat-value"><?= $stats['completed'] ?? 0 ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon info"><i class="fas fa-clipboard-list"></i></div>
            <div class="stat-content">
                <div class="stat-label">Planning</div>
                <div class="stat-value"><?= $stats['planning'] ?? 0 ?></div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <?php if (!empty($projects)): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Project Name</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($projects as $p): ?>
                            <tr>
                                <td><?= $p['id'] ?? '' ?></td>
                                <td><strong><?= htmlspecialchars($p['name'] ?? '') ?></strong></td>
                                <td><span class="badge bg-primary"><?= ucfirst($p['project_type'] ?? 'residential') ?></span></td>
                                <td><span class="badge bg-<?= $p['status'] === 'completed' ? 'success' : 'warning' ?>"><?= ucfirst($p['status'] ?? 'planning') ?></span></td>
                                <td><?= date('M d, Y', strtotime($p['created_at'] ?? 'now')) ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="/admin/projects/view/<?= $p['id'] ?>" class="btn btn-outline-primary"><i class="fas fa-eye"></i></a>
                                        <a href="/admin/projects/edit/<?= $p['id'] ?>" class="btn btn-outline-warning"><i class="fas fa-edit"></i></a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-building fa-3x text-muted mb-3"></i>
                <h5>No projects found</h5>
                <p class="text-muted">Create your first project</p>
                <a href="/admin/projects/create" class="btn btn-primary">Create Project</a>
            </div>
        <?php endif; ?>
    </div>
</div>