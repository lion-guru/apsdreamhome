<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><?= htmlspecialchars($pageTitle ?? 'Property Comparison') ?></h1>
        <a href="<?= $base ?? BASE_URL ?>/features/dashboard" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body aps-cp-card-body">
            <form method="GET" action="<?= $base ?? BASE_URL ?>/features/comparison" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label">Property A</label>
                    <select name="a" class="form-select">
                        <option value="">Select property...</option>
                        <?php if (!empty($properties)): ?>
                            <?php foreach ($properties as $p): ?>
                                <option value="<?= (int)($p['id'] ?? 0) ?>" <?= ((int)($selected['a'] ?? 0) === (int)($p['id'] ?? 0)) ? 'selected' : '' ?>><?= htmlspecialchars($p['name'] ?? '') ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label">Property B</label>
                    <select name="b" class="form-select">
                        <option value="">Select property...</option>
                        <?php if (!empty($properties)): ?>
                            <?php foreach ($properties as $p): ?>
                                <option value="<?= (int)($p['id'] ?? 0) ?>" <?= ((int)($selected['b'] ?? 0) === (int)($p['id'] ?? 0)) ? 'selected' : '' ?>><?= htmlspecialchars($p['name'] ?? '') ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-balance-scale me-1"></i>Compare</button>
                </div>
            </form>
        </div>
    </div>
    <?php if (!empty($comparison)): ?>
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom">
            <h5 class="mb-0">Comparison Results</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <div class="table-responsive"><table class="table table-bordered align-middle mb-0 table-responsive">
                    <thead class="table-light">
                        <tr><th style="width:25%">Attribute</th><th style="width:37.5%"><?= htmlspecialchars($comparison['a']['name'] ?? 'Property A') ?></th><th style="width:37.5%"><?= htmlspecialchars($comparison['b']['name'] ?? 'Property B') ?></th></tr>
                    </thead>
                    <tbody>
                        <?php $attrs = ['price' => 'Price', 'area' => 'Area (sqft)', 'type' => 'Type', 'location' => 'Location', 'bedrooms' => 'Bedrooms', 'bathrooms' => 'Bathrooms', 'status' => 'Status', 'amenities' => 'Amenities']; ?>
                        <?php foreach ($attrs as $key => $label): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></strong></td>
                                <td><?= htmlspecialchars($comparison['a'][$key] ?? '-') ?></td>
                                <td><?= htmlspecialchars($comparison['b'][$key] ?? '-') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table></div>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5 text-muted">
            <i class="fas fa-balance-scale fa-4x d-block mb-3"></i>
            <p>Select two properties above to compare them side by side.</p>
        </div>
    </div>
    <?php endif; ?>
</div>
