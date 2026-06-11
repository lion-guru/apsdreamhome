<?php $pageTitle = 'Lead Scoring Details'; ?>
<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title"><i class="fas fa-star me-2"></i>Lead Scoring Details</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/admin/leads">Leads</a></li>
                    <li class="breadcrumb-item"><a href="/admin/leads/scoring">Scoring</a></li>
                    <li class="breadcrumb-item active">Score Detail</li>
                </ul>
            </div>
            <div class="col-auto">
                <a href="/admin/leads/scoring" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back to Scoring</a>
            </div>
        </div>
    </div>
    <?php if (empty($score)): ?>
        <div class="text-center py-5 text-muted"><i class="fas fa-star fa-4x d-block mb-3"></i><h5>Score data not found</h5></div>
    <?php else: ?>
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 text-center">
                <div class="card-body py-4">
                    <div style="width:120px;height:120px;border-radius:50%;margin:0 auto 1rem;background:conic-gradient(#0d6efd <?= min($score['total_score'] ?? 0, 100) ?>%, #e9ecef 0);display:flex;align-items:center;justify-content:center">
                        <div style="width:90px;height:90px;border-radius:50%;background:#fff;display:flex;align-items:center;justify-content:center;flex-direction:column"><span style="font-size:28px;font-weight:700;color:#0d6efd;line-height:1"><?= $score['total_score'] ?? 0 ?></span><small style="font-size:11px;color:#6c757d">/100</small></div>
                    </div>
                    <h5 class="mb-1">Lead Score</h5>
                    <span class="badge bg-<?= ($score['total_score'] ?? 0) >= 70 ? 'success' : (($score['total_score'] ?? 0) >= 40 ? 'warning' : 'danger') ?>-subtle text-<?= ($score['total_score'] ?? 0) >= 70 ? 'success' : (($score['total_score'] ?? 0) >= 40 ? 'warning' : 'danger') ?> rounded-pill px-3"><?= ($score['total_score'] ?? 0) >= 70 ? 'Hot' : (($score['total_score'] ?? 0) >= 40 ? 'Warm' : 'Cold') ?></span>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-chart-simple me-2"></i>Score Breakdown</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="row mb-3"><div class="col-sm-6 text-muted">Lead ID</div><div class="col-sm-6"><strong>#<?= $score['lead_id'] ?? '-' ?></strong></div></div>
                    <div class="row mb-3"><div class="col-sm-6 text-muted">Budget Score</div><div class="col-sm-6"><?= $score['budget_score'] ?? 0 ?> / 25</div></div>
                    <div class="row mb-3"><div class="col-sm-6 text-muted">Timeline Score</div><div class="col-sm-6"><?= $score['timeline_score'] ?? 0 ?> / 25</div></div>
                    <div class="row mb-3"><div class="col-sm-6 text-muted">Location Score</div><div class="col-sm-6"><?= $score['location_score'] ?? 0 ?> / 25</div></div>
                    <div class="row mb-3"><div class="col-sm-6 text-muted">Engagement Score</div><div class="col-sm-6"><?= $score['engagement_score'] ?? 0 ?> / 25</div></div>
                    <div class="row"><div class="col-sm-6 text-muted">Last Updated</div><div class="col-sm-6"><?= $score['updated_at'] ?? date('Y-m-d H:i') ?></div></div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
