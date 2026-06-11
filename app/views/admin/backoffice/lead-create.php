<?php $employees = $employees ?? []; ?>
<div class="container-fluid py-4">
  <h1 class="h3 mb-4">New Lead</h1>
  <div class="card aps-cp-card">
    <div class="card-body">
      <form method="post" action="<?= BASE_URL ?>/admin/backoffice/leads/store">
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">
        <div class="row g-3">
          <div class="col-md-4"><label class="form-label">Lead Name *</label><input type="text" name="lead_name" class="form-control" required></div>
          <div class="col-md-4"><label class="form-label">Contact Name</label><input type="text" name="contact_name" class="form-control"></div>
          <div class="col-md-4"><label class="form-label">Phone</label><input type="text" name="contact_phone" class="form-control"></div>
          <div class="col-md-4"><label class="form-label">Email</label><input type="email" name="contact_email" class="form-control"></div>
          <div class="col-md-4"><label class="form-label">Lead Source</label><select name="lead_source" class="form-select">
            <?php foreach(['walk_in','referral','website','phone','social_media','agent','advertisement','portal','cold_call','other'] as $s): ?>
              <option value="<?= $s ?>"><?= ucfirst(str_replace('_',' ',$s)) ?></option>
            <?php endforeach; ?>
          </select></div>
          <div class="col-md-4"><label class="form-label">Lead Type</label><select name="lead_type" class="form-select">
            <?php foreach(['buyer','seller','tenant','landlord','investor'] as $t): ?>
              <option value="<?= $t ?>"><?= ucfirst($t) ?></option>
            <?php endforeach; ?>
          </select></div>
          <div class="col-md-4"><label class="form-label">Property Type</label><input type="text" name="property_type" class="form-control" placeholder="e.g. flat, house, plot"></div>
          <div class="col-md-4"><label class="form-label">Budget Min</label><input type="number" name="budget_min" class="form-control"></div>
          <div class="col-md-4"><label class="form-label">Budget Max</label><input type="number" name="budget_max" class="form-control"></div>
          <div class="col-md-4"><label class="form-label">Priority</label><select name="priority" class="form-select">
            <option value="warm">Warm</option><option value="hot">Hot</option><option value="cold">Cold</option><option value="dead">Dead</option>
          </select></div>
          <div class="col-md-4"><label class="form-label">Assigned To</label><select name="assigned_to" class="form-select">
            <option value="">Unassigned</option>
            <?php foreach ($employees as $e): ?><option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['name'] ?? '') ?></option><?php endforeach; ?>
          </select></div>
          <div class="col-md-4"><label class="form-label">Follow Up Date</label><input type="date" name="follow_up_date" class="form-control"></div>
          <div class="col-12"><label class="form-label">Preferred Location</label><input type="text" name="preferred_location" class="form-control"></div>
          <div class="col-12"><label class="form-label">Requirement Details</label><textarea name="requirement_details" class="form-control" rows="3"></textarea></div>
          <div class="col-12"><button class="btn btn-primary"><i class="fas fa-save me-1"></i>Create Lead</button></div>
        </div>
      </form>
    </div>
  </div>
</div>
