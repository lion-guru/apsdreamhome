<?php
require_once 'admin-functions.php';
use App\Core\Database;

// Check if user is admin
if (!isAdmin()) {
    header('Location: login.php');
    exit();
}

$db = \App\Core\App::database();

// Initialize variables
$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token. Please refresh and try again.';
    } elseif (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $title = trim($_POST['title'] ?? '');
                $location = trim($_POST['location'] ?? '');
                $area = floatval($_POST['area'] ?? 0);
                $purchase_date = $_POST['purchase_date'] ?? null;
                $purchase_price = floatval($_POST['purchase_price'] ?? 0);
                $owner_details = trim($_POST['owner_details'] ?? '');
                $legal_status = $_POST['legal_status'] ?? 'clear';

                if (empty($title) || empty($location)) {
                    $error = 'Title and Location are required.';
                } else {
                    try {
                        $sql = "INSERT INTO land_records (title, location, area, purchase_date, purchase_price, owner_details, legal_status)
                                VALUES (?, ?, ?, ?, ?, ?, ?)";
                        $db->execute($sql, [$title, $location, $area, $purchase_date, $purchase_price, $owner_details, $legal_status]);

                        AdminLogger::log('LAND_RECORD_ADDED', [
                            'title' => $title,
                            'location' => $location,
                            'admin' => getAuthUsername()
                        ]);

                        $success = 'Land record added successfully';
                    } catch (Exception $e) {
                        $error = 'Error adding land record: ' . $e->getMessage();
                    }
                }
                break;

            case 'upload':
                if (isset($_FILES['document']) && isset($_POST['land_id']) && isset($_POST['document_type'])) {
                    $land_id = intval($_POST['land_id']);
                    $document_type = $_POST['document_type'];
                    $file = $_FILES['document'];

                    $upload_dir = '../../uploads/land_documents/';
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }

                    $file_ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $file_name = time() . '_' . \App\Helpers\SecurityHelper::generateRandomString(16, false) . '.' . $file_ext;
                    $file_path = $upload_dir . $file_name;

                    if (move_uploaded_file($file['tmp_name'], $file_path)) {
                        try {
                            $sql = "INSERT INTO land_documents (land_id, document_type, file_path) VALUES (?, ?, ?)";
                            $db->execute($sql, [$land_id, $document_type, $file_path]);

                            AdminLogger::log('LAND_DOCUMENT_UPLOADED', [
                                'land_id' => $land_id,
                                'document_type' => $document_type,
                                'file' => $file_name,
                                'admin' => getAuthUsername()
                            ]);

                            $success = 'Document uploaded successfully';
                        } catch (Exception $e) {
                            $error = 'Error uploading document: ' . $e->getMessage();
                        }
                    } else {
                        $error = 'Error uploading file';
                    }
                }
                break;
        }
    }
}

// Fetch all land records
try {
    $land_records = $db->fetchAll("SELECT * FROM land_records ORDER BY created_at DESC");
} catch (Exception $e) {
    $land_records = [];
    $error = "Error fetching land records: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Land Records Management</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="container mt-4">
        <h2>Land Records Management</h2>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo h($error); ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo h($success); ?></div>
        <?php endif; ?>

        <!-- Add New Land Record Form -->
        <div class="card mb-4">
            <div class="card-header">
                <h4>Add New Land Record</h4>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <?php echo getCsrfField(); ?>
                    <input type="hidden" name="action" value="add">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="location" class="form-label">Location</label>
                            <input type="text" class="form-control" id="location" name="location" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="area" class="form-label">Area (sq ft)</label>
                            <input type="number" step="0.01" class="form-control" id="area" name="area" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="purchase_date" class="form-label">Purchase Date</label>
                            <input type="date" class="form-control" id="purchase_date" name="purchase_date" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="purchase_price" class="form-label">Purchase Price</label>
                            <input type="number" step="0.01" class="form-control" id="purchase_price" name="purchase_price" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="owner_details" class="form-label">Owner Details</label>
                        <textarea class="form-control" id="owner_details" name="owner_details" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="legal_status" class="form-label">Legal Status</label>
                        <select class="form-control" id="legal_status" name="legal_status" required>
                            <option value="clear">Clear Title</option>
                            <option value="pending">Pending Clearance</option>
                            <option value="disputed">Disputed</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Add Land Record</button>
                </form>
            </div>
        </div>

        <!-- Upload Document Form -->
        <div class="card mb-4">
            <div class="card-header">
                <h4>Upload Document</h4>
            </div>
            <div class="card-body">
                <form method="POST" action="" enctype="multipart/form-data">
                    <?php echo getCsrfField(); ?>
                    <input type="hidden" name="action" value="upload">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="land_id" class="form-label">Select Land Record</label>
                            <select class="form-control" id="land_id" name="land_id" required>
                                <?php foreach ($land_records as $record): ?>
                                    <option value="<?php echo h($record['id']); ?>"><?php echo h($record['title']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="document_type" class="form-label">Document Type</label>
                            <select class="form-control" id="document_type" name="document_type" required>
                                <option value="deed">Property Deed</option>
                                <option value="survey">Survey Report</option>
                                <option value="tax">Tax Documents</option>
                                <option value="permit">Permits</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="document" class="form-label">Upload Document</label>
                            <input type="file" class="form-control" id="document" name="document" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Upload Document</button>
                </form>
            </div>
        </div>

        <!-- Land Records Table -->
        <div class="card">
            <div class="card-header">
                <h4>Land Records</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Location</th>
                                <th>Area</th>
                                <th>Purchase Date</th>
                                <th>Purchase Price</th>
                                <th>Legal Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($land_records as $record): ?>
                                <tr>
                                    <td><?php echo h($record['title']); ?></td>
                                    <td><?php echo h($record['location']); ?></td>
                                    <td><?php echo h($record['area']); ?> sq ft</td>
                                    <td><?php echo h($record['purchase_date']); ?></td>
                                    <td>$<?php echo h(number_format($record['purchase_price'], 2)); ?></td>
                                    <td><?php echo ucfirst(h($record['legal_status'])); ?></td>
                                    <td>
                                        <a href="edit_land_record.php?id=<?php echo h($record['id']); ?>" class="btn btn-sm btn-primary">Edit</a>
                                        <a href="delete_land_record.php?id=<?php echo h($record['id']); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this record?')">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize Bootstrap components
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // Initialize form validation
            $('.needs-validation').on('submit', function(event) {
                if (!this.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                $(this).addClass('was-validated');
            });
        });
    </script>
</body>
</html>
