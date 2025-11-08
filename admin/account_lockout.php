<?php
// Admin Account Lockout Management - Scaffold
?>
<html>
<body>
    <?php include __DIR__ . '/../includes/templates/dynamic_header.php'; ?>
    <div class="container mt-4">
        <h2>Account Lockout Management</h2>
        <div class="alert alert-info">Protect accounts from brute-force attacks by locking after too many failed login attempts.</div>
        <form method="post" action="">
            <div class="mb-3">
                <label for="lockout_threshold" class="form-label">Failed Login Attempts Before Lockout</label>
                <input type="number" class="form-control" id="lockout_threshold" name="lockout_threshold" value="5" min="3" max="10">
            </div>
            <div class="mb-3">
                <label for="lockout_duration" class="form-label">Lockout Duration (minutes)</label>
                <input type="number" class="form-control" id="lockout_duration" name="lockout_duration" value="15" min="1" max="60">
            </div>
            <button type="submit" class="btn btn-primary">Save Settings</button>
        </form>
        <div class="mt-4">
            <h5>Locked Accounts</h5>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Username/Email</th>
                        <th>Lockout Time</th>
                        <th>Unlock</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>demo.user@example.com</td>
                        <td>2025-04-16 02:30:00</td>
                        <td><button class="btn btn-sm btn-success">Unlock</button></td>
                    </tr>
                    <!-- More rows will be populated dynamically -->
                </tbody>
            </table>
        </div>
    </div>
    <?php include __DIR__ . '/../includes/templates/new_footer.php'; ?>
</body>
</html>
