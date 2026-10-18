<?php
$configured = $configured ?? false;
$files = $files ?? [];
$driver = $driver ?? 's3';
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1>Cloud Storage (S3)</h1>
            <small class="text-muted">Upload, manage, and download files from S3-compatible storage. Credentials configured in <a href="<?= BASE_URL . '/admin/service-configs' ?>">Service Configuration</a>.</small>
        </div>
    </div>

    <?php if (!$configured): ?>
    <div class="card mb-4"><div class="card-body">
        <h5><i class="fas fa-exclamation-triangle text-warning me-2"></i>Not Configured</h5>
        <p class="text-muted">S3 storage requires these settings in <a href="<?= BASE_URL . '/admin/service-configs' ?>">Service Configuration</a> → AWS S3 group:</p>
        <ul>
            <li><code>access_key</code> — AWS Access Key ID</li>
            <li><code>secret_key</code> — AWS Secret Access Key</li>
            <li><code>region</code> — AWS region (e.g. ap-south-1)</li>
            <li><code>bucket</code> — S3 bucket name</li>
        </ul>
    </div></div>
    <?php else: ?>

    <div class="card mb-4"><div class="card-body">
        <form id="uploadForm" onsubmit="return uploadFile(event)" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small">File</label>
                    <input type="file" name="file" class="form-control" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Prefix (folder, optional)</label>
                    <input type="text" name="prefix" class="form-control" placeholder="documents/2026">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-upload me-2"></i>Upload</button>
                </div>
            </div>
        </form>
        <div id="upload-status" class="mt-2"></div>
    </div></div>

    <div class="card"><div class="card-body p-0">
        <table class="table mb-0">
            <thead><tr><th>Path</th><th>Size</th><th>Actions</th></tr></thead>
            <tbody>
            <?php if (empty($files)): ?>
                <tr><td colspan="3" class="text-center text-muted py-4">No files in bucket</td></tr>
            <?php else: foreach ($files as $f): ?>
                <tr>
                    <td><code><?= htmlspecialchars($f['path'] ?? $f['name'] ?? '') ?></code></td>
                    <td><?= isset($f['size']) ? number_format($f['size'] / 1024, 1) . ' KB' : '—' ?></td>
                    <td>
                        <a href="<?= BASE_URL . '/admin/cloud-storage/download?path=' . urlencode($f['path'] ?? $f['name'] ?? '') ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-download"></i></a>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteFile('<?= htmlspecialchars($f['path'] ?? $f['name'] ?? '') ?>')"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div></div>

    <?php endif; ?>
</div>

<script>
function uploadFile(e) {
    e.preventDefault();
    const form = e.target;
    const status = document.getElementById('upload-status');
    status.innerHTML = '<div class="alert alert-info py-1">Uploading...</div>';
    const formData = new FormData(form);
    fetch('<?= BASE_URL . '/admin/cloud-storage/upload' ?>', {
        method: 'POST', headers: {'X-Requested-With':'XMLHttpRequest'}, body: formData
    }).then(r => r.json()).then(d => {
        if (d.success) { status.innerHTML = '<div class="alert alert-success py-1">' + d.message + '</div>'; setTimeout(() => location.reload(), 1000); }
        else { status.innerHTML = '<div class="alert alert-danger py-1">' + (d.error || 'Upload failed') + '</div>'; }
    }).catch(() => { status.innerHTML = '<div class="alert alert-danger py-1">Error uploading</div>'; });
    return false;
}
function deleteFile(path) {
    if (!confirm('Delete "' + path + '"?')) return;
    fetch('<?= BASE_URL . '/admin/cloud-storage/delete' ?>', {
        method: 'POST', headers: {'Content-Type':'application/x-www-form-urlencoded', 'X-Requested-With':'XMLHttpRequest'},
        body: 'path=' + encodeURIComponent(path) + '&csrf_token=<?= $_SESSION['csrf_token'] ?? '' ?>'
    }).then(r => r.json()).then(d => {
        if (d.success) { location.reload(); } else { alert(d.error || 'Delete failed'); }
    }).catch(() => alert('Error'));
}
</script>
