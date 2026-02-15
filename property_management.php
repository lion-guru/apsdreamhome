<?php

/**
 * Property Management System
 * Comprehensive management for both company and resell properties
 */

require_once 'includes/config.php';
require_once 'includes/associate_permissions.php';
require_once 'includes/hybrid_commission_system.php';

// Initialize database connection
$db = \App\Core\App::database();

session_start();
if (!isset($_SESSION['associate_logged_in']) || $_SESSION['associate_logged_in'] !== true) {
    header("Location: associate_login.php");
    exit();
}

$associate_id = $_SESSION['associate_id'];
$associate_name = $_SESSION['associate_name'];

// Initialize hybrid commission system
$hybrid_system = new HybridRealEstateCommission($db);

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_property'])) {
        $result = addProperty($_POST);
        if ($result['success']) {
            $_SESSION['success_message'] = $result['message'];
            header("Location: property_management.php?added=1");
            exit();
        } else {
            $_SESSION['error_message'] = $result['message'];
        }
    }

    if (isset($_POST['update_property'])) {
        $result = updateProperty($_POST);
        if ($result['success']) {
            $_SESSION['success_message'] = $result['message'];
            header("Location: property_management.php?updated=1");
            exit();
        } else {
            $_SESSION['error_message'] = $result['message'];
        }
    }

    if (isset($_POST['record_sale'])) {
        $result = recordPropertySale($_POST);
        if ($result['success']) {
            $_SESSION['success_message'] = $result['message'];
            header("Location: property_management.php?sale_recorded=1");
            exit();
        } else {
            $_SESSION['error_message'] = $result['message'];
        }
    }
}

