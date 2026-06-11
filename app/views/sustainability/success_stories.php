<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= $base ?? BASE_URL ?>"><i class="fas fa-home"></i></a></li>
                    <li class="breadcrumb-item"><a href="<?= $base ?? BASE_URL ?>sustainability">Sustainability</a></li>
                    <li class="breadcrumb-item active">Success Stories</li>
                </ol>
            </nav>
            <h1 class="display-5 fw-bold"><i class="fas fa-star me-3 text-success"></i><?= ($page_title ?? 'Success Stories') ?></h1>
        </div>
    </div>

    <?php $st = $stories ?? []; ?>

    <div class="row g-4">
        <?php foreach ($st as $key => $story): ?>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body aps-cp-card-body">
                    <i class="fas fa-<?= $key === 'carbon_neutral_achievement' ? 'leaf' : ($key === 'green_building_pioneer' ? 'trophy' : 'users') ?> fa-3x text-success mb-3"></i>
                    <h5><?= ($story['title'] ?? ucfirst(str_replace('_', ' ', $key))) ?></h5>
                    <p class="small text-muted"><?= ($story['story'] ?? '') ?></p>
                    <hr>
                    <small class="fw-semibold d-block mb-2">Key Achievements</small>
                    <ul class="list-unstyled"><?php foreach (($story['key_achievements'] ?? []) as $ach): ?><li class="small"><i class="fas fa-check-circle text-success me-1"></i><?= htmlspecialchars($ach, ENT_QUOTES, 'UTF-8') ?></li><?php endforeach; ?></ul>
                    <hr>
                    <small class="fw-semibold d-block">Impact: <?= ($story['impact'] ?? '') ?></small>
                    <small class="text-muted d-block mt-2">Lesson: <?= ($story['lessons_learned'] ?? '') ?></small>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php if (empty($st)): ?><div class="col-12"><div class="alert alert-info">No success stories available.</div></div><?php endif; ?>
    </div>
</div>
