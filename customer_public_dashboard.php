<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Check if customer is logged in
if (!isset($_SESSION['customer_id'])) {
    header('Location: auth/login.php');
    exit();
}

$customer_id = $_SESSION['customer_id'];
$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($_POST['action']) {
        case 'update_profile':
            try {
                $stmt = $conn->prepare("UPDATE customers SET name = ?, email = ?, mobile = ?, address = ? WHERE id = ?");
                $stmt->bind_param("ssssi", $_POST['name'], $_POST['email'], $_POST['mobile'], $_POST['address'], $customer_id);
                $stmt->execute();
                $message = "Profile updated successfully!";
            } catch (Exception $e) {
                $error = "Error updating profile: " . $e->getMessage();
            }
            break;

        case 'submit_inquiry':
            try {
                $stmt = $conn->prepare("INSERT INTO customer_inquiries (customer_id, subject, message, inquiry_type, created_at) VALUES (?, ?, ?, ?, NOW())");
                $stmt->bind_param("isss", $customer_id, $_POST['subject'], $_POST['inquiry_message'], $_POST['inquiry_type']);
                $stmt->execute();
                $message = "Inquiry submitted successfully! We will get back to you soon.";
            } catch (Exception $e) {
                $error = "Error submitting inquiry: " . $e->getMessage();
            }
            break;
    }
}

// Fetch customer data
$customer_result = $conn->query("SELECT * FROM customers WHERE id = $customer_id");
$customer = $customer_result->fetch_assoc();

// Fetch customer's bookings
$bookings_result = $conn->query("SELECT b.*, p.property_name, p.property_type FROM bookings b LEFT JOIN properties p ON b.property_id = p.id WHERE b.customer_id = $customer_id ORDER BY b.created_at DESC");

// Fetch customer's payments
$payments_result = $conn->query("SELECT * FROM payments WHERE customer_id = $customer_id ORDER BY payment_date DESC");

// Fetch customer's documents
$documents_result = $conn->query("SELECT * FROM customer_documents WHERE customer_id = $customer_id ORDER BY uploaded_at DESC");

// Fetch EMI details
$emi_result = $conn->query("SELECT * FROM emi_schedule WHERE customer_id = $customer_id ORDER BY due_date ASC");

