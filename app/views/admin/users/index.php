<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title"><?php echo h($mlSupport->translate('User Management')); ?></h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/admin/dashboard"><?php echo h($mlSupport->translate('Dashboard')); ?></a></li>
                    <li class="breadcrumb-item active"><?php echo h($mlSupport->translate('Users')); ?></li>
                </ul>
            </div>
            <div class="col-auto float-end ms-auto">
                <a href="/admin/users/create" class="btn btn-primary"><i class="fa fa-plus"></i> <?php echo h($mlSupport->translate('Add User')); ?></a>
            </div>
        </div>
    </div>
    <!-- /Page Header -->

    <?php if ($flash_success = get_flash('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo h($mlSupport->translate($flash_success)); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if ($flash_error = get_flash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo h($mlSupport->translate($flash_error)); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped custom-table mb-0 datatable">
                            <thead>
                                <tr>
                                    <th><?php echo h($mlSupport->translate('ID')); ?></th>
                                    <th><?php echo h($mlSupport->translate('Username')); ?></th>
                                    <th><?php echo h($mlSupport->translate('Email')); ?></th>
                                    <th><?php echo h($mlSupport->translate('Mobile')); ?></th>
                                    <th><?php echo h($mlSupport->translate('Role')); ?></th>
                                    <th><?php echo h($mlSupport->translate('Status')); ?></th>
                                    <th><?php echo h($mlSupport->translate('Created At')); ?></th>
                                    <th class="text-end"><?php echo h($mlSupport->translate('Action')); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($users)): ?>
                                    <?php foreach ($users as $user): ?>
                                        <tr>
                                            <td><?php echo h($user['id']); ?></td>
                                            <td>
                                                <h2 class="table-avatar">
                                                    <a href="/admin/users/edit/<?php echo h($user['id']); ?>"><?php echo h($user['username']); ?></a>
                                                </h2>
                                            </td>
                                            <td><?php echo h($user['email']); ?></td>
                                            <td><?php echo h($user['mobile'] ?? '-'); ?></td>
                                            <td>
                                                <span class="badge bg-info">
                                                    <?php echo h(ucfirst($user['role'])); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo h(($user['status'] ?? 'active') == 'active' ? 'success' : 'danger'); ?>">
                                                    <?php echo h($mlSupport->translate(ucfirst($user['status'] ?? 'active'))); ?>
                                                </span>
                                            </td>
                                            <td><?php echo h(date('d M Y', strtotime($user['created_at']))); ?></td>
                                            <td class="text-end">
                                                <div class="dropdown dropdown-action">
                                                    <a href="#" class="action-icon dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"><i class="fa fa-ellipsis-v"></i></a>
                                                    <div class="dropdown-menu dropdown-menu-right">
                                                        <a class="dropdown-item" href="/admin/users/edit/<?php echo h($user['id']); ?>"><i class="fa fa-pencil m-r-5"></i> <?php echo h($mlSupport->translate('Edit')); ?></a>
                                                        <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                            <form action="/admin/users/delete/<?php echo h($user['id']); ?>" method="POST" style="display: inline;" onsubmit="return confirm('<?php echo h($mlSupport->translate('Are you sure you want to delete this user?')); ?>')">
                                                                <?php echo csrf_field(); ?>
                                                                <button type="submit" class="dropdown-item text-danger" style="border: none; background: none; width: 100%; text-align: left;">
                                                                    <i class="fa fa-trash-o m-r-5"></i> <?php echo h($mlSupport->translate('Delete')); ?>
                                                                </button>
                                                            </form>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-4"><?php echo h($mlSupport->translate('No users found.')); ?></td>
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