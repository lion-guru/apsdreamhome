<?php
$premiumListings = $premiumListings ?? [];
$listings = $listings ?? [];
$packages = $packages ?? [];
$filters = $filters ?? [];
$total = $total ?? 0;
$totalPages = $totalPages ?? 1;
$currentPage = $currentPage ?? 1;
$propertyTypes = ['plot', 'house', 'flat', 'shop', 'farmhouse', 'land', 'apartment', 'villa'];
$listingTypes = ['sell', 'rent', 'lease'];
$base = defined('BASE_URL') ? BASE_URL : '';
?>
<style>
.premium-carousel {
    display: flex;
    gap: 1rem;
    overflow-x: auto;
    padding: 0.5rem 0 1rem;
    scroll-snap-type: x mandatory;
    -webkit-overflow-scrolling: touch;
}
.premium-carousel::-webkit-scrollbar { height: 6px; }
.premium-carousel::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
.premium-card {
    flex: 0 0 280px;
    scroll-snap-align: start;
    border: 2px solid #fbbf24;
    border-radius: 16px;
    overflow: hidden;
    background: #fff;
    box-shadow: 0 4px 20px rgba(251,191,36,0.15);
    transition: all 0.3s;
    position: relative;
}
.premium-card:hover { transform: translateY(-4px); box-shadow: 0 8px 30px rgba(251,191,36,0.25); }
.premium-card .ribbon {
    position: absolute;
    top: 10px;
    left: -25px;
    z-index: 3;
    padding: 3px 30px;
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    transform: rotate(-45deg);
    letter-spacing: 0.05em;
}
.premium-card .p-img { height: 140px; object-fit: cover; width: 100%; }
.premium-card .p-body { padding: 0.75rem; }
.premium-card .p-body h6 { font-size: 0.9rem; font-weight: 700; margin-bottom: 0.2rem; }
.premium-card .p-body .price { font-size: 1.1rem; font-weight: 700; color: #059669; }
.premium-card .badge-premium { background: linear-gradient(135deg, #f59e0b, #d97706); }
.premium-card .badge-featured { background: linear-gradient(135deg, #ff6b35, #e85d26); }
.premium-card .badge-urgent { background: linear-gradient(135deg, #ef4444, #dc2626); }

.reg-card {
    border-radius: 12px;
    overflow: hidden;
    background: #fff;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    transition: all 0.3s;
    position: relative;
    height: 100%;
}
.reg-card:hover { transform: translateY(-3px); box-shadow: 0 6px 20px rgba(0,0,0,0.1); }
.reg-card .r-img { height: 150px; object-fit: cover; width: 100%; }
.reg-card .r-body { padding: 0.75rem; }
.reg-card .r-body h6 { font-size: 0.88rem; font-weight: 700; margin-bottom: 0.2rem; }
.reg-card .r-body .price { font-size: 1rem; font-weight: 700; color: #059669; }
.premium-badge-sm {
    display: inline-flex;
    align-items: center;
    gap: 0.2rem;
    padding: 0.15rem 0.4rem;
    border-radius: 4px;
    font-size: 0.65rem;
    font-weight: 700;
    text-transform: uppercase;
}
</style>

<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
      <h2 class="mb-1"><i class="fas fa-store me-2 text-primary"></i>Property Marketplace</h2>
      <p class="text-muted mb-0"><?= number_format($total) ?> properties listed by owners</p>
    </div>
    <div class="d-flex gap-2">
      <a href="<?= $base ?>/marketplace?listing_type=rent" class="btn btn-outline-info btn-sm"><i class="fas fa-building me-1"></i>Rentals</a>
      <a href="<?= $base ?>/list-property" class="btn btn-outline-primary btn-sm"><i class="fas fa-plus me-1"></i>List Your Property</a>
    </div>
  </div>

  <!-- Filters -->
  <div class="aps-cp-card mb-4">
    <div class="aps-cp-card-body">
      <form method="get" class="row g-2 align-items-end">
    <?php echo CSRFProtection::csrfField(); ?>
        <div class="col-md-2">
          <label class="form-label small">Property Type</label>
          <select name="type" class="form-select form-select-sm">
            <option value="">All Types</option>
            <?php foreach ($propertyTypes as $pt): ?>
               <option value="<?= $pt ?>" <?= (($filters['type'] ?? '') === $pt) ? 'selected' : '' ?>><?= ucfirst($pt) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label small">Listing Type</label>
          <select name="listing_type" class="form-select form-select-sm">
            <option value="">All</option>
            <?php foreach ($listingTypes as $lt): ?>
               <option value="<?= $lt ?>" <?= (($filters['listing_type'] ?? '') === $lt) ? 'selected' : '' ?>><?= ucfirst($lt) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label small">Min Price</label>
          <input type="number" name="min_price" class="form-control form-control-sm" value="<?= htmlspecialchars($filters['min_price'] ?? '') ?>" placeholder="Min ₹">
        </div>
        <div class="col-md-2">
          <label class="form-label small">Max Price</label>
          <input type="number" name="max_price" class="form-control form-control-sm" value="<?= htmlspecialchars($filters['max_price'] ?? '') ?>" placeholder="Max ₹">
        </div>
        <div class="col-md-2">
          <label class="form-label small">Location</label>
          <input type="text" name="location" class="form-control form-control-sm" value="<?= htmlspecialchars($filters['location'] ?? '') ?>" placeholder="City/Location">
        </div>
        <div class="col-md-2 d-flex gap-1">
          <button type="submit" class="btn btn-primary btn-sm flex-fill"><i class="fas fa-search me-1"></i>Search</button>
          <a href="<?= $base ?>/marketplace" class="btn btn-outline-secondary btn-sm"><i class="fas fa-times"></i></a>
        </div>
      </form>
    </div>
  </div>

  <!-- Premium Listings Section -->
  <?php if (!empty($premiumListings)): ?>
  <div class="mb-4">
    <div class="d-flex align-items-center gap-2 mb-3">
      <i class="fas fa-crown text-warning" class="style-34894"></i>
      <h5 class="mb-0 fw-bold">Premium Listings</h5>
      <span class="badge bg-warning text-dark"><?= count($premiumListings) ?> featured</span>
      <span class="text-muted small ms-2"><i class="fas fa-arrow-right"></i> Scroll to see all</span>
    </div>
    <div class="premium-carousel">
      <?php foreach ($premiumListings as $p):
        $ptype = '';
        $pColor = '';
        if (!empty($p['is_premium'])) { $ptype = 'PREMIUM'; $pColor = '#f59e0b'; }
        elseif (!empty($p['is_urgent'])) { $ptype = 'URGENT'; $pColor = '#ef4444'; }
        elseif (!empty($p['is_featured'])) { $ptype = 'Featured'; $pColor = '#ff6b35'; }
        $img = !empty($p['image']) ? ($base . '/' . $p['image']) : '';
      ?>
      <a href="<?= $base ?>/marketplace/<?= $p['id'] ?>" class="text-decoration-none text-dark premium-card">
        <div class="ribbon" class="style-35829"><?= $ptype ?></div>
        <?php if ($img): ?>
        <img src="<?= $img ?>" class="p-img" alt="<?= htmlspecialchars($p['name'] ?? '') ?>" loading="lazy">
        <?php else: ?>
        <div class="p-img bg-light d-flex align-items-center justify-content-center"><i class="fas fa-home fa-2x text-muted"></i></div>
        <?php endif; ?>
        <div class="p-body">
          <h6><?= htmlspecialchars(mb_substr($p['name'] ?? '', 0, 35)) ?></h6>
          <p class="small text-muted mb-1"><i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($p['city_name'] ?? $p['location'] ?? 'N/A') ?></p>
          <div class="price">₹<?= number_format($p['price']) ?></div>
          <div class="d-flex gap-1 mt-1">
            <span class="badge bg-light text-dark text-capitalize"><?= $p['property_type'] ?></span>
            <span class="badge bg-light text-dark"><?= number_format($p['area_sqft'] ?? 0) ?> sqft</span>
          </div>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

  <!-- Premium Packages Info -->
  <?php if (!empty($packages)): ?>
  <div class="alert alert-info d-flex align-items-center gap-2 py-2 px-3 mb-4 small">
    <i class="fas fa-crown text-warning"></i>
    <span>Want to sell faster? Try our premium packages:
    <?php foreach ($packages as $pkg): ?>
      <span class="badge ms-1" class="style-79108"><?= htmlspecialchars($pkg['badge_label'] ?? $pkg['name']) ?> ₹<?= number_format($pkg['price']) ?></span>
    <?php endforeach; ?>
    — <a href="<?= $base ?>/list-property" class="text-decoration-underline fw-bold">List now</a></span>
  </div>
  <?php endif; ?>

  <!-- Regular Listings -->
  <?php if (empty($listings) && empty($premiumListings)): ?>
    <div class="text-center py-5">
      <i class="fas fa-home fa-4x text-muted mb-3"></i>
      <h5 class="text-muted">No properties found</h5>
      <p class="text-muted">Try adjusting your filters or <a href="<?= $base ?>/list-property">list your property</a></p>
    </div>
  <?php elseif (!empty($listings)): ?>
    <div class="d-flex align-items-center gap-2 mb-3">
      <h5 class="mb-0 fw-bold">All Listings</h5>
      <?php if ($total > 0): ?><span class="text-muted small">(<?= number_format($total) ?> properties)</span><?php endif; ?>
    </div>
    <div class="row g-3">
      <?php foreach ($listings as $prop): ?>
        <?php
          $badge = ''; $bc = '';
          if (!empty($prop['is_premium'])) { $badge = 'Premium'; $bc = 'bg-warning text-dark'; }
          elseif (!empty($prop['is_urgent'])) { $badge = 'Urgent'; $bc = 'bg-danger'; }
          elseif (!empty($prop['is_featured'])) { $badge = 'Featured'; $bc = 'bg-warning text-dark'; }
          $img = !empty($prop['image']) ? ($base . '/' . $prop['image']) : '';
        ?>
        <div class="col-lg-4 col-md-6">
          <div class="reg-card">
            <?php if ($badge): ?>
            <span class="position-absolute top-0 end-0 m-2 premium-badge-sm <?= $bc ?>" class="style-58936"><i class="fas fa-crown me-1"></i><?= $badge ?></span>
            <?php endif; ?>
            <?php if ($img): ?>
            <img src="<?= $img ?>" class="r-img" alt="<?= htmlspecialchars($prop['name'] ?? '') ?>" loading="lazy">
            <?php else: ?>
            <div class="r-img bg-light d-flex align-items-center justify-content-center"><i class="fas fa-home fa-2x text-muted"></i></div>
            <?php endif; ?>
            <div class="r-body">
              <h6><?= htmlspecialchars(mb_substr($prop['name'] ?? '', 0, 45)) ?></h6>
              <p class="small text-muted mb-1"><i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($prop['city_name'] ?? $prop['location'] ?? 'N/A') ?></p>
              <div class="d-flex gap-1 mb-1">
                <span class="badge bg-primary text-capitalize"><?= $prop['property_type'] ?></span>
                <span class="badge bg-secondary text-capitalize"><?= $prop['listing_type'] ?></span>
              </div>
              <div class="d-flex justify-content-between align-items-center">
                <div class="price">₹<?= number_format($prop['price']) ?></div>
                <small class="text-muted"><?= number_format($prop['area_sqft'] ?? 0) ?> sqft</small>
              </div>
              <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top">
                <a href="<?= $base ?>/marketplace/<?= $prop['id'] ?>" class="btn btn-sm btn-outline-primary">Details</a>
                <small class="text-muted"><i class="fas fa-eye me-1"></i><?= (int)($prop['views'] ?? 0) ?></small>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <nav class="mt-4">
      <ul class="pagination justify-content-center">
        <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
          <a class="page-link" href="<?= $base ?>/marketplace?page=<?= $currentPage-1 ?>&<?= http_build_query(array_filter($filters)) ?>">Previous</a>
        </li>
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
          <li class="page-item <?= $i === $currentPage ? 'active' : '' ?>">
            <a class="page-link" href="<?= $base ?>/marketplace?page=<?= $i ?>&<?= http_build_query(array_filter($filters)) ?>"><?= $i ?></a>
          </li>
        <?php endfor; ?>
        <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
          <a class="page-link" href="<?= $base ?>/marketplace?page=<?= $currentPage+1 ?>&<?= http_build_query(array_filter($filters)) ?>">Next</a>
        </li>
      </ul>
    </nav>
    <?php endif; ?>
  <?php endif; ?>
</div>
