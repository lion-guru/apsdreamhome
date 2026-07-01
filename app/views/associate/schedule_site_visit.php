<?php
$page_title = $page_title ?? 'Schedule Site Visit';
$current_page = 'site_visits';
$leads = $leads ?? [];
$colonies = $colonies ?? [];
$selected_lead = $selected_lead ?? '';
?>

<div class="container-fluid px-4 py-3">
    <a href="<?= BASE_URL ?>/associate/site-visits" class="text-decoration-none mb-3 d-inline-block">
        <i class="fas fa-arrow-left me-1"></i> Back to Site Visits
    </a>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-calendar-plus text-primary me-2"></i>Schedule a Site Visit</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= BASE_URL ?>/associate/site-visits/schedule" id="scheduleForm">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

                        <!-- Lead Selection -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Link to Lead <small class="text-muted">(optional)</small></label>
                            <select class="form-select" name="lead_id" id="leadSelect">
                                <option value="">— Select a lead —</option>
                                <?php foreach ($leads as $l): ?>
                                    <option value="<?= $l['id'] ?>" <?= (string)$l['id'] === (string)$selected_lead ? 'selected' : '' ?>
                                        data-name="<?= htmlspecialchars($l['name']) ?>" data-phone="<?= htmlspecialchars($l['phone']) ?>">
                                        <?= htmlspecialchars($l['name']) ?> (<?= htmlspecialchars($l['phone']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">Link a lead to auto-fill visitor details and track the visit in their timeline.</small>
                        </div>

                        <hr>

                        <!-- Visitor Details -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Visitor Name *</label>
                                <input type="text" class="form-control" name="visitor_name" id="visitorName" required placeholder="Full name">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Phone Number *</label>
                                <input type="tel" class="form-control" name="visitor_phone" id="visitorPhone" required placeholder="10-digit mobile" pattern="[0-9]{10}">
                            </div>
                        </div>

                        <!-- Date & Time -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Visit Date *</label>
                                <input type="date" class="form-control" name="visit_date" required min="<?= date('Y-m-d') ?>" value="<?= date('Y-m-d') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Visit Time *</label>
                                <input type="time" class="form-control" name="visit_time" required value="10:00">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Duration</label>
                                <select class="form-select" name="duration">
                                    <option value="30">30 minutes</option>
                                    <option value="60" selected>1 hour</option>
                                    <option value="90">1.5 hours</option>
                                    <option value="120">2 hours</option>
                                </select>
                            </div>
                        </div>

                        <!-- Colony -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Colony / Location <small class="text-muted">(optional)</small></label>
                            <select class="form-select" name="colony_id">
                                <option value="">— Select colony —</option>
                                <?php foreach ($colonies as $c): ?>
                                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Notes -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Notes <small class="text-muted">(optional)</small></label>
                            <textarea class="form-control" name="notes" rows="3" placeholder="Special requirements, what to show, customer preferences..."></textarea>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-calendar-check me-1"></i> Schedule Visit
                            </button>
                            <a href="<?= BASE_URL ?>/associate/site-visits" class="btn btn-outline-secondary">Cancel</a>
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
