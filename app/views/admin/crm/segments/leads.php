<?php
$page_title = $page_title ?? 'Segment Leads';
$segment = $segment ?? null;
$leads = $leads ?? [];
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1 fw-bold"><i class="fas fa-filter me-2 text-primary"></i><?= htmlspecialchars($segment['name'] ?? 'Segment') ?></h2>
            <p class="text-muted mb-0"><?= count($leads) ?> leads matched &middot; <?= htmlspecialchars($segment['description'] ?? '') ?></p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>/admin/crm/segments" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i> Back</a>
            <form method="POST" action="<?= BASE_URL ?>/admin/crm/bulk-send" class="d-inline">
                <input type="hidden" name="segment_id" value="<?= $segment['id'] ?? '' ?>">
                <button class="btn btn-success"><i class="fas fa-paper-plane me-1"></i> Bulk Send to Segment</button>
            </form>
        </div>
    </div>

    <!-- Criteria badges -->
    <?php
    $criteria = json_decode($segment['filter_criteria'] ?? '{}', true) ?? [];
    if (!empty($criteria)):
    ?>
        <div class="mb-3">
            <small class="text-muted me-2">Criteria:</small>
            <?php foreach ($criteria as $k => $v): ?>
                <span class="badge bg-primary-subtle text-primary-emphasis me-1"><?= ucfirst(str_replace('_',' ',$k)) ?>: <?= htmlspecialchars(is_array($v) ? json_encode($v) : $v) ?></span>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <?php if (empty($leads)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No leads match this segment</h5>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>Source</th>
                                <th>Status</th>
                                <th>Score</th>
                                <th>Budget</th>
                                <th>Assigned</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($leads as $i => $l): ?>
                                <tr>
                                    <td class="text-muted"><?= $i + 1 ?></td>
                                    <td><a href="<?= BASE_URL ?>/admin/leads/<?= $l['id'] ?>" class="fw-bold text-decoration-none"><?= htmlspecialchars($l['name']) ?></a></td>
                                    <td><?= htmlspecialchars($l['phone'] ?? '') ?></td>
                                    <td><small class="text-muted"><?= htmlspecialchars($l['email'] ?? '') ?></small></td>
                                    <td><span class="badge bg-light text-dark"><?= htmlspecialchars($l['source'] ?? 'N/A') ?></span></td>
                                    <td><span class="badge bg-<?= match($l['status'] ?? 'new') { 'new'=>'primary', 'contacted'=>'info', 'qualified'=>'info', 'site_visit'=>'warning', 'proposal'=>'danger', 'negotiation'=>'danger', 'won'=>'success', default=>'secondary' } ?>"><?= ucfirst(str_replace('_',' ',$l['status'] ?? '')) ?></span></td>
                                    <td><span class="badge bg-<?= ($l['lead_score'] ?? 0) >= 70 ? 'success' : (($l['lead_score'] ?? 0) >= 40 ? 'warning' : 'danger') ?>"><?= $l['lead_score'] ?? 0 ?></span></td>
                                    <td class="fw-semibold"><?= isset($l['budget']) ? '₹' . number_format((float)$l['budget'], 0) : 'N/A' ?></td>
                                    <td><small class="text-muted"><?= htmlspecialchars($l['assignee_name'] ?? 'Unassigned') ?></small></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
