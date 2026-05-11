
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-th"></i> Plot Details</h2>
                <div>
                    <a href="/admin/plots" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Plots
                    </a>
                    <a href="/admin/plots/edit/<?php echo $plot['id']; ?>" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Edit Plot
                    </a>
                </div>
            </div>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header"><h5 class="mb-0">Basic Information</h5></div>
                        <div class="card-body">
                            <table class="table table-bordered">
                                <tr><th>Plot Number</th><td><?php echo htmlspecialchars($plot['plot_number'] ?? ''); ?></td></tr>
                                <tr><th>Block</th><td><?php echo htmlspecialchars($plot['block'] ?? ''); ?></td></tr>
                                <tr><th>Sector</th><td><?php echo htmlspecialchars($plot['sector'] ?? ''); ?></td></tr>
                                <tr><th>Type</th><td><?php echo htmlspecialchars($plot['plot_type'] ?? ''); ?></td></tr>
                                <tr><th>Status</th><td><span class="badge bg-<?php echo $plot['status'] === 'available' ? 'success' : ($plot['status'] === 'booked' ? 'warning' : 'secondary'); ?>"><?php echo htmlspecialchars($plot['status'] ?? ''); ?></span></td></tr>
                                <tr><th>Colony</th><td><?php echo htmlspecialchars($plot['colony_name'] ?? ''); ?></td></tr>
                                <tr><th>District</th><td><?php echo htmlspecialchars($plot['district_name'] ?? ''); ?></td></tr>
                                <tr><th>State</th><td><?php echo htmlspecialchars($plot['state_name'] ?? ''); ?></td></tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header"><h5 class="mb-0">Pricing & Area</h5></div>
                        <div class="card-body">
                            <table class="table table-bordered">
                                <tr><th>Area (sqft)</th><td><?php echo number_format($plot['area_sqft'] ?? 0); ?></td></tr>
                                <tr><th>Area (sqm)</th><td><?php echo number_format($plot['area_sqm'] ?? 0, 2); ?></td></tr>
                                <tr><th>Price per Sqft</th><td>₹<?php echo number_format($plot['price_per_sqft'] ?? 0, 2); ?></td></tr>
                                <tr><th>Total Price</th><td>₹<?php echo number_format($plot['total_price'] ?? 0, 2); ?></td></tr>
                                <tr><th>Booking Amount</th><td>₹<?php echo number_format($plot['booking_amount'] ?? 0, 2); ?></td></tr>
                                <tr><th>Frontage (ft)</th><td><?php echo $plot['frontage_ft'] ?? 'N/A'; ?></td></tr>
                                <tr><th>Depth (ft)</th><td><?php echo $plot['depth_ft'] ?? 'N/A'; ?></td></tr>
                                <tr><th>Facing</th><td><?php echo htmlspecialchars($plot['facing'] ?? 'N/A'); ?></td></tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (!empty($plot['description'])): ?>
            <div class="card mb-4">
                <div class="card-header"><h5 class="mb-0">Description</h5></div>
                <div class="card-body">
                    <p><?php echo nl2br(htmlspecialchars($plot['description'])); ?></p>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($history)): ?>
            <div class="card mb-4">
                <div class="card-header"><h5 class="mb-0">Status History</h5></div>
                <div class="card-body">
                    <table class="table table-striped">
                        <thead><tr><th>Date</th><th>Old Status</th><th>New Status</th><th>Changed By</th><th>Reason</th></tr></thead>
                        <tbody>
                            <?php foreach ($history as $h): ?>
                            <tr>
                                <td><?php echo date('d M Y H:i', strtotime($h['created_at'])); ?></td>
                                <td><?php echo htmlspecialchars($h['old_status']); ?></td>
                                <td><?php echo htmlspecialchars($h['new_status']); ?></td>
                                <td><?php echo htmlspecialchars($h['changed_by_name'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($h['change_reason'] ?? ''); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
