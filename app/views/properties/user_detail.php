<?php if (!isset($sc)) { $sc = function($k, $d='') { return $GLOBALS['_site_settings_cache'][$k] ?? $d; }; } $phoneRaw = preg_replace('/[^0-9]/', '', $sc('contact_whatsapp', '919277121112')); $phoneDisplay = $sc('contact_phone', '<?= htmlspecialchars($phoneDisplay) ?>'); $emailDisplay = $sc('contact_email', '<?= htmlspecialchars($emailDisplay) ?>'); ?>
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
                    <img src="<?= BASE_URL ?>/assets/images/placeholder/property.svg" class="card-img-top" alt="<?php echo htmlspecialchars($property['name'] ?? ''); ?>" style="height:400px;object-fit:cover;" onerror="this.parentElement.querySelector('.no-image-placeholder').style.display='flex'">
                    <?php endif; ?>
                    <div class="no-image-placeholder d-<?php echo empty($property['image']) ? 'flex' : 'none'; ?> align-items-center justify-content-center bg-light" style="height:400px;">
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
                        <?php if (!empty($property['district_name'])): ?>, <?php echo htmlspecialchars($property['district_name']); ?><?php endif; ?>
                        <?php if (!empty($property['state_name'])): ?>, <?php echo htmlspecialchars($property['state_name']); ?><?php endif; ?>
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
                    <p class="text-muted lh-lg"><?php echo nl2br(htmlspecialchars($property['description'])); ?></p>
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
                <div class="card-body">
                    <?php if (!empty($property['name']) || !empty($property['phone'])): ?>
                    <div class="mb-4">
                        <h6 class="fw-bold">Listed by:</h6>
                        <p class="mb-1"><i class="fas fa-user text-muted me-2"></i><?php echo htmlspecialchars($property['name'] ?? 'Owner'); ?></p>
                        <?php if (!empty($property['phone'])): ?>
                        <p class="mb-1"><i class="fas fa-phone text-muted me-2"></i>
                            <a href="tel:<?php echo htmlspecialchars($property['phone']); ?>" class="text-decoration-none"><?php echo htmlspecialchars($property['phone']); ?></a>
                        </p>
                        <?php endif; ?>
                        <?php if (!empty($property['email'])): ?>
                        <p class="mb-0"><i class="fas fa-envelope text-muted me-2"></i><?php echo htmlspecialchars($property['email']); ?></p>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <hr>
                    <h6 class="fw-bold mb-3"><i class="fas fa-paper-plane me-2"></i>Send Inquiry</h6>
                    <form method="POST" action="<?php echo BASE_URL; ?>/property/inquire">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="property_id" value="<?php echo $property['id']; ?>">
                        <div class="mb-3">
                            <input type="text" name="name" class="form-control" placeholder="Your Name *" required value="<?php echo htmlspecialchars($_SESSION['user_name'] ?? ''); ?>">
                        </div>
                        <div class="mb-3">
                            <input type="email" name="email" class="form-control" placeholder="Your Email" value="<?php echo htmlspecialchars($_SESSION['user_email'] ?? ''); ?>">
                        </div>
                        <div class="mb-3">
                            <input type="tel" name="phone" class="form-control" placeholder="Your Phone *" required value="<?php echo htmlspecialchars($_SESSION['user_phone'] ?? ''); ?>">
                        </div>
                        <div class="mb-3">
                            <textarea name="message" class="form-control" rows="3" placeholder="I'm interested in this property. Please contact me."></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-paper-plane me-2"></i>Send Inquiry
                        </button>
                    </form>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h6 class="fw-bold mb-3"><i class="fas fa-tools me-2"></i>Quick Actions</h6>
                    <a href="tel:919277121112" class="btn btn-success w-100 mb-2">
                        <i class="fas fa-phone me-2"></i>Call APS (<?= htmlspecialchars($phoneDisplay) ?>)
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
