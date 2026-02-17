<?php
require_once __DIR__ . '/../core/init.php';

// Authentication and role check
if (!isAuthenticated()) {
    header('Location: ../login.php');
    exit();
}

// Set page title and metadata
$page_title = 'Marketing Dashboard - APS Dream Homes';
$page_description = 'Complete digital marketing dashboard for APS Dream Homes. Track SEO, social media, and online marketing performance.';
$page_keywords = 'APS Dream Homes marketing, SEO dashboard, digital marketing, social media, online presence';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <meta name="description" content="<?php echo $page_description; ?>">
    <meta name="keywords" content="<?php echo $page_keywords; ?>">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --accent-color: #f093fb;
            --success-color: #28a745;
            --info-color: #17a2b8;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --dark-color: #2c3e50;
            --light-color: #ecf0f1;
        }
        
        /* Hero Section */
        .hero-section {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            padding: 120px 0 80px;
            color: white;
            position: relative;
            overflow: hidden;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="%23ffffff20" fill-opacity="1" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,122.7C672,117,768,139,864,138.7C960,139,1056,117,1152,96C1248,75,1344,53,1392,42.7L1440,32L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>') no-repeat bottom;
            background-size: cover;
        }
        
        .hero-content {
            position: relative;
            z-index: 2;
        }
        
        /* Dashboard Section */
        .dashboard-section {
            padding: 80px 0;
            background: #f8f9fa;
        }
        
        .metric-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .metric-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 25px 50px rgba(0,0,0,0.15);
        }
        
        .metric-icon {
            font-size: 3rem;
            margin-bottom: 20px;
            display: inline-block;
        }
        
        .metric-icon.website {
            color: var(--primary-color);
        }
        
        .metric-icon.seo {
            color: var(--success-color);
        }
        
        .metric-icon.social {
            color: var(--info-color);
        }
        
        .metric-icon.reviews {
            color: var(--warning-color);
        }
        
        .metric-number {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--dark-color);
            margin-bottom: 10px;
        }
        
        .metric-label {
            font-size: 1.1rem;
            color: #666;
            margin-bottom: 15px;
        }
        
        .metric-change {
            font-size: 0.9rem;
            font-weight: 600;
            padding: 5px 10px;
            border-radius: 50px;
            display: inline-block;
        }
        
        .metric-change.positive {
            background: #d4edda;
            color: var(--success-color);
        }
        
        .metric-change.negative {
            background: #f8d7da;
            color: var(--danger-color);
        }
        
        .metric-change.neutral {
            background: #e2e3e5;
            color: #666;
        }
        
        /* Chart Section */
        .chart-section {
            padding: 80px 0;
            background: white;
        }
        
        .chart-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .chart-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 20px;
        }
        
        /* Action Items Section */
        .action-items-section {
            padding: 80px 0;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        }
        
        .action-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            height: 100%;
            border-left: 5px solid var(--primary-color);
        }
        
        .action-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 25px 50px rgba(0,0,0,0.15);
        }
        
        .action-priority {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 15px;
        }
        
        .action-priority.high {
            background: #f8d7da;
            color: var(--danger-color);
        }
        
        .action-priority.medium {
            background: #fff3cd;
            color: var(--warning-color);
        }
        
        .action-priority.low {
            background: #d1ecf1;
            color: var(--info-color);
        }
        
        .action-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 15px;
        }
        
        .action-description {
            color: #666;
            line-height: 1.6;
            margin-bottom: 20px;
        }
        
        .btn-action {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 12px 25px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            display: inline-block;
            transition: all 0.3s ease;
        }
        
        .btn-action:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            color: white;
        }
        
        /* Checklist Section */
        .checklist-section {
            padding: 80px 0;
            background: white;
        }
        
        .checklist-card {
            background: #f8f9fa;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }
        
        .checklist-item {
            display: flex;
            align-items: center;
            padding: 15px;
            margin-bottom: 15px;
            background: white;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        
        .checklist-item:hover {
            transform: translateX(10px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .checklist-checkbox {
            width: 25px;
            height: 25px;
            border: 2px solid var(--primary-color);
            border-radius: 5px;
            margin-right: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .checklist-checkbox.checked {
            background: var(--primary-color);
            color: white;
        }
        
        .checklist-text {
            flex: 1;
            font-weight: 600;
            color: var(--dark-color);
        }
        
        .checklist-status {
            padding: 5px 15px;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .checklist-status.completed {
            background: #d4edda;
            color: var(--success-color);
        }
        
        .checklist-status.pending {
            background: #fff3cd;
            color: var(--warning-color);
        }
        
        .checklist-status.not-started {
            background: #f8d7da;
            color: var(--danger-color);
        }
        
        /* Mobile Responsive */
        @media (max-width: 768px) {
            .hero-section {
                padding: 80px 0 60px;
            }
            
            .metric-card {
                padding: 20px;
            }
            
            .chart-card {
                padding: 20px;
            }
            
            .checklist-card {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <?php include_once __DIR__ . '/includes/components/header.php'; ?>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center hero-content" data-aos="fade-up">
                    <div class="badge bg-white text-primary mb-3 px-3 py-2 rounded-pill">
                        <i class="fas fa-chart-line me-2"></i>Marketing Dashboard
                    </div>
                    <h1 class="display-3 fw-bold mb-4">APS Dream Homes Digital Presence</h1>
                    <p class="lead mb-4">
                        Complete overview of your online marketing performance, SEO metrics, 
                        and digital presence status for real estate business growth.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Dashboard Metrics -->
    <section class="dashboard-section">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <h2 class="display-4 fw-bold mb-3">Current Online Status</h2>
                <p class="lead text-muted">Track your digital marketing performance across all channels</p>
            </div>
            
            <div class="row">
                <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="metric-card">
                        <div class="metric-icon website">
                            <i class="fas fa-globe"></i>
                        </div>
                        <div class="metric-number">100%</div>
                        <div class="metric-label">Website Complete</div>
                        <div class="metric-change positive">
                            <i class="fas fa-check me-1"></i>Ready
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="metric-card">
                        <div class="metric-icon seo">
                            <i class="fas fa-search"></i>
                        </div>
                        <div class="metric-number">0%</div>
                        <div class="metric-label">Google Ranking</div>
                        <div class="metric-change negative">
                            <i class="fas fa-times me-1"></i>Not Started
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="metric-card">
                        <div class="metric-icon social">
                            <i class="fas fa-share-alt"></i>
                        </div>
                        <div class="metric-number">25%</div>
                        <div class="metric-label">Social Media</div>
                        <div class="metric-change neutral">
                            <i class="fas fa-minus me-1"></i>Basic
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="400">
                    <div class="metric-card">
                        <div class="metric-icon reviews">
                            <i class="fas fa-star"></i>
                        </div>
                        <div class="metric-number">0%</div>
                        <div class="metric-label">Customer Reviews</div>
                        <div class="metric-change negative">
                            <i class="fas fa-times me-1"></i>Missing
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Charts Section -->
    <section class="chart-section">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <h2 class="display-4 fw-bold mb-3">Performance Analytics</h2>
                <p class="lead text-muted">Visual representation of your marketing progress</p>
            </div>
            
            <div class="row">
                <div class="col-lg-6 mb-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="chart-card">
                        <h3 class="chart-title">Online Presence Progress</h3>
                        <canvas id="presenceChart" height="300"></canvas>
                    </div>
                </div>
                
                <div class="col-lg-6 mb-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="chart-card">
                        <h3 class="chart-title">Marketing Channel Performance</h3>
                        <canvas id="channelChart" height="300"></canvas>
                    </div>
                </div>
                
                <div class="col-lg-12 mb-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="chart-card">
                        <h3 class="chart-title">Monthly Growth Projection</h3>
                        <canvas id="growthChart" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Action Items Section -->
    <section class="action-items-section">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <h2 class="display-4 fw-bold mb-3">Priority Action Items</h2>
                <p class="lead text-muted">Immediate tasks to boost your online presence</p>
            </div>
            
            <div class="row">
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="action-card">
                        <div class="action-priority high">HIGH PRIORITY</div>
                        <h3 class="action-title">Deploy Website to Live Domain</h3>
                        <p class="action-description">
                            Move your complete website from localhost to live domain (apsdreamhomes.com) 
                            to establish online presence immediately.
                        </p>
                        <a href="#" class="btn-action">
                            <i class="fas fa-rocket me-2"></i>Deploy Now
                        </a>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="action-card">
                        <div class="action-priority high">HIGH PRIORITY</div>
                        <h3 class="action-title">Setup Google Business Profile</h3>
                        <p class="action-description">
                            Create and verify Google Business Profile to appear in local search results 
                            and Google Maps for Gorakhpur area.
                        </p>
                        <a href="google-business-profile.php" class="btn-action">
                            <i class="fab fa-google me-2"></i>Setup Now
                        </a>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="action-card">
                        <div class="action-priority medium">MEDIUM PRIORITY</div>
                        <h3 class="action-title">Submit Sitemap to Google</h3>
                        <p class="action-description">
                            Submit your sitemap.xml.php to Google Search Console for faster indexing 
                            and better search visibility.
                        </p>
                        <a href="#" class="btn-action">
                            <i class="fas fa-search me-2"></i>Submit Sitemap
                        </a>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="400">
                    <div class="action-card">
                        <div class="action-priority medium">MEDIUM PRIORITY</div>
                        <h3 class="action-title">Setup Google Analytics</h3>
                        <p class="action-description">
                            Install Google Analytics to track website traffic, user behavior, 
                            and conversion metrics for data-driven decisions.
                        </p>
                        <a href="#" class="btn-action">
                            <i class="fas fa-chart-bar me-2"></i>Setup Analytics
                        </a>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="500">
                    <div class="action-card">
                        <div class="action-priority medium">MEDIUM PRIORITY</div>
                        <h3 class="action-title">Generate Customer Reviews</h3>
                        <p class="action-description">
                            Encourage satisfied customers to leave reviews on Google, Facebook, 
                            and your website reviews page.
                        </p>
                        <a href="customer-reviews.php" class="btn-action">
                            <i class="fas fa-star me-2"></i>Get Reviews
                        </a>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="600">
                    <div class="action-card">
                        <div class="action-priority low">LOW PRIORITY</div>
                        <h3 class="action-title">Social Media Enhancement</h3>
                        <p class="action-description">
                            Enhance Facebook page with professional content, create Instagram 
                            profile, and start regular posting schedule.
                        </p>
                        <a href="#" class="btn-action">
                            <i class="fas fa-share-alt me-2"></i>Enhance Social
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Checklist Section -->
    <section class="checklist-section">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <h2 class="display-4 fw-bold mb-3">Marketing Setup Checklist</h2>
                <p class="lead text-muted">Track your progress through this comprehensive checklist</p>
            </div>
            
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="checklist-card" data-aos="fade-up">
                        <div class="checklist-item">
                            <div class="checklist-checkbox checked" onclick="toggleChecklist(this)">
                                <i class="fas fa-check"></i>
                            </div>
                            <div class="checklist-text">Website Development Complete</div>
                            <div class="checklist-status completed">Completed</div>
                        </div>
                        
                        <div class="checklist-item">
                            <div class="checklist-checkbox" onclick="toggleChecklist(this)">
                            </div>
                            <div class="checklist-text">Deploy Website to Live Domain</div>
                            <div class="checklist-status pending">Pending</div>
                        </div>
                        
                        <div class="checklist-item">
                            <div class="checklist-checkbox" onclick="toggleChecklist(this)">
                            </div>
                            <div class="checklist-text">Setup Google Business Profile</div>
                            <div class="checklist-status pending">Pending</div>
                        </div>
                        
                        <div class="checklist-item">
                            <div class="checklist-checkbox" onclick="toggleChecklist(this)">
                            </div>
                            <div class="checklist-text">Submit Sitemap to Google Search Console</div>
                            <div class="checklist-status not-started">Not Started</div>
                        </div>
                        
                        <div class="checklist-item">
                            <div class="checklist-checkbox" onclick="toggleChecklist(this)">
                            </div>
                            <div class="checklist-text">Setup Google Analytics</div>
                            <div class="checklist-status not-started">Not Started</div>
                        </div>
                        
                        <div class="checklist-item">
                            <div class="checklist-checkbox" onclick="toggleChecklist(this)">
                            </div>
                            <div class="checklist-text">Create Customer Reviews System</div>
                            <div class="checklist-status pending">Pending</div>
                        </div>
                        
                        <div class="checklist-item">
                            <div class="checklist-checkbox" onclick="toggleChecklist(this)">
                            </div>
                            <div class="checklist-text">Setup Facebook Business Page</div>
                            <div class="checklist-status pending">Pending</div>
                        </div>
                        
                        <div class="checklist-item">
                            <div class="checklist-checkbox" onclick="toggleChecklist(this)">
                            </div>
                            <div class="checklist-text">Start Content Marketing (Blog)</div>
                            <div class="checklist-status not-started">Not Started</div>
                        </div>
                        
                        <div class="checklist-item">
                            <div class="checklist-checkbox" onclick="toggleChecklist(this)">
                            </div>
                            <div class="checklist-text">Setup Email Marketing System</div>
                            <div class="checklist-status not-started">Not Started</div>
                        </div>
                        
                        <div class="checklist-item">
                            <div class="checklist-checkbox" onclick="toggleChecklist(this)">
                            </div>
                            <div class="checklist-text">Launch Paid Advertising Campaigns</div>
                            <div class="checklist-status not-started">Not Started</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include_once __DIR__ . '/includes/components/footer.php'; ?>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    
    <script>
        // Initialize AOS
        AOS.init({
            duration: 1000,
            once: true,
            offset: 100
        });

        // Online Presence Chart
        const presenceCtx = document.getElementById('presenceChart').getContext('2d');
        new Chart(presenceCtx, {
            type: 'doughnut',
            data: {
                labels: ['Website Complete', 'Google Ranking', 'Social Media', 'Customer Reviews'],
                datasets: [{
                    data: [100, 0, 25, 0],
                    backgroundColor: [
                        '#28a745',
                        '#dc3545',
                        '#ffc107',
                        '#dc3545'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Channel Performance Chart
        const channelCtx = document.getElementById('channelChart').getContext('2d');
        new Chart(channelCtx, {
            type: 'bar',
            data: {
                labels: ['Website', 'Google Search', 'Facebook', 'Direct', 'Referrals'],
                datasets: [{
                    label: 'Current Traffic',
                    data: [0, 0, 25, 0, 0],
                    backgroundColor: '#667eea',
                    borderRadius: 10
                }, {
                    label: 'Target Traffic',
                    data: [100, 80, 60, 40, 30],
                    backgroundColor: '#764ba2',
                    borderRadius: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Growth Projection Chart
        const growthCtx = document.getElementById('growthChart').getContext('2d');
        new Chart(growthCtx, {
            type: 'line',
            data: {
                labels: ['Month 1', 'Month 2', 'Month 3', 'Month 4', 'Month 5', 'Month 6'],
                datasets: [{
                    label: 'Website Visitors',
                    data: [0, 500, 1500, 3000, 5000, 8000],
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    tension: 0.4,
                    fill: true
                }, {
                    label: 'Lead Generation',
                    data: [0, 50, 150, 300, 500, 800],
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Toggle Checklist Items
        function toggleChecklist(checkbox) {
            const item = checkbox.closest('.checklist-item');
            const status = item.querySelector('.checklist-status');
            
            if (checkbox.classList.contains('checked')) {
                checkbox.classList.remove('checked');
                checkbox.innerHTML = '';
                status.className = 'checklist-status pending';
                status.textContent = 'Pending';
            } else {
                checkbox.classList.add('checked');
                checkbox.innerHTML = '<i class="fas fa-check"></i>';
                status.className = 'checklist-status completed';
                status.textContent = 'Completed';
            }
            
            updateProgress();
        }

        // Update Overall Progress
        function updateProgress() {
            const totalItems = document.querySelectorAll('.checklist-item').length;
            const completedItems = document.querySelectorAll('.checklist-checkbox.checked').length;
            const progressPercentage = Math.round((completedItems / totalItems) * 100);
            
            console.log(`Marketing Progress: ${progressPercentage}% (${completedItems}/${totalItems} items completed)`);
            
            // Update metrics based on completion
            if (progressPercentage >= 80) {
                document.querySelector('.metric-card:nth-child(2) .metric-number').textContent = '85%';
                document.querySelector('.metric-card:nth-child(2) .metric-change').className = 'metric-change positive';
                document.querySelector('.metric-card:nth-child(2) .metric-change').innerHTML = '<i class="fas fa-arrow-up me-1"></i>Excellent';
            }
        }

        // Action Button Handlers
        document.querySelectorAll('.btn-action').forEach(button => {
            button.addEventListener('click', function(e) {
                if (this.href === '#' || this.href.endsWith('#')) {
                    e.preventDefault();
                    
                    // Show action modal or redirect
                    const actionTitle = this.closest('.action-card').querySelector('.action-title').textContent;
                    alert(`Action: ${actionTitle}\n\nThis would take you to the implementation page or open a setup wizard.`);
                }
            });
        });

        // Simulate real-time updates
        setInterval(() => {
            // Update random metrics
            const metrics = document.querySelectorAll('.metric-number');
            const randomMetric = metrics[Math.floor(Math.random() * metrics.length)];
            
            if (randomMetric.textContent !== '100%' && randomMetric.textContent !== '0%') {
                const currentValue = parseInt(randomMetric.textContent);
                const change = Math.floor(Math.random() * 5) - 2;
                const newValue = Math.max(0, Math.min(100, currentValue + change));
                randomMetric.textContent = newValue + '%';
            }
        }, 5000);

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                if (this.href === '#' || this.href.endsWith('#')) {
                    e.preventDefault();
                } else {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                }
            });
        });
    </script>
</body>
</html>

