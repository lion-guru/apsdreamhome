<?php

/**
 * Modernized Tenant Dashboard
 * Streamlined experience for APS Dream Homes Tenants
 */

require_once __DIR__ . '/init.php';

// Check if user is logged in as tenant
if (!isset($_SESSION['uid']) || (isset($_SESSION['utype']) && $_SESSION['utype'] !== 'tenant')) {
    header("Location: login.php");
    exit;
}

$db = \App\Core\App::database();
$uid = $_SESSION['uid'];

// Fetch tenant profile from 'users' table
$tenant = $db->fetch("SELECT * FROM users WHERE id = :uid AND role = 'tenant'", ['uid' => $uid]);

if (!$tenant) {
    header("Location: login.php?error=tenant_not_found");
    exit;
}

// Map modern columns to legacy variables for view compatibility
$tenant['uid'] = $tenant['id'];
$tenant['uname'] = $tenant['name'];
$tenant['utype'] = $tenant['role'];
$tenant['uemail'] = $tenant['email'];
$tenant['uimage'] = $tenant['profile_image'] ?? null;

// Fetch real stats from DB (with fallbacks)
$stats = [
    'active_rentals' => 0,
    'pending_payments' => 0,
    'total_paid' => 0,
    'service_requests' => 0
];

try {
    // Count active rentals
    $stats['active_rentals'] = $db->query("SELECT COUNT(*) FROM properties WHERE tenant_id = :uid AND status = 'rented'", ['uid' => $uid])->fetchColumn();

    // Unread Notifications count
    $stats['service_requests'] = $db->query("SELECT COUNT(*) FROM notifications WHERE user_id = :uid AND is_read = 0", ['uid' => $uid])->fetchColumn();
} catch (Exception $e) {
    error_log('Tenant Dashboard stats error: ' . $e->getMessage());
}

$page_title = 'Tenant Dashboard | APS Dream Homes';
$layout = 'modern';

ob_start();
?>

