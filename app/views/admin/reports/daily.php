<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Daily Report</h4>
                <div>
                    <form method="GET" class="d-inline-flex align-items-center gap-2">
                        <input type="date" name="date" class="form-control form-control-sm" value="<?php echo htmlspecialchars($report_data['date'] ?? date('Y-m-d')); ?>">
                        <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-search"></i> View</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h6 class="card-subtitle mb-2 text-white-50">New Leads</h6>
                    <h2 class="mb-0"><?php echo $report_data['leads_count'] ?? 0; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h6 class="card-subtitle mb-2 text-white-50">Inquiries</h6>
                    <h2 class="mb-0"><?php echo $report_data['inquiries_count'] ?? 0; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h6 class="card-subtitle mb-2 text-white-50">Sales</h6>
                    <h2 class="mb-0"><?php echo $report_data['sales_count'] ?? 0; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card bg-warning text-dark">
                <div class="card-body text-center">
                    <h6 class="card-subtitle mb-2 text-dark-50">Revenue</h6>
                    <h2 class="mb-0">₹<?php echo number_format($report_data['revenue'] ?? 0); ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card bg-secondary text-white">
                <div class="card-body text-center">
                    <h6 class="card-subtitle mb-2 text-white-50">New Customers</h6>
                    <h2 class="mb-0"><?php echo $report_data['new_customers'] ?? 0; ?></h2>
                </div>
            </div>
        </div>
    </div>

    <div class="card aps-cp-card">
        <div class="card-header aps-cp-card-header">
            <h5 class="card-title mb-0">Daily Summary — <?php echo htmlspecialchars($report_data['date'] ?? date('Y-m-d')); ?></h5>
        </div>
        <div class="card-body aps-cp-card-body">
            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>Metric</th>
                        <th>Value</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td>Leads Generated</td><td><?php echo $report_data['leads_count'] ?? 0; ?></td></tr>
                    <tr><td>Inquiries Received</td><td><?php echo $report_data['inquiries_count'] ?? 0; ?></td></tr>
                    <tr><td>Sales Closed</td><td><?php echo $report_data['sales_count'] ?? 0; ?></td></tr>
                    <tr><td>Revenue</td><td>₹<?php echo number_format($report_data['revenue'] ?? 0); ?></td></tr>
                    <tr><td>New Customers</td><td><?php echo $report_data['new_customers'] ?? 0; ?></td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
