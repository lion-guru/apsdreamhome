<?php
/** Admin Engagement Surveys List */

$page_title = $page_title ?? 'Engagement Surveys';
$surveys = $surveys ?? [];
$stats = $stats ?? [];
$types = $types ?? [];
$filters = $filters ?? [];
?>

<style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
.status-badge {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
}
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold"><i class="fas fa-poll me-2 text-primary"></i>Engagement Surveys</h4>
            <p class="text-muted mb-0 small">Create and manage employee engagement surveys</p>
        </div>
        <a href="<?= BASE_URL ?>/admin/engagement/surveys/create" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Create Survey
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card stat-card border-0 shadow-sm bg-primary text-white">
                <div class="card-body text-center">
                    <div class="fw-bold fs-3"><?= $stats['total_surveys'] ?? 0 ?></div>
                    <div class="text-white-50 small">Total Surveys</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card border-0 shadow-sm bg-success text-white">
                <div class="card-body text-center">
                    <div class="fw-bold fs-3"><?= $stats['active_surveys'] ?? 0 ?></div>
                    <div class="text-white-50 small">Active</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card border-0 shadow-sm bg-info text-white">
                <div class="card-body text-center">
                    <div class="fw-bold fs-3"><?= $stats['total_responses'] ?? 0 ?></div>
                    <div class="text-white-50 small">Total Responses</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card border-0 shadow-sm bg-warning text-white">
                <div class="card-body text-center">
                    <div class="fw-bold fs-3"><?= $stats['unique_respondents'] ?? 0 ?></div>
                    <div class="text-white-50 small">Unique Respondents</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Search</label>
                    <input type="text" name="search" class="form-control form-control-sm" value="<?= htmlspecialchars($filters['search'] ?? '') ?>" placeholder="Title...">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        <option value="draft" <?= ($filters['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option>
                        <option value="scheduled" <?= ($filters['status'] ?? '') === 'scheduled' ? 'selected' : '' ?>>Scheduled</option>
                        <option value="active" <?= ($filters['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="closed" <?= ($filters['status'] ?? '') === 'closed' ? 'selected' : '' ?>>Closed</option>
                        <option value="archived" <?= ($filters['status'] ?? '') === 'archived' ? 'selected' : '' ?>>Archived</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Type</label>
                    <select name="survey_type" class="form-select form-select-sm">
                        <option value="">All Types</option>
                        <?php foreach ($types as $key => $label): ?>
                            <option value="<?= $key ?>" <?= ($filters['survey_type'] ?? '') === $key ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-sm w-100"><i class="fas fa-search me-1"></i> Filter</button>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <a href="<?= BASE_URL ?>/admin/engagement/surveys" class="btn btn-outline-secondary btn-sm w-100">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Surveys Table -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <?php if (empty($surveys)): ?>
            <div class="text-center py-5">
                <i class="fas fa-poll fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No Surveys Found</h5>
                <p class="text-muted small mb-3">Create your first engagement survey</p>
                <a href="<?= BASE_URL ?>/admin/engagement/surveys/create" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Create Survey</a>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 35%">Title</th>
                            <th style="width: 120px">Type</th>
                            <th style="width: 100px">Status</th>
                            <th style="width: 150px">Date Range</th>
                            <th style="width: 100px">Responses</th>
                            <th style="width: 150px">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($surveys as $survey): 
                            $statusClass = [
                                'draft' => 'secondary',
                                'scheduled' => 'info',
                                'active' => 'success',
                                'closed' => 'warning',
                                'archived' => 'secondary'
                            ][$survey['status']] ?? 'secondary';
                            
                            $isActive = $survey['status'] === 'active';
                            $isDraft = $survey['status'] === 'draft';
                        ?>
                        <tr>
                            <td>
                                <div class="fw-semibold"><?= htmlspecialchars($survey['title']) ?></div>
                                <?php if (!empty($survey['description'])): ?>
                                    <div class="small text-muted text-truncate" style="max-width: 300px;"><?= htmlspecialchars($survey['description']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark" style="background: #f1f5f9; color: #475569;">
                                    <?= htmlspecialchars($types[$survey['survey_type']] ?? $survey['survey_type']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-<?= $statusClass ?> status-badge">
                                    <?= ucfirst($survey['status']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($survey['start_date']): ?>
                                    <?= date('d M Y', strtotime($survey['start_date'])) ?>
                                    <?php if ($survey['end_date']): ?>
                                        - <?= date('d M Y', strtotime($survey['end_date'])) ?>
                                    <?php else: ?>
                                        - Ongoing
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted">Not scheduled</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="fw-semibold"><?= $survey['response_count'] ?? 0 ?></span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= BASE_URL ?>/admin/engagement/surveys/analytics/<?= $survey['id'] ?>" class="btn btn-outline-primary" title="Analytics"><i class="fas fa-chart-bar"></i></a>
                                    <a href="<?= BASE_URL ?>/admin/engagement/surveys/responses/<?= $survey['id'] ?>" class="btn btn-outline-info" title="Responses"><i class="fas fa-list"></i></a>
                                    <a href="<?= BASE_URL ?>/admin/engagement/surveys/edit/<?= $survey['id'] ?>" class="btn btn-outline-secondary" title="Edit"><i class="fas fa-edit"></i></a>
                                    <?php if ($isDraft): ?>
                                        <a href="<?= BASE_URL ?>/admin/engagement/surveys/activate/<?= $survey['id'] ?>" class="btn btn-outline-success" title="Activate" onclick="return confirm('Activate this survey?')"><i class="fas fa-play"></i></a>
                                    <?php elseif ($isActive): ?>
                                        <a href="<?= BASE_URL ?>/admin/engagement/surveys/close/<?= $survey['id'] ?>" class="btn btn-outline-warning" title="Close" onclick="return confirm('Close this survey?')"><i class="fas fa-stop"></i></a>
                                    <?php endif; ?>
                                    <a href="<?= BASE_URL ?>/admin/engagement/surveys/delete/<?= $survey['id'] ?>" class="btn btn-outline-danger" title="Delete" onclick="return confirm('Delete this survey?')"><i class="fas fa-trash"></i></a>
                                </div>
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