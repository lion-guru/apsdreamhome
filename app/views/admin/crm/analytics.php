<?php
$page_title = $page_title ?? 'CRM Analytics';
$funnel = $funnel ?? ['total_leads'=>0,'contacted'=>0,'qualified'=>0,'site_visit'=>0,'proposal'=>0,'negotiation'=>0,'won'=>0,'lost'=>0,'conversion_rate'=>0];
$source_analytics = $source_analytics ?? [];
$agent_performance = $agent_performance ?? [];
$pipeline_value = $pipeline_value ?? ['total'=>0,'by_stage'=>[]];
$current_period = $current_period ?? '30d';

$periodLabels = ['7d'=>'Last 7 Days','30d'=>'Last 30 Days','90d'=>'Last 90 Days','12m'=>'Last 12 Months'];
?>

<style>
.analytics-kpi{background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);color:#fff;border-radius:16px;padding:24px;position:relative;overflow:hidden}
.analytics-kpi::after{content:'';position:absolute;top:-30px;right:-30px;width:100px;height:100px;border-radius:50%;background:rgba(255,255,255,.1)}
.analytics-kpi .kpi-val{font-size:36px;font-weight:800;line-height:1}
.analytics-kpi .kpi-label{font-size:12px;opacity:.8;text-transform:uppercase;letter-spacing:1px;margin-top:4px}
.analytics-kpi .kpi-change{font-size:13px;margin-top:8px;display:inline-flex;align-items:center;gap:4px;padding:4px 10px;border-radius:20px;background:rgba(255,255,255,.2)}
.analytics-kpi.kpi-success{background:linear-gradient(135deg,#10b981 0%,#059669 100%)}
.analytics-kpi.kpi-warning{background:linear-gradient(135deg,#f59e0b 0%,#d97706 100%)}
.analytics-kpi.kpi-danger{background:linear-gradient(135deg,#ef4444 0%,#dc2626 100%)}
.analytics-kpi.kpi-info{background:linear-gradient(135deg,#3b82f6 0%,#2563eb 100%)}
.funnel-bar{height:40px;border-radius:8px;display:flex;align-items:center;padding:0 16px;color:#fff;font-weight:600;font-size:13px;margin-bottom:8px;transition:.3s;position:relative;overflow:hidden}
.funnel-bar:hover{transform:translateX(4px)}
.funnel-bar .funnel-count{margin-left:auto;font-size:18px;font-weight:800}
.funnel-bar .funnel-pct{position:absolute;right:100px;font-size:11px;opacity:.8}
.source-row{display:flex;align-items:center;gap:12px;padding:12px 0;border-bottom:1px solid #f5f5f5}
.source-row:last-child{border:none}
.source-bar-bg{flex:1;height:8px;background:#e9ecef;border-radius:4px;overflow:hidden}
.source-bar-fill{height:100%;border-radius:4px;transition:width .6s ease}
.agent-avatar{width:40px;height:40px;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:16px;flex-shrink:0}
.pipeline-stage{text-align:center;padding:16px 8px;border-radius:12px;border:1px solid #e9ecef;transition:.3s}
.pipeline-stage:hover{transform:translateY(-3px);box-shadow:0 6px 20px rgba(0,0,0,.08)}
.pipeline-stage .stage-val{font-size:28px;font-weight:800}
.pipeline-stage .stage-label{font-size:11px;text-transform:uppercase;letter-spacing:1px;color:#888;margin-top:4px}
.pipeline-stage .stage-value{font-size:13px;color:#555;margin-top:8px}
.period-btn{border-radius:8px;padding:8px 16px;font-weight:500;border:1px solid #e0e0e0;background:#fff;transition:.2s}
.period-btn.active{background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;border-color:transparent}
</style>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h2 class="mb-1 fw-bold"><i class="fas fa-chart-line me-2 text-primary"></i>CRM Analytics</h2>
            <p class="text-muted mb-0">Conversion funnel, source performance, and revenue pipeline</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <?php foreach ($periodLabels as $val => $label): ?>
                <a href="?period=<?= $val ?>" class="period-btn <?= $current_period === $val ? 'active' : '' ?>"><?= $label ?></a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- KPI Row -->
    <?php
    $totalLeads = (int)($funnel['total_leads'] ?? 0);
    $wonLeads = (int)($funnel['won'] ?? 0);
    $convRate = (float)($funnel['conversion_rate'] ?? 0);
    $pipelineTotal = (float)($pipeline_value['total'] ?? 0);
    ?>
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="analytics-kpi">
                <div class="kpi-val"><?= number_format($totalLeads) ?></div>
                <div class="kpi-label">Total Leads</div>
                <div class="kpi-change"><i class="fas fa-arrow-up"></i> All time</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="analytics-kpi kpi-success">
                <div class="kpi-val"><?= number_format($wonLeads) ?></div>
                <div class="kpi-label">Deals Won</div>
                <div class="kpi-change"><i class="fas fa-trophy"></i> Closed</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="analytics-kpi kpi-warning">
                <div class="kpi-val"><?= $convRate ?>%</div>
                <div class="kpi-label">Conversion Rate</div>
                <div class="kpi-change"><i class="fas fa-percentage"></i> Won/Total</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="analytics-kpi kpi-info">
                <div class="kpi-val">â‚¹<?= number_format($pipelineTotal / 100000, 1) ?>L</div>
                <div class="kpi-label">Pipeline Value</div>
                <div class="kpi-change"><i class="fas fa-rupee-sign"></i> Weighted</div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <!-- Conversion Funnel -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-light py-3">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-filter me-2"></i>Conversion Funnel</h6>
                </div>
                <div class="card-body">
                    <?php
                    $funnelStages = [
                        'new_lead' => ['New Lead', '#667eea', $funnel['total_leads'] ?? 0],
                        'contacted' => ['Contacted', '#3b82f6', $funnel['contacted'] ?? 0],
                        'qualified' => ['Qualified', '#8b5cf6', $funnel['qualified'] ?? 0],
                        'site_visit' => ['Site Visit', '#f59e0b', $funnel['site_visit'] ?? 0],
                        'proposal' => ['Proposal', '#ec4899', $funnel['proposal'] ?? 0],
                        'negotiation' => ['Negotiation', '#ef4444', $funnel['negotiation'] ?? 0],
                        'won' => ['Won', '#10b981', $funnel['won'] ?? 0],
                    ];
                    $funnelCnts = array_map(function($s) { return (int)$s[2]; }, $funnelStages);
                    $maxFunnel = max($funnelCnts);
                    if ($maxFunnel < 1) $maxFunnel = 1;
                    foreach ($funnelStages as $key => [$label, $color, $count]):
                        $width = max(5, ($count / $maxFunnel) * 100);
                        $pct = $totalLeads > 0 ? round(($count / $totalLeads) * 100, 1) : 0;
                    ?>
                        <div class="funnel-bar" class="style-60037">
                            <span><?= $label ?></span>
                            <span class="funnel-pct"><?= $pct ?>%</span>
                            <span class="funnel-count"><?= number_format($count) ?></span>
                        </div>
                    <?php endforeach; ?>
                    <div class="mt-3 p-3 bg-light rounded text-center">
                        <small class="text-muted">Overall Conversion</small>
                        <div class="style-33656"><?= $convRate ?>%</div>
                        <small class="text-muted"><?= number_format($wonLeads) ?> won from <?= number_format($totalLeads) ?> leads</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pipeline by Stage -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-light py-3">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-project-diagram me-2"></i>Pipeline by Stage</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <?php
                        $stageColors = ['new'=>'primary','contacted'=>'info','qualified'=>'info','site_visit'=>'warning','proposal'=>'danger','negotiation'=>'danger','booking'=>'success','won'=>'success','lost'=>'secondary','nurture'=>'warning'];
                        foreach ($pipeline_value['by_stage'] ?? [] as $stage):
                            $color = $stageColors[$stage['stage']] ?? 'secondary';
                        ?>
                            <div class="col-4">
                                <div class="pipeline-stage">
                                    <div class="stage-val text-<?= $color ?>"><?= $stage['count'] ?></div>
                                    <div class="stage-label"><?= ucfirst(str_replace('_',' ',$stage['stage'])) ?></div>
                                    <div class="stage-value">â‚¹<?= number_format($stage['value'] / 100000, 1) ?>L</div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <?php if (empty($pipeline_value['by_stage'])): ?>
                            <div class="col-12 text-center py-4">
                                <p class="text-muted">No pipeline data yet</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <!-- Source Analytics -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-light py-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-bullseye me-2"></i>Source Performance</h6>
                    <span class="badge bg-primary"><?= count($source_analytics) ?> sources</span>
                </div>
                <div class="card-body" class="style-23214">
                    <?php if (!empty($source_analytics)): ?>
                        <?php
                        $srcLeadCnts = array_map(function($s) { return (int)($s['total_leads'] ?? 0); }, $source_analytics);
                        $maxSrcLeads = max($srcLeadCnts);
                        if ($maxSrcLeads < 1) $maxSrcLeads = 1;
                        $sourceColors = ['#667eea','#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6','#ec4899','#06b6d4','#84cc16','#f97316'];
                        $ci = 0;
                        foreach ($source_analytics as $src):
                            $total = (int)($src['total_leads'] ?? 0);
                            $won = (int)($src['won_leads'] ?? $src['converted'] ?? 0);
                            $rate = $total > 0 ? round(($won / $total) * 100, 1) : 0;
                            $width = max(3, ($total / $maxSrcLeads) * 100);
                            $color = $sourceColors[$ci % count($sourceColors)];
                            $ci++;
                        ?>
                            <div class="source-row">
                                <div class="style-34092">
                                    <strong class="style-87981"><?= htmlspecialchars(ucfirst(str_replace('_',' ',$src['source'] ?? ''))) ?></strong>
                                </div>
                                <div class="source-bar-bg">
                                    <div class="source-bar-fill" class="style-4558"></div>
                                </div>
                                <div class="style-70567">
                                    <span class="fw-bold"><?= $total ?></span>
                                    <small class="text-muted d-block">leads</small>
                                </div>
                                <div class="style-70567">
                                    <span class="badge bg-<?= $rate >= 50 ? 'success' : ($rate >= 20 ? 'warning' : 'danger') ?>"><?= $rate ?>%</span>
                                    <small class="text-muted d-block">won</small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted text-center py-4">No source data available</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Agent Performance -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-light py-3">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-users-cog me-2"></i>Agent Performance</h6>
                </div>
                <div class="card-body" class="style-23214">
                    <?php if (!empty($agent_performance)): ?>
                        <?php
                        $agentColors = ['#667eea','#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6','#ec4899','#06b6d4'];
                        $ai = 0;
                        foreach ($agent_performance as $agent):
                            $color = $agentColors[$ai % count($agentColors)];
                            $ai++;
                        ?>
                            <div class="d-flex align-items-center gap-3 py-2" class="style-95886">
                                <div class="agent-avatar" class="style-96004"><?= strtoupper(substr($agent['name'] ?? 'A', 0, 1)) ?></div>
                                <div class="flex-grow-1">
                                    <div class="fw-bold" class="style-42715"><?= htmlspecialchars($agent['name'] ?? 'Unknown') ?></div>
                                    <small class="text-muted"><?= (int)($agent['total_leads'] ?? 0) ?> leads &middot; <?= (int)($agent['won_leads'] ?? 0) ?> won</small>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-<?= (int)($agent['conversion_rate'] ?? 0) >= 50 ? 'success' : ((int)($agent['conversion_rate'] ?? 0) >= 20 ? 'warning' : 'danger') ?>"><?= (int)($agent['conversion_rate'] ?? 0) ?>%</span>
                                    <small class="text-muted d-block">conv.</small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted text-center py-4">No agent data available</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Insights -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-light py-3">
            <h6 class="mb-0 fw-bold"><i class="fas fa-lightbulb me-2 text-warning"></i>Quick Insights</h6>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <?php
                $bestSource = !empty($source_analytics) ? $source_analytics[0] : null;
                $bestAgent = !empty($agent_performance) ? $agent_performance[0] : null;
                ?>
                <?php if ($bestSource): ?>
                <div class="col-md-4">
                    <div class="p-3 bg-success-subtle rounded">
                        <h6 class="text-success fw-bold mb-1"><i class="fas fa-trophy me-1"></i> Best Source</h6>
                        <p class="mb-0"><strong><?= htmlspecialchars(ucfirst(str_replace('_',' ',$bestSource['source'] ?? ''))) ?></strong> with <?= (int)($bestSource['total_leads'] ?? 0) ?> leads</p>
                    </div>
                </div>
                <?php endif; ?>
                <?php if ($bestAgent): ?>
                <div class="col-md-4">
                    <div class="p-3 bg-primary-subtle rounded">
                        <h6 class="text-primary fw-bold mb-1"><i class="fas fa-user-tie me-1"></i> Top Agent</h6>
                        <p class="mb-0"><strong><?= htmlspecialchars($bestAgent['name'] ?? '') ?></strong> â€” <?= (int)($bestAgent['conversion_rate'] ?? 0) ?>% conversion</p>
                    </div>
                </div>
                <?php endif; ?>
                <div class="col-md-4">
                    <div class="p-3 bg-warning-subtle rounded">
                        <h6 class="text-warning fw-bold mb-1"><i class="fas fa-exclamation-triangle me-1"></i> Needs Attention</h6>
                        <p class="mb-0"><strong><?= $totalLeads - $wonLeads - (int)($funnel['lost'] ?? 0) ?></strong> leads in pipeline, <?= $convRate ?>% conversion</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
