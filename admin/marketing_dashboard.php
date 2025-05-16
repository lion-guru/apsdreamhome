<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true || !in_array($_SESSION['admin_role'], ['marketing'])) {
    header('Location: login.php');
    exit();
}
$employee = $_SESSION['admin_username'] ?? 'Marketing';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marketing Dashboard | APS Dream Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background:#f4f6fb; }
        .dashboard-container { max-width:900px; margin:40px auto; background:white; border-radius:16px; box-shadow:0 8px 32px rgba(0,0,0,0.12); padding:2.5rem; }
        .dashboard-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:2rem; }
        .dashboard-header h2 { font-weight:700; color:#1761fd; }
        .user-badge { background:#eaf1ff; color:#1761fd; border-radius:20px; padding:8px 20px; font-size:1.1rem; }
        .quick-links { display:flex; gap:1.5rem; flex-wrap:wrap; margin-bottom:2rem; }
        .quick-link { background:#f7fafd; border-radius:12px; box-shadow:0 2px 8px rgba(23,97,253,0.06); padding:1.5rem; flex:1 1 200px; text-align:center; transition:box-shadow 0.2s; }
        .quick-link:hover { box-shadow:0 4px 16px rgba(23,97,253,0.12); }
        .quick-link i { font-size:2.2rem; margin-bottom:0.5rem; color:#1761fd; }
        .quick-link span { display:block; font-size:1.1rem; font-weight:500; color:#222; }
        .dashboard-section { margin-bottom:2.5rem; }
        .dashboard-section h4 { color:#1761fd; font-weight:600; margin-bottom:1rem; }
        .task-list { list-style:none; padding:0; }
        .task-list li { background:#f7fafd; border-radius:8px; padding:0.8rem 1.2rem; margin-bottom:0.7rem; font-size:1.05rem; }
    </style>
</head>
<body>
<div class="dashboard-container">
    <div class="dashboard-header">
        <h2><i class="fa fa-bullhorn"></i> Welcome, <?php echo htmlspecialchars($employee); ?>!</h2>
        <span class="user-badge">Marketing</span>
    </div>
    <div class="quick-links">
        <a href="analytics_dashboard.php" class="quick-link"><i class="fa fa-chart-line"></i><span>Analytics</span></a>
        <a href="leads.php" class="quick-link"><i class="fa fa-users"></i><span>Leads</span></a>
        <a href="campaigns.php" class="quick-link"><i class="fa fa-bullseye"></i><span>Campaigns</span></a>
        <a href="documents_dashboard.php" class="quick-link"><i class="fa fa-folder-open"></i><span>Documents</span></a>
    </div>
    <div class="dashboard-section">
        <h4>Today's Marketing Tasks</h4>
        <ul class="task-list">
            <li>Review campaign performance</li>
            <li>Plan new lead generation strategy</li>
            <li>Coordinate with sales team</li>
        </ul>
    </div>
    <div class="dashboard-section">
        <h4>Quick Actions</h4>
        <button class="btn btn-primary">Create Campaign</button>
        <button class="btn btn-success">Add Lead</button>
    </div>
</div>
</body>
</html>
