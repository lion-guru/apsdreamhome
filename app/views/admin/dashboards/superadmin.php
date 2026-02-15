<?php include APP_PATH . '/views/admin/layouts/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4"><i class="fas fa-user-shield me-2"></i>Superadmin Control Center</h2>
        </div>
    </div>

    <!-- System Overview -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white shadow">
                <div class="card-body">
                    <h5>Total Admins</h5>
                    <h3><?php echo $user_management_stats['total_admins']; ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white shadow">
                <div class="card-body">
                    <h5>Active Users</h5>
                    <h3><?php echo $user_management_stats['active_users']; ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Security Logs -->
    <div class="row">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><i class="fas fa-list me-2"></i>Recent Security Audit Logs</h5>
                </div>
                <div class="card-body">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Timestamp</th>
                                <th>Admin</th>
                                <th>Action</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><?php echo date('Y-m-d H:i:s'); ?></td>
                                <td>Superadmin</td>
                                <td>Dashboard Access</td>
                                <td><span class="badge bg-success">Success</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- AI System Health -->
        <div class="col-md-4">
            <div class="card shadow">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-robot me-2"></i>AI System Health</h5>
                </div>
                <div class="card-body">
                    <?php foreach ($ai_agents_status as $agent): ?>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <strong><?php echo $agent['name']; ?></strong><br>
                            <small class="text-muted"><?php echo $agent['type']; ?></small>
                        </div>
                        <span class="badge bg-success"><?php echo $agent['status']; ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include APP_PATH . '/views/admin/layouts/footer.php'; ?>
