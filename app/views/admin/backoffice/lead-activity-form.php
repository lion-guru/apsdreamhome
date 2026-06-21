<?php $lead = $lead ?? []; ?>
<div class="container-fluid py-4">
  <h1 class="h3 mb-4"><?= __('bko_add_activity') ?> #<?= htmlspecialchars($lead['lead_number'] ?? '') ?></h1>
  <div class="card aps-cp-card">
    <div class="card-body aps-cp-card-body">
      <form method="post" action="<?= BASE_URL ?>/admin/backoffice/leads/<?= $lead['id'] ?? '' ?>/activity">
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">
        <div class="row g-3">
          <div class="col-md-4"><label class="form-label"><?= __('bko_activity_type') ?></label><select name="activity_type" class="form-select">
            <option value="call"><?= __('bko_call') ?></option><option value="sms"><?= __('bko_sms') ?></option><option value="whatsapp"><?= __('bko_whatsapp') ?></option>
            <option value="email"><?= __('bko_email') ?></option><option value="visit"><?= __('bko_visit') ?></option><option value="meeting"><?= __('bko_meeting') ?></option><option value="note"><?= __('bko_note') ?></option>
          </select></div>
          <div class="col-md-4"><label class="form-label"><?= __('bko_subject') ?></label><input type="text" name="subject" class="form-control" required></div>
          <div class="col-md-4"><label class="form-label"><?= __('bko_activity_date') ?></label><input type="datetime-local" name="activity_date" class="form-control" value="<?= date('Y-m-d\TH:i') ?>"></div>
          <div class="col-12"><label class="form-label"><?= __('bko_description') ?></label><textarea name="description" class="form-control" rows="3"></textarea></div>
          <div class="col-md-4"><label class="form-label"><?= __('bko_next_follow_up') ?></label><input type="date" name="next_follow_up" class="form-control"></div>
          <div class="col-md-4"><label class="form-label"><?= __('bko_outcome') ?></label><input type="text" name="outcome" class="form-control"></div>
          <div class="col-12"><button class="btn btn-primary"><i class="fas fa-save me-1"></i><?= __('bko_add_activity') ?></button></div>
        </div>
      </form>
    </div>
  </div>
</div>
