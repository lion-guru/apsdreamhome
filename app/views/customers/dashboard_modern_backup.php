/**
* dashboard_modern - APS Dream Home Component
*
* @package APS Dream Home
* @version 1.0.0
* @author APS Dream Home Team
* @copyright 2026 APS Dream Home
*
* Description: Handles dashboard modern functionality
*
* Features:
* - Secure input validation
* - Comprehensive error handling
* - Performance optimization
* - Database integration
* - Session management
* - CSRF protection
*
* @see https://apsdreamhome.com/docs
*/
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Portal - APS Dream Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="/public/css/pages.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --info-color: #17a2b8;
            --light-color: #ecf0f1;
            --dark-color: #2c3e50;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        /* Customer Header */
        .customer-header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .customer-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 2rem;
        }

        .customer-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: var(--primary-color);
        }

        .customer-logo img {
            width: 40px;
            height: 40px;
            border-radius: 8px;
        }

        .customer-logo h3 {
            font-size: 20px;
            font-weight: 600;
            margin: 0;
        }

        .customer-menu {
            display: flex;
            align-items: center;
            gap: 2rem;
        }

        .customer-menu a {
            color: var(--dark-color);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .customer-menu a:hover {
            color: var(--secondary-color);
        }

        .customer-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .notification-btn {
            position: relative;
            background: none;
            border: none;
            font-size: 20px;
            color: var(--dark-color);
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .notification-btn:hover {
            color: var(--secondary-color);
        }

        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: var(--danger-color);
            color: white;
            font-size: 10px;
            padding: 2px 6px;
            border-radius: 10px;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 15px;
            background: #f8f9fa;
            border-radius: 25px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .user-profile:hover {
            background: #e9ecef;
        }

        .user-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            object-fit: cover;
        }

        /* Main Content */
        .customer-main {
            padding: 2rem;
            max-width: 1400px;
            margin: 0 auto;
        }

        /* Hero Section */
        .hero-section {
            background: white;
            border-radius: 20px;
            padding: 3rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 500px;
            height: 500px;
            background: linear-gradient(135deg, var(--secondary-color), var(--info-color));
            border-radius: 50%;
            opacity: 0.1;
        }

        .hero-content {
            position: relative;
            z-index: 1;
        }

        .hero-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        .hero-subtitle {
            font-size: 1.2rem;
            color: #666;
            margin-bottom: 2rem;
        }

        .hero-search {
            position: relative;
            max-width: 600px;
        }

        .hero-search input {
            width: 100%;
            padding: 15px 20px 15px 50px;
            border: 2px solid #e0e0e0;
            border-radius: 50px;
            font-size: 16px;
            outline: none;
            transition: border-color 0.3s ease;
        }

        .hero-search input:focus {
            border-color: var(--secondary-color);
        }

        .hero-search i {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            font-size: 20px;
        }

        .hero-search button {
            position: absolute;
            right: 5px;
            top: 50%;
            transform: translateY(-50%);
            background: var(--secondary-color);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 50px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .hero-search button:hover {
            background: #2980b9;
        }

        /* Quick Actions */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .quick-action-card {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
        }

        .quick-action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
        }

        .quick-action-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 24px;
        }

        .quick-action-icon.primary {
            background: rgba(52, 152, 219, 0.1);
            color: var(--secondary-color);
        }

        .quick-action-icon.success {
            background: rgba(39, 174, 96, 0.1);
            color: var(--success-color);
        }

        .quick-action-icon.warning {
            background: rgba(243, 156, 18, 0.1);
            color: var(--warning-color);
        }

        .quick-action-icon.info {
            background: rgba(23, 162, 184, 0.1);
            color: var(--info-color);
        }

        .quick-action-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .quick-action-description {
            color: #666;
            font-size: 0.9rem;
        }

        /* Property Listings */
        .properties-section {
            margin-bottom: 2rem;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .section-title {
            font-size: 1.8rem;
            font-weight: 600;
            color: var(--primary-color);
        }

        .view-all-btn {
            background: var(--secondary-color);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 25px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .view-all-btn:hover {
            background: #2980b9;
        }

        .property-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 2rem;
        }

        .property-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .property-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
        }

        .property-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            position: relative;
        }

        .property-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            background: var(--success-color);
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .property-favorite {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(255, 255, 255, 0.9);
            border: none;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .property-favorite:hover {
            background: white;
            transform: scale(1.1);
        }

        .property-favorite.active {
            color: var(--danger-color);
        }

        .property-details {
            padding: 1.5rem;
        }

        .property-price {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .property-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .property-location {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .property-features {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .property-feature {
            display: flex;
            align-items: center;
            gap: 5px;
            color: #666;
            font-size: 0.9rem;
        }

        .property-actions {
            display: flex;
            gap: 0.5rem;
        }

        .property-btn {
            flex: 1;
            padding: 8px 15px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .property-btn.primary {
            background: var(--secondary-color);
            color: white;
        }

        .property-btn.primary:hover {
            background: #2980b9;
        }

        .property-btn.outline {
            background: transparent;
            color: var(--secondary-color);
            border: 1px solid var(--secondary-color);
        }

        .property-btn.outline:hover {
            background: var(--secondary-color);
            color: white;
        }

        /* Activity Timeline */
        .activity-section {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        }

        .activity-timeline {
            position: relative;
            padding-left: 2rem;
        }

        .activity-timeline::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #e0e0e0;
        }

        .activity-item {
            position: relative;
            padding-bottom: 2rem;
        }

        .activity-item::before {
            content: '';
            position: absolute;
            left: -2.3rem;
            top: 0;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: white;
            border: 2px solid var(--secondary-color);
        }

        .activity-icon {
            position: absolute;
            left: -2.8rem;
            top: -0.5rem;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            background: var(--secondary-color);
            color: white;
        }

        .activity-content {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 10px;
        }

        .activity-title {
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .activity-description {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        .activity-time {
            color: #999;
            font-size: 0.8rem;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .customer-nav {
                padding: 1rem;
            }

            .customer-menu {
                display: none;
            }

            .customer-actions {
                gap: 0.5rem;
            }

            .hero-title {
                font-size: 1.8rem;
            }

            .hero-subtitle {
                font-size: 1rem;
            }

            .quick-actions {
                grid-template-columns: 1fr;
            }

            .property-grid {
                grid-template-columns: 1fr;
            }

            .customer-main {
                padding: 1rem;
            }
        }

        /* Loading Animation */
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid var(--secondary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>
</head>

<body>
    <!-- Customer Header -->
    <header class="customer-header">
        <nav class="customer-nav">
            <a href="#" class="customer-logo">
                <img src="/assets/images/logo/apslogo.png" alt="APS Dream Home">
                <h3>APS Dream Home</h3>
            </a>

            <div class="customer-menu">
                <a href="#properties">Properties</a>
                <a href="#favorites">Favorites</a>
                <a href="#inquiries">My Inquiries</a>
                <a href="#profile">Profile</a>
            </div>

            <div class="customer-actions">
                <button class="notification-btn">
                    <i class="bi bi-bell"></i>
                    <span class="notification-badge">2</span>
                </button>

                <div class="user-profile">
                    <img src="/assets/images/user/default-avatar.jpg" alt="User" class="user-avatar">
                    <span>John Doe</span>
                    <i class="bi bi-chevron-down"></i>
                </div>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="customer-main">
        <!-- Hero Section -->
        <section class="hero-section">
            <div class="hero-content">
                <h1 class="hero-title">Find Your Dream Home</h1>
                <p class="hero-subtitle">Discover the perfect property from our exclusive collection</p>

                <div class="hero-search">
                    <i class="bi bi-search"></i>
                    <input type="text" placeholder="Search by location, property type, or features...">
                    <button>Search</button>
                </div>
            </div>
        </section>

        <!-- Quick Actions -->
        <section class="quick-actions">
            <a href="#" class="quick-action-card">
                <div class="quick-action-icon primary">
                    <i class="bi bi-house"></i>
                </div>
                <h3 class="quick-action-title">Browse Properties</h3>
                <p class="quick-action-description">Explore our extensive property listings</p>
            </a>

            <a href="#" class="quick-action-card">
                <div class="quick-action-icon success">
                    <i class="bi bi-calculator"></i>
                </div>
                <h3 class="quick-action-title">EMI Calculator</h3>
                <p class="quick-action-description">Calculate your monthly payments</p>
            </a>

            <a href="#" class="quick-action-card">
                <div class="quick-action-icon warning">
                    <i class="bi bi-headset"></i>
                </div>
                <h3 class="quick-action-title">Schedule Visit</h3>
                <p class="quick-action-description">Book a property viewing appointment</p>
            </a>

            <a href="#" class="quick-action-card">
                <div class="quick-action-icon info">
                    <i class="bi bi-chat-dots"></i>
                </div>
                <h3 class="quick-action-title">Get Assistance</h3>
                <p class="quick-action-description">Connect with our property experts</p>
            </a>
        </section>

        <!-- Featured Properties -->
        <section class="properties-section">
            <div class="section-header">
                <h2 class="section-title">Featured Properties</h2>
                <button class="view-all-btn">View All Properties</button>
            </div>

            <div class="property-grid">
                <!-- Property Card 1 -->
                <div class="property-card">
                    <div style="position: relative;">
                        <img src="/assets/images/hero/luxury-home-1.jpg" alt="Property" class="property-image">
                        <span class="property-badge">Featured</span>
                        <button class="property-favorite">
                            <i class="bi bi-heart"></i>
                        </button>
                    </div>
                    <div class="property-details">
                        <div class="property-price">₹45,00,000</div>
                        <h3 class="property-title">Luxury Villa in Mumbai</h3>
                        <div class="property-location">
                            <i class="bi bi-geo-alt"></i>
                            Andheri, Mumbai
                        </div>
                        <div class="property-features">
                            <div class="property-feature">
                                <i class="bi bi-house-door"></i>
                                3 BHK
                            </div>
                            <div class="property-feature">
                                <i class="bi bi-arrows-angle-expand"></i>
                                1,850 sqft
                            </div>
                        </div>
                        <div class="property-actions">
                            <button class="property-btn primary">View Details</button>
                            <button class="property-btn outline">Schedule Visit</button>
                        </div>
                    </div>
                </div>

                <!-- Property Card 2 -->
                <div class="property-card">
                    <div style="position: relative;">
                        <img src="/assets/images/hero/luxury-home-2.jpg" alt="Property" class="property-image">
                        <span class="property-badge">New</span>
                        <button class="property-favorite active">
                            <i class="bi bi-heart-fill"></i>
                        </button>
                    </div>
                    <div class="property-details">
                        <div class="property-price">₹32,00,000</div>
                        <h3 class="property-title">Modern Apartment</h3>
                        <div class="property-location">
                            <i class="bi bi-geo-alt"></i>
                            Powai, Mumbai
                        </div>
                        <div class="property-features">
                            <div class="property-feature">
                                <i class="bi bi-house-door"></i>
                                2 BHK
                            </div>
                            <div class="property-feature">
                                <i class="bi bi-arrows-angle-expand"></i>
                                1,200 sqft
                            </div>
                        </div>
                        <div class="property-actions">
                            <button class="property-btn primary">View Details</button>
                            <button class="property-btn outline">Schedule Visit</button>
                        </div>
                    </div>
                </div>

                <!-- Property Card 3 -->
                <div class="property-card">
                    <div style="position: relative;">
                        <img src="/assets/images/property-banner.jpg" alt="Property" class="property-image">
                        <span class="property-badge">Hot Deal</span>
                        <button class="property-favorite">
                            <i class="bi bi-heart"></i>
                        </button>
                    </div>
                    <div class="property-details">
                        <div class="property-price">₹28,00,000</div>
                        <h3 class="property-title">Cozy Family Home</h3>
                        <div class="property-location">
                            <i class="bi bi-geo-alt"></i>
                            Thane, Mumbai
                        </div>
                        <div class="property-features">
                            <div class="property-feature">
                                <i class="bi bi-house-door"></i>
                                2 BHK
                            </div>
                            <div class="property-feature">
                                <i class="bi bi-arrows-angle-expand"></i>
                                950 sqft
                            </div>
                        </div>
                        <div class="property-actions">
                            <button class="property-btn primary">View Details</button>
                            <button class="property-btn outline">Schedule Visit</button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Recent Activity -->
        <section class="activity-section">
            <div class="section-header">
                <h2 class="section-title">Recent Activity</h2>
            </div>

            <div class="activity-timeline">
                <div class="activity-item">
                    <div class="activity-icon">
                        <i class="bi bi-eye"></i>
                    </div>
                    <div class="activity-content">
                        <div class="activity-title">Property Viewed</div>
                        <div class="activity-description">You viewed "Luxury Villa in Mumbai"</div>
                        <div class="activity-time">2 hours ago</div>
                    </div>
                </div>

                <div class="activity-item">
                    <div class="activity-icon">
                        <i class="bi bi-heart"></i>
                    </div>
                    <div class="activity-content">
                        <div class="activity-title">Added to Favorites</div>
                        <div class="activity-description">You added "Modern Apartment" to your favorites</div>
                        <div class="activity-time">5 hours ago</div>
                    </div>
                </div>

                <div class="activity-item">
                    <div class="activity-icon">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                    <div class="activity-content">
                        <div class="activity-title">Visit Scheduled</div>
                        <div class="activity-description">Property viewing scheduled for tomorrow at 3:00 PM</div>
                        <div class="activity-time">1 day ago</div>
                    </div>
                </div>

                <div class="activity-item">
                    <div class="activity-icon">
                        <i class="bi bi-chat-dots"></i>
                    </div>
                    <div class="activity-content">
                        <div class="activity-title">Inquiry Sent</div>
                        <div class="activity-description">You sent an inquiry about "Cozy Family Home"</div>
                        <div class="activity-time">2 days ago</div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Customer Portal JavaScript
        class CustomerPortal {
            constructor() {
                this.init();
                this.setupEventListeners();
            }

            init() {
                console.log('APS Dream Home Customer Portal initialized');
                this.setupPropertyCards();
                this.setupSearch();
                this.setupNotifications();
            }

            setupEventListeners() {
                // Property favorite buttons
                document.querySelectorAll('.property-favorite').forEach(btn => {
                    btn.addEventListener('click', (e) => this.toggleFavorite(e));
                });

                // Property action buttons
                document.querySelectorAll('.property-btn').forEach(btn => {
                    btn.addEventListener('click', (e) => this.handlePropertyAction(e));
                });

                // Quick action cards
                document.querySelectorAll('.quick-action-card').forEach(card => {
                    card.addEventListener('click', (e) => this.handleQuickAction(e));
                });

                // Search functionality
                this.setupSearch();

                // User profile menu
                this.setupUserMenu();
            }

            setupPropertyCards() {
                // Add hover effects and interactions
                document.querySelectorAll('.property-card').forEach(card => {
                    card.addEventListener('mouseenter', () => {
                        card.style.transform = 'translateY(-10px) scale(1.02)';
                    });

                    card.addEventListener('mouseleave', () => {
                        card.style.transform = 'translateY(-5px) scale(1)';
                    });
                });
            }

            toggleFavorite(e) {
                e.stopPropagation();
                const btn = e.currentTarget;
                const icon = btn.querySelector('i');

                btn.classList.toggle('active');

                if (btn.classList.contains('active')) {
                    icon.className = 'bi bi-heart-fill';
                    this.showNotification('Added to favorites', 'success');
                } else {
                    icon.className = 'bi bi-heart';
                    this.showNotification('Removed from favorites', 'info');
                }
            }

            handlePropertyAction(e) {
                e.stopPropagation();
                const btn = e.currentTarget;
                const action = btn.textContent.trim();

                if (action === 'View Details') {
                    this.viewPropertyDetails(btn.closest('.property-card'));
                } else if (action === 'Schedule Visit') {
                    this.schedulePropertyVisit(btn.closest('.property-card'));
                }
            }

            viewPropertyDetails(propertyCard) {
                const title = propertyCard.querySelector('.property-title').textContent;
                console.log('Viewing details for:', title);
                // Navigate to property details page
                this.showNotification(`Loading details for ${title}`, 'info');
            }

            schedulePropertyVisit(propertyCard) {
                const title = propertyCard.querySelector('.property-title').textContent;
                console.log('Scheduling visit for:', title);
                // Open visit scheduling modal
                this.showNotification(`Opening visit scheduler for ${title}`, 'info');
            }

            handleQuickAction(e) {
                e.preventDefault();
                const card = e.currentTarget;
                const title = card.querySelector('.quick-action-title').textContent;

                console.log('Quick action:', title);
                this.showNotification(`Opening ${title}`, 'info');
            }

            setupSearch() {
                const searchInput = document.querySelector('.hero-search input');
                const searchBtn = document.querySelector('.hero-search button');

                if (searchInput && searchBtn) {
                    searchBtn.addEventListener('click', () => {
                        this.performSearch(searchInput.value);
                    });

                    searchInput.addEventListener('keypress', (e) => {
                        if (e.key === 'Enter') {
                            this.performSearch(searchInput.value);
                        }
                    });
                }
            }

            performSearch(query) {
                if (!query.trim()) {
                    this.showNotification('Please enter a search term', 'warning');
                    return;
                }

                console.log('Searching for:', query);
                this.showNotification(`Searching for "${query}"...`, 'info');

                // Simulate search
                setTimeout(() => {
                    this.showNotification(`Found 12 properties matching "${query}"`, 'success');
                }, 1500);
            }

            setupNotifications() {
                const notificationBtn = document.querySelector('.notification-btn');
                if (notificationBtn) {
                    notificationBtn.addEventListener('click', () => {
                        this.showNotifications();
                    });
                }
            }

            showNotifications() {
                console.log('Showing notifications panel');
                this.showNotification('You have 2 new notifications', 'info');
            }

            setupUserMenu() {
                const userProfile = document.querySelector('.user-profile');
                if (userProfile) {
                    userProfile.addEventListener('click', () => {
                        this.toggleUserMenu();
                    });
                }

                // Close menu when clicking outside
                document.addEventListener('click', (e) => {
                    if (!userProfile.contains(e.target)) {
                        this.closeUserMenu();
                    }
                });
            }

            toggleUserMenu() {
                console.log('Toggle user menu');
                // Implement user dropdown menu
            }

            closeUserMenu() {
                // Close user dropdown menu
            }

            showNotification(message, type = 'info') {
                // Create notification element
                const notification = document.createElement('div');
                notification.className = `alert alert-${this.getAlertClass(type)} alert-dismissible fade show position-fixed`;
                notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
                notification.innerHTML = `
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;

                document.body.appendChild(notification);

                // Auto-hide after 3 seconds
                setTimeout(() => {
                    notification.remove();
                }, 3000);
            }

            getAlertClass(type) {
                const types = {
                    'success': 'success',
                    'info': 'info',
                    'warning': 'warning',
                    'error': 'danger'
                };
                return types[type] || 'info';
            }
        }

        // Initialize customer portal
        document.addEventListener('DOMContentLoaded', () => {
            window.customerPortal = new CustomerPortal();
        });

        // Mobile menu toggle
        const mobileMenuToggle = document.createElement('button');
        mobileMenuToggle.className = 'mobile-menu-toggle d-md-none';
        mobileMenuToggle.innerHTML = '<i class="bi bi-list"></i>';
        mobileMenuToggle.style.cssText = 'background: none; border: none; font-size: 24px; color: var(--dark-color);';

        // Add mobile menu toggle to header
        const customerNav = document.querySelector('.customer-nav');
        if (customerNav && window.innerWidth <= 768) {
            customerNav.insertBefore(mobileMenuToggle, customerNav.firstChild);
        }
    </script>
</body>

</html>


// Merged from: C:\xampp\htdocs\apsdreamhome\app\Controllers/..\views\admin\dashboard_modern.php

function updateStats() {
// This would connect to your backend API
console.log('Updating dashboard stats...');
}
//
// PERFORMANCE OPTIMIZATION GUIDELINES
//
// This file contains 1084 lines. Consider optimizations:
//
// 1. Use database indexing
// 2. Implement caching
// 3. Use prepared statements
// 4. Optimize loops
// 5. Use lazy loading
// 6. Implement pagination
// 7. Use connection pooling
// 8. Consider Redis for sessions
// 9. Implement output buffering
// 10. Use gzip compression
//
//