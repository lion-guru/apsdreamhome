<?php
// Modern, AI-powered, feature-rich dashboard for Users (2025 best practices)

session_start();
require_once(__DIR__ . '/config/config.php');
require_once(__DIR__ . '/includes/functions/role_helper.php');
require_once(__DIR__ . '/includes/functions/common-functions.php');
require_once(__DIR__ . '/includes/db_config.php');
enforceRole(['user','customer']);
error_reporting(E_ERROR | E_PARSE);

// Check if user is logged in
if (!isset($_SESSION['uid']) || !isset($_SESSION['utype']) || $_SESSION['utype'] != 'user') {
    header("location:login.php");
    exit();
}

// Add session timeout check
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
    session_unset();
    session_destroy();
    header("location:login.php?msg=timeout");
    exit();
}
$_SESSION['last_activity'] = time();

$user_id = $_SESSION['uid'];

// Fetch user details
$con = getDbConnection();
$query = "SELECT * FROM users WHERE id = $user_id";
$result = mysqli_query($con, $query);
$user_data = mysqli_fetch_assoc($result);

// Fetch user's properties
$property_query = "SELECT * FROM property WHERE uid = $user_id";
$property_result = mysqli_query($con, $property_query);
$property_count = mysqli_num_rows($property_result);

// Example stats (replace with real queries)
$dashboard_stats = [
    'properties' => $property_count,
    'kyc_status' => $user_data['kyc_status'] ?? 'Pending',
    'pending_docs' => 2,
    'notifications' => 3,
];

// Set page specific variables
$page_title = "User Dashboard - APS Dream Homes";
$meta_description = "Access your personalized dashboard to manage your profile, properties, and more at APS Dream Homes.";
$additional_css = '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">';

$menu_config = include(__DIR__ . '/includes/config/menu_config.php');
$current_role = getCurrentUserRole();
$menu_items = $menu_config[$current_role] ?? [];

