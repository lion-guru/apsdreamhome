<?php
$page_title = $page_title ?? __('analytics_title', [], 'Analytics - APS Dream Home');
$page_description = $page_description ?? __('analytics_desc', [], 'Analytics Dashboard - APS Dream Home');
$current_page = 'analytics';
?>
<div class="container-fluid py-4">
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