<?php
/**
 * Associate Compare Properties Page
 */
$page_title = $page_title ?? 'Compare Properties';
$current_page = 'compare';
$properties = $properties ?? [];
$selected = $selected ?? [];
$selectedProperties = [];
if (!empty($selected) && !empty($properties)) {
    foreach ($properties as $p) {
        if (in_array($p['id'], $selected)) {
            $selectedProperties[] = $p;
        }
    }
}
?>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0"><i class="fas fa-balance-scale me-2 text-primary"></i>Compare Properties</h5>
    </div>
    <div class="card-body">
        <!-- Property Selector -->
        <form method="GET" action="<?= BASE_URL ?>/associate/compare" class="mb-4">
            <div class="row g-2 align-items-end">
                <div class="col-md-10">
                    <label class="form-label fw-bold">Select properties to compare (max 4)</label>
                    <select name="ids[]" class="form-select" multiple size="4" max="4">
                        <?php foreach ($properties as $p): ?>
                            <option value="<?= $p['id'] ?>" <?= in_array($p['id'], $selected) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($p['title'] ?? 'Property #' . $p['id']) ?>
                                (<?= htmlspecialchars($p['city'] ?? '') ?> - ₹<?= number_format($p['price'] ?? 0) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-balance-scale me-1"></i> Compare
                    </button>
                </div>
            </div>
            <small class="text-muted">Hold Ctrl/Cmd to select multiple properties</small>
        </form>

        <!-- Comparison Table -->
        <?php if (!empty($selectedProperties) && count($selectedProperties) >= 2): ?>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 200px;">Feature</th>
                            <?php foreach ($selectedProperties as $sp): ?>
                                <th class="text-center">
                                    <strong><?= htmlspecialchars($sp['title'] ?? 'Property') ?></strong>
                                    <br><small class="text-muted"><?= htmlspecialchars($sp['city'] ?? '') ?></small>
                                </th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Price</strong></td>
                            <?php foreach ($selectedProperties as $sp): ?>
                                <td class="text-center fw-bold text-success">₹<?= number_format($sp['price'] ?? 0) ?></td>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <td><strong>Area</strong></td>
                            <?php foreach ($selectedProperties as $sp): ?>
                                <td class="text-center"><?= number_format($sp['area_sqft'] ?? 0) ?> sq ft</td>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <td><strong>Price/sq ft</strong></td>
                            <?php foreach ($selectedProperties as $sp): ?>
                                <td class="text-center">
                                    ₹<?= ($sp['area_sqft'] ?? 0) > 0 ? number_format(($sp['price'] ?? 0) / ($sp['area_sqft'] ?? 1)) : 'N/A' ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <td><strong>Bedrooms</strong></td>
                            <?php foreach ($selectedProperties as $sp): ?>
                                <td class="text-center"><?= $sp['bedrooms'] ?? 'N/A' ?></td>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <td><strong>Bathrooms</strong></td>
                            <?php foreach ($selectedProperties as $sp): ?>
                                <td class="text-center"><?= $sp['bathrooms'] ?? 'N/A' ?></td>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <td><strong>Property Type</strong></td>
                            <?php foreach ($selectedProperties as $sp): ?>
                                <td class="text-center"><?= htmlspecialchars(ucfirst($sp['property_type'] ?? 'N/A')) ?></td>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <td><strong>Status</strong></td>
                            <?php foreach ($selectedProperties as $sp): ?>
                                <td class="text-center">
                                    <?php
                                    $statusClass = match($sp['status'] ?? '') {
                                        'available' => 'success',
                                        'sold' => 'danger',
                                        'reserved' => 'warning',
                                        default => 'secondary'
                                    };
                                    ?>
                                    <span class="badge bg-<?= $statusClass ?>"><?= ucfirst($sp['status'] ?? 'N/A') ?></span>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <td><strong>Action</strong></td>
                            <?php foreach ($selectedProperties as $sp): ?>
                                <td class="text-center">
                                    <a href="<?= BASE_URL ?>/properties/<?= $sp['id'] ?>" class="btn btn-outline-primary btn-sm" target="_blank">
                                        <i class="fas fa-eye me-1"></i>View
                                    </a>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    </tbody>
                </table>
            </div>
        <?php elseif (count($selectedProperties) === 1): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>Please select at least 2 properties to compare.
            </div>
        <?php else: ?>
            <div class="text-center py-4">
                <i class="fas fa-balance-scale fa-3x text-muted mb-3 opacity-50"></i>
                <h5 class="text-muted">Select properties to compare</h5>
                <p class="text-muted">Choose 2-4 properties from the dropdown above to see a side-by-side comparison.</p>
            </div>
        <?php endif; ?>
    </div>
</div>
