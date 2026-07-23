<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= $base ?? BASE_URL ?>"><i class="fas fa-home"></i></a></li>
                    <li class="breadcrumb-item"><a href="<?= $base ?? BASE_URL ?>sustainability">Sustainability</a></li>
                    <li class="breadcrumb-item active">Awards</li>
                </ol>
            </nav>
            <h1 class="display-5 fw-bold"><i class="fas fa-trophy me-3 text-warning"></i><?= ($page_title ?? 'Awards') ?></h1>
        </div>
    </div>

    <?php $ad = $awards_data ?? []; $received = $ad['received_awards'] ?? []; $nominations = $ad['nominations'] ?? []; ?>

    <h3 class="mb-4"><i class="fas fa-medal me-2 text-warning"></i>Received Awards</h3>
    <div class="row g-4 mb-5">
        <?php if (empty($received)): ?><div class="col-12"><div class="alert alert-info">No awards received yet.</div></div><?php endif; ?>
        <?php foreach ($received as $award): ?>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 text-center h-100">
                <div class="card-body aps-cp-card-body">
                    <i class="fas fa-trophy fa-4x text-warning mb-3"></i>
                    <h5><?= ($award['award'] ?? '') ?></h5>
                    <p class="small text-muted mb-1"><?= ($award['organization'] ?? '') ?></p>
                    <p class="small text-muted">Category: <?= ($award['category'] ?? '') ?></p>
                    <small class="text-muted">Received: <?= ($award['date_received'] ?? '') ?></small>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <h3 class="mb-4"><i class="fas fa-hourglass-half me-2 text-info"></i>Current Nominations</h3>
    <div class="row g-4">
        <?php if (empty($nominations)): ?><div class="col-12"><div class="alert alert-info">No current nominations.</div></div><?php endif; ?>
        <?php foreach ($nominations as $nom): ?>
        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body d-flex align-items-center">
                    <i class="fas fa-award fa-3x text-info me-3"></i>
                    <div>
                        <h5 class="mb-1"><?= ($nom['award'] ?? '') ?></h5>
                        <p class="small text-muted mb-0"><?= ($nom['organization'] ?? '') ?> | Category: <?= ($nom['category'] ?? '') ?></p>
                        <small class="text-muted">Nominated: <?= ($nom['nomination_date'] ?? '') ?></small>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
