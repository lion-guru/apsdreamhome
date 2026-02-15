<?php require_once 'app/views/layouts/header.php'; ?>

<div class="container-fluid mt-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0 text-gray-800">
                    <i class="fas fa-chart-bar mr-2"></i>
                    लीड रिपोर्ट्स
                </h1>
                <div class="d-flex">
                    <!-- Date Range Filter -->
                    <form class="form-inline mr-3" method="GET" action="/leads/reports">
                        <div class="input-group">
                            <input type="date"
                                   name="start_date"
                                   class="form-control"
                                   value="<?= h($_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'))) ?>">
                            <div class="input-group-prepend input-group-append">
                                <span class="input-group-text">से</span>
                            </div>
                            <input type="date"
                                   name="end_date"
                                   class="form-control"
                                   value="<?= h($_GET['end_date'] ?? date('Y-m-d')) ?>">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="submit">
                                    <i class="fas fa-filter"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                    <div class="btn-group">
                        <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
                            <i class="fas fa-download mr-1"></i>एक्सपोर्ट
                        </button>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="/admin/export/leads?type=csv">
                                <i class="fas fa-file-csv mr-2"></i>CSV एक्सपोर्ट
                            </a>
                            <a class="dropdown-item" href="/admin/export/leads?type=excel">
                                <i class="fas fa-file-excel mr-2"></i>Excel एक्सपोर्ट
                            </a>
                            <a class="dropdown-item" href="/admin/export/leads?type=pdf">
                                <i class="fas fa-file-pdf mr-2"></i>PDF एक्सपोर्ट
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Type Tabs -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs">
                        <li class="nav-item">
                            <a class="nav-link <?= $reportType == 'summary' ? 'active' : '' ?>"
                               href="/leads/reports?type=summary&start_date=<?= $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days')) ?>&end_date=<?= $_GET['end_date'] ?? date('Y-m-d') ?>">
                                <i class="fas fa-chart-pie mr-1"></i>सारांश रिपोर्ट
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $reportType == 'performance' ? 'active' : '' ?>"
                               href="/leads/reports?type=performance&start_date=<?= $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days')) ?>&end_date=<?= $_GET['end_date'] ?? date('Y-m-d') ?>">
                                <i class="fas fa-chart-line mr-1"></i>परफॉर्मेंस रिपोर्ट
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $reportType == 'source' ? 'active' : '' ?>"
                               href="/leads/reports?type=source&start_date=<?= $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days')) ?>&end_date=<?= $_GET['end_date'] ?? date('Y-m-d') ?>">
                                <i class="fas fa-bullhorn mr-1"></i>स्रोत रिपोर्ट
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $reportType == 'conversion' ? 'active' : '' ?>"
                               href="/leads/reports?type=conversion&start_date=<?= $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days')) ?>&end_date=<?= $_GET['end_date'] ?? date('Y-m-d') ?>">
                                <i class="fas fa-exchange-alt mr-1"></i>कन्वर्शन रिपोर्ट
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Content -->
    <?php if ($reportType == 'summary'): ?>
        <!-- Summary Report -->
        <div class="row">
            <!-- Key Metrics Cards -->
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    कुल लीड्स
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?= $report['total_leads'] ?? 0 ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-users fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    कन्वर्टेड लीड्स
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?= $report['converted_leads'] ?? 0 ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    कन्वर्शन रेट
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?= number_format($report['conversion_rate'] ?? 0, 1) ?>%
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-percentage fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    औसत बजट
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    ₹<?= number_format($report['avg_budget'] ?? 0) ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-rupee-sign fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row mb-4">
            <!-- Status Distribution Chart -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-chart-pie mr-2"></i>स्टेटस डिस्ट्रिब्यूशन
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-container" style="position: relative; height:300px;">
                            <canvas id="statusChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Source Performance Chart -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-chart-bar mr-2"></i>स्रोत परफॉर्मेंस
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-container" style="position: relative; height:300px;">
                            <canvas id="sourceChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <?php elseif ($reportType == 'performance'): ?>
        <!-- Performance Report -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-trophy mr-2"></i>परफॉर्मेंस मेट्रिक्स
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th>मेट्रिक</th>
                                        <th>वैल्यू</th>
                                        <th>परिवर्तन</th>
                                        <th>स्टेटस</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>कुल लीड्स (30 दिन)</td>
                                        <td><?= $report['total_leads_30_days'] ?? 0 ?></td>
                                        <td>
                                            <span class="badge badge-success">
                                                <i class="fas fa-arrow-up mr-1"></i>
                                                +12%
                                            </span>
                                        </td>
                                        <td><span class="text-success">बढ़िया</span></td>
                                    </tr>
                                    <tr>
                                        <td>कन्वर्शन रेट</td>
                                        <td><?= number_format($report['conversion_rate'] ?? 0, 1) ?>%</td>
                                        <td>
                                            <span class="badge badge-warning">
                                                <i class="fas fa-minus mr-1"></i>
                                                -2%
                                            </span>
                                        </td>
                                        <td><span class="text-warning">सुधार की जरूरत</span></td>
                                    </tr>
                                    <tr>
                                        <td>औसत रिस्पॉन्स टाइम</td>
                                        <td><?= $report['avg_response_time'] ?? 'N/A' ?></td>
                                        <td>
                                            <span class="badge badge-info">
                                                <i class="fas fa-clock mr-1"></i>
                                                2.5 घंटे
                                            </span>
                                        </td>
                                        <td><span class="text-info">संतोषजनक</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <?php elseif ($reportType == 'source'): ?>
        <!-- Source Report -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-bullhorn mr-2"></i>स्रोत परफॉर्मेंस रिपोर्ट
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th>स्रोत</th>
                                        <th>कुल लीड्स</th>
                                        <th>कन्वर्टेड</th>
                                        <th>कन्वर्शन रेट</th>
                                        <th>औसत बजट</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (isset($report['source_performance']) && !empty($report['source_performance'])): ?>
                                        <?php foreach ($report['source_performance'] as $source): ?>
                                            <tr>
                                                <td>
                                                    <strong><?= h($source['source_name']) ?></strong>
                                                </td>
                                                <td><?= $source['total_leads'] ?></td>
                                                <td><?= $source['converted_leads'] ?></td>
                                                <td>
                                                    <div class="progress">
                                                        <div class="progress-bar bg-success"
                                                             style="width: <?= $source['conversion_rate'] ?>%">
                                                            <?= number_format($source['conversion_rate'], 1) ?>%
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>₹<?= number_format($source['avg_budget'] ?? 0) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center text-muted">
                                                कोई डेटा उपलब्ध नहीं
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <?php elseif ($reportType == 'conversion'): ?>
        <!-- Conversion Report -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-exchange-alt mr-2"></i>कन्वर्शन फनल रिपोर्ट
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-3">
                                <div class="conversion-stage">
                                    <div class="stage-number">1</div>
                                    <h4>नए लीड्स</h4>
                                    <div class="stage-count">
                                        <?= $report['new_leads'] ?? 0 ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="conversion-stage">
                                    <div class="stage-number">2</div>
                                    <h4>संपर्क किए गए</h4>
                                    <div class="stage-count">
                                        <?= $report['contacted_leads'] ?? 0 ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="conversion-stage">
                                    <div class="stage-number">3</div>
                                    <h4>क्वालिफाइड</h4>
                                    <div class="stage-count">
                                        <?= $report['qualified_leads'] ?? 0 ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="conversion-stage">
                                    <div class="stage-number">4</div>
                                    <h4>कन्वर्टेड</h4>
                                    <div class="stage-count">
                                        <?= $report['converted_leads'] ?? 0 ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Date Range Info -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-body text-center">
                    <p class="text-muted mb-0">
                        <i class="fas fa-calendar mr-2"></i>
                        रिपोर्ट पीरियड:
                        <strong><?= date('d M Y', strtotime($dateRange['start'])) ?></strong>
                        से
                        <strong><?= date('d M Y', strtotime($dateRange['end'])) ?></strong>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Status Distribution Chart
