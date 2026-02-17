<?php
/**
 * Transactions Management Page
 * Displays and manages financial transactions
 */

require_once __DIR__ . '/core/init.php';

$db = \App\Core\App::database();

// Check admin authentication
if (!isAdmin()) {
    header('Location: login.php');
    exit();
}

$transactions = $db->fetchAll("SELECT * FROM transactions ORDER BY id DESC");

$page_title = h($mlSupport->translate("Transactions"));
$include_datatables = true;
include 'admin_header.php';
include 'admin_sidebar.php';
?>

<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title"><?php echo h($mlSupport->translate('Transactions')); ?></h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php"><?php echo h($mlSupport->translate('Dashboard')); ?></a></li>
                        <li class="breadcrumb-item active"><?php echo h($mlSupport->translate('Transactions')); ?></li>
                    </ul>
                </div>
                <div class="col-auto float-right ml-auto">
                    <a href="add_transaction.php" class="btn btn-primary"><i class="fa fa-plus"></i> <?php echo h($mlSupport->translate('Add Transaction')); ?></a>
                </div>
            </div>
        </div>
        <!-- /Page Header -->

        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="datatable table table-stripped">
                                <thead>
                                    <tr>
                                        <th><?php echo h($mlSupport->translate('ID')); ?></th>
                                        <th><?php echo h($mlSupport->translate('Date')); ?></th>
                                        <th><?php echo h($mlSupport->translate('Type')); ?></th>
                                        <th><?php echo h($mlSupport->translate('Amount')); ?></th>
                                        <th><?php echo h($mlSupport->translate('Description')); ?></th>
                                        <th><?php echo h($mlSupport->translate('Status')); ?></th>
                                        <th class="text-right"><?php echo h($mlSupport->translate('Actions')); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($transactions)): ?>
                                        <?php foreach ($transactions as $transaction): ?>
                                        <tr>
                                            <td><?= (int)$transaction['id'] ?></td>
                                            <td><?= h($transaction['date'] ?? $transaction['transaction_date'] ?? '') ?></td>
                                            <td>
                                                <span class="badge badge-<?= ($transaction['type'] == 'income') ? 'success' : 'danger' ?>">
                                                    <?= h($mlSupport->translate(ucfirst(h($transaction['type'])))) ?>
                                                </span>
                                            </td>
                                            <td>₹<?= number_format($transaction['amount'], 2) ?></td>
                                            <td><?= h($transaction['description'] ?? '') ?></td>
                                            <td>
                                                <span class="badge badge-<?= ($transaction['status'] == 'completed') ? 'success' : (($transaction['status'] == 'pending') ? 'warning' : 'danger') ?>">
                                                    <?= h($mlSupport->translate(ucfirst(h($transaction['status'])))) ?>
                                                </span>
                                            </td>
                                            <td class="text-right">
                                                <div class="dropdown dropdown-action">
                                                    <a href="#" class="action-icon dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><i class="fa fa-ellipsis-v"></i></a>
                                                    <div class="dropdown-menu dropdown-menu-right">
                                                        <a class="dropdown-item" href="edit_transaction.php?id=<?= h($transaction['id']) ?>"><i class="fa fa-pencil m-r-5"></i> <?php echo h($mlSupport->translate('Edit')); ?></a>
                                                        <a class="dropdown-item" href="#" data-toggle="modal" data-target="#delete_transaction_<?= h($transaction['id']) ?>"><i class="fa fa-trash-o m-r-5"></i> <?php echo h($mlSupport->translate('Delete')); ?></a>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>

                                        <!-- Delete Transaction Modal -->
                                        <div id="delete_transaction_<?= (int)$transaction['id'] ?>" class="modal custom-modal fade" role="dialog">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-body">
                                                        <div class="form-header">
                                                            <h3><?php echo h($mlSupport->translate('Delete Transaction')); ?></h3>
                                                            <p><?php echo h($mlSupport->translate('Are you sure you want to delete transaction ID:')); ?> <strong><?= (int)$transaction['id'] ?></strong>?</p>
                                                        </div>
                                                        <div class="modal-btn delete-action">
                                                            <div class="row">
                                                                <div class="col-6">
                                                                    <form method="post" action="delete.php">
                                                                        <?= getCsrfField() ?>
                                                                        <input type="hidden" name="type" value="transaction">
                                                                        <input type="hidden" name="id" value="<?= (int)$transaction['id'] ?>">
                                                                        <button type="submit" class="btn btn-primary continue-btn w-100"><?php echo h($mlSupport->translate('Delete')); ?></button>
                                                                    </form>
                                                                </div>
                                                                <div class="col-6">
                                                                    <a href="javascript:void(0);" data-dismiss="modal" class="btn btn-primary cancel-btn"><?php echo h($mlSupport->translate('Cancel')); ?></a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- /Delete Transaction Modal -->
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center"><?php echo h($mlSupport->translate('No transactions found.')); ?></td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include 'admin_footer.php';
?>
