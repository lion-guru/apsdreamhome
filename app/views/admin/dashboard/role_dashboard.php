<?php
$base = defined('BASE_URL') ? BASE_URL : '/apsdreamhome';
$role = $role ?? 'admin';
$userName = $userName ?? ($_SESSION['user_name'] ?? $_SESSION['admin_name'] ?? 'User');
$stats = $stats ?? [];
$recentItems = $recentItems ?? [];

$roleLabels = [
    'super_admin' => 'Super Admin', 'admin' => 'Admin', 'manager' => 'Manager',
    'employee' => 'Employee', 'associate' => 'Associate', 'agent' => 'Agent',
    'customer' => 'Customer'
];
$roleLabel = $roleLabels[$role] ?? ucfirst($role);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1 fw-bold"><?php echo $roleLabel; ?> Dashboard</h1>
        <p class="text-muted mb-0">Welcome back, <?php echo htmlspecialchars($userName); ?>!</p>
    </div>
    <button class="btn btn-primary" onclick="location.reload()">
        <i class="fas fa-sync-alt me-2"></i>Refresh
    </button>
</div>

<?php
$partialPath = __DIR__ . '/partials/';

if (in_array($role, ['super_admin', 'admin', 'manager'])) {
    include $partialPath . 'stats_admin.php';
} elseif ($role === 'associate') {
    include $partialPath . 'stats_associate.php';
} elseif ($role === 'agent') {
    include $partialPath . 'stats_agent.php';
} elseif ($role === 'employee') {
    include $partialPath . 'stats_employee.php';
} else {
    include $partialPath . 'stats_admin.php';
}

include $partialPath . 'quick_actions_' . $role . '.php';

include $partialPath . 'recent_activity.php';
?>
