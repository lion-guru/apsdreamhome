<?php

// TODO: Add proper error handling with try-catch blocks

?>

<div class="container-fluid mt-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0 text-gray-800">
                    <i class="fas fa-search mr-2"></i>
                    Farmer Search Results
                </h1>
                <div class="d-flex">
                    <!-- Search Form -->
                    <form class="form-inline mr-3" method="GET" action="<?php echo BASE_URL; ?>/farmers/search">
    <?php echo CSRFProtection::csrfField(); ?>
                        <div class="input-group">
                            <input type="text"
                                   name="q"
                                   class="form-control"
                                   placeholder="Search Farmers..."
                                   value="<?= htmlspecialchars($search_term ?? '') ?>">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="submit">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                    <a href="<?php echo BASE_URL; ?>/farmers" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-2"></i>View All Farmers
                    </a>
                </div>
            </div>

            <?php if ($search_term): ?>
                <div class="mt-3">
                    <p class="text-muted">
                        "<strong><?= htmlspecialchars($search_term ?? '') ?></strong>" results for
                        <span class="badge badge-primary ml-2">
                            <?= count($farmers) ?> farmers found
                        </span>
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Search Results -->
    <div class="row">
        <div class="col-12">
            <?php if (empty($farmers)): ?>
                <div class="card shadow">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-search fa-4x text-muted mb-4"></i>
                        <h4 class="text-muted">No results found</h4>
                        <p class="text-muted mb-4">
                            "<strong><?= htmlspecialchars($search_term ?? '') ?></strong>" results for No farmers found.
                        </p>
                        <div class="d-flex justify-content-center">
                            <a href="<?php echo BASE_URL; ?>/farmers/create" class="btn btn-primary mr-3">
                                <i class="fas fa-plus mr-2"></i>Add New Farmer
                            </a>
                            <button type="button" class="btn btn-secondary" onclick="clearSearch()">
                                <i class="fas fa-times mr-2"></i>Clear Search
                            </button>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-users mr-2"></i>Search Results
                        </h6>
                    </div>
                    <div class="card-body aps-cp-card-body">
                        <div class="row">
                            <?php foreach ($farmers as $farmer): ?>
                                <div class="col-lg-6 col-xl-4 mb-4">
                                    <div class="card border-left-primary h-100">
                                        <div class="card-body aps-cp-card-body">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="farmer-avatar mr-3">
                                                    <?= strtoupper(substr($farmer['name'], 0, 1)) ?>
                                                </div>
                                                <div>
                                                    <h6 class="mb-1 font-weight-bold">
                                                        <?= htmlspecialchars($farmer['name'] ?? '') ?>
                                                    </h6>
                                                    <small class="text-muted">
                                                        ID: <?= $farmer['id'] ?>
                                                    </small>
                                                </div>
                                            </div>

                                            <div class="farmer-info">
                                                <div class="info-item mb-2">
                                                    <i class="fas fa-phone mr-2 text-success"></i>
                                                    <a href="tel:<?= htmlspecialchars($farmer['phone'] ?? '') ?>" class="text-decoration-none">
                                                        <?= htmlspecialchars($farmer['phone'] ?? '') ?>
                                                    </a>
                                                </div>

                                                <?php if ($farmer['email']): ?>
                                                    <div class="info-item mb-2">
                                                        <i class="fas fa-envelope mr-2 text-primary"></i>
                                                        <a href="mailto:<?= htmlspecialchars($farmer['email'] ?? '') ?>" class="text-decoration-none">
                                                            <?= htmlspecialchars($farmer['email'] ?? '') ?>
                                                        </a>
                                                    </div>
                                                <?php endif; ?>

                                                <div class="info-item mb-2">
                                                    <i class="fas fa-map-marker-alt mr-2 text-info"></i>
                                                    <span>
                                                        <?= htmlspecialchars($farmer['state_name'] ?? 'N/A') ?>,
                                                        <?= htmlspecialchars($farmer['district_name'] ?? 'N/A') ?>
                                                    </span>
                                                </div>

                                                <div class="info-item mb-3">
                                                    <i class="fas fa-map mr-2 text-warning"></i>
                                                    <span class="badge badge-success">
                                                        <?= $farmer['total_holdings'] ?? 0 ?> Holdings
                                                    </span>
                                                    <?php if (!empty($farmer['total_area'])): ?>
                                                        <small class="text-muted ml-2">
                                                            <?= number_format($farmer['total_area'], 2) ?> Acres
                                                        </small>
                                                    <?php endif; ?>
                                                </div>
                                            </div>

                                            <div class="btn-group-vertical btn-group-sm w-100">
                                                <a href="<?php echo BASE_URL; ?>/farmers/<?= $farmer['id'] ?>"
                                                   class="btn btn-outline-info btn-sm">
                                                    <i class="fas fa-eye mr-1"></i>View Details
                                                </a>
                                                <a href="<?php echo BASE_URL; ?>/farmers/<?= $farmer['id'] ?>/edit"
                                                   class="btn btn-outline-warning btn-sm">
                                                    <i class="fas fa-edit mr-1"></i>Edit
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Search Suggestions -->
    <?php if ($search_term): ?>
        <div class="row">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header aps-cp-card-header">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-lightbulb mr-2"></i>Search Suggestions
                        </h6>
                    </div>
                    <div class="card-body aps-cp-card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Search by name:</h6>
                                <ul class="list-unstyled">
                                    <li><a href="<?php echo BASE_URL; ?>/farmers/search?q=राम" class="text-decoration-none">राम</a></li>
                                    <li><a href="<?php echo BASE_URL; ?>/farmers/search?q=सिंह" class="text-decoration-none">सिंह</a></li>
                                    <li><a href="<?php echo BASE_URL; ?>/farmers/search?q=कुमार" class="text-decoration-none">कुमार</a></li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6>Search by phone:</h6>
                                <ul class="list-unstyled">
                                    <li><a href="<?php echo BASE_URL; ?>/farmers/search?q=700" class="text-decoration-none">700xxxxxxx</a></li>
                                    <li><a href="<?php echo BASE_URL; ?>/farmers/search?q=800" class="text-decoration-none">800xxxxxxx</a></li>
                                    <li><a href="<?php echo BASE_URL; ?>/farmers/search?q=900" class="text-decoration-none">900xxxxxxx</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
function clearSearch() {
    window.location.href = '/farmers/search';
}
</script>

<style>
.farmer-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 20px;
}

.card {
    border-radius: 12px;
    border: none;
    box-shadow: 0 0 20px rgba(0,0,0,0.08);
    transition: transform 0.2s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 25px rgba(0,0,0,0.15);
}

.info-item {
    display: flex;
    align-items: center;
    font-size: 0.9em;
}

.badge {
    font-size: 0.8em;
}

.btn-group-vertical .btn {
    margin-bottom: 0.25rem;
}

.btn-group-vertical .btn:last-child {
    margin-bottom: 0;
}

.search-suggestions {
    background: #f8f9fc;
    border-radius: 8px;
    padding: 1rem;
}

@media (max-width: 768px) {
    .farmer-avatar {
        width: 40px;
        height: 40px;
        font-size: 16px;
    }
}
</style>


