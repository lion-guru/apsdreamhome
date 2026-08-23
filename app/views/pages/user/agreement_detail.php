<?php
$page_title = $page_title ?? __('user_agreement_detail_page_title', null, 'Agreement Details');
$current_page = 'agreements';
$agreement = $agreement ?? [];
$user = $user ?? [];
if (!isset($sc)) { $sc = function($k, $d='') { return $GLOBALS['_site_settings_cache'][$k] ?? $d; }; }
$phoneDisplay = $sc('contact_phone', '+91 92771 21112');
$phoneRaw = preg_replace('/[^0-9]/', '', $sc('contact_whatsapp', '919277121112'));

$statusColors = [
    'draft' => 'secondary',
    'pending_signature' => 'warning',
    'signed' => 'success',
    'registered' => 'info',
    'cancelled' => 'danger',
    'expired' => 'dark',
];
$statusLabels = [
    'draft' => __('user_agreement_detail_status_draft', null, 'Draft'),
    'pending_signature' => __('user_agreement_detail_status_pending', null, 'Pending Signature'),
    'signed' => __('user_agreement_detail_status_signed', null, 'Signed'),
    'registered' => __('user_agreement_detail_status_registered', null, 'Registered'),
    'cancelled' => __('user_agreement_detail_status_cancelled', null, 'Cancelled'),
    'expired' => __('user_agreement_detail_status_expired', null, 'Expired'),
];
$typeLabels = [
    'sale_deed' => __('user_agreement_detail_type_sale_deed', null, 'Sale Deed'),
    'allotment' => __('user_agreement_detail_type_allotment', null, 'Allotment Letter'),
    'mortgage' => __('user_agreement_detail_type_mortgage', null, 'Mortgage'),
    'lease' => __('user_agreement_detail_type_lease', null, 'Lease Agreement'),
    'nda' => __('user_agreement_detail_type_nda', null, 'NDA'),
    'joint_venture' => __('user_agreement_detail_type_joint_venture', null, 'Joint Venture'),
    'other' => __('user_agreement_detail_type_other', null, 'Other'),
];

$status = $agreement['status'] ?? 'draft';
$color = $statusColors[$status] ?? 'secondary';
$label = $statusLabels[$status] ?? ucfirst(str_replace('_', ' ', $status));
$type = $typeLabels[$agreement['agreement_type'] ?? ''] ?? ucfirst(str_replace('_', ' ', $agreement['agreement_type'] ?? 'other'));
$agrNumber = htmlspecialchars($agreement['agreement_number'] ?? 'AGR-' . str_pad($agreement['id'] ?? 0, 4, '0', STR_PAD_LEFT));
$agrDate = date('d M Y', strtotime($agreement['agreement_date'] ?? $agreement['created_at'] ?? 'now'));
$signedAt = !empty($agreement['signed_at']) ? date('d M Y, h:i A', strtotime($agreement['signed_at'])) : '';
$plotNo = htmlspecialchars($agreement['plot_number'] ?? 'N/A');
$block = htmlspecialchars($agreement['block'] ?? '');
$colonyName = htmlspecialchars($agreement['colony_name'] ?? 'N/A');
$district = htmlspecialchars($agreement['district_name'] ?? '');
$area = number_format((float)($agreement['area_sqft'] ?? 0));
$dimLabel = htmlspecialchars($agreement['dimension_label'] ?? (($agreement['width_ft'] ?? 0) . ' x ' . ($agreement['length_ft'] ?? 0) . ' ft'));
$facing = htmlspecialchars($agreement['facing'] ?? 'N/A');
$totalValue = number_format((float)($agreement['total_value'] ?? $agreement['plot_price'] ?? $agreement['total_plot_value'] ?? 0), 2);
$bookingNo = htmlspecialchars($agreement['booking_number'] ?? 'N/A');
$customerName = htmlspecialchars($agreement['customer_name'] ?? ($user['name'] ?? 'Customer'));
?>

<div class="aps-cp-hero">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h2><i class="fas fa-file-signature me-2"></i><?= __('user_agreement_detail_heading', null, 'Agreement Details') ?></h2>
            <p><?= $type ?> — <?= $agrNumber ?></p>
        </div>
        <div class="col-md-4 mt-3 mt-md-0 text-md-end">
            <a href="<?= BASE_URL ?>/user/agreements" class="btn btn-light">
                <i class="fas fa-arrow-left me-2"></i><?= __('user_agreement_detail_back', null, 'Back to Agreements') ?>
            </a>
        </div>
    </div>
