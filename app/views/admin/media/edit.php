<?php $pageTitle = 'Edit Media'; ?>
<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title"><i class="fas fa-edit me-2"></i>Edit Media</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/media">Media</a></li>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/media/show/<?= $media['id'] ?? 0 ?>"><?= $media['title'] ?? 'Media' ?></a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <form method="POST" action="<?= BASE_URL ?>/admin/media/update/<?= $media['id'] ?? 0 ?>">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                <div class="row g-3">
                    <div class="col-md-8"><label class="form-label">Title</label><input type="text" name="title" class="form-control" value="<?= $media['title'] ?? '' ?>"></div>
                    <div class="col-md-4"><label class="form-label">Folder</label><select name="folder" class="form-select"><option value="general" <?= ($media['folder'] ?? '') === 'general' ? 'selected' : '' ?>>General</option><option value="properties" <?= ($media['folder'] ?? '') === 'properties' ? 'selected' : '' ?>>Properties</option><option value="projects" <?= ($media['folder'] ?? '') === 'projects' ? 'selected' : '' ?>>Projects</option><option value="documents" <?= ($media['folder'] ?? '') === 'documents' ? 'selected' : '' ?>>Documents</option></select></div>
                    <div class="col-12"><label class="form-label">Alt Text / Description</label><textarea name="description" class="form-control" rows="2"><?= $media['description'] ?? '' ?></textarea></div>
                    <div class="col-12"><button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Update</button> <a href="<?= BASE_URL ?>/admin/media" class="btn btn-secondary">Cancel</a></div>
                </div>
            </form>
        </div>
    </div>
</div>
