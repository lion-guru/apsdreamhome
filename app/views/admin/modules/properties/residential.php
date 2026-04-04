<?php
// Properties Module - Residential Properties Management
$module_title = 'Residential Properties';
$module_description = 'Manage all residential properties including houses, apartments, and villas';

// Sample data
$residential_properties = [
    [
        'id' => 'RES001',
        'title' => 'Premium 3BHK Villa',
        'type' => 'Villa',
        'location' => 'Gorakhpur, UP',
        'price' => '45,00,000',
        'area' => '1500 sqft',
        'bedrooms' => 3,
        'bathrooms' => 2,
        'status' => 'active',
        'listed_date' => '2024-01-15',
        'owner' => 'Ramesh Kumar',
        'contact' => '+91 92771 21112'
    ],
    [
        'id' => 'RES002',
        'title' => 'Modern 2BHK Apartment',
        'type' => 'Apartment',
        'location' => 'Lucknow, UP',
        'price' => '28,00,000',
        'area' => '850 sqft',
        'bedrooms' => 2,
        'bathrooms' => 2,
        'status' => 'active',
        'listed_date' => '2024-01-10',
        'owner' => 'Priya Singh',
        'contact' => '+91 98765 43211'
    ],
    [
        'id' => 'RES003',
        'title' => 'Luxury 4BHK Duplex',
        'type' => 'Duplex',
        'location' => 'Kanpur, UP',
        'price' => '65,00,000',
        'area' => '2200 sqft',
        'bedrooms' => 4,
        'bathrooms' => 3,
        'status' => 'sold',
        'listed_date' => '2024-01-05',
        'owner' => 'Amit Verma',
        'contact' => '+91 98765 43212'
    ]
];
?>

<!-- Residential Properties Module -->
<div class="properties-module">
    <!-- Module Header -->
    <div class="module-header-actions">
        <div class="module-header">
            <h3><i class="fas fa-building"></i> Residential Properties</h3>
            <p class="text-muted">Manage houses, apartments, villas and other residential properties</p>
        </div>

        <div class="module-actions">
            <button class="btn btn-primary" onclick="openAddPropertyModal()">
                <i class="fas fa-plus"></i> Add Property
            </button>
            <button class="btn btn-outline-secondary" onclick="exportProperties()">
                <i class="fas fa-download"></i> Export
            </button>
            <div class="dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="fas fa-filter"></i> Filter
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#" onclick="filterProperties('active')">Active Only</a></li>
                    <li><a class="dropdown-item" href="#" onclick="filterProperties('sold')">Sold Only</a></li>
                    <li><a class="dropdown-item" href="#" onclick="filterProperties('pending')">Pending Only</a></li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li><a class="dropdown-item" href="#" onclick="filterProperties('high-value')">High Value (>50L)</a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="quick-stats-cards">
        <div class="stat-card">
            <div class="stat-icon bg-primary">
                <i class="fas fa-home"></i>
            </div>
            <div class="stat-content">
                <h4><?php echo count($residential_properties); ?></h4>
                <p>Total Properties</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon bg-success">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-content">
                <h4><?php echo count(array_filter($residential_properties, fn($p) => $p['status'] === 'active')); ?></h4>
                <p>Active Listings</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon bg-warning">
                <i class="fas fa-handshake"></i>
            </div>
            <div class="stat-content">
                <h4><?php echo count(array_filter($residential_properties, fn($p) => $p['status'] === 'sold')); ?></h4>
                <p>Sold Properties</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon bg-info">
                <i class="fas fa-rupee-sign"></i>
            </div>
            <div class="stat-content">
                <h4>₹<?php echo number_format(array_sum(array_column($residential_properties, 'price'))); ?></h4>
                <p>Total Value</p>
            </div>
        </div>
    </div>

    <!-- Properties Table -->
    <div class="properties-table-container">
        <div class="table-header">
            <h4>Properties Inventory</h4>
            <div class="table-controls">
                <input type="text" class="form-control" placeholder="Search properties..." id="propertySearch" onkeyup="searchProperties()">
                <select class="form-select" id="statusFilter" onchange="filterByStatus()">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="sold">Sold</option>
                    <option value="pending">Pending</option>
                </select>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover" id="propertiesTable">
                <thead class="table-dark">
                    <tr>
                        <th>
                            <input type="checkbox" id="selectAll" onchange="toggleAllProperties()">
                        </th>
                        <th>Property ID</th>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Location</th>
                        <th>Price</th>
                        <th>Area</th>
                        <th>Bed/Bath</th>
                        <th>Status</th>
                        <th>Owner</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($residential_properties as $property): ?>
                        <tr class="property-row" data-status="<?php echo $property['status']; ?>">
                            <td>
                                <input type="checkbox" class="property-checkbox" value="<?php echo $property['id']; ?>">
                            </td>
                            <td>
                                <span class="property-id"><?php echo $property['id']; ?></span>
                            </td>
                            <td>
                                <strong><?php echo $property['title']; ?></strong>
                            </td>
                            <td>
                                <span class="badge bg-secondary"><?php echo $property['type']; ?></span>
                            </td>
                            <td><?php echo $property['location']; ?></td>
                            <td>
                                <strong>₹<?php echo number_format(str_replace(',', '', $property['price'])); ?></strong>
                            </td>
                            <td><?php echo $property['area']; ?></td>
                            <td>
                                <?php echo $property['bedrooms']; ?>BHK /
                                <?php echo $property['bathrooms']; ?> Bath
                            </td>
                            <td>
                                <?php if ($property['status'] === 'active'): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php elseif ($property['status'] === 'sold'): ?>
                                    <span class="badge bg-warning">Sold</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Pending</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="owner-info">
                                    <div class="owner-name"><?php echo $property['owner']; ?></div>
                                    <small class="text-muted"><?php echo $property['contact']; ?></small>
                                </div>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn btn-sm btn-info" onclick="viewProperty('<?php echo $property['id']; ?>')" title="View">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-warning" onclick="editProperty('<?php echo $property['id']; ?>')" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteProperty('<?php echo $property['id']; ?>')" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Module Styles -->