function addProperty($data)
{
    global $hybrid_system;
    $db = \App\Core\App::database();

    try {
        $property_id = $db->insert('real_estate_properties', [
            'property_code' => $data['property_code'],
            'property_name' => $data['property_name'],
            'property_type' => $data['property_type'],
            'property_category' => $data['property_category'],
            'location' => $data['location'],
            'area_sqft' => $data['area_sqft'],
            'rate_per_sqft' => $data['rate_per_sqft'],
            'total_value' => $data['total_value'],
            'development_cost' => $data['development_cost'],
            'commission_percentage' => $data['commission_percentage'],
            'status' => $data['status']
        ]);

        // Save development cost breakdown if provided
        if (isset($data['cost_breakdown']) && is_array($data['cost_breakdown'])) {
            $hybrid_system->saveDevelopmentCosts($property_id, $data['cost_breakdown']);
        }

        return ['success' => true, 'message' => 'Property added successfully'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error adding property: ' . $e->getMessage()];
    }
}

function updateProperty($data)
{
    global $hybrid_system;
    $db = \App\Core\App::database();

    try {
        $db->update('real_estate_properties', [
            'property_name' => $data['property_name'],
            'property_category' => $data['property_category'],
            'location' => $data['location'],
            'area_sqft' => $data['area_sqft'],
            'rate_per_sqft' => $data['rate_per_sqft'],
            'total_value' => $data['total_value'],
            'development_cost' => $data['development_cost'],
            'commission_percentage' => $data['commission_percentage'],
            'status' => $data['status']
        ], 'id = :id', ['id' => $data['property_id']]);

        return ['success' => true, 'message' => 'Property updated successfully'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error updating property: ' . $e->getMessage()];
    }
}

function recordPropertySale($data)
{
    global $hybrid_system;
    $db = \App\Core\App::database();

    try {
        $property_id = intval($data['property_id']);
        $associate_id = intval($data['associate_id']);
        $customer_id = intval($data['customer_id']);
        $sale_amount = floatval($data['sale_amount']);

        // Calculate commission
        $commission_result = $hybrid_system->calculateCommission($associate_id, $property_id, $sale_amount, $customer_id);

        if ($commission_result['success']) {
            // Update property status
            $db->execute("UPDATE real_estate_properties SET status = 'sold' WHERE id = :id", ['id' => $property_id]);

            return ['success' => true, 'message' => 'Sale recorded and commission calculated successfully'];
        }

        return ['success' => false, 'message' => 'Error calculating commission'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

// Get properties data
$company_properties = getPropertiesByType('company');
$resell_properties = getPropertiesByType('resell');
$all_properties = array_merge($company_properties, $resell_properties);

function getPropertiesByType($type)
{
    $db = \App\Core\App::database();
    return $db->fetchAll("SELECT * FROM real_estate_properties WHERE property_type = :type ORDER BY created_at DESC", ['type' => $type]);
}

// Get associates for dropdown
$db = \App\Core\App::database();
$associates = $db->fetchAll("SELECT id, full_name as name FROM mlm_agents WHERE status = 'active'");

// Get customers for dropdown
$customers = $db->fetchAll("SELECT id, name FROM customers LIMIT 100");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property Management - APS Dream Homes</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --info-color: #17a2b8;
            --dark-color: #343a40;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .dashboard-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            margin: 20px 0;
            overflow: hidden;
        }

        .property-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin: 1rem 0;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            border-left: 4px solid var(--info-color);
            transition: transform 0.3s ease;
        }

        .property-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        .property-card.company {
            border-left-color: var(--success-color);
        }

        .property-card.resell {
            border-left-color: var(--warning-color);
        }

        .badge-company {
            background: linear-gradient(45deg, var(--success-color), #20c997);
            color: white;
        }

        .badge-resell {
            background: linear-gradient(45deg, var(--warning-color), #fd7e14);
            color: white;
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .status-available {
            background: linear-gradient(45deg, var(--success-color), #20c997);
            color: white;
        }

        .status-sold {
            background: linear-gradient(45deg, var(--danger-color), #dc3545);
            color: white;
        }

        .status-booked {
            background: linear-gradient(45deg, var(--warning-color), #fd7e14);
            color: white;
        }

        .btn-add-property {
            background: linear-gradient(135deg, var(--success-color), #20c997);
            border: none;
            color: white;
            padding: 1rem 2rem;
            border-radius: 25px;
            font-size: 1.1rem;
            font-weight: 500;
        }

        .btn-add-property:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
            color: white;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .table th {
            background-color: #f8f9fa;
            border-top: none;
            font-weight: 600;
            color: var(--dark-color);
        }

        .modal-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
        }

        .modal-title {
            color: white;
        }

        .btn-primary-custom {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
            color: white;
        }

        .btn-primary-custom:hover {
            color: white;
            transform: translateY(-1px);
        }
    </style>
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="associate_dashboard.php">
                <i class="fas fa-home me-2"></i>APS Dream Homes
            </a>
            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle me-1"></i>
                        <?php echo htmlspecialchars($associate_name); ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="associate_dashboard.php">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a></li>
                        <li><a class="dropdown-item" href="hybrid_commission_dashboard.php">
                                <i class="fas fa-chart-line me-2"></i>Commission Dashboard
                            </a></li>
                        <li><a class="dropdown-item" href="development_cost_calculator.php">
                                <i class="fas fa-calculator me-2"></i>Cost Calculator
                            </a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item" href="associate_logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container-fluid mt-5">
        <div class="row justify-content-center">
            <div class="col-lg-12">
                <div class="dashboard-container">
                    <div class="p-4">
                        <!-- Header -->
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h1 class="mb-1">
                                    <i class="fas fa-building text-primary me-2"></i>Property Management
                                </h1>
                                <p class="text-muted mb-0">
                                    Manage your company and resell properties
                                </p>
                            </div>
                            <button class="btn btn-add-property" data-bs-toggle="modal" data-bs-target="#addPropertyModal">
                                <i class="fas fa-plus me-2"></i>Add New Property
                            </button>
                        </div>

                        <!-- Alerts -->
                        <?php if (isset($_SESSION['success_message'])): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i><?php echo $_SESSION['success_message']; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php unset($_SESSION['success_message']);
                        endif; ?>

                        <?php if (isset($_SESSION['error_message'])): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i><?php echo $_SESSION['error_message']; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php unset($_SESSION['error_message']);
                        endif; ?>

                        <!-- Property Statistics -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="property-card text-center">
                                    <i class="fas fa-building fa-2x text-success mb-2"></i>
                                    <h4><?php echo count($company_properties); ?></h4>
                                    <p class="mb-0">Company Properties</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="property-card text-center">
                                    <i class="fas fa-home fa-2x text-warning mb-2"></i>
                                    <h4><?php echo count($resell_properties); ?></h4>
                                    <p class="mb-0">Resell Properties</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="property-card text-center">
                                    <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                                    <h4>
                                        <?php
                                        $available = array_filter($all_properties, function ($p) {
                                            return $p['status'] === 'available';
                                        });
                                        echo count($available);
                                        ?>
                                    </h4>
                                    <p class="mb-0">Available</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="property-card text-center">
                                    <i class="fas fa-sold fa-2x text-info mb-2"></i>
                                    <h4>
                                        <?php
                                        $sold = array_filter($all_properties, function ($p) {
                                            return $p['status'] === 'sold';
                                        });
                                        echo count($sold);
                                        ?>
                                    </h4>
                                    <p class="mb-0">Sold</p>
                                </div>
                            </div>
                        </div>

                        <!-- Properties Table -->
                        <div class="property-card">
                            <h5 class="mb-3">
                                <i class="fas fa-list me-2"></i>All Properties
                            </h5>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Property Code</th>
                                            <th>Name</th>
                                            <th>Type</th>
                                            <th>Category</th>
                                            <th>Location</th>
                                            <th>Area</th>
                                            <th>Rate/sqft</th>
                                            <th>Total Value</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($all_properties as $property): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($property['property_code']); ?></strong>
                                                </td>
                                                <td><?php echo htmlspecialchars($property['property_name']); ?></td>
                                                <td>
                                                    <span class="badge <?php echo $property['property_type'] === 'company' ? 'badge-company' : 'badge-resell'; ?>">
                                                        <?php echo ucfirst($property['property_type']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo ucfirst($property['property_category']); ?></td>
                                                <td><?php echo htmlspecialchars($property['location']); ?></td>
                                                <td><?php echo number_format($property['area_sqft'], 2); ?> sqft</td>
                                                <td>₹<?php echo number_format($property['rate_per_sqft']); ?></td>
                                                <td>₹<?php echo number_format($property['total_value']); ?></td>
                                                <td>
                                                    <span class="status-badge status-<?php echo $property['status']; ?>">
                                                        <?php echo ucfirst($property['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-primary me-1" onclick="editProperty(<?php echo htmlspecialchars(json_encode($property)); ?>)">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <?php if ($property['status'] === 'available'): ?>
                                                        <button class="btn btn-sm btn-outline-success" onclick="recordSale(<?php echo htmlspecialchars(json_encode($property)); ?>)">
                                                            <i class="fas fa-shopping-cart"></i>
                                                        </button>
                                                    <?php endif; ?>
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
    </div>

    <!-- Add Property Modal -->
    <div class="modal fade" id="addPropertyModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-plus me-2"></i>Add New Property
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="post" action="">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Property Code *</label>
                                    <input type="text" class="form-control" name="property_code" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Property Name *</label>
                                    <input type="text" class="form-control" name="property_name" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Property Type *</label>
                                    <select class="form-select" name="property_type" required>
                                        <option value="company">Company Property (Colony Plotting)</option>
                                        <option value="resell">Resell Property</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Property Category *</label>
                                    <select class="form-select" name="property_category" required>
                                        <option value="plot">Plot</option>
                                        <option value="flat">Flat</option>
                                        <option value="house">House</option>
                                        <option value="commercial">Commercial</option>
                                        <option value="land">Land</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Location *</label>
                                    <input type="text" class="form-control" name="location" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Area (sq ft) *</label>
                                    <input type="number" class="form-control" name="area_sqft" step="0.01" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Rate per sqft (₹) *</label>
                                    <input type="number" class="form-control" name="rate_per_sqft" step="0.01" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Total Value (₹) *</label>
                                    <input type="number" class="form-control" name="total_value" step="0.01" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Development Cost (₹)</label>
                                    <input type="number" class="form-control" name="development_cost" step="0.01" value="0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Commission Percentage (%)</label>
                                    <input type="number" class="form-control" name="commission_percentage" step="0.01" value="15">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status">
                                <option value="available">Available</option>
                                <option value="booked">Booked</option>
                                <option value="sold">Sold</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_property" class="btn btn-primary-custom">
                            <i class="fas fa-plus me-2"></i>Add Property
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Property Modal -->
    <div class="modal fade" id="editPropertyModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-edit me-2"></i>Edit Property
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="post" action="">
                    <input type="hidden" name="property_id" id="edit_property_id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Property Name *</label>
                                    <input type="text" class="form-control" name="property_name" id="edit_property_name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Property Category *</label>
                                    <select class="form-select" name="property_category" id="edit_property_category" required>
                                        <option value="plot">Plot</option>
                                        <option value="flat">Flat</option>
                                        <option value="house">House</option>
                                        <option value="commercial">Commercial</option>
                                        <option value="land">Land</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Location *</label>
                                    <input type="text" class="form-control" name="location" id="edit_location" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Area (sq ft) *</label>
                                    <input type="number" class="form-control" name="area_sqft" id="edit_area_sqft" step="0.01" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Rate per sqft (₹) *</label>
                                    <input type="number" class="form-control" name="rate_per_sqft" id="edit_rate_per_sqft" step="0.01" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Total Value (₹) *</label>
                                    <input type="number" class="form-control" name="total_value" id="edit_total_value" step="0.01" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Development Cost (₹)</label>
                                    <input type="number" class="form-control" name="development_cost" id="edit_development_cost" step="0.01">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Commission Percentage (%)</label>
                                    <input type="number" class="form-control" name="commission_percentage" id="edit_commission_percentage" step="0.01">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status" id="edit_status">
                                <option value="available">Available</option>
                                <option value="booked">Booked</option>
                                <option value="sold">Sold</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="update_property" class="btn btn-primary-custom">
                            <i class="fas fa-save me-2"></i>Update Property
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Record Sale Modal -->
    <div class="modal fade" id="recordSaleModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-shopping-cart me-2"></i>Record Property Sale
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="post" action="">
                    <input type="hidden" name="property_id" id="sale_property_id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Property</label>
                            <input type="text" class="form-control" id="sale_property_name" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Associate *</label>
                            <select class="form-select" name="associate_id" required>
                                <option value="">Select Associate</option>
                                <?php foreach ($associates as $associate): ?>
                                    <option value="<?php echo $associate['id']; ?>"><?php echo htmlspecialchars($associate['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Customer *</label>
                            <select class="form-select" name="customer_id" required>
                                <option value="">Select Customer</option>
                                <?php foreach ($customers as $customer): ?>
                                    <option value="<?php echo $customer['id']; ?>"><?php echo htmlspecialchars($customer['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Sale Amount (₹) *</label>
                            <input type="number" class="form-control" name="sale_amount" id="sale_amount" step="0.01" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="record_sale" class="btn btn-success">
                            <i class="fas fa-check me-2"></i>Record Sale
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function editProperty(property) {
            document.getElementById('edit_property_id').value = property.id;
            document.getElementById('edit_property_name').value = property.property_name;
            document.getElementById('edit_property_category').value = property.property_category;
            document.getElementById('edit_location').value = property.location;
            document.getElementById('edit_area_sqft').value = property.area_sqft;
            document.getElementById('edit_rate_per_sqft').value = property.rate_per_sqft;
            document.getElementById('edit_total_value').value = property.total_value;
            document.getElementById('edit_development_cost').value = property.development_cost;
            document.getElementById('edit_commission_percentage').value = property.commission_percentage;
            document.getElementById('edit_status').value = property.status;

            new bootstrap.Modal(document.getElementById('editPropertyModal')).show();
        }

        function recordSale(property) {
            document.getElementById('sale_property_id').value = property.id;
            document.getElementById('sale_property_name').value = property.property_name;
            document.getElementById('sale_amount').value = property.total_value;

            new bootstrap.Modal(document.getElementById('recordSaleModal')).show();
        }

        // Auto-calculate total value when rate or area changes
        document.querySelector('input[name="rate_per_sqft"]').addEventListener('input', function() {
            const rate = parseFloat(this.value) || 0;
            const area = parseFloat(document.querySelector('input[name="area_sqft"]').value) || 0;
            const total = rate * area;
            document.querySelector('input[name="total_value"]').value = total.toFixed(2);
        });

        document.querySelector('input[name="area_sqft"]').addEventListener('input', function() {
            const area = parseFloat(this.value) || 0;
            const rate = parseFloat(document.querySelector('input[name="rate_per_sqft"]').value) || 0;
            const total = rate * area;
            document.querySelector('input[name="total_value"]').value = total.toFixed(2);
        });
    </script>
</body>

</html>