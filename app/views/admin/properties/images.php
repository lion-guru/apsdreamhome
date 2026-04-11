<?php
/**
 * Property Images Management Page
 * Drag & Drop Multi-Upload with Gallery Management
 */

$base = BASE_URL;
$page_title = "Manage Images - " . ($property['title'] ?? 'Property');

// Get admin info from session
$admin_name = $_SESSION['admin_name'] ?? 'Admin';
$admin_role = $_SESSION['admin_role'] ?? 'admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> | APS Dream Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body { background: #f8f9fa; font-family: 'Segoe UI', sans-serif; }
        
        /* Drop Zone */
        .drop-zone {
            border: 3px dashed #4f46e5;
            border-radius: 16px;
            padding: 60px 20px;
            text-align: center;
            background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%);
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .drop-zone:hover, .drop-zone.dragover {
            border-color: #7c3aed;
            background: linear-gradient(135deg, #e0e7ff 0%, #ddd6fe 100%);
            transform: scale(1.02);
        }
        .drop-zone i {
            font-size: 4rem;
            color: #4f46e5;
            margin-bottom: 20px;
        }
        .drop-zone h4 {
            color: #1e1b4b;
            margin-bottom: 10px;
        }
        .drop-zone p {
            color: #6b7280;
            margin-bottom: 20px;
        }
        
        /* Image Gallery Grid */
        .image-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        
        .image-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            position: relative;
        }
        .image-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        .image-card.primary {
            border: 3px solid #f59e0b;
        }
        .image-card .primary-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            background: linear-gradient(135deg, #f59e0b, #fbbf24);
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            z-index: 10;
        }
        
        .image-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        
        .image-actions {
            padding: 15px;
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
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
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
            background: linear-gradient(90deg, #4f46e5, #7c3aed);
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
            border-color: #4f46e5;
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
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background: rgba(79,70,229,0.3);
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
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-2 sidebar">
            <h5 class="text-white mb-4"><i class="fas fa-home me-2"></i>APS Dream Home</h5>
            <nav class="nav flex-column">
                <a href="<?php echo $base; ?>/admin/dashboard" class="nav-link">
                    <i class="fas fa-chart-pie me-2"></i>Dashboard
                </a>
                <a href="<?php echo $base; ?>/admin/properties" class="nav-link">
                    <i class="fas fa-building me-2"></i>Properties
                </a>
                <a href="<?php echo $base; ?>/admin/properties/<?php echo $property['id']; ?>/images" class="nav-link active">
                    <i class="fas fa-images me-2"></i>Images
                </a>
                <a href="<?php echo $base; ?>/admin/leads" class="nav-link">
                    <i class="fas fa-bullseye me-2"></i>Leads
                </a>
                <a href="<?php echo $base; ?>/admin/logout" class="nav-link text-danger mt-4">
                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="col-md-10 p-4">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-1"><i class="fas fa-images text-primary me-2"></i>Property Images</h2>
                    <p class="text-muted mb-0">
                        <?php echo htmlspecialchars($property['title'] ?? 'Property #' . $property['id']); ?> | 
                        Owner: <?php echo htmlspecialchars($property['owner_name'] ?? 'N/A'); ?>
                    </p>
                </div>
                <div>
                    <a href="<?php echo $base; ?>/admin/properties/<?php echo $property['id']; ?>/edit" class="btn btn-outline-primary me-2">
                        <i class="fas fa-edit me-2"></i>Edit Property
                    </a>
                    <a href="<?php echo $base; ?>/admin/properties" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back
                    </a>
                </div>
            </div>

            <!-- Flash Messages -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle me-2"></i><?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-circle me-2"></i><?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (!empty($_SESSION['errors'])): ?>
                <div class="alert alert-warning alert-dismissible fade show">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <ul class="mb-0">
                        <?php foreach ($_SESSION['errors'] as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; unset($_SESSION['errors']); ?>
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Upload Drop Zone -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="drop-zone" id="dropZone">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <h4>Drag & Drop Images Here</h4>
                        <p>or click to browse (JPG, PNG, GIF, WEBP - Max 10MB each)</p>
                        <button class="btn btn-primary btn-lg">
                            <i class="fas fa-folder-open me-2"></i>Select Files
                        </button>
                        <input type="file" id="fileInput" multiple accept="image/*" style="display: none;">
                    </div>
                </div>
            </div>

            <!-- Legacy Upload Form -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-upload me-2"></i>Traditional Upload</h5>
                </div>
                <div class="card-body">
                    <form action="<?php echo $base; ?>/admin/properties/images/upload" method="POST" enctype="multipart/form-data">
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
            <div class="card">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-th-large me-2"></i>Image Gallery</h5>
                    <span class="badge bg-primary"><?php echo count($images); ?> Images</span>
                </div>
                <div class="card-body">
                    <?php if (empty($images)): ?>
                        <div class="empty-state">
                            <i class="fas fa-images"></i>
                            <h4>No Images Yet</h4>
                            <p>Upload images to showcase this property</p>
                        </div>
                    <?php else: ?>
                        <div class="image-grid" id="imageGrid">
                            <?php foreach ($images as $image): ?>
                                <div class="image-card <?php echo $image['is_primary'] ? 'primary' : ''; ?> sortable" data-id="<?php echo $image['id']; ?>">
                                    <?php if ($image['is_primary']): ?>
                                        <span class="primary-badge"><i class="fas fa-star me-1"></i>Primary</span>
                                    <?php endif; ?>
                                    
                                    <img src="<?php echo $base; ?>/<?php echo htmlspecialchars($image['thumbnail_path'] ?? $image['image_path']); ?>" 
                                         alt="Property Image"
                                         onclick="openLightbox('<?php echo $base; ?>/<?php echo $image['image_path']; ?>')">
                                    
                                    <div class="p-3">
                                        <input type="text" 
                                               class="caption-input" 
                                               value="<?php echo htmlspecialchars($image['caption'] ?? ''); ?>"
                                               placeholder="Add caption..."
                                               onblur="updateCaption(<?php echo $image['id']; ?>, this.value)">
                                        
                                        <div class="image-actions">
                                            <?php if (!$image['is_primary']): ?>
                                                <form method="POST" action="<?php echo $base; ?>/admin/properties/images/set-primary" class="d-inline">
                                                    <input type="hidden" name="image_id" value="<?php echo $image['id']; ?>">
                                                    <input type="hidden" name="property_id" value="<?php echo $property['id']; ?>">
                                                    <button type="submit" class="btn btn-outline-warning btn-sm">
                                                        <i class="fas fa-star me-1"></i>Set Primary
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <span class="text-warning"><i class="fas fa-star me-1"></i>Primary</span>
                                            <?php endif; ?>
                                            
                                            <form method="POST" action="<?php echo $base; ?>/admin/properties/images/delete" class="d-inline" onsubmit="return confirm('Delete this image?')">
                                                <input type="hidden" name="image_id" value="<?php echo $image['id']; ?>">
                                                <input type="hidden" name="property_id" value="<?php echo $property['id']; ?>">
                                                <button type="submit" class="btn btn-outline-danger btn-sm">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="alert alert-info mt-4">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Tip:</strong> Drag and drop images to reorder them. Click the star to set primary image.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Upload Progress Panel -->
<div class="upload-progress" id="uploadProgress">
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
                <img id="lightboxImage" src="" class="img-fluid" style="max-height: 80vh;">
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Drag & Drop Upload
const dropZone = document.getElementById('dropZone');
const fileInput = document.getElementById('fileInput');
const uploadProgress = document.getElementById('uploadProgress');
const progressList = document.getElementById('progressList');
const propertyId = <?php echo $property['id']; ?>;

// Click to browse
dropZone.addEventListener('click', () => fileInput.click());

// File input change
fileInput.addEventListener('change', (e) => {
    handleFiles(e.target.files);
});

// Drag events
dropZone.addEventListener('dragover', (e) => {
    e.preventDefault();
    dropZone.classList.add('dragover');
});

dropZone.addEventListener('dragleave', () => {
    dropZone.classList.remove('dragover');
});

dropZone.addEventListener('drop', (e) => {
    e.preventDefault();
    dropZone.classList.remove('dragover');
    handleFiles(e.dataTransfer.files);
});

// Handle uploaded files
function handleFiles(files) {
    if (files.length === 0) return;
    
    uploadProgress.classList.add('active');
    progressList.innerHTML = '';
    
    Array.from(files).forEach((file, index) => {
        // Validate file type
        if (!file.type.startsWith('image/')) {
            showProgress(file.name, 0, 'Invalid file type');
            return;
        }
        
        // Validate file size (10MB)
        if (file.size > 10 * 1024 * 1024) {
            showProgress(file.name, 0, 'File too large (max 10MB)');
            return;
        }
        
        uploadFile(file, index);
    });
}

// Show progress item
function showProgress(filename, percent, error = null) {
    const id = 'progress-' + Math.random().toString(36).substr(2, 9);
    
    const html = `
        <div class="progress-item" id="${id}">
            <i class="fas fa-image text-primary me-2"></i>
            <span class="flex-grow-1 text-truncate" style="max-width: 150px;">${filename}</span>
            <div class="progress-bar">
                <div class="progress" style="width: ${percent}%"></div>
            </div>
            <span class="ms-2" style="min-width: 50px;">
                ${error ? `<i class="fas fa-exclamation-circle text-danger"></i>` : percent + '%'}
            </span>
        </div>
    `;
    
    progressList.insertAdjacentHTML('beforeend', html);
    return id;
}

// Upload single file
function uploadFile(file, index) {
    const progressId = showProgress(file.name, 0);
    
    const formData = new FormData();
    formData.append('file', file);
    formData.append('property_id', propertyId);
    
    const xhr = new XMLHttpRequest();
    
    xhr.upload.addEventListener('progress', (e) => {
        if (e.lengthComputable) {
            const percent = Math.round((e.loaded / e.total) * 100);
            updateProgress(progressId, percent);
        }
    });
    
    xhr.addEventListener('load', () => {
        if (xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            if (response.success) {
                updateProgress(progressId, 100);
                setTimeout(() => {
                    location.reload(); // Refresh to show new image
                }, 500);
            } else {
                updateProgress(progressId, 0, response.error);
            }
        } else {
            updateProgress(progressId, 0, 'Upload failed');
        }
    });
    
    xhr.addEventListener('error', () => {
        updateProgress(progressId, 0, 'Network error');
    });
    
    xhr.open('POST', '<?php echo $base; ?>/admin/properties/images/ajax-upload');
    xhr.send(formData);
}

// Update progress bar
function updateProgress(id, percent, error = null) {
    const item = document.getElementById(id);
    if (item) {
        const bar = item.querySelector('.progress');
        const status = item.querySelector('.ms-2');
        
        bar.style.width = percent + '%';
        
        if (error) {
            status.innerHTML = `<i class="fas fa-exclamation-circle text-danger" title="${error}"></i>`;
        } else {
            status.textContent = percent + '%';
        }
    }
}

// Update caption via AJAX
function updateCaption(imageId, caption) {
    const formData = new FormData();
    formData.append('image_id', imageId);
    formData.append('caption', caption);
    
    fetch('<?php echo $base; ?>/admin/properties/images/update-caption', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Caption updated');
        }
    })
    .catch(error => console.error('Error:', error));
}

// Lightbox
function openLightbox(src) {
    document.getElementById('lightboxImage').src = src;
    new bootstrap.Modal(document.getElementById('lightboxModal')).show();
}

// Drag & Drop Sorting (Simple implementation)
let draggedItem = null;

const sortableItems = document.querySelectorAll('.sortable');

sortableItems.forEach(item => {
    item.draggable = true;
    
    item.addEventListener('dragstart', function() {
        draggedItem = this;
        this.classList.add('dragging');
    });
    
    item.addEventListener('dragend', function() {
        this.classList.remove('dragging');
        draggedItem = null;
        
        // Save new order
        saveOrder();
    });
    
    item.addEventListener('dragover', function(e) {
        e.preventDefault();
        if (this !== draggedItem) {
            const rect = this.getBoundingClientRect();
            const offset = e.clientX - rect.left;
            const width = rect.width;
            
            if (offset < width / 2) {
                this.parentNode.insertBefore(draggedItem, this);
            } else {
                this.parentNode.insertBefore(draggedItem, this.nextSibling);
            }
        }
    });
});

// Save sort order to server
function saveOrder() {
    const items = document.querySelectorAll('.sortable');
    const order = Array.from(items).map(item => item.dataset.id);
    
    const formData = new FormData();
    order.forEach((id, index) => {
        formData.append('order[]', id);
    });
    
    fetch('<?php echo $base; ?>/admin/properties/images/reorder', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Order saved');
        }
    })
    .catch(error => console.error('Error:', error));
}
</script>

</body>
</html>
