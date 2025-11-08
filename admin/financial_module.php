<?php
session_start();
require_once __DIR__ . '/includes/session_manager.php';
require_once __DIR__ . '/../includes/db_config.php';
require_once __DIR__ . '/../includes/log_admin_activity.php';

// Role-based access control: Only finance or superadmin can access financial module
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true || !in_array($_SESSION['admin_role'], ['finance','superadmin'])) {
    header('Location: unauthorized.php?error=unauthorized');
    exit();
}

// Function to add income
function addIncome($con, $user_id, $amount, $source, $date, $description) {
    $stmt = $con->prepare("INSERT INTO income (user_id, amount, source, date, description) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("idsss", $user_id, $amount, $source, $date, $description);
    $stmt->execute();
    log_admin_activity('add_income', 'User ID: ' . $user_id . ', amount: ' . $amount . ', source: ' . $source . ', date: ' . $date . ', description: ' . $description);
    $stmt->close();
}

// Function to add expense
function addExpense($con, $user_id, $amount, $category, $date, $description) {
    $stmt = $con->prepare("INSERT INTO expenses (user_id, amount, category, date, description) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("idsss", $user_id, $amount, $category, $date, $description);
    $stmt->execute();
    log_admin_activity('add_expense', 'User ID: ' . $user_id . ', amount: ' . $amount . ', category: ' . $category . ', date: ' . $date . ', description: ' . $description);
    $stmt->close();
}

// Function to fetch all transactions
function getTransactions($con, $user_id) {
    $stmt = $con->prepare("SELECT * FROM (SELECT id, amount, source AS category, date, description, 'income' AS type FROM income WHERE user_id = ? UNION ALL SELECT id, amount, category, date, description, 'expense' AS type FROM expenses WHERE user_id = ?) AS transactions ORDER BY date DESC");
    $stmt->bind_param("ii", $user_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Function to delete a transaction
function deleteTransaction($con, $id, $type) {
    if ($type === 'income') {
        $stmt = $con->prepare("DELETE FROM income WHERE id = ?");
    } else {
        $stmt = $con->prepare("DELETE FROM expenses WHERE id = ?");
    }
    $stmt->bind_param("i", $id);
    $stmt->execute();
    log_admin_activity('delete_transaction', 'Transaction ID: ' . $id . ', type: ' . $type);
    $stmt->close();
}

// Function to fetch a transaction for editing
function getTransaction($con, $id, $type) {
    if ($type === 'income') {
        $stmt = $con->prepare("SELECT * FROM income WHERE id = ?");
    } else {
        $stmt = $con->prepare("SELECT * FROM expenses WHERE id = ?");
    }
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id']; // Assuming user_id is stored in session

    // Validate input
    $amount = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_FLOAT);
    $date = filter_input(INPUT_POST, 'date', FILTER_SANITIZE_STRING);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);

    if (isset($_POST['add_income']) && $amount !== false && $amount > 0) {
        $source = filter_input(INPUT_POST, 'source', FILTER_SANITIZE_STRING);
        addIncome($con, $user_id, $amount, $source, $date, $description);
    }

    if (isset($_POST['add_expense']) && $amount !== false && $amount > 0) {
        $category = filter_input(INPUT_POST, 'category', FILTER_SANITIZE_STRING);
        addExpense($con, $user_id, $amount, $category, $date, $description);
    }

    // Edit transaction
    if (isset($_POST['edit_transaction'])) {
        $transaction_id = filter_input(INPUT_POST, 'transaction_id', FILTER_VALIDATE_INT);
        $transaction_type = filter_input(INPUT_POST, 'transaction_type', FILTER_SANITIZE_STRING);
        if ($transaction_type === 'income') {
            $stmt = $con->prepare("UPDATE income SET amount = ?, source = ?, date = ?, description = ? WHERE id = ?");
            $stmt->bind_param("dsssi", $_POST['amount'], $_POST['source'], $date, $description, $transaction_id);
        } else {
            $stmt = $con->prepare("UPDATE expenses SET amount = ?, category = ?, date = ?, description = ? WHERE id = ?");
            $stmt->bind_param("dsssi", $_POST['amount'], $_POST['category'], $date, $description, $transaction_id);
        }
        $stmt->execute();
        log_admin_activity('edit_transaction', 'Transaction ID: ' . $transaction_id . ', type: ' . $transaction_type . ', amount: ' . $_POST['amount'] . ', category/source: ' . ($_POST['category'] ?? $_POST['source']) . ', date: ' . $date . ', description: ' . $description);
        $stmt->close();
    }
}

// Handle deletion of transactions
if (isset($_GET['delete']) && isset($_GET['type'])) {
    deleteTransaction($con, $_GET['delete'], $_GET['type']);
    header("Location: financial_module.php");
    exit();
}

// Fetch totals
function getTotalIncome($con, $user_id) {
    $stmt = $con->prepare("SELECT SUM(amount) FROM income WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($total);
    $stmt->fetch();
    $stmt->close();
    return $total ?: 0;
}

function getTotalExpenses($con, $user_id) {
    $stmt = $con->prepare("SELECT SUM(amount) FROM expenses WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($total);
    $stmt->fetch();
    $stmt->close();
    return $total ?: 0;
}

$user_id = $_SESSION['user_id']; // Assuming user_id is stored in session
$total_income = getTotalIncome($con, $user_id);
$total_expenses = getTotalExpenses($con, $user_id);
$transactions = getTransactions($con, $user_id);
$error_message = "";
$success_message = "";

// Check for user roles
$is_admin = ($_SESSION['admin_role'] === 'superadmin'); // Assuming user role is stored in session
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>Financial Module - Dashboard</title>
    <link rel="stylesheet" href="<?php echo get_asset_url('css/bootstrap.min.css', 'css'); ?>">
    <link rel="stylesheet" href="<?php echo get_asset_url('css/style.css', 'css'); ?>">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>
<body>

    <!-- Main Wrapper -->
    <?php include("header.php"); ?>
    <!-- /Header -->

    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content container-fluid">

            <!-- Page Header -->
            <div class="page-header">
                <div class="row">
                    <div class="col-sm-12">
                        <h3 class="page-title">Financial Module</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item active">Dashboard</li>
                        </ul>
                    </div>
                </div>
            </div>
            <!-- /Page Header -->

            <div class="row">
                <div class="col-xl-6 col-sm-12">
                    <h4>Total Income: <?php echo number_format($total_income, 2); ?></h4>
                    <h4>Total Expenses: <?php echo number_format($total_expenses, 2); ?></h4>
                    <h4>Net Balance: <?php echo number_format($total_income - $total_expenses, 2); ?></h4>
                </div>
            </div>

            <!-- Income Form -->
            <div class="row">
                <div class="col-xl-6 col-sm-12">
                    <h5>Add Income</h5>
                    <form method="POST" action="">
                        <input type="text" name="source" placeholder="Income Source" required>
                        <input type="number" step="0.01" name="amount" placeholder="Amount" required>
                        <input type="date" name="date" required>
                        <textarea name="description" placeholder="Description"></textarea>
                        <button type="submit" name="add_income" class="btn btn-primary">Add Income</button>
                    </form>
                </div>
            </div>

            <!-- Expense Form -->
            <div class="row">
                <div class="col-xl-6 col-sm-12">
                    <h5>Add Expense</h5>
                    <form method="POST" action="">
                        <input type="text" name="category" placeholder="Expense Category" required>
                        <input type="number" step="0.01" name="amount" placeholder="Amount" required>
                        <input type="date" name="date" required>
                        <textarea name="description" placeholder="Description"></textarea>
                        <button type="submit" name="add_expense" class="btn btn-danger">Add Expense</button>
                    </form>
                </div>
            </div>

            <!-- Transaction History -->
            <div class="row">
                <div class="col-xl-12 col-sm-12">
                    <h5>Transaction History</h5>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Category</th>
                                <th>Amount</th>
                                <th>Description</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($transactions as $transaction): ?>
                                <tr>
                                    <td><?php echo $transaction['date']; ?></td>
                                    <td><?php echo $transaction['category']; ?></td>
                                    <td><?php echo number_format($transaction['amount'], 2); ?></td>
                                    <td><?php echo $transaction['description']; ?></td>
                                    <td>
                                        <a href="?delete=<?php echo $transaction['id']; ?>&type=<?php echo $transaction['type']; ?>" class="btn btn-danger">Delete</a>
                                        <button class="btn btn-warning" data-toggle="modal" data-target="#editModal" data-id="<?php echo $transaction['id']; ?>" data-type="<?php echo $transaction['type']; ?>" data-amount="<?php echo $transaction['amount']; ?>" data-category="<?php echo $transaction['category']; ?>" data-date="<?php echo $transaction['date']; ?>" data-description="<?php echo $transaction['description']; ?>">Edit</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Edit Transaction Modal -->
            <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editModalLabel">Edit Transaction</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <form method="POST" action="">
                                <input type="hidden" name="transaction_id" id="transaction_id" value="">
                                <input type="hidden" name="transaction_type" id="transaction_type" value="">
                                <input type="text" id="edit_category" name="category" placeholder="Category" required>
                                <input type="number" step="0.01" id="edit_amount" name="amount" placeholder="Amount" required>
                                <input type="date" id="edit_date" name="date" required>
                                <textarea id="edit_description" name="description" placeholder="Description"></textarea>
                                <button type="submit" name="edit_transaction" class="btn btn-primary">Update Transaction</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <!-- /Page Wrapper -->

    <!-- Footer -->
    <?php include("footer.php"); ?>
    <!-- /Footer -->

    <script>
        $('#editModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget); // Button that triggered the modal
            var id = button.data('id'); // Extract info from data-* attributes
            var type = button.data('type');
            var amount = button.data('amount');
            var category = button.data('category');
            var date = button.data('date');
            var description = button.data('description');

            var modal = $(this);
            modal.find('#transaction_id').val(id);
            modal.find('#transaction_type').val(type);
            modal.find('#edit_amount').val(amount);
            modal.find('#edit_category').val(type === 'income' ? category : category);
            modal.find('#edit_date').val(date);
            modal.find('#edit_description').val(description);
        });
    </script>

</body>
</html>
