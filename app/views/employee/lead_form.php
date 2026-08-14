<?php
$sources = $sources ?? [];
$assignees = $assignees ?? [];
$base = BASE_URL ?? '';
?>
<style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
.form-header { background: linear-gradient(135deg, #1e40af, #3b82f6); color: #fff; border-radius: 12px; padding: 24px; margin-bottom: 20px; }
</style>

<div class="form-header">
    <a href="<?= $base ?>/employee/leads" class="text-white text-decoration-none" class="style-4669">
        <i class="fas fa-arrow-left me-1"></i>Back to Leads
    </a>
    <h4 class="mt-2 mb-0"><i class="fas fa-plus-circle me-2"></i>Add New Lead</h4>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="<?= $base ?>/employee/leads/store">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" required placeholder="Enter lead name">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Phone <span class="text-danger">*</span></label>
                    <input type="tel" name="phone" class="form-control" required placeholder="+91 XXXXX XXXXX" pattern="[+]?[0-9\s\-]{10,15}">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Email</label>
                    <input type="email" name="email" class="form-control" placeholder="email@example.com">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Source</label>
                    <select name="source" class="form-select">
                        <option value="manual">Manual Entry</option>
                        <?php foreach ($sources as $s): ?>
                            <option value="<?= htmlspecialchars($s['name'] ?? $s['id']) ?>"><?= htmlspecialchars($s['name']) ?></option>
                        <?php endforeach; ?>
                        <option value="referral">Referral</option>
                        <option value="walk_in">Walk-in</option>
                        <option value="phone_call">Phone Call</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Budget (â‚¹)</label>
                    <input type="number" name="budget" class="form-control" placeholder="e.g. 5000000" min="0">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">City</label>
                    <input type="text" name="city" class="form-control" placeholder="e.g. Lucknow">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Property Interest</label>
                    <input type="text" name="property_interest" class="form-control" placeholder="e.g. 2BHK Apartment, Plot">
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Notes</label>
                    <textarea name="notes" class="form-control" rows="3" placeholder="Initial notes about the lead..."></textarea>
                </div>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="fas fa-save me-1"></i>Create Lead
                </button>
                <a href="<?= $base ?>/employee/leads" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
