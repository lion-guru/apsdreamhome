<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Network Dashboard - APS Dream Homes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .dashboard-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            padding: 2rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .referral-link {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 10px;
            padding: 1rem;
            font-family: monospace;
            word-break: break-all;
        }
        .qr-code {
            text-align: center;
            padding: 1rem;
            background: white;
            border-radius: 10px;
            display: inline-block;
        }
        .network-tree {
            overflow-x: auto;
        }
        .node {
            display: inline-block;
            margin: 10px;
            padding: 10px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            min-width: 150px;
            text-align: center;
        }
        .level-1 { border-left: 4px solid #667eea; }
        .level-2 { border-left: 4px solid #28a745; }
        .level-3 { border-left: 4px solid #ffc107; }
        .level-4 { border-left: 4px solid #dc3545; }
        .level-5 { border-left: 4px solid #6f42c1; }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="row">
            <div class="col-12">
                <h1 class="text-white text-center mb-4">
                    <i class="fas fa-network-wired me-2"></i>
                    MLM Network Dashboard
                </h1>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="stat-card">
                    <h3 id="direct-referrals">0</h3>
                    <p>Direct Referrals</p>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stat-card">
                    <h3 id="total-team">0</h3>
                    <p>Total Team</p>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stat-card">
                    <h3 id="total-commission">₹0</h3>
                    <p>Total Commission</p>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stat-card">
                    <h3 id="pending-commission">₹0</h3>
                    <p>Pending Commission</p>
                </div>
            </div>
        </div>

        <!-- Rank Insight -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="dashboard-card bg-gradient" style="background: linear-gradient(135deg, #ff9966, #ff5e62); color:white;">
                    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center">
                        <div class="mb-3 mb-lg-0">
                            <div class="rank-badge" id="rank-badge">
                                <i class="fas fa-trophy"></i>
                                <span id="rank-label">Rank: Associate</span>
                            </div>
                            <p class="mb-0" id="rank-reward" style="opacity:0.85;">Reward: Mobile</p>
                            <small id="plan-mode" class="opacity-75"></small>
                        </div>
                        <div class="flex-grow-1 ms-lg-4 w-100">
                            <span id="next-rank-label" class="fw-semibold"></span>
                            <div class="progress mt-2">
                                <div id="rank-progress" class="progress-bar" role="progressbar" style="width: 0%;" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <small id="rank-target" class="opacity-75"></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Referral Tools -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="dashboard-card">
                    <h5><i class="fas fa-link me-2"></i>Your Referral Link</h5>
                    <div class="referral-link mb-3" id="referral-link">
                        Loading...
                    </div>
                    <button class="btn btn-primary me-2" onclick="copyReferralLink()">
                        <i class="fas fa-copy me-1"></i>Copy
                    </button>
                    <button class="btn btn-success" onclick="shareReferral()">
                        <i class="fas fa-share me-1"></i>Share
                    </button>
                </div>
            </div>
            <div class="col-md-6">
                <div class="dashboard-card">
                    <h5><i class="fas fa-qrcode me-2"></i>QR Code</h5>
                    <div class="qr-code" id="qr-code">
                        Loading...
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="dashboard-card">
                    <h5><i class="fas fa-chart-bar me-2"></i>Referrals by Level</h5>
                    <canvas id="levelChart" width="400" height="200"></canvas>
                </div>
            </div>
            <div class="col-md-6">
                <div class="dashboard-card">
                    <h5><i class="fas fa-chart-line me-2"></i>Recent Referrals</h5>
                    <canvas id="timelineChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- Network Tree -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="dashboard-card">
                    <h5><i class="fas fa-sitemap me-2"></i>Network Tree</h5>
                    <div class="search-toolbar">
                        <div class="input-group" style="max-width:320px;">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control" id="tree-search" placeholder="Search name or email">
                        </div>
                        <div>
                            <select class="form-select" id="rank-filter" style="min-width:180px;">
                                <option value="">All Ranks</option>
                            </select>
                        </div>
                    </div>
                    <div class="network-tree" id="network-tree">
                        Loading...
                    </div>
                </div>
            </div>
        </div>

        <!-- Direct Referrals -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="dashboard-card">
                    <h5><i class="fas fa-users me-2"></i>Direct Referrals</h5>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Type</th>
                                    <th>Email</th>
                                    <th>Mobile</th>
                                    <th>Joined</th>
                                </tr>
                            </thead>
                            <tbody id="direct-referrals-table">
                                <!-- Data will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script>
        // Load dashboard data
        async function loadDashboard() {
            try {
                const response = await fetch('<?= BASE_URL ?>api/network/dashboard');
                const data = await response.json();
                
                if (data.success) {
                    updateStats(data.data);
                    updateCharts(data.data);
                    updateNetworkTree(data.data);
                    updateDirectReferrals(data.data);
                }
            } catch (error) {
                console.error('Error loading dashboard:', error);
            }
        }

        function updateStats(data) {
            document.getElementById('direct-referrals').textContent = data.stats.direct_referrals;
            document.getElementById('total-team').textContent = data.stats.total_team;
            document.getElementById('total-commission').textContent = '₹' + data.stats.total_commission.toLocaleString();
            document.getElementById('pending-commission').textContent = '₹' + data.stats.pending_commission.toLocaleString();
            
            document.getElementById('referral-link').textContent = data.stats.referral_link;
            document.getElementById('qr-code').innerHTML = `<img src="${data.stats.qr_code}" alt="QR Code" class="img-fluid">`;

            if (data.stats.rank) {
                const rank = data.stats.rank;
                const badge = document.getElementById('rank-badge');
                badge.style.background = rank.color;
                document.getElementById('rank-label').textContent = `Rank: ${rank.current_label}`;
                document.getElementById('rank-reward').textContent = `Reward: ${rank.reward}`;
                document.getElementById('plan-mode').textContent = `Plan: ${data.stats.plan_mode === 'custom' ? 'Custom Agreement' : 'Standard Rank'}`;

                const progressBar = document.getElementById('rank-progress');
                progressBar.style.width = `${rank.progress_percent}%`;
                progressBar.style.background = rank.color;

                if (rank.next) {
                    document.getElementById('next-rank-label').textContent = `Progress to ${rank.next.label}`;
                    const needed = Math.max(rank.next.required - rank.business, 0);
                    document.getElementById('rank-target').textContent = `Need ₹${needed.toLocaleString()} more business for ${rank.next.label} (${rank.next.reward}).`;
                } else {
                    document.getElementById('next-rank-label').textContent = 'You have reached the top rank!';
                    document.getElementById('rank-target').textContent = '';
                }
            }
        }

        function updateCharts(data) {
            // Level chart
            const levelCtx = document.getElementById('levelChart').getContext('2d');
            new Chart(levelCtx, {
                type: 'doughnut',
                data: {
                    labels: data.stats.level_breakdown.map(item => `Level ${item.level}`),
                    datasets: [{
                        data: data.stats.level_breakdown.map(item => item.count),
                        backgroundColor: ['#667eea', '#28a745', '#ffc107', '#dc3545', '#6f42c1']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });

            // Timeline chart
            const timelineCtx = document.getElementById('timelineChart').getContext('2d');
            new Chart(timelineCtx, {
                type: 'line',
                data: {
                    labels: data.analytics.map(item => item.date),
                    datasets: [{
                        label: 'Referrals',
                        data: data.analytics.map(item => item.referrals),
                        borderColor: '#667eea',
                        backgroundColor: 'rgba(102, 126, 234, 0.1)',
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }

        function updateNetworkTree(data) {
            const treeDiv = document.getElementById('network-tree');
            
            if (data.network_tree.length === 0) {
                treeDiv.innerHTML = '<p class="text-center text-muted">No network members yet. Start referring!</p>';
                return;
            }

            let treeHTML = '<div class="text-center">';
            let currentLevel = 1;
            
            data.network_tree.forEach(member => {
                if (member.level !== currentLevel) {
                    treeHTML += '<br>';
                    currentLevel = member.level;
                }
                
                treeHTML += `
                    <div class="node level-${member.level}" data-rank="${member.rank_label}" data-name="${member.name.toLowerCase()}" data-email="${member.email?.toLowerCase() ?? ''}">
                        <strong>${member.name}</strong><br>
                        <small>${member.type}</small><br>
                        <small>Level ${member.level}</small>
                        <div class="node-rank" style="background:${member.rank_color || '#667eea'};">
                            ${member.rank_label || 'Associate'}
                        </div>
                    </div>
                `;
            });
            
            treeHTML += '</div>';
            treeDiv.innerHTML = treeHTML;

            populateRankFilter(data.network_tree);
            attachSearchHandlers();
        }

        function updateDirectReferrals(data) {
            const tableBody = document.getElementById('direct-referrals-table');
            
            if (data.direct_referrals.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="5" class="text-center">No direct referrals yet</td></tr>';
                return;
            }

            tableBody.innerHTML = data.direct_referrals.map(ref => `
                <tr>
                    <td>${ref.name}</td>
                    <td>${ref.type}</td>
                    <td>${ref.email}</td>
                    <td>${ref.mobile}</td>
                    <td>${new Date(ref.created_at).toLocaleDateString()}</td>
                </tr>
            `).join('');
        }

        function copyReferralLink() {
            const link = document.getElementById('referral-link').textContent;
            navigator.clipboard.writeText(link).then(() => {
                alert('Referral link copied to clipboard!');
            });
        }

        function shareReferral() {
            const link = document.getElementById('referral-link').textContent;
            if (navigator.share) {
                navigator.share({
                    title: 'Join APS Dream Homes',
                    text: 'Join my MLM network at APS Dream Homes!',
                    url: link
                });
            } else {
                window.open(`https://wa.me/?text=${encodeURIComponent('Join my MLM network: ' + link)}`);
            }
        }

        // Load dashboard on page load
        loadDashboard();
    </script>
</body>
</html>