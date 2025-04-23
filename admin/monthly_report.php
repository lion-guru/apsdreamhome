<?php
session_start();
include 'config.php';

if (!isset($_SESSION['auser'])) {
    header("Location: login.php");
    exit();
}

$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

// Monthly Sales
$sales = $conn->query("SELECT MONTH(booking_date) as month, SUM(amount) as total FROM bookings WHERE status='confirmed' AND YEAR(booking_date)=$year GROUP BY MONTH(booking_date)");
$salesData = array_fill(1, 12, 0);
while ($row = $sales->fetch_assoc()) {
    $salesData[intval($row['month'])] = floatval($row['total']);
}

// Monthly Expenses
$expenses = $conn->query("SELECT MONTH(expense_date) as month, SUM(amount) as total FROM expenses WHERE YEAR(expense_date)=$year GROUP BY MONTH(expense_date)");
$expenseData = array_fill(1, 12, 0);
while ($row = $expenses->fetch_assoc()) {
    $expenseData[intval($row['month'])] = floatval($row['total']);
}

// Monthly Commissions
$commissions = $conn->query("SELECT MONTH(created_at) as month, SUM(commission_amount) as total FROM commission_transactions WHERE status='paid' AND YEAR(created_at)=$year GROUP BY MONTH(created_at)");
$commissionData = array_fill(1, 12, 0);
while ($row = $commissions->fetch_assoc()) {
    $commissionData[intval($row['month'])] = floatval($row['total']);
}

$months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Monthly Business Report</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.3.0/dist/chart.umd.min.js"></script>
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
