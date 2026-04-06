<?php include __DIR__ . "/../../../layouts/admin_header.php"; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-coins"></i> Index</h2>
                <div>
                    <a href="/admin/commission" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Commission
                    </a>
                    <a href="/admin/commission/create-rule" class="btn btn-primary">
                        <i class="fas fa-plus"></i> New Rule
                    </a>
                    <a href="/admin/resell-properties" class="btn btn-info">
                        <i class="fas fa-home"></i> Properties
                    </a>
                </div>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Index Management - Complete Commission System
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Total Rules</h5>
                                    <h3>1</h3>
                                    <small>Commission Rules</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Active Rules</h5>
                                    <h3>1</h3>
                                    <small>Active Commission Rules</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Pending Commission</h5>
                                    <h3>₹56,000</h3>
                                    <small>Commission Amount</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Paid Commission</h5>
                                    <h3>₹0</h3>
                                    <small>Commission Paid</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="table-responsive mt-4">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Rule Name</th>
                                    <th>Type</th>
                                    <th>Property Type</th>
                                    <th>Commission Rate</th>
                                    <th>Price Range</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Standard Residential Commission</td>
                                    <td><span class="badge bg-primary">Percentage</span></td>
                                    <td>Residential</td>
                                    <td><strong>2.00%</strong></td>
                                    <td>₹0 - ₹1,00,00,000</td>
                                    <td><span class="badge bg-success">Active</span></td>
                                    <td>
                                        <a href="/admin/commission/edit-rule/1" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                                        <a href="/admin/commission/calculations" class="btn btn-sm btn-info"><i class="fas fa-calculator"></i></a>
                                        <a href="/admin/commission/payments" class="btn btn-sm btn-success"><i class="fas fa-money-bill"></i></a>
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

<?php include __DIR__ . "/../../../layouts/admin_footer.php"; ?>