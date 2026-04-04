<?php include __DIR__ . '/../../../layouts/admin_header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-home"></i> Colonies Management</h2>
                <div>
                    <a href="/admin/locations/colonies/create" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Colony
                    </a>
                    <a href="/admin/locations/states" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> States
                    </a>
                    <a href="/admin/locations/districts" class="btn btn-info">
                        <i class="fas fa-city"></i> Districts
                    </a>
                </div>
            </div>
            
            <!-- Filter Section -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="/admin/locations/colonies">
                        <div class="row">
                            <div class="col-md-4">
                                <label for="state_id" class="form-label">Filter by State</label>
                                <select class="form-select" id="state_id" name="state_id" onchange="filterDistricts(this.value)">
                                    <option value="">All States</option>
                                    <?php foreach ($states as $state): ?>
                                        <option value="<?php echo $state['id']; ?>" <?php echo (isset($_GET['state_id']) && $_GET['state_id'] == $state['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($state['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="district_id" class="form-label">Filter by District</label>
                                <select class="form-select" id="district_id" name="district_id">
                                    <option value="">All Districts</option>
                                    <?php foreach ($districts as $district): ?>
                                        <option value="<?php echo $district['id']; ?>" class="district-option state-<?php echo $district['state_id']; ?>" <?php echo (isset($_GET['district_id']) && $_GET['district_id'] == $district['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($district['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">&nbsp;</label>
                                <div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-filter"></i> Apply Filter
                                    </button>
                                    <a href="/admin/locations/colonies" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Clear
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Colonies Table -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Colonies</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Location</th>
                                    <th>Total Plots</th>
                                    <th>Available</th>
                                    <th>Starting Price</th>
                                    <th>Featured</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($colonies as $colony): ?>
                                <tr>
                                    <td><?php echo $colony['id']; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($colony['name']); ?></strong>
                                        <?php if ($colony['description']): ?>
                                            <br><small class="text-muted"><?php echo htmlspecialchars(substr($colony['description'], 0, 50)) . '...'; ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small>
                                            <?php echo htmlspecialchars($colony['state_name']); ?><br>
                                            <?php echo htmlspecialchars($colony['district_name']); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge bg-info"><?php echo $colony['total_plots']; ?></span>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo $colony['available_plots'] > 0 ? 'success' : 'danger'; ?>">
                                            <?php echo $colony['available_plots']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <strong>₹<?php echo number_format($colony['starting_price']); ?></strong>
                                    </td>
                                    <td>
                                        <?php if ($colony['is_featured']): ?>
                                            <i class="fas fa-star text-warning"></i>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo $colony['is_active'] ? 'success' : 'danger'; ?>">
                                            <?php echo $colony['is_active'] ? 'Active' : 'Inactive'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="/admin/locations/colonies/edit/<?php echo $colony['id']; ?>" class="btn btn-outline-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="/admin/locations/colonies/delete/<?php echo $colony['id']; ?>" class="btn btn-outline-danger" title="Delete" onclick="return confirm('Are you sure?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function filterDistricts(stateId) {
    var districtSelect = document.getElementById('district_id');
    var options = districtSelect.querySelectorAll('.district-option');
    
    // Reset district select
    districtSelect.value = '';
    
    if (stateId === '') {
        // Show all districts
        options.forEach(function(option) {
            option.style.display = 'block';
        });
    } else {
        // Show only districts of selected state
        options.forEach(function(option) {
            if (option.classList.contains('state-' + stateId)) {
                option.style.display = 'block';
            } else {
                option.style.display = 'none';
            }
        });
    }
}

// Initialize filter on page load
document.addEventListener('DOMContentLoaded', function() {
    var stateSelect = document.getElementById('state_id');
    if (stateSelect.value) {
        filterDistricts(stateSelect.value);
    }
});
</script>

<?php include __DIR__ . '/../../../layouts/admin_footer.php'; ?>
