<?php
/**
 * Employee Salary History View
 * Shows employee salary history and payment records
 */
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-money-bill-wave me-2"></i>Salary History</h2>
        <div class="dropdown">
            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <i class="fas fa-calendar me-2"></i>Select Year
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="?year=<?= date('Y') ?>"><?= date('Y') ?></a></li>
                <li><a class="dropdown-item" href="?year=<?= date('Y') - 1 ?>"><?= date('Y') - 1 ?></a></li>
                <li><a class="dropdown-item" href="?year=<?= date('Y') - 2 ?>"><?= date('Y') - 2 ?></a></li>
            </ul>
        </div>
    </div>

    <!-- Salary Overview -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stats-card card text-white" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">
                                ₹<?= number_format($salary_history[0]['current_salary'] ?? 0, 0) ?>
                            </h4>
                            <small>Current Salary</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-rupee-sign fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card card text-white" style="background: linear-gradient(135deg, #007bff 0%, #6610f2 100%);">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">
                                ₹<?= number_format(array_sum(array_column($salary_history, 'net_salary')), 0) ?>
                            </h4>
                            <small>Total Paid (YTD)</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-wallet fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card card text-white" style="background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">
                                ₹<?= number_format(array_sum(array_column($salary_history, 'bonus')), 0) ?>
                            </h4>
                            <small>Total Bonus (YTD)</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-gift fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card card text-white" style="background: linear-gradient(135deg, #dc3545 0%, #e83e8c 100%);">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">
                                ₹<?= number_format(array_sum(array_column($salary_history, 'deductions')), 0) ?>
                            </h4>
                            <small>Total Deductions (YTD)</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-minus-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Salary History Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-list me-2"></i>Salary Records</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($salary_history)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>No salary records found.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Month/Year</th>
                                        <th>Basic Salary</th>
                                        <th>Allowances</th>
                                        <th>Bonus</th>
                                        <th>Deductions</th>
                                        <th>Net Salary</th>
                                        <th>Payment Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($salary_history as $record): ?>
                                        <tr>
                                            <td>
                                                <strong>
                                                    <?= date('F Y', strtotime($record['month'] . '-01')) ?>
                                                </strong>
                                            </td>
                                            <td>
                                                ₹<?= number_format($record['basic_salary'] ?? 0, 2) ?>
                                            </td>
                                            <td>
                                                ₹<?= number_format($record['allowances'] ?? 0, 2) ?>
                                            </td>
                                            <td>
                                                ₹<?= number_format($record['bonus'] ?? 0, 2) ?>
                                            </td>
                                            <td>
                                                ₹<?= number_format($record['deductions'] ?? 0, 2) ?>
                                            </td>
                                            <td>
                                                <strong class="text-success">
                                                    ₹<?= number_format($record['net_salary'] ?? 0, 2) ?>
                                                </strong>
                                            </td>
                                            <td>
                                                <?php if (!empty($record['payment_date'])): ?>
                                                    <span class="badge bg-success">
                                                        <?= date('M d, Y', strtotime($record['payment_date'])) ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning">Pending</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php
                                                $status = $record['status'] ?? 'pending';
                                                $badgeClass = 'bg-secondary';

                                                switch ($status) {
                                                    case 'paid':
                                                        $badgeClass = 'bg-success';
                                                        break;
                                                    case 'pending':
                                                        $badgeClass = 'bg-warning';
                                                        break;
                                                    case 'failed':
                                                        $badgeClass = 'bg-danger';
                                                        break;
                                                }
                                                ?>
                                                <span class="badge <?= $badgeClass ?>">
                                                    <?= ucfirst($status) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-info" onclick="viewSalarySlip(<?= $record['salary_id'] ?>)">
                                                    <i class="fas fa-file-pdf me-1"></i>View Slip
                                                </button>
                                                <?php if (($record['status'] ?? 'pending') === 'pending'): ?>
                                                    <button class="btn btn-sm btn-success" onclick="downloadSalarySlip(<?= $record['salary_id'] ?>)">
                                                        <i class="fas fa-download me-1"></i>Download
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Salary Trend Chart -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-chart-line me-2"></i>Salary Trend</h5>
                </div>
                <div class="card-body">
                    <canvas id="salaryTrendChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Salary Components Breakdown -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-pie-chart me-2"></i>Salary Components</h5>
                </div>
                <div class="card-body">
                    <canvas id="salaryComponentsChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-info-circle me-2"></i>Salary Information</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($salary_history)): ?>
                        <?php $latestSalary = $salary_history[0]; ?>
                        <div class="salary-info">
                            <div class="info-item mb-3">
                                <strong>Current Basic Salary:</strong>
                                ₹<?= number_format($latestSalary['basic_salary'] ?? 0, 2) ?>
                            </div>
                            <div class="info-item mb-3">
                                <strong>Allowances:</strong>
                                ₹<?= number_format($latestSalary['allowances'] ?? 0, 2) ?>
                                <small class="text-muted d-block">
                                    (HRA, Medical, Transport, etc.)
                                </small>
                            </div>
                            <div class="info-item mb-3">
                                <strong>Tax Deductions:</strong>
                                ₹<?= number_format($latestSalary['tax_deductions'] ?? 0, 2) ?>
                            </div>
                            <div class="info-item mb-3">
                                <strong>PF Contribution:</strong>
                                ₹<?= number_format($latestSalary['pf_deduction'] ?? 0, 2) ?>
                                <small class="text-muted d-block">
                                    (12% of Basic Salary)
                                </small>
                            </div>
                            <hr>
                            <div class="info-item">
                                <strong>Annual CTC:</strong>
                                <span class="text-primary">
                                    ₹<?= number_format(($latestSalary['basic_salary'] ?? 0) * 12 + ($latestSalary['allowances'] ?? 0) * 12, 2) ?>
                                </span>
                            </div>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No salary information available.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Salary Slip Modal -->
<div class="modal fade" id="salarySlipModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Salary Slip</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="salarySlipContent">
                <!-- Salary slip will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Salary Trend Chart
const trendCtx = document.getElementById('salaryTrendChart');
if (trendCtx) {
    const months = [
        <?php
        $months = [];
        foreach ($salary_history as $record) {
            $months[] = "'" . date('M Y', strtotime($record['month'] . '-01')) . "'";
        }
        echo implode(', ', $months);
        ?>
    ];

    const netSalaries = [
        <?php
        $salaries = [];
        foreach ($salary_history as $record) {
            $salaries[] = $record['net_salary'] ?? 0;
        }
        echo implode(', ', $salaries);
        ?>
    ];

    new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: months,
            datasets: [{
                label: 'Net Salary',
                data: netSalaries,
                borderColor: '#28a745',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '₹' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });
}

// Salary Components Chart
const componentsCtx = document.getElementById('salaryComponentsChart');
if (componentsCtx && salary_history.length > 0) {
    const latest = salary_history[0];
    new Chart(componentsCtx, {
        type: 'doughnut',
        data: {
            labels: ['Basic Salary', 'Allowances', 'Bonus', 'Deductions'],
            datasets: [{
                data: [
                    <?= $latest['basic_salary'] ?? 0 ?>,
                    <?= $latest['allowances'] ?? 0 ?>,
                    <?= $latest['bonus'] ?? 0 ?>,
                    <?= $latest['deductions'] ?? 0 ?>
                ],
                backgroundColor: [
                    '#007bff',
                    '#28a745',
                    '#ffc107',
                    '#dc3545'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
}

function viewSalarySlip(salaryId) {
    // In a real implementation, you would make an AJAX call to fetch salary slip
    $('#salarySlipModal').modal('show');
    $('#salarySlipContent').html('<p>Loading salary slip...</p>');
}

function downloadSalarySlip(salaryId) {
    // In a real implementation, you would generate and download the salary slip PDF
    window.location.href = `/employee/salary-slip/download/${salaryId}`;
}

// Auto-refresh data every 5 minutes
setInterval(function() {
    if (!document.hidden) {
        location.reload();
    }
}, 300000);
</script>

<style>
.stats-card {
    border: none;
    border-radius: 10px;
    transition: transform 0.2s;
}

.stats-card:hover {
    transform: translateY(-2px);
}

.table th {
    border-top: none;
    font-weight: 600;
}

.badge {
    font-size: 0.8em;
}

.salary-info .info-item {
    padding: 8px 0;
    border-bottom: 1px solid #eee;
}

.salary-info .info-item:last-child {
    border-bottom: none;
    font-size: 1.1em;
}

.salary-card {
    transition: transform 0.2s, box-shadow 0.2s;
}

.salary-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
</style>
