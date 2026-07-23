<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 fw-bold">Notification Templates</h1>
            <p class="text-muted mb-0">Reusable templates for push, email, SMS, and WhatsApp notifications</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>/admin/push-notifications" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
            <a href="<?= BASE_URL ?>/admin/push-notifications/templates/new" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> New Template
            </a>
        </div>
    </div>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (empty($templates)): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="fas fa-file-alt fa-3x mb-3" style="color:#334155;"></i>
                <h5 class="text-muted">No Templates Yet</h5>
                <p class="text-muted mb-3">Create reusable notification templates to save time on campaign setup.</p>
                <a href="<?= BASE_URL ?>/admin/push-notifications/templates/new" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i> Create First Template
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($templates as $t): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card border-0 shadow-sm h-100" style="background:#1e293b;">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="fw-bold mb-0" style="color:#e2e8f0;">
                                    <?= htmlspecialchars($t['name']) ?>
                                </h6>
                                <?php
                                    $channelColors = ['push' => '#3b82f6', 'email' => '#8b5cf6', 'sms' => '#10b981', 'whatsapp' => '#25d366'];
                                    $ch = $t['channel'] ?? 'push';
                                ?>
                                <span class="badge" style="background:<?= $channelColors[$ch] ?? '#64748b' ?>;color:#fff;font-size:0.7rem;">
                                    <?= strtoupper($ch) ?>
                                </span>
                            </div>
                            <?php if (!empty($t['title'])): ?>
                                <p class="mb-1 fw-semibold" style="color:#94a3b8;font-size:0.85rem;">
                                    <?= htmlspecialchars($t['title']) ?>
                                </p>
                            <?php endif; ?>
                            <p class="mb-3 flex-grow-1" style="color:#64748b;font-size:0.82rem;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden;">
                                <?= htmlspecialchars($t['body']) ?>
                            </p>
                            <?php if (!empty($t['variables'])): ?>
                                <div class="mb-2">
                                    <?php
                                        $vars = array_filter(array_map('trim', explode(',', $t['variables'])));
                                        foreach ($vars as $v):
                                    ?>
                                        <span class="badge me-1" style="background:#334155;color:#94a3b8;font-size:0.7rem;">
                                            <?= htmlspecialchars($v) ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            <div class="d-flex justify-content-between align-items-center pt-2" style="border-top:1px solid #334155;">
                                <small style="color:#475569;">
                                    <i class="fas fa-chart-line me-1"></i> <?= (int)($t['usage_count'] ?? 0) ?> uses
                                </small>
                                <div class="d-flex gap-1">
                                    <a href="<?= BASE_URL ?>/admin/push-notifications/templates/<?= $t['id'] ?>/edit"
                                       class="btn btn-sm" style="background:#334155;color:#94a3b8;border:none;">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" action="<?= BASE_URL ?>/admin/push-notifications/templates/<?= $t['id'] ?>/delete"
                                          onsubmit="return confirm('Delete this template?');" class="d-inline">
                                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                        <button type="submit" class="btn btn-sm" style="background:#334155;color:#f87171;border:none;">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
