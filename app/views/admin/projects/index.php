<?php include __DIR__ . "/../../../layouts/admin_header.php"; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-building"></i> Projects Management</h2>
                <div>
                    <a href="/admin/projects/create" class="btn btn-primary">
                        <i class="fas fa-plus"></i> New Project
                    </a>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Total Projects</h5>
                                    <h3><?php echo $stats['total'] ?? 0; ?></h3>
                                    <small>All Projects</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Under Construction</h5>
                                    <h3><?php echo $stats['under_construction'] ?? 0; ?></h3>
                                    <small>Active Projects</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Completed</h5>
                                    <h3><?php echo $stats['completed'] ?? 0; ?></h3>
                                    <small>Finished Projects</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Planning</h5>
                                    <h3><?php echo $stats['planning'] ?? 0; ?></h3>
                                    <small>In Planning Phase</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Total Plots</h5>
                                    <h3><?php echo number_format($stats['total_plots'] ?? 0); ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Available Plots</h5>
                                    <h3 class="text-success"><?php echo number_format($stats['available_plots'] ?? 0); ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Sold Plots</h5>
                                    <h3 class="text-danger"><?php echo number_format($stats['sold_plots'] ?? 0); ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Project Name</th>
                                    <th>Type</th>
                                    <th>Developer</th>
                                    <th>Location</th>
                                    <th>Status</th>
                                    <th>Plots</th>
                                    <th>Price Range</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($projects)): ?>
                                    <?php foreach ($projects as $project): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($project['id']); ?></td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($project['name']); ?></strong>
                                                <?php if ($project['is_featured']): ?>
                                                    <span class="badge bg-warning">Featured</span>
                                                <?php endif; ?>
                                                <?php if ($project['is_hot_deal']): ?>
                                                    <span class="badge bg-danger">Hot Deal</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo ucfirst(htmlspecialchars($project['project_type'])); ?></td>
                                            <td><?php echo htmlspecialchars($project['developer_name']); ?></td>
                                            <td>
                                                <?php if ($project['colony_name']): ?>
                                                    <?php echo htmlspecialchars($project['colony_name']); ?>,
                                                <?php endif; ?>
                                                <?php if ($project['district_name']): ?>
                                                    <?php echo htmlspecialchars($project['district_name']); ?>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php
                                                $statusClass = 'bg-secondary';
                                                switch($project['status']) {
                                                    case 'under_construction': $statusClass = 'bg-warning'; break;
                                                    case 'completed': $statusClass = 'bg-success'; break;
                                                    case 'planning': $statusClass = 'bg-info'; break;
                                                    case 'delayed': $statusClass = 'bg-danger'; break;
                                                }
                                                ?>
                                                <span class="badge <?php echo $statusClass; ?>">
                                                    <?php echo ucfirst(str_replace('_', ' ', $project['status'])); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php echo $project['available_plots']; ?> /
                                                <?php echo $project['total_plots']; ?>
                                            </td>
                                            <td>
                                                ₹<?php echo number_format($project['price_range_min']); ?> -
                                                ₹<?php echo number_format($project['price_range_max']); ?>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="/admin/projects/view/<?php echo $project['id']; ?>" class="btn btn-sm btn-info" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="/admin/projects/edit/<?php echo $project['id']; ?>" class="btn btn-sm btn-primary" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="/admin/projects/images/<?php echo $project['id']; ?>" class="btn btn-sm btn-secondary" title="Images">
                                                        <i class="fas fa-images"></i>
                                                    </a>
                                                    <a href="/admin/projects/delete/<?php echo $project['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="9" class="text-center">No projects found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . "/../../../layouts/admin_footer.php"; ?>