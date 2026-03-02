<?php require_once 'app/views/layouts/associate_header.php'; ?>

<div class="container-fluid mt-4">
    <!-- Breadcrumb -->
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/associate/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Team Management</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Team Statistics -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-users mr-2"></i>Team Statistics
                    </h6>
                    <div class="btn-group">
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="showView('hierarchy')">
                            <i class="fas fa-sitemap mr-1"></i>Hierarchy View
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="showView('list')">
                            <i class="fas fa-list mr-1"></i>List View
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="h4 mb-0 text-primary font-weight-bold">
                                    <?= $team_stats['total_team_members'] ?? 0 ?>
                                </div>
                                <small class="text-muted">Total Team Members</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="h4 mb-0 text-success font-weight-bold">
                                    <?= $team_stats['direct_members'] ?? 0 ?>
                                </div>
                                <small class="text-muted">Direct Members</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="h4 mb-0 text-info font-weight-bold">
                                    <?= ($team_stats['level_2_members'] ?? 0) + ($team_stats['level_3_members'] ?? 0) ?>
                                </div>
                                <small class="text-muted">Indirect Members</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="h4 mb-0 text-warning font-weight-bold">
                                    <?= $team_stats['level_3_members'] ?? 0 ?>
                                </div>
                                <small class="text-muted">Level 3 Members</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Team Views -->
    <div class="row">
        <div class="col-12">
            <!-- Hierarchy View -->
            <div id="hierarchy-view" class="team-view">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-sitemap mr-2"></i>Team Hierarchy
                        </h6>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($hierarchy)): ?>
                            <div class="hierarchy-tree">
                                <?php echo $this->renderHierarchy($hierarchy); ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No members in your team yet</p>
                                <a href="/associate/recruit" class="btn btn-primary">
                                    <i class="fas fa-user-plus mr-2"></i>Add New Members
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- List View -->
            <div id="list-view" class="team-view" style="display: none;">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-list mr-2"></i>टीम मेंबर्स लिस्ट
                        </h6>
                        <div class="btn-group">
                            <button type="button" class="btn btn-outline-primary btn-sm dropdown-toggle" data-toggle="dropdown">
                                <i class="fas fa-filter mr-1"></i>फिल्टर
                            </button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="#" onclick="filterMembers('all')">सभी</a>
                                <a class="dropdown-item" href="#" onclick="filterMembers('active')">सक्रिय</a>
                                <a class="dropdown-item" href="#" onclick="filterMembers('inactive')">निष्क्रिय</a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($direct_members)): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>मेंबर डिटेल्स</th>
                                            <th>जॉइनिंग डेट</th>
                                            <th>सेल्स</th>
                                            <th>अर्निंग्स</th>
                                            <th>स्टेटस</th>
                                            <th>एक्शन</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($direct_members as $member): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="member-avatar mr-3">
                                                            <span class="badge badge-primary">
                                                                <?= strtoupper(substr($member['name'], 0, 1)) ?>
                                                            </span>
                                                        </div>
                                                        <div>
                                                            <div class="font-weight-bold">
                                                                <?= htmlspecialchars($member['name']) ?>
                                                            </div>
                                                            <small class="text-muted">
                                                                <?= htmlspecialchars($member['email']) ?>
                                                            </small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?= date('d M Y', strtotime($member['joining_date'])) ?>
                                                    <br>
                                                    <small class="text-muted">
                                                        <?= $member['days_in_system'] ?> दिन पहले
                                                    </small>
                                                </td>
                                                <td>
                                                    <span class="badge badge-info">
                                                        <?= $member['total_sales'] ?> सेल्स
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="font-weight-bold text-success">
                                                        ₹<?= number_format($member['total_earnings']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php
                                                    $status = $member['status'] ?? 'active';
                                                    $statusClass = $status === 'active' ? 'success' : 'warning';
                                                    ?>
                                                    <span class="badge badge-<?= $statusClass ?>">
                                                        <?= ucfirst($status) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <button type="button" class="btn btn-outline-primary"
                                                                onclick="viewMember(<?= $member['associate_id'] ?>)">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-outline-info"
                                                                onclick="contactMember(<?= $member['associate_id'] ?>)">
                                                            <i class="fas fa-phone"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                <p class="text-muted">कोई डायरेक्ट मेंबर्स नहीं मिले</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Team Performance Summary -->
    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-bar mr-2"></i>टीम परफॉर्मेंस
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="teamPerformanceChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-trophy mr-2"></i>टॉप परफॉर्मर्स
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($direct_members)): ?>
                        <?php
                        usort($direct_members, function($a, $b) {
                            return $b['total_earnings'] <=> $a['total_earnings'];
                        });
                        $topPerformers = array_slice($direct_members, 0, 5);
                        ?>
                        <?php foreach ($topPerformers as $index => $member): ?>
                            <div class="d-flex align-items-center mb-3">
                                <div class="mr-3">
                                    <span class="badge badge-primary badge-pill">
                                        #<?= $index + 1 ?>
                                    </span>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="font-weight-bold">
                                        <?= htmlspecialchars($member['name']) ?>
                                    </div>
                                    <small class="text-muted">
                                        <?= $member['total_sales'] ?> सेल्स
                                    </small>
                                </div>
                                <div class="text-right">
                                    <div class="font-weight-bold text-success">
                                        ₹<?= number_format($member['total_earnings']) ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-center text-muted mb-0">कोई परफॉर्मेंस डेटा नहीं</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function showView(viewType) {
    // Hide all views
    document.querySelectorAll('.team-view').forEach(view => {
        view.style.display = 'none';
    });

    // Show selected view
    document.getElementById(viewType + '-view').style.display = 'block';

    // Update button states
    document.querySelectorAll('.btn-group .btn').forEach(btn => {
        btn.classList.remove('active');
    });

    if (viewType === 'hierarchy') {
        document.querySelector('[onclick="showView(\'hierarchy\')"]').classList.add('active');
    } else {
        document.querySelector('[onclick="showView(\'list\')"]').classList.add('active');
    }
}

