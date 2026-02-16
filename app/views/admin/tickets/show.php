<?php
$title = 'Ticket Details';
require_once APP_ROOT . '/views/admin/inc/header.php';
require_once APP_ROOT . '/views/admin/inc/sidebar.php';
?>

<div class="main-content">
    <div class="page-header">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <div>
                <h2 class="page-title">
                    <?php echo htmlspecialchars($ticket['subject']); ?>
                    <span class="badge badge-secondary text-sm ml-2"><?php echo $ticket['ticket_number']; ?></span>
                </h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo url('admin/dashboard'); ?>">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo url('admin/tickets'); ?>">Tickets</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Details</li>
                    </ol>
                </nav>
            </div>

            <?php if (Auth::user()->role === 'admin' || Auth::user()->role === 'super_admin'): ?>
                <div>
                    <form action="<?php echo url('admin/tickets/updateStatus/' . $ticket['id']); ?>" method="POST" class="d-inline">
                        <select name="status" class="form-control d-inline-block w-auto mr-2" onchange="this.form.submit()">
                            <option value="open" <?php echo $ticket['status'] == 'open' ? 'selected' : ''; ?>>Open</option>
                            <option value="in_progress" <?php echo $ticket['status'] == 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                            <option value="resolved" <?php echo $ticket['status'] == 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                            <option value="closed" <?php echo $ticket['status'] == 'closed' ? 'selected' : ''; ?>>Closed</option>
                        </select>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8">
                <!-- Original Ticket Message -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <div class="d-flex justify-content-between">
                            <strong>Original Message</strong>
                            <small class="text-muted"><?php echo date('M d, Y H:i', strtotime($ticket['created_at'])); ?></small>
                        </div>
                    </div>
                    <div class="card-body">
                        <p class="card-text"><?php echo nl2br(htmlspecialchars($ticket['message'])); ?></p>
                        <?php if (!empty($ticket['attachment'])): ?>
                            <div class="mt-3">
                                <i class="fa fa-paperclip"></i> <a href="<?php echo url($ticket['attachment']); ?>" target="_blank">View Attachment</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Replies -->
                <h4 class="mb-3">Discussion</h4>
                <?php if (!empty($ticket['replies'])): ?>
                    <?php foreach ($ticket['replies'] as $reply): ?>
                        <div class="card mb-3 <?php echo ($reply['user_role'] === 'admin' || $reply['user_role'] === 'super_admin') ? 'border-primary' : ''; ?>">
                            <div class="card-header <?php echo ($reply['user_role'] === 'admin' || $reply['user_role'] === 'super_admin') ? 'bg-primary text-white' : 'bg-light'; ?>">
                                <div class="d-flex justify-content-between">
                                    <strong><?php echo htmlspecialchars($reply['user_name']); ?></strong>
                                    <small><?php echo date('M d, Y H:i', strtotime($reply['created_at'])); ?></small>
                                </div>
                            </div>
                            <div class="card-body">
                                <p class="card-text"><?php echo nl2br(htmlspecialchars($reply['message'])); ?></p>
                                <?php if (!empty($reply['attachment'])): ?>
                                    <div class="mt-3">
                                        <i class="fa fa-paperclip"></i> <a href="<?php echo url($reply['attachment']); ?>" target="_blank">View Attachment</a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted mb-4">No replies yet.</p>
                <?php endif; ?>

                <!-- Reply Form -->
                <?php if ($ticket['status'] !== 'closed'): ?>
                    <div class="card mt-4">
                        <div class="card-header">
                            <strong>Add Reply</strong>
                        </div>
                        <div class="card-body">
                            <form action="<?php echo url('admin/tickets/reply/' . $ticket['id']); ?>" method="POST" enctype="multipart/form-data">
                                <div class="form-group">
                                    <textarea class="form-control" name="message" rows="4" placeholder="Type your reply here..." required></textarea>
                                </div>
                                <div class="form-group mt-2">
                                    <label for="attachment">Attachment</label>
                                    <input type="file" class="form-control-file" id="attachment" name="attachment">
                                </div>
                                <button type="submit" class="btn btn-primary mt-3">Post Reply</button>
                            </form>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning mt-4">
                        This ticket is closed. Please open a new ticket if you need further assistance.
                    </div>
                <?php endif; ?>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">Ticket Info</div>
                    <div class="card-body">
                        <p><strong>Status:</strong>
                            <span class="badge badge-<?php
                                                        echo $ticket['status'] == 'open' ? 'success' : ($ticket['status'] == 'closed' ? 'secondary' : 'primary');
                                                        ?>">
                                <?php echo ucfirst(str_replace('_', ' ', $ticket['status'])); ?>
                            </span>
                        </p>
                        <p><strong>Priority:</strong> <?php echo ucfirst($ticket['priority']); ?></p>
                        <p><strong>Created:</strong> <?php echo date('M d, Y', strtotime($ticket['created_at'])); ?></p>
                        <p><strong>Last Updated:</strong> <?php echo date('M d, Y', strtotime($ticket['updated_at'])); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once APP_ROOT . '/views/admin/inc/footer.php'; ?>