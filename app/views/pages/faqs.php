<?php if (!isset($sc)) { $sc = function($k, $d='') { return $GLOBALS['_site_settings_cache'][$k] ?? $d; }; }$phoneRaw = preg_replace('/[^0-9]/', '', $sc('contact_whatsapp', '919277121112')); $phoneDisplay = $sc('contact_phone', '<?= $phoneDisplay ?>'); ?>
<?php
if (!function_exists('__')) {
    require_once __DIR__ . '/../../Helpers/TranslationHelper.php';
}
?>

<!-- Page Header -->
<section class="page-hero">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8 mx-auto text-center">
                <h1 class="display-4 fw-bold mb-3"><?= __('faqs_hero_title') ?></h1>
                <p class="lead mb-4"><?= __('faqs_hero_lead') ?></p>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-center">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/" class="text-white"><?= __('home') ?></a></li>
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/about" class="text-white"><?= __('about_us') ?></a></li>
                        <li class="breadcrumb-item active text-white" aria-current="page"><?= __('faqs') ?></li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<?php if (!empty($pageContent)): ?>
<section class="py-4 bg-white">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="cms-content"><?php echo $pageContent; ?></div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- FAQ Section -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h2 class="section-title"><?= __('faqs_section_title') ?></h2>
                <p class="lead text-muted"><?= __('faqs_section_lead') ?></p>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="accordion" id="faqAccordion">

                    <!-- General Questions -->
                    <div class="mb-4">
                        <h3 class="h5 mb-3 text-primary">
                            <i class="fas fa-question-circle me-2"></i><?= __('faqs_cat_general') ?>
                        </h3>

                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading1">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse1" aria-expanded="true" aria-controls="collapse1">
                                    <?= __('faqs_q1') ?>
                                </button>
                            </h2>
                            <div id="collapse1" class="accordion-collapse collapse show" aria-labelledby="heading1" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    <?= __('faqs_a1') ?>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading2">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse2" aria-expanded="false" aria-controls="collapse2">
                                    <?= __('faqs_q2') ?>
                                </button>
                            </h2>
                            <div id="collapse2" class="accordion-collapse collapse" aria-labelledby="heading2" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    <?= __('faqs_a2') ?>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading3">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse3" aria-expanded="false" aria-controls="collapse3">
                                    <?= __('faqs_q3') ?>
                                </button>
                            </h2>
                            <div id="collapse3" class="accordion-collapse collapse" aria-labelledby="heading3" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    <?= __('faqs_a3') ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Property Related -->
                    <div class="mb-4">
                        <h3 class="h5 mb-3 text-primary">
                            <i class="fas fa-home me-2"></i><?= __('faqs_cat_property') ?>
                        </h3>

                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading4">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse4" aria-expanded="false" aria-controls="collapse4">
                                    <?= __('faqs_q4') ?>
                                </button>
                            </h2>
                            <div id="collapse4" class="accordion-collapse collapse" aria-labelledby="heading4" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    <?= __('faqs_a4') ?>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading5">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse5" aria-expanded="false" aria-controls="collapse5">
                                    <?= __('faqs_q5') ?>
                                </button>
                            </h2>
                            <div id="collapse5" class="accordion-collapse collapse" aria-labelledby="heading5" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    <?= __('faqs_a5') ?>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading6">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse6" aria-expanded="false" aria-controls="collapse6">
                                    <?= __('faqs_q6') ?>
                                </button>
                            </h2>
                            <div id="collapse6" class="accordion-collapse collapse" aria-labelledby="heading6" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    <?= __('faqs_a6') ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pricing & Payment -->
                    <div class="mb-4">
                        <h3 class="h5 mb-3 text-primary">
                            <i class="fas fa-money-bill-wave me-2"></i><?= __('faqs_cat_pricing') ?>
                        </h3>

                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading7">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse7" aria-expanded="false" aria-controls="collapse7">
                                    <?= __('faqs_q7') ?>
                                </button>
                            </h2>
                            <div id="collapse7" class="accordion-collapse collapse" aria-labelledby="heading7" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    <?= __('faqs_a7') ?>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading8">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse8" aria-expanded="false" aria-controls="collapse8">
                                    <?= __('faqs_q8') ?>
                                </button>
                            </h2>
                            <div id="collapse8" class="accordion-collapse collapse" aria-labelledby="heading8" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    <?= __('faqs_a8') ?>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading9">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse9" aria-expanded="false" aria-controls="collapse9">
                                    <?= __('faqs_q9') ?>
                                </button>
                            </h2>
                            <div id="collapse9" class="accordion-collapse collapse" aria-labelledby="heading9" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    <?= __('faqs_a9') ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Services -->
                    <div class="mb-4">
                        <h3 class="h5 mb-3 text-primary">
                            <i class="fas fa-cogs me-2"></i><?= __('faqs_cat_services') ?>
                        </h3>

                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading10">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse10" aria-expanded="false" aria-controls="collapse10">
                                    <?= __('faqs_q10') ?>
                                </button>
                            </h2>
                            <div id="collapse10" class="accordion-collapse collapse" aria-labelledby="heading10" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    <?= __('faqs_a10') ?>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading11">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse11" aria-expanded="false" aria-controls="collapse11">
                                    <?= __('faqs_q11') ?>
                                </button>
                            </h2>
                            <div id="collapse11" class="accordion-collapse collapse" aria-labelledby="heading11" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    <?= __('faqs_a11') ?>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading12">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse12" aria-expanded="false" aria-controls="collapse12">
                                    <?= __('faqs_q12') ?>
                                </button>
                            </h2>
                            <div id="collapse12" class="accordion-collapse collapse" aria-labelledby="heading12" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    <?= __('faqs_a12') ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- After Sales -->
                    <div class="mb-4">
                        <h3 class="h5 mb-3 text-primary">
                            <i class="fas fa-headset me-2"></i><?= __('faqs_cat_aftersales') ?>
                        </h3>

                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading13">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse13" aria-expanded="false" aria-controls="collapse13">
                                    <?= __('faqs_q13') ?>
                                </button>
                            </h2>
                            <div id="collapse13" class="accordion-collapse collapse" aria-labelledby="heading13" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    <?= __('faqs_a13') ?>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading14">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse14" aria-expanded="false" aria-controls="collapse14">
                                    <?= __('faqs_q14') ?>
                                </button>
                            </h2>
                            <div id="collapse14" class="accordion-collapse collapse" aria-labelledby="heading14" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    <?= __('faqs_a14') ?>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading15">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse15" aria-expanded="false" aria-controls="collapse15">
                                    <?= __('faqs_q15') ?>
                                </button>
                            </h2>
                            <div id="collapse15" class="accordion-collapse collapse" aria-labelledby="heading15" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    <?= __('faqs_a15') ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contact CTA -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <h3 class="mb-3"><?= __('faqs_cta_title') ?></h3>
                <p class="lead text-muted mb-4"><?= __('faqs_cta_desc') ?></p>
                <div class="d-flex justify-content-center gap-3 flex-wrap">
                    <a href="<?= BASE_URL ?>/contact" class="btn btn-primary btn-lg">
                        <i class="fas fa-phone me-2"></i><?= __('contact_us') ?>
                    </a>
                    <a href="tel:<?= $phoneRaw ?>" class="btn btn-outline-primary btn-lg">
                        <i class="fas fa-phone-alt me-2"></i><?= __('faqs_cta_call') ?>
                    </a>
                    <a href="mailto:<?= $sc('contact_email', 'info@apsdreamhome.com') ?>" class="btn btn-outline-secondary btn-lg">
                        <i class="fas fa-envelope me-2"></i><?= __('faqs_cta_email') ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Comprehensive User Guide CTA -->
<section class="py-5 bg-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8 mb-3 mb-lg-0">
                <h3 class="mb-2 text-white">
                    <i class="fas fa-book-open me-2"></i><?= __('faqs_userguide_title') ?>
                </h3>
                <p class="lead mb-0 text-white-50">
                    <?= __('faqs_userguide_desc') ?>
                </p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <a href="<?php echo BASE_URL; ?>/tools-hub"
                   class="btn btn-light btn-lg text-primary fw-bold">
                    <i class="fas fa-tools me-2"></i><?= __('faqs_userguide_button') ?>
                </a>
            </div>
        </div>
    </div>
</section>
