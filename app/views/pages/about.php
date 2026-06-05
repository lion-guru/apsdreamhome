<?php
// About Page - APS Dream Home
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
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <img loading="lazy" src="assets/images/property-placeholder.jpg" class="card-img-top img-fluid" alt="Amit Kumar Singh">
                            <div class="card-body">
                                <h5 class="card-title"><?= __('about_leader_1_name') ?></h5>
                                <p class="text-muted"><?= __('about_leader_1_role') ?></p>
                                <p class="small"><?= __('about_leader_1_exp') ?></p>
                                <p><?= __('about_leader_1_bio') ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <img loading="lazy" src="assets/images/property-placeholder.jpg" class="card-img-top img-fluid" alt="Priya Singh">
                            <div class="card-body">
                                <h5 class="card-title"><?= __('about_leader_2_name') ?></h5>
                                <p class="text-muted"><?= __('about_leader_2_role') ?></p>
                                <p class="small"><?= __('about_leader_2_exp') ?></p>
                                <p><?= __('about_leader_2_bio') ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <img loading="lazy" src="assets/images/property-placeholder.jpg" class="card-img-top img-fluid" alt="Rahul Verma">
                            <div class="card-body">
                                <h5 class="card-title"><?= __('about_leader_3_name') ?></h5>
                                <p class="text-muted"><?= __('about_leader_3_role') ?></p>
                                <p class="small"><?= __('about_leader_3_exp') ?></p>
                                <p><?= __('about_leader_3_bio') ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card bg-light">
                    <div class="card-body">
                        <h3 class="card-title"><?= __('about_stats_title') ?></h3>
                        <div class="row text-center">
                            <div class="col-6 mb-3">
                                <h2 class="text-primary">500+</h2>
                                <p><?= __('about_stat_properties') ?></p>
                            </div>
                            <div class="col-6 mb-3">
                                <h2 class="text-success">2000+</h2>
                                <p><?= __('about_stat_families') ?></p>
                            </div>
                            <div class="col-6 mb-3">
                                <h2 class="text-info">50+</h2>
                                <p><?= __('about_stat_projects') ?></p>
                            </div>
                            <div class="col-6 mb-3">
                                <h2 class="text-warning">8+</h2>
                                <p><?= __('about_stat_years') ?></p>
                            </div>
                        </div>

                        <hr>

                        <div class="text-center">
                            <h5><?= __('about_reg_label') ?> U70109UP2022PTC163047</h5>
                            <p class="small text-muted"><?= __('about_reg_subtitle') ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
