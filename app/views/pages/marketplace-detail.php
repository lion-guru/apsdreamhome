<?php $prop = $property ?? []; ?>
<div class="container py-4">
  <nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= BASE_URL ?>">Home</a></li>
      <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/marketplace">Marketplace</a></li>
      <li class="breadcrumb-item active"><?= htmlspecialchars(mb_substr($prop['name'] ?? '', 0, 40)) ?></li>
    </ol>
  </nav>

  <div class="row g-4">
    <div class="col-lg-8">
      <div class="aps-cp-card">
        <?php if (!empty($prop['image'])): ?>
          <img src="<?= BASE_URL ?>/<?= htmlspecialchars($prop['image']) ?>" class="card-img-top rounded-top" style="width:100%;max-height:400px;object-fit:cover" alt="<?= htmlspecialchars($prop['name']) ?>">
        <?php endif; ?>
        <div class="aps-cp-card-body">
          <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
            <div>
              <h3 class="mb-1"><?= htmlspecialchars($prop['name'] ?? '') ?></h3>
              <p class="text-muted mb-0"><i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($prop['city_name'] ?? $prop['location'] ?? $prop['address'] ?? '') ?></p>
            </div>
            <h3 class="text-success mb-0">₹<?= number_format($prop['price'] ?? 0) ?></h3>
          </div>

          <div class="row g-3 mb-4">
            <div class="col-3 text-center">
              <div class="bg-light rounded p-2">
                <small class="text-muted">Type</small>
                <p class="mb-0 fw-bold text-capitalize"><?= htmlspecialchars($prop['property_type'] ?? '') ?></p>
              </div>
            </div>
            <div class="col-3 text-center">
              <div class="bg-light rounded p-2">
                <small class="text-muted">For</small>
                <p class="mb-0 fw-bold text-capitalize"><?= htmlspecialchars($prop['listing_type'] ?? '') ?></p>
              </div>
            </div>
            <div class="col-3 text-center">
              <div class="bg-light rounded p-2">
                <small class="text-muted">Area</small>
                <p class="mb-0 fw-bold"><?= number_format($prop['area_sqft'] ?? 0) ?> sqft</p>
              </div>
            </div>
            <div class="col-3 text-center">
              <div class="bg-light rounded p-2">
                <small class="text-muted">Price/sqft</small>
                <p class="mb-0 fw-bold">₹<?= number_format(($prop['area_sqft'] ?? 1) > 0 ? ($prop['price'] ?? 0) / $prop['area_sqft'] : 0) ?></p>
              </div>
            </div>
          </div>

          <h5>Description</h5>
          <p class="text-muted"><?= nl2br(htmlspecialchars($prop['description'] ?? 'No description provided.')) ?></p>

          <?php if (!empty($prop['bedrooms']) || !empty($prop['bathrooms']) || !empty($prop['furnished'])): ?>
            <h5 class="mt-4">Additional Details</h5>
            <div class="row g-2">
              <?php if (!empty($prop['bedrooms'])): ?>
                <div class="col-md-4"><strong>Bedrooms:</strong> <?= (int)$prop['bedrooms'] ?></div>
              <?php endif; ?>
              <?php if (!empty($prop['bathrooms'])): ?>
                <div class="col-md-4"><strong>Bathrooms:</strong> <?= (int)$prop['bathrooms'] ?></div>
              <?php endif; ?>
              <?php if (!empty($prop['furnished'])): ?>
                <div class="col-md-4"><strong>Furnished:</strong> <span class="text-capitalize"><?= htmlspecialchars($prop['furnished']) ?></span></div>
              <?php endif; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div class="col-lg-4">
      <div class="aps-cp-card mb-3">
        <div class="aps-cp-card-header"><i class="fas fa-user me-2"></i>Contact Owner</div>
        <div class="aps-cp-card-body">
          <?php if (!empty($prop['name'])): ?>
            <p class="mb-1"><strong><?= htmlspecialchars($prop['name']) ?></strong></p>
          <?php endif; ?>
          <?php if (!empty($prop['phone'])): ?>
            <p class="mb-1"><i class="fas fa-phone me-1"></i><a href="tel:<?= htmlspecialchars($prop['phone']) ?>"><?= htmlspecialchars($prop['phone']) ?></a></p>
          <?php endif; ?>
          <?php if (!empty($prop['email'])): ?>
            <p class="mb-0"><i class="fas fa-envelope me-1"></i><a href="mailto:<?= htmlspecialchars($prop['email']) ?>"><?= htmlspecialchars($prop['email']) ?></a></p>
          <?php endif; ?>
          <hr>
          <div class="d-flex gap-2">
            <a href="https://wa.me/91<?= preg_replace('/[^0-9]/', '', $prop['phone'] ?? '') ?>?text=Hi%2C%20I'm%20interested%20in%20your%20property%20listed%20on%20APS%20Dream%20Home" target="_blank" class="btn btn-success btn-sm flex-fill"><i class="fab fa-whatsapp me-1"></i>WhatsApp</a>
            <a href="tel:<?= htmlspecialchars($prop['phone'] ?? '') ?>" class="btn btn-primary btn-sm flex-fill"><i class="fas fa-phone me-1"></i>Call</a>
          </div>
        </div>
      </div>

      <div class="aps-cp-card">
        <div class="aps-cp-card-header"><i class="fas fa-chart-bar me-2"></i>Listing Stats</div>
        <div class="aps-cp-card-body">
          <p class="mb-1"><i class="fas fa-eye me-1 text-muted"></i> Views: <?= (int)($prop['views'] ?? 0) ?></p>
          <p class="mb-1"><i class="fas fa-envelope me-1 text-muted"></i> Inquiries: <?= (int)($prop['inquiries'] ?? 0) ?></p>
          <p class="mb-0"><i class="fas fa-calendar me-1 text-muted"></i> Listed: <?= date('d M Y', strtotime($prop['created_at'] ?? 'now')) ?></p>
        </div>
      </div>
    </div>
  </div>
</div>
