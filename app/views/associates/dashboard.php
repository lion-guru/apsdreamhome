<?php require_once 'app/views/layouts/associate_header.php'; ?>

<div class="container-fluid mt-4">
    <!-- Welcome Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-gradient-primary text-white">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="card-title mb-2">स्वागत है, <?= htmlspecialchars($associate['name']) ?>!</h4>
                            <p class="card-text mb-2">आपका असोसिएट कोड: <strong><?= htmlspecialchars($associate['associate_code']) ?></strong></p>
                            <p class="card-text">लेवल: <strong><?= htmlspecialchars($associate['level']) ?></strong> | कुल अर्निंग्स: <strong>₹<?= number_format($stats['personal']['total_sales_value'] ?? 0) ?></strong></p>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="rank-badge">
                                <?php
                                $rank = $rank_info['current_rank'];
                                $rankColors = [
                                    'Bronze' => 'bronze',
                                    'Silver' => 'silver',
                                    'Gold' => 'warning',
                                    'Diamond' => 'info'
                                ];
                                $color = $rankColors[$rank] ?? 'secondary';
                                ?>
                                <span class="badge badge-<?= $color ?> p-2">
                                    <i class="fas fa-crown mr-1"></i><?= $rank ?> Member
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">पर्सनल सेल्स</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ₹<?= number_format($stats['personal']['total_sales_value'] ?? 0) ?>
                            </div>
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
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">टीम मेंबर्स</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= $stats['team']['total_team_members'] ?? 0 ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">टोटल अर्निंग्स</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ₹<?= number_format($commission_summary['total_commissions'] ?? 0) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">पेंडिंग पेआउट्स</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ₹<?= number_format($pending_payouts[0]['amount'] ?? 0) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts and Recent Activity -->
    <div class="row">
        <!-- Monthly Performance Chart -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">मासिक परफॉर्मेंस</h6>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="monthlyPerformanceChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Commissions -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">हाल की कमिशन</h6>
                    <a href="/associate/earnings" class="btn btn-sm btn-outline-primary">सभी देखें</a>
                </div>
                <div class="card-body">
                    <?php if (!empty($recent_commissions)): ?>
                        <?php foreach ($recent_commissions as $commission): ?>
                            <div class="d-flex align-items-center mb-3">
                                <div class="mr-3">
                                    <div class="icon-circle bg-success">
                                        <i class="fas fa-rupee-sign text-white"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="small font-weight-bold text-gray-900">
                                        ₹<?= number_format($commission['commission_amount']) ?>
                                    </div>
                                    <div class="small text-gray-500">
                                        <?= htmlspecialchars($commission['property_title']) ?>
                                    </div>
                                </div>
                                <div class="text-xs text-gray-500">
                                    <?= date('M d', strtotime($commission['sale_date'])) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-center text-gray-500 mb-0">कोई कमिशन नहीं मिली</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Rank Progress -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">रैंक प्रोग्रेस</h6>
                </div>
                <div class="card-body">
                    <?php
                    $currentRank = $rank_info['current_rank'];
                    $nextRank = $rank_info['next_rank'];
                    $personalSales = $rank_info['personal_sales'];
                    $teamMembers = $rank_info['team_members'];

                    $personalProgress = min(($personalSales / $nextRank['personal_sales']) * 100, 100);
                    $teamProgress = min(($teamMembers / $nextRank['team_members']) * 100, 100);
                    ?>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <small>पर्सनल सेल्स प्रोग्रेस</small>
                            <small><?= number_format($personalProgress, 1) ?>%</small>
                        </div>
                        <div class="progress mb-2">
                            <div class="progress-bar bg-info" role="progressbar"
                                 style="width: <?= $personalProgress ?>%"></div>
                        </div>
                        <small class="text-muted">
                            ₹<?= number_format($personalSales) ?> / ₹<?= number_format($nextRank['personal_sales']) ?>
                        </small>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <small>टीम मेंबर्स प्रोग्रेस</small>
                            <small><?= number_format($teamProgress, 1) ?>%</small>
                        </div>
                        <div class="progress mb-2">
                            <div class="progress-bar bg-success" role="progressbar"
                                 style="width: <?= $teamProgress ?>%"></div>
                        </div>
                        <small class="text-muted">
                            <?= $teamMembers ?> / <?= $nextRank['team_members'] ?> मेंबर्स
                        </small>
                    </div>

                    <div class="text-center">
                        <small class="text-muted">नेक्स्ट रैंक: <strong><?= $nextRank['rank'] ?></strong></small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">क्विक एक्शन</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <a href="/associate/team" class="btn btn-outline-primary btn-block">
                                <i class="fas fa-users fa-2x mb-2"></i>
                                <br>टीम मैनेजमेंट
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="/associate/business" class="btn btn-outline-success btn-block">
                                <i class="fas fa-chart-bar fa-2x mb-2"></i>
                                <br>बिजनेस ओवरव्यू
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="/associate/earnings" class="btn btn-outline-info btn-block">
                                <i class="fas fa-money-bill-wave fa-2x mb-2"></i>
                                <br>अर्निंग्स
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="/associate/payouts" class="btn btn-outline-warning btn-block">
                                <i class="fas fa-credit-card fa-2x mb-2"></i>
                                <br>पेआउट्स
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js Script -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Monthly Performance Chart
    var ctx = document.getElementById('monthlyPerformanceChart').getContext('2d');
    var monthlyData = <?= json_encode($monthly_trends ?? []) ?>;

    var labels = [];
    var salesData = [];

    monthlyData.forEach(function(item) {
        labels.push(item.month);
        salesData.push(parseFloat(item.sales_value));
    });

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'मासिक सेल्स (₹)',
                data: salesData,
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1,
                fill: true
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
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                }
            }
        }
    });
});
</script>

<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.rank-badge .badge {
    font-size: 1.1em;
    padding: 0.5em 1em;
}

.icon-circle {
    height: 2.5rem;
    width: 2.5rem;
    border-radius: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.card {
    border-radius: 10px;
    border: none;
    box-shadow: 0 0 20px rgba(0,0,0,0.08);
}

.card-header {
    border-radius: 10px 10px 0 0 !important;
    border-bottom: 2px solid rgba(0,0,0,0.1);
}

.btn-block {
    height: 100px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    transition: all 0.3s ease;
}

.btn-block:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.progress {
    height: 8px;
    border-radius: 4px;
}

.progress-bar {
    border-radius: 4px;
}

.text-gray-900 {
    color: #212529 !important;
}
</style>

<?php require_once 'app/views/layouts/associate_footer.php'; ?>
