<?php include __DIR__ . "/../layouts/header.php"; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-header">
                <h1 class="h2 mb-4">
                    <i class="fas fa-user"></i> Dashboard
                </h1>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Dashboard - Customer Dashboard
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Profile Completion</h5>
                                    <h3>85%</h3>
                                    <small>Almost Complete</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Wishlist Items</h5>
                                    <h3>3</h3>
                                    <small>Saved Properties</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Inquiries</h5>
                                    <h3>2</h3>
                                    <small>Active Inquiries</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Documents</h5>
                                    <h3>2</h3>
                                    <small>Uploaded</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <h5>Recent Activity</h5>
                            <div class="list-group">
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between">
                                        <h6 class="mb-1">Added property to wishlist</h6>
                                        <small>2 hours ago</small>
                                    </div>
                                    <p class="mb-1">Premium Plot in Suryoday Colony</p>
                                </div>
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between">
                                        <h6 class="mb-1">Submitted inquiry</h6>
                                        <small>1 day ago</small>
                                    </div>
                                    <p class="mb-1">Information about residential plots</p>
                                </div>
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between">
                                        <h6 class="mb-1">Updated profile</h6>
                                        <small>3 days ago</small>
                                    </div>
                                    <p class="mb-1">Updated contact information</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h5>Recommended Properties</h5>
                            <div class="card mb-2">
                                <div class="card-body">
                                    <h6 class="card-title">Residential Plot in Braj Radha</h6>
                                    <p class="card-text">1000 sqft plot near temple</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-primary fw-bold">₹15,00,000</span>
                                        <a href="/properties/1" class="btn btn-sm btn-outline-primary">View</a>
                                    </div>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="card-title">Commercial Space in Raghunath</h6>
                                    <p class="card-text">1200 sqft commercial space</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-primary fw-bold">₹24,00,000</span>
                                        <a href="/properties/2" class="btn btn-sm btn-outline-primary">View</a>
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

<?php include __DIR__ . "/../layouts/footer.php"; ?>