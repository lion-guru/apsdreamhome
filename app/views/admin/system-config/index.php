<?php
/**
 * System Configuration Index View
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
        .config-card { transition: all 0.2s ease; }
        .config-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.1); transform: translateY(-2px); }
        .config-value { font-family: 'Monospace', monospace; font-size: 0.85rem; max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .group-header { background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%); color: white; }
        .search-highlight { background: #fff3cd; padding: 2px 4px; border-radius: 3px; }
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
                        <li class="breadcrumb-item active"><?= htmlspecialchars($page_title) ?></li>
                    </ol>
                </nav>
            </div>
            <div class="header-actions">
                <a href="<?= $base ?>/admin/system-config/export" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-download me-1"></i> Export All
                </a>
                <a href="<?= $base ?>/admin/system-config/import" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-upload me-1"></i> Import
                </a>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#bulkEditModal">
                    <i class="fas fa-edit me-1"></i> Bulk Edit
                </button>
            </div>
        </header>

        <div class="content-wrapper">
            <!-- Stats Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card aps-cp-stat border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon bg-primary bg-opacity-10 text-primary rounded-circle me-3">
                                    <i class="fas fa-cogs fa-lg"></i>
                                </div>
                                <div>
                                    <div class="stat-value"><?= $stats['total'] ?></div>
                                    <div class="stat-label">Total Configs</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card aps-cp-stat border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon bg-success bg-opacity-10 text-success rounded-circle me-3">
                                    <i class="fas fa-folder fa-lg"></i>
                                </div>
                                <div>
                                    <div class="stat-value"><?= $stats['groups'] ?></div>
                                    <div class="stat-label">Groups</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card aps-cp-stat border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon bg-warning bg-opacity-10 text-warning rounded-circle me-3">
                                    <i class="fas fa-edit fa-lg"></i>
                                </div>
                                <div>
                                    <div class="stat-value"><?= $stats['editable'] ?></div>
                                    <div class="stat-label">Editable</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card aps-cp-stat border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon bg-info bg-opacity-10 text-info rounded-circle me-3">
                                    <i class="fas fa-globe fa-lg"></i>
                                </div>
                                <div>
                                    <div class="stat-value"><?= $stats['public'] ?></div>
                                    <div class="stat-label">Public</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search & Filter -->
            <div class="card aps-cp-card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Search</label>
                            <input type="text" name="search" class="form-control" value="<?= htmlspecialchars($search) ?>" placeholder="Search config keys...">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Group Filter</label>
                            <select name="group" class="form-select">
                                <option value="">All Groups</option>
                                <?php foreach ($groups as $g): ?>
                                    <option value="<?= htmlspecialchars($g) ?>" <?= $current_group === $g ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($g) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search me-1"></i> Filter</button>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <a href="<?= $base ?>/admin/system-config" class="btn btn-outline-secondary w-100"><i class="fas fa-times me-1"></i> Clear</a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Configuration Groups -->
            <?php if (empty($configs)): ?>
                <div class="card aps-cp-card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-search fa-3x text-muted mb-3"></i>
                        <h4>No configurations found</h4>
                        <p class="text-muted">Try adjusting your search or filter criteria</p>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($configs as $groupName => $groupConfigs): ?>
                    <div class="card aps-cp-card mb-4">
                        <div class="card-header group-header d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-folder me-2"></i>
                                <strong><?= htmlspecialchars($groupName) ?></strong>
                                <span class="badge bg-light text-dark ms-2"><?= count($groupConfigs) ?> items</span>
                            </div>
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-light" onclick="expandGroup('<?= htmlspecialchars($groupName) ?>')" title="Expand All">
                                    <i class="fas fa-chevron-down"></i>
                                </button>
                                <button type="button" class="btn btn-light" onclick="collapseGroup('<?= htmlspecialchars($groupName) ?>')" title="Collapse All">
                                    <i class="fas fa-chevron-up"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body p-0" id="group-<?= htmlspecialchars($groupName) ?>">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 30%">Key</th>
                                            <th style="width: 40%">Value</th>
                                            <th style="width: 15%">Type</th>
                                            <th style="width: 10%">Flags</th>
                                            <th style="width: 5%">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($groupConfigs as $key => $config): ?>
                                            <tr class="config-row" data-key="<?= htmlspecialchars($key) ?>">
                                                <td>
                                                    <code class="config-key"><?= htmlspecialchars($key) ?></code>
                                                    <?php if (!empty($config['description'])): ?>
                                                        <div class="text-muted small mt-1"><?= htmlspecialchars($config['description']) ?></div>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="config-value">
                                                        <?php if (is_array($config['value'])): ?>
                                                            <pre class="mb-0 small"><?= htmlspecialchars(json_encode($config['value'], JSON_PRETTY_PRINT)) ?></pre>
                                                        <?php elseif (is_bool($config['value'])): ?>
                                                            <span class="badge <?= $config['value'] ? 'bg-success' : 'bg-secondary' ?>">
                                                                <?= $config['value'] ? 'true' : 'false' ?>
                                                            </span>
                                                        <?php else: ?>
                                                            <?= htmlspecialchars((string)$config['value']) ?>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-secondary"><?= htmlspecialchars($config['type']) ?></span>
                                                </td>
                                                <td>
                                                    <div class="d-flex gap-1 flex-wrap">
                                                        <?php if ($config['is_editable']): ?>
                                                            <span class="badge bg-success" title="Editable"><i class="fas fa-edit"></i></span>
                                                        <?php else: ?>
                                                            <span class="badge bg-secondary" title="Read-only"><i class="fas fa-lock"></i></span>
                                                        <?php endif; ?>
                                                        <?php if ($config['is_public']): ?>
                                                            <span class="badge bg-info" title="Public"><i class="fas fa-globe"></i></span>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <button type="button" class="btn btn-outline-primary" onclick="editConfig('<?= htmlspecialchars($key, ENT_QUOTES) ?>')" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-outline-danger" onclick="resetConfig('<?= htmlspecialchars($key, ENT_QUOTES) ?>')" title="Reset to Default">
                                                            <i class="fas fa-undo"></i>
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
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

    <!-- Bulk Edit Modal -->
    <div class="modal fade" id="bulkEditModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <form method="POST" action="<?= $base ?>/admin/system-config/bulk-update">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Bulk Edit Configurations</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="table-responsive" style="max-height: 60vh; overflow-y: auto;">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 5%"><input type="checkbox" id="selectAllConfigs"></th>
                                        <th>Key</th>
                                        <th>Type</th>
                                        <th>Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($configs as $groupConfigs): ?>
                                        <?php foreach ($groupConfigs as $key => $config): ?>
                                            <?php if ($config['is_editable']): ?>
                                                <tr>
                                                    <td><input type="checkbox" name="configs[<?= htmlspecialchars($key) ?>][selected]" value="1" class="config-checkbox"></td>
                                                    <td>
                                                        <code><?= htmlspecialchars($key) ?></code>
                                                        <input type="hidden" name="configs[<?= htmlspecialchars($key) ?>][type]" value="<?= htmlspecialchars($config['type']) ?>">
                                                        <input type="hidden" name="configs[<?= htmlspecialchars($key) ?>][group]" value="<?= htmlspecialchars($config['group'] ?? 'general') ?>">
                                                    </td>
                                                    <td><span class="badge bg-secondary"><?= htmlspecialchars($config['type']) ?></span></td>
                                                    <td>
                                                        <?php if ($config['type'] === 'boolean'): ?>
                                                            <select name="configs[<?= htmlspecialchars($key) ?>][value]" class="form-select form-select-sm">
                                                                <option value="1" <?= (bool)$config['current_value'] ? 'selected' : '' ?>>true</option>
                                                                <option value="0" <?= !(bool)$config['current_value'] ? 'selected' : '' ?>>false</option>
                                                            </select>
                                                        <?php else: ?>
                                                            <input type="text" name="configs[<?= htmlspecialchars($key) ?>][value]" class="form-control form-control-sm" value="<?= htmlspecialchars((string)$config['current_value']) ?>">
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Save Changes</button>
                    </div>
                </form>
            </div>

    <script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
        function editConfig(key) {
            window.location.href = '<?= $base ?>/admin/system-config/edit/' + encodeURIComponent(key);
        }

        function resetConfig(key) {
            if (confirm('Reset this configuration to default? This will delete the current value.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '<?= $base ?>/admin/system-config/reset/' + encodeURIComponent(key);
                form.innerHTML = '<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">';
                document.body.appendChild(form);
                form.submit();
            }
        }

        function expandGroup(groupName) {
            document.querySelectorAll('#group-' + groupName + ' .config-row').forEach(row => row.style.display = '');
        }

        function collapseGroup(groupName) {
            document.querySelectorAll('#group-' + groupName + ' .config-row').forEach((row, i) => {
                if (i > 5) row.style.display = 'none';
            });
        }

        document.getElementById('selectAllConfigs')?.addEventListener('change', function(e) {
            document.querySelectorAll('.config-checkbox').forEach(cb => cb.checked = e.target.checked);
        });

        // Search highlight
        const search = '<?= htmlspecialchars($search, ENT_QUOTES) ?>';
        if (search) {
            document.querySelectorAll('.config-key').forEach(el => {
                const text = el.textContent;
                if (text.toLowerCase().includes(search.toLowerCase())) {
                    el.innerHTML = text.replace(new RegExp('(' + search.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi'), '<span class="search-highlight">$1</span>');
                }
            });
        }
    </script>
</body>
</html>