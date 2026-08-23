<?php
$page_title = $page_title ?? __('alert_success_page_title', [], 'Subscription Confirmed');
$page_heading = $page_heading ?? __('alert_success_page_heading', [], 'Subscription Confirmed');
$content = $content ?? '';
?>
<section class="py-5 style-64693">
    <div class="container text-center py-5">
        <div class="display-1 mb-3"><i class="fas fa-check-circle"></i></div>
        <h1 class="display-5 fw-bold mb-3"><?= __('alert_success_heading', [], "You're All Set!") ?></h1>
        <p class="lead mb-4"><?= __('alert_success_subtitle', [], 'Your property alert has been created successfully') ?></p>
        <p class="mb-4 opacity-75"><?= __('alert_success_notifying', [], "We'll send notifications to") ?> <strong><?= htmlspecialchars($email ?? '') ?></strong></p>
    </div>
</section>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-5 text-center">
                    <h3 class="mb-3"><?= __('alert_success_what_next', [], 'What Happens Next?') ?></h3>
                    <p class="text-muted mb-4"><?= __('alert_success_sub_id', [], 'Your subscription ID is') ?> <strong>#<?= $subscription_id ?? 'N/A' ?></strong></p>
                    <div class="row g-4">
                        <div class="col-md-4">
                            <div class="text-primary mb-2"><i class="fas fa-search fa-2x"></i></div>
                            <h6>1. <?= __('alert_success_step1', [], 'We Search') ?></h6>
                            <p class="text-muted small"><?= __('alert_success_step1_desc', [], 'We monitor new property listings matching your criteria') ?></p>
                        </div>
                        <div class="col-md-4">
                            <div class="text-warning mb-2"><i class="fas fa-bell fa-2x"></i></div>
                            <h6>2. <?= __('alert_success_step2', [], 'We Notify') ?></h6>
                            <p class="text-muted small"><?= __('alert_success_step2_desc', [], "You'll receive alerts via your selected channels") ?></p>
                        </div>
                        <div class="col-md-4">
                            <div class="text-success mb-2"><i class="fas fa-home fa-2x"></i></div>
                            <h6>3. <?= __('alert_success_step3', [], 'You Choose') ?></h6>
                            <p class="text-muted small"><?= __('alert_success_step3_desc', [], 'View, save, and contact sellers directly') ?></p>
                        </div>
                    </div>
                    <div class="d-flex gap-3 justify-content-center mt-4">
                        <a href="<?= BASE_URL ?>/properties" class="btn btn-primary">
                            <i class="fas fa-search me-1"></i> <?= __('alert_success_browse', [], 'Browse Properties') ?>
                        </a>
                        <a href="<?= BASE_URL ?>/property-alerts/subscribe" class="btn btn-outline-primary">
                            <i class="fas fa-plus me-1"></i> <?= __('alert_success_create_another', [], 'Create Another Alert') ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>