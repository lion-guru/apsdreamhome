<?php
/**
 * Agent Dashboard - AI Agent System
 * Stub: Route handler for /agent_dashboard and /agents
 */
require_once __DIR__ . '/config/bootstrap.php';
$pageTitle = 'Agent Dashboard';
ob_start();
?>
<div class="container py-5">
    <div class="alert alert-info">
        <h4><i class="fas fa-robot"></i> Agent Dashboard</h4>
        <p>The AI agent dashboard is being initialized.</p>
    </div>
</div>
<?php
$content = ob_get_clean();
require_once __DIR__ . '/app/views/layouts/base.php';
