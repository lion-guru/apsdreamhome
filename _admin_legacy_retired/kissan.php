<?php
// Include database connection
include("config.php");
session_start();

// Initialize variables
$error = "";
$msg = "";

// CSRF token generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Function to handle form submission
function handleFormSubmission($con) {
    global $error, $msg;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Validate CSRF token
        if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            $error = "Invalid CSRF token.";
            return;
        }

        // Capture and sanitize input
       $farmer_name = htmlspecialchars(trim($_POST['farmer_name']));
if (!preg_match('/^[A-Za-z\s]+$/', $farmer_name)) {
    $error = "Farmer name must contain letters only.";
    return;
}
        // Validate mobile numbers (should be numeric and exactly 10 digits)
$farmer_mobile = htmlspecialchars(trim($_POST['farmer_mobile']));
if (!preg_match('/^[0-9]{10}$/', $farmer_mobile)) {
    $error = "Farmer mobile number must be a valid 10-digit number.";
    return;
}
        $bank_name = htmlspecialchars(trim($_POST['bank_name']));
        $account_number = htmlspecialchars(trim($_POST['account_number']));
        $bank_ifsc = htmlspecialchars(trim($_POST['bank_ifsc']));
        $site_name = htmlspecialchars(trim($_POST['site_name']));
        
        // Validate and sanitize numeric inputs
        $land_area = filter_var(trim($_POST['land_area']), FILTER_VALIDATE_FLOAT);
        $total_land_price = filter_var(trim($_POST['total_land_price']), FILTER_VALIDATE_FLOAT);
        //$total_paid_amount = filter_var(trim($_POST['total_paid_amount']), FILTER_VALIDATE_FLOAT);
        
        $gata_number = htmlspecialchars(trim($_POST['gata_number']));
        $district = htmlspecialchars(trim($_POST['district']));
        $tehsil = htmlspecialchars(trim($_POST['tehsil']));
        $city = htmlspecialchars(trim($_POST['city']));
        $gram = htmlspecialchars(trim($_POST['gram']));
        // Validate names (should only contain letters and spaces)
$land_manager_name = htmlspecialchars(trim($_POST['land_manager_name']));
if (!preg_match('/^[A-Za-z\s]+$/', $land_manager_name)) {
    $error = "Land manager name must contain letters only.";
    return;
}

        $land_manager_mobile = htmlspecialchars(trim($_POST['land_manager_mobile']));
