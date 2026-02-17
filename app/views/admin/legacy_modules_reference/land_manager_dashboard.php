<?php
require_once __DIR__ . '/core/init.php';

// Check if user is logged in and has required privileges
adminAccessControl(['company_owner', 'superadmin', 'admin']);

$db = \App\Core\App::database();

// Handle form submissions for land operations
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Token Validation
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        die('Invalid CSRF token. Action blocked.');
    }

    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'add_farmer':
            try {
                // Generate a farmer number
                $farmer_number = 'FMR-' . time() . \App\Helpers\SecurityHelper::secureRandomInt(100, 999);
                $db->execute("INSERT INTO farmer_profiles (farmer_number, full_name, phone, address, aadhar_number, pan_number, bank_account_number, ifsc_code, total_land_holding, created_at) VALUES (:farmer_number, :full_name, :phone, :address, :aadhar_number, :pan_number, :bank_account_number, :ifsc_code, :total_land_holding, NOW())", [
                    'farmer_number' => $farmer_number,
                    'full_name' => h($_POST['farmer_name']),
                    'phone' => h($_POST['farmer_mobile']),
                    'address' => h($_POST['farmer_address']),
                    'aadhar_number' => h($_POST['aadhar_number']),
                    'pan_number' => h($_POST['pan_number']),
                    'bank_account_number' => h($_POST['bank_account']),
                    'ifsc_code' => h($_POST['ifsc_code']),
                    'total_land_holding' => (float)$_POST['land_size']
                ]);
                $message = "Farmer added successfully!";
            } catch (Exception $e) {
                $error = "Error adding farmer: " . $e->getMessage();
            }
            break;

        case 'add_land_purchase':
            try {
                $purchase_price = (float)$_POST['purchase_price'];
                $paid_amount = (float)$_POST['paid_amount'];
                $due_amount = $purchase_price - $paid_amount;
                $db->execute("INSERT INTO land_purchases (farmer_id, location, area_sqft, purchase_price, paid_amount, due_amount, purchase_date, registry_status, created_at) VALUES (:farmer_id, :location, :area_sqft, :purchase_price, :paid_amount, :due_amount, :purchase_date, :registry_status, NOW())", [
                    'farmer_id' => (int)$_POST['farmer_id'],
                    'location' => h($_POST['location']),
                    'area_sqft' => (float)$_POST['area_sqft'],
                    'purchase_price' => $purchase_price,
                    'paid_amount' => $paid_amount,
                    'due_amount' => $due_amount,
                    'purchase_date' => h($_POST['purchase_date']),
                    'registry_status' => h($_POST['registry_status'])
                ]);
                $message = "Land purchase recorded successfully!";
            } catch (Exception $e) {
                $error = "Error recording land purchase: " . $e->getMessage();
            }
            break;

        case 'add_plot_development':
            try {
                $db->execute("INSERT INTO plots (project_id, plot_number, area_sqft, plot_price, status, created_at) VALUES (:project_id, :plot_number, :area_sqft, :plot_price, 'available', NOW())", [
                    'project_id' => (int)$_POST['project_id'],
                    'plot_number' => h($_POST['plot_number']),
                    'area_sqft' => (float)$_POST['plot_area'],
                    'plot_price' => (float)$_POST['plot_price']
                ]);
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
    $total_farmers = $db->fetchOne("SELECT COUNT(*) as count FROM farmer_profiles")['count'] ?? 0;

    // Total land area purchased
    $total_land_area = $db->fetchOne("SELECT SUM(area_sqft) as total FROM land_purchases")['total'] ?? 0;

    // Total amount paid to farmers
    $total_paid = $db->fetchOne("SELECT SUM(paid_amount) as total FROM land_purchases")['total'] ?? 0;

    // Total due amount
    $total_due = $db->fetchOne("SELECT SUM(due_amount) as total FROM land_purchases")['total'] ?? 0;

    // Pending Registry count
    $pending_registry = $db->fetchOne("SELECT COUNT(*) as count FROM land_purchases WHERE registry_status='pending'")['count'] ?? 0;

    // Total land investment
    $total_investment = $db->fetchOne("SELECT SUM(purchase_price) as total FROM land_purchases")['total'] ?? 0;

    // Total plots developed
    $total_plots = $db->fetchOne("SELECT COUNT(*) as count FROM plots")['count'] ?? 0;

    // Available plots
     $available_plots = $db->fetchOne("SELECT COUNT(*) as count FROM plots WHERE status='available'")['count'] ?? 0;

     // Sold plots
     $sold_plots = $db->fetchOne("SELECT COUNT(*) as count FROM plots WHERE status='sold'")['count'] ?? 0;

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
        'value' => h($total_farmers),
        'label' => 'Total Farmers/Kissans',
        'change' => '+12 this month',
        'change_type' => 'positive'
    ],
    [
        'icon' => 'fas fa-map-marked-alt',
        'value' => h(number_format($total_land_area, 0)) . ' sq.ft',
        'label' => 'Total Land Acquired',
        'change' => '+25,000 sq.ft added',
        'change_type' => 'positive'
    ],
    [
        'icon' => 'fas fa-rupee-sign',
        'value' => '₹' . h(number_format($total_investment, 0)),
        'label' => 'Total Land Investment',
        'change' => '+15% this quarter',
        'change_type' => 'positive'
    ],
    [
        'icon' => 'fas fa-th-large',
        'value' => h($total_plots),
        'label' => 'Total Plots Developed',
        'change' => '+8 new plots',
        'change_type' => 'positive'
    ],
    [
        'icon' => 'fas fa-check-circle',
        'value' => h($available_plots),
        'label' => 'Available for Sale',
        'change' => 'Ready to sell',
        'change_type' => 'positive'
    ],
    [
        'icon' => 'fas fa-handshake',
        'value' => h($sold_plots),
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
                    <h5 class="alert-heading mb-1">' . h($mlSupport->translate('Land Manager - Company Owner Controls')) . '</h5>
                    <p class="mb-0">' . h($mlSupport->translate('Complete control over land acquisition from farmers to plot development and sales. Manage the entire land-to-money conversion cycle.')) . '</p>
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
                <h5 class="card-title mb-0"><i class="fas fa-tractor me-2"></i>' . h($mlSupport->translate('Farmer Management')) . '</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6 mb-3">
                        <h3 class="text-success">' . h($total_farmers) . '</h3>
                        <small class="text-muted">' . h($mlSupport->translate('Total Farmers')) . '</small>
                    </div>
                    <div class="col-6 mb-3">
                        <h3 class="text-primary">₹' . h(number_format($total_investment/10000, 1)) . 'L</h3>
                        <small class="text-muted">' . h($mlSupport->translate('Total Investment')) . '</small>
                    </div>
                    <div class="col-12">
                        <button class="btn btn-success w-100" data-bs-toggle="modal" data-bs-target="#addFarmerModal">
                            <i class="fas fa-plus me-2"></i>' . h($mlSupport->translate('Add New Farmer')) . '
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-info text-white">
                <h5 class="card-title mb-0"><i class="fas fa-map me-2"></i>' . h($mlSupport->translate('Plot Development')) . '</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-4 mb-3">
                        <h3 class="text-info">' . h($total_plots) . '</h3>
                        <small class="text-muted">' . h($mlSupport->translate('Total Plots')) . '</small>
                    </div>
                    <div class="col-4 mb-3">
                        <h3 class="text-success">' . h($available_plots) . '</h3>
                        <small class="text-muted">' . h($mlSupport->translate('Available')) . '</small>
                    </div>
                    <div class="col-4 mb-3">
                        <h3 class="text-warning">' . h($sold_plots) . '</h3>
                        <small class="text-muted">' . h($mlSupport->translate('Sold')) . '</small>
                    </div>
                    <div class="col-12">
                        <button class="btn btn-info w-100" data-bs-toggle="modal" data-bs-target="#addPlotModal">
                            <i class="fas fa-plus me-2"></i>' . h($mlSupport->translate('Develop New Plot')) . '
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
                <h5 class="card-title mb-0"><i class="fas fa-bolt me-2"></i>' . h($mlSupport->translate('Quick Land Management Actions')) . '</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <button class="btn btn-outline-success w-100" data-bs-toggle="modal" data-bs-target="#addLandPurchaseModal">
                            <i class="fas fa-map-pin d-block mb-2" style="font-size: 2rem;"></i>
                            ' . h($mlSupport->translate('Record Land Purchase')) . '
                        </button>
                    </div>
                    <div class="col-md-4 mb-3">
                        <a href="view_kisaan.php" class="btn btn-outline-primary w-100">
                            <i class="fas fa-users d-block mb-2" style="font-size: 2rem;"></i>
                            ' . h($mlSupport->translate('View All Farmers')) . '
                        </a>
                    </div>
                    <div class="col-md-4 mb-3">
                        <a href="plot_master.php" class="btn btn-outline-info w-100">
                            <i class="fas fa-th-large d-block mb-2" style="font-size: 2rem;"></i>
                            ' . h($mlSupport->translate('Plot Management')) . '
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
                <?php echo getCsrfField(); ?>
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
                <?php echo getCsrfField(); ?>
                <input type="hidden" name="action" value="add_land_purchase">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Farmer *</label>
                            <select class="form-control" name="farmer_id" required>
                                <option value="">Select Farmer</option>
                                <?php
                                try {
                                    // Get all farmers for selection
                                    $farmers = $db->fetchAll("SELECT id, full_name as name FROM farmer_profiles ORDER BY full_name");
                                    foreach ($farmers as $farmer) {
                                        echo "<option value='" . h($farmer['id']) . "'>" . h($farmer['name']) . "</option>";
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
                <?php echo getCsrfField(); ?>
                <input type="hidden" name="action" value="add_plot_development">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Project *</label>
                        <select class="form-control" name="project_id" required>
                            <option value="">Select Project</option>
                            <?php
                            try {
                                $projects = $db->fetchAll("SELECT id, project_name FROM projects ORDER BY project_name");
                                foreach ($projects as $project) {
                                    echo "<option value='" . h($project['id']) . "'>" . h($project['project_name']) . "</option>";
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

<?php if (isset($message) && $message): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        alert('<?php echo h(addslashes($message)); ?>');
    });
</script>
<?php endif; ?>

<?php if (isset($error) && $error): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        alert('Error: <?php echo h(addslashes($error)); ?>');
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
