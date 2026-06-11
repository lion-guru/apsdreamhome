<?php $lead = $lead ?? []; ?>
<div class="container-fluid py-4">
  <h1 class="h3 mb-4">Add Activity to Lead #<?= htmlspecialchars($lead['lead_number'] ?? '') ?></h1>
  <div class="card aps-cp-card">
    <div class="card-body aps-cp-card-body">
      <form method="post" action="<?= BASE_URL ?>/admin/backoffice/leads/<?= $lead['id'] ?? '' ?>/activity">
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">
        <div class="row g-3">
          <div class="col-md-4"><label class="form-label">Activity Type</label><select name="activity_type" class="form-select">
            <option value="call">Call</option><option value="sms">SMS</option><option value="whatsapp">WhatsApp</option>
            <option value="email">Email</option><option value="visit">Visit</option><option value="meeting">Meeting</option><option value="note">Note</option>
          </select></div>
          <div class="col-md-4"><label class="form-label">Subject</label><input type="text" name="subject" class="form-control" required></div>
          <div class="col-md-4"><label class="form-label">Activity Date</label><input type="datetime-local" name="activity_date" class="form-control" value="<?= date('Y-m-d\TH:i') ?>"></div>
          <div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="3"></textarea></div>
          <div class="col-md-4"><label class="form-label">Next Follow Up</label><input type="date" name="next_follow_up" class="form-control"></div>
          <div class="col-md-4"><label class="form-label">Outcome</label><input type="text" name="outcome" class="form-control"></div>
          <div class="col-12"><button class="btn btn-primary"><i class="fas fa-save me-1"></i>Add Activity</button></div>
        </div>
      </form>
    </div>
  </div>
</div>
