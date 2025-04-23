<?php
session_start();
include 'config.php';

if (!isset($_SESSION['auser'])) {
    header("Location: login.php");
    exit();
}

// Income (total payments completed)
$income = $conn->query("SELECT SUM(amount) as sum FROM payments WHERE status='completed'")->fetch_assoc()['sum'] ?? 0;

// Expenses
$expenses = $conn->query("SELECT SUM(amount) as sum FROM expenses")->fetch_assoc()['sum'] ?? 0;

// Profit/Loss
$profit = ($income ?? 0) - ($expenses ?? 0);

// Outstanding Payments
$outstanding = $conn->query("SELECT SUM(amount) as sum FROM payments WHERE status='pending'")->fetch_assoc()['sum'] ?? 0;

// Expense breakdown by source
$breakdown = $conn->query("SELECT source, SUM(amount) as total FROM expenses GROUP BY source ORDER BY total DESC");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Finance Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.3.0/dist/chart.umd.min.js"></script>
</head>
<body>
<div class="container py-4">
    <h2 class="mb-4">Finance Dashboard</h2>
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-bg-success mb-3">
                <div class="card-body">
                    <h5 class="card-title">Total Income</h5>
                    <p class="card-text fs-2">₹<?= number_format($income, 2) ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-bg-danger mb-3">
                <div class="card-body">
                    <h5 class="card-title">Total Expenses</h5>
                    <p class="card-text fs-2">₹<?= number_format($expenses, 2) ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-bg-primary mb-3">
                <div class="card-body">
                    <h5 class="card-title">Profit / Loss</h5>
                    <p class="card-text fs-2">₹<?= number_format($profit, 2) ?></p>
                </div>
            </div>
        </div>
    </div>
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header">Outstanding Payments</div>
                <div class="card-body">
                    <p class="fs-3">₹<?= number_format($outstanding, 2) ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header">Expense Breakdown</div>
                <div class="card-body">
                    <canvas id="breakdownChart"></canvas>
                    <script>
                        const breakdownData = {
                            labels: [
                                <?php $labels = []; $data = []; while($b = $breakdown->fetch_assoc()) { $labels[] = $b['source']; $data[] = $b['total']; } echo '"' . implode('","', $labels) . '"'; ?>
                            ],
                            datasets: [{
                                label: 'Expenses',
                                data: [<?= implode(',', $data) ?>],
                                backgroundColor: ['#0d6efd','#198754','#ffc107','#dc3545','#6610f2','#fd7e14','#20c997']
                            }]
                        };
                        new Chart(document.getElementById('breakdownChart'), {
                            type: 'pie', data: breakdownData
                        });
                    </script>
                </div>
            </div>
        </div>
    </div>
    <div class="mb-4">
        <a href="expenses.php?export=csv" class="btn btn-success">Export Expenses CSV</a>
    </div>
</div>
</body>
</html>
