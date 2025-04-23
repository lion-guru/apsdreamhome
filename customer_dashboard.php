<?php
session_start();
require_once(__DIR__ . "/includes/config/config.php");
// require_once(__DIR__ . "/includes/functions/asset_helper.php"); // Deprecated, use get_asset_url() from common-functions.php or updated-config-paths.php instead
require_once(__DIR__ . "/includes/functions/common-functions.php");
error_reporting(E_ERROR | E_PARSE);
$user= $_SESSION['usertype'];
$associate_id= $_SESSION['uid'];
$msg = '';
// Check if the user is logged in
if ($user != 'assosiate') {
    header("location:login.php");
    exit();
}
// Fetch associate details from the database
//$associate_id = $_SESSION['associate_id'];
$query_asso_details = "SELECT * FROM user WHERE uid = $associate_id";
$result_asso_details=mysqli_query($conn,$query_asso_details);
while($row_asso_details=mysqli_fetch_array($result_asso_details))
{
	$uid = $row_asso_details['uid'];
	$asso_name =  $row_asso_details['uname'];
	$asso_email =  $row_asso_details['uemail'];
	$asso_phone =  $row_asso_details['uphone'];
	$sponsor_id  =  $row_asso_details['sponsor_id'];
	$sponsored_by =  $row_asso_details['sponsored_by'];
	$bank_name =  $row_asso_details['bank_name'];
	$account_number  =  $row_asso_details['account_number'];
	$ifsc_code =  $row_asso_details['ifsc_code'];
	$bank_micr = $row_asso_details['bank_micr'];
	$bank_branch = $row_asso_details['bank_branch'];
	$bank_district = $row_asso_details['bank_district'];
	$bank_state = $row_asso_details['bank_state'];
	$account_type =  $row_asso_details['account_type'];
	$pan =  $row_asso_details['pan'];
	$adhaar =  $row_asso_details['adhaar'];	
	$nominee_name =  $row_asso_details['nominee_name'];
	$nominee_relation =  $row_asso_details['nominee_relation'];
	$nominee_contact  =  $row_asso_details['nominee_contact'];
	$address =  $row_asso_details['address'];
	$date_of_birth =  $row_asso_details['date_of_birth'];
	$join_date =  $row_asso_details['join_date'];
	$is_updated =  $row_asso_details['is_updated'];
}
// Example dashboard stats (replace with real queries)
$dashboard_stats = [
    'plots' => 2,
    'kyc_status' => 'Verified',
    'pending_docs' => 1,
    'notifications' => 3,
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customer Dashboard | APS Dream Homes</title>
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
    <header class="bg-light py-3">
        <div class="container text-center">
            <h1 class="text-success">Customer Dashboard</h1>
        </div>
    </header>
    <div class="container py-4">
        <!-- Stats Cards -->
        <div class="dashboard-stats">
            <div class="stat-box">
                <div class="fs-3 fw-bold text-success"><?php echo $dashboard_stats['plots']; ?></div>
                <div>Plots Owned</div>
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
                <div class="fs-3 fw-bold text-primary"><?php echo $dashboard_stats['notifications']; ?></div>
                <div>Notifications</div>
            </div>
        </div>
        <!-- AI Chatbot Panel -->
        <div class="ai-chatbot mb-4">
            <strong><i class="fa-solid fa-robot"></i> Ask AI (Chatbot):</strong>
            <form id="aiChatForm" class="d-flex mt-2" onsubmit="return false;">
                <input type="text" class="form-control me-2" id="aiChatInput" placeholder="Ask about plots, KYC, or documents...">
                <button class="btn btn-success" onclick="sendAIQuery()"><i class="fa-solid fa-paper-plane"></i></button>
            </form>
            <div id="aiChatResponse" class="mt-2 text-secondary small">Try: 'Show my KYC status'</div>
        </div>
        <!-- Existing Dashboard Content (Profile, Plot Details, etc.) -->
        <div class="row">
            <div class="col-md-6">
                <div class="card dashboard-card p-4">
                    <h5>Profile & Settings</h5>
                    <ul>
                        <li><b>Name:</b> <?php echo htmlspecialchars($asso_name); ?></li>
                        <li><b>Email:</b> <?php echo htmlspecialchars($asso_email); ?></li>
                        <li><b>Phone:</b> <?php echo htmlspecialchars($asso_phone); ?></li>
                        <li><b>KYC Status:</b> <?php echo htmlspecialchars($dashboard_stats['kyc_status']); ?></li>
                        <li><a href="#">Edit Profile</a></li>
                        <li><a href="#">Change Password</a></li>
                    </ul>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card dashboard-card p-4">
                    <h5>Bank Details</h5>
                    <ul>
                        <li><b>Bank Name:</b> <?php echo htmlspecialchars($bank_name); ?></li>
                        <li><b>Account Number:</b> <?php echo htmlspecialchars($account_number); ?></li>
                        <li><b>IFSC:</b> <?php echo htmlspecialchars($ifsc_code); ?></li>
                        <li><b>PAN:</b> <?php echo htmlspecialchars($pan); ?></li>
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
