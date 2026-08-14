<?php
$page_title = $page_title ?? 'Advanced Analytics';
$totalBookings = $totalBookings ?? 0;
$totalRevenue = $totalRevenue ?? 0;
$totalLeads = $totalLeads ?? 0;
$convertedLeads = $convertedLeads ?? 0;
$totalProperties = $totalProperties ?? 0;
$totalPayments = $totalPayments ?? 0;
$monthlyRevenue = $monthlyRevenue ?? [];
$leadSources = $leadSources ?? [];
$propertyTypes = $propertyTypes ?? [];
$bookingStatus = $bookingStatus ?? [];
$leadStatus = $leadStatus ?? [];
$conversionRate = $conversionRate ?? 0;
$monthLabels = $monthLabels ?? [];
$revenueData = $revenueData ?? [];
$bookingCountData = $bookingCountData ?? [];
$sourceLabels = $sourceLabels ?? [];
$sourceData = $sourceData ?? [];
$propLabels = $propLabels ?? [];
$propData = $propData ?? [];
$bookingLabels = $bookingLabels ?? [];
$bookingData = $bookingData ?? [];
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-chart-line me-2 text-primary"></i>Advanced Analytics</h2>
        <div><a href="<?= BASE_URL ?>/admin/sales" class="btn btn-outline-primary btn-sm"><i class="fas fa-chart-bar me-1"></i>Sales Dashboard</a></div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-2"><div class="aps-cp-card"><div class="aps-cp-card-body text-center"><div class="aps-cp-stat-label">Total Bookings</div><div class="aps-cp-stat-value text-primary"><?= number_format($totalBookings) ?></div></div></div></div>
        <div class="col-md-2"><div class="aps-cp-card"><div class="aps-cp-card-body text-center"><div class="aps-cp-stat-label">Total Revenue</div><div class="aps-cp-stat-value text-success">â‚¹<?= number_format($totalRevenue/100000,1) ?>L</div></div></div></div>
        <div class="col-md-2"><div class="aps-cp-card"><div class="aps-cp-card-body text-center"><div class="aps-cp-stat-label">Total Leads</div><div class="aps-cp-stat-value"><?= number_format($totalLeads) ?></div></div></div></div>
        <div class="col-md-2"><div class="aps-cp-card"><div class="aps-cp-card-body text-center"><div class="aps-cp-stat-label">Conversion Rate</div><div class="aps-cp-stat-value text-<?= $conversionRate > 10 ? 'success' : 'warning' ?>"><?= $conversionRate ?>%</div></div></div></div>
        <div class="col-md-2"><div class="aps-cp-card"><div class="aps-cp-card-body text-center"><div class="aps-cp-stat-label">Properties</div><div class="aps-cp-stat-value"><?= number_format($totalProperties) ?></div></div></div></div>
        <div class="col-md-2"><div class="aps-cp-card"><div class="aps-cp-card-body text-center"><div class="aps-cp-stat-label">Payments</div><div class="aps-cp-stat-value text-info"><?= number_format($totalPayments) ?></div></div></div></div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-8">
            <div class="aps-cp-card">
                <div class="aps-cp-card-header"><i class="fas fa-chart-bar me-2"></i>Revenue Trend (12 Months)</div>
                <div class="aps-cp-card-body"><canvas id="revenueChart" height="100"></canvas></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="aps-cp-card">
                <div class="aps-cp-card-header"><i class="fas fa-chart-pie me-2"></i>Lead Sources</div>
                <div class="aps-cp-card-body"><canvas id="sourceChart" height="200"></canvas></div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="aps-cp-card">
                <div class="aps-cp-card-header"><i class="fas fa-home me-2"></i>Property Type Distribution</div>
                <div class="aps-cp-card-body"><canvas id="propertyChart" height="200"></canvas></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="aps-cp-card">
                <div class="aps-cp-card-header"><i class="fas fa-calendar-check me-2"></i>Booking Status Breakdown</div>
                <div class="aps-cp-card-body"><canvas id="bookingChart" height="200"></canvas></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="aps-cp-card">
                <div class="aps-cp-card-header"><i class="fas fa-funnel-dollar me-2"></i>Lead Pipeline</div>
                <div class="aps-cp-card-body">
                    <?php foreach ($leadStatus as $ls): ?>
                        <?php $pct = $totalLeads > 0 ? round($ls['cnt']/$totalLeads*100) : 0; ?>
                        <div class="mb-2">
                            <div class="d-flex justify-content-between"><small><?= htmlspecialchars($ls['status']) ?></small><small><?= $ls['cnt'] ?> (<?= $pct ?>%)</small></div>
                            <div class="progress" class="style-32124"><div class="progress-bar bg-primary" class="style-21859"></div></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?= BASE_URL ?>/assets/js/vendor/chart.umd.js"></script>
<script>
const colors = ['#0d9488','#06b6d4','#10b981','#f59e0b','#ef4444','#14b8a6','#ec4899','#14b8a6'];
new Chart(document.getElementById('revenueChart'), {
    type: 'bar', data: { labels: <?= json_encode($monthLabels) ?>, datasets: [{ label: 'Revenue (â‚¹)', data: <?= json_encode(array_map('floatval', $revenueData)) ?>, backgroundColor: '#0d9488aa', borderColor: '#0d9488', borderWidth: 1 }, { label: 'Bookings', data: <?= json_encode(array_map('intval', $bookingCountData)) ?>, type: 'line', borderColor: '#10b981', backgroundColor: 'transparent', yAxisID: 'y1', tension: 0.3 }] },
    options: { responsive: true, scales: { y: { beginAtZero: true, title: { display: true, text: 'Revenue (â‚¹)' } }, y1: { position: 'right', beginAtZero: true, title: { display: true, text: 'Bookings' }, grid: { drawOnChartArea: false } } } }
});
new Chart(document.getElementById('sourceChart'), {
    type: 'doughnut', data: { labels: <?= json_encode($sourceLabels) ?>, datasets: [{ data: <?= json_encode(array_map('intval', $sourceData)) ?>, backgroundColor: colors }] },
    options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
});
new Chart(document.getElementById('propertyChart'), {
    type: 'pie', data: { labels: <?= json_encode($propLabels) ?>, datasets: [{ data: <?= json_encode(array_map('intval', $propData)) ?>, backgroundColor: colors }] },
    options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
});
new Chart(document.getElementById('bookingChart'), {
    type: 'bar', data: { labels: <?= json_encode($bookingLabels) ?>, datasets: [{ data: <?= json_encode(array_map('intval', $bookingData)) ?>, backgroundColor: colors }] },
    options: { responsive: true, indexAxis: 'y', plugins: { legend: { display: false } } }
});
</script>
