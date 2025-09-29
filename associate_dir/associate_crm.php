<?php
/**
 * Associate CRM System - APS Dream Homes
 * Complete Customer Relationship Management for Associates
 * Features: Lead Management, Customer Tracking, Sales Pipeline, Activity Management
 */

session_start();
require_once '../includes/config.php';
require_once '../includes/associate_permissions.php';

$config = AppConfig::getInstance();
$conn = $config->getDatabaseConnection();

// Check if associate is logged in
if (!isset($_SESSION['associate_logged_in']) || $_SESSION['associate_logged_in'] !== true) {
    header("Location: associate_login.php");
    exit();
}

$associate_id = $_SESSION['associate_id'];
$associate_name = $_SESSION['associate_name'];

// Get associate data
try {
    $stmt = $conn->prepare("SELECT * FROM mlm_agents WHERE id = ?");
    $stmt->bind_param("i", $associate_id);
    $stmt->execute();
    $associate_data = $stmt->get_result()->fetch_assoc();
} catch (Exception $e) {
    error_log("Error fetching associate data: " . $e->getMessage());
    $associate_data = [];
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_lead'])) {
        handleAddLead($conn, $associate_id, $_POST);
    } elseif (isset($_POST['update_lead'])) {
        handleUpdateLead($conn, $_POST);
    } elseif (isset($_POST['add_customer'])) {
        handleAddCustomer($conn, $associate_id, $_POST);
    } elseif (isset($_POST['add_note'])) {
        handleAddNote($conn, $_POST);
    } elseif (isset($_POST['add_activity'])) {
        handleAddActivity($conn, $_POST);
    } elseif (isset($_POST['schedule_appointment'])) {
        handleScheduleAppointment($conn, $_POST);
    } elseif (isset($_POST['send_message'])) {
        handleSendMessage($conn, $_POST);
    }
}

// Get CRM data
$crm_data = getCRMData($conn, $associate_id);

// Functions
function getCRMData($conn, $associate_id) {
    $data = [];

    try {
        // Leads statistics
        $leads_query = "SELECT
                           COUNT(*) as total_leads,
                           SUM(CASE WHEN status = 'new' THEN 1 ELSE 0 END) as new_leads,
                           SUM(CASE WHEN status = 'contacted' THEN 1 ELSE 0 END) as contacted_leads,
                           SUM(CASE WHEN status = 'qualified' THEN 1 ELSE 0 END) as qualified_leads,
                           SUM(CASE WHEN status = 'proposal' THEN 1 ELSE 0 END) as proposal_leads,
                           SUM(CASE WHEN status = 'negotiation' THEN 1 ELSE 0 END) as negotiation_leads,
                           SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) as closed_leads,
                           SUM(CASE WHEN status = 'lost' THEN 1 ELSE 0 END) as lost_leads
                        FROM leads WHERE associate_id = ?";
        $leads_stmt = $conn->prepare($leads_query);
        $leads_stmt->bind_param("i", $associate_id);
        $leads_stmt->execute();
        $data['leads_stats'] = $leads_stmt->get_result()->fetch_assoc();

        // Recent leads
        $recent_leads_query = "SELECT * FROM leads WHERE associate_id = ?
                              ORDER BY created_at DESC LIMIT 10";
        $recent_leads_stmt = $conn->prepare($recent_leads_query);
        $recent_leads_stmt->bind_param("i", $associate_id);
        $recent_leads_stmt->execute();
        $data['recent_leads'] = $recent_leads_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        // Customers
        $customers_query = "SELECT c.*, COUNT(b.id) as total_bookings,
                                   SUM(b.total_amount) as total_value
                            FROM customers c
                            LEFT JOIN bookings b ON c.id = b.customer_id AND b.associate_id = ?
                            GROUP BY c.id
                            ORDER BY c.created_at DESC LIMIT 20";
        $customers_stmt = $conn->prepare($customers_query);
        $customers_stmt->bind_param("i", $associate_id);
        $customers_stmt->execute();
        $data['customers'] = $customers_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        // Activities
        $activities_query = "SELECT * FROM associate_activities
                            WHERE associate_id = ?
                            ORDER BY created_at DESC LIMIT 15";
        $activities_stmt = $conn->prepare($activities_query);
        $activities_stmt->bind_param("i", $associate_id);
        $activities_stmt->execute();
        $data['activities'] = $activities_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        // Appointments
        $appointments_query = "SELECT * FROM associate_appointments
                              WHERE associate_id = ? AND appointment_date >= CURDATE()
                              ORDER BY appointment_date, appointment_time LIMIT 10";
        $appointments_stmt = $conn->prepare($appointments_query);
        $appointments_stmt->bind_param("i", $associate_id);
        $appointments_stmt->execute();
        $data['appointments'] = $appointments_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        // Messages
        $messages_query = "SELECT * FROM associate_messages
                          WHERE associate_id = ?
                          ORDER BY created_at DESC LIMIT 10";
        $messages_stmt = $conn->prepare($messages_query);
        $messages_stmt->bind_param("i", $associate_id);
        $messages_stmt->execute();
        $data['messages'] = $messages_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        // Notes
        $notes_query = "SELECT * FROM associate_notes
                       WHERE associate_id = ?
                       ORDER BY created_at DESC LIMIT 10";
        $notes_stmt = $conn->prepare($notes_query);
        $notes_stmt->bind_param("i", $associate_id);
        $notes_stmt->execute();
        $data['notes'] = $notes_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        // Conversion rates
        $conversion_query = "SELECT
                            COUNT(CASE WHEN status = 'closed' THEN 1 END) / COUNT(*) * 100 as conversion_rate,
                            AVG(CASE WHEN status = 'closed' THEN DATEDIFF(created_at, first_contact) END) as avg_sales_cycle
                            FROM leads WHERE associate_id = ? AND status = 'closed'";
        $conversion_stmt = $conn->prepare($conversion_query);
        $conversion_stmt->bind_param("i", $associate_id);
        $conversion_stmt->execute();
        $data['conversion'] = $conversion_stmt->get_result()->fetch_assoc();

    } catch (Exception $e) {
        error_log("Error fetching CRM data: " . $e->getMessage());
        $data = [];
    }

    return $data;
}

