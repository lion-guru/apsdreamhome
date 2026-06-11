<?php $lead = $lead ?? []; $timeline = $timeline ?? []; $employees = $employees ?? []; ?>
<div class="container-fluid py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Lead: <?= htmlspecialchars($lead['lead_number'] ?? '') ?></h1>
    <div>
      <a href="<?= BASE_URL ?>/admin/backoffice/leads/<?= $lead['id'] ?? '' ?>/edit" class="btn btn-outline-primary btn-sm"><i class="fas fa-edit me-1"></i>Edit</a>
      <a href="<?= BASE_URL ?>/admin/backoffice/leads" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>
  </div>
  <div class="row g-4">
    <div class="col-md-8">
      <div class="card aps-cp-card mb-4">
        <div class="card-header"><strong>Lead Information</strong></div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-4"><strong>Name:</strong> <?= htmlspecialchars($lead['lead_name'] ?? '') ?></div>
            <div class="col-md-4"><strong>Contact:</strong> <?= htmlspecialchars($lead['contact_name'] ?? '') ?></div>
            <div class="col-md-4"><strong>Phone:</strong> <?= htmlspecialchars($lead['contact_phone'] ?? '') ?></div>
            <div class="col-md-4"><strong>Email:</strong> <?= htmlspecialchars($lead['contact_email'] ?? '') ?></div>
            <div class="col-md-4"><strong>Source:</strong> <span class="badge bg-secondary"><?= $lead['lead_source'] ?? '' ?></span></div>
            <div class="col-md-4"><strong>Type:</strong> <?= ucfirst($lead['lead_type'] ?? '') ?></div>
            <div class="col-md-4"><strong>Priority:</strong> <span class="badge bg-<?= ($lead['priority'] ?? '') === 'hot' ? 'danger' : 'warning' ?>"><?= ucfirst($lead['priority'] ?? '') ?></span></div>
            <div class="col-md-4"><strong>Score:</strong> <span class="badge bg-primary"><?= $lead['score'] ?? 0 ?></span></div>
            <div class="col-md-4"><strong>Status:</strong> <span class="badge bg-info"><?= str_replace('_',' ',ucfirst($lead['status'] ?? '')) ?></span></div>
            <div class="col-md-4"><strong>Budget:</strong> <?= $lead['budget_min'] ? '&#8377;'.number_format($lead['budget_min']) : '' ?> - <?= $lead['budget_max'] ? '&#8377;'.number_format($lead['budget_max']) : '' ?></div>
            <div class="col-md-4"><strong>Property Type:</strong> <?= htmlspecialchars($lead['property_type'] ?? '') ?></div>
            <div class="col-md-4"><strong>Location:</strong> <?= htmlspecialchars($lead['preferred_location'] ?? '') ?></div>
            <div class="col-12"><strong>Requirements:</strong> <?= nl2br(htmlspecialchars($lead['requirement_details'] ?? '')) ?></div>
          </div>
        </div>
      </div>

      <div class="card aps-cp-card">
        <div class="card-header"><strong>Advance Stage</strong></div>
        <div class="card-body">
          <form method="post" action="<?= BASE_URL ?>/admin/backoffice/leads/<?= $lead['id'] ?? '' ?>/advance" class="d-flex gap-2">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">
            <select name="new_stage" class="form-select" style="max-width:250px">
              <?php foreach(['new','contacted','qualified','viewing','negotiation','closed_won','closed_lost','on_hold'] as $s): ?>
                <option value="<?= $s ?>" <?= ($lead['status'] ?? '') === $s ? 'selected' : '' ?>><?= ucfirst(str_replace('_',' ',$s)) ?></option>
              <?php endforeach; ?>
            </select>
            <button class="btn btn-warning"><i class="fas fa-arrow-right me-1"></i>Move</button>
          </form>
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card aps-cp-card mb-4">
        <div class="card-header"><strong>Add Activity</strong></div>
        <div class="card-body">
          <form method="post" action="<?= BASE_URL ?>/admin/backoffice/leads/<?= $lead['id'] ?? '' ?>/activity">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">
            <div class="mb-2"><select name="activity_type" class="form-select form-select-sm">
              <option value="call">Call</option><option value="sms">SMS</option><option value="whatsapp">WhatsApp</option>
              <option value="email">Email</option><option value="visit">Visit</option><option value="meeting">Meeting</option><option value="note">Note</option>
            </select></div>
            <div class="mb-2"><input type="text" name="subject" class="form-control form-control-sm" placeholder="Subject"></div>
            <div class="mb-2"><textarea name="description" class="form-control form-control-sm" rows="2" placeholder="Details"></textarea></div>
            <div class="mb-2"><input type="date" name="next_follow_up" class="form-control form-control-sm"></div>
            <div class="mb-2"><input type="text" name="outcome" class="form-control form-control-sm" placeholder="Outcome"></div>
            <button class="btn btn-primary btn-sm w-100"><i class="fas fa-plus me-1"></i>Add Activity</button>
          </form>
        </div>
      </div>

      <div class="card aps-cp-card">
        <div class="card-header"><strong>Timeline</strong></div>
        <div class="card-body" style="max-height:400px;overflow-y:auto">
          <?php if (empty($timeline)): ?>
            <p class="text-muted">No activities yet</p>
          <?php else: ?>
            <?php foreach ($timeline as $t): ?>
              <div class="border-start border-2 ps-3 mb-3">
                <div class="small text-muted"><?= $t['activity_date'] ?? '' ?> | <?= ucfirst($t['activity_type'] ?? '') ?></div>
                <div><strong><?= htmlspecialchars($t['subject'] ?? '') ?></strong></div>
                <div class="small"><?= htmlspecialchars($t['description'] ?? '') ?></div>
                <?php if ($t['outcome'] ?? ''): ?><div class="small text-success">Outcome: <?= htmlspecialchars($t['outcome']) ?></div><?php endif; ?>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>
