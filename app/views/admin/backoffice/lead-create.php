<?php $employees = $employees ?? []; ?>
<div class="container-fluid py-4">
  <h1 class="h3 mb-4"><?= __('bko_new_lead') ?></h1>
  <div class="card aps-cp-card">
    <div class="card-body aps-cp-card-body">
      <form method="post" action="<?= BASE_URL ?>/admin/backoffice/leads/store">
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">
        <div class="row g-3">
          <div class="col-md-4"><label class="form-label"><?= __('bko_lead_name') ?> *</label><input type="text" name="lead_name" class="form-control" required></div>
          <div class="col-md-4"><label class="form-label"><?= __('bko_contact_name') ?></label><input type="text" name="contact_name" class="form-control"></div>
          <div class="col-md-4"><label class="form-label"><?= __('bko_phone') ?></label><input type="text" name="contact_phone" class="form-control"></div>
          <div class="col-md-4"><label class="form-label"><?= __('bko_email') ?></label><input type="email" name="contact_email" class="form-control"></div>
          <div class="col-md-4"><label class="form-label"><?= __('bko_lead_source') ?></label><select name="lead_source" class="form-select">
            <?php foreach(['walk_in','referral','website','phone','social_media','agent','advertisement','portal','cold_call','other'] as $s): ?>
              <option value="<?= $s ?>"><?= ucfirst(str_replace('_',' ',$s)) ?></option>
            <?php endforeach; ?>
          </select></div>
          <div class="col-md-4"><label class="form-label"><?= __('bko_lead_type') ?></label><select name="lead_type" class="form-select">
            <?php foreach(['buyer','seller','tenant','landlord','investor'] as $t): ?>
              <option value="<?= $t ?>"><?= ucfirst($t) ?></option>
            <?php endforeach; ?>
          </select></div>
          <div class="col-md-4"><label class="form-label"><?= __('bko_property_type') ?></label><input type="text" name="property_type" class="form-control" placeholder="e.g. flat, house, plot"></div>
          <div class="col-md-4"><label class="form-label"><?= __('bko_budget_min') ?></label><input type="number" name="budget_min" class="form-control"></div>
          <div class="col-md-4"><label class="form-label"><?= __('bko_budget_max') ?></label><input type="number" name="budget_max" class="form-control"></div>
          <div class="col-md-4"><label class="form-label"><?= __('bko_priority') ?></label><select name="priority" class="form-select">
            <option value="warm"><?= __('bko_warm') ?></option><option value="hot"><?= __('bko_hot') ?></option><option value="cold"><?= __('bko_cold') ?></option><option value="dead"><?= __('bko_dead') ?></option>
          </select></div>
          <div class="col-md-4"><label class="form-label"><?= __('bko_assigned_to') ?></label><select name="assigned_to" class="form-select">
            <option value=""><?= __('bko_unassigned') ?></option>
            <?php foreach ($employees as $e): ?><option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['name'] ?? '') ?></option><?php endforeach; ?>
          </select></div>
          <div class="col-md-4"><label class="form-label"><?= __('bko_follow_up_date') ?></label><input type="date" name="follow_up_date" class="form-control"></div>
          <div class="col-12"><label class="form-label"><?= __('bko_preferred_location') ?></label><input type="text" name="preferred_location" class="form-control"></div>
          <div class="col-12"><label class="form-label"><?= __('bko_requirement_details') ?></label><textarea name="requirement_details" class="form-control" rows="3"></textarea></div>
          <div class="col-12"><button class="btn btn-primary"><i class="fas fa-save me-1"></i><?= __('bko_save_lead') ?></button></div>
        </div>
      </form>
    </div>
  </div>
</div>
