<?php
$tasks = $tasks ?? [];
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">My Tasks</h1>
        <a href="/tasks/create" class="btn btn-primary"><i class="fas fa-plus"></i> New Task</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Task</th>
                            <th>Priority</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($tasks)): ?>
                            <tr><td colspan="5" class="text-center text-muted py-4">No tasks found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($tasks as $t): ?>
                                <tr>
                                    <td><?= htmlspecialchars($t['title'] ?? '') ?></td>
                                    <td>
                                        <?php $p = $t['priority'] ?? ''; ?>
                                        <?php if ($p === 'High'): ?>
                                            <span class="badge bg-danger">High</span>
                                        <?php elseif ($p === 'Medium'): ?>
                                            <span class="badge bg-warning text-dark">Medium</span>
                                        <?php elseif ($p === 'Low'): ?>
                                            <span class="badge bg-success">Low</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary"><?= htmlspecialchars($p) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($t['due_date'] ?? '') ?></td>
                                    <td>
                                        <?php $s = $t['status'] ?? ''; ?>
                                        <?php if ($s === 'Pending'): ?>
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        <?php elseif ($s === 'In Progress'): ?>
                                            <span class="badge bg-info">In Progress</span>
                                        <?php elseif ($s === 'Completed'): ?>
                                            <span class="badge bg-success">Completed</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary"><?= htmlspecialchars($s) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <a href="/tasks/<?= $t['id'] ?? 0 ?>" class="btn btn-sm btn-outline-primary" title="View"><i class="fas fa-eye"></i></a>
                                        <a href="/tasks/<?= $t['id'] ?? 0 ?>/edit" class="btn btn-sm btn-outline-secondary" title="Edit"><i class="fas fa-edit"></i></a>
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
