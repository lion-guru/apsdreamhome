<?php
session_start();
require_once '../includes/config.php';
require_once 'includes/universal_dashboard_template.php';

// Agent/Marketing Partner MLM Dashboard
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// Check if user is agent or admin managing agents
$is_agent = isset($_SESSION['admin_role']) && in_array($_SESSION['admin_role'], ['agent', 'marketing', 'sales']);
$employee = $_SESSION['admin_username'] ?? 'Marketing Partner';

// Database connection already available as $conn

// Handle form submissions for agent operations
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'add_agent':
            try {
                $sponsor_id = !empty($_POST['sponsor_id']) ? $_POST['sponsor_id'] : null;
                $stmt = $conn->prepare("INSERT INTO associates (name, email, mobile, address, sponsor_id, commission_rate, rank_level, join_date, status, created_at) VALUES (?, ?, ?, ?, ?, ?, 1, NOW(), 'active', NOW())");
                $stmt->bind_param("ssssid", 
                    $_POST['agent_name'], 
                    $_POST['agent_email'], 
                    $_POST['agent_mobile'],
                    $_POST['agent_address'],
                    $sponsor_id,
                    $_POST['commission_rate']
                );
                $stmt->execute();
                $message = "New Marketing Partner added successfully!";
            } catch (Exception $e) {
                $error = "Error adding marketing partner: " . $e->getMessage();
            }
            break;
            
        case 'add_sale':
            try {
                $stmt = $conn->prepare("INSERT INTO sales (agent_id, customer_name, property_id, sale_amount, commission_amount, sale_date, status, created_at) VALUES (?, ?, ?, ?, ?, ?, 'completed', NOW())");
                $commission = $_POST['sale_amount'] * ($_POST['commission_rate'] / 100);
                $stmt->bind_param("isidds", 
                    $_POST['agent_id'], 
                    $_POST['customer_name'], 
                    $_POST['property_id'],
                    $_POST['sale_amount'],
                    $commission,
                    $_POST['sale_date']
                );
                $stmt->execute();
                
                // Calculate MLM commissions for upline
                $this->calculateMLMCommissions($_POST['agent_id'], $commission);
                
                $message = "Sale recorded and commissions calculated!";
            } catch (Exception $e) {
                $error = "Error recording sale: " . $e->getMessage();
            }
            break;
            
        case 'process_payout':
            try {
                $stmt = $conn->prepare("UPDATE commission_transactions SET status='paid', paid_date=NOW() WHERE agent_id=? AND status='pending'");
                $stmt->bind_param("i", $_POST['agent_id']);
                $stmt->execute();
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
    $total_agents = $conn->query("SELECT COUNT(*) as count FROM associates WHERE status='active'")->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
    
    // Total sales volume
    $total_sales = $conn->query("SELECT SUM(sale_amount) as total FROM sales WHERE status='completed'")->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    
    // Total commissions paid
    $total_commissions = $conn->query("SELECT SUM(commission_amount) as total FROM commission_transactions WHERE status='paid'")->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    
    // Pending commissions
    $pending_commissions = $conn->query("SELECT SUM(commission_amount) as total FROM commission_transactions WHERE status='pending'")->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    
    // This month sales
    $monthly_sales = $conn->query("SELECT SUM(sale_amount) as total FROM sales WHERE MONTH(sale_date) = MONTH(NOW()) AND YEAR(sale_date) = YEAR(NOW())")->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    
    // Top performers
    $top_performers = $conn->query("SELECT COUNT(*) as count FROM associates WHERE rank_level >= 3")->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
    
} catch (Exception $e) {
    $total_agents = 0;
    $total_sales = 0;
    $total_commissions = 0;
    $pending_commissions = 0;
    $monthly_sales = 0;
    $top_performers = 0;
}

