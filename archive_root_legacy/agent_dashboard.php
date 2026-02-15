<?php
// Modern Agent Dashboard - AI Powered, Responsive, Feature-Rich (2025 best practices)
session_start();
require_once(__DIR__ . '/includes/config/config.php');
require_once(__DIR__ . '/includes/functions/common-functions.php');

// Check agent session
if (!isset($_SESSION['agent_id'])) {
    header('Location: login.php');
    exit();
}
$agent_id = $_SESSION['agent_id'];
// Fetch agent profile and stats (dummy placeholders, replace with real queries)
$agent = [
    'name' => 'Agent Name',
    'email' => 'agent@email.com',
    'phone' => '9876543210',
    'profile_pic' => get_asset_url('avatar/agent.png', 'images'),
];
$stats = [
    'total_sales' => 1200000,
    'total_customers' => 58,
    'commission_earned' => 90000,
    'pending_leads' => 12,
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agent Dashboard | APS Dream Homes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .dashboard-card { box-shadow: 0 2px 8px rgba(0,0,0,0.07); border-radius: 1rem; background: #fff; padding: 2rem; margin-bottom: 2rem; }
        .dashboard-stats { display: flex; gap: 2rem; flex-wrap: wrap; margin-bottom: 2rem; }
        .stat-box { background: #f8f9fa; border-radius: .5rem; padding: 1.5rem 2rem; min-width: 180px; text-align: center; }
        .ai-chatbot { background: #eaf4ff; border-left: 5px solid #007bff; padding: 1.5rem; border-radius: .5rem; margin-bottom: 2rem; }
        @media (max-width: 767px) { .dashboard-stats { flex-direction: column; gap: 1rem; } }
    </style>
</head>
<body>
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12 d-flex align-items-center">
            <img src="<?php echo $agent['profile_pic']; ?>" alt="Profile" class="rounded-circle me-3" style="width:60px;height:60px;object-fit:cover;">
            <div>
                <h2 class="mb-0">Welcome, <?php echo htmlspecialchars($agent['name']); ?></h2>
                <div class="text-muted small"><?php echo htmlspecialchars($agent['email']); ?> | <?php echo htmlspecialchars($agent['phone']); ?></div>
            </div>
        </div>
    </div>
    <!-- Stats Cards -->
    <div class="dashboard-stats">
        <div class="stat-box">
            <div class="fs-3 fw-bold text-primary"><?php echo format_currency($stats['total_sales']); ?></div>
            <div>Total Sales</div>
        </div>
        <div class="stat-box">
            <div class="fs-3 fw-bold text-success"><?php echo $stats['total_customers']; ?></div>
            <div>Customers</div>
        </div>
        <div class="stat-box">
            <div class="fs-3 fw-bold text-warning"><?php echo format_currency($stats['commission_earned']); ?></div>
            <div>Commission Earned</div>
        </div>
        <div class="stat-box">
            <div class="fs-3 fw-bold text-danger"><?php echo $stats['pending_leads']; ?></div>
            <div>Pending Leads</div>
        </div>
    </div>
    <!-- AI Chatbot Panel -->
    <div class="ai-chatbot mb-4">
        <strong><i class="fa-solid fa-robot"></i> Ask AI (Chatbot):</strong>
        <form id="aiChatForm" class="d-flex mt-2" onsubmit="return false;">
            <input type="text" class="form-control me-2" id="aiChatInput" placeholder="Ask anything about your sales, leads, or customers...">
            <button class="btn btn-primary" onclick="sendAIQuery()"><i class="fa-solid fa-paper-plane"></i></button>
        </form>
        <div id="aiChatResponse" class="mt-2 text-secondary small">Try: "Show my top 5 customers this month"</div>
    </div>
    <!-- Sales Chart -->
    <div class="dashboard-card">
        <h4><i class="fa-solid fa-chart-line"></i> Sales Performance</h4>
        <canvas id="salesChart"></canvas>
    </div>
    <!-- Customers Table -->
    <div class="dashboard-card">
        <h4><i class="fa-solid fa-users"></i> Recent Customers</h4>
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="customersTable">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Project</th>
                        <th>Status</th>
                        <th>Joined</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Example Row, replace with PHP loop -->
                    <tr>
                        <td>John Doe</td>
                        <td>john@example.com</td>
                        <td>9876543211</td>
                        <td>Green Valley</td>
                        <td><span class="badge bg-success">Active</span></td>
                        <td>2025-04-01</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <!-- Export & Share -->
    <div class="dashboard-card">
        <h4><i class="fa-solid fa-share-nodes"></i> Export & Share</h4>
        <button class="btn btn-outline-secondary me-2"><i class="fa-solid fa-file-csv"></i> Export CSV</button>
        <button class="btn btn-outline-secondary me-2"><i class="fa-solid fa-file-pdf"></i> Export PDF</button>
        <button class="btn btn-outline-secondary"><i class="fa-solid fa-qrcode"></i> Share via QR</button>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.3.0/dist/chart.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
// Dummy Sales Data (replace with PHP-generated data)
const salesLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'];
const salesData = [120000, 90000, 110000, 150000, 130000, 140000];
const ctx = document.getElementById('salesChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: salesLabels,
        datasets: [{
            label: 'Sales',
            data: salesData,
            borderColor: 'rgba(54, 162, 235, 1)',
            backgroundColor: 'rgba(54, 162, 235, 0.2)',
            fill: true,
            tension: 0.3
        }]
    },
    options: { responsive: true, plugins: { legend: { display: false } } }
});
// AI Chatbot JS (simulate response)
function sendAIQuery() {
    const input = document.getElementById('aiChatInput').value.trim();
    if (!input) return;
    // Simulate AI response
    document.getElementById('aiChatResponse').innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Thinking...';
    setTimeout(() => {
        document.getElementById('aiChatResponse').innerHTML = '<b>AI:</b> This is a sample AI-powered answer to: <code>' + input + '</code>';
    }, 1200);
}
// DataTables (if needed)
$(document).ready(function() {
    $('#customersTable').DataTable && $('#customersTable').DataTable({ pageLength: 5 });
});
</script>
</body>
</html>