<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    // Include site settings
    require_once __DIR__ . '/../../includes/site_settings.php';

    // Set HTTP security headers only if no output has been sent
    if (!headers_sent()) {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('X-XSS-Protection: 1; mode=block');
        header('Referrer-Policy: strict-origin-when-cross-origin');
    }
    ?>
    <meta name="description" content="<?php echo getSiteSetting('site_description', 'APS Dream Homes Pvt Ltd - Leading real estate developer in Gorakhpur with 8+ years of excellence'); ?>">
    <meta name="keywords" content="real estate, property, Gorakhpur, apartments, villas, plots, commercial, APS Dream Homes Pvt Ltd">
    <meta name="author" content="APS Dream Homes Pvt Ltd">

    <!-- Security Headers (set via PHP headers, not meta tags) -->
    <meta name="X-Content-Type-Options" content="nosniff">
    <meta name="Referrer-Policy" content="strict-origin-when-cross-origin">

    <title><?php echo getSiteSetting('site_title', 'APS Dream Homes Pvt Ltd'); ?></title>
<link rel="icon" href="<?= BASE_URL ?>assets/favicon.ico" type="image/x-icon">
<link rel="shortcut icon" href="<?= BASE_URL ?>assets/favicon.ico" type="image/x-icon">

    <!-- jQuery -->
    <script src="<?= BASE_URL ?>assets/js/jquery.min.js"></script>
    <!-- Bootstrap CSS -->
    <link href="<?= BASE_URL ?>assets/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/plugins/font-awesome/css/all.min.css">
    <!-- AOS Animation -->
    <link href="<?= BASE_URL ?>assets/vendor/css/aos.css" rel="stylesheet">

    <style>
        :root {
            --primary-color: #1e40af;
            --secondary-color: #3b82f6;
            --accent-color: #f59e0b;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --warning-color: #f59e0b;
            --info-color: #3b82f6;
            --light-color: #f8fafc;
            --dark-color: #1e293b;
            --real-estate-blue: #1e40af;
            --real-estate-gold: #d97706;
            --real-estate-gray: #64748b;
            --real-estate-green: #059669;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }

        /* APS Dream Home - Professional Real Estate Header */
        .navbar {
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 50%, #1e40af 100%) !important;
            -webkit-backdrop-filter: blur(25px);
            backdrop-filter: blur(25px);
            box-shadow: 0 4px 30px rgba(30, 64, 175, 0.15);
            padding: 0.75rem 0;
            width: 100%;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1030;
            border-bottom: 4px solid var(--real-estate-gold);
        }

        .navbar::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, transparent, var(--real-estate-gold), transparent);
        }

        /* Navbar Layout Optimization */
        .navbar-nav {
            flex: 1;
            justify-content: center;
            gap: 0.1rem;
        }

        .navbar .d-flex {
            flex: 0 0 auto;
            gap: 0.25rem;
        }

        /* Responsive Navbar Layout */
        @media (max-width: 991.98px) {
            .navbar-nav {
                gap: 0;
                width: 100%;
            }

            .navbar .d-flex {
                gap: 0.15rem;
                margin-top: 1rem;
                justify-content: center;
                width: 100%;
            }
        }

        @media (max-width: 575.98px) {
            .navbar .d-flex {
                gap: 0.1rem;
                flex-wrap: wrap;
            }

            .navbar .btn {
                flex: 1 1 auto;
                min-width: 0;
            }
        }

        /* Premium Navbar Styling */
        .premium-navbar {
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 50%, #1e40af 100%);
            -webkit-backdrop-filter: blur(20px);
            backdrop-filter: blur(20px);
            border-bottom: 3px solid var(--real-estate-gold);
            box-shadow: 0 8px 32px rgba(0,0,0,0.3);
            transition: all 0.3s ease;
            padding: clamp(0.4rem, 1vw, 0.8rem) 0;
        }

        .premium-navbar .container-fluid {
            padding-left: clamp(0.5rem, 1.5vw, 1rem);
            padding-right: clamp(0.5rem, 1.5vw, 1rem);
        }

        /* Enhanced Mobile Menu Button */
        .navbar-toggler.premium-toggler {
            border: none;
            padding: 0.5rem;
            background: rgba(255,255,255,0.1);
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .premium-toggler:focus {
            box-shadow: 0 0 0 0.2rem rgba(217, 119, 6, 0.25);
        }

        .premium-toggler:hover {
            background: rgba(255,255,255,0.2);
            transform: scale(1.05);
        }

        /* Premium Brand - Ultra Compact */
        .premium-brand {
            font-size: clamp(1rem, 2.5vw, 1.5rem);
            font-weight: 900;
            color: white !important;
            text-shadow: 0 3px 12px rgba(0,0,0,0.5);
            transition: all 0.4s ease;
            position: relative;
            background: linear-gradient(45deg, #ffffff, var(--real-estate-gold));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            min-width: auto;
            min-width: -webkit-fill-available;
            min-width: fit-content;
            max-width: clamp(150px, 20vw, 220px);
        }

        .brand-container {
            display: flex;
            align-items: center;
            gap: clamp(0.3rem, 1.2vw, 0.6rem);
        }

        .brand-logo {
            height: clamp(28px, 5vw, 40px);
            width: auto;
            max-width: clamp(32px, 7vw, 45px);
            transition: transform 0.3s ease;
            filter: drop-shadow(0 3px 8px rgba(0,0,0,0.3));
            border-radius: 6px;
            display: block;
        }

        .brand-icon {
            height: clamp(28px, 5vw, 40px);
            width: clamp(28px, 5vw, 40px);
            background: linear-gradient(135deg, var(--real-estate-gold), #f59e0b);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: clamp(0.8rem, 2vw, 1.2rem);
            box-shadow: 0 3px 8px rgba(217, 119, 6, 0.4);
            transition: transform 0.3s ease;
        }

        .brand-text {
            display: flex;
            flex-direction: column;
            line-height: 1;
        }

        .brand-title {
            font-size: clamp(0.8rem, 2vw, 1.2rem);
            font-weight: 900;
            letter-spacing: -0.3px;
        }

        .brand-subtitle {
            font-size: clamp(0.5rem, 1.2vw, 0.65rem);
            font-weight: 400;
            opacity: 0.9;
            text-transform: uppercase;
            letter-spacing: clamp(0.6px, 0.3vw, 1.2px);
        }

        /* Premium Navigation - Fully Responsive */
        .premium-nav .nav-link {
            font-weight: 600;
            font-size: 0.85rem;
            padding: 0.5rem 0.8rem;
            margin: 0 0.1rem;
            border-radius: 20px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            white-space: nowrap;
            min-height: 42px;
            display: flex;
            align-items: center;
            line-height: 1.2;
        }

        .premium-nav .nav-link i {
            font-size: clamp(0.65rem, 1.5vw, 0.8rem);
            margin-right: clamp(0.25rem, 0.8vw, 0.4rem);
        }

        /* Ultra compact navigation for smaller screens */
        @media (max-width: 1199.98px) {
            .premium-nav .nav-link {
                font-size: clamp(0.7rem, 1.6vw, 0.8rem);
                padding: clamp(0.35rem, 1vw, 0.5rem) clamp(0.5rem, 1.4vw, 0.7rem);
                margin: 0 clamp(0.03rem, 0.2vw, 0.08rem);
            }

            .premium-nav .nav-link i {
                font-size: clamp(0.7rem, 1.6vw, 0.8rem);
                margin-right: clamp(0.2rem, 0.6vw, 0.3rem);
            }
        }

        @media (max-width: 991.98px) {
            .premium-nav .nav-link {
                font-size: clamp(0.65rem, 1.4vw, 0.75rem);
                padding: clamp(0.3rem, 0.8vw, 0.4rem) clamp(0.4rem, 1vw, 0.5rem);
                margin: 0.05rem;
                min-height: clamp(36px, 3.5vw, 40px);
            }

            .premium-nav .nav-link i {
                font-size: clamp(0.65rem, 1.4vw, 0.75rem);
                margin-right: clamp(0.15rem, 0.4vw, 0.25rem);
            }
        }

        @media (max-width: 767.98px) {
            .premium-nav .nav-link {
                font-size: clamp(0.6rem, 1.2vw, 0.7rem);
                padding: clamp(0.25rem, 0.6vw, 0.35rem) clamp(0.35rem, 0.8vw, 0.45rem);
                margin: 0.03rem;
                min-height: clamp(34px, 3vw, 38px);
            }

            .premium-nav .nav-link i {
                font-size: clamp(0.6rem, 1.2vw, 0.7rem);
                margin-right: clamp(0.1rem, 0.3vw, 0.2rem);
            }
        }

        /* Premium Dropdown Toggles */
        .premium-dropdown-toggle {
            position: relative;
        }

        .premium-dropdown-toggle::after {
            content: '\f107';
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            margin-left: clamp(0.3rem, 1vw, 0.5rem);
            font-size: clamp(0.7rem, 1.5vw, 0.8rem);
            transition: transform 0.3s ease;
        }

        /* Premium Mega Menu - Desktop Optimized */
        .premium-mega-menu {
            width: 850px;
            max-width: 95vw;
            min-height: 380px;
            height: auto;
            max-height: 450px;
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border: none;
            border-radius: 16px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
            padding: 1.8rem;
            margin-top: 0.8rem;
            position: absolute;
            z-index: 1050;
            overflow-y: auto;
            left: 50%;
            transform: translateX(-50%);
        }

        .mega-menu-container {
            width: 100%;
        }

        .mega-header {
            color: var(--real-estate-gold);
            font-weight: 700;
            font-size: clamp(0.8rem, 2vw, 1rem);
            text-transform: uppercase;
            letter-spacing: clamp(0.5px, 0.3vw, 1px);
            border-bottom: 3px solid rgba(217, 119, 6, 0.3);
            padding-bottom: clamp(0.5rem, 1vw, 0.8rem);
            margin-bottom: clamp(0.8rem, 2vw, 1.2rem);
            position: relative;
        }

        .mega-header::after {
            content: '';
            position: absolute;
            bottom: -3px;
            left: 0;
            width: clamp(40px, 8vw, 60px);
            height: 3px;
            background: var(--real-estate-gold);
            border-radius: 2px;
        }

        .mega-item {
            display: block;
            padding: clamp(0.5rem, 1.5vw, 0.8rem) clamp(0.8rem, 2vw, 1.2rem);
            color: #374151;
            text-decoration: none;
            font-weight: 500;
            font-size: clamp(0.8rem, 2vw, 0.9rem);
            border-radius: clamp(6px, 1.5vw, 8px);
            transition: all 0.3s ease;
            margin-bottom: clamp(0.2rem, 0.5vw, 0.4rem);
            position: relative;
        }

        .mega-item:hover {
            background: linear-gradient(135deg, rgba(217, 119, 6, 0.1), rgba(245, 158, 11, 0.1));
            color: var(--real-estate-gold);
            transform: translateX(clamp(5px, 1.5vw, 8px));
            box-shadow: 0 clamp(2px, 0.5vw, 4px) clamp(8px, 2vw, 12px) rgba(217, 119, 6, 0.15);
        }

        .mega-item i {
            width: clamp(14px, 3vw, 18px);
            text-align: center;
            margin-right: clamp(0.3rem, 1vw, 0.5rem);
            font-size: clamp(0.8rem, 2vw, 1rem);
        }

        .mega-highlight {
            background: linear-gradient(135deg, #fef3c7, #fde68a);
            border: 1px solid #f59e0b;
            border-radius: clamp(6px, 1.5vw, 8px);
            padding: clamp(0.5rem, 1.5vw, 0.8rem);
            margin-top: clamp(0.5rem, 1.5vw, 1rem);
            text-align: center;
            color: #92400e;
            font-weight: 600;
            font-size: clamp(0.8rem, 2vw, 0.9rem);
        }

        .mega-sidebar {
            background: linear-gradient(135deg, #1e40af, #3b82f6);
            border-radius: clamp(8px, 2vw, 12px);
            padding: clamp(1rem, 2.5vw, 2rem);
            color: white;
            height: fit-content;
        }

        .mega-stats h6 {
            color: rgba(255,255,255,0.9);
            margin-bottom: clamp(1rem, 2vw, 1.5rem);
            font-size: clamp(0.9rem, 2vw, 1.1rem);
        }

        .stat-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: clamp(0.8rem, 1.5vw, 1rem);
            padding: clamp(0.5rem, 1vw, 0.8rem) 0;
            border-bottom: 1px solid rgba(255,255,255,0.2);
        }

        .stat-number {
            font-size: clamp(1.5rem, 4vw, 2rem);
            font-weight: 900;
            color: var(--real-estate-gold);
        }

        .stat-label {
            font-size: clamp(0.8rem, 1.8vw, 0.9rem);
            opacity: 0.9;
        }

        .mega-project-card {
            text-align: center;
        }

        .mega-project-card h6 {
            color: rgba(255,255,255,0.9);
            margin-bottom: clamp(0.8rem, 1.5vw, 1rem);
            font-size: clamp(0.9rem, 2vw, 1.1rem);
        }

        .mega-project-card p {
            font-size: clamp(0.8rem, 1.8vw, 0.9rem);
            opacity: 0.8;
            margin-bottom: clamp(1rem, 2vw, 1.5rem);
        }

        .mega-project-card .btn {
            padding: clamp(0.4rem, 1vw, 0.5rem) clamp(1rem, 2vw, 1.2rem);
            font-size: clamp(0.8rem, 1.8vw, 0.9rem);
            border-radius: clamp(20px, 3vw, 25px);
        }

        /* Premium Dropdowns - Desktop Optimized */
        .premium-dropdown {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border: none;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.12);
            padding: 1rem;
            min-width: 260px;
            max-width: 300px;
            position: absolute;
            top: 100%;
            left: 0;
            margin-top: 0.5rem;
        }

        .premium-dropdown .dropdown-header {
            color: var(--real-estate-gold);
            font-weight: 700;
            font-size: clamp(0.8rem, 1.8vw, 0.9rem);
            text-transform: uppercase;
            letter-spacing: clamp(0.5px, 0.3vw, 1px);
            border-bottom: 2px solid rgba(217, 119, 6, 0.3);
            padding-bottom: clamp(0.4rem, 1vw, 0.5rem);
            margin-bottom: clamp(0.8rem, 1.5vw, 1rem);
        }

        .premium-dropdown .dropdown-item {
            padding: clamp(0.6rem, 1.5vw, 0.8rem) clamp(0.8rem, 2vw, 1rem);
            border-radius: clamp(6px, 1.5vw, 8px);
            transition: all 0.3s ease;
            margin-bottom: clamp(0.2rem, 0.5vw, 0.3rem);
            font-size: clamp(0.8rem, 2vw, 0.9rem);
        }

        .premium-dropdown .dropdown-item:hover {
            background: linear-gradient(135deg, rgba(217, 119, 6, 0.1), rgba(245, 158, 11, 0.1));
            color: var(--real-estate-gold);
            transform: translateX(clamp(3px, 1vw, 5px));
        }

        .premium-dropdown .dropdown-item i {
            width: clamp(14px, 3vw, 16px);
            text-align: center;
            margin-right: clamp(0.4rem, 1vw, 0.5rem);
            font-size: clamp(0.8rem, 2vw, 0.9rem);
        }

        /* Premium Action Buttons - Desktop Optimized */
        .premium-actions {
            gap: 0.6rem;
            margin-left: 1rem;
            display: flex;
            align-items: center;
            flex-wrap: wrap;
        }

        .premium-actions .premium-btn {
            border-radius: 18px;
            font-weight: 600;
            padding: 0.45rem 0.75rem;
            font-size: 0.78rem;
            transition: all 0.3s ease;
            border: 2px solid transparent;
            white-space: nowrap;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.35rem;
            min-width: auto;
        }

        .premium-actions .premium-btn i {
            font-size: 0.9rem;
        }

        .premium-actions .premium-btn span {
            font-size: 0.74rem;
        }

        .premium-actions .premium-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        /* Enhanced Animations */
        .premium-mega-menu,
        .premium-dropdown {
            animation: slideDown 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            transform-origin: top center;
        }

        .dropdown-menu {
            animation: slideDown 0.3s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(clamp(-15px, -3vw, -20px)) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        /* Comprehensive Responsive Breakpoints */
        @media (max-width: 1399.98px) {
            .premium-brand {
                font-size: 1.5rem;
            }

            .brand-logo, .brand-icon {
                height: 42px;
                width: 42px;
            }

            .premium-nav .nav-link {
                padding: 0.6rem 0.9rem;
                font-size: 0.8rem;
            }

            .premium-actions .premium-btn {
                padding: 0.4rem 0.68rem;
                font-size: 0.74rem;
                min-width: auto;
            }

            .premium-mega-menu {
                width: 800px;
                padding: 1.5rem;
            }

            .premium-dropdown {
                min-width: 240px;
                max-width: 280px;
            }
        }

        @media (max-width: 1199.98px) {
            .premium-navbar {
                padding: 0.7rem 0;
            }

            .premium-brand {
                font-size: 1.3rem;
            }

            .brand-title {
                font-size: 1.1rem;
            }

            .brand-subtitle {
                font-size: 0.65rem;
            }

            .premium-nav .nav-link {
                margin: 0 0.08rem;
                padding: 0.5rem 0.7rem;
                font-size: 0.75rem;
            }

            .premium-actions {
                gap: 0.5rem;
                margin-left: 0.8rem;
            }

            .premium-actions .premium-btn {
                padding: 0.4rem 0.7rem;
                font-size: 0.7rem;
                min-width: auto;
            }

            .premium-mega-menu {
                width: 750px;
                padding: 1.3rem;
            }

            .premium-dropdown {
                min-width: 220px;
                max-width: 260px;
            }
        }

        @media (max-width: 991.98px) {
            .premium-navbar {
                padding: 0.8rem 0;
            }

            .brand-container {
                gap: clamp(0.4rem, 1.5vw, 0.6rem);
            }

            .brand-logo, .brand-icon {
                height: clamp(28px, 6vw, 35px);
                width: clamp(28px, 6vw, 35px);
            }

            .brand-title {
                font-size: clamp(0.8rem, 2vw, 1rem);
            }

            .brand-subtitle {
                font-size: clamp(0.5rem, 1.2vw, 0.65rem);
            }

            .premium-nav .nav-link {
                font-size: clamp(0.75rem, 1.8vw, 0.85rem);
                padding: clamp(0.35rem, 1vw, 0.5rem) clamp(0.5rem, 1.3vw, 0.7rem);
                margin: 0.1rem;
            }

            .premium-mega-menu {
                position: static !important;
                width: 100% !important;
                max-width: none !important;
                margin: clamp(0.8rem, 2vw, 1rem) 0 !important;
                border-radius: clamp(8px, 2vw, 12px) !important;
                max-height: none !important;
                min-height: auto !important;
            }

            .mega-sidebar {
                margin-top: 1.5rem;
                border-radius: clamp(8px, 2vw, 12px);
            }

            .premium-actions {
                gap: clamp(0.3rem, 1vw, 0.45rem) !important;
                margin-top: 1rem;
                justify-content: center;
                width: 100%;
            }

            .premium-actions .premium-btn {
                padding: 0.45rem 0.75rem;
                font-size: 0.76rem;
            }

            .premium-actions .premium-btn {
                flex: 1 1 auto;
                min-width: clamp(100px, 25vw, 120px);
            }
        }

        @media (max-width: 767.98px) {
            .premium-navbar {
                padding: 0.6rem 0;
            }

            .premium-brand {
                font-size: clamp(0.9rem, 2.2vw, 1.1rem);
            }

            .brand-container {
                gap: clamp(0.3rem, 1vw, 0.4rem);
            }

            .brand-logo, .brand-icon {
                height: clamp(24px, 5vw, 28px);
                width: clamp(24px, 5vw, 28px);
            }

            .brand-title {
                font-size: clamp(0.7rem, 1.8vw, 0.85rem);
            }

            .brand-subtitle {
                font-size: clamp(0.45rem, 1vw, 0.55rem);
                letter-spacing: clamp(0.5px, 0.2vw, 1px);
            }

            .premium-nav .nav-link {
                font-size: clamp(0.7rem, 1.5vw, 0.8rem);
                padding: clamp(0.3rem, 0.8vw, 0.4rem) clamp(0.4rem, 1vw, 0.5rem);
                margin: 0.05rem;
            }

            .premium-mega-menu {
                padding: clamp(0.8rem, 2vw, 1rem);
                margin: clamp(0.5rem, 1.5vw, 0.8rem) 0 !important;
                min-height: auto;
            }

            .mega-header {
                font-size: clamp(0.75rem, 1.8vw, 0.85rem);
                margin-bottom: clamp(0.6rem, 1.5vw, 0.8rem);
            }

            .mega-item {
                padding: clamp(0.4rem, 1vw, 0.5rem) clamp(0.6rem, 1.5vw, 0.8rem);
                font-size: clamp(0.75rem, 1.8vw, 0.85rem);
            }

            .premium-dropdown {
                min-width: clamp(200px, 35vw, 250px);
                padding: clamp(0.6rem, 1.5vw, 0.8rem);
            }

            .premium-dropdown .dropdown-header {
                font-size: clamp(0.75rem, 1.8vw, 0.85rem);
            }

            .premium-dropdown .dropdown-item {
                padding: clamp(0.5rem, 1.2vw, 0.6rem) clamp(0.6rem, 1.5vw, 0.8rem);
                font-size: clamp(0.75rem, 1.8vw, 0.85rem);
            }
        }

        @media (max-width: 575.98px) {
            .premium-navbar {
                padding: 0.3rem 0;
            }

            .brand-container {
                gap: clamp(0.2rem, 0.8vw, 0.3rem);
            }

            .brand-logo, .brand-icon {
                height: clamp(20px, 4vw, 24px);
                width: clamp(20px, 4vw, 24px);
            }

            .brand-title {
                font-size: clamp(0.65rem, 1.5vw, 0.75rem);
            }

            .brand-subtitle {
                font-size: clamp(0.4rem, 0.8vw, 0.45rem);
                letter-spacing: 0.5px;
            }

            .premium-nav .nav-link {
                font-size: clamp(0.65rem, 1.3vw, 0.7rem);
                padding: clamp(0.25rem, 0.6vw, 0.3rem) clamp(0.3rem, 0.8vw, 0.4rem);
                margin: 0.02rem;
            }

            .premium-mega-menu {
                width: 100vw !important;
                max-width: 100vw !important;
                left: -50vw !important;
                right: -50vw !important;
                margin-left: 50vw !important;
                margin-right: 50vw !important;
                border-radius: 0 !important;
                padding: clamp(0.6rem, 1.5vw, 0.8rem);
            }

            .mega-sidebar {
                margin-top: 1rem;
                padding: clamp(0.8rem, 2vw, 1rem);
            }

            .premium-actions {
                flex-direction: column;
                align-items: stretch;
                gap: clamp(0.4rem, 1vw, 0.5rem) !important;
            }

            .premium-actions .premium-btn {
                width: 100%;
                justify-content: center;
            }
        }

        /* Ultra-wide screens */
        @media (min-width: 1400px) {
            .premium-brand {
                font-size: clamp(1.8rem, 2.2vw, 2rem);
            }

            .brand-logo {
                height: clamp(50px, 6vw, 60px);
            }

            .brand-icon {
                height: clamp(50px, 6vw, 60px);
                width: clamp(50px, 6vw, 60px);
            }

            .premium-nav .nav-link {
                padding: clamp(0.8rem, 1vw, 1rem) clamp(1.2rem, 1.5vw, 1.5rem);
            }
        }

        /* Touch device optimizations */
        @media (hover: none) and (pointer: coarse) {
            .premium-nav .nav-link,
            .mega-item,
            .premium-dropdown .dropdown-item {
                transition: none;
                transform: none !important;
            }

            .premium-nav .nav-link:hover,
            .mega-item:hover,
            .premium-dropdown .dropdown-item:hover {
                transform: none;
                background: rgba(217, 119, 6, 0.1);
            }
        }

        /* High DPI displays */
        @media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
            .brand-logo {
                image-rendering: -webkit-optimize-contrast;
                image-rendering: crisp-edges;
            }
        }

        /* Reduced motion preference */
        @media (prefers-reduced-motion: reduce) {
            * {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }

        /* Print styles */
        @media print {
            .premium-navbar {
                background: #1e40af !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .premium-mega-menu,
            .premium-dropdown {
                display: none !important;
            }

            .premium-brand {
                color: white !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }

        /* Professional Navigation Links */
        .navbar-nav .nav-link {
            color: rgba(255,255,255,0.95) !important;
            font-weight: 600;
            padding: 0.75rem 1.5rem;
            margin: 0 0.2rem;
            border-radius: 15px;
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
            font-size: 0.95rem;
            letter-spacing: 0.3px;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
        }

        .navbar-nav .nav-link::before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            width: 0;
            height: 2px;
            background: var(--real-estate-gold);
            transition: all 0.3s ease;
            transform: translateX(-50%);
            border-radius: 1px;
        }

        .navbar-nav .nav-link:hover::before,
        .navbar-nav .nav-link.active::before {
            width: 85%;
        }

        .navbar-nav .nav-link:hover {
            color: white !important;
            background: rgba(255,255,255,0.15);
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
            border-color: var(--real-estate-gold);
        }

        .navbar-nav .nav-link.active {
            background: linear-gradient(135deg, rgba(217, 119, 6, 0.3), rgba(245, 158, 11, 0.2));
            color: var(--real-estate-gold) !important;
            border-color: var(--real-estate-gold);
        }

        .navbar-nav .nav-link i {
            margin-right: 0.5rem;
            font-size: 1rem;
            transition: transform 0.3s ease;
        }

        .navbar-nav .nav-link:hover i {
            transform: scale(1.1) rotate(5deg);
        }

        /* Premium Real Estate Dropdown Menus */
        .navbar-nav .dropdown-menu {
            background: rgba(255,255,255,0.98);
            -webkit-backdrop-filter: blur(20px);
            backdrop-filter: blur(20px);
            border: none;
            box-shadow: 0 15px 45px rgba(0,0,0,0.15);
            border-radius: 20px;
            margin-top: 10px;
            padding: 1.5rem 0;
            min-width: 320px;
            border-top: 5px solid var(--real-estate-gold);
            animation: slideDownFade 0.3s ease-out;
        }

        @keyframes slideDownFade {
            from {
                opacity: 0;
                transform: translateY(-20px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .navbar-nav .dropdown-item {
            color: #374151;
            padding: 1rem 2rem;
            font-weight: 500;
            transition: all 0.3s ease;
            cursor: pointer;
            border-radius: 12px;
            margin: 0.3rem 1rem;
            font-size: 0.95rem;
            background: rgba(255,255,255,0.5);
            border-left: 4px solid transparent;
        }

        .navbar-nav .dropdown-item:hover,
        .navbar-nav .dropdown-item:focus {
            background: linear-gradient(135deg, var(--real-estate-blue), var(--secondary-color));
            color: white;
            transform: translateX(10px);
            box-shadow: 0 6px 20px rgba(30, 64, 175, 0.3);
            border-left-color: var(--real-estate-gold);
        }

        .navbar-nav .dropdown-header {
            color: var(--real-estate-blue);
            font-weight: 800;
            padding: 1.25rem 2rem;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            border-bottom: 2px solid rgba(30, 64, 175, 0.1);
            margin-bottom: 1rem;
            font-size: 0.8rem;
        }

        .navbar-nav .dropdown-toggle::after {
            margin-left: 0.75em;
            vertical-align: middle;
            border-top: 0.5em solid;
            border-right: 0.5em solid transparent;
            border-bottom: 0;
            border-left: 0.5em solid transparent;
            transition: transform 0.3s ease;
        }

        .navbar-nav .dropdown-toggle[aria-expanded="true"]::after {
            transform: rotate(180deg);
        }

        /* Professional Action Buttons */
        .navbar .btn {
            border-radius: 15px;
            font-weight: 700;
            padding: 0.7rem 1.8rem;
            transition: all 0.3s ease;
            border: 2px solid transparent;
            font-size: 0.9rem;
            letter-spacing: 0.5px;
        }

        .navbar .btn-outline-success {
            color: var(--success-color);
            border-color: var(--success-color);
            background: rgba(16, 185, 129, 0.1);
        }

        .navbar .btn-outline-success:hover {
            background: var(--success-color);
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4);
        }

        .navbar .btn-outline-light {
            color: white;
            border-color: rgba(255,255,255,0.6);
            background: rgba(255,255,255,0.1);
        }

        .navbar .btn-outline-light:hover {
            background: rgba(255,255,255,0.2);
            color: white;
            border-color: white;
            transform: translateY(-3px);
        }

        .navbar .btn-success {
            background: linear-gradient(135deg, var(--success-color), #059669);
            border: none;
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.3);
        }

        .navbar .btn-success:hover {
            background: linear-gradient(135deg, #059669, var(--success-color));
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(16, 185, 129, 0.5);
        }

        /* Search Bar Styling - Legacy (kept for compatibility) */
        .navbar .form-control-sm {
            border-radius: 30px;
            border: 2px solid rgba(255,255,255,0.3);
            background: rgba(255,255,255,0.9);
            -webkit-backdrop-filter: blur(10px);
            backdrop-filter: blur(10px);
        }

        .navbar .form-control-sm:focus {
            border-color: var(--real-estate-gold);
            box-shadow: 0 0 0 0.2rem rgba(217, 119, 6, 0.25);
        }

        .navbar .input-group .btn-outline-light {
            border-radius: 30px;
            border-left: none;
        }

        .navbar .input-group .btn-outline-light:hover {
            background: #b45309;
            border-color: #b45309;
        }

        /* Mobile Responsive Design - Enhanced */
        @media (max-width: 991.98px) {
            .navbar {
                padding: 0.3rem 0;
            }

            .navbar-brand {
                font-size: 1.6rem;
            }

            .navbar-nav .dropdown-menu {
                position: static;
                float: none;
                width: auto;
                margin-top: 0;
                background: rgba(255,255,255,0.95);
                border: 0;
                box-shadow: none;
                border-radius: 0;
                animation: none;
                transform: none;
            }

            .navbar-nav .dropdown-item {
                color: rgba(255,255,255,0.9);
                padding: 0.75rem 1.5rem;
            }

            .navbar-nav .dropdown-item:hover {
                color: white;
                background: rgba(255,255,255,0.15);
                transform: none;
            }

            .navbar .btn {
                padding: 0.5rem 1rem;
                font-size: 0.85rem;
            }
        }

        /* Extra Small Devices (Portrait phones, less than 576px) */
        @media (max-width: 575.98px) {
            .navbar-brand {
                font-size: 1.3rem;
            }

            .navbar-brand img {
                max-height: 35px;
                margin-right: 0.3rem;
            }

            .navbar .btn {
                padding: 0.4rem 0.8rem;
                font-size: 0.8rem;
            }

            .navbar .btn i {
                margin-right: 0.2rem;
            }

            .navbar .btn .d-none {
                display: none !important;
            }

            .navbar .dropdown-menu {
                min-width: 250px;
            }

            .navbar .form-control-sm {
                width: 150px;
            }
        }

        /* Container Responsive Adjustments */
        @media (max-width: 1199.98px) {
            .navbar .container {
                max-width: 100%;
                padding-left: 12px;
                padding-right: 12px;
            }
        }

        @media (max-width: 991.98px) {
            .navbar .container {
                padding-left: 10px;
                padding-right: 10px;
            }
        }

        @media (max-width: 575.98px) {
            .navbar .container {
                padding-left: 8px;
                padding-right: 8px;
            }
        }

        /* Touch Device Optimization */
        @media (hover: none) and (pointer: coarse) {
            .navbar .btn,
            .navbar-nav .nav-link,
            .navbar .dropdown-item {
                min-height: 44px;
                display: flex;
                align-items: center;
            }

            .navbar-toggler {
                min-height: 48px;
                min-width: 48px;
            }
        }

        /* High DPI/Retina Display Support */
        @media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
            .navbar-brand img {
                image-rendering: -webkit-optimize-contrast;
                image-rendering: crisp-edges;
            }
        }

        /* Print Styles */
        @media print {
            .navbar {
                position: static;
                background: white !important;
                color: black !important;
                box-shadow: none;
                border-bottom: 1px solid #ccc;
            }

            .navbar .btn,
            .navbar .dropdown-menu {
                display: none !important;
            }
        }

        /* Dark Mode Support (if needed) */
        @media (prefers-color-scheme: dark) {
            .navbar {
                background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%) !important;
            }
        }

        /* Enhanced Animations */
        .navbar-toggler {
            border: none;
            padding: 0.5rem;
            background: rgba(255,255,255,0.1);
            border-radius: 10px;
        }

        .navbar-toggler:focus {
            box-shadow: 0 0 0 0.2rem rgba(217, 119, 6, 0.25);
        }

        /* Phone number styling */
        .navbar .btn-outline-success i {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.15); }
            100% { transform: scale(1); }
        }

        /* Main content spacing */
        .main-content {
            margin-top: 100px !important;
            min-height: calc(100vh - 100px);
        }

        /* Loading states */
        .loading-link {
            position: relative;
            pointer-events: none;
            opacity: 0.8;
        }

        .loading-link::after {
            content: '';
            position: absolute;
            top: 50%;
            right: 0.5rem;
            transform: translateY(-50%);
            width: 14px;
            height: 14px;
            border: 2px solid rgba(255,255,255,0.3);
            border-top: 2px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: translateY(-50%) rotate(0deg); }
            100% { transform: translateY(-50%) rotate(360deg); }
        }

        /* Ensure dropdown menus are visible and functional */
        .dropdown-menu {
            display: none;
            min-width: 200px;
        }

        .dropdown-menu.show {
            display: block !important;
        }

        /* Ensure dropdown items have proper spacing */
        .dropdown-item {
            padding: 0.5rem 1rem;
            clear: both;
            font-weight: normal;
            line-height: 1.5;
            text-align: inherit;
            white-space: nowrap;
        }

        /* Fix z-index issues */
        .navbar {
            z-index: 1030;
        }

        .navbar .dropdown-menu {
            z-index: 1040;
        }

        /* Ensure proper click areas */
        .navbar-nav .nav-link,
        .navbar .btn {
            position: relative;
            z-index: 1;
        }

        /* Fix for mobile menu button */
        .navbar-toggler {
            border: none;
            padding: 0.5rem;
        }

        .navbar-toggler:focus {
            box-shadow: 0 0 0 0.2rem rgba(217, 119, 6, 0.25);
        }

        /* Ensure dropdowns are properly positioned */
        .dropdown-menu {
            transform: none !important;
            opacity: 1 !important;
        }

        /* Fix dropdown toggle positioning */
        .dropdown-toggle::after {
            transition: transform 0.3s ease;
        }

        /* Ensure dropdown items are clickable */
        .dropdown-item {
            cursor: pointer;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }

        /* Override any Bootstrap positioning issues */
        .navbar .dropdown-menu {
            top: 100% !important;
            left: 0 !important;
            margin-top: 0.5rem !important;
        }

        .navbar .dropdown-menu.dropdown-menu-end {
            right: 0 !important;
            left: auto !important;
        }

        /* Mega Menu Styling */
        .mega-menu {
            width: 100%;
            max-width: 800px;
            min-height: 300px;
            max-height: 500px;
            overflow-y: auto;
            box-shadow: 0 8px 30px rgba(0,0,0,0.15);
            border: none;
            border-radius: 12px;
            padding: 1rem;
        }

        .mega-menu::-webkit-scrollbar {
            width: 6px;
        }

        .mega-menu::-webkit-scrollbar-track {
            background: rgba(255,255,255,0.1);
            border-radius: 3px;
        }

        .mega-menu::-webkit-scrollbar-thumb {
            background: var(--real-estate-gold);
            border-radius: 3px;
        }

        .mega-menu::-webkit-scrollbar-thumb:hover {
            background: #b45309;
        }

        .mega-menu-content {
            width: 100%;
        }

        .mega-menu .dropdown-header {
            color: var(--real-estate-gold);
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid rgba(217, 119, 6, 0.3);
            padding-bottom: 0.5rem;
            margin-bottom: 0.8rem;
        }

        .mega-menu .dropdown-item {
            padding: 0.6rem 1rem;
            font-size: 0.9rem;
            border-radius: 6px;
            transition: all 0.3s ease;
            margin-bottom: 0.2rem;
        }

        .mega-menu .dropdown-item:hover {
            background: linear-gradient(135deg, rgba(217, 119, 6, 0.1), rgba(245, 158, 11, 0.1));
            color: var(--real-estate-gold);
            transform: translateX(5px);
        }

        .mega-menu .dropdown-item i {
            width: 16px;
            text-align: center;
        }

        /* Enhanced dropdown animations */
        .dropdown-menu {
            animation: slideDown 0.3s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <!-- APS Dream Home - Enhanced Professional Real Estate Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top premium-navbar">
        <div class="container-fluid px-3">
            <!-- Premium Brand Section -->
            <a class="navbar-brand premium-brand" href="<?= BASE_URL ?>" title="APS Dream Homes - Premium Real Estate Developer">
                <div class="brand-container">
                    <?php
                    $site_title = getSiteSetting('site_title', 'APS Dream Homes');
                    $logoPath = getSiteSetting('logo_path', 'assets/images/logo/apslogo.png');

                    if (!empty($logoPath) && file_exists($logoPath)) {
                        echo '<img src="' . $logoPath . '" alt="' . $site_title . ' Logo" class="brand-logo" loading="lazy">';
                    } else {
                        echo '<div class="brand-icon" title="APS Dream Homes"><i class="fas fa-home" aria-hidden="true"></i></div>';
                    }
                    ?>
                    <div class="brand-text">
                        <span class="brand-title"><?php echo htmlspecialchars(str_replace(' Pvt Ltd', '', $site_title)); ?></span>
                        <span class="brand-subtitle">Real Estate</span>
                    </div>
                </div>
            </a>

            <!-- Enhanced Mobile Menu Button -->
            <button class="navbar-toggler premium-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="toggler-icon"></span>
                <span class="toggler-text">MENU</span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto premium-nav">
                    <!-- Properties Mega Menu -->
                    <li class="nav-item dropdown mega-dropdown">
                        <a class="nav-link dropdown-toggle premium-dropdown-toggle" href="#" id="propertiesDropdown" title="Browse Properties" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-home me-1" aria-hidden="true"></i>Properties
                        </a>
                        <div class="dropdown-menu premium-mega-menu" aria-labelledby="propertiesDropdown">
                            <div class="mega-menu-container">
                                <div class="row g-0">
                                    <div class="col-lg-8">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <h6 class="mega-header"><i class="fas fa-search me-2"></i>Browse Properties</h6>
                                                <a class="mega-item" href="<?php echo BASE_URL; ?>properties" title="View all available properties"><i class="fas fa-th-large me-2"></i>All Properties</a>
                                                <a class="mega-item" href="<?php echo BASE_URL; ?>properties?type=residential" title="Browse residential properties"><i class="fas fa-home me-2"></i>Residential</a>
                                                <a class="mega-item" href="<?php echo BASE_URL; ?>properties?type=commercial" title="Browse commercial properties"><i class="fas fa-building me-2"></i>Commercial</a>
                                                <a class="mega-item" href="<?php echo BASE_URL; ?>properties?type=plots" title="Browse land and plots"><i class="fas fa-map me-2"></i>Plots</a>
                                            </div>
                                            <div class="col-md-6">
                                                <h6 class="mega-header"><i class="fas fa-star me-2"></i>Featured</h6>
                                                <a class="mega-item" href="<?php echo BASE_URL; ?>featured-properties" title="View featured properties"><i class="fas fa-star me-2"></i>Featured Properties</a>
                                                <a class="mega-item" href="<?php echo BASE_URL; ?>resell" title="Browse resale properties"><i class="fas fa-recycle me-2"></i>Resale Properties</a>
                                                <div class="mega-highlight">
                                                    <i class="fas fa-fire text-warning me-2" aria-hidden="true"></i>
                                                    <span>Hot Deals Available!</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 mega-sidebar">
                                        <div class="mega-stats">
                                            <h6><i class="fas fa-chart-line me-2"></i>Property Stats</h6>
                                            <div class="stat-item">
                                                <span class="stat-number">500+</span>
                                                <span class="stat-label">Properties</span>
                                            </div>
                                            <div class="stat-item">
                                                <span class="stat-number">50+</span>
                                                <span class="stat-label">Locations</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>

                    <!-- Projects Mega Menu -->
                    <li class="nav-item dropdown mega-dropdown">
                        <a class="nav-link dropdown-toggle premium-dropdown-toggle" href="#" id="projectsDropdown" title="Browse Projects by Location and Status" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-project-diagram me-1" aria-hidden="true"></i>Projects
                        </a>
                        <div class="dropdown-menu premium-mega-menu" aria-labelledby="projectsDropdown">
                            <div class="mega-menu-container">
                                <div class="row g-0">
                                    <div class="col-lg-8">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <h6 class="mega-header"><i class="fas fa-list me-2"></i>Quick Access</h6>
                                                <a class="mega-item" href="<?php echo BASE_URL; ?>projects"><i class="fas fa-th-large me-2"></i>All Projects</a>
                                                <a class="mega-item" href="<?php echo BASE_URL; ?>projects?status=upcoming"><i class="fas fa-calendar-plus me-2"></i>Upcoming</a>
                                                <a class="mega-item" href="<?php echo BASE_URL; ?>projects?status=ongoing"><i class="fas fa-play-circle me-2"></i>Ongoing</a>
                                                <a class="mega-item" href="<?php echo BASE_URL; ?>projects?status=completed"><i class="fas fa-check-circle me-2"></i>Completed</a>
                                            </div>
                                            <div class="col-md-4">
                                                <h6 class="mega-header"><i class="fas fa-map-marker-alt me-2"></i>Locations</h6>
                                                <a class="mega-item" href="<?php echo BASE_URL; ?>projects?location=Gorakhpur"><i class="fas fa-map-marker-alt me-2"></i>Gorakhpur</a>
                                                <a class="mega-item" href="<?php echo BASE_URL; ?>projects?location=Lucknow"><i class="fas fa-map-marker-alt me-2"></i>Lucknow</a>
                                                <a class="mega-item" href="<?php echo BASE_URL; ?>projects?location=Varanasi"><i class="fas fa-map-marker-alt me-2"></i>Varanasi</a>
                                                <a class="mega-item" href="<?php echo BASE_URL; ?>projects?location=Allahabad"><i class="fas fa-map-marker-alt me-2"></i>Allahabad</a>
                                            </div>
                                            <div class="col-md-4">
                                                <h6 class="mega-header"><i class="fas fa-building me-2"></i>Featured Projects</h6>
                                                <?php
                                                try {
                                                    require_once __DIR__ . '/../../includes/db_connection.php';
                                                    $pdo = getMysqliConnection();
                                                    if ($pdo) {
                                                        $projectsQuery = $pdo->query("SELECT id, name, location, status FROM projects WHERE status = 'active' ORDER BY location, name LIMIT 4");

                                                        if ($projectsQuery && $projectsQuery->num_rows > 0) {
                                                            while ($project = $projectsQuery->fetch(PDO::FETCH_ASSOC)) {
                                                                echo '<a class="mega-item" href="' . BASE_URL . 'project?id=' . $project['id'] . '"><i class="fas fa-building me-2"></i>' .
                                                                     htmlspecialchars($project['name'] ?? '') . '</a>';
                                                            }
                                                        }
                                                    } else {
                                                        echo '<span class="text-muted">Loading projects...</span>';
                                                    }
                                                } catch (Exception $e) {
                                                    echo '<span class="text-muted">Loading projects...</span>';
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 mega-sidebar">
                                        <div class="mega-project-card">
                                            <i class="fas fa-rocket fa-2x text-primary mb-3"></i>
                                            <h6>Project Updates</h6>
                                            <p>Latest developments and upcoming launches</p>
                                            <a href="<?php echo BASE_URL; ?>projects" class="btn btn-primary btn-sm">View All</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>

                    <!-- Services -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle premium-dropdown-toggle" href="#" id="servicesDropdown" title="Our Real Estate Services" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-cogs me-1" aria-hidden="true"></i>Services
                        </a>
                        <ul class="dropdown-menu premium-dropdown" aria-labelledby="servicesDropdown">
                            <li><h6 class="dropdown-header"><i class="fas fa-handshake me-1"></i>Our Services</h6></li>
                            <li><a class="dropdown-item premium-item" href="<?php echo BASE_URL; ?>property-management"><i class="fas fa-cog me-2"></i>Property Management</a></li>
                            <li><a class="dropdown-item premium-item" href="<?php echo BASE_URL; ?>legal-services"><i class="fas fa-gavel me-2"></i>Legal Services</a></li>
                            <li><a class="dropdown-item premium-item" href="<?php echo BASE_URL; ?>financial-services"><i class="fas fa-rupee-sign me-2"></i>Financial Services</a></li>
                            <li><a class="dropdown-item premium-item" href="<?php echo BASE_URL; ?>interior-design"><i class="fas fa-palette me-2"></i>Interior Design</a></li>
                        </ul>
                    </li>

                    <!-- About -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle premium-dropdown-toggle" href="#" id="aboutDropdown" title="Learn About Our Company" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-info-circle me-1" aria-hidden="true"></i>About
                        </a>
                        <ul class="dropdown-menu premium-dropdown" aria-labelledby="aboutDropdown">
                            <li><h6 class="dropdown-header"><i class="fas fa-building me-1"></i>Company</h6></li>
                            <li><a class="dropdown-item premium-item" href="<?php echo BASE_URL; ?>about"><i class="fas fa-building me-2"></i>Company Overview</a></li>
                            <li><a class="dropdown-item premium-item" href="<?php echo BASE_URL; ?>team"><i class="fas fa-users me-2"></i>Our Team</a></li>
                            <li><a class="dropdown-item premium-item" href="<?php echo BASE_URL; ?>testimonials"><i class="fas fa-comments me-2"></i>Testimonials</a></li>
                            <li><a class="dropdown-item premium-item" href="<?php echo BASE_URL; ?>faq"><i class="fas fa-question-circle me-2"></i>FAQs</a></li>
                        </ul>
                    </li>

                    <!-- Resources -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle premium-dropdown-toggle" href="#" id="resourcesDropdown" title="Real Estate Resources and Media" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-folder me-1" aria-hidden="true"></i>Resources
                        </a>
                        <ul class="dropdown-menu premium-dropdown" aria-labelledby="resourcesDropdown">
                            <li><h6 class="dropdown-header"><i class="fas fa-newspaper me-1"></i>Content</h6></li>
                            <li><a class="dropdown-item premium-item" href="<?php echo BASE_URL; ?>blog"><i class="fas fa-blog me-2"></i>Blog</a></li>
                            <li><a class="dropdown-item premium-item" href="<?php echo BASE_URL; ?>news"><i class="fas fa-newspaper me-2"></i>News & Updates</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><h6 class="dropdown-header"><i class="fas fa-images me-1"></i>Media</h6></li>
                            <li><a class="dropdown-item premium-item" href="<?php echo BASE_URL; ?>gallery"><i class="fas fa-images me-2"></i>Gallery</a></li>
                            <li><a class="dropdown-item premium-item" href="<?php echo BASE_URL; ?>downloads"><i class="fas fa-download me-2"></i>Downloads</a></li>
                        </ul>
                    </li>

                    <!-- Quick Links -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle premium-dropdown-toggle" href="#" id="quickLinksDropdown" title="Quick Access Links" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-link me-1" aria-hidden="true"></i>Quick Links
                        </a>
                        <ul class="dropdown-menu premium-dropdown" aria-labelledby="quickLinksDropdown">
                            <li><h6 class="dropdown-header"><i class="fas fa-briefcase me-1"></i>Career & Contact</h6></li>
                            <li><a class="dropdown-item premium-item" href="<?php echo BASE_URL; ?>career"><i class="fas fa-briefcase me-2"></i>Careers</a></li>
                            <li><a class="dropdown-item premium-item" href="<?php echo BASE_URL; ?>contact"><i class="fas fa-phone me-2"></i>Contact Us</a></li>
                        </ul>
                    </li>
                </ul>

                <!-- Premium Action Buttons -->
                <div class="d-flex align-items-center gap-2 flex-nowrap premium-actions">
                    <!-- Premium Phone Button -->
                    <a href="tel:+917007444842" class="btn btn-success premium-btn" title="Call us at +91-7007444842" aria-label="Call APS Dream Homes">
                        <i class="fas fa-phone" aria-hidden="true"></i>
                        <span class="d-none d-xl-inline ms-1">Call</span>
                    </a>

                    <!-- Premium Account Dropdown -->
                    <div class="dropdown">
                        <button class="btn btn-outline-light premium-btn dropdown-toggle" type="button" id="userDropdown" title="User Account Options" aria-label="User account options">
                            <i class="fas fa-user" aria-hidden="true"></i>
                            <span class="d-none d-lg-inline ms-1">Account</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end premium-dropdown" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item premium-item" href="<?php echo BASE_URL; ?>login"><i class="fas fa-sign-in-alt me-2"></i>Login</a></li>
                            <li><a class="dropdown-item premium-item" href="<?php echo BASE_URL; ?>register"><i class="fas fa-user-plus me-2"></i>Register</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item premium-item" href="<?php echo BASE_URL; ?>customer-dashboard"><i class="fas fa-tachometer-alt me-2"></i>Customer Dashboard</a></li>
                            <li><a class="dropdown-item premium-item" href="<?php echo BASE_URL; ?>associate-dashboard"><i class="fas fa-chart-line me-2"></i>Associate Dashboard</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content" style="margin-top: 100px;">

    <!-- Bootstrap JS and dependencies -->
    <script src="<?= BASE_URL ?>assets/js/bootstrap.bundle.min.js"></script>

    <!-- Mobile Menu Overlay -->
    <div class="mobile-menu-overlay" id="mobileMenuOverlay"></div>

    <script>
        // APS Dream Home - Enhanced Mobile Menu and Dropdown System
        document.addEventListener('DOMContentLoaded', function() {
            console.log('ðŸ”§ APS Header: Starting enhanced mobile menu and dropdown fix...');

            // Mobile Menu Functionality
            const navbarToggler = document.querySelector('.navbar-toggler');
            const navbarCollapse = document.getElementById('navbarNav');
            const mobileMenuOverlay = document.getElementById('mobileMenuOverlay');

            // Function to close mobile menu
            function closeMobileMenu() {
                if (navbarCollapse && navbarToggler) {
                    const bsCollapse = bootstrap.Collapse.getInstance(navbarCollapse);
                    if (bsCollapse) {
                        bsCollapse.hide();
                    }
                }
                if (mobileMenuOverlay) {
                    mobileMenuOverlay.classList.remove('active');
                }
            }

            // Function to open mobile menu
            function openMobileMenu() {
                if (mobileMenuOverlay) {
                    mobileMenuOverlay.classList.add('active');
                }
            }

            // Mobile menu toggle events
            if (navbarToggler && navbarCollapse) {
                navbarToggler.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const isExpanded = navbarToggler.getAttribute('aria-expanded') === 'true';
                    if (isExpanded) {
                        closeMobileMenu();
                    } else {
                        openMobileMenu();
                    }
                });

                // Close menu when clicking outside
                if (mobileMenuOverlay) {
                    mobileMenuOverlay.addEventListener('click', closeMobileMenu);
                }

                // Close menu when clicking on nav links
                const navLinks = navbarCollapse.querySelectorAll('.nav-link');
                navLinks.forEach(link => {
                    link.addEventListener('click', function() {
                        // Only close if it's not a dropdown toggle
                        if (!this.classList.contains('dropdown-toggle')) {
                            closeMobileMenu();
                        }
                    });
                });

                // Bootstrap collapse events
                navbarCollapse.addEventListener('show.bs.collapse', openMobileMenu);
                navbarCollapse.addEventListener('hide.bs.collapse', closeMobileMenu);
            }

            // Enhanced Dropdown Functionality with Slide Animation
            const dropdownToggles = document.querySelectorAll('.dropdown-toggle');

            dropdownToggles.forEach((toggle, index) => {
                toggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    const menu = this.nextElementSibling;
                    const isExpanded = this.getAttribute('aria-expanded') === 'true';
                    const isMobile = window.innerWidth < 992;

                    console.log(`ðŸ” Dropdown ${index + 1} clicked, mobile: ${isMobile}`);

                    // Close all other dropdowns
                    dropdownToggles.forEach(otherToggle => {
                        if (otherToggle !== toggle) {
                            const otherMenu = otherToggle.nextElementSibling;
                            if (otherMenu) {
                                otherMenu.classList.remove('show');
                                otherMenu.style.maxHeight = '0';
                                otherToggle.setAttribute('aria-expanded', 'false');
                            }
                        }
                    });

                    // Toggle current dropdown with slide animation
                    if (menu) {
                        if (isExpanded) {
                            // Close dropdown with animation
                            menu.classList.remove('show');
                            if (isMobile) {
                                menu.style.maxHeight = '0';
                            }
                            this.setAttribute('aria-expanded', 'false');
                            console.log(`ðŸ”½ Dropdown ${index + 1} closed`);
                        } else {
                            // Open dropdown with animation
                            menu.classList.add('show');
                            if (isMobile) {
                                menu.style.maxHeight = menu.scrollHeight + 'px';
                            }
                            this.setAttribute('aria-expanded', 'true');
                            console.log(`ðŸ”¼ Dropdown ${index + 1} opened`);
                        }
                    }
                });
            });

            // Close dropdowns when clicking outside
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.dropdown')) {
                    dropdownToggles.forEach(toggle => {
                        const menu = toggle.nextElementSibling;
                        if (menu && menu.classList.contains('show')) {
                            menu.classList.remove('show');
                            if (window.innerWidth < 992) {
                                menu.style.maxHeight = '0';
                            }
                            toggle.setAttribute('aria-expanded', 'false');
                        }
                    });
                }
            });

            // Handle window resize
            let resizeTimer;
            window.addEventListener('resize', function() {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(function() {
                    if (window.innerWidth >= 992) {
                        // Reset mobile menu state on desktop
                        closeMobileMenu();
                        // Reset dropdown max-height
                        dropdownToggles.forEach(toggle => {
                            const menu = toggle.nextElementSibling;
                            if (menu) {
                                menu.style.maxHeight = '';
                            }
                        });
                    }
                }, 250);
            });

            console.log('âœ… Enhanced mobile menu and dropdown system initialized');
        });

        // CSS for mobile menu overlay and animations
        const style = document.createElement('style');
        style.textContent = `
            /* Mobile Menu Overlay */
            .mobile-menu-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                z-index: 1040;
                opacity: 0;
                visibility: hidden;
                transition: all 0.3s ease;
            }

            .mobile-menu-overlay.active {
                opacity: 1;
                visibility: visible;
            }

            /* Mobile dropdown slide animation */
            @media (max-width: 991.98px) {
                .dropdown-menu {
                    max-height: 0;
                    overflow: hidden;
                    transition: max-height 0.3s ease;
                    border: none;
                    box-shadow: none;
                    margin-top: 0;
                    margin-bottom: 0;
                }

                .dropdown-menu.show {
                    max-height: 500px;
                }

                .mega-menu {
                    max-height: 0;
                    overflow: hidden;
                    transition: max-height 0.3s ease;
                }

                .mega-menu.show {
                    max-height: 800px;
                }

                /* Enhanced mobile navbar collapse */
                .navbar-collapse {
                    position: fixed;
                    top: 0;
                    right: -100%;
                    width: 80%;
                    max-width: 320px;
                    height: 100vh;
                    background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
                    transition: right 0.3s ease;
                    z-index: 1050;
                    padding: 1rem;
                    overflow-y: auto;
                }

                .navbar-collapse.show {
                    right: 0;
                }

                /* Mobile menu close button */
                .navbar-collapse::before {
                    content: 'âœ•';
                    position: absolute;
                    top: 1rem;
                    right: 1rem;
                    color: white;
                    font-size: 1.5rem;
                    cursor: pointer;
                    z-index: 1051;
                }
            }

            /* Override Bootstrap's default navbar behavior for desktop */
            @media (min-width: 992px) {
                /* Force navbar to expand properly */
                .navbar-expand-lg .navbar-collapse {
                    display: flex !important;
                    flex-basis: auto !important;
                    flex-grow: 1 !important;
                    flex-shrink: 1 !important;
                    position: static !important;
                    right: auto !important;
                    width: auto !important;
                    max-width: none !important;
                    height: auto !important;
                    background: none !important;
                    padding: 0 !important;
                }
                
                .navbar-expand-lg .navbar-nav {
                    flex-direction: row !important;
                    margin: 0 !important;
                    padding: 0 !important;
                }
                
                .navbar-expand-lg .nav-item {
                    display: inline-block !important;
                    margin: 0 !important;
                    padding: 0 !important;
                }
                
                .navbar-expand-lg .nav-link {
                    display: inline-flex !important;
                    align-items: center !important;
                    white-space: nowrap !important;
                }
            }
                /* Force horizontal navbar layout */
                .navbar-nav {
                    flex-direction: row !important;
                    gap: 0.5rem;
                }
                
                .premium-nav .nav-item {
                    display: inline-block !important;
                }

                .premium-nav .nav-link {
                    font-size: 0.85rem;
                    padding: 0.5rem 0.8rem;
                }

                .premium-actions {
                    flex-direction: row !important;
                    gap: 0.5rem;
                }

                .premium-btn {
                    font-size: 0.8rem;
                    padding: 0.4rem 0.8rem;
                }
                
                /* Hide mobile toggler on desktop */
                .navbar-toggler.premium-toggler {
                    display: none !important;
                }
                
                /* Ensure navbar collapse stays horizontal */
                .navbar-collapse {
                    display: flex !important;
                    flex-direction: row !important;
                    justify-content: space-between !important;
                    align-items: center !important;
                    position: static !important;
                    right: auto !important;
                    width: auto !important;
                    max-width: none !important;
                    height: auto !important;
                    background: none !important;
                    padding: 0 !important;
                }
                
                .navbar-collapse::before {
                    display: none !important;
                }
            }

            @media (min-width: 1200px) {
                /* Force navbar to expand properly at 1200px */
                .navbar-expand-lg .navbar-collapse {
                    display: flex !important;
                    flex-basis: auto !important;
                    flex-grow: 1 !important;
                    flex-shrink: 1 !important;
                    position: static !important;
                    right: auto !important;
                    width: auto !important;
                    max-width: none !important;
                    height: auto !important;
                    background: none !important;
                    padding: 0 !important;
                }
                
                .navbar-expand-lg .navbar-nav {
                    flex-direction: row !important;
                    margin: 0 !important;
                    padding: 0 !important;
                }
                
                .navbar-expand-lg .nav-item {
                    display: inline-block !important;
                    margin: 0 !important;
                    padding: 0 !important;
                }
                
                .navbar-expand-lg .nav-link {
                    display: inline-flex !important;
                    align-items: center !important;
                    white-space: nowrap !important;
                }
                /* Ensure horizontal layout persists */
                .navbar-nav {
                    flex-direction: row !important;
                    gap: 0.8rem;
                }
                
                .premium-nav .nav-item {
                    display: inline-block !important;
                }

                .premium-nav .nav-link {
                    font-size: 0.9rem;
                    padding: 0.6rem 1rem;
                }

                .premium-actions {
                    flex-direction: row !important;
                    gap: 0.8rem;
                }
                
                /* Ensure navbar collapse stays horizontal */
                .navbar-collapse {
                    display: flex !important;
                    flex-direction: row !important;
                    justify-content: space-between !important;
                    align-items: center !important;
                    position: static !important;
                    right: auto !important;
                    width: auto !important;
                    max-width: none !important;
                    height: auto !important;
                    background: none !important;
                    padding: 0 !important;
                }
                
                .navbar-collapse::before {
                    display: none !important;
                }

                .premium-btn {
                    font-size: 0.85rem;
                    padding: 0.5rem 1rem;
                }
            }

            /* Fix for ultra-wide screens */
            @media (min-width: 1400px) {
                .container-fluid {
                    max-width: 1320px;
                }

                .premium-nav .nav-link {
                    font-size: 0.95rem;
                    padding: 0.7rem 1.2rem;
                }
            }
        `;
        document.head.appendChild(style);
    </script>





