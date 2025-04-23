<?php
session_start();
include 'config.php'; // Include your database connection

if (!isset($_SESSION['uid'])) {
    header("Location: login.php");
    exit();
}

require_permission('add_expense');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $uid = $_SESSION['uid'];
    $amount = $_POST['amount'];
    $source = $_POST['source'];
    $date = $_POST['date'];
    $description = $_POST['description'];

    // Insert expense into the new expenses table
    $stmt = $conn->prepare("INSERT INTO expenses (user_id, amount, source, expense_date, description) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("idsss", $uid, $amount, $source, $date, $description);
    if ($stmt->execute()) {
        // Add notification for expense
        require_once __DIR__ . '/../includes/functions/notification_util.php';
        addNotification($conn, 'Expense', 'Expense added: ' . $description, $uid);
        header("Location: expenses.php?msg=".urlencode('Expense added successfully.'));
        exit();
    } else {
        echo "Error: " . htmlspecialchars($stmt->error);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Add Expense</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body { background: #f8f9fa; }
        .form-label { font-weight: 500; }
        .form-floating > .fa { position: absolute; left: 20px; top: 22px; color: #aaa; pointer-events: none; }
        .form-floating input, .form-floating select { padding-left: 2.5rem; }
    </style>
</head>
<body>
<?php include __DIR__ . '/../includes/templates/dynamic_header.php'; ?>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <h2 class="mb-4 text-center">Add New Expense</h2>
                        <form method="POST" action="" class="needs-validation" novalidate>
                            <div class="form-floating mb-3 position-relative">
                                <input type="number" class="form-control" id="amount" name="amount" step="0.01" placeholder="Amount" required value="<?php echo isset($_POST['amount']) ? htmlspecialchars($_POST['amount']) : ''; ?>">
                                <label for="amount"><i class="fa fa-rupee-sign"></i> Amount</label>
                                <div class="invalid-feedback">Please enter the amount.</div>
                            </div>
                            <div class="form-floating mb-3 position-relative">
                                <input type="text" class="form-control" id="source" name="source" placeholder="Source" required value="<?php echo isset($_POST['source']) ? htmlspecialchars($_POST['source']) : ''; ?>">
                                <label for="source"><i class="fa fa-briefcase"></i> Source</label>
                                <div class="invalid-feedback">Please enter the source.</div>
                            </div>
                            <div class="form-floating mb-3 position-relative">
                                <input type="date" class="form-control" id="date" name="date" placeholder="Date" required value="<?php echo isset($_POST['date']) ? htmlspecialchars($_POST['date']) : ''; ?>">
                                <label for="date"><i class="fa fa-calendar"></i> Date</label>
                                <div class="invalid-feedback">Please select a date.</div>
                            </div>
                            <div class="form-floating mb-4 position-relative">
                                <input type="text" class="form-control" id="description" name="description" placeholder="Description" required value="<?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?>">
                                <label for="description"><i class="fa fa-align-left"></i> Description</label>
                                <div class="invalid-feedback">Please enter a description.</div>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg rounded-pill"><i class="fa fa-plus"></i> Add Expense</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php include __DIR__ . '/../includes/templates/new_footer.php'; ?>
</body>
</html>