</div>

<?php if ($status === 'pending_signature'): ?>
    <div class="aps-cp-card style-22751">
        <div class="aps-cp-card-body">
            <div class="d-flex align-items-center gap-3">
                <div class="style-35768">
                    <i class="fas fa-pen-fancy"></i>
                </div>
                <div class="flex-grow-1">
                    <h5 class="mb-1 style-73192"><?= __('user_agreement_detail_action_required', null, 'Action Required: Sign Your Agreement') ?></h5>
                    <p class="mb-0 style-73192"><?= __('user_agreement_detail_review_sign', null, 'Please review the agreement terms below and sign to proceed with your booking.') ?></p>
                </div>
                <button onclick="openSignModal()" class="btn btn-success btn-lg">
                    <i class="fas fa-pen me-2"></i><?= __('user_agreement_detail_sign_btn', null, 'Sign Agreement') ?>
                </button>
            </div>
        </div>
    </div>
<?php elseif ($status === 'signed'): ?>
    <div class="aps-cp-card style-95785">
        <div class="aps-cp-card-body">
            <div class="d-flex align-items-center gap-3">
                <div class="style-7191">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="flex-grow-1">
                    <h5 class="mb-1 style-58689"><?= __('user_agreement_detail_signed_success', null, 'Agreement Signed Successfully') ?></h5>
                    <p class="mb-0 style-58689"><?= __('user_agreement_detail_signed_on', null, 'Signed on') ?> <?= $signedAt ?><?= !empty($agreement['signed_ip']) ? ' ' . __('user_agreement_detail_signed_from_ip', null, 'from IP:') . ' ' . htmlspecialchars($agreement['signed_ip'] ?? '') : '' ?></p>
                </div>
                <a href="<?= BASE_URL ?>/user/agreements/<?= $agreement['id'] ?>/preview" class="btn btn-outline-success" target="_blank">
                    <i class="fas fa-print me-2"></i><?= __('user_agreement_detail_print_download', null, 'Print / Download') ?>
                </a>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="aps-cp-card">
            <div class="aps-cp-card-header">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i><?= __('user_agreement_detail_info_heading', null, 'Agreement Information') ?></h5>
                <span class="aps-cp-badge aps-cp-badge-<?= $color ?>"><?= $label ?></span>
            </div>
            <div class="aps-cp-card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted small"><?= __('user_agreement_detail_number', null, 'Agreement Number') ?></label>
                            <div class="fw-semibold"><?= $agrNumber ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted small"><?= __('user_agreement_detail_type_label', null, 'Agreement Type') ?></label>
                            <div class="fw-semibold"><?= $type ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted small"><?= __('user_agreement_detail_booking_ref', null, 'Booking Reference') ?></label>
                            <div class="fw-semibold"><?= $bookingNo ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted small"><?= __('user_agreement_detail_date', null, 'Agreement Date') ?></label>
                            <div class="fw-semibold"><?= $agrDate ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted small"><?= __('user_agreement_detail_value', null, 'Agreement Value') ?></label>
                            <div class="fw-semibold style-92042">&#8377;<?= $totalValue ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted small"><?= __('user_agreement_detail_buyer_name', null, 'Buyer Name') ?></label>
                            <div class="fw-semibold"><?= $customerName ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="aps-cp-card">
            <div class="aps-cp-card-header">
                <h5 class="mb-0"><i class="fas fa-map-marked-alt me-2"></i><?= __('user_agreement_detail_property_heading', null, 'Property Details') ?></h5>
            </div>
            <div class="aps-cp-card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted small"><?= __('user_agreement_detail_colony', null, 'Colony / Project') ?></label>
                            <div class="fw-semibold"><?= $colonyName ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted small"><?= __('user_agreement_detail_district', null, 'District') ?></label>
                            <div class="fw-semibold"><?= $district ?>, Uttar Pradesh</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label text-muted small"><?= __('user_agreement_detail_plot_number', null, 'Plot Number') ?></label>
                            <div class="fw-semibold"><?= $plotNo ?><?= $block ? ' (' . $block . ')' : '' ?></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label text-muted small"><?= __('user_agreement_detail_area', null, 'Area') ?></label>
                            <div class="fw-semibold"><?= $area ?> sq ft</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label text-muted small"><?= __('user_agreement_detail_dimensions', null, 'Dimensions') ?></label>
                            <div class="fw-semibold"><?= $dimLabel ?></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label text-muted small"><?= __('user_agreement_detail_facing', null, 'Facing') ?></label>
                            <div class="fw-semibold"><?= $facing ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="aps-cp-card">
            <div class="aps-cp-card-header">
                <h5 class="mb-0"><i class="fas fa-gavel me-2"></i><?= __('user_agreement_detail_terms_heading', null, 'Key Terms (Summary)') ?></h5>
            </div>
            <div class="aps-cp-card-body">
                <ul class="list-unstyled mb-0">
                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i><?= __('user_agreement_detail_term_payment', null, 'Payment schedule as per booking agreement') ?></li>
                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i><?= __('user_agreement_detail_term_interest', null, '18% p.a. interest on overdue installments') ?></li>
                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i><?= __('user_agreement_detail_term_possession', null, 'Possession within 24 months from agreement date') ?></li>
                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i><?= __('user_agreement_detail_term_stamp', null, 'Stamp duty and registration charges borne by buyer') ?></li>
                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i><?= __('user_agreement_detail_term_dispute', null, 'Dispute resolution: Gorakhpur jurisdiction') ?></li>
                    <li class="mb-0"><i class="fas fa-check text-success me-2"></i><?= __('user_agreement_detail_term_rera', null, 'RERA registered project') ?></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="aps-cp-card">
            <div class="aps-cp-card-header">
                <h5 class="mb-0"><i class="fas fa-cogs me-2"></i><?= __('user_agreement_detail_actions_heading', null, 'Actions') ?></h5>
            </div>
            <div class="aps-cp-card-body">
                <?php if ($status === 'pending_signature'): ?>
                    <button onclick="openSignModal()" class="btn btn-success w-100 mb-3">
                        <i class="fas fa-pen me-2"></i><?= __('user_agreement_detail_sign_btn', null, 'Sign Agreement') ?>
                    </button>
                    <a href="<?= BASE_URL ?>/user/agreements/<?= $agreement['id'] ?>/preview" class="btn btn-outline-primary w-100 mb-3" target="_blank">
                        <i class="fas fa-eye me-2"></i><?= __('user_agreement_detail_preview', null, 'Preview Full Agreement') ?>
                    </a>
                <?php elseif ($status === 'signed' || $status === 'registered'): ?>
                    <a href="<?= BASE_URL ?>/user/agreements/<?= $agreement['id'] ?>/preview" class="btn btn-primary w-100 mb-3" target="_blank">
                        <i class="fas fa-print me-2"></i><?= __('user_agreement_detail_print_pdf', null, 'Print / Download PDF') ?>
                    </a>
                <?php endif; ?>
                
                <a href="<?= BASE_URL ?>/user/bookings/<?= $agreement['booking_id'] ?? '' ?>" class="btn btn-outline-secondary w-100">
                    <i class="fas fa-arrow-right me-2"></i><?= __('user_agreement_detail_view_booking', null, 'View Booking') ?>
                </a>
            </div>
        </div>

        <?php if (!empty($agreement['document_url'])): ?>
        <div class="aps-cp-card">
            <div class="aps-cp-card-header">
                <h5 class="mb-0"><i class="fas fa-file-pdf me-2"></i><?= __('user_agreement_detail_attached_doc', null, 'Attached Document') ?></h5>
            </div>
            <div class="aps-cp-card-body">
                <a href="<?= BASE_URL ?>/<?= htmlspecialchars($agreement['document_url'] ?? '') ?>" class="btn btn-outline-danger w-100" target="_blank">
                    <i class="fas fa-download me-2"></i><?= __('user_agreement_detail_download_pdf', null, 'Download Agreement PDF') ?>
                </a>
            </div>
        </div>
        <?php endif; ?>

        <div class="aps-cp-card">
            <div class="aps-cp-card-header">
                <h5 class="mb-0"><i class="fas fa-question-circle me-2"></i><?= __('user_agreement_detail_need_help', null, 'Need Help?') ?></h5>
            </div>
            <div class="aps-cp-card-body">
                <p class="small text-muted mb-3"><?= __('user_agreement_detail_help_desc', null, 'Have questions about this agreement? Contact our legal team.') ?></p>
                <a href="tel:<?= $phoneRaw ?>" class="btn btn-outline-primary w-100 mb-2">
                    <i class="fas fa-phone me-2"></i><?= htmlspecialchars($phoneDisplay ?? '') ?>
                </a>
                <a href="mailto:legal@apsdreamhome.com" class="btn btn-outline-secondary w-100">
                    <i class="fas fa-envelope me-2"></i>legal@apsdreamhome.com
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Sign Agreement Modal -->
<div class="modal fade" id="signModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content style-18266">
            <div class="modal-header style-39104">
                <h5 class="modal-title"><i class="fas fa-pen-fancy me-2"></i><?= __('user_agreement_detail_sign_heading', null, 'Sign Agreement') ?></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-info mb-3">
                    <i class="fas fa-info-circle me-2"></i><?= __('user_agreement_detail_sign_disclaimer', null, 'By clicking "I Agree" below, you acknowledge that you have read and agree to all terms and conditions of this') ?> <?= $type ?>.
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="agreeCheck" class="style-32039">
                    <label class="form-check-label fw-semibold" for="agreeCheck" class="style-95214">
                        <?= __('user_agreement_detail_i_agree', null, 'I have read, understood, and agree to all the terms and conditions of this agreement.') ?>
                    </label>
                </div>
                <div class="text-muted small style-90969">
                    <p class="mb-1"><i class="fas fa-fingerprint me-2"></i><?= __('user_agreement_detail_digital_sig_note', null, 'Digital signature will be recorded with:') ?></p>
                    <ul class="mb-0 ps-4">
                        <li><?= __('user_agreement_detail_sig_timestamp', null, 'Timestamp (date and time)') ?></li>
                        <li><?= __('user_agreement_detail_sig_ip', null, 'IP address') ?></li>
                        <li><?= __('user_agreement_detail_sig_user', null, 'User identification') ?></li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer border-top-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= __('user_agreement_detail_cancel', null, 'Cancel') ?></button>
                <button type="button" class="btn btn-success" id="signBtn" onclick="signAgreement()" disabled>
                    <i class="fas fa-pen me-2"></i><?= __('user_agreement_detail_i_agree_sign', null, 'I Agree — Sign Now') ?>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
