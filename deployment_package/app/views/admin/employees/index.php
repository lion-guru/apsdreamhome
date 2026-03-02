<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title"><?php echo h($mlSupport->translate('Employees Management')); ?></h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/admin/dashboard"><?php echo h($mlSupport->translate('Dashboard')); ?></a></li>
                    <li class="breadcrumb-item active"><?php echo h($mlSupport->translate('Employees')); ?></li>
                </ul>
            </div>
            <div class="col-auto float-end ms-auto">
                <a href="/admin/employees/create" class="btn btn-primary"><i class="fa fa-plus"></i> <?php echo h($mlSupport->translate('Add Employee')); ?></a>
            </div>
        </div>
    </div>

    <?php if ($flash_success = get_flash('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo h($flash_success); ?>
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
                                    <th><?php echo h($mlSupport->translate('Name')); ?></th>
                                    <th><?php echo h($mlSupport->translate('Email')); ?></th>
                                    <th><?php echo h($mlSupport->translate('Phone')); ?></th>
                                    <th><?php echo h($mlSupport->translate('Department')); ?></th>
                                    <th><?php echo h($mlSupport->translate('Role')); ?></th>
                                    <th><?php echo h($mlSupport->translate('Join Date')); ?></th>
                                    <th><?php echo h($mlSupport->translate('Status')); ?></th>
                                    <th class="text-end"><?php echo h($mlSupport->translate('Action')); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($employees as $emp): ?>
                                    <tr>
                                        <td><?php echo h($emp['name']); ?></td>
                                        <td><?php echo h($emp['email']); ?></td>
                                        <td><?php echo h($emp['phone']); ?></td>
                                        <td><?php echo h($emp['department_name'] ?? $emp['department'] ?? 'N/A'); ?></td>
                                        <td><span class="badge bg-inverse-info"><?php echo h($mlSupport->translate(ucfirst($emp['role_name'] ?? $emp['role'] ?? 'N/A'))); ?></span></td>
                                        <td><?php echo h(date('d M Y', strtotime($emp['join_date'] ?? $emp['created_at']))); ?></td>
                                        <td>
                                            <span class="badge bg-inverse-<?php echo ($emp['status'] ?? 'active') == 'active' ? 'success' : 'danger'; ?>">
                                                <?php echo h($mlSupport->translate(ucfirst($emp['status']))); ?>
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <div class="dropdown dropdown-action">
                                                <a href="#" class="action-icon dropdown-toggle" data-bs-toggle="dropdown"><i class="fa fa-ellipsis-v"></i></a>
                                                <div class="dropdown-menu dropdown-menu-right">
                                                    <a class="dropdown-item" href="/admin/employees/edit/<?php echo h($emp['id']); ?>"><i class="fa fa-pencil m-r-5"></i> <?php echo h($mlSupport->translate('Edit')); ?></a>
                                                    <?php if ($emp['status'] == 'active'): ?>
                                                        <form action="/admin/employees/offboard/<?php echo h($emp['id']); ?>" method="POST" style="display: inline;" onsubmit="return confirm('<?php echo h($mlSupport->translate('Offboard this employee? This will revoke all their roles.')); ?>')">
                                                            <?php echo csrf_field(); ?>
                                                            <button type="submit" class="dropdown-item" style="border: none; background: none; width: 100%; text-align: left;">
                                                                <i class="fa fa-user-slash m-r-5"></i> <?php echo h($mlSupport->translate('Offboard')); ?>
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>
                                                    <form action="/admin/employees/delete/<?php echo h($emp['id']); ?>" method="POST" style="display: inline;" onsubmit="return confirm('<?php echo h($mlSupport->translate('Are you sure you want to delete this employee?')); ?>')">
                                                        <?php echo csrf_field(); ?>
                                                        <button type="submit" class="dropdown-item text-danger" style="border: none; background: none; width: 100%; text-align: left;">
                                                            <i class="fa fa-trash-o m-r-5"></i> <?php echo h($mlSupport->translate('Delete')); ?>
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

