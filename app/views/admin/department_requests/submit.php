<?php
/**
 * Submit Department Request Form
 */
?>

<div class="row mb-4">
    <div class="col-12">
        <h1 class="h3 mb-0">Submit Department Request</h1>
        <p class="text-muted">Submit a request to any department for review and action</p>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="<?= BASE_URL ?>/admin/department-requests/submit">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Department *</label>
                    <select class="form-select" name="department_id" required>
                        <option value="">Select Department</option>
                        <?php foreach ($departments as $dept): ?>
                        <option value="<?= $dept['id'] ?>"><?= $dept['name'] ?> (<?= $dept['code'] ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">Request Type *</label>
                    <select class="form-select" name="request_type" required>
                        <option value="inquiry">Inquiry</option>
                        <option value="verification">Verification</option>
                        <option value="approval">Approval</option>
                        <option value="escalation">Escalation</option>
                        <option value="info_request">Info Request</option>
                    </select>
                </div>
            </div>
            
            <div class="row g-3 mt-2">
                <div class="col-12">
                    <label class="form-label">Title *</label>
                    <input type="text" class="form-control" name="title" placeholder="Brief description of the request" required>
                </div>
            </div>
            
            <div class="row g-3 mt-2">
                <div class="col-12">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" name="description" rows="4" placeholder="Detailed description of the request..."></textarea>
                </div>
            </div>
            
            <div class="row g-3 mt-2">
                <div class="col-md-4">
                    <label class="form-label">Priority</label>
                    <select class="form-select" name="priority">
                        <option value="medium" selected>Medium</option>
                        <option value="low">Low</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                    </select>
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">Related Entity Type (Optional)</label>
                    <select class="form-select" name="related_entity_type">
                        <option value="">None</option>
                        <option value="booking">Booking</option>
                        <option value="lead">Lead</option>
                        <option value="property">Property</option>
                        <option value="user">User</option>
                        <option value="payment">Payment</option>
                    </select>
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">Related Entity ID (Optional)</label>
                    <input type="number" class="form-control" name="related_entity_id" placeholder="Entity ID">
                </div>
            </div>
            
            <div class="row g-3 mt-2">
                <div class="col-md-6">
                    <label class="form-label">Due Date (Optional)</label>
                    <input type="date" class="form-control" name="due_date">
                </div>
            </div>
            
            <div class="row mt-4">
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Submit Request</button>
                    <a href="<?= BASE_URL ?>/admin/department-requests" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>