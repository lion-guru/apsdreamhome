<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/services">Services Directory</a></li>
            <li class="breadcrumb-item active">Material Price Comparison</li>
        </ol>
    </nav>

    <div class="row mb-4">
        <div class="col-lg-8">
            <h1 class="mb-2"><i class="fas fa-cubes text-warning me-2"></i>Construction Material Prices</h1>
            <p class="text-muted">Compare prices of cement, steel, bricks, sand and more from multiple suppliers to get the best deal.</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm mb-4">
        <div class="card-body aps-cp-card-body">
            <form method="GET" class="row g-2">
    <?php echo CSRFProtection::csrfField(); ?>
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Search materials..." value="<?= htmlspecialchars($search ?? '') ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <select name="category" class="form-control" onchange="this.form.submit()">
                        <option value="">All Categories</option>
                        <?php foreach ($materialCategories as $mc): ?>
                            <option value="<?= $mc ?>" <?= $selectedCategory === $mc ? 'selected' : '' ?>><?= $mc ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-warning w-100"><i class="fas fa-filter me-1"></i>Compare</button>
                </div>
            </form>
        </div>
    </div>

    <?php if (empty($materials)): ?>
        <div class="text-center py-5">
            <i class="fas fa-cubes fa-4x text-muted mb-3"></i>
            <h4>No material prices listed yet</h4>
            <p class="text-muted">Suppliers haven't listed prices yet. Check back soon!</p>
        </div>
    <?php else: ?>
        <?php
        $grouped = [];
        foreach ($materials as $m) {
            $grouped[$m['category']][] = $m;
        }
        ?>

        <?php foreach ($grouped as $catName => $items): ?>
            <div class="card shadow-sm mb-4">
                <div class="card-header aps-cp-card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="fas fa-tag me-2"></i><?= htmlspecialchars($catName ?? '') ?></h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead class="table-light">
                                <tr><th>Material</th><th>Brand</th><th>Price</th><th>Unit</th><th>Supplier</th><th>Location</th><th>Updated</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $m): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($m['material_name'] ?? '') ?></strong></td>
                                        <td><?= htmlspecialchars($m['brand'] ?? '-') ?></td>
                                        <td><strong class="text-danger">₹<?= number_format($m['price'], 2) ?></strong></td>
                                        <td><?= htmlspecialchars($m['unit'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($m['supplier_name'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($m['city'] ?? '') ?></td>
                                        <td><small class="text-muted"><?= $m['price_date'] ? date('d M Y', strtotime($m['price_date'])) : '-' ?></small></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
