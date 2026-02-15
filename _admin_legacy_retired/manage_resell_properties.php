<?php
/**
 * Admin Panel - Manage Resell Properties
 * This file allows admins to approve, reject, and manage resell property listings
 */

session_start();
require_once '../includes/config.php';

// Check if user is logged in and has admin privileges
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$config = AppConfig::getInstance();
$conn = $config->getDatabaseConnection();

$message = '';
$message_type = '';

// Handle property approval/rejection
if (isset($_POST['action'])) {
    $property_id = $_POST['property_id'];
    $action = $_POST['action'];
    
    if ($action === 'approve') {
        $stmt = $conn->prepare("UPDATE resell_properties SET status = 'approved', approved_at = NOW(), approved_by = ? WHERE id = ?");
        $stmt->bind_param("ii", $_SESSION['user_id'], $property_id);
        
        if ($stmt->execute()) {
            $message = "Property approved successfully!";
            $message_type = "success";
            
            // Get property details to notify user
            $property_stmt = $conn->prepare("SELECT rp.*, ru.mobile, ru.full_name FROM resell_properties rp JOIN resell_users ru ON rp.user_id = ru.id WHERE rp.id = ?");
            $property_stmt->bind_param("i", $property_id);
            $property_stmt->execute();
            $property = $property_stmt->get_result()->fetch_assoc();
            
            // Send WhatsApp notification
            $whatsapp_message = "🎉 Congratulations! Your property has been approved!\n\n";
            $whatsapp_message .= "🏡 Property: " . $property['title'] . "\n";
            $whatsapp_message .= "💰 Price: ₹" . number_format($property['price']) . "\n";
            $whatsapp_message .= "📍 Location: " . $property['address'] . ", " . $property['city'] . "\n\n";
            $whatsapp_message .= "✅ Your property is now live on our platform\n";
            $whatsapp_message .= "👀 Expect inquiries from potential buyers\n";
            $whatsapp_message .= "📱 Support: +91-9876543210\n\n";
            $whatsapp_message .= "APS Dream Homes - Connecting Buyers & Sellers! 🏠✨";
            
            sendWhatsAppNotification($property['mobile'], $whatsapp_message);
        } else {
            $message = "Error approving property: " . $conn->error;
            $message_type = "danger";
        }
    } elseif ($action === 'reject') {
        $rejection_reason = $_POST['rejection_reason'];
        $stmt = $conn->prepare("UPDATE resell_properties SET status = 'rejected', rejection_reason = ? WHERE id = ?");
        $stmt->bind_param("si", $rejection_reason, $property_id);
        
        if ($stmt->execute()) {
            $message = "Property rejected successfully!";
            $message_type = "success";
        } else {
            $message = "Error rejecting property: " . $conn->error;
            $message_type = "danger";
        }
    } elseif ($action === 'delete') {
        $stmt = $conn->prepare("DELETE FROM resell_properties WHERE id = ?");
        $stmt->bind_param("i", $property_id);
        
        if ($stmt->execute()) {
            $message = "Property deleted successfully!";
            $message_type = "success";
        } else {
            $message = "Error deleting property: " . $conn->error;
            $message_type = "danger";
        }
    } elseif ($action === 'feature') {
        $featured = $_POST['featured'];
        $stmt = $conn->prepare("UPDATE resell_properties SET is_featured = ? WHERE id = ?");
        $stmt->bind_param("ii", $featured, $property_id);
        
        if ($stmt->execute()) {
            $message = "Property featured status updated!";
            $message_type = "success";
        } else {
            $message = "Error updating featured status: " . $conn->error;
            $message_type = "danger";
        }
    }
}

// Get filter parameters
$status_filter = $_GET['status'] ?? 'all';
$city_filter = $_GET['city'] ?? '';
$type_filter = $_GET['type'] ?? '';
$search_query = $_GET['search'] ?? '';

// Build query with filters
$query = "SELECT rp.*, ru.full_name, ru.mobile, ru.email 
          FROM resell_properties rp 
          JOIN resell_users ru ON rp.user_id = ru.id 
          WHERE 1=1";

$params = [];
$types = '';

if ($status_filter !== 'all') {
    $query .= " AND rp.status = ?";
    $params[] = $status_filter;
    $types .= 's';
}

if (!empty($city_filter)) {
    $query .= " AND rp.city = ?";
    $params[] = $city_filter;
    $types .= 's';
}

