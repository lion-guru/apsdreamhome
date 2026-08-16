<?php
$page_title = $page_title ?? __('user_resell_detail_title', 'Property Details');
$page_heading = $page_heading ?? __('user_resell_detail_heading', 'Property Details');
$content = $content ?? '';
?>
<div class="container py-5">
  <?php if (empty($property)): ?>
    <div class="alert alert-warning text-center">
      <h4><?= __('user_resell_detail_not_found', 'Property not found') ?></h4>
      <a href="<?= BASE_URL ?>/resell" class="btn btn-primary mt-3"><?= __('user_resell_detail_back', 'Back to listings') ?></a>
    </div>
  <?php else: ?>
    <nav aria-label="breadcrumb" class="mb-3">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>"><?= __('user_resell_detail_breadcrumb_home', 'Home') ?></a></li>
        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/resell"><?= __('user_resell_detail_breadcrumb_resell', 'Resell Properties') ?></a></li>
        <li class="breadcrumb-item active"><?= htmlspecialchars($property['title'] ?? '') ?></li>
      </ol>
    </nav>

    <div class="row">
      <div class="col-md-8">
        <div class="card shadow-sm mb-4">
          <div class="card-body aps-cp-card-body">
            <h2 class="mb-2"><?= htmlspecialchars($property['title'] ?? '') ?></h2>
            <p class="text-muted"><i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars(($property['district_name'] ?? '') ?: ($property['location'] ?? '')) ?></p>
            <div class="d-flex gap-2 mb-3">
              <span class="badge bg-info"><?= htmlspecialchars($property['property_type'] ?? '') ?></span>
              <span class="badge bg-success"><?= __('user_resell_detail_verified', 'Verified') ?></span>
              <span class="badge bg-<?= ($property['status'] ?? '') === 'active' ? 'success' : 'warning' ?>"><?= htmlspecialchars($property['status'] ?? '') ?></span>
            </div>
            <h2 class="text-primary mb-4">₹<?= number_format((float)($property['asking_price'] ?? 0), 0) ?></h2>

            <div class="row g-3 mb-4">
              <div class="col-md-3"><div class="border rounded p-3 text-center"><i class="fas fa-ruler-combined fa-2x text-muted"></i><div class="mt-2 small text-muted"><?= __('user_resell_detail_area', 'Area') ?></div><strong><?= htmlspecialchars($property['area_sqft'] ?? 0) ?> <?= __('user_resell_detail_sqft', 'sqft') ?></strong></div></div>
              <?php if (!empty($property['bedrooms'])): ?>
                <div class="col-md-3"><div class="border rounded p-3 text-center"><i class="fas fa-bed fa-2x text-muted"></i><div class="mt-2 small text-muted"><?= __('user_resell_detail_bedrooms', 'Bedrooms') ?></div><strong><?= htmlspecialchars($property['bedrooms'] ?? '') ?></strong></div></div>
              <?php endif; ?>
              <?php if (!empty($property['bathrooms'])): ?>
                <div class="col-md-3"><div class="border rounded p-3 text-center"><i class="fas fa-bath fa-2x text-muted"></i><div class="mt-2 small text-muted"><?= __('user_resell_detail_bathrooms', 'Bathrooms') ?></div><strong><?= htmlspecialchars($property['bathrooms'] ?? '') ?></strong></div></div>
              <?php endif; ?>
              <?php if (!empty($property['age_years'])): ?>
                <div class="col-md-3"><div class="border rounded p-3 text-center"><i class="fas fa-calendar fa-2x text-muted"></i><div class="mt-2 small text-muted"><?= __('user_resell_detail_age', 'Age') ?></div><strong><?= htmlspecialchars($property['age_years'] ?? '') ?> <?= __('user_resell_detail_years', 'years') ?></strong></div></div>
              <?php endif; ?>
            </div>

            <h5><?= __('user_resell_detail_description', 'Description') ?></h5>
            <p><?= nl2br(htmlspecialchars($property['description'] ?? __('user_resell_detail_no_description', 'No description provided.'))) ?></p>

            <?php if (!empty($property['amenities'])): ?>
              <h5><?= __('user_resell_detail_amenities', 'Amenities') ?></h5>
              <div class="d-flex flex-wrap gap-2 mb-3">
                <?php
                  $amenities = is_string($property['amenities']) ? json_decode($property['amenities'], true) : $property['amenities'];
                  if (is_array($amenities)) {
                    foreach ($amenities as $a) echo '<span class="badge bg-light text-dark border"><i class="fas fa-check me-1 text-success"></i>' . htmlspecialchars($a ?? '') . '</span>';
                  } else {
                    echo '<span class="text-muted">' . htmlspecialchars($property['amenities'] ?? '') . '</span>';
                  }
                ?>
              </div>
            <?php endif; ?>

            <?php if (!empty($property['ai_tags'])): ?>
              <h5><?= __('user_resell_detail_ai_tags', 'AI-Generated Tags') ?></h5>
              <div class="d-flex flex-wrap gap-2 mb-3">
                <?php foreach ($property['ai_tags'] as $tag): ?>
                  <span class="badge bg-info"><i class="fas fa-robot me-1"></i><?= htmlspecialchars($tag['tag'] ?? '') ?> (<?= round((float)$tag['confidence'] * 100) ?>%)</span>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>

            <?php if (!empty($property['images'])): ?>
              <h5><?= __('user_resell_detail_photos', 'Photos') ?></h5>
              <div class="row g-2">
                <?php foreach ($property['images'] as $img): ?>
                  <div class="col-md-4"><img src="<?= BASE_URL ?>/assets/images/placeholder/property.svg" class="img-fluid rounded" alt="" /></div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card shadow-sm mb-3">
          <div class="card-body aps-cp-card-body">
            <h5 class="card-title"><?= __('user_resell_detail_contact_owner', 'Contact Owner') ?></h5>
            <p class="mb-2"><i class="fas fa-user me-2"></i><strong><?= htmlspecialchars($property['owner_name'] ?? __('user_resell_detail_default_owner', 'Owner')) ?></strong></p>
            <?php if (!empty($property['owner_phone'])): ?>
              <p class="mb-2"><i class="fas fa-phone me-2"></i><a href="tel:<?= htmlspecialchars($property['owner_phone'] ?? '') ?>"><?= htmlspecialchars($property['owner_phone'] ?? '') ?></a></p>
            <?php endif; ?>
            <?php if (!empty($property['owner_email'])): ?>
              <p class="mb-2"><i class="fas fa-envelope me-2"></i><a href="mailto:<?= htmlspecialchars($property['owner_email'] ?? '') ?>"><?= htmlspecialchars($property['owner_email'] ?? '') ?></a></p>
            <?php endif; ?>
            <hr>
            <a href="<?= BASE_URL ?>/contact?property_id=<?= (int)$property['id'] ?>" class="btn btn-primary w-100 mb-2"><i class="fas fa-envelope me-1"></i> <?= __('user_resell_detail_send_inquiry', 'Send Inquiry') ?></a>
            <a href="<?= BASE_URL ?>/properties-workflow/<?= (int)$property['id'] ?>/schedule-visit" class="btn btn-outline-primary w-100"><i class="fas fa-calendar me-1"></i> <?= __('user_resell_detail_schedule_visit', 'Schedule Visit') ?></a>
          </div>
        </div>

        <?php if (!empty($valuation)): ?>
          <div class="card shadow-sm mb-3 border-success">
            <div class="card-body aps-cp-card-body">
              <h5 class="card-title text-success"><i class="fas fa-chart-line me-1"></i> <?= __('user_resell_detail_ai_valuation', 'AI Valuation') ?></h5>
              <p class="text-muted small mb-2"><?= __('user_resell_detail_market_data', 'Based on market data') ?></p>
              <h3 class="text-success">₹<?= number_format((float)($valuation['estimated_value'] ?? 0), 0) ?></h3>
              <small class="text-muted"><?= __('user_resell_detail_source', 'Source:') ?> <?= htmlspecialchars($valuation['valuation_source'] ?? 'ai') ?></small>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </div>
  <?php endif; ?>
</div>
