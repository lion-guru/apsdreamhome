<?php
session_start();
include 'config.php';
require_once 'includes/universal_dashboard_template.php';

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

// Recent transactions
$recent_transactions = $conn->query("SELECT p.amount, p.status, p.payment_date, b.property_title 
                                   FROM payments p 
                                   LEFT JOIN bookings b ON p.booking_id = b.id 
                                   ORDER BY p.payment_date DESC LIMIT 5");

// Expense breakdown by source
$breakdown = $conn->query("SELECT source, SUM(amount) as total FROM expenses GROUP BY source ORDER BY total DESC");

// Statistics for dashboard
$stats = [
    [
        'icon' => 'fas fa-coins',
        'value' => '₹' . number_format($income, 2),
        'label' => 'Total Income',
        'change' => '+12% this month',
        'change_type' => 'positive'
    ],
    [
        'icon' => 'fas fa-credit-card',
        'value' => '₹' . number_format($expenses, 2),
        'label' => 'Total Expenses',
        'change' => '+5% this month',
        'change_type' => 'negative'
    ],
    [
        'icon' => 'fas fa-chart-line',
        'value' => '₹' . number_format($profit, 2),
        'label' => 'Net Profit',
        'change' => ($profit >= 0 ? '+' : '') . '7% this month',
        'change_type' => $profit >= 0 ? 'positive' : 'negative'
    ],
    [
        'icon' => 'fas fa-hourglass-half',
        'value' => '₹' . number_format($outstanding, 2),
        'label' => 'Outstanding Payments',
        'change' => '-3% this month',
        'change_type' => 'positive'
    ]
];

// Quick actions for finance team
$quick_actions = [
    [
        'title' => 'Add Expense',
        'icon' => 'fas fa-plus',
        'url' => 'expenses.php?action=add',
        'color' => 'primary'
    ],
    [
        'title' => 'Payment Reports',
        'icon' => 'fas fa-file-invoice',
        'url' => 'payment_reports.php',
        'color' => 'success'
    ],
    [
        'title' => 'Export Data',
        'icon' => 'fas fa-download',
        'url' => 'expenses.php?export=csv',
        'color' => 'info'
    ],
    [
        'title' => 'Budget Planning',
        'icon' => 'fas fa-calculator',
        'url' => 'budget.php',
        'color' => 'warning'
    ]
];

// Recent activities
$recent_activities = [];
while($transaction = $recent_transactions->fetch_assoc()) {
    $recent_activities[] = [
        'title' => 'Payment ' . ucfirst($transaction['status']),
        'description' => '₹' . number_format($transaction['amount'], 2) . ' for ' . ($transaction['property_title'] ?? 'Property'),
        'time' => date('M j, Y', strtotime($transaction['payment_date'])),
        'icon' => $transaction['status'] == 'completed' ? 'fas fa-check-circle text-success' : 'fas fa-clock text-warning'
    ];
}

// Custom content for charts
$custom_content = '
<div class="row mt-4">
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-chart-pie me-2"></i>Expense Breakdown</h5>
            </div>
            <div class="card-body">
                <canvas id="breakdownChart" height="300"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-chart-bar me-2"></i>Monthly Trend</h5>
            </div>
            <div class="card-body">
                <canvas id="trendChart" height="300"></canvas>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.3.0/dist/chart.umd.min.js"></script>
<script>
// Expense Breakdown Chart
const breakdownData = {
    labels: [';

$labels = []; $data = [];
while($b = $breakdown->fetch_assoc()) {
    $labels[] = $b['source'];
    $data[] = $b['total'];
}

$custom_content .= '"' . implode('","', $labels) . '"';
$custom_content .= '],
    datasets: [{
        label: "Expenses",
        data: [' . implode(',', $data) . '],
        backgroundColor: [
            "var(--finance-primary)",
            "var(--finance-secondary)", 
            "var(--success)",
            "var(--warning)",
            "var(--info)",
            "var(--danger)"
        ],
        borderWidth: 2,
        borderColor: "rgba(255,255,255,0.8)"
    }]
};

new Chart(document.getElementById("breakdownChart"), {
    type: "doughnut",
    data: breakdownData,
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: "bottom"
            }
        }
    }
});

// Monthly Trend Chart
const trendData = {
    labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun"],
    datasets: [{
        label: "Income",
        data: [45000, 52000, 48000, 61000, 55000, 67000],
        borderColor: "var(--success)",
        backgroundColor: "rgba(40, 167, 69, 0.1)",
        tension: 0.4
    }, {
        label: "Expenses", 
        data: [32000, 38000, 35000, 42000, 39000, 45000],
        borderColor: "var(--danger)",
        backgroundColor: "rgba(220, 53, 69, 0.1)",
        tension: 0.4
    }]
};

new Chart(document.getElementById("trendChart"), {
    type: "line",
    data: trendData,
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return "₹" + value.toLocaleString();
                    }
                }
            }
        }
    }
});
</script>';

echo generateUniversalDashboard('finance', $stats, $quick_actions, $recent_activities, $custom_content);
