<?php
$page_title = $page_title ?? __('user_resell_public_title', 'Resell Properties');
$page_heading = $page_heading ?? __('user_resell_public_heading', 'Resell Properties Marketplace');
$content = $content ?? '';
?>
<div class="container py-5">
  <div class="row mb-4">
    <div class="col-md-8">
      <h1 class="h2"><i class="fas fa-home me-2 text-primary"></i><?= __('user_resell_public_heading', 'Resell Properties') ?></h1>
      <p class="text-muted"><?= __('user_resell_public_subtitle', 'Find your perfect property from our verified resell marketplace') ?></p>
    </div>
    <div class="col-md-4 text-end">
      <a href="<?= BASE_URL ?>/properties/submit" class="btn btn-primary"><i class="fas fa-plus me-1"></i> <?= __('user_resell_public_list_property', 'List Your Property') ?></a>
    </div>
  </div>

  <div class="card shadow-sm mb-4">
    <div class="card-body aps-cp-card-body">
      <form method="GET" class="row g-2">
    <?php echo CSRFProtection::csrfField(); ?>
        <div class="col-md-3">
          <label class="small text-muted"><?= __('user_resell_public_property_type', 'Property Type') ?></label>
          <select name="property_type" class="form-select">
            <option value=""><?= __('user_resell_public_all_types', 'All Types') ?></option>
            <option value="plot"><?= __('user_resell_public_type_plot', 'Plot') ?></option>
            <option value="house"><?= __('user_resell_public_type_house', 'House') ?></option>
            <option value="flat"><?= __('user_resell_public_type_flat', 'Flat') ?></option>
            <option value="shop"><?= __('user_resell_public_type_shop', 'Shop') ?></option>
            <option value="farmhouse"><?= __('user_resell_public_type_farmhouse', 'Farmhouse') ?></option>
            <option value="commercial"><?= __('user_resell_public_type_commercial', 'Commercial') ?></option>
          </select>
        </div>
        <div class="col-md-2">
          <label class="small text-muted"><?= __('user_resell_public_min_price', 'Min Price') ?></label>
          <input type="number" name="min_price" class="form-control" placeholder="<?= __('user_resell_public_min_ph', 'Min') ?>">
        </div>
        <div class="col-md-2">
          <label class="small text-muted"><?= __('user_resell_public_max_price', 'Max Price') ?></label>
          <input type="number" name="max_price" class="form-control" placeholder="<?= __('user_resell_public_max_ph', 'Max') ?>">
        </div>
        <div class="col-md-3">
          <label class="small text-muted"><?= __('user_resell_public_district', 'District') ?></label>
          <select name="district_id" class="form-select">
            <option value=""><?= __('user_resell_public_all_districts', 'All Districts') ?></option>
          </select>
        </div>
        <div class="col-md-2 d-flex align-items-end">
          <button class="btn btn-primary w-100"><i class="fas fa-search me-1"></i> <?= __('user_resell_public_search', 'Search') ?></button>
        </div>
      </form>
    </div>
  </div>

  <div class="row" id="propertyList">
    <?php if (empty($properties)): ?>
      <div class="col-12">
        <div class="alert alert-info text-center py-5">
          <i class="fas fa-info-circle fa-2x mb-3"></i>
          <h5><?= __('user_resell_public_empty', 'No properties available') ?></h5>
          <p class="text-muted"><?= __('user_resell_public_empty_desc', 'Be the first to list your property for resale!') ?></p>
          <a href="<?= BASE_URL ?>/properties/submit" class="btn btn-primary"><?= __('user_resell_public_empty_cta', 'List Property') ?></a>
        </div>
      </div>
    <?php else: foreach ($properties as $p): ?>
      <div class="col-md-4 mb-4">
        <div class="card shadow-sm h-100 hover-lift">
          <div class="card-img-top bg-light d-flex align-items-center justify-content-center" class="style-20862">
            <?php if (!empty($p['images'][0]['image_url'])): ?>
              <img src="<?= BASE_URL ?>/assets/images/placeholder/property.svg" class="w-100 h-100" class="style-44820" alt="" />
            <?php else: ?>
              <i class="fas fa-home fa-4x text-muted"></i>
            <?php endif; ?>
          </div>
          <div class="card-body aps-cp-card-body">
            <h5 class="card-title"><?= htmlspecialchars($p['title'] ?? __('user_resell_public_default_title', 'Property')) ?></h5>
            <p class="text-muted small mb-2"><i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars(($p['district_name'] ?? '') ?: ($p['location'] ?? '')) ?></p>
            <div class="d-flex justify-content-between mb-2">
              <span class="badge bg-info text-white"><?= htmlspecialchars($p['property_type'] ?? 'N/A') ?></span>
              <span class="badge bg-success"><?= __('user_resell_public_verified', 'Verified') ?></span>
            </div>
            <h3 class="text-primary mb-2">₹<?= number_format((float)($p['asking_price'] ?? 0), 0) ?></h3>
            <div class="d-flex justify-content-between text-muted small mb-3">
              <span><i class="fas fa-ruler-combined me-1"></i><?= htmlspecialchars($p['area_sqft'] ?? 0) ?> <?= __('user_resell_public_sqft', 'sqft') ?></span>
              <?php if (!empty($p['bedrooms'])): ?>
                <span><i class="fas fa-bed me-1"></i><?= htmlspecialchars($p['bedrooms'] ?? '') ?> <?= __('user_resell_public_bed', 'bed') ?></span>
              <?php endif; ?>
              <?php if (!empty($p['bathrooms'])): ?>
                <span><i class="fas fa-bath me-1"></i><?= htmlspecialchars($p['bathrooms'] ?? '') ?> <?= __('user_resell_public_bath', 'bath') ?></span>
              <?php endif; ?>
            </div>
            <div class="d-grid">
              <a href="<?= BASE_URL ?>/resell/<?= (int)$p['id'] ?>" class="btn btn-outline-primary"><?= __('user_resell_public_view_details', 'View Details') ?></a>
            </div>
          </div>
          <div class="card-footer text-muted small">
            <?= __('user_resell_public_listed', 'Listed:') ?> <?= date('M d, Y', strtotime($p['created_at'] ?? 'now')) ?>
          </div>
        </div>
      </div>
    <?php endforeach; endif; ?>
  </div>
</div>

<style>
.hover-lift { transition: transform 0.2s, box-shadow 0.2s; }
.hover-lift:hover { transform: translateY(-4px); box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15) !important; }
</style>
