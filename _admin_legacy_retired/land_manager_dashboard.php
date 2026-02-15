<?php
session_start();
require_once 'config.php';
require_once 'includes/universal_dashboard_template.php';

// Company Owner Land Management - Ultimate Land Control
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

$_SESSION['admin_role'] = 'company_owner';
$employee = $_SESSION['admin_username'] ?? 'Company Owner';

// Use the correct connection variable
$conn = $con ?? $conn;

// Handle form submissions for land operations
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'add_farmer':
            try {
                $stmt = $conn->prepare("INSERT INTO farmers (name, mobile, address, aadhar_number, pan_number, bank_account, ifsc_code, land_size, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
                $stmt->bind_param("sssssssd", 
                    $_POST['farmer_name'], 
                    $_POST['farmer_mobile'], 
                    $_POST['farmer_address'],
                    $_POST['aadhar_number'],
                    $_POST['pan_number'],
                    $_POST['bank_account'],
                    $_POST['ifsc_code'],
                    $_POST['land_size']
                );
                $stmt->execute();
                $message = "Farmer added successfully!";
            } catch (Exception $e) {
                $error = "Error adding farmer: " . $e->getMessage();
            }
            break;
            
        case 'add_land_purchase':
            try {
                $stmt = $conn->prepare("INSERT INTO land_purchases (farmer_id, location, area_sqft, purchase_price, paid_amount, due_amount, purchase_date, registry_status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
                $due_amount = $_POST['purchase_price'] - $_POST['paid_amount'];
                $stmt->bind_param("isddddss", 
                    $_POST['farmer_id'], 
                    $_POST['location'], 
                    $_POST['area_sqft'],
                    $_POST['purchase_price'],
                    $_POST['paid_amount'],
                    $due_amount,
                    $_POST['purchase_date'],
                    $_POST['registry_status']
                );
                $stmt->execute();
                $message = "Land purchase recorded successfully!";
            } catch (Exception $e) {
                $error = "Error recording land purchase: " . $e->getMessage();
            }
            break;
            
        case 'add_plot_development':
            try {
                $stmt = $conn->prepare("INSERT INTO plots (project_id, plot_number, area_sqft, plot_price, status, created_at) VALUES (?, ?, ?, ?, 'available', NOW())");
                $stmt->bind_param("isdd", 
                    $_POST['project_id'], 
                    $_POST['plot_number'], 
                    $_POST['plot_area'],
                    $_POST['plot_price']
                );
                $stmt->execute();
                $message = "Plot created successfully!";
            } catch (Exception $e) {
                $error = "Error creating plot: " . $e->getMessage();
            }
            break;
    }
}

// Get land management statistics
try {
    // Total farmers
    $total_farmers = $conn->query("SELECT COUNT(*) as count FROM farmers")->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
    
    // Total land area purchased
    $total_land_area = $conn->query("SELECT SUM(area_sqft) as total FROM land_purchases")->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    
    // Total land investment
    $total_investment = $conn->query("SELECT SUM(purchase_price) as total FROM land_purchases")->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    
    // Total plots developed
    $total_plots = $conn->query("SELECT COUNT(*) as count FROM plots")->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
    
    // Available plots
    $available_plots = $conn->query("SELECT COUNT(*) as count FROM plots WHERE status='available'")->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
    
    // Sold plots
    $sold_plots = $conn->query("SELECT COUNT(*) as count FROM plots WHERE status='sold'")->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
    
} catch (Exception $e) {
    $total_farmers = 0;
    $total_land_area = 0;
    $total_investment = 0;
    $total_plots = 0;
    $available_plots = 0;
    $sold_plots = 0;
}

// Land Management Statistics for Company Owner
$stats = [
    [
        'icon' => 'fas fa-users',
        'value' => $total_farmers,
        'label' => 'Total Farmers/Kissans',
        'change' => '+12 this month',
        'change_type' => 'positive'
    ],
    [
        'icon' => 'fas fa-map-marked-alt',
        'value' => number_format($total_land_area, 0) . ' sq.ft',
        'label' => 'Total Land Acquired',
        'change' => '+25,000 sq.ft added',
        'change_type' => 'positive'
    ],
    [
        'icon' => 'fas fa-rupee-sign',
        'value' => '₹' . number_format($total_investment, 0),
        'label' => 'Total Land Investment',
        'change' => '+15% this quarter',
        'change_type' => 'positive'
    ],
    [
        'icon' => 'fas fa-th-large',
        'value' => $total_plots,
        'label' => 'Total Plots Developed',
        'change' => '+8 new plots',
        'change_type' => 'positive'
    ],
    [
        'icon' => 'fas fa-check-circle',
        'value' => $available_plots,
        'label' => 'Available for Sale',
        'change' => 'Ready to sell',
        'change_type' => 'positive'
    ],
    [
        'icon' => 'fas fa-handshake',
        'value' => $sold_plots,
        'label' => 'Plots Sold',
        'change' => '85% success rate',
        'change_type' => 'positive'
    ]
];

