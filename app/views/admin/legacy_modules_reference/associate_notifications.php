<?php
// Associate Notifications UI - Scaffold
require_once __DIR__ . '/includes/header.php';
?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<div class="container mt-4">
    <h2>Notifications</h2>
    <div class="alert alert-info">All important updates and notifications for associates will appear here.</div>
    <ul class="list-group mb-4">
        <li class="list-group-item list-group-item-success">
            <strong>Congratulations!</strong> You have achieved the Silver Reward Slab this month!
            <span class="badge bg-success float-end">New</span>
        </li>
        <li class="list-group-item">
            Your payout for March 2025 has been processed and credited to your bank account.
            <span class="badge bg-info float-end">Info</span>
        </li>
        <li class="list-group-item list-group-item-warning">
            Reminder: Update your KYC details to avoid payout delays.
            <span class="badge bg-warning float-end">Action Needed</span>
        </li>
        <!-- More notifications will be loaded dynamically -->
    </ul>
    <div class="text-end">
        <button class="btn btn-outline-primary">Mark all as read</button>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
