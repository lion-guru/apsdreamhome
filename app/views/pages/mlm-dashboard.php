<?php
$page_title = 'MLM Dashboard - APS Dream Home';
$page_description = 'Build your network and grow your business';
include __DIR__ . '/../layouts/base.php';
?>

<div class="container-fluid mt-4">
    <!-- MLM Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card mlm-card">
                <div class="card-body text-center">
                    <h1><i class="fas fa-network-wired me-3"></i>MLM Associate Dashboard</h1>
                    <p class="mb-0">Build Your Network, Grow Your Business</p>
                    <div class="mt-3">
                        <span class="level-badge">Level: Gold</span>
                        <span class="level-badge ms-2">Plan: Premium</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Key Metrics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-primary">47</h3>
                    <p class="mb-0">Total Downline</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-success">₹25,000</h3>
                    <p class="mb-0">Monthly Commission</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-info">2.5L</h3>
                    <p class="mb-0">Business Volume</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-warning">32</h3>
                    <p class="mb-0">Active Members</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Commission Details -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-chart-pie me-2"></i>Commission Breakdown</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="text-center">
                                <div class="progress-circle">
                                    <div class="text-white">
                                        <strong>12%</strong>
                                    </div>
                                </div>
                                <h6 class="mt-2">Binary Commission</h6>
                                <p class="text-muted">₹15,000</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <div class="progress-circle">
                                    <div class="text-white">
                                        <strong>10%</strong>
                                    </div>
                                </div>
                                <h6 class="mt-2">Unilevel Commission</h6>
                                <p class="text-muted">₹7,500</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <div class="progress-circle">
                                    <div class="text-white">
                                        <strong>8%</strong>
                                    </div>
                                </div>
                                <h6 class="mt-2">Matrix Commission</h6>
                                <p class="text-muted">₹2,500</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card commission-card">
                <div class="card-header text-white">
                    <h5><i class="fas fa-trophy me-2"></i>Next Rank</h5>
                </div>
                <div class="card-body text-white">
                    <h4>Platinum</h4>
                    <div class="mb-3">
                        <small>Progress to Next Rank</small>
                        <div class="progress">
                            <div class="progress-bar bg-white" style="width: 75%">75%</div>
                        </div>
                    </div>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-users me-2"></i>15 Downline Needed</li>
                        <li><i class="fas fa-chart-line me-2"></i>₹5L Business Volume</li>
                        <li><i class="fas fa-calendar me-2"></i>15 days Remaining</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Network Tree -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-sitemap me-2"></i>Network Tree</h5>
                </div>
                <div class="card-body">
                    <div class="tree-node active text-center">
                        <strong>You (John Doe)</strong><br>
                        <small>Level Gold</small>
                    </div>
                    <div class="row mt-3">
                        <div class="col-6">
                            <div class="tree-node text-center">
                                <strong>Left Team</strong><br>
                                <small>23 members</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="tree-node text-center">
                                <strong>Right Team</strong><br>
                                <small>24 members</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-calendar-check me-2"></i>Recent Activities</h5>
                </div>
                <div class="card-body">
                    <div class="activity-item mb-2">
                        <div class="d-flex justify-content-between">
                            <span>New member joined</span>
                            <small class="text-muted">2 hours ago</small>
                        </div>
                    </div>
                    <div class="activity-item mb-2">
                        <div class="d-flex justify-content-between">
                            <span>Commission earned: ₹5,000</span>
                            <small class="text-muted">5 hours ago</small>
                        </div>
                    </div>
                    <div class="activity-item mb-2">
                        <div class="d-flex justify-content-between">
                            <span>Rank upgraded to Gold</span>
                            <small class="text-muted">1 day ago</small>
                        </div>
                    </div>
                    <div class="activity-item">
                        <div class="d-flex justify-content-between">
                            <span>BV target achieved</span>
                            <small class="text-muted">2 days ago</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payout Schedule -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-money-bill-wave me-2"></i>Payout Schedule</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>2024-03-15</td>
                                    <td>Monthly Commission</td>
                                    <td>₹25,000</td>
                                    <td><span class="badge bg-warning">Pending</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary">View Details</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>2024-02-15</td>
                                    <td>Binary Bonus</td>
                                    <td>₹3,000</td>
                                    <td><span class="badge bg-success">Paid</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary">View Details</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.mlm-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.level-badge {
    background: rgba(255,255,255,0.2);
    border-radius: 20px;
    padding: 5px 15px;
    font-size: 0.9em;
}

.progress-circle {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background: conic-gradient(#28a745 0deg, #ffc107 270deg, #dc3545 360deg);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
}

.tree-node {
    border: 2px solid #007bff;
    border-radius: 10px;
    padding: 10px;
    margin: 10px;
    background: #f8f9fa;
}

.tree-node.active {
    background: #e3f2fd;
    border-color: #2196f3;
}

.commission-card {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
}

.activity-item {
    padding: 10px;
    border-left: 3px solid #007bff;
    background: #f8f9fa;
    margin-bottom: 10px;
}
</style>
