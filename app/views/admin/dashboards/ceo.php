<?php include APP_PATH . '/views/admin/layouts/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4"><i class="fas fa-chart-line me-2"></i>Executive Dashboard (CEO/Director)</h2>
        </div>
    </div>

    <!-- Financial & Growth Overview -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-start border-primary border-4 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small">Total Revenue</h6>
                    <h3><?php echo $financial_summary['revenue']; ?></h3>
                    <p class="text-success mb-0"><i class="fas fa-arrow-up me-1"></i>12% from last month</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-start border-danger border-4 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small">Expenses</h6>
                    <h3><?php echo $financial_summary['expenses']; ?></h3>
                    <p class="text-danger mb-0"><i class="fas fa-arrow-up me-1"></i>5% from last month</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-start border-success border-4 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small">Net Profit</h6>
                    <h3><?php echo $financial_summary['profit']; ?></h3>
                    <p class="text-success mb-0"><i class="fas fa-arrow-up me-1"></i>18% from last month</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Growth Chart Mockup -->
        <div class="col-md-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Company Growth Analysis</h5>
                </div>
                <div class="card-body" style="height: 300px; display: flex; align-items: center; justify-content: center; background: #f8f9fa;">
                    <div class="text-center text-muted">
                        <i class="fas fa-chart-area fa-3x mb-3"></i>
                        <p>Growth Visualization (Chart.js Integration Pending)</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="col-md-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Key Performance Indicators</h5>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            New Customers
                            <span class="badge bg-primary rounded-pill"><?php echo $company_growth['new_customers']; ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            New Properties
                            <span class="badge bg-success rounded-pill"><?php echo $company_growth['new_properties']; ?></span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include APP_PATH . '/views/admin/layouts/footer.php'; ?>
