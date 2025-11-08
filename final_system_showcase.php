<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>APS Dream Home - Complete System Showcase</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js" rel="stylesheet">
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .feature-card {
            transition: all 0.3s ease;
            border: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        .stat-card {
            background: linear-gradient(45deg, #28a745, #20c997);
            color: white;
            border-radius: 15px;
        }
        .demo-section {
            padding: 60px 0;
        }
        .section-title {
            position: relative;
            margin-bottom: 40px;
        }
        .section-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 3px;
            background: #667eea;
        }
        .tech-badge {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            margin: 5px;
            display: inline-block;
        }
        .navbar-brand {
            font-weight: bold;
            font-size: 1.5rem;
        }
        .hero-section {
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .floating-card {
            animation: float 6s ease-in-out infinite;
        }
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
            100% { transform: translateY(0px); }
        }
        .progress-modern {
            height: 8px;
            border-radius: 4px;
            background: #e9ecef;
        }
        .progress-modern .progress-bar {
            border-radius: 4px;
        }
    </style>
</head>
<body>

<!-- Navigation -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <div class="container">
        <a class="navbar-brand" href="#hero">
            <i class="fas fa-home"></i> APS Dream Home
        </a>
        <div class="navbar-nav ms-auto">
            <a class="nav-link" href="#features">Features</a>
            <a class="nav-link" href="#tech">Technology</a>
            <a class="nav-link" href="#dashboard">Dashboard</a>
            <a class="nav-link" href="#demo">Live Demo</a>
        </div>
    </div>
</nav>

<!-- Hero Section -->
<section id="hero" class="hero-section gradient-bg">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-3 mb-4">
                    <i class="fas fa-building"></i>
                    APS Dream Home
                </h1>
                <p class="lead mb-4">
                    Complete Real Estate ERP/CRM System with Advanced MLM Commission Management
                </p>
                <div class="mb-4">
                    <span class="tech-badge"><i class="fab fa-php"></i> PHP 8.2</span>
                    <span class="tech-badge"><i class="fas fa-database"></i> MariaDB</span>
                    <span class="tech-badge"><i class="fab fa-bootstrap"></i> Bootstrap 5.3</span>
                    <span class="tech-badge"><i class="fas fa-chart-line"></i> Chart.js</span>
                    <span class="tech-badge"><i class="fas fa-mobile-alt"></i> Responsive</span>
                </div>
                <div class="d-flex gap-3">
                    <a href="#dashboard" class="btn btn-light btn-lg">
                        <i class="fas fa-tachometer-alt"></i> View Dashboard
                    </a>
                    <a href="#demo" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-play"></i> Live Demo
                    </a>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="floating-card">
                    <div class="card bg-white text-dark">
                        <div class="card-body p-4">
                            <h5><i class="fas fa-chart-pie text-primary"></i> System Statistics</h5>
                            <div class="row text-center mt-3">
                                <div class="col-3">
                                    <h3 class="text-primary">150+</h3>
                                    <small>Properties</small>
                                </div>
                                <div class="col-3">
                                    <h3 class="text-success">50+</h3>
                                    <small>Associates</small>
                                </div>
                                <div class="col-3">
                                    <h3 class="text-warning">300+</h3>
                                    <small>Customers</small>
                                </div>
                                <div class="col-3">
                                    <h3 class="text-info">120+</h3>
                                    <small>Tables</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section id="features" class="demo-section bg-light">
    <div class="container">
        <h2 class="text-center section-title">Advanced System Features</h2>
        <div class="row">
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card feature-card h-100">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="fas fa-users fa-3x text-primary"></i>
                        </div>
                        <h5>MLM Commission System</h5>
                        <p>Multi-level marketing with automatic commission calculation and hierarchical structure management.</p>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-success"></i> Auto Commission Calculation</li>
                            <li><i class="fas fa-check text-success"></i> Parent-Child Hierarchy</li>
                            <li><i class="fas fa-check text-success"></i> Commission Tracking</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card feature-card h-100">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="fas fa-calendar-check fa-3x text-success"></i>
                        </div>
                        <h5>Visit Management</h5>
                        <p>Advanced property visit scheduling with customer journey tracking and analytics.</p>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-success"></i> Site Visit Scheduling</li>
                            <li><i class="fas fa-check text-success"></i> Virtual Tour Management</li>
                            <li><i class="fas fa-check text-success"></i> Follow-up Automation</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card feature-card h-100">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="fas fa-credit-card fa-3x text-warning"></i>
                        </div>
                        <h5>EMI Management</h5>
                        <p>Complete EMI system with payment tracking, installment scheduling, and auto calculations.</p>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-success"></i> EMI Calculator</li>
                            <li><i class="fas fa-check text-success"></i> Payment Reminders</li>
                            <li><i class="fas fa-check text-success"></i> Defaulter Management</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card feature-card h-100">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="fas fa-building fa-3x text-info"></i>
                        </div>
                        <h5>Property Management</h5>
                        <p>Complete property lifecycle management from listing to sale with plot management.</p>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-success"></i> Property Listings</li>
                            <li><i class="fas fa-check text-success"></i> Plot Management</li>
                            <li><i class="fas fa-check text-success"></i> Booking System</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card feature-card h-100">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="fas fa-shield-alt fa-3x text-danger"></i>
                        </div>
                        <h5>Security & Audit</h5>
                        <p>Enterprise-grade security with activity logging, role-based access, and audit trails.</p>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-success"></i> Role-Based Access</li>
                            <li><i class="fas fa-check text-success"></i> Activity Logging</li>
                            <li><i class="fas fa-check text-success"></i> Security Audit</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card feature-card h-100">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="fas fa-robot fa-3x text-purple"></i>
                        </div>
                        <h5>AI Integration</h5>
                        <p>AI-powered customer insights, automated responses, and intelligent recommendations.</p>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-success"></i> Customer Analytics</li>
                            <li><i class="fas fa-check text-success"></i> Auto Responses</li>
                            <li><i class="fas fa-check text-success"></i> Smart Recommendations</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Technology Stack -->
<section id="tech" class="demo-section">
    <div class="container">
        <h2 class="text-center section-title">Technology Stack</h2>
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5><i class="fas fa-server"></i> Backend Technologies</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label>PHP 8.2.12</label>
                            <div class="progress progress-modern">
                                <div class="progress-bar bg-primary" style="width: 95%"></div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label>MariaDB 10.4</label>
                            <div class="progress progress-modern">
                                <div class="progress-bar bg-success" style="width: 90%"></div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label>Apache 2.4.58</label>
                            <div class="progress progress-modern">
                                <div class="progress-bar bg-warning" style="width: 85%"></div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label>PDO & MySQLi</label>
                            <div class="progress progress-modern">
                                <div class="progress-bar bg-info" style="width: 88%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5><i class="fas fa-paint-brush"></i> Frontend Technologies</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label>Bootstrap 5.3.2</label>
                            <div class="progress progress-modern">
                                <div class="progress-bar bg-primary" style="width: 92%"></div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label>Chart.js</label>
                            <div class="progress progress-modern">
                                <div class="progress-bar bg-success" style="width: 87%"></div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label>Font Awesome 6.0</label>
                            <div class="progress progress-modern">
                                <div class="progress-bar bg-warning" style="width: 90%"></div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label>Responsive Design</label>
                            <div class="progress progress-modern">
                                <div class="progress-bar bg-info" style="width: 95%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Dashboard Preview -->
<section id="dashboard" class="demo-section bg-light">
    <div class="container">
        <h2 class="text-center section-title">System Dashboard</h2>
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card stat-card text-center">
                    <div class="card-body">
                        <i class="fas fa-users fa-2x mb-2"></i>
                        <h3>1,247</h3>
                        <p>Total Customers</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card stat-card text-center">
                    <div class="card-body">
                        <i class="fas fa-building fa-2x mb-2"></i>
                        <h3>89</h3>
                        <p>Properties Listed</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card stat-card text-center">
                    <div class="card-body">
                        <i class="fas fa-handshake fa-2x mb-2"></i>
                        <h3>156</h3>
                        <p>Successful Bookings</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card stat-card text-center">
                    <div class="card-body">
                        <i class="fas fa-money-bill-wave fa-2x mb-2"></i>
                        <h3>₹2.4M</h3>
                        <p>Total Commission</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-chart-line"></i> Sales Performance</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="salesChart" height="100"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-chart-pie"></i> Property Types</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="propertyChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Live Demo Section -->
<section id="demo" class="demo-section gradient-bg">
    <div class="container text-center">
        <h2 class="section-title text-white">Live System Demo</h2>
        <p class="lead text-white mb-5">Explore all the features of APS Dream Home system</p>
        
        <div class="row">
            <div class="col-lg-4 mb-3">
                <a href="admin/" class="btn btn-light btn-lg w-100">
                    <i class="fas fa-tachometer-alt"></i><br>
                    Admin Dashboard
                </a>
            </div>
            <div class="col-lg-4 mb-3">
                <a href="visit_management_system.php" class="btn btn-light btn-lg w-100">
                    <i class="fas fa-calendar-check"></i><br>
                    Visit Management
                </a>
            </div>
            <div class="col-lg-4 mb-3">
                <a href="system_test_complete.php" class="btn btn-light btn-lg w-100">
                    <i class="fas fa-chart-bar"></i><br>
                    System Status
                </a>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-lg-6 mb-3">
                <a href="test_payment_emi_system.php" class="btn btn-outline-light btn-lg w-100">
                    <i class="fas fa-credit-card"></i><br>
                    EMI System Demo
                </a>
            </div>
            <div class="col-lg-6 mb-3">
                <a href="system_maintenance.php" class="btn btn-outline-light btn-lg w-100">
                    <i class="fas fa-tools"></i><br>
                    System Maintenance
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Footer -->
<footer class="bg-dark text-white py-4">
    <div class="container text-center">
        <p>&copy; 2024 APS Dream Home - Complete Real Estate ERP/CRM System</p>
        <p class="mb-0">
            <small>Built with <i class="fas fa-heart text-danger"></i> using PHP, MariaDB, Bootstrap & Chart.js</small>
        </p>
    </div>
</footer>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
<script>
// Sales Chart
const salesCtx = document.getElementById('salesChart').getContext('2d');
new Chart(salesCtx, {
    type: 'line',
    data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
        datasets: [{
            label: 'Sales (₹ Lakhs)',
            data: [12, 19, 15, 25, 22, 30],
            borderColor: '#667eea',
            backgroundColor: 'rgba(102, 126, 234, 0.1)',
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: true
            }
        }
    }
});

// Property Chart
const propertyCtx = document.getElementById('propertyChart').getContext('2d');
new Chart(propertyCtx, {
    type: 'doughnut',
    data: {
        labels: ['Residential', 'Commercial', 'Plots', 'Villas'],
        datasets: [{
            data: [40, 25, 20, 15],
            backgroundColor: ['#28a745', '#17a2b8', '#ffc107', '#dc3545']
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// Smooth scrolling
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        document.querySelector(this.getAttribute('href')).scrollIntoView({
            behavior: 'smooth'
        });
    });
});
</script>

</body>
</html>