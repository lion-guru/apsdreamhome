<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-plus"></i> Add Plot</h2>
                <a href="<?= BASE_URL ?>admin/plots" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
            
            <div class="card aps-cp-card">
                <div class="card-body aps-cp-card-body">
                    <form method="POST" action="<?= BASE_URL ?>admin/plots" id="plotForm">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?? $_SESSION['csrf_token'] ?? ''; ?>">
                        <ul class="nav nav-tabs mb-3" id="plotTabs">
                            <li class="nav-item"><a class="nav-link active" href="#basic" data-bs-toggle="tab">Basic Info</a></li>
                            <li class="nav-item"><a class="nav-link" href="#dims" data-bs-toggle="tab">Dimensions & Area</a></li>
                            <li class="nav-item"><a class="nav-link" href="#price" data-bs-toggle="tab">Pricing</a></li>
                            <li class="nav-item"><a class="nav-link" href="#extra" data-bs-toggle="tab">Features</a></li>
                        </ul>

                        <div class="tab-content">
                            <!-- Basic Info -->
                            <div class="tab-pane fade show active" id="basic">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Colony *</label>
                                            <select class="form-select" name="colony_id" required>
                                                <option value="">Select Colony</option>
                                                <?php if (empty($colonies)): ?>
                                                    <option value="" disabled>No colonies found</option>
                                                <?php endif; ?>
                                                <?php foreach ($colonies as $colony): ?>
                                                    <option value="<?= $colony['id'] ?? '' ?>">
                                                        <?= htmlspecialchars(($colony['state_name'] ?? '') . ' > ' . ($colony['district_name'] ?? '') . ' > ' . ($colony['colony_name'] ?? $colony['name'] ?? '')) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <?php if (empty($colonies)): ?>
                                                <small class="text-warning d-block mt-1">
                                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                                    No colonies exist yet.
                                                    <a href="<?= BASE_URL ?>/admin/colonies/create" class="fw-bold">Create Colony First</a>
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label class="form-label">Plot Number *</label>
                                            <input type="text" class="form-control" name="plot_number" required placeholder="e.g., A-101">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label class="form-label">Block</label>
                                            <input type="text" class="form-control" name="block" placeholder="e.g., A, B, C">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">Plot Type</label>
                                            <select class="form-select" name="plot_type">
                                                <option value="residential">Residential</option>
                                                <option value="commercial">Commercial</option>
                                                <option value="industrial">Industrial</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">Status</label>
                                            <select class="form-select" name="status">
                                                <option value="available">Available</option>
                                                <option value="booked">Booked</option>
                                                <option value="hold">Hold</option>
                                                <option value="reserved">Reserved</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">Facing</label>
                                            <select class="form-select" name="facing">
                                                <option value="">Select</option>
                                                <option value="north">North</option>
                                                <option value="south">South</option>
                                                <option value="east">East</option>
                                                <option value="west">West</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Dimensions -->
                            <div class="tab-pane fade" id="dims">
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Width (ft)</label>
                                        <input type="number" class="form-control" name="width_ft" step="0.01" id="width_ft" onchange="calcCreateDim()">
                                        <small class="text-muted">e.g., 20 for 20x40</small>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Length (ft)</label>
                                        <input type="number" class="form-control" name="length_ft" step="0.01" id="length_ft" onchange="calcCreateDim()">
                                        <small class="text-muted">e.g., 40 for 20x40</small>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Dimension Label</label>
                                        <input type="text" class="form-control" name="dimension_label" id="dimension_label" placeholder="20x40" readonly>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Area (sqft) *</label>
                                        <input type="number" class="form-control" name="area_sqft" min="1" step="0.01" required id="area_sqft" onchange="calcCreatePps()">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Area (sqm)</label>
                                        <input type="number" class="form-control" name="area_sqm" step="0.01" value="0">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Frontage (ft)</label>
                                        <input type="number" class="form-control" name="frontage_ft" step="0.01" value="0">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Depth (ft)</label>
                                        <input type="number" class="form-control" name="depth_ft" step="0.01" value="0">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Road Width (ft)</label>
                                        <input type="number" class="form-control" name="road_width_ft" step="0.01" value="0">
                                    </div>
                                </div>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> 
                                    <strong>Quick Select:</strong>
                                    <button type="button" class="btn btn-sm btn-outline-secondary ms-2" onclick="setCreateDims(20,40)">20x40</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setCreateDims(20,50)">20x50</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setCreateDims(25,48)">25x48</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setCreateDims(25,60)">25x60</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setCreateDims(30,50)">30x50</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setCreateDims(30,60)">30x60</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setCreateDims(40,60)">40x60</button>
                                </div>
                            </div>

                            <!-- Pricing -->
                            <div class="tab-pane fade" id="price">
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Base Price per Sqft *</label>
                                        <input type="number" class="form-control" name="base_price_per_sqft" min="0" step="0.01" id="base_pps" value="1500" onchange="calcCreatePps()">
                                        <small class="text-muted">Standard colony rate</small>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Current Price per Sqft *</label>
                                        <input type="number" class="form-control" name="price_per_sqft" min="0" step="0.01" required id="price_per_sqft" onchange="calcCreatePps()">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Total Price</label>
                                        <input type="number" class="form-control" name="total_price" min="0" step="0.01" id="total_price" readonly>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Negotiated / Deal Price</label>
                                        <input type="number" class="form-control" name="negotiated_price" step="0.01" placeholder="Only if different from total">
                                    </div>
                                </div>
                            </div>

                            <!-- Features -->
                            <div class="tab-pane fade" id="extra">
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="corner_plot" id="corner_plot" value="1">
                                            <label class="form-check-label" for="corner_plot">Corner Plot</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="park_facing" id="park_facing" value="1">
                                            <label class="form-check-label" for="park_facing">Park Facing</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea class="form-control" name="description" rows="3" placeholder="Optional plot description, features, or notes"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="<?= BASE_URL ?>admin/plots" class="btn btn-secondary">
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
function calcCreateDim() {
    const w = parseFloat(document.getElementById('width_ft').value) || 0;
    const l = parseFloat(document.getElementById('length_ft').value) || 0;
    if (w > 0 && l > 0) {
        document.getElementById('dimension_label').value = w + 'x' + l;
        document.getElementById('area_sqft').value = (w * l).toFixed(2);
        calcCreatePps();
    }
}
function calcCreatePps() {
    const area = parseFloat(document.getElementById('area_sqft').value) || 0;
    const pps = parseFloat(document.getElementById('price_per_sqft').value) || 0;
    document.getElementById('total_price').value = (area * pps).toFixed(2);
    if (!document.getElementById('price_per_sqft').value) {
        document.getElementById('price_per_sqft').value = document.getElementById('base_pps').value || 1500;
        calcCreatePps();
    }
}
function setCreateDims(w, l) {
    document.getElementById('width_ft').value = w;
    document.getElementById('length_ft').value = l;
    calcCreateDim();
}
</script>