function filterMembers(filter) {
    // This would implement filtering logic
    console.log('Filtering members by:', filter);
}

function viewMember(memberId) {
    // This would open member details modal/page
    console.log('Viewing member:', memberId);
}

function contactMember(memberId) {
    // This would open contact options
    console.log('Contacting member:', memberId);
}

// Team Performance Chart
document.addEventListener('DOMContentLoaded', function() {
    var ctx = document.getElementById('teamPerformanceChart').getContext('2d');

    // Sample data - replace with actual data
    var data = {
        labels: ['जनवरी', 'फ़रवरी', 'मार्च', 'अप्रैल', 'मई', 'जून'],
        datasets: [{
            label: 'टीम सेल्स',
            data: [12000, 19000, 15000, 25000, 22000, 30000],
            backgroundColor: 'rgba(54, 162, 235, 0.2)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
        }]
    };

    new Chart(ctx, {
        type: 'bar',
        data: data,
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
</script>

<style>
.team-view {
    transition: all 0.3s ease;
}

.member-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #007bff;
    color: white;
    font-weight: bold;
}

.hierarchy-tree {
    padding: 20px 0;
}

.hierarchy-node {
    margin: 10px 0;
    padding: 15px;
    border: 1px solid #ddd;
    border-radius: 8px;
    background: #f8f9fa;
}

.hierarchy-node .node-header {
    font-weight: bold;
    color: #007bff;
    margin-bottom: 5px;
}

.hierarchy-node .node-details {
    font-size: 0.9em;
    color: #666;
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

.table th {
    border-top: none;
    font-weight: 600;
    color: #495057;
}

.badge {
    font-size: 0.8em;
}

.btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}
</style>

<?php require_once 'app/views/layouts/associate_footer.php'; ?>
