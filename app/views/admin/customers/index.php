<?php
if (!isAdmin()) {
    header("Location: /admin/login");
    exit();
}
$page_title = $page_title ?? $mlSupport->translate("Customer Management");
require_once ABSPATH . '/resources/views/admin/layouts/header.php';
?>

<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title"><?php echo h($page_title); ?></h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/admin/dashboard"><?php echo h($mlSupport->translate('Dashboard')); ?></a></li>
                        <li class="breadcrumb-item active"><?php echo h($mlSupport->translate('Customers')); ?></li>
                    </ul>
                </div>
                <div class="col-auto float-right ml-auto">
                    <a href="/admin/customers/create" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i><?php echo h($mlSupport->translate('Add Customer')); ?>
                    </a>
                </div>
            </div>
        </div>
        <!-- /Page Header -->

        <div class="row">
            <div class="col-md-12">
                <?php if($flash_success = get_flash('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <?php echo h($flash_success); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                <?php if($flash_error = get_flash('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <?php echo h($flash_error); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <!-- Search and Filter -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" action="/admin/customers" class="row g-3">
                            <div class="col-md-8">
                                <div class="input-group">
                                    <input type="text" class="form-control" name="search"
                                        placeholder="<?php echo h($mlSupport->translate('Search customers by name, email, or phone...')); ?>"
                                        value="<?php echo h($searchTerm ?? ''); ?>">
                                    <button class="btn btn-outline-primary" type="submit">
                                        <i class="fas fa-search"></i> <?php echo h($mlSupport->translate('Search')); ?>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <a href="/admin/customers" class="btn btn-outline-secondary w-100">
                                    <i class="fas fa-eraser me-2"></i><?php echo h($mlSupport->translate('Clear Filters')); ?>
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Customers Table -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-center mb-0" id="customersTable">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th><?php echo h($mlSupport->translate('Customer')); ?></th>
                                        <th><?php echo h($mlSupport->translate('Email')); ?></th>
                                        <th><?php echo h($mlSupport->translate('Phone')); ?></th>
                                        <th><?php echo h($mlSupport->translate('Status')); ?></th>
                                        <th class="text-end"><?php echo h($mlSupport->translate('Actions')); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($customers)): ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-4">
                                                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                                <h5 class="text-muted"><?php echo h($mlSupport->translate('No Customers Found')); ?></h5>
                                                <p class="text-muted">
                                                    <?php if ($searchTerm): ?>
                                                        <?php echo h($mlSupport->translate('No customers match your search criteria.')); ?>
                                                    <?php else: ?>
                                                        <?php echo h($mlSupport->translate('No customers have been registered yet.')); ?>
                                                    <?php endif; ?>
                                                </p>
                                                <a href="/admin/customers/create" class="btn btn-primary">
                                                    <i class="fas fa-plus me-2"></i><?php echo h($mlSupport->translate('Add First Customer')); ?>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($customers as $index => $customer): ?>
                                            <tr>
                                                <td><?php echo h($index + 1); ?></td>
                                                <td>
                                                    <h2 class="table-avatar">
                                                        <a href="/admin/customers/show/<?php echo h($customer['id']); ?>" class="avatar avatar-sm me-2">
                                                            <img class="avatar-img rounded-circle" src="/assets/img/profiles/<?php echo h($customer['profile_image'] ?? 'default-avatar.jpg'); ?>" alt="Customer Image" onerror="this.src='/assets/img/profiles/default-avatar.jpg'">
                                                        </a>
                                                        <a href="/admin/customers/show/<?php echo h($customer['id']); ?>">
                                                            <?php echo h($customer['name'] ?? 'N/A'); ?>
                                                        </a>
                                                    </h2>
                                                </td>
                                                <td>
                                                    <a href="mailto:<?php echo h($customer['email'] ?? ''); ?>">
                                                        <?php echo h($customer['email'] ?? 'N/A'); ?>
                                                    </a>
                                                </td>
                                                <td>
                                                    <?php if (!empty($customer['phone'])): ?>
                                                        <a href="tel:<?php echo h($customer['phone']); ?>">
                                                            <?php echo h($customer['phone']); ?>
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="text-muted"><?php echo h($mlSupport->translate('Not provided')); ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?php echo h($customer['status'] == 'active' ? 'success' : 'danger'); ?>">
                                                        <?php echo h($mlSupport->translate(ucwords($customer['status']))); ?>
                                                    </span>
                                                </td>
                                                <td class="text-end">
                                                    <div class="dropdown dropdown-action">
                                                        <a href="#" class="action-icon dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"><i class="fas fa-ellipsis-v"></i></a>
                                                        <div class="dropdown-menu dropdown-menu-end">
                                                            <a class="dropdown-item" href="/admin/customers/show/<?php echo h($customer['id']); ?>"><i class="fas fa-eye me-2"></i> <?php echo h($mlSupport->translate('View Profile')); ?></a>
                                                            <a class="dropdown-item" href="/admin/customers/edit/<?php echo h($customer['id']); ?>"><i class="fas fa-pencil-alt me-2"></i> <?php echo h($mlSupport->translate('Edit')); ?></a>
                                                            <a class="dropdown-item" href="javascript:void(0);" onclick="deleteCustomer(<?php echo h($customer['id']); ?>, '<?php echo h(addslashes($customer['name'])); ?>')"><i class="fas fa-trash me-2"></i> <?php echo h($mlSupport->translate('Delete')); ?></a>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
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

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel"><?php echo h($mlSupport->translate('Delete Customer')); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="deleteModalBody"></p>
                <form id="deleteForm" method="POST" action="">
                    <?php echo csrf_field(); ?>
                    <div class="modal-footer px-0 pb-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo h($mlSupport->translate('Cancel')); ?></button>
                        <button type="submit" class="btn btn-danger"><?php echo h($mlSupport->translate('Delete Permanently')); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function deleteCustomer(id, name) {
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    document.getElementById('deleteModalBody').innerHTML = '<?php echo h($mlSupport->translate('Are you sure you want to delete customer')); ?> <strong>' + name + '</strong>? <?php echo h($mlSupport->translate('This action cannot be undone.')); ?>';
    document.getElementById('deleteForm').action = '/admin/customers/delete/' + id;
    modal.show();
}
</script>

<?php require_once ABSPATH . '/resources/views/admin/layouts/footer.php'; ?>
