<div class="container-fluid py-4">
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/push-notifications" class="style-75937">Push Notifications</a></li>
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/push-notifications/campaigns" class="style-75937">Campaigns</a></li>
                <li class="breadcrumb-item active" class="style-27277"><?= htmlspecialchars($campaign['name']) ?></li>
            </ol>
        </nav>
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-1 fw-bold"><?= htmlspecialchars($campaign['name']) ?></h1>
                <p class="mb-0" class="style-54585">
                    <?= htmlspecialchars(mb_strimwidth($campaign['description'] ?? '', 0, 80, '...')) ?>
                </p>
            </div>
            <div class="d-flex gap-2">
                <?php if (in_array($campaign['status'], ['draft', 'scheduled'])): ?>
                    <form method="POST" action="<?= BASE_URL ?>/admin/push-notifications/campaigns/<?= $campaign['id'] ?>/launch" class="d-inline">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                        <button type="submit" class="btn btn-success" onclick="return confirm('Launch this campaign? Notifications will be queued for delivery.');">
                            <i class="fas fa-rocket me-1"></i> Launch
                        </button>
                    </form>
                    <a href="<?= BASE_URL ?>/admin/push-notifications/campaigns/<?= $campaign['id'] ?>/edit" class="btn btn-outline-secondary">
                        <i class="fas fa-edit me-1"></i> Edit
                    </a>
                <?php elseif ($campaign['status'] === 'running'): ?>
                    <form method="POST" action="<?= BASE_URL ?>/admin/push-notifications/campaigns/<?= $campaign['id'] ?>/pause" class="d-inline">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                        <button type="submit" class="btn btn-warning" onclick="return confirm('Pause this campaign? Pending notifications will be cancelled.');">
                            <i class="fas fa-pause me-1"></i> Pause
                        </button>
                    </form>
                <?php endif; ?>
                <a href="<?= BASE_URL ?>/admin/push-notifications/campaigns" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>
            </div>
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

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm" class="style-52634">
                <div class="card-body text-center">
                    <div class="fs-2 fw-bold" class="style-96443"><?= (int)($campaign['total_recipients'] ?? 0) ?></div>
                    <div class="style-37380">Total Recipients</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm" class="style-52634">
                <div class="card-body text-center">
                    <div class="fs-2 fw-bold" class="style-63663"><?= (int)($campaign['sent_count'] ?? 0) ?></div>
                    <div class="style-37380">Sent</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm" class="style-52634">
                <div class="card-body text-center">
                    <div class="fs-2 fw-bold" class="style-62247"><?= (int)($campaign['failed_count'] ?? 0) ?></div>
                    <div class="style-37380">Failed</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm" class="style-52634">
                <div class="card-body text-center">
                    <?php
                        $statusStyles = [
                            'draft' => ['bg' => '#334155', 'text' => '#94a3b8'],
                            'scheduled' => ['bg' => '#1e3a5f', 'text' => '#60a5fa'],
                            'running' => ['bg' => '#14532d', 'text' => '#4ade80'],
                            'paused' => ['bg' => '#713f12', 'text' => '#fbbf24'],
                            'completed' => ['bg' => '#1e3a5f', 'text' => '#60a5fa'],
                            'cancelled' => ['bg' => '#7f1d1d', 'text' => '#f87171'],
                        ];
                        $st = $statusStyles[$campaign['status']] ?? ['bg' => '#334155', 'text' => '#94a3b8'];
                    ?>
                    <span class="badge fs-6 p-2" class="style-43336">
                        <?= ucfirst(htmlspecialchars($campaign['status'])) ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm mb-4" class="style-52634">
                <div class="card-header" class="style-52852">
                    <h6 class="mb-0 fw-bold" class="style-96443">Campaign Info</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <td class="style-25420">Channel</td>
                            <td class="style-96443">
                                <?php
                                    $channelColors = ['push' => '#3b82f6', 'email' => '#8b5cf6', 'sms' => '#10b981', 'whatsapp' => '#25d366', 'all' => '#f59e0b'];
                                    $ch = $campaign['channel'] ?? 'push';
                                ?>
                                <span class="badge" class="style-24324">
                                    <?= strtoupper($ch) ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="style-54585">Target</td>
                            <td class="style-96443">
                                <?= htmlspecialchars(ucwords(str_replace('_', ' ', $campaign['target_type'] ?? ''))) ?>
                                <?php if (!empty($campaign['target_value'])): ?>
                                    <br><small class="style-27277"><?= htmlspecialchars($campaign['target_value']) ?></small>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="style-54585">Template</td>
                            <td class="style-96443">
                                <?= $campaign['template_name'] ? htmlspecialchars($campaign['template_name']) : '<span class="style-42047">None</span>' ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="style-54585">Created</td>
                            <td class="style-96443"><?= date('d M Y, h:i A', strtotime($campaign['created_at'])) ?></td>
                        </tr>
                        <?php if (!empty($campaign['started_at'])): ?>
                        <tr>
                            <td class="style-54585">Started</td>
                            <td class="style-96443"><?= date('d M Y, h:i A', strtotime($campaign['started_at'])) ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if (!empty($campaign['completed_at'])): ?>
                        <tr>
                            <td class="style-54585">Completed</td>
                            <td class="style-96443"><?= date('d M Y, h:i A', strtotime($campaign['completed_at'])) ?></td>
                        </tr>
                        <?php endif; ?>
                    </table>
                </div>
            </div>

            <div class="card border-0 shadow-sm" class="style-52634">
                <div class="card-header" class="style-52852">
                    <h6 class="mb-0 fw-bold" class="style-96443">Message Preview</h6>
                </div>
                <div class="card-body">
                    <div class="p-3 rounded" class="style-1278">
                        <div class="fw-semibold mb-1" class="style-96443">
                            <?= htmlspecialchars($campaign['title']) ?>
                        </div>
                        <div class="style-24601">
                            <?= nl2br(htmlspecialchars($campaign['body'])) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <?php if (in_array($campaign['status'], ['running', 'completed'])): ?>
                <div class="card border-0 shadow-sm mb-4" class="style-52634">
                    <div class="card-header" class="style-52852">
                        <h6 class="mb-0 fw-bold" class="style-96443">Queue Status</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3 text-center">
                            <div class="col-3">
                                <div class="fw-bold fs-5" class="style-60246"><?= (int)($queueStats['pending'] ?? 0) ?></div>
                                <div class="style-20964">Pending</div>
                            </div>
                            <div class="col-3">
                                <div class="fw-bold fs-5" class="style-23731"><?= (int)($queueStats['processing'] ?? 0) ?></div>
                                <div class="style-20964">Processing</div>
                            </div>
                            <div class="col-3">
                                <div class="fw-bold fs-5" class="style-63663"><?= (int)($queueStats['sent'] ?? 0) ?></div>
                                <div class="style-20964">Sent</div>
                            </div>
                            <div class="col-3">
                                <div class="fw-bold fs-5" class="style-62247"><?= (int)($queueStats['failed'] ?? 0) ?></div>
                                <div class="style-20964">Failed</div>
                            </div>
                        </div>
                        <?php
                            $total = max(1, (int)($campaign['total_recipients'] ?? 1));
                            $sent = (int)($queueStats['sent'] ?? 0);
                            $pct = round(($sent / $total) * 100);
                        ?>
                        <div class="mt-3">
                            <div class="d-flex justify-content-between mb-1">
                                <small class="style-27277">Progress</small>
                                <small class="style-27277"><?= $pct ?>%</small>
                            </div>
                            <div class="progress" class="style-19462">
                                <div class="progress-bar" class="style-97319"></div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="card border-0 shadow-sm" class="style-52634">
                <div class="card-header" class="style-52852">
                    <h6 class="mb-0 fw-bold" class="style-96443">Recent Activity</h6>
                </div>
                <div class="card-body p-0" class="style-43942">
                    <?php if (empty($logs)): ?>
                        <div class="text-center py-4" class="style-54585">
                            <i class="fas fa-inbox fa-2x mb-2" class="style-97679"></i>
                            <p class="mb-0 small">No activity yet</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($logs as $l): ?>
                            <div class="d-flex align-items-start gap-3 px-3 py-2" class="style-77065">
                                <div class="mt-1">
                                    <?php if (($l['status'] ?? '') === 'sent' || ($l['status'] ?? '') === 'delivered'): ?>
                                        <i class="fas fa-check-circle" class="style-5723"></i>
                                    <?php elseif (($l['status'] ?? '') === 'failed'): ?>
                                        <i class="fas fa-times-circle" class="style-73289"></i>
                                    <?php else: ?>
                                        <i class="fas fa-circle" class="style-79965"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="flex-grow-1 min-width-0">
                                    <div class="d-flex justify-content-between">
                                        <span class="style-40593">
                                            <?= htmlspecialchars($l['user_name'] ?? 'User #' . $l['user_id']) ?>
                                        </span>
                                        <small class="style-42047">
                                            <?= date('d M, h:i A', strtotime($l['created_at'])) ?>
                                        </small>
                                    </div>
                                    <?php if (!empty($l['error_message'])): ?>
                                        <small class="style-62247"><?= htmlspecialchars(mb_strimwidth($l['error_message'], 0, 60, '...')) ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
