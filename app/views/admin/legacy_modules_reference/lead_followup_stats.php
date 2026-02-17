<?php
// lead_followup_stats.php: Dashboard widget/page for lead follow-up statistics
require_once __DIR__ . '/core/init.php';

use App\Core\Database;

if (!isAuthenticated() || !isAdmin()) {
    header('Location: login.php');
    exit();
}

$db = \App\Core\App::database();

// Get stats
$totalLeads = $db->fetch("SELECT COUNT(*) as c FROM leads")['c'] ?? 0;
$newLeads = $db->fetch("SELECT COUNT(*) as c FROM leads WHERE status = 'New'")['c'] ?? 0;
$qualifiedLeads = $db->fetch("SELECT COUNT(*) as c FROM leads WHERE status = 'Qualified'")['c'] ?? 0;
$contactedLeads = $db->fetch("SELECT COUNT(*) as c FROM leads WHERE status = 'Contacted'")['c'] ?? 0;
$convertedLeads = $db->fetch("SELECT COUNT(*) as c FROM leads WHERE status = 'Converted'")['c'] ?? 0;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>Lead Follow-up Stats</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body { background: #f8f9fa; }
        .main-content { margin-left: 220px; padding: 2rem 1rem; }
        .stat-card { min-width: 200px; }
    </style>
</head>
<body>
<?php include __DIR__ . '/includes/admin_sidebar.php'; ?>
<div class="main-content">
    <h1 class="h3 mb-4">Lead Follow-up Statistics</h1>
    <div class="row g-4">
        <div class="col-md-3">
            <div class="card stat-card text-bg-primary mb-3">
                <div class="card-body">
                    <h5 class="card-title">Total Leads</h5>
                    <p class="card-text display-6"><?= $totalLeads ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card text-bg-warning mb-3">
                <div class="card-body">
                    <h5 class="card-title">New</h5>
                    <p class="card-text display-6"><?= $newLeads ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card text-bg-info mb-3">
                <div class="card-body">
                    <h5 class="card-title">Qualified</h5>
                    <p class="card-text display-6"><?= $qualifiedLeads ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card text-bg-success mb-3">
                <div class="card-body">
                    <h5 class="card-title">Contacted</h5>
                    <p class="card-text display-6"><?= $contactedLeads ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card text-bg-secondary mb-3">
                <div class="card-body">
                    <h5 class="card-title">Converted</h5>
                    <p class="card-text display-6"><?= $convertedLeads ?></p>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
