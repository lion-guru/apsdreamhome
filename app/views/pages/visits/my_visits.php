<?php
$page_title = $page_title ?? 'My Visits';
$page_heading = $page_heading ?? 'My Property Visits';
$content = $content ?? '';
$visits = $visits ?? [];
?>
<div class="container py-5">
    <h2 class="mb-4">My Property Visits</h2>
    <?php if (empty($visits)): ?>
        <div class="text-center py-5">
            <div class="display-1 text-muted mb-3"><i class="fas fa-calendar"></i></div>
            <h4 class="text-muted">No visits scheduled</h4>
            <a href="<?= BASE_URL ?>/properties" class="btn btn-primary">Browse Properties</a>
        </div>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($visits as $v): ?>
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body aps-cp-card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <h5 class="mb-0"><?= htmlspecialchars($v['property_title'] ?? 'Property') ?></h5>
                                <?php $statusClass = ['scheduled' => 'warning', 'confirmed' => 'info', 'completed' => 'success', 'cancelled' => 'danger'][$v['status']] ?? 'secondary'; ?>
                                <span class="badge bg-<?= $statusClass ?>"><?= ucfirst($v['status']) ?></span>
                            </div>
                            <p class="mb-1"><i class="fas fa-calendar me-2 text-primary"></i> <?= date('D, M j Y', strtotime($v['visit_date'])) ?> at <?= date('h:i A', strtotime($v['visit_time'])) ?></p>
                            <p class="mb-1"><i class="fas fa-tag me-2 text-info"></i> <?= ucfirst(str_replace('_', ' ', $v['visit_type'])) ?></p>
                            <?php if (!empty($v['notes'])): ?>
                                <p class="mb-1"><i class="fas fa-comment me-2 text-muted"></i> <?= htmlspecialchars($v['notes']) ?></p>
                            <?php endif; ?>
                            <?php if (in_array($v['status'], ['scheduled', 'confirmed'])): ?>
                                <form method="POST" action="<?= BASE_URL ?>/visit/cancel" class="mt-2" onsubmit="return confirm('Cancel this visit?')">
                                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                    <input type="hidden" name="id" value="<?= $v['id'] ?>">
                                    <input type="hidden" name="reason" value="Cancelled by customer">
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-times me-1"></i> Cancel Visit</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>