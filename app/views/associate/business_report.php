<?php
$bySource = $by_source ?? [];
$byReason = $by_reason ?? [];
$leadTotal = (int)($lead_total ?? 0);
$bookingCount = (int)($booking_count ?? 0);
$revenue = (float)($booking_revenue ?? 0);
$conv = $leadTotal > 0 ? round($bookingCount / $leadTotal * 100, 1) : 0;
$reasonLabels = ['price' => 'Price / budget', 'location' => 'Location', 'loan' => 'Loan rejected', 'competitor' => 'Went to competitor', 'no_response' => 'No response', 'other' => 'Other'];
?>
<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="fas fa-chart-line text-primary me-2"></i><?= __('assoc_rep_title', [], 'My Business Report') ?></h4>
            <small class="text-muted"><?= __('assoc_rep_sub', [], 'Where your business comes from — and where it goes') ?></small>
        </div>
        <a href="<?= BASE_URL ?>/associate/leads" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i><?= __('assoc_rep_back', [], 'Back to Leads') ?></a>
        <a href="<?= BASE_URL ?>/associate/reports/stage-aging" class="btn btn-outline-primary btn-sm"><i class="fas fa-hourglass-half me-1"></i><?= __('assoc_rep_aging', [], 'Stage Aging') ?></a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3"><div class="card border-0 shadow-sm"><div class="card-body text-center"><div class="fs-2 fw-bold"><?= number_format($leadTotal) ?></div><div class="small text-muted"><?= __('assoc_rep_leads', [], 'My Leads') ?></div></div></div></div>
        <div class="col-6 col-md-3"><div class="card border-0 shadow-sm"><div class="card-body text-center"><div class="fs-2 fw-bold"><?= number_format($bookingCount) ?></div><div class="small text-muted"><?= __('assoc_rep_bookings', [], 'My Bookings') ?></div></div></div></div>
        <div class="col-6 col-md-3"><div class="card border-0 shadow-sm"><div class="card-body text-center"><div class="fs-2 fw-bold">₹<?= number_format($revenue, 0) ?></div><div class="small text-muted"><?= __('assoc_rep_revenue', [], 'Booking Revenue') ?></div></div></div></div>
        <div class="col-6 col-md-3"><div class="card border-0 shadow-sm"><div class="card-body text-center"><div class="fs-2 fw-bold"><?= $conv ?>%</div><div class="small text-muted"><?= __('assoc_rep_conv', [], 'Lead → Booking') ?></div></div></div></div>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm"><div class="card-header bg-white fw-bold"><i class="fas fa-link me-2"></i><?= __('assoc_rep_bysource', [], 'Leads by Source') ?></div>
                <div class="card-body p-0">
                    <?php if (empty($bySource)): ?><p class="text-muted text-center py-4 mb-0">—</p><?php else: ?>
                    <table class="table table-sm mb-0"><tbody>
                        <?php foreach ($bySource as $r): ?>
                        <tr><td><?= htmlspecialchars($r['source'] ?? '') ?></td><td class="text-end fw-bold"><?= number_format($r['c']) ?></td></tr>
                        <?php endforeach; ?>
                    </tbody></table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm"><div class="card-header bg-white fw-bold"><i class="fas fa-heart-broken me-2"></i><?= __('assoc_rep_byreason', [], 'Lost Reasons') ?></div>
                <div class="card-body p-0">
                    <?php if (empty($byReason)): ?><p class="text-muted text-center py-4 mb-0"><?= __('assoc_rep_nolost', [], 'No lost leads — keep it that way!') ?></p><?php else: ?>
                    <table class="table table-sm mb-0"><tbody>
                        <?php foreach ($byReason as $r): ?>
                        <tr><td><?= htmlspecialchars($reasonLabels[$r['reason']] ?? $r['reason']) ?></td><td class="text-end fw-bold"><?= number_format($r['c']) ?></td></tr>
                        <?php endforeach; ?>
                    </tbody></table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <p class="text-muted small mt-3"><i class="fas fa-info-circle me-1"></i><?= __('assoc_rep_note', [], 'Conversion is aggregate (bookings carry no lead link in schema) — direction, not audit.') ?></p>
</div>
