<?php
$page_title = $page_title ?? 'My Site Visits';
$current_page = 'site-visits';
$visits = $visits ?? [];
$stats = $stats ?? ['total'=>0,'upcoming'=>0,'completed'=>0];
$colonies = $colonies ?? [];
$today = date('Y-m-d');
$success = $_SESSION['flash_success'] ?? null;
$error = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
?>

<div class="aps-cp-hero" class="style-6804">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h2><i class="fas fa-map-marker-alt me-2"></i>My Site Visits</h2>
            <p>Schedule and track your property site visits.</p>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#bookVisitModal">
                <i class="fas fa-plus me-2"></i>Schedule Visit
            </button>
        </div>
    </div>
</div>

<?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show border-0 rounded-3"><i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($success) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show border-0 rounded-3"><i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-md-4 col-6">
        <div class="aps-cp-stat aps-cp-stat--orange">
            <div class="aps-cp-stat-icon"><i class="fas fa-map-marker-alt"></i></div>
            <div class="aps-cp-stat-body">
                <div class="aps-cp-stat-value"><?= $stats['total'] ?></div>
                <div class="aps-cp-stat-label">Total Visits</div>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-6">
        <div class="aps-cp-stat aps-cp-stat--blue">
            <div class="aps-cp-stat-icon"><i class="fas fa-calendar-check"></i></div>
            <div class="aps-cp-stat-body">
                <div class="aps-cp-stat-value"><?= $stats['upcoming'] ?></div>
                <div class="aps-cp-stat-label">Upcoming</div>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-6">
        <div class="aps-cp-stat aps-cp-stat--green">
            <div class="aps-cp-stat-icon"><i class="fas fa-check-circle"></i></div>
            <div class="aps-cp-stat-body">
                <div class="aps-cp-stat-value"><?= $stats['completed'] ?></div>
                <div class="aps-cp-stat-label">Completed</div>
            </div>
        </div>
    </div>
</div>

<!-- Visits List -->
<div class="aps-cp-card">
    <div class="aps-cp-card-header">
        <h5><i class="fas fa-list me-2 text-warning"></i>All Site Visits</h5>
    </div>
    <div class="aps-cp-card-body p-0">
        <?php if (empty($visits)): ?>
            <div class="aps-cp-empty">
                <div class="aps-cp-empty-icon"><i class="fas fa-map-marker-alt"></i></div>
                <h5>No site visits yet</h5>
                <p>Schedule a visit to see properties in person.</p>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#bookVisitModal">
                    <i class="fas fa-plus me-1"></i> Schedule Your First Visit
                </button>
            </div>
        <?php else: ?>
            <?php foreach ($visits as $v):
                $isToday = ($v['visit_date'] === $today);
                $isPast = strtotime($v['visit_date']) < strtotime($today);
                $statusColors = [
                    'scheduled' => 'primary', 'rescheduled' => 'warning',
                    'completed' => 'success', 'cancelled' => 'secondary',
                ];
                $color = $statusColors[$v['status']] ?? 'secondary';
                $isOverdue = $isPast && $v['status'] === 'scheduled';
            ?>
            <div class="p-3 border-bottom <?= $isToday ? 'bg-light' : '' ?>" class="style-32192">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                    <div class="d-flex align-items-start gap-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center" class="style-23127">
                            <i class="fas fa-map-marker-alt text-<?= $color ?>"></i>
                        </div>
                        <div>
                            <strong><?= date('D, d M Y', strtotime($v['visit_date'])) ?> at <?= date('h:i A', strtotime($v['visit_time'])) ?></strong>
                            <?php if ($isToday): ?><span class="badge bg-primary ms-2">Today</span><?php endif; ?>
                            <?php if ($isOverdue): ?><span class="badge bg-danger ms-2">Missed</span><?php endif; ?>
                            <div class="text-muted" class="style-47175">
                                <?php if (!empty($v['colony_name'])): ?>
                                    <i class="fas fa-building me-1"></i><?= htmlspecialchars($v['colony_name']) ?>
                                <?php endif; ?>
                                <?php if (!empty($v['plot_number'])): ?>
                                    | Plot #<?= htmlspecialchars($v['plot_number']) ?>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($v['notes'])): ?>
                                <div class="style-69622"><?= htmlspecialchars($v['notes']) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <span class="badge bg-<?= $color ?>"><?= ucfirst($v['status']) ?></span>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Book Visit Modal -->
<div class="modal fade" id="bookVisitModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= BASE_URL ?>/user/site-visits/book">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-calendar-plus text-primary me-2"></i>Schedule Site Visit</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Your Name *</label>
                        <input type="text" class="form-control" name="visitor_name" value="<?= htmlspecialchars($_SESSION['user_name'] ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Phone *</label>
                        <input type="tel" class="form-control" name="visitor_phone" value="<?= htmlspecialchars($_SESSION['user_phone'] ?? '') ?>" required pattern="[0-9]{10}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Colony / Location</label>
                        <select class="form-select" name="colony_id">
                            <option value="">â€” Select â€”</option>
                            <?php foreach ($colonies as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Date *</label>
                            <input type="date" class="form-control" name="visit_date" min="<?= $today ?>" value="<?= $today ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Time *</label>
                            <input type="time" class="form-control" name="visit_time" value="10:00" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Notes</label>
                        <textarea class="form-control" name="notes" rows="2" placeholder="What you'd like to see, preferences..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-calendar-check me-1"></i> Schedule Visit</button>
                </div>
            </form>
        </div>
    </div>
</div>
