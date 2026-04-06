<?php include __DIR__ . "/../../../layouts/admin_header.php"; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-building"></i> Create</h2>
                <div>
                    <a href="/admin/projects" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Projects
                    </a>
                </div>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Create Management - Complete Project Management System
                    </div>
                    <form method="POST" action="/admin/projects/create">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Project Name *</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="project_type" class="form-label">Project Type *</label>
                                    <select class="form-select" id="project_type" name="project_type" required>
                                        <option value="">Select Type</option>
                                        <option value="residential">Residential</option>
                                        <option value="commercial">Commercial</option>
                                        <option value="industrial">Industrial</option>
                                        <option value="mixed">Mixed</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="developer_name" class="form-label">Developer Name *</label>
                                    <input type="text" class="form-control" id="developer_name" name="developer_name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="developer_contact" class="form-label">Developer Contact *</label>
                                    <input type="text" class="form-control" id="developer_contact" name="developer_contact" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="colony_name" class="form-label">Colony Name *</label>
                                    <input type="text" class="form-control" id="colony_name" name="colony_name" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="district_name" class="form-label">District Name *</label>
                                    <input type="text" class="form-control" id="district_name" name="district_name" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="state_name" class="form-label">State Name *</label>
                                    <input type="text" class="form-control" id="state_name" name="state_name" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="total_plots" class="form-label">Total Plots *</label>
                                    <input type="number" class="form-control" id="total_plots" name="total_plots" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="available_plots" class="form-label">Available Plots *</label>
                                    <input type="number" class="form-control" id="available_plots" name="available_plots" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="sold_plots" class="form-label">Sold Plots *</label>
                                    <input type="number" class="form-control" id="sold_plots" name="sold_plots" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="price_range_min" class="form-label">Min Price (₹) *</label>
                                    <input type="number" class="form-control" id="price_range_min" name="price_range_min" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="price_range_max" class="form-label">Max Price (₹) *</label>
                                    <input type="number" class="form-control" id="price_range_max" name="price_range_max" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="launch_date" class="form-label">Launch Date *</label>
                                    <input type="date" class="form-control" id="launch_date" name="launch_date" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="completion_date" class="form-label">Completion Date *</label>
                                    <input type="date" class="form-control" id="completion_date" name="completion_date" required>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="4"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="amenities" class="form-label">Amenities</label>
                            <textarea class="form-control" id="amenities" name="amenities" rows="3" placeholder="Enter amenities separated by commas"></textarea>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured">
                                <label class="form-check-label" for="is_featured">
                                    Featured Project
                                </label>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between">
                            <a href="/admin/projects" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Project
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . "/../../../layouts/admin_footer.php"; ?>