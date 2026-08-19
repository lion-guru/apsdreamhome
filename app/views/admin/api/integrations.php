<?php

/**
 * API Integrations - APS Dream Home Admin
 */
$page_title = $page_title ?? 'API Integrations';
$page_description = 'Manage API integrations and third-party services';
$api_integrations = $api_integrations ?? [];
$third_party = $third_party ?? [];

?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-2">API Integrations</h1>
            <p class="text-muted">Manage API integrations and third-party services</p>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <ul class="nav nav-tabs" id="integrationTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="api-tab" data-bs-toggle="tab" data-bs-target="#api" type="button" role="tab">
                        <i class="fas fa-plug"></i> API Integrations
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="thirdparty-tab" data-bs-toggle="tab" data-bs-target="#thirdparty" type="button" role="tab">
                        <i class="fas fa-handshake"></i> Third Party
                    </button>
                </li>
            </ul>
        </div>
    </div>

    <div class="tab-content" id="integrationTabsContent">
        <div class="tab-pane fade show active" id="api" role="tabpanel">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">API Integrations</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Service Name</th>
                                    <th>API URL</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($api_integrations)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        <i class="fas fa-inbox fa-2x d-block mb-2"></i>
                                        No API integrations configured.
                                    </td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($api_integrations as $item): ?>
                                <tr>
                                    <td><?php echo $item['id'] ?? ''; ?></td>
                                    <td><strong><?php echo htmlspecialchars($item['service_name'] ?? ''); ?></strong></td>
                                    <td><code class="text-muted small"><?php echo htmlspecialchars($item['api_url'] ?? ''); ?></code></td>
                                    <td>
                                        <?php if (($item['status'] ?? '') === 'active'): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('d M Y', strtotime($item['created_at'] ?? 'now')); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" title="Edit" aria-label="Add"><i class="fas fa-edit"></i></button>
                                        <button class="btn btn-sm btn-outline-danger" title="Disable" aria-label="Edit">
                                            <i class="fas fa-ban"></i>
                                        </button>
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

        <div class="tab-pane fade" id="thirdparty" role="tabpanel">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Third Party Integrations</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Type</th>
                                    <th>API Token</th>
                                    <th>Created</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($third_party)): ?>
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">
                                        <i class="fas fa-inbox fa-2x d-block mb-2"></i>
                                        No third-party integrations registered.
                                    </td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($third_party as $item): ?>
                                <tr>
                                    <td><?php echo $item['id'] ?? ''; ?></td>
                                    <td><span class="badge bg-info"><?php echo htmlspecialchars($item['type'] ?? ''); ?></span></td>
                                    <td>
                                        <code class="text-muted">
                                            <?php echo substr($item['api_token'] ?? '', 0, 20) . '...'; ?>
                                        </code>
                                    </td>
                                    <td><?php echo date('d M Y', strtotime($item['created_at'] ?? 'now')); ?></td>
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
