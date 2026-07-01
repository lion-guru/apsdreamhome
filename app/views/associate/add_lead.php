<?php
$page_title = $page_title ?? 'Add Lead - APS Dream Home';
$success = $success ?? null;
$error = $error ?? null;
$properties = $properties ?? [];
?>

<style>
    .crm-form .form-label { font-weight: 600; color: #475569; font-size: 0.85rem; margin-bottom: 4px; }
    .crm-form .form-control, .crm-form .form-select { border-radius: 10px; padding: 10px 14px; border-color: #e2e8f0; }
    .crm-form .form-control:focus, .crm-form .form-select:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,0.15); }
    .form-section { background: #fff; border-radius: 12px; padding: 20px; margin-bottom: 16px; border: 1px solid #e2e8f0; }
    .form-section-title { font-size: 0.9rem; font-weight: 700; color: #1e293b; margin-bottom: 16px; padding-bottom: 8px; border-bottom: 2px solid #f1f5f9; }
    .form-section-title i { color: #6366f1; margin-right: 6px; }
    .required-star { color: #ef4444; }
</style>

<div class="container-fluid px-4 py-3 crm-form">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="fas fa-user-plus text-primary me-2"></i>Add New Lead</h4>
            <small class="text-muted">Fill in the details to add a lead to your pipeline</small>
        </div>
        <a href="<?= BASE_URL ?>/associate/leads" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Back to Leads
        </a>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show border-0 rounded-3" role="alert">
            <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show border-0 rounded-3" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form action="<?= BASE_URL ?>/associate/leads/store" method="POST" id="addLeadForm">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

        <!-- Contact Info -->
        <div class="form-section">
            <div class="form-section-title"><i class="fas fa-user"></i>Contact Information</div>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Full Name <span class="required-star">*</span></label>
                    <input type="text" class="form-control" name="name" required placeholder="e.g. Rajesh Kumar" id="leadName">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Phone Number <span class="required-star">*</span></label>
                    <input type="tel" class="form-control" name="phone" required placeholder="10-digit mobile number" pattern="[0-9]{10}" maxlength="10" id="leadPhone">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Email Address</label>
                    <input type="email" class="form-control" name="email" placeholder="name@example.com">
                </div>
            </div>
        </div>

        <!-- Property Interest -->
        <div class="form-section">
            <div class="form-section-title"><i class="fas fa-home"></i>Property Interest</div>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Property Type</label>
                    <select class="form-select" name="property_interest">
                        <option value="">— Select Type —</option>
                        <option value="Residential Plot">Residential Plot</option>
                        <option value="Commercial Plot">Commercial Plot</option>
                        <option value="Villa">Premium Villa</option>
                        <option value="Apartment">Apartment</option>
                        <option value="Farm Land">Farm Land</option>
                        <option value="General Inquiry">General Inquiry</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Budget Range</label>
                    <select class="form-select" name="budget_range">
                        <option value="">— Select Budget —</option>
                        <option value="Under ₹5 Lakh">Under ₹5 Lakh</option>
                        <option value="₹5-10 Lakh">₹5 - 10 Lakh</option>
                        <option value="₹10-25 Lakh">₹10 - 25 Lakh</option>
                        <option value="₹25-50 Lakh">₹25 - 50 Lakh</option>
                        <option value="₹50 Lakh - 1 Cr">₹50 Lakh - 1 Cr</option>
                        <option value="₹1-2 Cr">₹1 - 2 Cr</option>
                        <option value="₹2-5 Cr">₹2 - 5 Cr</option>
                        <option value="Above ₹5 Cr">Above ₹5 Cr</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Preferred Location</label>
                    <input type="text" class="form-control" name="location_preference" placeholder="e.g. Suryoday Colony">
                </div>
            </div>
        </div>

        <!-- Lead Details -->
        <div class="form-section">
            <div class="form-section-title"><i class="fas fa-clipboard-list"></i>Lead Details</div>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Lead Source</label>
                    <select class="form-select" name="source">
                        <option value="associate">Self Generated</option>
                        <option value="referral">Referral</option>
                        <option value="walk_in">Walk-in</option>
                        <option value="phone_call">Phone Call</option>
                        <option value="website">Website</option>
                        <option value="social_media">Social Media</option>
                        <option value="advertisement">Advertisement</option>
                        <option value="existing_customer">Existing Customer</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Priority</label>
                    <select class="form-select" name="priority">
                        <option value="medium" selected>Medium</option>
                        <option value="high">High — Hot Lead</option>
                        <option value="low">Low — Can Wait</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Notes & Requirements</label>
                    <textarea class="form-control" name="notes" rows="3" placeholder="Client requirements, preferences, special notes..."></textarea>
                </div>
            </div>
        </div>

        <!-- Submit -->
        <div class="d-flex justify-content-between align-items-center">
            <a href="<?= BASE_URL ?>/associate/leads" class="btn btn-outline-secondary">
                <i class="fas fa-times me-1"></i> Cancel
            </a>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="fas fa-save me-1"></i> Save Lead
                </button>
                <button type="submit" class="btn btn-success px-4" onclick="document.getElementById('addLeadForm').setAttribute('data-redirect', 'add-another')">
                    <i class="fas fa-plus me-1"></i> Save & Add Another
                </button>
            </div>
        </div>
    </form>
</div>
