<?php
// About Page - APS Dream Home
// Dynamic content from DB ($siteContent) with __() lang fallback

$sc = $siteContent ?? [];

// Helper: get from DB first, then lang fallback
function aboutContent($sc, $key, $fallbackKey) {
    if (!empty($sc[$key])) return $sc[$key];
    return __($fallbackKey);
}
?>

<section class="py-5 bg-primary text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-12">
                <?php if (!empty($pageContent)): ?>
                    <?php echo $pageContent; ?>
                <?php else: ?>
                    <h1 class="display-4 fw-bold mb-4"><?= __('about_hero_title') ?></h1>
                    <p class="lead mb-4"><?= __('about_hero_lead') ?></p>
                    <p><?= __('about_hero_desc') ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <h2 class="mb-4"><?= __('about_leadership_title') ?></h2>
                <div class="row">
                    <?php for ($i = 1; $i <= 3; $i++): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <?php $photo = aboutContent($sc, "leader_{$i}_photo", "about_leader_{$i}_name"); ?>
                            <img loading="lazy"
                                 src="<?= BASE_URL ?>/<?= htmlspecialchars(aboutContent($sc, "leader_{$i}_photo", "about_leader_{$i}_name")) ?>"
                                 class="card-img-top img-fluid"
                                 alt="<?= htmlspecialchars(aboutContent($sc, "leader_{$i}_name", "about_leader_{$i}_name")) ?>"
                                 style="height:250px; object-fit:cover;">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars(aboutContent($sc, "leader_{$i}_name", "about_leader_{$i}_name")) ?></h5>
                                <p class="text-muted"><?= htmlspecialchars(aboutContent($sc, "leader_{$i}_role", "about_leader_{$i}_role")) ?></p>
                                <p class="small"><?= htmlspecialchars(aboutContent($sc, "leader_{$i}_exp", "about_leader_{$i}_exp")) ?></p>
                                <p><?= htmlspecialchars(aboutContent($sc, "leader_{$i}_bio", "about_leader_{$i}_bio")) ?></p>
                            </div>
                        </div>
                    </div>
                    <?php endfor; ?>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card bg-light">
                    <div class="card-body">
                        <h3 class="card-title"><?= __('about_stats_title') ?></h3>
                        <div class="row text-center">
                            <div class="col-6 mb-3">
                                <h2 class="text-primary"><?= htmlspecialchars(aboutContent($sc, 'stat_properties', 'about_stat_properties')) ?></h2>
                                <p><?= __('about_stat_properties') ?></p>
                            </div>
                            <div class="col-6 mb-3">
                                <h2 class="text-success"><?= htmlspecialchars(aboutContent($sc, 'stat_families', 'about_stat_families')) ?></h2>
                                <p><?= __('about_stat_families') ?></p>
                            </div>
                            <div class="col-6 mb-3">
                                <h2 class="text-info"><?= htmlspecialchars(aboutContent($sc, 'stat_projects', 'about_stat_projects')) ?></h2>
                                <p><?= __('about_stat_projects') ?></p>
                            </div>
                            <div class="col-6 mb-3">
                                <h2 class="text-warning"><?= htmlspecialchars(aboutContent($sc, 'stat_years', 'about_stat_years')) ?></h2>
                                <p><?= __('about_stat_years') ?></p>
                            </div>
                        </div>

                        <hr>

                        <div class="text-center">
                            <h5><?= __('about_reg_label') ?> <?= htmlspecialchars(aboutContent($sc, 'reg_number', 'about_reg_number')) ?></h5>
                            <p class="small text-muted"><?= __('about_reg_subtitle') ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
