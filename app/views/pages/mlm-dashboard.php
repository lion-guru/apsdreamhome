<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>MLM Dashboard - APS Dream Home</title>
    <link href="<?= BASE_URL ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/fonts/fontawesome/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/uiux-fixes.css?v=1">
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h1 class="h3 mb-4"><?= __('mlm_heading', [], 'MLM Dashboard') ?></h1>
                <div class="row">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body aps-cp-card-body">
                                <h5><?= __('mlm_total_users', [], 'Total users') ?></h5>
                                <h3>150</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body aps-cp-card-body">
                                <h5><?= __('mlm_active_users', [], 'Active users') ?></h5>
                                <h3>120</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body aps-cp-card-body">
                                <h5><?= __('mlm_commission_earned', [], 'Commission Earned') ?></h5>
                                <h3>₹45,000</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body aps-cp-card-body">
                                <h5><?= __('mlm_network_size', [], 'Network Size') ?></h5>
                                <h3>500</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>