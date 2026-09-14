<?php
// Customer Live Registry & Handover Tracker — 7-stage stepper.
// Data: $booking, $registry_source, $canonical_id, $stages,
//       $completed_count, $current_index, $progress_pct, $deed, $can_download.
$booking = $booking ?? [];
$stages = $stages ?? [];
$completed_count = (int)($completed_count ?? 0);
$progress_pct = (float)($progress_pct ?? 0);
$can_download = (bool)($can_download ?? false);
$canonical_id = (int)($canonical_id ?? 0);
$deed = $deed ?? null;
$apptRaw = $booking['appointment_date'] ?? '';
$apptTs = (!empty($apptRaw) && $apptRaw !== '0000-00-00 00:00:00') ? strtotime($apptRaw) : false;
$docsChecklist = [
    'Original allotment letter + booking receipts',
    'Agreement for Sale (signed copy)',
    'Aadhaar Card + PAN Card (buyer + co-buyer)',
    'Passport-size photographs (4 each)',
    'NOC copy (if applicable)',
    'Stamp duty payment challan / receipt',
];
?>
<style>
    .rg-hero { background: linear-gradient(135deg, #0a192f 0%, #1e3a5f 100%); border-radius: 16px; padding: 1.75rem 1.5rem; color: #fff; margin-bottom: 1.5rem; }
    .rg-hero h2 { font-size: 1.3rem; font-weight: 700; margin-bottom: .25rem; }
    .rg-hero p { opacity: .8; font-size: .85rem; margin-bottom: 0; }
    .rg-progress-wrap { background: rgba(255,255,255,.15); border-radius: 8px; height: 10px; overflow: hidden; margin-top: 1rem; }
    .rg-progress-bar { height: 100%; border-radius: 8px; background: linear-gradient(90deg, #22c55e, #10b981); transition: width .6s ease; }
    .rg-progress-label { font-size: .78rem; opacity: .9; margin-top: .4rem; }
    .rg-stepper { position: relative; margin: 0; padding: 0; list-style: none; }
    .rg-step { position: relative; display: flex; gap: 1rem; padding-bottom: 1.5rem; }
    .rg-step:last-child { padding-bottom: 0; }
    .rg-step::before { content: ''; position: absolute; left: 19px; top: 44px; bottom: 0; width: 2px; background: #e2e8f0; }
    .rg-step:last-child::before { display: none; }
    .rg-step.done::before { background: #22c55e; }
    .rg-dot { flex: 0 0 auto; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1rem; z-index: 1; border: 2px solid #e2e8f0; background: #f8fafc; color: #94a3b8; }
    .rg-step.done .rg-dot { background: #22c55e; border-color: #22c55e; color: #fff; }
    .rg-step.current .rg-dot { background: #0d9488; border-color: #0d9488; color: #fff; animation: rgPulse 1.6s ease-out infinite; }
    @keyframes rgPulse { 0% { box-shadow: 0 0 0 0 rgba(13,148,136,.5); } 70% { box-shadow: 0 0 0 12px rgba(13,148,136,0); } 100% { box-shadow: 0 0 0 0 rgba(13,148,136,0); } }
    .rg-card { flex: 1 1 auto; background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: .9rem 1.1rem; box-shadow: 0 1px 3px rgba(0,0,0,.05); }
    .rg-step.done .rg-card { border-color: #bbf7d0; }
    .rg-step.current .rg-card { border-color: #5eead4; box-shadow: 0 0 0 3px rgba(13,148,136,.12); }
    .rg-card h6 { margin: 0 0 .15rem; font-size: .92rem; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: .5rem; flex-wrap: wrap; }
    .rg-card .rg-desc { font-size: .8rem; color: #64748b; margin-bottom: .3rem; }
    .rg-meta { font-size: .76rem; color: #334155; display: flex; flex-wrap: wrap; gap: .35rem .9rem; }
    .rg-badge { display: inline-block; padding: 1px 9px; border-radius: 20px; font-size: .66rem; font-weight: 700; text-transform: uppercase; letter-spacing: .4px; }
    .rg-badge.done { background: #dcfce7; color: #166534; }
    .rg-badge.current { background: #ccfbf1; color: #0f766e; }
    .rg-badge.pending { background: #f1f5f9; color: #64748b; }
    .rg-panel { background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 1.25rem; margin-bottom: 1.25rem; box-shadow: 0 1px 4px rgba(0,0,0,.06); }
    .rg-panel h5 { font-size: .95rem; font-weight: 700; margin-bottom: .8rem; display: flex; align-items: center; gap: .5rem; }
    .rg-check { list-style: none; margin: 0; padding: 0; font-size: .82rem; color: #334155; }
    .rg-check li { padding: .35rem 0 .35rem 1.6rem; position: relative; border-bottom: 1px dashed #f1f5f9; }
    .rg-check li:last-child { border-bottom: none; }
    .rg-check li::before { content: '\2713'; position: absolute; left: 0; top: .35rem; width: 1.15rem; height: 1.15rem; border-radius: 50%; background: #dcfce7; color: #166534; font-size: .7rem; font-weight: 700; display: flex; align-items: center; justify-content: center; }
    @media (max-width: 576px) { .rg-hero { padding: 1.25rem 1rem; } .rg-dot { width: 34px; height: 34px; } .rg-step::before { left: 16px; top: 38px; } }
</style>

<div class="rg-hero">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
        <div>
            <h2><i class="fas fa-stamp me-2"></i>Registry Journey</h2>
            <p>Plot <?= htmlspecialchars($booking['plot_number'] ?? '-') ?><?= !empty($booking['block']) ? ' (' . htmlspecialchars($booking['block']) . ')' : '' ?> &bull; <?= htmlspecialchars($booking['colony_name'] ?? '-') ?> &bull; Booking <?= htmlspecialchars($booking['booking_number'] ?? ('#' . $canonical_id)) ?></p>
        </div>
        <a href="<?= BASE_URL ?>/customer/passbook" class="btn btn-sm btn-light"><i class="fas fa-arrow-left me-1"></i>Passbook</a>
    </div>
    <div class="rg-progress-wrap"><div class="rg-progress-bar" style="width: <?= min(100, max(0, $progress_pct)) ?>%"></div></div>
    <div class="rg-progress-label"><?= $completed_count ?> of <?= count($stages) ?> stages completed (<?= $progress_pct ?>%)</div>
</div>

<?php if (($registry_source ?? '') === 'plot_bookings'): ?>
<div class="alert alert-info"><i class="fas fa-info-circle me-2"></i>Your registry file is being prepared by our legal team. Token &amp; allotment is confirmed below — the remaining stages unlock once the registry file opens.</div>
<?php endif; ?>

<div class="rg-panel">
    <h5><i class="fas fa-route text-primary"></i>Live Progress</h5>
    <ol class="rg-stepper">
        <?php foreach ($stages as $i => $s):
            $st = $s['status'] ?? 'pending';
            $cls = $st === 'completed' ? 'done' : ($st === 'in_progress' ? 'current' : '');
            $icon = $st === 'completed' ? 'fa-check' : ($st === 'in_progress' ? 'fa-spinner fa-spin' : 'fa-clock');
            $badge = $st === 'completed' ? 'done' : ($st === 'in_progress' ? 'current' : 'pending');
            $badgeLabel = $st === 'completed' ? 'Completed' : ($st === 'in_progress' ? 'In Progress' : 'Pending');
        ?>
        <li class="rg-step <?= $cls ?>">
            <div class="rg-dot"><i class="fas <?= $icon ?>"></i></div>
            <div class="rg-card">
                <h6><span class="text-muted"><?= $i + 1 ?>.</span> <?= htmlspecialchars($s['label'] ?? '') ?> <span class="rg-badge <?= $badge ?>"><?= $badgeLabel ?></span></h6>
                <div class="rg-desc"><?= htmlspecialchars($s['desc'] ?? '') ?></div>
                <?php if (!empty($s['meta'])): ?>
                <div class="rg-meta"><?php foreach ((array)$s['meta'] as $m): ?><span><i class="fas fa-angle-right text-success me-1"></i><?= htmlspecialchars($m) ?></span><?php endforeach; ?></div>
                <?php endif; ?>
            </div>
        </li>
        <?php endforeach; ?>
    </ol>
</div>

<?php if ($apptTs): ?>
<div class="rg-panel" style="border-color: #5eead4;">
    <h5><i class="fas fa-calendar-check text-success"></i>Sub-Registrar Appointment</h5>
    <div class="row g-3">
        <div class="col-md-4"><small class="text-muted d-block">Date &amp; Time</small><strong><?= date('d M Y, h:i A', $apptTs) ?></strong></div>
        <div class="col-md-4"><small class="text-muted d-block">Venue</small><strong><?= htmlspecialchars($booking['sub_registrar_office'] ?? 'Sub-Registrar Office') ?></strong></div>
        <div class="col-md-4"><small class="text-muted d-block">Registry Token</small><strong><?= htmlspecialchars($booking['registry_number'] ?? 'Issued at venue') ?></strong></div>
    </div>
    <hr>
    <h5 class="mt-2"><i class="fas fa-briefcase text-primary"></i>Documents to Carry</h5>
    <ul class="rg-check"><?php foreach ($docsChecklist as $doc): ?><li><?= htmlspecialchars($doc) ?></li><?php endforeach; ?></ul>
</div>
<?php endif; ?>

<?php if (!empty($deed['file_path'])): ?>
<div class="rg-panel">
    <h5><i class="fas fa-file-contract text-primary"></i>Registered Deed</h5>
    <p class="mb-2 text-muted" style="font-size:.82rem;">Status: <strong><?= htmlspecialchars(ucfirst($deed['status'] ?? 'uploaded')) ?></strong><?= !empty($deed['verified_at']) ? ' &bull; Verified ' . htmlspecialchars(date('d M Y', strtotime($deed['verified_at']))) : '' ?></p>
    <a class="btn btn-sm btn-outline-primary" href="<?= BASE_URL ?>/<?= ltrim(htmlspecialchars($deed['file_path']), '/') ?>" target="_blank" rel="noopener"><i class="fas fa-download me-1"></i>View Registry Deed</a>
</div>
<?php endif; ?>

<div class="rg-panel text-center">
    <h5 class="justify-content-center"><i class="fas fa-award text-warning"></i>Possession Certificate</h5>
    <?php if ($can_download && $canonical_id > 0): ?>
        <p class="text-muted" style="font-size:.82rem;">Your plot handover is complete. Download the official certificate below.</p>
        <a href="<?= BASE_URL ?>/customer/possession-certificate/<?= $canonical_id ?>" class="btn btn-success px-4"><i class="fas fa-file-download me-2"></i>Download Possession Certificate</a>
    <?php else: ?>
        <p class="text-muted" style="font-size:.82rem;">The certificate unlocks automatically after Stage 7 (Mutation &amp; Possession Handover) is completed.</p>
        <button class="btn btn-secondary px-4" disabled><i class="fas fa-lock me-2"></i>Download Possession Certificate</button>
    <?php endif; ?>
</div>
