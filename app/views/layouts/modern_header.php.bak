<?php
/**
 * Modern Header - APS Dream Home
 * Enhanced responsive header with modern design
 */
?>

<?php
// DEPRECATED: This file is an unused template variation
// Modern header implementation not referenced anywhere in codebase
// Use includes/templates/header.php or includes/universal_template.php instead
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Preload critical fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Modern CSS Framework -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <!-- Custom Header Styles -->
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
            --secondary-gradient: linear-gradient(135deg, #d97706 0%, #b45309 100%);
            --glass-bg: rgba(255, 255, 255, 0.95);
            --glass-border: rgba(0, 0, 0, 0.1);
            --text-dark: #1e293b;
            --text-light: #64748b;
            --accent-color: #d97706;
            --primary-color: #1e40af;
        }

        /* Modern Header */
        .header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--glass-border);
            z-index: 1000;
            transition: all 0.3s ease;
            padding: 0.5rem 0;
        }

        .header.scrolled {
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
            padding: 0.3rem 0;
        }

        /* Logo */
        .logo {
            font-weight: 800;
            font-size: 1.5rem;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }

        .logo i {
            font-size: 1.8rem;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* Navigation Links */
        .nav-link {
            color: var(--text-dark) !important;
            font-weight: 500;
            padding: 0.5rem 1rem !important;
            margin: 0 0.2rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            background: rgba(30, 64, 175, 0.05);
            transform: translateY(-2px);
        }

        .nav-link.active {
            background: var(--primary-gradient);
            color: white !important;
            box-shadow: 0 4px 15px rgba(30, 64, 175, 0.2);
        }

        /* Logo and Branding */
        .logo {
            font-size: 1.5rem;
            font-weight: 800;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Navigation */
        .nav-link-modern {
            font-weight: 500;
            color: #2c3e50 !important;
            transition: all 0.3s ease;
            position: relative;
            padding: 0.5rem 1rem;
        }

        .nav-link-modern:hover {
            color: #667eea !important;
        }

        .nav-link-modern::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 50%;
            width: 0;
            height: 2px;
            background: var(--primary-gradient);
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }

        .nav-link-modern:hover::after {
            width: 100%;
        }

        /* CTA Buttons */
        .btn-header-cta {
            background: var(--primary-gradient);
            color: white;
            border-radius: 25px;
            padding: 0.5rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
        }

        .btn-header-cta:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }

        /* Mobile Menu */
        .mobile-menu-toggle {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: #2c3e50;
            cursor: pointer;
        }

        .mobile-menu {
            position: fixed;
            top: 0;
            right: -100%;
            width: 300px;
            height: 100vh;
            background: white;
            box-shadow: -5px 0 15px rgba(0, 0, 0, 0.1);
            transition: right 0.3s ease;
            z-index: 1001;
            padding: 2rem;
        }

        .mobile-menu.show {
            right: 0;
        }

        .mobile-menu-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .mobile-menu-backdrop.show {
            opacity: 1;
            visibility: visible;
        }

        /* Animations */
        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-slide-down {
            animation: slideInDown 0.5s ease-out;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .header {
                padding: 0.5rem 0;
            }

            .nav-link-modern {
                padding: 1rem 0;
                border-bottom: 1px solid #f8f9fa;
            }

            .btn-header-cta {
                width: 100%;
                margin-top: 1rem;
            }
        }
    </style>
</head>

            /* CTA Buttons */
            .btn-header-cta {
                background: var(--primary-gradient);
                color: white;
                border-radius: 25px;
                padding: 0.5rem 1.5rem;
                font-weight: 600;
                transition: all 0.3s ease;
                border: none;
            }

            .btn-header-cta:hover {
                transform: translateY(-2px);
                box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
            }

            /* Mobile Menu */
            .mobile-menu-toggle {
                background: none;
                border: none;
                font-size: 1.5rem;
                color: #2c3e50;
                cursor: pointer;
            }
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-modern" href="<?php echo BASE_URL; ?>contact">
                            <i class="fas fa-phone me-1"></i>Contact
                        </a>
                    </li>
                </ul>

                <!-- CTA Buttons -->
                <div class="d-flex gap-2">
                    <a href="<?php echo BASE_URL; ?>associate" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-users me-1"></i>Join as Associate
                    </a>
                    <a href="<?php echo BASE_URL; ?>admin" class="btn btn-header-cta btn-sm">
                        <i class="fas fa-tachometer-alt me-1"></i>Admin
                    </a>
                </div>
            </div>
        </nav>
    </div>
</header>

<!-- Mobile Menu -->
<div class="mobile-menu-backdrop" id="mobileMenuBackdrop"></div>
<div class="mobile-menu" id="mobileMenu">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="mb-0">Menu</h5>
        <button class="btn-close" id="mobileMenuClose"></button>
    </div>

    <nav class="nav flex-column">
        <a class="nav-link nav-link-modern" href="<?php echo BASE_URL; ?>">
            <i class="fas fa-home me-2"></i>Home
        </a>
        <a class="nav-link nav-link-modern" href="<?php echo BASE_URL; ?>properties">
            <i class="fas fa-building me-2"></i>Properties
        </a>
        <a class="nav-link nav-link-modern" href="<?php echo BASE_URL; ?>company-projects">
            <i class="fas fa-project-diagram me-2"></i>Projects
        </a>
        <a class="nav-link nav-link-modern" href="<?php echo BASE_URL; ?>about">
            <i class="fas fa-info-circle me-2"></i>About
        </a>
        <a class="nav-link nav-link-modern" href="<?php echo BASE_URL; ?>contact">
            <i class="fas fa-phone me-2"></i>Contact
        </a>
        <a class="nav-link nav-link-modern" href="<?php echo BASE_URL; ?>associate">
            <i class="fas fa-users me-2"></i>Join as Associate
        </a>
        <a class="nav-link nav-link-modern" href="<?php echo BASE_URL; ?>admin">
            <i class="fas fa-tachometer-alt me-2"></i>Admin Panel
        </a>
    </nav>
</div>

<!-- JavaScript for Mobile Menu -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const mobileMenuToggle = document.getElementById('mobileMenuToggle');
        const mobileMenu = document.getElementById('mobileMenu');
        const mobileMenuClose = document.getElementById('mobileMenuClose');
        const mobileMenuBackdrop = document.getElementById('mobileMenuBackdrop');

        function showMobileMenu() {
            mobileMenu.classList.add('show');
            mobileMenuBackdrop.classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        function hideMobileMenu() {
            mobileMenu.classList.remove('show');
            mobileMenuBackdrop.classList.remove('show');
            document.body.style.overflow = '';
        }

        mobileMenuToggle?.addEventListener('click', showMobileMenu);
        mobileMenuClose?.addEventListener('click', hideMobileMenu);
        mobileMenuBackdrop?.addEventListener('click', hideMobileMenu);

        // Close mobile menu when clicking on nav links
        mobileMenu?.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', hideMobileMenu);
        });
    });
</script>

<!-- Spacer for fixed header -->
<div style="height: 80px;"></div>
