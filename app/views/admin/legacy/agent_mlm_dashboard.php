<?php
require_once __DIR__ . '/core/init.php';

// Check if user is agent or admin managing agents
adminAccessControl(['agent', 'marketing', 'sales', 'super_admin', 'superadmin']);

use App\Core\Database;

$is_agent = in_array(getAuthSubRole(), ['agent', 'marketing', 'sales']);
$employee = getAuthUsername() ?? 'Marketing Partner';

// Database connection
try {
    $db = \App\Core\App::database();
    $associateModel = new Associate($db);
} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Handle form submissions for agent operations
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Token Validation
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        die('Invalid CSRF token. Action blocked.');
    }

    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'add_agent':
            try {
                $sponsor_id = !empty($_POST['sponsor_id']) ? $_POST['sponsor_id'] : null;
                $email = $_POST['agent_email'];

                // Check if user exists
                $user = $db->fetch("SELECT uid FROM user WHERE uemail = :email", ['email' => $email]);

                if (!$user) {
                    // Create basic user account first
                    $password = \password_hash('DreamHome123!', PASSWORD_DEFAULT);
                    $username = \explode('@', $email)[0] . \App\Helpers\SecurityHelper::secureRandomInt(100, 999);
                    $db->execute(
                        "INSERT INTO user (uname, uemail, upass, utype, job_role, ustatus, join_date) VALUES (:uname, :uemail, :upass, 'associate', 'agent', 'active', NOW())",
                        [
                            'uname' => $username,
                            'uemail' => $email,
                            'upass' => $password
                        ]
                    );
                    $user_id = $db->lastInsertId();
                } else {
                    $user_id = $user['uid'];
                }

                // Create associate record
                $associateData = [
                    'user_id' => $user_id,
                    'sponsor_user_id' => $sponsor_id,
                    'name' => $_POST['agent_name'],
                    'phone' => $_POST['agent_mobile']
                ];

                $result = $associateModel->create($associateData);

                if ($result['success']) {
                    // Create MLM profile
                    $db->execute(
                        "INSERT INTO mlm_profiles (user_id, referral_code, sponsor_user_id, user_type, current_level, status, created_at) VALUES (:user_id, :referral_code, :sponsor_user_id, 'associate', '1', 'active', NOW())",
                        [
                            'user_id' => $user_id,
                            'referral_code' => $result['uid'],
                            'sponsor_user_id' => $sponsor_id
                        ]
                    );

                    $message = "New Marketing Partner added successfully! UID: " . $result['uid'];
                } else {
                    $error = $result['message'];
                }
            } catch (Exception $e) {
                $error = "Error adding marketing partner: " . $e->getMessage();
            }
            break;

        case 'add_sale':
            try {
                $agent_id = $_POST['agent_id']; // This is associate_id
                $sale_amount = $_POST['sale_amount'];
                $property_id = $_POST['property_id'];
                $customer_name = $_POST['customer_name'];

                // Get user_id for this associate
                $associate = $db->fetch("SELECT user_id FROM associates WHERE id = :id", ['id' => $agent_id]);

                if (!$associate) {
                    throw new Exception("Associate not found.");
                }

                $user_id = $associate['user_id'];

                // Try to find or create customer
                $customer = $db->fetch("SELECT uid FROM user WHERE uname = :uname AND utype = 'customer'", ['uname' => $customer_name]);

                if (!$customer) {
                    // Create a dummy customer for this sale if not exists
                    $password = \password_hash('Customer123!', PASSWORD_DEFAULT);
                    $email = \strtolower(\str_replace(' ', '.', $customer_name)) . \App\Helpers\SecurityHelper::secureRandomInt(100, 999) . "@example.com";
                    $db->execute(
                        "INSERT INTO user (uname, uemail, utype, job_role, ustatus, join_date) VALUES (:uname, :uemail, 'customer', 'customer', 'active', NOW())",
                        [
                            'uname' => $customer_name,
                            'uemail' => $email
                        ]
                    );
                    $customer_id = $db->lastInsertId();
                } else {
                    $customer_id = $customer['uid'];
                }

                // Record in bookings
                $db->execute(
                    "INSERT INTO bookings (property_id, customer_id, booking_date, status, amount, created_at) VALUES (:property_id, :customer_id, :booking_date, 'confirmed', :amount, NOW())",
                    [
                        'property_id' => $property_id,
                        'customer_id' => $customer_id,
                        'booking_date' => $_POST['sale_date'],
                        'amount' => $sale_amount
                    ]
                );
                $booking_id = $db->lastInsertId();

                // Update property status to sold or booked
                $db->execute("UPDATE properties SET status = 'sold' WHERE id = :id", ['id' => $property_id]);

                // Calculate and record commissions using the Associate model
                $associateModel->processSaleCommissions($user_id, $sale_amount, $booking_id);

                $message = "Sale recorded, property status updated, and commissions distributed up to 7 levels!";
            } catch (Exception $e) {
                $error = "Error recording sale: " . $e->getMessage();
            }
            break;

        case 'process_payout':
            try {
                $db->execute(
                    "UPDATE commission_transactions SET status='paid' WHERE associate_id = :associate_id AND status='pending'",
                    ['associate_id' => $_POST['agent_id']]
                );

                // Update lifetime sales and paid commission in mlm_profiles
                $paidResult = $db->fetch(
                    "SELECT SUM(commission_amount) as total FROM commission_transactions WHERE associate_id = :associate_id AND status='paid'",
                    ['associate_id' => $_POST['agent_id']]
                );
                $paid = $paidResult['total'] ?? 0;

                $db->execute(
                    "UPDATE mlm_profiles m JOIN associates a ON m.user_id = a.user_id SET m.total_commission = :paid, m.pending_commission = 0 WHERE a.id = :id",
                    [
                        'paid' => $paid,
                        'id' => $_POST['agent_id']
                    ]
                );

                $message = "Commission payout processed successfully!";
            } catch (Exception $e) {
                $error = "Error processing payout: " . $e->getMessage();
            }
            break;
    }
}

