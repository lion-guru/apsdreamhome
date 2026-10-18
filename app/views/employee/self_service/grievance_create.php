<?php
/**
 * Employee Self-Service - Submit Grievance
 */

$page_title = $page_title ?? 'Submit Grievance';
$categories = $categories ?? [];
$priorities = $priorities ?? [];
?>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-xl-7">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1 fw-bold text-dark">
                        <i class="fas fa-file-signature me-2 text-primary"></i>Submit New Grievance
                    </h4>
                    <p class="text-muted mb-0 small">All grievances are handled confidentially in accordance with company HR policy</p>
                </div>
                <a href="<?= BASE_URL ?>/employee/self-service/grievances" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Back to Grievances
                </a>
            </div>

            <!-- Flash Messages -->
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-4" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Grievance Form Card -->
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-shield-alt text-primary me-2"></i>Grievance Details
                    </h6>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="<?= BASE_URL ?>/employee/self-service/grievances/create">
                        <div class="row g-3">
                            <!-- Category -->
                            <div class="col-md-6">
                                <label for="category" class="form-label fw-semibold small text-dark">Category <span class="text-danger">*</span></label>
                                <select name="category" id="category" class="form-select rounded-2" required>
                                    <option value="" disabled selected>-- Select Category --</option>
                                    <?php foreach ($categories as $catKey => $catLabel): ?>
                                        <option value="<?= htmlspecialchars($catKey) ?>"><?= htmlspecialchars($catLabel) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Priority -->
                            <div class="col-md-6">
                                <label for="priority" class="form-label fw-semibold small text-dark">Priority <span class="text-danger">*</span></label>
                                <select name="priority" id="priority" class="form-select rounded-2" required>
                                    <?php foreach ($priorities as $priKey => $priLabel): ?>
                                        <option value="<?= htmlspecialchars($priKey) ?>" <?= $priKey === 'normal' ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($priLabel) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Sub Category -->
                            <div class="col-12">
                                <label for="sub_category" class="form-label fw-semibold small text-dark">Sub-Category (Optional)</label>
                                <input type="text" name="sub_category" id="sub_category" class="form-control rounded-2" placeholder="e.g. Workstation ergonomics, Salary delay, Team communication">
                            </div>

                            <!-- Subject -->
                            <div class="col-12">
                                <label for="subject" class="form-label fw-semibold small text-dark">Subject <span class="text-danger">*</span></label>
                                <input type="text" name="subject" id="subject" class="form-control rounded-2" required placeholder="Concise summary of your grievance">
                            </div>

                            <!-- Description -->
                            <div class="col-12">
                                <label for="description" class="form-label fw-semibold small text-dark">Detailed Description <span class="text-danger">*</span></label>
                                <textarea name="description" id="description" rows="5" class="form-control rounded-2" required placeholder="Please describe the incident, timeline, individuals involved, and any steps already taken..."></textarea>
                                <div class="form-text small text-muted">Provide factual details to assist in rapid and fair investigation.</div>
                            </div>

                            <!-- Anonymous Submission Option -->
                            <div class="col-12">
                                <div class="form-check p-3 bg-light rounded-2 border">
                                    <input class="form-check-input ms-0 me-2" type="checkbox" name="is_anonymous" value="1" id="is_anonymous">
                                    <label class="form-check-label fw-semibold text-dark small" for="is_anonymous">
                                        <i class="fas fa-user-secret me-1 text-secondary"></i> Submit Anonymously
                                    </label>
                                    <p class="text-muted mb-0 small mt-1 ms-4">
                                        If checked, your name and employee code will remain hidden from the investigation team. However, please note that anonymous submissions may take longer to resolve if additional clarifying evidence is required.
                                    </p>
                                </div>
                            </div>

                            <!-- Submit Buttons -->
                            <div class="col-12 d-flex justify-content-end gap-2 pt-3 border-top">
                                <a href="<?= BASE_URL ?>/employee/self-service/grievances" class="btn btn-light px-4">Cancel</a>
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="fas fa-paper-plane me-1"></i> Submit Grievance
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
