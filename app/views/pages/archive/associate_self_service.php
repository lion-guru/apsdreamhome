<?php
// Associate Self-Service Tools - Scaffold
require_once __DIR__ . '/includes/header.php';
?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<div class="container mt-4">
    <h2>Self-Service Tools</h2>
    <div class="alert alert-info">Password change, KYC update, and other tools will be available here.</div>
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">Change Password</div>
                <div class="card-body">
                    <form method="post" action="">
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password</label>
                            <input type="password" class="form-control" id="current_password" name="current_password" required>
                        </div>
                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" required>
                        </div>
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Change Password</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">Update KYC / Bank Details</div>
                <div class="card-body">
                    <form method="post" action="">
                        <div class="mb-3">
                            <label for="bank_account" class="form-label">Bank Account Number</label>
                            <input type="text" class="form-control" id="bank_account" name="bank_account" required>
                        </div>
                        <div class="mb-3">
                            <label for="ifsc" class="form-label">IFSC Code</label>
                            <input type="text" class="form-control" id="ifsc" name="ifsc" required>
                        </div>
                        <div class="mb-3">
                            <label for="pan" class="form-label">PAN Number</label>
                            <input type="text" class="form-control" id="pan" name="pan" required>
                        </div>
                        <div class="mb-3">
                            <label for="aadhaar" class="form-label">Aadhaar Number</label>
                            <input type="text" class="form-control" id="aadhaar" name="aadhaar" required>
                        </div>
                        <button type="submit" class="btn btn-success">Update KYC/Bank Details</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
