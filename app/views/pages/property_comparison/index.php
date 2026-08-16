<?php
$page_title = $page_title ?? __('cmp_page_title', [], 'Property Comparison');
$page_heading = $page_heading ?? __('cmp_page_heading', [], 'Property Comparison');
$content = $content ?? '';
$properties = $properties ?? [];
$comparison = $comparison ?? [];
$count = $count ?? 0;
$not_found = $not_found ?? false;
$shared = $shared ?? false;
$view_count = $view_count ?? 0;
?>
<style>
.cmp-hero { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: #fff; padding: 50px 0; }
.cmp-card { border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); transition: all 0.3s; }
.cmp-card:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,0.12); }
.cmp-best { border: 2px solid #10b981 !important; position: relative; }
.cmp-best::before { content: "BEST VALUE"; position: absolute; top: -10px; left: 20px; background: #10b981; color: white; font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 4px; }
.cmp-row { display: grid; grid-template-columns: 200px repeat(4, 1fr); gap: 1px; background: #e5e7eb; }
.cmp-row > div { background: white; padding: 12px 16px; font-size: 14px; }
.cmp-row .label { font-weight: 600; color: #6b7280; background: #f9fafb; }
.cmp-image { height: 200px; background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%); display: flex; align-items: center; justify-content: center; font-size: 48px; color: #9ca3af; }
</style>

<section class="cmp-hero">
    <div class="container text-center">
        <h1 class="display-5 fw-bold mb-2"><i class="fas fa-balance-scale me-2"></i><?= __('cmp_hero_heading', [], 'Compare Properties') ?></h1>
        <p class="lead mb-0 opacity-90"><?= __('cmp_hero_subtitle', [], 'Side-by-side comparison of up to 4 properties') ?></p>
    </div>
</section>

<div class="container py-4">
    <?php if (!empty($_SESSION['comparison_error'])): ?>
        <div class="alert alert-warning alert-dismissible fade show">
            <i class="fas fa-exclamation-triangle me-2"></i><?= htmlspecialchars($_SESSION['comparison_error'] ?? '') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['comparison_error']); ?>
    <?php endif; ?>

    <?php if ($not_found): ?>
        <div class="alert alert-danger text-center">
            <h4><?= __('cmp_not_found', [], 'Comparison Not Found') ?></h4>
            <p><?= __('cmp_not_found_desc', [], 'The shared comparison link is invalid or has expired.') ?></p>
            <a href="<?= BASE_URL ?>/property-comparison" class="btn btn-primary"><?= __('cmp_my_comparison', [], 'My Comparison') ?></a>
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><?= $shared ? __('cmp_shared_comparison', [], 'Shared Comparison') . ' (' . $view_count . ' views)' : __('cmp_your_list', [], 'Your Comparison List') ?> <span class="badge bg-warning text-dark"><?= $count ?> / 4</span></h4>
        <div class="d-flex gap-2">
            <?php if (!empty($share_token) && $count > 0): ?>
                <button class="btn btn-outline-primary" onclick="copyShareLink()">
                    <i class="fas fa-share-alt me-1"></i> <?= __('cmp_copy_link', [], 'Copy Share Link') ?>
                </button>
            <?php endif; ?>
            <?php if ($count > 0): ?>
                <form method="POST" action="<?= BASE_URL ?>/property-comparison/clear" class="style-35851">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    <button type="submit" class="btn btn-outline-danger" onclick="return confirm('<?= __('cmp_clear_confirm', [], 'Clear all?') ?>')">
                        <i class="fas fa-trash me-1"></i> <?= __('cmp_clear_all', [], 'Clear All') ?>
                    </button>
                </form>
            <?php endif; ?>
            <a href="<?= BASE_URL ?>/properties" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> <?= __('cmp_add_more', [], 'Add More') ?>
            </a>
        </div>
    </div>

    <?php if ($count === 0): ?>
        <div class="text-center py-5">
            <div class="display-1 text-muted mb-3"><i class="fas fa-balance-scale"></i></div>
            <h4 class="text-muted"><?= __('cmp_no_properties', [], 'No Properties to Compare') ?></h4>
            <p class="text-muted mb-4"><?= __('cmp_no_properties_desc', [], 'Add properties from listing pages to compare them side-by-side') ?></p>
            <a href="<?= BASE_URL ?>/properties" class="btn btn-primary btn-lg">
                <i class="fas fa-search me-2"></i><?= __('cmp_browse', [], 'Browse Properties') ?>
            </a>
        </div>
    <?php else: ?>
        <div class="cmp-card">
            <div class="cmp-row">
                <div class="label cmp-image" class="style-833"><i class="fas fa-image"></i></div>
                <?php foreach ($properties as $p): ?>
                    <div class="cmp-image position-relative <?= ($comparison['best_value_id'] ?? null) == $p['id'] ? 'cmp-best' : '' ?>" class="style-47346">
                        <?php if (!empty($p['image'])): ?>
                            <img src="<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['title'] ?? '') ?>" class="style-25330">
                        <?php else: ?>
                            <i class="fas fa-home text-muted"></i>
                        <?php endif; ?>
                        <form method="POST" action="<?= BASE_URL ?>/property-comparison/remove" class="style-15676">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                            <input type="hidden" name="property_id" value="<?= $p['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-danger" class="style-82522" title="Remove">
                                <i class="fas fa-times"></i>
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
                <?php for ($i = $count; $i < 4; $i++): ?>
                    <div class="cmp-image text-muted">
                        <a href="<?= BASE_URL ?>/properties" class="text-decoration-none text-muted">
                            <i class="fas fa-plus-circle fa-2x"></i>
                            <div class="small mt-1">Add</div>
                        </a>
                    </div>
                <?php endfor; ?>
            </div>

            <div class="cmp-row">
                <div class="label"><i class="fas fa-tag me-1"></i> <?= __('cmp_property_type', [], 'Property Type') ?></div>
                <?php foreach ($properties as $p): ?>
                    <div><strong><?= htmlspecialchars(ucfirst($p['property_type'] ?? 'N/A')) ?></strong></div>
                <?php endforeach; ?>
                <?php for ($i = $count; $i < 4; $i++): ?><div class="text-muted">—</div><?php endfor; ?>
            </div>

            <div class="cmp-row">
                <div class="label"><i class="fas fa-map-marker-alt me-1"></i> <?= __('cmp_location', [], 'Location') ?></div>
                <?php foreach ($properties as $p): ?>
                    <div>
                        <i class="fas fa-map-marker-alt text-danger me-1"></i>
                        <?= htmlspecialchars($p['city'] ?? $p['address'] ?? 'N/A') ?>
                    </div>
                <?php endforeach; ?>
                <?php for ($i = $count; $i < 4; $i++): ?><div class="text-muted">—</div><?php endfor; ?>
            </div>

            <div class="cmp-row">
                <div class="label"><i class="fas fa-rupee-sign me-1"></i> <?= __('cmp_price', [], 'Price') ?></div>
                <?php foreach ($properties as $p): ?>
                    <div>
                        <strong class="text-success">₹<?= number_format($p['price'] ?? 0) ?></strong>
                        <?php if (($comparison['cheapest'] ?? 0) > 0 && ($p['price'] ?? 0) == $comparison['cheapest']): ?>
                            <i class="fas fa-arrow-down text-success ms-1" title="Cheapest"></i>
                        <?php elseif (($comparison['priciest'] ?? 0) > 0 && ($p['price'] ?? 0) == $comparison['priciest']): ?>
                            <i class="fas fa-arrow-up text-danger ms-1" title="Priciest"></i>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
                <?php for ($i = $count; $i < 4; $i++): ?><div class="text-muted">—</div><?php endfor; ?>
            </div>

            <div class="cmp-row">
                <div class="label"><i class="fas fa-ruler-combined me-1"></i> <?= __('cmp_area', [], 'Area (sqft)') ?></div>
                <?php foreach ($properties as $p): ?>
                    <div>
                        <strong><?= number_format($p['area_sqft'] ?? 0) ?></strong> sqft
                        <?php if (($comparison['largest'] ?? 0) > 0 && ($p['area_sqft'] ?? 0) == $comparison['largest']): ?>
                            <i class="fas fa-trophy text-warning ms-1" title="Largest"></i>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
                <?php for ($i = $count; $i < 4; $i++): ?><div class="text-muted">—</div><?php endfor; ?>
            </div>

            <div class="cmp-row">
                <div class="label"><i class="fas fa-calculator me-1"></i> <?= __('cmp_price_sqft', [], 'Price/sqft') ?></div>
                <?php foreach ($properties as $p):
                    $ps = (!empty($p['price']) && !empty($p['area_sqft']) && $p['area_sqft'] > 0) ? round($p['price'] / $p['area_sqft'], 2) : 0;
                ?>
                    <div>
                        <strong>₹<?= number_format($ps) ?></strong>
                        <?php if (($comparison['best_value_id'] ?? null) == $p['id']): ?>
                            <span class="badge bg-success ms-1"><?= __('cmp_best', [], 'Best') ?></span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
                <?php for ($i = $count; $i < 4; $i++): ?><div class="text-muted">—</div><?php endfor; ?>
            </div>

            <div class="cmp-row">
                <div class="label"><i class="fas fa-bed me-1"></i> <?= __('cmp_bedrooms', [], 'Bedrooms') ?></div>
                <?php foreach ($properties as $p): ?>
                    <div><?= htmlspecialchars($p['bedrooms'] ?? '—') ?></div>
                <?php endforeach; ?>
                <?php for ($i = $count; $i < 4; $i++): ?><div class="text-muted">—</div><?php endfor; ?>
            </div>

            <div class="cmp-row">
                <div class="label"><i class="fas fa-bath me-1"></i> <?= __('cmp_bathrooms', [], 'Bathrooms') ?></div>
                <?php foreach ($properties as $p): ?>
                    <div><?= htmlspecialchars($p['bathrooms'] ?? '—') ?></div>
                <?php endforeach; ?>
                <?php for ($i = $count; $i < 4; $i++): ?><div class="text-muted">—</div><?php endfor; ?>
            </div>

            <div class="cmp-row">
                <div class="label"><i class="fas fa-list me-1"></i> <?= __('cmp_listing', [], 'Listing') ?></div>
                <?php foreach ($properties as $p): ?>
                    <div>
                        <span class="badge bg-<?= ($p['listing_type'] ?? '') === 'rent' ? 'info' : 'success' ?>">
                            <?= htmlspecialchars(ucfirst($p['listing_type'] ?? 'sale')) ?>
                        </span>
                    </div>
                <?php endforeach; ?>
                <?php for ($i = $count; $i < 4; $i++): ?><div class="text-muted">—</div><?php endfor; ?>
            </div>

            <div class="cmp-row">
                <div class="label"><i class="fas fa-info-circle me-1"></i> <?= __('cmp_status', [], 'Status') ?></div>
                <?php foreach ($properties as $p): ?>
                    <div>
                        <span class="badge bg-<?= ($p['status'] ?? '') === 'approved' ? 'success' : 'warning' ?>">
                            <?= htmlspecialchars(ucfirst($p['status'] ?? 'pending')) ?>
                        </span>
                    </div>
                <?php endforeach; ?>
                <?php for ($i = $count; $i < 4; $i++): ?><div class="text-muted">—</div><?php endfor; ?>
            </div>

            <div class="cmp-row">
                <div class="label"><i class="fas fa-cogs me-1"></i> <?= __('cmp_actions', [], 'Actions') ?></div>
                <?php foreach ($properties as $p): ?>
                    <div class="d-grid gap-1">
                        <a href="<?= BASE_URL ?>/properties/<?= $p['id'] ?>" class="btn btn-sm btn-primary">
                            <i class="fas fa-eye me-1"></i> <?= __('cmp_view', [], 'View') ?>
                        </a>
                        <a href="<?= BASE_URL ?>/property/inquire?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-success">
                            <i class="fas fa-envelope me-1"></i> <?= __('cmp_inquire', [], 'Inquire') ?>
                        </a>
                    </div>
                <?php endforeach; ?>
                <?php for ($i = $count; $i < 4; $i++): ?><div class="text-muted">—</div><?php endfor; ?>
            </div>
        </div>

        <?php if (!empty($comparison['avg_price'])): ?>
            <div class="row g-3 mt-3">
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <p class="text-muted small mb-1"><?= __('cmp_cheapest', [], 'Cheapest') ?></p>
                            <h4 class="text-success mb-0">₹<?= number_format($comparison['cheapest'] ?? 0) ?></h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <p class="text-muted small mb-1"><?= __('cmp_most_expensive', [], 'Most Expensive') ?></p>
                            <h4 class="text-danger mb-0">₹<?= number_format($comparison['priciest'] ?? 0) ?></h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <p class="text-muted small mb-1"><?= __('cmp_avg_price', [], 'Average Price') ?></p>
                            <h4 class="text-info mb-0">₹<?= number_format($comparison['avg_price'] ?? 0) ?></h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <p class="text-muted small mb-1"><?= __('cmp_largest_area', [], 'Largest Area') ?></p>
                            <h4 class="text-warning mb-0"><?= number_format($comparison['largest'] ?? 0) ?> sqft</h4>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php if (!empty($share_token)): ?>
<input type="hidden" id="shareUrl" value="<?= BASE_URL ?>/property-comparison/share?token=<?= $share_token ?>">
<script>
function copyShareLink() {
    const url = document.getElementById('shareUrl').value;
    navigator.clipboard.writeText(url).then(() => {
        alert('Share link copied!\n' + url);
    });
}
</script>
<?php endif; ?>