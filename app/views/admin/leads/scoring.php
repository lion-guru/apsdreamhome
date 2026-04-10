<?php
/**
 * Lead Scoring Dashboard
 * Admin view for lead scoring and management
 */

$page_title = 'Lead Scoring Dashboard - APS Dream Home';
?>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-2"><i class="fas fa-chart-line me-2"></i>Lead Scoring Dashboard</h1>
            <p class="text-muted">AI-powered lead scoring and prioritization</p>
        </div>
        <div class="btn-group">
            <a href="/admin/leads/scoring/recalculate" class="btn btn-primary">
                <i class="fas fa-sync me-2"></i>Recalculate All Scores
            </a>
            <a href="/admin/leads/scoring/export?<?= http_build_query($_GET) ?>" class="btn btn-success">
                <i class="fas fa-download me-2"></i>Export
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title mb-0">Hot Leads</h6>
                            <h2 class="mb-0"><?= $score_distribution['hot_count'] ?? 0 ?></h2>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-fire fa-2x opacity-50"></i>
                        </div>
                    </div>
                    <small>Score 70-100</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title mb-0">Warm Leads</h6>
                            <h2 class="mb-0"><?= $score_distribution['warm_count'] ?? 0 ?></h2>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-user-clock fa-2x opacity-50"></i>
                        </div>
                    </div>
                    <small>Score 40-69</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-secondary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title mb-0">Cold Leads</h6>
                            <h2 class="mb-0"><?= $score_distribution['cold_count'] ?? 0 ?></h2>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-snowflake fa-2x opacity-50"></i>
                        </div>
                    </div>
                    <small>Score 0-39</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title mb-0">Avg Score</h6>
                            <h2 class="mb-0"><?= round($stats['avg_score'] ?? 0) ?></h2>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-calculator fa-2x opacity-50"></i>
                        </div>
                    </div>
                    <small>Of <?= $stats['total_scored'] ?? 0 ?> leads</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filters</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="/admin/leads/scoring" class="row g-3">
                <div class="col-md-2">
                    <label class="form-label">Min Score</label>
                    <input type="number" class="form-control" name="score_min" value="<?= $filters['score_min'] ?>" min="0" max="100">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Max Score</label>
                    <input type="number" class="form-control" name="score_max" value="<?= $filters['score_max'] ?>" min="0" max="100">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select class="form-select" name="status">
                        <option value="">All</option>
                        <option value="new" <?= $filters['status'] == 'new' ? 'selected' : '' ?>>New</option>
                        <option value="contacted" <?= $filters['status'] == 'contacted' ? 'selected' : '' ?>>Contacted</option>
                        <option value="qualified" <?= $filters['status'] == 'qualified' ? 'selected' : '' ?>>Qualified</option>
                        <option value="proposal" <?= $filters['status'] == 'proposal' ? 'selected' : '' ?>>Proposal</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Assigned To</label>
                    <select class="form-select" name="assigned_to">
                        <option value="">All Agents</option>
                        <?php foreach ($agents as $agent): ?>
                        <option value="<?= $agent['id'] ?>" <?= $filters['assigned_to'] == $agent['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars(agent['name'] ?? '') ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search me-2"></i>Apply
                    </button>
                    <a href="/admin/leads/scoring" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Score Distribution Bar -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Score Distribution</h5>
        </div>
        <div class="card-body">
            <div class="progress" style="height: 30px;">
                <?php
                $total = ($score_distribution['hot_count'] ?? 0) + ($score_distribution['warm_count'] ?? 0) + ($score_distribution['cold_count'] ?? 0);
                if ($total > 0):
                    $hotPct = ($score_distribution['hot_count'] / $total) * 100;
                    $warmPct = ($score_distribution['warm_count'] / $total) * 100;
                    $coldPct = ($score_distribution['cold_count'] / $total) * 100;
                ?>
                <div class="progress-bar bg-success" style="width: <?= $hotPct ?>%" title="Hot: <?= $score_distribution['hot_count'] ?>">
                    <?= round($hotPct) ?>%
                </div>
                <div class="progress-bar bg-warning" style="width: <?= $warmPct ?>%" title="Warm: <?= $score_distribution['warm_count'] ?>">
                    <?= round($warmPct) ?>%
                </div>
                <div class="progress-bar bg-secondary" style="width: <?= $coldPct ?>%" title="Cold: <?= $score_distribution['cold_count'] ?>">
                    <?= round($coldPct) ?>%
                </div>
                <?php else: ?>
                <div class="progress-bar bg-light text-dark" style="width: 100%">No data</div>
                <?php endif; ?>
            </div>
            <div class="d-flex justify-content-center mt-2">
                <span class="badge bg-success me-2">Hot (70-100)</span>
                <span class="badge bg-warning me-2">Warm (40-69)</span>
                <span class="badge bg-secondary">Cold (0-39)</span>
            </div>
        </div>
    </div>

    <!-- Leads Table -->
    <div class="card">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-users me-2"></i>Leads (<?= count($leads) ?>)</h5>
            <div class="btn-group btn-group-sm">
                <button type="button" class="btn btn-outline-primary" onclick="filterByScore('hot')">Hot Only</button>
                <button type="button" class="btn btn-outline-warning" onclick="filterByScore('warm')">Warm Only</button>
                <button type="button" class="btn btn-outline-secondary" onclick="filterByScore('cold')">Cold Only</button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Score</th>
                            <th>Lead</th>
                            <th>Contact</th>
                            <th>Interest</th>
                            <th>Source</th>
                            <th>Assigned To</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($leads as $lead): 
                            $scoreClass = $lead['score'] >= 70 ? 'success' : ($lead['score'] >= 40 ? 'warning' : 'secondary');
                            $scoreLabel = $lead['score'] >= 70 ? 'Hot' : ($lead['score'] >= 40 ? 'Warm' : 'Cold');
                        ?>
                        <tr data-score="<?= $lead['score'] ?>">
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="me-2">
                                        <span class="badge bg-<?= $scoreClass ?> fs-6"><?= round($lead['score']) ?></span>
                                    </div>
                                    <div class="progress flex-grow-1" style="width: 60px; height: 8px;">
                                        <div class="progress-bar bg-<?= $scoreClass ?>" style="width: <?= $lead['score'] ?>"></div>
                                    </div>
                                </div>
                                <small class="text-muted"><?= $scoreLabel ?></small>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars(lead['name'] ?? '') ?></strong>
                                <br><small class="text-muted">#<?= $lead['id'] ?></small>
                            </td>
                            <td>
                                <i class="fas fa-envelope me-1 text-muted"></i><?= htmlspecialchars(lead['email'] ?? '') ?>
                                <br><i class="fas fa-phone me-1 text-muted"></i><?= htmlspecialchars(lead['phone'] ?? '') ?>
                            </td>
                            <td>
                                <?= $lead['property_interest'] ? ucfirst($lead['property_interest']) : '-' ?>
                                <?php if ($lead['budget']): ?>
                                <br><small class="text-success">₹<?= number_format(floatval(lead['budget'] ?? 0)) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark"><?= ucfirst($lead['source']) ?></span>
                            </td>
                            <td>
                                <?= $lead['assigned_name'] ? htmlspecialchars(lead['assigned_name'] ?? '') : '<span class="text-muted">Unassigned</span>' ?>
                            </td>
                            <td>
                                <span class="badge bg-<?= getLeadStatusColor($lead['status']) ?>">
                                    <?= ucfirst($lead['status']) ?>
                                </span>
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-info" onclick="viewScoreDetails(<?= $lead['id'] ?>)">
                                    <i class="fas fa-chart-pie"></i> Score
                                </button>
                                <a href="/admin/leads/<?= $lead['id'] ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Score Details Modal -->
<div class="modal fade" id="scoreModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Lead Score Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="scoreModalBody">
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function filterByScore(type) {
    const rows = document.querySelectorAll('tbody tr');
    rows.forEach(row => {
        const score = parseInt(row.getAttribute('data-score'));
        let show = false;
        
        if (type === 'hot' && score >= 70) show = true;
        else if (type === 'warm' && score >= 40 && score < 70) show = true;
        else if (type === 'cold' && score < 40) show = true;
        
        row.style.display = show ? '' : 'none';
    });
}

function viewScoreDetails(leadId) {
    const modal = new bootstrap.Modal(document.getElementById('scoreModal'));
    modal.show();
    
    fetch(`/api/leads/${leadId}/score-details`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                let html = `
                    <div class="text-center mb-4">
                        <h2 class="display-4 text-primary">${data.lead.score}</h2>
                        <p class="text-muted">Overall Score</p>
                    </div>
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Factor</th>
                                <th>Score</th>
                                <th>Weight</th>
                            </tr>
                        </thead>
                        <tbody>
                `;
                
                for (const [key, value] of Object.entries(data.score_breakdown)) {
                    html += `
                        <tr>
                            <td>${value.factor}</td>
                            <td>
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar" style="width: ${value.percentage}%">${Math.round(value.score)}/${value.max}</div>
                                </div>
                            </td>
                            <td>${Math.round((value.max/100)*100)}%</td>
                        </tr>
                    `;
                }
                
                html += '</tbody></table>';
                document.getElementById('scoreModalBody').innerHTML = html;
            }
        })
        .catch(error => {
            document.getElementById('scoreModalBody').innerHTML = '<div class="alert alert-danger">Failed to load score details</div>';
        });
}
</script>

<?php
function getLeadStatusColor($status) {
    $colors = [
        'new' => 'info',
        'contacted' => 'warning',
        'qualified' => 'success',
        'proposal' => 'primary',
        'negotiation' => 'dark',
        'converted' => 'success',
        'lost' => 'secondary'
    ];
    return $colors[$status] ?? 'light';
}

?>
