<?php $page_title = $page_title ?? 'Advanced Analytics';
try {
    $db = $this->db ?? null;
    if (!$db) { $config = require dirname(dirname(dirname(dirname(__DIR__)))) . '/config/database.php'; $db = new PDO("mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4", $config['username'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]); }
    $totalBookings = (int)($db->query("SELECT COUNT(*) FROM plot_bookings")->fetchColumn());
    $totalRevenue = (float)($db->query("SELECT COALESCE(SUM(total_plot_value),0) FROM plot_bookings WHERE status NOT IN ('cancelled')")->fetchColumn());
    $totalLeads = (int)($db->query("SELECT COUNT(*) FROM leads WHERE deleted_at IS NULL")->fetchColumn());
    $convertedLeads = (int)($db->query("SELECT COUNT(*) FROM leads WHERE is_converted = 1 AND deleted_at IS NULL")->fetchColumn());
    $totalProperties = (int)($db->query("SELECT COUNT(*) FROM user_properties")->fetchColumn());
    $totalPayments = (int)($db->query("SELECT COUNT(*) FROM payment_transactions WHERE payment_status = 'completed'")->fetchColumn());
    $monthlyRevenue = $db->query("SELECT DATE_FORMAT(booking_date, '%Y-%m') as month, SUM(total_plot_value) as revenue, COUNT(*) as cnt FROM plot_bookings WHERE status NOT IN ('cancelled') AND booking_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH) GROUP BY month ORDER BY month ASC")->fetchAll(PDO::FETCH_ASSOC);
    $leadSources = $db->query("SELECT source, COUNT(*) as cnt FROM leads WHERE deleted_at IS NULL GROUP BY source ORDER BY cnt DESC LIMIT 8")->fetchAll(PDO::FETCH_ASSOC);
    $propertyTypes = $db->query("SELECT property_type, COUNT(*) as cnt FROM user_properties GROUP BY property_type ORDER BY cnt DESC")->fetchAll(PDO::FETCH_ASSOC);
    $bookingStatus = $db->query("SELECT status, COUNT(*) as cnt FROM plot_bookings GROUP BY status ORDER BY cnt DESC")->fetchAll(PDO::FETCH_ASSOC);
    $leadStatus = $db->query("SELECT status, COUNT(*) as cnt FROM leads WHERE deleted_at IS NULL GROUP BY status ORDER BY cnt DESC")->fetchAll(PDO::FETCH_ASSOC);
    $conversionRate = $totalLeads > 0 ? round($convertedLeads / $totalLeads * 100, 1) : 0;
} catch (Exception $e) { $totalBookings = $totalRevenue = $totalLeads = $convertedLeads = $totalProperties = $totalPayments = 0; $monthlyRevenue = $leadSources = $propertyTypes = $bookingStatus = $leadStatus = []; $conversionRate = 0; }
$monthLabels = array_column($monthlyRevenue, 'month'); $revenueData = array_column($monthlyRevenue, 'revenue'); $bookingCountData = array_column($monthlyRevenue, 'cnt');
$sourceLabels = array_column($leadSources, 'source'); $sourceData = array_column($leadSources, 'cnt');
$propLabels = array_column($propertyTypes, 'property_type'); $propData = array_column($propertyTypes, 'cnt');
$bookingLabels = array_column($bookingStatus, 'status'); $bookingData = array_column($bookingStatus, 'cnt');
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-chart-line me-2 text-primary"></i>Advanced Analytics</h2>
        <div><a href="<?= BASE_URL ?>/admin/sales" class="btn btn-outline-primary btn-sm"><i class="fas fa-chart-bar me-1"></i>Sales Dashboard</a></div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-2"><div class="aps-cp-card"><div class="aps-cp-card-body text-center"><div class="aps-cp-stat-label">Total Bookings</div><div class="aps-cp-stat-value text-primary"><?= number_format($totalBookings) ?></div></div></div></div>
        <div class="col-md-2"><div class="aps-cp-card"><div class="aps-cp-card-body text-center"><div class="aps-cp-stat-label">Total Revenue</div><div class="aps-cp-stat-value text-success">₹<?= number_format($totalRevenue/100000,1) ?>L</div></div></div></div>
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
                            <div class="progress" style="height:8px"><div class="progress-bar bg-primary" style="width:<?= $pct ?>%"></div></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
const colors = ['#4f46e5','#06b6d4','#10b981','#f59e0b','#ef4444','#8b5cf6','#ec4899','#14b8a6'];
new Chart(document.getElementById('revenueChart'), {
    type: 'bar', data: { labels: <?= json_encode($monthLabels) ?>, datasets: [{ label: 'Revenue (₹)', data: <?= json_encode(array_map('floatval', $revenueData)) ?>, backgroundColor: '#4f46e5aa', borderColor: '#4f46e5', borderWidth: 1 }, { label: 'Bookings', data: <?= json_encode(array_map('intval', $bookingCountData)) ?>, type: 'line', borderColor: '#10b981', backgroundColor: 'transparent', yAxisID: 'y1', tension: 0.3 }] },
    options: { responsive: true, scales: { y: { beginAtZero: true, title: { display: true, text: 'Revenue (₹)' } }, y1: { position: 'right', beginAtZero: true, title: { display: true, text: 'Bookings' }, grid: { drawOnChartArea: false } } } }
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
