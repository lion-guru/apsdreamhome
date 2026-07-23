<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 fw-bold">Campaigns</h1>
            <p class="text-muted mb-0">Create and manage notification campaigns</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>/admin/push-notifications" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
            <a href="<?= BASE_URL ?>/admin/push-notifications/campaigns/new" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> New Campaign
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

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm" style="background:#1e293b;">
                <div class="card-body text-center">
                    <div class="fs-2 fw-bold" style="color:#e2e8f0;"><?= (int)($stats['total'] ?? 0) ?></div>
                    <div style="color:#64748b;font-size:0.85rem;">Total Campaigns</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm" style="background:#1e293b;">
                <div class="card-body text-center">
                    <div class="fs-2 fw-bold" style="color:#94a3b8;"><?= (int)($stats['draft'] ?? 0) ?></div>
                    <div style="color:#64748b;font-size:0.85rem;">Drafts</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm" style="background:#1e293b;">
                <div class="card-body text-center">
                    <div class="fs-2 fw-bold" style="color:#22c55e;"><?= (int)($stats['running'] ?? 0) ?></div>
                    <div style="color:#64748b;font-size:0.85rem;">Running</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm" style="background:#1e293b;">
                <div class="card-body text-center">
                    <div class="fs-2 fw-bold" style="color:#3b82f6;"><?= (int)($stats['completed'] ?? 0) ?></div>
                    <div style="color:#64748b;font-size:0.85rem;">Completed</div>
                </div>
            </div>
        </div>
    </div>

    <?php if (empty($campaigns)): ?>
        <div class="card border-0 shadow-sm" style="background:#1e293b;">
            <div class="card-body text-center py-5">
                <i class="fas fa-bullhorn fa-3x mb-3" style="color:#334155;"></i>
                <h5 style="color:#94a3b8;">No Campaigns Yet</h5>
                <p style="color:#64748b;">Create your first campaign to start sending batch notifications.</p>
                <a href="<?= BASE_URL ?>/admin/push-notifications/campaigns/new" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i> Create Campaign
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="card border-0 shadow-sm" style="background:#1e293b;">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr style="border-bottom:1px solid #334155;">
                                <th style="color:#94a3b8;padding:12px 16px;font-weight:600;">Campaign</th>
                                <th style="color:#94a3b8;padding:12px 16px;font-weight:600;">Channel</th>
                                <th style="color:#94a3b8;padding:12px 16px;font-weight:600;">Target</th>
                                <th style="color:#94a3b8;padding:12px 16px;font-weight:600;">Recipients</th>
                                <th style="color:#94a3b8;padding:12px 16px;font-weight:600;">Sent</th>
                                <th style="color:#94a3b8;padding:12px 16px;font-weight:600;">Status</th>
                                <th style="color:#94a3b8;padding:12px 16px;font-weight:600;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($campaigns as $c): ?>
                                <tr style="border-bottom:1px solid #1e293b;">
                                    <td style="padding:12px 16px;">
                                        <a href="<?= BASE_URL ?>/admin/push-notifications/campaigns/<?= $c['id'] ?>" style="color:#e2e8f0;text-decoration:none;font-weight:600;">
                                            <?= htmlspecialchars($c['name']) ?>
                                        </a>
                                        <?php if (!empty($c['description'])): ?>
                                            <br><small style="color:#64748b;"><?= htmlspecialchars(mb_strimwidth($c['description'], 0, 60, '...')) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding:12px 16px;">
                                        <?php
                                            $channelColors = ['push' => '#3b82f6', 'email' => '#8b5cf6', 'sms' => '#10b981', 'whatsapp' => '#25d366', 'all' => '#f59e0b'];
                                            $ch = $c['channel'] ?? 'push';
                                        ?>
                                        <span class="badge" style="background:<?= $channelColors[$ch] ?? '#64748b' ?>;color:#fff;font-size:0.7rem;">
                                            <?= strtoupper($ch) ?>
                                        </span>
                                    </td>
                                    <td style="padding:12px 16px;color:#94a3b8;font-size:0.85rem;">
                                        <?= htmlspecialchars(ucwords(str_replace('_', ' ', $c['target_type'] ?? ''))) ?>
                                        <?php if (!empty($c['target_value'])): ?>
                                            <br><small style="color:#64748b;"><?= htmlspecialchars($c['target_value']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding:12px 16px;color:#e2e8f0;font-weight:600;">
                                        <?= (int)($c['total_recipients'] ?? 0) ?>
                                    </td>
                                    <td style="padding:12px 16px;">
                                        <span style="color:#22c55e;font-weight:600;"><?= (int)($c['sent_count'] ?? 0) ?></span>
                                        <?php if (($c['failed_count'] ?? 0) > 0): ?>
                                            <span style="color:#f87171;font-size:0.85rem;"> / <?= (int)$c['failed_count'] ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding:12px 16px;">
                                        <?php
                                            $statusStyles = [
                                                'draft' => ['bg' => '#334155', 'text' => '#94a3b8'],
                                                'scheduled' => ['bg' => '#1e3a5f', 'text' => '#60a5fa'],
                                                'running' => ['bg' => '#14532d', 'text' => '#4ade80'],
                                                'paused' => ['bg' => '#713f12', 'text' => '#fbbf24'],
                                                'completed' => ['bg' => '#1e3a5f', 'text' => '#60a5fa'],
                                                'cancelled' => ['bg' => '#7f1d1d', 'text' => '#f87171'],
                                            ];
                                            $st = $statusStyles[$c['status']] ?? ['bg' => '#334155', 'text' => '#94a3b8'];
                                        ?>
                                        <span class="badge" style="background:<?= $st['bg'] ?>;color:<?= $st['text'] ?>;font-size:0.75rem;">
                                            <?= ucfirst(htmlspecialchars($c['status'])) ?>
                                        </span>
                                    </td>
                                    <td style="padding:12px 16px;">
                                        <div class="d-flex gap-1">
                                            <a href="<?= BASE_URL ?>/admin/push-notifications/campaigns/<?= $c['id'] ?>"
                                               class="btn btn-sm" style="background:#334155;color:#94a3b8;border:none;" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if (in_array($c['status'], ['draft', 'scheduled'])): ?>
                                                <a href="<?= BASE_URL ?>/admin/push-notifications/campaigns/<?= $c['id'] ?>/edit"
                                                   class="btn btn-sm" style="background:#334155;color:#94a3b8;border:none;" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
