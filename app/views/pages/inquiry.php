<?php
$page_title = $page_title ?? __('inquiry_title');
$page_description = $page_description ?? 'Contact us for property inquiries and support';
$current_page = 'inquiry';
?>
<div class="container">
    <div class="row">
        <div class="col-12">
            <h1 class="h3 mb-4"><?= __('inquiry_heading') ?></h1>
            <div class="card aps-cp-card">
                <div class="card-body aps-cp-card-body">
                    <form method="POST" action="<?= BASE_URL ?>/inquiry">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label"><?= __('contact_form_name') ?></label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label"><?= __('contact_form_email') ?></label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="phone" class="form-label"><?= __('contact_form_phone') ?></label>
                                    <input type="tel" class="form-control" id="phone" name="phone" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="subject" class="form-label"><?= __('contact_form_subject') ?></label>
                                    <input type="text" class="form-control" id="subject" name="subject" required>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="message" class="form-label"><?= __('contact_form_message') ?></label>
                            <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                        </div>
                        <div class="d-flex justify-content-between">
                            <a href="/" class="btn btn-secondary"><?= __('contact_form_cancel') ?></a>
                            <button type="submit" class="btn btn-primary"><?= __('inquiry_submit_btn') ?></button>
                        </div>
                    </form>

                    <div class="form-check mb-3 mt-3">
                        <input class="form-check-input" type="checkbox" name="terms_consent" id="terms_consent" required>
                        <label class="form-check-label small text-muted" for="terms_consent">
                            I have read, understood, and unconditionally agree to the
                            <a href="/cancellation-policy" target="_blank" class="text-primary text-decoration-underline">Cancellation Policy</a>,
                            <a href="/refund-policy" target="_blank" class="text-primary text-decoration-underline">Refund Policy</a>, and
                            <a href="/terms-conditions" target="_blank" class="text-primary text-decoration-underline">Master Agreement Terms</a>.
                        </label>
                        <div class="invalid-feedback">You must accept the legal terms and cancellation policy to proceed.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>