<div class="container py-5 mt-5">
    <div class="row mb-4 animate-fade-up">
        <div class="col-md-8 d-flex align-items-center">
            <div class="position-relative me-4">
                <img src="<?= !empty($tenant['uimage']) ? h($tenant['uimage']) : 'https://ui-avatars.com/api/?name=' . urlencode($tenant['uname']) . '&size=100&background=1e3a8a&color=fff' ?>"
                    alt="Profile" class="rounded-circle shadow-sm border border-3 border-white" style="width:100px; height:100px; object-fit:cover;">
                <span class="position-absolute bottom-0 end-0 bg-success border border-2 border-white rounded-circle p-2" title="Online"></span>
            </div>
            <div>
                <h1 class="display-6 fw-bold text-success mb-1">Hello, <?= h($tenant['uname']) ?>!</h1>
                <p class="text-muted mb-0"><i class="fas fa-home me-2"></i>Tenant Portal | <i class="fas fa-envelope me-2"></i><?= h($tenant['uemail']) ?></p>
            </div>
        </div>
        <div class="col-md-4 text-md-end d-flex align-items-center justify-content-md-end mt-3 mt-md-0">
            <a href="contact.php?subject=Service Request" class="btn btn-success rounded-pill px-4 shadow-sm">
                <i class="fas fa-tools me-2"></i>Service Request
            </a>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="row g-4 mb-5 animate-fade-up" style="animation-delay: 0.1s;">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-4 text-center h-100 transition-hover">
                <div class="icon-box bg-success-soft rounded-circle mx-auto mb-3" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-home text-success fs-4"></i>
                </div>
                <h3 class="fw-bold mb-1"><?= $stats['active_rentals'] ?></h3>
                <p class="text-muted mb-0">Active Rentals</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-4 text-center h-100 transition-hover">
                <div class="icon-box bg-danger-soft rounded-circle mx-auto mb-3" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-exclamation-circle text-danger fs-4"></i>
                </div>
                <h3 class="fw-bold mb-1"><?= $stats['pending_payments'] ?></h3>
                <p class="text-muted mb-0">Pending Payments</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-4 text-center h-100 transition-hover">
                <div class="icon-box bg-info-soft rounded-circle mx-auto mb-3" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-tools text-info fs-4"></i>
                </div>
                <h3 class="fw-bold mb-1"><?= $stats['service_requests'] ?></h3>
                <p class="text-muted mb-0">Active Requests</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-4 text-center h-100 transition-hover">
                <div class="icon-box bg-warning-soft rounded-circle mx-auto mb-3" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-receipt text-warning fs-4"></i>
                </div>
                <h3 class="fw-bold mb-1">₹<?= number_format($stats['total_paid']) ?></h3>
                <p class="text-muted mb-0">Total Paid</p>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- AI Support Assistant -->
        <div class="col-lg-4 animate-fade-up" style="animation-delay: 0.2s;">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-4">
                        <div class="bg-success-soft rounded-circle p-3 me-3">
                            <i class="fas fa-robot text-success fs-4"></i>
                        </div>
                        <h5 class="mb-0 fw-bold">Tenant AI Support</h5>
                    </div>

                    <div class="ai-chat-box bg-light-blue p-3 rounded-4 mb-4" style="min-height: 150px;">
                        <p class="small mb-0"><i class="fas fa-info-circle text-primary me-2"></i>Welcome! I'm here to help. You can ask me about rent due dates, maintenance status, or community guidelines.</p>
                        <hr class="my-3 opacity-10">
                        <p class="small mb-0"><strong>Tip:</strong> You can now pay your rent via UPI for instant confirmation!</p>
                    </div>

                    <div class="d-grid">
                        <a href="contact.php" class="btn btn-success rounded-pill py-2 shadow-sm">
                            <i class="fas fa-comment-dots me-2"></i>Ask a Question
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rental Status -->
        <div class="col-lg-8 animate-fade-up" style="animation-delay: 0.3s;">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-0 py-4 px-4 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-key text-warning me-2"></i>My Active Lease</h5>
                    <button class="btn btn-sm btn-outline-success rounded-pill px-3">Download Lease</button>
                </div>
                <div class="card-body p-4">
                    <?php if ($stats['active_rentals'] > 0): ?>
                        <div class="alert alert-success-soft border-0 rounded-4 p-4">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h6 class="fw-bold mb-1">Modern Apartment - Block B</h6>
                                    <p class="small text-muted mb-0">Next Payment Due: <?= date('d M, Y', strtotime('+1 month')) ?></p>
                                </div>
                                <div class="col-md-4 text-md-end">
                                    <button class="btn btn-success rounded-pill px-4">Pay Rent</button>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-file-contract fs-1 mb-3 d-block"></i>
                            No active lease found. Interested in renting?
                            <a href="properties.php?type=rent" class="text-success text-decoration-none fw-bold">Browse Properties</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .bg-primary-soft {
        background-color: rgba(30, 58, 138, 0.1);
    }

    .bg-success-soft {
        background-color: rgba(25, 135, 84, 0.1);
    }

    .bg-warning-soft {
        background-color: rgba(255, 193, 7, 0.1);
    }

    .bg-danger-soft {
        background-color: rgba(220, 53, 69, 0.1);
    }

    .bg-info-soft {
        background-color: rgba(13, 202, 240, 0.1);
    }

    .bg-light-blue {
        background-color: #f0f7ff;
    }

    .alert-success-soft {
        background-color: rgba(25, 135, 84, 0.05);
        color: #198754;
    }

    .transition-hover {
        transition: all 0.3s ease;
    }

    .transition-hover:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05) !important;
    }

    .animate-fade-up {
        animation: fadeUp 0.6s ease forwards;
        opacity: 0;
    }

    @keyframes fadeUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/modern.php';
?>