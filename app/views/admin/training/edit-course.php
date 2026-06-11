<?php
$page_title = 'Edit Course';
$page_description = 'Edit training course';
$course = $course ?? [];
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/admin/training/courses">Courses</a></li>
                    <li class="breadcrumb-item active">Edit Course</li>
                </ol>
            </nav>
            <h1 class="h3 mb-0">Edit Training Course</h1>
            <p class="text-muted">Update course details</p>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-8">
            <form action="<?php echo BASE_URL; ?>/admin/training/courses/update/<?php echo $course['id'] ?? 0; ?>" method="POST" class="card border-0 shadow-sm">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                <div class="card-body aps-cp-card-body">
                    <div class="mb-3">
                        <label class="form-label">Course Title <span class="text-danger">*</span></label>
                        <input type="text" name="course_title" class="form-control" value="<?php echo htmlspecialchars($course['course_title'] ?? ''); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="course_description" class="form-control" rows="4"><?php echo htmlspecialchars($course['course_description'] ?? ''); ?></textarea>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Category</label>
                            <select name="course_category" class="form-select">
                                <?php $cat = $course['course_category'] ?? 'sales'; ?>
                                <option value="sales" <?php echo $cat == 'sales' ? 'selected' : ''; ?>>Sales</option>
                                <option value="product" <?php echo $cat == 'product' ? 'selected' : ''; ?>>Product</option>
                                <option value="compliance" <?php echo $cat == 'compliance' ? 'selected' : ''; ?>>Compliance</option>
                                <option value="leadership" <?php echo $cat == 'leadership' ? 'selected' : ''; ?>>Leadership</option>
                                <option value="technical" <?php echo $cat == 'technical' ? 'selected' : ''; ?>>Technical</option>
                                <option value="business" <?php echo $cat == 'business' ? 'selected' : ''; ?>>Business</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Difficulty Level</label>
                            <select name="difficulty_level" class="form-select">
                                <?php $diff = $course['difficulty_level'] ?? 'beginner'; ?>
                                <option value="beginner" <?php echo $diff == 'beginner' ? 'selected' : ''; ?>>Beginner</option>
                                <option value="intermediate" <?php echo $diff == 'intermediate' ? 'selected' : ''; ?>>Intermediate</option>
                                <option value="advanced" <?php echo $diff == 'advanced' ? 'selected' : ''; ?>>Advanced</option>
                                <option value="expert" <?php echo $diff == 'expert' ? 'selected' : ''; ?>>Expert</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Duration (Hours)</label>
                            <input type="number" name="course_duration_hours" class="form-control" step="0.5" min="0" value="<?php echo htmlspecialchars($course['course_duration_hours'] ?? '0'); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Max Enrollments</label>
                            <input type="number" name="max_enrollments" class="form-control" min="0" value="<?php echo htmlspecialchars($course['max_enrollments'] ?? '0'); ?>" placeholder="0 = unlimited">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Course Objectives</label>
                        <textarea name="course_objectives" class="form-control" rows="3"><?php echo htmlspecialchars($course['course_objectives'] ?? ''); ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Prerequisites</label>
                        <textarea name="prerequisites" class="form-control" rows="2"><?php echo htmlspecialchars($course['prerequisites'] ?? ''); ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Target Audience</label>
                        <textarea name="target_audience" class="form-control" rows="2"><?php echo htmlspecialchars($course['target_audience'] ?? ''); ?></textarea>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Passing Score (%)</label>
                            <input type="number" name="passing_score_percentage" class="form-control" min="0" max="100" value="<?php echo htmlspecialchars($course['passing_score_percentage'] ?? '70'); ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Points Reward</label>
                            <input type="number" name="points_reward" class="form-control" min="0" value="<?php echo htmlspecialchars($course['points_reward'] ?? '0'); ?>">
                        </div>
                        <div class="col-md-4 d-flex align-items-end gap-3">
                            <div class="form-check">
                                <input type="checkbox" name="is_mandatory" class="form-check-input" id="isMandatory" value="1" <?php echo ($course['is_mandatory'] ?? 0) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="isMandatory">Mandatory</label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" name="is_active" class="form-check-input" id="isActive" value="1" <?php echo ($course['is_active'] ?? 0) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="isActive">Active</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white border-0 d-flex justify-content-end gap-2">
                    <a href="<?php echo BASE_URL; ?>/admin/training/courses" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Update Course</button>
                </div>
            </form>
        </div>
    </div>
</div>
