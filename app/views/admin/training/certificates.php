<?php
$page_title = 'Training Certificates';
$page_description = 'Manage issued certificates';
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0"><i class="fas fa-certificate me-2 text-warning"></i>Training Certificates</h1>
            <p class="text-muted mb-0">View and download all issued training certificates</p>
        </div>
        <a href="<?= BASE_URL ?>/admin/training" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back to Training</a>
    </div>

    <?php if (empty($certificates)): ?>
    <div class="text-center py-5">
        <i class="fas fa-certificate fa-3x text-muted mb-3 d-block"></i>
        <h5 class="text-muted">No certificates issued yet</h5>
        <p class="text-muted mb-3">Certificates are generated when associates complete training courses.</p>
        <a href="<?= BASE_URL ?>/admin/training/enrollments" class="btn btn-primary"><i class="fas fa-users me-1"></i>View Enrollments</a>
    </div>
    <?php else: ?>
    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="card bg-warning text-dark shadow-sm border-0"><div class="card-body"><h6 class="text-uppercase small mb-1">Total Certificates</h6><h3 class="mb-0"><?= count($certificates) ?></h3></div></div></div>
        <div class="col-md-3"><div class="card bg-success text-white shadow-sm border-0"><div class="card-body"><h6 class="text-uppercase small mb-1">Active</h6><h3 class="mb-0"><?= count(array_filter($certificates, fn($c) => ($c['status'] ?? 'active') === 'active')) ?></h3></div></div></div>
        <div class="col-md-3"><div class="card bg-info text-white shadow-sm border-0"><div class="card-body"><h6 class="text-uppercase small mb-1">This Month</h6><h3 class="mb-0"><?= count(array_filter($certificates, fn($c) => substr($c['issued_date'] ?? '', 0, 7) === date('Y-m'))) ?></h3></div></div></div>
        <div class="col-md-3"><div class="card bg-primary text-white shadow-sm border-0"><div class="card-body"><h6 class="text-uppercase small mb-1">Avg Score</h6><h3 class="mb-0"><?php $scores = array_column(array_filter($certificates, fn($c) => !empty($c['score_percentage'])), 'score_percentage'); echo $scores ? number_format(array_sum($scores)/count($scores), 1).'%' : '-'; ?></h3></div></div></div>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Certificate #</th>
                        <th>Associate</th>
                        <th>Course</th>
                        <th>Type</th>
                        <th>Score</th>
                        <th>Issued</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($certificates as $c): ?>
                    <tr>
                        <td><code class="small"><?= htmlspecialchars($c['certificate_number'] ?? '-') ?></code></td>
                        <td class="fw-semibold"><?= htmlspecialchars($c['associate_name'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($c['course_title'] ?? '-') ?></td>
                        <td><span class="badge bg-info"><?= htmlspecialchars($c['certificate_type'] ?? 'Course Completion') ?></span></td>
                        <td><?php if (!empty($c['score_percentage'])): ?><span class="badge bg-<?= $c['score_percentage'] >= 90 ? 'success' : ($c['score_percentage'] >= 70 ? 'warning' : 'danger') ?>"><?= number_format($c['score_percentage'], 1) ?>%</span><?php else: ?>-<?php endif; ?></td>
                        <td><?= $c['issued_date'] ?? '-' ?></td>
                        <td><span class="badge bg-<?= ($c['status'] ?? 'active') === 'active' ? 'success' : 'secondary' ?>"><?= ucfirst($c['status'] ?? 'active') ?></span></td>
                        <td>
                            <div class="btn-group">
                                <?php if (!empty($c['certificate_url'])): ?>
                                <a href="<?= htmlspecialchars($c['certificate_url']) ?>" target="_blank" class="btn btn-sm btn-outline-primary" title="View"><i class="fas fa-external-link-alt"></i></a>
                                <?php endif; ?>
                                <a href="<?= BASE_URL ?>/admin/training/certificates/download/<?= $c['id'] ?>" class="btn btn-sm btn-outline-success" title="Download"><i class="fas fa-download"></i></a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>
