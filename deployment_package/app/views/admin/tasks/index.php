<?php include APP_PATH . '/views/admin/layouts/header.php'; ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-tasks me-2"></i>Task Management</h2>
        <a href="<?php echo BASE_URL; ?>/admin/tasks/create" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Add New Task
        </a>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['success'];
            unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['error'];
            unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form action="" method="GET" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Search tasks..." value="<?php echo htmlspecialchars($filters['search'] ?? ''); ?>">
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="pending" <?php echo ($filters['status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                        <option value="in_progress" <?php echo ($filters['status'] == 'in_progress') ? 'selected' : ''; ?>>In Progress</option>
                        <option value="completed" <?php echo ($filters['status'] == 'completed') ? 'selected' : ''; ?>>Completed</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="priority" class="form-select">
                        <option value="">All Priorities</option>
                        <option value="high" <?php echo ($filters['priority'] == 'high') ? 'selected' : ''; ?>>High</option>
                        <option value="medium" <?php echo ($filters['priority'] == 'medium') ? 'selected' : ''; ?>>Medium</option>
                        <option value="low" <?php echo ($filters['priority'] == 'low') ? 'selected' : ''; ?>>Low</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="assigned_to" class="form-select">
                        <option value="">All Users</option>
                        <?php foreach ($users as $user): ?>
                            <option value="<?php echo $user['id']; ?>" <?php echo ($filters['assigned_to'] == $user['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($user['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Title</th>
                            <th>Assigned To</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Due Date</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($tasks)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No tasks found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($tasks as $task): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold"><?php echo htmlspecialchars($task['title']); ?></div>
                                        <small class="text-muted"><?php echo htmlspecialchars(substr($task['description'], 0, 50)) . (strlen($task['description']) > 50 ? '...' : ''); ?></small>
                                    </td>
                                    <td>
                                        <?php if (!empty($task['assigned_to_name'])): ?>
                                            <small class="text-muted"><i class="fas fa-user-tag me-1"></i><?php echo htmlspecialchars($task['assigned_to_name']); ?></small>
                                        <?php else: ?>
                                            <span class="text-muted">Unassigned</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $priorityClass = 'bg-secondary';
                                        if ($task['priority'] == 'high') $priorityClass = 'bg-danger';
                                        elseif ($task['priority'] == 'medium') $priorityClass = 'bg-warning text-dark';
                                        elseif ($task['priority'] == 'low') $priorityClass = 'bg-info';
                                        ?>
                                        <span class="badge <?php echo $priorityClass; ?>"><?php echo ucfirst($task['priority']); ?></span>
                                    </td>
                                    <td>
                                        <?php
                                        $statusClass = 'bg-secondary';
                                        if ($task['status'] == 'completed') $statusClass = 'bg-success';
                                        elseif ($task['status'] == 'in_progress') $statusClass = 'bg-primary';
                                        elseif ($task['status'] == 'pending') $statusClass = 'bg-warning text-dark';
                                        ?>
                                        <span class="badge <?php echo $statusClass; ?>"><?php echo ucfirst(str_replace('_', ' ', $task['status'])); ?></span>
                                    </td>
                                    <td>
                                        <?php 
                                        if (!empty($task['due_date'])) {
                                            $dueDate = strtotime($task['due_date']);
                                            $today = time();
                                            $class = ($dueDate < $today && $task['status'] != 'completed') ? 'text-danger fw-bold' : '';
                                            echo '<span class="' . $class . '">' . date('d M Y', $dueDate) . '</span>';
                                        } else {
                                            echo '-';
                                        }
                                        ?>
                                    </td>
                                    <td class="text-end">
                                        <a href="<?php echo BASE_URL; ?>/admin/tasks/edit/<?php echo $task['id']; ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="<?php echo BASE_URL; ?>/admin/tasks/delete/<?php echo $task['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include APP_PATH . '/views/admin/layouts/footer.php'; ?>