function handleAddLead($conn, $associate_id, $data) {
    try {
        $query = "INSERT INTO leads (associate_id, name, email, phone, source, budget, property_type,
                                 location, status, priority, notes, created_at, updated_at)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'new', ?, ?, NOW(), NOW())";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("issssssss", $associate_id, $data['name'], $data['email'], $data['phone'],
                         $data['source'], $data['budget'], $data['property_type'], $data['location'],
                         $data['priority'], $data['notes']);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Lead added successfully!";
            header("Location: associate_crm.php");
            exit();
        }
    } catch (Exception $e) {
        error_log("Error adding lead: " . $e->getMessage());
        $_SESSION['error_message'] = "Error adding lead. Please try again.";
    }
}

function handleUpdateLead($conn, $data) {
    try {
        $query = "UPDATE leads SET status = ?, priority = ?, notes = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssi", $data['status'], $data['priority'], $data['notes'], $data['lead_id']);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Lead updated successfully!";
            header("Location: associate_crm.php?section=leads");
            exit();
        }
    } catch (Exception $e) {
        error_log("Error updating lead: " . $e->getMessage());
        $_SESSION['error_message'] = "Error updating lead. Please try again.";
    }
}

function handleAddCustomer($conn, $associate_id, $data) {
    try {
        $query = "INSERT INTO customers (name, email, phone, address, city, state, pincode, created_at)
                  VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssssss", $data['name'], $data['email'], $data['phone'], $data['address'],
                         $data['city'], $data['state'], $data['pincode']);

        if ($stmt->execute()) {
            $customer_id = $conn->insert_id;

            // Create activity
            $activity_query = "INSERT INTO associate_activities (associate_id, activity_type, description, created_at)
                              VALUES (?, 'customer_added', ?, NOW())";
            $activity_stmt = $conn->prepare($activity_query);
            $activity_stmt->bind_param("is", $associate_id, "Added new customer: " . $data['name']);
            $activity_stmt->execute();

            $_SESSION['success_message'] = "Customer added successfully!";
            header("Location: associate_crm.php?section=customers");
            exit();
        }
    } catch (Exception $e) {
        error_log("Error adding customer: " . $e->getMessage());
        $_SESSION['error_message'] = "Error adding customer. Please try again.";
    }
}

function handleAddNote($conn, $data) {
    try {
        $query = "INSERT INTO associate_notes (associate_id, note_type, related_id, note, created_at)
                  VALUES (?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("siss", $data['associate_id'], $data['note_type'], $data['related_id'], $data['note']);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Note added successfully!";
            header("Location: associate_crm.php?section=notes");
            exit();
        }
    } catch (Exception $e) {
        error_log("Error adding note: " . $e->getMessage());
        $_SESSION['error_message'] = "Error adding note. Please try again.";
    }
}

function handleAddActivity($conn, $data) {
    try {
        $query = "INSERT INTO associate_activities (associate_id, activity_type, description, created_at)
                  VALUES (?, ?, ?, NOW())";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iss", $data['associate_id'], $data['activity_type'], $data['description']);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Activity added successfully!";
            header("Location: associate_crm.php?section=activities");
            exit();
        }
    } catch (Exception $e) {
        error_log("Error adding activity: " . $e->getMessage());
        $_SESSION['error_message'] = "Error adding activity. Please try again.";
    }
}

function handleScheduleAppointment($conn, $data) {
    try {
        $query = "INSERT INTO associate_appointments (associate_id, customer_name, appointment_date,
                         appointment_time, location, notes, created_at)
                  VALUES (?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("isssss", $data['associate_id'], $data['customer_name'], $data['appointment_date'],
                         $data['appointment_time'], $data['location'], $data['notes']);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Appointment scheduled successfully!";
            header("Location: associate_crm.php?section=appointments");
            exit();
        }
    } catch (Exception $e) {
        error_log("Error scheduling appointment: " . $e->getMessage());
        $_SESSION['error_message'] = "Error scheduling appointment. Please try again.";
    }
}

