<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Media Library</h1>
        <a href="/admin/media/create" class="btn btn-primary">
            <i class="fas fa-upload"></i> Upload Media
        </a>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $_SESSION['success']; unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $_SESSION['error']; unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <?php if (!empty($media)): ?>
            <?php foreach ($media as $item): ?>
                <div class="col-md-3 mb-4">
                    <div class="card h-100 shadow-sm">
                        <?php 
                        $isImage = in_array(pathinfo($item->filename, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                        ?>
                        <div class="card-img-top d-flex align-items-center justify-content-center bg-light" style="height: 150px; overflow: hidden;">
                            <?php if ($isImage): ?>
                                <img src="/<?= $item->path ?>" class="img-fluid" style="max-height: 100%; width: auto;" alt="<?= $item->original_filename ?>">
                            <?php else: ?>
                                <i class="fas fa-file fa-3x text-secondary"></i>
                            <?php endif; ?>
                        </div>
                        <div class="card-body p-2">
                            <small class="text-muted d-block text-truncate" title="<?= $item->original_filename ?>">
                                <?= $item->original_filename ?>
                            </small>
                            <small class="text-muted d-block">
                                <?= round($item->size / 1024, 2) ?> KB
                            </small>
                        </div>
                        <div class="card-footer bg-white p-2 d-flex justify-content-between">
                            <button class="btn btn-sm btn-outline-primary copy-url" data-url="/<?= $item->path ?>" title="Copy URL">
                                <i class="fas fa-link"></i>
                            </button>
                            <form action="/admin/media/delete/<?= $item->id ?>" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this file?');">
                                <input type="hidden" name="csrf_token" value="<?= $this->getCsrfToken() ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <p class="text-muted">No media files found. Upload some!</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    document.querySelectorAll('.copy-url').forEach(btn => {
        btn.addEventListener('click', function() {
            const url = this.getAttribute('data-url');
            const fullUrl = window.location.origin + url;
            navigator.clipboard.writeText(fullUrl).then(() => {
                alert('URL copied to clipboard: ' + fullUrl);
            });
        });
    });
</script>
