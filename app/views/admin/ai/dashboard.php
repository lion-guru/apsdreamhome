<?php
$page_title = $page_title ?? 'AI System Dashboard';
$agents = $agents ?? [];
$agent_stats = $agent_stats ?? [];
$gateway_stats = $gateway_stats ?? [];
$engine_status = $engine_status ?? [];
$health = $health ?? [];
$recent_activity = $recent_activity ?? [];
?>

<style>
.ai-header{background:linear-gradient(135deg,#0f0c29 0%,#302b63 50%,#24243e 100%);color:#fff;border-radius:0 0 24px 24px;padding:32px 0;margin-bottom:24px;position:relative;overflow:hidden}
.ai-header::before{content:'';position:absolute;top:-50%;right:-10%;width:400px;height:400px;background:radial-gradient(circle,rgba(99,102,241,.2) 0%,transparent 70%);border-radius:50%}
.ai-header::after{content:'';position:absolute;bottom:-30%;left:10%;width:300px;height:300px;background:radial-gradient(circle,rgba(16,185,129,.15) 0%,transparent 70%);border-radius:50%}
.ai-stat-card{background:#fff;border-radius:16px;border:1px solid #f0f0f5;padding:20px;text-align:center;transition:.3s;position:relative;overflow:hidden}
.ai-stat-card:hover{transform:translateY(-3px);box-shadow:0 12px 32px rgba(0,0,0,.08)}
.ai-stat-card .stat-icon{width:52px;height:52px;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:22px;margin:0 auto 12px}
.ai-stat-card .stat-value{font-size:28px;font-weight:800;margin:0}
.ai-stat-card .stat-label{font-size:11px;color:#888;text-transform:uppercase;letter-spacing:1.2px;margin:4px 0 0}
.agent-card{background:#fff;border-radius:16px;border:1px solid #f0f0f5;padding:20px;transition:.3s;height:100%}
.agent-card:hover{transform:translateY(-3px);box-shadow:0 12px 32px rgba(0,0,0,.08)}
.agent-card .agent-icon{width:56px;height:56px;border-radius:16px;display:flex;align-items:center;justify-content:center;font-size:24px;margin-bottom:12px;transition:.3s}
.agent-card:hover .agent-icon{transform:scale(1.1)}
.agent-card .agent-name{font-size:15px;font-weight:700;color:#333;margin-bottom:4px}
.agent-card .agent-desc{font-size:12px;color:#888;margin-bottom:16px}
.agent-run-btn{border-radius:10px;padding:8px 16px;font-weight:600;font-size:12px;border:none;transition:.3s;cursor:pointer}
.agent-run-btn:hover{transform:translateY(-1px);box-shadow:0 4px 12px rgba(0,0,0,.15)}
.health-dot{width:10px;height:10px;border-radius:50%;display:inline-block;margin-right:6px}
.health-dot.green{background:#10b981}
.health-dot.red{background:#ef4444}
.health-dot.yellow{background:#f59e0b}
.activity-item{display:flex;align-items:flex-start;gap:10px;padding:10px 0;border-bottom:1px solid #f5f5f5;font-size:13px}
.activity-item:last-child{border:none}
.activity-dot{width:8px;height:8px;border-radius:50%;flex-shrink:0;margin-top:5px}
.gateway-bar{height:8px;border-radius:4px;background:#f0f0f5;overflow:hidden}
.gateway-bar-fill{height:100%;border-radius:4px;transition:.5s}
.section-title{font-size:16px;font-weight:700;color:#333;margin-bottom:16px;display:flex;align-items:center;gap:8px}
.section-title i{color:#6366f1}
.pulse-dot{animation:pulse 2s infinite}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.5}}
</style>

<!-- Header -->
<div class="ai-header">
    <div class="container-fluid px-4" style="position:relative;z-index:1">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h2 class="mb-1 fw-bold"><i class="fas fa-brain me-2"></i>AI System Dashboard</h2>
                <p class="mb-0 opacity-75" style="font-size:14px">5 autonomous agents — real estate intelligence engine</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="<?= BASE_URL ?>/admin/ai-system/qualifier" class="btn btn-light"><i class="fas fa-magnet me-1"></i>Lead Qualifier</a>
                <a href="<?= BASE_URL ?>/admin/ai-system/market-report" class="btn btn-light"><i class="fas fa-chart-line me-1"></i>Market Report</a>
                <form method="POST" action="<?= BASE_URL ?>/admin/ai-system/run" style="display:inline">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <input type="hidden" name="agent_type" value="qualifier">
                    <input type="hidden" name="action" value="batch">
                    <button type="submit" class="btn btn-warning fw-bold"><i class="fas fa-play me-1"></i>Run All Agents</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid px-4" style="margin-top:-12px">

    <!-- Gateway Stats -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="ai-stat-card">
                <div class="stat-icon" style="background:#eef2ff;color:#6366f1"><i class="fas fa-bolt"></i></div>
                <div class="stat-value" style="color:#6366f1"><?= number_format($gateway_stats['total_calls'] ?? 0) ?></div>
                <div class="stat-label">AI Calls Today</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="ai-stat-card">
                <div class="stat-icon" style="background:#ecfdf5;color:#10b981"><i class="fas fa-check-circle"></i></div>
                <div class="stat-value" style="color:#10b981"><?= $gateway_stats['avg_confidence'] ?? '0.00' ?></div>
                <div class="stat-label">Avg Confidence</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="ai-stat-card">
                <div class="stat-icon" style="background:#fef3c7;color:#f59e0b"><i class="fas fa-clock"></i></div>
                <div class="stat-value" style="color:#f59e0b"><?= $gateway_stats['avg_response_ms'] ?? '0' ?>ms</div>
                <div class="stat-label">Avg Response</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="ai-stat-card">
                <div class="stat-icon" style="background:#fef2f2;color:#ef4444"><i class="fas fa-database"></i></div>
                <div class="stat-value" style="color:#ef4444"><?= $health['unqualified_leads'] ?? 0 ?></div>
                <div class="stat-label">Unqualified Leads</div>
            </div>
        </div>
    </div>

    <!-- Engine Distribution -->
    <div class="row g-3 mb-4">
        <div class="col-md-8">
            <div style="background:#fff;border-radius:16px;border:1px solid #f0f0f5;padding:20px">
                <div class="section-title"><i class="fas fa-layer-group"></i> AI Engine Distribution (Today)</div>
                <?php
                $total = max(1, ($gateway_stats['total_calls'] ?? 1));
                $engines = [
                    ['name' => 'Rule Engine', 'count' => $gateway_stats['rule_calls'] ?? 0, 'color' => '#10b981'],
                    ['name' => 'Self-Learning', 'count' => $gateway_stats['sl_calls'] ?? 0, 'color' => '#6366f1'],
                    ['name' => 'Pattern Match', 'count' => $gateway_stats['pattern_calls'] ?? 0, 'color' => '#f59e0b'],
                    ['name' => 'Gemini Flash', 'count' => $gateway_stats['gemini_calls'] ?? 0, 'color' => '#ec4899'],
                ];
                ?>
                <div class="d-flex gap-3 mb-3" style="height:12px">
                    <?php foreach ($engines as $e): ?>
                        <div class="gateway-bar flex-grow-1" title="<?= $e['name'] ?>: <?= $e['count'] ?>">
                            <div class="gateway-bar-fill" style="width:<?= $total > 0 ? ($e['count'] / $total * 100) : 0 ?>%;background:<?= $e['color'] ?>"></div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="d-flex gap-3 flex-wrap" style="font-size:12px">
                    <?php foreach ($engines as $e): ?>
                        <span><span style="display:inline-block;width:10px;height:10px;border-radius:3px;background:<?= $e['color'] ?>;margin-right:4px"></span><?= $e['name'] ?>: <?= $e['count'] ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div style="background:#fff;border-radius:16px;border:1px solid #f0f0f5;padding:20px;height:100%">
                <div class="section-title"><i class="fas fa-heartbeat"></i> System Health</div>
                <div class="d-flex flex-column gap-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <span style="font-size:13px"><span class="health-dot <?= $health['gemini_api'] === 'connected' ? 'green' : 'red' ?>"></span>Gemini API</span>
                        <span class="badge <?= $health['gemini_api'] === 'connected' ? 'bg-success' : 'bg-secondary' ?>" style="font-size:11px"><?= $health['gemini_api'] === 'connected' ? 'Connected' : 'Not Configured' ?></span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between">
                        <span style="font-size:13px"><span class="health-dot green"></span>Intent Patterns</span>
                        <span class="badge bg-primary" style="font-size:11px"><?= $health['intent_patterns'] ?? 0 ?> active</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between">
                        <span style="font-size:13px"><span class="health-dot green"></span>Leads Today</span>
                        <span class="badge bg-info" style="font-size:11px"><?= $health['leads_today'] ?? 0 ?></span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between">
                        <span style="font-size:13px"><span class="health-dot yellow"></span>Scheduled Visits</span>
                        <span class="badge bg-warning" style="font-size:11px"><?= $health['scheduled_visits'] ?? 0 ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Free AI Engines Status -->
    <div class="row g-3 mb-4">
        <div class="col-12">
            <div style="background:linear-gradient(135deg,#f0fdf4 0%,#ecfdf5 100%);border-radius:16px;border:1px solid #bbf7d0;padding:20px">
                <div class="section-title" style="color:#166534"><i class="fas fa-bolt" style="color:#16a34a"></i> Free AI Engines (Cost: ₹0)</div>
                <div class="row g-3">
                    <?php foreach ($engine_status as $engine => $info): ?>
                        <div class="col-md-3">
                            <div class="d-flex align-items-center gap-3">
                                <span class="health-dot <?= $info['available'] ? 'green' : 'red' ?>"></span>
                                <div>
                                    <div style="font-size:13px;font-weight:700;color:#166534;text-transform:capitalize"><?= $engine ?></div>
                                    <div style="font-size:11px;color:#15803d"><?= $info['model'] ?? 'N/A' ?></div>
                                    <div style="font-size:10px;color:#888"><?= $info['speed'] ?? '' ?></div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- 5 AI Agents -->
    <div class="section-title"><i class="fas fa-robot"></i> AI Agents</div>
    <div class="row g-3 mb-4">
        <?php foreach ($agents as $key => $agent):
            $stat = $agent_stats[$key] ?? ['total' => 0, 'completed' => 0, 'last_run' => null];
        ?>
            <div class="col-md-4 col-lg-2_4">
                <div class="agent-card">
                    <div class="agent-icon" style="background:<?= $agent['color'] ?>15;color:<?= $agent['color'] ?>">
                        <i class="<?= $agent['icon'] ?>"></i>
                    </div>
                    <div class="agent-name"><?= $agent['name'] ?></div>
                    <div class="d-flex justify-content-between mb-3" style="font-size:12px;color:#888">
                        <span><?= $stat['completed'] ?? 0 ?> runs today</span>
                        <?php if ($stat['last_run']): ?>
                            <span class="pulse-dot" style="color:#10b981" title="Last: <?= $stat['last_run'] ?>">●</span>
                        <?php endif; ?>
                    </div>
                    <div class="d-flex gap-2">
                        <form method="POST" action="<?= BASE_URL ?>/admin/ai-system/run" style="flex:1">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                            <input type="hidden" name="agent_type" value="<?= $key ?>">
                            <input type="hidden" name="action" value="batch">
                            <button type="submit" class="agent-run-btn w-100" style="background:<?= $agent['color'] ?>;color:#fff">Run</button>
                        </form>
                        <?php if ($key === 'qualifier'): ?>
                            <a href="<?= BASE_URL ?>/admin/ai-system/qualifier" class="agent-run-btn" style="background:#f3f4f6;color:#333"><i class="fas fa-eye"></i></a>
                        <?php elseif ($key === 'market'): ?>
                            <a href="<?= BASE_URL ?>/admin/ai-system/market-report" class="agent-run-btn" style="background:#f3f4f6;color:#333"><i class="fas fa-eye"></i></a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="row g-4">
        <!-- Recent AI Activity -->
        <div class="col-lg-8">
            <div style="background:#fff;border-radius:16px;border:1px solid #f0f0f5;padding:20px">
                <div class="section-title"><i class="fas fa-history"></i> Recent AI Activity</div>
                <?php if (empty($recent_activity)): ?>
                    <div class="text-center py-4" style="color:#aaa">
                        <i class="fas fa-robot" style="font-size:48px;opacity:.2;margin-bottom:12px;display:block"></i>
                        <p class="mb-0">No AI activity today. Run an agent to get started.</p>
                    </div>
                <?php else: ?>
                    <?php foreach (array_slice($recent_activity, 0, 12) as $a):
                        $colors = ['qualify' => '#3b82f6', 'batch' => '#10b981', 'match' => '#f59e0b', 'reminders' => '#8b5cf6', 'default' => '#6b7280'];
                        $color = $colors[$a['action_type'] ?? 'default'] ?? $colors['default'];
                    ?>
                        <div class="activity-item">
                            <div class="activity-dot" style="background:<?= $color ?>"></div>
                            <div class="flex-grow-1">
                                <div style="font-weight:600"><?= htmlspecialchars($a['agent_type'] ?? '') ?></div>
                                <div style="color:#888"><?= htmlspecialchars($a['details'] ?? $a['action_type'] ?? '') ?></div>
                            </div>
                            <div style="font-size:11px;color:#aaa;white-space:nowrap"><?= date('h:i A', strtotime($a['created_at'] ?? '')) ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="col-lg-4">
            <div style="background:#fff;border-radius:16px;border:1px solid #f0f0f5;padding:20px">
                <div class="section-title"><i class="fas fa-bolt"></i> Quick Actions</div>
                <div class="d-flex flex-column gap-2">
                    <form method="POST" action="<?= BASE_URL ?>/admin/ai-system/run">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                        <input type="hidden" name="agent_type" value="qualifier">
                        <input type="hidden" name="action" value="batch">
                        <button type="submit" class="btn btn-outline-primary w-100 text-start"><i class="fas fa-magnet me-2"></i>Qualify All Unscored Leads</button>
                    </form>
                    <form method="POST" action="<?= BASE_URL ?>/admin/ai-system/run">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                        <input type="hidden" name="agent_type" value="matchmaker">
                        <input type="hidden" name="action" value="batch">
                        <button type="submit" class="btn btn-outline-success w-100 text-start"><i class="fas fa-home me-2"></i>Match Properties for All Leads</button>
                    </form>
                    <form method="POST" action="<?= BASE_URL ?>/admin/ai-system/run">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                        <input type="hidden" name="agent_type" value="scheduler">
                        <input type="hidden" name="action" value="reminders">
                        <button type="submit" class="btn btn-outline-warning w-100 text-start"><i class="fas fa-bell me-2"></i>Send Visit Reminders</button>
                    </form>
                    <form method="POST" action="<?= BASE_URL ?>/admin/ai-system/run">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                        <input type="hidden" name="agent_type" value="scheduler">
                        <input type="hidden" name="action" value="reschedule">
                        <button type="submit" class="btn btn-outline-info w-100 text-start"><i class="fas fa-calendar me-2"></i>Auto-Reschedule Missed Visits</button>
                    </form>
                    <a href="<?= BASE_URL ?>/admin/ai-system/market-report" class="btn btn-outline-dark w-100 text-start"><i class="fas fa-chart-line me-2"></i>View Market Intelligence</a>
                    <a href="<?= BASE_URL ?>/admin/ai-system/qualifier" class="btn btn-outline-dark w-100 text-start"><i class="fas fa-filter me-2"></i>Lead Qualifier Dashboard</a>
                </div>
            </div>
        </div>
    </div>
</div>
