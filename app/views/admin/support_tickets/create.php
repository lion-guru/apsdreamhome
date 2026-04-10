<?php
/**
 * Create Support Ticket View
 */
$customers = $customers ?? [];
$agents = $agents ?? [];
$page_title = $page_title ?? 'Create Support Ticket';
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
                <h2 class="mb-1">Create Support Ticket</h2>
                <p class="text-muted mb-0">Create a new customer support ticket</p>
            </div>
            <a href="<?php echo $base; ?>/admin/support_tickets" class="btn btn-outline-secondary">Back to Tickets</a>
        </div>
        
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form id="ticketForm" action="<?php echo $base; ?>/admin/support_tickets/store" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Customer *</label>
                            <select name="customer_id" class="form-select" required>
                                <option value="">Select Customer</option>
                                <?php foreach ($customers as $customer): ?>
                                    <option value="<?php echo $customer['id']; ?>"><?php echo htmlspecialchars($customer['name'] . ' (' . $customer['email'] . ')'); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Assign To</label>
                            <select name="assigned_agent_id" class="form-select">
                                <option value="">Unassigned</option>
                                <?php foreach ($agents as $agent): ?>
                                    <option value="<?php echo $agent['id']; ?>"><?php echo htmlspecialchars(agent['name'] ?? ''); ?></option>
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
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
                    alert('Ticket created successfully! Ticket #: ' + data.ticket_number);
                    window.location.href = '<?php echo $base; ?>/admin/support_tickets';
                } else {
                    alert(data.message || 'Failed to create ticket');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred');
            });
        });
    </script>
</body>
</html>
