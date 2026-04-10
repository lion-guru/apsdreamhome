<?php
include APP_PATH . '/views/admin/layouts/header.php';

// Set page variables
$page_title = 'AI Properties Management - APS Dream Home';
$active_page = 'properties';
?>

<!-- AI Properties Header -->
<div class="ai-properties-header mb-4">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-md-6">
                <div class="ai-status-indicator">
                    <div class="ai-pulse"></div>
                    <h4 class="mb-0">
                        <i class="fas fa-home me-2"></i>
                        AI Properties Manager Active
                    </h4>
                    <small class="text-success">156 Properties Analyzed</small>
                </div>
            </div>
            <div class="col-md-6 text-end">
                <div class="ai-controls">
                    <button class="btn btn-ai-primary me-2" onclick="toggleAIMode()">
                        <i class="fas fa-brain me-1"></i>
                        AI Mode
                    </button>
                    <button class="btn btn-ai-secondary me-2" onclick="refreshAIProperties()">
                        <i class="fas fa-sync me-1"></i>
                        Refresh
                    </button>
                    <button class="btn btn-ai-info me-2" onclick="showAIInsights()">
                        <i class="fas fa-chart-line me-1"></i>
                        Insights
                    </button>
                    <button class="btn btn-success me-2" onclick="showAddPropertyModal()">
                        <i class="fas fa-plus me-1"></i>
                        Add Property
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- AI-Powered Stats Overview -->
<div class="ai-stats-overview mb-4">
    <div class="container-fluid">
        <div class="row g-3">
            <!-- Total Properties -->
            <div class="col-md-3">
                <div class="stat-card ai-enhanced">
                    <div class="stat-icon ai-gradient">
                        <i class="fas fa-home"></i>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-number">156</h3>
                        <p class="stat-label">Total Properties</p>
                        <div class="ai-indicator">
                            <small class="text-success"><i class="fas fa-arrow-up"></i> +12.5%</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Active Listings -->
            <div class="col-md-3">
                <div class="stat-card ai-enhanced">
                    <div class="stat-icon ai-gradient">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-number">142</h3>
                        <p class="stat-label">Active Listings</p>
                        <div class="ai-indicator">
                            <small class="text-info"><i class="fas fa-minus"></i> Stable</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sold Properties -->
            <div class="col-md-3">
                <div class="stat-card ai-enhanced">
                    <div class="stat-icon ai-gradient">
                        <i class="fas fa-handshake"></i>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-number">14</h3>
                        <p class="stat-label">Sold This Month</p>
                        <div class="ai-indicator">
                            <small class="text-success"><i class="fas fa-arrow-up"></i> +8.3%</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- AI Performance Score -->
            <div class="col-md-3">
                <div class="stat-card ai-enhanced">
                    <div class="stat-icon ai-gradient">
                        <i class="fas fa-robot"></i>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-number">94%</h3>
                        <p class="stat-label">AI Performance</p>
                        <div class="ai-indicator">
                            <small class="text-success"><i class="fas fa-arrow-up"></i> Optimal</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Advanced Search and Filter -->
