<?php

/**
 * API Developers - APS Dream Home Admin
 */
$page_title = $page_title ?? 'API Developers';
$page_description = 'Manage API developers and keys';
$developers = $developers ?? [];
$total = $total ?? 0;
$active = $active ?? 0;

?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-2">API Developers</h1>
            <p class="text-muted">Manage API developers and their access keys</p>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-primary bg-opacity-10 text-primary rounded p-3">
                                <i class="fas fa-code fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Total Developers</h6>
                            <h3 class="mb-0"><?php echo e($total); ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-success bg-opacity-10 text-success rounded p-3">
                                <i class="fas fa-user-check fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Active</h6>
                            <h3 class="mb-0"><?php echo e($active); ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-warning bg-opacity-10 text-warning rounded p-3">
                                <i class="fas fa-user-clock fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Inactive</h6>
                            <h3 class="mb-0"><?php echo $total - $active; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-info bg-opacity-10 text-info rounded p-3">
                                <i class="fas fa-key fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">API Keys Issued</h6>
                            <h3 class="mb-0"><?php echo e($total); ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Developer List</h5>
                    <a href="<?php echo BASE_URL; ?>/admin/api/developers/create" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Add Developer
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Developer Name</th>
                                    <th>Email</th>
                                    <th>API Key</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($developers)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">
                                        <i class="fas fa-inbox fa-2x d-block mb-2"></i>
                                        No developers found. <a href="<?php echo BASE_URL; ?>/admin/api/developers/create">Add one</a>.
                                    </td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($developers as $dev): ?>
                                <tr>
                                    <td><?php echo $dev['id'] ?? ''; ?></td>
                                    <td><strong><?php echo htmlspecialchars($dev['dev_name'] ?? ''); ?></strong></td>
                                    <td><?php echo htmlspecialchars($dev['email'] ?? ''); ?></td>
                                    <td>
                                        <code class="text-muted"><?php echo substr($dev['api_key'] ?? '', 0, 16) . '...'; ?></code>
                                        <button class="btn btn-sm btn-link p-0 ms-1" onclick="copyToClipboard('<?php echo $dev['api_key'] ?? ''; ?>')" title="Copy full key">
                                            <i class="fas fa-copy text-muted"></i>
                                        </button>
                                    </td>
                                    <td>
                                        <?php if (($dev['status'] ?? '') === 'active'): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('d M Y', strtotime($dev['created_at'] ?? 'now')); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-info" title="View Details" aria-label="Add"><i class="fas fa-eye"></i></button>
                                        <button class="btn btn-sm btn-outline-danger" title="Revoke Key" aria-label="View"><i class="fas fa-key"></i></button>
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
    </div>
</div>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        showToast('API Key copied to clipboard!', 'success');
    }).catch(function() {
        prompt('Copy this API key:', text);
    });
}
</script>
