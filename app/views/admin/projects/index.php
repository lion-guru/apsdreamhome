<?php include BASE_PATH . '/resources/views/admin/layouts/header.php'; ?>

<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title"><?php echo h($mlSupport->translate('Project Management')); ?></h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/admin/dashboard"><?php echo h($mlSupport->translate('Dashboard')); ?></a></li>
                        <li class="breadcrumb-item active"><?php echo h($mlSupport->translate('Projects')); ?></li>
                    </ul>
                </div>
                <div class="col-auto float-end ms-auto">
                    <a href="/admin/projects/create" class="btn btn-primary"><i class="fa fa-plus"></i> <?php echo h($mlSupport->translate('Add Project')); ?></a>
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
                                        <th><?php echo h($mlSupport->translate('Code')); ?></th>
                                        <th><?php echo h($mlSupport->translate('Project Name')); ?></th>
                                        <th><?php echo h($mlSupport->translate('Location')); ?></th>
                                        <th><?php echo h($mlSupport->translate('Type')); ?></th>
                                        <th><?php echo h($mlSupport->translate('Units')); ?></th>
                                        <th><?php echo h($mlSupport->translate('Status')); ?></th>
                                        <th><?php echo h($mlSupport->translate('Featured')); ?></th>
                                        <th class="text-end"><?php echo h($mlSupport->translate('Action')); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($projects)): ?>
                                        <?php foreach ($projects as $project): ?>
                                            <tr>
                                                <td><?php echo h($project['project_code']); ?></td>
                                                <td>
                                                    <h2 class="table-avatar">
                                                        <a href="/admin/projects/edit/<?php echo h($project['project_id']); ?>"><?php echo h($project['project_name']); ?></a>
                                                    </h2>
                                                </td>
                                                <td><?php echo h($project['location'] . ', ' . $project['city']); ?></td>
                                                <td><?php echo h($mlSupport->translate($project['project_type'])); ?></td>
                                                <td><?php echo h($project['available_plots'] . ' / ' . $project['total_plots']); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo h($project['is_active'] ? 'success' : 'danger'); ?>">
                                                        <?php echo h($mlSupport->translate($project['is_active'] ? 'Active' : 'Inactive')); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($project['is_featured']): ?>
                                                        <span class="badge bg-warning text-white"><?php echo h($mlSupport->translate('Featured')); ?></span>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-end">
                                                    <div class="dropdown dropdown-action">
                                                        <a href="#" class="action-icon dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"><i class="fa fa-ellipsis-v"></i></a>
                                                        <div class="dropdown-menu dropdown-menu-right">
                                                            <a class="dropdown-item" href="/admin/projects/edit/<?php echo h($project['project_id']); ?>"><i class="fa fa-pencil m-r-5"></i> <?php echo h($mlSupport->translate('Edit')); ?></a>
                                                            <form action="/admin/projects/delete/<?php echo h($project['project_id']); ?>" method="POST" style="display: inline;" onsubmit="return confirm('<?php echo h($mlSupport->translate('Are you sure you want to delete this project?')); ?>')">
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
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8" class="text-center py-4"><?php echo h($mlSupport->translate('No projects found.')); ?></td>
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

<?php include BASE_PATH . '/resources/views/admin/layouts/footer.php'; ?>
