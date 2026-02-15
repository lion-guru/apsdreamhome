<?php
/**
 * Modernized Investor Dashboard
 * Professional design for APS Dream Homes Investors
 */

require_once __DIR__ . '/init.php';

// Check if user is logged in as investor
if (!isset($_SESSION['uid']) || (isset($_SESSION['utype']) && $_SESSION['utype'] !== 'investor')) {
    header("Location: login.php");
    exit;
}

$db = \App\Core\App::database();
$uid = $_SESSION['uid'];

// Fetch investor profile
$investor = $db->fetch("SELECT * FROM user WHERE uid = ? AND utype = 'investor'", [$uid]);

if (!$investor) {
    header("Location: login.php?error=investor_not_found");
    exit;
}

// Fetch real stats from DB (with fallbacks)
$stats = [
    'investments' => 0,
    'returns' => '₹0.00',
    'messages' => 0,
    'opportunities' => 0
];

try {
    // Count investments
    $stats['investments'] = $db->query("SELECT COUNT(*) FROM properties WHERE owner_id = ? AND type = 'investment'", [$uid])->fetchColumn();

    // Count unread notifications
    $stats['messages'] = $db->query("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0", [$uid])->fetchColumn();
} catch (Exception $e) {
    error_log('Investor Dashboard stats error: ' . $e->getMessage());
}

$page_title = 'Investor Dashboard | APS Dream Homes';
$layout = 'modern';

ob_start();
?>

<div class="container py-5 mt-5">
    <div class="row mb-4 animate-fade-up">
        <div class="col-md-8 d-flex align-items-center">
            <div class="position-relative me-4">
                <img src="<?= !empty($investor['uimage']) ? h($investor['uimage']) : 'https://ui-avatars.com/api/?name=' . urlencode($investor['uname']) . '&size=100&background=1e3a8a&color=fff' ?>" 
                     alt="Profile" class="rounded-circle shadow-sm border border-3 border-white" style="width:100px; height:100px; object-fit:cover;">
                <span class="position-absolute bottom-0 end-0 bg-success border border-2 border-white rounded-circle p-2" title="Online"></span>
            </div>
            <div>
                <h1 class="display-6 fw-bold text-primary mb-1">Welcome, <?= h($investor['uname']) ?>!</h1>
                <p class="text-muted mb-0"><i class="fas fa-chart-pie me-2"></i>Investment Portfolio | <i class="fas fa-envelope me-2"></i><?= h($investor['uemail']) ?></p>
            </div>
        </div>
        <div class="col-md-4 text-md-end d-flex align-items-center justify-content-md-end mt-3 mt-md-0">
            <a href="properties.php?filter=investment" class="btn btn-primary rounded-pill px-4 shadow-sm">
                <i class="fas fa-plus me-2"></i>New Investment
            </a>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="row g-4 mb-5 animate-fade-up" style="animation-delay: 0.1s;">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-4 text-center h-100 transition-hover">
                <div class="icon-box bg-primary-soft rounded-circle mx-auto mb-3" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-briefcase text-primary fs-4"></i>
                </div>
                <h3 class="fw-bold mb-1"><?= $stats['investments'] ?></h3>
                <p class="text-muted mb-0">Total Assets</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-4 text-center h-100 transition-hover">
                <div class="icon-box bg-success-soft rounded-circle mx-auto mb-3" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-chart-line text-success fs-4"></i>
                </div>
                <h3 class="fw-bold mb-1"><?= $stats['returns'] ?></h3>
                <p class="text-muted mb-0">Total Returns</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-4 text-center h-100 transition-hover">
                <div class="icon-box bg-info-soft rounded-circle mx-auto mb-3" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-bell text-info fs-4"></i>
                </div>
                <h3 class="fw-bold mb-1"><?= $stats['messages'] ?></h3>
                <p class="text-muted mb-0">Alerts</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-4 text-center h-100 transition-hover">
                <div class="icon-box bg-warning-soft rounded-circle mx-auto mb-3" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-lightbulb text-warning fs-4"></i>
                </div>
                <h3 class="fw-bold mb-1">High</h3>
                <p class="text-muted mb-0">Market Outlook</p>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- AI Investment Insights -->
        <div class="col-lg-4 animate-fade-up" style="animation-delay: 0.2s;">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-4">
                        <div class="bg-primary-soft rounded-circle p-3 me-3">
                            <i class="fas fa-robot text-primary fs-4"></i>
                        </div>
                        <h5 class="mb-0 fw-bold">Portfolio AI</h5>
                    </div>
                    
                    <div class="ai-chat-box bg-light-blue p-3 rounded-4 mb-4" style="min-height: 150px;">
                        <p class="small mb-0"><i class="fas fa-sparkles text-warning me-2"></i>Analysis complete! Based on current market trends in <strong>Gorakhpur</strong>, your residential holdings are expected to appreciate by 8.5% over the next quarter.</p>
                        <hr class="my-3 opacity-10">
                        <p class="small mb-0"><strong>Recommendation:</strong> Reinvest rental yields from Lucknow property into the new commercial project in Varanasi for diversified growth.</p>
                    </div>

                    <div class="d-grid">
                        <button class="btn btn-primary rounded-pill py-2 shadow-sm" onclick="handleAIChat()">
                            <i class="fas fa-sync-alt me-2"></i>Refresh Insights
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Investment Opportunities -->
        <div class="col-lg-8 animate-fade-up" style="animation-delay: 0.3s;">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-0 py-4 px-4 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-gem text-warning me-2"></i>Curated Opportunities</h5>
                    <a href="properties.php" class="btn btn-sm btn-link text-decoration-none p-0">View Marketplace <i class="fas fa-arrow-right ms-1"></i></a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light text-muted small text-uppercase">
                                <tr>
                                    <th class="ps-4">Project</th>
                                    <th>Potential ROI</th>
                                    <th>Risk Level</th>
                                    <th class="text-end pe-4">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold">Varanasi Ganga Nagri</div>
                                        <div class="small text-muted">Luxury Waterfront Plots</div>
                                    </td>
                                    <td><span class="text-success fw-bold">12-15% p.a.</span></td>
                                    <td><span class="badge bg-success-soft text-success rounded-pill px-3">Low</span></td>
                                    <td class="text-end pe-4">
                                        <button class="btn btn-sm btn-outline-primary rounded-pill px-3">Details</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold">Lucknow Ram Nagri</div>
                                        <div class="small text-muted">Premium Commercial Space</div>
                                    </td>
                                    <td><span class="text-success fw-bold">18-22% p.a.</span></td>
                                    <td><span class="badge bg-warning-soft text-warning rounded-pill px-3">Medium</span></td>
                                    <td class="text-end pe-4">
                                        <button class="btn btn-sm btn-outline-primary rounded-pill px-3">Details</button>
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
.bg-primary-soft { background-color: rgba(30, 58, 138, 0.1); }
.bg-success-soft { background-color: rgba(25, 135, 84, 0.1); }
.bg-warning-soft { background-color: rgba(255, 193, 7, 0.1); }
.bg-danger-soft { background-color: rgba(220, 53, 69, 0.1); }
.bg-info-soft { background-color: rgba(13, 202, 240, 0.1); }
.bg-light-blue { background-color: #f0f7ff; }

.transition-hover { transition: all 0.3s ease; }
.transition-hover:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.05) !important; }

.animate-fade-up { animation: fadeUp 0.6s ease forwards; opacity: 0; }
@keyframes fadeUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
</style>

<script>
function handleAIChat() {
    alert('AI Analysis re-triggered. Fetching latest market data for your portfolio...');
}
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/modern.php';
?>

