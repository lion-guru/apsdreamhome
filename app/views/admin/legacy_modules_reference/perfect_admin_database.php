<?php
/**
 * Database Management - Perfect Admin
 */

$dbStats = $adminService->getDatabaseStats();
?>

<div class="row g-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title mb-0">Database Tables</h5>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-download me-1"></i>Backup Database
                        </button>
                        <button class="btn btn-sm btn-outline-info">
                            <i class="fas fa-sync me-1"></i>Optimize Tables
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Table Name</th>
                                <th>Rows</th>
                                <th>Data Size</th>
                                <th>Engine</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($dbStats as $table): ?>
                                <tr>
                                    <td class="fw-semibold"><?php echo h($table['name']); ?></td>
                                    <td><?php echo number_format($table['rows']); ?></td>
                                    <td>
                                        <span class="badge bg-secondary-subtle text-secondary">
                                            <?php echo h($table['size']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo h($table['engine']); ?></td>
                                    <td class="text-end">
                                        <div class="btn-group">
                                            <button class="btn btn-sm btn-outline-info" title="Browse">
                                                <i class="fas fa-search"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-warning" title="Repair">
                                                <i class="fas fa-tools"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h5 class="card-title mb-3">Database Connection Status</h5>
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-success-subtle text-success p-3 rounded-circle">
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="fw-bold text-success">Connected Successfully</div>
                        <div class="text-muted small">Host: localhost | Driver: PDO_MYSQL | Status: Active</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
