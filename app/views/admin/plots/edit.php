
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-edit"></i> Edit Plot: <?php echo htmlspecialchars($plot['plot_number'] ?? ''); ?></h2>
                <div>
                    <a href="/admin/plots" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Plots
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

            <div class="card">
                <div class="card-body">
                    <form method="POST" action="/admin/plots/edit/<?php echo $plot['id']; ?>">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Plot Number *</label>
                                <input type="text" class="form-control" name="plot_number" value="<?php echo htmlspecialchars($plot['plot_number']); ?>" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Block</label>
                                <input type="text" class="form-control" name="block" value="<?php echo htmlspecialchars($plot['block'] ?? ''); ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Sector</label>
                                <input type="text" class="form-control" name="sector" value="<?php echo htmlspecialchars($plot['sector'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Plot Type *</label>
                                <select class="form-select" name="plot_type" required>
                                    <option value="">Select Type</option>
                                    <option value="residential" <?php echo $plot['plot_type'] === 'residential' ? 'selected' : ''; ?>>Residential</option>
                                    <option value="commercial" <?php echo $plot['plot_type'] === 'commercial' ? 'selected' : ''; ?>>Commercial</option>
                                    <option value="industrial" <?php echo $plot['plot_type'] === 'industrial' ? 'selected' : ''; ?>>Industrial</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Status *</label>
                                <select class="form-select" name="status" required>
                                    <option value="available" <?php echo $plot['status'] === 'available' ? 'selected' : ''; ?>>Available</option>
                                    <option value="booked" <?php echo $plot['status'] === 'booked' ? 'selected' : ''; ?>>Booked</option>
                                    <option value="sold" <?php echo $plot['status'] === 'sold' ? 'selected' : ''; ?>>Sold</option>
                                    <option value="blocked" <?php echo $plot['status'] === 'blocked' ? 'selected' : ''; ?>>Blocked</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Facing</label>
                                <select class="form-select" name="facing">
                                    <option value="">Select</option>
                                    <option value="north" <?php echo $plot['facing'] === 'north' ? 'selected' : ''; ?>>North</option>
                                    <option value="south" <?php echo $plot['facing'] === 'south' ? 'selected' : ''; ?>>South</option>
                                    <option value="east" <?php echo $plot['facing'] === 'east' ? 'selected' : ''; ?>>East</option>
                                    <option value="west" <?php echo $plot['facing'] === 'west' ? 'selected' : ''; ?>>West</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Area (sqft) *</label>
                                <input type="number" class="form-control" name="area_sqft" step="0.01" value="<?php echo $plot['area_sqft']; ?>" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Area (sqm)</label>
                                <input type="number" class="form-control" name="area_sqm" step="0.01" value="<?php echo $plot['area_sqm'] ?? 0; ?>">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Price per Sqft *</label>
                                <input type="number" class="form-control" name="price_per_sqft" step="0.01" value="<?php echo $plot['price_per_sqft']; ?>" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Total Price</label>
                                <input type="number" class="form-control" name="total_price" step="0.01" value="<?php echo $plot['total_price']; ?>">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Booking Amount</label>
                                <input type="number" class="form-control" name="booking_amount" step="0.01" value="<?php echo $plot['booking_amount'] ?? 0; ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Total Paid</label>
                                <input type="number" class="form-control" name="total_paid" step="0.01" value="<?php echo $plot['total_paid'] ?? 0; ?>">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Frontage (ft)</label>
                                <input type="number" class="form-control" name="frontage_ft" step="0.01" value="<?php echo $plot['frontage_ft'] ?? 0; ?>">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Depth (ft)</label>
                                <input type="number" class="form-control" name="depth_ft" step="0.01" value="<?php echo $plot['depth_ft'] ?? 0; ?>">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Road Width (ft)</label>
                                <input type="number" class="form-control" name="road_width_ft" step="0.01" value="<?php echo $plot['road_width_ft'] ?? 0; ?>">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Payment Status</label>
                                <select class="form-select" name="payment_status">
                                    <option value="pending" <?php echo ($plot['payment_status'] ?? '') === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="partial" <?php echo ($plot['payment_status'] ?? '') === 'partial' ? 'selected' : ''; ?>>Partial</option>
                                    <option value="completed" <?php echo ($plot['payment_status'] ?? '') === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="corner_plot" id="corner_plot" value="1" <?php echo $plot['corner_plot'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="corner_plot">Corner Plot</label>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="park_facing" id="park_facing" value="1" <?php echo $plot['park_facing'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="park_facing">Park Facing</label>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3"><?php echo htmlspecialchars($plot['description'] ?? ''); ?></textarea>
                        </div>
                        <div class="d-flex justify-content-between mt-4">
                            <a href="/admin/plots" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Plot</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
