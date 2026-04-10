<?php
/**
 * Support Ticket Details View
 */
$ticket = $ticket ?? [];
$responses = $responses ?? [];
$page_title = $page_title ?? 'Ticket Details';
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
                <h2 class="mb-1">Ticket #<?php echo htmlspecialchars($ticket['ticket_number'] ?? '-'); ?></h2>
                <p class="text-muted mb-0"><?php echo htmlspecialchars($ticket['subject'] ?? '-'); ?></p>
            </div>
            <div>
                <a href="<?php echo $base; ?>/admin/support_tickets/edit/<?php echo $ticket['id']; ?>" class="btn btn-warning me-2">Edit</a>
                <a href="<?php echo $base; ?>/admin/support_tickets" class="btn btn-outline-secondary">Back</a>
            </div>
        </div>
        
        <?php if (!empty($ticket)): ?>
        <div class="row">
            <div class="col-md-8">
                <!-- Ticket Details -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Ticket Information</h5>
                        <span class="badge bg-<?php echo ($ticket['status'] ?? '') === 'open' ? 'danger' : (($ticket['status'] ?? '') === 'in_progress' ? 'warning' : (($ticket['status'] ?? '') === 'resolved' ? 'success' : 'secondary')); ?>">
                            <?php echo ucfirst(str_replace('_', ' ', $ticket['status'] ?? 'unknown')); ?>
                        </span>
                    </div>
                    <div class="card-body">
                        <p class="text-muted"><?php echo nl2br(htmlspecialchars($ticket['description'] ?? '-')); ?></p>
                        <hr>
                        <div class="row">
                            <div class="col-md-4">
                                <small class="text-muted">Priority</small>
                                <p><span class="badge bg-<?php echo ($ticket['priority'] ?? '') === 'urgent' ? 'danger' : (($ticket['priority'] ?? '') === 'high' ? 'warning' : (($ticket['priority'] ?? '') === 'medium' ? 'info' : 'secondary')); ?>">
                                    <?php echo ucfirst($ticket['priority'] ?? 'low'); ?>
                                </span></p>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted">Category</small>
                                <p><?php echo ucfirst($ticket['category'] ?? 'general'); ?></p>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted">Created</small>
                                <p><?php echo isset($ticket['created_at']) ? date('M d, Y H:i', strtotime($ticket['created_at'])) : '-'; ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Responses -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-comments me-2"></i>Conversation</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($responses)): ?>
                            <div class="list-group list-group-flush mb-4">
                                <?php foreach ($responses as $response): ?>
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between">
                                            <strong><?php echo htmlspecialchars($response['responder_name'] ?? 'System'); ?></strong>
                                            <small class="text-muted"><?php echo isset($response['created_at']) ? date('M d, Y H:i', strtotime($response['created_at'])) : '-'; ?></small>
                                        </div>
                                        <p class="mb-0 mt-2"><?php echo nl2br(htmlspecialchars($response['message'] ?? '-')); ?></p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted mb-4">No responses yet</p>
                        <?php endif; ?>
                        
                        <!-- Add Response -->
                        <form id="responseForm">
                            <div class="mb-3">
                                <textarea class="form-control" rows="3" placeholder="Add a response..." id="responseMessage"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-2"></i>Send Response
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <!-- Customer Info -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-user me-2"></i>Customer</h5>
                    </div>
                    <div class="card-body">
                        <h6><?php echo htmlspecialchars($ticket['customer_name'] ?? '-'); ?></h6>
                        <p class="text-muted small"><?php echo htmlspecialchars($ticket['customer_email'] ?? '-'); ?></p>
                        <p class="text-muted small"><?php echo htmlspecialchars($ticket['customer_phone'] ?? '-'); ?></p>
                    </div>
                </div>
                
                <!-- Assigned Agent -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-user-tie me-2"></i>Assigned To</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($ticket['assigned_agent_name'])): ?>
                            <h6><?php echo htmlspecialchars(ticket['assigned_agent_name'] ?? ''); ?></h6>
                            <p class="text-muted small"><?php echo htmlspecialchars($ticket['assigned_agent_email'] ?? '-'); ?></p>
                        <?php else: ?>
                            <p class="text-muted">Unassigned</p>
                            <button class="btn btn-sm btn-outline-primary w-100">Assign to Me</button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="alert alert-warning">Ticket not found.</div>
        <?php endif; ?>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('responseForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const message = document.getElementById('responseMessage').value;
            if (!message.trim()) {
                alert('Please enter a message');
                return;
            }
            // AJAX submission would go here
            alert('Response submitted!');
            location.reload();
        });
    </script>
</body>
</html>
