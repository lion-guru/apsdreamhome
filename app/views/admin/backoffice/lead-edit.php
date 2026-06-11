<?php $lead = $lead ?? []; $employees = $employees ?? []; ?>
<div class="container-fluid py-4">
  <h1 class="h3 mb-4">Edit Lead: <?= htmlspecialchars($lead['lead_number'] ?? '') ?></h1>
  <div class="card aps-cp-card">
    <div class="card-body">
      <form method="post" action="<?= BASE_URL ?>/admin/backoffice/leads/<?= $lead['id'] ?? '' ?>/update">
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">
        <div class="row g-3">
          <div class="col-md-4"><label class="form-label">Lead Name *</label><input type="text" name="lead_name" class="form-control" value="<?= htmlspecialchars($lead['lead_name'] ?? '') ?>" required></div>
          <div class="col-md-4"><label class="form-label">Contact Name</label><input type="text" name="contact_name" class="form-control" value="<?= htmlspecialchars($lead['contact_name'] ?? '') ?>"></div>
          <div class="col-md-4"><label class="form-label">Phone</label><input type="text" name="contact_phone" class="form-control" value="<?= htmlspecialchars($lead['contact_phone'] ?? '') ?>"></div>
          <div class="col-md-4"><label class="form-label">Email</label><input type="email" name="contact_email" class="form-control" value="<?= htmlspecialchars($lead['contact_email'] ?? '') ?>"></div>
          <div class="col-md-4"><label class="form-label">Lead Source</label><select name="lead_source" class="form-select">
            <?php foreach(['walk_in','referral','website','phone','social_media','agent','advertisement','portal','cold_call','other'] as $s): ?>
              <option value="<?= $s ?>" <?= ($lead['lead_source'] ?? '') === $s ? 'selected' : '' ?>><?= ucfirst(str_replace('_',' ',$s)) ?></option>
            <?php endforeach; ?>
          </select></div>
          <div class="col-md-4"><label class="form-label">Lead Type</label><select name="lead_type" class="form-select">
            <?php foreach(['buyer','seller','tenant','landlord','investor'] as $t): ?>
              <option value="<?= $t ?>" <?= ($lead['lead_type'] ?? '') === $t ? 'selected' : '' ?>><?= ucfirst($t) ?></option>
            <?php endforeach; ?>
          </select></div>
          <div class="col-md-4"><label class="form-label">Property Type</label><input type="text" name="property_type" class="form-control" value="<?= htmlspecialchars($lead['property_type'] ?? '') ?>"></div>
          <div class="col-md-4"><label class="form-label">Budget Min</label><input type="number" name="budget_min" class="form-control" value="<?= $lead['budget_min'] ?? '' ?>"></div>
          <div class="col-md-4"><label class="form-label">Budget Max</label><input type="number" name="budget_max" class="form-control" value="<?= $lead['budget_max'] ?? '' ?>"></div>
          <div class="col-md-4"><label class="form-label">Priority</label><select name="priority" class="form-select">
            <?php foreach(['hot','warm','cold','dead'] as $p): ?>
              <option value="<?= $p ?>" <?= ($lead['priority'] ?? '') === $p ? 'selected' : '' ?>><?= ucfirst($p) ?></option>
            <?php endforeach; ?>
          </select></div>
          <div class="col-md-4"><label class="form-label">Status</label><select name="status" class="form-select">
            <?php foreach(['new','contacted','qualified','viewing','negotiation','closed_won','closed_lost','on_hold'] as $s): ?>
              <option value="<?= $s ?>" <?= ($lead['status'] ?? '') === $s ? 'selected' : '' ?>><?= ucfirst(str_replace('_',' ',$s)) ?></option>
            <?php endforeach; ?>
          </select></div>
          <div class="col-md-4"><label class="form-label">Assigned To</label><select name="assigned_to" class="form-select">
            <option value="">Unassigned</option>
            <?php foreach ($employees as $e): ?><option value="<?= $e['id'] ?>" <?= (int)($lead['assigned_to'] ?? 0) === (int)$e['id'] ? 'selected' : '' ?>><?= htmlspecialchars($e['name'] ?? '') ?></option><?php endforeach; ?>
          </select></div>
          <div class="col-md-4"><label class="form-label">Follow Up Date</label><input type="date" name="follow_up_date" class="form-control" value="<?= $lead['follow_up_date'] ?? '' ?>"></div>
          <div class="col-12"><label class="form-label">Preferred Location</label><input type="text" name="preferred_location" class="form-control" value="<?= htmlspecialchars($lead['preferred_location'] ?? '') ?>"></div>
          <div class="col-12"><label class="form-label">Requirement Details</label><textarea name="requirement_details" class="form-control" rows="3"><?= htmlspecialchars($lead['requirement_details'] ?? '') ?></textarea></div>
          <div class="col-12"><label class="form-label">Closure Notes</label><textarea name="closure_notes" class="form-control" rows="2"><?= htmlspecialchars($lead['closure_notes'] ?? '') ?></textarea></div>
          <div class="col-12"><button class="btn btn-primary"><i class="fas fa-save me-1"></i>Update Lead</button></div>
        </div>
      </form>
    </div>
  </div>
</div>
