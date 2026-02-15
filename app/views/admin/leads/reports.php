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
                <form class="d-flex align-items-center me-3" method="GET" action="/admin/leads/reports">
                    <div class="input-group">
                        <input type="date"
                               name="start_date"
                               class="form-control"
                               value="<?= htmlspecialchars($_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'))) ?>">
                        <span class="input-group-text">से</span>
                        <input type="date"
                               name="end_date"
                               class="form-control"
                               value="<?= htmlspecialchars($_GET['end_date'] ?? date('Y-m-d')) ?>">
                        <button class="btn btn-outline-secondary" type="submit">
                            <i class="fas fa-filter"></i>
                        </button>
                    </div>
                </form>
                <div class="btn-group">
                    <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="fas fa-download me-1"></i>एक्सपोर्ट
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="/admin/export/leads?type=csv">
                            <i class="fas fa-file-csv me-2"></i>CSV एक्सपोर्ट
                        </a></li>
                        <li><a class="dropdown-item" href="/admin/export/leads?type=excel">
                            <i class="fas fa-file-excel me-2"></i>Excel एक्सपोर्ट
                        </a></li>
                        <li><a class="dropdown-item" href="/admin/export/leads?type=pdf">
                            <i class="fas fa-file-pdf me-2"></i>PDF एक्सपोर्ट
                        </a></li>
                    </ul>
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
                           href="/admin/leads/reports?type=summary&start_date=<?= $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days')) ?>&end_date=<?= $_GET['end_date'] ?? date('Y-m-d') ?>">
                            <i class="fas fa-chart-pie me-1"></i>सारांश रिपोर्ट
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $reportType == 'performance' ? 'active' : '' ?>"
                           href="/admin/leads/reports?type=performance&start_date=<?= $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days')) ?>&end_date=<?= $_GET['end_date'] ?? date('Y-m-d') ?>">
                            <i class="fas fa-chart-line me-1"></i>परफॉर्मेंस रिपोर्ट
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $reportType == 'source' ? 'active' : '' ?>"
                           href="/admin/leads/reports?type=source&start_date=<?= $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days')) ?>&end_date=<?= $_GET['end_date'] ?? date('Y-m-d') ?>">
                            <i class="fas fa-bullhorn me-1"></i>स्रोत रिपोर्ट
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $reportType == 'conversion' ? 'active' : '' ?>"
                           href="/admin/leads/reports?type=conversion&start_date=<?= $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days')) ?>&end_date=<?= $_GET['end_date'] ?? date('Y-m-d') ?>">
                            <i class="fas fa-exchange-alt me-1"></i>कन्वर्शन रिपोर्ट
                        </a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <!-- Report Content -->
                <?php if ($reportType == 'summary'): ?>
                    <!-- Summary Report -->
                    <div class="row">
                        <!-- Key Metrics Cards -->
                        <div class="col-lg-3 col-md-6 mb-4">
                            <div class="card border-start border-primary border-4 shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col me-2">
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
                            <div class="card border-start border-success border-4 shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col me-2">
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
                            <div class="card border-start border-info border-4 shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col me-2">
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
                            <div class="card border-start border-warning border-4 shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col me-2">
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
                        <div class="col-lg-6 mb-4">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        <i class="fas fa-chart-pie me-2"></i>स्टेटस डिस्ट्रिब्यूशन
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container" style="position: relative; height:300px;">
                                        <canvas id="statusChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6 mb-4">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        <i class="fas fa-chart-bar me-2"></i>स्रोत परफॉर्मेंस
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
                                        <i class="fas fa-trophy me-2"></i>परफॉर्मेंस मेट्रिक्स
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover">
                                            <thead class="table-light">
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
                                                        <span class="badge bg-success">
                                                            <i class="fas fa-arrow-up me-1"></i>
                                                            +12%
                                                        </span>
                                                    </td>
                                                    <td><span class="text-success">बढ़िया</span></td>
                                                </tr>
                                                <tr>
                                                    <td>कन्वर्शन रेट</td>
                                                    <td><?= number_format($report['conversion_rate'] ?? 0, 1) ?>%</td>
                                                    <td>
                                                        <span class="badge bg-warning">
                                                            <i class="fas fa-minus me-1"></i>
                                                            -2%
                                                        </span>
                                                    </td>
                                                    <td><span class="text-warning">सुधार की जरूरत</span></td>
                                                </tr>
                                                <tr>
                                                    <td>औसत रिस्पॉन्स टाइम</td>
                                                    <td><?= $report['avg_response_time'] ?? 'N/A' ?></td>
                                                    <td>
                                                        <span class="badge bg-info">
                                                            <i class="fas fa-clock me-1"></i>
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
                                        <i class="fas fa-bullhorn me-2"></i>स्रोत परफॉर्मेंस रिपोर्ट
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover">
                                            <thead class="table-light">
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
                                                                <strong><?= htmlspecialchars($source['source_name']) ?></strong>
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
                                        <i class="fas fa-exchange-alt me-2"></i>कन्वर्शन फनल रिपोर्ट
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-md-3">
                                            <div class="conversion-stage border rounded p-3 mb-3">
                                                <div class="stage-number badge bg-primary rounded-circle mb-2">1</div>
                                                <h4>नए लीड्स</h4>
                                                <div class="stage-count h3">
                                                    <?= $report['new_leads'] ?? 0 ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="conversion-stage border rounded p-3 mb-3">
                                                <div class="stage-number badge bg-info rounded-circle mb-2">2</div>
                                                <h4>संपर्क किए गए</h4>
                                                <div class="stage-count h3">
                                                    <?= $report['contacted_leads'] ?? 0 ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="conversion-stage border rounded p-3 mb-3">
                                                <div class="stage-number badge bg-warning rounded-circle mb-2">3</div>
                                                <h4>क्वालिफाइड</h4>
                                                <div class="stage-count h3">
                                                    <?= $report['qualified_leads'] ?? 0 ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="conversion-stage border rounded p-3 mb-3">
                                                <div class="stage-number badge bg-success rounded-circle mb-2">4</div>
                                                <h4>कन्वर्टेड</h4>
                                                <div class="stage-count h3">
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
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        <?php if ($reportType == 'summary'): ?>
        // Status Chart
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode(array_keys($report['status_distribution'] ?? [])) ?>,
                datasets: [{
                    data: <?= json_encode(array_values($report['status_distribution'] ?? [])) ?>,
                    backgroundColor: [
                        '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b'
                    ],
                    hoverBackgroundColor: [
                        '#2e59d9', '#17a673', '#2c9faf', '#dda20a', '#be2617'
                    ],
                    hoverBorderColor: "rgba(234, 236, 244, 1)",
                }]
            },
            options: {
                maintainAspectRatio: false,
                tooltips: {
                    backgroundColor: "rgb(255,255,255)",
                    bodyFontColor: "#858796",
                    borderColor: '#dddfeb',
                    borderWidth: 1,
                    xPadding: 15,
                    yPadding: 15,
                    displayColors: false,
                    caretPadding: 10,
                },
                legend: {
                    display: true,
                    position: 'bottom'
                },
                cutoutPercentage: 80,
            }
        });

        // Source Chart
        const sourceCtx = document.getElementById('sourceChart').getContext('2d');
        new Chart(sourceCtx, {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_keys($report['source_performance_chart'] ?? [])) ?>,
                datasets: [{
                    label: "लीड्स",
                    backgroundColor: "#4e73df",
                    hoverBackgroundColor: "#2e59d9",
                    borderColor: "#4e73df",
                    data: <?= json_encode(array_values($report['source_performance_chart'] ?? [])) ?>,
                }]
            },
            options: {
                maintainAspectRatio: false,
                layout: {
                    padding: {
                        left: 10,
                        right: 25,
                        top: 25,
                        bottom: 0
                    }
                },
                scales: {
                    xAxes: [{
                        gridLines: {
                            display: false,
                            drawBorder: false
                        },
                        ticks: {
                            maxTicksLimit: 6
                        }
                    }],
                    yAxes: [{
                        ticks: {
                            min: 0,
                            maxTicksLimit: 5,
                            padding: 10
                        },
                        gridLines: {
                            color: "rgb(234, 236, 244)",
                            zeroLineColor: "rgb(234, 236, 244)",
                            drawBorder: false,
                            borderDash: [2],
                            zeroLineBorderDash: [2]
                        }
                    }],
                },
                legend: {
                    display: false
                },
                tooltips: {
                    titleMarginBottom: 10,
                    titleFontColor: '#6e707e',
                    titleFontSize: 14,
                    backgroundColor: "rgb(255,255,255)",
                    bodyFontColor: "#858796",
                    borderColor: '#dddfeb',
                    borderWidth: 1,
                    xPadding: 15,
                    yPadding: 15,
                    displayColors: false,
                    intersect: false,
                    mode: 'index',
                    caretPadding: 10,
                }
            }
        });
        <?php endif; ?>
    });
</script>
