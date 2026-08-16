<?php
$page_title = $page_title ?? 'NPS Surveys';
$page_heading = $page_heading ?? 'NPS Surveys';
$content = $content ?? '';
$surveys = $surveys ?? [];
$stats = $stats ?? [];
ob_start();
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">NPS Surveys</h2>
            <p class="text-muted mb-0">Measure customer satisfaction with Net Promoter Score</p>
        </div>
        <a href="<?= BASE_URL ?>/admin/nps/create" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> New Survey
        </a>
    </div>

    <div class="row g-3 mb-4">
        <?php if (!empty($stats)): ?>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <p class="text-muted small mb-1">Responses</p>
                        <h3><?= number_format($stats['total_responses'] ?? 0) ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <p class="text-muted small mb-1">NPS Score</p>
                        <h3 class="text-<?= ($stats['nps_score'] ?? 0) >= 0 ? 'success' : 'danger' ?>">
                            <?= number_format($stats['nps_score'] ?? 0, 1) ?>
                        </h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <p class="text-muted small mb-1">Promoters</p>
                        <h3 class="text-success"><?= number_format($stats['promoters'] ?? 0) ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <p class="text-muted small mb-1">Detractors</p>
                        <h3 class="text-danger"><?= number_format($stats['detractors'] ?? 0) ?></h3>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Survey List</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Question</th>
                            <th>Status</th>
                            <th>Responses</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($surveys)): ?>
                            <tr><td colspan="7" class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x d-block mb-2 text-muted" aria-hidden="true"></i>No surveys yet</td></tr>
                        <?php else: ?>
                            <?php foreach ($surveys as $s): ?>
                                <tr>
                                    <td>#<?= $s['id'] ?></td>
                                    <td><strong><?= htmlspecialchars($s['title'] ?? '') ?></strong></td>
                                    <td><?= htmlspecialchars($s['question_text'] ?? '') ?></td>
                                    <td>
                                        <span class="badge bg-<?= $s['is_active'] ? 'success' : 'secondary' ?>">
                                            <?= $s['is_active'] ? 'Active' : 'Inactive' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <!-- We don't have response count per survey in the list, but we can fetch it or leave blank -->
                                        <small>-</small>
                                    </td>
                                    <td><small><?= date('M j, Y', strtotime($s['created_at'])) ?></small></td>
                                    <td>
                                        <a href="<?= BASE_URL ?>/admin/nps/show/<?= $s['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a>
                                        <a href="<?= BASE_URL ?>/admin/nps/edit/<?= $s['id'] ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-edit"></i></a>
                                        <a href="<?= BASE_URL ?>/admin/nps/delete?id=<?= $s['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this survey and all its responses?')"><i class="fas fa-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
include APP_PATH . '/views/layouts/admin.php';
