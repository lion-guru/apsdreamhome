<?php
/**
 * APS Dream Homes - Colony Management System
 * Enhanced colony management for APS own projects
 */

require_once 'includes/db_connection.php';
require_once 'includes/enhanced_universal_template.php';

$template = new EnhancedUniversalTemplate();

// Page metadata
$page_title = 'APS Colonies Management - Internal Dashboard';
$page_description = 'Manage APS Dream Homes colonies, plots, and project development';

$template->setTitle($page_title);
$template->setDescription($page_description);

// Sample data for colonies (in production, this would come from database)
$colonies = [
    [
        'id' => 1,
        'name' => 'Suryoday Colony',
        'location' => 'Gorakhpur, Uttar Pradesh',
        'total_area' => '25 Acres',
        'developed_area' => '25 Acres',
        'total_plots' => 200,
        'sold_plots' => 200,
        'available_plots' => 0,
        'completion_status' => 'Complete',
        'status' => 'sold_out',
        'starting_price' => '₹12,00,000',
        'current_price' => '₹15,00,000',
        'revenue_generated' => '₹30,00,00,000',
        'completion_date' => '2023-12-31',
        'features' => ['24/7 Security', 'Wide Roads', 'Green Spaces', 'Community Hall', 'Children Play Area'],
        'amenities' => ['Power Backup', 'Water Supply', 'Sewage System', 'Street Lights', 'Landscaped Gardens'],
        'coordinates' => ['latitude' => 26.7606, 'longitude' => 83.3732],
        'manager' => 'Rajesh Kumar',
        'contact' => '9876543210'
    ],
    [
        'id' => 2,
        'name' => 'Raghunath Nagri',
        'location' => 'Gorakhpur, Uttar Pradesh',
        'total_area' => '30 Acres',
        'developed_area' => '25 Acres',
        'total_plots' => 250,
        'sold_plots' => 225,
        'available_plots' => 25,
        'completion_status' => 'Phase 2 Ongoing',
        'status' => 'active',
        'starting_price' => '₹10,00,000',
        'current_price' => '₹13,00,000',
        'revenue_generated' => '₹29,25,00,000',
        'completion_date' => '2024-06-30',
        'features' => ['Prime Location', 'Modern Infrastructure', 'Investment Opportunity', 'Easy Financing'],
        'amenities' => ['Club House', 'Swimming Pool', 'Gym', 'Jogging Track', 'Security'],
        'coordinates' => ['latitude' => 26.7700, 'longitude' => 83.3800],
        'manager' => 'Priya Sharma',
        'contact' => '9876543211'
    ],
    [
        'id' => 3,
        'name' => 'Brajradha Nagri',
        'location' => 'Gorakhpur, Uttar Pradesh',
        'total_area' => '35 Acres',
        'developed_area' => '20 Acres',
        'total_plots' => 300,
        'sold_plots' => 220,
        'available_plots' => 80,
        'completion_status' => 'Development Started',
        'status' => 'active',
        'starting_price' => '₹15,00,000',
        'current_price' => '₹18,00,000',
        'revenue_generated' => '₹39,60,00,000',
        'completion_date' => '2024-12-31',
        'features' => ['Premium Location', 'High Appreciation', 'Modern Design', 'Luxury Living'],
        'amenities' => ['Community Hall', 'Landscaped Gardens', 'Security', 'Power Backup', 'Water Management'],
        'coordinates' => ['latitude' => 26.7500, 'longitude' => 83.3600],
        'manager' => 'Amit Singh',
        'contact' => '9876543212'
    ],
    [
        'id' => 4,
        'name' => 'Stuti Bihar Sonbarsa',
        'location' => 'Sonbarsa, Gorakhpur, Uttar Pradesh',
        'total_area' => '20 Acres',
        'developed_area' => '20 Acres',
        'total_plots' => 150,
        'sold_plots' => 150,
        'available_plots' => 0,
        'completion_status' => 'Complete',
        'status' => 'sold_out',
        'starting_price' => '₹8,00,000',
        'current_price' => '₹11,00,000',
        'revenue_generated' => '₹16,50,00,000',
        'completion_date' => '2023-06-30',
        'features' => ['Peaceful Environment', 'Complete Infrastructure', 'High Appreciation', 'Sold Out'],
        'amenities' => ['Complete Infrastructure', 'Roads', 'Electricity', 'Water', 'Sewage'],
        'coordinates' => ['latitude' => 26.7800, 'longitude' => 83.3900],
        'manager' => 'Sneha Patel',
        'contact' => '9876543213'
    ]
];

