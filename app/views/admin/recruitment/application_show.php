<?php
/** Admin Recruitment - Show Application */

$page_title = $page_title ?? 'Application: ' . ($application['application_number'] ?? 'Unknown');
$application = $application ?? [];
$job = $job ?? [];
$interviews = $interviews ?? [];
$offer = $offer ?? null;
?>

<style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
.app-row { transition: background 0.15s; }
.app-row:hover { background: #fefcf6; }
.status-badge { font-size: 0.7rem; padding: 0.2rem 0.5rem; border-radius: 999px; }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold"><i class="fas fa-user me-2 text-primary"></i><?= htmlspecialchars($application['candidate_name'] ?? 'Unknown') ?></h4>
            <p class="text-muted mb-0 small"><?= htmlspecialchars($application['application_number'] ?? '') ?> • <?= htmlspecialchars($job['title'] ?? '') ?></p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>/admin/recruitment/<?= $job['id'] ?>/applications" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Applications
            </a>
        </div>
    </div>

    <!-- Application Header -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-semibold">Application Overview</h5>
            <div class="d-flex gap-2">
                <?php if (in_array($application['status'], ['applied', 'screening', 'shortlisted'])): ?>
                    <a href="<?= BASE_URL ?>/admin/recruitment/applications/<?= $application['id'] ?>/schedule" class="btn btn-primary" onclick="return confirm('Schedule interview?')"><i class="fas fa-calendar-plus me-1"></i> Schedule Interview</a>
                <?php endif; ?>
                <?php if ($application['status'] === 'interviewed'): ?>
                    <a href="<?= BASE_URL ?>/admin/recruitment/applications/<?= $application['id'] ?>/offer" class="btn btn-success"><i class="fas fa-file-contract me-1"></i> Create Offer</a>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-body">
            <div class="row g-3 mb-3">
                <div class="col-md-3">
                    <small class="text-muted">Application #</small>
                    <div class="fw-semibold"><code><?= htmlspecialchars($application['application_number']) ?></code></div>
                </div>
                <div class="col-md-3">
                    <small class="text-muted">Candidate</small>
                    <div class="fw-semibold"><?= htmlspecialchars($application['candidate_name']) ?></div>
                </div>
                <div class="col-md-3">
                    <small class="text-muted">Email</small>
                    <div class="text-muted"><?= htmlspecialchars($application['email']) ?></div>
                </div>
                <div class="col-md-3">
                    <small class="text-muted">Phone</small>
                    <div class="fw-semibold"><?= htmlspecialchars($application['phone'] ?? '—') ?></div>
                </div>
            </div>
            <div class="row g-3">
                <div class="col-md-3">
                    <small class="text-muted">Current CTC</small>
                    <div class="fw-semibold"><?= $application['current_ctc'] ? '₹' . number_format($application['current_ctc']) : '—' ?></div>
                </div>
                <div class="col-md-3">
                    <small class="text-muted">Expected CTC</small>
                    <div class="fw-semibold"><?= $application['expected_ctc'] ? '₹' . number_format($application['expected_ctc']) : '—' ?></div>
                </div>
                <div class="col-md-3">
                    <small class="text-muted">Notice Period</small>
                    <div class="fw-semibold"><?= $application['notice_period_days'] ?? 0 ?> days</div>
                </div>
                <div class="col-md-3">
                    <small class="text-muted">Source</small>
                    <div class="fw-semibold"><?= ucfirst($application['source'] ?? 'careers_page') ?></div>
                </div>
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <small class="text-muted">Current Employer</small>
                    <div class="fw-semibold"><?= htmlspecialchars($application['current_employer'] ?? '—') ?></div>
                </div>
                <div class="col-md-6">
                    <small class="text-muted">Current Designation</small>
                    <div class="fw-semibold"><?= htmlspecialchars($application['current_designation'] ?? '—') ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Status & Progress -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-semibold"><i class="fas fa-flag me-2"></i>Application Status</h5>
            <span class="badge bg-<?= 
                $application['status'] === 'hired' ? 'success' : 
                ($application['status'] === 'offer_accepted' ? 'success' : 
                ($application['status'] === 'offer_extended' ? 'success' : 
                ($application['status'] === 'interviewed' ? 'info' : 
                ($application['status'] === 'interview_scheduled' ? 'warning' : 
                ($application['status'] === 'shortlisted' ? 'primary' : 
                ($application['status'] === 'screening' ? 'info' : 
                ($application['status'] === 'rejected' ? 'danger' : 'secondary'))))))) ?> status-badge fs-6">
                <?= ucfirst(str_replace('_', ' ', $application['status'])) ?>
            </span>
        </div>
        <div class="card-body">
            <div class="progress" style="height: 8px;">
                <div class="progress-bar bg-primary" role="progressbar" style="width: 
                    <?= match($application['status']) {
                        'applied' => '10%',
                        'screening' => '20%',
                        'shortlisted' => '35%',
                        'interview_scheduled' => '50%',
                        'interviewed' => '65%',
                        'offer_extended' => '80%',
                        'offer_accepted' => '90%',
                        'hired' => '100%',
                        default => '5%',
                    } ?>" aria-valuenow="<?= match($application['status']) {
                        'applied' => 10,
                        'screening' => 20,
                        'shortlisted' => 35,
                        'interview_scheduled' => 50,
                        'interviewed' => 65,
                        'offer_extended' => 80,
                        'offer_accepted' => 90,
                        'hired' => 100,
                        default => 5,
                    } ?>" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
        </div>
    </div>

    <!-- Application Details -->
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0 fw-semibold"><i class="fas fa-user me-2"></i>Candidate Details</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <small class="text-muted">Total Experience</small>
                            <div class="fw-semibold"><?= $application['total_experience_years'] ?? 0 ?> years</div>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted">Relevant Experience</small>
                            <div class="fw-semibold"><?= $application['relevant_experience_years'] ?? 0 ?> years</div>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted">Highest Qualification</small>
                            <div class="fw-semibold"><?= htmlspecialchars($application['highest_qualification'] ?? '—') ?></div>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted">Current Location</small>
                            <div class="fw-semibold"><?= htmlspecialchars($application['current_location'] ?? '—') ?></div>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted">Preferred Location</small>
                            <div class="fw-semibold"><?= htmlspecialchars($application['preferred_location'] ?? '—') ?></div>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted">LinkedIn</small>
                            <div>
                                <?php if (!empty($application['linkedin_url'])): ?>
                                    <a href="<?= htmlspecialchars($application['linkedin_url']) ?>" target="_blank" class="btn btn-sm btn-outline-primary"><i class="fab fa-linkedin me-1"></i> Profile</a>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted">Portfolio</small>
                            <div>
                                <?php if (!empty($application['portfolio_url'])): ?>
                                    <a href="<?= htmlspecialchars($application['portfolio_url']) ?>" target="_blank" class="btn btn-sm btn-outline-info"><i class="fas fa-globe me-1"></i> View</a>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0 fw-semibold"><i class="fas fa-file-alt me-2"></i>Documents</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <small class="text-muted">Resume</small>
                            <div>
                                <?php if (!empty($application['resume_path'])): ?>
                                    <a href="<?= BASE_URL . '/' . $application['resume_path'] ?>" target="_blank" class="btn btn-sm btn-outline-primary"><i class="fas fa-file-alt me-1"></i> View Resume</a>
                                <?php else: ?>
                                    <span class="text-muted">Not uploaded</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted">Cover Letter</small>
                            <div>
                                <?php if (!empty($application['cover_letter'])): ?>
                                    <a href="#" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#coverLetterModal"><i class="fas fa-file-text me-1"></i> View Cover Letter</a>
                                <?php else: ?>
                                    <span class="text-muted">Not provided</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Interviews -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-semibold"><i class="fas fa-calendar-alt me-2"></i>Interviews (<?= count($interviews) ?>)</h5>
            <?php if ($application['status'] === 'shortlisted' || $application['status'] === 'interview_scheduled'): ?>
                <a href="<?= BASE_URL ?>/admin/recruitment/applications/<?= $application['id'] ?>/schedule" class="btn btn-sm btn-primary"><i class="fas fa-calendar-plus me-1"></i> Schedule</a>
            <?php endif; ?>
        </div>
        <div class="card-body p-0">
            <?php if (empty($interviews)): ?>
            <div class="text-center py-4 text-muted">No interviews scheduled</div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 100px">Round</th>
                            <th>Type</th>
                            <th style="width: 150px">Interviewer</th>
                            <th style="width: 150px">Scheduled</th>
                            <th style="width: 100px">Status</th>
                            <th style="width: 100px">Feedback</th>
                            <th style="width: 120px">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($interviews as $interview): 
                            $statusClass = [
                                'scheduled' => 'warning',
                                'confirmed' => 'info',
                                'completed' => 'success',
                                'cancelled' => 'danger',
                                'no_show' => 'danger',
                            ][$interview['status']] ?? 'secondary';
                        ?>
                        <tr class="app-row">
                            <td><?= $interview['round_number'] ?></td>
                            <td>
                                <span class="badge bg-<?= 
                                    $interview['round_type'] === 'technical' ? 'primary' : 
                                    ($interview['round_type'] === 'hr' ? 'success' : 
                                    ($interview['round_type'] === 'managerial' ? 'warning' : 'info')) ?>">
                                    <?= ucfirst($interview['round_type']) ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($interview['interviewer_name'] ?? '—') ?></td>
                            <td><?= date('d M Y H:i', strtotime($interview['scheduled_at'])) ?></td>
                            <td><span class="badge bg-<?= $statusClass ?>"><?= ucfirst($interview['status']) ?></span></td>
                            <td>
                                <?php if ($interview['status'] === 'completed'): ?>
                                    <span class="badge bg-<?= 
                                        $interview['recommendation'] === 'strong_hire' ? 'success' : 
                                        ($interview['recommendation'] === 'hire' ? 'success' : 
                                        ($interview['recommendation'] === 'no_hire' ? 'danger' : 'warning')) ?>">
                                        <?= ucfirst(str_replace('_', ' ', $interview['recommendation'] ?? '—')) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= BASE_URL ?>/admin/recruitment/interviews/<?= $interview['id'] ?>" class="btn btn-outline-primary" title="View"><i class="fas fa-eye"></i></a>
                                    <?php if ($interview['status'] === 'scheduled'): ?>
                                        <a href="<?= BASE_URL ?>/admin/recruitment/interviews/<?= $interview['id'] ?>/feedback" class="btn btn-outline-success" title="Submit Feedback"><i class="fas fa-comment"></i></a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- Offer Letter -->
    <?php if ($offer): ?>
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-semibold"><i class="fas fa-file-contract me-2"></i>Offer Letter</h5>
        </div>
        <div class="card-body">
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <small class="text-muted">Offer #</small>
                    <div class="fw-semibold"><?= htmlspecialchars($offer['offer_number']) ?></div>
                </div>
                <div class="col-md-4">
                    <small class="text-muted">CTC</small>
                    <div class="fw-semibold text-success">₹<?= number_format($offer['ctc_annual']) ?> /yr</div>
                </div>
                <div class="col-md-4">
                    <small class="text-muted">Status</small>
                    <div class="fw-semibold">
                        <span class="badge bg-<?= 
                            $offer['status'] === 'accepted' ? 'success' : 
                            ($offer['status'] === 'sent' ? 'info' : 
                            ($offer['status'] === 'declined' ? 'danger' : 'warning')) ?>">
                            <?= ucfirst($offer['status']) ?>
                        </span>
                    </div>
                </div>
            </div>
            <div class="d-flex gap-2">
                <?php if ($offer['status'] === 'draft'): ?>
                    <a href="<?= BASE_URL ?>/admin/recruitment/offers/<?= $offer['id'] ?>/send" class="btn btn-primary" onclick="return confirm('Send this offer?')"><i class="fas fa-paper-plane me-1"></i> Send Offer</a>
                <?php elseif ($offer['status'] === 'sent'): ?>
                    <a href="<?= BASE_URL ?>/admin/recruitment/offers/<?= $offer['id'] ?>" class="btn btn-outline-primary"><i class="fas fa-eye me-1"></i> View</a>
                <?php endif; ?>
                <?php if ($offer['status'] === 'accepted'): ?>
                    <span class="badge bg-success fs-6">Offer Accepted</span>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>