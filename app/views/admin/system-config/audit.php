<?php
/**
 * System Configuration Audit View
 */
$base = BASE_URL;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> - APS Dream Home</title>
    <link href="<?= $base ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= $base ?>/assets/fonts/fontawesome/css/all.min.css" rel="stylesheet">
    <link href="<?= $base ?>/assets/css/style.css?v=7" rel="stylesheet">
    <style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
        .diff-add { background: #d4edda; color: #155724; }
        .diff-remove { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../layouts/admin.php'; ?>
    
    <main class="main-content">
        <header class="top-header">
            <div>
                <h1 class="page-title"><?= htmlspecialchars($page_title) ?></h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?= $base ?>/admin/erp">Admin</a></li>
                        <li class="breadcrumb-item"><a href="<?= $base ?>/admin/system-config">System Config</a></li>
                        <li class="breadcrumb-item active">Audit Log</li>
                    </ol>
                </nav>
            </div>
            <div class="header-actions">
                <a href="<?= $base ?>/admin/system-config" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>
            </div>
        </header>

        <div class="content-wrapper">
            <?php if (empty($audits)): ?>
                <div class="card aps-cp-card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-history fa-3x text-muted mb-3"></i>
                        <h4>No audit records found</h4>
                        <p class="text-muted">Configuration changes will appear here</p>
                    </div>
                </div>
            <?php else: ?>
                <div class="card aps-cp-card">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 15%">Config Key</th>
                                        <th style="width: 20%">Old Value</th>
                                        <th style="width: 20%">New Value</th>
                                        <th style="width: 15%">Changed By</th>
                                        <th style="width: 15%">Date</th>
                                        <th style="width: 15%">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($audits as $audit): ?>
                                        <tr>
                                            <td>
                                                <code><?= htmlspecialchars($audit['config_key']) ?></code>
                                            </td>
                                            <td>
                                                <?php if ($audit['old_value'] !== null): ?>
                                                    <pre class="mb-0 small diff-remove p-2 rounded"><?= htmlspecialchars((string)$audit['old_value']) ?></pre>
                                                <?php else: ?>
                                                    <span class="text-muted">(empty)</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($audit['new_value'] !== null): ?>
                                                    <pre class="mb-0 small diff-add p-2 rounded"><?= htmlspecialchars((string)$audit['new_value']) ?></pre>
                                                <?php else: ?>
                                                    <span class="text-muted">(empty)</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($audit['changed_by_name']): ?>
                                                    <div><?= htmlspecialchars($audit['changed_by_name']) ?></div>
                                                    <small class="text-muted">ID: <?= $audit['changed_by'] ?></small>
                                                <?php else: ?>
                                                    <span class="text-muted">System</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= date('Y-m-d H:i:s', strtotime($audit['changed_at'])) ?></td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="viewDetails(<?= htmlspecialchars(json_encode($audit), ENT_QUOTES) ?>)">
                                                    <i class="fas fa-eye"></i> Details
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($pagination['pages'] > 1): ?>
                            <nav aria-label="Audit pagination">
                                <ul class="pagination justify-content-center mb-0">
                                    <?php if ($pagination['page'] > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?= $pagination['page'] - 1 ?>">Previous</a>
                                        </li>
                                    <?php endif; ?>
                                    <?php for ($i = max(1, $pagination['page'] - 2); $i <= min($pagination['pages'], $pagination['page'] + 2); $i++): ?>
                                        <li class="page-item <?= $i === $pagination['page'] ? 'active' : '' ?>">
                                            <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    <?php if ($pagination['page'] < $pagination['pages']): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?= $pagination['page'] + 1 ?>">Next</a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                        </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Details Modal -->
    <div class="modal fade" id="auditDetailModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Audit Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="auditDetailContent"></div>
            </div>
        </div>
    </div>

    <script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
        function viewDetails(audit) {
            document.getElementById('auditDetailContent').innerHTML = `
                <dl class="row">
                    <dt class="col-sm-3">Config Key</dt>
                    <dd class="col-sm-9"><code>${audit.config_key}</code></dd>
                    <dt class="col-sm-3">Old Value</dt>
                    <dd class="col-sm-9"><pre class="diff-remove p-2 rounded">${audit.old_value !== null ? audit.old_value : '(empty)'}</pre></dd>
                    <dt class="col-sm-3">New Value</dt>
                    <dd class="col-sm-9"><pre class="diff-add p-2 rounded">${audit.new_value !== null ? audit.new_value : '(empty)'}</pre></dd>
                    <dt class="col-sm-3">Changed By</dt>
                    <dd class="col-sm-9">${audit.changed_by_name || 'System'} (ID: ${audit.changed_by})</dd>
                    <dt class="col-sm-3">Date</dt>
                    <dd class="col-sm-9">${audit.changed_at}</dd>
                </dl>
            `;
            new bootstrap.Modal(document.getElementById('auditDetailModal')).show();
        }
    </script>
</body>
</html>