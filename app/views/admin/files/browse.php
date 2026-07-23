<?php
$pageTitle = $pageTitle ?? 'File Browser';
$base = $base ?? (defined('BASE_URL') ? BASE_URL : '/' . trim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/'));
$files = $files ?? [];
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-folder-open me-2 text-warning"></i>File Browser</h1>
        <a href="<?= $base ?>/admin/files" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>
    <div class="card shadow">
        <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary">All Files</h6></div>
        <div class="card-body aps-cp-card-body">
            <?php if (empty($files)): ?>
                <p class="text-muted text-center py-4"><i class="fas fa-folder-open fa-2x d-block mb-2"></i>No files found in this category.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Size</th>
                                <th>Uploaded By</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($files as $f): ?>
                            <tr>
                                <td>
                                    <i class="fas fa-file-<?= (strpos($f['mime_type'] ?? '', 'image') === 0) ? 'image text-success' : 'alt text-secondary' ?> me-2"></i>
                                    <?= htmlspecialchars($f['original_name'] ?? '') ?>
                                </td>
                                <td><span class="badge bg-secondary"><?= htmlspecialchars($f['mime_type'] ?? $f['file_type'] ?? '') ?></span></td>
                                <td><?php $b = floatval($f['size_bytes'] ?? 0); echo $b >= 1048576 ? number_format($b / 1048576, 1) . ' MB' : ($b >= 1024 ? number_format($b / 1024, 1) . ' KB' : number_format($b) . ' B'); ?></td>
                                <td><?= htmlspecialchars($f['uploaded_by'] ?? $f['uploaded_by_name'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($f['created_at'] ?? '') ?></td>
                                <td>
                                    <a href="<?= $base ?>/admin/files/details/<?= $f['uuid'] ?? $f['id'] ?? '' ?>" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
                                    <a href="<?= $base ?>/admin/files/download/<?= $f['uuid'] ?? $f['id'] ?? '' ?>" class="btn btn-sm btn-success"><i class="fas fa-download"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
