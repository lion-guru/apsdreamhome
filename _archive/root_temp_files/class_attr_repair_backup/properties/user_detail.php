<?php if (!isset($sc)) { $sc = function($k, $d='') { return $GLOBALS['_site_settings_cache'][$k] ?? $d; }; } $phoneRaw = preg_replace('/[^0-9]/', '', $sc('contact_whatsapp', '919277121112')); $phoneDisplay = $sc('contact_phone', '+91 92771 21112'); $emailDisplay = $sc('contact_email', 'support@apsdreamhome.com'); ?>
<div class="container my-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>">Home</a></li>
            <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/properties">Properties</a></li>
            <li class="breadcrumb-item active"><?php echo htmlspecialchars($property['name'] ?? 'Property Detail'); ?></li>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- Property Images -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="position-relative">
                    <?php if (!empty($property['image'])): ?>
                    <img src="<?= BASE_URL ?>/assets/images/placeholder/property.svg" class="card-img-top" alt="<?php echo htmlspecialchars($property['name'] ?? ''); ?>" class="style-32235" onerror="this.parentElement.querySelector('.no-image-placeholder').style.display='flex'">
                    <?php endif; ?>
                    <div class="no-image-placeholder d-<?php echo empty($property['image']) ? 'flex' : 'none'; ?> align-items-center justify-content-center bg-light" class="style-74655">
                        <div class="text-center text-muted">
                            <i class="fas fa-building fa-4x mb-3"></i>
                            <p class="mb-0">No Image Available</p>
                        </div>
                    </div>
                    <span class="position-absolute top-0 start-0 badge bg-<?php echo $property['listing_type'] === 'rent' ? 'warning' : 'success'; ?> m-3 fs-6">
                        <?php echo strtoupper($property['listing_type'] ?? 'Sell'); ?>
                    </span>
                    <span class="position-absolute top-0 end-0 badge bg-info m-3 fs-6">
                        <?php echo htmlspecialchars(ucfirst($property['property_type'] ?? '')); ?>
                    </span>
                </div>
            </div>

            <!-- Property Details -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-body p-4">
                    <h2 class="card-title h3 mb-3"><?php echo htmlspecialchars($property['name'] ?? ''); ?></h2>
                    <p class="text-muted mb-2">
                        <i class="fas fa-map-marker-alt text-danger me-1"></i>
                        <?php echo htmlspecialchars($property['address'] ?? ($property['city_name'] ?? '')); ?>
                        <?php if (!empty($property['district_name'])): ?>, <?php echo htmlspecialchars($property['district_name'] ?? ''); ?><?php endif; ?>
                        <?php if (!empty($property['state_name'])): ?>, <?php echo htmlspecialchars($property['state_name'] ?? ''); ?><?php endif; ?>
                    </p>

                    <h3 class="text-primary h4 mb-4">
                        <i class="fas fa-rupee-sign"></i> <?php echo number_format($property['price'] ?? 0); ?>
                        <?php if (($property['listing_type'] ?? '') === 'rent'): ?>
                        <small class="text-muted fs-6">/month</small>
                        <?php else: ?>
                        <small class="text-muted fs-6">lakh</small>
                        <?php endif; ?>
                    </h3>

                    <div class="row g-3 mb-4">
                        <?php if (!empty($property['area_sqft'])): ?>
                        <div class="col-md-4">
                            <div class="border rounded p-3 text-center">
                                <i class="fas fa-vector-square text-primary fs-3 mb-2"></i>
                                <h6 class="mb-1">Area</h6>
                                <p class="mb-0 fw-bold"><?php echo number_format($property['area_sqft']); ?> sq.ft</p>
                            </div>
                        </div>
                        <?php endif; ?>
                        <div class="col-md-4">
                            <div class="border rounded p-3 text-center">
                                <i class="fas fa-list text-primary fs-3 mb-2"></i>
                                <h6 class="mb-1">Type</h6>
                                <p class="mb-0 fw-bold"><?php echo htmlspecialchars(ucfirst($property['property_type'] ?? 'N/A')); ?></p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded p-3 text-center">
                                <i class="fas fa-calendar text-primary fs-3 mb-2"></i>
                                <h6 class="mb-1">Posted</h6>
                                <p class="mb-0 fw-bold"><?php echo date('d M Y', strtotime($property['created_at'] ?? 'now')); ?></p>
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($property['description'])): ?>
                    <h5 class="fw-bold mb-3">Description</h5>
                    <p class="text-muted lh-lg"><?php echo nl2br(htmlspecialchars($property['description'] ?? '')); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Contact Owner -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-primary text-white py-3">
                    <h5 class="mb-0"><i class="fas fa-envelope me-2"></i>Contact Owner</h5>
                </div>
                <div class="card-body aps-cp-card-body">
                    <?php if (!empty($property['name']) || !empty($property['phone'])): ?>
                    <div class="mb-4">
                        <h6 class="fw-bold">Listed by:</h6>
                        <p class="mb-1"><i class="fas fa-user text-muted me-2"></i><?php echo htmlspecialchars($property['name'] ?? 'Owner'); ?></p>
                        <?php if (!empty($property['phone'])): ?>
                        <p class="mb-1"><i class="fas fa-phone text-muted me-2"></i>
                            <a href="tel:<?php echo htmlspecialchars($property['phone'] ?? ''); ?>" class="text-decoration-none"><?php echo htmlspecialchars($property['phone'] ?? ''); ?></a>
                        </p>
                        <?php endif; ?>
                        <?php if (!empty($property['email'])): ?>
                        <p class="mb-0"><i class="fas fa-envelope text-muted me-2"></i><?php echo htmlspecialchars($property['email'] ?? ''); ?></p>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <hr>
                    <h6 class="fw-bold mb-3"><i class="fas fa-hand-pointer me-2"></i>Interested in this property?</h6>
                    <p class="text-muted small mb-3">Click below and our team will contact you within 30 minutes.</p>
                    <button type="button" class="btn btn-primary w-100 mb-3" onclick="showDetailInterestModal()" id="detailInterestBtn">
                        <i class="fas fa-hand-pointer me-2"></i>I'm Interested
                    </button>
                    <div class="text-center">
                        <small class="text-muted">or</small>
                    </div>
                    <div class="mt-3">
                        <a href="tel:+919277121112" class="btn btn-success w-100 mb-2">
                            <i class="fas fa-phone me-2"></i>Call Now
                        </a>
                        <a href="https://wa.me/919277121112?text=Hi, I'm interested in <?php echo urlencode($property['name'] ?? 'this property'); ?>" target="_blank" class="btn btn-outline-success w-100">
                            <i class="fab fa-whatsapp me-2"></i>WhatsApp
                        </a>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body aps-cp-card-body">
                    <h6 class="fw-bold mb-3"><i class="fas fa-tools me-2"></i>Quick Actions</h6>
                    <a href="tel:919277121112" class="btn btn-success w-100 mb-2">
                        <i class="fas fa-phone me-2"></i>Call APS (<?= htmlspecialchars($phoneDisplay ?? '') ?>)
                    </a>
                    <a href="https://wa.me/<?= $phoneRaw ?>?text=Hi, I'm interested in <?php echo urlencode($property['name'] ?? 'this property'); ?>" target="_blank" class="btn btn-outline-success w-100 mb-2">
                        <i class="fab fa-whatsapp me-2"></i>WhatsApp
                    </a>
                    <a href="<?php echo BASE_URL; ?>/tools/emi-calculator" class="btn btn-outline-primary w-100">
                        <i class="fas fa-calculator me-2"></i>EMI Calculator
                    </a>
                </div>
            </div>

            <!-- Views Count -->
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-3">
                    <i class="fas fa-eye text-muted me-1"></i>
                    <span class="text-muted">Viewed <?php echo number_format($property['views'] ?? 0); ?> times</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Interest Modal -->
