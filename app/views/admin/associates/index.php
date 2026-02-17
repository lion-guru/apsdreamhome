<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title"><?php echo h($mlSupport->translate('Associates Management')); ?></h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/admin/dashboard"><?php echo h($mlSupport->translate('Dashboard')); ?></a></li>
                    <li class="breadcrumb-item active"><?php echo h($mlSupport->translate('Associates')); ?></li>
                </ul>
            </div>
            <div class="col-auto float-end ms-auto">
                <a href="/admin/associates/create" class="btn btn-primary"><i class="fa fa-plus"></i> <?php echo h($mlSupport->translate('Add Associate')); ?></a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped custom-table mb-0 datatable">
                            <thead>
                                <tr>
                                    <th><?php echo h($mlSupport->translate('Associate Code')); ?></th>
                                    <th><?php echo h($mlSupport->translate('Name')); ?></th>
                                    <th><?php echo h($mlSupport->translate('Email')); ?></th>
                                    <th><?php echo h($mlSupport->translate('Phone')); ?></th>
                                    <th><?php echo h($mlSupport->translate('Sponsor')); ?></th>
                                    <th><?php echo h($mlSupport->translate('Downline')); ?></th>
                                    <th><?php echo h($mlSupport->translate('Comm. Rate')); ?></th>
                                    <th><?php echo h($mlSupport->translate('Status')); ?></th>
                                    <th class="text-end"><?php echo h($mlSupport->translate('Action')); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($associates as $assoc): ?>
                                    <tr>
                                        <td><?php echo h($assoc['associate_code']); ?></td>
                                        <td><?php echo h($assoc['name']); ?></td>
                                        <td><?php echo h($assoc['email']); ?></td>
                                        <td><?php echo h($assoc['phone']); ?></td>
                                        <td><?php echo h($assoc['sponsor_name'] ?: $mlSupport->translate('None')); ?></td>
                                        <td><span class="badge bg-info"><?php echo h($assoc['downline_count']); ?></span></td>
                                        <td><?php echo h($assoc['commission_rate']); ?>%</td>
                                        <td>
                                            <span class="badge bg-<?php echo $assoc['status'] == 'active' ? 'success' : 'danger'; ?>">
                                                <?php echo h($mlSupport->translate(ucfirst($assoc['status']))); ?>
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <div class="dropdown dropdown-action">
                                                <a href="#" class="action-icon dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"><i class="fas fa-ellipsis-v"></i></a>
                                                <div class="dropdown-menu dropdown-menu-end">
                                                    <a class="dropdown-item" href="/admin/associates/show/<?php echo h($assoc['id']); ?>"><i class="fa fa-eye m-r-5"></i> <?php echo h($mlSupport->translate('View Details')); ?></a>
                                                    <a class="dropdown-item" href="/admin/associates/tree/<?php echo h($assoc['id']); ?>"><i class="fa fa-sitemap m-r-5"></i> <?php echo h($mlSupport->translate('Genealogy Tree')); ?></a>
                                                    <a class="dropdown-item" href="/admin/associates/commissions/<?php echo h($assoc['id']); ?>"><i class="fa fa-money m-r-5"></i> <?php echo h($mlSupport->translate('Commissions')); ?></a>
                                                    <div class="dropdown-divider"></div>
                                                    <a class="dropdown-item" href="/admin/associates/edit/<?php echo h($assoc['id']); ?>"><i class="fa fa-pencil m-r-5"></i> <?php echo h($mlSupport->translate('Edit')); ?></a>
                                                    <form action="/admin/associates/delete/<?php echo h($assoc['id']); ?>" method="POST" style="display: inline;" onsubmit="return confirm('<?php echo h($mlSupport->translate('Are you sure you want to delete this associate?')); ?>')">
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