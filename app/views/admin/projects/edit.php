

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-building"></i> Edit Project: <?php echo htmlspecialchars(project['name'] ?? ''); ?></h2>
                <div>
                    <a href="/admin/projects" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Projects
                    </a>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <form method="POST" action="/admin/projects/update/<?php echo $project['id']; ?>">
                        <h5 class="mb-3">Basic Information</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Project Name *</label>
                                    <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars(project['name'] ?? ''); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="project_type" class="form-label">Project Type *</label>
                                    <select class="form-select" id="project_type" name="project_type" required>
                                        <option value="">Select Type</option>
                                        <option value="residential" <?php echo $project['project_type'] === 'residential' ? 'selected' : ''; ?>>Residential</option>
                                        <option value="commercial" <?php echo $project['project_type'] === 'commercial' ? 'selected' : ''; ?>>Commercial</option>
                                        <option value="industrial" <?php echo $project['project_type'] === 'industrial' ? 'selected' : ''; ?>>Industrial</option>
                                        <option value="mixed" <?php echo $project['project_type'] === 'mixed' ? 'selected' : ''; ?>>Mixed</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars(project['description'] ?? ''); ?></textarea>
                        </div>

                        <h5 class="mb-3 mt-4">Developer Information</h5>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="developer_name" class="form-label">Developer Name</label>
                                    <input type="text" class="form-control" id="developer_name" name="developer_name" value="<?php echo htmlspecialchars(project['developer_name'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="developer_contact" class="form-label">Developer Contact</label>
                                    <input type="text" class="form-control" id="developer_contact" name="developer_contact" value="<?php echo htmlspecialchars(project['developer_contact'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="developer_phone" class="form-label">Developer Phone</label>
                                    <input type="text" class="form-control" id="developer_phone" name="developer_phone" value="<?php echo htmlspecialchars(project['developer_phone'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>

                        <h5 class="mb-3 mt-4">Location</h5>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="state_id" class="form-label">State</label>
                                    <select class="form-select" id="state_id" name="state_id">
                                        <option value="">Select State</option>
                                        <?php foreach ($states as $state): ?>
                                            <option value="<?php echo $state['id']; ?>" <?php echo $project['state_id'] == $state['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars(state['name'] ?? ''); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="district_id" class="form-label">District</label>
                                    <select class="form-select" id="district_id" name="district_id">
                                        <option value="">Select District</option>
                                        <?php foreach ($districts as $district): ?>
                                            <option value="<?php echo $district['id']; ?>" <?php echo $project['district_id'] == $district['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars(district['name'] ?? ''); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="colony_id" class="form-label">Colony</label>
                                    <select class="form-select" id="colony_id" name="colony_id">
                                        <option value="">Select Colony</option>
                                        <?php foreach ($colonies as $colony): ?>
                                            <option value="<?php echo $colony['id']; ?>" <?php echo $project['colony_id'] == $colony['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars(colony['name'] ?? ''); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <textarea class="form-control" id="address" name="address" rows="2"><?php echo htmlspecialchars(project['address'] ?? ''); ?></textarea>
                        </div>

                        <h5 class="mb-3 mt-4">Project Details</h5>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="total_area" class="form-label">Total Area (sq ft)</label>
                                    <input type="number" class="form-control" id="total_area" name="total_area" step="0.01" value="<?php echo $project['total_area']; ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="total_plots" class="form-label">Total Plots</label>
                                    <input type="number" class="form-control" id="total_plots" name="total_plots" value="<?php echo $project['total_plots']; ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="available_plots" class="form-label">Available Plots</label>
                                    <input type="number" class="form-control" id="available_plots" name="available_plots" value="<?php echo $project['available_plots']; ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="booked_plots" class="form-label">Booked Plots</label>
                                    <input type="number" class="form-control" id="booked_plots" name="booked_plots" value="<?php echo $project['booked_plots']; ?>">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="sold_plots" class="form-label">Sold Plots</label>
                                    <input type="number" class="form-control" id="sold_plots" name="sold_plots" value="<?php echo $project['sold_plots']; ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="price_range_min" class="form-label">Min Price (₹)</label>
                                    <input type="number" class="form-control" id="price_range_min" name="price_range_min" value="<?php echo $project['price_range_min']; ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="price_range_max" class="form-label">Max Price (₹)</label>
                                    <input type="number" class="form-control" id="price_range_max" name="price_range_max" value="<?php echo $project['price_range_max']; ?>">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="avg_price_per_sqft" class="form-label">Avg Price per Sqft (₹)</label>
                                    <input type="number" class="form-control" id="avg_price_per_sqft" name="avg_price_per_sqft" step="0.01" value="<?php echo $project['avg_price_per_sqft']; ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="planning" <?php echo $project['status'] === 'planning' ? 'selected' : ''; ?>>Planning</option>
                                        <option value="under_construction" <?php echo $project['status'] === 'under_construction' ? 'selected' : ''; ?>>Under Construction</option>
                                        <option value="completed" <?php echo $project['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                        <option value="delayed" <?php echo $project['status'] === 'delayed' ? 'selected' : ''; ?>>Delayed</option>
                                        <option value="cancelled" <?php echo $project['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <h5 class="mb-3 mt-4">Timeline</h5>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="launch_date" class="form-label">Launch Date</label>
                                    <input type="date" class="form-control" id="launch_date" name="launch_date" value="<?php echo $project['launch_date']; ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="completion_date" class="form-label">Completion Date</label>
                                    <input type="date" class="form-control" id="completion_date" name="completion_date" value="<?php echo $project['completion_date']; ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="possession_date" class="form-label">Possession Date</label>
                                    <input type="date" class="form-control" id="possession_date" name="possession_date" value="<?php echo $project['possession_date']; ?>">
                                </div>
                            </div>
                        </div>

                        <h5 class="mb-3 mt-4">Marketing</h5>
                        <div class="mb-3">
                            <label for="marketing_description" class="form-label">Marketing Description</label>
                            <textarea class="form-control" id="marketing_description" name="marketing_description" rows="3"><?php echo htmlspecialchars(project['marketing_description'] ?? ''); ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="tags" class="form-label">Tags</label>
                            <input type="text" class="form-control" id="tags" name="tags" value="<?php echo htmlspecialchars(project['tags'] ?? ''); ?>" placeholder="Comma separated tags">
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" <?php echo $project['is_featured'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="is_featured">Featured Project</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="is_hot_deal" name="is_hot_deal" <?php echo $project['is_hot_deal'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="is_hot_deal">Hot Deal</label>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="/admin/projects" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Project
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