// Get MLM statistics
try {
    // Total agents/partners
    $total_agents_result = $db->fetch("SELECT COUNT(*) as count FROM associates WHERE status='active'");
    $total_agents = $total_agents_result['count'] ?? 0;

    // Total sales volume (from bookings)
    $total_sales_result = $db->fetch("SELECT SUM(amount) as total FROM bookings WHERE status='confirmed'");
    $total_sales = $total_sales_result['total'] ?? 0;

    // Total commissions paid
    $total_commissions_result = $db->fetch("SELECT SUM(commission_amount) as total FROM commission_transactions WHERE status='paid'");
    $total_commissions = $total_commissions_result['total'] ?? 0;

    // Pending commissions
    $pending_commissions_result = $db->fetch("SELECT SUM(commission_amount) as total FROM commission_transactions WHERE status='pending'");
    $pending_commissions = $pending_commissions_result['total'] ?? 0;

    // This month sales
    $monthly_sales_result = $db->fetch("SELECT SUM(amount) as total FROM bookings WHERE status='confirmed' AND MONTH(booking_date) = MONTH(NOW()) AND YEAR(booking_date) = YEAR(NOW())");
    $monthly_sales = $monthly_sales_result['total'] ?? 0;

    // Top performers (Level 3+)
    $top_performers_result = $db->fetch("SELECT COUNT(*) as count FROM mlm_profiles WHERE current_level IN ('Gold', 'Platinum', 'Diamond', 'Crown', 'Ambassador')");
    $top_performers = $top_performers_result['count'] ?? 0;
} catch (Exception $e) {
    $total_agents = 0;
    $total_sales = 0;
    $total_commissions = 0;
    $pending_commissions = 0;
    $monthly_sales = 0;
    $top_performers = 0;
}

