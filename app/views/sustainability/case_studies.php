<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= $base ?? BASE_URL ?>"><i class="fas fa-home"></i></a></li>
                    <li class="breadcrumb-item"><a href="<?= $base ?? BASE_URL ?>sustainability">Sustainability</a></li>
                    <li class="breadcrumb-item active">Case Studies</li>
                </ol>
            </nav>
            <h1 class="display-5 fw-bold"><i class="fas fa-book-open me-3 text-success"></i><?= ($page_title ?? 'Case Studies') ?></h1>
        </div>
    </div>

    <?php $cs = $case_studies ?? []; ?>

    <div class="row g-4">
        <?php foreach ($cs as $key => $study): ?>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body aps-cp-card-body">
                    <h5><?= ($study['title'] ?? ucfirst(str_replace('_', ' ', $key))) ?></h5>
                    <div class="mb-3">
                        <small class="text-muted fw-semibold">Challenge:</small>
                        <p class="small"><?= ($study['challenge'] ?? '') ?></p>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted fw-semibold">Solution:</small>
                        <p class="small"><?= ($study['solution'] ?? '') ?></p>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted fw-semibold">Results:</small>
                        <ul class="list-unstyled"><?php foreach (($study['results'] ?? []) as $r): ?><li class="small"><i class="fas fa-check-circle text-success me-1"></i><?= htmlspecialchars($r, ENT_QUOTES, 'UTF-8') ?></li><?php endforeach; ?></ul>
                    </div>
                    <div class="d-flex justify-content-between small text-muted">
                        <span>Timeline: <?= ($study['implementation_time'] ?? 'N/A') ?></span>
                        <span>ROI: <strong class="text-success"><?= ($study['roi_achieved'] ?? 'N/A') ?></strong></span>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php if (empty($cs)): ?><div class="col-12"><div class="alert alert-info">No case studies available.</div></div><?php endif; ?>
    </div>
</div>
