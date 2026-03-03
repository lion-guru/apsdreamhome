<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1>Security Dashboard</h1>
            <p class="text-muted">Monitor and manage security threats</p>
        </div>
    </div>
    
    <!-- Security Overview Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h5 class="card-title">Critical Threats</h5>
                    <h2><?= $criticalThreats ?></h2>
                    <small>Last 24 hours</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5 class="card-title">High Threats</h5>
                    <h2><?= $highThreats ?></h2>
                    <small>Last 24 hours</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Failed Logins</h5>
                    <h2><?= $failedLogins ?></h2>
                    <small>Last 24 hours</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Blocked IPs</h5>
                    <h2><?= $blockedIPs ?></h2>
                    <small>Currently blocked</small>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Active Threats Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Active Security Threats</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Severity</th>
                                    <th>IP Address</th>
                                    <th>User</th>
                                    <th>Description</th>
                                    <th>Detected</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($threats as $threat): ?>
                                <tr>
                                    <td>
                                        <span class="badge bg-<?= getSeverityColor($threat['severity']) ?>">
                                            <?= $threat['type'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= getSeverityColor($threat['severity']) ?>">
                                            <?= ucfirst($threat['severity']) ?>
                                        </span>
                                    </td>
                                    <td><?= $threat['ip_address'] ?></td>
                                    <td><?= $threat['user_email'] ?? 'N/A' ?></td>
                                    <td><?= getThreatDescription($threat) ?></td>
                                    <td><?= formatDate($threat['created_at']) ?></td>
                                    <td>
                                        <div class="btn-group">
                                            <button class="btn btn-sm btn-primary" onclick="investigateThreat(<?= $threat['id'] ?>)">
                                                Investigate
                                            </button>
                                            <button class="btn btn-sm btn-warning" onclick="resolveThreat(<?= $threat['id'] ?>)">
                                                Resolve
                                            </button>
                                            <button class="btn btn-sm btn-danger" onclick="blockIP('<?= $threat['ip_address'] ?>')">
                                                Block IP
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Security Charts -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Threat Trends</h5>
                </div>
                <div class="card-body">
                    <canvas id="threatTrendsChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Threat Types</h5>
                </div>
                <div class="card-body">
                    <canvas id="threatTypesChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Security dashboard JavaScript
function investigateThreat(threatId) {
    window.open('/admin/security/threats/' + threatId, '_blank');
}

function resolveThreat(threatId) {
    if (confirm('Are you sure you want to resolve this threat?')) {
        fetch('/admin/security/threats/' + threatId + '/resolve', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                resolution: 'Manually resolved by admin'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    }
}

function blockIP(ipAddress) {
    if (confirm('Are you sure you want to block this IP address?')) {
        fetch('/admin/security/block-ip', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                ip_address: ipAddress,
                duration: 24 // hours
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('IP address blocked successfully');
                location.reload();
            }
        });
    }
}

// Initialize charts
document.addEventListener('DOMContentLoaded', function() {
    // Threat trends chart
    const threatTrendsCtx = document.getElementById('threatTrendsChart').getContext('2d');
    new Chart(threatTrendsCtx, {
        type: 'line',
        data: {
            labels: <?= json_encode($trendLabels) ?>,
            datasets: [{
                label: 'Threats',
                data: <?= json_encode($trendData) ?>,
                borderColor: 'rgb(255, 99, 132)',
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
    
    // Threat types chart
    const threatTypesCtx = document.getElementById('threatTypesChart').getContext('2d');
    new Chart(threatTypesCtx, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode($typeLabels) ?>,
            datasets: [{
                data: <?= json_encode($typeData) ?>,
                backgroundColor: [
                    '#FF6384',
                    '#36A2EB',
                    '#FFCE56',
                    '#4BC0C0',
                    '#9966FF'
                ]
            }]
        },
        options: {
            responsive: true
        }
    });
});
</script>

<?php
function getSeverityColor($severity) {
    switch ($severity) {
        case 'critical': return 'danger';
        case 'high': return 'warning';
        case 'medium': return 'info';
        case 'low': return 'secondary';
        default: return 'secondary';
    }
}

function getThreatDescription($threat) {
    $data = json_decode($threat['threat_data'], true);
    
    switch ($threat['type']) {
        case 'brute_force':
            return "Brute force attack detected. {$data['attempts']} failed attempts.";
        case 'credential_stuffing':
            return "Credential stuffing attack. {$data['unique_emails']} unique emails targeted.";
        case 'unusual_location':
            return "Login from unusual location: {$data['current_location']['country']}";
        case 'unusual_time':
            return "Login at unusual time: {$data['current_hour']}:00";
        case 'api_abuse':
            return "API abuse detected. {$data['requests_per_minute']} requests per minute.";
        default:
            return "Security threat detected";
    }
}
?>
