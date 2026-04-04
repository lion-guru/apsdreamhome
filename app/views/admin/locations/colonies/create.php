<?php include __DIR__ . '/../../../layouts/admin_header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-plus"></i> Add Colony</h2>
                <a href="/admin/locations/colonies" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger">
                            <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="/admin/locations/colonies/create" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="state_id" class="form-label">State *</label>
                                    <select class="form-select" id="state_id" name="state_id" onchange="loadDistricts(this.value)" required>
                                        <option value="">Select State</option>
                                        <?php foreach ($states as $state): ?>
                                            <option value="<?php echo $state['id']; ?>">
                                                <?php echo htmlspecialchars($state['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="district_id" class="form-label">District *</label>
                                    <select class="form-select" id="district_id" name="district_id" required>
                                        <option value="">Select State First</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Colony Name *</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="total_plots" class="form-label">Total Plots *</label>
                                    <input type="number" class="form-control" id="total_plots" name="total_plots" min="1" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="amenities" class="form-label">Amenities</label>
                            <textarea class="form-control" id="amenities" name="amenities" rows="2" placeholder="e.g., Park, Temple, School, Hospital, Market"></textarea>
                            <small class="form-text text-muted">Separate amenities with commas</small>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="available_plots" class="form-label">Available Plots</label>
                                    <input type="number" class="form-control" id="available_plots" name="available_plots" min="0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="starting_price" class="form-label">Starting Price (₹)</label>
                                    <input type="number" class="form-control" id="starting_price" name="starting_price" min="0" step="0.01">
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="map_link" class="form-label">Google Maps Link</label>
                            <input type="url" class="form-control" id="map_link" name="map_link" placeholder="https://maps.google.com/?q=location">
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="image_path" class="form-label">Image Path</label>
                                    <input type="text" class="form-control" id="image_path" name="image_path" placeholder="assets/images/colonies/colony.jpg">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="brochure_path" class="form-label">Brochure Path</label>
                                    <input type="text" class="form-control" id="brochure_path" name="brochure_path" placeholder="assets/brochures/colony.pdf">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured">
                                        <label class="form-check-label" for="is_featured">
                                            Featured Colony
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" checked>
                                        <label class="form-check-label" for="is_active">
                                            Active
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="/admin/locations/colonies" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Colony
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function loadDistricts(stateId) {
    var districtSelect = document.getElementById('district_id');
    
    if (stateId === '') {
        districtSelect.innerHTML = '<option value="">Select State First</option>';
        return;
    }
    
    // AJAX call to get districts
    fetch('/admin/locations/api/districts/' + stateId)
        .then(response => response.json())
        .then(data => {
            districtSelect.innerHTML = '<option value="">Select District</option>';
            data.forEach(function(district) {
                districtSelect.innerHTML += '<option value="' + district.id + '">' + district.name + '</option>';
            });
        })
        .catch(error => {
            console.error('Error loading districts:', error);
            districtSelect.innerHTML = '<option value="">Error loading districts</option>';
        });
}

// Auto-calculate available plots if not set
document.getElementById('total_plots').addEventListener('input', function() {
    var available = document.getElementById('available_plots');
    if (available.value === '') {
        available.value = this.value;
    }
});
</script>

<?php include __DIR__ . '/../../../layouts/admin_footer.php'; ?>
