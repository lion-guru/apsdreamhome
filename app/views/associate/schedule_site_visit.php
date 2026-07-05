<?php
$page_title = $page_title ?? __('assoc_ssv_title', [], 'Schedule Site Visit');
$current_page = 'site_visits';
$leads = $leads ?? [];
$colonies = $colonies ?? [];
$selected_lead = $selected_lead ?? '';
?>

<div class="container-fluid px-4 py-3">
    <a href="<?= BASE_URL ?>/associate/site-visits" class="text-decoration-none mb-3 d-inline-block">
        <i class="fas fa-arrow-left me-1"></i> <?= __('assoc_ssv_back', [], 'Back to Site Visits') ?>
    </a>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-calendar-plus text-primary me-2"></i><?= __('assoc_ssv_title', [], 'Schedule a Site Visit') ?></h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= BASE_URL ?>/associate/site-visits/schedule" id="scheduleForm">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

                        <div class="mb-3">
                            <label class="form-label fw-bold"><?= __('assoc_ssv_link_lead', [], 'Link to Lead') ?> <small class="text-muted">(<?= __('assoc_ssv_optional', [], 'optional') ?>)</small></label>
                            <select class="form-select" name="lead_id" id="leadSelect">
                                <option value="">— <?= __('assoc_ssv_select_lead', [], 'Select a lead') ?> —</option>
                                <?php foreach ($leads as $l): ?>
                                    <option value="<?= $l['id'] ?>" <?= (string)$l['id'] === (string)$selected_lead ? 'selected' : '' ?>
                                        data-name="<?= htmlspecialchars($l['name']) ?>" data-phone="<?= htmlspecialchars($l['phone']) ?>">
                                        <?= htmlspecialchars($l['name']) ?> (<?= htmlspecialchars($l['phone']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted"><?= __('assoc_ssv_link_hint', [], 'Link a lead to auto-fill visitor details and track the visit in their timeline.') ?></small>
                        </div>

                        <hr>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold"><?= __('assoc_ssv_visitor_name', [], 'Visitor Name') ?> *</label>
                                <input type="text" class="form-control" name="visitor_name" id="visitorName" required placeholder="<?= __('assoc_ssv_name_placeholder', [], 'Full name') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold"><?= __('assoc_ssv_phone', [], 'Phone Number') ?> *</label>
                                <input type="tel" class="form-control" name="visitor_phone" id="visitorPhone" required placeholder="<?= __('assoc_ssv_phone_placeholder', [], '10-digit mobile') ?>" pattern="[0-9]{10}">
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold"><?= __('assoc_ssv_date', [], 'Visit Date') ?> *</label>
                                <input type="date" class="form-control" name="visit_date" required min="<?= date('Y-m-d') ?>" value="<?= date('Y-m-d') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold"><?= __('assoc_ssv_time', [], 'Visit Time') ?> *</label>
                                <input type="time" class="form-control" name="visit_time" required value="10:00">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold"><?= __('assoc_ssv_duration', [], 'Duration') ?></label>
                                <select class="form-select" name="duration">
                                    <option value="30"><?= __('assoc_ssv_30min', [], '30 minutes') ?></option>
                                    <option value="60" selected><?= __('assoc_ssv_1hr', [], '1 hour') ?></option>
                                    <option value="90"><?= __('assoc_ssv_1_5hr', [], '1.5 hours') ?></option>
                                    <option value="120"><?= __('assoc_ssv_2hr', [], '2 hours') ?></option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold"><?= __('assoc_ssv_colony', [], 'Colony / Location') ?> <small class="text-muted">(<?= __('assoc_ssv_optional', [], 'optional') ?>)</small></label>
                            <select class="form-select" name="colony_id">
                                <option value="">— <?= __('assoc_ssv_select_colony', [], 'Select colony') ?> —</option>
                                <?php foreach ($colonies as $c): ?>
                                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold"><?= __('assoc_ssv_notes', [], 'Notes') ?> <small class="text-muted">(<?= __('assoc_ssv_optional', [], 'optional') ?>)</small></label>
                            <textarea class="form-control" name="notes" rows="3" placeholder="<?= __('assoc_ssv_notes_placeholder', [], 'Special requirements, what to show, customer preferences...') ?>"></textarea>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-calendar-check me-1"></i> <?= __('assoc_ssv_schedule', [], 'Schedule Visit') ?>
                            </button>
                            <a href="<?= BASE_URL ?>/associate/site-visits" class="btn btn-outline-secondary"><?= __('assoc_ssv_cancel', [], 'Cancel') ?></a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('leadSelect').addEventListener('change', function() {
    var opt = this.options[this.selectedIndex];
    if (opt.value) {
        document.getElementById('visitorName').value = opt.dataset.name || '';
        document.getElementById('visitorPhone').value = opt.dataset.phone || '';
    }
});
</script>
