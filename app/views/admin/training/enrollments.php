<?php
$page_title = 'Training Enrollments';
$page_description = 'Manage course enrollments';
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-0">Training Enrollments</h1>
            <p class="text-muted">View and manage all course enrollments</p>
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
                                <i class="fas fa-file-invoice fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Total Enrollments</h6>
                            <h3 class="mb-0"><?php echo $totalEnrollments ?? 0; ?></h3>
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
                                <i class="fas fa-play-circle fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Active</h6>
                            <h3 class="mb-0"><?php echo $activeEnrollments ?? 0; ?></h3>
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
                                <i class="fas fa-check-double fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Completed</h6>
                            <h3 class="mb-0"><?php echo $completedEnrollments ?? 0; ?></h3>
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
                            <div class="bg-danger bg-opacity-10 text-danger rounded p-3">
                                <i class="fas fa-times-circle fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Dropped</h6>
                            <h3 class="mb-0"><?php echo $droppedEnrollments ?? 0; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Enrollments Table -->
    <?php if (empty($enrollments)): ?>
    <div class="alert alert-info"><i class="fas fa-info-circle me-2"></i>No enrollments found.</div>
    <?php else: ?>
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>User</th>
                        <th>Course</th>
                        <th>Progress</th>
                        <th>Status</th>
                        <th>Enrolled</th>
                        <th>Completed</th>
                        <th>Score</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($enrollments as $e): ?>
                    <tr>
                        <td>
                            <div class="fw-semibold"><?php echo htmlspecialchars($e['user_name'] ?? ''); ?></div>
                            <small class="text-muted"><?php echo htmlspecialchars($e['user_email'] ?? ''); ?></small>
                        </td>
                        <td><?php echo htmlspecialchars($e['course_title'] ?? ''); ?></td>
                        <td class="style-74978">
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress flex-grow-1" class="style-31164">
                                    <div class="progress-bar <?php echo ($e['progress_percentage'] ?? 0) >= 100 ? 'bg-success' : 'bg-primary'; ?>" 
                                         class="style-72116"></div>
                                </div>
                                <small class="text-muted"><?php echo (int)($e['progress_percentage'] ?? 0); ?>%</small>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-<?php echo match($e['status'] ?? '') {
                                'active' => 'success',
                                'completed' => 'info',
                                'dropped' => 'danger',
                                'expired' => 'warning',
                                'suspended' => 'secondary',
                                default => 'secondary'
                            }; ?>">
                                <?php echo ucfirst($e['status'] ?? 'unknown'); ?>
                            </span>
                        </td>
                        <td><small><?php echo $e['enrolled_at'] ?? '-'; ?></small></td>
                        <td><small><?php echo $e['completed_at'] ?? '-'; ?></small></td>
                        <td><?php echo $e['final_score'] !== null ? $e['final_score'] . '%' : '-'; ?></td>
                        <td>
                            <a href="<?php echo BASE_URL; ?>/admin/training/enrollments/<?php echo $e['id']; ?>" class="btn btn-sm btn-outline-info">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>
