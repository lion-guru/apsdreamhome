<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= $base ?? BASE_URL ?>"><i class="fas fa-home"></i></a></li>
                    <li class="breadcrumb-item"><a href="<?= $base ?? BASE_URL ?>sustainability">Sustainability</a></li>
                    <li class="breadcrumb-item active">Resources</li>
                </ol>
            </nav>
            <h1 class="display-5 fw-bold"><i class="fas fa-book me-3 text-success"></i><?= ($page_title ?? 'Resources') ?></h1>
        </div>
    </div>

    <?php $res = $resources ?? []; ?>

    <?php foreach ($res as $category => $items): ?>
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white border-0"><h5 class="mb-0"><i class="fas fa-<?= $category === 'calculators' ? 'calculator' : ($category === 'guides' ? 'book' : 'flask') ?> me-2 text-success"></i><?= ucfirst($category) ?></h5></div>
        <div class="card-body aps-cp-card-body">
            <div class="row g-3">
                <?php foreach ($items as $name => $desc): ?>
                <div class="col-md-6">
                    <div class="d-flex align-items-center border rounded p-3 h-100">
                        <i class="fas fa-arrow-right text-success me-3"></i>
                        <div>
                            <strong class="small d-block"><?= ucfirst(str_replace('_', ' ', $name)) ?></strong>
                            <small class="text-muted"><?= htmlspecialchars($desc, ENT_QUOTES, 'UTF-8') ?></small>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php if (empty($res)): ?><div class="alert alert-info">No resources available.</div><?php endif; ?>
</div>
