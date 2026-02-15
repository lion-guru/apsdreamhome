<?php

/**
 * Modernized User Profile
 * Displays and manages user profile information for APS Dream Homes
 */

require_once __DIR__ . '/init.php';

// Check if user is logged in
if (!isset($_SESSION['uid'])) {
    header("Location: login.php");
    exit;
}

$db = \App\Core\App::database();
$uid = $_SESSION['uid'];
$user_data = null;
$msg = '';
$error = '';

// Handle feedback submission
if (isset($_POST['insert'])) {
    $content = trim($_POST['content'] ?? '');

    if (!empty($content)) {
        try {
            $success = $db->query("INSERT INTO feedback (uid, fdescription, status) VALUES (:uid, :content, '0')", [
                'uid' => $uid,
                'content' => $content
            ]);
            if ($success) {
                $msg = "Feedback sent successfully!";
            } else {
                $error = "Failed to send feedback. Please try again.";
            }
        } catch (Exception $e) {
            $error = "An error occurred while sending feedback.";
        }
    } else {
        $error = "Please provide your feedback content.";
    }
}

// Fetch user data from 'user' table (standardized for this project)
try {
    $user_data = $db->fetch("SELECT * FROM user WHERE uid = :uid", ['uid' => $uid]);
} catch (Exception $e) {
    error_log('Profile data fetch error: ' . $e->getMessage());
}

if (!$user_data) {
    header("Location: login.php?error=user_not_found");
    exit;
}

// Page variables
$page_title = 'My Profile | APS Dream Homes';
$layout = 'modern';

ob_start();
?>

<div class="container py-5 mt-5">
    <div class="row mb-5 animate-fade-up">
        <div class="col-md-8 d-flex align-items-center">
            <div class="position-relative me-4">
                <img src="<?= !empty($user_data['uimage']) ? h($user_data['uimage']) : 'https://ui-avatars.com/api/?name=' . urlencode($user_data['uname']) . '&size=120&background=1e3a8a&color=fff' ?>"
                    alt="Profile" class="rounded-circle shadow border border-4 border-white" style="width:120px; height:120px; object-fit:cover;">
                <span class="position-absolute bottom-0 end-0 bg-success border border-3 border-white rounded-circle p-2" title="Online"></span>
            </div>
            <div>
                <h1 class="display-6 fw-bold text-primary mb-1"><?= h($user_data['uname']) ?></h1>
                <p class="text-muted mb-0"><i class="fas fa-id-badge me-2 text-primary"></i><?= ucfirst(h($user_data['utype'])) ?> Account | <i class="fas fa-calendar-alt me-2 text-primary"></i>Member since <?= date('M Y') ?></p>
            </div>
        </div>
        <div class="col-md-4 text-md-end d-flex align-items-center justify-content-md-end mt-4 mt-md-0">
            <a href="edit-profile.php" class="btn btn-primary rounded-pill px-4 shadow-sm me-2">
                <i class="fas fa-edit me-2"></i>Edit Profile
            </a>
            <a href="<?= h($user_data['utype']) ?>_dashboard.php" class="btn btn-outline-primary rounded-pill px-4 shadow-sm">
                <i class="fas fa-th-large me-2"></i>Dashboard
            </a>
        </div>
    </div>

    <?php if ($msg): ?>
        <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
            <i class="fas fa-check-circle me-2"></i><?= $msg ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i><?= $error ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Profile Info -->
        <div class="col-lg-4 animate-fade-up" style="animation-delay: 0.1s;">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4"><i class="fas fa-info-circle text-primary me-2"></i>Account Information</h5>

                    <div class="info-item mb-3 p-3 bg-light rounded-4">
                        <small class="text-muted d-block mb-1">Full Name</small>
                        <span class="fw-bold"><?= h($user_data['uname']) ?></span>
                    </div>

                    <div class="info-item mb-3 p-3 bg-light rounded-4">
                        <small class="text-muted d-block mb-1">Email Address</small>
                        <span class="fw-bold"><?= h($user_data['uemail']) ?></span>
                    </div>

                    <div class="info-item mb-3 p-3 bg-light rounded-4">
                        <small class="text-muted d-block mb-1">Phone Number</small>
                        <span class="fw-bold"><?= h($user_data['uphone'] ?? 'Not provided') ?></span>
                    </div>

                    <div class="info-item p-3 bg-light rounded-4">
                        <small class="text-muted d-block mb-1">Account Type</small>
                        <span class="badge bg-primary-soft text-primary rounded-pill px-3"><?= ucfirst(h($user_data['utype'])) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Activity/Feedback -->
        <div class="col-lg-8 animate-fade-up" style="animation-delay: 0.2s;">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4"><i class="fas fa-paper-plane text-primary me-2"></i>Send Feedback</h5>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label small text-muted">How can we improve your experience?</label>
                            <textarea name="content" class="form-control border-0 bg-light rounded-4 p-3" rows="4" placeholder="Share your thoughts or report an issue..."></textarea>
                        </div>
                        <div class="text-end">
                            <button type="submit" name="insert" class="btn btn-primary rounded-pill px-5 shadow-sm">
                                <i class="fas fa-paper-plane me-2"></i>Send Feedback
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4"><i class="fas fa-history text-primary me-2"></i>Recent Activity</h5>
                    <div class="timeline">
                        <div class="timeline-item d-flex mb-3">
                            <div class="timeline-icon bg-success-soft text-success rounded-circle me-3 flex-shrink-0" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-sign-in-alt small"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold">Last Login</h6>
                                <p class="small text-muted mb-0">Today at <?= date('h:i A') ?></p>
                            </div>
                        </div>
                        <div class="timeline-item d-flex">
                            <div class="timeline-icon bg-primary-soft text-primary rounded-circle me-3 flex-shrink-0" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-user-edit small"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold">Profile Viewed</h6>
                                <p class="small text-muted mb-0">Just now from your device</p>
                            </div>
                        </div>
                    </div>
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

    .bg-light {
        background-color: #f8f9fa !important;
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

    .form-control:focus {
        box-shadow: none;
        background-color: #f0f7ff !important;
    }
</style>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/modern.php';
?>