// Fetch customer inquiries
$inquiries_result = $conn->query("SELECT * FROM customer_inquiries WHERE customer_id = $customer_id ORDER BY created_at DESC LIMIT 10");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard - APS Dream Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .dashboard-container {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 2rem 0;
        }
        .dashboard-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
            text-align: center;
        }
        .btn-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 25px;
            padding: 0.5rem 1.5rem;
            color: white;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
            color: white;
        }
        .table-custom {
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .table-custom thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .nav-pills .nav-link.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 25px;
        }
        .customer-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            border-radius: 20px 20px 0 0;
        }
        .form-control, .form-select {
            border-radius: 10px;
            border: 2px solid #e0e6ed;
            padding: 0.75rem 1rem;
        }
        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .status-badge {
            border-radius: 20px;
            padding: 0.5rem 1rem;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .modal-content {
            border-radius: 20px;
            border: none;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="container-fluid">
            <!-- Header Section -->
            <div class="row">
                <div class="col-12">
                    <div class="dashboard-card">
                        <div class="customer-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h1><i class="fas fa-user-circle me-3"></i>Welcome, <?php echo htmlspecialchars($customer['name']); ?>!</h1>
                                    <p class="mb-0">Customer ID: #<?php echo $customer['id']; ?> | Member since: <?php echo date('M Y', strtotime($customer['created_at'])); ?></p>
                                </div>
                                <div class="text-end">
                                    <button class="btn btn-outline-light me-2" data-bs-toggle="modal" data-bs-target="#profileModal">
                                        <i class="fas fa-edit me-1"></i>Edit Profile
                                    </button>
                                    <a href="auth/logout.php" class="btn btn-outline-light">
                                        <i class="fas fa-sign-out-alt me-1"></i>Logout
                                    </a>
                                </div>
                            </div>
                        </div>

                        <?php if ($message): ?>
                            <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
                                <?php echo $message; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show m-3" role="alert">
                                <?php echo $error; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <!-- Statistics Cards -->
                        <div class="row p-4">
                            <div class="col-md-3">
                                <div class="stats-card">
                                    <i class="fas fa-home fa-2x mb-2"></i>
                                    <h4><?php echo $bookings_result->num_rows; ?></h4>
                                    <p>Total Bookings</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stats-card">
                                    <i class="fas fa-credit-card fa-2x mb-2"></i>
                                    <h4><?php echo $payments_result->num_rows; ?></h4>
                                    <p>Total Payments</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stats-card">
                                    <i class="fas fa-file-alt fa-2x mb-2"></i>
                                    <h4><?php echo $documents_result->num_rows; ?></h4>
                                    <p>Documents</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stats-card">
                                    <i class="fas fa-calendar-check fa-2x mb-2"></i>
                                    <h4><?php echo $emi_result->num_rows; ?></h4>
                                    <p>EMI Installments</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Navigation Tabs -->
            <div class="row">
                <div class="col-12">
                    <div class="dashboard-card p-4">
                        <ul class="nav nav-pills mb-4" id="pills-tab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="pills-bookings-tab" data-bs-toggle="pill" data-bs-target="#pills-bookings" type="button" role="tab">
                                    <i class="fas fa-home me-1"></i>My Bookings
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="pills-payments-tab" data-bs-toggle="pill" data-bs-target="#pills-payments" type="button" role="tab">
                                    <i class="fas fa-credit-card me-1"></i>Payment History
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="pills-emi-tab" data-bs-toggle="pill" data-bs-target="#pills-emi" type="button" role="tab">
                                    <i class="fas fa-calendar-check me-1"></i>EMI Schedule
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="pills-documents-tab" data-bs-toggle="pill" data-bs-target="#pills-documents" type="button" role="tab">
                                    <i class="fas fa-file-alt me-1"></i>Documents
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="pills-inquiry-tab" data-bs-toggle="pill" data-bs-target="#pills-inquiry" type="button" role="tab">
                                    <i class="fas fa-question-circle me-1"></i>Support
                                </button>
                            </li>
                        </ul>

                        <!-- Tab Content -->
                        <div class="tab-content" id="pills-tabContent">
                            <!-- Bookings Tab -->
                            <div class="tab-pane fade show active" id="pills-bookings" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table table-custom">
                                        <thead>
                                            <tr>
                                                <th>Property</th>
                                                <th>Type</th>
                                                <th>Amount</th>
                                                <th>Status</th>
                                                <th>Booking Date</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($booking = $bookings_result->fetch_assoc()): ?>
                                            <tr>
                                                <td><strong><?php echo htmlspecialchars($booking['property_name']); ?></strong></td>
                                                <td><?php echo ucfirst($booking['property_type']); ?></td>
                                                <td>₹<?php echo number_format($booking['total_amount']); ?></td>
                                                <td>
                                                    <span class="status-badge bg-<?php 
                                                        echo $booking['status'] === 'confirmed' ? 'success' : 
                                                            ($booking['status'] === 'pending' ? 'warning' : 'secondary'); 
                                                    ?>">
                                                        <?php echo ucfirst($booking['status']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('M d, Y', strtotime($booking['created_at'])); ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-primary" title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Payments Tab -->
                            <div class="tab-pane fade" id="pills-payments" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table table-custom">
                                        <thead>
                                            <tr>
                                                <th>Transaction ID</th>
                                                <th>Amount</th>
                                                <th>Payment Type</th>
                                                <th>Status</th>
                                                <th>Date</th>
                                                <th>Receipt</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $payments_result->data_seek(0);
                                            while ($payment = $payments_result->fetch_assoc()): 
                                            ?>
                                            <tr>
                                                <td>#<?php echo $payment['transaction_id']; ?></td>
                                                <td>₹<?php echo number_format($payment['amount']); ?></td>
                                                <td><?php echo ucfirst($payment['payment_type']); ?></td>
                                                <td>
                                                    <span class="status-badge bg-<?php 
                                                        echo $payment['status'] === 'success' ? 'success' : 
                                                            ($payment['status'] === 'pending' ? 'warning' : 'danger'); 
                                                    ?>">
                                                        <?php echo ucfirst($payment['status']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('M d, Y', strtotime($payment['payment_date'])); ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-success" title="Download Receipt">
                                                        <i class="fas fa-download"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- EMI Tab -->
                            <div class="tab-pane fade" id="pills-emi" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table table-custom">
                                        <thead>
                                            <tr>
                                                <th>EMI No.</th>
                                                <th>Amount</th>
                                                <th>Due Date</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($emi = $emi_result->fetch_assoc()): ?>
                                            <tr>
                                                <td>#<?php echo $emi['emi_number']; ?></td>
                                                <td>₹<?php echo number_format($emi['amount']); ?></td>
                                                <td><?php echo date('M d, Y', strtotime($emi['due_date'])); ?></td>
                                                <td>
                                                    <span class="status-badge bg-<?php 
                                                        echo $emi['status'] === 'paid' ? 'success' : 
                                                            ($emi['status'] === 'overdue' ? 'danger' : 'warning'); 
                                                    ?>">
                                                        <?php echo ucfirst($emi['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($emi['status'] === 'pending'): ?>
                                                    <button class="btn btn-sm btn-outline-primary" title="Pay Now">
                                                        <i class="fas fa-credit-card"></i> Pay
                                                    </button>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Documents Tab -->
                            <div class="tab-pane fade" id="pills-documents" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="table-responsive">
                                            <table class="table table-custom">
                                                <thead>
                                                    <tr>
                                                        <th>Document Name</th>
                                                        <th>Type</th>
                                                        <th>Status</th>
                                                        <th>Uploaded Date</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php while ($doc = $documents_result->fetch_assoc()): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($doc['document_name']); ?></td>
                                                        <td><?php echo ucfirst($doc['document_type']); ?></td>
                                                        <td>
                                                            <span class="status-badge bg-<?php 
                                                                echo $doc['status'] === 'approved' ? 'success' : 
                                                                    ($doc['status'] === 'pending' ? 'warning' : 'danger'); 
                                                            ?>">
                                                                <?php echo ucfirst($doc['status']); ?>
                                                            </span>
                                                        </td>
                                                        <td><?php echo date('M d, Y', strtotime($doc['uploaded_at'])); ?></td>
                                                        <td>
                                                            <button class="btn btn-sm btn-outline-primary" title="View Document">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                            <button class="btn btn-sm btn-outline-success" title="Download">
                                                                <i class="fas fa-download"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                    <?php endwhile; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card">
                                            <div class="card-header">
                                                <h6><i class="fas fa-upload me-2"></i>Upload New Document</h6>
                                            </div>
                                            <div class="card-body">
                                                <form method="POST" enctype="multipart/form-data">
                                                    <input type="hidden" name="action" value="upload_document">
                                                    <div class="mb-3">
                                                        <label class="form-label">Document Type</label>
                                                        <select name="document_type" class="form-select" required>
                                                            <option value="">Select Type</option>
                                                            <option value="aadhar">Aadhar Card</option>
                                                            <option value="pan">PAN Card</option>
                                                            <option value="income">Income Proof</option>
                                                            <option value="bank">Bank Statement</option>
                                                            <option value="other">Other</option>
                                                        </select>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Document File</label>
                                                        <input type="file" name="document_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                                                    </div>
                                                    <button type="submit" class="btn btn-custom w-100">
                                                        <i class="fas fa-upload me-1"></i>Upload Document
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Support/Inquiry Tab -->
                            <div class="tab-pane fade" id="pills-inquiry" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-8">
                                        <h5>Recent Inquiries</h5>
                                        <div class="table-responsive">
                                            <table class="table table-custom">
                                                <thead>
                                                    <tr>
                                                        <th>Subject</th>
                                                        <th>Type</th>
                                                        <th>Status</th>
                                                        <th>Date</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php while ($inquiry = $inquiries_result->fetch_assoc()): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($inquiry['subject']); ?></td>
                                                        <td><?php echo ucfirst($inquiry['inquiry_type']); ?></td>
                                                        <td>
                                                            <span class="status-badge bg-<?php 
                                                                echo $inquiry['status'] === 'resolved' ? 'success' : 
                                                                    ($inquiry['status'] === 'in_progress' ? 'warning' : 'secondary'); 
                                                            ?>">
                                                                <?php echo ucfirst(str_replace('_', ' ', $inquiry['status'])); ?>
                                                            </span>
                                                        </td>
                                                        <td><?php echo date('M d, Y', strtotime($inquiry['created_at'])); ?></td>
                                                    </tr>
                                                    <?php endwhile; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card">
                                            <div class="card-header">
                                                <h6><i class="fas fa-question-circle me-2"></i>Submit New Inquiry</h6>
                                            </div>
                                            <div class="card-body">
                                                <form method="POST">
                                                    <input type="hidden" name="action" value="submit_inquiry">
                                                    <div class="mb-3">
                                                        <label class="form-label">Inquiry Type</label>
                                                        <select name="inquiry_type" class="form-select" required>
                                                            <option value="">Select Type</option>
                                                            <option value="general">General Inquiry</option>
                                                            <option value="payment">Payment Issue</option>
                                                            <option value="booking">Booking Related</option>
                                                            <option value="technical">Technical Support</option>
                                                            <option value="complaint">Complaint</option>
                                                        </select>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Subject</label>
                                                        <input type="text" name="subject" class="form-control" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Message</label>
                                                        <textarea name="inquiry_message" class="form-control" rows="4" required></textarea>
                                                    </div>
                                                    <button type="submit" class="btn btn-custom w-100">
                                                        <i class="fas fa-paper-plane me-1"></i>Submit Inquiry
                                                    </button>
                                                </form>
                                            </div>
                                        </div>

                                        <div class="card mt-3">
                                            <div class="card-header">
                                                <h6><i class="fas fa-phone me-2"></i>Contact Information</h6>
                                            </div>
                                            <div class="card-body">
                                                <p><i class="fas fa-phone text-primary me-2"></i>+91 98765 43210</p>
                                                <p><i class="fas fa-envelope text-primary me-2"></i>support@apsdreamhome.com</p>
                                                <p><i class="fas fa-map-marker-alt text-primary me-2"></i>Gorakhpur, Uttar Pradesh</p>
                                                <p><i class="fas fa-clock text-primary me-2"></i>Mon-Sat: 9:00 AM - 6:00 PM</p>
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
    </div>

    <!-- Profile Edit Modal -->
    <div class="modal fade" id="profileModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Profile</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="update_profile">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($customer['name']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($customer['email']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mobile</label>
                            <input type="tel" name="mobile" class="form-control" value="<?php echo htmlspecialchars($customer['mobile']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control" rows="3"><?php echo htmlspecialchars($customer['address']); ?></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-custom">Update Profile</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-refresh statistics every 30 seconds
        setInterval(function() {
            location.reload();
        }, 30000);

        // Add smooth scrolling to navigation
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
    </script>
</body>
</html>