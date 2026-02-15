<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="fw-bold"><i class="fas fa-boxes text-primary me-2"></i> Inventory Management</h2>
            <p class="text-muted">Track available plots, units, and construction progress for your projects.</p>
        </div>
        <div class="col-md-4 text-end">
            <button class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Add Unit
            </button>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-center py-4">
                <h2 class="fw-bold text-primary mb-0">124</h2>
                <small class="text-muted">Total Units</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-center py-4">
                <h2 class="fw-bold text-success mb-0">86</h2>
                <small class="text-muted">Sold / Booked</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-center py-4">
                <h2 class="fw-bold text-warning mb-0">38</h2>
                <small class="text-muted">Available</small>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mt-4">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Project Inventory</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-3">Unit / Plot No.</th>
                            <th>Project</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Progress</th>
                            <th class="text-end pe-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($plots)): ?>
                            <tr><td colspan="6" class="text-center py-4">No inventory data available.</td></tr>
                        <?php else: ?>
                            <?php foreach ($plots as $plot): ?>
                            <tr>
                                <td class="ps-3 fw-bold"><?php echo h($plot['plot_no'] ?? 'Unit-'.$plot['id']); ?></td>
                                <td><?php echo h($plot['project_name'] ?? 'Main Project'); ?></td>
                                <td><?php echo h($plot['type'] ?? 'Residential'); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo ($plot['status'] ?? '') === 'available' ? 'success' : 'secondary'; ?>">
                                        <?php echo ucfirst($plot['status'] ?? 'available'); ?>
                                    </span>
                                </td>
                                <td style="width: 150px;">
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar bg-info" style="width: <?php echo $plot['progress'] ?? 45; ?>%"></div>
                                    </div>
                                    <small class="text-muted"><?php echo $plot['progress'] ?? 45; ?>%</small>
                                </td>
                                <td class="text-end pe-3">
                                    <button class="btn btn-sm btn-outline-primary">Edit</button>
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
