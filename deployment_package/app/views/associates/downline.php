<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Downline Management'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .member-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
        }
        .member-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        .level-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 20px;
            padding: 5px 12px;
            font-size: 0.8em;
        }
        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8em;
        }
        .status-active { background: #d4edda; color: #155724; }
        .status-inactive { background: #f8d7da; color: #721c24; }
        .search-box {
            background: #f8f9fa;
            border: none;
            border-radius: 25px;
            padding: 15px 20px;
        }
        .filter-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .stats-widget {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/layouts/associate_header.php'; ?>

    <div class="container-fluid mt-4">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2><i class="fas fa-users me-2"></i>Downline Management</h2>
                        <p class="text-muted mb-0">Manage and track your network members</p>
                    </div>
                    <div>
                        <a href="<?php echo BASE_URL; ?>associate/genealogy" class="btn btn-outline-primary">
                            <i class="fas fa-project-diagram me-2"></i>View Genealogy
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Widgets -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stats-widget">
                    <i class="fas fa-users fa-2x mb-2"></i>
                    <h3><?php echo $stats['overall']['total_downline'] ?? 0; ?></h3>
                    <p class="mb-0">Total Members</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-widget">
                    <i class="fas fa-user-plus fa-2x mb-2"></i>
                    <h3><?php echo $stats['overall']['level_1'] ?? 0; ?></h3>
                    <p class="mb-0">Direct Referrals</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-widget">
                    <i class="fas fa-chart-line fa-2x mb-2"></i>
                    <h3><?php echo $stats['overall']['level_2'] ?? 0; ?></h3>
                    <p class="mb-0">Level 2 Members</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-widget">
                    <i class="fas fa-coins fa-2x mb-2"></i>
                    <h3>₹<?php echo number_format($stats['overall']['total_earnings'] ?? 0); ?></h3>
                    <p class="mb-0">Network Earnings</p>
                </div>
            </div>
        </div>

        <!-- Filters and Search -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="filter-card p-3">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <input type="text" class="form-control search-box" placeholder="Search members by name, email, city...">
                        </div>
                        <div class="col-md-2">
                            <select class="form-select">
                                <option>All Levels</option>
                                <option>Level 1</option>
                                <option>Level 2</option>
                                <option>Level 3</option>
                                <option>Level 4+</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select">
                                <option>All Status</option>
                                <option>Active</option>
                                <option>Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select">
                                <option>All Positions</option>
                                <option>Left Leg</option>
                                <option>Right Leg</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-primary w-100">
                                <i class="fas fa-search me-2"></i>Filter
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Downline Members -->
        <div class="row">
            <?php if (!empty($downline)): ?>
                <?php foreach ($downline as $level => $members): ?>
                    <div class="col-12 mb-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">
                                    <i class="fas fa-layer-group me-2"></i>
                                    Level <?php echo $level; ?> Members
                                    <span class="badge bg-primary ms-2"><?php echo count($members); ?></span>
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <?php foreach ($members as $member): ?>
                                        <div class="col-md-4 mb-3">
                                            <div class="card member-card h-100 position-relative">
                                                <div class="level-badge">L<?php echo $member['level']; ?></div>
                                                <div class="card-body">
                                                    <div class="text-center mb-3">
                                                        <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                                            <i class="fas fa-user fa-2x text-primary"></i>
                                                        </div>
                                                    </div>
                                                    <h6 class="card-title text-center mb-2">
                                                        <?php echo htmlspecialchars($member['name'] ?? 'Unknown'); ?>
                                                    </h6>
                                                    <div class="text-center mb-3">
                                                        <span class="status-badge status-<?php echo strtolower($member['status'] ?? 'active'); ?>">
                                                            <?php echo ucfirst($member['status'] ?? 'Active'); ?>
                                                        </span>
                                                    </div>
                                                    <div class="member-info text-sm">
                                                        <p class="mb-1">
                                                            <i class="fas fa-envelope me-2"></i>
                                                            <?php echo htmlspecialchars($member['email'] ?? ''); ?>
                                                        </p>
                                                        <p class="mb-1">
                                                            <i class="fas fa-phone me-2"></i>
                                                            <?php echo htmlspecialchars($member['phone'] ?? ''); ?>
                                                        </p>
                                                        <p class="mb-1">
                                                            <i class="fas fa-map-marker-alt me-2"></i>
                                                            <?php echo htmlspecialchars(($member['city'] ?? '') . ', ' . ($member['state'] ?? '')); ?>
                                                        </p>
                                                        <p class="mb-1">
                                                            <i class="fas fa-calendar me-2"></i>
                                                            Joined: <?php echo date('M d, Y', strtotime($member['joining_date'])); ?>
                                                        </p>
                                                        <p class="mb-1">
                                                            <i class="fas fa-coins me-2"></i>
                                                            Earnings: ₹<?php echo number_format($member['total_commission'] ?? 0); ?>
                                                        </p>
                                                    </div>
                                                    <div class="text-center mt-3">
                                                        <button class="btn btn-outline-primary btn-sm me-2" onclick="viewMember(<?php echo $member['user_id']; ?>)">
                                                            <i class="fas fa-eye me-1"></i>View
                                                        </button>
                                                        <button class="btn btn-outline-success btn-sm" onclick="contactMember(<?php echo $member['user_id']; ?>)">
                                                            <i class="fas fa-message me-1"></i>Contact
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                            <h4 class="text-muted">No Downline Members Yet</h4>
                            <p class="text-muted">Start building your network by referring new associates!</p>
                            <a href="<?php echo BASE_URL; ?>referrals" class="btn btn-primary">
                                <i class="fas fa-user-plus me-2"></i>Start Referring
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Monthly Growth Chart -->
        <?php if (!empty($stats['monthly'])): ?>
        <div class="row mt-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Monthly Growth</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Month</th>
                                        <th>New Joins</th>
                                        <th>Monthly Earnings</th>
                                        <th>Growth Rate</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($stats['monthly'] as $monthly): ?>
                                        <tr>
                                            <td><?php echo date('F Y', strtotime($monthly['month'] . '-01')); ?></td>
                                            <td><?php echo $monthly['new_joins']; ?></td>
                                            <td>₹<?php echo number_format($monthly['monthly_earnings']); ?></td>
                                            <td>
                                                <?php
                                                $growth_rate = 0; // Calculate growth rate
                                                echo '<span class="text-success">+' . $growth_rate . '%</span>';
                                                ?>
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
        <?php endif; ?>

        <!-- Performance Tips -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-lightbulb me-2"></i>Performance Tips</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6><i class="fas fa-bullseye text-success me-2"></i>Build Balanced Teams</h6>
                                <p class="text-muted">Focus on developing both legs of your downline for maximum growth and stability.</p>
                            </div>
                            <div class="col-md-6">
                                <h6><i class="fas fa-handshake text-primary me-2"></i>Support Your Team</h6>
                                <p class="text-muted">Regular communication and support help your downline members succeed.</p>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <h6><i class="fas fa-chart-line text-warning me-2"></i>Track Progress</h6>
                                <p class="text-muted">Monitor your team's growth and commission earnings regularly.</p>
                            </div>
                            <div class="col-md-6">
                                <h6><i class="fas fa-trophy text-danger me-2"></i>Set Goals</h6>
                                <p class="text-muted">Set achievable targets for recruitment and team development.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Member Details Modal -->
    <div class="modal fade" id="memberModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Member Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="memberDetails">
                    <!-- Details will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function viewMember(memberId) {
            // Load member details
            document.getElementById('memberDetails').innerHTML = `
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading member details...</p>
                </div>
            `;
            new bootstrap.Modal(document.getElementById('memberModal')).show();

            // In real implementation, make AJAX call to get member details
            setTimeout(() => {
                document.getElementById('memberDetails').innerHTML = `
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Personal Information</h6>
                            <p><strong>Name:</strong> John Doe</p>
                            <p><strong>Email:</strong> john@example.com</p>
                            <p><strong>Phone:</strong> +91-9876543210</p>
                            <p><strong>Location:</strong> Mumbai, Maharashtra</p>
                        </div>
                        <div class="col-md-6">
                            <h6>MLM Information</h6>
                            <p><strong>Level:</strong> 2</p>
                            <p><strong>Joining Date:</strong> Jan 15, 2024</p>
                            <p><strong>Total Earnings:</strong> ₹25,000</p>
                            <p><strong>Team Size:</strong> 8 members</p>
                        </div>
                    </div>
                `;
            }, 1000);
        }

        function contactMember(memberId) {
            // Open contact modal or redirect to messaging
            alert('Contact functionality would be implemented here');
        }

        // Search functionality
        document.querySelector('.search-box').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const memberCards = document.querySelectorAll('.member-card');

            memberCards.forEach(card => {
                const name = card.querySelector('.card-title').textContent.toLowerCase();
                const email = card.querySelector('.member-info').textContent.toLowerCase();

                if (name.includes(searchTerm) || email.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>
