<?php
require_once 'admin-functions.php';
require_once 'src/Database/Database.php';

// Get database connection
$conn = getDbConnection();
if (!$conn) {
    die("Failed to establish database connection");
}

// Initialize variables
$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $title = $_POST['title'];
                $location = $_POST['location'];
                $area = $_POST['area'];
                $purchase_date = $_POST['purchase_date'];
                $purchase_price = $_POST['purchase_price'];
                $owner_details = $_POST['owner_details'];
                $legal_status = $_POST['legal_status'];

                $sql = "INSERT INTO land_records (title, location, area, purchase_date, purchase_price, owner_details, legal_status) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssdsdss", $title, $location, $area, $purchase_date, $purchase_price, $owner_details, $legal_status);

                if ($stmt->execute()) {
                    $success = 'Land record added successfully';
                } else {
                    $error = 'Error adding land record: ' . $conn->error;
                }
                break;

            case 'upload':
                if (isset($_FILES['document']) && isset($_POST['land_id']) && isset($_POST['document_type'])) {
                    $land_id = $_POST['land_id'];
                    $document_type = $_POST['document_type'];
                    $file = $_FILES['document'];

                    $upload_dir = '../uploads/land_documents/';
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }

                    $file_name = time() . '_' . basename($file['name']);
                    $file_path = $upload_dir . $file_name;

                    if (move_uploaded_file($file['tmp_name'], $file_path)) {
                        $sql = "INSERT INTO land_documents (land_id, document_type, file_path) VALUES (?, ?, ?)";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("iss", $land_id, $document_type, $file_path);

                        if ($stmt->execute()) {
                            $success = 'Document uploaded successfully';
                        } else {
                            $error = 'Error uploading document: ' . $conn->error;
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
$sql = "SELECT * FROM land_records ORDER BY created_at DESC";
$result = $conn->query($sql);
$land_records = $result->fetch_all(MYSQLI_ASSOC);
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
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <!-- Add New Land Record Form -->
        <div class="card mb-4">
            <div class="card-header">
                <h4>Add New Land Record</h4>
            </div>
            <div class="card-body">
                <form method="POST" action="">
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
                    <input type="hidden" name="action" value="upload">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="land_id" class="form-label">Select Land Record</label>
                            <select class="form-control" id="land_id" name="land_id" required>
                                <?php foreach ($land_records as $record): ?>
                                    <option value="<?php echo $record['id']; ?>"><?php echo $record['title']; ?></option>
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
                                    <td><?php echo $record['title']; ?></td>
                                    <td><?php echo $record['location']; ?></td>
                                    <td><?php echo $record['area']; ?> sq ft</td>
                                    <td><?php echo $record['purchase_date']; ?></td>
                                    <td>$<?php echo number_format($record['purchase_price'], 2); ?></td>
                                    <td><?php echo ucfirst($record['legal_status']); ?></td>
                                    <td>
                                        <a href="edit_land_record.php?id=<?php echo $record['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                        <a href="delete_land_record.php?id=<?php echo $record['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this record?')">Delete</a>
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