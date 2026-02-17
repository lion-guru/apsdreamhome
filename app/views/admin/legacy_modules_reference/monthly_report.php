<?php
require_once __DIR__ . '/core/init.php';
require_once __DIR__ . '/admin-functions.php';

$db = \App\Core\App::database();
$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

// Monthly Sales
$sales = $db->fetchAll("SELECT MONTH(booking_date) as month, SUM(amount) as total FROM bookings WHERE status='confirmed' AND YEAR(booking_date)=:year GROUP BY MONTH(booking_date)", ['year' => $year]);
$salesData = array_fill(1, 12, 0);
foreach ($sales as $row) {
    $salesData[intval($row['month'])] = floatval($row['total']);
}

// Monthly Expenses
$expenses = $db->fetchAll("SELECT MONTH(expense_date) as month, SUM(amount) as total FROM expenses WHERE YEAR(expense_date)=:year GROUP BY MONTH(expense_date)", ['year' => $year]);
$expenseData = array_fill(1, 12, 0);
foreach ($expenses as $row) {
    $expenseData[intval($row['month'])] = floatval($row['total']);
}

// Monthly Commissions
$commissions = $db->fetchAll("SELECT MONTH(created_at) as month, SUM(commission_amount) as total FROM commission_transactions WHERE status='paid' AND YEAR(created_at)=:year GROUP BY MONTH(created_at)", ['year' => $year]);
$commissionData = array_fill(1, 12, 0);
foreach ($commissions as $row) {
    $commissionData[intval($row['month'])] = floatval($row['total']);
}

$months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Monthly Business Report</title>
    <link rel="stylesheet" href="<?= get_admin_asset_url('bootstrap.min.css', 'css') ?>">
    <script src="<?= get_admin_asset_url('chart.min.js', 'js') ?>"></script>
</head>
<body>
<div class="container py-4">
    <h2 class="mb-4">Monthly Business Report (<?= $year ?>)</h2>
    <form class="mb-3" method="get">
        <label for="year" class="form-label">Select Year:</label>
        <select name="year" id="year" class="form-select w-auto d-inline-block" onchange="this.form.submit()">
            <?php for($y = date('Y')-5; $y <= date('Y'); $y++): ?>
                <option value="<?= $y ?>" <?= $y==$year?'selected':'' ?>><?= $y ?></option>
            <?php endfor; ?>
        </select>
    </form>
    <div class="card mb-4">
        <div class="card-header">Sales, Expenses & Commissions (Monthly)</div>
        <div class="card-body">
            <canvas id="monthlyChart"></canvas>
            <script>
                const months = <?= json_encode($months) ?>;
                const sales = <?= json_encode(array_values($salesData)) ?>;
                const expenses = <?= json_encode(array_values($expenseData)) ?>;
                const commissions = <?= json_encode(array_values($commissionData)) ?>;
                new Chart(document.getElementById('monthlyChart'), {
                    type: 'bar',
                    data: {
                        labels: months,
                        datasets: [
                            {label: 'Sales', data: sales, backgroundColor: '#198754'},
                            {label: 'Expenses', data: expenses, backgroundColor: '#dc3545'},
                            {label: 'Commissions', data: commissions, backgroundColor: '#0d6efd'}
                        ]
                    },
                    options: {
                        responsive: true,
                        plugins: { legend: { position: 'top' } }
                    }
                });
            </script>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-header">Total Sales</div>
                <div class="card-body fs-3">₹<?= number_format(array_sum($salesData),2) ?></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-header">Total Expenses</div>
                <div class="card-body fs-3">₹<?= number_format(array_sum($expenseData),2) ?></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-header">Total Commissions</div>
                <div class="card-body fs-3">₹<?= number_format(array_sum($commissionData),2) ?></div>
            </div>
        </div>
    </div>
</div>
</body>
</html>

