<?php
$stats = $stats ?? [];
$recent_logs = $recent_logs ?? [];
$channel_stats = $channel_stats ?? [];
$type_stats = $type_stats ?? [];
$daily_stats = $daily_stats ?? [];
$template_stats = $template_stats ?? [];
$base = defined('BASE_URL') ? BASE_URL : '/' . trim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'Notification Dashboard' ?> - APS Dream Home</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #0f172a; color: #e2e8f0; min-height: 100vh; }
        
        .page-header { background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); padding: 2rem; border-bottom: 1px solid rgba(255,255,255,0.06); }
        .page-header h1 { font-size: 1.75rem; font-weight: 700; color: #f8fafc; }
        .page-header p { color: #94a3b8; font-size: 0.9rem; margin-top: 0.25rem; }
        
        .stat-card { background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; padding: 1.5rem; position: relative; overflow: hidden; }
        .stat-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px; }
        .stat-card.blue::before { background: linear-gradient(90deg, #3b82f6, #60a5fa); }
        .stat-card.green::before { background: linear-gradient(90deg, #22c55e, #4ade80); }
        .stat-card.amber::before { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
        .stat-card.purple::before { background: linear-gradient(90deg, #a855f7, #c084fc); }
        .stat-card .icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }
        .stat-card .icon.blue { background: rgba(59,130,246,0.15); color: #60a5fa; }
        .stat-card .icon.green { background: rgba(34,197,94,0.15); color: #4ade80; }
        .stat-card .icon.amber { background: rgba(245,158,11,0.15); color: #fbbf24; }
        .stat-card .icon.purple { background: rgba(168,85,247,0.15); color: #c084fc; }
        .stat-card .value { font-size: 2rem; font-weight: 700; color: #f8fafc; margin: 0.75rem 0 0.25rem; }
        .stat-card .label { color: #94a3b8; font-size: 0.85rem; }
        
        .section-card { background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; padding: 1.5rem; margin-bottom: 1.5rem; }
        .section-card h3 { font-size: 1.1rem; font-weight: 600; color: #f8fafc; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem; }
        .section-card h3 i { font-size: 0.9rem; color: #60a5fa; }
        
        .table-dark { --bs-table-bg: transparent; --bs-table-hover-bg: rgba(255,255,255,0.04); }
        .table-dark th { color: #94a3b8; font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 1px solid rgba(255,255,255,0.08); }
        .table-dark td { color: #e2e8f0; font-size: 0.85rem; border-bottom: 1px solid rgba(255,255,255,0.04); }
        
        .badge-channel { padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
        .badge-email { background: rgba(59,130,246,0.15); color: #60a5fa; }
        .badge-sms { background: rgba(34,197,94,0.15); color: #4ade80; }
        .badge-push { background: rgba(168,85,247,0.15); color: #c084fc; }
        .badge-whatsapp { background: rgba(37,211,102,0.15); color: #25d366; }
        
        .badge-status { padding: 0.2rem 0.6rem; border-radius: 8px; font-size: 0.7rem; font-weight: 600; }
        .badge-sent { background: rgba(34,197,94,0.15); color: #4ade80; }
        .badge-failed { background: rgba(239,68,68,0.15); color: #f87171; }
        .badge-pending { background: rgba(245,158,11,0.15); color: #fbbf24; }
        
        .channel-bar { height: 8px; border-radius: 4px; background: rgba(255,255,255,0.08); overflow: hidden; margin-top: 0.5rem; }
        .channel-bar-fill { height: 100%; border-radius: 4px; transition: width 0.5s ease; }
        
        .template-card { background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06); border-radius: 12px; padding: 1rem; margin-bottom: 0.75rem; }
        .template-card .name { font-weight: 600; color: #f8fafc; font-size: 0.9rem; }
        .template-card .body { color: #94a3b8; font-size: 0.8rem; margin-top: 0.5rem; line-height: 1.5; }
        .template-card .meta { color: #64748b; font-size: 0.75rem; margin-top: 0.5rem; }
        
        .btn-test { background: rgba(59,130,246,0.15); color: #60a5fa; border: 1px solid rgba(59,130,246,0.3); padding: 0.5rem 1rem; border-radius: 8px; font-size: 0.85rem; cursor: pointer; transition: all 0.2s; }
        .btn-test:hover { background: rgba(59,130,246,0.25); color: #93bbfc; }
        
        .empty-state { text-align: center; padding: 3rem; color: #64748b; }
        .empty-state i { font-size: 2.5rem; margin-bottom: 1rem; display: block; }
        
        .nav-tabs-custom { border-bottom: 1px solid rgba(255,255,255,0.08); }
        .nav-tabs-custom .nav-link { color: #94a3b8; border: none; padding: 0.75rem 1.25rem; font-size: 0.9rem; }
        .nav-tabs-custom .nav-link.active { color: #60a5fa; border-bottom: 2px solid #60a5fa; background: transparent; }
        
        @media (max-width: 768px) {
            .stat-card .value { font-size: 1.5rem; }
            .page-header { padding: 1.5rem; }
        }
    </style>
</head>
<body>
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1><i class="fas fa-bell me-2"></i>Notification Dashboard</h1>
                <p>Monitor notification delivery across all channels</p>
            </div>
            <a href="<?= $base ?>/admin/notifications/test" class="btn-test"><i class="fas fa-paper-plane me-1"></i>Send Test</a>
        </div>
    </div>
    
    <div class="container-fluid px-4 py-4">
        <!-- Stat Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-3 col-6">
                <div class="stat-card blue">
                    <div class="icon blue"><i class="fas fa-paper-plane"></i></div>
                    <div class="value"><?= number_format($stats['total_sent'] ?? 0) ?></div>
                    <div class="label">Total Sent</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-card green">
                    <div class="icon green"><i class="fas fa-check-circle"></i></div>
                    <div class="value"><?= ($stats['success_rate'] ?? 0) ?>%</div>
                    <div class="label">Success Rate</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-card amber">
                    <div class="icon amber"><i class="fas fa-calendar-day"></i></div>
                    <div class="value"><?= number_format($stats['today_sent'] ?? 0) ?></div>
                    <div class="label">Sent Today</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-card purple">
                    <div class="icon purple"><i class="fas fa-layer-group"></i></div>
                    <div class="value"><?= ($stats['sms_templates'] ?? 0) + ($stats['wa_templates'] ?? 0) ?></div>
                    <div class="label">Active Templates</div>
                </div>
            </div>
        </div>
        
        <div class="row g-4">
            <!-- Channel Breakdown -->
            <div class="col-lg-6">
                <div class="section-card">
                    <h3><i class="fas fa-chart-bar"></i>Channel Performance</h3>
                    <?php if (!empty($channel_stats)): ?>
                        <?php foreach ($channel_stats as $ch): ?>
                            <?php
                                $total = max(1, ($ch['sent'] ?? 0) + ($ch['failed'] ?? 0));
                                $pct = round(($ch['sent'] ?? 0) / $total * 100);
                                $colors = ['email' => '#3b82f6', 'sms' => '#22c55e', 'push' => '#a855f7', 'whatsapp' => '#25d366'];
                                $color = $colors[$ch['channel']] ?? '#94a3b8';
                            ?>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge-channel badge-<?= $ch['channel'] ?>"><?= strtoupper($ch['channel']) ?></span>
                                    <span style="color: #94a3b8; font-size: 0.85rem;"><?= number_format($ch['count']) ?> total / <?= number_format($ch['sent']) ?> sent</span>
                                </div>
                                <div class="channel-bar">
                                    <div class="channel-bar-fill" style="width: <?= $pct ?>%; background: <?= $color ?>;"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state"><i class="fas fa-chart-bar"></i><p>No channel data yet. Notifications will appear here once sent.</p></div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Notification Types -->
            <div class="col-lg-6">
                <div class="section-card">
                    <h3><i class="fas fa-tags"></i>Notification Types</h3>
                    <?php if (!empty($type_stats)): ?>
                        <table class="table table-dark table-hover">
                            <thead><tr><th>Type</th><th class="text-end">Count</th></tr></thead>
                            <tbody>
                                <?php foreach ($type_stats as $t): ?>
                                    <tr>
                                        <td><span style="text-transform: capitalize;"><?= htmlspecialchars($t['type']) ?></span></td>
                                        <td class="text-end"><?= number_format($t['count']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="empty-state"><i class="fas fa-tags"></i><p>No notification types recorded yet.</p></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Template Tabs -->
        <div class="section-card">
            <h3><i class="fas fa-file-code"></i>Message Templates</h3>
            <ul class="nav nav-tabs-custom mb-3" role="tablist">
                <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#sms-tab">SMS (<?= $stats['sms_templates'] ?? 0 ?>)</a></li>
                <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#wa-tab">WhatsApp (<?= $stats['wa_templates'] ?? 0 ?>)</a></li>
            </ul>
            
            <div class="tab-content">
                <div class="tab-pane fade show active" id="sms-tab">
                    <?php if (!empty($template_stats['sms'])): ?>
                        <?php foreach ($template_stats['sms'] as $t): ?>
                            <div class="template-card">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="name"><?= htmlspecialchars($t['template_name']) ?></span>
                                    <span class="badge-channel badge-sms"><?= htmlspecialchars($t['template_code']) ?></span>
                                </div>
                                <div class="body"><?= htmlspecialchars(substr($t['body'] ?? '', 0, 150)) ?>...</div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state"><i class="fas fa-sms"></i><p>No SMS templates configured.</p></div>
                    <?php endif; ?>
                </div>
                
                <div class="tab-pane fade" id="wa-tab">
                    <?php if (!empty($template_stats['whatsapp'])): ?>
                        <?php foreach ($template_stats['whatsapp'] as $t): ?>
                            <div class="template-card">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="name"><?= htmlspecialchars($t['template_name']) ?></span>
                                    <div>
                                        <span class="badge-channel badge-whatsapp"><?= htmlspecialchars($t['category'] ?? '') ?></span>
                                        <span class="badge-status badge-<?= ($t['status'] ?? '') === 'approved' ? 'sent' : 'pending' ?>"><?= htmlspecialchars($t['status'] ?? 'draft') ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state"><i class="fab fa-whatsapp"></i><p>No WhatsApp templates configured.</p></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Recent Activity -->
        <div class="section-card">
            <h3><i class="fas fa-history"></i>Recent Activity</h3>
            <?php if (!empty($recent_logs)): ?>
                <table class="table table-dark table-hover">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Type</th>
                            <th>Channel</th>
                            <th>Recipient</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_logs as $log): ?>
                            <tr>
                                <td style="white-space: nowrap;"><?= date('d M, H:i', strtotime($log['created_at'] ?? '')) ?></td>
                                <td style="text-transform: capitalize;"><?= htmlspecialchars($log['type'] ?? '') ?></td>
                                <td><span class="badge-channel badge-<?= $log['channel'] ?? 'email' ?>"><?= strtoupper($log['channel'] ?? 'email') ?></span></td>
                                <td><?= htmlspecialchars(($log['user_name'] ?? $log['recipient_token'] ?? 'System')) ?></td>
                                <td><span class="badge-status badge-<?= $log['status'] ?? 'sent' ?>"><?= ucfirst($log['status'] ?? 'sent') ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state"><i class="fas fa-history"></i><p>No notification activity yet. Notifications sent during login/registration will appear here.</p></div>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
