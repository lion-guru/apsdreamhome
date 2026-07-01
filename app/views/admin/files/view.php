<?php
$pageTitle = $pageTitle ?? 'File Details';
$base = $base ?? (defined('BASE_URL') ? BASE_URL : '/apsdreamhome');
$file = $file ?? ['id' => 0, 'original_name' => '', 'file_type' => '', 'size_bytes' => 0, 'file_category' => '', 'description' => '', 'created_at' => '', 'download_count' => 0, 'uuid' => '', 'uploaded_by' => '', 'mime_type' => ''];
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-file me-2 text-primary"></i>File Details</h1>
        <div>
            <a href="<?= $base ?>/admin/files" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
            <a href="<?= $base ?>/admin/files/download/<?= $file['uuid'] ?? '' ?>" class="btn btn-success"><i class="fas fa-download me-1"></i>Download</a>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary"><?= htmlspecialchars($file['original_name'] ?? '') ?></h6></div>
                <div class="card-body aps-cp-card-body">
                    <div class="table-responsive"><table class="table table-bordered">
                        <tr><th style="width:180px">File Name</th><td><?= htmlspecialchars($file['original_name'] ?? '') ?></td></tr>
                        <tr><th>Type</th><td><span class="badge bg-info"><?= htmlspecialchars($file['mime_type'] ?? $file['file_type'] ?? '') ?></span></td></tr>
                        <tr><th>Category</th><td><span class="badge bg-primary"><?= htmlspecialchars($file['file_category'] ?? '') ?></span></td></tr>
                        <tr><th>Size</th><td><?php $b = floatval($file['size_bytes'] ?? 0); echo $b >= 1048576 ? number_format($b / 1048576, 2) . ' MB' : ($b >= 1024 ? number_format($b / 1024, 2) . ' KB' : number_format($b) . ' B'); ?></td></tr>
                        <tr><th>Uploaded By</th><td><?= htmlspecialchars($file['uploaded_by'] ?? '') ?></td></tr>
                        <tr><th>Uploaded Date</th><td><?= htmlspecialchars($file['created_at'] ?? '') ?></td></tr>
                        <tr><th>Downloads</th><td><?= number_format($file['download_count'] ?? 0) ?></td></tr>
                        <tr><th>Description</th><td><?= htmlspecialchars($file['description'] ?? 'N/A') ?></td></tr>
                    </table></div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3"><h6 class="m-0 fw-bold text-info">Preview</h6></div>
                <div class="card-body text-center py-5">
                    <?php
                    $mime = $file['mime_type'] ?? '';
                    $isImage = strpos($mime, 'image/') === 0;
                    $isPdf = $mime === 'application/pdf';
                    ?>
                    <?php if ($isImage): ?>
                        <img src="<?= $base ?>/admin/files/download/<?= htmlspecialchars($file['uuid'] ?? '') ?>" alt="<?= htmlspecialchars($file['original_name'] ?? '') ?>" class="img-fluid rounded" style="max-height:300px">
                    <?php elseif ($isPdf): ?>
                        <i class="fas fa-file-pdf fa-5x text-danger"></i>
                        <p class="mt-2">PDF document - <a href="<?= $base ?>/admin/files/download/<?= $file['uuid'] ?? '' ?>">Download to view</a></p>
                    <?php else: ?>
                        <i class="fas fa-file-alt fa-5x text-muted"></i>
                        <p class="mt-2">Preview not available for this file type.</p>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card shadow">
                <div class="card-header py-3"><h6 class="m-0 fw-bold text-danger">Danger Zone</h6></div>
                <div class="card-body aps-cp-card-body">
                    <form method="POST" action="<?= $base ?>/admin/files/delete/<?= $file['uuid'] ?? '' ?>" onsubmit="return confirm('Permanently delete this file?')">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <button type="submit" class="btn btn-danger w-100"><i class="fas fa-trash me-1"></i>Delete File</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