if (!preg_match('/^[0-9]{10}$/', $land_manager_mobile)) {
    $error = "Land manager mobile number must be a valid 10-digit number.";
    return;
}
        $agreement_status = htmlspecialchars(trim($_POST['agreement_status']));

        // Handle file upload
        $land_paper = $_FILES['land_paper'];
        $allowed_types = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'image/jpeg', 'image/png', 'image/gif'];
        $allowed_extensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'gif'];
        $max_file_size = 10 * 1024 * 1024; // 10 MB

        // Ensure uploads directory exists
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) {
            if (!mkdir($upload_dir, 0755, true)) {
                $error = "Failed to create upload directory.";
                return;
            }
        }

        // Validate file upload
        if ($land_paper['error'] !== UPLOAD_ERR_OK) {
            $error = "File upload error: " . $land_paper['error'];
            return;
        }

        // Check file type
        if (!in_array($land_paper['type'], $allowed_types)) {
            $error = "Invalid file type. Only PDF, Word, Excel, and image files are allowed.";
            return;
        }

        // Check file extension
        $file_extension = pathinfo($land_paper['name'], PATHINFO_EXTENSION);
        if (!in_array(strtolower($file_extension), $allowed_extensions)) {
            $error = "Invalid file extension. Allowed types are: " . implode(", ", $allowed_extensions);
            return;
        }

        // Check file size
        if ($land_paper['size'] > $max_file_size) {
            $error = "File size exceeds the maximum limit of 10 MB.";
            return;
        }

        // Rename and move uploaded file
        $file_name = uniqid() . '.' . $file_extension; // Unique file name
        $file_path = $upload_dir . $file_name;
        if (!move_uploaded_file($land_paper['tmp_name'], $file_path)) {
            $error = "Failed to move uploaded file.";
            return;
        }

        // Prepare SQL statement for insertion
        $stmt = $con->prepare("INSERT INTO kisaan_land_management (farmer_name, farmer_mobile, bank_name, account_number, bank_ifsc, site_name, land_area, total_land_price, gata_number, district, tehsil, city, gram, land_paper, land_manager_name, land_manager_mobile, agreement_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        // Adjust the bind_param to include correct types
        $stmt->bind_param("ssssdddsdssssssss", $farmer_name, $farmer_mobile, $bank_name, $account_number, $bank_ifsc, $site_name, $land_area, $total_land_price, $gata_number, $district, $tehsil, $city, $gram, $file_path, $land_manager_name, $land_manager_mobile, $agreement_status);
        
        if ($stmt->execute()) {
            $msg = "Record added successfully.";
        } else {
            $error = "Error adding record: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Fetch land management records from the database
$query = "SELECT * FROM kisaan_land_management";
$result = $con->query($query);

// Handle form submission
handleFormSubmission($con);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kissan Land Management</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="shortcut icon" type="image/x-icon" href="assets/<?php echo get_asset_url('favicon.png', 'images'); ?>">
    <link rel="stylesheet" href="<?php echo get_asset_url('css/style.css', 'css'); ?>">
	<link rel="stylesheet" href="<?php echo get_asset_url('css/bootstrap.min.css', 'css'); ?>">
    <link rel="stylesheet" href="<?php echo get_asset_url('css/font-awesome.min.css', 'css'); ?>">
    <link rel="stylesheet" href="<?php echo get_asset_url('css/feathericon.min.css', 'css'); ?>">
	<script>
        
    function validateName(inputField) {
        const value = inputField.value;
        const warning = document.getElementById(inputField.dataset.warningId);
        const hasDigit = /\d/.test(value); // Check if the input contains any digits
        const hasSpecialChar = /[^A-Za-z\s]/.test(value); // Check for special characters

        if (hasDigit || hasSpecialChar) {
            warning.style.display = 'block';
            warning.textContent = "Input name should contain letters and spaces only.";
        } else {
            warning.style.display = 'none';
        }
    }

		 
        function validateMobile(inputField) {
            const value = inputField.value;
            const warning = document.getElementById(inputField.dataset.warningId);
            const hasNonDigit = /\D/.test(value); // Check if the input contains any non-digit characters

            if (hasNonDigit) {
                warning.style.display = 'block';
                warning.textContent = "Input must be a valid 10-digit mobile number (numbers only).";
            } else {
                warning.style.display = 'none';
            }
        }

    </script>
</head>
<body>
    <?php include("header.php"); ?>
    <div class="container mt-5">
        <h3>Kissan Land Management</h3>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($msg): ?>
            <div class="alert alert-success"><?php echo $msg; ?></div>
        <?php endif; ?>

        <!-- Form to Add New Kissan Land Details -->
        <form method="post" id="myForm" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
             <div class="form-group">
                <label for="farmer_name">Farmer Name</label>
                <input type="text" class="form-control" name="farmer_name" required pattern="[A-Za-z\s]+" 
                       oninput="validateName(this)" data-warning-id="farmerNameWarning">
                <div id="farmerNameWarning" class="text-danger" style="display:none;"></div>
            </div>
             <div class="form-group">
                <label for="farmer_mobile">Farmer Mobile</label>
                <input type="text" class="form-control" name="farmer_mobile" required pattern="[0-9]{10}" 
                       title="Please enter a valid 10-digit mobile number."
                       oninput="validateMobile(this)" data-warning-id="farmerMobileWarning">
                <div id="farmerMobileWarning" class="text-danger" style="display:none;"></div>
            </div>
            <div class="form-group">
                <label for="bank_name">Bank Name</label>
                <input type="text" class="form-control" name="bank_name" required>
            </div>
            <div class="form-group">
                <label for="account_number">Account Number</label>
                <input type="text" class="form-control" name="account_number" required>
            </div>
            <div class="form-group">
                <label for="bank_ifsc">Bank IFSC</label>
                <input type="text" class="form-control" name="bank_ifsc" required>
            </div>
            <div class="form-group">
                <label for="site_name">Site Name</label>
                <input type="text" class="form-control" name="site_name" required>
            </div>
            <div class="form-group">
                <label for="land_area">Land Area (in desmil)</label>
                <input type="number" step="0.01" class="form-control" name="land_area" required>
            </div>
            <div class="form-group">
                <label for="total_land_price">Total Land Price</label>
                <input type="number" step="0.01" class="form-control" name="total_land_price" required>
            </div>
          <!--  <div class="form-group">
                <label for="total_paid_amount">Total Paid Amount</label>
                <input type="number" step="0.01" class="form-control" name="total_paid_amount" required>
            </div> -->
            <div class="form-group">
                <label for="gata_number">Gata Number</label>
                <input type="text" class="form-control" name="gata_number" required>
            </div>
            <div class="form-group">
                <label for="district">District</label>
                <input type="text" class="form-control" name="district" required>
            </div>
            <div class="form-group">
                <label for="tehsil">Tehsil</label>
                <input type="text" class="form-control" name="tehsil" required>
            </div>
            <div class="form-group">
                <label for="city">City</label>
                <input type="text" class="form-control" name="city" required>
            </div>
            <div class="form-group">
                <label for="gram">Gram</label>
                <input type="text" class="form-control" name="gram" required>
            </div>
            <div class="form-group">
                <label for="land_paper">Land Paper (Upload DOC, Excel, PDF, or Image)</label>
                <input type="file" class="form-control" name="land_paper" accept=".doc,.docx,.xls,.xlsx,.pdf,.jpg,.jpeg,.png,.gif" required>
            </div>
            <div class="form-group">
    <label for="land_manager_name">Land Manager Name</label>
    <input type="text" class="form-control" name="land_manager_name" required pattern="[A-Za-z\s]+" 
           oninput="validateName(this)" data-warning-id="landManagerNameWarning">
    <div id="landManagerNameWarning" class="text-danger" style="display:none;"></div>
</div>
            <div class="form-group">
                <label for="land_manager_mobile">Land Manager Mobile</label>
                <input type="text" class="form-control" name="land_manager_mobile" required pattern="[0-9]{10}" 
                       title="Please enter a valid 10-digit mobile number."
                       oninput="validateMobile(this)" data-warning-id="landManagerMobileWarning">
                <div id="landManagerMobileWarning" class="text-danger" style="display:none;"></div>
            </div>
            <div class="form-group">
                <label for="agreement_status">Agreement Status</label>
                <select class="form-control" name="agreement_status" required>
                    <option value="Registered">Registered</option>
                    <option value="On Agreement">On Agreement</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Add Land Details</button>
        </form>
    </div>
   
    <script src="<?php echo get_asset_url('js/bootstrap.min.js', 'js'); ?>"></script>
	<script src="<?php echo get_asset_url('js/jquery-3.2.1.min.js', 'js'); ?>"></script>
    <script src="<?php echo get_asset_url('js/popper.min.js', 'js'); ?>"></script>
    <script src="assets/plugins/slimscroll/jquery.slimscroll.min.js"></script>
    <script src="<?php echo get_asset_url('js/script.js', 'js'); ?>"></script>
</body>
</html>
