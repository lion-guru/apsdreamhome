<?php
$page_title = $page_title ?? 'Upload Media';
$categories = $categories ?? [];
$base = defined('BASE_URL') ? BASE_URL : '/' . trim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><i class="fas fa-cloud-upload-alt me-2 text-primary"></i>Upload Media</h2>
            <p class="text-muted mb-0">Upload images, documents, and other media files</p>
        </div>
        <div>
            <a href="<?php echo e($base); ?>/admin/media-library" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Library
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form id="uploadForm" action="<?php echo e($base); ?>/admin/media-library/upload" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="media_file" class="form-label fw-bold">Select File</label>
                            <input type="file" class="form-control" id="media_file" name="media_file" required
                                   accept=".jpg,.jpeg,.png,.gif,.webp,.pdf,.doc,.docx,.xls,.xlsx">
                            <div class="form-text">Max size: 10MB. Allowed: jpg, png, gif, webp, pdf, doc, xlsx</div>
                        </div>

                        <div class="mb-3">
                            <label for="title" class="form-label fw-bold">Title</label>
                            <input type="text" class="form-control" id="title" name="title" placeholder="File title">
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label fw-bold">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3" placeholder="Brief description"></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="category" class="form-label fw-bold">Category</label>
                                <select class="form-select" id="category" name="category">
                                    <option value="general">General</option>
                                    <option value="properties">Properties</option>
                                    <option value="colonies">Colonies</option>
                                    <option value="team">Team</option>
                                    <option value="documents">Documents</option>
                                    <option value="banners">Banners</option>
                                    <option value="blog">Blog</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="tags" class="form-label fw-bold">Tags</label>
                                <input type="text" class="form-control" id="tags" name="tags" placeholder="Comma-separated tags">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-upload me-2"></i>Upload File
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Upload Guidelines</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Images: JPG, PNG, GIF, WebP</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Documents: PDF, DOC, DOCX, XLS, XLSX</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Max file size: 10MB</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Thumbnails auto-generated for images</li>
                        <li><i class="fas fa-check text-success me-2"></i>Files available via direct URL</li>
                    </ul>
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-3">
                <div class="card-body text-center" id="preview" style="display:none;">
                    <h6 class="mb-2">Preview</h6>
                    <div id="previewContent"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('media_file').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (!file) return;
    
    const preview = document.getElementById('preview');
    const content = document.getElementById('previewContent');
    preview.style.display = 'block';
    
    if (file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = function(ev) {
            content.innerHTML = '<img src="' + ev.target.result + '" class="img-fluid rounded" style="max-height:200px;">';
        };
        reader.readAsDataURL(file);
    } else {
        content.innerHTML = '<i class="fas fa-file fa-3x text-muted mb-2"></i><p class="small mb-0">' + file.name + '</p><p class="text-muted small">' + (file.size / 1024).toFixed(1) + ' KB</p>';
    }
    
    document.getElementById('title').value = file.name.replace(/\.[^.]+$/, '');
});
</script>
