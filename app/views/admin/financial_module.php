<?php
require_once __DIR__ . '/core/init.php';

// Check if user is logged in and has required privileges
adminAccessControl(['finance', 'superadmin']);

$db = \App\Core\App::database();

// Function to add income
function addIncome($user_id, $amount, $source, $date, $description) {
    $db = \App\Core\App::database();
    $db->insert('income', [
        'user_id' => $user_id,
        'amount' => $amount,
        'source' => $source,
        'date' => $date,
        'description' => $description
    ]);
    log_admin_activity('add_income', 'User ID: ' . $user_id . ', amount: ' . $amount . ', source: ' . $source . ', date: ' . $date . ', description: ' . $description);
}

// Function to add expense
function addExpense($user_id, $amount, $category, $date, $description) {
    $db = \App\Core\App::database();
    $db->insert('expenses', [
        'user_id' => $user_id,
        'amount' => $amount,
        'category' => $category,
        'date' => $date,
        'description' => $description
    ]);
    log_admin_activity('add_expense', 'User ID: ' . $user_id . ', amount: ' . $amount . ', category: ' . $category . ', date: ' . $date . ', description: ' . $description);
}

// Function to fetch all transactions
function getTransactions($user_id) {
    $db = \App\Core\App::database();
    $query = "SELECT * FROM (SELECT id, amount, source AS category, date, description, 'income' AS type FROM income WHERE user_id = ? UNION ALL SELECT id, amount, category, date, description, 'expense' AS type FROM expenses WHERE user_id = ?) AS transactions ORDER BY date DESC";
    return $db->fetchAll($query, [$user_id, $user_id]);
}

// Function to delete a transaction
function deleteTransaction($id, $type) {
    $db = \App\Core\App::database();
    $table = ($type === 'income') ? 'income' : 'expenses';
    $db->execute("DELETE FROM $table WHERE id = ?", [$id]);
    log_admin_activity('delete_transaction', 'Transaction ID: ' . $id . ', type: ' . $type);
}

// Function to fetch a transaction for editing
function getTransaction($id, $type) {
    $db = \App\Core\App::database();
    $table = ($type === 'income') ? 'income' : 'expenses';
    return $db->fetch("SELECT * FROM $table WHERE id = ?", [$id]);
}

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // CSRF Token Validation
    if (!validateCsrfToken()) {
        die('Invalid CSRF token. Action blocked.');
    }

    $user_id = getAuthUserId(); // Use unified function to get user ID

    // Validate input
    $amount = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_FLOAT);
    $date = filter_input(INPUT_POST, 'date', FILTER_SANITIZE_SPECIAL_CHARS);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_SPECIAL_CHARS);

    if (isset($_POST['add_income']) && $amount !== false && $amount > 0) {
        $source = filter_input(INPUT_POST, 'source', FILTER_SANITIZE_SPECIAL_CHARS);
        addIncome($user_id, $amount, $source, $date, $description);
    }

    if (isset($_POST['add_expense']) && $amount !== false && $amount > 0) {
        $category = filter_input(INPUT_POST, 'category', FILTER_SANITIZE_SPECIAL_CHARS);
        addExpense($user_id, $amount, $category, $date, $description);
    }

    // Edit transaction
    if (isset($_POST['edit_transaction'])) {
        $transaction_id = filter_input(INPUT_POST, 'transaction_id', FILTER_VALIDATE_INT);
        $transaction_type = filter_input(INPUT_POST, 'transaction_type', FILTER_SANITIZE_SPECIAL_CHARS);
        $amount_val = $_POST['amount'];
        $date_val = $date;
        $desc_val = $description;

        if ($transaction_type === 'income') {
            $db->execute("UPDATE income SET amount = ?, source = ?, date = ?, description = ? WHERE id = ?", [$amount_val, $_POST['source'], $date_val, $desc_val, $transaction_id]);
        } else {
            $db->execute("UPDATE expenses SET amount = ?, category = ?, date = ?, description = ? WHERE id = ?", [$amount_val, $_POST['category'], $date_val, $desc_val, $transaction_id]);
        }
        log_admin_activity('edit_transaction', 'Transaction ID: ' . $transaction_id . ', type: ' . $transaction_type . ', amount: ' . $amount_val . ', category/source: ' . ($_POST['category'] ?? $_POST['source']) . ', date: ' . $date_val . ', description: ' . $desc_val);
    }

    // Delete transaction
    if (isset($_POST['delete_transaction'])) {
        $delete_id = filter_input(INPUT_POST, 'delete_id', FILTER_VALIDATE_INT);
        $delete_type = filter_input(INPUT_POST, 'delete_type', FILTER_SANITIZE_SPECIAL_CHARS);
        if ($delete_id && $delete_type) {
            deleteTransaction($delete_id, $delete_type);
            $success_message = "Transaction deleted successfully.";
        } else {
            $error_message = "Invalid transaction deletion request.";
        }
    }
}

// Fetch totals
function getTotalIncome($user_id) {
    $db = \App\Core\App::database();
    $row = $db->fetch("SELECT SUM(amount) as total FROM income WHERE user_id = ?", [$user_id]);
    return $row['total'] ?? 0;
}

function getTotalExpenses($user_id) {
    $db = \App\Core\App::database();
    $row = $db->fetch("SELECT SUM(amount) as total FROM expenses WHERE user_id = ?", [$user_id]);
    return $row['total'] ?? 0;
}

$user_id = getAuthUserId();
$total_income = getTotalIncome($user_id);
$total_expenses = getTotalExpenses($user_id);
$transactions = getTransactions($user_id);
$error_message = "";
$success_message = "";

// Check for user roles
$is_admin = isAdmin() && hasSubRole(['superadmin']);
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

            <?php if ($success_message): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo h($success_message); ?>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>

            <?php if ($error_message): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo h($error_message); ?>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-xl-6 col-sm-12">
                    <h4>Total Income: <?php echo h(number_format($total_income, 2)); ?></h4>
                    <h4>Total Expenses: <?php echo h(number_format($total_expenses, 2)); ?></h4>
                    <h4>Net Balance: <?php echo h(number_format($total_income - $total_expenses, 2)); ?></h4>
                </div>
            </div>

            <!-- Income Form -->
            <div class="row">
                <div class="col-xl-6 col-sm-12">
                    <h5>Add Income</h5>
                    <form method="POST" action="">
                        <?php echo getCsrfField(); ?>
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
                        <?php echo getCsrfField(); ?>
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
                                    <td><?php echo h($transaction['date']); ?></td>
                                    <td><?php echo h($transaction['category']); ?></td>
                                    <td><?php echo h(number_format($transaction['amount'], 2)); ?></td>
                                    <td><?php echo h($transaction['description']); ?></td>
                                    <td>
                                        <form action="" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this transaction?');">
                                            <?php echo getCsrfField(); ?>
                                            <input type="hidden" name="delete_id" value="<?php echo h($transaction['id']); ?>">
                                            <input type="hidden" name="delete_type" value="<?php echo h($transaction['type']); ?>">
                                            <button type="submit" name="delete_transaction" class="btn btn-sm btn-danger">Delete</button>
                                        </form>
                                        <button class="btn btn-sm btn-warning" data-toggle="modal" data-target="#editModal" data-id="<?php echo h($transaction['id']); ?>" data-type="<?php echo h($transaction['type']); ?>" data-amount="<?php echo h($transaction['amount']); ?>" data-category="<?php echo h($transaction['category']); ?>" data-date="<?php echo h($transaction['date']); ?>" data-description="<?php echo h($transaction['description']); ?>">Edit</button>
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
                                <?php echo getCsrfField(); ?>
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
