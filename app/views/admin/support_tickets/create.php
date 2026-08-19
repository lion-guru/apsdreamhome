<?php
$users = $users ?? [];
$users = $users ?? [];
$page_title = $page_title ?? 'Create Support Ticket';
$base = defined('BASE_URL') ? BASE_URL : '/' . trim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
?>
<div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1">Create Support Ticket</h2>
                <p class="text-muted mb-0">Create a new customer support ticket</p>
            </div>
            <a href="<?php echo $base; ?>/admin/support-tickets" class="btn btn-outline-secondary">Back to Tickets</a>
        </div>
        
        <div class="card border-0 shadow-sm">
            <div class="card-body aps-cp-card-body">
                <form id="ticketForm" action="<?php echo $base; ?>/admin/support-tickets/store" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Customer *</label>
                            <select name="customer_id" class="form-select" required>
                                <option value="">Select Customer</option>
                                <?php foreach ($users as $customer): ?>
                                    <option value="<?php echo $customer['id']; ?>"><?php echo htmlspecialchars($customer['name'] . ' (' . $customer['email'] . ')'); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Assign To</label>
                            <select name="assigned_agent_id" class="form-select">
                                <option value="">Unassigned</option>
                                <?php foreach ($users as $agent): ?>
                                    <option value="<?php echo $agent['id']; ?>"><?php echo htmlspecialchars($agent['name'] ?? ''); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Subject *</label>
                            <input type="text" name="subject" class="form-control" placeholder="Brief description of the issue" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Category</label>
                            <select name="category" class="form-select">
                                <option value="general">General</option>
                                <option value="technical">Technical</option>
                                <option value="billing">Billing</option>
                                <option value="booking">Booking</option>
                                <option value="complaint">Complaint</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description *</label>
                        <textarea name="description" class="form-control" rows="5" placeholder="Detailed description of the issue" required></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Priority *</label>
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input type="radio" name="priority" value="low" class="form-check-input" id="priorityLow">
                                <label class="form-check-label" for="priorityLow"><span class="badge bg-secondary">Low</span></label>
                            </div>
                            <div class="form-check">
                                <input type="radio" name="priority" value="medium" class="form-check-input" id="priorityMedium" checked>
                                <label class="form-check-label" for="priorityMedium"><span class="badge bg-info">Medium</span></label>
                            </div>
                            <div class="form-check">
                                <input type="radio" name="priority" value="high" class="form-check-input" id="priorityHigh">
                                <label class="form-check-label" for="priorityHigh"><span class="badge bg-warning">High</span></label>
                            </div>
                            <div class="form-check">
                                <input type="radio" name="priority" value="urgent" class="form-check-input" id="priorityUrgent">
                                <label class="form-check-label" for="priorityUrgent"><span class="badge bg-danger">Urgent</span></label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between mt-4">
                        <a href="<?php echo $base; ?>/admin/support_tickets" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Create Ticket
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    
    <script>
        document.getElementById('ticketForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            fetch(this.action, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Ticket created successfully! Ticket #: ' + data.ticket_number, 'success');
                    window.location.href = '<?php echo $base; ?>/admin/support_tickets';
                } else {
                    showToast(data.message || 'Failed to create ticket', 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('An error occurred', 'danger');
            });
        });
    </script>

