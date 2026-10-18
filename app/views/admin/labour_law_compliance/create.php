<?php
$page_title = 'Add Labour Law Compliance';
$csrf = \App\Helpers\SecurityHelper::generateCSRFToken();
?>
<div class="container-fluid">
    <h1 class="h3 mb-3">Add Labour Law Compliance</h1>
    <form method="post" action="/admin/labour-law-compliance/store">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
        <div class="mb-3">
            <label class="form-label">Law Name</label>
            <input type="text" name="law_name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Compliance Type</label>
            <input type="text" name="compliance_type" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Registration Number</label>
            <input type="text" name="registration_no" class="form-control">
        </div>
        <div class="mb-3">
            <label class="form-label">Authority</label>
            <input type="text" name="authority" class="form-control">
        </div>
        <div class="mb-3">
            <label class="form-label">Applicable Unit</label>
            <input type="text" name="applicable_unit" class="form-control">
        </div>
        <div class="mb-3">
            <label class="form-label">Frequency</label>
            <select name="frequency" class="form-select">
                <option value="monthly">Monthly</option>
                <option value="quarterly">Quarterly</option>
                <option value="half_yearly">Half Yearly</option>
                <option value="annual" selected>Annual</option>
                <option value="one_time">One Time</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Due Date</label>
            <input type="date" name="due_date" class="form-control">
        </div>
        <div class="mb-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="pending">Pending</option>
                <option value="filed">Filed</option>
                <option value="overdue">Overdue</option>
                <option value="not_applicable">Not Applicable</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Remarks</label>
            <textarea name="remarks" class="form-control"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Save</button>
        <a href="/admin/labour-law-compliance" class="btn btn-secondary">Cancel</a>
    </form>
</div>
