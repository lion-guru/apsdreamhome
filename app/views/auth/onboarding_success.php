<?php
/**
 * Onboarding Success - 3D Digital ID Journey
 * Displays a premium holographic 3D ID card for newly joined team members.
 */
$base = defined('BASE_URL') ? BASE_URL : '/apsdreamhome';

// Fallback values if session data is missing
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$userName = $_SESSION['user_name'] ?? 'New Associate';
$userRole = $_SESSION['user_role'] ?? 'Associate';
$userId = $_SESSION['user_id'] ?? 'APS-000000';
$joinDate = date('M Y');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to the Team | <?= htmlspecialchars($userName) ?></title>
    <link href="<?= $base ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= $base ?>/assets/fonts/fontawesome/css/all.min.css" rel="stylesheet">
    <style>
        /* Dark & Sleek Premium Theme */
        body {
            background-color: #0f172a;
            color: #f8fafc;
            font-family: 'Inter', 'Segoe UI', sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow-x: hidden;
        }
        
        .onboarding-container {
            width: 100%;
            max-width: 1200px;
            padding: 40px 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            z-index: 10;
        }

        /* Ambient Background Glow */
        .ambient-glow {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 800px;
            height: 800px;
            background: radial-gradient(circle, rgba(13,148,136,0.15) 0%, rgba(15,23,42,0) 70%);
            z-index: 1;
            pointer-events: none;
        }

        /* Typography */
        .welcome-title {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 10px;
            background: linear-gradient(to right, #2dd4bf, #38bdf8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-align: center;
            opacity: 0;
            transform: translateY(20px);
            animation: fadeUp 0.8s forwards 0.2s;
        }

        .welcome-subtitle {
            font-size: 1.2rem;
            color: #94a3b8;
            margin-bottom: 50px;
            text-align: center;
            opacity: 0;
            transform: translateY(20px);
            animation: fadeUp 0.8s forwards 0.4s;
        }

        /* --- 3D ID Card Styles --- */
        .id-card-wrapper {
            perspective: 1500px;
            width: 100%;
            max-width: 400px;
            display: flex;
            justify-content: center;
            margin: 0 auto;
            opacity: 0;
            transform: scale(0.9) translateY(40px);
            animation: cardDrop 1s cubic-bezier(0.34, 1.56, 0.64, 1) forwards 0.6s;
        }

        .digital-id-card {
            width: 320px;
            height: 500px;
            position: relative;
            transform-style: preserve-3d;
            border-radius: 24px;
            /* Driven by JS variables */
            transform: rotateX(var(--rotate-x, 0deg)) rotateY(var(--rotate-y, 0deg));
            will-change: transform;
            cursor: pointer;
        }

        .card-layer {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border-radius: 24px;
            backface-visibility: hidden;
            overflow: hidden;
        }

        /* Front Face (Glassmorphism & Dark Mode) */
        .card-front {
            background: linear-gradient(135deg, rgba(30,41,59,0.9) 0%, rgba(15,23,42,0.95) 100%);
            border: 1px solid rgba(255,255,255,0.1);
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);
            display: flex;
            flex-direction: column;
        }

        /* Holographic Foil / Glare overlay */
        .card-glare {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border-radius: 24px;
            pointer-events: none;
            z-index: 50;
            /* Radial glare following mouse */
            background: radial-gradient(
                farthest-corner circle at var(--mouse-x, 50%) var(--mouse-y, 50%),
                rgba(255,255,255,0.1) 0%,
                rgba(255,255,255,0) 50%
            );
            mix-blend-mode: overlay;
        }

        /* Header of Card */
        .id-header {
            padding: 25px 25px 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        
        .id-logo {
            font-weight: 800;
            font-size: 1.2rem;
            color: #2dd4bf;
            letter-spacing: 1px;
        }

        .id-tag {
            background: rgba(45,212,191,0.1);
            color: #2dd4bf;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        /* Profile Section */
        .id-profile {
            padding: 30px 25px;
            text-align: center;
            flex-grow: 1;
            position: relative;
            z-index: 10;
        }

        .profile-img-wrap {
            width: 120px;
            height: 120px;
            margin: 0 auto 20px;
            border-radius: 50%;
            padding: 4px;
            background: linear-gradient(135deg, #2dd4bf 0%, #38bdf8 100%);
            box-shadow: 0 10px 25px rgba(45,212,191,0.2);
            /* Adds slight depth within the card */
            transform: translateZ(30px);
        }

        .profile-img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #1e293b;
            background: #cbd5e1;
        }

        .id-name {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 5px;
            color: white;
            transform: translateZ(20px);
        }

        .id-role {
            font-size: 1rem;
            color: #38bdf8;
            font-weight: 500;
            margin-bottom: 25px;
            transform: translateZ(20px);
        }

        /* ID Details Footer */
        .id-details {
            background: rgba(0,0,0,0.2);
            padding: 20px 25px;
            border-top: 1px solid rgba(255,255,255,0.05);
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }

        .detail-group {
            display: flex;
            flex-direction: column;
        }

        .detail-label {
            font-size: 0.7rem;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 4px;
        }

        .detail-value {
            font-size: 0.95rem;
            color: #e2e8f0;
            font-weight: 600;
            font-family: monospace;
        }

        .qr-placeholder {
            width: 50px;
            height: 50px;
            background: white;
            padding: 2px;
            border-radius: 4px;
            transform: translateZ(10px);
        }
        
        .qr-placeholder img {
            width: 100%;
            height: 100%;
            opacity: 0.9;
        }

        /* Lanyard / Clip */
        .id-clip {
            position: absolute;
            top: -15px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 20px;
            background: linear-gradient(to bottom, #94a3b8, #64748b);
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            z-index: 100;
        }
        .id-clip::before {
            content: '';
            position: absolute;
            top: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 14px;
            height: 16px;
            border: 3px solid #64748b;
            border-bottom: none;
            border-radius: 10px 10px 0 0;
        }

        /* Actions */
        .action-buttons {
            margin-top: 50px;
            display: flex;
            gap: 20px;
            opacity: 0;
            animation: fadeUp 0.8s forwards 1.2s;
        }

        .btn-custom {
            padding: 14px 32px;
            border-radius: 30px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 1rem;
        }

        .btn-dashboard {
            background: linear-gradient(135deg, #2dd4bf 0%, #0284c7 100%);
            color: white;
            border: none;
            box-shadow: 0 10px 25px rgba(45,212,191,0.3);
        }

        .btn-dashboard:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 35px rgba(45,212,191,0.4);
            color: white;
        }

        .btn-outline {
            background: transparent;
            color: #94a3b8;
            border: 1px solid #334155;
        }

        .btn-outline:hover {
            border-color: #94a3b8;
            color: white;
        }

        /* Animations */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes cardDrop {
            0% { opacity: 0; transform: scale(0.9) translateY(-100px) rotateX(20deg); }
            100% { opacity: 1; transform: scale(1) translateY(0) rotateX(0deg); }
        }

    </style>
</head>
<body>

    <div class="ambient-glow"></div>

    <div class="onboarding-container">
        <h1 class="welcome-title">Welcome to the Team</h1>
        <p class="welcome-subtitle">Your Digital ID has been successfully generated.</p>

        <div class="id-card-wrapper">
            <div class="digital-id-card">
                <div class="id-clip"></div>
                <div class="card-layer card-front">
                    <div class="card-glare"></div>
                    
                    <div class="id-header">
                        <div class="id-logo">APS Dreamhome</div>
                        <div class="id-tag">Active</div>
                    </div>
                    
                    <div class="id-profile">
                        <div class="profile-img-wrap">
                            <img src="<?= $base ?>/assets/images/user-placeholder.jpg" alt="Profile" class="profile-img">
                        </div>
                        <div class="id-name"><?= htmlspecialchars($userName) ?></div>
                        <div class="id-role"><?= htmlspecialchars($userRole) ?></div>
                    </div>

                    <div class="id-details">
                        <div class="detail-group">
                            <span class="detail-label">ID Number</span>
                            <span class="detail-value"><?= htmlspecialchars($userId) ?></span>
                        </div>
                        <div class="detail-group">
                            <span class="detail-label">Joined</span>
                            <span class="detail-value"><?= $joinDate ?></span>
                        </div>
                        <div class="qr-placeholder">
                            <!-- A static placeholder QR for aesthetics -->
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=<?= urlencode($userId) ?>" alt="QR">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="action-buttons">
            <a href="<?= $base ?>/dashboard" class="btn-custom btn-dashboard">Go to Dashboard <i class="fas fa-arrow-right ms-2"></i></a>
            <a href="#" class="btn-custom btn-outline" onclick="window.print()"><i class="fas fa-download me-2"></i>Save ID</a>
        </div>
    </div>

    <!-- Include 3D ID Card JS -->
    <script src="<?= $base ?>/assets/js/3d-id-card.js"></script>
</body>
</html>
