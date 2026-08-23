<?php
if (!defined('BASE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $basePath = preg_replace('#/public$#', '', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
    define('BASE_URL', $protocol . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . $basePath);
}
require_once __DIR__ . '/../../Helpers/TranslationHelper.php';
$d = $state['form_data'] ?? [];
$uploaded = $d['images'] ?? [];
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars(__current_lang()) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Step 6: Images | List Property | APS Dream Home</title>
    <link href="<?= BASE_URL ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/fonts/fontawesome/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/image-uploader.css">
    <style>
        body { background: #f4f6f9; min-height: 100vh; font-family: 'Inter', sans-serif; padding: 20px 0; }
        .wizard-card { border: none; border-radius: 16px; box-shadow: 0 4px 24px rgba(0,0,0,.06); }
    </style>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/uiux-fixes.css?v=1">
</head>
<body>
<div class="container">
    <h2 class="mb-4"><i class="fas fa-plus-circle text-primary me-2"></i>List Your Property</h2>
    <div class="row g-4">
        <div class="col-lg-3"><?php include __DIR__ . '/progress.php'; ?></div>
        <div class="col-lg-9">
            <div class="wizard-card card">
                <div class="card-body p-4">
                    <h4 class="mb-1">Step 6: Images</h4>
                    <p class="text-muted">Upload up to 10 photos. Drag-drop, reorder, or delete.</p>

                    <form method="POST" action="<?= BASE_URL ?>/list-property/step6" id="images-form">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                        <div id="uploaded-images-container" class="d-none">
                            <?php foreach ($uploaded as $i => $url): ?>
                                <input type="hidden" name="uploaded_images[]" value="<?= htmlspecialchars($url ?? '') ?>" data-index="<?= $i ?>">
                            <?php endforeach; ?>
                        </div>

                        <div id="image-uploader"
                             data-upload-url="<?= BASE_URL ?>/list-property/upload-image"
                             data-csrf="<?= htmlspecialchars($csrf_token ?? '') ?>"
                             data-max="10"
                             data-existing='<?= json_encode($uploaded) ?>'>
                            <div class="upload-dropzone border-2 border-dashed rounded p-5 text-center bg-light">
                                <i class="fas fa-cloud-upload-alt fa-3x text-primary mb-2"></i>
                                <p class="mb-1"><strong>Drag &amp; drop images here</strong></p>
                                <p class="text-muted small mb-2">or click to browse (JPG/PNG/WebP, max 5MB each, up to 10 images)</p>
                                <input type="file" id="file-input" accept="image/jpeg,image/png,image/webp" multiple class="d-none">
                                <button type="button" class="btn btn-primary" id="browse-btn"><i class="fas fa-folder-open me-1"></i> Browse</button>
                            </div>
                            <div id="upload-progress" class="mt-3 style-24280">
                                <div class="progress"><div class="progress-bar progress-bar-striped progress-bar-animated style-55795"></div></div>
                            </div>
                            <div id="thumbnails" class="d-flex flex-wrap gap-2 mt-3"></div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="<?= BASE_URL ?>/list-property/step5" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Back</a>
                            <div>
                                <button type="button" class="btn btn-link text-muted" id="save-draft-btn">Save as Draft</button>
                                <button type="submit" class="btn btn-primary px-4">Next <i class="fas fa-arrow-right ms-1"></i></button>
                            </div>
                        </div>
                    </form>
                    <form id="draft-form" method="POST" action="<?= BASE_URL ?>/list-property/save-draft" class="d-none">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="<?= BASE_URL ?>/assets/js/image-uploader.js"></script>
<script>
document.getElementById('save-draft-btn').addEventListener('click', function() {
    document.getElementById('draft-form').submit();
});
</script>
</body>
</html>
