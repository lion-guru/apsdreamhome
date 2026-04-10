

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-plus"></i> Add Plot</h2>
                <a href="/admin/plots" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger">
                            <?php echo htmlspecialchars(_SESSION['error'] ?? ''); unset($_SESSION['error']); ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="/admin/plots/create" id="plotForm">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="colony_id" class="form-label">Colony *</label>
                                    <select class="form-select" id="colony_id" name="colony_id" required>
                                        <option value="">Select Colony</option>
                                        <?php foreach ($colonies as $colony): ?>
                                            <option value="<?php echo $colony['id']; ?>">
                                                <?php echo htmlspecialchars($colony['state_name'] . ' > ' . $colony['district_name'] . ' > ' . $colony['colony_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="plot_number" class="form-label">Plot Number *</label>
                                    <input type="text" class="form-control" id="plot_number" name="plot_number" required>
                                    <small class="form-text text-muted">e.g., P001, A-101, Block-A-Plot-1</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="area_sqft" class="form-label">Area (Sqft) *</label>
                                    <input type="number" class="form-control" id="area_sqft" name="area_sqft" min="1" step="0.01" required onchange="calculateTotalPrice()">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="price_per_sqft" class="form-label">Price per Sqft *</label>
                                    <input type="number" class="form-control" id="price_per_sqft" name="price_per_sqft" min="0" step="0.01" required onchange="calculateTotalPrice()">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="total_price" class="form-label">Total Price</label>
                                    <input type="number" class="form-control" id="total_price" name="total_price" min="0" step="0.01" readonly>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status *</label>
                                    <select class="form-select" id="status" name="status" required>
                                        <option value="available">Available</option>
                                        <option value="booked">Booked</option>
                                        <option value="sold">Sold</option>
                                        <option value="hold">Hold</option>
                                        <option value="reserved">Reserved</option>
                                        <option value="under_construction">Under Construction</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="/admin/plots" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Plot
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function calculateTotalPrice() {
    const areaSqft = parseFloat(document.getElementById('area_sqft').value) || 0;
    const pricePerSqft = parseFloat(document.getElementById('price_per_sqft').value) || 0;
    const totalPrice = areaSqft * pricePerSqft;
    
    document.getElementById('total_price').value = totalPrice.toFixed(2);
}
</script>


