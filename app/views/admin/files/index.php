ï»¿<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">ðŸ“� File Manager</h1>
        <div>
            <a href="<?= BASE_URL ?>/admin/files/upload" class="btn btn-primary me-2">
                <i class="fas fa-upload"></i> Upload File
            </a>
            <a href="<?= BASE_URL ?>/admin/files/storage" class="btn btn-info me-2">
                <i class="fas fa-chart-pie"></i> Storage Stats
            </a>
            <a href="<?= BASE_URL ?>/admin/files/browse" class="btn btn-secondary">
                <i class="fas fa-folder-open"></i> Browse
            </a>
        </div>
    </div>

    <!-- Storage Stats -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body aps-cp-card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Files
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= number_format($stats['total_files'] ?? 0) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body aps-cp-card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Storage Used
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= $stats['total_size_human'] ?? '0 B' ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-hdd fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body aps-cp-card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Disk Usage
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= $stats['disk_used_percent'] ?? 0 ?>%
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-database fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body aps-cp-card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Free Space
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= $stats['disk_free'] ? round($stats['disk_free'] / 1024 / 1024 / 1024, 2) . ' GB' : 'N/A' ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-cloud fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filters</h6>
        </div>
        <div class="card-body aps-cp-card-body">
            <form method="GET" class="row">
                <div class="col-md-3">
                    <select name="category" class="form-control">
                        <option value="">All Categories</option>
                        <option value="property" <?= $category === 'property' ? 'selected' : '' ?>>Property</option>
                        <option value="user" <?= $category === 'user' ? 'selected' : '' ?>>User</option>
                        <option value="document" <?= $category === 'document' ? 'selected' : '' ?>>Document</option>
                        <option value="payment" <?= $category === 'payment' ? 'selected' : '' ?>>Payment</option>
                        <option value="general" <?= $category === 'general' ? 'selected' : '' ?>>General</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <input type="text" name="search" class="form-control" placeholder="Search files..." 
                           value="<?= htmlspecialchars($search ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Search
                    </button>
                    <a href="<?= BASE_URL ?>/admin/files" class="btn btn-secondary">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Files Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                Files (<?= $files['total'] ?? 0 ?> total)
            </h6>
        </div>
        <div class="card-body aps-cp-card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>File</th>
                            <th>Category</th>
                            <th>Size</th>
                            <th>Uploaded</th>
                            <th>Downloads</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($files['files'] ?? [])): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <i class="fas fa-folder-open fa-3x text-muted mb-3" class="style-82835"></i>
                                <h5 class="text-muted">No files found</h5>
                                <p class="text-muted mb-3">Upload your first file to get started with the file manager.</p>
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($files['files'] ?? [] as $file): ?>
                        <tr>
                            <td>
                                <i class="fas fa-file-<?= $file['file_type'] === 'image' ? 'image text-success' : ($file['file_type'] === 'document' ? 'text text-primary' : 'alt text-secondary') ?> mr-2"></i>
                                <strong><?= htmlspecialchars($file['original_name'] ?? '') ?></strong>
                                <?php if ($file['is_versioned']): ?>
                                    <span class="badge bg-info">v<?= $file['version_number'] ?></span>
                                <?php endif; ?>
                                <br>
                                <small class="text-muted"><?= substr($file['uuid'], 0, 8) ?>...</small>
                            </td>
                            <td>
                                <span class="badge bg-<?= $file['file_category'] === 'property' ? 'primary' : ($file['file_category'] === 'user' ? 'success' : ($file['file_category'] === 'document' ? 'info' : ($file['file_category'] === 'payment' ? 'warning' : 'secondary'))) ?>">
                                    <?= ucfirst($file['file_category']) ?>
                                </span>
                            </td>
                            <td><?= formatBytes($file['size_bytes']) ?></td>
                            <td><?= date('Y-m-d H:i', strtotime($file['created_at'])) ?></td>
                            <td><?= number_format($file['download_count']) ?></td>
                            <td>
                                <a href="<?= BASE_URL ?>/admin/files/details/<?= $file['uuid'] ?>" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="<?= BASE_URL ?>/admin/files/download/<?= $file['uuid'] ?>" class="btn btn-sm btn-success">
                                    <i class="fas fa-download"></i>
                                </a>
                                <form action="<?= BASE_URL ?>/admin/files/delete/<?= $file['uuid'] ?>" method="POST" class="style-26772">
                                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this file?')" aria-label="Delete"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if (($files['last_page'] ?? 1) > 1): ?>
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $files['last_page']; $i++): ?>
                    <li class="page-item <?= $files['page'] == $i ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&category=<?= $category ?>&search=<?= $search ?>"><?= $i ?></a>
                    </li>
                    <?php endfor; ?>
                </ul>
            </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php 
// Helper function for formatting bytes
function formatBytes($bytes) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $unitIndex = 0;
    while ($bytes >= 1024 && $unitIndex < count($units) - 1) {
        $bytes /= 1024;
        $unitIndex++;
    }
    return round($bytes, 2) . ' ' . $units[$unitIndex];
}
?>


