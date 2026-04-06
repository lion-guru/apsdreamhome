<?php include __DIR__ . "/../layouts/header.php"; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-header">
                <h1 class="h2 mb-4">
                    <i class="fas fa-credit-card"></i> Index
                </h1>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Index - APS Dream Home Payment System
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Total Payments</h5>
                                    <h3>2</h3>
                                    <small>All Payments</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Successful</h5>
                                    <h3>2</h3>
                                    <small>Completed Payments</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Pending</h5>
                                    <h3>0</h3>
                                    <small>Pending Payments</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Total Amount</h5>
                                    <h3>₹2,95,000</h3>
                                    <small>Total Revenue</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="table-responsive mt-4">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Payment ID</th>
                                    <th>Customer</th>
                                    <th>Property</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Gateway</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>PAY001</td>
                                    <td>Rahul Sharma</td>
                                    <td>Plot A-101</td>
                                    <td><span class="badge bg-primary">Booking</span></td>
                                    <td><strong>₹59,000</strong></td>
                                    <td><span class="badge bg-success">Razorpay</span></td>
                                    <td><span class="badge bg-success">Completed</span></td>
                                    <td>2024-04-10</td>
                                    <td>
                                        <a href="/payment/view/PAY001" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
                                        <a href="/payment/refund/PAY001" class="btn btn-sm btn-warning"><i class="fas fa-undo"></i></a>
                                    </td>
                                </tr>
                                <tr>
                                    <td>PAY002</td>
                                    <td>Priya Singh</td>
                                    <td>Suryoday Heights</td>
                                    <td><span class="badge bg-warning">Down Payment</span></td>
                                    <td><strong>₹2,36,000</strong></td>
                                    <td><span class="badge bg-info">Paytm</span></td>
                                    <td><span class="badge bg-success">Completed</span></td>
                                    <td>2024-04-09</td>
                                    <td>
                                        <a href="/payment/view/PAY002" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
                                        <a href="/payment/refund/PAY002" class="btn btn-sm btn-warning"><i class="fas fa-undo"></i></a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . "/../layouts/footer.php"; ?>