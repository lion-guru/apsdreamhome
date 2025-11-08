<?php
// MLM Business Analytics & Visualizations
require_once __DIR__ . '/../includes/config/config.php';
require_once __DIR__ . '/../includes/functions/mlm_commission_ledger.php';
require_once __DIR__ . '/../includes/functions/mlm_business.php';
require_once __DIR__ . '/../includes/functions/mlm_commission_bonuses.php';
require_once __DIR__ . '/../includes/functions/permission_util.php';

require_permission('view_commission_analytics');

// Total commission distributed
$res = $con->query("SELECT SUM(commission_amount) as total FROM mlm_commission_ledger");
$row = $res->fetch_assoc();
$total_commission = $row['total'] ?? 0;

// Monthly commission trend (last 12 months)
$monthly = $con->query("SELECT DATE_FORMAT(created_at, '%Y-%m') as ym, SUM(commission_amount) as total FROM mlm_commission_ledger GROUP BY ym ORDER BY ym DESC LIMIT 12");
$monthly_data = [];
while ($row = $monthly->fetch_assoc()) {
    $monthly_data[] = $row;
}
$monthly_data = array_reverse($monthly_data);

// Top earners by rank
$top = $con->query("SELECT a.name, a.id, SUM(l.commission_amount) as earned FROM mlm_commission_ledger l JOIN associates a ON l.associate_id=a.id GROUP BY l.associate_id ORDER BY earned DESC LIMIT 10");
$top_earners = [];
while ($row = $top->fetch_assoc()) {
    $rank = getAssociateRank(getTotalTeamBusiness($con, $row['id']));
    $row['rank'] = $rank;
    $top_earners[] = $row;
}

// Bonus payout analytics (last 12 months)
$bonus_monthly = [];
$bonus_sql = "SELECT a.id, a.name, SUM(ct.amount) as total_business FROM associates a JOIN commission_transactions ct ON a.id=ct.associate_id GROUP BY a.id";
$bonus_res = $con->query($bonus_sql);
$bonus_distribution = [];
while ($row = $bonus_res->fetch_assoc()) {
    $rank = getAssociateRank($row['total_business']);
    $bonus = calculateBonus($row['total_business'], $rank);
    $bonus_distribution[] = [
        'name' => $row['name'],
        'id' => $row['id'],
        'rank' => $rank,
        'total_business' => $row['total_business'],
        'bonus' => $bonus
    ];
}

// Business growth rate (last 6 months)
$growth = $con->query("SELECT DATE_FORMAT(transaction_date, '%Y-%m') as ym, SUM(amount) as total FROM commission_transactions GROUP BY ym ORDER BY ym DESC LIMIT 6");
$growth_data = [];
while ($row = $growth->fetch_assoc()) {
    $growth_data[] = $row;
}
$growth_data = array_reverse($growth_data);

// Rank distribution for pie chart
$rank_counts = [
    'Diamond' => 0, 'Platinum' => 0, 'Gold' => 0, 'Silver' => 0, 'Starter' => 0
];
$rank_sql = "SELECT a.id, SUM(ct.amount) as total_business FROM associates a LEFT JOIN commission_transactions ct ON a.id=ct.associate_id GROUP BY a.id";
$rank_res = $con->query($rank_sql);
while ($row = $rank_res->fetch_assoc()) {
    $rank = getAssociateRank($row['total_business']);
    $rank_counts[$rank]++;
}

// After commission analytics calculation or approval
require_once __DIR__ . '/../includes/functions/notification_util.php';
addNotification($con, 'Commission', 'Commission analytics calculated or approved.', $_SESSION['auser'] ?? null);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MLM Analytics & Commission Insights</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.3.0/dist/chart.umd.min.js"></script>
</head>
<body class="p-4">
<div class="container">
    <h2>MLM Commission Analytics</h2>
    <div class="mb-3"><strong>Total Commission Distributed:</strong> ₹<?php echo number_format($total_commission, 2); ?></div>
    <h5>Monthly Commission Trend (Last 12 Months)</h5>
    <canvas id="monthlyChart" height="80"></canvas>
    <script>
    const monthlyLabels = <?php echo json_encode(array_column($monthly_data,'ym')); ?>;
    const monthlyTotals = <?php echo json_encode(array_map('floatval',array_column($monthly_data,'total'))); ?>;
    new Chart(document.getElementById('monthlyChart').getContext('2d'), {
        type: 'line',
        data: {
            labels: monthlyLabels,
            datasets: [{
                label: 'Commission (₹)',
                data: monthlyTotals,
                borderColor: 'blue',
                backgroundColor: 'rgba(0,0,255,0.1)',
                fill: true,
                tension: 0.3
            }]
        },
        options: {responsive: true}
    });
    </script>
    <h5 class="mt-4">Top 10 Earners By Rank</h5>
    <table class="table table-bordered">
        <thead><tr><th>Name</th><th>Associate ID</th><th>Rank</th><th>Total Earned (₹)</th></tr></thead>
        <tbody>
            <?php foreach ($top_earners as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo $row['rank']; ?></td>
                    <td><?php echo number_format($row['earned'],2); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <h5 class="mt-4">Bonus Distribution by Rank</h5>
    <table class="table table-bordered">
        <thead><tr><th>Name</th><th>Associate ID</th><th>Rank</th><th>Total Business (₹)</th><th>Bonus (₹)</th></tr></thead>
        <tbody>
            <?php foreach ($bonus_distribution as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo $row['rank']; ?></td>
                    <td><?php echo number_format($row['total_business'],2); ?></td>
                    <td><?php echo number_format($row['bonus'],2); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <h5 class="mt-4">Rank Distribution</h5>
    <canvas id="rankPie" height="80"></canvas>
    <script>
    const rankLabels = <?php echo json_encode(array_keys($rank_counts)); ?>;
    const rankData = <?php echo json_encode(array_values($rank_counts)); ?>;
    new Chart(document.getElementById('rankPie').getContext('2d'), {
        type: 'pie',
        data: {
            labels: rankLabels,
            datasets: [{
                label: 'Associates by Rank',
                data: rankData,
                backgroundColor: ['#b9f2ff','#e5e4e2','#ffd700','#c0c0c0','#a9a9a9'],
            }]
        },
        options: {responsive: true}
    });
    </script>
    <h5 class="mt-4">Business Growth Rate (Last 6 Months)</h5>
    <canvas id="growthChart" height="80"></canvas>
    <script>
    const growthLabels = <?php echo json_encode(array_column($growth_data,'ym')); ?>;
    const growthTotals = <?php echo json_encode(array_map('floatval',array_column($growth_data,'total'))); ?>;
    new Chart(document.getElementById('growthChart').getContext('2d'), {
        type: 'bar',
        data: {
            labels: growthLabels,
            datasets: [{
                label: 'Business Volume (₹)',
                data: growthTotals,
                backgroundColor: 'rgba(40,167,69,0.5)',
                borderColor: '#28a745',
                borderWidth: 1
            }]
        },
        options: {responsive: true}
    });
    </script>
</div>
</body>
</html>
