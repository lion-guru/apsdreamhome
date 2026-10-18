<?php
/**
 * Employee Self-Service: Engagement Surveys List
 */
$page_title = $page_title ?? 'Engagement Surveys';
$surveys = $surveys ?? [];

$totalSurveys = count($surveys);
$completedCount = 0;
$pendingCount = 0;

foreach ($surveys as $s) {
    if (!empty($s['has_responded'])) {
        $completedCount++;
    } else {
        $pendingCount++;
    }
}
?>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="mb-1 fw-bold"><i class="fas fa-poll-h me-2 text-primary"></i>Engagement Surveys</h4>
            <p class="text-muted mb-0 small">Share your confidential feedback to help improve company culture and workplace experience</p>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($_SESSION['success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($_SESSION['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['info'])): ?>
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="fas fa-info-circle me-2"></i><?= htmlspecialchars($_SESSION['info']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['info']); ?>
    <?php endif; ?>

    <!-- KPI Metric Cards -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-primary bg-opacity-10 p-3 text-primary">
                        <i class="fas fa-clipboard-list fa-2x"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Available Surveys</div>
                        <h4 class="mb-0 fw-bold"><?= $totalSurveys ?></h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-warning bg-opacity-10 p-3 text-warning">
                        <i class="fas fa-hourglass-half fa-2x"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Pending Response</div>
                        <h4 class="mb-0 fw-bold text-warning"><?= $pendingCount ?></h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-success bg-opacity-10 p-3 text-success">
                        <i class="fas fa-check-double fa-2x"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Completed</div>
                        <h4 class="mb-0 fw-bold text-success"><?= $completedCount ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Surveys Grid / Table -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 fw-semibold"><i class="fas fa-list-check me-2 text-primary"></i>Active Surveys</h5>
        </div>
        <div class="card-body p-0">
            <?php if (empty($surveys)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-clipboard-check fa-3x text-muted mb-3 opacity-50"></i>
                    <h5 class="text-muted">No Active Surveys</h5>
                    <p class="text-muted small mb-0">There are no surveys available for you at this moment. Please check back later.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Survey</th>
                                <th>Type</th>
                                <th>Deadline</th>
                                <th>Privacy</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($surveys as $survey):
                                $hasResponded = !empty($survey['has_responded']);
                                $endDate = !empty($survey['end_date']) ? date('M d, Y', strtotime($survey['end_date'])) : 'No Expiry';
                                $typeLabel = ucfirst($survey['survey_type'] ?? 'pulse');
                            ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-semibold text-dark"><?= htmlspecialchars($survey['title']) ?></div>
                                    <?php if (!empty($survey['description'])): ?>
                                        <div class="text-muted small text-truncate" style="max-width: 320px;">
                                            <?= htmlspecialchars($survey['description']) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        <i class="fas fa-tag me-1 text-muted"></i><?= htmlspecialchars($typeLabel) ?>
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted"><i class="far fa-calendar-alt me-1"></i><?= $endDate ?></small>
                                </td>
                                <td>
                                    <?php if (!empty($survey['is_anonymous'])): ?>
                                        <span class="badge bg-secondary-subtle text-secondary border">
                                            <i class="fas fa-user-secret me-1"></i>Anonymous
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-info-subtle text-info border">
                                            <i class="fas fa-user-check me-1"></i>Attributed
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($hasResponded): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">
                                            <i class="fas fa-check-circle me-1"></i>Completed
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle">
                                            <i class="fas fa-clock me-1"></i>Pending
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-4">
                                    <?php if (!$hasResponded || !empty($survey['allow_multiple'])): ?>
                                        <a href="<?= BASE_URL ?>/employee/self-service/surveys/take/<?= (int)$survey['id'] ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-pen me-1"></i> Take Survey
                                        </a>
                                    <?php else: ?>
                                        <a href="<?= BASE_URL ?>/employee/self-service/surveys/results/<?= (int)$survey['id'] ?>" class="btn btn-sm btn-outline-secondary">
                                            <i class="fas fa-eye me-1"></i> View My Response
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
