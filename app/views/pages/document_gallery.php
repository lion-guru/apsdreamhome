<?php
$selectedCategory = $selected_category ?? '';
$searchQuery = $search_query ?? '';
$categories = $categories ?? [];
$documents = $documents ?? [];

// Document categories for booking process
$bookingDocCategories = [
    'booking_forms' => ['label' => 'Booking Forms', 'icon' => 'fa-file-contract', 'color' => 'primary'],
    'kyc_docs' => ['label' => 'KYC Documents', 'icon' => 'fa-id-card', 'color' => 'success'],
    'payment_docs' => ['label' => 'Payment Documents', 'icon' => 'fa-receipt', 'color' => 'warning'],
    'legal_docs' => ['label' => 'Legal Documents', 'icon' => 'fa-gavel', 'color' => 'danger'],
    'registry_docs' => ['label' => 'Registry Documents', 'icon' => 'fa-file-signature', 'color' => 'info'],
    'possession_docs' => ['label' => 'Possession Documents', 'icon' => 'fa-key', 'color' => 'secondary'],
];
?>

<div class="container mt-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>"><?= __('breadcrumb_home') ?></a></li>
            <li class="breadcrumb-item active"><?= __('document_gallery_title') ?></li>
        </ol>
    </nav>

    <div class="row mb-4">
        <div class="col-12">
            <h1 class="display-6 fw-bold text-primary">
                <i class="fas fa-folder-open me-2"></i><?= __('document_gallery_title') ?>
            </h1>
            <p class="text-muted"><?= __('document_gallery_subtitle') ?></p>
        </div>
    </div>

    <!-- Document Categories for Booking Process -->
    <section class="mb-5" id="booking-docs-guide">
        <div class="row g-4 mb-4">
            <div class="col-12">
                <h4 class="fw-bold text-primary mb-3"><i class="fas fa-info-circle me-2"></i>Documents Required for Plot Booking</h4>
                <p class="text-muted">Complete document checklist for a smooth booking process. All documents can be uploaded digitally or submitted physically at our office.</p>
            </div>
            <?php foreach ($bookingDocCategories as $key => $cat): ?>
                <div class="col-lg-2 col-md-4 col-6">
                    <div class="doc-category-card h-100 p-4 text-center border rounded-4" style="border-color: var(--bs-<?= $cat['color'] ?>) !important;">
                        <div class="doc-category-icon mb-3">
                            <i class="fas <?= $cat['icon'] ?> fa-3x text-<?= $cat['color'] ?>"></i>
                        </div>
                        <h6 class="fw-bold mb-2"><?= $cat['label'] ?></h6>
                        <small class="text-muted">
                            <?php
                            $descMap = [
                                'booking_forms' => 'Application, Agreement, Terms',
                                'kyc_docs' => 'PAN, Aadhaar, Photo, Address',
                                'payment_docs' => 'Receipts, Bank Statements, NOC',
                                'legal_docs' => 'Sale Deed, Title Deed, POA',
                                'registry_docs' => 'Mutation, Registry, Khata',
                                'possession_docs' => 'Handover, NOC, Keys'
                            ];
                            echo $descMap[$key] ?? '';
                            ?>
                        </small>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Video Recording & Terms Notice -->
        <div class="alert alert-info border-0 shadow-sm" style="background: linear-gradient(135deg, #e0f2fe 0%, #fef3c7 100%);">
            <div class="d-flex align-items-start gap-3">
                <i class="fas fa-video fa-2x text-primary mt-1"></i>
                <div>
                    <h5 class="fw-bold mb-2">Video Recording & Digital Consent</h5>
                    <p class="mb-2">For all plot bookings, we record a short video confirmation where you acknowledge understanding the terms and conditions. This provides legal protection for both parties.</p>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <a href="<?= BASE_URL ?>/documents/booking-terms" class="btn btn-sm btn-outline-primary" target="_blank">
                                <i class="fas fa-file-alt me-1"></i> View Booking Terms & Conditions
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="<?= BASE_URL ?>/documents/privacy-policy" class="btn btn-sm btn-outline-secondary" target="_blank">
                                <i class="fas fa-shield-alt me-1"></i> Privacy Policy
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Physical Document Submission -->
        <div class="alert alert-warning border-0 shadow-sm mt-3">
            <div class="d-flex align-items-start gap-3">
                <i class="fas fa-file-alt fa-2x text-warning mt-1"></i>
                <div>
                    <h5 class="fw-bold mb-2">Physical Document Submission</h5>
                    <p class="mb-2">You can also download, print, and submit physical copies at our office:</p>
                    <ul class="mb-0 small">
                        <li>Download forms from <a href="<?= BASE_URL ?>/documents/download-forms" target="_blank">here</a></li>
                        <li>Fill, sign, and attach required KYC documents</li>
                        <li>Submit at: APS Dream Home Office, Gorakhpur, UP</li>
                        <li>Associates can also help you fill forms at your location</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body aps-cp-card-body">
            <form method="GET" action="<?= BASE_URL ?>/documents" class="row g-3">
    <?php echo CSRFProtection::csrfField(); ?>
                <div class="col-md-6">
                    <label for="q" class="form-label"><?= __('common_search') ?></label>
                    <input type="text" class="form-control" id="q" name="q" placeholder="<?= __('document_search_placeholder') ?>" value="<?= htmlspecialchars($searchQuery ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label for="category" class="form-label"><?= __('common_category') ?></label>
                    <select class="form-select" id="category" name="category">
                        <option value=""><?= __('documents_all_categories') ?></option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= htmlspecialchars($cat['category'] ?? '') ?>" <?= $selectedCategory === ($cat['category'] ?? '') ? 'selected' : '' ?>>
                                <?= ucfirst(htmlspecialchars($cat['category'] ?? '')) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i> <?= __('common_filter') ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Documents Grid -->
    <div class="row">
        <?php if (!empty($documents)): ?>
            <?php foreach ($documents as $doc): ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card h-100 document-card">
                        <div class="card-body aps-cp-card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="document-icon me-3">
                                    <?php
                                    $fileType = $doc['document_type'] ?? $doc['type'] ?? 'pdf';
                                    $iconMap = [
                                        'pdf' => 'fa-file-pdf text-danger',
                                        'doc' => 'fa-file-word text-primary',
                                        'docx' => 'fa-file-word text-primary',
                                        'xls' => 'fa-file-excel text-success',
                                        'xlsx' => 'fa-file-excel text-success',
                                        'jpg' => 'fa-file-image text-info',
                                        'jpeg' => 'fa-file-image text-info',
                                        'png' => 'fa-file-image text-info',
                                    ];
                                    $icon = $iconMap[strtolower($fileType)] ?? 'fa-file text-muted';
                                    ?>
                                    <i class="fas <?= $icon ?> fa-3x"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="card-title mb-1"><?= htmlspecialchars($doc['document_number'] ?? $doc['document_type'] ?? 'Document') ?></h6>
                                    <small class="text-muted">
                                        <i class="fas fa-tag me-1"></i><?= ucfirst(htmlspecialchars($doc['document_type'] ?? 'general')) ?>
                                        <?php if (!empty($doc['issued_by'])): ?>
                                            <span class="ms-2"><i class="fas fa-building me-1"></i><?= htmlspecialchars($doc['issued_by']) ?></span>
                                        <?php endif; ?>
                                    </small>
                                </div>
                            </div>
                            <?php if (!empty($doc['issue_date'])): ?>
                                <p class="card-text small text-muted mb-3">
                                    <i class="fas fa-calendar me-1"></i>Issued: <?= htmlspecialchars($doc['issue_date']) ?>
                                    <?php if (!empty($doc['expiry_date'])): ?>
                                        | Expires: <?= htmlspecialchars($doc['expiry_date']) ?>
                                    <?php endif; ?>
                                </p>
                            <?php endif; ?>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    <?php
                                    $statusColors = [
                                        'verified' => 'success',
                                        'pending' => 'warning',
                                        'rejected' => 'danger',
                                        'expired' => 'secondary',
                                    ];
                                    $status = $doc['verification_status'] ?? 'pending';
                                    $statusColor = $statusColors[$status] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?= $statusColor ?>"><?= ucfirst(htmlspecialchars($status)) ?></span>
                                </small>
                                <?php if (!empty($doc['url'])): ?>
                                    <a href="<?= htmlspecialchars($doc['url']) ?>" class="btn btn-sm btn-primary" target="_blank">
                                        <i class="fas fa-external-link-alt me-1"></i><?= __('common_view') ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="card aps-cp-card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-file fa-4x text-muted mb-3"></i>
                        <h5 class="text-muted"><?= __('documents_not_found') ?></h5>
                        <p class="text-muted"><?= __('documents_adjust_search') ?></p>
                        <a href="<?= BASE_URL ?>/documents" class="btn btn-primary"><?= __('documents_view_all') ?></a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.document-card {
    transition: transform 0.2s, box-shadow 0.2s;
    border: none;
    box-shadow: 0 2px 15px rgba(0,0,0,0.08);
}
.document-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 20px rgba(0,0,0,0.12);
}
.document-icon {
    width: 50px;
    text-align: center;
}

.doc-category-card {
    transition: all 0.3s ease;
    background: white;
}
.doc-category-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    border-color: var(--bs-primary) !important;
}
.doc-category-icon {
    transition: transform 0.3s ease;
}
.doc-category-card:hover .doc-category-icon {
    transform: scale(1.1);
}
</style>
