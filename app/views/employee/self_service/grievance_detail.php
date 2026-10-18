<?php
/**
 * Employee Self-Service - Grievance Detail View
 */

$page_title = $page_title ?? 'Grievance Detail';
$grievance = $grievance ?? [];
$comments = $comments ?? [];
?>

<style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
.timeline-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    margin-top: 6px;
}
.comment-bubble {
    border-radius: 12px;
    border: 1px solid #e2e8f0;
}
.comment-mine {
    background-color: #f0fdf4;
    border-color: #bbf7d0;
}
.comment-support {
    background-color: #f8fafc;
    border-color: #e2e8f0;
}
</style>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-9">
            <!-- Header -->
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <h4 class="mb-0 fw-bold text-dark">
                            <i class="fas fa-file-alt me-2 text-primary"></i>Grievance #<?= htmlspecialchars($grievance['grievance_number'] ?? 'N/A') ?>
                        </h4>
                        <span class="badge bg-secondary"><?= ucwords(str_replace('_', ' ', $grievance['status'] ?? 'open')) ?></span>
                    </div>
                    <p class="text-muted mb-0 small">Submitted on <?= date('d M Y, h:i A', strtotime($grievance['created_at'] ?? 'now')) ?></p>
                </div>
                <div class="d-flex gap-2">
                    <a href="<?= BASE_URL ?>/employee/self-service/grievances" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i> Back to List
                    </a>
                    <?php if (in_array($grievance['status'] ?? '', ['resolved', 'under_review', 'investigation', 'mediation'])): ?>
                        <a href="<?= BASE_URL ?>/employee/self-service/grievances/<?= $grievance['id'] ?>/close" class="btn btn-success btn-sm">
                            <i class="fas fa-check-circle me-1"></i> Close Grievance
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Flash Messages -->
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-4" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show rounded-3 mb-4" role="alert">
                    <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Resolution Card if Resolved -->
            <?php if (!empty($grievance['resolution'])): ?>
                <div class="card border-0 shadow-sm rounded-3 mb-4 border-start border-4 border-success bg-white">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <h6 class="fw-bold text-success mb-0">
                                <i class="fas fa-check-double me-2"></i>Official Resolution
                            </h6>
                            <span class="text-muted small">
                                Resolved at <?= !empty($grievance['resolved_at']) ? date('d M Y, h:i A', strtotime($grievance['resolved_at'])) : '-' ?>
                            </span>
                        </div>
                        <div class="p-3 bg-light rounded-2 text-dark">
                            <?= nl2br(htmlspecialchars($grievance['resolution'])) ?>
                        </div>
                        <?php if ($grievance['status'] === 'resolved'): ?>
                            <div class="mt-3 text-end">
                                <a href="<?= BASE_URL ?>/employee/self-service/grievances/<?= $grievance['id'] ?>/close" class="btn btn-success btn-sm">
                                    <i class="fas fa-star me-1"></i> Accept Resolution & Close
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Grievance Information Card -->
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-info-circle text-primary me-2"></i><?= htmlspecialchars($grievance['subject'] ?? 'No Subject') ?>
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <span class="text-muted small d-block">Category</span>
                            <span class="fw-semibold text-dark"><?= htmlspecialchars(ucwords(str_replace('_', ' ', $grievance['category'] ?? 'other'))) ?></span>
                        </div>
                        <div class="col-md-3">
                            <span class="text-muted small d-block">Priority</span>
                            <span class="badge bg-<?= in_array($grievance['priority'] ?? '', ['urgent', 'critical']) ? 'danger' : 'primary' ?>">
                                <?= ucfirst($grievance['priority'] ?? 'normal') ?>
                            </span>
                        </div>
                        <div class="col-md-3">
                            <span class="text-muted small d-block">Assigned Investigator</span>
                            <span class="fw-semibold text-dark"><?= htmlspecialchars($grievance['assignee_name'] ?? 'HR Desk (In Review)') ?></span>
                        </div>
                        <div class="col-md-3">
                            <span class="text-muted small d-block">SLA Window</span>
                            <span class="fw-semibold text-dark"><?= (int)($grievance['sla_hours'] ?? 24) ?> hours</span>
                        </div>
                    </div>
                    <hr>
                    <div>
                        <span class="text-muted small d-block mb-2">Description</span>
                        <div class="p-3 bg-light rounded-2 text-dark">
                            <?= nl2br(htmlspecialchars($grievance['description'] ?? '')) ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Comments & Discussion Thread -->
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-comments text-primary me-2"></i>Communication Thread
                    </h6>
                    <span class="badge bg-light text-secondary border"><?= count($comments) ?> note<?= count($comments) === 1 ? '' : 's' ?></span>
                </div>
                <div class="card-body p-4">
                    <?php if (empty($comments)): ?>
                        <p class="text-muted small text-center py-3 mb-0">No updates or replies yet. You may post additional information below.</p>
                    <?php else: ?>
                        <div class="d-flex flex-column gap-3 mb-4">
                            <?php foreach ($comments as $c): ?>
                                <?php if (!empty($c['is_internal'])) continue; // Employee cannot see internal notes ?>
                                <div class="comment-bubble p-3 <?= ($c['employee_id'] == ($grievance['employee_id'] ?? 0)) ? 'comment-mine' : 'comment-support' ?>">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="fw-bold small text-dark">
                                            <i class="fas fa-user-circle me-1 text-secondary"></i>
                                            <?= htmlspecialchars($c['author_name'] ?? 'Investigator') ?>
                                            <span class="badge bg-light text-secondary ms-1"><?= ucfirst($c['author_role'] ?? 'Staff') ?></span>
                                        </span>
                                        <span class="text-muted" style="font-size: 0.75rem;">
                                            <?= date('d M Y, h:i A', strtotime($c['created_at'])) ?>
                                        </span>
                                    </div>
                                    <div class="small text-dark mt-1">
                                        <?= nl2br(htmlspecialchars($c['comment'])) ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Add Comment Form -->
                    <?php if (!in_array($grievance['status'] ?? '', ['closed'])): ?>
                        <form method="POST" action="<?= BASE_URL ?>/employee/self-service/grievances/<?= $grievance['id'] ?>/comment">
                            <div class="mb-3">
                                <label for="comment" class="form-label fw-semibold small text-dark">Add Clarification / Response</label>
                                <textarea name="comment" id="comment" rows="3" class="form-control rounded-2" required placeholder="Type your response or additional context here..."></textarea>
                            </div>
                            <div class="text-end">
                                <button type="submit" class="btn btn-primary btn-sm px-3">
                                    <i class="fas fa-paper-plane me-1"></i> Send Reply
                                </button>
                            </div>
                        </form>
                    <?php else: ?>
                        <div class="p-3 bg-light rounded-2 text-center text-muted small">
                            <i class="fas fa-lock me-1"></i> This grievance has been officially closed. No further comments can be posted.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
