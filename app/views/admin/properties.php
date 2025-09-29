<?php include '../app/views/includes/header.php'; ?>

<div class="container-fluid mt-4">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2">
            <div class="admin-sidebar">
                <div class="sidebar-header">
                    <h5><i class="fas fa-tachometer-alt me-2"></i>Admin Panel</h5>
                </div>
                <nav class="nav nav-pills flex-column">
                    <a href="/admin" class="nav-link">Dashboard</a>
                    <a href="/admin/properties" class="nav-link active">Properties</a>
                    <a href="/admin/leads" class="nav-link">Leads</a>
                    <a href="/admin/users" class="nav-link">Users</a>
                    <a href="/admin/reports" class="nav-link">Reports</a>
                    <a href="/admin/settings" class="nav-link">Settings</a>
                </nav>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-md-9 col-lg-10">
            <div class="admin-content">
                <!-- Page Header -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h2>Property Management</h2>
                                <p class="text-muted">Manage property listings and inventory</p>
                            </div>
                            <div>
                                <a href="/admin/properties/create" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Add New Property
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="filters-card">
                            <form method="GET" class="row g-3">
                                <div class="col-md-3">
                                    <input type="text" class="form-control" name="search"
                                           placeholder="Search properties..." value="<?php echo htmlspecialchars($filters['search'] ?? ''); ?>">
                                </div>
                                <div class="col-md-2">
                                    <select class="form-select" name="type">
                                        <option value="">All Types</option>
                                        <option value="apartment" <?php echo ($filters['type'] ?? '') === 'apartment' ? 'selected' : ''; ?>>Apartment</option>
                                        <option value="villa" <?php echo ($filters['type'] ?? '') === 'villa' ? 'selected' : ''; ?>>Villa</option>
                                        <option value="house" <?php echo ($filters['type'] ?? '') === 'house' ? 'selected' : ''; ?>>House</option>
                                        <option value="commercial" <?php echo ($filters['type'] ?? '') === 'commercial' ? 'selected' : ''; ?>>Commercial</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select class="form-select" name="status">
                                        <option value="">All Status</option>
                                        <option value="available" <?php echo ($filters['status'] ?? '') === 'available' ? 'selected' : ''; ?>>Available</option>
                                        <option value="sold" <?php echo ($filters['status'] ?? '') === 'sold' ? 'selected' : ''; ?>>Sold</option>
                                        <option value="rented" <?php echo ($filters['status'] ?? '') === 'rented' ? 'selected' : ''; ?>>Rented</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select class="form-select" name="user_id">
                                        <option value="">All Agents</option>
                                        <!-- Add agent options here -->
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <button type="submit" class="btn btn-primary me-2">
                                        <i class="fas fa-search me-2"></i>Filter
                                    </button>
                                    <a href="/admin/properties" class="btn btn-outline-secondary">
                                        <i class="fas fa-times me-2"></i>Clear
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-home"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo $propertyStats['by_status'][0]['count'] ?? 0; ?></h3>
                                <p>Total Properties</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo count(array_filter($propertyStats['by_status'] ?? [], fn($s) => $s['status'] === 'available')) ?? 0; ?></h3>
                                <p>Available</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo count(array_filter($propertyStats['by_status'] ?? [], fn($s) => $s['status'] === 'sold')) ?? 0; ?></h3>
                                <p>Sold</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-building"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo count($propertyStats['by_type'] ?? []); ?></h3>
                                <p>Types</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Properties Grid -->
                <div class="row">
                    <div class="col-12">
                        <div class="properties-grid-admin">
                            <?php if (!empty($properties)): ?>
                                <?php foreach ($properties as $property): ?>
                                <div class="property-card-admin">
                                    <div class="property-image-container">
                                        <img src="<?php echo htmlspecialchars($property['main_image'] ?? 'https://via.placeholder.com/400x250/667eea/ffffff?text=Property'); ?>"
                                             alt="<?php echo htmlspecialchars($property['title']); ?>"
                                             class="property-image">
                                        <div class="property-badges">
                                            <span class="badge bg-<?php echo getStatusColor($property['status']); ?>">
                                                <?php echo ucfirst($property['status']); ?>
                                            </span>
                                            <span class="badge bg-primary">
                                                <?php echo htmlspecialchars($property['property_type'] ?? 'Property'); ?>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="property-content">
                                        <h5 class="property-title">
                                            <a href="/admin/properties/<?php echo $property['id']; ?>">
                                                <?php echo htmlspecialchars($property['title']); ?>
                                            </a>
                                        </h5>

                                        <div class="property-location">
                                            <i class="fas fa-map-marker-alt"></i>
                                            <span><?php echo htmlspecialchars($property['address'] ?? 'Location not specified'); ?></span>
                                        </div>

                                        <div class="property-features">
                                            <div class="feature-item">
                                                <i class="fas fa-bed"></i>
                                                <span><?php echo $property['bedrooms'] ?? 0; ?> Beds</span>
                                            </div>
                                            <div class="feature-item">
                                                <i class="fas fa-bath"></i>
                                                <span><?php echo $property['bathrooms'] ?? 0; ?> Baths</span>
                                            </div>
                                            <div class="feature-item">
                                                <i class="fas fa-ruler-combined"></i>
                                                <span><?php echo number_format($property['area_sqft'] ?? 0); ?> sq.ft</span>
                                            </div>
                                        </div>

                                        <div class="property-price">
                                            ₹<?php echo number_format($property['price'] ?? 0); ?>
                                        </div>

                                        <div class="property-footer">
                                            <div class="property-agent">
                                                <small class="text-muted">
                                                    Listed by: <?php echo htmlspecialchars($property['owner_name'] ?? 'Unknown'); ?>
                                                </small>
                                            </div>
                                            <div class="property-actions">
                                                <a href="/admin/properties/<?php echo $property['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="/admin/properties/<?php echo $property['id']; ?>/edit" class="btn btn-sm btn-outline-secondary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button class="btn btn-sm btn-outline-danger" onclick="deleteProperty(<?php echo $property['id']; ?>)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="col-12 text-center py-5">
                                    <i class="fas fa-home fa-4x text-muted mb-4"></i>
                                    <h4>No Properties Found</h4>
                                    <p class="text-muted mb-4">No properties match your current filters.</p>
                                    <a href="/admin/properties/create" class="btn btn-primary">
                                        <i class="fas fa-plus me-2"></i>Add First Property
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <?php if (!empty($properties)): ?>
                <div class="row">
                    <div class="col-12">
                        <nav aria-label="Property pagination">
                            <ul class="pagination justify-content-center">
                                <li class="page-item disabled">
                                    <span class="page-link">Previous</span>
                                </li>
                                <li class="page-item active">
                                    <span class="page-link">1</span>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="#">2</a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="#">3</a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="#">Next</a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