// Recent MLM activities
try {
    $recent_activities = [];

    // 1. New agent registrations
    $new_agents = $db->fetchAll("
        SELECT u.uname as name, m.created_at, m.referral_code
        FROM user u
        JOIN mlm_profiles m ON u.uid = m.user_id
        ORDER BY m.created_at DESC LIMIT 5
    ");

    foreach ($new_agents as $agent) {
        $recent_activities[] = [
            'title' => 'New Partner Joined',
            'description' => h($agent['name'] ?? 'Unknown') . ' joined as a marketing partner (UID: ' . h($agent['referral_code'] ?? 'N/A') . ')',
            'time' => date('j M, g:i a', strtotime($agent['created_at'] ?? 'now')),
            'icon' => 'fas fa-user-plus text-success'
        ];
    }

    // 2. Recent commissions
    $recent_commissions = $db->fetchAll("
        SELECT u.uname as name, c.commission_amount, c.transaction_date
        FROM commission_transactions c
        JOIN associates a ON c.associate_id = a.id
        JOIN user u ON a.user_id = u.uid
        ORDER BY c.transaction_date DESC LIMIT 5
    ");

    foreach ($recent_commissions as $comm) {
        $recent_activities[] = [
            'title' => 'Commission Earned',
            'description' => h($comm['name'] ?? 'Unknown') . ' earned ₹' . number_format($comm['commission_amount'] ?? 0, 2) . ' commission',
            'time' => date('j M, g:i a', strtotime($comm['transaction_date'] ?? 'now')),
            'icon' => 'fas fa-money-bill-wave text-info'
        ];
    }

    // If no activities, add placeholders
    if (empty($recent_activities)) {
        $recent_activities = [
            [
                'title' => 'No Recent Activity',
                'description' => 'Start building your network to see activities here.',
                'time' => 'Just now',
                'icon' => 'fas fa-info-circle text-secondary'
            ]
        ];
    } else {
        // Sort by time (descending)
        usort($recent_activities, function ($a, $b) {
            return strtotime($b['time']) - strtotime($a['time']);
        });
    }
} catch (Exception $e) {
    $recent_activities = [
        [
            'title' => 'Activity Error',
            'description' => 'Could not load recent activities.',
            'time' => 'Error',
            'icon' => 'fas fa-exclamation-triangle text-danger'
        ]
    ];
}

// MLM Dashboard Statistics
$stats = [
    [
        'icon' => 'fas fa-users',
        'value' => h($total_agents),
        'label' => 'Active Marketing Partners',
        'change' => '+15 this month',
        'change_type' => 'positive'
    ],
    [
        'icon' => 'fas fa-chart-line',
        'value' => '₹' . h(number_format($total_sales, 0)),
        'label' => 'Total Sales Volume',
        'change' => '+25% growth',
        'change_type' => 'positive'
    ],
    [
        'icon' => 'fas fa-money-bill-wave',
        'value' => '₹' . h(number_format($total_commissions, 0)),
        'label' => 'Commissions Paid',
        'change' => '₹' . h(number_format($pending_commissions, 0)) . ' pending',
        'change_type' => 'pending'
    ],
    [
        'icon' => 'fas fa-calendar-alt',
        'value' => '₹' . h(number_format($monthly_sales, 0)),
        'label' => 'This Month Sales',
        'change' => '+18% vs last month',
        'change_type' => 'positive'
    ],
    [
        'icon' => 'fas fa-crown',
        'value' => h($top_performers),
        'label' => 'Top Performers',
        'change' => 'Level 3+ achievers',
        'change_type' => 'positive'
    ],
    [
        'icon' => 'fas fa-network-wired',
        'value' => '7',
        'label' => 'MLM Levels Active',
        'change' => 'Multi-tier commission',
        'change_type' => 'positive'
    ]
];

// Agent MLM Quick Actions
$quick_actions = [
    [
        'title' => 'Add New Partner',
        'icon' => 'fas fa-user-plus',
        'url' => '#',
        'color' => 'success',
        'modal' => 'addAgentModal'
    ],
    [
        'title' => 'Record Sale',
        'icon' => 'fas fa-handshake',
        'url' => '#',
        'color' => 'warning',
        'modal' => 'addSaleModal'
    ],
    [
        'title' => 'Team Tree View',
        'icon' => 'fas fa-sitemap',
        'url' => 'associates_tree_view.php',
        'color' => 'info'
    ],
    [
        'title' => 'Commission Reports',
        'icon' => 'fas fa-chart-pie',
        'url' => 'commission_reports.php',
        'color' => 'primary'
    ],
    [
        'title' => 'Process Payouts',
        'icon' => 'fas fa-credit-card',
        'url' => '#',
        'color' => 'danger',
        'modal' => 'processPayoutModal'
    ],
    [
        'title' => 'Training Materials',
        'icon' => 'fas fa-graduation-cap',
        'url' => 'training_materials.php',
        'color' => 'secondary'
    ]
];

// Custom content with MLM management features
$custom_content = '
<div class="row mt-4">
    <div class="col-12">
        <div class="alert alert-info border-0" style="background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);">
            <div class="d-flex align-items-center">
                <i class="fas fa-network-wired fa-2x text-info me-3"></i>
                <div>
                    <h5 class="alert-heading mb-1">MLM Marketing Partner Network</h5>
                    <p class="mb-0">Manage your multi-level marketing team, track commissions, and grow your network. Build teams, earn commissions, and achieve success together!</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MLM Performance Dashboard -->
<div class="row mt-4">
    <div class="col-lg-4 mb-4">
        <div class="card h-100">
            <div class="card-header bg-success text-white">
                <h5 class="card-title mb-0"><i class="fas fa-users me-2"></i>Team Building</h5>
            </div>
            <div class="card-body">
                <div class="text-center">
                    <h2 class="text-success">' . h($total_agents) . '</h2>
                    <p class="text-muted">Active Partners</p>
                    <div class="progress mb-3">
                        <div class="progress-bar bg-success" style="width: 75%"></div>
                    </div>
                    <small class="text-muted">Target: 500 Partners</small>
                </div>
                <hr>
                <button class="btn btn-success w-100" data-bs-toggle="modal" data-bs-target="#addAgentModal">
                    <i class="fas fa-plus me-2"></i>Add Partner
                </button>
            </div>
        </div>
    </div>

    <div class="col-lg-4 mb-4">
        <div class="card h-100">
            <div class="card-header bg-warning text-dark">
                <h5 class="card-title mb-0"><i class="fas fa-chart-line me-2"></i>Sales Performance</h5>
            </div>
            <div class="card-body">
                <div class="text-center">
                    <h3 class="text-warning">₹' . h(number_format($monthly_sales / 100000, 1)) . 'L</h3>
                    <p class="text-muted">This Month</p>
                    <div class="row">
                        <div class="col-6">
                            <small class="text-muted">Target</small>
                            <h6 class="text-primary">₹50L</h6>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Achievement</small>
                            <h6 class="text-success">' . h(round(($monthly_sales / 5000000) * 100, 1)) . '%</h6>
                        </div>
                    </div>
                </div>
                <hr>
                <button class="btn btn-warning w-100" data-bs-toggle="modal" data-bs-target="#addSaleModal">
                    <i class="fas fa-plus me-2"></i>Record Sale
                </button>
            </div>
        </div>
    </div>

    <div class="col-lg-4 mb-4">
        <div class="card h-100">
            <div class="card-header bg-info text-white">
                <h5 class="card-title mb-0"><i class="fas fa-money-bill-wave me-2"></i>Commission Tracking</h5>
            </div>
            <div class="card-body">
                <div class="text-center">
                    <h3 class="text-info">₹' . h(number_format($total_commissions / 100000, 1)) . 'L</h3>
                    <p class="text-muted">Total Paid</p>
                    <div class="row">
                        <div class="col-6">
                            <small class="text-muted">Pending</small>
                            <h6 class="text-warning">₹' . h(number_format($pending_commissions / 1000, 0)) . 'K</h6>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Partners</small>
                            <h6 class="text-success">' . h($total_agents) . '</h6>
                        </div>
                    </div>
                </div>
                <hr>
                <button class="btn btn-info w-100" data-bs-toggle="modal" data-bs-target="#processPayoutModal">
                    <i class="fas fa-credit-card me-2"></i>Process Payout
                </button>
            </div>
        </div>
    </div>
</div>

<!-- MLM Commission Structure -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0"><i class="fas fa-layer-group me-2"></i>7-Level MLM Commission Structure</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3 col-6 mb-3">
                        <div class="p-3 border rounded bg-light">
                            <h4 class="text-success">Level 1</h4>
                            <p class="mb-1"><strong>10%</strong> Direct Commission</p>
                            <small class="text-muted">Your direct referrals</small>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-3">
                        <div class="p-3 border rounded bg-light">
                            <h4 class="text-primary">Level 2</h4>
                            <p class="mb-1"><strong>5%</strong> Level Commission</p>
                            <small class="text-muted">2nd level downline</small>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-3">
                        <div class="p-3 border rounded bg-light">
                            <h4 class="text-info">Level 3</h4>
                            <p class="mb-1"><strong>3%</strong> Level Commission</p>
                            <small class="text-muted">3rd level downline</small>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-3">
                        <div class="p-3 border rounded bg-light">
                            <h4 class="text-warning">Level 4</h4>
                            <p class="mb-1"><strong>2%</strong> Level Commission</p>
                            <small class="text-muted">4th level downline</small>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-3">
                        <div class="p-3 border rounded bg-light">
                            <h4 class="text-secondary">Level 5</h4>
                            <p class="mb-1"><strong>1%</strong> Level Commission</p>
                            <small class="text-muted">5th level downline</small>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-3">
                        <div class="p-3 border rounded bg-light">
                            <h4 class="text-danger">Level 6</h4>
                            <p class="mb-1"><strong>0.5%</strong> Level Commission</p>
                            <small class="text-muted">6th level downline</small>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-3">
                        <div class="p-3 border rounded bg-light">
                            <h4 class="text-dark">Level 7</h4>
                            <p class="mb-1"><strong>0.25%</strong> Level Commission</p>
                            <small class="text-muted">7th level downline</small>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-3">
                        <div class="p-3 border rounded" style="background: linear-gradient(135deg, #ffd700, #ffed4e);">
                            <h4 class="text-dark">Bonus</h4>
                            <p class="mb-1"><strong>2%</strong> Performance Bonus</p>
                            <small class="text-muted">Monthly achievers</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>';

// Generate the dashboard
echo generateUniversalDashboard('marketing', $stats, $quick_actions, $recent_activities, $custom_content);
?>

<!-- Add Agent Modal -->
<div class="modal fade" id="addAgentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>Add New Marketing Partner</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <?php echo getCsrfField(); ?>
                <input type="hidden" name="action" value="add_agent">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Partner Name *</label>
                            <input type="text" class="form-control" name="agent_name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email Address *</label>
                            <input type="email" class="form-control" name="agent_email" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Mobile Number *</label>
                            <input type="tel" class="form-control" name="agent_mobile" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Commission Rate (%)</label>
                            <input type="number" step="0.01" class="form-control" name="commission_rate" value="10" min="0" max="50">
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Address</label>
                            <textarea class="form-control" name="agent_address" rows="2"></textarea>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Sponsor/Upline Partner (Optional)</label>
                            <select class="form-control" name="sponsor_id">
                                <option value="">Select Sponsor (Optional)</option>
                                <?php
                                try {
                                    $sponsors = $db->fetchAll("SELECT id, name FROM associates WHERE status = :status ORDER BY name", ['status' => 'active']);
                                    foreach ($sponsors as $sponsor) {
                                        echo "<option value='" . h($sponsor['id']) . "'>" . h($sponsor['name']) . "</option>";
                                    }
                                } catch (Exception $e) {
                                    echo "<option value=''>No sponsors available</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Add Partner</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Sale Modal -->
<div class="modal fade" id="addSaleModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title"><i class="fas fa-handshake me-2"></i>Record New Sale</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <?php echo getCsrfField(); ?>
                <input type="hidden" name="action" value="add_sale">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Marketing Partner *</label>
                            <select class="form-control" name="agent_id" required>
                                <option value="">Select Partner</option>
                                <?php
                                try {
                                    $agents = $db->fetchAll("SELECT id, name FROM associates WHERE status = :status ORDER BY name", ['status' => 'active']);
                                    foreach ($agents as $agent) {
                                        echo "<option value='" . h($agent['id']) . "'>" . h($agent['name']) . "</option>";
                                    }
                                } catch (Exception $e) {
                                    echo "<option value=''>No partners found</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Customer Name *</label>
                            <input type="text" class="form-control" name="customer_name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Property/Plot *</label>
                            <select class="form-control" name="property_id" required>
                                <option value="">Select Property</option>
                                <?php
                                try {
                                    $properties = $db->fetchAll("SELECT id, title, price FROM properties WHERE status = :status ORDER BY title", ['status' => 'available']);
                                    foreach ($properties as $prop) {
                                        echo "<option value='" . h($prop['id']) . "' data-price='" . h($prop['price']) . "'>" . h($prop['title']) . " (₹" . h(number_format($prop['price'], 0)) . ")</option>";
                                    }
                                } catch (Exception $e) {
                                    echo "<option value=''>No properties available</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Sale Amount (₹) *</label>
                            <input type="number" class="form-control" name="sale_amount" id="sale_amount" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Sale Date *</label>
                            <input type="date" class="form-control" name="sale_date" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Record Sale</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const propertySelect = document.querySelector('select[name="property_id"]');
        const saleAmountInput = document.getElementById('sale_amount');

        if (propertySelect && saleAmountInput) {
            propertySelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                if (selectedOption.dataset.price) {
                    saleAmountInput.value = selectedOption.dataset.price;
                }
            });
        }
    });
</script>

<!-- Process Payout Modal -->
<div class="modal fade" id="processPayoutModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="fas fa-credit-card me-2"></i>Process Commission Payout</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <?php echo getCsrfField(); ?>
                <input type="hidden" name="action" value="process_payout">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Marketing Partner *</label>
                        <select class="form-control" name="agent_id" required>
                            <option value="">Select Partner for Payout</option>
                            <?php
                            try {
                                $pending_agents = $db->fetchAll("SELECT DISTINCT a.id, a.name, SUM(ct.commission_amount) as pending_amount
                                    FROM associates a
                                    JOIN commission_transactions ct ON a.id = ct.associate_id
                                    WHERE ct.status = :status
                                    GROUP BY a.id, a.name
                                    ORDER BY pending_amount DESC", ['status' => 'pending']);
                                foreach ($pending_agents as $agent) {
                                    echo "<option value='" . h($agent['id']) . "'>" . h($agent['name']) . " (₹" . h(number_format($agent['pending_amount'] ?? 0, 0)) . " pending)</option>";
                                }
                            } catch (Exception $e) {
                                echo "<option value=''>No pending payouts</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        This will mark all pending commissions as paid for the selected partner.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info">Process Payout</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php if ($message): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            alert('<?php echo h(addslashes($message)); ?>');
        });
    </script>
<?php endif; ?>

<?php if ($error): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            alert('Error: <?php echo h(addslashes($error)); ?>');
        });
    </script>
<?php endif; ?>

<style>
    .dashboard-marketing .card {
        border: 2px solid #fd7e14;
        box-shadow: 0 4px 12px rgba(253, 126, 20, 0.15);
    }

    .progress-bar {
        transition: width 0.3s ease;
    }

    .border {
        border-width: 2px !important;
    }

    .bg-light {
        background-color: #f8f9fa !important;
    }
</style>

?>