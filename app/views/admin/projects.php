<?php
/**
 * Admin Projects Management View
 * Shows all projects with CRUD operations
 */
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><i class="fas fa-project-diagram me-2"></i>Project Management</h2>
            <p class="text-muted mb-0">Manage all projects and sites across locations</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-primary" onclick="showCreateModal()">
                <i class="fas fa-plus me-2"></i>Add New Project
            </button>
            <div class="dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-download me-2"></i>Export
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="/admin/export/projects">Export to Excel</a></li>
                    <li><a class="dropdown-item" href="/admin/export/projects?format=pdf">Export to PDF</a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Project Stats -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stats-card card text-white" style="background: linear-gradient(135deg, #007bff 0%, #6610f2 100%);">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">
                                <?php
                                $totalProjects = array_sum(array_column($stats, 'total_projects'));
                                echo $totalProjects;
                                ?>
                            </h4>
                            <small>Total Projects</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-project-diagram fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card card text-white" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">
                                <?php
                                $featuredProjects = array_sum(array_column($stats, 'featured_projects'));
                                echo $featuredProjects;
                                ?>
                            </h4>
                            <small>Featured</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-star fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card card text-white" style="background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">
                                <?php
                                $ongoingProjects = array_sum(array_column($stats, 'ongoing_projects'));
                                echo $ongoingProjects;
                                ?>
                            </h4>
                            <small>Ongoing</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-cog fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card card text-white" style="background: linear-gradient(135deg, #dc3545 0%, #e83e8c 100%);">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">
                                <?php
                                $completedProjects = array_sum(array_column($stats, 'completed_projects'));
                                echo $completedProjects;
                                ?>
                            </h4>
                            <small>Completed</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label for="search" class="form-label">Search</label>
                            <input type="text" class="form-control" id="search" name="search"
                                   placeholder="Search by name, code, location..."
                                   value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
                        </div>
                        <div class="col-md-2">
                            <label for="city" class="form-label">City</label>
                            <select class="form-select" id="city" name="city">
                                <option value="">All Cities</option>
                                <?php foreach ($cities as $city): ?>
                                    <option value="<?= htmlspecialchars($city) ?>"
                                            <?= ($filters['city'] ?? '') === $city ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($city) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="project_type" class="form-label">Type</label>
                            <select class="form-select" id="project_type" name="project_type">
                                <option value="">All Types</option>
                                <?php foreach ($project_types as $type): ?>
                                    <option value="<?= htmlspecialchars($type) ?>"
                                            <?= ($filters['project_type'] ?? '') === $type ? 'selected' : '' ?>>
                                        <?= htmlspecialchars(ucfirst($type)) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">All Status</option>
                                <option value="ongoing" <?= ($filters['status'] ?? '') === 'ongoing' ? 'selected' : '' ?>>Ongoing</option>
                                <option value="completed" <?= ($filters['status'] ?? '') === 'completed' ? 'selected' : '' ?>>Completed</option>
                                <option value="planning" <?= ($filters['status'] ?? '') === 'planning' ? 'selected' : '' ?>>Planning</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-search me-2"></i>Filter
                            </button>
                            <a href="/admin/projects" class="btn btn-outline-secondary">
                                <i class="fas fa-undo me-2"></i>Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Projects Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-list me-2"></i>Projects List</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($projects)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-project-diagram fa-3x text-muted mb-3"></i>
                            <h4>No Projects Found</h4>
                            <p class="text-muted">No projects match your current filters.</p>
                            <button class="btn btn-primary" onclick="showCreateModal()">
                                <i class="fas fa-plus me-2"></i>Add Your First Project
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Project</th>
                                        <th>Code</th>
                                        <th>Type</th>
                                        <th>Location</th>
                                        <th>Price</th>
                                        <th>Status</th>
                                        <th>Available</th>
                                        <th>Featured</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($projects as $project): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php if (!empty($project['gallery_images'])): ?>
                                                        <img src="/uploads/projects/<?= htmlspecialchars($project['gallery_images'][0]) ?>"
                                                             class="rounded me-3" alt="Project Image"
                                                             style="width: 50px; height: 50px; object-fit: cover;">
                                                    <?php else: ?>
                                                        <div class="bg-light rounded me-3 d-flex align-items-center justify-content-center"
                                                             style="width: 50px; height: 50px;">
                                                            <i class="fas fa-image text-muted"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div>
                                                        <strong><?= htmlspecialchars($project['project_name']) ?></strong>
                                                        <br>
                                                        <small class="text-muted">
                                                            <?= htmlspecialchars($project['short_description'] ?? '') ?>
                                                        </small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">
                                                    <?= htmlspecialchars($project['project_code']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">
                                                    <?= htmlspecialchars(ucfirst($project['project_type'])) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <strong><?= htmlspecialchars($project['city']) ?></strong>
                                                <br>
                                                <small class="text-muted">
                                                    <?= htmlspecialchars($project['location']) ?>
                                                </small>
                                            </td>
                                            <td>
                                                <strong>₹<?= number_format($project['base_price'], 0) ?></strong>
                                                <br>
                                                <small class="text-muted">
                                                    ₹<?= number_format($project['price_per_sqft'], 0) ?>/sqft
                                                </small>
                                            </td>
                                            <td>
                                                <?php
                                                $status = $project['project_status'] ?? 'ongoing';
                                                $badgeClass = 'bg-secondary';

                                                switch ($status) {
                                                    case 'completed':
                                                        $badgeClass = 'bg-success';
                                                        break;
                                                    case 'ongoing':
                                                        $badgeClass = 'bg-warning';
                                                        break;
                                                    case 'planning':
                                                        $badgeClass = 'bg-info';
                                                        break;
                                                }
                                                ?>
                                                <span class="badge <?= $badgeClass ?>">
                                                    <?= ucfirst($status) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <strong><?= $project['available_plots'] ?? 0 ?></strong>
                                                <br>
                                                <small class="text-muted">
                                                    of <?= $project['total_plots'] ?? 0 ?> total
                                                </small>
                                            </td>
                                            <td>
                                                <?php if ($project['is_featured']): ?>
                                                    <span class="badge bg-warning text-dark">
                                                        <i class="fas fa-star me-1"></i>Featured
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-muted">No</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="/admin/projects/<?= $project['project_id'] ?>"
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="/projects/<?= htmlspecialchars($project['project_code']) ?>"
                                                       class="btn btn-sm btn-outline-info" target="_blank">
                                                        <i class="fas fa-external-link-alt"></i>
                                                    </a>
                                                    <button class="btn btn-sm btn-outline-warning"
                                                            onclick="editProject(<?= $project['project_id'] ?>)">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-danger"
                                                            onclick="deleteProject(<?= $project['project_id'] ?>, '<?= htmlspecialchars($project['project_name']) ?>')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create/Edit Project Modal -->
<div class="modal fade" id="projectModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="projectModalTitle">Add New Project</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="projectForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" id="projectId" name="project_id">

                    <!-- Basic Information -->
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="mb-3">Basic Information</h6>
                            <div class="mb-3">
                                <label for="projectName" class="form-label">Project Name *</label>
                                <input type="text" class="form-control" id="projectName" name="project_name" required>
                            </div>
                            <div class="mb-3">
                                <label for="projectCode" class="form-label">Project Code *</label>
                                <input type="text" class="form-control" id="projectCode" name="project_code" required>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="projectType" class="form-label">Project Type *</label>
                                        <select class="form-select" id="projectType" name="project_type" required>
                                            <option value="">Select Type</option>
                                            <option value="residential">Residential</option>
                                            <option value="commercial">Commercial</option>
                                            <option value="mixed">Mixed Use</option>
                                            <option value="plotting">Plotting</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="projectStatus" class="form-label">Status *</label>
                                        <select class="form-select" id="projectStatus" name="project_status" required>
                                            <option value="planning">Planning</option>
                                            <option value="ongoing" selected>Ongoing</option>
                                            <option value="completed">Completed</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="mb-3">Location Details</h6>
                            <div class="mb-3">
                                <label for="location" class="form-label">Location *</label>
                                <input type="text" class="form-control" id="location" name="location" required>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="city" class="form-label">City *</label>
                                        <select class="form-select" id="city" name="city" required>
                                            <option value="">Select City</option>
                                            <option value="Gorakhpur">Gorakhpur</option>
                                            <option value="Lucknow">Lucknow</option>
                                            <option value="Varanasi">Varanasi</option>
                                            <option value="Kanpur">Kanpur</option>
                                            <option value="Agra">Agra</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="state" class="form-label">State</label>
                                        <input type="text" class="form-control" id="state" name="state" value="Uttar Pradesh">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="pincode" class="form-label">Pincode</label>
                                        <input type="text" class="form-control" id="pincode" name="pincode">
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="address" class="form-label">Full Address</label>
                                <textarea class="form-control" id="address" name="address" rows="2"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Pricing and Inventory -->
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <h6 class="mb-3">Pricing Information</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="basePrice" class="form-label">Base Price (₹) *</label>
                                        <input type="number" class="form-control" id="basePrice" name="base_price" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="pricePerSqft" class="form-label">Price per sqft (₹) *</label>
                                        <input type="number" class="form-control" id="pricePerSqft" name="price_per_sqft" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="bookingAmount" class="form-label">Booking Amount (₹)</label>
                                        <input type="number" class="form-control" id="bookingAmount" name="booking_amount">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="possessionDate" class="form-label">Possession Date</label>
                                        <input type="date" class="form-control" id="possessionDate" name="possession_date">
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="emiAvailable" name="emi_available">
                                    <label class="form-check-label" for="emiAvailable">
                                        EMI Available
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="mb-3">Inventory</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="totalArea" class="form-label">Total Area (sq ft)</label>
                                        <input type="number" class="form-control" id="totalArea" name="total_area">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="totalPlots" class="form-label">Total Plots *</label>
                                        <input type="number" class="form-control" id="totalPlots" name="total_plots" required>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="availablePlots" class="form-label">Available Plots *</label>
                                <input type="number" class="form-control" id="availablePlots" name="available_plots" required>
                            </div>
                        </div>
                    </div>

                    <!-- Description and Features -->
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <h6 class="mb-3">Description</h6>
                            <div class="mb-3">
                                <label for="shortDescription" class="form-label">Short Description</label>
                                <textarea class="form-control" id="shortDescription" name="short_description" rows="2"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">Full Description</label>
                                <textarea class="form-control" id="description" name="description" rows="4"></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="mb-3">Project Features</h6>
                            <div class="mb-3">
                                <label class="form-label">Amenities</label>
                                <div class="border p-3 rounded" style="max-height: 200px; overflow-y: auto;">
                                    <?php
                                    $amenities = [
                                        '24/7 Security', 'Swimming Pool', 'Gymnasium', 'Children Play Area',
                                        'Jogging Track', 'Club House', 'Landscaped Gardens', 'Power Backup',
                                        'Water Supply', 'Car Parking', 'Elevator', 'CCTV Surveillance'
                                    ];
                                    foreach ($amenities as $amenity):
                                    ?>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" name="amenities[]" value="<?= htmlspecialchars($amenity) ?>" id="amenity<?= array_search($amenity, $amenities) ?>">
                                            <label class="form-check-label" for="amenity<?= array_search($amenity, $amenities) ?>">
                                                <?= htmlspecialchars($amenity) ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Information -->
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <h6 class="mb-3">Additional Information</h6>
                            <div class="mb-3">
                                <label for="reraNumber" class="form-label">RERA Number</label>
                                <input type="text" class="form-control" id="reraNumber" name="rera_number">
                            </div>
                            <div class="mb-3">
                                <label for="developerName" class="form-label">Developer Name</label>
                                <input type="text" class="form-control" id="developerName" name="developer_name" value="APS Dream Homes Pvt Ltd">
                            </div>
                            <div class="mb-3">
                                <label for="contactNumber" class="form-label">Contact Number</label>
                                <input type="text" class="form-control" id="contactNumber" name="contact_number">
                            </div>
                            <div class="mb-3">
                                <label for="contactEmail" class="form-label">Contact Email</label>
                                <input type="email" class="form-control" id="contactEmail" name="contact_email">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="mb-3">Settings</h6>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="isFeatured" name="is_featured">
                                    <label class="form-check-label" for="isFeatured">
                                        Mark as Featured Project
                                    </label>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="isActive" name="is_active" checked>
                                    <label class="form-check-label" for="isActive">
                                        Active Project
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="projectSubmitBtn">Create Project</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showCreateModal() {
    document.getElementById('projectModalTitle').textContent = 'Add New Project';
    document.getElementById('projectForm').reset();
    document.getElementById('projectId').value = '';
    document.getElementById('projectSubmitBtn').textContent = 'Create Project';

    $('#projectModal').modal('show');
}

function editProject(projectId) {
    // In a real implementation, you would fetch project data via AJAX
    document.getElementById('projectModalTitle').textContent = 'Edit Project';
    document.getElementById('projectId').value = projectId;
    document.getElementById('projectSubmitBtn').textContent = 'Update Project';

    // Populate form with project data (would be fetched from server)
    $('#projectModal').modal('show');
}

function deleteProject(projectId, projectName) {
    if (confirm(`Are you sure you want to delete "${projectName}"? This action cannot be undone.`)) {
        // In a real implementation, you would submit a delete request
        alert('Project deletion would be implemented here.');
    }
}

// Auto-generate project code based on project name
document.getElementById('projectName')?.addEventListener('input', function() {
    const name = this.value;
    const code = name.toUpperCase().replace(/[^A-Z0-9]/g, '').substring(0, 10);
    if (code && !document.getElementById('projectCode').value) {
        document.getElementById('projectCode').value = code;
    }
});
</script>

<style>
.stats-card {
    border: none;
    border-radius: 10px;
    transition: transform 0.2s;
}

.stats-card:hover {
    transform: translateY(-2px);
}

.table th {
    border-top: none;
    font-weight: 600;
}

.badge {
    font-size: 0.8em;
}

.project-card {
    transition: transform 0.2s, box-shadow 0.2s;
}

.project-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
</style>
