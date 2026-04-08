<?php
/**
 * Tasks Management View
 */
$tasks = $tasks ?? [];
$total = $total ?? 0;
$page = $page ?? 1;
$total_pages = $total_pages ?? 1;
$filters = $filters ?? [];
$page_title = $page_title ?? 'Tasks';
$base = defined('BASE_URL') ? BASE_URL : '/apsdreamhome';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1"><i class="fas fa-tasks me-2 text-primary"></i>Tasks</h2>
                <p class="text-muted mb-0">Manage team tasks and assignments</p>
            </div>
            <div>
                <button class="btn btn-primary me-2" onclick="alert('Create task feature coming soon')">
                    <i class="fas fa-plus me-2"></i>New Task
                </button>
                <a href="<?php echo $base; ?>/admin/dashboard" class="btn btn-outline-secondary">Back</a>
            </div>
        </div>
        
        <!-- Task Stats -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <h4 class="text-primary"><?php echo $stats['total'] ?? 0; ?></h4>
                        <p class="text-muted mb-0">Total Tasks</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <h4 class="text-warning"><?php echo $stats['pending'] ?? 0; ?></h4>
                        <p class="text-muted mb-0">Pending</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <h4 class="text-info"><?php echo $stats['in_progress'] ?? 0; ?></h4>
                        <p class="text-muted mb-0">In Progress</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <h4 class="text-success"><?php echo $stats['completed'] ?? 0; ?></h4>
                        <p class="text-muted mb-0">Completed</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Tasks List -->
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <?php if (!empty($tasks)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Task</th>
                                    <th>Assigned To</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                    <th>Due Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tasks as $task): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($task['title'] ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($task['assigned_to'] ?? 'Unassigned'); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo ($task['priority'] ?? '') === 'high' ? 'danger' : (($task['priority'] ?? '') === 'medium' ? 'warning' : 'secondary'); ?>">
                                                <?php echo ucfirst($task['priority'] ?? 'low'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo ($task['status'] ?? '') === 'completed' ? 'success' : (($task['status'] ?? '') === 'in_progress' ? 'info' : 'warning'); ?>">
                                                <?php echo ucfirst(str_replace('_', ' ', $task['status'] ?? 'pending')); ?>
                                            </span>
                                        </td>
                                        <td><?php echo isset($task['due_date']) ? date('M d, Y', strtotime($task['due_date'])) : '-'; ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary">Edit</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-tasks fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No tasks found</p>
                        <button class="btn btn-primary" onclick="alert('Create task feature coming soon')">
                            <i class="fas fa-plus me-2"></i>Create First Task
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
