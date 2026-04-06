<?php include __DIR__ . "/../layouts/admin_header.php"; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-header">
                <h1 class="h2 mb-4">
                    <i class="fas fa-bell"></i> Settings
                </h1>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Settings - Notification Management System
                    </div>
                    
                    <div class="row">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Email Sent Today</h5>
                                    <h3>25</h3>
                                    <small>Messages Delivered</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h5 class="card-title">SMS Sent Today</h5>
                                    <h3>18</h3>
                                    <small>Messages Delivered</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Failed Messages</h5>
                                    <h3>2</h3>
                                    <small>Need Attention</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Total Templates</h5>
                                    <h3>6</h3>
                                    <small>Active Templates</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <h5>Recent Email Logs</h5>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Recipient</th>
                                            <th>Subject</th>
                                            <th>Status</th>
                                            <th>Sent</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>rahul.sharma@example.com</td>
                                            <td>Payment Successful</td>
                                            <td><span class="badge bg-success">Delivered</span></td>
                                            <td>2 hours ago</td>
                                        </tr>
                                        <tr>
                                            <td>priya.singh@example.com</td>
                                            <td>Welcome to APS Dream Home</td>
                                            <td><span class="badge bg-success">Delivered</span></td>
                                            <td>1 day ago</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h5>Recent SMS Logs</h5>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Recipient</th>
                                            <th>Message</th>
                                            <th>Status</th>
                                            <th>Sent</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>+91-9876543210</td>
                                            <td>OTP: 123456</td>
                                            <td><span class="badge bg-success">Delivered</span></td>
                                            <td>1 hour ago</td>
                                        </tr>
                                        <tr>
                                            <td>+91-9876543220</td>
                                            <td>Payment successful</td>
                                            <td><span class="badge bg-success">Delivered</span></td>
                                            <td>3 hours ago</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . "/../layouts/admin_footer.php"; ?>