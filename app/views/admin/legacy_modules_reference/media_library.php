<?php
/**
 * APS Dream Home - Enhanced Admin Panel with Media Library
 * Complete admin interface with media management capabilities
 */

require_once __DIR__ . '/core/init.php';
require_once __DIR__ . '/../../../includes/media_library_manager.php';

if (!isAdmin()) {
    header('Location: login.php');
    exit();
}

// Initialize media manager
$mediaManager = new MediaLibraryManager($GLOBALS['conn'] ?? $GLOBALS['con'] ?? null);

// Handle actions
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken()) {
        die('Invalid CSRF token. Action blocked.');
    }
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'upload_media':
                $result = $mediaManager->handleUpload();
                if ($result['success']) {
                    $message = $result['message'];
                    $messageType = 'success';
                } else {
                    $message = $result['message'];
                    $messageType = 'danger';
                }
                break;

            case 'update_media':
                $id = intval($_POST['media_id'] ?? 0);
                $title = $_POST['title'] ?? '';
                $description = $_POST['description'] ?? '';
                $category = $_POST['category'] ?? '';
                $tags = $_POST['tags'] ?? '';

                $result = $mediaManager->updateMediaFile($id, $title, $description, $category, $tags);
                if ($result['success']) {
                    $message = $result['message'];
                    $messageType = 'success';
                } else {
                    $message = $result['message'];
                    $messageType = 'danger';
                }
                break;

            case 'delete_media':
                $id = intval($_POST['media_id'] ?? 0);
                $result = $mediaManager->deleteMediaFile($id);
                if ($result['success']) {
                    $message = $result['message'];
                    $messageType = 'success';
                } else {
                    $message = $result['message'];
                    $messageType = 'danger';
                }
                break;
        }
    }
}

