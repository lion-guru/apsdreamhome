<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">Service Interest Details</h1>
        <p class="text-muted mb-0">ID: #<?php echo $service['id']; ?></p>
    </div>
    <div>
        <a href="<?php echo BASE_URL; ?>/admin/services" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Customer Info -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Customer Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Name</label>
                        <div class="fw-semibold"><?php echo htmlspecialchars($service['name'] ?? 'N/A'); ?></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Phone</label>
                        <div class="fw-semibold">
                            <?php echo htmlspecialchars($service['phone'] ?? 'N/A'); ?>
                            <?php if (!empty($service['phone'])): ?>
                            <a href="tel:<?php echo htmlspecialchars(service['phone'] ?? ''); ?>" class="btn btn-sm btn-success ms-2">
                                <i class="fas fa-phone"></i>
                            </a>
                            <a href="https://wa.me/91<?php echo preg_replace('/[^0-9]/', '', $service['phone']); ?>" target="_blank" class="btn btn-sm btn-success">
                                <i class="fab fa-whatsapp"></i>
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Email</label>
                        <div class="fw-semibold"><?php echo htmlspecialchars($service['email'] ?? 'N/A'); ?></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Lead Status</label>
                        <div class="fw-semibold">
                            <span class="badge bg-<?php echo $service['lead_status'] === 'new' ? 'danger' : 'secondary'; ?>">
                                <?php echo ucfirst($service['lead_status'] ?? 'N/A'); ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Service Details -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Service Details</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Service Type</label>
                        <div class="fw-semibold">
                            <?php
                            $serviceLabels = [
                                'home_loan' => 'Home Loan',
                                'legal' => 'Legal Help',
                                'registry' => 'Registry',
                                'mutation' => 'Mutation',
                                'interior' => 'Interior Design',
                                'home_insurance' => 'Home Insurance',
                                'property_tax' => 'Property Tax',
                                'rental_agreement' => 'Rental Agreement',
                                'Tenant_verification' => 'Tenant Verification'
                            ];
                            ?>
                            <span class="badge bg-primary"><?php echo $serviceLabels[$service['service_type']] ?? $service['service_type']; ?></span>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Property</label>
                        <div class="fw-semibold"><?php echo htmlspecialchars($service['property_name'] ?? 'N/A'); ?></div>
                    </div>
                </div>
                
                <?php if (!empty($service['notes'])): ?>
                <div class="mt-3">
                    <label class="text-muted small">Notes</label>
                    <div><?php echo nl2br(htmlspecialchars(service['notes'] ?? '')); ?></div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Update Status -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Update Status</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="<?php echo BASE_URL; ?>/admin/services/update-status">
                    <input type="hidden" name="id" value="<?php echo $service['id']; ?>">
                    <div class="mb-3">
                        <select name="status" class="form-select">
                            <option value="new" <?php echo $service['status'] === 'new' ? 'selected' : ''; ?>>New</option>
                            <option value="contacted" <?php echo $service['status'] === 'contacted' ? 'selected' : ''; ?>>Contacted</option>
                            <option value="interested" <?php echo $service['status'] === 'interested' ? 'selected' : ''; ?>>Interested</option>
                            <option value="not_interested" <?php echo $service['status'] === 'not_interested' ? 'selected' : ''; ?>>Not Interested</option>
                            <option value="converted" <?php echo $service['status'] === 'converted' ? 'selected' : ''; ?>>Converted (Sale Done)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Notes</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Add notes about this service inquiry..."><?php echo htmlspecialchars($service['notes'] ?? ''); ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-save me-1"></i>Update Status
                    </button>
                </form>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <?php if (!empty($service['phone'])): ?>
                    <a href="tel:<?php echo htmlspecialchars(service['phone'] ?? ''); ?>" class="btn btn-success">
                        <i class="fas fa-phone me-2"></i>Call Now
                    </a>
                    <a href="https://wa.me/91<?php echo preg_replace('/[^0-9]/', '', $service['phone']); ?>?text=Namaste! APS Dream Home se aapka service inquiry mila hai. Hum aapki kaise madad kar sakte hain?" target="_blank" class="btn btn-success">
                        <i class="fab fa-whatsapp me-2"></i>WhatsApp
                    </a>
                    <?php endif; ?>
                    <?php if (!empty($service['email'])): ?>
                    <a href="mailto:<?php echo htmlspecialchars(service['email'] ?? ''); ?>?subject=Re: APS Dream Home - <?php echo $serviceLabels[$service['service_type']] ?? 'Service'; ?> Inquiry" class="btn btn-primary">
                        <i class="fas fa-envelope me-2"></i>Send Email
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Timeline -->
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Timeline</h5>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <label class="text-muted small">Created</label>
                    <div><?php echo date('d M Y, h:i A', strtotime($service['created_at'])); ?></div>
                </div>
                <?php if (!empty($service['updated_at'])): ?>
                <div class="mb-2">
                    <label class="text-muted small">Last Updated</label>
                    <div><?php echo date('d M Y, h:i A', strtotime($service['updated_at'])); ?></div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
