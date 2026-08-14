<?php $lead = $lead ?? []; $timeline = $timeline ?? []; $employees = $employees ?? []; ?>
<div class="container-fluid py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3"><?= __('bko_lead') ?>: <?= htmlspecialchars($lead['lead_number'] ?? '') ?></h1>
    <div>
      <a href="<?= BASE_URL ?>/admin/backoffice/leads/<?= $lead['id'] ?? '' ?>/edit" class="btn btn-outline-primary btn-sm"><i class="fas fa-edit me-1"></i><?= __('bko_edit') ?></a>
      <a href="<?= BASE_URL ?>/admin/backoffice/leads" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i><?= __('bko_back') ?></a>
    </div>
  </div>
  <div class="row g-4">
    <div class="col-md-8">
      <div class="card aps-cp-card mb-4">
        <div class="card-header aps-cp-card-header"><strong><?= __('bko_lead_info') ?></strong></div>
        <div class="card-body aps-cp-card-body">
          <div class="row g-3">
            <div class="col-md-4"><strong><?= __('bko_name') ?>:</strong> <?= htmlspecialchars($lead['lead_name'] ?? '') ?></div>
            <div class="col-md-4"><strong><?= __('bko_contact_name') ?>:</strong> <?= htmlspecialchars($lead['contact_name'] ?? '') ?></div>
            <div class="col-md-4"><strong><?= __('bko_phone') ?>:</strong> <?= htmlspecialchars($lead['contact_phone'] ?? '') ?></div>
            <div class="col-md-4"><strong><?= __('bko_email') ?>:</strong> <?= htmlspecialchars($lead['contact_email'] ?? '') ?></div>
            <div class="col-md-4"><strong><?= __('bko_lead_source') ?>:</strong> <span class="badge bg-secondary"><?= $lead['lead_source'] ?? '' ?></span></div>
            <div class="col-md-4"><strong><?= __('bko_type') ?>:</strong> <?= ucfirst($lead['lead_type'] ?? '') ?></div>
            <div class="col-md-4"><strong><?= __('bko_priority') ?>:</strong> <span class="badge bg-<?= ($lead['priority'] ?? '') === 'hot' ? 'danger' : 'warning' ?>"><?= ucfirst($lead['priority'] ?? '') ?></span></div>
            <div class="col-md-4"><strong><?= __('bko_score') ?>:</strong> <span class="badge bg-primary"><?= $lead['score'] ?? 0 ?></span></div>
            <div class="col-md-4"><strong><?= __('bko_status') ?>:</strong> <span class="badge bg-info"><?= str_replace('_',' ',ucfirst($lead['status'] ?? '')) ?></span></div>
            <div class="col-md-4"><strong><?= __('bko_budget') ?>:</strong> <?= $lead['budget_min'] ? '&#8377;'.number_format($lead['budget_min']) : '' ?> - <?= $lead['budget_max'] ? '&#8377;'.number_format($lead['budget_max']) : '' ?></div>
            <div class="col-md-4"><strong><?= __('bko_property_type') ?>:</strong> <?= htmlspecialchars($lead['property_type'] ?? '') ?></div>
            <div class="col-md-4"><strong><?= __('bko_preferred_location') ?>:</strong> <?= htmlspecialchars($lead['preferred_location'] ?? '') ?></div>
            <div class="col-12"><strong><?= __('bko_requirements') ?>:</strong> <?= nl2br(htmlspecialchars($lead['requirement_details'] ?? '')) ?></div>
          </div>
        </div>
      </div>

      <div class="card aps-cp-card">
        <div class="card-header aps-cp-card-header"><strong><?= __('bko_advance_stage') ?></strong></div>
        <div class="card-body aps-cp-card-body">
          <form method="post" action="<?= BASE_URL ?>/admin/backoffice/leads/<?= $lead['id'] ?? '' ?>/advance" class="d-flex gap-2">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">
            <select name="new_stage" class="form-select" class="style-14945">
              <?php foreach(['new','contacted','qualified','viewing','negotiation','closed_won','closed_lost','on_hold'] as $s): ?>
                <option value="<?= $s ?>" <?= ($lead['status'] ?? '') === $s ? 'selected' : '' ?>><?= ucfirst(str_replace('_',' ',$s)) ?></option>
              <?php endforeach; ?>
            </select>
            <button class="btn btn-warning"><i class="fas fa-arrow-right me-1"></i><?= __('bko_move') ?></button>
          </form>
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card aps-cp-card mb-4">
        <div class="card-header aps-cp-card-header"><strong><?= __('bko_add_activity') ?></strong></div>
        <div class="card-body aps-cp-card-body">
          <form method="post" action="<?= BASE_URL ?>/admin/backoffice/leads/<?= $lead['id'] ?? '' ?>/activity">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">
            <div class="mb-2"><select name="activity_type" class="form-select form-select-sm">
              <option value="call"><?= __('bko_call') ?></option><option value="sms"><?= __('bko_sms') ?></option><option value="whatsapp"><?= __('bko_whatsapp') ?></option>
              <option value="email"><?= __('bko_email') ?></option><option value="visit"><?= __('bko_visit') ?></option><option value="meeting"><?= __('bko_meeting') ?></option><option value="note"><?= __('bko_note') ?></option>
            </select></div>
            <div class="mb-2"><input type="text" name="subject" class="form-control form-control-sm" placeholder="<?= __('bko_subject') ?>"></div>
            <div class="mb-2"><textarea name="description" class="form-control form-control-sm" rows="2" placeholder="<?= __('bko_details') ?>"></textarea></div>
            <div class="mb-2"><input type="date" name="next_follow_up" class="form-control form-control-sm"></div>
            <div class="mb-2"><input type="text" name="outcome" class="form-control form-control-sm" placeholder="<?= __('bko_outcome') ?>"></div>
            <button class="btn btn-primary btn-sm w-100"><i class="fas fa-plus me-1"></i><?= __('bko_add_activity') ?></button>
          </form>
        </div>
      </div>

      <div class="card aps-cp-card">
        <div class="card-header aps-cp-card-header"><strong><?= __('bko_timeline') ?></strong></div>
        <div class="card-body aps-cp-card-body" class="style-23214">
          <?php if (empty($timeline)): ?>
            <p class="text-muted"><?= __('bko_no_activities') ?></p>
          <?php else: ?>
            <?php foreach ($timeline as $t): ?>
              <div class="border-start border-2 ps-3 mb-3">
                <div class="small text-muted"><?= $t['activity_date'] ?? '' ?> | <?= ucfirst($t['activity_type'] ?? '') ?></div>
                <div><strong><?= htmlspecialchars($t['subject'] ?? '') ?></strong></div>
                <div class="small"><?= htmlspecialchars($t['description'] ?? '') ?></div>
                <?php if ($t['outcome'] ?? ''): ?><div class="small text-success"><?= __('bko_outcome') ?>: <?= htmlspecialchars($t['outcome']) ?></div><?php endif; ?>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>
