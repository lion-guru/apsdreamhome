<?php
/**
 * Language Selector View
 * Allows users to choose between English and Hindi
 */
$page_title = $page_title ?? 'Select Language - APS Dream Home';
$supported_languages = $supported_languages ?? [
    'en' => ['name' => 'English', 'native' => 'English', 'flag' => '🇬🇧'],
    'hi' => ['name' => 'Hindi', 'native' => 'हिन्दी', 'flag' => '🇮🇳']
];
$current_language = $current_language ?? ($_SESSION['lang'] ?? 'en');
$base = defined('BASE_URL') ? BASE_URL : '';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm border-0 text-center">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="mb-0 text-primary"><i class="fas fa-globe me-2"></i> Choose Language / भाषा चुनें</h5>
                </div>
                <div class="card-body p-4">
                    <p class="text-muted mb-4">Select your preferred language for the APS Dream Home portal.</p>
                    <div class="d-grid gap-3">
                        <?php foreach ($supported_languages as $code => $info): ?>
                            <a href="<?= $base ?>/language/switch/<?= $code ?>" 
                               class="btn btn-lg py-3 d-flex align-items-center justify-content-between <?= $code === $current_language ? 'btn-primary' : 'btn-outline-secondary' ?>">
                                <span class="fs-4"><?= $info['flag'] ?? '🌐' ?></span>
                                <span class="fw-bold fs-5"><?= htmlspecialchars($info['native'] ?? $info['name'] ?? $code) ?> (<?= htmlspecialchars($info['name'] ?? $code) ?>)</span>
                                <?php if ($code === $current_language): ?>
                                    <i class="fas fa-check-circle fs-5"></i>
                                <?php else: ?>
                                    <i class="fas fa-chevron-right text-muted"></i>
                                <?php endif; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="card-footer bg-light py-3">
                    <a href="<?= $base ?>/" class="btn btn-link text-decoration-none text-muted btn-sm">
                        <i class="fas fa-arrow-left me-1"></i> Return to Homepage
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
