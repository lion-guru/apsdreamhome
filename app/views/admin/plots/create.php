<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title ?? 'Add New Plot') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
                <div class="position-sticky pt-3">
                    <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                        <span>Admin Panel</span>
                    </h6>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/dashboard">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/sites">
                                <i class="fas fa-map-marked-alt"></i> Sites
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/properties">
                                <i class="fas fa-building"></i> Properties
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="/admin/plots">
                                <i class="fas fa-th"></i> Plots
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/bookings">
                                <i class="fas fa-calendar-check"></i> Bookings
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/users">
                                <i class="fas fa-users"></i> Users
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Add New Plot</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <a href="/admin/plots" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Plots
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Plot Form -->
                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="/admin/plots">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                            
                            <div class="row">
                                <!-- Site Selection -->
                                <div class="col-md-6">
                                    <h5 class="mb-3"><i class="fas fa-map-marker-alt"></i> Site Information</h5>
                                    
                                    <div class="mb-3">
                                        <label for="site_id" class="form-label">Site *</label>
                                        <select class="form-select" id="site_id" name="site_id" required onchange="loadSiteDetails()">
                                            <option value="">Select Site</option>
                                            <?php foreach ($sites as $site): ?>
                                                <option value="<?= $site['id'] ?>" <?= ($selected_site_id ?? '') == $site['id'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($site['site_name']) ?> - <?= htmlspecialchars($site['location']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="form-text">Select the site where this plot is located</div>
                                    </div>
                                </div>

                                <!-- Plot Details -->
                                <div class="col-md-6">
                                    <h5 class="mb-3"><i class="fas fa-th"></i> Plot Details</h5>
                                    
                                    <div class="mb-3">
                                        <label for="plot_no" class="form-label">Plot Number *</label>
                                        <input type="text" class="form-control" id="plot_no" name="plot_no" required>
                                        <div class="form-text">Unique plot identifier (e.g., A-101, B-205)</div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="area" class="form-label">Total Area (sq ft) *</label>
                                                <input type="number" class="form-control" id="area" name="area" step="0.01" required onchange="calculateAvailableArea()">
                                                <div class="form-text">Total area of the plot</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="available_area" class="form-label">Available Area (sq ft) *</label>
                                                <input type="number" class="form-control" id="available_area" name="available_area" step="0.01" required>
                                                <div class="form-text">Area available for construction</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="plot_dimension" class="form-label">Plot Dimensions</label>
                                        <input type="text" class="form-control" id="plot_dimension" name="plot_dimension" placeholder="e.g., 40x60">
                                        <div class="form-text">Length x Width dimensions</div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="plot_facing" class="form-label">Plot Facing</label>
                                        <select class="form-select" id="plot_facing" name="plot_facing">
                                            <option value="">Select Facing</option>
                                            <option value="North">North</option>
                                            <option value="South">South</option>
                                            <option value="East">East</option>
                                            <option value="West">West</option>
                                            <option value="North-East">North-East</option>
                                            <option value="North-West">North-West</option>
                                            <option value="South-East">South-East</option>
                                            <option value="South-West">South-West</option>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label for="plot_price" class="form-label">Plot Price (₹)</label>
                                        <input type="number" class="form-control" id="plot_price" name="plot_price" step="0.01" placeholder="0.00">
                                        <div class="form-text">Price of the plot in Indian Rupees</div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="plot_status" class="form-label">Plot Status</label>
                                        <select class="form-select" id="plot_status" name="plot_status">
                                            <option value="available" selected>Available</option>
                                            <option value="sold">Sold</option>
                                            <option value="reserved">Reserved</option>
                                            <option value="under_process">Under Process</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Gata Details -->
                            <div class="row mt-4">
                                <div class="col-12">
                                    <h5 class="mb-3"><i class="fas fa-drafting-compass"></i> Gata Details</h5>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label for="gata_a" class="form-label">Gata A</label>
                                                <input type="number" class="form-control" id="gata_a" name="gata_a">
                                                <div class="form-text">Gata A number</div>
                                            </div>
                                            <div class="mb-3">
                                                <label for="area_gata_a" class="form-label">Area Gata A (sq ft)</label>
                                                <input type="number" class="form-control" id="area_gata_a" name="area_gata_a" step="0.01" value="0">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label for="gata_b" class="form-label">Gata B</label>
                                                <input type="number" class="form-control" id="gata_b" name="gata_b">
                                                <div class="form-text">Gata B number</div>
                                            </div>
                                            <div class="mb-3">
                                                <label for="area_gata_b" class="form-label">Area Gata B (sq ft)</label>
                                                <input type="number" class="form-control" id="area_gata_b" name="area_gata_b" step="0.01" value="0">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label for="gata_c" class="form-label">Gata C</label>
                                                <input type="number" class="form-control" id="gata_c" name="gata_c">
                                                <div class="form-text">Gata C number</div>
                                            </div>
                                            <div class="mb-3">
                                                <label for="area_gata_c" class="form-label">Area Gata C (sq ft)</label>
                                                <input type="number" class="form-control" id="area_gata_c" name="area_gata_c" step="0.01" value="0">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label for="gata_d" class="form-label">Gata D</label>
                                                <input type="number" class="form-control" id="gata_d" name="gata_d">
                                                <div class="form-text">Gata D number</div>
                                            </div>
                                            <div class="mb-3">
                                                <label for="area_gata_d" class="form-label">Area Gata D (sq ft)</label>
                                                <input type="number" class="form-control" id="area_gata_d" name="area_gata_d" step="0.01" value="0">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i> 
                                        <strong>Gata Details:</strong> Enter the gata numbers and their respective areas for land record purposes. 
                                        The sum of all gata areas should equal the total plot area.
                                    </div>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="d-flex justify-content-between">
                                        <a href="/admin/plots" class="btn btn-outline-secondary">
                                            <i class="fas fa-times"></i> Cancel
                                        </a>
                                        <div>
                                            <button type="reset" class="btn btn-outline-warning me-2">
                                                <i class="fas fa-redo"></i> Reset
                                            </button>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-save"></i> Create Plot
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const siteId = document.getElementById('site_id').value;
            const plotNo = document.getElementById('plot_no').value.trim();
            const area = parseFloat(document.getElementById('area').value);
            const availableArea = parseFloat(document.getElementById('available_area').value);

            if (!siteId || !plotNo || isNaN(area) || area <= 0 || isNaN(availableArea) || availableArea <= 0) {
                e.preventDefault();
                alert('Please fill in all required fields with valid values.');
                return false;
            }

            // Validate gata areas
            const gataAreas = [
                parseFloat(document.getElementById('area_gata_a').value) || 0,
                parseFloat(document.getElementById('area_gata_b').value) || 0,
                parseFloat(document.getElementById('area_gata_c').value) || 0,
                parseFloat(document.getElementById('area_gata_d').value) || 0
            ];
            
            const totalGataArea = gataAreas.reduce((sum, gataArea) => sum + gataArea, 0);
            
            if (totalGataArea > area) {
                e.preventDefault();
                alert('Total gata area cannot exceed total plot area.');
                return false;
            }
        });

        // Auto-calculate available area
        function calculateAvailableArea() {
            const area = parseFloat(document.getElementById('area').value) || 0;
            const availableArea = document.getElementById('available_area');
            
            if (availableArea.value === '' || availableArea.value === '0') {
                availableArea.value = area.toString();
            }
        }

        // Load site details (placeholder for future enhancement)
        function loadSiteDetails() {
            const siteId = document.getElementById('site_id').value;
            if (siteId) {
                // This could be enhanced to load site-specific information
                console.log('Selected site ID:', siteId);
            }
        }

        // Auto-calculate gata total
        document.querySelectorAll('[id^="area_gata_"]').forEach(input => {
            input.addEventListener('input', updateGataTotal);
        });

        function updateGataTotal() {
            const areas = [
                parseFloat(document.getElementById('area_gata_a').value) || 0,
                parseFloat(document.getElementById('area_gata_b').value) || 0,
                parseFloat(document.getElementById('area_gata_c').value) || 0,
                parseFloat(document.getElementById('area_gata_d').value) || 0
            ];
            
            const total = areas.reduce((sum, area) => sum + area, 0);
            const totalArea = parseFloat(document.getElementById('area').value) || 0;
            
            if (total > totalArea) {
                document.getElementById('area_gata_a').value = 
                    parseFloat(document.getElementById('area_gata_a').value) || 0;
            }
        }
    </script>
</body>
</html>
