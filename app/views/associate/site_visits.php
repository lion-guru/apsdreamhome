<?php
$page_title = $page_title ?? __('assoc_sv_title', [], 'Site Visits');
$current_page = 'site_visits';
$visits = $visits ?? [];
$stats = $stats ?? ['total'=>0,'today'=>0,'upcoming'=>0,'completed'=>0,'cancelled'=>0,'overdue'=>0];
$active_tab = $active_tab ?? 'upcoming';

$statusMap = [
    'scheduled' => ['label'=>__('assoc_sv_status_scheduled', [], 'Scheduled'),'color'=>'primary','icon'=>'fa-calendar-check'],
    'rescheduled' => ['label'=>__('assoc_sv_status_rescheduled', [], 'Rescheduled'),'color'=>'warning','icon'=>'fa-calendar-alt'],
    'completed' => ['label'=>__('assoc_sv_status_completed', [], 'Completed'),'color'=>'success','icon'=>'fa-check-circle'],
    'cancelled' => ['label'=>__('assoc_sv_status_cancelled', [], 'Cancelled'),'color'=>'secondary','icon'=>'fa-times-circle'],
    'in_progress' => ['label'=>__('assoc_sv_status_in_progress', [], 'In Progress'),'color'=>'info','icon'=>'fa-spinner'],
    'no_show' => ['label'=>__('assoc_sv_status_no_show', [], 'No Show'),'color'=>'danger','icon'=>'fa-user-slash'],
];
?>

