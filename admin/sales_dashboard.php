<?php
session_start();
include 'config.php';
require_once 'includes/universal_dashboard_template.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true || !in_array($_SESSION['admin_role'], ['sales'])) {
    header('Location: login.php');
    exit();
}

$employee = $_SESSION['admin_username'] ?? 'Sales';

// Sales statistics
$total_leads = $conn->query("SELECT COUNT(*) as c FROM leads WHERE status='active'")->fetch_assoc()['c'] ?? 0;
$converted_leads = $conn->query("SELECT COUNT(*) as c FROM leads WHERE status='converted'")->fetch_assoc()['c'] ?? 0;
$total_bookings = $conn->query("SELECT COUNT(*) as c FROM bookings WHERE status='confirmed'")->fetch_assoc()['c'] ?? 0;
$monthly_sales = $conn->query("SELECT SUM(amount) as sum FROM payments WHERE status='completed' AND MONTH(payment_date) = MONTH(CURDATE())")->fetch_assoc()['sum'] ?? 0;

// Recent leads
$recent_leads = $conn->query("SELECT name, phone, status, created_at 
                             FROM leads 
                             ORDER BY created_at DESC LIMIT 5");

// Recent activities
$recent_activities = [];
while($lead = $recent_leads->fetch_assoc()) {
    $recent_activities[] = [
        'title' => 'New Lead - ' . ucfirst($lead['status']),
        'description' => $lead['name'] . ' (' . $lead['phone'] . ')',
        'time' => date('M j, Y', strtotime($lead['created_at'])),
        'icon' => $lead['status'] == 'converted' ? 'fas fa-check-circle text-success' : 
                 ($lead['status'] == 'active' ? 'fas fa-user-plus text-primary' : 'fas fa-clock text-warning')
    ];
}

// Statistics for dashboard
$conversion_rate = $total_leads > 0 ? round(($converted_leads / $total_leads) * 100, 1) : 0;

$stats = [
    [
        'icon' => 'fas fa-users',
        'value' => $total_leads,
        'label' => 'Total Leads',
        'change' => '+15 this week',
        'change_type' => 'positive'
    ],
    [
        'icon' => 'fas fa-handshake',
        'value' => $converted_leads,
        'label' => 'Converted Leads',
        'change' => $conversion_rate . '% conversion rate',
        'change_type' => 'positive'
    ],
    [
        'icon' => 'fas fa-calendar-check',
        'value' => $total_bookings,
        'label' => 'Confirmed Bookings',
        'change' => '+8 this month',
        'change_type' => 'positive'
    ],
    [
        'icon' => 'fas fa-rupee-sign',
        'value' => '₹' . number_format($monthly_sales, 0),
        'label' => 'Monthly Sales',
        'change' => '+22% from last month',
        'change_type' => 'positive'
    ]
];

// Quick actions for sales team
$quick_actions = [
    [
        'title' => 'Add Lead',
        'icon' => 'fas fa-user-plus',
        'url' => 'leads.php?action=add',
        'color' => 'primary'
    ],
    [
        'title' => 'View Bookings',
        'icon' => 'fas fa-calendar-check',
        'url' => 'bookings.php',
        'color' => 'success'
    ],
    [
        'title' => 'Sales Analytics',
        'icon' => 'fas fa-chart-line',
        'url' => 'analytics_dashboard.php',
        'color' => 'info'
    ],
    [
        'title' => 'Generate Report',
        'icon' => 'fas fa-file-alt',
        'url' => 'sales_reports.php',
        'color' => 'warning'
    ]
];

// Custom content for sales specific widgets
$custom_content = '
<div class="row mt-4">
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-chart-pie me-2"></i>Lead Status Distribution</h5>
            </div>
            <div class="card-body">
                <canvas id="leadChart" height="300"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-chart-line me-2"></i>Monthly Sales Trend</h5>
            </div>
            <div class="card-body">
                <canvas id="salesChart" height="300"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-tasks me-2"></i>Today\'s Sales Tasks</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-phone me-2 text-primary"></i>Follow up with new leads</span>
                                <span class="badge bg-primary rounded-pill">' . $total_leads . '</span>
                            </li>
                            <li class="list-group-item">
                                <i class="fas fa-calendar-alt me-2 text-success"></i>Update booking status
                            </li>
                            <li class="list-group-item">
                                <i class="fas fa-chart-bar me-2 text-info"></i>Review sales targets
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">
                                <i class="fas fa-handshake me-2 text-warning"></i>Schedule client meetings
                            </li>
                            <li class="list-group-item">
                                <i class="fas fa-file-invoice me-2 text-secondary"></i>Prepare proposals
                            </li>
                            <li class="list-group-item">
                                <i class="fas fa-envelope me-2 text-danger"></i>Send follow-up emails
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.3.0/dist/chart.umd.min.js"></script>
<script>
// Lead Status Chart
const leadData = {
    labels: ["Active", "Converted", "Cold", "Follow-up"],
    datasets: [{
        data: [' . $total_leads . ', ' . $converted_leads . ', 12, 8],
        backgroundColor: [
            "var(--sales-primary)",
            "var(--success)",
            "var(--secondary)",
            "var(--warning)"
        ],
        borderWidth: 2,
        borderColor: "rgba(255,255,255,0.8)"
    }]
};

new Chart(document.getElementById("leadChart"), {
    type: "doughnut",
    data: leadData,
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

// Sales Trend Chart
const salesData = {
    labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun"],
    datasets: [{
        label: "Sales (₹)",
        data: [280000, 320000, 290000, 410000, 380000, ' . ($monthly_sales ?: 450000) . '],
        borderColor: "var(--sales-primary)",
        backgroundColor: "rgba(37, 99, 235, 0.1)",
        tension: 0.4,
        fill: true
    }]
};

new Chart(document.getElementById("salesChart"), {
    type: "line",
    data: salesData,
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return "₹" + (value/1000) + "K";
                    }
                }
            }
        }
    }
});
</script>';

echo generateUniversalDashboard('sales', $stats, $quick_actions, $recent_activities, $custom_content);
