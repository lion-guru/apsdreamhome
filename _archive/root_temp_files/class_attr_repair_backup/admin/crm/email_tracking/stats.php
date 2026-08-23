ï»¿<?php $page_title = $page_title ?? 'Email Tracking'; $overall = $overall ?? []; $daily = $daily ?? []; $top_links = $top_links ?? []; $days = $days ?? 30; ?>
<style>.et-stat{background:#fff;border-radius:14px;border:1px solid #f0f0f5;padding:20px;text-align:center}.et-stat .val{font-size:28px;font-weight:800}.et-stat .label{font-size:11px;color:#888;text-transform:uppercase;letter-spacing:1px}</style>

<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0"><i class="fas fa-envelope-open me-2 text-primary"></i>Email Tracking</h4>
        <div class="d-flex gap-2">
            <?php foreach ([7=>7,14=>14,30=>30,90=>90] as $v=>$l): ?><a href="<?= BASE_URL ?>/admin/crm/email-tracking/stats?days=<?= $v ?>" class="btn btn-sm <?= $days==$v ? 'btn-primary' : 'btn-outline-secondary' ?>"><?= $l ?>d</a><?php endforeach; ?>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-2"><div class="et-stat"><div class="val text-primary"><?= number_format($overall['total_events'] ?? 0) ?></div><div class="label">Total Events</div></div></div>
        <div class="col-md-2"><div class="et-stat"><div class="val text-success"><?= number_format($overall['opens'] ?? 0) ?></div><div class="label">Opens</div></div></div>
        <div class="col-md-2"><div class="et-stat"><div class="val text-info"><?= number_format($overall['clicks'] ?? 0) ?></div><div class="label">Clicks</div></div></div>
        <div class="col-md-2"><div class="et-stat"><div class="val text-warning"><?= number_format($overall['emails_tracked'] ?? 0) ?></div><div class="label">Emails Tracked</div></div></div>
        <div class="col-md-2"><div class="et-stat"><div class="val text-secondary"><?= number_format($overall['unique_recipients'] ?? 0) ?></div><div class="label">Unique Recipients</div></div></div>
        <div class="col-md-2"><div class="et-stat"><div class="val" class="style-23141"><?= ($overall['opens'] ?? 0) > 0 && ($overall['emails_tracked'] ?? 0) > 0 ? round(($overall['opens'] / $overall['emails_tracked']) * 100, 1) : 0 ?>%</div><div class="label">Open Rate</div></div></div>
    </div>

    <div class="row g-3">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm" class="style-56956"><div class="card-header" class="style-62632"><h6 class="mb-0"><i class="fas fa-link me-1"></i>Top Clicked Links</h6></div>
                <div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0"><thead><tr><th>URL</th><th>Clicks</th><th>Unique</th></tr></thead><tbody>
                <?php if (empty($top_links)): ?><tr><td colspan="3" class="text-center py-3 text-muted">No click data yet</td></tr>
                <?php else: foreach ($top_links as $l): ?><tr><td><small><?= htmlspecialchars(substr($l['link_url'], 0, 60)) ?></small></td><td><span class="badge bg-primary"><?= $l['clicks'] ?></span></td><td><?= $l['unique_clicks'] ?></td></tr><?php endforeach; endif; ?>
                </tbody></table></div></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm" class="style-56956"><div class="card-header" class="style-62632"><h6 class="mb-0"><i class="fas fa-chart-line me-1"></i>Daily Activity</h6></div>
                <div class="card-body">
                <?php if (empty($daily)): ?><p class="text-muted text-center">No data</p>
                <?php else: $grouped = []; foreach ($daily as $d) { $grouped[$d['day']][] = $d; } krsort($grouped); foreach (array_slice($grouped, 0, 14, true) as $day => $events): ?>
                    <div class="d-flex justify-content-between align-items-center mb-1"><small class="text-muted"><?= date('d M', strtotime($day)) ?></small><div><?php foreach ($events as $e): ?><span class="badge me-1" class="style-56894"><?= $e['event_type'] === 'open' ? 'ðŸ‘�' : 'ðŸ–±' ?> <?= $e['cnt'] ?></span><?php endforeach; ?></div></div>
                <?php endforeach; endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