function handleSendMessage($conn, $data) {
    try {
        $query = "INSERT INTO associate_messages (associate_id, recipient_type, recipient_id,
                         message_type, subject, message, created_at)
                  VALUES (?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sissss", $data['associate_id'], $data['recipient_type'], $data['recipient_id'],
                         $data['message_type'], $data['subject'], $data['message']);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Message sent successfully!";
            header("Location: associate_crm.php?section=messages");
            exit();
        }
    } catch (Exception $e) {
        error_log("Error sending message: " . $e->getMessage());
        $_SESSION['error_message'] = "Error sending message. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Associate CRM - APS Dream Homes</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- FullCalendar CSS -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">

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

        .main-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            margin: 20px 0;
            overflow: hidden;
        }

        .top-header {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }

        .sidebar {
            background: white;
            border-right: 1px solid #eee;
            min-height: 100vh;
        }

        .sidebar .nav-link {
            color: #333;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #f8f9fa;
            transition: all 0.3s;
            font-weight: 500;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
        }

        .sidebar .nav-link i {
            margin-right: 0.5rem;
            width: 20px;
            text-align: center;
        }

        .stats-card {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            text-align: center;
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 25px rgba(0,0,0,0.15);
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 1.5rem;
        }

        .card-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-radius: 15px 15px 0 0 !important;
            border: none;
            font-weight: 600;
        }

        .btn {
            border-radius: 10px;
            font-weight: 500;
            padding: 0.75rem 1.5rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
            transform: translateY(-2px);
        }

        .form-control, .form-select {
            border-radius: 10px;
            border: 2px solid #e9ecef;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .table th {
            background-color: #f8f9fa;
            border-top: none;
            font-weight: 600;
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .status-new { background-color: #e3f2fd; color: #1976d2; }
        .status-contacted { background-color: #fff3e0; color: #f57c00; }
        .status-qualified { background-color: #e8f5e8; color: #388e3c; }
        .status-proposal { background-color: #f3e5f5; color: #7b1fa2; }
        .status-negotiation { background-color: #fff8e1; color: #f57f17; }
        .status-closed { background-color: #e8f5e8; color: #2e7d32; }
        .status-lost { background-color: #ffebee; color: #c62828; }

        .priority-high { background-color: #ffcdd2; color: #b71c1c; }
        .priority-medium { background-color: #fff3e0; color: #ef6c00; }
        .priority-low { background-color: #e8f5e8; color: #2e7d32; }

        .activity-item {
            padding: 1rem;
            border-left: 4px solid var(--primary-color);
            background: #f8f9fa;
            border-radius: 0 10px 10px 0;
            margin-bottom: 1rem;
        }

        .lead-card {
            border: 1px solid #e9ecef;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
            transition: all 0.3s;
        }

        .lead-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }

        .chart-container {
            position: relative;
            height: 300px;
        }

        .pipeline-stage {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1rem;
            margin: 0.5rem;
            min-height: 200px;
        }

        .appointment-card {
            border-left: 4px solid var(--primary-color);
            padding: 1rem;
            margin-bottom: 1rem;
            background: #f8f9fa;
            border-radius: 0 10px 10px 0;
        }

        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                top: 0;
                left: -280px;
                width: 280px;
                z-index: 1000;
                transition: all 0.3s ease;
            }

            .sidebar.show {
                left: 0;
            }

            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <button class="btn btn-outline-light me-2 d-lg-none" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>
            <a class="navbar-brand fw-bold" href="#">
                <i class="fas fa-home me-2"></i>APS Dream Homes CRM
            </a>
            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle me-1"></i>
                        <?php echo htmlspecialchars($associate_name); ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="associate_portal.php"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a></li>
                        <li><a class="dropdown-item" href="associate_logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-2 sidebar d-none d-lg-block" id="sidebar">
                <div class="p-3">
                    <div class="text-center mb-4">
                        <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                            <i class="fas fa-user"></i>
                        </div>
                        <h6 class="mt-2 mb-0"><?php echo htmlspecialchars($associate_name); ?></h6>
                        <small class="text-muted">CRM System</small>
                    </div>

                    <nav class="nav nav-pills flex-column">
                        <a href="?section=dashboard" class="nav-link <?php echo (!isset($_GET['section']) || $_GET['section'] == 'dashboard') ? 'active' : ''; ?>">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                        <a href="?section=leads" class="nav-link <?php echo (isset($_GET['section']) && $_GET['section'] == 'leads') ? 'active' : ''; ?>">
                            <i class="fas fa-users"></i> Leads
                        </a>
                        <a href="?section=customers" class="nav-link <?php echo (isset($_GET['section']) && $_GET['section'] == 'customers') ? 'active' : ''; ?>">
                            <i class="fas fa-user-friends"></i> Customers
                        </a>
                        <a href="?section=pipeline" class="nav-link <?php echo (isset($_GET['section']) && $_GET['section'] == 'pipeline') ? 'active' : ''; ?>">
                            <i class="fas fa-chart-line"></i> Sales Pipeline
                        </a>
                        <a href="?section=activities" class="nav-link <?php echo (isset($_GET['section']) && $_GET['section'] == 'activities') ? 'active' : ''; ?>">
                            <i class="fas fa-tasks"></i> Activities
                        </a>
                        <a href="?section=appointments" class="nav-link <?php echo (isset($_GET['section']) && $_GET['section'] == 'appointments') ? 'active' : ''; ?>">
                            <i class="fas fa-calendar"></i> Appointments
                        </a>
                        <a href="?section=notes" class="nav-link <?php echo (isset($_GET['section']) && $_GET['section'] == 'notes') ? 'active' : ''; ?>">
                            <i class="fas fa-sticky-note"></i> Notes
                        </a>
                        <a href="?section=messages" class="nav-link <?php echo (isset($_GET['section']) && $_GET['section'] == 'messages') ? 'active' : ''; ?>">
                            <i class="fas fa-envelope"></i> Messages
                        </a>
                        <a href="?section=reports" class="nav-link <?php echo (isset($_GET['section']) && $_GET['section'] == 'reports') ? 'active' : ''; ?>">
                            <i class="fas fa-chart-bar"></i> Reports
                        </a>
                        <hr>
                        <a href="properties.php" target="_blank" class="nav-link">
                            <i class="fas fa-building"></i> Properties
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-lg-10 col-12">
                <div class="main-container">
                    <!-- Header -->
                    <div class="top-header">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h1 class="mb-2">Associate CRM System</h1>
                                <p class="mb-0">Manage your leads, customers, and grow your business</p>
                            </div>
                            <div class="col-md-4 text-end">
                                <button class="btn btn-light btn-lg" onclick="showAddLeadModal()">
                                    <i class="fas fa-plus me-2"></i>Add New Lead
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="p-4">
                        <!-- Success/Error Messages -->
                        <?php if (isset($_SESSION['success_message'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php endif; ?>

                        <?php if (isset($_SESSION['error_message'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php endif; ?>

                        <!-- Dashboard Section -->
                        <?php if (!isset($_GET['section']) || $_GET['section'] == 'dashboard'): ?>
                        <!-- CRM Statistics -->
                        <div class="row mb-4">
                            <div class="col-xl-3 col-lg-6 mb-3">
                                <div class="stats-card">
                                    <i class="fas fa-users stats-icon"></i>
                                    <h2><?php echo $crm_data['leads_stats']['total_leads'] ?? 0; ?></h2>
                                    <h5>Total Leads</h5>
                                    <small>New: <?php echo $crm_data['leads_stats']['new_leads'] ?? 0; ?></small>
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-6 mb-3">
                                <div class="stats-card">
                                    <i class="fas fa-user-friends stats-icon"></i>
                                    <h2><?php echo count($crm_data['customers'] ?? []); ?></h2>
                                    <h5>Total Customers</h5>
                                    <small>Active Clients</small>
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-6 mb-3">
                                <div class="stats-card">
                                    <i class="fas fa-percentage stats-icon"></i>
                                    <h2><?php echo round($crm_data['conversion']['conversion_rate'] ?? 0, 1); ?>%</h2>
                                    <h5>Conversion Rate</h5>
                                    <small>Lead to Customer</small>
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-6 mb-3">
                                <div class="stats-card">
                                    <i class="fas fa-calendar stats-icon"></i>
                                    <h2><?php echo count($crm_data['appointments'] ?? []); ?></h2>
                                    <h5>Appointments</h5>
                                    <small>Upcoming</small>
                                </div>
                            </div>
                        </div>

                        <!-- Charts -->
                        <div class="row mb-4">
                            <div class="col-lg-8">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Lead Pipeline</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="chart-container">
                                            <canvas id="pipelineChart"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Lead Sources</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="chart-container">
                                            <canvas id="sourceChart"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Activities -->
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Recent Activities</h5>
                                    </div>
                                    <div class="card-body">
                                        <?php if (!empty($crm_data['activities'])): ?>
                                        <?php foreach ($crm_data['activities'] as $activity): ?>
                                        <div class="activity-item">
                                            <i class="fas fa-<?php
                                                echo $activity['activity_type'] == 'lead_added' ? 'user-plus' :
                                                     ($activity['activity_type'] == 'call_made' ? 'phone' :
                                                     ($activity['activity_type'] == 'email_sent' ? 'envelope' :
                                                     ($activity['activity_type'] == 'meeting_scheduled' ? 'calendar' : 'tasks')));
                                            ?>"></i>
                                            <strong><?php echo htmlspecialchars($activity['description']); ?></strong>
                                            <br>
                                            <small class="text-muted">
                                                <?php echo date('M d, Y H:i', strtotime($activity['created_at'])); ?>
                                            </small>
                                        </div>
                                        <?php endforeach; ?>
                                        <?php else: ?>
                                        <div class="text-center py-4">
                                            <i class="fas fa-clock fa-3x text-muted mb-3"></i>
                                            <h6 class="text-muted">No recent activities</h6>
                                            <p class="text-muted">Activities will appear here as you work</p>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0"><i class="fas fa-calendar me-2"></i>Upcoming Appointments</h5>
                                    </div>
                                    <div class="card-body">
                                        <?php if (!empty($crm_data['appointments'])): ?>
                                        <?php foreach ($crm_data['appointments'] as $appointment): ?>
                                        <div class="appointment-card">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <h6 class="mb-1"><?php echo htmlspecialchars($appointment['customer_name']); ?></h6>
                                                    <p class="mb-1">
                                                        <i class="fas fa-map-marker-alt me-1"></i>
                                                        <?php echo htmlspecialchars($appointment['location']); ?>
                                                    </p>
                                                    <small class="text-muted">
                                                        <?php echo date('M d, Y', strtotime($appointment['appointment_date'])); ?> at
                                                        <?php echo date('H:i', strtotime($appointment['appointment_time'])); ?>
                                                    </small>
                                                </div>
                                                <span class="badge bg-primary">Scheduled</span>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                        <?php else: ?>
                                        <div class="text-center py-4">
                                            <i class="fas fa-calendar fa-3x text-muted mb-3"></i>
                                            <h6 class="text-muted">No upcoming appointments</h6>
                                            <p class="text-muted">Schedule appointments to track meetings</p>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Leads Section -->
                        <?php if (isset($_GET['section']) && $_GET['section'] == 'leads'): ?>
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0"><i class="fas fa-users me-2"></i>Leads Management</h5>
                                        <button class="btn btn-primary" onclick="showAddLeadModal()">
                                            <i class="fas fa-plus me-2"></i>Add New Lead
                                        </button>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-hover" id="leadsTable">
                                                <thead>
                                                    <tr>
                                                        <th>Name</th>
                                                        <th>Contact</th>
                                                        <th>Source</th>
                                                        <th>Budget</th>
                                                        <th>Status</th>
                                                        <th>Priority</th>
                                                        <th>Date</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if (!empty($crm_data['recent_leads'])): ?>
                                                    <?php foreach ($crm_data['recent_leads'] as $lead): ?>
                                                    <tr>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px;">
                                                                    <i class="fas fa-user"></i>
                                                                </div>
                                                                <div>
                                                                    <strong><?php echo htmlspecialchars($lead['name']); ?></strong><br>
                                                                    <small class="text-muted"><?php echo htmlspecialchars($lead['property_type']); ?></small>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <i class="fas fa-envelope me-1"></i><?php echo htmlspecialchars($lead['email']); ?><br>
                                                            <i class="fas fa-phone me-1"></i><?php echo htmlspecialchars($lead['phone']); ?>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-info"><?php echo htmlspecialchars($lead['source']); ?></span>
                                                        </td>
                                                        <td>₹<?php echo number_format($lead['budget']); ?></td>
                                                        <td>
                                                            <span class="status-badge status-<?php echo strtolower($lead['status']); ?>">
                                                                <?php echo ucfirst($lead['status']); ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <span class="status-badge priority-<?php echo strtolower($lead['priority']); ?>">
                                                                <?php echo ucfirst($lead['priority']); ?>
                                                            </span>
                                                        </td>
                                                        <td><?php echo date('M d, Y', strtotime($lead['created_at'])); ?></td>
                                                        <td>
                                                            <button class="btn btn-sm btn-outline-primary" onclick="editLead(<?php echo $lead['id']; ?>)">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <button class="btn btn-sm btn-outline-success" onclick="viewLead(<?php echo $lead['id']; ?>)">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                    <?php else: ?>
                                                    <tr>
                                                        <td colspan="8" class="text-center py-4">
                                                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                                            <h6 class="text-muted">No leads found</h6>
                                                            <p class="text-muted">Start by adding your first lead</p>
                                                        </td>
                                                    </tr>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Customers Section -->
                        <?php if (isset($_GET['section']) && $_GET['section'] == 'customers'): ?>
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0"><i class="fas fa-user-friends me-2"></i>Customer Management</h5>
                                        <button class="btn btn-primary" onclick="showAddCustomerModal()">
                                            <i class="fas fa-plus me-2"></i>Add New Customer
                                        </button>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-hover" id="customersTable">
                                                <thead>
                                                    <tr>
                                                        <th>Name</th>
                                                        <th>Contact</th>
                                                        <th>Location</th>
                                                        <th>Total Bookings</th>
                                                        <th>Total Value</th>
                                                        <th>Date Added</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if (!empty($crm_data['customers'])): ?>
                                                    <?php foreach ($crm_data['customers'] as $customer): ?>
                                                    <tr>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px;">
                                                                    <i class="fas fa-user"></i>
                                                                </div>
                                                                <div>
                                                                    <strong><?php echo htmlspecialchars($customer['name']); ?></strong><br>
                                                                    <small class="text-muted">Customer</small>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <i class="fas fa-envelope me-1"></i><?php echo htmlspecialchars($customer['email']); ?><br>
                                                            <i class="fas fa-phone me-1"></i><?php echo htmlspecialchars($customer['phone']); ?>
                                                        </td>
                                                        <td>
                                                            <?php echo htmlspecialchars($customer['city'] . ', ' . $customer['state']); ?>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-primary"><?php echo $customer['total_bookings']; ?> bookings</span>
                                                        </td>
                                                        <td>₹<?php echo number_format($customer['total_value']); ?></td>
                                                        <td><?php echo date('M d, Y', strtotime($customer['created_at'])); ?></td>
                                                        <td>
                                                            <button class="btn btn-sm btn-outline-info" onclick="viewCustomer(<?php echo $customer['id']; ?>)">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                            <button class="btn btn-sm btn-outline-primary" onclick="editCustomer(<?php echo $customer['id']; ?>)">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                    <?php else: ?>
                                                    <tr>
                                                        <td colspan="7" class="text-center py-4">
                                                            <i class="fas fa-user-friends fa-3x text-muted mb-3"></i>
                                                            <h6 class="text-muted">No customers found</h6>
                                                            <p class="text-muted">Add customers to build your client base</p>
                                                        </td>
                                                    </tr>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Sales Pipeline Section -->
                        <?php if (isset($_GET['section']) && $_GET['section'] == 'pipeline'): ?>
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Sales Pipeline</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-lg-2">
                                                <div class="pipeline-stage">
                                                    <h6 class="text-center">New Leads</h6>
                                                    <div class="text-center mb-3">
                                                        <span class="badge bg-primary fs-4"><?php echo $crm_data['leads_stats']['new_leads'] ?? 0; ?></span>
                                                    </div>
                                                    <!-- Add lead cards here -->
                                                </div>
                                            </div>
                                            <div class="col-lg-2">
                                                <div class="pipeline-stage">
                                                    <h6 class="text-center">Contacted</h6>
                                                    <div class="text-center mb-3">
                                                        <span class="badge bg-info fs-4"><?php echo $crm_data['leads_stats']['contacted_leads'] ?? 0; ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-2">
                                                <div class="pipeline-stage">
                                                    <h6 class="text-center">Qualified</h6>
                                                    <div class="text-center mb-3">
                                                        <span class="badge bg-warning fs-4"><?php echo $crm_data['leads_stats']['qualified_leads'] ?? 0; ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-2">
                                                <div class="pipeline-stage">
                                                    <h6 class="text-center">Proposal</h6>
                                                    <div class="text-center mb-3">
                                                        <span class="badge bg-secondary fs-4"><?php echo $crm_data['leads_stats']['proposal_leads'] ?? 0; ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-2">
                                                <div class="pipeline-stage">
                                                    <h6 class="text-center">Negotiation</h6>
                                                    <div class="text-center mb-3">
                                                        <span class="badge bg-warning fs-4"><?php echo $crm_data['leads_stats']['negotiation_leads'] ?? 0; ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-2">
                                                <div class="pipeline-stage">
                                                    <h6 class="text-center">Closed</h6>
                                                    <div class="text-center mb-3">
                                                        <span class="badge bg-success fs-4"><?php echo $crm_data['leads_stats']['closed_leads'] ?? 0; ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Activities Section -->
                        <?php if (isset($_GET['section']) && $_GET['section'] == 'activities'): ?>
                        <div class="row">
                            <div class="col-lg-8">
                                <div class="card">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0"><i class="fas fa-tasks me-2"></i>Activity Log</h5>
                                        <button class="btn btn-primary" onclick="showAddActivityModal()">
                                            <i class="fas fa-plus me-2"></i>Add Activity
                                        </button>
                                    </div>
                                    <div class="card-body">
                                        <?php if (!empty($crm_data['activities'])): ?>
                                        <?php foreach ($crm_data['activities'] as $activity): ?>
                                        <div class="activity-item">
                                            <i class="fas fa-<?php
                                                echo $activity['activity_type'] == 'lead_added' ? 'user-plus' :
                                                     ($activity['activity_type'] == 'call_made' ? 'phone' :
                                                     ($activity['activity_type'] == 'email_sent' ? 'envelope' :
                                                     ($activity['activity_type'] == 'meeting_scheduled' ? 'calendar' : 'tasks')));
                                            ?>"></i>
                                            <strong><?php echo htmlspecialchars($activity['description']); ?></strong>
                                            <br>
                                            <small class="text-muted">
                                                <?php echo date('M d, Y H:i', strtotime($activity['created_at'])); ?>
                                            </small>
                                        </div>
                                        <?php endforeach; ?>
                                        <?php else: ?>
                                        <div class="text-center py-4">
                                            <i class="fas fa-tasks fa-3x text-muted mb-3"></i>
                                            <h6 class="text-muted">No activities recorded</h6>
                                            <p class="text-muted">Log your activities to track progress</p>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Quick Add Activity</h6>
                                    </div>
                                    <div class="card-body">
                                        <form method="POST" action="">
                                            <input type="hidden" name="add_activity" value="1">
                                            <input type="hidden" name="associate_id" value="<?php echo $associate_id; ?>">
                                            <div class="mb-3">
                                                <label class="form-label">Activity Type</label>
                                                <select class="form-select" name="activity_type" required>
                                                    <option value="call_made">Call Made</option>
                                                    <option value="email_sent">Email Sent</option>
                                                    <option value="meeting_scheduled">Meeting Scheduled</option>
                                                    <option value="property_shown">Property Shown</option>
                                                    <option value="follow_up">Follow Up</option>
                                                    <option value="negotiation">Negotiation</option>
                                                    <option value="closed_deal">Deal Closed</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Description</label>
                                                <textarea class="form-control" name="description" rows="3" required></textarea>
                                            </div>
                                            <button type="submit" class="btn btn-primary w-100">
                                                <i class="fas fa-plus me-2"></i>Add Activity
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Appointments Section -->
                        <?php if (isset($_GET['section']) && $_GET['section'] == 'appointments'): ?>
                        <div class="row">
                            <div class="col-lg-8">
                                <div class="card">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0"><i class="fas fa-calendar me-2"></i>Appointments</h5>
                                        <button class="btn btn-primary" onclick="showAppointmentModal()">
                                            <i class="fas fa-plus me-2"></i>Schedule Appointment
                                        </button>
                                    </div>
                                    <div class="card-body">
                                        <div id="calendar"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0"><i class="fas fa-calendar-plus me-2"></i>Schedule New Appointment</h6>
                                    </div>
                                    <div class="card-body">
                                        <form method="POST" action="">
                                            <input type="hidden" name="schedule_appointment" value="1">
                                            <input type="hidden" name="associate_id" value="<?php echo $associate_id; ?>">
                                            <div class="mb-3">
                                                <label class="form-label">Customer Name</label>
                                                <input type="text" class="form-control" name="customer_name" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Date</label>
                                                <input type="date" class="form-control" name="appointment_date" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Time</label>
                                                <input type="time" class="form-control" name="appointment_time" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Location</label>
                                                <input type="text" class="form-control" name="location" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Notes</label>
                                                <textarea class="form-control" name="notes" rows="3"></textarea>
                                            </div>
                                            <button type="submit" class="btn btn-primary w-100">
                                                <i class="fas fa-calendar-plus me-2"></i>Schedule Appointment
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Notes Section -->
                        <?php if (isset($_GET['section']) && $_GET['section'] == 'notes'): ?>
                        <div class="row">
                            <div class="col-lg-8">
                                <div class="card">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0"><i class="fas fa-sticky-note me-2"></i>Notes</h5>
                                        <button class="btn btn-primary" onclick="showAddNoteModal()">
                                            <i class="fas fa-plus me-2"></i>Add Note
                                        </button>
                                    </div>
                                    <div class="card-body">
                                        <?php if (!empty($crm_data['notes'])): ?>
                                        <?php foreach ($crm_data['notes'] as $note): ?>
                                        <div class="card mb-3">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <h6><?php echo htmlspecialchars($note['note']); ?></h6>
                                                        <small class="text-muted">
                                                            Type: <?php echo ucfirst(str_replace('_', ' ', $note['note_type'])); ?> |
                                                            Related ID: <?php echo $note['related_id']; ?> |
                                                            <?php echo date('M d, Y H:i', strtotime($note['created_at'])); ?>
                                                        </small>
                                                    </div>
                                                    <span class="badge bg-info">Note</span>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                        <?php else: ?>
                                        <div class="text-center py-4">
                                            <i class="fas fa-sticky-note fa-3x text-muted mb-3"></i>
                                            <h6 class="text-muted">No notes found</h6>
                                            <p class="text-muted">Add notes to keep track of important information</p>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Quick Add Note</h6>
                                    </div>
                                    <div class="card-body">
                                        <form method="POST" action="">
                                            <input type="hidden" name="add_note" value="1">
                                            <input type="hidden" name="associate_id" value="<?php echo $associate_id; ?>">
                                            <div class="mb-3">
                                                <label class="form-label">Note Type</label>
                                                <select class="form-select" name="note_type" required>
                                                    <option value="general">General</option>
                                                    <option value="lead_note">Lead Note</option>
                                                    <option value="customer_note">Customer Note</option>
                                                    <option value="property_note">Property Note</option>
                                                    <option value="meeting_note">Meeting Note</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Related ID (Optional)</label>
                                                <input type="text" class="form-control" name="related_id" placeholder="Lead/Customer ID">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Note</label>
                                                <textarea class="form-control" name="note" rows="5" required></textarea>
                                            </div>
                                            <button type="submit" class="btn btn-primary w-100">
                                                <i class="fas fa-plus me-2"></i>Add Note
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Messages Section -->
                        <?php if (isset($_GET['section']) && $_GET['section'] == 'messages'): ?>
                        <div class="row">
                            <div class="col-lg-8">
                                <div class="card">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0"><i class="fas fa-envelope me-2"></i>Messages</h5>
                                        <button class="btn btn-primary" onclick="showMessageModal()">
                                            <i class="fas fa-plus me-2"></i>Send Message
                                        </button>
                                    </div>
                                    <div class="card-body">
                                        <?php if (!empty($crm_data['messages'])): ?>
                                        <?php foreach ($crm_data['messages'] as $message): ?>
                                        <div class="card mb-3">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <h6><?php echo htmlspecialchars($message['subject']); ?></h6>
                                                        <p class="mb-2"><?php echo htmlspecialchars(substr($message['message'], 0, 150)); ?>...</p>
                                                        <small class="text-muted">
                                                            To: <?php echo ucfirst($message['recipient_type']); ?> |
                                                            Type: <?php echo ucfirst($message['message_type']); ?> |
                                                            <?php echo date('M d, Y H:i', strtotime($message['created_at'])); ?>
                                                        </small>
                                                    </div>
                                                    <span class="badge bg-primary">Sent</span>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                        <?php else: ?>
                                        <div class="text-center py-4">
                                            <i class="fas fa-envelope fa-3x text-muted mb-3"></i>
                                            <h6 class="text-muted">No messages found</h6>
                                            <p class="text-muted">Send messages to communicate with customers</p>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Send Message</h6>
                                    </div>
                                    <div class="card-body">
                                        <form method="POST" action="">
                                            <input type="hidden" name="send_message" value="1">
                                            <input type="hidden" name="associate_id" value="<?php echo $associate_id; ?>">
                                            <div class="mb-3">
                                                <label class="form-label">Recipient Type</label>
                                                <select class="form-select" name="recipient_type" required>
                                                    <option value="customer">Customer</option>
                                                    <option value="lead">Lead</option>
                                                    <option value="admin">Admin</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Recipient ID</label>
                                                <input type="text" class="form-control" name="recipient_id" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Message Type</label>
                                                <select class="form-select" name="message_type" required>
                                                    <option value="email">Email</option>
                                                    <option value="sms">SMS</option>
                                                    <option value="whatsapp">WhatsApp</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Subject</label>
                                                <input type="text" class="form-control" name="subject" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Message</label>
                                                <textarea class="form-control" name="message" rows="5" required></textarea>
                                            </div>
                                            <button type="submit" class="btn btn-primary w-100">
                                                <i class="fas fa-paper-plane me-2"></i>Send Message
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Reports Section -->
                        <?php if (isset($_GET['section']) && $_GET['section'] == 'reports'): ?>
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>CRM Reports & Analytics</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-lg-6">
                                                <h6>Performance Metrics</h6>
                                                <div class="row">
                                                    <div class="col-6">
                                                        <div class="card bg-light">
                                                            <div class="card-body text-center">
                                                                <h4><?php echo $crm_data['leads_stats']['total_leads'] ?? 0; ?></h4>
                                                                <p class="mb-0">Total Leads</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="card bg-light">
                                                            <div class="card-body text-center">
                                                                <h4><?php echo round($crm_data['conversion']['conversion_rate'] ?? 0, 1); ?>%</h4>
                                                                <p class="mb-0">Conversion Rate</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <h6>Activity Summary</h6>
                                                <div class="row">
                                                    <div class="col-6">
                                                        <div class="card bg-light">
                                                            <div class="card-body text-center">
                                                                <h4><?php echo count($crm_data['activities'] ?? []); ?></h4>
                                                                <p class="mb-0">Activities</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="card bg-light">
                                                            <div class="card-body text-center">
                                                                <h4><?php echo count($crm_data['appointments'] ?? []); ?></h4>
                                                                <p class="mb-0">Appointments</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <!-- FullCalendar JS -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>

    <script>
        // Mobile sidebar toggle
        document.getElementById('sidebarToggle')?.addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('show');
        });

        // Initialize DataTables
        document.addEventListener('DOMContentLoaded', function() {
            $('#leadsTable').DataTable({
                responsive: true,
                pageLength: 10,
                order: [[6, 'desc']]
            });

            $('#customersTable').DataTable({
                responsive: true,
                pageLength: 10,
                order: [[5, 'desc']]
            });

            // Initialize FullCalendar
            const calendarEl = document.getElementById('calendar');
            if (calendarEl) {
                const calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridMonth',
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,timeGridWeek,listWeek'
                    },
                    events: <?php echo json_encode(array_map(function($apt) {
                        return [
                            'title' => $apt['customer_name'],
                            'start' => $apt['appointment_date'] . 'T' . $apt['appointment_time'],
                            'backgroundColor' => '#667eea'
                        ];
                    }, $crm_data['appointments'] ?? [])); ?>
                });
                calendar.render();
            }
        });

        // Chart data
        const pipelineData = {
            labels: ['New', 'Contacted', 'Qualified', 'Proposal', 'Negotiation', 'Closed'],
            datasets: [{
                label: 'Leads',
                data: [
                    <?php echo $crm_data['leads_stats']['new_leads'] ?? 0; ?>,
                    <?php echo $crm_data['leads_stats']['contacted_leads'] ?? 0; ?>,
                    <?php echo $crm_data['leads_stats']['qualified_leads'] ?? 0; ?>,
                    <?php echo $crm_data['leads_stats']['proposal_leads'] ?? 0; ?>,
                    <?php echo $crm_data['leads_stats']['negotiation_leads'] ?? 0; ?>,
                    <?php echo $crm_data['leads_stats']['closed_leads'] ?? 0; ?>
                ],
                backgroundColor: [
                    'rgba(102, 126, 234, 0.8)',
                    'rgba(23, 162, 184, 0.8)',
                    'rgba(255, 193, 7, 0.8)',
                    'rgba(108, 117, 125, 0.8)',
                    'rgba(220, 53, 69, 0.8)',
                    'rgba(40, 167, 69, 0.8)'
                ],
                borderWidth: 2
            }]
        };

        const sourceData = {
            labels: ['Website', 'Referral', 'Social Media', 'Direct', 'Other'],
            datasets: [{
                data: [35, 25, 20, 15, 5],
                backgroundColor: [
                    'rgba(102, 126, 234, 0.8)',
                    'rgba(40, 167, 69, 0.8)',
                    'rgba(23, 162, 184, 0.8)',
                    'rgba(255, 193, 7, 0.8)',
                    'rgba(108, 117, 125, 0.8)'
                ],
                borderWidth: 2
            }]
        };

        // Initialize charts
        document.addEventListener('DOMContentLoaded', function() {
            const pipelineCtx = document.getElementById('pipelineChart')?.getContext('2d');
            if (pipelineCtx) {
                new Chart(pipelineCtx, {
                    type: 'bar',
                    data: pipelineData,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            }
                        }
                    }
                });
            }

            const sourceCtx = document.getElementById('sourceChart')?.getContext('2d');
            if (sourceCtx) {
                new Chart(sourceCtx, {
                    type: 'doughnut',
                    data: sourceData,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                });
            }
        });

        // Modal functions
        function showAddLeadModal() {
            const modalContent = `
                <div class="modal fade" id="leadModal" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header bg-primary text-white">
                                <h5 class="modal-title"><i class="fas fa-plus me-2"></i>Add New Lead</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="POST" action="">
                                <input type="hidden" name="add_lead" value="1">
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Full Name *</label>
                                            <input type="text" class="form-control" name="name" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Email *</label>
                                            <input type="email" class="form-control" name="email" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Phone *</label>
                                            <input type="tel" class="form-control" name="phone" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Source</label>
                                            <select class="form-select" name="source">
                                                <option value="website">Website</option>
                                                <option value="referral">Referral</option>
                                                <option value="social_media">Social Media</option>
                                                <option value="direct">Direct</option>
                                                <option value="advertisement">Advertisement</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Budget</label>
                                            <input type="number" class="form-control" name="budget">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Property Type</label>
                                            <select class="form-select" name="property_type">
                                                <option value="apartment">Apartment</option>
                                                <option value="villa">Villa</option>
                                                <option value="plot">Plot</option>
                                                <option value="commercial">Commercial</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Location</label>
                                            <input type="text" class="form-control" name="location">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Priority</label>
                                            <select class="form-select" name="priority">
                                                <option value="low">Low</option>
                                                <option value="medium" selected>Medium</option>
                                                <option value="high">High</option>
                                            </select>
                                        </div>
                                        <div class="col-12 mb-3">
                                            <label class="form-label">Notes</label>
                                            <textarea class="form-control" name="notes" rows="3"></textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-plus me-2"></i>Add Lead
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            `;
            document.body.insertAdjacentHTML('beforeend', modalContent);
            new bootstrap.Modal(document.getElementById('leadModal')).show();
        }

        function showAddCustomerModal() {
            const modalContent = `
                <div class="modal fade" id="customerModal" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header bg-success text-white">
                                <h5 class="modal-title"><i class="fas fa-plus me-2"></i>Add New Customer</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="POST" action="">
                                <input type="hidden" name="add_customer" value="1">
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Full Name *</label>
                                            <input type="text" class="form-control" name="name" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Email *</label>
                                            <input type="email" class="form-control" name="email" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Phone *</label>
                                            <input type="tel" class="form-control" name="phone" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Address</label>
                                            <input type="text" class="form-control" name="address">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">City</label>
                                            <input type="text" class="form-control" name="city">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">State</label>
                                            <input type="text" class="form-control" name="state">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Pincode</label>
                                            <input type="text" class="form-control" name="pincode">
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-plus me-2"></i>Add Customer
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            `;
            document.body.insertAdjacentHTML('beforeend', modalContent);
            new bootstrap.Modal(document.getElementById('customerModal')).show();
        }

        function showAddActivityModal() {
            const modalContent = `
                <div class="modal fade" id="activityModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header bg-info text-white">
                                <h5 class="modal-title"><i class="fas fa-plus me-2"></i>Add Activity</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="POST" action="">
                                <input type="hidden" name="add_activity" value="1">
                                <input type="hidden" name="associate_id" value="<?php echo $associate_id; ?>">
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label">Activity Type</label>
                                        <select class="form-select" name="activity_type" required>
                                            <option value="call_made">Call Made</option>
                                            <option value="email_sent">Email Sent</option>
                                            <option value="meeting_scheduled">Meeting Scheduled</option>
                                            <option value="property_shown">Property Shown</option>
                                            <option value="follow_up">Follow Up</option>
                                            <option value="negotiation">Negotiation</option>
                                            <option value="closed_deal">Deal Closed</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Description</label>
                                        <textarea class="form-control" name="description" rows="3" required></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-info">
                                        <i class="fas fa-plus me-2"></i>Add Activity
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            `;
            document.body.insertAdjacentHTML('beforeend', modalContent);
            new bootstrap.Modal(document.getElementById('activityModal')).show();
        }

        function showAppointmentModal() {
            const modalContent = `
                <div class="modal fade" id="appointmentModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header bg-warning text-white">
                                <h5 class="modal-title"><i class="fas fa-calendar-plus me-2"></i>Schedule Appointment</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="POST" action="">
                                <input type="hidden" name="schedule_appointment" value="1">
                                <input type="hidden" name="associate_id" value="<?php echo $associate_id; ?>">
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label">Customer Name</label>
                                        <input type="text" class="form-control" name="customer_name" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Date</label>
                                        <input type="date" class="form-control" name="appointment_date" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Time</label>
                                        <input type="time" class="form-control" name="appointment_time" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Location</label>
                                        <input type="text" class="form-control" name="location" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Notes</label>
                                        <textarea class="form-control" name="notes" rows="3"></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-warning">
                                        <i class="fas fa-calendar-plus me-2"></i>Schedule
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            `;
            document.body.insertAdjacentHTML('beforeend', modalContent);
            new bootstrap.Modal(document.getElementById('appointmentModal')).show();
        }

        function showAddNoteModal() {
            const modalContent = `
                <div class="modal fade" id="noteModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header bg-secondary text-white">
                                <h5 class="modal-title"><i class="fas fa-plus me-2"></i>Add Note</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="POST" action="">
                                <input type="hidden" name="add_note" value="1">
                                <input type="hidden" name="associate_id" value="<?php echo $associate_id; ?>">
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label">Note Type</label>
                                        <select class="form-select" name="note_type" required>
                                            <option value="general">General</option>
                                            <option value="lead_note">Lead Note</option>
                                            <option value="customer_note">Customer Note</option>
                                            <option value="property_note">Property Note</option>
                                            <option value="meeting_note">Meeting Note</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Related ID (Optional)</label>
                                        <input type="text" class="form-control" name="related_id" placeholder="Lead/Customer ID">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Note</label>
                                        <textarea class="form-control" name="note" rows="5" required></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-secondary">
                                        <i class="fas fa-plus me-2"></i>Add Note
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            `;
            document.body.insertAdjacentHTML('beforeend', modalContent);
            new bootstrap.Modal(document.getElementById('noteModal')).show();
        }

        function showMessageModal() {
            const modalContent = `
                <div class="modal fade" id="messageModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header bg-primary text-white">
                                <h5 class="modal-title"><i class="fas fa-plus me-2"></i>Send Message</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="POST" action="">
                                <input type="hidden" name="send_message" value="1">
                                <input type="hidden" name="associate_id" value="<?php echo $associate_id; ?>">
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label">Recipient Type</label>
                                        <select class="form-select" name="recipient_type" required>
                                            <option value="customer">Customer</option>
                                            <option value="lead">Lead</option>
                                            <option value="admin">Admin</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Recipient ID</label>
                                        <input type="text" class="form-control" name="recipient_id" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Message Type</label>
                                        <select class="form-select" name="message_type" required>
                                            <option value="email">Email</option>
                                            <option value="sms">SMS</option>
                                            <option value="whatsapp">WhatsApp</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Subject</label>
                                        <input type="text" class="form-control" name="subject" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Message</label>
                                        <textarea class="form-control" name="message" rows="5" required></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-paper-plane me-2"></i>Send Message
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            `;
            document.body.insertAdjacentHTML('beforeend', modalContent);
            new bootstrap.Modal(document.getElementById('messageModal')).show();
        }

        // Utility functions
        function editLead(leadId) {
            alert('Lead editing functionality will be implemented');
        }

        function viewLead(leadId) {
            alert('Lead viewing functionality will be implemented');
        }

        function viewCustomer(customerId) {
            alert('Customer viewing functionality will be implemented');
        }

        function editCustomer(customerId) {
            alert('Customer editing functionality will be implemented');
        }
    </script>
</body>
</html>