// Get media files and stats
$mediaFiles = $mediaManager->getMediaFiles();
$mediaStats = $mediaManager->getMediaStats();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Media Library - APS Dream Home Admin</title>

    <!-- Bootstrap CSS -->
    <link href="<?= h(BASE_URL) ?>assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= h(BASE_URL) ?>assets/css/font-awesome.min.css" rel="stylesheet">

    <style>
        .admin-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 0;
        }
        .media-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        .media-item {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        .media-item:hover {
            transform: translateY(-5px);
        }
        .media-image {
            width: 100%;
            height: 150px;
            object-fit: cover;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .media-info {
            padding: 15px;
        }
        .media-actions {
            padding: 10px 15px;
            background: #f8f9fa;
            border-top: 1px solid #dee2e6;
        }
        .stats-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            text-align: center;
        }
        .upload-area {
            border: 2px dashed #dee2e6;
            border-radius: 10px;
            padding: 40px;
            text-align: center;
            transition: border-color 0.3s ease;
        }
        .upload-area:hover {
            border-color: #667eea;
        }
        .file-icon {
            font-size: 3rem;
            color: #6c757d;
        }
        .filter-tabs {
            border-bottom: 2px solid #dee2e6;
            margin-bottom: 20px;
        }
        .filter-tabs .nav-link {
            border: none;
            border-bottom: 2px solid transparent;
            color: #6c757d;
        }
        .filter-tabs .nav-link.active {
            border-bottom-color: #667eea;
            color: #667eea;
            background: none;
        }
    </style>
</head>
<body>

<!-- Admin Header -->
<div class="admin-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1 class="mb-0">
                    <i class="fas fa-images me-2"></i>Media Library
                </h1>
                <p class="mb-0 opacity-75">Manage your website media files</p>
            </div>
            <div class="col-md-6 text-end">
                <a href="dashboard.php" class="btn btn-light me-2">
                    <i class="fas fa-dashboard me-1"></i>Dashboard
                </a>
                <a href="dynamic_content_manager.php" class="btn btn-light me-2">
                    <i class="fas fa-cog me-1"></i>Templates
                </a>
                <a href="logout.php" class="btn btn-outline-light">
                    <i class="fas fa-sign-out-alt me-1"></i>Logout
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container mt-4">

    <?php if ($message): ?>
    <div class="alert alert-<?= h($messageType) ?> alert-dismissible fade show" role="alert">
        <?= h($message) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stats-card">
                <i class="fas fa-file fa-2x text-primary mb-2"></i>
                <h3><?= h(number_format($mediaStats['total_files'])) ?></h3>
                <p class="mb-0">Total Files</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <i class="fas fa-hdd fa-2x text-success mb-2"></i>
                <h3><?= h(number_format($mediaStats['total_size'] / 1024 / 1024, 2)) ?> MB</h3>
                <p class="mb-0">Total Size</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <i class="fas fa-clock fa-2x text-info mb-2"></i>
                <h3><?= h($mediaStats['recent_uploads']) ?></h3>
                <p class="mb-0">Recent (7 days)</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <i class="fas fa-tags fa-2x text-warning mb-2"></i>
                <h3><?= (int)count($mediaStats['by_category']) ?></h3>
                <p class="mb-0">Categories</p>
            </div>
        </div>
    </div>

    <!-- Upload Section -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-upload me-2"></i>Upload New File
                    </h5>
                </div>
                <div class="card-body">
                    <form method="post" enctype="multipart/form-data">
                        <?php echo getCsrfField(); ?>
                        <input type="hidden" name="action" value="upload_media">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="media_file" class="form-label">Select File</label>
                                    <input type="file" class="form-control" id="media_file" name="media_file" required>
                                    <small class="text-muted">Allowed: JPG, PNG, GIF, WebP, PDF, DOC, DOCX, XLS, XLSX (Max: 10MB)</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="category" class="form-label">Category</label>
                                    <select class="form-select" id="category" name="category">
                                        <option value="general">General</option>
                                        <option value="property">Property</option>
                                        <option value="team">Team</option>
                                        <option value="project">Project</option>
                                        <option value="blog">Blog</option>
                                        <option value="document">Document</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Title</label>
                                    <input type="text" class="form-control" id="title" name="title" placeholder="Enter file title">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="tags" class="form-label">Tags</label>
                                    <input type="text" class="form-control" id="tags" name="tags" placeholder="Enter tags (comma separated)">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3" placeholder="Enter file description"></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-upload me-2"></i>Upload File
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-filter me-2"></i>Filter & Search
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="search_media" class="form-label">Search</label>
                        <input type="text" class="form-control" id="search_media" placeholder="Search files...">
                    </div>

                    <div class="mb-3">
                            <label class="form-label">Filter by Category</label>
                            <div class="d-flex flex-wrap gap-2">
                                <?php foreach ($mediaStats['by_category'] as $category => $count): ?>
                                <button class="btn btn-outline-primary btn-sm filter-btn" data-category="<?= h($category) ?>">
                                    <?= h(ucfirst($category)) ?> (<?= (int)$count ?>)
                                </button>
                                <?php endforeach; ?>
                            </div>
                        </div>

                    <button class="btn btn-secondary btn-sm" id="clear_filters">
                        <i class="fas fa-times me-1"></i>Clear Filters
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Media Grid -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-images me-2"></i>Media Files
            </h5>
            <span class="badge bg-primary"><?= (int)count($mediaFiles) ?> files</span>
        </div>
        <div class="card-body">
            <div class="media-grid" id="media_grid">
                <?php foreach ($mediaFiles as $file): ?>
                <div class="media-item" data-category="<?= h($file['category']) ?>" data-search="<?= h(strtolower($file['title'] . ' ' . $file['description'] . ' ' . $file['tags'])) ?>">
                    <div class="media-image">
                        <?php if ($mediaManager->isImage($file['mime_type'])): ?>
                            <img src="<?= h($mediaManager->getFileUrl($file['filename'])) ?>" alt="<?= h($file['title']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                        <?php else: ?>
                            <i class="fas fa-file file-icon"></i>
                        <?php endif; ?>
                    </div>
                    <div class="media-info">
                        <h6 class="mb-1"><?= h($file['title'] ?: $file['original_name']) ?></h6>
                        <small class="text-muted d-block mb-1"><?= h(ucfirst($file['category'])) ?></small>
                        <small class="text-muted"><?= h(number_format($file['file_size'] / 1024, 2)) ?> KB</small>
                    </div>
                    <div class="media-actions">
                        <button class="btn btn-sm btn-outline-primary me-1" onclick="editMedia(<?= (int)$file['id'] ?>)">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteMedia(<?= (int)$file['id'] ?>)">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Media File</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editForm">
                    <?php echo getCsrfField(); ?>
                    <input type="hidden" name="action" value="update_media">
                    <input type="hidden" name="media_id" id="edit_media_id">

                    <div class="mb-3">
                        <label for="edit_title" class="form-label">Title</label>
                        <input type="text" class="form-control" id="edit_title" name="title">
                    </div>

                    <div class="mb-3">
                        <label for="edit_category" class="form-label">Category</label>
                        <select class="form-select" id="edit_category" name="category">
                            <option value="general">General</option>
                            <option value="property">Property</option>
                            <option value="team">Team</option>
                            <option value="project">Project</option>
                            <option value="blog">Blog</option>
                            <option value="document">Document</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="edit_tags" class="form-label">Tags</label>
                        <input type="text" class="form-control" id="edit_tags" name="tags">
                    </div>

                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveMedia()">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="<?= h(BASE_URL) ?>assets/js/bootstrap.bundle.min.js"></script>

<script>
// Filter functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search_media');
    const filterButtons = document.querySelectorAll('.filter-btn');
    const clearButton = document.getElementById('clear_filters');
    const mediaGrid = document.getElementById('media_grid');
    const mediaItems = mediaGrid.querySelectorAll('.media-item');

    // Search functionality
    searchInput.addEventListener('input', function() {
        filterMedia();
    });

    // Category filter
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Toggle active state
            filterButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');

            filterMedia();
        });
    });

    // Clear filters
    clearButton.addEventListener('click', function() {
        searchInput.value = '';
        filterButtons.forEach(btn => btn.classList.remove('active'));
        filterMedia();
    });

    function filterMedia() {
        const searchTerm = searchInput.value.toLowerCase();
        const activeCategory = document.querySelector('.filter-btn.active');
        const categoryFilter = activeCategory ? activeCategory.dataset.category : null;

        mediaItems.forEach(item => {
            const searchMatch = searchTerm === '' || item.dataset.search.includes(searchTerm);
            const categoryMatch = !categoryFilter || item.dataset.category === categoryFilter;

            if (searchMatch && categoryMatch) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    }
});

// Edit media
function editMedia(id) {
    fetch('media_library_manager.php?action=get_file&id=' + id)
        .then(response => response.json())
        .then(data => {
            if (data) {
                document.getElementById('edit_media_id').value = data.id;
                document.getElementById('edit_title').value = data.title || '';
                document.getElementById('edit_category').value = data.category || 'general';
                document.getElementById('edit_tags').value = data.tags || '';
                document.getElementById('edit_description').value = data.description || '';

                const modal = new bootstrap.Modal(document.getElementById('editModal'));
                modal.show();
            }
        });
}

// Save media
function saveMedia() {
    const form = document.getElementById('editForm');
    const formData = new FormData(form);

    fetch('media_library_manager.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message);
        }
    });
}

// Delete media
function deleteMedia(id) {
    if (confirm('Are you sure you want to delete this file?')) {
        const formData = new FormData();
        formData.append('action', 'delete_file');
        formData.append('id', id);
        formData.append('csrf_token', '<?php echo h(getCsrfToken()); ?>');

        fetch('media_library_manager.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message);
            }
        });
    }
}
</script>

</body>
</html>
