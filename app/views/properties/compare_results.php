<?php
/**
 * Property Comparison Results Page
 * Side-by-side comparison of selected properties
 */

$page_title = 'Property Comparison Results - APS Dream Home';
include __DIR__ . '/../layouts/header.php';

$propertiesCount = count($properties);
$colClass = $propertiesCount == 2 ? 'col-md-6' : ($propertiesCount == 3 ? 'col-md-4' : 'col-md-3');
?>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-2">Property Comparison</h1>
                    <p class="text-muted">Comparing <?= $propertiesCount ?> properties side-by-side</p>
                </div>
                <div class="btn-group">
                    <a href="/compare" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Selection
                    </a>
                    <button type="button" class="btn btn-outline-primary" onclick="shareComparison()">
                        <i class="fas fa-share-alt me-2"></i>Share
                    </button>
                    <?php if (isset($_SESSION['user_id'])): ?>
                    <button type="button" class="btn btn-primary" onclick="saveComparison()">
                        <i class="fas fa-save me-2"></i>Save
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Comparison Summary Cards -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="row">
                <!-- Price Range -->
                <div class="col-md-3">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h6 class="text-muted mb-2">Price Range</h6>
                            <p class="h5 text-primary mb-1">
                                ₹<?= number_format($comparison['price_range']['min']) ?> - 
                                ₹<?= number_format($comparison['price_range']['max']) ?>
                            </p>
                            <small class="text-muted">Avg: ₹<?= number_format($comparison['price_range']['avg']) ?></small>
                        </div>
                    </div>
                </div>
                
                <!-- Area Range -->
                <div class="col-md-3">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h6 class="text-muted mb-2">Area Range</h6>
                            <p class="h5 text-success mb-1">
                                <?= number_format($comparison['area_range']['min']) ?> - 
                                <?= number_format($comparison['area_range']['max']) ?> sqft
                            </p>
                            <small class="text-muted">Avg: <?= number_format($comparison['area_range']['avg']) ?> sqft</small>
                        </div>
                    </div>
                </div>
                
                <!-- Best Value -->
                <div class="col-md-3">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h6 class="text-muted mb-2">Best Value</h6>
                            <?php if ($comparison['best_value']): ?>
                                <?php foreach ($properties as $prop): ?>
                                    <?php if ($prop['id'] == $comparison['best_value']): ?>
                                    <p class="h5 text-info mb-1"><?= htmlspecialchars($prop['title']) ?></p>
                                    <small class="text-muted">
                                        ₹<?= number_format($comparison['price_per_sqft'][$prop['id']], 2) ?>/sqft
                                    </small>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted">-</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Largest Area -->
                <div class="col-md-3">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h6 class="text-muted mb-2">Largest Area</h6>
                            <?php if ($comparison['largest_area']): ?>
                                <?php foreach ($properties as $prop): ?>
                                    <?php if ($prop['id'] == $comparison['largest_area']): ?>
                                    <p class="h5 text-warning mb-1"><?= htmlspecialchars($prop['title']) ?></p>
                                    <small class="text-muted"><?= number_format($prop['area_sqft']) ?> sqft</small>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted">-</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Property Comparison Cards -->
    <div class="row mb-4">
        <?php foreach ($properties as $property): ?>
        <div class="<?= $colClass ?> mb-4">
            <div class="card h-100 <?= $property['id'] == $comparison['best_value'] ? 'border-info' : '' ?> 
                        <?= $property['id'] == $comparison['largest_area'] ? 'border-warning' : '' ?>">
                
                <!-- Property Image -->
                <div class="position-relative">
                    <?php if ($property['primary_image']): ?>
                    <img src="/<?= htmlspecialchars($property['primary_image']) ?>" 
                         class="card-img-top" alt="<?= htmlspecialchars($property['title']) ?>"
                         style="height: 200px; object-fit: cover;">
                    <?php else: ?>
                    <div class="bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                        <i class="fas fa-home fa-3x text-muted"></i>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Badges -->
                    <div class="position-absolute top-0 start-0 m-2">
                        <?php if ($property['id'] == $comparison['best_value']): ?>
                        <span class="badge bg-info mb-1 d-block">
                            <i class="fas fa-trophy me-1"></i>Best Value
                        </span>
                        <?php endif; ?>
                        <?php if ($property['id'] == $comparison['largest_area']): ?>
                        <span class="badge bg-warning text-dark">
                            <i class="fas fa-expand me-1"></i>Largest
                        </span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Status Badge -->
                    <div class="position-absolute top-0 end-0 m-2">
                        <span class="badge bg-<?= $property['status'] === 'available' ? 'success' : 'warning' ?>">
                            <?= ucfirst($property['status']) ?>
                        </span>
                    </div>
                </div>

                <div class="card-body">
                    <h5 class="card-title"><?= htmlspecialchars($property['title']) ?></h5>
                    <p class="text-muted small">
                        <i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($property['location']) ?>
                    </p>
                    
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="text-primary mb-0">₹<?= number_format($property['price']) ?></h4>
                    </div>

                    <!-- Price per sqft -->
                    <div class="alert alert-light py-2 mb-3">
                        <small class="text-muted">Price per sqft:</small>
                        <strong class="float-end">₹<?= number_format($comparison['price_per_sqft'][$property['id']], 2) ?></strong>
                    </div>
                </div>

                <!-- Features List -->
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between">
                        <span><i class="fas fa-ruler-combined me-2 text-muted"></i>Area</span>
                        <strong><?= number_format($property['area_sqft']) ?> sqft</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span><i class="fas fa-bed me-2 text-muted"></i>Bedrooms</span>
                        <strong><?= $property['bedrooms'] ?> BHK</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span><i class="fas fa-bath me-2 text-muted"></i>Bathrooms</span>
                        <strong><?= $property['bathrooms'] ?></strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span><i class="fas fa-certificate me-2 text-muted"></i>RERA Status</span>
                        <span class="badge bg-<?= $property['rera_status'] ? 'success' : 'secondary' ?>">
                            <?= $property['rera_status'] ? 'Approved' : 'Pending' ?>
                        </span>
                    </li>
                    <?php if ($property['agent_name']): ?>
                    <li class="list-group-item">
                        <small class="text-muted">
                            <i class="fas fa-user me-2"></i>Agent: <?= htmlspecialchars($property['agent_name']) ?>
                        </small>
                    </li>
                    <?php endif; ?>
                </ul>

                <div class="card-footer bg-white">
                    <a href="/properties/<?= $property['id'] ?>" class="btn btn-outline-primary btn-sm w-100">
                        <i class="fas fa-eye me-2"></i>View Details
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Detailed Comparison Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-table me-2"></i>Detailed Comparison</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 20%;">Feature</th>
                                    <?php foreach ($properties as $property): ?>
                                    <th style="width: <?= 80 / $propertiesCount ?>%;">
                                        <?= htmlspecialchars($property['title']) ?>
                                    </th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="fw-bold"><i class="fas fa-tag me-2"></i>Price</td>
                                    <?php foreach ($properties as $property): ?>
                                    <td class="text-primary fw-bold">₹<?= number_format($property['price']) ?></td>
                                    <?php endforeach; ?>
                                </tr>
                                <tr>
                                    <td class="fw-bold"><i class="fas fa-ruler-combined me-2"></i>Area</td>
                                    <?php foreach ($properties as $property): ?>
                                    <td><?= number_format($property['area_sqft']) ?> sqft</td>
                                    <?php endforeach; ?>
                                </tr>
                                <tr>
                                    <td class="fw-bold"><i class="fas fa-money-bill-wave me-2"></i>Price/sqft</td>
                                    <?php foreach ($properties as $property): ?>
                                    <td>₹<?= number_format($comparison['price_per_sqft'][$property['id']], 2) ?></td>
                                    <?php endforeach; ?>
                                </tr>
                                <tr>
                                    <td class="fw-bold"><i class="fas fa-bed me-2"></i>Bedrooms</td>
                                    <?php foreach ($properties as $property): ?>
                                    <td><?= $property['bedrooms'] ?> BHK</td>
                                    <?php endforeach; ?>
                                </tr>
                                <tr>
                                    <td class="fw-bold"><i class="fas fa-bath me-2"></i>Bathrooms</td>
                                    <?php foreach ($properties as $property): ?>
                                    <td><?= $property['bathrooms'] ?></td>
                                    <?php endforeach; ?>
                                </tr>
                                <tr>
                                    <td class="fw-bold"><i class="fas fa-map-marker-alt me-2"></i>Location</td>
                                    <?php foreach ($properties as $property): ?>
                                    <td><?= htmlspecialchars($property['location']) ?></td>
                                    <?php endforeach; ?>
                                </tr>
                                <tr>
                                    <td class="fw-bold"><i class="fas fa-certificate me-2"></i>RERA Status</td>
                                    <?php foreach ($properties as $property): ?>
                                    <td>
                                        <span class="badge bg-<?= $property['rera_status'] ? 'success' : 'secondary' ?>">
                                            <?= $property['rera_status'] ? 'Approved' : 'Pending' ?>
                                        </span>
                                    </td>
                                    <?php endforeach; ?>
                                </tr>
                                <tr>
                                    <td class="fw-bold"><i class="fas fa-info-circle me-2"></i>Status</td>
                                    <?php foreach ($properties as $property): ?>
                                    <td>
                                        <span class="badge bg-<?= $property['status'] === 'available' ? 'success' : 'warning' ?>">
                                            <?= ucfirst($property['status']) ?>
                                        </span>
                                    </td>
                                    <?php endforeach; ?>
                                </tr>
                                <tr>
                                    <td class="fw-bold"><i class="fas fa-user me-2"></i>Agent</td>
                                    <?php foreach ($properties as $property): ?>
                                    <td>
                                        <?= $property['agent_name'] ? htmlspecialchars($property['agent_name']) : '-' ?>
                                        <?php if ($property['agent_phone']): ?>
                                        <br><small class="text-muted"><?= htmlspecialchars($property['agent_phone']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <?php endforeach; ?>
                                </tr>
                                <tr>
                                    <td class="fw-bold"><i class="fas fa-cog me-2"></i>Action</td>
                                    <?php foreach ($properties as $property): ?>
                                    <td>
                                        <a href="/properties/<?= $property['id'] ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye me-1"></i>View
                                        </a>
                                        <a href="/enquiry?property_id=<?= $property['id'] ?>" class="btn btn-sm btn-outline-success">
                                            <i class="fas fa-phone me-1"></i>Enquire
                                        </a>
                                    </td>
                                    <?php endforeach; ?>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Save Comparison Modal -->
<div class="modal fade" id="saveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Save Comparison</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="session-name">Comparison Name</label>
                    <input type="text" class="form-control" id="session-name" 
                           value="Comparison <?= date('Y-m-d H:i') ?>" placeholder="Enter a name for this comparison">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="confirmSave()">Save</button>
            </div>
        </div>
    </div>
</div>

<script>
function shareComparison() {
    const shareUrl = '<?= $share_url ?>';
    
    if (navigator.share) {
        navigator.share({
            title: 'Property Comparison - APS Dream Home',
            url: shareUrl
        });
    } else {
        // Copy to clipboard
        navigator.clipboard.writeText(shareUrl).then(() => {
            alert('Comparison link copied to clipboard!');
        }).catch(() => {
            prompt('Copy this link to share:', shareUrl);
        });
    }
}

function saveComparison() {
    const modal = new bootstrap.Modal(document.getElementById('saveModal'));
    modal.show();
}

function confirmSave() {
    const sessionName = document.getElementById('session-name').value;
    const propertyIds = <?= json_encode(array_column($properties, 'id')) ?>;
    
    fetch('/compare/save', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: new URLSearchParams({
            'session_name': sessionName,
            'property_ids[]': propertyIds
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Comparison saved successfully!');
            bootstrap.Modal.getInstance(document.getElementById('saveModal')).hide();
        } else {
            alert('Failed to save: ' + data.message);
        }
    })
    .catch(error => {
        alert('Error saving comparison');
    });
}
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
