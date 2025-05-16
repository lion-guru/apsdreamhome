<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true || !in_array($_SESSION['admin_role'], ['it_head'])) {
    header('Location: login.php');
    exit();
}
$employee = $_SESSION['admin_username'] ?? 'IT Head';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IT Dashboard | APS Dream Home</title>
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
        <h2><i class="fa fa-gauge"></i> Welcome, <?php echo htmlspecialchars($employee); ?>!</h2>
        <span class="user-badge">IT Head</span>
    </div>
    <div class="quick-links">
        <a href="ai_dashboard.php" class="quick-link"><i class="fa fa-robot"></i><span>AI Tools</span></a>
        <a href="support_dashboard.php" class="quick-link"><i class="fa fa-headset"></i><span>Support Tickets</span></a>
        <a href="documents_dashboard.php" class="quick-link"><i class="fa fa-folder-open"></i><span>IT Documents</span></a>
        <a href="compliance_dashboard.php" class="quick-link"><i class="fa fa-shield-alt"></i><span>Compliance</span></a>
    </div>
    <div class="dashboard-section">
        <h4>Today's IT Tasks</h4>
        <ul class="task-list">
            <li>Review system security status</li>
            <li>Check infrastructure uptime</li>
            <li>Resolve pending support tickets</li>
        </ul>
    </div>
    <div class="dashboard-section">
        <h4>Quick Actions</h4>
        <button class="btn btn-primary">Add New Asset</button>
        <button class="btn btn-success">Run Security Audit</button>
    </div>
</div>
</body>
</html>
