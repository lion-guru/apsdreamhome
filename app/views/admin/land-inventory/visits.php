<?php
$lead  = $lead  ?? [];
$visits = $visits ?? [];
$id = (int)($lead['id'] ?? 0);
?>
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="fas fa-map-marker-alt text-primary me-2"></i>Site Visits — Lead #<?= $id ?></h4>
            <small class="text-muted"><?= htmlspecialchars($lead['land_owner_name'] ?? '') ?></small>
        </div>
        <a href="<?= BASE_URL ?>/admin/land-inventory/leads/<?= $id ?>" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Back to Lead
        </a>
    </div>

    <div class="row g-3">
        <div class="col-md-5">
            <div class="aps-cp-card">
                <div class="aps-cp-card-header"><i class="fas fa-plus-circle me-2"></i>Record New Visit</div>
                <div class="aps-cp-card-body">
                    <form method="post" action="<?= BASE_URL ?>/admin/land-inventory/leads/<?= $id ?>/visits/store">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                        <div class="mb-2">
                            <label class="form-label small">Visit Date / Time <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="visit_date" class="form-control form-control-sm" value="<?= date('Y-m-d\TH:i') ?>" required>
                        </div>
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label small">GPS Lat</label>
                                <input type="number" step="0.0000001" name="gps_lat" class="form-control form-control-sm">
                            </div>
                            <div class="col-6">
                                <label class="form-label small">GPS Lng</label>
                                <input type="number" step="0.0000001" name="gps_lng" class="form-control form-control-sm">
                            </div>
                        </div>
                        <div class="mb-2 mt-2">
                            <label class="form-label small">Weather</label>
                            <input type="text" name="weather" class="form-control form-control-sm" placeholder="Sunny / Cloudy / Rainy">
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">Observations</label>
                            <textarea name="observations" class="form-control form-control-sm" rows="3"></textarea>
                        </div>
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label small">Risk Rating</label>
                                <select name="risk_rating" class="form-select form-select-sm">
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <div class="form-check mt-4">
                                    <input class="form-check-input" type="checkbox" name="encroachment_found" value="1" id="enc">
                                    <label class="form-check-label" for="enc">Encroachment Found</label>
                                </div>
                            </div>
                        </div>
                        <div class="mb-2 mt-2">
                            <label class="form-label small">Encroachment Details</label>
                            <textarea name="encroachment_details" class="form-control form-control-sm" rows="2"></textarea>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">Photos (JSON URLs)</label>
                            <textarea name="photos_json" class="form-control form-control-sm" rows="2" placeholder='["https://...", "https://..."]'></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm w-100">
                            <i class="fas fa-save me-1"></i>Record Visit
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="aps-cp-card">
                <div class="aps-cp-card-header"><i class="fas fa-history me-2"></i>Visit History (<?= count($visits) ?>)</div>
                <div class="aps-cp-card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr><th>Date</th><th>GPS</th><th>Risk</th><th>Encroach</th><th>Observations</th></tr>
                            </thead>
                            <tbody>
                            <?php foreach ($visits as $v): ?>
                                <tr>
                                    <td><small><?= htmlspecialchars($v['visit_date'] ?? '') ?></small></td>
                                    <td><small><?= htmlspecialchars($v['gps_lat'] ?? '—') ?>, <?= htmlspecialchars($v['gps_lng'] ?? '—') ?></small></td>
                                    <td>
                                        <span class="badge bg-<?= ($v['risk_rating'] ?? '') === 'high' ? 'danger' : (($v['risk_rating'] ?? '') === 'medium' ? 'warning' : 'success') ?>">
                                            <?= htmlspecialchars($v['risk_rating'] ?? 'low') ?>
                                        </span>
                                    </td>
                                    <td><?= !empty($v['encroachment_found']) ? '<span class="badge bg-danger">Yes</span>' : '<span class="badge bg-success">No</span>' ?></td>
                                    <td><small><?= nl2br(htmlspecialchars($v['observations'] ?? '—')) ?></small></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($visits)): ?>
                                <tr><td colspan="5" class="text-center text-muted py-4">No visits recorded yet.</td></tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
