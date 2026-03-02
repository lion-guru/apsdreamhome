<?php
// Modern Builder Dashboard - AI Powered, Responsive, Feature-Rich (2025 best practices)
require_once(__DIR__ . '/includes/functions/role_helper.php');
enforceRole(['builder']);
$menu_config = include(__DIR__ . '/includes/config/menu_config.php');
$current_role = getCurrentUserRole();
$menu_items = $menu_config[$current_role] ?? [];
include(__DIR__ . '/includes/templates/header.php');
// Fetch builder stats (replace with real queries)
$builder_name = htmlspecialchars($_SESSION['builder_name'] ?? 'Builder');
$stats = [
    'total_projects' => 6,
    'active_projects' => 4,
    'completed_projects' => 2,
    'total_budget' => 50000000,
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Builder Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
        <div class="col-12">
            <h2 class="mb-0">Welcome, <?php echo $builder_name; ?>!</h2>
            <div class="text-muted small">Builder Dashboard</div>
        </div>
    </div>
    <!-- Stats Cards -->
    <div class="dashboard-stats">
        <div class="stat-box">
            <div class="fs-3 fw-bold text-primary"><?php echo $stats['total_projects']; ?></div>
            <div>Total Projects</div>
        </div>
        <div class="stat-box">
            <div class="fs-3 fw-bold text-success"><?php echo $stats['active_projects']; ?></div>
            <div>Active Projects</div>
        </div>
        <div class="stat-box">
            <div class="fs-3 fw-bold text-warning"><?php echo $stats['completed_projects']; ?></div>
            <div>Completed Projects</div>
        </div>
        <div class="stat-box">
            <div class="fs-3 fw-bold text-info"><?php echo number_format($stats['total_budget']); ?></div>
            <div>Total Budget</div>
        </div>
    </div>
    <!-- AI Chatbot Panel -->
    <div class="ai-chatbot mb-4">
        <strong><i class="fa-solid fa-robot"></i> Ask AI (Chatbot):</strong>
        <form id="aiChatForm" class="d-flex mt-2" onsubmit="return false;">
            <input type="text" class="form-control me-2" id="aiChatInput" placeholder="Ask about your projects, budgets, or deadlines...">
            <button class="btn btn-primary" onclick="sendAIQuery()"><i class="fa-solid fa-paper-plane"></i></button>
        </form>
        <div id="aiChatResponse" class="mt-2 text-secondary small">Try: "Show my delayed projects"</div>
    </div>
    <!-- Projects Table -->
    <div class="dashboard-card">
        <h4><i class="fa-solid fa-building"></i> My Projects</h4>
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="projectsTable">
                <thead class="table-light">
                    <tr>
                        <th>Project Name</th>
                        <th>Status</th>
                        <th>Description</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Budget</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $builder_id = $_SESSION['uid'];
                    $stmt = $conn->prepare("SELECT * FROM projects WHERE builder_id = ?");
                    $stmt->bind_param("i", $builder_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['project_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['status']); ?></td>
                            <td><?php echo htmlspecialchars($row['description']); ?></td>
                            <td><?php echo htmlspecialchars($row['start_date']); ?></td>
                            <td><?php echo htmlspecialchars($row['end_date']); ?></td>
                            <td><?php echo htmlspecialchars($row['budget']); ?></td>
                            <td>
                                <a href="edit_project.php?id=<?php echo $row['bid']; ?>" class="btn btn-sm btn-info">Edit</a>
                                <a href="delete_project.php?id=<?php echo $row['bid']; ?>" class="btn btn-sm btn-danger">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <a href="add_project.php" class="btn btn-primary mt-3"><i class="fa fa-plus"></i> Add New Project</a>
    </div>
    <!-- AI Suggestions & Automation Status Panel -->
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
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
// DataTables (if needed)
$(document).ready(function() {
    $('#projectsTable').DataTable && $('#projectsTable').DataTable({ pageLength: 5 });
});
</script>
</body>
</html>
