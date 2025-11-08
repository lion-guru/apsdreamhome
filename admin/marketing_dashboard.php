<?php
session_start();
include 'config.php';
require_once 'includes/universal_dashboard_template.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true || !in_array($_SESSION['admin_role'], ['marketing'])) {
    header('Location: login.php');
    exit();
}

$employee = $_SESSION['admin_username'] ?? 'Marketing';

// Marketing statistics
$total_campaigns = $conn->query("SELECT COUNT(*) as c FROM marketing_campaigns")->fetch_assoc()['c'] ?? 0;
$active_campaigns = $conn->query("SELECT COUNT(*) as c FROM marketing_campaigns WHERE status='active'")->fetch_assoc()['c'] ?? 0;
$total_leads = $conn->query("SELECT COUNT(*) as c FROM leads WHERE source LIKE '%marketing%'")->fetch_assoc()['c'] ?? 0;
$conversion_rate = $conn->query("SELECT (COUNT(CASE WHEN status='converted' THEN 1 END) * 100.0 / COUNT(*)) as rate FROM leads WHERE source LIKE '%marketing%'")->fetch_assoc()['rate'] ?? 0;

// Recent campaigns
$recent_campaigns = $conn->query("SELECT name, status, budget, created_at 
                                 FROM marketing_campaigns 
                                 ORDER BY created_at DESC LIMIT 5");

// Recent activities
$recent_activities = [];
while($campaign = $recent_campaigns->fetch_assoc()) {
    $recent_activities[] = [
        'title' => 'Campaign - ' . ucfirst($campaign['status']),
        'description' => $campaign['name'] . ' (Budget: ₹' . number_format($campaign['budget'] ?? 0) . ')',
        'time' => date('M j, Y', strtotime($campaign['created_at'])),
        'icon' => $campaign['status'] == 'active' ? 'fas fa-play-circle text-success' : 
                 ($campaign['status'] == 'completed' ? 'fas fa-check-circle text-primary' : 'fas fa-pause-circle text-warning')
    ];
}

// Statistics for dashboard
$stats = [
    [
        'icon' => 'fas fa-bullseye',
        'value' => $total_campaigns,
        'label' => 'Total Campaigns',
        'change' => '+3 this month',
        'change_type' => 'positive'
    ],
    [
        'icon' => 'fas fa-play',
        'value' => $active_campaigns,
        'label' => 'Active Campaigns',
        'change' => '+2 running now',
        'change_type' => 'positive'
    ],
    [
        'icon' => 'fas fa-users',
        'value' => $total_leads,
        'label' => 'Marketing Leads',
        'change' => '+25 this week',
        'change_type' => 'positive'
    ],
    [
        'icon' => 'fas fa-chart-line',
        'value' => round($conversion_rate, 1) . '%',
        'label' => 'Conversion Rate',
        'change' => '+2.5% improvement',
        'change_type' => 'positive'
    ]
];

// Quick actions for marketing team
$quick_actions = [
    [
        'title' => 'Create Campaign',
        'icon' => 'fas fa-plus',
        'url' => 'campaigns.php?action=create',
        'color' => 'primary'
    ],
    [
        'title' => 'View Analytics',
        'icon' => 'fas fa-chart-line',
        'url' => 'analytics_dashboard.php',
        'color' => 'success'
    ],
    [
        'title' => 'Manage Leads',
        'icon' => 'fas fa-users',
        'url' => 'leads.php',
        'color' => 'info'
    ],
    [
        'title' => 'Social Media',
        'icon' => 'fas fa-share-alt',
        'url' => 'social_media.php',
        'color' => 'warning'
    ]
];

// Custom content for marketing specific widgets
$custom_content = '
<div class="row mt-4">
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-chart-pie me-2"></i>Campaign Performance</h5>
            </div>
            <div class="card-body">
                <canvas id="campaignChart" height="300"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-chart-bar me-2"></i>Lead Sources</h5>
            </div>
            <div class="card-body">
                <canvas id="leadSourceChart" height="300"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-lg-8 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-tasks me-2"></i>Marketing Action Plan</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-muted mb-3">This Week</h6>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-chart-line me-2 text-primary"></i>Review campaign performance</span>
                                <span class="badge bg-primary rounded-pill">' . $active_campaigns . '</span>
                            </li>
                            <li class="list-group-item">
                                <i class="fas fa-bullhorn me-2 text-success"></i>Plan new lead generation strategy
                            </li>
                            <li class="list-group-item">
                                <i class="fas fa-handshake me-2 text-info"></i>Coordinate with sales team
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted mb-3">Next Week</h6>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">
                                <i class="fas fa-share-alt me-2 text-warning"></i>Launch social media campaigns
                            </li>
                            <li class="list-group-item">
                                <i class="fas fa-envelope me-2 text-secondary"></i>Email marketing automation
                            </li>
                            <li class="list-group-item">
                                <i class="fas fa-calendar me-2 text-danger"></i>Event planning & coordination
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-fire me-2"></i>Top Performing</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <h6 class="text-muted">Best Campaign</h6>
                    <p class="mb-1">Social Media Boost</p>
                    <div class="progress">
                        <div class="progress-bar bg-success" style="width: 85%"></div>
                    </div>
                    <small class="text-muted">85% engagement rate</small>
                </div>
                <div class="mb-3">
                    <h6 class="text-muted">Best Channel</h6>
                    <p class="mb-1">Facebook Ads</p>
                    <div class="progress">
                        <div class="progress-bar bg-primary" style="width: 72%"></div>
                    </div>
                    <small class="text-muted">72% conversion rate</small>
                </div>
                <div>
                    <h6 class="text-muted">Best Content</h6>
                    <p class="mb-1">Property Showcase</p>
                    <div class="progress">
                        <div class="progress-bar bg-warning" style="width: 68%"></div>
                    </div>
                    <small class="text-muted">68% click-through rate</small>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.3.0/dist/chart.umd.min.js"></script>
<script>
// Campaign Performance Chart
const campaignData = {
    labels: ["Active", "Completed", "Paused", "Draft"],
    datasets: [{
        data: [' . $active_campaigns . ', ' . ($total_campaigns - $active_campaigns) . ', 2, 1],
        backgroundColor: [
            "var(--marketing-primary)",
            "var(--success)",
            "var(--warning)",
            "var(--secondary)"
        ],
        borderWidth: 2,
        borderColor: "rgba(255,255,255,0.8)"
    }]
};

new Chart(document.getElementById("campaignChart"), {
    type: "doughnut",
    data: campaignData,
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

// Lead Sources Chart
const leadSourceData = {
    labels: ["Social Media", "Google Ads", "Email", "Referrals", "Direct"],
    datasets: [{
        label: "Leads",
        data: [45, 35, 25, 20, 15],
        backgroundColor: "var(--marketing-primary)",
        borderColor: "var(--marketing-primary)",
        borderWidth: 2
    }]
};

new Chart(document.getElementById("leadSourceChart"), {
    type: "bar",
    data: leadSourceData,
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>';

echo generateUniversalDashboard('marketing', $stats, $quick_actions, $recent_activities, $custom_content);