// Calculate totals
$total_colonies = count($colonies);
$total_plots = array_sum(array_column($colonies, 'total_plots'));
$total_sold = array_sum(array_column($colonies, 'sold_plots'));
$total_available = array_sum(array_column($colonies, 'available_plots'));
$total_revenue = array_sum(array_map(function($colony) {
    return (float)str_replace(['₹', ','], '', $colony['revenue_generated']);
}, $colonies));

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .management-hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 0;
        }

        .colony-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }

        .colony-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }

        .colony-header {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 20px;
            position: relative;
        }

        .status-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .status-active { background: rgba(40, 167, 69, 0.9); }
        .status-sold-out { background: rgba(220, 53, 69, 0.9); }
        .status-ongoing { background: rgba(255, 193, 7, 0.9); }

        .colony-title {
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .colony-location {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .colony-body {
            padding: 20px;
        }

        .colony-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .stat-item {
            text-align: center;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .stat-value {
            font-size: 1.2rem;
            font-weight: 700;
            color: #667eea;
            display: block;
        }

        .stat-label {
            font-size: 0.8rem;
            color: #666;
            text-transform: uppercase;
        }

        .progress-container {
            margin-bottom: 15px;
        }

        .progress-label {
            font-size: 0.9rem;
            margin-bottom: 5px;
            display: flex;
            justify-content: space-between;
        }

        .progress {
            height: 8px;
            border-radius: 4px;
        }

        .amenities-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 15px;
        }

        .amenity-tag {
            background: #e9ecef;
            color: #495057;
            padding: 3px 8px;
            border-radius: 10px;
            font-size: 0.8rem;
        }

        .colony-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn-action {
            flex: 1;
            min-width: 120px;
        }

        .summary-cards {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .summary-stat {
            text-align: center;
            padding: 20px;
        }

        .summary-number {
            font-size: 2.5rem;
            font-weight: 800;
            color: #667eea;
            display: block;
            margin-bottom: 5px;
        }

        .summary-label {
            color: #666;
            font-size: 1rem;
            font-weight: 600;
        }

        .location-map {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            margin-bottom: 20px;
        }

        .map-placeholder {
            background: #dee2e6;
            height: 200px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6c757d;
            font-size: 1.1rem;
        }

        @media (max-width: 768px) {
            .colony-stats {
                grid-template-columns: repeat(2, 1fr);
            }

            .colony-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <!-- Hero Section -->
    <section class="management-hero">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center">
                    <h1 class="display-5 fw-bold mb-3">
                        <i class="fas fa-project-diagram me-3"></i>APS Colonies Management
                    </h1>
                    <p class="lead">
                        Complete management system for APS Dream Homes colonies and projects
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Summary Statistics -->
    <section class="py-4">
        <div class="container">
            <div class="summary-cards">
                <div class="row">
                    <div class="col-lg-3 col-md-6">
                        <div class="summary-stat">
                            <span class="summary-number"><?php echo $total_colonies; ?></span>
                            <span class="summary-label">Total Colonies</span>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="summary-stat">
                            <span class="summary-number"><?php echo number_format($total_plots); ?></span>
                            <span class="summary-label">Total Plots</span>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="summary-stat">
                            <span class="summary-number"><?php echo number_format($total_sold); ?></span>
                            <span class="summary-label">Plots Sold</span>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="summary-stat">
                            <span class="summary-number">₹<?php echo number_format($total_revenue/10000000, 1); ?>Cr</span>
                            <span class="summary-label">Total Revenue</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Colonies Management -->
    <section class="py-4">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-city me-2"></i>Our Colonies</h2>
                <button class="btn btn-primary" onclick="showAddColonyModal()">
                    <i class="fas fa-plus me-2"></i>Add New Colony
                </button>
            </div>

            <div class="row">
                <?php foreach ($colonies as $colony): ?>
                    <div class="col-lg-6">
                        <div class="colony-card">
                            <div class="colony-header">
                                <div class="colony-title"><?php echo $colony['name']; ?></div>
                                <div class="colony-location">
                                    <i class="fas fa-map-marker-alt me-2"></i><?php echo $colony['location']; ?>
                                </div>
                                <div class="status-badge status-<?php echo $colony['status'] === 'sold_out' ? 'sold-out' : ($colony['status'] === 'active' ? 'active' : 'ongoing'); ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $colony['status'])); ?>
                                </div>
                            </div>

                            <div class="colony-body">
                                <div class="colony-stats">
                                    <div class="stat-item">
                                        <span class="stat-value"><?php echo $colony['total_area']; ?></span>
                                        <span class="stat-label">Total Area</span>
                                    </div>
                                    <div class="stat-item">
                                        <span class="stat-value"><?php echo $colony['sold_plots']; ?>/<?php echo $colony['total_plots']; ?></span>
                                        <span class="stat-label">Plots Sold</span>
                                    </div>
                                    <div class="stat-item">
                                        <span class="stat-value"><?php echo $colony['available_plots']; ?></span>
                                        <span class="stat-label">Available</span>
                                    </div>
                                    <div class="stat-item">
                                        <span class="stat-value"><?php echo $colony['current_price']; ?></span>
                                        <span class="stat-label">Current Price</span>
                                    </div>
                                </div>

                                <div class="progress-container">
                                    <div class="progress-label">
                                        <span>Sales Progress</span>
                                        <span><?php echo round(($colony['sold_plots'] / $colony['total_plots']) * 100, 1); ?>%</span>
                                    </div>
                                    <div class="progress">
                                        <div class="progress-bar bg-success" style="width: <?php echo ($colony['sold_plots'] / $colony['total_plots']) * 100; ?>%"></div>
                                    </div>
                                </div>

                                <div class="amenities-list">
                                    <?php foreach (array_slice($colony['features'], 0, 3) as $feature): ?>
                                        <span class="amenity-tag"><?php echo $feature; ?></span>
                                    <?php endforeach; ?>
                                </div>

                                <div class="colony-actions">
                                    <button class="btn btn-outline-primary btn-action" onclick="viewColonyDetails(<?php echo $colony['id']; ?>)">
                                        <i class="fas fa-eye me-1"></i>View Details
                                    </button>
                                    <button class="btn btn-outline-success btn-action" onclick="managePlots(<?php echo $colony['id']; ?>)">
                                        <i class="fas fa-map me-1"></i>Manage Plots
                                    </button>
                                    <?php if ($colony['status'] === 'active'): ?>
                                        <button class="btn btn-outline-info btn-action" onclick="viewAnalytics(<?php echo $colony['id']; ?>)">
                                            <i class="fas fa-chart-line me-1"></i>Analytics
                                        </button>
                                    <?php endif; ?>
                                </div>

                                <div class="mt-3">
                                    <small class="text-muted">
                                        <i class="fas fa-user me-1"></i>Manager: <?php echo $colony['manager']; ?> |
                                        <i class="fas fa-phone ms-2 me-1"></i><?php echo $colony['contact']; ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Location Map -->
    <section class="py-4">
        <div class="container">
            <div class="location-map">
                <h4><i class="fas fa-map-marked-alt me-2"></i>Project Locations</h4>
                <div class="map-placeholder">
                    <i class="fas fa-map fa-2x mb-2"></i>
                    <p>Interactive map showing all APS Dream Homes colonies</p>
                    <small class="text-muted">Gorakhpur | Lucknow | Sonbarsa</small>
                </div>
            </div>
        </div>
    </section>

    <!-- Colony Details Modal -->
    <div class="modal fade" id="colonyDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Colony Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="colonyDetailsContent">
                    <!-- Content will be loaded dynamically -->
                </div>
            </div>
        </div>
    </div>

    <!-- Add Colony Modal -->
    <div class="modal fade" id="addColonyModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Colony</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addColonyForm">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Colony Name *</label>
                                    <input type="text" class="form-control" name="name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Location *</label>
                                    <input type="text" class="form-control" name="location" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Total Area</label>
                                    <input type="text" class="form-control" name="total_area" placeholder="e.g., 25 Acres">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Total Plots</label>
                                    <input type="number" class="form-control" name="total_plots">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3"></textarea>
                        </div>

                        <div class="text-center">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save me-2"></i>Add Colony
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // View colony details
        function viewColonyDetails(colonyId) {
            // Find colony data
            const colonies = <?php echo json_encode($colonies); ?>;
            const colony = colonies.find(c => c.id == colonyId);

            if (colony) {
                const content = `
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Project Information</h6>
                            <p><strong>Name:</strong> ${colony.name}</p>
                            <p><strong>Location:</strong> ${colony.location}</p>
                            <p><strong>Total Area:</strong> ${colony.total_area}</p>
                            <p><strong>Completion:</strong> ${colony.completion_status}</p>
                            <p><strong>Manager:</strong> ${colony.manager}</p>
                        </div>
                        <div class="col-md-6">
                            <h6>Sales Performance</h6>
                            <p><strong>Sold:</strong> ${colony.sold_plots}/${colony.total_plots}</p>
                            <p><strong>Available:</strong> ${colony.available_plots}</p>
                            <p><strong>Revenue:</strong> ${colony.revenue_generated}</p>
                            <p><strong>Price Range:</strong> ${colony.starting_price} - ${colony.current_price}</p>
                        </div>
                    </div>
                `;
                document.getElementById('colonyDetailsContent').innerHTML = content;
                new bootstrap.Modal(document.getElementById('colonyDetailsModal')).show();
            }
        }

        // Manage plots
        function managePlots(colonyId) {
            alert('Plot management for colony ID: ' + colonyId);
            // Here you would redirect to plot management page
        }

        // View analytics
        function viewAnalytics(colonyId) {
            alert('Analytics view for colony ID: ' + colonyId);
            // Here you would redirect to analytics page
        }

        // Show add colony modal
        function showAddColonyModal() {
            new bootstrap.Modal(document.getElementById('addColonyModal')).show();
        }

        // Form submission
        document.getElementById('addColonyForm').addEventListener('submit', function(e) {
            e.preventDefault();

            // Here you would send the data to your server
            alert('Colony added successfully!');

            // Close modal
            bootstrap.Modal.getInstance(document.getElementById('addColonyModal')).hide();

            // Reset form
            this.reset();
        });

        // Auto-refresh data every 2 minutes for real-time updates
        setInterval(function() {
            console.log('Refreshing colony data...');
            // Here you would refresh the data from server
        }, 120000);
    </script>
</body>
</html>
