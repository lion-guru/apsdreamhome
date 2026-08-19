ï»¿<?php

/**
 * Property Images Management Page
 * Drag & Drop Multi-Upload with Gallery Management
 */

// Auth handled by PropertyImageController (requireAdmin() called in each method)

// Initialize variables with defaults if not passed from controller
$property = $property ?? [
    'id' => $_GET['id'] ?? 0,
    'title' => 'Property',
    'owner_name' => 'N/A'
];

$images = $images ?? [];

$base = BASE_URL;
$page_title = "Manage Images - " . ($property['title'] ?? 'Property');
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="mb-1"><i class="fas fa-images text-primary me-2"></i>Property Images</h2>
            <p class="text-muted mb-0">
                <?php echo htmlspecialchars($property['title'] ?? 'Property #' . $property['id']); ?> |
                Owner: <?php echo htmlspecialchars($property['owner_name'] ?? 'N/A'); ?>
            </p>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="<?php echo $base; ?>/admin/properties/<?php echo $property['id']; ?>/edit" class="btn btn-outline-primary me-2">
                <i class="fas fa-edit me-2"></i>Edit Property
            </a>
            <a href="<?php echo $base; ?>/admin/properties" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back
            </a>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($_SESSION['success'], ENT_QUOTES, 'UTF-8');
                                                    unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8');
                                                            unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($_SESSION['errors'])): ?>
        <div class="alert alert-warning alert-dismissible fade show">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <ul class="mb-0">
                <?php foreach ($_SESSION['errors'] as $error): ?>
                    <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                <?php endforeach;
                unset($_SESSION['errors']); ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Upload Drop Zone -->
    <div class="card mb-4">
        <div class="card-body aps-cp-card-body">
            <div class="drop-zone" id="dropZone">
                <i class="fas fa-cloud-upload-alt"></i>
                <h4>Drag & Drop Images Here</h4>
                <p>or click to browse (JPG, PNG, GIF, WEBP - Max 10MB each)</p>
                <button class="btn btn-primary btn-lg">
                    <i class="fas fa-folder-open me-2"></i>Select Files
                </button>
                <input type="file" id="fileInput" multiple accept="image/*" class="style-54390">
            </div>
        </div>
    </div>

    <!-- Legacy Upload Form -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="fas fa-upload me-2"></i>Traditional Upload</h5>
        </div>
        <div class="card-body aps-cp-card-body">
            <form action="<?php echo $base; ?>/admin/properties/images/upload" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="property_id" value="<?php echo $property['id']; ?>">

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Select Images</label>
                            <input type="file" name="images[]" multiple accept="image/*" class="form-control" required>
                            <div class="form-text">You can select multiple images</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Caption (Optional)</label>
                            <input type="text" name="caption" class="form-control" placeholder="e.g., Living Room, Master Bedroom">
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-success">
                    <i class="fas fa-upload me-2"></i>Upload Images
                </button>
            </form>
        </div>
    </div>

    <!-- Image Gallery -->
    <div class="card aps-cp-card">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-th-large me-2"></i>Image Gallery</h5>
            <span class="badge bg-primary"><?php echo is_array($images) ? count($images) : 0; ?> Images</span>
        </div>
        <div class="card-body aps-cp-card-body">
            <?php if (empty($images)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-images fa-3x text-muted mb-3"></i>
                    <h4>No Images Yet</h4>
                    <p class="text-muted">Upload images to showcase this property</p>
                </div>
            <?php else: ?>
                <div class="row" id="imageGrid">
                    <?php foreach (($images ?? []) as $image): ?>
                        <div class="col-md-4 mb-3">
                            <div class="card <?php echo $image['is_primary'] ? 'border-warning border-3' : ''; ?>">
                                <?php if ($image['is_primary']): ?>
                                    <span class="badge bg-warning position-absolute top-0 start-0 m-2">
                                        <i class="fas fa-star me-1"></i>Primary
                                    </span>
                                <?php endif; ?>
                                <img src="<?php echo $base; ?>/<?php echo htmlspecialchars($image['thumbnail_path'] ?? $image['image_path']); ?>"
                                    alt="Property Image"
                                    class="card-img-top"
                                    class="style-12213"
                                    onclick="openLightbox('<?php echo $base; ?>/<?php echo $image['image_path']; ?>')" loading="lazy">
                                <div class="card-body p-2">
                                    <input type="text"
                                        class="form-control form-control-sm mb-2"
                                        value="<?php echo htmlspecialchars($image['caption'] ?? ''); ?>"
                                        placeholder="Add caption..."
                                        onblur="updateCaption(<?php echo $image['id']; ?>, this.value)">

                                    <div class="d-flex gap-1">
                                        <?php if (!$image['is_primary']): ?>
                                            <form method="POST" action="<?php echo $base; ?>/admin/properties/images/set-primary" class="d-inline">
                                                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                                <input type="hidden" name="image_id" value="<?php echo $image['id']; ?>">
                                                <input type="hidden" name="property_id" value="<?php echo $property['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-warning" aria-label="Favorite"><i class="fas fa-star"></i></button>
                                            </form>
                                        <?php endif; ?>

                                        <form method="POST" action="<?php echo $base; ?>/admin/properties/images/delete" class="d-inline" data-aps-confirm="Delete this image?">
                                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                            <input type="hidden" name="image_id" value="<?php echo $image['id']; ?>">
                                            <input type="hidden" name="property_id" value="<?php echo $property['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" aria-label="Delete"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="alert alert-info mt-3">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Tip:</strong> Click the star to set primary image.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Upload Progress Panel -->
<div class="upload-progress" id="uploadProgress" class="style-54390">
    <h6 class="mb-3"><i class="fas fa-upload me-2"></i>Uploading...</h6>
    <div id="progressList"></div>
</div>

<!-- Lightbox Modal -->
<div class="modal fade" id="lightboxModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Image Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="lightboxImage" src="" alt="Preview" class="img-fluid">
            </div>
        </div>
    </div>
</div>

<style>
    .drop-zone {
        border: 3px dashed #0d9488;
        border-radius: 16px;
        padding: 60px 20px;
        text-align: center;
        background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%);
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .drop-zone:hover,
    .drop-zone.dragover {
        border-color: #0f766e;
        background: linear-gradient(135deg, #e0e7ff 0%, #ddd6fe 100%);
        transform: scale(1.02);
    }

    .drop-zone i {
        font-size: 4rem;
        color: #0d9488;
        margin-bottom: 20px;
    }

    .upload-progress {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
    }

    .image-actions .btn {
        padding: 8px 12px;
        font-size: 0.875rem;
    }

    /* Upload Progress */
    .upload-progress {
        position: fixed;
        bottom: 20px;
        right: 20px;
        width: 400px;
        max-width: 90vw;
        background: white;
        border-radius: 12px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        padding: 20px;
        z-index: 9999;
        display: none;
    }

    .upload-progress.active {
        display: block;
    }

    .progress-item {
        display: flex;
        align-items: center;
        padding: 10px 0;
        border-bottom: 1px solid #e5e7eb;
    }

    .progress-item:last-child {
        border-bottom: none;
    }

    .progress-bar {
        flex: 1;
        height: 8px;
        background: #e5e7eb;
        border-radius: 4px;
        margin: 0 10px;
        overflow: hidden;
    }

    .progress-bar .progress {
        height: 100%;
        background: linear-gradient(90deg, #0d9488, #0f766e);
        transition: width 0.3s ease;
    }

    /* Sortable Grid */
    .image-card.sortable {
        cursor: move;
    }

    .image-card.sortable.dragging {
        opacity: 0.5;
        transform: rotate(3deg);
    }

    /* Caption Input */
    .caption-input {
        width: 100%;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        padding: 8px 12px;
        font-size: 0.875rem;
        margin-top: 10px;
    }

    .caption-input:focus {
        outline: none;
        border-color: #0d9488;
    }

    /* Sidebar */
    .sidebar {
        min-height: 100vh;
        background: linear-gradient(180deg, #1e1b4b 0%, #312e81 100%);
        padding: 20px;
    }

    .sidebar .nav-link {
        color: #c7d2fe;
        padding: 12px 15px;
        border-radius: 8px;
        margin-bottom: 5px;
        transition: all 0.2s;
    }

    .sidebar .nav-link:hover,
    .sidebar .nav-link.active {
        background: rgba(79, 70, 229, 0.3);
        color: white;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #6b7280;
    }

    .empty-state i {
        font-size: 5rem;
        margin-bottom: 20px;
        color: #d1d5db;
    }
</style>
