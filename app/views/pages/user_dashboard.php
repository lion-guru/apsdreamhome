<?php
/**
 * Modernized User Dashboard
 * Migrated from Views/dashboards/user_dashboard.php
 */

require_once __DIR__ . '/init.php';

// Check if user is logged in
if (!isset($_SESSION['uid']) || (isset($_SESSION['utype']) && $_SESSION['utype'] !== 'user' && $_SESSION['utype'] !== 'customer')) {
    header("Location: login.php");
    exit;
}

$db = \App\Core\App::database();
$uid = $_SESSION['uid'];
$user_name = $_SESSION['name'] ?? 'User';

// Fetch user data
$user_data = $db->fetch("SELECT * FROM users WHERE id = ?", [$uid]);

// Fetch user's properties
$properties = $db->fetchAll("SELECT * FROM property WHERE uid = ? ORDER BY date DESC", [$uid]);

// Fetch stats
$stats = [
    'properties' => count($properties),
    'kyc_status' => $user_data['kyc_status'] ?? 'Pending',
    'pending_docs' => 0, // Placeholder
    'notifications' => 0  // Placeholder
];

// Fetch unread notifications count
$stats['notifications'] = $db->query("SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0", [$uid])->fetchColumn() ?? 0;

$page_title = "User Dashboard | APS Dream Homes";
$layout = 'modern';

ob_start();
?>