<div class="modal fade" id="detailInterestModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered style-77674">
        <div class="modal-content style-28474">
            <div class="modal-header border-0 pb-0">
                <div>
                    <h6 class="fw-bold mb-0">I'm Interested</h6>
                    <small class="text-muted"><?php echo htmlspecialchars($property['name'] ?? ''); ?></small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="detailInterestForm" onsubmit="submitDetailInterest(event)">
    <?php echo CSRFProtection::csrfField(); ?>
                    <input type="hidden" name="property_id" value="<?php echo e($property['id']); ?>">
                    <input type="hidden" name="source" value="property_detail">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Phone Number *</label>
                        <input type="tel" name="phone" class="form-control" placeholder="+91 98765 43210" required
                               value="<?php echo htmlspecialchars($_SESSION['user_phone'] ?? ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Your Name</label>
                        <input type="text" name="name" class="form-control" placeholder="Enter your name"
                               value="<?php echo htmlspecialchars($_SESSION['user_name'] ?? ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Budget Range</label>
                        <div class="d-flex flex-wrap gap-2">
                            <?php foreach (['Under 10L','10L-25L','25L-50L','50L-1Cr','1Cr+'] as $budget): ?>
                            <button type="button" class="btn btn-outline-primary btn-sm detail-budget-chip" onclick="selectDetailBudget(this)"><?php echo e($budget); ?></button>
                            <?php endforeach; ?>
                        </div>
                        <input type="hidden" name="budget" id="detailInterestBudget">
                    </div>
                    <button type="submit" class="btn btn-primary w-100" id="detailInterestSubmitBtn">
                        <i class="fas fa-paper-plane me-1"></i>Submit Interest
                    </button>
                </form>
                <div id="detailInterestSuccess" class="text-center py-3 style-2248">
                    <i class="fas fa-check-circle text-success fa-3x mb-3"></i>
                    <h6 class="fw-bold">Interest Recorded!</h6>
                    <p class="text-muted small mb-0">Our team will contact you shortly.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.detail-budget-chip.active { background: #c2410c; color: #fff; border-color: #c2410c; }
</style>

<script>
function showDetailInterestModal() {
    document.getElementById('detailInterestForm').style.display = 'block';
    document.getElementById('detailInterestSuccess').style.display = 'none';
    new bootstrap.Modal(document.getElementById('detailInterestModal')).show();
}

function selectDetailBudget(el) {
    document.querySelectorAll('.detail-budget-chip').forEach(c => c.classList.remove('active'));
    el.classList.add('active');
    document.getElementById('detailInterestBudget').value = el.textContent;
}

function submitDetailInterest(e) {
    e.preventDefault();
    const form = document.getElementById('detailInterestForm');
    const btn = document.getElementById('detailInterestSubmitBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Submitting...';

    const fd = new FormData(form);
    fetch('<?php echo BASE_URL; ?>/property/interest', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                form.style.display = 'none';
                document.getElementById('detailInterestSuccess').style.display = 'block';
                setTimeout(() => bootstrap.Modal.getInstance(document.getElementById('detailInterestModal')).hide(), 2500);
            } else {
                alert(data.message || 'Something went wrong. Please try again.');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-paper-plane me-1"></i>Submit Interest';
            }
        })
        .catch(() => {
            alert('Network error. Please try again.');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-paper-plane me-1"></i>Submit Interest';
        });
}
</script>