<?php if ($reportType == 'summary' && isset($report['status_distribution'])): ?>
const statusCtx = document.getElementById('statusChart').getContext('2d');
new Chart(statusCtx, {
    type: 'doughnut',
    data: {
        labels: <?= json_encode(array_column($report['status_distribution'], 'status')) ?>,
        datasets: [{
            data: <?= json_encode(array_column($report['status_distribution'], 'count')) ?>,
            backgroundColor: [
                '#007bff',
                '#28a745',
                '#ffc107',
                '#dc3545',
                '#6c757d'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});
<?php endif; ?>

// Source Performance Chart
<?php if ($reportType == 'summary' && isset($report['source_performance'])): ?>
const sourceCtx = document.getElementById('sourceChart').getContext('2d');
new Chart(sourceCtx, {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($report['source_performance'], 'source_name')) ?>,
        datasets: [{
            label: 'लीड्स की संख्या',
            data: <?= json_encode(array_column($report['source_performance'], 'total_leads')) ?>,
            backgroundColor: '#007bff'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
<?php endif; ?>
</script>

<style>
.card {
    border-radius: 12px;
    border: none;
    box-shadow: 0 0 20px rgba(0,0,0,0.08);
}

.card-header {
    border-radius: 12px 12px 0 0 !important;
    border-bottom: 2px solid rgba(0,0,0,0.1);
}

.nav-tabs .nav-link {
    border: none;
    color: #6c757d;
    border-radius: 8px 8px 0 0;
}

.nav-tabs .nav-link.active {
    background: #007bff;
    color: white;
}

.conversion-stage {
    padding: 2rem;
    border: 2px solid #e9ecef;
    border-radius: 12px;
    margin: 0 0.5rem;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}

.stage-number {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    font-weight: bold;
    margin: 0 auto 1rem;
}

.stage-count {
    font-size: 2rem;
    font-weight: bold;
    color: #007bff;
}

.table th {
    background-color: #f8f9fa;
    border-top: none;
}

.progress {
    height: 20px;
    border-radius: 10px;
}

.progress-bar {
    border-radius: 10px;
    font-size: 0.8rem;
    font-weight: bold;
}

.badge {
    font-size: 0.85rem;
    padding: 0.5rem 0.75rem;
}

.dropdown-toggle::after {
    margin-left: 0.5em;
}

@media (max-width: 768px) {
    .conversion-stage {
        margin: 1rem 0;
    }

    .stage-number {
        width: 40px;
        height: 40px;
        font-size: 1.2rem;
    }

    .stage-count {
        font-size: 1.5rem;
    }
}
</style>

<?php require_once 'app/views/layouts/footer.php'; ?>
