<?php $pageTitle = __('auth_logged_out'); ?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body py-5">
                    <div class="mb-4"><i class="fas fa-check-circle text-success" style="font-size:5rem"></i></div>
                    <h4><?= __("auth_logged_out_title") ?></h4>
                    <p class="text-muted mb-1"><?= __("auth_session_ended") ?></p>
                    <p class="text-muted small mb-4"><?= __("auth_thank_you_visit") ?></p>
                    <div class="d-flex justify-content-center gap-3">
                        <a href="<?= BASE_URL ?>login" class="btn btn-primary"><i class="fas fa-sign-in-alt me-1"></i><?= __("auth_login_again") ?></a>
                        <a href="<?= BASE_URL ?>" class="btn btn-outline-secondary"><i class="fas fa-home me-1"></i><?= __("auth_go_home") ?></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>