<?php include __DIR__ . "/../../../layouts/admin_header.php"; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-home"></i> Index</h2>
                <div>
                    <a href="/admin/resell-properties" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Resell Properties
                    </a>
                    <a href="/admin/resell-properties/create" class="btn btn-primary">
                        <i class="fas fa-plus"></i> New Property
                    </a>
                    <a href="/admin/commission" class="btn btn-info">
                        <i class="fas fa-coins"></i> Commission
                    </a>
                </div>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Index Management - Complete Resell Property System
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Total Properties</h5>
                                    <h3>1</h3>
                                    <small>All Resell Properties</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Active</h5>
                                    <h3>1</h3>
                                    <small>Active Listings</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Featured</h5>
                                    <h3>1</h3>
                                    <small>Featured Properties</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Pending Commission</h5>
                                    <h3>₹56,000</h3>
                                    <small>Commission Amount</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="table-responsive mt-4">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Property Title</th>
                                    <th>Type</th>
                                    <th>Seller</th>
                                    <th>Location</th>
                                    <th>Expected Price</th>
                                    <th>Status</th>
                                    <th>Commission</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Premium Residential Plot in Suryoday Colony</td>
                                    <td><span class="badge bg-primary">Residential</span></td>
                                    <td>Rahul Sharma</td>
                                    <td>Suryoday Colony, Gorakhpur</td>
                                    <td><strong>₹28,00,000</strong></td>
                                    <td><span class="badge bg-success">Active</span></td>
                                    <td><span class="badge bg-info">2%</span></td>
                                    <td>
                                        <a href="/admin/resell-properties/view/1" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
                                        <a href="/admin/resell-properties/edit/1" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                                        <a href="/admin/resell-properties/commission/1" class="btn btn-sm btn-success"><i class="fas fa-coins"></i></a>
                                        <a href="/admin/resell-properties/status/1" class="btn btn-sm btn-primary"><i class="fas fa-sync"></i></a>
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