// Land Manager Quick Actions
$quick_actions = [
    [
        'title' => 'Add New Farmer',
        'icon' => 'fas fa-user-plus',
        'url' => '#',
        'color' => 'success',
        'modal' => 'addFarmerModal'
    ],
    [
        'title' => 'Record Land Purchase',
        'icon' => 'fas fa-map-pin',
        'url' => '#',
        'color' => 'warning',
        'modal' => 'addLandPurchaseModal'
    ],
    [
        'title' => 'Develop Plots',
        'icon' => 'fas fa-th-large',
        'url' => '#',
        'color' => 'info',
        'modal' => 'addPlotModal'
    ],
    [
        'title' => 'View Land Records',
        'icon' => 'fas fa-file-alt',
        'url' => 'land_records.php',
        'color' => 'primary'
    ],
    [
        'title' => 'Farmer Payments',
        'icon' => 'fas fa-money-bill-wave',
        'url' => 'farmer_payments.php',
        'color' => 'danger'
    ],
    [
        'title' => 'Plot Sales Dashboard',
        'icon' => 'fas fa-chart-line',
        'url' => 'plot_sales_dashboard.php',
        'color' => 'secondary'
    ]
];

// Recent land activities
$recent_activities = [
    [
        'title' => 'New Land Acquisition',
        'description' => '5 acres acquired from Ramesh Sharma in Sector 45 for ₹2.5 Crores',
        'time' => '2 hours ago',
        'icon' => 'fas fa-plus-circle text-success'
    ],
    [
        'title' => 'Plot Development Complete',
        'description' => '25 plots ready for sale in Green Valley project - Plot sizes 1200-1800 sq.ft',
        'time' => '5 hours ago',
        'icon' => 'fas fa-hammer text-info'
    ],
    [
        'title' => 'Farmer Payment',
        'description' => '₹75,000 paid to Suresh Kumar for final settlement of land purchase',
        'time' => '1 day ago',
        'icon' => 'fas fa-money-bill-wave text-warning'
    ],
    [
        'title' => 'Registry Completed',
        'description' => 'Registry process completed for 3.2 acre land in Jhansi Road area',
        'time' => '2 days ago',
        'icon' => 'fas fa-file-signature text-primary'
    ]
];

// Custom content with land management forms
$custom_content = '
<div class="row mt-4">
    <div class="col-12">
        <div class="alert alert-warning border-0" style="background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);">
            <div class="d-flex align-items-center">
                <i class="fas fa-crown fa-2x text-warning me-3"></i>
                <div>
                    <h5 class="alert-heading mb-1">Land Manager - Company Owner Controls</h5>
                    <p class="mb-0">Complete control over land acquisition from farmers to plot development and sales. Manage the entire land-to-money conversion cycle.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Land Management Dashboard -->
<div class="row mt-4">
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-success text-white">
                <h5 class="card-title mb-0"><i class="fas fa-tractor me-2"></i>Farmer Management</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6 mb-3">
                        <h3 class="text-success">' . $total_farmers . '</h3>
                        <small class="text-muted">Total Farmers</small>
                    </div>
                    <div class="col-6 mb-3">
                        <h3 class="text-primary">₹' . number_format($total_investment/10000, 1) . 'L</h3>
                        <small class="text-muted">Total Investment</small>
                    </div>
                    <div class="col-12">
                        <button class="btn btn-success w-100" data-bs-toggle="modal" data-bs-target="#addFarmerModal">
                            <i class="fas fa-plus me-2"></i>Add New Farmer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-info text-white">
                <h5 class="card-title mb-0"><i class="fas fa-map me-2"></i>Plot Development</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-4 mb-3">
                        <h3 class="text-info">' . $total_plots . '</h3>
                        <small class="text-muted">Total Plots</small>
                    </div>
                    <div class="col-4 mb-3">
                        <h3 class="text-success">' . $available_plots . '</h3>
                        <small class="text-muted">Available</small>
                    </div>
                    <div class="col-4 mb-3">
                        <h3 class="text-warning">' . $sold_plots . '</h3>
                        <small class="text-muted">Sold</small>
                    </div>
                    <div class="col-12">
                        <button class="btn btn-info w-100" data-bs-toggle="modal" data-bs-target="#addPlotModal">
                            <i class="fas fa-plus me-2"></i>Develop New Plot
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions for Land Management -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-warning text-dark">
                <h5 class="card-title mb-0"><i class="fas fa-bolt me-2"></i>Quick Land Management Actions</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <button class="btn btn-outline-success w-100" data-bs-toggle="modal" data-bs-target="#addLandPurchaseModal">
                            <i class="fas fa-map-pin d-block mb-2" style="font-size: 2rem;"></i>
                            Record Land Purchase
                        </button>
                    </div>
                    <div class="col-md-4 mb-3">
                        <a href="view_kisaan.php" class="btn btn-outline-primary w-100">
                            <i class="fas fa-users d-block mb-2" style="font-size: 2rem;"></i>
                            View All Farmers
                        </a>
                    </div>
                    <div class="col-md-4 mb-3">
                        <a href="plot_master.php" class="btn btn-outline-info w-100">
                            <i class="fas fa-th-large d-block mb-2" style="font-size: 2rem;"></i>
                            Plot Management
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>';

