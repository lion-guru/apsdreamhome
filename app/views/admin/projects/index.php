<?php include __DIR__ . "/../../../layouts/admin_header.php"; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-building"></i> Index</h2>
                <div>
                    <a href="/admin/projects" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Projects
                    </a>
                    <a href="/admin/projects/create" class="btn btn-primary">
                        <i class="fas fa-plus"></i> New Project
                    </a>
                </div>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Index Management - Complete Project Management System
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Total Projects</h5>
                                    <h3>3</h3>
                                    <small>All Projects</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Active Projects</h5>
                                    <h3>2</h3>
                                    <small>Under Construction</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Planning</h5>
                                    <h3>1</h3>
                                    <small>In Planning Phase</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Featured</h5>
                                    <h3>2</h3>
                                    <small>Featured Projects</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="table-responsive mt-4">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Project Name</th>
                                    <th>Type</th>
                                    <th>Developer</th>
                                    <th>Location</th>
                                    <th>Status</th>
                                    <th>Featured</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Suryoday Heights Phase 1</td>
                                    <td><span class="badge bg-primary">Residential</span></td>
                                    <td>APS Developers</td>
                                    <td>Suryoday Colony, Gorakhpur</td>
                                    <td><span class="badge bg-warning">Under Construction</span></td>
                                    <td><i class="fas fa-star text-warning"></i></td>
                                    <td>
                                        <a href="/admin/projects/view/1" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
                                        <a href="/admin/projects/edit/1" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                                        <a href="/admin/projects/status/1" class="btn btn-sm btn-success"><i class="fas fa-sync"></i></a>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Braj Radha Enclave</td>
                                    <td><span class="badge bg-primary">Residential</span></td>
                                    <td>Braj Properties</td>
                                    <td>Braj Radha Nagri, Deoria</td>
                                    <td><span class="badge bg-warning">Under Construction</span></td>
                                    <td><i class="far fa-star text-muted"></i></td>
                                    <td>
                                        <a href="/admin/projects/view/2" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
                                        <a href="/admin/projects/edit/2" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                                        <a href="/admin/projects/status/2" class="btn btn-sm btn-success"><i class="fas fa-sync"></i></a>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Raghunath City Center</td>
                                    <td><span class="badge bg-secondary">Mixed</span></td>
                                    <td>Raghunath Developers</td>
                                    <td>Raghunath Nagri, Gorakhpur</td>
                                    <td><span class="badge bg-info">Planning</span></td>
                                    <td><i class="fas fa-star text-warning"></i></td>
                                    <td>
                                        <a href="/admin/projects/view/3" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
                                        <a href="/admin/projects/edit/3" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                                        <a href="/admin/projects/status/3" class="btn btn-sm btn-success"><i class="fas fa-sync"></i></a>
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