var AGR_STRINGS = {
    signing: <?= json_encode(__('user_agreement_detail_signing', null, 'Signing...')) ?>,
    signFailed: <?= json_encode(__('user_agreement_detail_sign_failed', null, 'Signing failed. Please try again.')) ?>,
    networkError: <?= json_encode(__('user_agreement_detail_network_error', null, 'Network error. Please try again.')) ?>,
    iAgreeSign: <?= json_encode(__('user_agreement_detail_i_agree_sign', null, 'I Agree — Sign Now')) ?>
};

document.addEventListener('DOMContentLoaded', function() {
    var check = document.getElementById('agreeCheck');
    var btn = document.getElementById('signBtn');
    if (check && btn) {
        check.addEventListener('change', function() {
            btn.disabled = !this.checked;
        });
    }
});

function openSignModal() {
    var modal = new bootstrap.Modal(document.getElementById('signModal'));
    modal.show();
}

function signAgreement() {
    var btn = document.getElementById('signBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>' + AGR_STRINGS.signing;

    var fd = new FormData();
    var meta = document.querySelector('meta[name="csrf-token"]');
    fd.append('csrf_token', meta ? meta.getAttribute('content') : '');

    fetch('<?= BASE_URL ?>/user/agreements/<?= $agreement['id'] ?>/sign', {
        method: 'POST',
        headers: { 'Accept': 'application/json' },
        body: fd,
        credentials: 'same-origin'
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success && data.redirect) {
            window.location.href = data.redirect;
        } else {
            alert(data.error || AGR_STRINGS.signFailed);
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-pen me-2"></i>' + AGR_STRINGS.iAgreeSign;
        }
    })
    .catch(function(err) {
        alert(AGR_STRINGS.networkError);
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-pen me-2"></i>' + AGR_STRINGS.iAgreeSign;
    });
}
</script>
