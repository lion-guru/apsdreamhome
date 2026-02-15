<?php
/**
 * Customer Management Page
 * Displays and manages customer records
 */

require_once __DIR__ . '/core/init.php';

require_permission('manage_customers');
$db = \App\Core\App::database();
$customers = $db->fetchAll("SELECT * FROM customers ORDER BY id DESC");

$page_title = $mlSupport->translate("Customer Management");
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
                    <h3 class="page-title"><?php echo h($mlSupport->translate('Customer Management')); ?></h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php"><?php echo h($mlSupport->translate('Dashboard')); ?></a></li>
                        <li class="breadcrumb-item active"><?php echo h($mlSupport->translate('Customers')); ?></li>
                    </ul>
                </div>
                <div class="col-auto float-right ml-auto">
                    <a href="add_customer.php" class="btn btn-primary"><i class="fa fa-plus"></i> <?php echo h($mlSupport->translate('Add Customer')); ?></a>
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
                                        <th><?php echo h($mlSupport->translate('Name')); ?></th>
                                        <th><?php echo h($mlSupport->translate('Email')); ?></th>
                                        <th><?php echo h($mlSupport->translate('Phone')); ?></th>
                                        <th><?php echo h($mlSupport->translate('City')); ?></th>
                                        <th><?php echo h($mlSupport->translate('Status')); ?></th>
                                        <th class="text-right"><?php echo h($mlSupport->translate('Actions')); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($customers)): ?>
                                        <?php foreach ($customers as $customer): ?>
                                        <tr>
                                            <td><?php echo h($customer['id']); ?></td>
                                            <td><?php echo h($customer['name']); ?></td>
                                            <td><?php echo h($customer['email']); ?></td>
                                            <td><?php echo h($customer['phone']); ?></td>
                                            <td><?php echo h($customer['city'] ?? ''); ?></td>
                                            <td>
                                                <span class="badge badge-<?php echo h(($customer['status'] ?? 'active') == 'active' ? 'success' : 'danger'); ?>">
                                                    <?php echo h($mlSupport->translate(ucfirst($customer['status'] ?? 'active'))); ?>
                                                </span>
                                            </td>
                                            <td class="text-right">
                                                <div class="dropdown dropdown-action">
                                                    <a href="#" class="action-icon dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><i class="fa fa-ellipsis-v"></i></a>
                                                    <div class="dropdown-menu dropdown-menu-right">
                                                        <a class="dropdown-item" href="edit_customer.php?id=<?php echo h($customer['id']); ?>"><i class="fa fa-pencil m-r-5"></i> <?php echo h($mlSupport->translate('Edit')); ?></a>
                                                        <a class="dropdown-item" href="#" data-toggle="modal" data-target="#delete_customer_<?php echo h($customer['id']); ?>"><i class="fa fa-trash-o m-r-5"></i> <?php echo h($mlSupport->translate('Delete')); ?></a>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>

                                        <!-- Delete Customer Modal -->
                                        <div id="delete_customer_<?php echo h($customer['id']); ?>" class="modal custom-modal fade" role="dialog">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-body">
                                                        <div class="form-header">
                                                            <h3><?php echo h($mlSupport->translate('Delete Customer')); ?></h3>
                                                            <p><?php echo h($mlSupport->translate('Are you sure you want to delete')); ?> <strong><?php echo h($customer['name']); ?></strong>?</p>
                                                        </div>
                                                        <div class="modal-btn delete-action">
                                                            <div class="row">
                                                                <div class="col-6">
                                                                    <form method="post" action="delete.php">
                                                                        <?php echo getCsrfField(); ?>
                                                                        <input type="hidden" name="type" value="customer">
                                                                        <input type="hidden" name="id" value="<?php echo h($customer['id']); ?>">
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
                                        <!-- /Delete Customer Modal -->
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center"><?php echo h($mlSupport->translate('No customers found.')); ?></td>
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
require_once __DIR__ . '/../includes/notification_manager.php';
require_once __DIR__ . '/../includes/email_service.php';
$nm = new NotificationManager(null, new EmailService());
$nm->send([
    'user_id' => 1, // Admin
    'type' => 'info',
    'title' => $mlSupport->translate('Customer Management Access'),
    'message' => $mlSupport->translate('Customer management page was viewed by admin') . ' ' . h($_SESSION['auser'] ?? 'Unknown'),
    'channels' => ['db']
]);

include 'admin_footer.php';
?>
