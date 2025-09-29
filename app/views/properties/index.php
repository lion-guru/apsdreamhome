<?php include '../app/views/includes/header.php'; ?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">Properties</h1>
        </div>
    </div>

    <!-- Search and Filter Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="/properties">
                        <div class="row">
                            <div class="col-md-3">
                                <input type="text" class="form-control" name="location"
                                       placeholder="Location" value="<?php echo htmlspecialchars($_GET['location'] ?? ''); ?>">
                            </div>
                            <div class="col-md-2">
                                <select class="form-select" name="type">
                                    <option value="">All Types</option>
                                    <option value="residential" <?php echo (isset($_GET['type']) && $_GET['type'] == 'residential') ? 'selected' : ''; ?>>Residential</option>
                                    <option value="commercial" <?php echo (isset($_GET['type']) && $_GET['type'] == 'commercial') ? 'selected' : ''; ?>>Commercial</option>
                                    <option value="plot" <?php echo (isset($_GET['type']) && $_GET['type'] == 'plot') ? 'selected' : ''; ?>>Plot</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="number" class="form-control" name="min_price"
                                       placeholder="Min Price" value="<?php echo htmlspecialchars($_GET['min_price'] ?? ''); ?>">
                            </div>
                            <div class="col-md-2">
                                <input type="number" class="form-control" name="max_price"
                                       placeholder="Max Price" value="<?php echo htmlspecialchars($_GET['max_price'] ?? ''); ?>">
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary me-2">Search</button>
                                <a href="/properties" class="btn btn-secondary">Clear</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Properties Grid -->
    <div class="row">
        <?php if (!empty($properties)): ?>
            <?php foreach ($properties as $property): ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-img-top" style="height: 200px; background: #f8f9fa; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-home fa-3x text-muted"></i>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($property['title']); ?></h5>
                            <p class="card-text text-muted">
                                <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($property['location']); ?>
                            </p>
                            <p class="card-text">
                                <strong>Price:</strong> ₹<?php echo number_format($property['price']); ?>
                            </p>
                            <p class="card-text">
                                <strong>Type:</strong> <?php echo ucfirst(htmlspecialchars($property['type'])); ?>
                            </p>
                            <?php if (!empty($property['bedrooms'])): ?>
                                <p class="card-text">
                                    <strong>Bedrooms:</strong> <?php echo htmlspecialchars($property['bedrooms']); ?>
                                </p>
                            <?php endif; ?>
                            <?php if (!empty($property['area'])): ?>
                                <p class="card-text">
                                    <strong>Area:</strong> <?php echo htmlspecialchars($property['area']); ?> sqft
                                </p>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer">
                            <a href="/properties/<?php echo $property['id']; ?>" class="btn btn-primary btn-sm">View Details</a>
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <a href="/properties/<?php echo $property['id']; ?>/contact" class="btn btn-outline-primary btn-sm">Contact Agent</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No properties found matching your criteria.
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if (!empty($properties) && isset($pagination)): ?>
        <div class="row">
            <div class="col-12">
                <nav aria-label="Property pagination">
                    <ul class="pagination justify-content-center">
                        <?php if ($pagination['current_page'] > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?php echo $pagination['base_url'] . '&page=' . ($pagination['current_page'] - 1); ?>">
                                    Previous
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php for ($i = max(1, $pagination['current_page'] - 2); $i <= min($pagination['total_pages'], $pagination['current_page'] + 2); $i++): ?>
                            <li class="page-item <?php echo ($i == $pagination['current_page']) ? 'active' : ''; ?>">
                                <a class="page-link" href="<?php echo $pagination['base_url'] . '&page=' . $i; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?php echo $pagination['base_url'] . '&page=' . ($pagination['current_page'] + 1); ?>">
                                    Next
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include '../app/views/includes/footer.php'; ?>
