<div class="container-fluid py-4">
    <h4 class="mb-4"><i class="fas fa-cloud-upload-alt me-2 text-primary"></i>Upload Media</h4>
    <form method="POST" action="<?= BASE_URL ?>/admin/media-library/upload" enctype="multipart/form-data" class="card shadow-sm p-4">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
        <div class="mb-3">
            <label class="form-label">File <span class="text-danger">*</span></label>
            <input type="file" name="file" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Title</label>
            <input type="text" name="title" class="form-control">
        </div>
        <div class="mb-3">
            <label class="form-label">Folder</label>
            <select name="folder" class="form-select">
                <option value="general">General</option>
                <option value="properties">Properties</option>
                <option value="projects">Projects</option>
                <option value="blog">Blog</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary"><i class="fas fa-upload me-1"></i>Upload</button>
    </form>
</div>