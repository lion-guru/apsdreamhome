<?php include __DIR__ . "/../../../layouts/admin_header.php"; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-users"></i> Dashboard</h2>
                <div>
                    <a href="/admin/mlm" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Dashboard
                    </a>
                </div>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Dashboard Management - Complete MLM Associate System with 7 Levels
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Total Associates</h5>
                                    <h3>7</h3>
                                    <small>Active Network</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Active Associates</h5>
                                    <h3>6</h3>
                                    <small>1 Pending</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Total Commission</h5>
                                    <h3>₹31,000</h3>
                                    <small>This Month</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Network Depth</h5>
                                    <h3>4 Levels</h3>
                                    <small>Max Hierarchy</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5><i class="fas fa-chart-line"></i> Commission Structure</h5>
                                </div>
                                <div class="card-body">
                                    <ul class="list-group">
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span>Direct Business</span>
                                            <strong>10%</strong>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span>Junior Business</span>
                                            <strong>5%</strong>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span>Team Override</span>
                                            <strong>3%</strong>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span>Leadership Bonus</span>
                                            <strong>2%</strong>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5><i class="fas fa-trophy"></i> Level Progression</h5>
                                </div>
                                <div class="card-body">
                                    <div class="progress mb-2">
                                        <div class="progress-bar" style="width: 14%">Associate</div>
                                    </div>
                                    <div class="progress mb-2">
                                        <div class="progress-bar bg-success" style="width: 28%">Senior Associate</div>
                                    </div>
                                    <div class="progress mb-2">
                                        <div class="progress-bar bg-info" style="width: 42%">Team Leader</div>
                                    </div>
                                    <div class="progress mb-2">
                                        <div class="progress-bar bg-warning" style="width: 57%">Sr. Team Leader</div>
                                    </div>
                                    <div class="progress mb-2">
                                        <div class="progress-bar bg-danger" style="width: 71%">Manager</div>
                                    </div>
                                    <div class="progress mb-2">
                                        <div class="progress-bar bg-dark" style="width: 85%">Sr. Manager</div>
                                    </div>
                                    <div class="progress">
                                        <div class="progress-bar bg-primary" style="width: 100%">Director</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . "/../../../layouts/admin_footer.php"; ?>