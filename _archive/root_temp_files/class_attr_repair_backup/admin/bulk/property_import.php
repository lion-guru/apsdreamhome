<?php
/** Admin UI: Bulk Property CSV Import */
if (!defined('BASE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $basePath = preg_replace('#/public$#', '', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
    define('BASE_URL', $protocol . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . $basePath);
}
$preview = $preview ?? null;
$result = $result ?? null;
$csrf = $csrf ?? '';
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-file-csv text-primary me-2"></i>Bulk Property Import</h2>
        <div>
            <a href="<?= BASE_URL ?>/admin/bulk/property-import/sample" class="btn btn-outline-secondary">
                <i class="fas fa-download me-1"></i> Sample CSV
            </a>
            <a href="<?= BASE_URL ?>/admin/bulk/property-import/template" class="btn btn-primary">
                <i class="fas fa-file-download me-1"></i> Template
            </a>
        </div>
    </div>

    <?php if (!empty($flash_success)): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-1"></i> <?= htmlspecialchars($flash_success ?? '') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($flash_error)): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-triangle me-1"></i> <?= htmlspecialchars($flash_error ?? '') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white"><h5 class="mb-0">1. Upload CSV</h5></div>
                <div class="card-body aps-cp-card-body">
                    <form method="POST" action="<?= BASE_URL ?>/admin/bulk/property-import/upload" enctype="multipart/form-data" id="upload-form">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf ?? '') ?>">
                        <div class="upload-dropzone border-2 border-dashed rounded p-5 text-center bg-light" id="dropzone">
                            <i class="fas fa-cloud-upload-alt fa-3x text-primary mb-2"></i>
                            <p class="mb-1"><strong>Drag &amp; drop CSV here</strong></p>
                            <p class="text-muted small mb-3">or click to browse (max 10MB)</p>
                            <input type="file" name="csv" id="csv-input" accept=".csv,text/csv" class="d-none" required>
                            <button type="button" class="btn btn-primary" id="browse-btn"><i class="fas fa-folder-open me-1"></i> Browse</button>
                        </div>
                        <div id="file-info" class="alert alert-info mt-3" class="style-24280"></div>
                        <div class="d-grid mt-3">
                            <button type="submit" class="btn btn-primary" id="preview-btn" disabled>
                                <i class="fas fa-search me-1"></i> Preview &amp; Validate
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white"><h5 class="mb-0"><i class="fas fa-info-circle me-1"></i> CSV Format</h5></div>
                <div class="card-body aps-cp-card-body">
                    <p class="text-muted small mb-2"><strong>Required columns:</strong></p>
                    <code class="d-block bg-light p-2 rounded small">title, type, listing_type, price</code>
                    <hr>
                    <p class="text-muted small mb-2"><strong>Valid types:</strong></p>
                    <code class="d-block bg-light p-2 rounded small">plot, flat, house, shop, farmhouse, land, apartment, villa</code>
                    <hr>
                    <p class="text-muted small mb-2"><strong>Valid listing_type:</strong></p>
                    <code class="d-block bg-light p-2 rounded small">sale, sell, rent</code>
                    <hr>
                    <p class="text-muted small mb-2"><strong>Multi-value columns (semicolon-separated):</strong></p>
                    <code class="d-block bg-light p-2 rounded small">amenities, images</code>
                    <hr>
                    <p class="text-muted small mb-0"><i class="fas fa-shield-alt text-success me-1"></i> Duplicates (title+location) are auto-skipped. Imports are transactional (batch of 100).</p>
                </div>
            </div>
        </div>
    </div>

    <?php if ($preview && !empty($preview['ok'])): ?>
        <div class="card shadow-sm border-0 mt-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">2. Preview &amp; Validate</h5>
                <span class="badge bg-primary">Total rows: <?= (int)$preview['total_rows'] ?></span>
            </div>
            <div class="card-body aps-cp-card-body">
                <?php if (!empty($preview['errors'])): ?>
                    <div class="alert alert-warning">
                        <strong><i class="fas fa-exclamation-triangle me-1"></i> <?= count($preview['errors']) ?> validation error(s):</strong>
                        <ul class="mb-0 mt-2 small"><?php foreach (array_slice($preview['errors'], 0, 10) as $e): ?><li><?= htmlspecialchars($e ?? '') ?></li><?php endforeach; ?></ul>
                    </div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="table table-sm table-striped">
                        <thead class="table-light">
                            <tr>
                                <th>Row</th>
                                <th>Status</th>
                                <th>Title</th>
                                <th>Type</th>
                                <th>Listing</th>
                                <th>Price</th>
                                <th>City</th>
                                <th>Owner</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($preview['preview'] as $p): ?>
                                <tr>
                                    <td><?= (int)$p['row'] ?></td>
                                    <td>
                                        <?php if ($p['valid']): ?>
                                            <span class="badge bg-success">OK</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger" title="<?= htmlspecialchars(implode('; ', $p['errors'])) ?>">Invalid</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($p['data']['title'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($p['data']['type'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($p['data']['listing_type'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($p['data']['price'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($p['data']['city'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($p['data']['owner_name'] ?? '-') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <form method="POST" action="<?= BASE_URL ?>/admin/bulk/property-import/execute" class="mt-3">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf ?? '') ?>">
                    <input type="hidden" name="preview_data" value="<?= htmlspecialchars(json_encode($preview)) ?>">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="skip_duplicates" id="skip_dup" value="1" checked>
                                <label class="form-check-label" for="skip_dup">Skip duplicates (title + location match)</label>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-database me-1"></i> Execute Import (<?= (int)$preview['total_rows'] ?> rows)
                        </button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($result && !empty($result['ok'])): ?>
        <div class="card shadow-sm border-0 mt-4">
            <div class="card-header bg-success text-white"><h5 class="mb-0">3. Import Complete</h5></div>
            <div class="card-body aps-cp-card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="card border-success">
                            <div class="card-body text-center">
                                <h2 class="text-success mb-0"><?= (int)$result['imported'] ?></h2>
                                <small class="text-muted">Imported</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-warning">
                            <div class="card-body text-center">
                                <h2 class="text-warning mb-0"><?= (int)$result['skipped'] ?></h2>
                                <small class="text-muted">Skipped</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-danger">
                            <div class="card-body text-center">
                                <h2 class="text-danger mb-0"><?= (int)($result['total_errors'] ?? count($result['errors'] ?? [])) ?></h2>
                                <small class="text-muted">Errors</small>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if (!empty($result['errors'])): ?>
                    <h6 class="mt-4">First <?= count($result['errors']) ?> error(s):</h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead><tr><th>#</th><th>Error</th></tr></thead>
                            <tbody>
                                <?php foreach ($result['errors'] as $i => $e): ?>
                                    <tr><td><?= $i + 1 ?></td><td><code class="small"><?= htmlspecialchars($e ?? '') ?></code></td></tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

                <div class="mt-3">
                    <a href="<?= BASE_URL ?>/admin/user-properties" class="btn btn-outline-primary">
                        <i class="fas fa-list me-1"></i> View User Properties
                    </a>
                    <a href="<?= BASE_URL ?>/admin/bulk/property-import" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> Import More
                    </a>
                </div>
            </div>
        </div>
    <?php elseif ($result && empty($result['ok'])): ?>
        <div class="card shadow-sm border-0 mt-4">
            <div class="card-body aps-cp-card-body">
                <div class="alert alert-danger">
                    <h5><i class="fas fa-times-circle me-1"></i> Import failed</h5>
                    <?= htmlspecialchars($result['error'] ?? 'Unknown error') ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
