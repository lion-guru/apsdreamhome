<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Monthly Report</h4>
                <div>
                    <form method="GET" class="d-inline-flex align-items-center gap-2">
    <?php echo CSRFProtection::csrfField(); ?>
                        <input type="month" name="month" class="form-control form-control-sm" value="<?php echo htmlspecialchars($report_data['month'] ?? date('Y-m')); ?>">
                        <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-search"></i> View</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h6 class="card-subtitle mb-2 text-white-50">Leads</h6>
                    <h2 class="mb-0"><?php echo $report_data['leads_count'] ?? 0; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h6 class="card-subtitle mb-2 text-white-50">Inquiries</h6>
                    <h2 class="mb-0"><?php echo $report_data['inquiries_count'] ?? 0; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h6 class="card-subtitle mb-2 text-white-50">Sales</h6>
                    <h2 class="mb-0"><?php echo $report_data['sales_count'] ?? 0; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body text-center">
                    <h6 class="card-subtitle mb-2 text-dark-50">Revenue</h6>
                    <h2 class="mb-0">₹<?php echo number_format($report_data['revenue'] ?? 0); ?></h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card aps-cp-card">
                <div class="card-header aps-cp-card-header">
                    <h5 class="card-title mb-0">Conversion Rate</h5>
                </div>
                <div class="card-body text-center">
                    <h1 class="display-4 text-primary"><?php echo $report_data['conversion_rate'] ?? 0; ?>%</h1>
                    <p class="text-muted"><?php echo htmlspecialchars($report_data['month'] ?? ''); ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card aps-cp-card">
                <div class="card-header aps-cp-card-header">
                    <h5 class="card-title mb-0">Monthly Summary</h5>
                </div>
                <div class="card-body aps-cp-card-body">
                    <div class="table-responsive"><table class="table table-bordered mb-0">
                        <thead class="table-light">
                            <tr><th>Metric</th><th>Value</th></tr>
                        </thead>
                        <tbody>
                            <tr><td>Leads</td><td><?php echo $report_data['leads_count'] ?? 0; ?></td></tr>
                            <tr><td>Inquiries</td><td><?php echo $report_data['inquiries_count'] ?? 0; ?></td></tr>
                            <tr><td>Sales</td><td><?php echo $report_data['sales_count'] ?? 0; ?></td></tr>
                            <tr><td>Revenue</td><td>₹<?php echo number_format($report_data['revenue'] ?? 0); ?></td></tr>
                        </tbody>
                    </table></div>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($report_data['top_performers'])): ?>
    <div class="card mt-3">
        <div class="card-header aps-cp-card-header">
            <h5 class="card-title mb-0">Top Performers</h5>
        </div>
        <div class="card-body aps-cp-card-body">
            <div class="table-responsive"><table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Sales</th>
                        <th>Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $rank = 1; ?>
                    <?php foreach ($report_data['top_performers'] as $performer): ?>
                    <tr>
                        <td><?php echo $rank++; ?></td>
                        <td><?php echo htmlspecialchars($performer['name'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($performer['email'] ?? ''); ?></td>
                        <td><?php echo $performer['sales_count'] ?? 0; ?></td>
                        <td>₹<?php echo number_format($performer['total_revenue'] ?? 0); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table></div>
        </div>
    </div>
    <?php endif; ?>
</div>
