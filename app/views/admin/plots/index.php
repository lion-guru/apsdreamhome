<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title"><?php echo h($mlSupport->translate('Plot Management')); ?></h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/admin/dashboard"><?php echo h($mlSupport->translate('Dashboard')); ?></a></li>
                    <li class="breadcrumb-item active"><?php echo h($mlSupport->translate('Plots')); ?></li>
                </ul>
            </div>
            <div class="col-auto float-end ms-auto">
                <a href="/admin/plots/create" class="btn btn-primary"><i class="fas fa-plus me-2"></i> <?php echo h($mlSupport->translate('Add Plot')); ?></a>
            </div>
        </div>
    </div>
    <!-- /Page Header -->

    <?php if ($flash_success = get_flash('success')): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <?php echo h($mlSupport->translate($flash_success)); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if ($flash_error = get_flash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
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
                                    <th><?php echo h($mlSupport->translate('Plot No.')); ?></th>
                                    <th><?php echo h($mlSupport->translate('Area')); ?></th>
                                    <th><?php echo h($mlSupport->translate('Type')); ?></th>
                                    <th><?php echo h($mlSupport->translate('Status')); ?></th>
                                    <th><?php echo h($mlSupport->translate('Price')); ?></th>
                                    <th><?php echo h($mlSupport->translate('Features')); ?></th>
                                    <th class="text-end"><?php echo h($mlSupport->translate('Action')); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($plots)): ?>
                                    <?php foreach ($plots as $plot): ?>
                                        <tr>
                                            <td>
                                                <h2 class="table-avatar">
                                                    <a href="/admin/plots/edit/<?php echo h($plot['id']); ?>"><?php echo h($plot['plot_number']); ?></a>
                                                </h2>
                                            </td>
                                            <td><?php echo h($plot['plot_area'] . ' ' . $mlSupport->translate($plot['plot_area_unit'])); ?></td>
                                            <td><?php echo h($mlSupport->translate(ucfirst($plot['plot_type']))); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo h($plot['plot_status'] == 'available' ? 'success' : ($plot['plot_status'] == 'booked' ? 'warning' : 'danger')); ?>">
                                                    <?php echo h($mlSupport->translate(ucfirst($plot['plot_status']))); ?>
                                                </span>
                                            </td>
                                            <td><?php echo h($currency_symbol ?? '₹'); ?><?php echo h(number_format($plot['total_price'], 2)); ?></td>
                                            <td>
                                                <?php if ($plot['corner_plot']): ?><span class="badge bg-info me-1"><?php echo h($mlSupport->translate('Corner')); ?></span><?php endif; ?>
                                                <?php if ($plot['park_facing']): ?><span class="badge bg-primary me-1"><?php echo h($mlSupport->translate('Park Facing')); ?></span><?php endif; ?>
                                                <?php if ($plot['road_facing']): ?><span class="badge bg-secondary"><?php echo h($mlSupport->translate('Road Facing')); ?></span><?php endif; ?>
                                            </td>
                                            <td class="text-end">
                                                <div class="dropdown dropdown-action">
                                                    <a href="#" class="action-icon dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"><i class="fas fa-ellipsis-v"></i></a>
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        <a class="dropdown-item" href="/admin/plots/edit/<?php echo h($plot['id']); ?>"><i class="fas fa-pencil-alt m-r-5"></i> <?php echo h($mlSupport->translate('Edit')); ?></a>
                                                        <form action="/admin/plots/delete/<?php echo h($plot['id']); ?>" method="POST" style="display: inline;" onsubmit="return confirm('<?php echo h($mlSupport->translate('Are you sure you want to delete this plot?')); ?>')">
                                                            <?php echo csrf_field(); ?>
                                                            <button type="submit" class="dropdown-item text-danger" style="border: none; background: none; width: 100%; text-align: left;">
                                                                <i class="fas fa-trash-alt m-r-5"></i> <?php echo h($mlSupport->translate('Delete')); ?>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4"><?php echo h($mlSupport->translate('No plots found.')); ?></td>
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
