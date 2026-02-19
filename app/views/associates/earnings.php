<?php require_once 'app/views/layouts/associate_header.php'; ?>

<div class="container-fluid mt-4">
    <!-- Breadcrumb -->
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/associate/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Earnings and Commissions</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Earnings Summary -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-wallet mr-2"></i>Earnings Summary
                    </h6>
                    <div class="btn-group">
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="exportEarnings()">
                            <i class="fas fa-download mr-1"></i>Export
                        </button>
                        <button type="button" class="btn btn-outline-info btn-sm" onclick="printEarnings()">
                            <i class="fas fa-print mr-1"></i>Print
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center p-3 bg-light rounded">
                                <div class="h4 mb-2 text-success font-weight-bold">
                                    ₹<?= number_format($summary['total_commissions'] ?? 0) ?>
                                </div>
                                <small class="text-muted">Total Commission Earned</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 bg-light rounded">
                                <div class="h4 mb-2 text-info font-weight-bold">
                                    ₹<?= number_format($summary['level_1_earnings'] ?? 0) ?>
                                </div>
                                <small class="text-muted">Level 1 Commission</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 bg-light rounded">
                                <div class="h4 mb-2 text-warning font-weight-bold">
                                    ₹<?= number_format(($summary['level_2_earnings'] ?? 0) + ($summary['level_3_earnings'] ?? 0)) ?>
                                </div>
                                <small class="text-muted">Team Commission</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 bg-light rounded">
                                <div class="h4 mb-2 text-primary font-weight-bold">
                                    <?= $summary['total_commission_payments'] ?? 0 ?>
                                </div>
                                <small class="text-muted">Total Payments</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-body">
                    <form method="GET" class="row">
                        <div class="col-md-3">
                            <label for="date_from" class="form-label">From Date</label>
                            <input type="date" class="form-control" id="date_from" name="date_from"
                                   value="<?= $filters['date_from'] ?? '' ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="date_to" class="form-label">To Date</label>
                            <input type="date" class="form-control" id="date_to" name="date_to"
                                   value="<?= $filters['date_to'] ?? '' ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-control" id="status" name="status">
                                <option value="">All</option>
                                <option value="completed" <?= ($filters['status'] ?? '') === 'completed' ? 'selected' : '' ?>>Completed</option>
                                <option value="pending" <?= ($filters['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="failed" <?= ($filters['status'] ?? '') === 'failed' ? 'selected' : '' ?>>Failed</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-flex">
                                <button type="submit" class="btn btn-primary mr-2">
                                    <i class="fas fa-filter mr-1"></i>फिल्टर
                                </button>
                                <a href="/associate/earnings" class="btn btn-secondary">
                                    <i class="fas fa-times mr-1"></i>क्लियर
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Earnings Table -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-list mr-2"></i>अर्निंग्स डिटेल्स
                    </h6>
                    <div class="text-muted">
                        टोटल रिकॉर्ड्स: <strong><?= count($earnings) ?></strong>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!empty($earnings)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th>डेट</th>
                                        <th>सेल डिटेल्स</th>
                                        <th>कमिशन लेवल</th>
                                        <th>कमिशन अमाउंट</th>
                                        <th>स्टेटस</th>
                                        <th>एक्शन</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($earnings as $earning): ?>
                                        <tr>
                                            <td>
                                                <?= date('d M Y', strtotime($earning['sale_date'])) ?>
                                                <br>
                                                <small class="text-muted">
                                                    <?= date('h:i A', strtotime($earning['sale_date'])) ?>
                                                </small>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong><?= htmlspecialchars($earning['property_title']) ?></strong>
                                                </div>
                                                <small class="text-muted">
                                                    कस्टमर: <?= htmlspecialchars($earning['customer_name']) ?>
                                                </small>
                                                <br>
                                                <small class="text-muted">
                                                    सेल अमाउंट: ₹<?= number_format($earning['sale_amount']) ?>
                                                </small>
                                            </td>
                                            <td>
                                                <span class="badge badge-info">
                                                    लेवल <?= $earning['commission_level'] ?>
                                                </span>
                                                <br>
                                                <small class="text-muted">
                                                    <?= $earning['commission_percentage'] ?>% कमिशन
                                                </small>
                                            </td>
                                            <td>
                                                <span class="font-weight-bold text-success">
                                                    ₹<?= number_format($earning['commission_amount']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php
                                                $status = $earning['status'] ?? 'completed';
                                                $statusClass = 'success';
                                                if ($status === 'pending') $statusClass = 'warning';
                                                if ($status === 'failed') $statusClass = 'danger';
                                                ?>
                                                <span class="badge badge-<?= $statusClass ?>">
                                                    <?= ucfirst($status) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button type="button" class="btn btn-outline-info"
                                                            onclick="viewEarningDetails(<?= $earning['id'] ?>)">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-primary"
                                                            onclick="downloadReceipt(<?= $earning['id'] ?>)">
                                                        <i class="fas fa-download"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <nav aria-label="Earnings pagination" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <li class="page-item disabled">
                                    <a class="page-link" href="#" tabindex="-1">पिछला</a>
                                </li>
                                <li class="page-item active">
                                    <a class="page-link" href="#">1</a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="#">2</a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="#">3</a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="#">अगला</a>
                                </li>
                            </ul>
                        </nav>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-wallet fa-3x text-muted mb-3"></i>
                            <p class="text-muted">कोई अर्निंग्स रिकॉर्ड नहीं मिले</p>
                            <?php if (!empty($filters)): ?>
                                <p class="text-muted">फिल्टर्स को चेंज करके फिर से ट्राई करें</p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Commission Breakdown Chart -->
    <div class="row mb-4">
        <div class="col-lg-8 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-pie mr-2"></i>कमिशन ब्रेकडाउन
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="commissionChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle mr-2"></i>कमिशन स्ट्रक्चर
                    </h6>
                </div>
                <div class="card-body">
                    <div class="commission-structure">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <span>लेवल 1 (डायरेक्ट)</span>
                                <span class="badge badge-primary">5%</span>
                            </div>
                            <small class="text-muted">डायरेक्ट रेफरल से कमिशन</small>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <span>लेवल 2</span>
                                <span class="badge badge-success">3%</span>
                            </div>
                            <small class="text-muted">ग्रैंडचाइल्ड से कमिशन</small>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <span>लेवल 3</span>
                                <span class="badge badge-info">2%</span>
                            </div>
                            <small class="text-muted">ग्रेट ग्रैंडचाइल्ड से कमिशन</small>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <span>लेवल 4</span>
                                <span class="badge badge-warning">1%</span>
                            </div>
                            <small class="text-muted">फोर्थ जनरेशन से कमिशन</small>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <span>लेवल 5</span>
                                <span class="badge badge-secondary">0.5%</span>
                            </div>
                            <small class="text-muted">फिफ्थ जनरेशन से कमिशन</small>
                        </div>

                        <hr>
                        <div class="alert alert-info">
                            <small>
                                <i class="fas fa-info-circle mr-1"></i>
                                कमिशन तभी मिलता है जब सेल कंप्लीट हो जाती है
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Earnings Trend -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-line mr-2"></i>मासिक अर्निंग्स ट्रेंड
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="monthlyEarningsChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Commission Breakdown Chart
    var commissionCtx = document.getElementById('commissionChart').getContext('2d');

    var commissionData = {
        labels: ['लेवल 1', 'लेवल 2', 'लेवल 3', 'लेवल 4', 'लेवल 5'],
        datasets: [{
            data: [
                <?= $summary['level_1_earnings'] ?? 0 ?>,
                <?= $summary['level_2_earnings'] ?? 0 ?>,
                <?= $summary['level_3_earnings'] ?? 0 ?>,
                <?= $summary['level_4_earnings'] ?? 0 ?>,
                <?= $summary['level_5_earnings'] ?? 0 ?>
            ],
            backgroundColor: [
                'rgba(255, 99, 132, 0.8)',
                'rgba(54, 162, 235, 0.8)',
                'rgba(255, 205, 86, 0.8)',
                'rgba(75, 192, 192, 0.8)',
                'rgba(153, 102, 255, 0.8)'
            ],
            borderWidth: 2
        }]
    };

    new Chart(commissionCtx, {
        type: 'doughnut',
        data: commissionData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            var label = context.label || '';
                            var value = context.parsed || 0;
                            var total = context.dataset.data.reduce((a, b) => a + b, 0);
                            var percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                            return label + ': ₹' + value.toLocaleString() + ' (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });

    // Monthly Earnings Chart (Sample data - replace with actual)
    var monthlyCtx = document.getElementById('monthlyEarningsChart').getContext('2d');

    var monthlyData = {
        labels: ['जनवरी', 'फ़रवरी', 'मार्च', 'अप्रैल', 'मई', 'जून'],
        datasets: [{
            label: 'मासिक अर्निंग्स (₹)',
            data: [15000, 22000, 18000, 25000, 30000, 35000],
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            tension: 0.1,
            fill: true
        }]
    };

    new Chart(monthlyCtx, {
        type: 'line',
        data: monthlyData,
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
});

function exportEarnings() {
    // This would implement export functionality
    alert('एक्सपोर्ट फीचर जल्द आ रहा है!');
}

function printEarnings() {
    window.print();
}

function viewEarningDetails(earningId) {
    // This would open earning details modal
    console.log('Viewing earning details:', earningId);
}

function downloadReceipt(earningId) {
    // This would download receipt
    console.log('Downloading receipt for:', earningId);
}
</script>

<style>
.card {
    border-radius: 10px;
    border: none;
    box-shadow: 0 0 20px rgba(0,0,0,0.08);
}

.card-header {
    border-radius: 10px 10px 0 0 !important;
    border-bottom: 2px solid rgba(0,0,0,0.1);
}

.table th {
    border-top: none;
    font-weight: 600;
    color: #495057;
    background-color: #f8f9fa;
}

.table td {
    vertical-align: middle;
}

.badge {
    font-size: 0.8em;
}

.commission-structure {
    max-height: 400px;
    overflow-y: auto;
}

.pagination .page-link {
    color: #007bff;
}

.pagination .page-item.active .page-link {
    background-color: #007bff;
    border-color: #007bff;
}

.text-success {
    color: #28a745 !important;
}

.text-info {
    color: #17a2b8 !important;
}

.text-warning {
    color: #ffc107 !important;
}

.text-primary {
    color: #007bff !important;
}

.btn-outline-primary:hover,
.btn-outline-info:hover,
.btn-outline-secondary:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.form-control {
    border-radius: 8px;
    border: 2px solid #e3e6f0;
}

.form-control:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}
</style>

<?php require_once 'app/views/layouts/associate_footer.php'; ?>
