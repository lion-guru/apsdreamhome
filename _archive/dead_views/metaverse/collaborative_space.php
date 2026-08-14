<div class="container-fluid py-4">
    <?php $space = $space ?? []; $participants = $participants ?? []; ?>
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= $base ?? BASE_URL ?>"><i class="fas fa-home"></i></a></li>
                    <li class="breadcrumb-item"><a href="<?= $base ?? BASE_URL ?>metaverse/collaborative-spaces">Spaces</a></li>
                    <li class="breadcrumb-item active"><?= ($space['name'] ?? 'Space') ?></li>
                </ol>
            </nav>
            <h1 class="display-5 fw-bold"><i class="fas fa-cube me-3 text-warning"></i><?= ($space['name'] ?? 'Collaborative Space') ?></h1>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-0">
                    <div class="vr-viewport" class="style-77836">
                        <div class="d-flex align-items-center justify-content-center h-100">
                            <div class="text-center text-white">
                                <i class="fas fa-cube fa-5x mb-3 opacity-50"></i>
                                <p class="lead">3D Space Loading...</p>
                                <button class="btn btn-warning btn-lg" onclick="alert('Collaborative space viewer would open here')">
                                    <i class="fas fa-sign-in-alt me-2"></i>Enter Space
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2 text-warning"></i>Space Details</h5>
                </div>
                <div class="card-body aps-cp-card-body">
                    <p><?= ($space['description'] ?? 'No description available.') ?></p>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="border rounded p-3 text-center">
                                <small class="text-muted d-block">Max Participants</small>
                                <strong><?= ($space['max_participants'] ?? 10) ?></strong>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded p-3 text-center">
                                <small class="text-muted d-block">Environment</small>
                                <strong><?= ucfirst($space['environment'] ?? 'default') ?></strong>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded p-3 text-center">
                                <small class="text-muted d-block">Status</small>
                                <strong class="text-<?= ($space['is_public'] ?? 0) ? 'success' : 'secondary' ?>"><?= ($space['is_public'] ?? 0) ? 'Public' : 'Private' ?></strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0"><i class="fas fa-user-friends me-2 text-warning"></i>Participants (<?= count($participants) ?>)</h5>
                </div>
                <div class="card-body aps-cp-card-body">
                    <?php if (empty($participants)): ?>
                    <p class="text-muted text-center py-3">No participants yet.</p>
                    <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($participants as $p): ?>
                        <li class="list-group-item px-0 d-flex align-items-center">
                             src="<?= BASE_URL ? loading="lazy">/<?= htmlspecialchars($p['avatar'] ?? 'assets/img/avatar.png') ?>" alt="" class="rounded-circle me-3" class="style-25739" onerror="this.src='<?= BASE_URL ?>/assets/images/placeholder/hero.svg'">
                            <div>
                                <strong><?= ($p['name'] ?? 'User') ?></strong>
                                <small class="d-block text-muted"><i class="fas fa-clock me-1"></i><?= ($p['joined_at'] ?? 'Just now') ?></small>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
