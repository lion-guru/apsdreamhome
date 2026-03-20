<?php
// CEO Dashboard Template
$data = $data ?? [];
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <nav class="col-md-3 col-lg-2 d-md-block bg-dark sidebar">
            <div class="position-sticky pt-3">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active text-white" href="<?= BASE_URL ?>/admin/dashboard/ceo">
                            <i class="fas fa-tachometer-alt me-2"></i>CEO Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="<?= BASE_URL ?>/admin/dashboard">
                            <i class="fas fa-building me-2"></i>Company Overview
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="<?= BASE_URL ?>/admin/financial">
                            <i class="fas fa-chart-line me-2"></i>Financial Reports
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="<?= BASE_URL ?>/admin/teams">
                            <i class="fas fa-users me-2"></i>Team Management
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="<?= BASE_URL ?>/admin/analytics">
                            <i class="fas fa-analytics me-2"></i>Analytics
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Main content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">CEO Executive Dashboard</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary">Export Report</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary">Print</button>
                    </div>
                </div>
            </div>

            <!-- Executive Summary Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Revenue</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">₹<?= number_format($data['total_revenue'] ?? 0) ?>Cr</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-rupee-sign fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Growth Rate</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $data['growth_rate'] ?? 0 ?>%</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-info shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Employees</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $data['total_employees'] ?? 0 ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-users fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Active Projects</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $data['active_projects'] ?? 0 ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-project-diagram fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Revenue Analytics -->
            <div class="row mb-4">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold text-primary">Revenue Analytics</h6>
                        </div>
                        <div class="card-body">
                            <canvas id="revenueChart" width="400" height="200"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold text-primary">Department Performance</h6>
                        </div>
                        <div class="card-body">
                            <canvas id="departmentChart" width="400" height="200"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Key Metrics -->
            <div class="row mb-4">
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold text-primary">Key Performance Indicators</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Metric</th>
                                            <th>Current</th>
                                            <th>Target</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Sales Conversion</td>
                                            <td><?= $data['sales_conversion'] ?? 0 ?>%</td>
                                            <td>75%</td>
                                            <td><span class="badge bg-<?= ($data['sales_conversion'] ?? 0) >= 75 ? 'success' : 'warning' ?>"> <?= ($data['sales_conversion'] ?? 0) >= 75 ? 'On Target' : 'Below Target' ?></span></td>
                                        </tr>
                                        <tr>
                                            <td>Customer Satisfaction</td>
                                            <td><?= $data['customer_satisfaction'] ?? 0 ?>%</td>
                                            <td>90%</td>
                                            <td><span class="badge bg-<?= ($data['customer_satisfaction'] ?? 0) >= 90 ? 'success' : 'warning' ?>"> <?= ($data['customer_satisfaction'] ?? 0) >= 90 ? 'Excellent' : 'Good' ?></span></td>
                                        </tr>
                                        <tr>
                                            <td>Project Completion</td>
                                            <td><?= $data['project_completion'] ?? 0 ?>%</td>
                                            <td>85%</td>
                                            <td><span class="badge bg-<?= ($data['project_completion'] ?? 0) >= 85 ? 'success' : 'warning' ?>"> <?= ($data['project_completion'] ?? 0) >= 85 ? 'On Track' : 'Delayed' ?></span></td>
                                        </tr>
                                        <tr>
                                            <td>Employee Productivity</td>
                                            <td><?= $data['employee_productivity'] ?? 0 ?>%</td>
                                            <td>80%</td>
                                            <td><span class="badge bg-<?= ($data['employee_productivity'] ?? 0) >= 80 ? 'success' : 'warning' ?>"> <?= ($data['employee_productivity'] ?? 0) >= 80 ? 'High' : 'Average' ?></span></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold text-primary">Recent Executive Actions</h6>
                        </div>
                        <div class="card-body">
                            <?php if (isset($data['executive_actions']) && !empty($data['executive_actions'])): ?>
                                <?php foreach ($data['executive_actions'] as $action): ?>
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-<?= $action['icon'] ?? 'tasks' ?> text-<?= $action['priority'] ?? 'primary' ?>"></i>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <div class="small text-gray-500"><?= $action['date'] ?? '' ?></div>
                                            <div class="fw-bold"><?= $action['title'] ?? '' ?></div>
                                            <div class="text-muted small"><?= $action['description'] ?? '' ?></div>
                                            <div class="mt-1">
                                                <span class="badge bg-<?= $action['status_color'] ?? 'secondary' ?>"><?= $action['status'] ?? 'Pending' ?></span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted">No recent executive actions found.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Strategic Initiatives -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold text-primary">Strategic Initiatives</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <?php if (isset($data['strategic_initiatives']) && !empty($data['strategic_initiatives'])): ?>
                                    <?php foreach ($data['strategic_initiatives'] as $initiative): ?>
                                        <div class="col-md-4 mb-3">
                                            <div class="card border-left-<?= $initiative['color'] ?? 'primary' ?>">
                                                <div class="card-body">
                                                    <h6 class="card-title"><?= $initiative['title'] ?? '' ?></h6>
                                                    <p class="card-text small"><?= $initiative['description'] ?? '' ?></p>
                                                    <div class="progress mb-2">
                                                        <div class="progress-bar bg-<?= $initiative['progress_color'] ?? 'primary' ?>" 
                                                             style="width: <?= $initiative['progress'] ?? 0 ?>%"></div>
                                                    </div>
                                                    <small class="text-muted"><?= $initiative['progress'] ?? 0 ?>% Complete</small>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="col-12">
                                        <p class="text-muted">No strategic initiatives defined.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Revenue Chart
const revenueCtx = document.getElementById('revenueChart').getContext('2d');
const revenueChart = new Chart(revenueCtx, {
    type: 'line',
    data: {
        labels: <?= json_encode($data['revenue_labels'] ?? ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun']) ?>,
        datasets: [{
            label: 'Revenue (₹ Cr)',
            data: <?= json_encode($data['revenue_data'] ?? [0, 0, 0, 0, 0, 0]) ?>,
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Department Chart
const deptCtx = document.getElementById('departmentChart').getContext('2d');
const deptChart = new Chart(deptCtx, {
    type: 'doughnut',
    data: {
        labels: <?= json_encode($data['dept_labels'] ?? ['Sales', 'Marketing', 'Operations', 'Finance']) ?>,
        datasets: [{
            data: <?= json_encode($data['dept_data'] ?? [25, 25, 25, 25]) ?>,
            backgroundColor: [
                'rgba(255, 99, 132, 0.8)',
                'rgba(54, 162, 235, 0.8)',
                'rgba(255, 206, 86, 0.8)',
                'rgba(75, 192, 192, 0.8)'
            ]
        }]
    },
    options: {
        responsive: true
    }
});
</script>
