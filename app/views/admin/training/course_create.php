<?php
/** Admin Training Course Create/Edit */

$page_title = $page_title ?? ($mode === 'edit' ? 'Edit Training Course' : 'Create Training Course');
$mode = $mode ?? 'create';
$course = $course ?? [];
$categories = $categories ?? [];
?>

<style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>"></style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold"><i class="fas fa-graduation-cap me-2 text-primary"></i><?= $page_title ?></h4>
            <p class="text-muted mb-0 small">Create or edit a training course with full details</p>
        </div>
        <a href="<?= BASE_URL ?>/admin/training/courses" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Courses
        </a>
    </div>

    <form method="POST" id="courseForm" class="needs-validation" novalidate>
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
        <?php if ($mode === 'edit'): ?>
            <input type="hidden" name="id" value="<?= $course['id'] ?>">
        <?php endif; ?>

        <!-- Basic Info -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0 fw-semibold"><i class="fas fa-info-circle me-2"></i>Course Information</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Course Code <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control" required value="<?= htmlspecialchars($course['code'] ?? '') ?>" placeholder="e.g., HR-ONB-001" <?= $mode === 'edit' ? 'readonly' : '' ?>>
                        <?php if ($mode === 'edit'): ?>
                            <div class="form-text">Code cannot be changed after creation</div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" required value="<?= htmlspecialchars($course['title'] ?? '') ?>" placeholder="e.g., New Employee Orientation">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Category</label>
                        <select name="category" class="form-select">
                            <?php foreach ($categories as $key => $label): ?>
                                <option value="<?= $key ?>" <?= ($course['category'] ?? '') === $key ? 'selected' : '' ?>><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Duration (hours)</label>
                        <input type="number" step="0.5" min="0" name="duration_hours" class="form-control" value="<?= htmlspecialchars($course['duration_hours'] ?? 0) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Format</label>
                        <select name="format" class="form-select">
                            <option value="online" <?= ($course['format'] ?? '') === 'online' ? 'selected' : '' ?>>Online</option>
                            <option value="offline" <?= ($course['format'] ?? '') === 'offline' ? 'selected' : '' ?>>Offline</option>
                            <option value="hybrid" <?= ($course['format'] ?? '') === 'hybrid' ? 'selected' : '' ?>>Hybrid</option>
                            <option value="self_paced" <?= ($course['format'] ?? '') === 'self_paced' ? 'selected' : '' ?>>Self-Paced</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Max Participants</label>
                        <input type="number" min="1" name="max_participants" class="form-control" value="<?= htmlspecialchars($course['max_participants'] ?? '') ?>" placeholder="Optional">
                    </div>
                </div>
            </div>
        </div>

        <!-- Description -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0 fw-semibold"><i class="fas fa-align-left me-2"></i>Description</h5>
            </div>
            <div class="card-body">
                <textarea name="description" class="form-control" rows="4" placeholder="Course description, learning objectives..."><?= htmlspecialchars($course['description'] ?? '') ?></textarea>
            </div>
        </div>

        <!-- Prerequisites & Materials -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0 fw-semibold"><i class="fas fa-book me-2"></i>Prerequisites & Materials</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold">Prerequisites</label>
                        <textarea name="prerequisites" class="form-control" rows="3" placeholder="Required knowledge, prior courses, or experience..."><?= htmlspecialchars($course['prerequisites'] ?? '') ?></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Instructor Name</label>
                        <input type="text" name="instructor_name" class="form-control" value="<?= htmlspecialchars($course['instructor_name'] ?? '') ?>" placeholder="e.g., John Smith">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Instructor Email</label>
                        <input type="email" name="instructor_email" class="form-control" value="<?= htmlspecialchars($course['instructor_email'] ?? '') ?>" placeholder="john.smith@company.com">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Material URL</label>
                        <input type="url" name="material_url" class="form-control" value="<?= htmlspecialchars($course['material_url'] ?? '') ?>" placeholder="https://... (course materials link)">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Certificate Template</label>
                        <input type="url" name="certificate_template" class="form-control" value="<?= htmlspecialchars($course['certificate_template'] ?? '') ?>" placeholder="Certificate template URL (optional)">
                    </div>
                </div>
            </div>
        </div>

        <!-- Assessment Settings -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0 fw-semibold"><i class="fas fa-clipboard-check me-2"></i>Assessment & Certification</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="form-check form-switch mt-3">
                            <input class="form-check-input" type="checkbox" name="assessment_required" value="1" id="assessment_required" <?= !empty($course['assessment_required']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="assessment_required">Assessment Required</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Passing Score (%)</label>
                        <input type="number" min="0" max="100" name="passing_score" class="form-control" value="<?= htmlspecialchars($course['passing_score'] ?? 70) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Valid For (months)</label>
                        <input type="number" min="1" max="120" name="valid_for_months" class="form-control" value="<?= htmlspecialchars($course['valid_for_months'] ?? 12) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Status</label>
                        <select name="is_active" class="form-select">
                            <option value="1" <?= ($course['is_active'] ?? 1) ? 'selected' : '' ?>>Active</option>
                            <option value="0" <?= empty($course['is_active']) ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="<?= BASE_URL ?>/admin/training/courses" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Cancel
            </a>
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="fas fa-save me-1"></i> Save Course
            </button>
        </div>
    </form>
</div>

<script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
document.getElementById('courseForm').addEventListener('submit', function(e) {
    if (!this.checkValidity()) {
        e.preventDefault();
        e.stopPropagation();
    }
    this.classList.add('was-validated');
});
</script>