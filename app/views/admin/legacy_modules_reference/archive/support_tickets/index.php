<?php include APP_PATH . '/views/admin/layouts/header.php'; ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-ticket-alt me-2"></i>Support Tickets</h2>
        <a href="<?php echo BASE_URL; ?>/admin/support-tickets/create" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Create Ticket
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
                    <input type="text" name="search" class="form-control" placeholder="Search tickets..." value="<?php echo htmlspecialchars($filters['search'] ?? ''); ?>">
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="open" <?php echo ($filters['status'] == 'open') ? 'selected' : ''; ?>>Open</option>
                        <option value="closed" <?php echo ($filters['status'] == 'closed') ? 'selected' : ''; ?>>Closed</option>
                        <option value="pending" <?php echo ($filters['status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
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
                        <option value="">All Agents</option>
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
                            <th>ID</th>
                            <th>Subject</th>
                            <th>Requester</th>
                            <th>Assigned To</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($tickets)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">No tickets found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($tickets as $ticket): ?>
                                <tr>
                                    <td>#<?php echo $ticket['id']; ?></td>
                                    <td>
                                        <div class="fw-bold"><?php echo htmlspecialchars($ticket['subject']); ?></div>
                                        <small class="text-muted"><?php echo htmlspecialchars($ticket['category']); ?></small>
                                    </td>
                                    <td>
                                        <?php if (!empty($ticket['requester_name'])): ?>
                                            <div><?php echo htmlspecialchars($ticket['requester_name']); ?></div>
                                            <small class="text-muted"><?php echo htmlspecialchars($ticket['requester_email']); ?></small>
                                        <?php else: ?>
                                            <span class="text-muted">Unknown User (ID: <?php echo $ticket['user_id']; ?>)</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($ticket['assigned_to_name'])): ?>
                                            <small class="text-muted"><i class="fas fa-user-shield me-1"></i><?php echo htmlspecialchars($ticket['assigned_to_name']); ?></small>
                                        <?php else: ?>
                                            <span class="text-muted">Unassigned</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $priorityClass = 'bg-secondary';
                                        if ($ticket['priority'] == 'high') $priorityClass = 'bg-danger';
                                        elseif ($ticket['priority'] == 'medium') $priorityClass = 'bg-warning text-dark';
                                        elseif ($ticket['priority'] == 'low') $priorityClass = 'bg-info';
                                        ?>
                                        <span class="badge <?php echo $priorityClass; ?>"><?php echo ucfirst($ticket['priority']); ?></span>
                                    </td>
                                    <td>
                                        <?php
                                        $statusClass = 'bg-secondary';
                                        if ($ticket['status'] == 'closed') $statusClass = 'bg-success';
                                        elseif ($ticket['status'] == 'open') $statusClass = 'bg-primary';
                                        elseif ($ticket['status'] == 'pending') $statusClass = 'bg-warning text-dark';
                                        ?>
                                        <span class="badge <?php echo $statusClass; ?>"><?php echo ucfirst($ticket['status']); ?></span>
                                    </td>
                                    <td>
                                        <?php echo date('d M Y', strtotime($ticket['created_at'])); ?>
                                    </td>
                                    <td class="text-end">
                                        <a href="<?php echo BASE_URL; ?>/admin/support-tickets/edit/<?php echo $ticket['id']; ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="<?php echo BASE_URL; ?>/admin/support-tickets/delete/<?php echo $ticket['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure?')">
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
