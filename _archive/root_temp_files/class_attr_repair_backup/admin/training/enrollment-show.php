<?php
$page_title = 'Enrollment Details';
$page_description = 'View enrollment details';
$e = $enrollment ?? [];
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/admin/training/enrollments">Enrollments</a></li>
                    <li class="breadcrumb-item active">Enrollment Details</li>
                </ol>
            </nav>
            <h1 class="h3 mb-0">Enrollment Details</h1>
        </div>
    </div>

    <div class="row">
        <!-- User Info -->
        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-user me-2"></i>User Information</h5>
                </div>
                <div class="card-body aps-cp-card-body">
                    <div class="table-responsive"><table class="table table-sm">
                        <tr><th>Name</th><td><?php echo htmlspecialchars($e['user_name'] ?? '-'); ?></td></tr>
                        <tr><th>Email</th><td><?php echo htmlspecialchars($e['user_email'] ?? '-'); ?></td></tr>
                        <tr><th>Phone</th><td><?php echo htmlspecialchars($e['user_phone'] ?? '-'); ?></td></tr>
                    </table></div>
                </div>
            </div>
        </div>

        <!-- Course Info -->
        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-book me-2"></i>Course Information</h5>
                </div>
                <div class="card-body aps-cp-card-body">
                    <div class="table-responsive"><table class="table table-sm">
                        <tr><th>Course</th><td><?php echo htmlspecialchars($e['course_title'] ?? '-'); ?></td></tr>
                        <tr><th>Category</th><td><span class="badge bg-info"><?php echo ucfirst($e['course_category'] ?? '-'); ?></span></td></tr>
                        <tr><th>Difficulty</th><td><span class="badge bg-secondary"><?php echo ucfirst($e['difficulty_level'] ?? '-'); ?></span></td></tr>
                        <tr><th>Duration</th><td><?php echo $e['course_duration_hours'] ?? 0; ?> hrs</td></tr>
                        <tr><th>Mandatory</th><td><?php echo ($e['is_mandatory'] ?? 0) ? '<span class="badge bg-danger">Yes</span>' : '<span class="badge bg-secondary">No</span>'; ?></td></tr>
                    </table></div>
                </div>
            </div>
        </div>

        <!-- Progress -->
        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Progress</h5>
                </div>
                <div class="card-body aps-cp-card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Progress: <?php echo (int)($e['progress_percentage'] ?? 0); ?>%</label>
                        <div class="progress style-51309">
                            <div class="progress-bar <?php echo ($e['progress_percentage'] ?? 0) >= 100 ? 'bg-success' : 'bg-primary'; ?> progress-bar-striped progress-bar-animated"
                                 class="style-72116">
                                <?php echo (int)($e['progress_percentage'] ?? 0); ?>%
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive"><table class="table table-sm">
                        <tr><th>Status</th>
                            <td>
                                <span class="badge bg-<?php echo match($e['status'] ?? '') {
                                    'active' => 'success', 'completed' => 'info', 'dropped' => 'danger',
                                    'expired' => 'warning', 'suspended' => 'secondary', default => 'secondary'
                                }; ?>"><?php echo ucfirst($e['status'] ?? 'unknown'); ?></span>
                            </td>
                        </tr>
                        <tr><th>Final Score</th><td><?php echo $e['final_score'] !== null ? $e['final_score'] . '%' : '-'; ?></td></tr>
                        <tr><th>Passing Score</th><td><?php echo $e['passing_score_percentage'] ?? 0; ?>%</td></tr>
                        <tr><th>Attempts</th><td><?php echo $e['attempts_count'] ?? 0; ?></td></tr>
                        <tr><th>Enrolled At</th><td><?php echo $e['enrolled_at'] ?? '-'; ?></td></tr>
                        <tr><th>Completed At</th><td><?php echo $e['completed_at'] ?? '-'; ?></td></tr>
                        <tr><th>Last Accessed</th><td><?php echo $e['last_accessed_at'] ?? '-'; ?></td></tr>
                    </table></div>
                </div>
            </div>
        </div>

        <!-- Certificate Info -->
        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-certificate me-2"></i>Certificate Information</h5>
                </div>
                <div class="card-body aps-cp-card-body">
                    <div class="table-responsive"><table class="table table-sm">
                        <tr><th>Certificate Issued</th>
                            <td><?php echo ($e['certificate_issued'] ?? 0) ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>'; ?></td>
                        </tr>
                        <tr><th>Certificate URL</th>
                            <td>
                                <?php if (!empty($e['certificate_url'])): ?>
                                <a href="<?php echo htmlspecialchars($e['certificate_url'] ?? ''); ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-external-link-alt me-1"></i>View Certificate
                                </a>
                                <?php else: ?>
                                <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table></div>
                </div>
            </div>
        </div>

        <!-- Notes -->
        <div class="col-12 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-sticky-note me-2"></i>Notes</h5>
                </div>
                <div class="card-body aps-cp-card-body">
                    <p class="mb-0"><?php echo nl2br(htmlspecialchars($e['notes'] ?? 'No notes available.')); ?></p>
                </div>
            </div>
        </div>

        <div class="col-12">
            <a href="<?php echo BASE_URL; ?>/admin/training/enrollments" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back to Enrollments
            </a>
        </div>
    </div>
</div>
