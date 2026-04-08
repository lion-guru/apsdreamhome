<?php
/**
 * Support Tickets Index View
 */
$tickets = $tickets ?? [];
$total = $total ?? 0;
$page = $page ?? 1;
$per_page = $per_page ?? 20;
$total_pages = $total_pages ?? 1;
$filters = $filters ?? [];
$page_title = $page_title ?? 'Support Tickets';
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
                <h2 class="mb-1">Support Tickets</h2>
                <p class="text-muted mb-0">Manage customer support requests</p>
            </div>
            <div>
                <a href="<?php echo $base; ?>/admin/support_tickets/create" class="btn btn-primary me-2">
                    <i class="fas fa-plus me-2"></i>New Ticket
                </a>
                <a href="<?php echo $base; ?>/admin/dashboard" class="btn btn-outline-secondary">Back</a>
            </div>
        </div>
        
        <!-- Filters -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <input type="text" name="search" class="form-control" placeholder="Search tickets..." value="<?php echo htmlspecialchars($filters['search'] ?? ''); ?>">
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="open" <?php echo ($filters['status'] ?? '') === 'open' ? 'selected' : ''; ?>>Open</option>
                            <option value="in_progress" <?php echo ($filters['status'] ?? '') === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                            <option value="resolved" <?php echo ($filters['status'] ?? '') === 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                            <option value="closed" <?php echo ($filters['status'] ?? '') === 'closed' ? 'selected' : ''; ?>>Closed</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="priority" class="form-select">
                            <option value="">All Priority</option>
                            <option value="low" <?php echo ($filters['priority'] ?? '') === 'low' ? 'selected' : ''; ?>>Low</option>
                            <option value="medium" <?php echo ($filters['priority'] ?? '') === 'medium' ? 'selected' : ''; ?>>Medium</option>
                            <option value="high" <?php echo ($filters['priority'] ?? '') === 'high' ? 'selected' : ''; ?>>High</option>
                            <option value="urgent" <?php echo ($filters['priority'] ?? '') === 'urgent' ? 'selected' : ''; ?>>Urgent</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Tickets Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <?php if (!empty($tickets)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Ticket #</th>
                                    <th>Subject</th>
                                    <th>Customer</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                    <th>Assigned To</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tickets as $ticket): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($ticket['ticket_number'] ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($ticket['subject'] ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($ticket['customer_name'] ?? '-'); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo ($ticket['priority'] ?? '') === 'urgent' ? 'danger' : (($ticket['priority'] ?? '') === 'high' ? 'warning' : (($ticket['priority'] ?? '') === 'medium' ? 'info' : 'secondary')); ?>">
                                                <?php echo ucfirst($ticket['priority'] ?? 'low'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo ($ticket['status'] ?? '') === 'open' ? 'danger' : (($ticket['status'] ?? '') === 'in_progress' ? 'warning' : (($ticket['status'] ?? '') === 'resolved' ? 'success' : 'secondary')); ?>">
                                                <?php echo ucfirst(str_replace('_', ' ', $ticket['status'] ?? 'unknown')); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($ticket['assigned_agent_name'] ?? 'Unassigned'); ?></td>
                                        <td><?php echo isset($ticket['created_at']) ? date('M d, Y', strtotime($ticket['created_at'])) : '-'; ?></td>
                                        <td>
                                            <a href="<?php echo $base; ?>/admin/support_tickets/show/<?php echo $ticket['id']; ?>" class="btn btn-sm btn-outline-primary">View</a>
                                            <a href="<?php echo $base; ?>/admin/support_tickets/edit/<?php echo $ticket['id']; ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <?php if ($total_pages > 1): ?>
                        <nav class="mt-3">
                            <ul class="pagination justify-content-center">
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-ticket-alt fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No tickets found</p>
                        <a href="<?php echo $base; ?>/admin/support_tickets/create" class="btn btn-primary">Create First Ticket</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
