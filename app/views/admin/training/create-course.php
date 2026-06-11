<?php
$page_title = 'Create Training Course';
$page_description = 'Create a new training course';
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/admin/training/courses">Courses</a></li>
                    <li class="breadcrumb-item active">Create Course</li>
                </ol>
            </nav>
            <h1 class="h3 mb-0">Create Training Course</h1>
            <p class="text-muted">Add a new training course for your team</p>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-8">
            <form action="<?php echo BASE_URL; ?>/admin/training/courses/store" method="POST" class="card border-0 shadow-sm">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                <div class="card-body aps-cp-card-body">
                    <div class="mb-3">
                        <label class="form-label">Course Title <span class="text-danger">*</span></label>
                        <input type="text" name="course_title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="course_description" class="form-control" rows="4"></textarea>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Category</label>
                            <select name="course_category" class="form-select">
                                <option value="sales">Sales</option>
                                <option value="product">Product</option>
                                <option value="compliance">Compliance</option>
                                <option value="leadership">Leadership</option>
                                <option value="technical">Technical</option>
                                <option value="business">Business</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Difficulty Level</label>
                            <select name="difficulty_level" class="form-select">
                                <option value="beginner">Beginner</option>
                                <option value="intermediate">Intermediate</option>
                                <option value="advanced">Advanced</option>
                                <option value="expert">Expert</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Duration (Hours)</label>
                            <input type="number" name="course_duration_hours" class="form-control" step="0.5" min="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Max Enrollments</label>
                            <input type="number" name="max_enrollments" class="form-control" min="0" placeholder="0 = unlimited">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Course Objectives</label>
                        <textarea name="course_objectives" class="form-control" rows="3" placeholder="List key learning objectives"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Prerequisites</label>
                        <textarea name="prerequisites" class="form-control" rows="2" placeholder="Any prerequisites for this course"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Target Audience</label>
                        <textarea name="target_audience" class="form-control" rows="2" placeholder="Who should take this course"></textarea>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Passing Score (%)</label>
                            <input type="number" name="passing_score_percentage" class="form-control" min="0" max="100" value="70">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Points Reward</label>
                            <input type="number" name="points_reward" class="form-control" min="0" value="0">
                        </div>
                        <div class="col-md-4 d-flex align-items-end gap-3">
                            <div class="form-check">
                                <input type="checkbox" name="is_mandatory" class="form-check-input" id="isMandatory" value="1">
                                <label class="form-check-label" for="isMandatory">Mandatory</label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" name="is_active" class="form-check-input" id="isActive" value="1" checked>
                                <label class="form-check-label" for="isActive">Active</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white border-0 d-flex justify-content-end gap-2">
                    <a href="<?php echo BASE_URL; ?>/admin/training/courses" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save Course</button>
                </div>
            </form>
        </div>
    </div>
</div>
