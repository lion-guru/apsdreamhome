<?php include("../includes/templates/dynamic_header.php");?>
<?php
session_start();
require("config.php");

// Secure authentication check
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $query = mysqli_query($con, "SELECT * FROM kisaan_land_management WHERE id = $id");
    $record = mysqli_fetch_assoc($query);
    if (!$record) {
        echo "Record not found!";
        exit();
    }
} else {
    echo "No ID provided!";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Land Record</title>
    <link rel="stylesheet" href="<?php echo get_asset_url('css/bootstrap.min.css', 'css'); ?>">
</head>
<body>
    <div class="container">
        <h2>Edit Land Record</h2>
        <form action="update_land_record.php" method="POST">
            <input type="hidden" name="id" value="<?php echo $record['id']; ?>">
            <div class="form-group">
                <label for="farmer_name">Farmer Name:</label>
                <input type="text" class="form-control" id="farmer_name" name="farmer_name" value="<?php echo htmlspecialchars($record['farmer_name']); ?>" required>
            </div>
            <div class="form-group">
                <label for="farmer_mobile">Farmer Mobile:</label>
                <input type="text" class="form-control" id="farmer_mobile" name="farmer_mobile" value="<?php echo htmlspecialchars($record['farmer_mobile']); ?>" required>
            </div>
            <div class="form-group">
                <label for="bank_name">Bank Name:</label>
                <input type="text" class="form-control" id="bank_name" name="bank_name" value="<?php echo htmlspecialchars($record['bank_name']); ?>" required>
            </div>
            <div class="form-group">
                <label for="account_number">Account Number:</label>
                <input type="text" class="form-control" id="account_number" name="account_number" value="<?php echo htmlspecialchars($record['account_number']); ?>" required>
            </div>
            <div class="form-group">
                <label for="bank_ifsc">Bank IFSC:</label>
                <input type="text" class="form-control" id="bank_ifsc" name="bank_ifsc" value="<?php echo htmlspecialchars($record['bank_ifsc']); ?>" required>
            </div>
            <div class="form-group">
                <label for="site_name">Site Name:</label>
                <input type="text" class="form-control" id="site_name" name="site_name" value="<?php echo htmlspecialchars($record['site_name']); ?>" required>
            </div>
            <div class="form-group">
                <label for="land_area">Land Area (in decmil):</label>
                <input type="number" class="form-control" id="land_area" name="land_area" value="<?php echo htmlspecialchars($record['land_area']); ?>" required>
            </div>
            <div class="form-group">
                <label for="total_land_price">Total Land Price:</label>
                <input type="number" class="form-control" id="total_land_price" name="total_land_price" value="<?php echo htmlspecialchars($record['total_land_price']); ?>" required>
            </div>
            <div class="form-group">
                <label for="total_paid_amount">Total Paid Amount:</label>
                <input type="number" class="form-control" id="total_paid_amount" name="total_paid_amount" value="<?php echo htmlspecialchars($record['total_paid_amount']); ?>" required>
            </div>
            <div class="form-group">
                <label for="amount_pending">Amount Pending:</label>
                <input type="number" class="form-control" id="amount_pending" name="amount_pending" value="<?php echo htmlspecialchars($record['amount_pending']); ?>" required>
            </div>
            <div class="form-group">
                <label for="gata_number">Gata Number:</label>
                <input type="text" class="form-control" id="gata_number" name="gata_number" value="<?php echo htmlspecialchars($record['gata_number']); ?>" required>
            </div>
            <div class="form-group">
                <label for="district">District:</label>
                <input type="text" class="form-control" id="district" name="district" value="<?php echo htmlspecialchars($record['district']); ?>" required>
            </div>
            <div class="form-group">
                <label for="tehsil">Tehsil:</label>
                <input type="text" class="form-control" id="tehsil" name="tehsil" value="<?php echo htmlspecialchars($record['tehsil']); ?>" required>
            </div>
            <div class="form-group">
                <label for="city">City:</label>
                <input type="text" class="form-control" id="city" name="city" value="<?php echo htmlspecialchars($record['city']); ?>" required>
            </div>
            <div class="form-group">
                <label for="gram">Gram:</label>
                <input type="text" class="form-control" id="gram" name="gram" value="<?php echo htmlspecialchars($record['gram']); ?>" required>
            </div>
            <div class="form-group">
                <label for="land_manager_name">Land Manager Name:</label>
                <input type="text" class="form-control" id="land_manager_name" name="land_manager_name" value="<?php echo htmlspecialchars($record['land_manager_name']); ?>" required>
            </div>
            <div class="form-group">
                <label for="land_manager_mobile">Land Manager Mobile:</label>
                <input type="text" class="form-control" id="land_manager_mobile" name="land_manager_mobile" value="<?php echo htmlspecialchars($record['land_manager_mobile']); ?>" required>
            </div>
            <div class="form-group">
                <label for="agreement_status">Agreement Status:</label>
                <input type="text" class="form-control" id="agreement_status" name="agreement_status" value="<?php echo htmlspecialchars($record['agreement_status']); ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Update Record</button>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php include("../includes/templates/new_footer.php");?>
</body>
</html>
