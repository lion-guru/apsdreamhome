<?php
require_once __DIR__ . '/../core/init.php';

include 'admin_header.php';
include 'admin_sidebar.php';
?>

<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title"><?php echo h($mlSupport->translate('Property Details')); ?></h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/admin/dashboard"><?php echo h($mlSupport->translate('Dashboard')); ?></a></li>
                        <li class="breadcrumb-item"><a href="/admin/properties"><?php echo h($mlSupport->translate('Properties')); ?></a></li>
                        <li class="breadcrumb-item active"><?php echo h($mlSupport->translate('View Property')); ?></li>
                    </ul>
                </div>
                <div class="col-auto float-end ms-auto">
                    <a href="/admin/properties/edit/<?php echo h($property['id']); ?>" class="btn btn-primary"><i class="fa fa-pencil"></i> <?php echo h($mlSupport->translate('Edit Property')); ?></a>
                </div>
            </div>
        </div>
        <!-- /Page Header -->

        <div class="row">
            <div class="col-md-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <h4 class="card-title text-primary mb-3"><?php echo h($mlSupport->translate('Basic Information')); ?></h4>
                                <table class="table table-bordered">
                                    <tr>
                                        <th width="30%"><?php echo h($mlSupport->translate('Property Title')); ?></th>
                                        <td><?php echo h($property['title']); ?></td>
                                    </tr>
                                    <tr>
                                        <th><?php echo h($mlSupport->translate('Category')); ?></th>
                                        <td><?php echo h($mlSupport->translate(ucfirst($property['type']))); ?></td>
                                    </tr>
                                    <tr>
                                        <th><?php echo h($mlSupport->translate('Type')); ?></th>
                                        <td><?php echo h($property['property_type_name'] ?: '-'); ?></td>
                                    </tr>
                                    <tr>
                                        <th><?php echo h($mlSupport->translate('Price')); ?></th>
                                        <td>₹<?php echo h(number_format($property['price'], 2)); ?></td>
                                    </tr>
                                    <tr>
                                        <th><?php echo h($mlSupport->translate('Status')); ?></th>
                                        <td>
                                            <span class="badge bg-<?php echo h($property['status'] == 'available' ? 'success' : ($property['status'] == 'booked' ? 'warning' : 'danger')); ?>">
                                                <?php echo h($mlSupport->translate(ucfirst($property['status']))); ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th><?php echo h($mlSupport->translate('Featured')); ?></th>
                                        <td><?php echo $property['featured'] ? '<span class="badge bg-warning text-white">' . h($mlSupport->translate('Yes')) . '</span>' : h($mlSupport->translate('No')); ?></td>
                                    </tr>
                                </table>
                            </div>

                            <div class="col-md-6 mb-4">
                                <h4 class="card-title text-primary mb-3"><?php echo h($mlSupport->translate('Location & Area')); ?></h4>
                                <table class="table table-bordered">
                                    <tr>
                                        <th width="30%"><?php echo h($mlSupport->translate('Location')); ?></th>
                                        <td><?php echo h($property['location'] ?: '-'); ?></td>
                                    </tr>
                                    <tr>
                                        <th><?php echo h($mlSupport->translate('City')); ?></th>
                                        <td><?php echo h($property['city'] ?: '-'); ?></td>
                                    </tr>
                                    <tr>
                                        <th><?php echo h($mlSupport->translate('State')); ?></th>
                                        <td><?php echo h($property['state'] ?: '-'); ?></td>
                                    </tr>
                                    <tr>
                                        <th><?php echo h($mlSupport->translate('Pincode')); ?></th>
                                        <td><?php echo h($property['pincode'] ?: '-'); ?></td>
                                    </tr>
                                    <tr>
                                        <th><?php echo h($mlSupport->translate('Area')); ?></th>
                                        <td><?php echo h($property['area'] ? $property['area'] . ' ' . $property['area_unit'] : '-'); ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <h4 class="card-title text-primary mb-3"><?php echo h($mlSupport->translate('Specifications')); ?></h4>
                                <table class="table table-bordered">
                                    <tr>
                                        <th width="30%"><?php echo h($mlSupport->translate('Bedrooms')); ?></th>
                                        <td><?php echo h($property['bedrooms'] ?: '-'); ?></td>
                                    </tr>
                                    <tr>
                                        <th><?php echo h($mlSupport->translate('Bathrooms')); ?></th>
                                        <td><?php echo h($property['bathrooms'] ?: '-'); ?></td>
                                    </tr>
                                </table>
                            </div>

                            <div class="col-md-12">
                                <h4 class="card-title text-primary mb-3"><?php echo h($mlSupport->translate('Description')); ?></h4>
                                <div class="p-3 border rounded bg-light">
                                    <?php echo nl2br(h($property['description'] ?: $mlSupport->translate('No description available.'))); ?>
                                </div>
                            </div>
                        </div>

                        <div class="text-end mt-4">
                            <a href="/admin/properties" class="btn btn-secondary"><?php echo h($mlSupport->translate('Back to List')); ?></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'admin_footer.php'; ?>