<div class="container py-5 mt-5">
    <div class="row mb-4 animate-fade-up">
        <div class="col-md-8">
            <h1 class="display-5 fw-bold text-primary">Welcome, <?= h($user_name) ?>!</h1>
            <p class="lead text-muted">Manage your profile, properties, and track your activity.</p>
        </div>
        <div class="col-md-4 text-md-end d-flex align-items-center justify-content-md-end mt-3 mt-md-0">
            <a href="submit-property.php" class="btn btn-primary rounded-pill px-4 shadow-sm">
                <i class="fas fa-plus me-2"></i>Add New Property
            </a>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="row g-4 mb-5 animate-fade-up" style="animation-delay: 0.1s;">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-4 text-center h-100 transition-hover">
                <div class="icon-box bg-primary-soft rounded-circle mx-auto mb-3" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-building text-primary fs-4"></i>
                </div>
                <h3 class="fw-bold mb-1"><?= $stats['properties'] ?></h3>
                <p class="text-muted mb-0">My Properties</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-4 text-center h-100 transition-hover">
                <div class="icon-box bg-info-soft rounded-circle mx-auto mb-3" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-user-check text-info fs-4"></i>
                </div>
                <h3 class="fw-bold mb-1"><?= h($stats['kyc_status']) ?></h3>
                <p class="text-muted mb-0">KYC Status</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-4 text-center h-100 transition-hover">
                <div class="icon-box bg-warning-soft rounded-circle mx-auto mb-3" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-file-alt text-warning fs-4"></i>
                </div>
                <h3 class="fw-bold mb-1"><?= $stats['pending_docs'] ?></h3>
                <p class="text-muted mb-0">Pending Docs</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-4 text-center h-100 transition-hover">
                <div class="icon-box bg-success-soft rounded-circle mx-auto mb-3" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-bell text-success fs-4"></i>
                </div>
                <h3 class="fw-bold mb-1"><?= $stats['notifications'] ?></h3>
                <p class="text-muted mb-0">Notifications</p>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8 animate-fade-up" style="animation-delay: 0.2s;">
            <!-- Properties Table -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-transparent border-0 p-4 d-flex justify-content-between align-items-center">
                    <h4 class="fw-bold mb-0"><i class="fas fa-list me-2 text-primary"></i>My Properties</h4>
                    <a href="properties.php" class="btn btn-sm btn-outline-primary rounded-pill">View All</a>
                </div>
                <div class="card-body p-0">
                    <?php if (count($properties) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light text-muted small text-uppercase">
                                    <tr>
                                        <th class="ps-4">Property</th>
                                        <th>Type</th>
                                        <th>Location</th>
                                        <th>Price</th>
                                        <th>Status</th>
                                        <th class="text-end pe-4">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($properties as $property): ?>
                                        <tr>
                                            <td class="ps-4">
                                                <div class="d-flex align-items-center">
                                                    <img src="<?= !empty($property['pimage']) ? BASE_URL . 'public/uploads/property/' . $property['pimage'] : BASE_URL . 'public/assets/images/property-placeholder.jpg' ?>"
                                                         alt="Property" class="rounded-3 me-3" style="width: 50px; height: 50px; object-fit: cover;">
                                                    <div>
                                                        <h6 class="mb-0 fw-bold"><?= h($property['title']) ?></h6>
                                                        <small class="text-muted">ID: #<?= $property['pid'] ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><span class="badge bg-light text-dark fw-normal"><?= ucfirst(h($property['type'])) ?></span></td>
                                            <td><i class="fas fa-map-marker-alt text-muted me-1 small"></i><?= h($property['city']) ?></td>
                                            <td class="fw-bold text-primary">₹<?= number_format($property['price']) ?></td>
                                            <td>
                                                <?php
                                                $status_class = 'bg-success';
                                                if ($property['status'] == 'pending') $status_class = 'bg-warning';
                                                if ($property['status'] == 'rejected') $status_class = 'bg-danger';
                                                ?>
                                                <span class="badge <?= $status_class ?> rounded-pill px-3"><?= ucfirst(h($property['status'])) ?></span>
                                            </td>
                                            <td class="text-end pe-4">
                                                <div class="dropdown">
                                                    <button class="btn btn-light btn-sm rounded-circle shadow-sm" type="button" data-bs-toggle="dropdown">
                                                        <i class="fas fa-ellipsis-v"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3">
                                                        <li><a class="dropdown-item py-2" href="property_detail.php?pid=<?= $property['pid'] ?>"><i class="fas fa-eye me-2 text-primary"></i>View Details</a></li>
                                                        <li><a class="dropdown-item py-2" href="edit-property.php?id=<?= $property['pid'] ?>"><i class="fas fa-edit me-2 text-info"></i>Edit Property</a></li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li><a class="dropdown-item py-2 text-danger" href="#"><i class="fas fa-trash-alt me-2"></i>Delete</a></li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <img src="/assets/images/no-data.svg" alt="No Properties" class="mb-3" style="width: 150px; opacity: 0.5;">
                            <h5 class="text-muted">You haven't listed any properties yet.</h5>
                            <a href="submit-property.php" class="btn btn-primary rounded-pill mt-3 px-4">Start Listing</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- AI Suggestions Panel -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                <h4 class="fw-bold mb-4"><i class="fas fa-magic me-2 text-primary"></i>AI Suggestions & Reminders</h4>
                <div id="aiSuggestionsPanel">
                    <div class="d-flex align-items-center justify-content-center py-4">
                        <div class="spinner-border text-primary spinner-border-sm me-2" role="status"></div>
                        <span class="text-muted">Loading personalized suggestions...</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar Content -->
        <div class="col-lg-4 animate-fade-up" style="animation-delay: 0.3s;">
            <!-- AI Chatbot Panel -->
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-primary text-white overflow-hidden position-relative">
                <div class="position-absolute top-0 end-0 p-3 opacity-25">
                    <i class="fas fa-robot display-1"></i>
                </div>
                <h4 class="fw-bold mb-3 position-relative">Ask APS AI</h4>
                <p class="small mb-4 position-relative opacity-75">Get instant answers about your properties, KYC, or site features.</p>
                <form id="aiChatForm" class="position-relative" onsubmit="return false;">
                    <div class="input-group bg-white rounded-pill p-1 shadow-sm">
                        <input type="text" class="form-control border-0 bg-transparent ps-3" id="aiChatInput" placeholder="How do I update KYC?">
                        <button class="btn btn-primary rounded-pill px-3" onclick="sendAIQuery()">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </form>
                <div id="aiChatResponse" class="mt-3 small position-relative bg-white bg-opacity-10 p-3 rounded-3 d-none">
                    <!-- AI response will appear here -->
                </div>
            </div>

            <!-- Profile Summary -->
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
                <h5 class="fw-bold mb-4">Profile Summary</h5>
                <div class="text-center mb-4">
                    <div class="position-relative d-inline-block">
                        <img src="<?= !empty($user_data['uimage']) ? '/admin/user/' . $user_data['uimage'] : '/assets/images/user-placeholder.jpg' ?>"
                             alt="Profile" class="rounded-circle shadow-sm border border-4 border-light" style="width: 100px; height: 100px; object-fit: cover;">
                        <span class="position-absolute bottom-0 end-0 bg-success border border-2 border-white rounded-circle p-2" title="Online"></span>
                    </div>
                    <h5 class="mt-3 mb-1 fw-bold"><?= h($user_name) ?></h5>
                    <p class="text-muted small mb-3"><?= h($user_data['email'] ?? 'No email provided') ?></p>
                    <a href="profile.php" class="btn btn-outline-primary btn-sm rounded-pill px-4">View Profile</a>
                </div>
                <hr class="text-muted opacity-25">
                <div class="row text-center mt-3">
                    <div class="col-6 border-end">
                        <h6 class="fw-bold mb-0">Role</h6>
                        <small class="text-muted"><?= ucfirst($_SESSION['utype'] ?? 'User') ?></small>
                    </div>
                    <div class="col-6">
                        <h6 class="fw-bold mb-0">Joined</h6>
                        <small class="text-muted"><?= date('M Y', strtotime($user_data['created_at'] ?? 'now')) ?></small>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card border-0 shadow-sm rounded-4 p-4">
                <h5 class="fw-bold mb-4">Quick Actions</h5>
                <div class="d-grid gap-2">
                    <button class="btn btn-light text-start rounded-3 py-2 px-3 hover-lift border-0">
                        <i class="fas fa-file-download text-primary me-2"></i> Download My Report
                    </button>
                    <button class="btn btn-light text-start rounded-3 py-2 px-3 hover-lift border-0">
                        <i class="fas fa-share-alt text-success me-2"></i> Share Property List
                    </button>
                    <button class="btn btn-light text-start rounded-3 py-2 px-3 hover-lift border-0">
                        <i class="fas fa-shield-alt text-info me-2"></i> Security Settings
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.bg-primary-soft { background-color: rgba(13, 110, 253, 0.1); }
.bg-info-soft { background-color: rgba(13, 202, 240, 0.1); }
.bg-warning-soft { background-color: rgba(255, 193, 7, 0.1); }
.bg-success-soft { background-color: rgba(25, 135, 84, 0.1); }

.transition-hover {
    transition: all 0.3s ease;
}
.transition-hover:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.05) !important;
}

