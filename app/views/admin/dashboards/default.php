<?php
// Fallback Admin Dashboard
$this->layout('layouts/admin');
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Welcome to Admin Dashboard</h3>
                </div>
                <div class="card-body">
                    <p>Welcome back, <?php echo h($user_role); ?>!</p>
                    <p>Select an option from the menu to get started.</p>
                </div>
            </div>
        </div>
    </div>
</div>