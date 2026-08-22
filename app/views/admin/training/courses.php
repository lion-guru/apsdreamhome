<?php
$page_title = 'Training Courses';
$page_description = 'Manage training courses';
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-0">Training Courses</h1>
                <p class="text-muted">Manage training courses and content</p>
            </div>
            <a href="<?php echo BASE_URL; ?>/admin/training/courses/create" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i>Create Course
            </a>
        </div>
    </div>

    <!-- Stats -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-primary bg-opacity-10 text-primary rounded p-3">
                                <i class="fas fa-book fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Total Courses</h6>
                            <h3 class="mb-0"><?php echo $totalCourses ?? 0; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-success bg-opacity-10 text-success rounded p-3">
                                <i class="fas fa-check-circle fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Active</h6>
                            <h3 class="mb-0"><?php echo $activeCourses ?? 0; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-warning bg-opacity-10 text-warning rounded p-3">
                                <i class="fas fa-gavel fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Mandatory</h6>
                            <h3 class="mb-0"><?php echo $mandatoryCourses ?? 0; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-info bg-opacity-10 text-info rounded p-3">
                                <i class="fas fa-users fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Enrollments</h6>
                            <h3 class="mb-0">
                                <?php
                                $totalEnrolled = 0;
                                if (!empty($courses)) {
                                    foreach ($courses as $c) { $totalEnrolled += (int)($c['current_enrollments'] ?? 0); }
                                }
                                echo $totalEnrolled;
                                ?>
                            </h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Course Cards -->
    <?php if (empty($courses)): ?>
    <div class="alert alert-info">
        <i class="fas fa-info-circle me-2"></i>No courses found. <a href="<?php echo BASE_URL; ?>/admin/training/courses/create" class="alert-link">Create your first course</a>.
    </div>
    <?php else: ?>
    <div class="row">
        <?php foreach ($courses as $course): ?>
        <div class="col-md-6 col-xl-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <span class="badge bg-<?php echo match($course['course_category'] ?? 'sales') {
                                'sales' => 'success',
                                'product' => 'info',
                                'compliance' => 'danger',
                                'leadership' => 'warning',
                                'technical' => 'primary',
                                'business' => 'secondary',
                                default => 'secondary'
                            }; ?> me-1">
                                <?php echo ucfirst($course['course_category'] ?? 'sales'); ?>
                            </span>
                            <span class="badge bg-<?php echo match($course['difficulty_level'] ?? 'beginner') {
                                'beginner' => 'success',
                                'intermediate' => 'warning',
                                'advanced' => 'danger',
                                'expert' => 'dark',
                                default => 'secondary'
                            }; ?>">
                                <?php echo ucfirst($course['difficulty_level'] ?? 'beginner'); ?>
                            </span>
                        </div>
                        <?php if ($course['is_mandatory'] ?? 0): ?>
                        <span class="badge bg-danger"><i class="fas fa-star me-1"></i>Mandatory</span>
                        <?php endif; ?>
                    </div>
                    <h5 class="card-title mb-1"><?php echo htmlspecialchars($course['course_title'] ?? ''); ?></h5>
                    <p class="card-text text-muted small mb-2">
                        <?php echo htmlspecialchars(substr($course['course_description'] ?? '', 0, 120)); ?>
                        <?php if (strlen($course['course_description'] ?? '') > 120): ?>...<?php endif; ?>
                    </p>
                    <div class="d-flex justify-content-between small text-muted mb-3">
                        <span><i class="fas fa-clock me-1"></i><?php echo $course['course_duration_hours'] ?? 0; ?> hrs</span>
                        <span>
                            <i class="fas fa-user-graduate me-1"></i>
                            <?php echo ($course['current_enrollments'] ?? 0); ?> / <?php echo ($course['max_enrollments'] ?? 0) ?: '∞'; ?> enrolled
                        </span>
                    </div>
                    <?php if (($course['is_active'] ?? 0)): ?>
                    <span class="badge bg-success">Active</span>
                    <?php else: ?>
                    <span class="badge bg-secondary">Inactive</span>
                    <?php endif; ?>
                </div>
                <div class="card-footer bg-white border-0 d-flex justify-content-end gap-2">
                    <a href="<?php echo BASE_URL; ?>/admin/training/courses/edit/<?php echo e($course['id']); ?>" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-edit me-1"></i>Edit
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
