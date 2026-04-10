<?php
/**
 * Media Library View
 */
$media = $media ?? [];
$page = $page ?? 1;
$total = $total ?? 0;
$page_title = $page_title ?? 'Media Library';
$base = defined('BASE_URL') ? BASE_URL : '/apsdreamhome';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1"><i class="fas fa-photo-video me-2 text-primary"></i>Media Library</h2>
                <p class="text-muted mb-0">Manage all media files</p>
            </div>
            <div>
                <button class="btn btn-primary me-2" onclick="alert('Upload feature coming soon')">
                    <i class="fas fa-upload me-2"></i>Upload New
                </button>
                <a href="<?php echo $base; ?>/admin/dashboard" class="btn btn-outline-secondary">Back</a>
            </div>
        </div>
        
        <!-- Filters -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <select class="form-select">
                            <option value="">All Types</option>
                            <option value="image">Images</option>
                            <option value="video">Videos</option>
                            <option value="document">Documents</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <input type="text" class="form-control" placeholder="Search media...">
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-primary w-100">Filter</button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Media Grid -->
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <?php if (!empty($media)): ?>
                    <div class="row g-3">
                        <?php foreach ($media as $item): ?>
                            <div class="col-md-2 col-sm-4">
                                <div class="card border">
                                    <?php if (strpos($item['type'], 'image') !== false): ?>
                                        <img src="<?php echo $base . '/' . $item['path']; ?>" class="card-img-top" style="height: 120px; object-fit: cover;">
                                    <?php else: ?>
                                        <div class="bg-light d-flex align-items-center justify-content-center" style="height: 120px;">
                                            <i class="fas fa-file fa-2x text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div class="card-body p-2">
                                        <p class="card-text small text-truncate mb-1"><?php echo htmlspecialchars(item['name'] ?? ''); ?></p>
                                        <small class="text-muted"><?php echo $item['size']; ?></small>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-images fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No media files found</p>
                        <button class="btn btn-primary" onclick="alert('Upload feature coming soon')">
                            <i class="fas fa-upload me-2"></i>Upload First File
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
