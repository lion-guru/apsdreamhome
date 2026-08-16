<?php
/** @var array $stats */
/** @var array $recent */
/** @var array $types */
$page_title = $page_title ?? 'PDF Generator';
$page_heading = $page_heading ?? 'PDF Generator';
$current_page = $current_page ?? 'pdfs';
$flash = $flash ?? [];
?>

<div class="container-fluid py-4">
    <h1 class="h3 mb-4"><?= htmlspecialchars($page_heading ?? '') ?></h1>

    <?php if (!empty($flash['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($flash['success'] ?? '') ?></div>
    <?php endif; ?>
    <?php if (!empty($flash['error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($flash['error'] ?? '') ?></div>
    <?php endif; ?>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card aps-cp-card">
                <div class="card-body aps-cp-card-body">
                    <div class="text-muted small">Generated (session)</div>
                    <div class="h3 mb-0"><?= (int)($stats['generated'] ?? 0) ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card aps-cp-card">
                <div class="card-body aps-cp-card-body">
                    <div class="text-muted small">Cache hits</div>
                    <div class="h3 mb-0"><?= (int)($stats['cache_hits'] ?? 0) ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card aps-cp-card">
                <div class="card-body aps-cp-card-body">
                    <div class="text-muted small">Cache misses</div>
                    <div class="h3 mb-0"><?= (int)($stats['cache_misses'] ?? 0) ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card aps-cp-card">
                <div class="card-body aps-cp-card-body">
                    <div class="text-muted small">Errors</div>
                    <div class="h3 mb-0"><?= (int)($stats['errors'] ?? 0) ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header aps-cp-card-header">
            <h2 class="h5 mb-0">Generate PDF on Demand</h2>
        </div>
        <div class="card-body aps-cp-card-body">
            <form id="pdfGenerateForm" class="row g-3 align-items-end">
    <?php echo CSRFProtection::csrfField(); ?>
                <div class="col-md-4">
                    <label class="form-label">Type</label>
                    <select name="type" class="form-select" required>
                        <?php foreach ($types as $t): ?>
                            <option value="<?= htmlspecialchars($t ?? '') ?>"><?= htmlspecialchars($t ?? '') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Entity ID</label>
                    <input type="number" name="id" class="form-control" min="1" required>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary">Generate</button>
                </div>
            </form>
            <div id="pdfResult" class="mt-3"></div>
        </div>
    </div>

    <div class="card aps-cp-card">
        <div class="card-header aps-cp-card-header">
            <h2 class="h5 mb-0">Recent PDFs</h2>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive"><table class="table table-sm table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Type</th>
                        <th>Filename</th>
                        <th>Size</th>
                        <th>Generated</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recent)): ?>
                        <tr><td colspan="4" class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x d-block mb-2 text-muted" aria-hidden="true"></i>No PDFs generated yet.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($recent as $f): ?>
                        <tr>
                            <td><span class="badge bg-secondary"><?= htmlspecialchars($f['type'] ?? '') ?></span></td>
                            <td><code><?= htmlspecialchars($f['filename'] ?? '') ?></code></td>
                            <td><?= number_format((int)$f['bytes'] / 1024, 1) ?> KB</td>
                            <td class="text-muted small"><?= htmlspecialchars($f['mtime'] ?? '') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table></div>
        </div>
    </div>
</div>

<script>
document.getElementById('pdfGenerateForm').addEventListener('submit', async function (e) {
    e.preventDefault();
    const form = e.target;
    const result = document.getElementById('pdfResult');
    result.innerHTML = '<div class="text-muted">Generating...</div>';
    try {
        const r = await fetch('<?= defined("BASE_URL") ? BASE_URL : "" ?>/admin/pdfs/generate', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: new URLSearchParams(new FormData(form))
        });
        const j = await r.json();
        if (j.success) {
            result.innerHTML =
                '<div class="alert alert-success">Generated: ' +
                '<a href="' + j.data.url + '" target="_blank" rel="noopener">' + j.data.url + '</a> (' +
                Math.round(j.data.bytes / 1024 * 10) / 10 + ' KB)' +
                '</div>';
        } else {
            result.innerHTML = '<div class="alert alert-danger">' + (j.error || 'Failed') + '</div>';
        }
    } catch (err) {
        result.innerHTML = '<div class="alert alert-danger">' + err.message + '</div>';
    }
});
</script>
