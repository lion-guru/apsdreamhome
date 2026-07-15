<?php
/**
 * Language Selector View
 * Data: $page_title, $supported_languages, $current_language
 */
$page_title = $page_title ?? 'Select Language';
$supported_languages = $supported_languages ?? ['en' => ['name' => 'English', 'native_name' => 'English', 'flag' => '🇺🇸']];
$current_language = $current_language ?? 'en';
?>
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="page-title"><?= htmlspecialchars($page_title) ?></h1>
            <p class="text-muted">Choose your preferred language for the APS Dream Home platform.</p>
        </div>
    </div>

    <div class="row g-3">
        <?php foreach ($supported_languages as $code => $info): 
            $isCurrent = $code === $current_language;
        ?>
        <div class="col-md-4 col-sm-6">
            <div class="card aps-cp-card h-100 <?= $isCurrent ? 'border-primary' : '' ?>">
                <div class="card-body text-center p-4">
                    <span class="badge bg-primary rounded-pill p-3 fs-1 mb-3"><?= $info['flag'] ?? '🌐' ?></span>
                    <h5 class="mb-1"><?= htmlspecialchars($info['name'] ?? $code) ?></h5>
                    <small class="text-muted d-block mb-3"><?= htmlspecialchars($info['native_name'] ?? $code) ?></small>
                    
                    <form method="POST" action="<?= BASE_URL ?>/language/set">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                        <input type="hidden" name="locale" value="<?= $code ?>">
                        <button type="submit" class="btn <?= $isCurrent ? 'btn-success disabled' : 'btn-primary' ?>">
                            <?= $isCurrent ? '<i class="fas fa-check me-1"></i> Active' : '<i class="fas fa-globe me-1"></i> Select' ?>
                        </button>
                    </form>
                    
                    <?php if ($isCurrent): ?>
                    <div class="text-success mt-2">
                        <i class="fas fa-check-circle me-1"></i>Currently Active
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>