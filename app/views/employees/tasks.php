<?php
/**
 * Employee Tasks View
 * Shows employee tasks and allows status updates
 */
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-tasks me-2"></i>My Tasks</h2>
        <div class="d-flex gap-2">
            <div class="dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-filter me-2"></i>Filter by Status
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="?status=">All Tasks</a></li>
                    <li><a class="dropdown-item" href="?status=pending">Pending</a></li>
                    <li><a class="dropdown-item" href="?status=in_progress">In Progress</a></li>
                    <li><a class="dropdown-item" href="?status=completed">Completed</a></li>
                    <li><a class="dropdown-item" href="?status=overdue">Overdue</a></li>
                </ul>
            </div>
            <div class="dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-sort me-2"></i>Sort by Priority
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="?priority=">All Priorities</a></li>
                    <li><a class="dropdown-item" href="?priority=high">High</a></li>
                    <li><a class="dropdown-item" href="?priority=medium">Medium</a></li>
                    <li><a class="dropdown-item" href="?priority=low">Low</a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Tasks List -->
    <div class="row">
        <?php if (empty($tasks)): ?>
            <div class="col-12">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>No tasks found.
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($tasks as $task): ?>
                <div class="col-md-6 col-lg-4 mb-3">
                    <div class="card task-card h-100">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">
                                <i class="fas fa-<?= $this->getTaskIcon($task['priority'] ?? 'medium') ?> me-2"></i>
                                Task #<?= htmlspecialchars($task['task_id']) ?>
                            </h6>
                            <span class="badge bg-<?= $this->getStatusBadgeClass($task['status'] ?? 'pending') ?>">
                                <?= ucfirst(str_replace('_', ' ', $task['status'] ?? 'pending')) ?>
                            </span>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title mb-2">
                                <?= htmlspecialchars($task['title'] ?? 'Untitled Task') ?>
                            </h5>
                            <p class="card-text text-muted mb-2">
                                <?= htmlspecialchars(substr($task['description'] ?? '', 0, 100)) ?>
                                <?php if (strlen($task['description'] ?? '') > 100): ?>...<?php endif; ?>
                            </p>

                            <!-- Task Meta -->
                            <div class="task-meta mb-3">
                                <small class="text-muted">
                                    <i class="fas fa-calendar me-1"></i>
                                    Due: <?= htmlspecialchars($task['due_date'] ?? 'No deadline') ?>
                                </small>
                                <br>
                                <small class="text-muted">
                                    <i class="fas fa-clock me-1"></i>
                                    Priority: <?= ucfirst($task['priority'] ?? 'medium') ?>
                                </small>
                                <?php if (!empty($task['estimated_hours'])): ?>
                                    <br>
                                    <small class="text-muted">
                                        <i class="fas fa-hourglass-half me-1"></i>
                                        Est. Hours: <?= htmlspecialchars($task['estimated_hours']) ?>
                                    </small>
                                <?php endif; ?>
                            </div>

                            <!-- Progress Bar for In Progress Tasks -->
                            <?php if (($task['status'] ?? 'pending') === 'in_progress' && !empty($task['progress'])): ?>
                                <div class="progress mb-3">
                                    <div class="progress-bar" role="progressbar"
                                         style="width: <?= htmlspecialchars($task['progress']) ?>%">
                                        <?= htmlspecialchars($task['progress']) ?>%
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Action Buttons -->
                            <div class="task-actions">
                                <?php if (($task['status'] ?? 'pending') === 'pending'): ?>
                                    <button class="btn btn-sm btn-success" onclick="updateTaskStatus(<?= $task['task_id'] ?>, 'in_progress')">
                                        <i class="fas fa-play me-1"></i>Start Task
                                    </button>
                                <?php elseif (($task['status'] ?? 'pending') === 'in_progress'): ?>
                                    <button class="btn btn-sm btn-warning" onclick="updateTaskStatus(<?= $task['task_id'] ?>, 'completed')">
                                        <i class="fas fa-check me-1"></i>Complete
                                    </button>
                                <?php endif; ?>

                                <button class="btn btn-sm btn-info" onclick="viewTaskDetails(<?= $task['task_id'] ?>)">
                                    <i class="fas fa-eye me-1"></i>Details
                                </button>
                            </div>
                        </div>

                        <!-- Task Footer -->
                        <div class="card-footer text-muted">
                            <small>
                                <i class="fas fa-user me-1"></i>
                                Assigned by: <?= htmlspecialchars($task['assigned_by_name'] ?? 'System') ?>
                            </small>
                            <br>
                            <small>
                                <i class="fas fa-calendar-plus me-1"></i>
                                Created: <?= htmlspecialchars($task['created_at'] ?? '') ?>
                            </small>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Pagination (if needed) -->
    <?php if (!empty($tasks) && count($tasks) >= 20): ?>
        <div class="row">
            <div class="col-12">
                <nav aria-label="Task pagination">
                    <ul class="pagination justify-content-center">
                        <li class="page-item disabled">
                            <a class="page-link" href="#" tabindex="-1">Previous</a>
                        </li>
                        <li class="page-item active">
                            <a class="page-link" href="#">1</a>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="#">2</a>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="#">3</a>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="#">Next</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Task Details Modal -->
<div class="modal fade" id="taskDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Task Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="taskDetailsContent">
                <!-- Task details will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
function updateTaskStatus(taskId, status) {
    if (confirm('Are you sure you want to update this task status?')) {
        // Create form and submit
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/employee/update-task/${taskId}`;

        const statusInput = document.createElement('input');
        statusInput.type = 'hidden';
        statusInput.name = 'status';
        statusInput.value = status;

        form.appendChild(statusInput);
        document.body.appendChild(form);
        form.submit();
    }
}

function viewTaskDetails(taskId) {
    // In a real implementation, you would make an AJAX call to fetch task details
    $('#taskDetailsModal').modal('show');
    $('#taskDetailsContent').html('<p>Loading task details...</p>');
}

function updateTaskProgress(taskId) {
    const progress = prompt('Enter progress percentage (0-100):');
    if (progress !== null && progress >= 0 && progress <= 100) {
        // Submit progress update
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/employee/update-task/${taskId}`;

        const progressInput = document.createElement('input');
        progressInput.type = 'hidden';
        progressInput.name = 'progress';
        progressInput.value = progress;

        form.appendChild(progressInput);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<style>
.task-card {
    transition: transform 0.2s, box-shadow 0.2s;
}

.task-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.task-meta {
    border-top: 1px solid #eee;
    padding-top: 10px;
    margin-top: 10px;
}

.task-actions {
    margin-top: 15px;
}

.task-actions .btn {
    margin-right: 5px;
    margin-bottom: 5px;
}

.stats-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.progress {
    height: 10px;
}

.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #dee2e6;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -23px;
    top: 5px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid white;
}

.timeline-title {
    margin-bottom: 5px;
    font-weight: 600;
}

.timeline-text {
    margin-bottom: 5px;
    color: #666;
}
</style>
