<div class="main-content">
    <div class="page-header">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <div>
                <h2 class="page-title">Support Tickets</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Tickets</li>
                    </ol>
                </nav>
            </div>
            <a href="/admin/tickets/create" class="btn btn-primary">
                <i class="fas fa-plus"></i> New Ticket
            </a>
        </div>
    </div>

    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover admin-table">
                        <thead>
                            <tr>
                                <th>Ticket #</th>
                                <th>Subject</th>
                                <th>User</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($tickets)): ?>
                                <?php foreach ($tickets as $ticket): ?>
                                    <tr>
                                        <td>
                                            <a href="/admin/tickets/show/<?php echo $ticket['id']; ?>">
                                                <?php echo htmlspecialchars($ticket['ticket_number']); ?>
                                            </a>
                                        </td>
                                        <td><?php echo htmlspecialchars($ticket['subject']); ?></td>
                                        <td>
                                            <?php echo htmlspecialchars($ticket['user_name'] ?? 'N/A'); ?>
                                            <small class="d-block text-muted"><?php echo htmlspecialchars($ticket['user_email'] ?? ''); ?></small>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?php
                                                                        echo $ticket['priority'] == 'high' ? 'danger' : ($ticket['priority'] == 'medium' ? 'warning' : 'info');
                                                                        ?>">
                                                <?php echo ucfirst($ticket['priority']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?php
                                                                        echo $ticket['status'] == 'open' ? 'success' : ($ticket['status'] == 'closed' ? 'secondary' : 'primary');
                                                                        ?>">
                                                <?php echo ucfirst(str_replace('_', ' ', $ticket['status'])); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($ticket['created_at'])); ?></td>
                                        <td>
                                            <a href="<?php echo url('admin/tickets/show/' . $ticket['id']); ?>" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center">No tickets found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>