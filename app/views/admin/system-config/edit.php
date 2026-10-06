<?php
/**
 * System Configuration Edit View
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
        .json-editor { font-family: 'Monospace', monospace; }
        .type-badge { font-size: 0.75rem; }
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
                        <li class="breadcrumb-item active">Edit</li>
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
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?= htmlspecialchars($_SESSION['error']) ?>
                    <?php unset($_SESSION['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle me-2"></i>
                    <?= htmlspecialchars($_SESSION['success']) ?>
                    <?php unset($_SESSION['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-lg-8">
                    <div class="card aps-cp-card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-edit me-2"></i><?= htmlspecialchars($config['key']) ?></h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="<?= $base ?>/admin/system-config/edit/<?= htmlspecialchars($config['key']) ?>">
                                <input type="hidden name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                
                                <div class="row mb-3">
                                    <label class="col-sm-2 col-form-label">Type</label>
                                    <div class="col-sm-10">
                                        <span class="badge bg-primary type-badge"><?= htmlspecialchars($config['type']) ?></span>
                                        <input type="hidden" name="type" value="<?= htmlspecialchars($config['type']) ?>">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label class="col-sm-2 col-form-label">Group</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control" value="<?= htmlspecialchars($config['group'] ?? 'general') ?>" readonly>
                                    </div>
                                </div>

                                <?php if (!empty($config['description'])): ?>
                                    <div class="row mb-3">
                                        <label class="col-sm-2 col-form-label">Description</label>
                                        <div class="col-sm-10">
                                            <p class="form-control-plaintext text-muted"><?= htmlspecialchars($config['description']) ?></p>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <div class="row mb-3">
                                    <label class="col-sm-2 col-form-label required">Value</label>
                                    <div class="col-sm-10">
                                        <?php 
                                        $value = $config['current_value'];
                                        $type = $config['type'];
                                        $key = $config['key'];
                                        ?>
                                        
                                        <?php if ($type === 'boolean'): ?>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="value" id="value" <?= (bool)$value ? 'checked' : '' ?> value="1">
                                                <label class="form-check-label" for="value">Enabled</label>
                                            </div>
                                            
                                        <?php elseif ($type === 'integer' || $type === 'float'): ?>
                                            <input type="number" name="value" class="form-control" value="<?= htmlspecialchars((string)$value) ?>" step="<?= $type === 'float' ? '0.01' : '1' ?>">
                                            
                                        <?php elseif ($type === 'json' || $type === 'array'): ?>
                                            <textarea name="value" class="form-control json-editor" rows="10" style="font-size: 0.85rem;"><?= htmlspecialchars(is_array($value) ? json_encode($value, JSON_PRETTY_PRINT) : $value) ?></textarea>
                                            <div class="form-text">Valid JSON format required</div>
                                            
                                        <?php else: ?>
                                            <input type="text" name="value" class="form-control" value="<?= htmlspecialchars((string)$value) ?>">
                                        <?php endif; ?>
                                    </div>

                                    <div class="row mb-3">
                                        <label class="col-sm-2 col-form-label">Flags</label>
                                        <div class="col-sm-10">
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="checkbox" id="is_public" name="is_public" <?= ($config['is_public'] ?? false) ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="is_public">Public (accessible via API)</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="checkbox" id="is_editable" name="is_editable" <?= ($config['is_editable'] ?? true) ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="is_editable">Editable via UI</label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label class="col-sm-2 col-form-label">Validation Rules (JSON)</label>
                                        <div class="col-sm-10">
                                            <textarea name="validation_rules" class="form-control json-editor" rows="3"><?= htmlspecialchars(json_encode($config['validation_rules'] ?? [], JSON_PRETTY_PRINT)) ?></textarea>
                                            <div class="form-text">e.g., {"min": 0, "max": 100, "pattern": "^[a-z]+$"}</div>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label class="col-sm-2 col-form-label">Options (JSON)</label>
                                        <div class="col-sm-10">
                                            <textarea name="options" class="form-control json-editor" rows="3"><?= htmlspecialchars(json_encode($config['options'] ?? [], JSON_PRETTY_PRINT)) ?></textarea>
                                            <div class="form-text">For select/radio types: [{"value": "opt1", "label": "Option 1"}]</div>
                                        </div>
                                    </div>

                                    <hr>
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-1"></i> Save Changes
                                        </button>
                                        <a href="<?= $base ?>/admin/system-config" class="btn btn-outline-secondary">
                                            <i class="fas fa-times me-1"></i> Cancel
                                        </a>
                                        <button type="button" class="btn btn-outline-danger ms-auto" onclick="resetConfig()">
                                            <i class="fas fa-undo me-1"></i> Reset to Default
                                        </button>
                                    </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card aps-cp-card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Configuration Info</h5>
                        </div>
                        <div class="card-body">
                            <dl class="row">
                                <dt class="col-sm-4">Key</dt>
                                <dd class="col-sm-8"><code><?= htmlspecialchars($config['key']) ?></code></dd>
                                <dt class="col-sm-4">Type</dt>
                                <dd class="col-sm-8"><span class="badge bg-primary"><?= htmlspecialchars($config['type']) ?></span></dd>
                                <dt class="col-sm-4">Group</dt>
                                <dd class="col-sm-8"><?= htmlspecialchars($config['group'] ?? 'general') ?></dd>
                                <dt class="col-sm-4">Editable</dt>
                                <dd class="col-sm-8"><?= ($config['is_editable'] ?? true) ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>' ?></dd>
                                <dt class="col-sm-4">Public</dt>
                                <dd class="col-sm-8"><?= ($config['is_public'] ?? false) ? '<span class="badge bg-info">Yes</span>' : '<span class="badge bg-secondary">No</span>' ?></dd>
                            </dl>

                            <hr>
                            <h6>Validation Rules</h6>
                            <pre class="small bg-light p-3 rounded"><?= htmlspecialchars(json_encode($config['validation_rules'] ?? [], JSON_PRETTY_PRINT)) ?></pre>

                            <h6 class="mt-3">Options</h6>
                            <pre class="small bg-light p-3 rounded"><?= htmlspecialchars(json_encode($config['options'] ?? [], JSON_PRETTY_PRINT)) ?></pre>
                        </div>
                    </div>

                    <div class="card aps-cp-card mt-3">
                        <div class="card-header bg-danger text-white">
                            <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Danger Zone</h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted small">Resetting will delete this configuration. It may be recreated by the system with default values.</p>
                            <form method="POST" action="<?= $base ?>/admin/system-config/reset/<?= htmlspecialchars($config['key']) ?>" onsubmit="return confirm('Are you sure you want to reset this configuration?');">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                <button type="submit" class="btn btn-danger w-100">
                                    <i class="fas fa-undo me-1"></i> Reset to Default
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
        function resetConfig() {
            if (confirm('Are you sure you want to reset this configuration to default? This action cannot be undone.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '<?= $base ?>/admin/system-config/reset/<?= htmlspecialchars($config['key'], ENT_QUOTES) ?>';
                form.innerHTML = '<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">';
                document.body.appendChild(form);
                form.submit();
            }
        }

        // JSON validation on blur
        document.querySelectorAll('.json-editor').forEach(textarea => {
            textarea.addEventListener('blur', function() {
                try {
                    JSON.parse(this.value);
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                } catch (e) {
                    this.classList.remove('is-valid');
                    this.classList.add('is-invalid');
                }
            });
        });
    </script>
</body>
</html>