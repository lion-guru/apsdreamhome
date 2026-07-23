<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= $base ?? BASE_URL ?>"><i class="fas fa-home"></i></a></li>
                    <li class="breadcrumb-item"><a href="<?= $base ?? BASE_URL ?>sustainability">Sustainability</a></li>
                    <li class="breadcrumb-item active">Governance</li>
                </ol>
            </nav>
            <h1 class="display-5 fw-bold"><i class="fas fa-gavel me-3 text-success"></i><?= ($page_title ?? 'Sustainability Governance') ?></h1>
        </div>
    </div>

    <?php $gd = $governance_data ?? []; $policy = $gd['sustainability_policy'] ?? []; $structure = $gd['governance_structure'] ?? []; $reporting = $gd['reporting_framework'] ?? []; ?>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0"><h5 class="mb-0"><i class="fas fa-file-alt me-2 text-primary"></i>Sustainability Policy</h5></div>
                <div class="card-body aps-cp-card-body">
                    <h6><?= ($policy['policy_document'] ?? 'N/A') ?></h6>
                    <div class="d-flex justify-content-between mb-2"><span>Last Updated</span><strong><?= ($policy['last_updated'] ?? '') ?></strong></div>
                    <div class="d-flex justify-content-between mb-2"><span>Scope</span><small><?= ($policy['scope'] ?? '') ?></small></div>
                    <hr><h6>Key Principles</h6>
                    <ul class="list-unstyled"><?php foreach (($policy['key_principles'] ?? []) as $p): ?><li class="small"><i class="fas fa-check-circle text-success me-1"></i><?= htmlspecialchars($p, ENT_QUOTES, 'UTF-8') ?></li><?php endforeach; ?></ul>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0"><h5 class="mb-0"><i class="fas fa-sitemap me-2 text-info"></i>Governance Structure</h5></div>
                <div class="card-body aps-cp-card-body">
                    <?php foreach ($structure as $key => $val): ?>
                    <div class="mb-3"><strong class="small d-block text-capitalize"><?= str_replace('_', ' ', $key) ?></strong><small class="text-muted"><?= htmlspecialchars($val, ENT_QUOTES, 'UTF-8') ?></small></div>
                    <?php endforeach; ?>
                    <?php if (empty($structure)): ?><p class="text-muted text-center py-3">No data.</p><?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0"><h5 class="mb-0"><i class="fas fa-chart-bar me-2 text-warning"></i>Reporting Framework</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="mb-3">
                        <strong class="small d-block">Standards</strong>
                        <ul class="list-unstyled"><?php foreach (($reporting['standards_followed'] ?? []) as $s): ?><li class="small"><i class="fas fa-check text-success me-1"></i><?= htmlspecialchars($s, ENT_QUOTES, 'UTF-8') ?></li><?php endforeach; ?></ul>
                    </div>
                    <div class="d-flex justify-content-between mb-2"><span>Frequency</span><strong><?= ($reporting['reporting_frequency'] ?? '') ?></strong></div>
                    <div class="d-flex justify-content-between mb-2"><span>Assurance</span><small><?= ($reporting['assurance_level'] ?? '') ?></small></div>
                    <div class="d-flex justify-content-between"><span>Transparency</span><strong class="text-success"><?= ($reporting['transparency_score'] ?? '') ?></strong></div>
                </div>
            </div>
        </div>
    </div>
</div>
