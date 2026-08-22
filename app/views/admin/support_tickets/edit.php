<?php
$ticket = $ticket ?? [];
$users = $users ?? [];
$users = $users ?? [];
$page_title = $page_title ?? 'Edit Support Ticket';
$base = defined('BASE_URL') ? BASE_URL : '/' . trim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
?>
<div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1">Edit Ticket #<?php echo htmlspecialchars($ticket['ticket_number'] ?? '-'); ?></h2>
                <p class="text-muted mb-0">Update ticket details and status</p>
            </div>
            <a href="<?php echo e($base); ?>/admin/support-tickets" class="btn btn-outline-secondary">Back to Tickets</a>
        </div>
        
        <?php if (!empty($ticket)): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body aps-cp-card-body">
                <form id="editTicketForm" action="<?php echo e($base); ?>/admin/support-tickets/<?php echo $ticket['id']; ?>/update" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Customer</label>
                            <select name="customer_id" class="form-select">
                                <?php foreach ($users as $customer): ?>
                                    <option value="<?php echo e($customer['id']); ?>" <?php echo ($ticket['customer_id'] ?? '') == $customer['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($customer['name'] . ' (' . $customer['email'] . ')'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Assigned To</label>
                            <select name="assigned_agent_id" class="form-select">
                                <option value="">Unassigned</option>
                                <?php foreach ($users as $agent): ?>
                                    <option value="<?php echo e($agent['id']); ?>" <?php echo ($ticket['assigned_agent_id'] ?? '') == $agent['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars(agent['name'] ?? ''); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Subject</label>
                        <input type="text" name="subject" class="form-control" value="<?php echo htmlspecialchars($ticket['subject'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="4" required><?php echo htmlspecialchars($ticket['description'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Category</label>
                            <select name="category" class="form-select">
                                <option value="general" <?php echo ($ticket['category'] ?? '') === 'general' ? 'selected' : ''; ?>>General</option>
                                <option value="technical" <?php echo ($ticket['category'] ?? '') === 'technical' ? 'selected' : ''; ?>>Technical</option>
                                <option value="billing" <?php echo ($ticket['category'] ?? '') === 'billing' ? 'selected' : ''; ?>>Billing</option>
                                <option value="booking" <?php echo ($ticket['category'] ?? '') === 'booking' ? 'selected' : ''; ?>>Booking</option>
                                <option value="complaint" <?php echo ($ticket['category'] ?? '') === 'complaint' ? 'selected' : ''; ?>>Complaint</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Priority</label>
                            <select name="priority" class="form-select">
                                <option value="low" <?php echo ($ticket['priority'] ?? '') === 'low' ? 'selected' : ''; ?>>Low</option>
                                <option value="medium" <?php echo ($ticket['priority'] ?? '') === 'medium' ? 'selected' : ''; ?>>Medium</option>
                                <option value="high" <?php echo ($ticket['priority'] ?? '') === 'high' ? 'selected' : ''; ?>>High</option>
                                <option value="urgent" <?php echo ($ticket['priority'] ?? '') === 'urgent' ? 'selected' : ''; ?>>Urgent</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="open" <?php echo ($ticket['status'] ?? '') === 'open' ? 'selected' : ''; ?>>Open</option>
                                <option value="in_progress" <?php echo ($ticket['status'] ?? '') === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                <option value="resolved" <?php echo ($ticket['status'] ?? '') === 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                                <option value="closed" <?php echo ($ticket['status'] ?? '') === 'closed' ? 'selected' : ''; ?>>Closed</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between mt-4">
                        <a href="<?php echo e($base); ?>/admin/support_tickets/show/<?php echo $ticket['id']; ?>" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Update Ticket
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <?php else: ?>
        <div class="alert alert-warning">Ticket not found.</div>
        <?php endif; ?>
    </div>
    
    
    <script>
        document.getElementById('editTicketForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            showLoader();
            fetch(this.action, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Ticket updated successfully!', 'success');
                    .catch(err => console.error('Request failed:', err));
                    window.location.href = '<?php echo e($base); ?>/admin/support_tickets/show/<?php echo $ticket['id'] ?? 0; ?>';
                } else {
                    showToast(data.message || 'Failed to update ticket', 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('An error occurred', 'danger');
            ).finally(() => hideLoader());
        });
    </script>

