<?php
session_start();
include 'config.php';
require_once 'includes/universal_dashboard_template.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true || !in_array($_SESSION['admin_role'], ['it_head'])) {
    header('Location: login.php');
    exit();
}

$employee = $_SESSION['admin_username'] ?? 'IT Head';

// IT statistics
$total_tickets = $conn->query("SELECT COUNT(*) as c FROM support_tickets")->fetch_assoc()['c'] ?? 0;
$open_tickets = $conn->query("SELECT COUNT(*) as c FROM support_tickets WHERE status='open'")->fetch_assoc()['c'] ?? 0;
$resolved_tickets = $conn->query("SELECT COUNT(*) as c FROM support_tickets WHERE status='resolved'")->fetch_assoc()['c'] ?? 0;
$total_assets = $conn->query("SELECT COUNT(*) as c FROM it_assets WHERE status='active'")->fetch_assoc()['c'] ?? 0;

// Recent tickets
$recent_tickets = $conn->query("SELECT title, priority, status, created_at, assigned_to 
                              FROM support_tickets 
                              ORDER BY created_at DESC LIMIT 5");

// Recent activities
$recent_activities = [];
while($ticket = $recent_tickets->fetch_assoc()) {
    $recent_activities[] = [
        'title' => 'Support Ticket - ' . ucfirst($ticket['status']),
        'description' => $ticket['title'] . ' (Priority: ' . ucfirst($ticket['priority']) . ')',
        'time' => date('M j, Y', strtotime($ticket['created_at'])),
        'icon' => $ticket['status'] == 'resolved' ? 'fas fa-check-circle text-success' : 
                 ($ticket['status'] == 'in_progress' ? 'fas fa-cog text-warning' : 'fas fa-exclamation-circle text-danger')
    ];
}

// Statistics for dashboard
$resolution_rate = $total_tickets > 0 ? round(($resolved_tickets / $total_tickets) * 100, 1) : 0;

$stats = [
    [
        'icon' => 'fas fa-ticket-alt',
        'value' => $total_tickets,
        'label' => 'Total Tickets',
        'change' => '+12 this week',
        'change_type' => 'neutral'
    ],
    [
        'icon' => 'fas fa-clock',
        'value' => $open_tickets,
        'label' => 'Open Tickets',
        'change' => '-5 from yesterday',
        'change_type' => 'positive'
    ],
    [
        'icon' => 'fas fa-check-circle',
        'value' => $resolved_tickets,
        'label' => 'Resolved Tickets',
        'change' => $resolution_rate . '% resolution rate',
        'change_type' => 'positive'
    ],
    [
        'icon' => 'fas fa-server',
        'value' => $total_assets,
        'label' => 'IT Assets',
        'change' => '98% uptime',
        'change_type' => 'positive'
    ]
];

// Quick actions for IT team
$quick_actions = [
    [
        'title' => 'Create Ticket',
        'icon' => 'fas fa-plus',
        'url' => 'support_dashboard.php?action=create',
        'color' => 'primary'
    ],
    [
        'title' => 'System Monitor',
        'icon' => 'fas fa-desktop',
        'url' => 'system_monitor.php',
        'color' => 'success'
    ],
    [
        'title' => 'AI Tools',
        'icon' => 'fas fa-robot',
        'url' => 'ai_dashboard.php',
        'color' => 'info'
    ],
    [
        'title' => 'Security Audit',
        'icon' => 'fas fa-shield-alt',
        'url' => 'compliance_dashboard.php',
        'color' => 'warning'
    ]
];

// Custom content for IT specific widgets
$custom_content = '
<div class="row mt-4">
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-chart-pie me-2"></i>Ticket Priority Distribution</h5>
            </div>
            <div class="card-body">
                <canvas id="priorityChart" height="300"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-chart-line me-2"></i>System Performance</h5>
            </div>
            <div class="card-body">
                <canvas id="performanceChart" height="300"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-server me-2"></i>Server Status</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span>Web Server</span>
                    <span class="badge bg-success">Online</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span>Database Server</span>
                    <span class="badge bg-success">Online</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span>Mail Server</span>
                    <span class="badge bg-warning">Maintenance</span>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span>Backup Server</span>
                    <span class="badge bg-success">Online</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-tasks me-2"></i>Today\'s IT Tasks</h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-bug me-2 text-danger"></i>Fix critical bugs</span>
                        <span class="badge bg-danger rounded-pill">' . $open_tickets . '</span>
                    </li>
                    <li class="list-group-item">
                        <i class="fas fa-shield-alt me-2 text-primary"></i>Review system security
                    </li>
                    <li class="list-group-item">
                        <i class="fas fa-hdd me-2 text-success"></i>Check infrastructure uptime
                    </li>
                    <li class="list-group-item">
                        <i class="fas fa-database me-2 text-info"></i>Database optimization
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.3.0/dist/chart.umd.min.js"></script>
<script>
// Priority Distribution Chart
const priorityData = {
    labels: ["High", "Medium", "Low", "Critical"],
    datasets: [{
        data: [' . ($open_tickets * 0.3) . ', ' . ($open_tickets * 0.5) . ', ' . ($open_tickets * 0.15) . ', ' . ($open_tickets * 0.05) . '],
        backgroundColor: [
            "var(--danger)",
            "var(--warning)",
            "var(--success)",
            "var(--it-primary)"
        ],
        borderWidth: 2,
        borderColor: "rgba(255,255,255,0.8)"
    }]
};

new Chart(document.getElementById("priorityChart"), {
    type: "doughnut",
    data: priorityData,
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

// Performance Chart
const performanceData = {
    labels: ["00:00", "04:00", "08:00", "12:00", "16:00", "20:00"],
    datasets: [{
        label: "CPU Usage (%)",
        data: [25, 30, 45, 60, 55, 40],
        borderColor: "var(--it-primary)",
        backgroundColor: "rgba(99, 102, 241, 0.1)",
        tension: 0.4
    }, {
        label: "Memory Usage (%)", 
        data: [40, 42, 48, 65, 58, 45],
        borderColor: "var(--warning)",
        backgroundColor: "rgba(255, 193, 7, 0.1)",
        tension: 0.4
    }]
};

new Chart(document.getElementById("performanceChart"), {
    type: "line",
    data: performanceData,
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                max: 100,
                ticks: {
                    callback: function(value) {
                        return value + "%";
                    }
                }
            }
        }
    }
});
</script>';

echo generateUniversalDashboard('it', $stats, $quick_actions, $recent_activities, $custom_content);
