<?php
require_once 'core/init.php';

// Check permission
if (!require_permission('process_payout')) {
    header('Location: dashboard.php?error=Access Denied');
    exit();
}

$page_title = "Payouts Management";
$include_datatables = true;

// Handle Form Submission
$success_msg = '';
$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_payout'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error_msg = "Invalid CSRF token. Please try again.";
    } else {
        $payout_amount = floatval($_POST['payout_amount']);
        $user_id = intval($_POST['user_id']);

        if ($payout_amount <= 0) {
            $error_msg = "Payout amount must be greater than zero.";
        } else {
            $db = \App\Core\App::database();
            try {
                if ($db->query("INSERT INTO payouts (user_id, payout_amount, payout_date) VALUES (:user_id, :amount, NOW())", [
                    'user_id' => $user_id,
                    'amount' => $payout_amount
                ])) {
                    $payout_id = $db->getConnection()->insert_id;
                    
                    require_once __DIR__ . '/../includes/notification_manager.php';
                    require_once __DIR__ . '/../includes/email_service.php';
                    
                    $nm = new NotificationManager($db->getConnection(), new EmailService());
                    
                    // Get associate info
                    $associate = $db->fetch("SELECT uname, uemail FROM user WHERE uid = :user_id", ['user_id' => $user_id]);
                    $associate_name = $associate['uname'] ?? "Associate #$user_id";
                    $associate_email = $associate['uemail'] ?? null;

                    // Internal Notification for Admin
                    $nm->send([
                        'user_id' => 1, // Admin
                        'template' => 'PAYOUT_PROCESSED',
                        'data' => [
                            'amount' => $payout_amount,
                            'associate_name' => $associate_name,
                            'payout_id' => $payout_id
                        ],
                        'channels' => ['db']
                    ]);

                    // Notification for Associate
                    if ($associate_email) {
                        $nm->send([
                            'email' => $associate_email,
                            'template' => 'PAYOUT_PROCESSED',
                            'data' => [
                                'amount' => $payout_amount,
                                'associate_name' => $associate_name,
                                'payout_id' => $payout_id
                            ],
                            'channels' => ['email', 'db'],
                            'user_id' => $user_id
                        ]);
                    } else {
                        $nm->send([
                            'user_id' => $user_id,
                            'template' => 'PAYOUT_PROCESSED',
                            'data' => [
                                'amount' => $payout_amount,
                                'associate_name' => $associate_name,
                                'payout_id' => $payout_id
                            ],
                            'channels' => ['db']
                        ]);
                    }

                    $success_msg = "Payout added successfully!";
                } else {
                    $error_msg = "Failed to add payout. Please try again.";
                }
            } catch (Exception $e) {
                $error_msg = "Error: " . $e->getMessage();
            }
        }
    }
}

include 'admin_header.php';
include 'admin_sidebar.php';
?>

<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <h3 class="page-title"><?php echo $page_title; ?></h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Payouts</li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- /Page Header -->

        <?php if ($success_msg): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>Success!</strong> <?php echo $success_msg; ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <?php if ($error_msg): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Error!</strong> <?php echo $error_msg; ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Process New Payout</h4>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <?php echo getCsrfField(); ?>
                            <div class="form-group">
                                <label>Associate / User</label>
                                <select name="user_id" class="form-control select2" required>
                                    <option value="">Select User</option>
                                    <?php
                                    $db = \App\Core\App::database();
                                    $users = $db->fetchAll("SELECT uid as id, uname as name FROM user ORDER BY uname ASC");
                                    foreach ($users as $row) {
                                        echo "<option value='{$row['id']}'>{$row['name']} (ID: {$row['id']})</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Payout Amount</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">₹</span>
                                    </div>
                                    <input type="number" step="0.01" name="payout_amount" class="form-control" placeholder="0.00" required>
                                </div>
                            </div>
                            <div class="text-right">
                                <button type="submit" name="add_payout" class="btn btn-primary">Process Payout</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Payout History</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-center mb-0 datatable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Associate Name</th>
                                        <th>Amount</th>
                                        <th>Date</th>
                                        <th class="text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $payouts = $db->fetchAll("SELECT p.id, u.uname as name, p.payout_amount, p.payout_date 
                                                       FROM payouts p 
                                                       JOIN user u ON p.user_id = u.uid 
                                                       ORDER BY p.payout_date DESC");
                                    foreach ($payouts as $row) {
                                        echo "<tr>
                                                <td>#{$row['id']}</td>
                                                <td>{$row['name']}</td>
                                                <td>₹" . number_format($row['payout_amount'], 2) . "</td>
                                                <td>" . date('d M Y, h:i A', strtotime($row['payout_date'])) . "</td>
                                                <td class='text-right'>
                                                    <div class='actions'>
                                                        <a class='btn btn-sm bg-info-light' href='view_payout.php?id={$row['id']}'>
                                                            <i class='fe fe-eye'></i> View
                                                        </a>
                                                    </div>
                                                </td>
                                              </tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'admin_footer.php'; ?>


