<?php
/**
 * RERA Milestone Tracker — Colony List
 */
?>
<div class="content-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="page-title"><i class="fas fa-tasks me-2"></i>RERA Milestone Tracker</h1>
        <p class="text-muted">Select a colony to view its RERA milestone progress</p>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <i class="fas fa-city me-2"></i>Colonies with RERA Projects
    </div>
    <div class="card-body">
        <?php if (!empty($colonies)): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Colony</th>
                            <th>RERA Number</th>
                            <th class="text-center">RERA Projects</th>
                            <th class="text-center">Milestones</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($colonies as $c): ?>
                        <tr>
                            <td><i class="fas fa-building text-muted me-1"></i><?php echo  htmlspecialchars($c['name']) ?></td>
                            <td><?php echo  htmlspecialchars($c['rera_number'] ?? '<span class="text-muted">—</span>') ?></td>
                            <td class="text-center">
                                <span class="badge bg-info"><?php echo  (int)$c['rera_count'] ?></span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-primary"><?php echo  (int)$c['milestone_count'] ?></span>
                            </td>
                            <td class="text-end">
                                <?php if ((int)$c['rera_count'] > 0): ?>
                                    <a href="<?php echo  BASE_URL . '/admin/legal-colony-pipeline/milestones/' . (int)$c['id'] ?>"
                                       class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye me-1"></i>View Tracker
                                    </a>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark" title="No RERA project registered yet">No RERA Data</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-city fa-3x text-muted mb-3"></i>
                <p class="text-muted">No colonies found. Add colonies first to track RERA milestones.</p>
                <a href="<?php echo  BASE_URL . '/admin/colonies' ?>" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>Manage Colonies
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>
