<?php
$page_title = $page_title ?? __('user_resell_submit_title', 'List Resell Property');
$page_heading = $page_heading ?? __('user_resell_submit_heading', 'List Your Property');
$content = $content ?? '';
?>
<div class="container py-5">
  <div class="row">
    <div class="col-md-10 mx-auto">
      <h1 class="h2 mb-4"><i class="fas fa-home me-2 text-primary"></i><?= __('user_resell_submit_page_heading', 'List Your Property for Resale') ?></h1>

      <?php if (empty($_SESSION['user_id'])): ?>
        <div class="alert alert-warning">
          <i class="fas fa-exclamation-triangle me-2"></i><?= __('user_resell_submit_login_required', 'Please') ?> <a href="<?= BASE_URL ?>/login"><?= __('user_resell_submit_login_link', 'login') ?></a> <?= __('user_resell_submit_login_suffix', 'to list a property.') ?>
        </div>
      <?php else: ?>

      <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success'] ?? '') ?><?php unset($_SESSION['success']); ?></div>
      <?php endif; ?>
      <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error'] ?? '') ?><?php unset($_SESSION['error']); ?></div>
      <?php endif; ?>

      <form method="POST" class="card shadow-sm">
                          <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
        <div class="card-body aps-cp-card-body">
          <div class="row g-3">
            <div class="col-md-12">
              <label class="form-label"><?= __('user_resell_submit_property_title', 'Property Title') ?> *</label>
              <input name="title" class="form-control" required maxlength="200" placeholder="<?= __('user_resell_submit_title_ph', 'e.g. 3BHK House in Gorakhpur') ?>">
            </div>
            <div class="col-md-12">
              <label class="form-label"><?= __('user_resell_submit_description', 'Description') ?></label>
              <textarea name="description" class="form-control" rows="4" placeholder="<?= __('user_resell_submit_desc_ph', 'Describe your property...') ?>"></textarea>
            </div>
            <div class="col-md-4">
              <label class="form-label"><?= __('user_resell_submit_property_type', 'Property Type') ?> *</label>
              <select name="property_type" class="form-select" required>
                <option value=""><?= __('user_resell_submit_select', 'Select') ?></option>
                <option value="plot"><?= __('user_resell_submit_type_plot', 'Plot') ?></option>
                <option value="house"><?= __('user_resell_submit_type_house', 'House') ?></option>
                <option value="flat"><?= __('user_resell_submit_type_flat', 'Flat/Apartment') ?></option>
                <option value="shop"><?= __('user_resell_submit_type_shop', 'Shop') ?></option>
                <option value="farmhouse"><?= __('user_resell_submit_type_farmhouse', 'Farmhouse') ?></option>
                <option value="commercial"><?= __('user_resell_submit_type_commercial', 'Commercial') ?></option>
                <option value="land"><?= __('user_resell_submit_type_land', 'Land') ?></option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label"><?= __('user_resell_submit_asking_price', 'Asking Price (₹)') ?> *</label>
              <input name="asking_price" type="number" class="form-control" required min="1" placeholder="2500000">
            </div>
            <div class="col-md-4">
              <label class="form-label"><?= __('user_resell_submit_area', 'Area (sqft)') ?> *</label>
              <input name="area_sqft" type="number" class="form-control" required min="1" placeholder="1200">
            </div>
            <div class="col-md-3">
              <label class="form-label"><?= __('user_resell_submit_bedrooms', 'Bedrooms') ?></label>
              <input name="bedrooms" type="number" class="form-control" min="0" value="0">
            </div>
            <div class="col-md-3">
              <label class="form-label"><?= __('user_resell_submit_bathrooms', 'Bathrooms') ?></label>
              <input name="bathrooms" type="number" class="form-control" min="0" value="0">
            </div>
            <div class="col-md-3">
              <label class="form-label"><?= __('user_resell_submit_age', 'Age (years)') ?></label>
              <input name="age_years" type="number" class="form-control" min="0" value="0">
            </div>
            <div class="col-md-3">
              <label class="form-label"><?= __('user_resell_submit_original_price', 'Original Price (₹)') ?></label>
              <input name="original_price" type="number" class="form-control" placeholder="<?= __('user_resell_submit_optional', 'Optional') ?>">
            </div>
            <div class="col-md-12">
              <label class="form-label"><?= __('user_resell_submit_location', 'Location/Address') ?> *</label>
              <input name="location" class="form-control" required placeholder="<?= __('user_resell_submit_location_ph', 'Full address or area name') ?>">
            </div>
            <div class="col-12">
              <label class="form-label"><?= __('user_resell_submit_amenities', 'Amenities (comma separated)') ?></label>
              <input name="amenities" class="form-control" placeholder="<?= __('user_resell_submit_amenities_ph', 'parking, garden, security, power backup') ?>">
              <small class="text-muted"><?= __('user_resell_submit_amenities_hint', 'Separate multiple amenities with commas') ?></small>
            </div>
            <div class="col-12">
              <hr>
              <button class="btn btn-primary btn-lg"><i class="fas fa-paper-plane me-2"></i><?= __('user_resell_submit_cta', 'Submit for Review') ?></button>
              <a href="<?= BASE_URL ?>/resell" class="btn btn-outline-secondary btn-lg"><?= __('user_resell_submit_cancel', 'Cancel') ?></a>
            </div>
          </div>
        </div>
      </form>

      <div class="alert alert-info mt-3">
        <i class="fas fa-info-circle me-2"></i>
        <strong><?= __('user_resell_submit_note_label', 'Note:') ?></strong> <?= __('user_resell_submit_note_text', 'Your property will be reviewed by our team and made public within 24 hours. You\'ll be notified via email once approved.') ?>
      </div>

      <?php endif; ?>
    </div>
  </div>
</div>
