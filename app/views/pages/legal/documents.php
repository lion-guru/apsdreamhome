<?php
require_once __DIR__ . '/../../../Helpers/TranslationHelper.php';

$page_title = __('legal_documents_title', [], 'Legal Documents | APS Dream Homes');

$documents = [
    ['title' => __('doc_registration', [], 'Registration Certificate'), 'description' => __('doc_registration_desc', [], 'Official company registration certificate.')],
    ['title' => __('doc_iso', [], 'ISO Certification'), 'description' => __('doc_iso_desc', [], 'Quality management system certification.')],
    ['title' => __('doc_rera', [], 'RERA Approval'), 'description' => __('doc_rera_desc', [], 'Real Estate Regulatory Authority approval documents.')],
    ['title' => __('doc_pan', [], 'PAN Card'), 'description' => __('doc_pan_desc', [], 'Company Permanent Account Number card.')],
    ['title' => __('doc_gst', [], 'GST Certificate'), 'description' => __('doc_gst_desc', [], 'Goods and Services Tax registration certificate.')],
    ['title' => __('doc_trade', [], 'Trade License'), 'description' => __('doc_trade_desc', [], 'Municipal trade license for real estate business.')],
];
?>

<section class="py-5 bg-primary text-white" class="style-68644">
    <div class="container text-center">
        <h1 class="display-4 fw-bold mb-3"><?php echo __('legal_documents_heading', [], 'Legal Documents'); ?></h1>
        <p class="lead"><?php echo __('legal_documents_subtitle', [], 'Transparency and Trust in Every Step'); ?></p>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="row justify-content-center mb-5">
            <div class="col-lg-8 text-center">
                <h2 class="fw-bold mb-3"><?php echo __('our_credentials', [], 'Our Credentials'); ?></h2>
                <p class="text-muted"><?php echo __('credentials_desc', [], 'At APS Dream Homes, we maintain complete transparency. Here are our official legal documents and certifications.'); ?></p>
            </div>
        </div>
        <div class="row">
            <?php foreach ($documents as $doc): ?>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body text-center p-4">
                        <div class="mb-3">
                            <i class="fas fa-file-alt fa-3x text-primary"></i>
                        </div>
                        <h5 class="fw-bold"><?= htmlspecialchars($doc['title']) ?></h5>
                        <p class="text-muted small"><?= htmlspecialchars($doc['description']) ?></p>
                        <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i><?php echo __('verified', [], 'Verified'); ?></span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="mt-4 p-4 bg-light rounded border-start border-primary border-4">
            <i class="fas fa-info-circle text-primary me-2"></i>
            <strong><?php echo __('important', [], 'Important:'); ?></strong> <?php echo __('legal_verify_contact', [], 'For legal verification, please contact our corporate office.'); ?>
        </div>
    </div>
</section>