<style>
    .sv-stat { border-radius: 14px; padding: 18px; border: none; text-align: center; transition: transform 0.2s; cursor: pointer; text-decoration: none; color: inherit; display: block; }
    .sv-stat:hover { transform: translateY(-3px); box-shadow: 0 6px 20px rgba(0,0,0,0.1); color: inherit; }
    .sv-stat .sv-num { font-size: 2rem; font-weight: 700; line-height: 1.2; }
    .sv-stat .sv-label { font-size: 0.78rem; opacity: 0.8; }
    .tab-pill { padding: 8px 20px; border-radius: 25px; font-size: 0.82rem; font-weight: 600; cursor: pointer; transition: all 0.2s; border: 2px solid transparent; text-decoration: none; display: inline-block; }
    .tab-pill:hover { transform: translateY(-1px); }
    .tab-pill.active { background: #0d9488; color: #fff; border-color: #0d9488; }
    .tab-pill:not(.active) { background: #f1f5f9; color: #64748b; border-color: #e2e8f0; }
    .visit-card { border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px; margin-bottom: 12px; transition: all 0.2s; background: #fff; }
    .visit-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.06); }
    .visit-card.is-today { border-left: 4px solid #0d9488; background: #faf5ff; }
    .visit-card.is-overdue { border-left: 4px solid #ef4444; background: #fef2f2; }
    .visit-card.is-completed { border-left: 4px solid #10b981; }
    .visit-card .visitor-name { font-weight: 700; font-size: 1rem; color: #1e293b; }
    .visit-card .visit-meta { font-size: 0.82rem; color: #64748b; }
    .rating-stars { color: #f59e0b; }
</style>

<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="fas fa-map-marker-alt text-primary me-2"></i><?= __('assoc_sv_title', [], 'Site Visits') ?></h4>
            <small class="text-muted"><?= __('assoc_sv_subtitle', [], 'Manage your property visit appointments') ?></small>
        </div>
        <a href="<?= BASE_URL ?>/associate/site-visits/schedule" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> <?= __('assoc_sv_schedule_visit', [], 'Schedule Visit') ?>
        </a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-2">
            <a href="<?= BASE_URL ?>/associate/site-visits?tab=all" class="card sv-stat shadow-sm <?= $active_tab === 'all' ? 'border-primary' : '' ?>">
                <div class="sv-num text-primary"><?= $stats['total'] ?></div>
                <div class="sv-label"><?= __('assoc_sv_all', [], 'All') ?></div>
            </a>
        </div>
        <div class="col-6 col-md-2">
            <a href="<?= BASE_URL ?>/associate/site-visits?tab=today" class="card sv-stat shadow-sm <?= $active_tab === 'today' ? 'border-warning' : '' ?>">
                <div class="sv-num text-warning"><?= $stats['today'] ?></div>
                <div class="sv-label"><?= __('assoc_sv_today', [], 'Today') ?></div>
            </a>
        </div>
        <div class="col-6 col-md-2">
            <a href="<?= BASE_URL ?>/associate/site-visits?tab=upcoming" class="card sv-stat shadow-sm <?= $active_tab === 'upcoming' ? 'border-info' : '' ?>">
                <div class="sv-num text-info"><?= $stats['upcoming'] ?></div>
                <div class="sv-label"><?= __('assoc_sv_upcoming', [], 'Upcoming') ?></div>
            </a>
        </div>
        <div class="col-6 col-md-2">
            <a href="<?= BASE_URL ?>/associate/site-visits?tab=completed" class="card sv-stat shadow-sm <?= $active_tab === 'completed' ? 'border-success' : '' ?>">
                <div class="sv-num text-success"><?= $stats['completed'] ?></div>
                <div class="sv-label"><?= __('assoc_sv_completed', [], 'Completed') ?></div>
            </a>
        </div>
        <div class="col-6 col-md-2">
            <a href="<?= BASE_URL ?>/associate/site-visits?tab=overdue" class="card sv-stat shadow-sm <?= $active_tab === 'overdue' ? 'border-danger' : '' ?>">
                <div class="sv-num text-danger"><?= $stats['overdue'] ?></div>
                <div class="sv-label"><?= __('assoc_sv_overdue', [], 'Overdue') ?></div>
            </a>
        </div>
    </div>

    <div class="d-flex gap-2 mb-4 flex-wrap">
        <?php
        $tabs = ['today'=>__('assoc_sv_today', [], 'Today'),'upcoming'=>__('assoc_sv_upcoming', [], 'Upcoming'),'all'=>__('assoc_sv_all', [], 'All'),'completed'=>__('assoc_sv_completed', [], 'Completed'),'overdue'=>__('assoc_sv_overdue', [], 'Overdue')];
        foreach ($tabs as $tKey => $tLabel): ?>
            <a href="<?= BASE_URL ?>/associate/site-visits?tab=<?= $tKey ?>" class="tab-pill <?= $active_tab === $tKey ? 'active' : '' ?>"><?= $tLabel ?></a>
        <?php endforeach; ?>
    </div>

    <?php if (empty($visits)): ?>
        <div class="text-center py-5">
            <i class="fas fa-map-marker-alt fa-3x text-muted mb-3" class="style-82835"></i>
            <h5 class="text-muted"><?= __('assoc_sv_empty', [], 'No site visits found') ?></h5>
            <p class="text-muted"><?= __('assoc_sv_empty_desc', [], 'Schedule your first site visit to get started.') ?></p>
            <a href="<?= BASE_URL ?>/associate/site-visits/schedule" class="btn btn-primary"><i class="fas fa-plus me-1"></i> <?= __('assoc_sv_schedule_visit', [], 'Schedule Visit') ?></a>
        </div>
    <?php else: ?>
        <?php
        $today = date('Y-m-d');
        foreach ($visits as $v):
            $isToday = ($v['visit_date'] === $today);
            $isPast = strtotime($v['visit_date']) < strtotime($today);
            $isCompleted = ($v['status'] === 'completed');
            $isCancelled = ($v['status'] === 'cancelled');
            $isOverdue = $isPast && !$isCompleted && !$isCancelled;
            $cardClass = $isToday ? 'is-today' : ($isOverdue ? 'is-overdue' : ($isCompleted ? 'is-completed' : ''));
            $sv = $statusMap[$v['status']] ?? $statusMap['scheduled'];
        ?>
        <div class="visit-card <?= $cardClass ?>">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div class="d-flex align-items-start gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" class="style-58004">
                        <i class="fas <?= $sv['icon'] ?> text-<?= $sv['color'] ?>"></i>
                    </div>
                    <div>
                        <div class="visitor-name"><?= htmlspecialchars($v['visitor_name']) ?></div>
                        <div class="visit-meta">
                            <i class="fas fa-phone me-1"></i><?= htmlspecialchars($v['visitor_phone']) ?>
                            <?php if (!empty($v['lead_name'])): ?>
                                &nbsp;&bull;&nbsp;<a href="<?= BASE_URL ?>/associate/leads/<?= $v['lead_id'] ?>" class="text-decoration-none"><i class="fas fa-user me-1"></i><?= htmlspecialchars($v['lead_name']) ?></a>
                            <?php endif; ?>
                        </div>
                        <div class="visit-meta mt-1">
                            <i class="fas fa-calendar me-1"></i><?= date('D, d M Y', strtotime($v['visit_date'])) ?>
                            &nbsp;&bull;&nbsp;<i class="fas fa-clock me-1"></i><?= date('h:i A', strtotime($v['visit_time'])) ?>
                            <?php if (!empty($v['duration_minutes'])): ?>
                                &nbsp;&bull;&nbsp;<i class="fas fa-hourglass-half me-1"></i><?= $v['duration_minutes'] ?> <?= __('assoc_sv_min', [], 'min') ?>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($v['notes'])): ?>
                            <div class="mt-1" class="style-69622"><?= htmlspecialchars(mb_substr($v['notes'], 0, 120)) ?></div>
                        <?php endif; ?>
                        <?php if ($isCompleted && !empty($v['rating'])): ?>
                            <div class="rating-stars mt-1">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star<?= $i <= $v['rating'] ? '' : '-o' ?>"></i>
                                <?php endfor; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="d-flex gap-1 align-items-start">
                    <?php if ($isToday && !$isCompleted && !$isCancelled): ?>
                        <form method="POST" action="<?= BASE_URL ?>/associate/site-visits/<?= $v['id'] ?>/complete" class="d-inline" onsubmit="return confirm('<?= __('assoc_sv_confirm_complete', [], 'Mark this visit as completed?') ?>')">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                            <input type="hidden" name="rating" value="5">
                            <input type="hidden" name="feedback" value="">
                            <button type="submit" class="btn btn-success btn-sm" title="<?= __('assoc_sv_mark_complete', [], 'Mark Complete') ?>"><i class="fas fa-check"></i></button>
                        </form>
                    <?php endif; ?>
                    <?php if (!$isCompleted && !$isCancelled): ?>
                        <button class="btn btn-outline-success btn-sm" data-bs-toggle="modal" data-bs-target="#completeModal<?= $v['id'] ?>" title="<?= __('assoc_sv_complete_with_feedback', [], 'Complete with Feedback') ?>">
                            <i class="fas fa-clipboard-check"></i>
                        </button>
                        <button class="btn btn-outline-warning btn-sm" data-bs-toggle="modal" data-bs-target="#rescheduleModal<?= $v['id'] ?>" title="<?= __('assoc_sv_reschedule_visit', [], 'Reschedule') ?>">
                            <i class="fas fa-calendar-alt"></i>
                        </button>
                        <form method="POST" action="<?= BASE_URL ?>/associate/site-visits/<?= $v['id'] ?>/cancel" class="d-inline" onsubmit="return confirm('<?= __('assoc_sv_confirm_cancel', [], 'Cancel this visit?') ?>')">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                            <input type="hidden" name="reason" value="<?= __('assoc_sv_cancelled_by', [], 'Cancelled by associate') ?>">
                            <button type="submit" class="btn btn-outline-danger btn-sm" title="<?= __('assoc_sv_cancel_visit', [], 'Cancel') ?>"><i class="fas fa-times"></i></button>
                        </form>
                    <?php endif; ?>
                    <?php if (!empty($v['visitor_phone'])): ?>
                        <a href="tel:<?= htmlspecialchars($v['visitor_phone']) ?>" class="btn btn-outline-primary btn-sm" title="<?= __('assoc_sv_call', [], 'Call') ?>"><i class="fas fa-phone"></i></a>
                        <a href="https://wa.me/91<?= htmlspecialchars($v['visitor_phone']) ?>" class="btn btn-outline-success btn-sm" target="_blank" title="<?= __('assoc_sv_whatsapp', [], 'WhatsApp') ?>" class="style-2555"><i class="fab fa-whatsapp"></i></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php if (!$isCompleted && !$isCancelled): ?>
        <div class="modal fade" id="completeModal<?= $v['id'] ?>" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" action="<?= BASE_URL ?>/associate/site-visits/<?= $v['id'] ?>/complete">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                        <div class="modal-header">
                            <h6 class="modal-title"><i class="fas fa-clipboard-check text-success me-2"></i><?= __('assoc_sv_complete_visit', [], 'Complete Visit') ?></h6>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label"><?= __('assoc_sv_rating', [], 'Rating') ?></label>
                                <div class="d-flex gap-2">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <label class="btn btn-outline-warning btn-sm">
                                            <input type="radio" name="rating" value="<?= $i ?>" <?= $i === 5 ? 'checked' : '' ?> class="d-none">
                                            <i class="fas fa-star"></i> <?= $i ?>
                                        </label>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><?= __('assoc_sv_feedback', [], 'Feedback') ?></label>
                                <textarea class="form-control" name="feedback" rows="3" placeholder="<?= __('assoc_sv_feedback_placeholder', [], 'How did the visit go? Customer feedback...') ?>"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal"><?= __('assoc_sv_cancel', [], 'Cancel') ?></button>
                            <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-check me-1"></i> <?= __('assoc_sv_mark_complete', [], 'Mark Complete') ?></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="rescheduleModal<?= $v['id'] ?>" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" action="<?= BASE_URL ?>/associate/site-visits/<?= $v['id'] ?>/reschedule">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                        <div class="modal-header">
                            <h6 class="modal-title"><i class="fas fa-calendar-alt text-warning me-2"></i><?= __('assoc_sv_reschedule_visit', [], 'Reschedule Visit') ?></h6>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p class="text-muted mb-3"><?= __('assoc_sv_current', [], 'Current') ?>: <?= date('D, d M Y h:i A', strtotime($v['visit_date'] . ' ' . $v['visit_time'])) ?></p>
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="form-label"><?= __('assoc_sv_new_date', [], 'New Date') ?></label>
                                    <input type="date" class="form-control" name="new_date" min="<?= date('Y-m-d') ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label"><?= __('assoc_sv_new_time', [], 'New Time') ?></label>
                                    <input type="time" class="form-control" name="new_time" required>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal"><?= __('assoc_sv_cancel', [], 'Cancel') ?></button>
                            <button type="submit" class="btn btn-warning btn-sm"><i class="fas fa-calendar-alt me-1"></i> <?= __('assoc_sv_reschedule', [], 'Reschedule') ?></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
