<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/services">Services Directory</a></li>
            <li class="breadcrumb-item active"><?= htmlspecialchars($category['name'] ?? '') ?></li>
        </ol>
    </nav>

    <div class="row mb-4">
        <div class="col-lg-8">
            <h1 class="mb-2"><i class="<?= htmlspecialchars($category['icon'] ?? 'fas fa-building') ?> me-2 text-primary"></i><?= htmlspecialchars($category['name'] ?? '') ?></h1>
            <p class="text-muted"><?= htmlspecialchars($category['description'] ?? '') ?></p>
        </div>
        <div class="col-lg-4 text-lg-end">
            <a href="<?= BASE_URL ?>/services/submit" class="btn btn-primary"><i class="fas fa-plus me-1"></i>List Your Service</a>
        </div>
    </div>

    <!-- Search & Filter -->
    <div class="card shadow-sm mb-4">
        <div class="card-body aps-cp-card-body">
            <form method="GET" class="row g-2">
    <?php echo CSRFProtection::csrfField(); ?>
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Search by name or keyword..." value="<?= htmlspecialchars($search ?? '') ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <input type="text" name="city" class="form-control" placeholder="City..." value="<?= htmlspecialchars($city ?? '') ?>">
                </div>
                <div class="col-md-2">
                    <select name="sort" class="form-control">
                        <option value="latest" <?= $sort === 'latest' ? 'selected' : '' ?>>Latest</option>
                        <option value="rating" <?= $sort === 'rating' ? 'selected' : '' ?>>Top Rated</option>
                        <option value="views" <?= $sort === 'views' ? 'selected' : '' ?>>Most Viewed</option>
                        <option value="name" <?= $sort === 'name' ? 'selected' : '' ?>>Name A-Z</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter me-1"></i>Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Results -->
    <?php if (empty($listings['items'])): ?>
        <div class="text-center py-5">
            <i class="fas fa-search fa-4x text-muted mb-3"></i>
            <h4>No listings found</h4>
            <p class="text-muted">Try a different search or <a href="<?= BASE_URL ?>/services/submit">add your listing</a></p>
        </div>
    <?php else: ?>
        <p class="text-muted mb-3">Showing <?= count($listings['items']) ?> of <?= $listings['total'] ?> providers</p>
        <div class="row">
            <?php foreach ($listings['items'] as $l): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h5 class="card-title mb-1"><?= htmlspecialchars($l['business_name'] ?? '') ?>
                                        <?php if ($l['is_verified']): ?><i class="fas fa-check-circle text-info ms-1" title="Verified"></i><?php endif; ?>
                                        <?php if ($l['is_featured']): ?><i class="fas fa-crown text-warning ms-1" title="Featured"></i><?php endif; ?>
                                    </h5>
                                    <p class="text-muted small mb-1"><i class="<?= htmlspecialchars($l['category_icon'] ?? 'fas fa-tag') ?> me-1"></i><?= htmlspecialchars($l['category_name'] ?? '') ?></p>
                                </div>
                            </div>

                            <?php if ($l['rating'] > 0): ?>
                                <p class="mb-2"><span class="text-warning"><?= str_repeat('★', (int)$l['rating']) ?></span><span class="text-muted"> (<?= $l['review_count'] ?> reviews)</span></p>
                            <?php endif; ?>

                            <?php if ($l['owner_name']): ?><p class="mb-1 small"><i class="fas fa-user me-1 text-muted"></i><?= htmlspecialchars($l['owner_name'] ?? '') ?></p><?php endif; ?>
                            <?php if ($l['city']): ?><p class="mb-1 small"><i class="fas fa-map-marker-alt me-1 text-muted"></i><?= htmlspecialchars($l['city'] ?? '') ?></p><?php endif; ?>
                            <?php if ($l['phone']): ?><p class="mb-1 small"><i class="fas fa-phone me-1 text-muted"></i><a href="tel:<?= htmlspecialchars($l['phone'] ?? '') ?>"><?= htmlspecialchars($l['phone'] ?? '') ?></a></p><?php endif; ?>
                            <?php if ($l['experience_years'] > 0): ?><p class="mb-1 small"><i class="fas fa-clock me-1 text-muted"></i><?= $l['experience_years'] ?> years experience</p><?php endif; ?>

                            <div class="mt-3">
                                <a href="<?= BASE_URL ?>/services/listing/<?= $l['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye me-1"></i>View Details</a>
                                <?php if ($l['phone']): ?>
                                    <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $l['phone']) ?>" target="_blank" class="btn btn-sm btn-outline-success"><i class="fab fa-whatsapp"></i></a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($listings['pages'] > 1): ?>
            <nav class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php for ($p = 1; $p <= $listings['pages']; $p++): ?>
                        <li class="page-item <?= $p === $listings['page'] ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $p ?>&search=<?= urlencode($search) ?>&city=<?= urlencode($city) ?>&sort=<?= $sort ?>"><?= $p ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</div>
