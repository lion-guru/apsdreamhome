<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-edit"></i> Edit Plot: <?= htmlspecialchars($plot['plot_number'] ?? '') ?></h2>
                <div>
                    <a href="<?= BASE_URL ?>admin/plots" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Plots
                    </a>
                </div>
            </div>


            <div class="card aps-cp-card">
                <div class="card-body aps-cp-card-body">
                    <form method="POST" action="<?= BASE_URL ?>admin/plots/edit/<?= $plot['id'] ?? 0 ?>">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                        <ul class="nav nav-tabs mb-3" id="plotTabs">
                            <li class="nav-item"><a class="nav-link active" href="#basic" data-bs-toggle="tab">Basic Info</a></li>
                            <li class="nav-item"><a class="nav-link" href="#dimensions" data-bs-toggle="tab">Dimensions & Area</a></li>
                            <li class="nav-item"><a class="nav-link" href="#pricing" data-bs-toggle="tab">Pricing & Override</a></li>
                            <li class="nav-item"><a class="nav-link" href="#features" data-bs-toggle="tab">Features</a></li>
                        </ul>

                        <div class="tab-content">
                            <!-- Basic Info Tab -->
                            <div class="tab-pane fade show active" id="basic">
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Plot Number *</label>
                                        <input type="text" class="form-control" name="plot_number" value="<?= htmlspecialchars($plot['plot_number'] ?? '') ?>" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Block</label>
                                        <input type="text" class="form-control" name="block" value="<?= htmlspecialchars($plot['block'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Sector</label>
                                        <input type="text" class="form-control" name="sector" value="<?= htmlspecialchars($plot['sector'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Plot Type *</label>
                                        <select class="form-select" name="plot_type" required>
                                            <option value="residential" <?= ($plot['plot_type'] ?? '') === 'residential' ? 'selected' : '' ?>>Residential</option>
                                            <option value="commercial" <?= ($plot['plot_type'] ?? '') === 'commercial' ? 'selected' : '' ?>>Commercial</option>
                                            <option value="industrial" <?= ($plot['plot_type'] ?? '') === 'industrial' ? 'selected' : '' ?>>Industrial</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Status *</label>
                                        <select class="form-select" name="status" required>
                                            <option value="available" <?= ($plot['status'] ?? '') === 'available' ? 'selected' : '' ?>>Available</option>
                                            <option value="booked" <?= ($plot['status'] ?? '') === 'booked' ? 'selected' : '' ?>>Booked</option>
                                            <option value="sold" <?= ($plot['status'] ?? '') === 'sold' ? 'selected' : '' ?>>Sold</option>
                                            <option value="hold" <?= ($plot['status'] ?? '') === 'hold' ? 'selected' : '' ?>>Hold</option>
                                            <option value="reserved" <?= ($plot['status'] ?? '') === 'reserved' ? 'selected' : '' ?>>Reserved</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Facing</label>
                                        <select class="form-select" name="facing">
                                            <option value="">Select</option>
                                            <option value="north" <?= ($plot['facing'] ?? '') === 'north' ? 'selected' : '' ?>>North</option>
                                            <option value="south" <?= ($plot['facing'] ?? '') === 'south' ? 'selected' : '' ?>>South</option>
                                            <option value="east" <?= ($plot['facing'] ?? '') === 'east' ? 'selected' : '' ?>>East</option>
                                            <option value="west" <?= ($plot['facing'] ?? '') === 'west' ? 'selected' : '' ?>>West</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Dimensions Tab -->
                            <div class="tab-pane fade" id="dimensions">
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Width (ft)</label>
                                        <input type="number" class="form-control" name="width_ft" step="0.01" value="<?= $plot['width_ft'] ?? '' ?>" id="width_ft" onchange="calcDimensionLabel()">
                                        <small class="text-muted">e.g., 20 for 20x40</small>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Length (ft)</label>
                                        <input type="number" class="form-control" name="length_ft" step="0.01" value="<?= $plot['length_ft'] ?? '' ?>" id="length_ft" onchange="calcDimensionLabel()">
                                        <small class="text-muted">e.g., 40 for 20x40</small>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Dimension Label</label>
                                        <input type="text" class="form-control" name="dimension_label" id="dimension_label" value="<?= htmlspecialchars($plot['dimension_label'] ?? '') ?>" placeholder="20x40">
                                        <small class="text-muted">Auto-calculated if width x length set</small>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Area (sqft) *</label>
                                        <input type="number" class="form-control" name="area_sqft" step="0.01" value="<?= $plot['area_sqft'] ?? 0 ?>" required id="area_sqft" onchange="calcFromArea()">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Area (sqm)</label>
                                        <input type="number" class="form-control" name="area_sqm" step="0.01" value="<?= $plot['area_sqm'] ?? 0 ?>">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Frontage (ft)</label>
                                        <input type="number" class="form-control" name="frontage_ft" step="0.01" value="<?= $plot['frontage_ft'] ?? 0 ?>">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Depth (ft)</label>
                                        <input type="number" class="form-control" name="depth_ft" step="0.01" value="<?= $plot['depth_ft'] ?? 0 ?>">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Road Width (ft)</label>
                                        <input type="number" class="form-control" name="road_width_ft" step="0.01" value="<?= $plot['road_width_ft'] ?? 0 ?>">
                                    </div>
                                </div>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> 
                                    <strong>Quick Dimensions:</strong>
                                    <button type="button" class="btn btn-sm btn-outline-secondary ms-2" onclick="setDimensions(20,40)">20x40 (800 sqft)</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setDimensions(20,50)">20x50 (1000 sqft)</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setDimensions(25,48)">25x48 (1200 sqft)</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setDimensions(25,60)">25x60 (1500 sqft)</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setDimensions(30,50)">30x50 (1500 sqft)</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setDimensions(30,60)">30x60 (1800 sqft)</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setDimensions(40,60)">40x60 (2400 sqft)</button>
                                </div>
                            </div>

                            <!-- Pricing Tab -->
                            <div class="tab-pane fade" id="pricing">
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Base Price per Sqft</label>
                                        <input type="number" class="form-control" name="base_price_per_sqft" step="0.01" value="<?= $plot['base_price_per_sqft'] ?? $plot['price_per_sqft'] ?? 0 ?>" id="base_pps">
                                        <small class="text-muted">Original base rate (₹/sqft)</small>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Current Price per Sqft *</label>
                                        <input type="number" class="form-control" name="price_per_sqft" step="0.01" value="<?= $plot['price_per_sqft'] ?? 0 ?>" required id="curr_pps" onchange="calcTotalPrice()">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Total Price</label>
                                        <input type="number" class="form-control" name="total_price" step="0.01" value="<?= $plot['total_price'] ?? 0 ?>" id="total_price" onchange="calcPpsFromTotal()">
                                    </div>
                                </div>

                                <!-- Price Override Section -->
                                <div class="card border-warning mb-3">
                                    <div class="card-header bg-warning text-dark">
                                        <i class="fas fa-exclamation-triangle"></i> <strong>Price Override</strong> — Use only when negotiating a special deal price
                                    </div>
                                    <div class="card-body aps-cp-card-body">
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">Negotiated / Deal Price</label>
                                                <input type="number" class="form-control" name="negotiated_price" step="0.01" value="<?= $plot['negotiated_price'] ?? '' ?>" placeholder="Leave empty if same as total price">
                                            </div>
                                            <div class="col-md-8 mb-3">
                                                <label class="form-label">Override Reason</label>
                                                <input type="text" class="form-control" name="price_override_reason" value="<?= htmlspecialchars($plot['price_override_reason'] ?? '') ?>" placeholder="e.g., Special deal for cash payment, Festival discount, etc.">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Price History -->
                                <?php $priceHistory = $priceHistory ?? []; if (!empty($priceHistory)): ?>
                                <div class="mt-3">
                                    <h6 class="fw-bold"><i class="fas fa-history"></i> Price Change History</h6>
                                    <div class="table-responsive"><table class="table table-sm table-striped">
                                        <thead><tr><th>Date</th><th>Old Price</th><th>New Price</th><th>Type</th><th>Reason</th></tr></thead>
                                        <tbody>
                                            <?php foreach ($priceHistory as $ph): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($ph['created_at'] ?? '') ?></td>
                                                <td>₹<?= number_format(intval($ph['old_price'] ?? 0)) ?></td>
                                                <td>₹<?= number_format(intval($ph['new_price'] ?? 0)) ?></td>
                                                <td><span class="badge bg-info"><?= htmlspecialchars($ph['change_type'] ?? '') ?></span></td>
                                                <td><?= htmlspecialchars($ph['reason'] ?? '') ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table></div>
                                </div>
                                <?php endif; ?>
                            </div>

                            <!-- Features Tab -->
                            <div class="tab-pane fade" id="features">
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="corner_plot" id="corner_plot" value="1" <?= ($plot['corner_plot'] ?? 0) ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="corner_plot">Corner Plot</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="park_facing" id="park_facing" value="1" <?= ($plot['park_facing'] ?? 0) ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="park_facing">Park Facing</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Payment Status</label>
                                        <select class="form-select" name="payment_status">
                                            <option value="pending" <?= ($plot['payment_status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                                            <option value="partial" <?= ($plot['payment_status'] ?? '') === 'partial' ? 'selected' : '' ?>>Partial</option>
                                            <option value="completed" <?= ($plot['payment_status'] ?? '') === 'completed' ? 'selected' : '' ?>>Completed</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Booking Amount</label>
                                        <input type="number" class="form-control" name="booking_amount" step="0.01" value="<?= $plot['booking_amount'] ?? 0 ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Total Paid</label>
                                        <input type="number" class="form-control" name="total_paid" step="0.01" value="<?= $plot['total_paid'] ?? 0 ?>">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea class="form-control" name="description" rows="3"><?= htmlspecialchars($plot['description'] ?? '') ?></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="<?= BASE_URL ?>admin/plots" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Plot</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function calcDimensionLabel() {
    const w = parseFloat(document.getElementById('width_ft').value) || 0;
    const l = parseFloat(document.getElementById('length_ft').value) || 0;
    if (w > 0 && l > 0) {
        document.getElementById('dimension_label').value = w + 'x' + l;
        document.getElementById('area_sqft').value = (w * l).toFixed(2);
        calcTotalPrice();
    }
}
function calcFromArea() {
    const area = parseFloat(document.getElementById('area_sqft').value) || 0;
    const w = parseFloat(document.getElementById('width_ft').value) || 0;
    if (w > 0 && area > 0) {
        const l = area / w;
        document.getElementById('length_ft').value = l.toFixed(2);
        document.getElementById('dimension_label').value = w + 'x' + Math.round(l);
    }
    calcTotalPrice();
}
function calcTotalPrice() {
    const area = parseFloat(document.getElementById('area_sqft').value) || 0;
    const pps = parseFloat(document.getElementById('curr_pps').value) || 0;
    document.getElementById('total_price').value = (area * pps).toFixed(2);
}
function calcPpsFromTotal() {
    const area = parseFloat(document.getElementById('area_sqft').value) || 0;
    const total = parseFloat(document.getElementById('total_price').value) || 0;
    if (area > 0) {
        document.getElementById('curr_pps').value = (total / area).toFixed(2);
    }
}
function setDimensions(w, l) {
    document.getElementById('width_ft').value = w;
    document.getElementById('length_ft').value = l;
    calcDimensionLabel();
}
</script>
