<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?= $mlSupport->translate('Add News') ?></h1>
        <a href="/admin/news" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> <?= $mlSupport->translate('Back to List') ?>
        </a>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $_SESSION['error'];
            unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="/admin/news/store" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= $this->getCsrfToken() ?>">

                <div class="mb-3">
                    <label for="title" class="form-label"><?= $mlSupport->translate('Title') ?> <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="title" name="title" required>
                </div>

                <div class="mb-3">
                    <label for="date" class="form-label"><?= $mlSupport->translate('Date') ?> <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="date" name="date" required value="<?= date('Y-m-d') ?>">
                </div>

                <div class="mb-3">
                    <label for="image" class="form-label"><?= $mlSupport->translate('Image') ?></label>
                    <input type="file" class="form-control" id="image" name="image" accept="image/*">
                </div>

                <div class="mb-3">
                    <label for="summary" class="form-label"><?= $mlSupport->translate('Summary') ?></label>
                    <textarea class="form-control" id="summary" name="summary" rows="3"></textarea>
                </div>

                <div class="mb-3">
                    <label for="content" class="form-label"><?= $mlSupport->translate('Content') ?></label>
                    <textarea class="form-control" id="content" name="content" rows="10"></textarea>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> <?= $mlSupport->translate('Save News') ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add CKEditor or similar if needed -->
<script src="https://cdn.ckeditor.com/4.16.2/standard/ckeditor.js"></script>
<script>
    CKEDITOR.replace('content');
</script>