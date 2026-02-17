<?php
/**
 * Inquiry Analytics Template
 * Detailed analytics for property inquiries and agent responses
 */
?>

<!-- Reports Header -->
<section class="reports-header py-4 bg-light">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-md-6">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>admin">Admin</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>admin/reports">Reports</a></li>
                        <li class="breadcrumb-item active">Inquiries</li>
                    </ol>
                </nav>
                <h2 class="mb-0">
                    <i class="fas fa-question-circle text-warning me-2"></i>
                    Inquiry Analytics
                </h2>
            </div>
            <div class="col-md-6 text-md-end">
                <a href="<?php echo BASE_URL; ?>admin/reports/export?type=inquiries&format=csv" class="btn btn-warning text-dark">
                    <i class="fas fa-file-csv me-2"></i>Export CSV
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Filters -->
<section class="report-filters py-4">
    <div class="container-fluid">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Period</label>
                        <select name="period" class="form-select">
                            <option value="7days" <?php echo ($filters['period'] ?? '') === '7days' ? 'selected' : ''; ?>>Last 7 Days</option>
                            <option value="30days" <?php echo ($filters['period'] ?? '') === '30days' ? 'selected' : ''; ?>>Last 30 Days</option>
                            <option value="90days" <?php echo ($filters['period'] ?? '') === '90days' ? 'selected' : ''; ?>>Last 90 Days</option>
                            <option value="1year" <?php echo ($filters['period'] ?? '') === '1year' ? 'selected' : ''; ?>>Last Year</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="all">All Statuses</option>
                            <option value="pending" <?php echo ($filters['status'] ?? '') === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="responded" <?php echo ($filters['status'] ?? '') === 'responded' ? 'selected' : ''; ?>>Responded</option>
                            <option value="closed" <?php echo ($filters['status'] ?? '') === 'closed' ? 'selected' : ''; ?>>Closed</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Priority</label>
                        <select name="priority" class="form-select">
                            <option value="all">All Priorities</option>
                            <option value="high" <?php echo ($filters['priority'] ?? '') === 'high' ? 'selected' : ''; ?>>High</option>
                            <option value="medium" <?php echo ($filters['priority'] ?? '') === 'medium' ? 'selected' : ''; ?>>Medium</option>
                            <option value="low" <?php echo ($filters['priority'] ?? '') === 'low' ? 'selected' : ''; ?>>Low</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter me-2"></i>Apply Filters
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Stats Overview -->
<section class="stats-overview py-4">
    <div class="container-fluid">
        <div class="row g-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm text-center p-3">
                    <h6 class="text-muted">Total Inquiries</h6>
                    <h3 class="mb-0 text-primary"><?php echo number_format($inquiry_stats['total_inquiries'] ?? 0); ?></h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm text-center p-3">
                    <h6 class="text-muted">New (Last 30d)</h6>
                    <h3 class="mb-0 text-success"><?php echo number_format($inquiry_stats['new_inquiries'] ?? 0); ?></h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm text-center p-3">
                    <h6 class="text-muted">Response Rate</h6>
                    <h3 class="mb-0 text-info"><?php echo number_format($inquiry_stats['response_rate'] ?? 0, 1); ?>%</h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm text-center p-3">
                    <h6 class="text-muted">Avg. Response Time</h6>
                    <h3 class="mb-0 text-warning"><?php echo htmlspecialchars($inquiry_stats['avg_response_time'] ?? '0 hours'); ?></h3>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Charts Section -->
<section class="inquiry-charts py-4">
    <div class="container-fluid">
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Inquiry & Response Trends</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="inquiryTrendChart" height="300"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Response Time Distribution</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="responseTimeChart" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Agent Performance -->
<section class="agent-performance py-4">
    <div class="container-fluid">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Agent Inquiry Resolution Performance</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Agent Name</th>
                                <th class="text-center">Total Assigned</th>
                                <th class="text-center">Resolved</th>
                                <th class="text-center">Resolution Rate</th>
                                <th class="text-end">Avg. Response Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($agent_performance)): ?>
                                <?php foreach ($agent_performance as $agent): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($agent['name']); ?></td>
                                        <td class="text-center"><?php echo number_format($agent['total']); ?></td>
                                        <td class="text-center"><?php echo number_format($agent['resolved']); ?></td>
                                        <td class="text-center">
                                            <div class="progress" style="height: 10px;">
                                                <?php $rate = $agent['total'] > 0 ? ($agent['resolved'] / $agent['total']) * 100 : 0; ?>
                                                <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $rate; ?>%" aria-valuenow="<?php echo $rate; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                            <small class="text-muted"><?php echo number_format($rate, 1); ?>%</small>
                                        </td>
                                        <td class="text-end text-muted"><?php echo htmlspecialchars($agent['avg_time']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">No agent performance data found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inquiry Trend Chart
    const trendCtx = document.getElementById('inquiryTrendChart').getContext('2d');
    const trendData = <?php echo json_encode($inquiry_trends ?? []); ?>;
    
    new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: trendData.map(d => d.date),
            datasets: [{
                label: 'Total Inquiries',
                data: trendData.map(d => d.inquiries),
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                fill: true,
                tension: 0.4
            }, {
                label: 'Responses Sent',
                data: trendData.map(d => d.responses),
                borderColor: '#198754',
                backgroundColor: 'rgba(25, 135, 84, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    // Response Time Distribution Chart
    const responseTimeCtx = document.getElementById('responseTimeChart').getContext('2d');
    const responseTimeData = <?php echo json_encode($response_times ?? []); ?>;
    
    new Chart(responseTimeCtx, {
        type: 'bar',
        data: {
            labels: responseTimeData.map(d => d.range),
            datasets: [{
                label: 'Number of Inquiries',
                data: responseTimeData.map(d => d.count),
                backgroundColor: '#ffc107'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y',
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: { beginAtZero: true }
            }
        }
    });
});
</script>
