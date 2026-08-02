<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= $base ?? BASE_URL ?>"><i class="fas fa-home"></i></a></li>
                    <li class="breadcrumb-item"><a href="<?= $base ?? BASE_URL ?>metaverse/collaborative-spaces">Spaces</a></li>
                    <li class="breadcrumb-item active">Create Space</li>
                </ol>
            </nav>
            <h1 class="display-5 fw-bold"><i class="fas fa-plus-circle me-3 text-warning"></i><?= ($page_title ?? 'Create Space') ?></h1>
        </div>
    </div>

    <?php $space_environments = $space_environments ?? []; ?>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0"><i class="fas fa-cog me-2 text-warning"></i>Space Configuration</h5>
                </div>
                <div class="card-body aps-cp-card-body">
                    <form method="POST" action="<?= $base ?? BASE_URL ?>metaverse/create-space">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Space Name</label>
                                <input type="text" name="name" class="form-control" required placeholder="e.g. Design Team Room">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Max Participants</label>
                                <input type="number" name="max_participants" class="form-control" value="10" min="2" max="100">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Description</label>
                                <textarea name="description" class="form-control" rows="3" placeholder="Describe the purpose of this space..."></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Environment</label>
                                <select name="environment" class="form-select">
                                    <?php foreach ($space_environments as $key => $env): ?>
                                    <option value="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>"><?= ($env['name'] ?? $key) ?> (up to <?= ($env['capacity'] ?? 20) ?> people)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Visibility</label>
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" name="is_public" id="isPublic" checked>
                                    <label class="form-check-label" for="isPublic">Public Space (anyone can join)</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-warning btn-lg"><i class="fas fa-plus me-2"></i>Create Space</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <h5 class="mb-3"><i class="fas fa-th-large me-2 text-muted"></i>Available Environments</h5>
            <?php foreach ($space_environments as $key => $env): ?>
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-body aps-cp-card-body">
                    <h6><?= ($env['name'] ?? $key) ?></h6>
                    <p class="small text-muted mb-2"><?= ($env['description'] ?? '') ?></p>
                    <small class="text-muted"><i class="fas fa-user me-1"></i>Capacity: <?= ($env['capacity'] ?? 20) ?></small>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
