<?php
/**
 * Standalone property inquiry form (GET /property/inquire?id=X).
 * POSTs to /property/interest which handles DB + seller notify + thread.
 * @var array|null $property
 * @var int $property_id
 * @var string $listing_type
 * @var string $ref
 */
$property = $property ?? null;
$property_id = (int)($property_id ?? 0);
$listing_type = $listing_type ?? 'company';
$ref = $ref ?? $_GET['ref'] ?? '';
$base = defined('BASE_URL') ? BASE_URL : '';
?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h4 class="fw-bold mb-1"><i class="fas fa-paper-plane me-2 text-success"></i>Send Inquiry</h4>
                    <?php if ($property): ?>
                        <p class="text-muted">Regarding: <strong><?= htmlspecialchars($property['title'] ?? ('Property #' . $property_id)) ?></strong>
                            <span class="badge bg-<?= $listing_type === 'user' ? 'info' : 'primary' ?> ms-1"><?= $listing_type === 'user' ? 'Owner Listing' : 'APS Verified' ?></span>
                        </p>
                    <?php elseif ($property_id > 0): ?>
                        <div class="alert alert-warning">Property not found. <a href="<?= $base ?>/properties">Browse properties</a></div>
                    <?php endif; ?>
                    <?php if (!empty($_SESSION['flash_success'])): ?>
                        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['flash_success']) ?></div>
                        <?php unset($_SESSION['flash_success']); ?>
                    <?php endif; ?>
                    <?php if (!empty($_SESSION['flash_error'])): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['flash_error']) ?></div>
                        <?php unset($_SESSION['flash_error']); ?>
                    <?php endif; ?>
                    <form method="POST" action="<?= $base ?>/property/interest">
                        <?php echo CSRFProtection::csrfField(); ?>
                        <input type="hidden" name="property_id" value="<?= $property_id ?>">
                        <input type="hidden" name="source" value="inquire_page">
                        <div class="mb-3">
                            <label class="form-label">Your Name *</label>
                            <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($_SESSION['user_name'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone Number *</label>
                            <input type="tel" name="phone" class="form-control" required pattern="[0-9]{10}" maxlength="10" value="<?= htmlspecialchars($_SESSION['user_phone'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email (optional)</label>
                            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($_SESSION['user_email'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Message</label>
                            <textarea name="message" class="form-control" rows="3" placeholder="Hi! I'm interested in this property. Please share details."></textarea>
                        </div>
                        <?php if (!empty($ref)): ?>
                            <input type="hidden" name="referral_code" value="<?= htmlspecialchars($ref) ?>">
                        <?php endif; ?>
                        <button type="submit" class="btn btn-success w-100 py-2">
                            <i class="fas fa-paper-plane me-2"></i>Send Inquiry
                        </button>
                    </form>
                    <div class="text-center mt-3">
                        <a href="<?= $base ?>/properties" class="text-muted"><i class="fas fa-arrow-left me-1"></i>Back to Properties</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
