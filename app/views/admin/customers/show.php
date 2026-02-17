<?php

/**
 * Customers Show View
 */
?>
<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title"><?php echo h($page_title); ?></h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/admin/dashboard"><?php echo h($mlSupport->translate('Dashboard')); ?></a></li>
                    <li class="breadcrumb-item"><a href="/admin/customers"><?php echo h($mlSupport->translate('Customers')); ?></a></li>
                    <li class="breadcrumb-item active"><?php echo h($mlSupport->translate('Profile')); ?></li>
                </ul>
            </div>
        </div>
    </div>
    <!-- /Page Header -->

    <div class="row">
        <div class="col-md-12">
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <img class="rounded-circle" alt="User Image" src="/assets/img/profiles/<?php echo h($customer['profile_image'] ?? 'default-avatar.jpg'); ?>" width="80" height="80" onerror="this.src='/assets/img/profiles/default-avatar.jpg'">
                        </div>
                        <div class="col">
                            <h4 class="user-name mb-0"><?php echo h($customer['name'] ?? 'N/A'); ?></h4>
                            <h6 class="text-muted"><?php echo h($mlSupport->translate('Customer')); ?></h6>
                            <div class="user-Location"><i class="fas fa-map-marker-alt me-1"></i> <?php echo h(($customer['city'] ?? '') . ', ' . ($customer['state'] ?? '')); ?></div>
                            <div class="about-text text-muted"><?php echo h($customer['address'] ?? ''); ?></div>
                        </div>
                        <div class="col-auto">
                            <a href="/admin/customers/edit/<?php echo h($customer['id']); ?>" class="btn btn-primary">
                                <i class="fas fa-pencil-alt me-2"></i> <?php echo h($mlSupport->translate('Edit Profile')); ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#per_details_tab" role="tab"><?php echo h($mlSupport->translate('About')); ?></a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <!-- Personal Details Tab -->
                        <div class="tab-pane fade show active" id="per_details_tab" role="tabpanel">
                            <h5 class="card-title d-flex justify-content-between mb-3">
                                <span><?php echo h($mlSupport->translate('Personal Details')); ?></span>
                            </h5>
                            <div class="row mb-3">
                                <div class="col-sm-3 text-muted"><?php echo h($mlSupport->translate('Name')); ?></div>
                                <div class="col-sm-9"><?php echo h($customer['name'] ?? 'N/A'); ?></div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-3 text-muted"><?php echo h($mlSupport->translate('Email')); ?></div>
                                <div class="col-sm-9"><?php echo h($customer['email'] ?? 'N/A'); ?></div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-3 text-muted"><?php echo h($mlSupport->translate('Phone')); ?></div>
                                <div class="col-sm-9"><?php echo h($customer['phone'] ?? 'N/A'); ?></div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-3 text-muted"><?php echo h($mlSupport->translate('Status')); ?></div>
                                <div class="col-sm-9">
                                    <span class="badge bg-<?php echo h($customer['status'] == 'active' ? 'success' : 'danger'); ?>">
                                        <?php echo h($mlSupport->translate(ucwords($customer['status']))); ?>
                                    </span>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-3 text-muted"><?php echo h($mlSupport->translate('Address')); ?></div>
                                <div class="col-sm-9">
                                    <?php echo h($customer['address'] ?? 'N/A'); ?><br>
                                    <?php echo h($customer['city'] ?? ''); ?><br>
                                    <?php echo h($customer['state'] ?? ''); ?> - <?php echo h($customer['pincode'] ?? ''); ?>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-3 text-muted"><?php echo h($mlSupport->translate('Occupation')); ?></div>
                                <div class="col-sm-9"><?php echo h($customer['job_role'] ?? 'N/A'); ?></div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-3 text-muted"><?php echo h($mlSupport->translate('KYC Status')); ?></div>
                                <div class="col-sm-9"><?php echo h($customer['kyc_status'] ?? 'N/A'); ?></div>
                            </div>
                        </div>
                        <!-- /Personal Details Tab -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>