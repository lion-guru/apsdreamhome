<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title><?= __('analytics_title', [], 'Analytics - APS Dream Home') ?></title>
    <link href="<?= BASE_URL ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/fonts/fontawesome/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h1 class="h3 mb-4"><?= __('analytics_dashboard', [], 'Analytics Dashboard') ?></h1>
                <div class="row">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body aps-cp-card-body">
                                <h5><?= __('analytics_total_visitors', [], 'Total Visitors') ?></h5>
                                <h3>1,234</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body aps-cp-card-body">
                                <h5><?= __('analytics_page_views', [], 'Page Views') ?></h5>
                                <h3>5,678</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body aps-cp-card-body">
                                <h5><?= __('analytics_conversions', [], 'Conversions') ?></h5>
                                <h3>89</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body aps-cp-card-body">
                                <h5><?= __('analytics_revenue', [], 'Revenue') ?></h5>
                                <h3>&#8377;12,34,567</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
