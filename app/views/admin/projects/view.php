

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-building"></i> View Project: <?php echo htmlspecialchars(project['name'] ?? ''); ?></h2>
                <div>
                    <a href="/admin/projects" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Projects
                    </a>
                    <a href="/admin/projects/edit/<?php echo $project['id']; ?>" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Project Details</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Project Name:</strong> <?php echo htmlspecialchars(project['name'] ?? ''); ?></p>
                            <p><strong>Type:</strong> <?php echo ucfirst(htmlspecialchars(project['project_type'] ?? '')); ?></p>
                            <p><strong>Developer:</strong> <?php echo htmlspecialchars(project['developer_name'] ?? ''); ?></p>
                            <p><strong>Contact:</strong> <?php echo htmlspecialchars(project['developer_contact'] ?? ''); ?></p>
                            <p><strong>Phone:</strong> <?php echo htmlspecialchars(project['developer_phone'] ?? ''); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Status:</strong> <?php echo ucfirst(str_replace('_', ' ', $project['status'])); ?></p>
                            <p><strong>Total Plots:</strong> <?php echo $project['total_plots']; ?></p>
                            <p><strong>Available:</strong> <?php echo $project['available_plots']; ?></p>
                            <p><strong>Sold:</strong> <?php echo $project['sold_plots']; ?></p>
                            <p><strong>Booked:</strong> <?php echo $project['booked_plots']; ?></p>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Location:</strong>
                                <?php if ($project['colony_name']): ?><?php echo htmlspecialchars(project['colony_name'] ?? ''); ?>,<?php endif; ?>
                                <?php if ($project['district_name']): ?><?php echo htmlspecialchars(project['district_name'] ?? ''); ?>,<?php endif; ?>
                                <?php if ($project['state_name']): ?><?php echo htmlspecialchars(project['state_name'] ?? ''); ?><?php endif; ?>
                            </p>
                            <p><strong>Address:</strong> <?php echo htmlspecialchars(project['address'] ?? ''); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Price Range:</strong> ₹<?php echo number_format(floatval(project['price_range_min'] ?? 0)); ?> - ₹<?php echo number_format(floatval(project['price_range_max'] ?? 0)); ?></p>
                            <p><strong>Avg Price per Sqft:</strong> ₹<?php echo number_format(floatval(project['avg_price_per_sqft'] ?? 0)); ?></p>
                            <p><strong>Total Area:</strong> <?php echo number_format(floatval(project['total_area'] ?? 0)); ?> sq ft</p>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-4">
                            <p><strong>Launch Date:</strong> <?php echo $project['launch_date']; ?></p>
                        </div>
                        <div class="col-md-4">
                            <p><strong>Completion Date:</strong> <?php echo $project['completion_date']; ?></p>
                        </div>
                        <div class="col-md-4">
                            <p><strong>Possession Date:</strong> <?php echo $project['possession_date']; ?></p>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-12">
                            <p><strong>Description:</strong></p>
                            <p><?php echo nl2br(htmlspecialchars(project['description'] ?? '')); ?></p>
                        </div>
                    </div>
                    <?php if ($project['marketing_description']): ?>
                    <hr>
                    <div class="row">
                        <div class="col-12">
                            <p><strong>Marketing Description:</strong></p>
                            <p><?php echo nl2br(htmlspecialchars(project['marketing_description'] ?? '')); ?></p>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if ($project['tags']): ?>
                    <hr>
                    <div class="row">
                        <div class="col-12">
                            <p><strong>Tags:</strong> <?php echo htmlspecialchars(project['tags'] ?? ''); ?></p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($project['amenities']): ?>
            <?php $amenities = json_decode($project['amenities'], true); ?>
            <?php if (is_array($amenities) && !empty($amenities)): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Amenities</h5>
                </div>
                <div class="card-body">
                    <ul>
                        <?php foreach ($amenities as $amenity): ?>
                            <li><?php echo htmlspecialchars($amenity); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

