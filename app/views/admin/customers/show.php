<?php
if (!isAdmin()) {
    header("Location: /admin/login");
    exit();
}
$page_title = $page_title ?? $mlSupport->translate("Customer Profile");
require_once ABSPATH . '/resources/views/admin/layouts/header.php';
?>

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <h3 class="page-title"><?php echo h($page_title); ?></h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/admin/dashboard"><?php echo h($mlSupport->translate('Dashboard')); ?></a></li>
                        <li class="breadcrumb-item"><a href="/admin/customers"><?php echo h($mlSupport->translate('Customers')); ?></a></li>
                        <li class="breadcrumb-item active"><?php echo h($mlSupport->translate('Profile')); ?></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="profile-header">
                    <div class="row align-items-center">
                        <div class="col-auto profile-image">
                            <a href="#">
                                <img class="rounded-circle" alt="User Image" src="/assets/img/profiles/<?php echo h($customer['profile_image'] ?? 'default-avatar.jpg'); ?>" onerror="this.src='/assets/img/profiles/default-avatar.jpg'">
                            </a>
                        </div>
                        <div class="col ml-md-n2 profile-user-info">
                            <h4 class="user-name mb-0"><?php echo h($customer['name'] ?? 'N/A'); ?></h4>
                            <h6 class="text-muted"><?php echo h($mlSupport->translate('Customer')); ?></h6>
                            <div class="user-Location"><i class="fas fa-map-marker-alt"></i> <?php echo h(($customer['city'] ?? '') . ', ' . ($customer['state'] ?? '')); ?></div>
                            <div class="about-text"><?php echo h($customer['address'] ?? ''); ?></div>
                        </div>
                        <div class="col-auto profile-btn">
                            <a href="/admin/customers/edit/<?php echo h($customer['id']); ?>" class="btn btn-primary">
                                <?php echo h($mlSupport->translate('Edit Profile')); ?>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="profile-menu">
                    <ul class="nav nav-tabs nav-tabs-solid">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#per_details_tab"><?php echo h($mlSupport->translate('About')); ?></a>
                        </li>
                    </ul>
                </div>
                <div class="tab-content profile-tab-cont">
                    <!-- Personal Details Tab -->
                    <div class="tab-pane fade show active" id="per_details_tab">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title d-flex justify-content-between">
                                            <span><?php echo h($mlSupport->translate('Personal Details')); ?></span>
                                            <a class="edit-link" href="/admin/customers/edit/<?php echo h($customer['id']); ?>"><i class="fa fa-edit mr-1"></i><?php echo h($mlSupport->translate('Edit')); ?></a>
                                        </h5>
                                        <div class="row">
                                            <p class="col-sm-2 text-muted mb-0 mb-sm-3"><?php echo h($mlSupport->translate('Name')); ?></p>
                                            <p class="col-sm-10"><?php echo h($customer['name'] ?? 'N/A'); ?></p>
                                        </div>
                                        <div class="row">
                                            <p class="col-sm-2 text-muted mb-0 mb-sm-3"><?php echo h($mlSupport->translate('Email')); ?></p>
                                            <p class="col-sm-10"><?php echo h($customer['email'] ?? 'N/A'); ?></p>
                                        </div>
                                        <div class="row">
                                            <p class="col-sm-2 text-muted mb-0 mb-sm-3"><?php echo h($mlSupport->translate('Phone')); ?></p>
                                            <p class="col-sm-10"><?php echo h($customer['phone'] ?? 'N/A'); ?></p>
                                        </div>
                                        <div class="row">
                                            <p class="col-sm-2 text-muted mb-0 mb-sm-3"><?php echo h($mlSupport->translate('Status')); ?></p>
                                            <p class="col-sm-10">
                                                <span class="badge bg-<?php echo h($customer['status'] == 'active' ? 'success' : 'danger'); ?>">
                                                    <?php echo h($mlSupport->translate(ucwords($customer['status']))); ?>
                                                </span>
                                            </p>
                                        </div>
                                        <div class="row">
                                            <p class="col-sm-2 text-muted mb-0"><?php echo h($mlSupport->translate('Address')); ?></p>
                                            <p class="col-sm-10 mb-0"><?php echo h($customer['address'] ?? 'N/A'); ?>,<br>
                                            <?php echo h($customer['city'] ?? ''); ?>,<br>
                                            <?php echo h($customer['state'] ?? ''); ?> - <?php echo h($customer['pincode'] ?? ''); ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /Personal Details Tab -->
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once ABSPATH . '/resources/views/admin/layouts/footer.php'; ?>
