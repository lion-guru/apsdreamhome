<?php
$base = defined('BASE_URL') ? rtrim(BASE_URL, '/') : '';
$top        = $top_leads ?? [];
$visits     = $visits_today ?? [];
$lostBook   = $closed_not_booked ?? [];
$cold       = $cold_revivable ?? [];
$neg        = $in_negotiation ?? [];
$bk         = $booking_breakdown ?? [];
$stats      = $stats ?? [];
$forecast   = $demand_forecast ?? [];
$churnStats = $churn_stats ?? [];
$highRisk   = $high_risk_leads ?? [];
$byLevel    = (is_array($churnStats) && isset($churnStats['by_level']) && is_array($churnStats['by_level'])) ? $churnStats['by_level'] : [];
?>

<div class="container-fluid px-4 py-3">
    <?php $flashSuccess = $this->getFlash('success'); ?>
    <?php $flashError = $this->getFlash('error'); ?>
    <?php if ($flashSuccess): ?>
    <div class="alert alert-success alert-dismissible fade show mb-3" role="alert"><i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($flashSuccess) ?><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>
    <?php endif; ?>
    <?php if ($flashError): ?>
    <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert"><i class="fas fa-exclamation-triangle me-2"></i><?= htmlspecialchars($flashError) ?><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>
    <?php endif; ?>

    <!-- Header Strip -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="mb-1"><i class="fas fa-sun me-2 text-warning"></i>AI Morning Brief</h2>
            <div class="text-muted small"><?= date('l, d M Y') ?> · Daily 3-minute sales brief for the Sales Director</div>
        </div>
        <div>
            <a class="btn btn-outline-warning btn-sm" href="<?= $base ?>/admin/ai/hub"><i class="fas fa-arrow-left me-1"></i>AI Hub</a>
        </div>
    </div>

    <!-- KPI Strip -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-2"><div class="card text-center py-3 border-0 shadow-sm h-100"><div class="h3 mb-0 text-primary"><?= (int)($stats['top_leads'] ?? 0) ?></div><div class="small text-muted">Top Leads</div></div></div>
        <div class="col-6 col-md-2"><div class="card text-center py-3 border-0 shadow-sm h-100"><div class="h3 mb-0 text-success"><?= (int)($stats['visits_today'] ?? 0) ?></div><div class="small text-muted">Site Visits Today</div></div></div>
        <div class="col-6 col-md-2"><div class="card text-center py-3 border-0 shadow-sm h-100"><div class="h3 mb-0 text-danger"><?= (int)($stats['lost_to_booking'] ?? 0) ?></div><div class="small text-muted">Closed Won — Need to Book</div></div></div>
        <div class="col-6 col-md-2"><div class="card text-center py-3 border-0 shadow-sm h-100"><div class="h3 mb-0 text-warning"><?= (int)($stats['in_negotiation'] ?? 0) ?></div><div class="small text-muted">In Negotiation</div></div></div>
        <div class="col-6 col-md-2"><div class="card text-center py-3 border-0 shadow-sm h-100"><div class="h3 mb-0 text-info"><?= (int)($stats['revivable'] ?? 0) ?></div><div class="small text-muted">Revivable Cold Leads</div></div></div>
        <div class="col-6 col-md-2"><div class="card text-center py-3 border-0 shadow-sm h-100"><div class="h3 mb-0 text-secondary"><?= count($bk) ?></div><div class="small text-muted">Booking Stages</div></div></div>
    </div>

    <!-- AI Insight Panel (advisory — never authoritative) -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header fw-bold bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span><i class="fas fa-robot text-info me-2"></i>AI Insight Panel <span class="badge bg-info text-dark ms-1">advisory</span></span>
            <form method="POST" action="<?= $base ?>/admin/ai-morning-brief/run-churn-analysis" class="d-flex align-items-center gap-2 m-0">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                <button type="submit" class="btn btn-sm btn-outline-info"><i class="fas fa-sync-alt me-1"></i>Refresh Churn Analysis</button>
            </form>
        </div>
        <div class="card-body">
            <div class="row g-4">
                <!-- Churn Risk -->
                <div class="col-lg-5">
                    <h6 class="fw-bold text-uppercase small text-muted mb-2"><i class="fas fa-fire-alt text-danger me-1"></i>Lead Churn Risk</h6>
                    <div class="row g-2 mb-2">
                        <div class="col-4"><div class="bg-light rounded text-center py-2"><div class="h5 mb-0"><?= (int)($churnStats['total_predictions'] ?? 0) ?></div><div class="small text-muted">Scored</div></div></div>
                        <div class="col-4"><div class="bg-light rounded text-center py-2"><div class="h5 mb-0 text-danger"><?= (int)($churnStats['high_critical'] ?? 0) ?></div><div class="small text-muted">High / Critical</div></div></div>
                        <div class="col-4"><div class="bg-light rounded text-center py-2"><div class="h5 mb-0"><?= number_format((float)($churnStats['avg_risk_score'] ?? 0), 1) ?></div><div class="small text-muted">Avg Risk</div></div></div>
                    </div>
                    <div class="row g-2 mb-2 small">
                        <div class="col-3"><span class="badge bg-secondary w-100">Low <?= (int)($byLevel['low'] ?? 0) ?></span></div>
                        <div class="col-3"><span class="badge bg-warning text-dark w-100">Med <?= (int)($byLevel['medium'] ?? 0) ?></span></div>
                        <div class="col-3"><span class="badge bg-danger w-100">High <?= (int)($byLevel['high'] ?? 0) ?></span></div>
                        <div class="col-3"><span class="badge bg-dark w-100">Crit <?= (int)($byLevel['critical'] ?? 0) ?></span></div>
                    </div>
                    <?php if (!empty($highRisk)): ?>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="table-light"><tr><th>Lead</th><th>Risk</th><th>Score</th><th>Next action</th><th></th></tr></thead>
                            <tbody>
                                <?php foreach ($highRisk as $l): ?>
                                <tr>
                                    <td>
                                        <div class="fw-semibold"><?= htmlspecialchars($l['name'] ?? '-') ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($l['phone'] ?? '') ?></small>
                                    </td>
                                    <td><span class="badge bg-<?= ($l['risk_level'] ?? '') === 'critical' ? 'dark' : 'danger' ?>"><?= htmlspecialchars($l['risk_level'] ?? '') ?></span></td>
                                    <td><?= (int)($l['risk_score'] ?? 0) ?></td>
                                    <td class="small"><?= htmlspecialchars($l['next_action'] ?? '') ?></td>
                                    <td><a class="btn btn-sm btn-outline-primary" href="<?= $base ?>/admin/leads/show/<?= (int)($l['lead_id'] ?? 0) ?>">Open</a></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="text-muted small py-3 text-center border rounded">No churn predictions yet. Click <strong>Refresh Churn Analysis</strong> to score open leads (cached, bounded to 300).</div>
                    <?php endif; ?>
                </div>

                <!-- Demand Forecast -->
                <div class="col-lg-7">
                    <h6 class="fw-bold text-uppercase small text-muted mb-2"><i class="fas fa-chart-line text-primary me-1"></i>Colony Demand Forecast <span class="text-normal fw-normal">(30 / 90d)</span></h6>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="table-light"><tr><th>Colony</th><th>Avail</th><th>Alloc&nbsp;%</th><th>30d Leads</th><th>90d Bookings</th><th>Runway</th><th>Conf</th></tr></thead>
                            <tbody>
                                <?php foreach ($forecast as $f): ?>
                                <tr>
                                    <td class="fw-semibold"><?= htmlspecialchars($f['colony_name'] ?? '') ?>
                                        <?php if (!empty($f['limited_data'])): ?><span class="badge bg-secondary" title="Thin lead history - treat with caution">limited data</span><?php endif; ?>
                                    </td>
                                    <td><?= (int)($f['available'] ?? 0) ?> / <?= (int)($f['total'] ?? 0) ?></td>
                                    <td><?= isset($f['allocation']) ? number_format($f['allocation'] * 100, 1) . '%' : '-' ?></td>
                                    <td><?= (int)($f['next_30d_leads'] ?? 0) ?></td>
                                    <td><?= (int)($f['next_90d_bookings'] ?? 0) ?></td>
                                    <td><?= $f['runway_days'] !== null ? (int)$f['runway_days'] . 'd' : 'n/a' ?></td>
                                    <td><?= number_format((float)($f['confidence'] ?? 0), 2) ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($forecast)): ?><tr><td colspan="7" class="text-center text-muted py-3">Forecast unavailable</td></tr><?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="small text-muted mt-2"><i class="fas fa-info-circle me-1"></i>Allocation = engaged-plot share of tenant demand (an assumption, not a measured per-colony signal - no colony-tagged leads exist in the database today). Bookings assume 8% lead-to-booking conversion. RERA status is colony-wise; verify at up-rera.in or our legal desk (+91&nbsp;70074&nbsp;44842) before quoting.</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- RIGHT: Top 5 leads to call -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header fw-bold bg-white d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-phone-volume text-primary me-2"></i>Top 5 Leads to Call Today</span>
                    <span class="badge bg-primary">call first</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light"><tr><th>Lead</th><th>Phone</th><th>Score</th><th>Source</th><th></th></tr></thead>
                        <tbody>
                            <?php foreach ($top as $l): ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold"><?= htmlspecialchars($l['name'] ?? '—') ?></div>
                                    <?php if (!empty($l['last_contact'])): ?><div class="small text-muted">last: <?= htmlspecialchars($l['last_contact']) ?></div><?php endif; ?>
                                </td>
                                <td><a class="text-decoration-none" href="tel:+91<?= htmlspecialchars($l['phone'] ?? '') ?>"><i class="fas fa-phone-alt text-success"></i> <?= htmlspecialchars($l['phone'] ?? '') ?></a></td>
                                <td><span class="badge bg-<?= ($l['lead_score'] ?? 0) >= 70 ? 'success' : (($l['lead_score'] ?? 0) >= 40 ? 'warning' : 'secondary') ?>"><?= (int)$l['lead_score'] ?></span></td>
                                <td><span class="badge bg-light text-dark"><?= htmlspecialchars($l['source'] ?? '') ?></span></td>
                                <td><a class="btn btn-sm btn-outline-primary" href="<?= $base ?>/admin/leads/show/<?= (int)$l['id'] ?>">Open</a></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($top)): ?><tr><td colspan="5" class="text-center text-muted py-4">No leads match the criteria — back to the morning!</td></tr><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- RIGHT: Site visits today -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header fw-bold bg-white d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-car-side text-success me-2"></i>Today's Site Visits</span>
                    <span class="badge bg-success"><?= count($visits) ?> today</span>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <?php foreach ($visits as $v): ?>
                        <li class="list-group-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="fw-semibold"><i class="fas fa-map-marker-alt text-primary me-1"></i><?= htmlspecialchars($v['colony_name'] ?? '?') ?></div>
                                    <div class="small text-muted"><?= htmlspecialchars($v['visitor_name'] ?? '') ?> · <?= htmlspecialchars($v['visitor_phone'] ?? '') ?></div>
                                    <div class="small text-muted">Owner: <?= htmlspecialchars($v['owner_name'] ?? 'Unassigned') ?> · <?= htmlspecialchars($v['visit_time'] ?? 'TBD') ?></div>
                                </div>
                                <span class="badge bg-info"><?= htmlspecialchars($v['status'] ?? 'scheduled') ?></span>
                            </div>
                        </li>
                        <?php endforeach; ?>
                        <?php if (empty($visits)): ?><li class="list-group-item text-center text-muted py-4">No site visits scheduled today</li><?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mt-1">
        <!-- Closed won but not yet booked -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header fw-bold bg-white d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-exclamation-circle text-danger me-2"></i>Closed Won — But Not Yet Booked</span>
                    <span class="badge bg-danger"><?= count($lostBook) ?> stuck leads</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light"><tr><th>Lead</th><th>Phone</th><th>Owner</th><th></th></tr></thead>
                        <tbody>
                            <?php foreach ($lostBook as $l): ?>
                            <tr>
                                <td><?= htmlspecialchars($l['name'] ?? '—') ?><br><small class="text-muted">Score <?= (int)$l['lead_score'] ?></small></td>
                                <td><a href="tel:+91<?= htmlspecialchars($l['phone'] ?? '') ?>"><?= htmlspecialchars($l['phone'] ?? '') ?></a></td>
                                <td class="small"><?= htmlspecialchars($l['owner_name'] ?? 'Unassigned') ?></td>
                                <td><a class="btn btn-sm btn-danger" href="<?= $base ?>/admin/bookings/create?lead_id=<?= (int)$l['id'] ?>"><i class="fas fa-arrow-right"></i> Book</a></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($lostBook)): ?><tr><td colspan="4" class="text-center text-muted py-4">No closed-won leads waiting for booking — pipeline is clean 🎉</td></tr><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- In negotiation + booking status breakdown -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header fw-bold bg-white"><i class="fas fa-handshake text-warning me-2"></i>In Negotiation (<?= count($neg) ?>)</div>
                <ul class="list-group list-group-flush">
                    <?php foreach ($neg as $l): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span><span class="fw-semibold"><?= htmlspecialchars($l['name'] ?? '') ?></span><br><small class="text-muted"><?= htmlspecialchars($l['source'] ?? '') ?> · score <?= (int)$l['lead_score'] ?></small></span>
                        <a class="btn btn-sm btn-outline-warning" href="<?= $base ?>/admin/leads/show/<?= (int)$l['id'] ?>">Open</a>
                    </li>
                    <?php endforeach; ?>
                    <?php if (empty($neg)): ?><li class="list-group-item text-center text-muted py-4">Nobody in negotiation — pipeline ageing clean</li><?php endif; ?>
                </ul>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header fw-bold bg-white"><i class="fas fa-file-invoice-dollar text-primary me-2"></i>Booking Breakdown</div>
                <ul class="list-group list-group-flush">
                    <?php foreach ($bk as $row): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span class="text-capitalize"><?= htmlspecialchars($row['status'] ?? '') ?></span>
                        <span><span class="badge bg-primary"><?= (int)$row['cnt'] ?></span> <small class="text-muted">₹ <?= number_format((float)$row['total']) ?></small></span>
                    </li>
                    <?php endforeach; ?>
                    <?php if (empty($bk)): ?><li class="list-group-item text-center text-muted py-4">No bookings created yet</li><?php endif; ?>
                </ul>
            </div>
        </div>
    </div>

    <div class="row g-4 mt-1">
        <!-- Revivable leads -->
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header fw-bold bg-white d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-undo-alt text-info me-2"></i>Revivable Leads (score &lt; 30, silent 7+ days)</span>
                    <span class="badge bg-info"><?= count($cold) ?> to try</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light"><tr><th>Lead</th><th>Phone</th><th>Source</th><th>Score</th><th>Age</th><th></th></tr></thead>
                        <tbody>
                            <?php foreach ($cold as $l): ?>
                            <tr>
                                <td><?= htmlspecialchars($l['name'] ?? '—') ?></td>
                                <td><a href="tel:+91<?= htmlspecialchars($l['phone'] ?? '') ?>"><?= htmlspecialchars($l['phone'] ?? '') ?></a></td>
                                <td><?= htmlspecialchars($l['source'] ?? '') ?></td>
                                <td><span class="badge bg-secondary"><?= (int)$l['lead_score'] ?></span></td>
                                <td class="small"><?= htmlspecialchars($l['created_at'] ? date('d M', strtotime($l['created_at'])) : '') ?></td>
                                <td><a class="btn btn-sm btn-outline-info" href="<?= $base ?>/admin/leads/show/<?= (int)$l['id'] ?>">Reopen</a></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($cold)): ?><tr><td colspan="6" class="text-center text-muted py-4">No cold leads ripe for reviving right now</td></tr><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-3 small text-muted text-center">
        Page generated by AI Morning Brief — refreshed every load. AI recommendations are advisory, not authoritative — follow up with each lead personally.
    </div>
</div>
