<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= $base ?? BASE_URL ?>"><i class="fas fa-home"></i></a></li>
                    <li class="breadcrumb-item"><a href="<?= $base ?? BASE_URL ?>sustainability">Sustainability</a></li>
                    <li class="breadcrumb-item active">Partnerships</li>
                </ol>
            </nav>
            <h1 class="display-5 fw-bold"><i class="fas fa-handshake me-3 text-success"></i><?= ($page_title ?? 'Partnerships') ?></h1>
        </div>
    </div>

    <?php $ps = $partnerships ?? []; ?>

    <?php foreach ($ps as $category => $partners): ?>
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white border-0"><h5 class="mb-0"><i class="fas fa-<?= $category === 'environmental_ngos' ? 'tree' : ($category === 'government_agencies' ? 'university' : 'building') ?> me-2 text-success"></i><?= ucfirst(str_replace('_', ' ', $category)) ?></h5></div>
        <div class="card-body aps-cp-card-body">
            <div class="row g-4">
                <?php foreach ($partners as $key => $partner): ?>
                <div class="col-md-6">
                    <div class="border rounded p-3 h-100">
                        <h6><?= ($partner['partner'] ?? $key) ?></h6>
                        <p class="small text-muted mb-2"><?= ($partner['collaboration'] ?? '') ?></p>
                        <?php if (!empty($partner['joint_projects'] ?? [])): ?>
                        <div><small><strong>Joint Projects:</strong></small></div>
                        <ul class="list-unstyled mb-0"><?php foreach ($partner['joint_projects'] as $project): ?><li class="small"><i class="fas fa-check-circle text-success me-1"></i><?= htmlspecialchars($project, ENT_QUOTES, 'UTF-8') ?></li><?php endforeach; ?></ul>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php if (empty($ps)): ?><div class="alert alert-info">No partnership data available.</div><?php endif; ?>
</div>
