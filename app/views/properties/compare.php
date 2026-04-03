<?php
/**
 * Property Comparison Page
 * Select properties to compare
 */

$page_title = 'Compare Properties - APS Dream Home';
include __DIR__ . '/../layouts/header.php';
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-2">Compare Properties</h1>
            <p class="text-muted">Select 2 to <?= $max_compare ?> properties to compare side-by-side</p>
        </div>
    </div>

    <!-- Selected Properties Counter -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="alert alert-info d-flex justify-content-between align-items-center">
                <span>
                    <i class="fas fa-info-circle me-2"></i>
                    <strong id="selected-count">0</strong> properties selected 
                    <span class="text-muted">(minimum <?= $min_compare ?> required)</span>
                </span>
                <button type="button" class="btn btn-primary" id="btn-compare" disabled onclick="compareProperties()">
                    <i class="fas fa-exchange-alt me-2"></i>Compare Now
                </button>
            </div>
        </div>
    </div>

    <!-- Saved Sessions (for logged-in users) -->
    <?php if (!empty($sessions)): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-history me-2"></i>Saved Comparisons</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Properties</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($sessions as $session): ?>
                                <tr>
                                    <td><?= htmlspecialchars($session['name']) ?></td>
                                    <td><?= $session['property_count'] ?> properties</td>
                                    <td><?= date('M d, Y', strtotime($session['created_at'])) ?></td>
                                    <td>
                                        <a href="/compare/load/<?= $session['id'] ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteSession(<?= $session['id'] ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
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
    <?php endif; ?>

    <!-- Property Selection Grid -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-home me-2"></i>Select Properties</h5>
                    <div class="input-group w-auto">
                        <input type="text" class="form-control form-control-sm" id="search-properties" 
                               placeholder="Search properties..." onkeyup="searchProperties()">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row" id="property-grid">
                        <?php foreach ($properties as $property): ?>
                        <div class="col-md-6 col-lg-4 col-xl-3 mb-4 property-card" 
                             data-name="<?= strtolower(htmlspecialchars($property['title'])) ?>"
                             data-location="<?= strtolower(htmlspecialchars($property['location'])) ?>">
                            <div class="card h-100 property-select-card" id="property-<?= $property['id'] ?>" 
                                 onclick="toggleProperty(<?= $property['id'] ?>)" style="cursor: pointer;">
                                
                                <!-- Selection Badge -->
                                <div class="position-absolute top-0 end-0 m-2">
                                    <div class="form-check">
                                        <input class="form-check-input property-checkbox" type="checkbox" 
                                               value="<?= $property['id'] ?>" id="checkbox-<?= $property['id'] ?>">
                                    </div>
                                </div>

                                <!-- Property Image -->
                                <div class="property-image-wrapper" style="height: 200px; overflow: hidden;">
                                    <?php if ($property['primary_image']): ?>
                                    <img src="/<?= htmlspecialchars($property['primary_image']) ?>" 
                                         class="card-img-top" alt="<?= htmlspecialchars($property['title']) ?>"
                                         style="width: 100%; height: 100%; object-fit: cover;">
                                    <?php else: ?>
                                    <div class="bg-light d-flex align-items-center justify-content-center h-100">
                                        <i class="fas fa-home fa-3x text-muted"></i>
                                    </div>
                                    <?php endif; ?>
                                </div>

                                <div class="card-body">
                                    <h5 class="card-title text-truncate"><?= htmlspecialchars($property['title']) ?></h5>
                                    <p class="text-muted small mb-2">
                                        <i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($property['location']) ?>
                                    </p>
                                    
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="h5 text-primary mb-0">
                                            ₹<?= number_format($property['price']) ?>
                                        </span>
                                        <span class="badge bg-<?= $property['status'] === 'available' ? 'success' : 'warning' ?>">
                                            <?= ucfirst($property['status']) ?>
                                        </span>
                                    </div>

                                    <div class="row text-center small text-muted">
                                        <div class="col-4 border-end">
                                            <i class="fas fa-ruler-combined d-block mb-1"></i>
                                            <?= $property['area_sqft'] ?> sqft
                                        </div>
                                        <div class="col-4 border-end">
                                            <i class="fas fa-bed d-block mb-1"></i>
                                            <?= $property['bedrooms'] ?> BHK
                                        </div>
                                        <div class="col-4">
                                            <i class="fas fa-bath d-block mb-1"></i>
                                            <?= $property['bathrooms'] ?> Bath
                                        </div>
                                    </div>

                                    <?php if ($property['rera_status']): ?>
                                    <div class="mt-2">
                                        <span class="badge bg-info">
                                            <i class="fas fa-certificate me-1"></i>RERA Approved
                                        </span>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.property-select-card {
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.property-select-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.property-select-card.selected {
    border-color: #007bff;
    box-shadow: 0 0 0 3px rgba(0,123,255,0.25);
}

.property-select-card .property-checkbox {
    width: 20px;
    height: 20px;
    cursor: pointer;
}
</style>

<script>
let selectedProperties = [];
const maxCompare = <?= $max_compare ?>;
const minCompare = <?= $min_compare ?>;

function toggleProperty(propertyId) {
    const card = document.getElementById('property-' + propertyId);
    const checkbox = document.getElementById('checkbox-' + propertyId);
    
    if (selectedProperties.includes(propertyId)) {
        // Remove from selection
        selectedProperties = selectedProperties.filter(id => id !== propertyId);
        card.classList.remove('selected');
        checkbox.checked = false;
    } else {
        // Add to selection (if under max)
        if (selectedProperties.length >= maxCompare) {
            alert('You can compare maximum ' + maxCompare + ' properties');
            return;
        }
        selectedProperties.push(propertyId);
        card.classList.add('selected');
        checkbox.checked = true;
    }
    
    updateSelectionCounter();
}

function updateSelectionCounter() {
    const counter = document.getElementById('selected-count');
    const btnCompare = document.getElementById('btn-compare');
    
    counter.textContent = selectedProperties.length;
    
    if (selectedProperties.length >= minCompare) {
        btnCompare.disabled = false;
        counter.parentElement.classList.remove('alert-info');
        counter.parentElement.classList.add('alert-success');
    } else {
        btnCompare.disabled = true;
        counter.parentElement.classList.remove('alert-success');
        counter.parentElement.classList.add('alert-info');
    }
}

function compareProperties() {
    if (selectedProperties.length < minCompare) {
        alert('Please select at least ' + minCompare + ' properties');
        return;
    }
    
    const params = new URLSearchParams();
    selectedProperties.forEach(id => params.append('properties[]', id));
    
    window.location.href = '/compare/results?' + params.toString();
}

function searchProperties() {
    const searchTerm = document.getElementById('search-properties').value.toLowerCase();
    const cards = document.querySelectorAll('.property-card');
    
    cards.forEach(card => {
        const name = card.getAttribute('data-name');
        const location = card.getAttribute('data-location');
        
        if (name.includes(searchTerm) || location.includes(searchTerm)) {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });
}

function deleteSession(sessionId) {
    if (!confirm('Are you sure you want to delete this saved comparison?')) {
        return;
    }
    
    fetch('/compare/delete/' + sessionId, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Failed to delete: ' + data.message);
        }
    })
    .catch(error => {
        alert('Error deleting comparison');
    });
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    // Check if properties were pre-selected from URL
    const urlParams = new URLSearchParams(window.location.search);
    const preselected = urlParams.getAll('properties[]');
    
    preselected.forEach(id => {
        const propertyId = parseInt(id);
        if (document.getElementById('property-' + propertyId)) {
            toggleProperty(propertyId);
        }
    });
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