.hover-lift {
    transition: transform 0.2s ease;
}
.hover-lift:hover {
    transform: translateX(5px);
}

.animate-fade-up {
    animation: fadeUp 0.6s ease forwards;
    opacity: 0;
}

@keyframes fadeUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.icon-box {
    transition: transform 0.3s ease;
}
.card:hover .icon-box {
    transform: scale(1.1);
}
</style>

<script>
// AI Chatbot Functionality
function sendAIQuery() {
    const input = document.getElementById('aiChatInput').value.trim();
    if (!input) return;

    const responseBox = document.getElementById('aiChatResponse');
    responseBox.classList.remove('d-none');
    responseBox.innerHTML = '<div class="d-flex align-items-center"><div class="spinner-border spinner-border-sm me-2" role="status"></div><span>Thinking...</span></div>';

    setTimeout(() => {
        responseBox.innerHTML = `<strong>AI Response:</strong><p class="mb-0 mt-1">I've analyzed your request about "${input}". Based on your profile, you can find more information in the help section or contact our support.</p>`;
    }, 1500);
}

// AI Suggestions (Simulated AJAX)
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(() => {
        const panel = document.getElementById('aiSuggestionsPanel');
        panel.innerHTML = `
            <div class="list-group list-group-flush">
                <div class="list-group-item bg-transparent border-0 px-0 mb-3">
                    <div class="d-flex w-100 justify-content-between">
                        <h6 class="mb-1 fw-bold text-success"><i class="fas fa-check-circle me-2"></i>Complete your KYC</h6>
                        <small class="text-muted">High Priority</small>
                    </div>
                    <p class="mb-1 small text-muted">Upload your documents to get verified and list more properties.</p>
                    <a href="profile.php" class="btn btn-link btn-sm p-0 text-decoration-none">Update Now <i class="fas fa-arrow-right ms-1 small"></i></a>
                </div>
                <div class="list-group-item bg-transparent border-0 px-0 mb-3">
                    <div class="d-flex w-100 justify-content-between">
                        <h6 class="mb-1 fw-bold text-info"><i class="fas fa-lightbulb me-2"></i>Price Suggestion</h6>
                        <small class="text-muted">Insight</small>
                    </div>
                    <p class="mb-1 small text-muted">Properties in Lucknow are seeing a 15% increase in demand this month.</p>
                </div>
            </div>
        `;
    }, 1000);
});
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/modern.php';
?>
