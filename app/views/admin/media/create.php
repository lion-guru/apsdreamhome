<?php $pageTitle = 'Upload Media'; ?>
<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title"><i class="fas fa-upload me-2"></i>Upload Media</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/media">Media</a></li>
                    <li class="breadcrumb-item active">Upload</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <form method="POST" action="<?= BASE_URL ?>/admin/media/store" enctype="multipart/form-data">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                <div class="row g-3">
                    <div class="col-12"><label class="form-label">File <span class="text-danger">*</span></label><input type="file" name="file" class="form-control" required></div>
                    <div class="col-md-6"><label class="form-label">Title</label><input type="text" name="title" class="form-control" placeholder="Optional display name"></div>
                    <div class="col-md-3"><label class="form-label">Type</label><select name="media_type" class="form-select"><option value="image">Image</option><option value="document">Document</option><option value="video">Video</option></select></div>
                    <div class="col-md-3"><label class="form-label">Folder</label><select name="folder" class="form-select"><option value="general">General</option><option value="properties">Properties</option><option value="projects">Projects</option><option value="documents">Documents</option></select></div>
                    <div class="col-12"><label class="form-label">Alt Text / Description</label><textarea name="description" class="form-control" rows="2"></textarea></div>
                    <div class="col-12"><button type="submit" class="btn btn-primary"><i class="fas fa-upload me-1"></i>Upload</button> <a href="<?= BASE_URL ?>/admin/media" class="btn btn-secondary">Cancel</a></div>
                </div>
            </form>
        </div>
    </div>
</div>
