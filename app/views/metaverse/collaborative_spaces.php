<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= $base ?? BASE_URL ?>"><i class="fas fa-home"></i></a></li>
                    <li class="breadcrumb-item"><a href="<?= $base ?? BASE_URL ?>metaverse">Metaverse</a></li>
                    <li class="breadcrumb-item active">Collaborative Spaces</li>
                </ol>
            </nav>
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="display-5 fw-bold"><i class="fas fa-users me-3 text-warning"></i><?= ($page_title ?? 'Collaborative Spaces') ?></h1>
                <a href="<?= $base ?? BASE_URL ?>metaverse/create-space" class="btn btn-warning btn-lg"><i class="fas fa-plus me-2"></i>Create Space</a>
            </div>
        </div>
    </div>

    <?php $spaces = $spaces ?? []; ?>

    <?php if (empty($spaces)): ?>
    <div class="text-center py-5">
        <i class="fas fa-users fa-5x text-muted mb-3"></i>
        <h3>No Collaborative Spaces Yet</h3>
        <p class="text-muted">Create your first space to start collaborating in the metaverse.</p>
        <a href="<?= $base ?? BASE_URL ?>metaverse/create-space" class="btn btn-warning btn-lg mt-3"><i class="fas fa-plus me-2"></i>Create Space</a>
    </div>
    <?php else: ?>
    <div class="row g-4">
        <?php foreach ($spaces as $space): ?>
        <div class="col-md-6 col-lg-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <h5 class="card-title mb-0"><?= ($space['name'] ?? 'Unnamed Space') ?></h5>
                        <span class="badge bg-<?= ($space['is_public'] ?? 0) ? 'success' : 'secondary' ?>">
                            <?= ($space['is_public'] ?? 0) ? 'Public' : 'Private' ?>
                        </span>
                    </div>
                    <p class="card-text text-muted"><?= ($space['description'] ?? 'No description') ?></p>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <small><i class="fas fa-user me-1"></i><?= ($space['participant_count'] ?? 0) ?> / <?= ($space['max_participants'] ?? 10) ?> participants</small>
                        <a href="<?= $base ?? BASE_URL ?>metaverse/collaborative-space/<?= ($space['id'] ?? '') ?>" class="btn btn-outline-warning btn-sm">Enter Space</a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
