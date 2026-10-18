<?php
/**
 * Employee Self-Service - My Grievances
 */

$page_title = $page_title ?? 'My Grievances';
$grievances = $grievances ?? [];

// Calculate stats
$total = count($grievances);
$open = count(array_filter($grievances, fn($g) => in_array($g['status'], ['open', 'under_review', 'investigation', 'mediation'])));
$resolved = count(array_filter($grievances, fn($g) => $g['status'] === 'resolved'));
$closed = count(array_filter($grievances, fn($g) => $g['status'] === 'closed'));
?>

<style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
.grv-stat-card {
    border-radius: 12px;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    border: 1px solid rgba(0,0,0,0.06);
}
.grv-stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.08);
}
.grv-row {
    transition: background-color 0.15s ease;
}
.grv-row:hover {
    background-color: #f8fafc;
}
.badge-priority-critical { background-color: #dc2626; color: #fff; }
.badge-priority-urgent { background-color: #ea580c; color: #fff; }
.badge-priority-high { background-color: #f59e0b; color: #1e293b; }
.badge-priority-normal { background-color: #0284c7; color: #fff; }
.badge-priority-low { background-color: #64748b; color: #fff; }

.badge-status-open { background-color: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
.badge-status-under_review { background-color: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; }
.badge-status-investigation { background-color: #f3e8ff; color: #7e22ce; border: 1px solid #e9d5ff; }
.badge-status-mediation { background-color: #ffedd5; color: #c2410c; border: 1px solid #fed7aa; }
.badge-status-resolved { background-color: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
.badge-status-closed { background-color: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }
.badge-status-reopened { background-color: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }
</style>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h4 class="mb-1 fw-bold text-dark">
                <i class="fas fa-balance-scale me-2 text-primary"></i>My Grievances & Concerns
            </h4>
            <p class="text-muted mb-0 small">Confidential grievance submission, tracking, and resolution portal</p>
        </div>
        <a href="<?= BASE_URL ?>/employee/self-service/grievances/create" class="btn btn-primary d-inline-flex align-items-center gap-2 px-3 py-2 shadow-sm rounded-3">
            <i class="fas fa-plus-circle"></i>
            <span>Submit New Grievance</span>
        </a>
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

    <!-- KPI Summary Row -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card grv-stat-card bg-white shadow-sm h-100">
                <div class="card-body p-3 text-center">
                    <span class="text-muted small fw-medium text-uppercase">Total Submitted</span>
                    <h3 class="fw-bold text-dark my-1"><?= $total ?></h3>
                    <span class="badge bg-light text-muted small">All time</span>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card grv-stat-card bg-white shadow-sm h-100">
                <div class="card-body p-3 text-center">
                    <span class="text-muted small fw-medium text-uppercase">In Progress</span>
                    <h3 class="fw-bold text-warning my-1"><?= $open ?></h3>
                    <span class="badge bg-warning bg-opacity-10 text-warning small">Active Reviews</span>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card grv-stat-card bg-white shadow-sm h-100">
                <div class="card-body p-3 text-center">
                    <span class="text-muted small fw-medium text-uppercase">Resolved</span>
                    <h3 class="fw-bold text-success my-1"><?= $resolved ?></h3>
                    <span class="badge bg-success bg-opacity-10 text-success small">Awaiting Close</span>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card grv-stat-card bg-white shadow-sm h-100">
                <div class="card-body p-3 text-center">
                    <span class="text-muted small fw-medium text-uppercase">Closed</span>
                    <h3 class="fw-bold text-secondary my-1"><?= $closed ?></h3>
                    <span class="badge bg-secondary bg-opacity-10 text-secondary small">Completed</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Grievances List Card -->
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold text-dark">
                <i class="fas fa-list-ul me-2 text-secondary"></i>Grievance History & Status
            </h6>
            <span class="text-muted small"><?= count($grievances) ?> record<?= count($grievances) === 1 ? '' : 's' ?></span>
        </div>
        <div class="card-body p-0">
            <?php if (empty($grievances)): ?>
                <div class="text-center py-5">
                    <div class="mb-3 text-muted">
                        <i class="fas fa-clipboard-check fa-3x opacity-50"></i>
                    </div>
                    <h6 class="fw-bold text-dark">No Grievances Recorded</h6>
                    <p class="text-muted small mb-3">You have not submitted any workplace grievances or concerns.</p>
                    <a href="<?= BASE_URL ?>/employee/self-service/grievances/create" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-plus me-1"></i> Submit a Concern
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small text-uppercase text-muted">
                            <tr>
                                <th class="ps-3">Grievance #</th>
                                <th>Subject & Category</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Submitted On</th>
                                <th class="text-end pe-3">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($grievances as $g): ?>
                                <tr class="grv-row">
                                    <td class="ps-3">
                                        <code class="fw-bold text-primary"><?= htmlspecialchars($g['grievance_number']) ?></code>
                                        <?php if (!empty($g['is_anonymous'])): ?>
                                            <span class="badge bg-dark ms-1" title="Submitted Anonymously"><i class="fas fa-user-secret"></i></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($g['subject']) ?></div>
                                        <span class="badge bg-light text-secondary border small mt-1">
                                            <?= htmlspecialchars(ucwords(str_replace('_', ' ', $g['category'] ?? 'General'))) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-priority-<?= htmlspecialchars($g['priority']) ?> rounded-pill px-2 py-1 small">
                                            <?= ucfirst($g['priority']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-status-<?= htmlspecialchars($g['status']) ?> rounded-pill px-2 py-1 small">
                                            <?= ucwords(str_replace('_', ' ', $g['status'])) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="small text-dark"><?= date('d M Y', strtotime($g['created_at'])) ?></div>
                                        <div class="text-muted" style="font-size: 0.75rem;"><?= date('h:i A', strtotime($g['created_at'])) ?></div>
                                    </td>
                                    <td class="text-end pe-3">
                                        <a href="<?= BASE_URL ?>/employee/self-service/grievances/<?= $g['id'] ?>" class="btn btn-sm btn-outline-primary rounded-2">
                                            <i class="fas fa-eye me-1"></i> View
                                        </a>
                                        <?php if ($g['status'] === 'resolved'): ?>
                                            <a href="<?= BASE_URL ?>/employee/self-service/grievances/<?= $g['id'] ?>/close" class="btn btn-sm btn-success rounded-2 ms-1">
                                                <i class="fas fa-star me-1"></i> Close & Rate
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