(function() {
    var dropzone = document.getElementById('dropzone');
    var fileInput = document.getElementById('csv-input');
    var browseBtn = document.getElementById('browse-btn');
    var previewBtn = document.getElementById('preview-btn');
    var fileInfo = document.getElementById('file-info');
    if (!dropzone) return;
    browseBtn.addEventListener('click', () => fileInput.click());
    dropzone.addEventListener('click', (e) => { if (e.target !== browseBtn) fileInput.click(); });
    ['dragenter', 'dragover'].forEach(e => dropzone.addEventListener(e, (ev) => { ev.preventDefault(); dropzone.classList.add('border-primary', 'bg-light'); }));
    ['dragleave', 'drop'].forEach(e => dropzone.addEventListener(e, (ev) => { ev.preventDefault(); dropzone.classList.remove('border-primary', 'bg-light'); }));
    dropzone.addEventListener('drop', (ev) => { if (ev.dataTransfer.files.length) { fileInput.files = ev.dataTransfer.files; handleFile(); } });
    fileInput.addEventListener('change', handleFile);
    function handleFile() {
        if (!fileInput.files.length) return;
        var f = fileInput.files[0];
        fileInfo.innerHTML = '<i class="fas fa-file-csv me-1"></i> <strong>' + f.name + '</strong> (' + (f.size/1024).toFixed(1) + ' KB)';
        fileInfo.style.display = 'block';
        previewBtn.disabled = false;
    }
})();
</script>