// Use standardized header
?>
<!-- Standardized Header -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>
    <meta name="description" content="<?= $meta_description ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <?= $additional_css ?>
    <style>
        body { background: #f8f9fa; }
        .dashboard-card { border-radius: 1rem; box-shadow: 0 2px 16px rgba(0,0,0,0.08); margin-bottom: 2rem; background: #fff; }
        .feature-icon { font-size: 2.5rem; color: #0d6efd; }
        .dashboard-stats { display: flex; gap: 2rem; flex-wrap: wrap; margin-bottom: 2rem; }
        .stat-box { background: #f8f9fa; border-radius: .5rem; padding: 1.5rem 2rem; min-width: 180px; text-align: center; }
        .ai-chatbot { background: #eaf4ff; border-left: 5px solid #0d6efd; padding: 1.5rem; border-radius: .5rem; margin-bottom: 2rem; }
        @media (max-width: 767px) { .dashboard-stats { flex-direction: column; gap: 1rem; } }
    </style>
</head>
<body>
<?php include(__DIR__ . '/includes/templates/dynamic_header.php'); ?>
<div class="container py-5">
    <h1 class="mb-4">Welcome, <?= htmlspecialchars($user_data['name'] ?? 'User') ?>!</h1>
    <!-- Stats Cards -->
    <div class="dashboard-stats">
        <div class="stat-box">
            <div class="fs-3 fw-bold text-primary"><?php echo $dashboard_stats['properties']; ?></div>
            <div>Properties</div>
        </div>
        <div class="stat-box">
            <div class="fs-3 fw-bold text-info"><?php echo $dashboard_stats['kyc_status']; ?></div>
            <div>KYC Status</div>
        </div>
        <div class="stat-box">
            <div class="fs-3 fw-bold text-warning"><?php echo $dashboard_stats['pending_docs']; ?></div>
            <div>Pending Docs</div>
        </div>
        <div class="stat-box">
            <div class="fs-3 fw-bold text-success"><?php echo $dashboard_stats['notifications']; ?></div>
            <div>Notifications</div>
        </div>
    </div>
    <!-- AI Chatbot Panel -->
    <div class="ai-chatbot mb-4">
        <strong><i class="fa-solid fa-robot"></i> Ask AI (Chatbot):</strong>
        <form id="aiChatForm" class="d-flex mt-2" onsubmit="return false;">
            <input type="text" class="form-control me-2" id="aiChatInput" placeholder="Ask about properties, KYC, or documents...">
            <button class="btn btn-primary" onclick="sendAIQuery()"><i class="fa-solid fa-paper-plane"></i></button>
        </form>
        <div id="aiChatResponse" class="mt-2 text-secondary small">Try: "Show my property list"</div>
    </div>
    <!-- Properties Table -->
    <div class="dashboard-card p-4">
        <h4><i class="fa-solid fa-building"></i> My Properties</h4>
        <?php if ($property_count > 0): ?>
        <div class="table-responsive">
            <table class="table table-bordered table-hover mt-2">
                <thead class="table-light">
                    <tr>
                        <th>Property ID</th>
                        <th>Type</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th>Value</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($row = mysqli_fetch_assoc($property_result)): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['id']) ?></td>
                        <td><?= htmlspecialchars($row['type']) ?></td>
                        <td><?= htmlspecialchars($row['location']) ?></td>
                        <td><?= htmlspecialchars($row['status']) ?></td>
                        <td><?= htmlspecialchars($row['value']) ?></td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="alert alert-info">No properties found.</div>
        <?php endif; ?>
    </div>
    <!-- AI Suggestions Panel -->
    <div class="dashboard-card p-4 bg-white rounded shadow-sm">
        <h4 class="mb-3"><i class="fa fa-magic me-2"></i>AI Suggestions & Reminders</h4>
        <div id="aiSuggestionsPanel">
            <div class="text-center text-muted">Loading personalized suggestions...</div>
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
<footer class="bg-light text-center text-lg-start">
    <div class="text-center p-3" style="background-color: rgba(0, 0, 0, 0.2);">
        &copy; 2023 APS Dream Homes
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/js/all.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
// AI Chatbot JS (simulate response)
function sendAIQuery() {
    const input = document.getElementById('aiChatInput').value.trim();
    if (!input) return;
    document.getElementById('aiChatResponse').innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Thinking...';
    setTimeout(() => {
        document.getElementById('aiChatResponse').innerHTML = '<b>AI:</b> This is a sample AI-powered answer to: <code>' + input + '</code>';
    }, 1200);
}
// AI Suggestions Panel (AJAX)
function logAIInteraction(action, suggestion, feedback, notes) {
    $.post('admin/log_ai_interaction.php', {
        action: action,
        suggestion: suggestion,
        feedback: feedback||'',
        notes: notes||''
    });
}
$(function(){
    $.get('user_ai_suggestions.php', function(resp) {
        if(resp.success) {
            let html = '';
            if(resp.status && resp.status.length) {
                html += '<div class="mb-2"><b>Reminders:</b><ul>';
                resp.status.forEach(function(rem) { html += '<li>'+rem+' <span class="badge bg-light text-dark pointer ms-1" onclick="logAIInteraction(\'feedback\', `'+rem.replace(/'/g,"&#39;")+'`,\'like\')">👍</span> <span class="badge bg-light text-dark pointer" onclick="logAIInteraction(\'feedback\', `'+rem.replace(/'/g,"&#39;")+'`,\'dislike\')">👎</span></li>'; });
                html += '</ul></div>';
            }
            if(resp.suggestions && resp.suggestions.length) {
                html += '<div><b>AI Suggestions:</b><ul>';
                resp.suggestions.forEach(function(sugg) { html += '<li>'+sugg+' <span class="badge bg-light text-dark pointer ms-1" onclick="logAIInteraction(\'feedback\', `'+sugg.replace(/'/g,"&#39;")+'`,\'like\')">👍</span> <span class="badge bg-light text-dark pointer" onclick="logAIInteraction(\'feedback\', `'+sugg.replace(/'/g,"&#39;")+'`,\'dislike\')">👎</span></li>'; });
                html += '</ul></div>';
            }
            if(!html) html = '<div class="text-success">You are all caught up!</div>';
            $('#aiSuggestionsPanel').html(html);
            if(resp.suggestions) resp.suggestions.forEach(function(sugg){ logAIInteraction('view', sugg); });
            if(resp.status) resp.status.forEach(function(rem){ logAIInteraction('view', rem); });
        } else {
            $('#aiSuggestionsPanel').html('<div class="text-danger">Could not load suggestions.</div>');
        }
    },'json');
});
</script>
</body>
</html>