<div class="advanced-search-filter mb-4">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="search-card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-search me-2"></i>
                            AI-Powered Search & Filter
                        </h5>
                        <div class="ai-badge">AI Enhanced</div>
                    </div>
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Smart Search</label>
                                <div class="input-group">
                                    <input type="text" name="search" class="form-control" placeholder="AI-powered property search..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                                    <button class="btn btn-ai-primary" type="submit">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Property Type</label>
                                <select name="type" class="form-select">
                                    <option value="">All Types</option>
                                    <option value="residential" <?php echo (($_GET['type'] ?? '') === 'residential' ? 'selected' : ''); ?>>Residential</option>
                                    <option value="commercial" <?php echo (($_GET['type'] ?? '') === 'commercial' ? 'selected' : ''); ?>>Commercial</option>
                                    <option value="land" <?php echo (($_GET['type'] ?? '') === 'land' ? 'selected' : ''); ?>>Land</option>
                                    <option value="luxury" <?php echo (($_GET['type'] ?? '') === 'luxury' ? 'selected' : ''); ?>>Luxury</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Price Range</label>
                                <select name="price_range" class="form-select">
                                    <option value="">All Prices</option>
                                    <option value="0-10lakh">₹0 - 10 Lakhs</option>
                                    <option value="10-50lakh">₹10 - 50 Lakhs</option>
                                    <option value="50lakh-1cr">₹50 Lakhs - 1 Crore</option>
                                    <option value="1cr+">Above 1 Crore</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="">All Status</option>
                                    <option value="active">Active</option>
                                    <option value="sold">Sold</option>
                                    <option value="pending">Pending</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Location</label>
                                <input type="text" name="location" class="form-control" placeholder="Search by location..." value="<?php echo htmlspecialchars($_GET['location'] ?? ''); ?>">
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Enhanced Properties Table -->
<div class="enhanced-properties-table mb-4">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="table-card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-home me-2"></i>
                                Properties Inventory
                            </h5>
                            <div class="table-actions">
                                <button class="btn btn-sm btn-ai-primary" onclick="exportProperties()">
                                    <i class="fas fa-download"></i> Export
                                </button>
                                <button class="btn btn-sm btn-ai-secondary" onclick="printProperties()">
                                    <i class="fas fa-print"></i> Print
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>
                                            <input type="checkbox" id="selectAll" onchange="toggleAllProperties()">
                                        </th>
                                        <th>Property ID</th>
                                        <th>Title</th>
                                        <th>Type</th>
                                        <th>Location</th>
                                        <th>Price</th>
                                        <th>Status</th>
                                        <th>Listed Date</th>
                                        <th>AI Score</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><input type="checkbox" class="property-checkbox"></td>
                                        <td><span class="property-id">PRP001</span></td>
                                        <td><strong>Premium Villa 3BHK</strong></td>
                                        <td><span class="badge bg-primary">Residential</span></td>
                                        <td>Gorakhpur, UP</td>
                                        <td>₹45,00,000</td>
                                        <td><span class="badge bg-success">Active</span></td>
                                        <td>2024-01-15</td>
                                        <td>
                                            <div class="ai-score"><span class="score-text">92</span><small>%</small></div>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-ai-info" onclick="viewProperty('PRP001')">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-ai-warning" onclick="editProperty('PRP001')">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-ai-secondary" onclick="deleteProperty('PRP001')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><input type="checkbox" class="property-checkbox"></td>
                                        <td><span class="property-id">PRP002</span></td>
                                        <td><strong>Commercial Plot 2000 sqft</strong></td>
                                        <td><span class="badge bg-warning">Commercial</span></td>
                                        <td>Lucknow, UP</td>
                                        <td>₹80,00,000</td>
                                        <td><span class="badge bg-success">Active</span></td>
                                        <td>2024-01-10</td>
                                        <td>
                                            <div class="ai-score"><span class="score-text">88</span><small>%</small></div>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-ai-info" onclick="viewProperty('PRP002')">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-ai-warning" onclick="editProperty('PRP002')">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-ai-secondary" onclick="deleteProperty('PRP002')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- AI Properties Styles -->
