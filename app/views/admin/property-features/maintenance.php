<?php $page_title = 'Property Maintenance'; ?>
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-2"><i class="fas fa-tools me-2"></i>Property Maintenance</h1>
            <p class="text-muted">Manage maintenance requests for all properties</p>
        </div>
    </div>

    <?php if ($msg = $_SESSION['flash_success'] ?? null): ?><div class="alert alert-success alert-dismissible fade show"><?= htmlspecialchars($msg ?? '') ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php unset($_SESSION['flash_success']); endif; ?>
    <?php if ($msg = $_SESSION['flash_error'] ?? null): ?><div class="alert alert-danger alert-dismissible fade show"><?= htmlspecialchars($msg ?? '') ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php unset($_SESSION['flash_error']); endif; ?>

    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3"><div class="bg-primary bg-opacity-10 text-primary rounded p-3"><i class="fas fa-clipboard-list fa-2x"></i></div></div>
                        <div class="flex-grow-1"><h6 class="text-muted mb-1">Total Requests</h6><h3 class="mb-0"><?= number_format($total_count ?? 0) ?></h3></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3"><div class="bg-warning bg-opacity-10 text-warning rounded p-3"><i class="fas fa-exclamation-circle fa-2x"></i></div></div>
                        <div class="flex-grow-1"><h6 class="text-muted mb-1">Open</h6><h3 class="mb-0"><?= number_format($open_count ?? 0) ?></h3></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3"><div class="bg-info bg-opacity-10 text-info rounded p-3"><i class="fas fa-spinner fa-2x"></i></div></div>
                        <div class="flex-grow-1"><h6 class="text-muted mb-1">In Progress</h6><h3 class="mb-0"><?= number_format($in_progress_count ?? 0) ?></h3></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3"><div class="bg-success bg-opacity-10 text-success rounded p-3"><i class="fas fa-check-circle fa-2x"></i></div></div>
                        <div class="flex-grow-1"><h6 class="text-muted mb-1">Completed</h6><h3 class="mb-0"><?= number_format($completed_count ?? 0) ?></h3></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-list me-2"></i>Maintenance Requests</h5></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light"><tr><th class="ps-4">#</th><th>Property</th><th>Issue Type</th><th>Priority</th><th>Status</th><th>Assigned To</th><th>Created</th><th class="text-end pe-4">Actions</th></tr></thead>
                    <tbody>
                        <?php if (empty($requests)): ?>
                            <tr><td colspan="8" class="text-center py-5 text-muted"><i class="fas fa-tools fa-3x d-block mb-3"></i>No maintenance requests</td></tr>
                        <?php else: ?>
                            <?php foreach ($requests as $i => $r): ?>
                            <?php
                                $pClass = match($r['priority'] ?? '') { 'high' => 'danger', 'urgent' => 'danger', 'medium' => 'warning', 'low' => 'info', default => 'secondary' };
                                $sClass = match($r['status'] ?? '') { 'completed' => 'success', 'in_progress' => 'info', 'open' => 'warning', default => 'secondary' };
                            ?>
                            <tr>
                                <td class="ps-4"><?= $i + 1 ?></td>
                                <td><strong><?= htmlspecialchars($r['property_title'] ?? 'Property #' . $r['property_id']) ?></strong><?= $r['plot_id'] ? ' <small class="text-muted">(Plot #' . $r['plot_id'] . ')</small>' : '' ?></td>
                                <td><?= htmlspecialchars($r['issue_type'] ?? '-') ?></td>
                                <td><span class="badge bg-<?= $pClass ?>-subtle text-<?= $pClass ?> rounded-pill px-3"><?= ucfirst($r['priority'] ?? 'Medium') ?></span></td>
                                <td><span class="badge bg-<?= $sClass ?>-subtle text-<?= $sClass ?> rounded-pill px-3"><?= ucfirst(str_replace('_', ' ', $r['status'] ?? 'Open')) ?></span></td>
                                <td><?= htmlspecialchars($r['assigned_name'] ?? '-') ?></td>
                                <td class="small"><?= date('d M Y', strtotime($r['created_at'] ?? 'now')) ?></td>
                                <td class="text-end pe-4">
                                    <a href="<?= BASE_URL ?>/admin/property-features/maintenance/<?= $r['id'] ?>" class="btn btn-sm btn-outline-primary" title="View Details"><i class="fas fa-eye"></i></a>
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
