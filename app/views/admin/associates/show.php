<?php
if (!isAdmin()) {
    header("Location: /admin/login");
    exit();
}
$title = $title ?? $mlSupport->translate("Associate Details");
require_once ABSPATH . '/resources/views/admin/layouts/header.php';
?>

<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title"><?php echo h($mlSupport->translate('Associate Details')); ?></h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/admin/dashboard"><?php echo h($mlSupport->translate('Dashboard')); ?></a></li>
                        <li class="breadcrumb-item"><a href="/admin/associates"><?php echo h($mlSupport->translate('Associates')); ?></a></li>
                        <li class="breadcrumb-item active"><?php echo h($mlSupport->translate('Details')); ?></li>
                    </ul>
                </div>
                <div class="col-auto float-end ms-auto">
                    <a href="/admin/associates/edit/<?php echo h($associate['id']); ?>" class="btn btn-primary"><i class="fa fa-edit"></i> <?php echo h($mlSupport->translate('Edit Associate')); ?></a>
                    <a href="/admin/associates/tree/<?php echo h($associate['id']); ?>" class="btn btn-info text-white"><i class="fa fa-sitemap"></i> <?php echo h($mlSupport->translate('View Tree')); ?></a>
                </div>
            </div>
        </div>
        <!-- /Page Header -->

        <div class="row">
            <div class="col-md-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body text-center">
                        <div class="profile-img mb-3">
                            <img src="/admin-assets/img/profiles/avatar-01.png" class="rounded-circle" width="100" alt="Profile">
                        </div>
                        <h4 class="mb-1"><?php echo h($associate['user_name']); ?></h4>
                        <p class="text-muted mb-2"><?php echo h($associate['associate_code']); ?></p>
                        <span class="badge bg-<?php echo h($associate['status']) == 'active' ? 'success' : 'danger'; ?> mb-3">
                            <?php echo h($mlSupport->translate(ucfirst($associate['status']))); ?>
                        </span>

                        <div class="text-start mt-4">
                            <div class="mb-2">
                                <label class="text-muted small mb-0"><?php echo h($mlSupport->translate('Email')); ?></label>
                                <p class="mb-0 fw-bold"><?php echo h($associate['user_email']); ?></p>
                            </div>
                            <div class="mb-2">
                                <label class="text-muted small mb-0"><?php echo h($mlSupport->translate('Phone')); ?></label>
                                <p class="mb-0 fw-bold"><?php echo h($associate['user_phone']); ?></p>
                            </div>
                            <div class="mb-2">
                                <label class="text-muted small mb-0"><?php echo h($mlSupport->translate('Sponsor')); ?></label>
                                <p class="mb-0 fw-bold"><?php echo h($associate['sponsor_name'] ?: $mlSupport->translate('Direct')); ?></p>
                            </div>
                            <div class="mb-2">
                                <label class="text-muted small mb-0"><?php echo h($mlSupport->translate('Commission Rate')); ?></label>
                                <p class="mb-0 fw-bold"><?php echo h($associate['commission_rate']); ?>%</p>
                            </div>
                            <div class="mb-2">
                                <label class="text-muted small mb-0"><?php echo h($mlSupport->translate('Joined On')); ?></label>
                                <p class="mb-0 fw-bold"><?php echo h(date('d M Y', strtotime($associate['created_at']))); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <!-- Stats Widgets -->
                <div class="row">
                    <div class="col-md-6 col-lg-3">
                        <div class="card dash-widget shadow-sm border-0">
                            <div class="card-body">
                                <span class="dash-widget-icon bg-primary"><i class="fa fa-users"></i></span>
                                <div class="dash-widget-info">
                                    <h3><?php echo h($stats['team']['total_team_members'] ?? 0); ?></h3>
                                    <span><?php echo h($mlSupport->translate('Team Size')); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="card dash-widget shadow-sm border-0">
                            <div class="card-body">
                                <span class="dash-widget-icon bg-success"><i class="fa fa-shopping-cart"></i></span>
                                <div class="dash-widget-info">
                                    <h3><?php echo h($associate['total_sales'] ?? 0); ?></h3>
                                    <span><?php echo h($mlSupport->translate('Total Sales')); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="card dash-widget shadow-sm border-0">
                            <div class="card-body">
                                <span class="dash-widget-icon bg-warning text-white"><i class="fa fa-money"></i></span>
                                <div class="dash-widget-info">
                                    <h3>₹<?php echo h(number_format($associate['total_earnings'] ?? 0, 2)); ?></h3>
                                    <span><?php echo h($mlSupport->translate('Earnings')); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="card dash-widget shadow-sm border-0">
                            <div class="card-body">
                                <span class="dash-widget-icon bg-info text-white"><i class="fa fa-level-up"></i></span>
                                <div class="dash-widget-info">
                                    <h3><?php echo h($associate['current_level'] ?: 1); ?></h3>
                                    <span><?php echo h($mlSupport->translate('Current Level')); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Team Members Table -->
                <div class="card shadow-sm border-0 mt-3">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0"><?php echo h($mlSupport->translate('Direct Team Members')); ?></h4>
                        <a href="/admin/associates/tree/<?php echo h($associate['id']); ?>" class="btn btn-sm btn-outline-primary"><?php echo h($mlSupport->translate('View Full Genealogy')); ?></a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped custom-table mb-0 datatable">
                                <thead>
                                    <tr>
                                        <th><?php echo h($mlSupport->translate('Name')); ?></th>
                                        <th><?php echo h($mlSupport->translate('Code')); ?></th>
                                        <th><?php echo h($mlSupport->translate('Sales')); ?></th>
                                        <th><?php echo h($mlSupport->translate('Earnings')); ?></th>
                                        <th><?php echo h($mlSupport->translate('Joined')); ?></th>
                                        <th class="text-end"><?php echo h($mlSupport->translate('Action')); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($team)): ?>
                                        <?php foreach ($team as $member): ?>
                                            <tr>
                                                <td>
                                                    <h2 class="table-avatar">
                                                        <a href="/admin/associates/show/<?php echo h($member['id']); ?>"><?php echo h($member['name']); ?> <span><?php echo h($member['email']); ?></span></a>
                                                    </h2>
                                                </td>
                                                <td><?php echo h($member['associate_code']); ?></td>
                                                <td><?php echo h($member['total_sales']); ?></td>
                                                <td>₹<?php echo h(number_format($member['total_earnings'], 2)); ?></td>
                                                <td><?php echo h(date('d M Y', strtotime($member['created_at']))); ?></td>
                                                <td class="text-end">
                                                    <a href="/admin/associates/show/<?php echo h($member['id']); ?>" class="btn btn-sm btn-outline-primary"><?php echo h($mlSupport->translate('View Details')); ?></a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-4"><?php echo h($mlSupport->translate('No direct team members found.')); ?></td>
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

<?php require_once ABSPATH . '/resources/views/admin/layouts/footer.php'; ?>