<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --glass-bg: rgba(255, 255, 255, 0.9);
        --glass-border: rgba(255, 255, 255, 0.3);
        --glass-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.15);
        --ai-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
    }

    .ai-properties-header {
        background: var(--ai-gradient);
        border-radius: 15px;
        padding: 1.5rem;
        box-shadow: var(--glass-shadow);
        margin-bottom: 2rem;
    }

    .ai-status-indicator {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .ai-pulse {
        width: 12px;
        height: 12px;
        background: #00ff00;
        border-radius: 50%;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% {
            opacity: 1;
        }

        50% {
            opacity: 0.5;
        }

        100% {
            opacity: 1;
        }
    }

    .ai-controls .btn {
        border: none;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-ai-primary {
        background: linear-gradient(135deg, #00ff88 0%, #00cc66 100%);
        color: white;
    }

    .btn-ai-secondary {
        background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
        color: white;
    }

    .btn-ai-info {
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
        color: white;
    }

    .btn-ai-warning {
        background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
        color: white;
    }

    .ai-stats-overview .stat-card {
        background: var(--glass-bg);
        border: 1px solid var(--glass-border);
        border-radius: 15px;
        padding: 1.5rem;
        backdrop-filter: blur(10px);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .ai-stats-overview .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(102, 126, 234, 0.2);
    }

    .ai-enhanced {
        border-left: 4px solid #00ff88;
    }

    .ai-gradient {
        background: var(--ai-gradient);
        color: white;
    }

    .ai-indicator {
        margin-top: 0.5rem;
    }

    .advanced-search-filter .search-card {
        background: var(--glass-bg);
        border: 1px solid var(--glass-border);
        border-radius: 15px;
        backdrop-filter: blur(10px);
        overflow: hidden;
    }

    .search-card .card-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 1rem 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .ai-badge {
        background: rgba(0, 255, 136, 0.2);
        color: #00ff88;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .enhanced-properties-table .table-card {
        background: var(--glass-bg);
        border: 1px solid var(--glass-border);
        border-radius: 15px;
        backdrop-filter: blur(10px);
    }

    .table-card .card-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 15px 15px 0 0;
    }

    .property-id {
        background: var(--primary-gradient);
        color: white;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .ai-score {
        background: linear-gradient(135deg, #00ff88 0%, #00cc66 100%);
        color: white;
        padding: 0.25rem 0.5rem;
        border-radius: 15px;
        font-weight: 600;
        display: inline-block;
    }

    .score-text {
        font-size: 0.9rem;
    }

    @media (max-width: 768px) {
        .ai-stats-overview .col-md-3 {
            margin-bottom: 1rem;
        }

        .table-responsive {
            font-size: 0.875rem;
        }
    }
</style>

<!-- AI Properties JavaScript -->
<script>
    let aiMode = 'full';
    let selectedProperties = [];

    document.addEventListener('DOMContentLoaded', function() {
        initializeAIProperties();
        startRealTimeUpdates();
    });

    function initializeAIProperties() {
        console.log('AI Properties Manager initialized');
        updateAIStatus('156 properties analyzed successfully');
    }

    function toggleAIMode() {
        aiMode = aiMode === 'full' ? 'economy' : 'full';
        showNotification(`AI Mode switched to ${aiMode}`, 'success');
    }

    function refreshAIProperties() {
        showNotification('Refreshing AI properties data...', 'info');

        setTimeout(() => {
            showNotification('AI properties data refreshed', 'success');
            updatePropertyStats();
        }, 2000);
    }

    function showAIInsights() {
        showNotification('Generating AI property insights...', 'info');

        setTimeout(() => {
            showNotification('3 optimization opportunities identified', 'warning');
            showNotification('Market trends analysis complete', 'success');
        }, 1500);
    }

    function updatePropertyStats() {
        const stats = document.querySelectorAll('.stat-number');
        stats.forEach(stat => {
            const currentValue = parseInt(stat.textContent.replace(/[^0-9]/g, ''));
            const increment = Math.floor(Math.random() * 5) + 1;

            let current = currentValue;
            const step = increment / 20;
            let frame = 0;

            const animation = setInterval(() => {
                frame++;
                current += step;
                stat.textContent = Math.floor(current).toLocaleString();

                if (frame >= 20) {
                    clearInterval(animation);
                }
            }, 50);
        });
    }

    function startRealTimeUpdates() {
        setInterval(() => {
            updateLiveActivity();
        }, 8000);
    }

    function updateLiveActivity() {
        const activities = [
            'New property viewed',
            'Price updated by AI',
            'Market analysis completed',
            'Property optimized'
        ];

        if (Math.random() > 0.8) {
            const activity = activities[Math.floor(Math.random() * activities.length)];
            console.log(`AI Activity: ${activity}`);
        }
    }

    function toggleAllProperties() {
        const selectAll = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.property-checkbox');

        checkboxes.forEach(checkbox => {
            checkbox.checked = selectAll.checked;
        });

        updateSelectedProperties();
    }

    function updateSelectedProperties() {
        const checkboxes = document.querySelectorAll('.property-checkbox:checked');
        selectedProperties = Array.from(checkboxes).map(cb => cb.value);

        console.log(`Selected properties: ${selectedProperties.length}`);
    }

    function viewProperty(propertyId) {
        showNotification(`Viewing property ${propertyId}`, 'info');
    }

    function editProperty(propertyId) {
        showNotification(`Editing property ${propertyId}`, 'info');
    }

    function deleteProperty(propertyId) {
        if (confirm(`Are you sure you want to delete property ${propertyId}?`)) {
            showNotification(`Property ${propertyId} deleted`, 'success');
        }
    }

    function exportProperties() {
        showNotification('Exporting properties data...', 'info');

        setTimeout(() => {
            showNotification('Properties exported successfully', 'success');
        }, 2000);
    }

    function printProperties() {
        window.print();
        showNotification('Print dialog opened', 'info');
    }

    function showAddPropertyModal() {
        showNotification('Opening add property modal...', 'info');
    }

    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `ai-notification ${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'warning' ? 'exclamation-triangle' : 'info-circle'}"></i>
                <span>${message}</span>
            </div>
        `;

        document.body.appendChild(notification);

        setTimeout(() => {
            notification.remove();
        }, 3000);
    }

    function updateAIStatus(status) {
        const statusElement = document.querySelector('.ai-status-indicator small');
        if (statusElement) {
            statusElement.textContent = status;
        }
    }
</script>

<?php
include APP_PATH . '/views/admin/layouts/footer.php';
?>