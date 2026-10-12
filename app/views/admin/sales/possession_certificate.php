<?php
/** Possession certificate issuance per booking. */
/** @var array $booking */
/** @var array $certs */
$booking = $booking ?? [];
$certs = $certs ?? [];
$base = defined('BASE_URL') ? BASE_URL : '';
$e = function ($v) { return htmlspecialchars((string)($v ?? '')); };
?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-1"><i class="fas fa-key me-2"></i>Possession Handover (कब्ज़ा पत्र)</h4>
        <p class="text-muted mb-0">Booking <?= $e($booking['booking_number'] ?? '') ?> — plot <?= $e($booking['plot_number'] ?? '') ?>, <?= $e($booking['colony_name'] ?? '') ?></p>
    </div>
    <a href="<?= $base ?>/admin/sales/bookings/<?= (int)($booking['id'] ?? 0) ?>" class="btn btn-outline-secondary btn-sm">← Booking</a>
</div>
<div class="aps-cp-card mb-3"><div class="aps-cp-card-body">
    <h6 class="fw-bold mb-3">Issue New Certificate</h6>
    <form method="POST" action="<?= $base ?>/admin/sales/bookings/<?= (int)($booking['id'] ?? 0) ?>/possession-certificate/issue" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
        <div class="row g-2">
            <div class="col-md-2"><label class="form-label small">Handover date *</label><input type="date" name="handover_date" class="form-control" required value="<?= date('Y-m-d') ?>"></div>
            <div class="col-md-2"><label class="form-label small">Status</label><select name="handover_status" class="form-select"><option value="final">Final</option><option value="provisional">Provisional</option></select></div>
            <div class="col-md-3"><label class="form-label small">Site engineer</label><input type="text" name="site_engineer_name" class="form-control" maxlength="100"></div>
            <div class="col-md-2"><label class="form-label small">Engineer phone</label><input type="tel" name="site_engineer_phone" class="form-control" maxlength="20"></div>
            <div class="col-md-3"><label class="form-label small">Pillar photo (≤5MB)</label><input type="file" name="pillar_photo" class="form-control" accept=".jpg,.jpeg,.png,.webp"></div>
            <div class="col-12 form-check ms-1"><input type="checkbox" name="demarcation_done" value="1" class="form-check-input" id="demBox" checked><label class="form-check-label small" for="demBox">Boundary pillars demarcated (4 pillars installed)</label></div>
            <div class="col-12"><label class="form-label small">Remarks (पूर्व/पश्चिम/उत्तर/दक्षिण measurements)</label><textarea name="remarks" class="form-control" rows="2" maxlength="1000"></textarea></div>
        </div>
        <button type="submit" class="btn btn-success mt-3"><i class="fas fa-certificate me-1"></i>Issue Certificate</button>
    </form>
</div></div>
<div class="aps-cp-card"><div class="aps-cp-card-body p-0">
    <div class="table-responsive"><table class="table table-hover mb-0">
        <thead class="table-light"><tr><th>Cert No.</th><th>Handover</th><th>Status</th><th>Engineer</th><th>Photo</th><th>Issued</th></tr></thead>
        <tbody>
        <?php if (empty($certs)): ?>
            <tr><td colspan="6" class="text-center text-muted py-4">No certificates issued yet.</td></tr>
        <?php else: foreach ($certs as $c): ?>
            <tr>
                <td class="fw-medium"><?= $e($c['certificate_no']) ?></td>
                <td><?= $e($c['handover_date']) ?></td>
                <td><span class="badge bg-<?= ($c['handover_status'] ?? '') === 'final' ? 'success' : 'warning text-dark' ?>"><?= $e($c['handover_status']) ?></span>
                    <?= !empty($c['demarcation_done']) ? '<span class="badge bg-info text-dark">pillars ✓</span>' : '' ?></td>
                <td><?= $e($c['site_engineer_name'] ?? '—') ?></td>
                <td><?= !empty($c['pillar_photo_path']) ? '<a target="_blank" href="' . $base . '/' . $e($c['pillar_photo_path']) . '">view</a>' : '<span class="text-muted">—</span>' ?></td>
                <td class="small"><?= $e($c['created_at'] ?? '') ?></td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table></div>
</div></div>