// MLM Dashboard Statistics
$stats = [
    [
        'icon' => 'fas fa-users',
        'value' => $total_agents,
        'label' => 'Active Marketing Partners',
        'change' => '+15 this month',
        'change_type' => 'positive'
    ],
    [
        'icon' => 'fas fa-chart-line',
        'value' => '₹' . number_format($total_sales, 0),
        'label' => 'Total Sales Volume',
        'change' => '+25% growth',
        'change_type' => 'positive'
    ],
    [
        'icon' => 'fas fa-money-bill-wave',
        'value' => '₹' . number_format($total_commissions, 0),
        'label' => 'Commissions Paid',
        'change' => '₹' . number_format($pending_commissions, 0) . ' pending',
        'change_type' => 'pending'
    ],
    [
        'icon' => 'fas fa-calendar-alt',
        'value' => '₹' . number_format($monthly_sales, 0),
        'label' => 'This Month Sales',
        'change' => '+18% vs last month',
        'change_type' => 'positive'
    ],
    [
        'icon' => 'fas fa-crown',
        'value' => $top_performers,
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

// Recent MLM activities
$recent_activities = [
    [
        'title' => 'New High Performer',
        'description' => 'Rajesh Kumar achieved Level 4 - Monthly sales crossed ₹10 Lakhs',
        'time' => '1 hour ago',
        'icon' => 'fas fa-trophy text-warning'
    ],
    [
        'title' => 'Team Expansion',
        'description' => 'Priya Sharma added 5 new partners to her downline this week',
        'time' => '3 hours ago',
        'icon' => 'fas fa-users text-success'
    ],
    [
        'title' => 'Commission Payout',
        'description' => '₹2.5 Lakhs commission distributed to 45 partners for last month',
        'time' => '6 hours ago',
        'icon' => 'fas fa-money-bill-wave text-info'
    ],
    [
        'title' => 'Training Session',
        'description' => 'Monthly sales training completed - 120 partners attended online',
        'time' => '1 day ago',
        'icon' => 'fas fa-graduation-cap text-primary'
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
                    <h2 class="text-success">' . $total_agents . '</h2>
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
                    <h3 class="text-warning">₹' . number_format($monthly_sales/100000, 1) . 'L</h3>
                    <p class="text-muted">This Month</p>
                    <div class="row">
                        <div class="col-6">
                            <small class="text-muted">Target</small>
                            <h6 class="text-primary">₹50L</h6>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Achievement</small>
                            <h6 class="text-success">' . round(($monthly_sales/5000000)*100, 1) . '%</h6>
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
                    <h3 class="text-info">₹' . number_format($total_commissions/100000, 1) . 'L</h3>
                    <p class="text-muted">Total Paid</p>
                    <div class="row">
                        <div class="col-6">
                            <small class="text-muted">Pending</small>
                            <h6 class="text-warning">₹' . number_format($pending_commissions/1000, 0) . 'K</h6>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Partners</small>
                            <h6 class="text-success">' . $total_agents . '</h6>
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
                                    $sponsors = $conn->query("SELECT id, name FROM associates WHERE status='active' ORDER BY name");
                                    while ($sponsor = $sponsors->fetch(PDO::FETCH_ASSOC)) {
                                        echo "<option value='" . $sponsor['id'] . "'>" . htmlspecialchars($sponsor['name']) . "</option>";
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
                <input type="hidden" name="action" value="add_sale">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Marketing Partner *</label>
                            <select class="form-control" name="agent_id" required>
                                <option value="">Select Partner</option>
                                <?php
                                try {
                                    $agents = $conn->query("SELECT id, name FROM associates WHERE status='active' ORDER BY name");
                                    while ($agent = $agents->fetch(PDO::FETCH_ASSOC)) {
                                        echo "<option value='" . $agent['id'] . "'>" . htmlspecialchars($agent['name']) . "</option>";
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
                                    $properties = $conn->query("SELECT id, CONCAT(plot_number, ' - ', area_sqft, ' sq.ft') as property_desc FROM plots WHERE status='available' ORDER BY plot_number");
                                    while ($property = $properties->fetch(PDO::FETCH_ASSOC)) {
                                        echo "<option value='" . $property['id'] . "'>" . htmlspecialchars($property['property_desc']) . "</option>";
                                    }
                                } catch (Exception $e) {
                                    echo "<option value='1'>Sample Property</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Sale Amount (₹) *</label>
                            <input type="number" step="0.01" class="form-control" name="sale_amount" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Commission Rate (%)</label>
                            <input type="number" step="0.01" class="form-control" name="commission_rate" value="10" min="0" max="50">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Sale Date</label>
                            <input type="date" class="form-control" name="sale_date" value="<?php echo date('Y-m-d'); ?>">
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

<!-- Process Payout Modal -->
<div class="modal fade" id="processPayoutModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="fas fa-credit-card me-2"></i>Process Commission Payout</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="process_payout">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Marketing Partner *</label>
                        <select class="form-control" name="agent_id" required>
                            <option value="">Select Partner for Payout</option>
                            <?php
                            try {
                                $pending_agents = $conn->query("SELECT DISTINCT a.id, a.name, SUM(ct.commission_amount) as pending_amount 
                                    FROM associates a 
                                    JOIN commission_transactions ct ON a.id = ct.agent_id 
                                    WHERE ct.status='pending' 
                                    GROUP BY a.id, a.name 
                                    ORDER BY pending_amount DESC");
                                while ($agent = $pending_agents->fetch(PDO::FETCH_ASSOC)) {
                                    echo "<option value='" . $agent['id'] . "'>" . htmlspecialchars($agent['name']) . " (₹" . number_format($agent['pending_amount'], 0) . " pending)</option>";
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
        alert('<?php echo addslashes($message); ?>');
    });
</script>
<?php endif; ?>

<?php if ($error): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        alert('Error: <?php echo addslashes($error); ?>');
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

<?php
// MLM Commission calculation function (would be expanded in real implementation)
function calculateMLMCommissions($agent_id, $commission_amount) {
    // This is a simplified version - full implementation would handle 7-level MLM structure
    // Level 1: 10%, Level 2: 5%, Level 3: 3%, etc.
}
?>