if (!empty($type_filter)) {
    $query .= " AND rp.property_type = ?";
    $params[] = $type_filter;
    $types .= 's';
}

if (!empty($search_query)) {
    $query .= " AND (rp.title LIKE ? OR rp.address LIKE ? OR ru.full_name LIKE ?)";
    $search_param = "%$search_query%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'sss';
}

$query .= " ORDER BY rp.created_at DESC";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$properties = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get unique cities and types for filters
$cities_stmt = $conn->query("SELECT DISTINCT city FROM resell_properties ORDER BY city");
$cities = $cities_stmt->fetch_all(MYSQLI_ASSOC);

$types_stmt = $conn->query("SELECT DISTINCT property_type FROM resell_properties ORDER BY property_type");
$property_types = $types_stmt->fetch_all(MYSQLI_ASSOC);

// WhatsApp notification function
function sendWhatsAppNotification($mobile, $message) {
    error_log("WhatsApp Notification to: " . $mobile . "\nMessage: " . $message);
    return true;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Resell Properties - Admin Panel - APS Dream Homes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .property-card {
            transition: transform 0.2s, box-shadow 0.2s;
            border: 1px solid #e0e0e0;
        }
        .property-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .status-badge {
            font-size: 0.8rem;
            padding: 0.4rem 0.8rem;
        }
        .featured-badge {
            background: linear-gradient(135deg, #ff6b35, #f7931e);
        }
        .filter-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 1.5rem;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <h1 class="h3 mb-0">
                        <i class="fas fa-home text-primary me-2"></i>
                        Manage Resell Properties
                    </h1>
                    <a href="dashboard.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                    </a>
                </div>
                <p class="text-muted">Approve, reject, and manage property listings from individual sellers</p>
            </div>
        </div>

        <!-- Stats Overview -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stats-card">
                    <h4><?php 
                        $total_stmt = $conn->query("SELECT COUNT(*) as total FROM resell_properties");
                        echo number_format($total_stmt->fetch_assoc()['total']);
                    ?></h4>
                    <p class="mb-0">Total Properties</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card" style="background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);">
                    <h4><?php 
                        $pending_stmt = $conn->query("SELECT COUNT(*) as total FROM resell_properties WHERE status = 'pending'");
                        echo number_format($pending_stmt->fetch_assoc()['total']);
                    ?></h4>
                    <p class="mb-0">Pending Approval</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card" style="background: linear-gradient(135deg, #2196F3 0%, #1976D2 100%);">
                    <h4><?php 
                        $approved_stmt = $conn->query("SELECT COUNT(*) as total FROM resell_properties WHERE status = 'approved'");
                        echo number_format($approved_stmt->fetch_assoc()['total']);
                    ?></h4>
                    <p class="mb-0">Approved</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card" style="background: linear-gradient(135deg, #f44336 0%, #d32f2f 100%);">
                    <h4><?php 
                        $rejected_stmt = $conn->query("SELECT COUNT(*) as total FROM resell_properties WHERE status = 'rejected'");
                        echo number_format($rejected_stmt->fetch_assoc()['total']);
                    ?></h4>
                    <p class="mb-0">Rejected</p>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="filter-section">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="all" <?= $status_filter === 'all' ? 'selected' : '' ?>>All Status</option>
                                <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="approved" <?= $status_filter === 'approved' ? 'selected' : '' ?>>Approved</option>
                                <option value="rejected" <?= $status_filter === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                                <option value="sold" <?= $status_filter === 'sold' ? 'selected' : '' ?>>Sold</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">City</label>
                            <select name="city" class="form-select">
                                <option value="">All Cities</option>
                                <?php foreach ($cities as $city): ?>
                                    <option value="<?= $city['city'] ?>" <?= $city_filter === $city['city'] ? 'selected' : '' ?>><?= $city['city'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Property Type</label>
                            <select name="type" class="form-select">
                                <option value="">All Types</option>
                                <?php foreach ($property_types as $type): ?>
                                    <option value="<?= $type['property_type'] ?>" <?= $type_filter === $type['property_type'] ? 'selected' : '' ?>><?= ucfirst($type['property_type']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Search</label>
                            <div class="input-group">
                                <input type="text" name="search" class="form-control" placeholder="Search properties..." value="<?= htmlspecialchars($search_query) ?>">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Message Alert -->
        <?php if ($message): ?>
            <div class="alert alert-<?= $message_type ?> alert-dismissible fade show" role="alert">
                <?= $message ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Properties List -->
        <div class="row">
            <?php if (empty($properties)): ?>
                <div class="col-12">
                    <div class="text-center py-5">
                        <i class="fas fa-home fa-3x text-muted mb-3"></i>
                        <h4>No properties found</h4>
                        <p class="text-muted">No properties match your current filters.</p>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($properties as $property): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card property-card h-100">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <span class="badge status-badge bg-<?= 
                                    $property['status'] === 'approved' ? 'success' : 
                                    ($property['status'] === 'pending' ? 'warning' : 
                                    ($property['status'] === 'rejected' ? 'danger' : 'secondary')) 
                                ?>">
                                    <?= ucfirst($property['status']) ?>
                                </span>
                                <?php if ($property['is_featured']): ?>
                                    <span class="badge featured-badge">
                                        <i class="fas fa-star me-1"></i>Featured
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($property['title']) ?></h5>
                                <h6 class="text-primary mb-3">₹<?= number_format($property['price']) ?></h6>
                                
                                <div class="row mb-3">
                                    <div class="col-6">
                                        <small class="text-muted">
                                            <i class="fas fa-bed me-1"></i><?= $property['bedrooms'] ?> Beds
                                        </small>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">
                                            <i class="fas fa-bath me-1"></i><?= $property['bathrooms'] ?> Baths
                                        </small>
                                    </div>
                                </div>
                                
                                <p class="card-text text-muted small mb-2">
                                    <i class="fas fa-map-marker-alt me-1"></i>
                                    <?= htmlspecialchars($property['address']) ?>, <?= $property['city'] ?>, <?= $property['state'] ?>
                                </p>
                                
                                <p class="card-text small mb-3">
                                    <?= nl2br(htmlspecialchars(substr($property['description'], 0, 100))) ?><?= strlen($property['description']) > 100 ? '...' : '' ?>
                                </p>
                                
                                <div class="mb-3">
                                    <strong>Seller:</strong> <?= htmlspecialchars($property['full_name']) ?><br>
                                    <strong>Mobile:</strong> <?= htmlspecialchars($property['mobile']) ?><br>
                                    <strong>Email:</strong> <?= htmlspecialchars($property['email']) ?>
                                </div>
                                
                                <div class="d-flex flex-wrap gap-2">
                                    <?php if ($property['status'] === 'pending'): ?>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="property_id" value="<?= $property['id'] ?>">
                                            <input type="hidden" name="action" value="approve">
                                            <button type="submit" class="btn btn-success btn-sm">
                                                <i class="fas fa-check me-1"></i>Approve
                                            </button>
                                        </form>
                                        <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#rejectModal<?= $property['id'] ?>">
                                            <i class="fas fa-times me-1"></i>Reject
                                        </button>
                                    <?php elseif ($property['status'] === 'approved'): ?>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="property_id" value="<?= $property['id'] ?>">
                                            <input type="hidden" name="action" value="feature">
                                            <input type="hidden" name="featured" value="<?= $property['is_featured'] ? '0' : '1' ?>">
                                            <button type="submit" class="btn btn-<?= $property['is_featured'] ? 'warning' : 'outline-warning' ?> btn-sm">
                                                <i class="fas fa-star me-1"></i><?= $property['is_featured'] ? 'Unfeature' : 'Feature' ?>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this property?')">
                                        <input type="hidden" name="property_id" value="<?= $property['id'] ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <button type="submit" class="btn btn-outline-danger btn-sm">
                                            <i class="fas fa-trash me-1"></i>Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                            <div class="card-footer text-muted small">
                                Listed: <?= date('M d, Y', strtotime($property['created_at'])) ?>
                                <?php if ($property['approved_at']): ?>
                                    <br>Approved: <?= date('M d, Y', strtotime($property['approved_at'])) ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Reject Modal -->
                    <div class="modal fade" id="rejectModal<?= $property['id'] ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Reject Property</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form method="POST">
                                    <div class="modal-body">
                                        <input type="hidden" name="property_id" value="<?= $property['id'] ?>">
                                        <input type="hidden" name="action" value="reject">
                                        <div class="mb-3">
                                            <label class="form-label">Rejection Reason</label>
                                            <textarea name="rejection_reason" class="form-control" rows="3" placeholder="Please provide a reason for rejection..." required></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-danger">Confirm Rejection</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>