// Generate the dashboard
echo generateUniversalDashboard('company_owner', $stats, $quick_actions, $recent_activities, $custom_content);

// Add modals for forms
?>

<!-- Add Farmer Modal -->
<div class="modal fade" id="addFarmerModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>Add New Farmer/Kissan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="add_farmer">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Farmer Name *</label>
                            <input type="text" class="form-control" name="farmer_name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Mobile Number *</label>
                            <input type="tel" class="form-control" name="farmer_mobile" required>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Address</label>
                            <textarea class="form-control" name="farmer_address" rows="2"></textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Aadhar Number</label>
                            <input type="text" class="form-control" name="aadhar_number">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">PAN Number</label>
                            <input type="text" class="form-control" name="pan_number">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Bank Account</label>
                            <input type="text" class="form-control" name="bank_account">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">IFSC Code</label>
                            <input type="text" class="form-control" name="ifsc_code">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Total Land Size (Acres)</label>
                            <input type="number" step="0.01" class="form-control" name="land_size">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Add Farmer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Land Purchase Modal -->
<div class="modal fade" id="addLandPurchaseModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title"><i class="fas fa-map-pin me-2"></i>Record Land Purchase</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="add_land_purchase">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Farmer *</label>
                            <select class="form-control" name="farmer_id" required>
                                <option value="">Select Farmer</option>
                                <?php
                                try {
                                    $farmers = $conn->query("SELECT id, name FROM farmers ORDER BY name");
                                    while ($farmer = $farmers->fetch(PDO::FETCH_ASSOC)) {
                                        echo "<option value='" . $farmer['id'] . "'>" . htmlspecialchars($farmer['name']) . "</option>";
                                    }
                                } catch (Exception $e) {
                                    echo "<option value=''>No farmers found</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Location *</label>
                            <input type="text" class="form-control" name="location" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Area (sq.ft) *</label>
                            <input type="number" step="0.01" class="form-control" name="area_sqft" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Purchase Price (₹) *</label>
                            <input type="number" step="0.01" class="form-control" name="purchase_price" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Paid Amount (₹)</label>
                            <input type="number" step="0.01" class="form-control" name="paid_amount" value="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Purchase Date</label>
                            <input type="date" class="form-control" name="purchase_date" value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Registry Status</label>
                            <select class="form-control" name="registry_status">
                                <option value="agreement">Agreement Only</option>
                                <option value="registry">Registry Complete</option>
                                <option value="pending">Pending</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Record Purchase</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Plot Development Modal -->
<div class="modal fade" id="addPlotModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="fas fa-th-large me-2"></i>Develop New Plot</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="add_plot_development">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Project *</label>
                        <select class="form-control" name="project_id" required>
                            <option value="">Select Project</option>
                            <?php
                            try {
                                $projects = $conn->query("SELECT id, project_name FROM projects ORDER BY project_name");
                                while ($project = $projects->fetch(PDO::FETCH_ASSOC)) {
                                    echo "<option value='" . $project['id'] . "'>" . htmlspecialchars($project['project_name']) . "</option>";
                                }
                            } catch (Exception $e) {
                                echo "<option value='1'>Default Project</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Plot Number *</label>
                        <input type="text" class="form-control" name="plot_number" required placeholder="e.g., A-001, B-015">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Plot Area (sq.ft) *</label>
                        <input type="number" step="0.01" class="form-control" name="plot_area" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Plot Price (₹) *</label>
                        <input type="number" step="0.01" class="form-control" name="plot_price" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info">Create Plot</button>
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
.dashboard-company-owner .card {
    border: 2px solid var(--company-owner-primary, #d4af37);
    box-shadow: 0 4px 12px rgba(212, 175, 55, 0.15);
}

.dashboard-company-owner .modal-header {
    border-bottom: 2px solid rgba(255,255,255,0.2);
}

.dashboard-company-owner .btn {
    border-radius: 8px;
    font-weight: 500;
}

.text-success { color: #28a745 !important; }
.text-info { color: #17a2b8 !important; }
.text-warning { color: #ffc107 !important; }
.text-primary { color: #007bff !important; }
</style>