<style>
    .properties-module {
        max-width: 100%;
    }

    .module-header-actions {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 2rem;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .module-header h3 {
        margin: 0 0 0.5rem 0;
        color: #2c3e50;
    }

    .module-header p {
        margin: 0;
        color: #6c757d;
    }

    .module-actions {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .quick-stats-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .quick-stats-cards .stat-card {
        background: white;
        border-radius: 8px;
        padding: 1.5rem;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        display: flex;
        align-items: center;
        gap: 1rem;
        transition: all 0.3s ease;
    }

    .quick-stats-cards .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
    }

    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.2rem;
    }

    .stat-icon.bg-primary {
        background: #007bff;
    }

    .stat-icon.bg-success {
        background: #28a745;
    }

    .stat-icon.bg-warning {
        background: #ffc107;
        color: #000;
    }

    .stat-icon.bg-info {
        background: #17a2b8;
    }

    .stat-content h4 {
        margin: 0;
        font-size: 1.5rem;
        font-weight: 700;
        color: #2c3e50;
    }

    .stat-content p {
        margin: 0;
        color: #6c757d;
        font-size: 0.9rem;
    }

    .properties-table-container {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    .table-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1.5rem;
        border-bottom: 1px solid #dee2e6;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .table-header h4 {
        margin: 0;
        color: #2c3e50;
    }

    .table-controls {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .table-controls input,
    .table-controls select {
        min-width: 200px;
    }

    .property-id {
        background: #007bff;
        color: white;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .owner-info .owner-name {
        font-weight: 600;
        color: #2c3e50;
    }

    .action-buttons {
        display: flex;
        gap: 0.25rem;
    }

    .action-buttons .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.8rem;
    }

    @media (max-width: 768px) {
        .module-header-actions {
            flex-direction: column;
            align-items: stretch;
        }

        .quick-stats-cards {
            grid-template-columns: 1fr;
        }

        .table-header {
            flex-direction: column;
            align-items: stretch;
        }

        .table-controls {
            flex-direction: column;
        }

        .table-controls input,
        .table-controls select {
            min-width: auto;
        }

        .action-buttons {
            flex-direction: column;
        }
    }
</style>

<!-- Module JavaScript -->
<script>
    let allProperties = <?php echo json_encode($residential_properties); ?>;
    let filteredProperties = [...allProperties];

    function openAddPropertyModal() {
        showNotification('Opening add property modal...', 'info');
        // Here you would open a modal with property form
    }

    function exportProperties() {
        showNotification('Exporting residential properties...', 'info');
        setTimeout(() => {
            showNotification('Properties exported successfully', 'success');
        }, 1500);
    }

    function filterProperties(status) {
        if (status === 'high-value') {
            filteredProperties = allProperties.filter(p => parseInt(p.price.replace(/,/g, '')) > 5000000);
        } else {
            filteredProperties = allProperties.filter(p => p.status === status);
        }
        renderPropertiesTable();
        showNotification(`Filtered by ${status}`, 'info');
    }

    function filterByStatus() {
        const status = document.getElementById('statusFilter').value;
        if (status === '') {
            filteredProperties = [...allProperties];
        } else {
            filteredProperties = allProperties.filter(p => p.status === status);
        }
        renderPropertiesTable();
    }

    function searchProperties() {
        const searchTerm = document.getElementById('propertySearch').value.toLowerCase();
        filteredProperties = allProperties.filter(p =>
            p.title.toLowerCase().includes(searchTerm) ||
            p.location.toLowerCase().includes(searchTerm) ||
            p.owner.toLowerCase().includes(searchTerm) ||
            p.id.toLowerCase().includes(searchTerm)
        );
        renderPropertiesTable();
    }

    function toggleAllProperties() {
        const selectAll = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.property-checkbox');

        checkboxes.forEach(checkbox => {
            checkbox.checked = selectAll.checked;
        });
    }

    function viewProperty(propertyId) {
        const property = allProperties.find(p => p.id === propertyId);
        showNotification(`Viewing property: ${property.title}`, 'info');
        // Here you would open property details modal
    }

    function editProperty(propertyId) {
        const property = allProperties.find(p => p.id === propertyId);
        showNotification(`Editing property: ${property.title}`, 'info');
        // Here you would open edit property modal
    }

    function deleteProperty(propertyId) {
        const property = allProperties.find(p => p.id === propertyId);
        if (confirm(`Are you sure you want to delete property: ${property.title}?`)) {
            showNotification(`Property ${propertyId} deleted`, 'success');
            // Here you would make API call to delete
        }
    }

    function renderPropertiesTable() {
        const tbody = document.querySelector('#propertiesTable tbody');
        tbody.innerHTML = '';

        filteredProperties.forEach(property => {
            const row = createPropertyRow(property);
            tbody.appendChild(row);
        });
    }

    function createPropertyRow(property) {
        const tr = document.createElement('tr');
        tr.className = 'property-row';
        tr.setAttribute('data-status', property.status);

        tr.innerHTML = `
            <td>
                <input type="checkbox" class="property-checkbox" value="${property.id}">
            </td>
            <td>
                <span class="property-id">${property.id}</span>
            </td>
            <td>
                <strong>${property.title}</strong>
            </td>
            <td>
                <span class="badge bg-secondary">${property.type}</span>
            </td>
            <td>${property.location}</td>
            <td>
                <strong>₹${parseInt(property.price).toLocaleString('en-IN')}</strong>
            </td>
            <td>${property.area}</td>
            <td>${property.bedrooms}BHK / ${property.bathrooms} Bath</td>
            <td>
                ${getStatusBadge(property.status)}
            </td>
            <td>
                <div class="owner-info">
                    <div class="owner-name">${property.owner}</div>
                    <small class="text-muted">${property.contact}</small>
                </div>
            </td>
            <td>
                <div class="action-buttons">
                    <button class="btn btn-sm btn-info" onclick="viewProperty('${property.id}')" title="View">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-warning" onclick="editProperty('${property.id}')" title="Edit">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="deleteProperty('${property.id}')" title="Delete">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        `;

        return tr;
    }

    function getStatusBadge(status) {
        const badges = {
            'active': '<span class="badge bg-success">Active</span>',
            'sold': '<span class="badge bg-warning">Sold</span>',
            'pending': '<span class="badge bg-secondary">Pending</span>'
        };
        return badges[status] || badges['pending'];
    }

    function showNotification(message, type = 'info') {
        // This would use the global notification system
        console.log(`${type.toUpperCase()}: ${message}`);
    }
</script>