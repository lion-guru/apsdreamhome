<?php
// Modern, AI-powered, feature-rich dashboard for Tenants (2025 best practices)
require_once 'header.php';
session_start();
if (!isset($_SESSION['uid']) || $_SESSION['utype'] !== 'tenant') {
    header('Location: login.php');
    exit();
}
// Example tenant stats (replace with real queries)
$tenant_name = htmlspecialchars($_SESSION['name'] ?? 'Tenant');
$stats = [
    'active_rentals' => 2,
    'pending_payments' => 1,
    'total_paid' => 150000,
    'service_requests' => 3,
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tenant Dashboard | APS Dream Homes</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f8f9fa; }
        .dashboard-card { border-radius: 1rem; box-shadow: 0 2px 16px rgba(0,0,0,0.08); margin-bottom: 2rem; background: #fff; }
        .feature-icon { font-size: 2.5rem; color: #28a745; }
        .dashboard-stats { display: flex; gap: 2rem; flex-wrap: wrap; margin-bottom: 2rem; }
        .stat-box { background: #f8f9fa; border-radius: .5rem; padding: 1.5rem 2rem; min-width: 180px; text-align: center; }
        .ai-chatbot { background: #eaf4ff; border-left: 5px solid #28a745; padding: 1.5rem; border-radius: .5rem; margin-bottom: 2rem; }
        @media (max-width: 767px) { .dashboard-stats { flex-direction: column; gap: 1rem; } }
    </style>
</head>
<body>
<div class="container py-5">
    <h1 class="mb-4">Welcome, <?php echo $tenant_name; ?>!</h1>
    <!-- Stats Cards -->
    <div class="dashboard-stats">
        <div class="stat-box">
            <div class="fs-3 fw-bold text-success"><?php echo $stats['active_rentals']; ?></div>
            <div>Active Rentals</div>
        </div>
        <div class="stat-box">
            <div class="fs-3 fw-bold text-danger"><?php echo $stats['pending_payments']; ?></div>
            <div>Pending Payments</div>
        </div>
        <div class="stat-box">
            <div class="fs-3 fw-bold text-primary"><?php echo number_format($stats['total_paid']); ?></div>
            <div>Total Paid (₹)</div>
        </div>
        <div class="stat-box">
            <div class="fs-3 fw-bold text-warning"><?php echo $stats['service_requests']; ?></div>
            <div>Service Requests</div>
        </div>
    </div>
    <!-- AI Chatbot Panel -->
    <div class="ai-chatbot mb-4">
        <strong><i class="fa-solid fa-robot"></i> Ask AI (Chatbot):</strong>
        <form id="aiChatForm" class="d-flex mt-2" onsubmit="return false;">
            <input type="text" class="form-control me-2" id="aiChatInput" placeholder="Ask about rent, payments, or maintenance...">
            <button class="btn btn-success" onclick="sendAIQuery()"><i class="fa-solid fa-paper-plane"></i></button>
        </form>
        <div id="aiChatResponse" class="mt-2 text-secondary small">Try: "When is my next rent due?"</div>
    </div>
    <div class="row">
        <div class="col-md-4">
            <div class="card dashboard-card p-4 text-center">
                <i class="fa-solid fa-home feature-icon"></i>
                <h4 class="mt-3">My Rentals</h4>
                <p>See your rented properties and payment status.</p>
                <a href="#" class="btn btn-outline-success">View Rentals</a>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card dashboard-card p-4 text-center">
                <i class="fa-solid fa-file-invoice-dollar feature-icon"></i>
                <h4 class="mt-3">Rent Payments</h4>
                <p>Pay rent online, download receipts, and view history.</p>
                <a href="#" class="btn btn-outline-success">Pay Rent</a>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card dashboard-card p-4 text-center">
                <i class="fa-solid fa-bell feature-icon"></i>
                <h4 class="mt-3">Notices & Alerts</h4>
                <p>Get updates about due dates and important notices.</p>
                <a href="#" class="btn btn-outline-success">View Notices</a>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="card dashboard-card p-4">
                <h5>Service Requests</h5>
                <ul>
                    <li><a href="#">Raise a Maintenance Request</a></li>
                    <li><a href="#">Track Requests</a></li>
                </ul>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card dashboard-card p-4">
                <h5>Profile & Settings</h5>
                <ul>
                    <li><a href="#">Edit Profile</a></li>
                    <li><a href="#">Change Password</a></li>
                </ul>
            </div>
        </div>
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
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
