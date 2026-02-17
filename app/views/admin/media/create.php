<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Upload Media</h1>
        <a href="/admin/media" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Library
        </a>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $_SESSION['error']; unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="/admin/media/store" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= $this->getCsrfToken() ?>">
                
                <div class="mb-3">
                    <label for="file" class="form-label">Select File <span class="text-danger">*</span></label>
                    <input type="file" class="form-control" id="file" name="file" required>
                    <div class="form-text">Supported formats: JPG, PNG, GIF, WEBP, PDF, DOCX. Max size: 5MB.</div>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload"></i> Upload
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
