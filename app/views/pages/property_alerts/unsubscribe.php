<?php
$page_title = $page_title ?? __('alert_unsub_page_title', [], 'Unsubscribe');
$page_heading = $page_heading ?? __('alert_unsub_page_heading', [], 'Unsubscribe');
$content = $content ?? '';
?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body p-5">
                    <?php if ($success ?? false): ?>
                        <div class="display-1 text-success mb-3"><i class="fas fa-check-circle"></i></div>
                        <h2 class="mb-3"><?= __('alert_unsub_heading', [], 'Unsubscribed') ?></h2>
                        <p class="text-muted mb-4"><?= __('alert_unsub_success', [], "You have been successfully unsubscribed from property alerts. We're sorry to see you go!") ?></p>
                    <?php else: ?>
                        <div class="display-1 text-warning mb-3"><i class="fas fa-exclamation-triangle"></i></div>
                        <h2 class="mb-3"><?= __('alert_unsub_invalid', [], 'Invalid Link') ?></h2>
                        <p class="text-muted mb-4"><?= __('alert_unsub_invalid_desc', [], 'The unsubscribe link is invalid or has expired.') ?></p>
                    <?php endif; ?>
                    <a href="<?= BASE_URL ?>/" class="btn btn-primary">
                        <i class="fas fa-home me-1"></i> <?= __('alert_unsub_home', [], 'Back to Home') ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>