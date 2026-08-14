<?php $pipeline = $pipeline ?? []; $funnelMetrics = $funnelMetrics ?? []; ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Online Lead Nurturing</h4>
</div>
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body aps-cp-card-body">
                <h3 class="text-primary"><?= $funnelMetrics['total_leads'] ?? 0 ?></h3>
                <small class="text-muted">Total Leads</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body aps-cp-card-body">
                <h3 class="text-success"><?= $funnelMetrics['qualified'] ?? 0 ?></h3>
                <small class="text-muted">Qualified</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body aps-cp-card-body">
                <h3 class="text-warning"><?= $funnelMetrics['conversion_rate'] ?? 0 ?>%</h3>
                <small class="text-muted">Conversion Rate</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body aps-cp-card-body">
                <h3 class="text-info"><?= $funnelMetrics['avg_days_to_close'] ?? 0 ?></h3>
                <small class="text-muted">Avg Days to Close</small>
            </div>
        </div>
    </div>
</div>
<div class="row flex-nowrap overflow-auto pb-3" class="style-93303">
    <?php $stages = [
        'new' => ['label' => 'New', 'color' => 'secondary'],
        'contacted' => ['label' => 'Contacted', 'color' => 'info'],
        'interested' => ['label' => 'Interested', 'color' => 'primary'],
        'qualified' => ['label' => 'Qualified', 'color' => 'success'],
        'viewing' => ['label' => 'Viewing', 'color' => 'warning'],
        'negotiating' => ['label' => 'Negotiating', 'color' => 'warning'],
        'closed' => ['label' => 'Closed', 'color' => 'dark'],
    ]; ?>
    <?php foreach ($stages as $key => $stage): ?>
        <div class="col-md-3 col-lg-2" class="style-30890">
            <div class="card h-100">
                <div class="card-header bg-<?= $stage['color'] ?> text-white d-flex justify-content-between align-items-center py-2">
                    <small class="fw-bold"><?= $stage['label'] ?></small>
                    <span class="badge bg-light text-dark"><?= count($pipeline[$key] ?? []) ?></span>
                </div>
                <div class="card-body p-2" class="style-37139">
                    <?php $items = $pipeline[$key] ?? []; ?>
                    <?php if (empty($items)): ?>
                        <p class="text-muted text-center small py-4">No leads</p>
                    <?php else: ?>
                        <?php foreach ($items as $lead): ?>
                            <div class="card mb-2 border">
                                <div class="card-body p-2">
                                    <strong class="small"><?= htmlspecialchars($lead['name'] ?? 'Unknown') ?></strong>
                                    <small class="d-block text-muted"><?= htmlspecialchars($lead['phone'] ?? '-') ?></small>
                                    <small class="d-block text-muted"><?= htmlspecialchars($lead['interest'] ?? 'General') ?></small>
                                    <small class="d-block">Score: <strong><?= $lead['score'] ?? $lead['lead_score'] ?? 'N/A' ?></strong></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