// Delete property confirmation
function deleteProperty(propertyId) {
    if (confirm('Are you sure you want to delete this property? This action cannot be undone.')) {
        fetch(`/admin/properties/${propertyId}/delete`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Failed to delete property: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to delete property. Please try again.');
        });
    }
}

// Helper function for status colors
function getStatusColor(status) {
    const colors = {
        'available': 'success',
        'sold': 'danger',
        'rented': 'info',
        'pending': 'warning'
    };
    return colors[status] || 'secondary';
}
</script>

<style>
.admin-sidebar {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    height: fit-content;
    position: sticky;
    top: 20px;
}

.sidebar-header {
    padding: 20px;
    border-bottom: 1px solid #eee;
}

.admin-content {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    padding: 30px;
}

.filters-card {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 10px;
    margin-bottom: 20px;
}

.stat-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    margin-bottom: 20px;
}

.stat-icon {
    width: 50px;
    height: 50px;
    background: rgba(255,255,255,0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 15px;
    font-size: 20px;
}

.properties-grid-admin {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 2rem;
}

.property-card-admin {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 5px 25px rgba(0,0,0,0.08);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.property-card-admin:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 35px rgba(0,0,0,0.15);
}

.property-image-container {
    position: relative;
    height: 200px;
    overflow: hidden;
}

.property-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.property-card-admin:hover .property-image {
    transform: scale(1.05);
}

.property-badges {
    position: absolute;
    top: 10px;
    left: 10px;
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.property-content {
    padding: 20px;
}

.property-title {
    margin-bottom: 10px;
    font-size: 1.1rem;
}

.property-title a {
    color: #1a237e;
    text-decoration: none;
    transition: color 0.3s ease;
}

.property-title a:hover {
    color: #667eea;
}

.property-location {
    color: #666;
    font-size: 0.9rem;
    margin-bottom: 15px;
}

.property-features {
    display: flex;
    justify-content: space-between;
    margin-bottom: 15px;
    flex-wrap: wrap;
    gap: 10px;
}

.feature-item {
    display: flex;
    align-items: center;
    color: #666;
    font-size: 0.85rem;
}

.feature-item i {
    color: #667eea;
    margin-right: 5px;
    font-size: 0.9rem;
}

.property-price {
    font-size: 1.25rem;
    font-weight: bold;
    color: #28a745;
    margin-bottom: 15px;
}

.property-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 15px;
    border-top: 1px solid #eee;
}

.property-actions {
    display: flex;
    gap: 0.5rem;
}

@media (max-width: 768px) {
    .admin-sidebar {
        margin-bottom: 20px;
    }

    .properties-grid-admin {
        grid-template-columns: 1fr;
    }
}
</style>

<?php include '../app/views/includes/footer.php'; ?>
