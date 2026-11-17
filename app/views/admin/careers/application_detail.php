<?php
$page_title = $page_title ?? 'Application Details - APS Dream Home';
$application = $application ?? [];
$history = $history ?? [];
$status = $application['status'] ?? 'pending';
$badgeClass = match($status) {
    'shortlisted' => 'bg-success',
    'reviewing'   => 'bg-primary',
    'hired'       => 'bg-info text-dark',
    'rejected'    => 'bg-danger',
    default       => 'bg-warning text-dark'
};
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-file-alt text-primary me-2"></i>Application Details</h1>
        <a href="<?= BASE_URL ?>/admin/careers/manage/applications" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back to Applications
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Applicant Information</h6>
                    <span class="badge <?= $badgeClass ?>"><?= ucfirst($status) ?></span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block">Full Name</small>
                            <strong><?= htmlspecialchars($application['full_name'] ?? '-') ?></strong>
                        </div>
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block">Position</small>
                            <strong><?= htmlspecialchars($application['position'] ?? '-') ?></strong>
                        </div>
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block">Email</small>
                            <a href="mailto:<?= htmlspecialchars($application['email'] ?? '') ?>"><?= htmlspecialchars($application['email'] ?? '-') ?></a>
                        </div>
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block">Phone</small>
                            <a href="tel:<?= htmlspecialchars($application['phone'] ?? '') ?>"><?= htmlspecialchars($application['phone'] ?? '-') ?></a>
                        </div>
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block">Experience</small>
                            <strong><?= (int)($application['experience_years'] ?? 0) ?> years</strong>
                        </div>
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block">Current Company</small>
                            <strong><?= htmlspecialchars($application['current_company'] ?? '-') ?></strong>
                        </div>
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block">Applied On</small>
                            <strong><?= htmlspecialchars($application['created_at'] ?? '-') ?></strong>
                        </div>
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block">Application #</small>
                            <strong>#<?= (int)($application['id'] ?? 0) ?></strong>
                        </div>
                    </div>
                    <?php if (!empty($application['cover_letter'])): ?>
                    <hr>
                    <small class="text-muted d-block mb-1">Cover Letter</small>
                    <div class="border rounded p-3 bg-light"><?= nl2br(htmlspecialchars($application['cover_letter'])) ?></div>
                    <?php endif; ?>
                    <?php if (!empty($application['resume_path'])): ?>
                    <hr>
                    <a href="<?= BASE_URL ?>/admin/careers/manage/applications/<?= (int)$application['id'] ?>/resume" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-download me-1"></i>Download Resume
                    </a>
                    <?php else: ?>
                    <hr>
                    <span class="text-muted"><i class="fas fa-file-slash me-1"></i>No resume uploaded</span>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (!empty($history)): ?>
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Status History</h6>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <?php foreach ($history as $h): ?>
                            <li class="list-group-item"><i class="fas fa-circle text-primary me-2" style="font-size:.6rem"></i><?= htmlspecialchars($h['created_at'] ?? '') ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <?php else: ?>
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Status History</h6>
                </div>
                <div class="card-body"><p class="small text-muted mb-0">No status changes recorded yet.</p></div>
            </div>
            <?php endif; ?>
        </div>

        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Change Status</h6>
                </div>
                <div class="card-body">
                    <form method="post" action="<?= BASE_URL ?>/admin/careers/manage/applications/<?= (int)$application['id'] ?>/status">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                        <div class="mb-3">
                            <label for="status" class="form-label">New Status</label>
                            <select name="status" id="status" class="form-select" aria-label="Application status">
                                <?php foreach (['pending' => 'Pending', 'reviewing' => 'Reviewing', 'shortlisted' => 'Shortlisted', 'rejected' => 'Rejected', 'hired' => 'Hired'] as $value => $label): ?>
                                    <option value="<?= $value ?>" <?= (($application['status'] ?? '') === $value) ? 'selected' : '' ?>><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes (optional)</label>
                            <textarea name="notes" id="notes" class="form-control" aria-label="Status notes" rows="3" placeholder="Reason / interview feedback..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-save me-1"></i>Save Status</button>
                    </form>
                </div>
            </div>

            <div class="card shadow mb-4 border-danger">
                <div class="card-header py-3 bg-danger text-white">
                    <h6 class="m-0 font-weight-bold">Delete Application</h6>
                </div>
                <div class="card-body">
                    <p class="small text-muted">Permanently removes this application and its resume. This cannot be undone.</p>
                    <form method="post" action="<?= BASE_URL ?>/admin/careers/manage/applications/<?= (int)$application['id'] ?>/delete" onsubmit="return confirm('Delete this application permanently?');">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                        <button type="submit" class="btn btn-danger w-100"><i class="fas fa-trash-alt me-1"></i>Delete Application</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
